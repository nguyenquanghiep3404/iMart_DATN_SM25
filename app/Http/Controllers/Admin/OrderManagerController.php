<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;

class OrderManagerController extends Controller
{
    public function index(Request $request)
    {
        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();
        $warehouses = StoreLocation::with(['province', 'district'])
            ->where('type', 'warehouse')
            ->withCount(['assignedUsers as orderManagers_count' => function ($q) {
                $q->whereHas('roles', function ($qr) {
                    $qr->where('id', 5);
                });
            }])
            ->orderByDesc('id')
            ->paginate(10);
        return view('admin.oderMannager.index', compact('warehouses', 'provinces'));
    }
    public function edit(User $user)
    {
        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();
        $warehouses = StoreLocation::with('province')->where('type', 'warehouse')->orderBy('name')->get();
        
        // Lấy warehouse hiện tại của user
        $currentWarehouse = $user->assignedStoreLocations()->first();

        return view('admin.oderMannager.layouts.edit', compact('user', 'provinces', 'warehouses', 'currentWarehouse'));
    }
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc,dns',
                "unique:users,email,{$user->id}",
            ],
            'phone_number' => [
                'nullable',
                'regex:/^(0|\+84)[0-9]{9}$/',
            ],
            'province_code' => 'required|exists:provinces_old,code',
            'warehouse_id' => 'required|exists:store_locations,id',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6|confirmed',
            'password_confirmation' => 'nullable|same:password',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại trong hệ thống.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ. Định dạng đúng: 0xxxxxxxxx hoặc +84xxxxxxxx.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_code.exists' => 'Tỉnh/thành phố không hợp lệ.',
            'warehouse_id.required' => 'Vui lòng chọn kho làm việc.',
            'warehouse_id.exists' => 'Kho làm việc không hợp lệ.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password_confirmation.same' => 'Mật khẩu xác nhận không khớp.',
        ]);
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->status = $validated['status'];
        if (!empty($validated['password'])) {
            $user->password = \Hash::make($validated['password']);
        }

        $user->save();

        // Cập nhật warehouse assignment
        $user->assignedStoreLocations()->sync([$validated['warehouse_id']]);

        // Redirect về warehouse detail nếu user thuộc warehouse
        $currentWarehouse = $user->assignedStoreLocations()->first();
        $redirectRoute = $currentWarehouse 
            ? route('admin.order-manager.warehouse.show', $currentWarehouse->id)
            : route('admin.order-manager.index');
            
        return redirect($redirectRoute)->with('success', 'Cập nhật thành công!');
    }
    public function create(Request $request)
    {
        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();
        $warehouses = StoreLocation::with('province')->where('type', 'warehouse')->orderBy('name')->get();
        // Nếu có warehouse_id trong request, lọc warehouses và set selected province
        $selectedProvince = null;
        if ($request->has('warehouse_id')) {
            $warehouse = $warehouses->find($request->warehouse_id);
            if ($warehouse) {
                $selectedProvince = $warehouse->province_code;
                $warehouses = $warehouses->where('id', $request->warehouse_id);
            }
        }
        return view('admin.oderMannager.layouts.create', compact('provinces', 'warehouses', 'selectedProvince'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        // Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => ['required', 'regex:/^(0|\+84)[0-9]{9}$/', 'unique:users,phone_number'],
            'province_code' => 'required|exists:provinces_old,code',
            'warehouse_id' => 'required|exists:store_locations,id',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|same:password',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không đúng định dạng.',
            'phone_number.unique' => 'Số điện thoại đã tồn tại.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'province_code.exists' => 'Tỉnh/thành phố không hợp lệ.',
            'warehouse_id.required' => 'Vui lòng chọn kho làm việc.',
            'warehouse_id.exists' => 'Kho làm việc không hợp lệ.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu.',
            'password_confirmation.same' => 'Mật khẩu xác nhận không khớp.',
        ]);
        // Tạo user mới
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'];
        $user->status = $validated['status'];
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Gán role nhân viên quản lý đơn hàng (ví dụ role_id = 5)
        $user->roles()->attach(5);
        // Gán warehouse cho user
        $user->assignedStoreLocations()->attach($validated['warehouse_id']);
        // Redirect về trang danh sách với thông báo
        $redirectRoute = $request->has('warehouse_id')
            ? route('admin.order-manager.warehouse.show', $validated['warehouse_id'])
            : route('admin.order-manager.index');
        return redirect($redirectRoute)->with('success', 'Thêm nhân viên quản lý đơn hàng thành công!');
    }
    public function show(User $user)
    {
        // Lấy warehouse hiện tại của user
        $currentWarehouse = $user->assignedStoreLocations()->first();
        
        return view('admin.oderMannager.layouts.show', compact('user', 'currentWarehouse'));
    }
    public function showWarehouse($warehouseId, Request $request)
    {
        $warehouse = StoreLocation::with(['province', 'district'])->findOrFail($warehouseId);
        $provinces = ProvinceOld::select('code', 'name', 'name_with_type')->orderBy('name')->get();
        $query = $warehouse->assignedUsers()->whereHas('roles', function ($q) {
            $q->where('id', 5);
        });
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone_number', 'like', "%$search%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $users = $query->latest()->paginate(10);
        return view('admin.oderMannager.warehouse', compact('warehouse', 'users', 'provinces'));
    }
    public function staffIndex(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('id', 5);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone_number', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.oderMannager.index', compact('users'));
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->roles()->detach();
        $user->delete();
        return redirect()->route('admin.order-manager.index')->with('success', 'Đã xoá nhân viên thành công!');
    }
    // API: Lấy danh sách warehouses cho dropdown 
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
