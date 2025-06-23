<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Gate;
use App\Models\Role;
use App\Rules\ExclusiveRole;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    use AuthorizesRequests;
    // Phân quyền
    public function __construct()
    {
        // Tự động phân quyền cho tất cả các phương thức CRUD
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Hiển thị danh sách người dùng.
     */
    public function index(Request $request)
    {
        // 1. Bắt đầu một truy vấn Eloquent, chưa thực thi
         $query = User::with('roles')->orderBy('created_at', 'desc');

        // 2. Kiểm tra xem có từ khóa tìm kiếm được gửi lên không
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');

            // 3. Thêm điều kiện `WHERE` vào truy vấn
            // Tìm kiếm trong các cột 'name', 'email', và 'phone_number'
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'LIKE', "%{$searchTerm}%");
            });
        }
        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        // 5. Trả về view với dữ liệu đã được lọc
        return view('admin.users.index', compact('users'));
    }

    /**
     * Hiển thị form tạo mới người dùng.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Lưu người dùng mới vào database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' yêu cầu có trường password_confirmation
            'phone_number' => 'nullable|string|max:255|unique:users,phone_number',
            // 'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'banned'])],
            'roles' => ['required', 'array', new ExclusiveRole], // <-- SỬ DỤNG RULE MỚI
            'roles.*' => 'exists:roles,id'

        ]);
        $validatedData['status'] = 'inactive'; // Gán trạng thái chưa hoạt động từ đầu



        // Password đã được tự động băm bởi $casts['password'] = 'hashed' trong Model
        // Nếu không dùng $casts, bạn cần băm thủ công:
        // $validatedData['password'] = Hash::make($validatedData['password']);

         // TẠO USER VỚI DỮ LIỆU ĐÃ LOẠI BỎ 'roles'
        $user = User::create(Arr::except($validatedData, ['roles']));

        // Gán vai trò sau khi đã tạo user
        $user->roles()->sync($request->input('roles'));
        // Gửi email xác thực
        // dd($validatedData);die;
        $user->sendEmailVerificationNotification();

        return redirect()->route('admin.users.index')->with('success', 'Thêm mới người dùng thành công!');
    }

    /**
     * Hiển thị thông tin chi tiết của một người dùng.
     */
    public function show(User $user) // Route Model Binding
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa thông tin người dùng.
     */
    public function edit(User $user) // Route Model Binding
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user' , 'roles'));
    }

    /**
     * Cập nhật thông tin người dùng trong database.
     */
    public function update(Request $request, User $user) // Route Model Binding
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, // unique ngoại trừ user hiện tại
            'password' => 'nullable|string|min:8|confirmed', // Cho phép thay đổi mật khẩu, nếu không nhập thì không đổi
            'phone_number' => 'nullable|string|max:255|unique:users,phone_number,' . $user->id,
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'banned'])],
            'roles' => ['required', 'array', new ExclusiveRole], // <-- SỬ DỤNG RULE MỚI
            'roles.*' => 'exists:roles,id'
        ]);
        $updateData = Arr::except($validatedData, ['roles', 'password', 'password_confirmation']);


        // Chỉ cập nhật password nếu nó được cung cấp
        if (!empty($validatedData['password'])) {
            // Password đã được tự động băm bởi $casts['password'] = 'hashed' trong Model
            // Nếu không dùng $casts: $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // Bỏ qua việc cập nhật password nếu không có giá trị mới
        }

        // Cập nhật thông tin user
    $user->update($updateData);

    // Cập nhật vai trò
    $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')->with('success', 'Người dùng đã được cập nhật!');
    }

    /**
     * Xóa người dùng khỏi database.
     */
    public function destroy(User $user) // Route Model Binding
    {
    //     if (Gate::denies('is-admin')) {
    //     abort(403, 'BẠN KHÔNG CÓ QUYỀN THỰC HIỆN HÀNH ĐỘNG NÀY.');
    // }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Người dùng đã bị xóa!');
    }


}
