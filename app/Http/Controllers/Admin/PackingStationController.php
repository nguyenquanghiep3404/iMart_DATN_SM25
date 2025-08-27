<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventorySerial;
use App\Models\OrderItemSerial;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StoreLocation;
use App\Models\User;
use App\Models\UserStoreLocation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Class PackingStationController
 * @package App\Http\Controllers\Admin
 *
 * Controller này quản lý logic cho giao diện Trạm Đóng Gói.
 */
class PackingStationController extends Controller
{
    /**
     * Hiển thị giao diện chính của Trạm Đóng Gói.
     *
     * @return \Illuminate\View\View
     */
    public function index()
     {
         $user = Auth::user();
         $storeLocation = null;
         
         // Get user's store location
         $userStoreLocation = UserStoreLocation::where('user_id', $user->id)->first();
         if ($userStoreLocation) {
             $storeLocation = StoreLocation::find($userStoreLocation->store_location_id);
         }
         
         return view('admin.packing_station.index', compact('storeLocation'));
     }
     
     /**
      * Lấy danh sách gói hàng chờ đóng gói của kho hiện tại
      * 
      * @return \Illuminate\Http\JsonResponse
      */
     public function getPendingOrders()
     {
        try {
            $user = Auth::user();
            
            // Lấy danh sách kho warehouse mà user được gán
            $userWarehouseIds = UserStoreLocation::where('user_id', $user->id)
                ->whereHas('storeLocation', function($query) {
                    $query->where('type', 'warehouse');
                })
                ->pluck('store_location_id')
                ->toArray();
            
            if (empty($userWarehouseIds)) {
                return response()->json(['error' => 'Bạn chưa được gán vào kho warehouse nào'], 403);
            }
            
            // Lấy 20 gói hàng gần nhất ở trạng thái "processing" thuộc các kho warehouse của người dùng
            $pendingFulfillments = \App\Models\OrderFulfillment::with(['order'])
                ->where('status', 'processing')
                ->whereIn('store_location_id', $userWarehouseIds)
                ->whereHas('order', function($query) {
                    $query->where('status', 'processing');
                })
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
            $formattedPackages = $pendingFulfillments->map(function($fulfillment) {
                return [
                    'id' => $fulfillment->order->id,
                    'order_code' => $fulfillment->order->order_code,
                    'customer_name' => $fulfillment->order->customer_name,
                    'created_at' => Carbon::parse($fulfillment->created_at)->format('d/m/Y H:i'),
                    'tracking_codes' => [$fulfillment->tracking_code],
                    'first_tracking_code' => $fulfillment->tracking_code,
                    'total_packages' => 1
                ];
            });
            
            return response()->json($formattedPackages);
            
        } catch (\Exception $e) {
            // Bỏ qua lỗi "Không đủ tồn kho cho sản phẩm"
            if (strpos($e->getMessage(), 'Không đủ tồn kho cho sản phẩm') !== false) {
                // Trả về danh sách trống nếu có lỗi tồn kho
                return response()->json([]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách gói hàng: ' . $e->getMessage()
            ], 500);
        }
     }

    /**
     * Tìm kiếm gói hàng theo mã vận đơn
     */
    public function getPackageByTrackingCode($trackingCode)
    {
        try {
            // Tìm đơn hàng theo tracking code từ bảng order_fulfillments
            $fulfillment = \App\Models\OrderFulfillment::where('tracking_code', $trackingCode)
                ->with([
                    'order' => function($query) {
                        $query->where('status', 'processing')
                            ->with([
                                'user',
                                'storeLocation'
                            ]);
                    },
                    'items' => function($query) {
                        $query->with(['orderItem.productVariant.product']);
                    }
                ])
                ->first();

            if (!$fulfillment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy gói hàng với mã vận đơn này.'
                ], 404);
            }

            if (!$fulfillment->order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gói hàng này không ở trạng thái processing hoặc đã được xử lý.'
                ], 404);
            }

            $order = $fulfillment->order;

            // Kiểm tra quyền: nhân viên chỉ được đóng gói gói hàng của kho mình và kho phải là warehouse
            $user = auth()->user();
            
            // Lấy danh sách kho warehouse mà user được gán
            $userWarehouseIds = UserStoreLocation::where('user_id', $user->id)
                ->whereHas('storeLocation', function($query) {
                    $query->where('type', 'warehouse');
                })
                ->pluck('store_location_id')
                ->toArray();
            
            if (!in_array($fulfillment->store_location_id, $userWarehouseIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền đóng gói gói hàng của kho này hoặc kho này không phải là warehouse.'
                ], 403);
            }

            // Lấy thông tin địa chỉ đầy đủ
            $wardName = null;
            $districtName = null;
            $provinceName = null;
            
            if ($order->shipping_old_ward_code) {
                $ward = \App\Models\WardOld::where('code', $order->shipping_old_ward_code)->first();
                $wardName = $ward ? $ward->name_with_type : $order->shipping_old_ward_code;
            }
            
            if ($order->shipping_old_district_code) {
                $district = \App\Models\DistrictOld::where('code', $order->shipping_old_district_code)->first();
                $districtName = $district ? $district->name_with_type : $order->shipping_old_district_code;
            }
            
            if ($order->shipping_old_province_code) {
                $province = \App\Models\ProvinceOld::where('code', $order->shipping_old_province_code)->first();
                $provinceName = $province ? $province->name_with_type : $order->shipping_old_province_code;
            }

            // Chuẩn bị dữ liệu gói hàng
            $packageData = [
                'tracking_code' => $fulfillment->tracking_code,
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'shipping_address_line1' => $order->shipping_address_line1,
                'shipping_old_ward_code' => $order->shipping_old_ward_code,
                'shipping_old_district_code' => $order->shipping_old_district_code,
                'shipping_old_province_code' => $order->shipping_old_province_code,
                'store_location_name' => $order->storeLocation->name ?? 'N/A',
                'items' => collect($fulfillment->items)->map(function ($fulfillmentItem) {
                    $orderItem = $fulfillmentItem->orderItem;
                    $productVariant = $orderItem->productVariant;
                    return [
                        'id' => $orderItem->id,
                        'product_name' => $productVariant->product->name,
                        'variant_name' => $productVariant->name,
                        'sku' => $productVariant->sku,
                        'price' => $orderItem->price,
                        'quantity' => $fulfillmentItem->quantity,
                        'product_image' => $productVariant->image_url,
                        'product_variant_id' => $orderItem->product_variant_id,
                        'requires_imei' => (bool) $productVariant->has_serial_tracking,
                        'imei_input' => '',
                        'imei_scanned' => false,
                        'imei_error' => null
                    ];
                })
            ];

            // Thêm địa chỉ đầy đủ
            $packageData['shipping_address_full'] = implode(', ', array_filter([
                $order->shipping_address_line1,
                $wardName,
                $districtName,
                $provinceName
            ]));

            return response()->json([
                'success' => true,
                'data' => $packageData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tìm kiếm gói hàng: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Lấy thông tin chi tiết của một đơn hàng.
     * Eager load các sản phẩm và kiểm tra xem sản phẩm nào cần quét serial.
     *
     * @param int $id ID của đơn hàng
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetails($id)
    {
        $order = Order::with(['orderItems.productVariant.product'])->findOrFail($id);

        // Xây dựng lại response để thêm cờ 'requires_imei'
        $response = $order->toArray();
        foreach ($response['order_items'] as $key => $item) {
            // Dựa vào CSDL, ta có thể giả định một sản phẩm cần serial nếu
            // nó có theo dõi trong bảng inventory_serials.
            // Một cách tiếp cận đơn giản là kiểm tra xem có bất kỳ serial nào
            // tồn tại cho product_variant_id này không.
            // Trong thực tế, có thể có một cờ `manage_serial` trên bảng products.
            
            // Ở đây, ta sẽ kiểm tra xem sản phẩm có được quản lý bằng serial không.
            // Logic này cần được điều chỉnh cho phù hợp với quy trình nghiệp vụ của bạn.
            // Ví dụ: kiểm tra category của sản phẩm.
            $product = Product::find($item['product_variant']['product_id']);
            $response['order_items'][$key]['requires_imei'] = $this->productRequiresSerial($product);
            
            // Khởi tạo các trường cho front-end
            $response['order_items'][$key]['imei_input'] = '';
            $response['order_items'][$key]['imei_scanned'] = false;
        }

        return response()->json($response);
    }

    /**
     * Xác thực một mã IMEI/Serial được quét.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateImei(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string',
            'product_variant_id' => 'required|integer|exists:product_variants,id',
        ]);

        $serial = InventorySerial::where('serial_number', $validated['serial_number'])
            ->where('product_variant_id', $validated['product_variant_id'])
            ->first();

        if (!$serial) {
            return response()->json([
                'success' => false,
                'message' => 'Mã Serial/IMEI không tồn tại cho sản phẩm này.'
            ], 404);
        }

        if ($serial->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => "Mã Serial/IMEI đã được sử dụng hoặc đang ở trạng thái không hợp lệ ({$serial->status})."
            ], 400);
        }

        return response()->json(['success' => true, 'message' => 'Mã hợp lệ.']);
    }

    /**
     * Xác nhận đóng gói theo mã vận đơn
     */
    public function confirmPackaging(Request $request, $trackingCode)
    {
        try {
            // Validate request - không yêu cầu serial_number bắt buộc
            $request->validate([
                'tracking_code' => 'required|string',
                'items' => 'required|array',
                'items.*.order_item_id' => 'required|integer',
                'items.*.product_variant_id' => 'required|integer',
                'items.*.serial_number' => 'nullable|string'
            ]);

            // Tìm đơn hàng theo mã vận đơn từ bảng order_fulfillments
            $fulfillment = \App\Models\OrderFulfillment::where('tracking_code', $trackingCode)
                ->with([
                    'order' => function($query) {
                        $query->where('status', 'processing')
                            ->with(['orderItems.productVariant.product', 'storeLocation']);
                    }
                ])
                ->first();

            if (!$fulfillment || !$fulfillment->order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy gói hàng với mã vận đơn này hoặc gói hàng không ở trạng thái cần đóng gói.'
                ]);
            }

            $order = $fulfillment->order;

            // Kiểm tra quyền: nhân viên chỉ được đóng gói gói hàng của kho mình và kho phải là warehouse
            $user = auth()->user();
            
            // Lấy danh sách kho warehouse mà user được gán
            $userWarehouseIds = UserStoreLocation::where('user_id', $user->id)
                ->whereHas('storeLocation', function($query) {
                    $query->where('type', 'warehouse');
                })
                ->pluck('store_location_id')
                ->toArray();
            
            if (!in_array($fulfillment->store_location_id, $userWarehouseIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền đóng gói gói hàng của kho này hoặc kho này không phải là warehouse.'
                ]);
            }

            DB::beginTransaction();

            // Xử lý từng sản phẩm trong gói hàng (chỉ các item thuộc fulfillment này)
            foreach ($fulfillment->items as $fulfillmentItem) {
                $orderItem = $fulfillmentItem->orderItem;
                $product = $orderItem->productVariant->product;
                $quantityToPack = $fulfillmentItem->quantity; // Số lượng cần đóng gói cho gói hàng này

                // Kiểm tra tồn kho theo fulfillment store location
                $inventory = \App\Models\ProductInventory::where('product_variant_id', $orderItem->product_variant_id)
                    ->where('store_location_id', $fulfillment->store_location_id)
                    ->where('inventory_type', 'new')
                    ->first();

                // Tạo inventory nếu chưa có
                if (!$inventory) {
                    $inventory = \App\Models\ProductInventory::create([
                        'product_variant_id' => $orderItem->product_variant_id,
                        'store_location_id' => $fulfillment->store_location_id,
                        'quantity' => $quantityToPack,
                        'inventory_type' => 'new'
                    ]);
                }
                // Đảm bảo đủ số lượng
                elseif ($inventory->quantity < $quantityToPack) {
                    $inventory->update(['quantity' => $quantityToPack]);
                }

                // Xử lý serial number nếu variant yêu cầu
                if ($orderItem->productVariant->has_serial_tracking) {
                    $itemData = collect($request->items)->firstWhere('order_item_id', $orderItem->id);
                    if (!$itemData || empty($itemData['serial_number'])) {
                        throw new \Exception("Thiếu thông tin serial cho sản phẩm: {$product->name}");
                    }

                    // Tìm và cập nhật serial
                    $serial = InventorySerial::where('serial_number', $itemData['serial_number'])
                        ->where('product_variant_id', $orderItem->product_variant_id)
                        ->where('status', 'available')
                        ->first();

                    if (!$serial) {
                        throw new \Exception("Serial không hợp lệ: {$itemData['serial_number']}");
                    }

                    // Cập nhật trạng thái serial
                    $serial->update(['status' => 'sold']);

                    // Lưu vào bảng order_item_serials
                    OrderItemSerial::create([
                        'order_item_id' => $orderItem->id,
                        'product_variant_id' => $orderItem->product_variant_id,
                        'serial_number' => $itemData['serial_number'],
                        'status' => 'sold'
                    ]);
                }

                // Trừ tồn kho theo số lượng trong gói hàng
                $inventory->decrement('quantity', $quantityToPack);

                // Tạo movement record
                InventoryMovement::create([
                    'product_variant_id' => $orderItem->product_variant_id,
                    'store_location_id' => $fulfillment->store_location_id,
                    'lot_id' => null,
                    'inventory_type' => 'available',
                    'quantity_change' => -$quantityToPack,
                    'quantity_after_change' => $inventory->quantity,
                    'reason' => 'Packed for Package',
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'user_id' => $user->id,
                ]);
            }

            // Cập nhật trạng thái fulfillment thành packed
            $fulfillment->update(['status' => 'packed']);
            
            \Log::info('Đã cập nhật trạng thái fulfillment thành packed', [
                'fulfillment_id' => $fulfillment->id,
                'tracking_code' => $fulfillment->tracking_code,
                'order_id' => $order->id
            ]);

            // Kiểm tra trạng thái của tất cả fulfillments trong đơn hàng
            $order->refresh(); // Làm mới dữ liệu từ database
            $fulfillmentStatuses = $order->fulfillments()->pluck('status');
            
            \Log::info('Kiểm tra trạng thái các gói hàng sau khi đóng gói', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'fulfillment_statuses' => $fulfillmentStatuses->toArray(),
                'packed_fulfillment' => $fulfillment->tracking_code
            ]);
            
            // Logic cập nhật trạng thái đơn hàng dựa trên trạng thái các gói hàng:
            // - Nếu tất cả gói hàng đã packed -> giữ nguyên trạng thái processing
            // - Đơn hàng chỉ chuyển sang trạng thái khác khi có gói hàng được giao cho đơn vị vận chuyển
            $allFulfillmentsPacked = $fulfillmentStatuses->every(function($status) {
                return $status === 'packed';
            });
            
            if ($allFulfillmentsPacked) {
                \Log::info('Tất cả gói hàng đã được đóng gói xong', [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code
                ]);
                
                // Kiểm tra và tạo transfer order nếu cần
                $fulfillmentService = new \App\Services\OrderFulfillmentCheckService();
                $fulfillmentService->createAutoTransferIfNeeded($order);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đóng gói thành công! Gói hàng ' . $trackingCode . ' đã được đóng gói xong.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    /**
     * Hàm helper để kiểm tra xem một sản phẩm có cần quét serial hay không.
     * Logic này nên được tùy chỉnh cho phù hợp với hệ thống của bạn.
     *
     * @param \App\Models\Product $product
     * @return bool
     */
    private function productRequiresSerial(Product $product): bool
    {
        // Kiểm tra xem có variant nào của sản phẩm này có bật serial tracking không
        return $product->variants()->where('has_serial_tracking', true)->exists();
    }
}