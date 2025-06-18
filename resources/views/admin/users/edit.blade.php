@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa Người dùng: ' . $user->name)

@push('styles')
{{-- Sử dụng lại toàn bộ style từ trang create/edit attribute --}}
<style>
    .body-content { background-color: #f8f9fa; }
    .card-form {
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: #fff;
    }
    .card-form-header {
        background-color: #4f46e5; /* Màu primary */
        color: white;
        padding: 1rem 1.5rem;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        border-bottom: 1px solid #4338ca;
    }
    .card-form-title {
        font-size: 1.25rem;
        font-weight: 600;
    }
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }
    .form-input, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        font-size: 0.875rem;
    }
    .form-input:focus, .form-select:focus {
        border-color: #4f46e5;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }
    .btn {
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; }
    .btn-secondary:hover { background-color: #d1d5db; }
    .form-input.is-invalid, .form-select.is-invalid {
        border-color: #ef4444;
    }
    .invalid-feedback {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    /* Toast Notification Styles */
    .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
    .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
    .toast.hide { opacity: 0; transform: translateX(100%); }
</style>
@endpush


@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-7xl">
        {{-- Breadcrumbs --}}
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Chỉnh sửa Người dùng</h1>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800">Người dùng</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700" aria-current="page">Chỉnh sửa: {{ $user->name }}</li>
                </ol>
            </nav>
        </div>

        {{-- Form Card --}}
        <div class="flex justify-center">
            <div class="w-full lg:w-3/4 xl:w-2/3">
                <div class="card-form">
                    <div class="card-form-header">
                        <h3 class="card-form-title">Thông tin người dùng</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PUT') {{-- Bắt buộc cho request UPDATE --}}

                        <div class="p-6 space-y-6">
                            {{-- Tên người dùng --}}
                            <div>
                                <label for="user_name" class="form-label">Họ và Tên <span class="text-red-500">*</span></label>
                                <input type="text" id="user_name" name="name"
                                       class="form-input @error('name') is-invalid @enderror"
                                       placeholder="Ví dụ: Nguyễn Văn A"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="user_email" class="form-label">Địa chỉ Email <span class="text-red-500">*</span></label>
                                <input type="email" id="user_email" name="email"
                                       class="form-input @error('email') is-invalid @enderror"
                                       placeholder="Ví dụ: example@email.com"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Số điện thoại --}}
                            <div>
                                <label for="user_phone_number" class="form-label">Số điện thoại</label>
                                <input type="tel" id="user_phone_number" name="phone_number"
                                       class="form-input @error('phone_number') is-invalid @enderror"
                                       placeholder="Ví dụ: 0987654321"
                                       value="{{ old('phone_number', $user->phone_number) }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4"> {{-- Phân tách thông tin cá nhân và mật khẩu --}}

                            {{-- Mật khẩu (Tùy chọn) --}}
                            <div>
                                <label for="user_password" class="form-label">Mật khẩu mới (Để trống nếu không muốn thay đổi)</label>
                                <input type="password" id="user_password" name="password"
                                       class="form-input @error('password') is-invalid @enderror"
                                       placeholder="Ít nhất 8 ký tự">
                                <p class="text-xs text-gray-500 mt-1">Nếu bạn muốn thay đổi mật khẩu, hãy nhập mật khẩu mới ở đây. Nếu không, bỏ trống trường này.</p>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Xác nhận Mật khẩu mới --}}
                            <div>
                                <label for="user_password_confirmation" class="form-label">Xác nhận Mật khẩu mới</label>
                                <input type="password" id="user_password_confirmation" name="password_confirmation"
                                       class="form-input"
                                       placeholder="Nhập lại mật khẩu mới nếu có">
                            </div>

                            <hr class="my-4">

                            {{-- Trạng thái --}}
                            <div>
                                <label for="user_status" class="form-label">Trạng thái <span class="text-red-500">*</span></label>
                                <select id="user_status" name="status"
                                        class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Kích hoạt (Active)</option>
                                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Không kích hoạt (Inactive)</option>
                                    <option value="banned" {{ old('status', $user->status) == 'banned' ? 'selected' : '' }}>Bị cấm (Banned)</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Thông tin không thể chỉnh sửa trực tiếp (ví dụ) --}}
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h4 class="text-md font-semibold text-gray-600 mb-2">Thông tin khác</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Ngày đăng ký:</span>
                                        <span class="text-gray-800 ml-1">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Email xác thực lúc:</span>
                                        <span class="text-gray-800 ml-1">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Chưa xác thực' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Đăng nhập cuối:</span>
                                        <span class="text-gray-800 ml-1">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Actions Button --}}
                        <div class="bg-gray-50 px-6 py-4 flex justify-between items-center rounded-b-lg border-t border-gray-200">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
                                <i class="fas fa-eye mr-1"></i> Xem chi tiết
                            </a>
                            <div class="space-x-3">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-2"></i> Hủy bỏ
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i> Cập nhật Người dùng
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script cho Toast (nếu cần sau khi submit form) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Script Toast từ các ví dụ trước
    const toasts = document.querySelectorAll('.toast');
    const hideToast = (toastElement) => {
        if (toastElement) {
            toastElement.classList.add('hide');
            setTimeout(() => {
                if(toastElement.parentNode) {
                    toastElement.remove();
                }
            }, 350);
        }
    };
    toasts.forEach(toast => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
        const autoHideTimeout = setTimeout(() => { hideToast(toast); }, 5000);
        const closeButton = toast.querySelector('[data-dismiss-target]');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                clearTimeout(autoHideTimeout);
                const targetSelector = this.getAttribute('data-dismiss-target');
                const toastToHide = document.querySelector(targetSelector);
                hideToast(toastToHide);
            });
        } else {
             toast.addEventListener('click', () => hideToast(toast));
        }
    });
});
</script>
@endpush
