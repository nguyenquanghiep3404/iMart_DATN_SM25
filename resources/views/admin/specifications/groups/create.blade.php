@extends('admin.layouts.app')

@section('title', 'Thêm mới Nhóm Thông số')

@push('styles')
    {{-- Các style này được lấy từ giao diện mẫu của bạn để đảm bảo tính nhất quán --}}
    <style>
        .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); background-color: #fff; }
        .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
        .card-custom-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .card-custom-body { padding: 1.5rem; }
        .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; line-height: 1.25rem; }
        .btn-primary { background-color: #4f46e5; color: white; } .btn-primary:hover { background-color: #4338ca; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background-color: #d1d5db; }
        .form-input, .form-select { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
        .form-input:focus, .form-select:focus { border-color: #4f46e5; outline: 0; box-shadow: 0 0 0 0.2rem rgba(79,70,229,0.25); }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
        .form-text { font-size: 0.875rem; color: #6b7280; }
        .is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; }
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
        .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
        .toast.hide { opacity: 0; transform: translateX(100%); }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-4xl">
        
        {{-- TOAST NOTIFICATIONS CONTAINER --}}
        <div id="toast-container" class="toast-container">
             @if ($errors->any())
                <div id="toast-error" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="ml-3 text-sm font-normal">Đã có lỗi xảy ra. Vui lòng kiểm tra lại.</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-error" aria-label="Close"><span class="sr-only">Close</span><i class="fas fa-times"></i></button>
                </div>
            @endif
        </div>

        {{-- PAGE HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Thêm mới Nhóm Thông số</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.specification-groups.index') }}" class="text-indigo-600 hover:text-indigo-800">Nhóm Thông số</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thêm mới</li>
                </ol>
            </nav>
        </div>

        {{-- FORM --}}
        <form action="{{ route('admin.specification-groups.store') }}" method="POST">
            @csrf
            <div class="card-custom">
                <div class="card-custom-header">
                    <h3 class="card-custom-title">Thông tin nhóm thông số</h3>
                </div>
                <div class="card-custom-body">
                    <div class="space-y-6">
                        {{-- Tên Nhóm --}}
                        <div>
                            <label for="name" class="form-label">Tên Nhóm <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ví dụ: Cấu hình, Màn hình, Pin & Sạc" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Thứ tự --}}
                        <div>
                            <label for="order" class="form-label">Thứ tự hiển thị</label>
                            <input type="number" id="order" name="order" class="form-input @error('order') is-invalid @enderror" value="{{ old('order', 0) }}" style="width: 150px;">
                            <p class="form-text mt-1">Số càng nhỏ, ưu tiên hiển thị càng cao.</p>
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-custom-footer">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.specification-groups.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Lưu lại
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // TOAST NOTIFICATION SCRIPT
        const toasts = document.querySelectorAll('.toast');
        const hideToast = (toastElement) => {
            if (toastElement) {
                toastElement.classList.add('hide');
                setTimeout(() => toastElement.remove(), 350);
            }
        };
        toasts.forEach(toast => {
            const autoHideTimeout = setTimeout(() => hideToast(toast), 5000);
            const closeButton = toast.querySelector('[data-dismiss-target]');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    clearTimeout(autoHideTimeout);
                    const targetId = this.getAttribute('data-dismiss-target');
                    hideToast(document.querySelector(targetId));
                });
            }
        });
    });
</script>
@endpush
