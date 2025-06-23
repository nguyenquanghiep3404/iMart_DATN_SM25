<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoleController extends Controller
{
    use AuthorizesRequests;
    // Phân quyền
     public function __construct()
    {
        // Tự động phân quyền cho tất cả các phương thức CRUD
        $this->authorizeResource(Role::class, 'role');
    }
    public function index()
    {
        // Lấy tất cả vai trò, kèm theo đếm số lượng user cho mỗi vai trò để hiển thị
        $roles = Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('_', $permission->name)[0]; // Nhóm các quyền lại (vd: browse_users, read_users -> nhóm 'users')
        });
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255|unique:roles,name',
        'description' => 'nullable|string|max:255',
        'permissions' => 'nullable|array', // Thêm validation cho permissions
        'permissions.*' => 'exists:permissions,id' // Mỗi id trong mảng phải tồn tại trong bảng permissions
    ]);

    $role = Role::create($validated);

    // Gán các quyền được chọn cho vai trò vừa tạo bằng phương thức sync()
    if ($request->has('permissions')) {
        $this->authorize('assignPermissions', $role);
        $role->permissions()->sync($request->input('permissions', []));
    }
    return redirect()->route('admin.roles.index')->with('success', 'Tạo vai trò mới thành công!');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('_', $permission->name)[0];
        });
        $role->load('permissions'); // Tải trước các permission của role này để tối ưu
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
        'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
        'description' => 'nullable|string|max:255',
        'permissions' => 'nullable|array', // Thêm validation cho permissions
        'permissions.*' => 'exists:permissions,id'
    ]);

    $role->update($validated);

    if ($request->has('permissions')) {
        // Gọi phương thức mới trong RolePolicy
        $this->authorize('assignPermissions', $role);

        // Nếu được phép, tiến hành đồng bộ permissions
        $role->permissions()->sync($request->input('permissions', []));
    }

    return redirect()->route('admin.roles.index')->with('success', 'Cập nhật vai trò thành công!');
    }

    public function destroy(Role $role)
    {
        // (Tùy chọn) Thêm logic kiểm tra nếu vai trò này không được phép xóa (ví dụ: 'admin')
        if (in_array($role->name, ['admin', 'customer'])) {
            return back()->with('error', 'Không thể xóa các vai trò mặc định của hệ thống.');
        }

        // Xóa vai trò
        $role->delete();

        return back()->with('success', 'Đã xóa vai trò thành công!');
    }
}
