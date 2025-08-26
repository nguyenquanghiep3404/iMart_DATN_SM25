<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Get fulfillments (packages) that need shipper assignment.
     * LOGIC MỚI: Lấy các gói hàng (fulfillments) thay vì đơn hàng (orders).
     */
    public function getOrders(Request $request)
    {
        try {
            // Bắt đầu truy vấn từ OrderFulfillment
            $fulfillments = OrderFulfillment::with([
                // XÓA Ở ĐÂY: Bỏ 'order.province' và 'order.district' vì không cần thiết và gây lỗi
                'order' => function ($query) {
                    $query->select(
                        'id',
                        'order_code',
                        'customer_name',
                        'customer_phone',
                        'shipping_address_line1',
                        'shipping_address_line2',
                        'desired_delivery_date',
                        'shipping_old_province_code as province_id',
                        'shipping_old_district_code as district_id',
                        'grand_total'
                    );
                }
            ])
                ->select(
                'id', 'order_id', 'status', 'shipper_id', 
                'tracking_code', 'estimated_delivery_date' // <-- LẤY CẢ 2 TRƯỜNG MỚI
                )
                ->where('status', 'packed')
                ->whereNull('shipper_id')
                ->whereHas('order', function ($query) {
                    $query->whereIn('shipping_old_province_code', function ($subQuery) {
                        $subQuery->select('province_code')
                            ->from('store_locations')
                            ->where('type', 'warehouse')
                            ->where('is_active', true);
                    });
                })
 ->orderBy('estimated_delivery_date', 'asc') // <-- Sắp xếp theo trường mới
            ->get()
                ->map(function ($fulfillment) {
                    // Xử lý ghép địa chỉ
                    $address = trim($fulfillment->order->shipping_address_line1 . ', ' . $fulfillment->order->shipping_address_line2, ', ');

                    return [
                        'id' => $fulfillment->id,
                        'tracking_code' => $fulfillment->tracking_code,
                    'deadline' => $fulfillment->estimated_delivery_date, 
                        'order_id' => $fulfillment->order->id,
                        'order_code' => $fulfillment->order->order_code,
                        'customer_name' => $fulfillment->order->customer_name,
                        'customer_phone' => $fulfillment->order->customer_phone,
                        'address' => $address,
                        'total_amount' => $fulfillment->order->grand_total,
                        'province_id' => $fulfillment->order->province_id,
                        'district_id' => $fulfillment->order->district_id,
                    ];
                });

            return response()->json(['success' => true, 'data' => $fulfillments]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách gói hàng: ' . $e->getMessage()
            ], 500);
        }
    }






    /**
     * Get available shippers based on the selected province.
     * LOGIC MỚI: Lọc shipper theo kho hàng tại tỉnh/thành được chọn.
     */
    public function getShippers(Request $request, $province_code = null)
    {
        try {
            // SỬA Ở ĐÂY: Thay thế where('role', 'shipper') bằng whereHas('roles', ...)
            $shippersQuery = User::whereHas('roles', function ($query) {
                $query->where('name', 'shipper');
            })->where('status', 'active');
            // KẾT THÚC SỬA

            if ($province_code) {
                // Nếu có province_code, chỉ lấy shipper thuộc các kho ở tỉnh đó
                $shippersQuery->whereHas('storeLocations', function ($query) use ($province_code) {
                    $query->where('type', 'warehouse')
                        ->where('province_code', $province_code);
                });
            } else {
                // Nếu không có province_code, không trả về shipper nào để buộc người dùng phải chọn tỉnh
                return response()->json(['success' => true, 'data' => []]);
            }

            $shippers = $shippersQuery->select(['id', 'name', 'email', 'phone_number'])
                ->orderBy('name')
                ->get()
                ->map(function ($shipper) {
                    $warehouse = $shipper->storeLocations()->where('type', 'warehouse')->first();
                    return [
                        'id' => $shipper->id,
                        'name' => $shipper->name,
                        'email' => $shipper->email,
                        'phone' => $shipper->phone_number ?? 'N/A',
                        'area' => $warehouse ? $warehouse->name : 'Chưa rõ khu vực'
                    ];
                });

            return response()->json(['success' => true, 'data' => $shippers]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách shipper: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get provinces for filtering
     * Cải tiến: Chỉ lấy các tỉnh/thành có gói hàng đang chờ gán.
     */
    public function getProvinces()
    {
        try {
            // Lấy mã tỉnh từ các đơn hàng có fulfillment đang chờ gán
            $provinceCodes = Order::whereHas('fulfillments', function ($q) {
                $q->where('status', 'packed')->whereNull('shipper_id');
            })->distinct()->pluck('shipping_old_province_code');

            $provinces = ProvinceOld::whereIn('code', $provinceCodes)
                ->select(['code as id', 'name'])
                ->orderBy('name')
                ->get();

            return response()->json(['success' => true, 'data' => $provinces]);
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
    public function getDistricts($province_code)
    {
        try {
            $districts = DistrictOld::where('parent_code', $province_code)
                ->select(['code as id', 'name'])
                ->orderBy('name')
                ->get();

            return response()->json(['success' => true, 'data' => $districts]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách quận/huyện: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Assign shipper to selected fulfillments (packages)
     * LOGIC MỚI: Gán shipper cho từng gói hàng (fulfillment).
     */
    public function assignShipper(Request $request)
    {
        $request->validate([
            'fulfillment_ids' => 'required|array|min:1',
            'fulfillment_ids.*' => 'required|integer|exists:order_fulfillments,id',
            'shipper_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $shipperExists = User::where('id', $value)
                        ->whereHas('roles', function ($query) {
                            $query->where('name', 'shipper');
                        })
                        ->exists();

                    if (!$shipperExists) {
                        $fail('Shipper được chọn không hợp lệ hoặc không có vai trò shipper.');
                    }
                },
            ],
        ]);

        DB::beginTransaction();
        try {
            $fulfillmentIds = $request->fulfillment_ids;
            $shipperId = $request->shipper_id;
            $shipper = User::find($shipperId);

            $fulfillments = OrderFulfillment::whereIn('id', $fulfillmentIds)
                ->where('status', 'packed')
                ->whereNull('shipper_id')
                ->get();

            if ($fulfillments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có gói hàng hợp lệ nào để gán. Vui lòng làm mới trang.'
                ], 404);
            }

            // Chỉ cập nhật bảng order_fulfillments
            foreach ($fulfillments as $fulfillment) {
                $fulfillment->update([
                    'shipper_id' => $shipperId,
                    // THAY ĐỔI 1: Cập nhật status thành 'awaiting_shipment'
                    'status' => 'awaiting_shipment',
                    'shipped_at' => Carbon::now()
                ]);
            }

            // THAY ĐỔI 2: Đã xóa hoàn toàn khối code cập nhật trạng thái của bảng 'orders'

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã gán shipper {$shipper->name} thành công cho {$fulfillments->count()} gói hàng. Trạng thái gói hàng đã được chuyển sang 'Chờ vận chuyển'."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gán shipper: ' . $e->getMessage()
            ], 500);
        }
    }


}