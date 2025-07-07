@extends('admin.layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-xl shadow">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Chỉnh sửa nhân viên</h2>

        <form method="POST" action="{{ route('admin.order-manager.update', $user->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Họ và Tên --}}
            <div>
                <label for="name" class="block mb-1 font-semibold text-gray-700">Họ và Tên <span
                        class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Nguyễn Văn A" />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block mb-1 font-semibold text-gray-700">Email <span
                        class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="example@email.com" />
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Số điện thoại --}}
            <div>
                <label for="phone_number" class="block mb-1 font-semibold text-gray-700">Số điện thoại</label>
                <input type="text" name="phone_number" id="phone_number"
                    value="{{ old('phone_number', $user->phone_number) }}"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="0987654321" />
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Trạng thái --}}
            <div>
                <label for="status" class="block mb-1 font-semibold text-gray-700">Trạng thái <span
                        class="text-red-500">*</span></label>
                <select name="status" id="status"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Đang hoạt động
                    </option>
                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Không hoạt
                        động</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mật khẩu mới --}}
            <div>
                <label for="password" class="block mb-1 font-semibold text-gray-700">Mật khẩu mới (để trống nếu không
                    đổi)</label>
                <input type="password" name="password" id="password"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="••••••••" />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div>
                <label for="password_confirmation" class="block mb-1 font-semibold text-gray-700">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="••••••••" />
            </div>

            {{-- Nút thao tác --}}
            <div class="flex items-center space-x-4">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2 rounded-md font-semibold hover:bg-indigo-700 transition">
                    Lưu
                </button>
                <a href="" class="text-gray-600 hover:underline font-semibold">
                    Hủy
                </a>
            </div>
        </form>
    </div>
@endsection
