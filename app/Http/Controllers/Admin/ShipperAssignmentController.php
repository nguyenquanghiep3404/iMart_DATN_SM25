<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShipperAssignmentController extends Controller
{
    /**
     * Display the shipper assignment page
     */
    public function index()
    {
        return view('admin.shipper-assignment.index');
    }

    /**
     * Get orders that need shipper assignment
     */
    public function getOrders(Request $request)
    {
        try {
            $orders = Order::with(['user', 'province', 'district', 'fulfillments'])
                ->where('status', 'confirmed') // Only confirmed orders
                ->whereNull('shipper_id') // Not assigned to any shipper yet
                ->whereNotNull('delivery_deadline') // Has delivery deadline
                ->whereHas('fulfillments', function($query) {
                    $query->whereNotNull('tracking_code');
                }) // Must have tracking code in fulfillment
                ->select([
                    'id',
                    'order_code',
                    'user_id',
                    'total_amount',
                    'shipping_address',
                    'delivery_deadline',
                    'province_id',
                    'district_id',
                    'shipper_id'
                ])
                ->orderBy('delivery_deadline', 'asc')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'customer_name' => $order->user->name ?? 'N/A',
                        'customer_phone' => $order->user->phone ?? 'N/A',
                        'shipping_address' => $order->shipping_address,
                        'total_amount' => $order->total_amount,
                        'delivery_deadline' => $order->delivery_deadline,
                        'province_id' => $order->province_id,
                        'district_id' => $order->district_id,
                        'province_name' => $order->province->name ?? 'N/A',
                        'district_name' => $order->district->name ?? 'N/A',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách gói hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available shippers
     */
    public function getShippers(Request $request)
    {
        try {
            $shippers = User::where('role', 'shipper')
                ->where('status', 'active')
                ->select(['id', 'name', 'email', 'phone'])
                ->orderBy('name')
                ->get()
                ->map(function ($shipper) {
                    return [
                        'id' => $shipper->id,
                        'name' => $shipper->name,
                        'email' => $shipper->email,
                        'phone' => $shipper->phone ?? 'N/A',
                        'area' => 'Toàn quốc' // Default area, can be customized
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $shippers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách shipper: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get provinces for filtering (only provinces with warehouses)
     */
    public function getProvinces()
    {
        try {
            $provinces = ProvinceOld::select(['provinces_old.code', 'provinces_old.name'])
                ->join('store_locations', 'provinces_old.code', '=', 'store_locations.province_code')
                ->where('store_locations.type', 'warehouse')
                ->where('store_locations.is_active', true)
                ->distinct()
                ->orderBy('provinces_old.name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $provinces
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách tỉnh/thành: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get districts by province for filtering
     */
    public function getDistricts($province)
    {
        try {
            if (!$province) {
                return response()->json([
                    'success' => false,
                    'message' => 'Province code is required'
                ], 400);
            }

            $districts = DistrictOld::where('parent_code', $province)
                ->select(['code', 'name'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $districts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách quận/huyện: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign shipper to selected packages
     */
    public function assignShipper(Request $request)
    {
        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'required|integer|exists:orders,id',
            'shipper_id' => 'required|integer|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $packageIds = $request->package_ids;
            $shipperId = $request->shipper_id;

            // Verify shipper exists and has correct role
            $shipper = User::where('id', $shipperId)
                ->where('role', 'shipper')
                ->where('status', 'active')
                ->first();

            if (!$shipper) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipper không tồn tại hoặc không hoạt động'
                ], 404);
            }

            // Get orders that can be assigned (must have tracking code)
            $orders = Order::whereIn('id', $packageIds)
                ->where('status', 'confirmed')
                ->whereNull('shipper_id')
                ->whereNotNull('tracking_code')
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng hợp lệ để gán shipper. Đơn hàng phải có trạng thái "confirmed" và đã có mã vận đơn.'
                ], 404);
            }

            $updatedCount = 0;
            $trackingCodes = [];

            foreach ($orders as $order) {
                // Gán shipper cho tất cả fulfillments của đơn hàng
                $order->fulfillments()->update([
                    'shipper_id' => $shipperId,
                    'status' => \App\Models\OrderFulfillment::STATUS_SHIPPED,
                    'shipped_at' => Carbon::now()
                ]);
                
                // Update order with shipper only - tracking code should already exist
                $order->update([
                    'shipper_id' => $shipperId,
                    'status' => 'shipping', // Change status to shipping
                    'shipped_at' => Carbon::now()
                ]);

                $updatedCount++;
                $fulfillment = $order->fulfillments()->whereNotNull('tracking_code')->first();
                $trackingCodes[] = [
                    'order_code' => $order->order_code,
                    'tracking_code' => $fulfillment ? $fulfillment->tracking_code : 'N/A'
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã gán shipper thành công cho {$updatedCount} đơn hàng",
                'data' => [
                    'updated_count' => $updatedCount,
                    'shipper_name' => $shipper->name,
                    'tracking_codes' => $trackingCodes
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gán shipper: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get assignment statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_pending' => Order::where('status', 'confirmed')
                    ->whereNull('shipper_id')
                    ->whereNotNull('tracking_code')
                    ->count(),
                'assigned_today' => Order::whereNotNull('shipper_id')
                    ->whereDate('shipped_at', Carbon::today())
                    ->count(),
                'overdue_packages' => Order::where('status', 'confirmed')
                    ->whereNull('shipper_id')
                    ->whereNotNull('tracking_code')
                    ->where('delivery_deadline', '<', Carbon::now())
                    ->count(),
                'active_shippers' => User::where('role', 'shipper')
                    ->where('status', 'active')
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải thống kê: ' . $e->getMessage()
            ], 500);
        }
    }
}