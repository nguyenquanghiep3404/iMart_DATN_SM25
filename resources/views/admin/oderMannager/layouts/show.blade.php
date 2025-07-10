@extends('admin.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto p-8 bg-white rounded-xl shadow-md space-y-10 mt-5">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-circle text-indigo-600 text-3xl"></i>
                Chi tiết nhân viên
            </h1>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.order-manager.index') }}"
                    class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                </a>
                <a href="{{ route('admin.order-manager.edit', $user->id) }}"
                    class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    <i class="fas fa-edit mr-1"></i> Chỉnh sửa
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Cột 1 --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Thông tin cơ bản</h2>
                <div class="space-y-2 text-gray-700">
                    <p><i class="fas fa-user mr-2 text-indigo-500"></i><strong>Họ tên:</strong> {{ $user->name }}</p>
                    <p><i class="fas fa-envelope mr-2 text-indigo-500"></i><strong>Email:</strong> {{ $user->email }}</p>
                    <p><i class="fas fa-phone-alt mr-2 text-indigo-500"></i><strong>SĐT:</strong>
                        {{ $user->phone_number ?? '—' }}</p>
                    <p>
                        <i class="fas fa-circle mr-2 text-indigo-500"></i><strong>Trạng thái:</strong>
                        @if ($user->status === 'active')
                            <span class="text-green-600 font-semibold">Đang hoạt động</span>
                        @else
                            <span class="text-red-600 font-semibold">Không hoạt động</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Cột 2 --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Thông tin hệ thống</h2>
                <div class="space-y-2 text-gray-700">
                    <p><i class="fas fa-calendar-plus mr-2 text-indigo-500"></i><strong>Ngày tạo:</strong>
                        {{ $user->created_at->format('d/m/Y H:i') }}</p>
                    <p><i class="fas fa-check-circle mr-2 text-indigo-500"></i><strong>Email xác minh:</strong>
                        {{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Chưa xác minh' }}</p>
                    <p><i class="fas fa-clock mr-2 text-indigo-500"></i><strong>Đăng nhập cuối:</strong>
                        {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') : 'Chưa đăng nhập' }}
                    </p>
                    <p><i class="fas fa-history mr-2 text-indigo-500"></i><strong>Cập nhật gần nhất:</strong>
                        {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
