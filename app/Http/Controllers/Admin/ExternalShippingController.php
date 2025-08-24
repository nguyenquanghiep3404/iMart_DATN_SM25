<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\StoreLocation;
use App\Models\User;
use App\Models\UserStoreLocation;
use App\Services\OrderFulfillmentCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalShippingController extends Controller
{
    protected $fulfillmentCheckService;

    public function __construct(OrderFulfillmentCheckService $fulfillmentCheckService)
    {
        $this->fulfillmentCheckService = $fulfillmentCheckService;
    }

    /**
     * Hiển thị danh sách gói hàng cần giao cho đơn vị thứ 3
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $storeLocation = null;
        
        // Get user's store location
        $userStoreLocation = UserStoreLocation::where('user_id', $user->id)->first();
        if ($userStoreLocation) {
            $storeLocation = StoreLocation::find($userStoreLocation->store_location_id);
        }
        
        // Lấy danh sách kho warehouse mà user được gán
        $userWarehouseIds = UserStoreLocation::where('user_id', $user->id)
            ->whereHas('storeLocation', function($query) {
                $query->where('type', 'warehouse');
            })
            ->pluck('store_location_id')
            ->toArray();
        
        // Lấy các gói hàng đã đóng gói từ các kho mà user có quyền truy cập
        // Bao gồm cả gói hàng đã giao để có thể cập nhật trạng thái "đánh dấu giao thành công"
        $fulfillmentsQuery = OrderFulfillment::with(['order.user', 'storeLocation'])
            ->whereIn('store_location_id', $userWarehouseIds);
                
        // Filter theo trạng thái nếu có
        if ($request->filled('status')) {
            $fulfillmentsQuery->where('status', $request->status);
        } else {
            $fulfillmentsQuery->whereIn('status', ['packed', 'shipped', 'delivered']);
        }
            
        // Tìm kiếm theo mã vận đơn hoặc tên khách hàng
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $fulfillmentsQuery->where(function($query) use ($searchTerm) {
                $query->where('tracking_code', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('order.user', function($userQuery) use ($searchTerm) {
                          $userQuery->where('name', 'LIKE', "%{$searchTerm}%");
                      });
            });
        }
        
        $fulfillments = $fulfillmentsQuery->orderBy('created_at', 'desc')->get();
        
        // Filter các gói hàng thuộc 2 trường hợp đặc biệt (không có kho tại tỉnh đích)
        $filteredFulfillments = $fulfillments->filter(function($fulfillment) {
            return $this->isExternalShippingOrder($fulfillment->order);
        });
        
        // Paginate manually
        $currentPage = request()->get('page', 1);
        $perPage = 20;
        $currentItems = $filteredFulfillments->slice(($currentPage - 1) * $perPage, $perPage);
        $paginatedFulfillments = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredFulfillments->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        return view('admin.external-shipping.index', [
            'fulfillments' => $paginatedFulfillments,
            'storeLocation' => $storeLocation
        ]);
    }

    /**
     * Lấy chi tiết gói hàng để hiển thị trong modal
     */
    public function show($fulfillmentId)
    {
        try {
            Log::info('ExternalShippingController::show called', ['fulfillment_id' => $fulfillmentId]);
            
            $fulfillment = OrderFulfillment::with([
                'order.user',
                'order.shippingProvince:code,name,name_with_type',
                'order.shippingDistrict:code,name,name_with_type',
                'order.shippingWard:code,name,name_with_type',
                'storeLocation',
                'items.orderItem.productVariant.product.coverImage',
                'items.orderItem.productVariant.primaryImage'
            ])->findOrFail($fulfillmentId);

            Log::info('Fulfillment loaded successfully', ['fulfillment_id' => $fulfillmentId, 'tracking_code' => $fulfillment->tracking_code]);

            // Kiểm tra quyền: nhân viên chỉ được xem gói hàng của kho mình và kho phải là warehouse
            $user = auth()->user();
            
            // Lấy danh sách kho warehouse mà user được gán
            $userWarehouseIds = UserStoreLocation::where('user_id', $user->id)
                ->whereHas('storeLocation', function($query) {
                    $query->where('type', 'warehouse');
                })
                ->pluck('store_location_id')
                ->toArray();
            
            Log::info('User warehouse IDs', ['user_id' => $user->id, 'warehouse_ids' => $userWarehouseIds]);
            
            // Kiểm tra xem gói hàng có thuộc kho mà user có quyền truy cập không
            if (!in_array($fulfillment->store_location_id, $userWarehouseIds)) {
                Log::warning('Access denied for fulfillment', ['fulfillment_id' => $fulfillmentId, 'user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xem gói hàng của kho này hoặc kho này không phải là warehouse.'
                ], 403);
            }

            Log::info('Checking if external shipping required', ['fulfillment_id' => $fulfillmentId]);
            
            // Kiểm tra xem đơn hàng có thuộc trường hợp cần giao cho đơn vị thứ 3 không
            if (!$this->isExternalShippingOrder($fulfillment->order)) {
                Log::warning('Order does not require external shipping', ['fulfillment_id' => $fulfillmentId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gói hàng này không thuộc trường hợp cần giao cho đơn vị thứ 3'
                ], 400);
            }

            Log::info('Calculating shipping fee', ['fulfillment_id' => $fulfillmentId]);
            $shippingFee = $this->calculateExternalShippingFee($fulfillment->order);
            
            Log::info('Getting shipping type', ['fulfillment_id' => $fulfillmentId]);
            $shippingType = $this->getShippingType($fulfillment->order);
            
            Log::info('Returning successful response', ['fulfillment_id' => $fulfillmentId, 'shipping_fee' => $shippingFee]);

            return response()->json([
                'success' => true,
                'fulfillment' => $fulfillment,
                'order' => $fulfillment->order,
                'shipping_fee' => $shippingFee,
                'shipping_type' => $shippingType
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in ExternalShippingController::show', [
                'fulfillment_id' => $fulfillmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải chi tiết gói hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gán gói hàng cho đơn vị vận chuyển thứ 3
     */
    public function assignToShippingUnit(Request $request, $fulfillmentId)
    {
        $request->validate([
            'shipping_unit' => 'required|string|in:GHN,GHTK,ViettelPost'
        ]);

        try {
            DB::beginTransaction();

            $fulfillment = OrderFulfillment::with('order')->findOrFail($fulfillmentId);

            // Kiểm tra quyền: nhân viên chỉ được gán gói hàng của kho mình và kho phải là warehouse
            $user = auth()->user();
            
            // Lấy danh sách kho warehouse mà user được gán
            $userWarehouseIds = UserStoreLocation::where('user_id', $user->id)
                ->whereHas('storeLocation', function($query) {
                    $query->where('type', 'warehouse');
                })
                ->pluck('store_location_id')
                ->toArray();
            
            // Kiểm tra xem gói hàng có thuộc kho mà user có quyền truy cập không
            if (!in_array($fulfillment->store_location_id, $userWarehouseIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền gán gói hàng của kho này hoặc kho này không phải là warehouse.'
                ], 403);
            }

            // Kiểm tra điều kiện
            if (!$this->isExternalShippingOrder($fulfillment->order)) {
                throw new \Exception('Gói hàng này không thuộc trường hợp cần giao cho đơn vị thứ 3');
            }

            // Cập nhật trạng thái gói hàng
            $fulfillment->update([
                'status' => OrderFulfillment::STATUS_SHIPPED,
                'shipping_carrier' => $request->shipping_unit,
                'shipped_at' => now()
            ]);

            DB::commit();

            Log::info('Gói hàng được gán cho đơn vị vận chuyển thứ 3', [
                'fulfillment_id' => $fulfillmentId,
                'shipping_unit' => $request->shipping_unit,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã gán gói hàng cho ' . $request->shipping_unit . ' thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi gán gói hàng cho đơn vị vận chuyển thứ 3', [
                'fulfillment_id' => $fulfillmentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đánh dấu gói hàng giao thành công
     */
    public function markAsDelivered(Request $request, $fulfillmentId)
    {
        try {
            DB::beginTransaction();

            $fulfillment = OrderFulfillment::with('order')->findOrFail($fulfillmentId);

            // Kiểm tra quyền: nhân viên chỉ được đánh dấu giao hàng cho gói hàng của kho mình và kho phải là warehouse
            $user = auth()->user();
            
            // Lấy danh sách kho warehouse mà user được gán
            $userWarehouseIds = UserStoreLocation::where('user_id', $user->id)
                ->whereHas('storeLocation', function($query) {
                    $query->where('type', 'warehouse');
                })
                ->pluck('store_location_id')
                ->toArray();
            
            // Kiểm tra xem gói hàng có thuộc kho mà user có quyền truy cập không
            if (!in_array($fulfillment->store_location_id, $userWarehouseIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền đánh dấu giao hàng cho gói hàng của kho này hoặc kho này không phải là warehouse.'
                ], 403);
            }

            // Kiểm tra trạng thái hiện tại
            if ($fulfillment->status !== OrderFulfillment::STATUS_SHIPPED) {
                throw new \Exception('Gói hàng phải ở trạng thái "Đang vận chuyển" mới có thể đánh dấu giao thành công');
            }

            // Cập nhật trạng thái gói hàng
            $fulfillment->update([
                'status' => OrderFulfillment::STATUS_DELIVERED,
                'delivered_at' => now()
            ]);

            DB::commit();

            Log::info('Gói hàng được đánh dấu giao thành công', [
                'fulfillment_id' => $fulfillmentId,
                'tracking_code' => $fulfillment->tracking_code,
                'order_id' => $fulfillment->order_id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu gói hàng giao thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đánh dấu gói hàng giao thành công', [
                'fulfillment_id' => $fulfillmentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra xem đơn hàng có cần giao cho đơn vị thứ 3 không
     */
    private function isExternalShippingRequired($order)
    {
        $canAssign = $this->fulfillmentCheckService->canAssignShipper($order);
        return !$canAssign['can_assign'] && $canAssign['requires_external_shipping'];
    }

    /**
     * Tính phí giao hàng cho đơn vị thứ 3
     */
    public function calculateExternalShippingFee($order)
    {
        $canAssign = $this->fulfillmentCheckService->canAssignShipper($order);
        return $canAssign['shipping_fee'] ?? 0;
    }

    /**
     * Xác định loại giao hàng
     */
    public function getShippingType($order)
    {
        $canAssign = $this->fulfillmentCheckService->canAssignShipper($order);
        return $canAssign['reason'] ?? 'Không xác định';
    }
    
    /**
     * Kiểm tra đơn hàng có thuộc trường hợp đặc biệt không
     */
    public function isSpecialCase($order)
    {
        $canAssign = $this->fulfillmentCheckService->canAssignShipper($order);
        return !$canAssign['can_assign'] && $canAssign['requires_external_shipping'];
    }
    
    /**
     * Kiểm tra đơn hàng có phải là external shipping không (không phụ thuộc trạng thái)
     */
    private function isExternalShippingOrder($order)
    {
        // Kiểm tra trực tiếp xem có warehouse trong tỉnh đích không
        $destinationProvince = \App\Models\ProvinceOld::find($order->shipping_old_province_code);
        
        if (!$destinationProvince) {
            return false;
        }
        
        // Tìm warehouse trong tỉnh đích
        $warehouseInProvince = \App\Models\StoreLocation::where('type', 'warehouse')
            ->where('province_code', $destinationProvince->code)
            ->where('is_active', true)
            ->first();
            
        // Nếu không có warehouse trong tỉnh đích thì là external shipping
        return !$warehouseInProvince;
    }
}