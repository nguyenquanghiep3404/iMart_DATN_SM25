<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;

class PurchaseOrderController extends Controller
{
    /**
     * Hiển thị danh sách các phiếu nhập kho.
     */
    public function index(Request $request)
    {
        // Bắt đầu query với eager loading để tối ưu
        $query = PurchaseOrder::with(['supplier', 'storeLocation', 'items']);

        // 1. Lọc theo từ khóa (mã phiếu hoặc tên NCC)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('po_code', 'like', "%{$searchTerm}%")
                  ->orWhereHas('supplier', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // 2. Lọc theo kho nhận
        if ($request->filled('location_id')) {
            // Lưu ý: Cần có cột 'store_location_id' trong bảng 'purchase_orders'
            $query->where('store_location_id', $request->input('location_id'));
        }

        // 3. Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sắp xếp
        $query->orderBy($request->input('sort_by', 'order_date'), $request->input('sort_dir', 'desc'));

        // Phân trang
        $purchaseOrders = $query->paginate(15)->withQueryString();

        // Lấy dữ liệu cho các bộ lọc dropdown
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();

        $statuses = [
            'pending' => 'Đang chờ',
            'completed' => 'Hoàn thành', // Giả sử có trạng thái này
            'cancelled' => 'Đã hủy', // Giả sử có trạng thái này
        ];

        return view('admin.purchase_orders.index', compact('purchaseOrders', 'locations', 'statuses'));
    }
    public function create()
    {
        // Lấy danh sách Tỉnh/Thành phố
        $provinces = ProvinceOld::orderBy('name')->get();

        // Lấy danh sách Nhà cung cấp và các địa chỉ liên quan
        // Eager load 'addresses' và các quan hệ địa chỉ của nó để tối ưu
        $suppliers = Supplier::with([
            'addresses', 
            'addresses.province', 
            'addresses.district', 
            'addresses.ward'
        ])->get();

        // Lấy danh sách Kho hàng và các quan hệ địa chỉ
        $locations = StoreLocation::with(['province', 'district', 'ward'])
            ->where('is_active', true)
            ->get();
            
        return view('admin.purchase_orders.create', compact('suppliers', 'provinces', 'locations'));
    }

    // Các hàm khác như create, store, show, edit, update, destroy sẽ được thêm ở đây
}