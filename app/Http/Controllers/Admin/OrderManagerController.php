<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class OrderManagerController extends Controller
{
    public function index()
    {
        $users = User::whereIn('id', function ($query) {
            $query->select('user_id')
                  ->from('role_user')
                  ->where('role_id', 5);
        })->paginate(10);
        return view('admin.oderMannager.index', compact('users'));
    }
    public function edit(User $user)
    {
        return view('admin.oderMannager.layouts.edit', compact('user'));
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
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại trong hệ thống.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ. Định dạng đúng: 0xxxxxxxxx hoặc +84xxxxxxxx.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);
        
    
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->status = $validated['status'];
        if (!empty($validated['password'])) {
            $user->password = \Hash::make($validated['password']);
        }
    
        $user->save();
    
        return redirect()->route('admin.order-manager.index')->with('success', 'Cập nhật thành công!');
    }
    public function create()
    {
        return view('admin.oderMannager.layouts.create'); 
    }
    public function store(Request $request)
    {
        dd($request->all());
        // Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => ['required', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:6|confirmed',  // password_confirmation phải khớp
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
        // Redirect về trang danh sách với thông báo
        return redirect()->route('admin.order-manager.index')->with('success', 'Thêm nhân viên quản lý đơn hàng thành công!');
    }
    public function show(User $user)
    {
        return view('admin.oderMannager.layouts.show', compact('user'));
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
}

