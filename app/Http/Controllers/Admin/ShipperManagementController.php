<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Order;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ShipperManagementController extends Controller
{
    /**
     * Hiển thị trang danh sách kho trang index.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        // Lấy danh sách kho (warehouse) với thông tin tỉnh/thành và số lượng shipper
        $warehouses = StoreLocation::with(['province', 'district'])
            ->where('type', 'warehouse')
            ->where('is_active', true)
            ->withCount(['shippers as shipper_count' => function ($query) {
                $shipperRole = Role::where('name', 'shipper')->first();
                if ($shipperRole) {
                    $query->whereHas('roles', function ($q) use ($shipperRole) {
                        $q->where('role_id', $shipperRole->id);
                    });
                }
            }])
            ->orderBy('name')
            ->paginate($perPage);
        // Lấy danh sách tỉnh/thành để filter
        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();
        return view('admin.shippers.index', compact('warehouses', 'provinces'));
    }
    /**
     * Hiển thị danh sách shipper của một kho cụ thể.
     */
    public function showWarehouse(int $warehouseId, Request $request)
    {
        $warehouse = StoreLocation::with(['province', 'district'])
            ->where('type', 'warehouse')
            ->findOrFail($warehouseId);

        $shipperRole = Role::where('name', 'shipper')->firstOrFail();

        $query = User::whereHas('roles', fn($q) => $q->where('role_id', $shipperRole->id))
            ->whereHas('warehouseAssignments', function ($q) use ($warehouseId) {
                $q->where('store_location_id', $warehouseId);
            })
            ->withCount([
                'shipperOrders as assigned_orders_count', // Đếm tổng số đơn được gán
                'shipperOrders as delivered_orders_count' => function ($q) {
                    $q->where('status', 'delivered'); // Chỉ đếm các đơn có trạng thái 'delivered'
                }
            ]);

        // Logic filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $shippers = $query->latest()->paginate(10);

        // Tính toán stats cho kho này
        $allShippersQuery = User::whereHas('roles', function ($q) use ($shipperRole) {
            $q->where('role_id', $shipperRole->id);
        })->whereHas('warehouseAssignments', function ($q) use ($warehouseId) {
            $q->where('store_location_id', $warehouseId);
        });

        $allShipperIds = (clone $allShippersQuery)->pluck('id');

        $stats = [
            'total' => $allShippersQuery->count(),
            'active' => (clone $allShippersQuery)->where('status', 'active')->count(),
            'inactive' => (clone $allShippersQuery)->where('status', 'inactive')->count(),
            'assigned' => Order::whereIn('shipped_by', $allShipperIds)->count(),
            'delivered' => Order::whereIn('shipped_by', $allShipperIds)->where('status', 'delivered')->count(),
        ];

        return view('admin.shippers.warehouse', compact('warehouse', 'shippers', 'stats'));
    }

    /**
     * Hiển thị form để tạo một nhân viên mới.
     */
    public function create()
    {
        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();
        
        // Nếu có warehouse_id từ URL, chỉ hiển thị kho đó và tỉnh tương ứng
        if (request()->has('warehouse_id')) {
            $selectedWarehouse = StoreLocation::where('type', 'warehouse')
                ->where('is_active', true)
                ->where('id', request('warehouse_id'))
                ->with(['province'])
                ->first();
            
            if ($selectedWarehouse) {
                $warehouses = collect([$selectedWarehouse]);
                $selectedProvince = $selectedWarehouse->province;
            } else {
                $warehouses = collect();
                $selectedProvince = null;
            }
        } else {
            // Nếu không có warehouse_id, hiển thị tất cả kho
            $warehouses = StoreLocation::where('type', 'warehouse')
                ->where('is_active', true)
                ->with(['province'])
                ->orderBy('name')
                ->get();
            $selectedProvince = null;
        }
        
        return view('admin.shippers.create', compact('provinces', 'warehouses', 'selectedProvince'));
    }

    /**
     * Lưu một nhân viên mới vào database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:15', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'status' => ['required', 'in:active,inactive,banned'],
            'province_code' => ['required', 'exists:provinces_old,code'],
            'warehouse_id' => ['required', 'exists:store_locations,id'],
        ], [
            'name.required' => 'Vui lòng nhập tên nhân viên.',
            'name.string' => 'Tên nhân viên phải là chuỗi ký tự.',
            'name.max' => 'Tên nhân viên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.unique' => 'Số điện thoại này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_code.exists' => 'Tỉnh/thành phố không tồn tại.',
            'warehouse_id.required' => 'Vui lòng chọn kho làm việc.',
            'warehouse_id.exists' => 'Kho làm việc không tồn tại.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        $shipperRole = Role::where('name', 'shipper')->first();
        if ($shipperRole) {
            $user->roles()->attach($shipperRole);
        }

        // Gán shipper vào kho
        if ($request->warehouse_id) {
            $user->warehouseAssignments()->attach($request->warehouse_id);
        }

        // Kiểm tra nếu có warehouse_id trong request, quay lại trang warehouse detail
        if ($request->has('warehouse_id')) {
            $warehouse = StoreLocation::find($request->warehouse_id);
            return redirect()->route('admin.shippers.warehouse.show', $warehouse)->with('success', 'Thêm nhân viên giao hàng thành công.');
        }

        return redirect()->route('admin.shippers.index')->with('success', 'Thêm nhân viên giao hàng thành công.');
    }

    /**
     * Hiển thị form để chỉnh sửa thông tin một nhân viên.
     */
    public function edit(User $shipper)
    {
        if (!$shipper->hasRole('shipper')) {
            abort(404);
        }
        
        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();
        
        // Lấy kho hiện tại của shipper
        $currentWarehouse = $shipper->warehouseAssignments()->first();
        
        if ($currentWarehouse) {
            // Chỉ hiển thị các kho cùng tỉnh với kho hiện tại
            $warehouses = StoreLocation::where('type', 'warehouse')
                ->where('is_active', true)
                ->where('province_code', $currentWarehouse->province_code)
                ->with(['province'])
                ->orderBy('name')
                ->get();
        } else {
            // Nếu shipper chưa có kho, hiển thị tất cả kho
            $warehouses = StoreLocation::where('type', 'warehouse')
                ->where('is_active', true)
                ->with(['province'])
                ->orderBy('name')
                ->get();
        }
        
        return view('admin.shippers.edit', compact('shipper', 'provinces', 'warehouses', 'currentWarehouse'));
    }

    /**
     * Cập nhật thông tin một nhân viên.
     */
    public function update(Request $request, User $shipper)
    {
        // SỬA LỖI VALIDATION Ở ĐÂY
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Thêm ID của shipper để bỏ qua chính nó khi kiểm tra unique
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $shipper->id],
            'phone_number' => ['required', 'string', 'max:15', 'unique:users,phone_number,' . $shipper->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'status' => ['required', 'in:active,inactive,banned'],
            'province_code' => ['required', 'exists:provinces_old,code'],
            'warehouse_id' => ['required', 'exists:store_locations,id'],
        ], [
            'name.required' => 'Vui lòng nhập tên nhân viên.',
            'name.string' => 'Tên nhân viên phải là chuỗi ký tự.',
            'name.max' => 'Tên nhân viên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.unique' => 'Số điện thoại này đã được sử dụng.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_code.exists' => 'Tỉnh/thành phố không tồn tại.',
            'warehouse_id.required' => 'Vui lòng chọn kho làm việc.',
            'warehouse_id.exists' => 'Kho làm việc không tồn tại.',
        ]);

        $shipper->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $shipper->password = Hash::make($request->password);
            $shipper->save();
        }

        // Cập nhật gán kho cho shipper
        if ($request->warehouse_id) {
            // Xóa tất cả gán kho cũ
            $shipper->warehouseAssignments()->detach();
            // Gán kho mới
            $shipper->warehouseAssignments()->attach($request->warehouse_id);
        }

        // Kiểm tra nếu có warehouse_id trong request, quay lại trang warehouse detail
        if ($request->has('warehouse_id')) {
            $warehouse = StoreLocation::find($request->warehouse_id);
            return redirect()->route('admin.shippers.warehouse.show', $warehouse)->with('success', 'Cập nhật thông tin thành công.');
        }

        return redirect()->route('admin.shippers.index')->with('success', 'Cập nhật thông tin thành công.');
    }

    /**
     * Chuyển một shipper vào thùng rác (Xóa mềm).
     */
    public function destroy(User $shipper, Request $request)
    {
        if (!$shipper->hasRole('shipper')) {
            abort(404);
        }
        
        // Lưu warehouse_id trước khi xóa
        $warehouseId = $request->input('warehouse_id');
        
        $shipper->delete(); // Lệnh này sẽ tự động thực hiện xóa mềm

        // Kiểm tra nếu có warehouse_id trong request, quay lại trang warehouse detail
        if ($warehouseId) {
            $warehouse = StoreLocation::find($warehouseId);
            return redirect()->route('admin.shippers.warehouse.show', $warehouse)->with('success', 'Đã chuyển nhân viên vào thùng rác.');
        }

        // Chuyển hướng về lại trang danh sách với thông báo thành công
        return redirect()->route('admin.shippers.index')->with('success', 'Đã chuyển nhân viên vào thùng rác.');
    }

    /**
     * Hiển thị danh sách các shipper đã bị xóa mềm.
     */
    public function trash()
    {
        $shipperRole = Role::where('name', 'shipper')->firstOrFail();

        $trashedShippers = User::onlyTrashed()
            ->whereHas('roles', fn($q) => $q->where('role_id', $shipperRole->id))
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('admin.shippers.trash', compact('trashedShippers'));
    }

    /**
     * Khôi phục một shipper đã bị xóa mềm.
     */
    public function restore($id)
    {
        $shipper = User::onlyTrashed()->findOrFail($id);
        $shipper->restore();
        return redirect()->route('admin.shippers.trash')->with('success', "Đã khôi phục nhân viên '{$shipper->name}' thành công!");
    }

    /**
     * Xóa vĩnh viễn một shipper khỏi CSDL.
     */
    public function forceDelete($id)
    {
        $shipper = User::onlyTrashed()->findOrFail($id);

        // Quan trọng: Tách vai trò trước khi xóa vĩnh viễn
        $shipper->roles()->detach();
        // Có thể thêm logic xóa avatar hoặc các dữ liệu liên quan khác ở đây
        $shipper->forceDelete();

        return redirect()->route('admin.shippers.trash')->with('success', "Đã xóa vĩnh viễn nhân viên '{$shipper->name}'.");
    }

    /**
     * API: Lấy danh sách warehouses cho dropdown
     */
    public function getWarehouses()
    {
        $warehouses = StoreLocation::where('type', 'warehouse')
            ->where('is_active', true)
            ->with(['province'])
            ->select('id', 'name', 'province_code')
            ->orderBy('name')
            ->get();

        return response()->json($warehouses);
    }

}
