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
        })->paginate(10); // Phân trang 10 bản ghi mỗi trang
        // dd($users->toArray());
        return view('admin.oderMannager.index', compact('users'));
    }
    public function edit(User $user)
    {
        return view('admin.oderMannager.layouts.edit', compact('user'));
    }
    public function update(Request $request, User $user)
    {
        // ✅ Gán kết quả validate vào biến
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc,dns',
                "unique:users,email,{$user->id}", // loại trừ user hiện tại
            ],
            'phone_number' => [
                'nullable',
                'regex:/^(0|\+84)[0-9]{9}$/',
            ],
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6|confirmed',
        ]);
    
        // Cập nhật các trường cơ bản
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->status = $validated['status'];
    
        // Nếu có mật khẩu mới, mã hóa rồi cập nhật
        if (!empty($validated['password'])) {
            $user->password = \Hash::make($validated['password']);
        }
    
        $user->save();
    
        return redirect()->route('admin.odermannager.index')->with('success', 'Cập nhật thành công!');
    }
    public function create()
    {
        return view('admin.oderMannager.layouts.create'); 
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => ['nullable', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->status = $validated['status'];
        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.odermannager.index')->with('success', 'Thêm nhân viên thành công!');
    }
    public function show(User $user)
{


    return view('admin.oderMannager.layouts.show', compact('user'));
} 
}

