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
        return view('admin.shippers.create');
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
        return view('admin.shippers.edit', compact('shipper'));
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

        return redirect()->route('admin.shippers.index')->with('success', 'Cập nhật thông tin thành công.');
    }

    /**
     * Chuyển một shipper vào thùng rác (Xóa mềm).
     */
    public function destroy(User $shipper)
    {
        if (!$shipper->hasRole('shipper')) {
            abort(404);
        }
        $shipper->delete(); // Lệnh này sẽ tự động thực hiện xóa mềm

        // Chuyển hướng về lại trang danh sách với thông báo thành công
        // Nếu bạn muốn chuyển thẳng đến trang thùng rác, hãy đổi 'admin.shippers.index' thành 'admin.shippers.trash'
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

        return view('admin.users.trash', compact('trashedShippers'));
    }

    /**
     * Khôi phục một shipper đã bị xóa mềm.
     */
    public function restore($id)
    {
        $shipper = User::onlyTrashed()->findOrFail($id);
        $shipper->restore();
        return redirect()->route('admin.users.trash')->with('success', "Đã khôi phục nhân viên '{$shipper->name}' thành công!");
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

        return redirect()->route('admin.users.trash')->with('success', "Đã xóa vĩnh viễn nhân viên '{$shipper->name}'.");
    }
}
