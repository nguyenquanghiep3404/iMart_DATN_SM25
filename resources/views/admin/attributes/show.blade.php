@extends('admin.layouts.app')

@section('title', 'Chi tiết Thuộc tính: ' . $attribute->name)

@push('styles')
    {{-- Các style bạn đã cung cấp --}}
    <style>
        .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background-color: #fff; margin-bottom: 1.5rem; }
        .card-custom-header { color: white; padding: 1rem 1.5rem; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
        .card-custom-header-primary { background-color: #4f46e5; border-bottom: 1px solid #4338ca; }
        .card-custom-header-success { background-color: #10b981; border-bottom: 1px solid #059669; }
        .card-custom-header-info { background-color: #3b82f6; border-bottom: 1px solid #2563eb; }
        .card-custom-title { font-size: 1.125rem; font-weight: 600; }
        .card-custom-tools a { color: rgba(255, 255, 255, 0.8); }
        .card-custom-tools a:hover { color: white; }
        .card-custom-body { padding: 1.5rem; }
        .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; border-top: 1px solid #e5e7eb; }
        .dl-custom dt { font-weight: 600; color: #374151; }
        .dl-custom dd { color: #4b5563; margin-bottom: 0.5rem; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; }
        .form-input, .form-select, .form-control-sm { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; font-size: 0.875rem; }
        .form-control-sm { padding: 0.375rem 0.75rem; }
        .form-input:focus, .form-select:focus, .form-control-sm:focus { border-color: #4f46e5; outline: 0; box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25); }
        .form-input.is-invalid, .form-select.is-invalid, .form-control-sm.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; }
        .input-group { display: flex; align-items: stretch; width: 100%; }
        .input-group .form-input, .input-group .form-control-sm { border-top-right-radius: 0; border-bottom-right-radius: 0; flex-grow: 1; }
        .input-group-append { display: flex; }
        .input-group-text { display: flex; align-items: center; padding: 0.625rem 0.75rem; margin-bottom: 0; font-size: 0.875rem; font-weight: 400; line-height: 1.5; color: #4b5563; text-align: center; white-space: nowrap; background-color: #e9ecef; border: 1px solid #d1d5db; border-left: 0; border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
        .input-group-sm .input-group-text { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        .btn-primary { background-color: #4f46e5; color: white; }
        .btn-primary:hover { background-color: #4338ca; }
        .btn-success { background-color: #10b981; color: white; }
        .btn-success:hover { background-color: #059669; }
        .btn-danger { background-color: #ef4444; color: white; }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; }
        .btn-secondary:hover { background-color: #d1d5db; }
        .table-custom { width: 100%; margin-bottom: 1rem; color: #212529; border-collapse: collapse; }
        .table-custom th, .table-custom td { padding: 0.75rem; vertical-align: middle; border-top: 1px solid #e5e7eb; }
        .table-custom thead th { vertical-align: bottom; border-bottom: 2px solid #e5e7eb; background-color: #f9fafb; font-weight: 600; text-align: left; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0, 0, 0, .03); }
        .attribute-value-modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.6); }
        .attribute-value-modal.show { display: flex; align-items: center; justify-content: center; }
        .attribute-value-modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 500px; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .attribute-value-modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .attribute-value-modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .attribute-value-close { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
        .attribute-value-close:hover { opacity: 1; color: #1f2937; }
        .attribute-value-modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
        .attribute-value-modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .attribute-value-modal-footer > :not(:first-child) { margin-left: .5rem; }

        /* === CSS CHO TOAST NOTIFICATION === */
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1100; /* Cao hơn modal */
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .toast {
            opacity: 1;
            transform: translateX(0);
            transition: all 0.3s ease-in-out;
        }
        .toast.hide {
            opacity: 0;
            transform: translateX(100%);
        }
    </style>
    {{-- CSS cho color picker --}}
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.4.0/css/bootstrap-colorpicker.min.css"
        integrity="sha512-mHKEeDrcVkMMIeHdXKCpHqJK7sdVKTNllKJBQ4vNKhQGagBGaccEngBe2AcBe53RUEADKqrMvBwdGuY4QzXq6g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
    <div class="body-content px-4 sm:px-6 md:px-8 py-8">

        {{-- Hiển thị thông báo (flash message) dưới dạng TOAST ở góc trên bên phải --}}
        <div id="toast-container" class="toast-container">
            @if (session('success_value'))
                <div id="toast-success" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg dark:text-gray-400 dark:bg-gray-800" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="ml-3 text-sm font-normal">{{ session('success_value') }}</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-success" aria-label="Close">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if (session('error_value'))
                <div id="toast-error" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg dark:text-gray-400 dark:bg-gray-800" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                         <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ml-3 text-sm font-normal">{{ session('error_value') }}</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-error" aria-label="Close">
                        <span class="sr-only">Close</span>
                         <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>

        <div class="container mx-auto max-w-7xl">

            {{-- Breadcrumbs --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Thuộc tính: {{ $attribute->name }}</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        {{-- <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li> --}}
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.attributes.index') }}"
                                class="text-indigo-600 hover:text-indigo-800">Thuộc tính</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">
                            {{ $attribute->name }}</li>
                    </ol>
                </nav>
            </div>

            {{-- Nội dung chính của trang --}}
            <div class="flex flex-col lg:flex-row lg:space-x-6">
                {{-- Left Column --}}
                <div class="w-full lg:w-5/12 space-y-6">
                    {{-- Attribute Info Card --}}
                    <div class="card-custom">
                        <div class="card-custom-header card-custom-header-primary">
                            <h3 class="card-custom-title">Thông tin Thuộc tính</h3>
                            <div class="card-custom-tools">
                                <a href="{{ route('admin.attributes.edit', $attribute->id) }}"
                                    class="p-1 hover:bg-white/20 rounded" title="Chỉnh sửa thuộc tính này">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-custom-body">
                            <dl class="dl-custom grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2">
                                <dt class="sm:col-span-1">ID</dt>
                                <dd class="sm:col-span-2">{{ $attribute->id }}</dd>
                                <dt class="sm:col-span-1">Tên</dt>
                                <dd class="sm:col-span-2">{{ $attribute->name }}</dd>
                                <dt class="sm:col-span-1">Slug</dt>
                                <dd class="sm:col-span-2">{{ $attribute->slug }}</dd>
                                <dt class="sm:col-span-1">Kiểu hiển thị</dt>
                                <dd class="sm:col-span-2">
                                    {{ Str::ucfirst(str_replace('_', ' ', $attribute->display_type)) }}</dd>
                                <dt class="sm:col-span-1">Ngày tạo</dt>
                                <dd class="sm:col-span-2">{{ $attribute->created_at->format('d/m/Y H:i:s') }}</dd>
                                <dt class="sm:col-span-1">Cập nhật</dt>
                                <dd class="sm:col-span-2">{{ $attribute->updated_at->format('d/m/Y H:i:s') }}</dd>
                            </dl>
                        </div>
                    </div>

                    {{-- Add New Value Card --}}
                    <div class="card-custom">
                        <div class="card-custom-header card-custom-header-success">
                            <h3 class="card-custom-title">Thêm giá trị mới cho "{{ $attribute->name }}"</h3>
                        </div>
                        <form method="POST" action="{{ route('admin.attributes.values.store', $attribute->id) }}">
                            @csrf
                            <div class="card-custom-body space-y-4">
                                <div>
                                    <label for="value_name_add" class="form-label">Tên giá trị <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="value_name_add" name="value"
                                        class="form-input @error('value') is-invalid @enderror"
                                        placeholder="Ví dụ: Đỏ, Xanh lá" value="{{ old('value') }}" required>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    @if ($attribute->display_type === 'color_swatch')
                                        <label for="value_meta_color_add" class="form-label">Mã màu (Meta)</label>
                                        <div class="input-group colorpicker-group-add">
                                            <input type="text" class="form-input @error('meta') is-invalid @enderror"
                                                id="value_meta_color_add" name="meta" placeholder="Ví dụ: #FF0000"
                                                value="{{ old('meta') }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-square"
                                                        style="color: {{ old('meta', '#FFFFFF') }};"></i></span>
                                            </div>
                                        </div>
                                        <small class="text-xs text-gray-500 mt-1">Nhập mã màu HEX (ví dụ: #FF0000).</small>
                                    @else
                                        <label for="value_meta_other_add" class="form-label">Thông tin Meta (Tùy
                                            chọn)</label>
                                        <input type="text" class="form-input @error('meta') is-invalid @enderror"
                                            id="value_meta_other_add" name="meta" placeholder="Thông tin bổ sung nếu cần"
                                            value="{{ old('meta') }}">
                                    @endif
                                    @error('meta')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-custom-footer text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus mr-1"></i> Thêm giá trị
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="w-full lg:w-7/12 mt-6 lg:mt-0">
                    <div class="card-custom">
                        <div class="card-custom-header card-custom-header-info">
                            <h3 class="card-custom-title">Các giá trị của thuộc tính "{{ $attribute->name }}"
                                ({{ $attribute->attributeValues->count() }})</h3>
                        </div>
                        <div class="card-custom-body p-0">
                            <div class="overflow-x-auto">
                                @if ($attribute->attributeValues->isEmpty())
                                    <p class="text-center p-5 text-gray-500">Thuộc tính này chưa có giá trị nào.</p>
                                @else
                                    <table class="table-custom table-striped">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px">ID</th>
                                                <th>Tên giá trị</th>
                                                <th>{{ $attribute->display_type === 'color_swatch' ? 'Màu (Meta)' : 'Meta' }}
                                                </th>
                                                <th style="width: 130px" class="text-center">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($attribute->attributeValues as $value)
                                                <tr>
                                                    <td>{{ $value->id }}</td>
                                                    {{-- Inline Form for Editing Attribute Value --}}
                                                    <form
                                                        action="{{ route('admin.attributes.values.update', [$attribute->id, $value->id]) }}"
                                                        method="POST" id="formEditValue{{ $value->id }}"
                                                        class="contents"> {{-- 'contents' class to make form not break table layout --}}
                                                        @csrf
                                                        @method('PUT')
                                                        <td>
                                                            <input type="text"
                                                                name="edit_value_name_{{ $value->id }}"
                                                                class="form-control-sm @error('edit_value_name_' . $value->id) is-invalid @enderror"
                                                                value="{{ old('edit_value_name_' . $value->id, $value->value) }}"
                                                                required>
                                                            @error('edit_value_name_' . $value->id)
                                                                <span
                                                                    class="invalid-feedback block">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            @if ($attribute->display_type === 'color_swatch')
                                                                <div
                                                                    class="input-group input-group-sm colorpicker-group-edit-{{ $value->id }}">
                                                                    <input type="text"
                                                                        name="edit_value_meta_{{ $value->id }}"
                                                                        class="form-control-sm @error('edit_value_meta_' . $value->id) is-invalid @enderror"
                                                                        value="{{ old('edit_value_meta_' . $value->id, $value->meta) }}"
                                                                        placeholder="#FF0000">
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text"><i
                                                                                class="fas fa-square"
                                                                                style="color: {{ old('edit_value_meta_' . $value->id, $value->meta ?? '#FFFFFF') }};"></i></span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <input type="text"
                                                                    name="edit_value_meta_{{ $value->id }}"
                                                                    class="form-control-sm @error('edit_value_meta_' . $value->id) is-invalid @enderror"
                                                                    value="{{ old('edit_value_meta_' . $value->id, $value->meta) }}">
                                                            @endif
                                                            @error('edit_value_meta_' . $value->id)
                                                                <span
                                                                    class="invalid-feedback block">{{ $message }}</span>
                                                            @enderror
                                                        </td>
                                                    </form>
                                                    <td class="text-center">
                                                        <div class="inline-flex space-x-1">
                                                            <button type="submit"
                                                                form="formEditValue{{ $value->id }}"
                                                                class="btn btn-sm btn-primary" title="Lưu thay đổi">
                                                                <i class="fas fa-save"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                title="Xóa giá trị"
                                                                onclick="openAttributeValueModal('deleteValueModal{{ $value->id }}')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                {{-- Delete Modal for each value --}}
                                                <div id="deleteValueModal{{ $value->id }}"
                                                    class="attribute-value-modal" tabindex="-1" role="dialog">
                                                    <div class="attribute-value-modal-content">
                                                        <div class="attribute-value-modal-header">
                                                            <h5 class="attribute-value-modal-title">Xác nhận xóa Giá trị
                                                            </h5>
                                                            <button type="button" class="attribute-value-close"
                                                                onclick="closeAttributeValueModal('deleteValueModal{{ $value->id }}')"
                                                                aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="attribute-value-modal-body">
                                                            <div class="flex items-start">
                                                                <div
                                                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0">
                                                                    <i
                                                                        class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                                                </div>
                                                                <div class="ml-3 text-left">
                                                                    <p class="text-gray-700">Bạn có chắc chắn muốn xóa giá
                                                                        trị "<strong>{{ $value->value }}</strong>" của
                                                                        thuộc tính "{{ $attribute->name }}"?</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="attribute-value-modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                onclick="closeAttributeValueModal('deleteValueModal{{ $value->id }}')">Hủy</button>
                                                            <form
                                                                action="{{ route('admin.attributes.values.destroy', [$attribute->id, $value->id]) }}"
                                                                method="POST" style="display: inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Xóa</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- jQuery is required by bootstrap-colorpicker --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.4.0/js/bootstrap-colorpicker.min.js"
        integrity="sha512-94dgCw8xWrVcgkmOc2fwKjO4dqK/XsdX1hKaUnQLWCUPsMo4yW5MhXlEVlU3j4G+m87zS6G8rLTrrK3L3eVHIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
         // Đảm bảo tất cả script chạy sau khi DOM đã được tải
        document.addEventListener('DOMContentLoaded', function() {

            // === SCRIPT CHO TOAST NOTIFICATION ===
            const toasts = document.querySelectorAll('.toast');

            const hideToast = (toastElement) => {
                if (toastElement) {
                    toastElement.classList.add('hide');
                    setTimeout(() => {
                        toastElement.remove();
                    }, 350);
                }
            };

            toasts.forEach(toast => {
                const autoHideTimeout = setTimeout(() => {
                    hideToast(toast);
                }, 5000);

                const closeButton = toast.querySelector('[data-dismiss-target]');
                if (closeButton) {
                    closeButton.addEventListener('click', function() {
                        clearTimeout(autoHideTimeout);
                        const targetId = this.getAttribute('data-dismiss-target');
                        const toastToHide = document.querySelector(targetId);
                        hideToast(toastToHide);
                    });
                }
            });

            // === SCRIPT CHO MODAL ===
            window.openAttributeValueModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            }

            window.closeAttributeValueModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            }

            window.addEventListener('click', function(event) {
                document.querySelectorAll('.attribute-value-modal.show').forEach(modal => {
                    if (event.target.closest('.attribute-value-modal-content') === null && event.target.classList.contains('attribute-value-modal')) {
                        closeAttributeValueModal(modal.id);
                    }
                });
            });

            window.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.attribute-value-modal.show').forEach(modal => closeAttributeValueModal(modal.id));
                }
            });

            // === SCRIPT CHO COLOR PICKER (SỬ DỤNG JQUERY) ===
            // For Add New Value form
            $('.colorpicker-group-add').colorpicker().on('colorpickerChange', function(event) {
                $(this).find('.fa-square').css('color', event.color.toString());
            });

            // For Edit Value forms in the table
            @if (isset($attribute) && $attribute->attributeValues)
                @foreach ($attribute->attributeValues as $value)
                    @if ($attribute->display_type === 'color_swatch')
                        $('.colorpicker-group-edit-{{ $value->id }}').colorpicker().on('colorpickerChange', function(event) {
                            $(this).find('.fa-square').css('color', event.color.toString());
                        });
                    @endif
                @endforeach
            @endif
        });
    </script>
@endpush