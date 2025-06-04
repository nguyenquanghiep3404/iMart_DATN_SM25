@extends('admin.layouts.app')

@section('title', 'Thêm Người dùng mới')

@push('styles')
{{-- Các style đã có từ trang "Thêm Thuộc tính mới" --}}
<style>
    .body-content { background-color: #f8f9fa; } /* Thêm màu nền chung nếu cần */
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
        color: #374151; /* text-gray-700 */
    }
    .form-input, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem; /* rounded-lg */
        border: 1px solid #d1d5db; /* border-gray-300 */
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        font-size: 0.875rem; /* text-sm */
        line-height: 1.25rem;
    }
    .form-input:focus, .form-select:focus {
        border-color: #4f46e5; /* focus:border-indigo-500 */
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25); /* focus:ring-indigo-200 focus:ring-opacity-50 */
    }
    .btn {
        border-radius: 0.5rem; /* rounded-lg */
        transition: all 0.2s ease-in-out;
        font-weight: 500; /* font-medium */
        padding: 0.625rem 1.25rem; /* py-2.5 px-5 */
        font-size: 0.875rem; /* text-sm */
        line-height: 1.25rem;
        display: inline-flex; /* Để icon và text căn giữa */
        align-items: center;
        justify-content: center;
    }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; } /* text-gray-700, bg-gray-200 */
    .btn-secondary:hover { background-color: #d1d5db; } /* hover:bg-gray-300 */

    .form-input.is-invalid, .form-select.is-invalid {
        border-color: #ef4444; /* border-red-500 */
    }
    .invalid-feedback {
        color: #ef4444; /* text-red-500 */
        font-size: 0.875rem; /* text-sm */
        margin-top: 0.25rem; /* mt-1 */
    }
    /* Toast Notification Styles (Nếu cần hiển thị toast trên trang này) */
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
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Thêm Người dùng mới</h1>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800">Người dùng</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700" aria-current="page">Thêm mới</li>
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

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="p-6 space-y-6">
                            {{-- Tên người dùng --}}
                            <div>
                                <label for="user_name" class="form-label">Họ và Tên <span class="text-red-500">*</span></label>
                                <input type="text" id="user_name" name="name"
                                       class="form-input @error('name') is-invalid @enderror"
                                       placeholder="Ví dụ: Nguyễn Văn A"
                                       value="{{ old('name') }}" >
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
                                       value="{{ old('email') }}" >
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
                                       value="{{ old('phone_number') }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Mật khẩu --}}
                            <div>
                                <label for="user_password" class="form-label">Mật khẩu <span class="text-red-500">*</span></label>
                                <input type="password" id="user_password" name="password"
                                       class="form-input @error('password') is-invalid @enderror"
                                       placeholder="Ít nhất 8 ký tự">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Xác nhận Mật khẩu --}}
                            <div>
                                <label for="user_password_confirmation" class="form-label">Xác nhận Mật khẩu <span class="text-red-500">*</span></label>
                                <input type="password" id="user_password_confirmation" name="password_confirmation"
                                       class="form-input"
                                       placeholder="Nhập lại mật khẩu" >
                                {{-- Lỗi cho password_confirmation thường được gộp chung với lỗi 'password' nếu có 'confirmed' rule --}}
                            </div>

                            {{-- Trạng thái --}}
                            <div>
                                <label for="user_status" class="form-label">Trạng thái <span class="text-red-500">*</span></label>
                                <select id="user_status" name="status"
                                        class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Kích hoạt (Active)</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không kích hoạt (Inactive)</option>
                                    <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Bị cấm (Banned)</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Cân nhắc thêm các trường khác nếu cần, ví dụ: Vai trò (Roles) --}}
                            {{--
                            <div>
                                <label for="user_roles" class="form-label">Vai trò</label>
                                <select id="user_roles" name="roles[]" class="form-select" multiple>
                                    @foreach($roles as $role) // Giả sử có biến $roles từ controller
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Giữ Ctrl (hoặc Cmd trên Mac) để chọn nhiều vai trò.</p>
                            </div>
                            --}}

                        </div>

                        {{-- Actions Button --}}
                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg border-t border-gray-200">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Lưu Người dùng
                            </button>
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
    // Script Toast từ các ví dụ trước (nếu bạn muốn hiển thị toast sau khi redirect)
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
