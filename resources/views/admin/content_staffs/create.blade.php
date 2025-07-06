@extends('admin.layouts.app')

@section('title', 'Thêm Nhân viên Content')

@section('content')
<div class="max-w-screen-md mx-auto p-4 md:p-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Thêm nhân viên Content</h1>
        <p class="text-gray-500 mt-1">Điền thông tin chi tiết cho biên tập viên nội dung.</p>
    </header>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-8 rounded-xl shadow-sm">
        <form action="{{ route('admin.content-staffs.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Họ và Tên <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mật khẩu <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" class="w-full py-2 px-3 border border-gray-300 rounded-lg pr-10">
                            <span onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-gray-500 hover:text-indigo-600">
                                <i class="fas fa-eye" id="toggle-password-icon"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Xác nhận mật khẩu <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full py-2 px-3 border border-gray-300 rounded-lg pr-10">
                            <span onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-gray-500 hover:text-indigo-600">
                                <i class="fas fa-eye" id="toggle-password_confirmation-icon"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Trạng thái
                    </label>
                    <select name="status" id="status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg">
                        <option value="active" @selected(old('status') == 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status') == 'inactive')>Không hoạt động</option>
                        <option value="banned" @selected(old('status') == 'banned')>Đã khóa</option>
                    </select>
                </div>
            </div>

            <div class="pt-8 flex justify-end space-x-3">
                <a href="{{ route('admin.content-staffs.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Hủy</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold">Tạo nhân viên</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = document.getElementById('toggle-' + id + '-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
