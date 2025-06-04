<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng.
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10); // Lấy 10 user/trang
        return view('admin.users.index', compact('users'));
    }

    /**
     * Hiển thị form tạo mới người dùng.
     */
    public function create()
    {
        return view('admin.users.create');
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
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'banned'])],
        ]);

        // Password đã được tự động băm bởi $casts['password'] = 'hashed' trong Model
        // Nếu không dùng $casts, bạn cần băm thủ công:
        // $validatedData['password'] = Hash::make($validatedData['password']);

        User::create($validatedData);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
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
        return view('admin.users.edit', compact('user'));
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
        ]);

        // Chỉ cập nhật password nếu nó được cung cấp
        if (!empty($validatedData['password'])) {
            // Password đã được tự động băm bởi $casts['password'] = 'hashed' trong Model
            // Nếu không dùng $casts: $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // Bỏ qua việc cập nhật password nếu không có giá trị mới
        }

        $user->update($validatedData);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Xóa người dùng khỏi database.
     */
    public function destroy(User $user) // Route Model Binding
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
