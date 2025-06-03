@extends('admin.layouts.app')

@section('title', 'Thêm Thuộc tính mới')

{{-- 
  Các style này dành riêng cho trang thêm/sửa, 
  đưa vào đây bằng @push để không ảnh hưởng các trang khác.
--}}
@push('styles')
<style>
    .card-form {
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background-color: #fff;
    }
    .card-form-header {
        background-color: #4f46e5;
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
    }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; }
    .btn-secondary:hover { background-color: #d1d5db; }
    .form-input.is-invalid, .form-select.is-invalid {
        border-color: #ef4444; /* red-500 */
    }
    .invalid-feedback {
        color: #ef4444; /* red-500 */
        font-size: 0.875rem; /* text-sm */
        margin-top: 0.25rem;
    }
</style>
@endpush


@section('content')
<div class="body-content px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-7xl">
        {{-- Breadcrumbs --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Thêm thuộc tính mới</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    {{-- <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li> --}}
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.attributes.index') }}" class="text-indigo-600 hover:text-indigo-800">Thuộc tính</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
                </ol>
            </nav>
        </div>

        {{-- Form Card --}}
        <div class="flex justify-center">
            <div class="w-full lg:w-3/4 xl:w-2/3">
                <div class="card-form">
                    <div class="card-form-header">
                        <h3 class="card-form-title">Thông tin thuộc tính</h3>
                    </div>

                    {{-- Form trỏ đến route store --}}
                    <form method="POST" action="{{ route('admin.attributes.store') }}">
                        @csrf {{-- Chống tấn công CSRF --}}
                        
                        <div class="p-6 space-y-6">
                            {{-- Tên thuộc tính --}}
                            <div>
                                <label for="attribute_name" class="form-label">Tên thuộc tính <span class="text-red-500">*</span></label>
                                <input type="text" id="attribute_name" name="name" 
                                       class="form-input @error('name') is-invalid @enderror" 
                                       placeholder="Ví dụ: Màu sắc, Kích thước" 
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Slug --}}
                            <div>
                                <label for="attribute_slug" class="form-label">Slug (Đường dẫn thân thiện)</label>
                                <input type="text" id="attribute_slug" name="slug" 
                                       class="form-input @error('slug') is-invalid @enderror" 
                                       placeholder="Để trống sẽ tự động tạo từ tên" 
                                       value="{{ old('slug') }}">
                                <p class="text-xs text-gray-500 mt-1">Chỉ chứa chữ cái, số và dấu gạch ngang.</p>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Kiểu hiển thị --}}
                            <div>
                                <label for="display_type" class="form-label">Kiểu hiển thị <span class="text-red-500">*</span></label>
                                <select id="display_type" name="display_type" 
                                        class="form-select @error('display_type') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('display_type') ? '' : 'selected' }}>-- Chọn kiểu hiển thị --</option>
                                    {{-- Controller của bạn chỉ có 3 kiểu này, nếu thêm cần cập nhật cả ở controller --}}
                                    <option value="select" {{ old('display_type') == 'select' ? 'selected' : '' }}>Select Box</option>
                                    <option value="radio" {{ old('display_type') == 'radio' ? 'selected' : '' }}>Radio Button</option>
                                    <option value="color_swatch" {{ old('display_type') == 'color_swatch' ? 'selected' : '' }}>Color Swatch (Mẫu màu)</option>
                                </select>
                                @error('display_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Mô tả (không bắt buộc) --}}
                            {{-- <div>
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea id="description" name="description" 
                                          class="form-input h-24 resize-y" 
                                          placeholder="Mô tả ngắn về thuộc tính này...">{{ old('description') }}</textarea>
                            </div> --}}
                        </div>
                        
                        {{-- Actions Button --}}
                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Hủy bỏ
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Lưu thuộc tính
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection