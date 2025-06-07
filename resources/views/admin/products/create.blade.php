@extends('admin.layouts.app')

@section('title', 'Thêm sản phẩm mới')

@push('styles')
    {{-- Các styles CSS từ HTML gốc được đưa vào đây --}}

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }

        .card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.05), 0 3px 7px -3px rgba(0, 0, 0, 0.05);
            padding: 1.75rem;
            margin-bottom: 1.75rem;
        }

        .card-header {
            display: flex;
            align-items: center;
            color: #1e3a8a;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-header i {
            margin-right: 0.75rem;
            height: 24px;
            width: 24px;
        }

        .input-group {
            margin-bottom: 1.25rem;
        }

        .input-group label {
            display: block;
            color: #4b5563;
            font-weight: 500;
            margin-bottom: 0.625rem;
        }

        .input-field,
        .select-field,
        .textarea-field {
            width: 100%;
            padding: 0.875rem 1.125rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.625rem;
            box-shadow: inset 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            background-color: #f8fafc;
        }

        .input-field:focus,
        .select-field:focus,
        .textarea-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
            background-color: white;
        }
        
        /* Style cho trình soạn thảo TinyMCE để khớp với theme */
        .tox-tinymce {
            border-radius: 0.625rem !important;
            border: 1px solid #cbd5e1 !important;
        }
        .tox:not(.tox-fullscreen) .tox-toolbar-overlord {
            border-top-right-radius: 0.625rem !important;
            border-top-left-radius: 0.625rem !important;
        }
        .tox .tox-statusbar {
             border-bottom-right-radius: 0.625rem !important;
            border-bottom-left-radius: 0.625rem !important;
        }


        .btn {
            padding: 0.875rem 1.75rem;
            border-radius: 0.625rem;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3), 0 2px 4px -2px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            box-shadow: 0 6px 10px -1px rgba(29, 78, 216, 0.4), 0 4px 6px -2px rgba(29, 78, 216, 0.3);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3), 0 2px 4px -2px rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background-color: #dc2626;
            box-shadow: 0 6px 10px -1px rgba(220, 38, 38, 0.4), 0 4px 6px -2px rgba(220, 38, 38, 0.3);
            transform: translateY(-1px);
        }

        .btn-ai {
            background: linear-gradient(to right, #6366f1, #a855f7);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4), 0 2px 4px -2px rgba(168, 85, 247, 0.3);
        }

        .btn-ai:hover {
            box-shadow: 0 6px 10px -1px rgba(99, 102, 241, 0.5), 0 4px 6px -2px rgba(168, 85, 247, 0.4);
            transform: translateY(-1px);
        }

        .btn-ai .loading-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 0.75rem;
        }

        .image-preview {
            /* Dùng chung cho ảnh bìa, thư viện */
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 2px dashed #cbd5e1;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview .remove-img-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            background-color: rgba(220, 38, 38, 0.8);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
            z-index: 10;
        }

        .image-preview .remove-img-btn:hover {
            background-color: #dc2626;
        }

        /* Styles for variant image previews - START */
        .variant-image-preview-item {
            /* Class riêng cho từng item ảnh của biến thể */
            position: relative;
            width: 90px;
            /* Kích thước nhỏ hơn */
            height: 90px;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            /* Viền mặc định */
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .variant-image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .variant-image-preview-item.is-primary {
            border: 3px solid #2563eb;
            /* Viền xanh làm nổi bật ảnh chính */
            box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
        }

        .variant-image-preview-item .set-primary-btn {
            position: absolute;
            bottom: 4px;
            /* Vị trí nút đặt làm chính */
            left: 4px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 3px 5px;
            border-radius: 4px;
            font-size: 0.7rem;
            cursor: pointer;
            z-index: 10;
            /* Nằm trên ảnh */
            display: none; /* Ẩn mặc định, chỉ hiện khi hover */
            align-items: center;
        }
        
        .variant-image-preview-item:hover .set-primary-btn {
            display: inline-flex; /* Hiện khi hover vào item */
        }
        .variant-image-preview-item.is-primary .set-primary-btn {
             display: none; /* Luôn ẩn nút khi đã là ảnh chính */
        }


        .variant-image-preview-item .set-primary-btn:hover {
            background-color: rgba(37, 99, 235, 0.9);
            /* Màu khi hover */
        }

        .variant-image-preview-item .set-primary-btn i {
            /* Icon trong nút */
            width: 12px;
            height: 12px;
            margin-right: 3px;
        }

        /* Styles for variant image previews - END */

        .variant-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.625rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            background-color: #f8fafc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        }

        .variant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .variant-title {
            font-weight: 600;
            color: #0f172a;
            font-size: 1.1rem;
        }

        .form-check-input {
            height: 1.125rem;
            width: 1.125rem;
            margin-top: 0.125rem;
            border-color: #94a3b8;
        }

        .form-check-input:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .required-star {
            color: #ef4444;
            font-weight: bold;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon .icon-prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .input-with-icon .input-field,
        .input-with-icon .select-field {
            padding-left: 2.75rem;
        }

        .label-with-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.625rem;
        }

        .label-with-action label {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')

    <div class="container mx-auto p-4 md:p-8 max-w-7xl">
        <header class="mb-10 flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-800">
                    Thêm Sản Phẩm Mới <span class="text-2xl text-purple-600">✨AI</span>
                </h1>
                <p class="text-gray-600 mt-1">Cung cấp thông tin chi tiết để tạo sản phẩm Apple mới, với sự trợ giúp của AI!
                </p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i data-lucide="arrow-left" class="mr-2 h-5 w-5"></i> Quay Lại Danh Sách
            </a>
        </header>
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <div class="flex items-center">
                    <i data-lucide="alert-octagon" class="h-6 w-6 mr-3"></i>
                    <div>
                        <p class="font-bold">Có lỗi xảy ra trong quá trình xác thực:</p>
                        <ul class="list-disc list-inside mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-md shadow-md"
                role="alert">
                <div class="flex items-center">
                    <i data-lucide="check-circle" class="h-6 w-6 mr-3"></i>
                    <div>
                        <p class="font-bold">Thành công!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <div class="flex items-center">
                    <i data-lucide="alert-triangle" class="h-6 w-6 mr-3"></i>
                    <div>
                        <p class="font-bold">Lỗi!</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
        <form id="addProductForm" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-8 gap-y-6">
                {{-- Cột trái: Thông tin chính & Biến thể --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Card Thông Tin Chung --}}
                    <div class="card">
                        <div class="card-header">
                            <i data-lucide="file-text"></i>Thông Tin Chung
                        </div>
                        <div class="input-group">
                            <label for="name">Tên sản phẩm <span class="required-star">*</span></label>
                            <div class="input-with-icon">
                                <i data-lucide="type" class="icon-prefix"></i>
                                <input type="text" id="name" name="name"
                                    class="input-field @error('name') border-red-500 @enderror" value="{{ old('name') }}">
                            </div>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <label for="slug">Đường dẫn thân thiện (Slug)</label>
                            <div class="input-with-icon">
                                <i data-lucide="link-2" class="icon-prefix"></i>
                                <input type="text" id="slug" name="slug"
                                    class="input-field @error('slug') border-red-500 @enderror" value="{{ old('slug') }}"
                                    placeholder="Tự động tạo nếu để trống">
                            </div>
                            @error('slug')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="short_description">Mô tả ngắn</label>
                                <button type="button" id="generateShortDescAI" class="btn btn-ai btn-sm">
                                    <span class="button-text">✨ Tạo bằng AI</span>
                                    <span class="loading-spinner hidden"></span>
                                </button>
                            </div>
                            <textarea id="short_description" name="short_description"
                                class="textarea-field @error('short_description') border-red-500 @enderror" rows="3">{{ old('short_description') }}</textarea>
                            @error('short_description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- PHẦN THAY ĐỔI: Tích hợp trình soạn thảo WYSIWYG --}}
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="description">Mô tả chi tiết</label>
                                <button type="button" id="generateLongDescAI" class="btn btn-ai btn-sm">
                                    <span class="button-text">✨ Tạo bằng AI</span>
                                    <span class="loading-spinner hidden"></span>
                                </button>
                            </div>
                            {{-- Giữ lại thẻ textarea, TinyMCE sẽ thay thế nó. Loại bỏ các class không cần thiết --}}
                            <textarea id="description" name="description"
                                class="@error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- KẾT THÚC PHẦN THAY ĐỔI --}}

                    </div>

                    {{-- Card Loại Sản Phẩm & Biến Thể --}}
                    <div class="card">
                        <div class="card-header">
                            <i data-lucide="git-fork"></i>Loại Sản Phẩm & Biến Thể
                        </div>
                        <div class="input-group">
                            <label>Loại sản phẩm <span class="required-star">*</span></label>
                            <div class="flex items-center space-x-6">
                                <label
                                    class="flex items-center cursor-pointer p-2 rounded-md hover:bg-blue-50 transition-colors">
                                    <input type="radio" name="type" value="simple" class="form-check-input mr-2"
                                        {{ old('type', 'simple') == 'simple' ? 'checked' : '' }}
                                        onchange="toggleProductTypeFields()"> Đơn giản
                                </label>
                                <label
                                    class="flex items-center cursor-pointer p-2 rounded-md hover:bg-blue-50 transition-colors">
                                    <input type="radio" name="type" value="variable" class="form-check-input mr-2"
                                        {{ old('type') == 'variable' ? 'checked' : '' }}
                                        onchange="toggleProductTypeFields()"> Có biến thể
                                </label>
                            </div>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Trường cho sản phẩm đơn giản --}}
                        <div id="simpleProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200"
                            style="{{ old('type', 'simple') == 'simple' ? '' : 'display:none;' }}">
                            <h3 class="text-lg font-semibold text-gray-700 mb-1">Thông tin sản phẩm đơn giản</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div class="input-group">
                                    <label for="simple_sku">SKU <span class="required-star">*</span></label>
                                    <div class="input-with-icon">
                                        <i data-lucide="scan-barcode" class="icon-prefix"></i>
                                        <input type="text" id="simple_sku" name="simple_sku"
                                            class="input-field @error('simple_sku') border-red-500 @enderror"
                                            value="{{ old('simple_sku') }}">
                                    </div>
                                    @error('simple_sku')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <label for="simple_price">Giá bán <span class="required-star">*</span> (VNĐ)</label>
                                    <div class="input-with-icon">
                                        <i data-lucide="dollar-sign" class="icon-prefix"></i>
                                        <input type="number" id="simple_price" name="simple_price"
                                            class="input-field @error('simple_price') border-red-500 @enderror"
                                            step="1000" min="0" value="{{ old('simple_price') }}">
                                    </div>
                                    @error('simple_price')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <label for="simple_sale_price">Giá khuyến mãi (VNĐ)</label>
                                    <div class="input-with-icon">
                                        <i data-lucide="badge-percent" class="icon-prefix"></i>
                                        <input type="number" id="simple_sale_price" name="simple_sale_price"
                                            class="input-field @error('simple_sale_price') border-red-500 @enderror"
                                            step="1000" min="0" value="{{ old('simple_sale_price') }}">
                                    </div>
                                    @error('simple_sale_price')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <label for="simple_stock_quantity">Số lượng tồn kho <span
                                            class="required-star">*</span></label>
                                    <div class="input-with-icon">
                                        <i data-lucide="boxes" class="icon-prefix"></i>
                                        <input type="number" id="simple_stock_quantity" name="simple_stock_quantity"
                                            class="input-field @error('simple_stock_quantity') border-red-500 @enderror"
                                            min="0" value="{{ old('simple_stock_quantity') }}">
                                    </div>
                                    @error('simple_stock_quantity')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Trường cho sản phẩm có biến thể --}}
                        <div id="variableProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200"
                            style="{{ old('type') == 'variable' ? '' : 'display:none;' }}">
                            <h3 class="text-lg font-semibold text-gray-700 mb-1">Quản lý biến thể</h3>
                            <div class="input-group">
                                <label for="sku_prefix">Tiền tố SKU (cho biến thể)</label>
                                <div class="input-with-icon">
                                    <i data-lucide="scan-line" class="icon-prefix"></i>
                                    <input type="text" id="sku_prefix" name="sku_prefix"
                                        class="input-field @error('sku_prefix') border-red-500 @enderror"
                                        value="{{ old('sku_prefix') }}" placeholder="Ví dụ: APPL-IP15P-">
                                </div>
                                @error('sku_prefix')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="input-group">
                                <label class="flex items-center mb-2">
                                    <i data-lucide="list-filter" class="mr-2 h-5 w-5 text-gray-500"></i>
                                    Thuộc tính sử dụng cho biến thể
                                </label>
                                <div id="productAttributesContainer"
                                    class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 p-3 border border-gray-200 rounded-md bg-gray-50">
                                    {{-- Attributes populated by JS --}}
                                </div>
                                @if ($errors->has('variants.*.attributes'))
                                    <p class="text-red-500 text-xs mt-1">Có lỗi với việc chọn thuộc tính cho biến thể.</p>
                                @endif
                            </div>

                            <div id="variantsContainer" class="space-y-5">
                                {{-- Variant cards will be added here by JS --}}
                                {{-- Handle old('variants') using JavaScript in DOMContentLoaded --}}
                            </div>
                            @if ($errors->has('variants') && is_string($errors->first('variants')))
                                <p class="text-red-500 text-xs mt-1">{{ $errors->first('variants') }}</p>
                            @endif
                            @foreach ($errors->get('variants.*') as $variantErrorKey => $messages)
                                @foreach ($messages as $message)
                                    <p class="text-red-500 text-xs mt-1">Lỗi tại {{ $variantErrorKey }}:
                                        {{ $message }}</p>
                                @endforeach
                            @endforeach


                            <button type="button" id="addVariantButton" class="btn btn-secondary mt-2">
                                <i data-lucide="plus-circle" class="mr-2 h-5 w-5"></i> Thêm Biến Thể Mới
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Tổ chức, Hình ảnh, SEO, etc. --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Card Xuất Bản --}}
                    <div class="card">
                        <div class="card-header">
                            <i data-lucide="send-to-back"></i>Xuất Bản
                        </div>
                        <div class="input-group">
                            <label for="status">Trạng thái <span class="required-star">*</span></label>
                            <div class="input-with-icon">
                                <i data-lucide="activity" class="icon-prefix"></i>
                                <select id="status" name="status"
                                    class="select-field @error('status') border-red-500 @enderror">
                                    <option value="published"
                                        {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Đã xuất bản
                                    </option>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Bản nháp
                                    </option>
                                    <option value="pending_review"
                                        {{ old('status') == 'pending_review' ? 'selected' : '' }}>Chờ duyệt</option>
                                    <option value="trashed" {{ old('status') == 'trashed' ? 'selected' : '' }}>Đã xóa (ẩn)
                                    </option>
                                </select>
                            </div>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group mt-5">
                            <label
                                class="flex items-center cursor-pointer p-2 rounded-md hover:bg-blue-50 transition-colors">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                    class="form-check-input mr-3" {{ old('is_featured') ? 'checked' : '' }}>
                                <i data-lucide="star" class="mr-2 h-5 w-5 text-yellow-500"></i> Sản phẩm nổi bật
                            </label>
                            @error('is_featured')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Card Tổ Chức --}}
                    <div class="card">
                        <div class="card-header">
                            <i data-lucide="folder-tree"></i>Tổ Chức
                        </div>
                        <div class="input-group">
                            <label for="category_id">Danh mục <span class="required-star">*</span></label>
                            <div class="input-with-icon">
                                <i data-lucide="folder-open" class="icon-prefix"></i>
                                <select id="category_id" name="category_id"
                                    class="select-field @error('category_id') border-red-500 @enderror">
                                    <option value="">Chọn danh mục</option>
                                    @if (isset($categories) && $categories->count() > 0)
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @error('category_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="tags">Thẻ sản phẩm (Tags)</label>
                                <button type="button" id="generateTagsAI" class="btn btn-ai btn-sm">
                                    <span class="button-text">✨ Gợi ý</span>
                                    <span class="loading-spinner hidden"></span>
                                </button>
                            </div>
                            <div class="input-with-icon">
                                <i data-lucide="tags" class="icon-prefix"></i>
                                <input type="text" id="tags" name="tags"
                                    class="input-field @error('tags') border-red-500 @enderror"
                                    value="{{ old('tags') }}" placeholder="Ví dụ: iphone 15, apple, new">
                            </div>
                            @error('tags')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <small class="text-gray-500 mt-1 block">Cách nhau bởi dấu phẩy.</small>
                        </div>
                    </div>

                    {{-- Card Hình Ảnh Sản Phẩm Chung --}}
                    <div class="card">
                        <div class="card-header">
                            <i data-lucide="image"></i>Hình Ảnh Chung (Sản Phẩm)
                        </div>
                        <div class="input-group">
                            <label for="cover_image_file">Ảnh bìa <span class="required-star">*</span></label>
                            <input type="file" id="cover_image_file" name="cover_image_file"
                                class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('cover_image_file') border-red-500 @enderror"
                                accept="image/*" onchange="previewCoverImage(event)">
                            <div id="coverImagePreviewContainer" class="image-preview-container mt-3"></div>
                            @error('cover_image_file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <label for="gallery_image_files">Thư viện ảnh chung (Nhiều ảnh)</label>
                            <input type="file" id="gallery_image_files" name="gallery_image_files[]"
                                class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('gallery_image_files.*') border-red-500 @enderror"
                                accept="image/*" multiple onchange="previewGalleryImages(event)">
                            <div id="galleryImagesPreviewContainer" class="image-preview-container mt-3"></div>
                            @foreach ($errors->get('gallery_image_files.*') as $key => $messages)
                                {{-- Loop through errors for each gallery image --}}
                                @foreach ($messages as $message)
                                    <p class="text-red-500 text-xs mt-1">Lỗi ảnh thư viện (ảnh {{ $key + 1 }}):
                                        {{ $message }}</p>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    {{-- Card SEO --}}
                    <div class="card">
                        <div class="card-header">
                            <i data-lucide="search-check"></i>Tối Ưu Hóa SEO
                            <button type="button" id="generateAllSeoAI" class="btn btn-ai btn-sm ml-auto">
                                <span class="button-text">✨ Tạo Tất Cả SEO</span>
                                <span class="loading-spinner hidden"></span>
                            </button>
                        </div>
                        <div class="input-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title"
                                class="input-field @error('meta_title') border-red-500 @enderror"
                                value="{{ old('meta_title') }}">
                            @error('meta_title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea id="meta_description" name="meta_description"
                                class="textarea-field @error('meta_description') border-red-500 @enderror" rows="3">{{ old('meta_description') }}</textarea>
                            @error('meta_description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords"
                                class="input-field @error('meta_keywords') border-red-500 @enderror"
                                value="{{ old('meta_keywords') }}" placeholder="Từ khóa 1, Từ khóa 2">
                            @error('meta_keywords')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Card Thông Tin Bổ Sung --}}
                    <div class="card">
                        <div class="card-header">
                            <i data-lucide="info"></i>Thông Tin Bổ Sung
                        </div>
                        <div class="input-group">
                            <label for="warranty_information">Thông tin bảo hành</label>
                            <textarea id="warranty_information" name="warranty_information"
                                class="textarea-field @error('warranty_information') border-red-500 @enderror" rows="3">{{ old('warranty_information') }}</textarea>
                            @error('warranty_information')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Các nút hành động của Form --}}
            <div class="mt-10 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary w-full sm:w-auto">
                    <i data-lucide="x-circle" class="mr-2 h-5 w-5"></i> Hủy Bỏ
                </a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
                    <i data-lucide="save" class="mr-2 h-5 w-5"></i> Lưu Sản Phẩm
                </button>
            </div>
        </form>
        {{-- Modal for messages --}}
        <div id="messageModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden px-4 z-50">
            <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div id="messageModalIconContainer"
                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                        {{-- Icon will be inserted here by JS --}}
                    </div>
                    <h3 id="messageModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2">Thông báo</h3>
                    <div class="mt-2 px-7 py-3">
                        <p id="messageModalText" class="text-sm text-gray-500"></p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="messageModalCloseButton" class="btn btn-primary w-full">
                            Đã hiểu
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@push('scripts')

    {{-- THAY ĐỔI 1: THÊM SCRIPT CỦA TINYMCE TỪ CDN --}}
    {{-- Lưu ý: Để sử dụng trong môi trường production không bị cảnh báo domain, bạn nên đăng ký một API Key miễn phí tại tiny.cloud --}}
    <script src="https://cdn.tiny.cloud/1/polil4haaavbgscm984gn9lw0zb9xx9hjopkrx9k2ofql26b/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>


    <script>
        // Global variables for the script
        let selectedProductAttributes = [];
        let variantIndexGlobal = 0; // Renamed to avoid conflict with loop var `index`
        // --- Data from PHP (Laravel Backend) ---
        @php
            // Prepare categories data for JS
            $jsCategoriesData = [];
            if (isset($categories) && $categories instanceof \Illuminate\Support\Collection) {
                $jsCategoriesData = $categories
                    ->map(function ($cat) {
                        return ['id' => $cat->id, 'name' => $cat->name];
                    })
                    ->values()
                    ->all();
            }

            // Prepare attributes data for JS, including a default icon logic
            $jsAttributesData = [];
            if (isset($attributes) && $attributes instanceof \Illuminate\Support\Collection) {
                $jsAttributesData = $attributes
                    ->map(function ($attr) {
                        $icon = 'tag'; // default icon
                        if (isset($attr->name)) {
                            // Determine icon based on attribute name
                            if (str_contains(strtolower($attr->name), 'màu') || (isset($attr->display_type) && $attr->display_type === 'color_swatch')) {
                                $icon = 'palette';
                            } elseif (str_contains(strtolower($attr->name), 'dung lượng') || str_contains(strtolower($attr->name), 'bộ nhớ')) {
                                $icon = 'hard-drive';
                            } elseif (str_contains(strtolower($attr->name), 'kích thước')) {
                                $icon = 'maximize';
                            }
                        }
                        // Ensure attributeValues is an array of objects
                        $attributeValuesData = [];
                        if (isset($attr->attributeValues) && ($attr->attributeValues instanceof \Illuminate\Support\Collection || is_array($attr->attributeValues))) {
                            foreach ($attr->attributeValues as $val) {
                                $attributeValuesData[] = [
                                    'id' => $val->id ?? ($val['id'] ?? null),
                                    'value' => $val->value ?? ($val['value'] ?? ''),
                                    'meta' => $val->meta ?? ($val['meta'] ?? null),
                                ];
                            }
                        }
                        return [
                            'id' => $attr->id,
                            'name' => $attr->name ?? 'N/A',
                            'slug' => $attr->slug ?? '',
                            'icon' => $icon,
                            'attributeValues' => $attributeValuesData,
                        ];
                    })
                    ->values()
                    ->all();
            }
        @endphp
        const categoriesFromPHP = @json($jsCategoriesData, JSON_UNESCAPED_UNICODE);
        const allAttributesFromPHP = @json($jsAttributesData, JSON_UNESCAPED_UNICODE);
        const oldVariantsData = @json(old('variants', []), JSON_UNESCAPED_UNICODE); // For repopulating form
        // --- DOM Elements ---
        const productNameInput = document.getElementById('name');
        const categorySelectElement = document.getElementById('category_id');
        const shortDescriptionTextarea = document.getElementById('short_description');
        //const longDescriptionTextarea = document.getElementById('description'); // Sẽ được quản lý bởi TinyMCE
        const metaTitleInput = document.getElementById('meta_title');
        const metaDescriptionTextarea = document.getElementById('meta_description');
        const metaKeywordsInput = document.getElementById('meta_keywords');
        const tagsInput = document.getElementById('tags');
        const slugInput = document.getElementById('slug');
        const generateShortDescBtn = document.getElementById('generateShortDescAI');
        const generateLongDescBtn = document.getElementById('generateLongDescAI');
        const generateTagsBtn = document.getElementById('generateTagsAI');
        const generateAllSeoBtn = document.getElementById('generateAllSeoAI');
        const messageModal = document.getElementById('messageModal');
        const messageModalIconContainer = document.getElementById('messageModalIconContainer');
        const messageModalTitle = document.getElementById('messageModalTitle');
        const messageModalText = document.getElementById('messageModalText');
        const messageModalCloseButton = document.getElementById('messageModalCloseButton');
        const productAttributesContainer = document.getElementById('productAttributesContainer');
        const variantsContainer = document.getElementById('variantsContainer');
        const addVariantButton = document.getElementById('addVariantButton');
        // --- Utility and AI Functions (Keep existing) ---
        function showMessageModal(title, text, type = 'info') {
            /* ... Keep existing ... */
            if (!messageModal || !messageModalTitle || !messageModalText || !messageModalIconContainer) return;
            messageModalTitle.textContent = title;
            messageModalText.textContent = text;
            messageModalIconContainer.innerHTML = '';
            let iconName = 'info',
                iconColorClass = 'text-blue-600',
                iconBgClass = 'bg-blue-100';
            if (type === 'success') {
                iconName = 'check-circle';
                iconColorClass = 'text-green-600';
                iconBgClass = 'bg-green-100';
            } else if (type === 'error') {
                iconName = 'alert-triangle';
                iconColorClass = 'text-red-600';
                iconBgClass = 'bg-red-100';
            }
            const icon = document.createElement('i');
            icon.dataset.lucide = iconName;
            icon.className = `h-6 w-6 ${iconColorClass}`;
            messageModalIconContainer.className =
                `mx-auto flex items-center justify-center h-12 w-12 rounded-full ${iconBgClass}`;
            messageModalIconContainer.appendChild(icon);
            if (typeof lucide !== 'undefined') lucide.createIcons();
            messageModal.classList.remove('hidden');
        }
        if (messageModalCloseButton) {
            messageModalCloseButton.addEventListener('click', () => messageModal.classList.add('hidden'));
        }

        function toggleButtonLoading(button, isLoading) {
            /* ... Keep existing ... */
            if (!button) return;
            const textSpan = button.querySelector('.button-text');
            const spinnerSpan = button.querySelector('.loading-spinner');
            if (textSpan && spinnerSpan) {
                textSpan.classList.toggle('hidden', isLoading);
                spinnerSpan.classList.toggle('hidden', !isLoading);
            }
            button.disabled = isLoading;
        }
        async function callGeminiAPI(prompt, isStructured = false, schema = null) {
            /* ... Keep existing ... */
            const apiKey = ""; // IMPORTANT: API Key is missing here.
            const apiUrl =
                `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${apiKey}`;
            let payload = {
                contents: [{
                    role: "user",
                    parts: [{
                        text: prompt
                    }]
                }]
            };
            if (isStructured && schema) {
                payload.generationConfig = {
                    responseMimeType: "application/json",
                    // The new Gemini 1.5 API uses a different structure for schema
                    responseSchema: {
                        type: schema.type,
                        properties: schema.properties,
                        required: schema.required
                    }
                };
            }
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    console.error("API Error:", errorData);
                    throw new Error(`API request failed: ${errorData.error?.message || response.statusText}`);
                }
                const result = await response.json();
                if (result.candidates && result.candidates[0]?.content?.parts?.[0]) {
                    const responseText = result.candidates[0].content.parts[0].text;
                    return isStructured ? JSON.parse(responseText) : responseText;
                } else {
                    console.error("Unexpected API response structure:", result);
                    throw new Error("Không nhận được nội dung hợp lệ từ AI.");
                }
            } catch (error) {
                console.error("Error calling Gemini API:", error);
                showMessageModal("Lỗi API", `Không thể xử lý yêu cầu từ AI: ${error.message}`, "error");
                return null;
            }
        }

        function getProductContext() {
            /* ... Keep existing ... */
            const productName = productNameInput ? productNameInput.value.trim() : "";
            const categoryName = categorySelectElement ? (categorySelectElement.options[categorySelectElement.selectedIndex]
                ?.text || "") : "";
            let attributesString = "";
            const productTypeRadio = document.querySelector('input[name="type"]:checked');
            if (productTypeRadio && productTypeRadio.value === 'variable') {
                const firstVariantCard = variantsContainer ? variantsContainer.querySelector('.variant-card') : null;
                if (firstVariantCard) {
                    const attributeSelects = firstVariantCard.querySelectorAll(
                    'select[name$="[attributes]"]'); // Simplified selector
                    let tempAttrs = [];
                    attributeSelects.forEach(select => {
                        const attrNameLabel = select.closest('.input-group')?.querySelector('label');
                        if (attrNameLabel) {
                            const attrName = attrNameLabel.textContent.replace('*', '').trim();
                            const attrValue = select.options[select.selectedIndex]?.text;
                            if (attrValue && attrValue !== `Chọn ${attrName}`) {
                                tempAttrs.push(`${attrName}: ${attrValue}`);
                            }
                        }
                    });
                    if (tempAttrs.length > 0) attributesString = ` với các thuộc tính nổi bật: ${tempAttrs.join(', ')}`;
                }
            }
            if (!productName) {
                showMessageModal("Thiếu thông tin", "Vui lòng nhập tên sản phẩm trước khi sử dụng tính năng AI.", "error");
                return null;
            }
            return `Sản phẩm: ${productName}, thuộc danh mục: ${categoryName}${attributesString}. Tập trung vào các sản phẩm của Apple.`;
        }
        // AI Event Listeners (Keep existing)
        const seoSchema = {
            type: "OBJECT",
            properties: {
                meta_title: {
                    type: "STRING"
                },
                meta_description: {
                    type: "STRING"
                },
                meta_keywords: {
                    type: "STRING"
                }
            },
            required: ["meta_title", "meta_description", "meta_keywords"]
        };
        if (generateShortDescBtn) {
            generateShortDescBtn.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context || !shortDescriptionTextarea) return;
                toggleButtonLoading(generateShortDescBtn, true);
                const prompt =
                    `Viết một mô tả ngắn (khoảng 1-2 câu, tối đa 50 từ) thật hấp dẫn cho sản phẩm Apple sau: ${context}. Tập trung vào điểm nổi bật nhất, giọng văn chuyên nghiệp.`;
                const generatedText = await callGeminiAPI(prompt);
                if (generatedText) {
                    shortDescriptionTextarea.value = generatedText;
                    showMessageModal("Hoàn tất", "Đã tạo mô tả ngắn bằng AI!", "success");
                }
                toggleButtonLoading(generateShortDescBtn, false);
            });
        }
        
        // THAY ĐỔI 3: CẬP NHẬT EVENT LISTENER CỦA NÚT TẠO MÔ TẢ DÀI BẰNG AI
        if (generateLongDescBtn) {
            generateLongDescBtn.addEventListener('click', async () => {
                const context = getProductContext();
                // Thay vì check longDescriptionTextarea, ta check sự tồn tại của TinyMCE instance
                if (!context || typeof tinymce === 'undefined' || !tinymce.get('description')) return;

                const shortDesc = shortDescriptionTextarea ? shortDescriptionTextarea.value.trim() : '';
                toggleButtonLoading(generateLongDescBtn, true);
                let prompt =
                    `Viết một mô tả chi tiết (khoảng 3-5 đoạn văn, có thể dùng markdown cho tiêu đề và danh sách) cho sản phẩm Apple sau: ${context}.`;
                prompt += shortDesc ? `\nCó thể dựa trên mô tả ngắn sau: "${shortDesc}".` : '';
                prompt +=
                    `\nHãy viết bằng giọng văn chuyên nghiệp, phù hợp để đăng bán trên website thương mại điện tử. Tránh các từ ngữ quá quảng cáo, tập trung vào thông tin hữu ích. Sử dụng thẻ <h3> cho tiêu đề phụ và <ul><li> cho danh sách nếu cần.`;
                
                const generatedText = await callGeminiAPI(prompt);

                if (generatedText) {
                    // Cập nhật nội dung cho trình soạn thảo TinyMCE thay vì textarea
                    tinymce.get('description').setContent(generatedText);
                    showMessageModal("Hoàn tất", "Đã tạo mô tả chi tiết bằng AI!", "success");
                }
                toggleButtonLoading(generateLongDescBtn, false);
            });
        }
        if (generateAllSeoBtn) {
            generateAllSeoBtn.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context || !shortDescriptionTextarea || !metaTitleInput || !
                    metaDescriptionTextarea || !metaKeywordsInput) return;
                
                // Lấy nội dung từ trình soạn thảo TinyMCE thay vì textarea
                const productDescription = (typeof tinymce !== 'undefined' && tinymce.get('description')) ? tinymce.get('description').getContent({format: 'text'}) : (shortDescriptionTextarea.value.trim());

                if (!productDescription) {
                    showMessageModal("Thiếu thông tin", "Vui lòng có mô tả sản phẩm trước khi tạo SEO.",
                        "error");
                    return;
                }
                toggleButtonLoading(generateAllSeoBtn, true);
                const prompt =
                    `Dựa trên thông tin sản phẩm Apple: "${context}" và mô tả: "${productDescription}", hãy tạo các thẻ meta SEO (meta title, meta description, meta keywords) cho một trang web bán sản phẩm này.`;
                const seoData = await callGeminiAPI(prompt, true, seoSchema);
                if (seoData) {
                    metaTitleInput.value = seoData.meta_title || '';
                    metaDescriptionTextarea.value = seoData.meta_description || '';
                    metaKeywordsInput.value = seoData.meta_keywords || '';
                    showMessageModal("Hoàn tất", "Đã tạo thông tin SEO bằng AI!", "success");
                }
                toggleButtonLoading(generateAllSeoBtn, false);
            });
        }
        if (generateTagsBtn) {
            generateTagsBtn.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context || !tagsInput) return;
                toggleButtonLoading(generateTagsBtn, true);
                const prompt =
                    `Gợi ý 5-7 thẻ (tags) phù hợp nhất cho sản phẩm Apple sau: ${context}. Các thẻ nên ngắn gọn, tập trung vào tên sản phẩm, dòng sản phẩm, tính năng chính hoặc đối tượng người dùng. Trả về dưới dạng danh sách các từ khóa cách nhau bởi dấu phẩy.`;
                const generatedTags = await callGeminiAPI(prompt);
                if (generatedTags) {
                    tagsInput.value = generatedTags;
                    showMessageModal("Hoàn tất", "Đã gợi ý thẻ sản phẩm bằng AI!", "success");
                }
                toggleButtonLoading(generateTagsBtn, false);
            });
        }
        // --- Product Type and Variant Logic (UPDATED) ---
        function updateSelectedAttributesForVariants() {
            selectedProductAttributes = [];
            document.querySelectorAll('.product-attribute-checkbox:checked').forEach(checkbox => {
                const attrId = parseInt(checkbox.value);
                if (Array.isArray(allAttributesFromPHP)) {
                    const attribute = allAttributesFromPHP.find(a => a && typeof a.id !== 'undefined' && a.id ===
                        attrId);
                    if (attribute) selectedProductAttributes.push(attribute);
                }
            });
        }

        function toggleProductTypeFields() {
            const typeRadio = document.querySelector('input[name="type"]:checked');
            if (!typeRadio) return;
            const type = typeRadio.value;
            const simpleFields = document.getElementById('simpleProductFields');
            const variableFields = document.getElementById('variableProductFields');
            const simpleInputs = ['simple_sku', 'simple_price', 'simple_stock_quantity'];

            if (simpleFields) simpleFields.style.display = (type === 'simple' ? 'block' : 'none');
            if (variableFields) variableFields.style.display = (type === 'variable' ? 'block' : 'none');
            // simpleInputs.forEach(id => { const input = document.getElementById(id); if (input) input.required = (type === 'simple'); });

            if (type === 'simple') {
                if (variantsContainer) variantsContainer.innerHTML = '';
                variantIndexGlobal = 0;
                document.querySelectorAll('.product-attribute-checkbox').forEach(cb => cb.checked = false);
                updateSelectedAttributesForVariants();
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
        if (productAttributesContainer && Array.isArray(allAttributesFromPHP)) {
            allAttributesFromPHP.forEach(attr => {
                if (!attr || typeof attr.id === 'undefined' || typeof attr.name === 'undefined') return;
                const labelEl = document.createElement('label');
                labelEl.className =
                    'flex items-center cursor-pointer p-2 rounded-md hover:bg-gray-100 transition-colors';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = `attr_${attr.id}`;
                checkbox.value = attr.id;
                checkbox.dataset.attributeName = attr.name;
                checkbox.className = 'product-attribute-checkbox form-check-input mr-2';
                checkbox.onchange = updateSelectedAttributesForVariants;

                if (Array.isArray(oldVariantsData) && oldVariantsData.length > 0) {
                    const usedInOld = oldVariantsData.some(oldVar => oldVar.attributes && typeof oldVar
                        .attributes === 'object' && oldVar.attributes[attr.id.toString()]);
                    if (usedInOld) checkbox.checked = true;
                }
                const iconEl = document.createElement('i');
                iconEl.dataset.lucide = attr.icon || 'tag';
                iconEl.className = 'mr-1.5 h-4 w-4 text-gray-500';
                const textNode = document.createTextNode(attr.name);
                labelEl.appendChild(checkbox);
                labelEl.appendChild(iconEl);
                labelEl.appendChild(textNode);
                productAttributesContainer.appendChild(labelEl);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
            updateSelectedAttributesForVariants();
        }
        if (addVariantButton) {
            addVariantButton.addEventListener('click', () => {
                const currentVariantIndex = variantIndexGlobal; // Use a local copy for this specific variant
                const productTypeRadio = document.querySelector('input[name="type"]:checked');
                if (!productTypeRadio || (productTypeRadio.value === 'variable' && selectedProductAttributes
                        .length === 0)) {
                    showMessageModal('Thông báo',
                        'Vui lòng chọn ít nhất một thuộc tính cho sản phẩm trước khi thêm biến thể.', 'info');
                    return;
                }
                const variantCard = document.createElement('div');
                variantCard.className = 'variant-card';
                variantCard.dataset.variantIndex = currentVariantIndex; // Store index

                let variantHeaderHTML =
                    `<div class="variant-header"><div class="flex items-center"><i data-lucide="puzzle" class="mr-2 h-5 w-5 text-blue-600"></i><h4 class="variant-title">Biến Thể #${currentVariantIndex + 1}</h4></div><button type="button" class="remove-variant-btn btn btn-danger btn-sm p-1.5 rounded-md"><i data-lucide="trash-2" class="h-4 w-4"></i></button></div>`;

                let attributesHTML = '<div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-4">';
                selectedProductAttributes.forEach(attr => {
                    if (!attr || !Array.isArray(attr.attributeValues)) return;
                    attributesHTML +=
                        `<div class="input-group"><label for="variants_${currentVariantIndex}_attr_${attr.id}" class="text-sm font-medium">${attr.name} <span class="required-star">*</span></label><div class="input-with-icon"><i data-lucide="${attr.icon || 'tag'}" class="icon-prefix"></i><select name="variants[${currentVariantIndex}][attributes][${attr.id}]" id="variants_${currentVariantIndex}_attr_${attr.id}" class="select-field text-sm" ><option value="">Chọn ${attr.name}</option>${attr.attributeValues.map(val => `<option value="${val.id}">${val.value}</option>`).join('')}</select></div></div>`;
                });
                attributesHTML += '</div>';

                let variantFieldsHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            <div class="input-group"><label for="variants_${currentVariantIndex}_sku" class="text-sm font-medium">SKU Biến Thể <span class="required-star">*</span></label><div class="input-with-icon"><i data-lucide="scan-barcode" class="icon-prefix"></i><input type="text" name="variants[${currentVariantIndex}][sku]" id="variants_${currentVariantIndex}_sku" class="input-field text-sm" ></div></div>
            <div class="input-group"><label for="variants_${currentVariantIndex}_price" class="text-sm font-medium">Giá Biến Thể <span class="required-star">*</span> (VNĐ)</label><div class="input-with-icon"><i data-lucide="dollar-sign" class="icon-prefix"></i><input type="number" name="variants[${currentVariantIndex}][price]" id="variants_${currentVariantIndex}_price" class="input-field text-sm" step="1000" min="0" ></div></div>
            <div class="input-group"><label for="variants_${currentVariantIndex}_sale_price" class="text-sm font-medium">Giá KM Biến Thể (VNĐ)</label><div class="input-with-icon"><i data-lucide="badge-percent" class="icon-prefix"></i><input type="number" name="variants[${currentVariantIndex}][sale_price]" id="variants_${currentVariantIndex}_sale_price" class="input-field text-sm" step="1000" min="0"></div></div>
            <div class="input-group"><label for="variants_${currentVariantIndex}_stock_quantity" class="text-sm font-medium">Tồn Kho <span class="required-star">*</span></label><div class="input-with-icon"><i data-lucide="boxes" class="icon-prefix"></i><input type="number" name="variants[${currentVariantIndex}][stock_quantity]" id="variants_${currentVariantIndex}_stock_quantity" class="input-field text-sm" min="0" ></div></div>
            
            {{-- Input for multiple variant images --}}
            <div class="input-group md:col-span-2">
                <label for="variants_${currentVariantIndex}_image_files" class="text-sm font-medium">Ảnh Biến Thể (chọn nhiều)</label>
                <input type="file" name="variants[${currentVariantIndex}][image_files][]" id="variants_${currentVariantIndex}_image_files" class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*" multiple onchange="previewVariantImages(event, ${currentVariantIndex})">
                <input type="hidden" name="variants[${currentVariantIndex}][primary_image_filename]" id="variants_${currentVariantIndex}_primary_image_filename">
                <div id="variant_${currentVariantIndex}_image_preview_container" class="image-preview-container mt-2" style="justify-content: flex-start; gap: 0.5rem;">
                    {{-- Variant image previews will appear here --}}
                </div>
                {{-- Error messages for variant images can be handled by Laravel's validation if needed --}}
            </div>
        </div>
        <div class="mt-4">
            <label class="flex items-center text-sm cursor-pointer p-2 rounded-md hover:bg-gray-100 transition-colors">
                <input type="radio" name="variant_is_default_radio_group" value="${currentVariantIndex}" class="form-check-input mr-2 variant-default-radio">
                <input type="hidden" name="variants[${currentVariantIndex}][is_default]" value="false" class="is-default-hidden-input">
                Đặt làm biến thể mặc định
            </label>
        </div>
    `;

                variantCard.innerHTML = variantHeaderHTML + attributesHTML + variantFieldsHTML;
                if (variantsContainer) variantsContainer.appendChild(variantCard);

                const removeBtn = variantCard.querySelector('.remove-variant-btn');
                if (removeBtn) removeBtn.addEventListener('click', function() {
                    this.closest('.variant-card').remove();
                    updateDefaultVariantRadioAndHiddenFields();
                });

                const defaultRadioEl = variantCard.querySelector('.variant-default-radio');
                if (defaultRadioEl) defaultRadioEl.addEventListener('change', handleDefaultVariantChange);

                variantIndexGlobal++; // Increment global counter for the *next* new variant
                if (typeof lucide !== 'undefined') lucide.createIcons();
                updateDefaultVariantRadioAndHiddenFields();
            });
        }

        function handleDefaultVariantChange(event) {
            /* ... Keep existing ... */
            document.querySelectorAll('.variant-default-radio').forEach(radio => {
                const card = radio.closest('.variant-card');
                if (card) {
                    const hiddenInput = card.querySelector('.is-default-hidden-input');
                    if (hiddenInput) hiddenInput.value = (radio === event.target && radio.checked) ? "true" :
                        "false";
                }
            });
        }

        function updateDefaultVariantRadioAndHiddenFields() {
            /* ... Keep existing ... */
            const defaultRadios = document.querySelectorAll('.variant-default-radio');
            let oneIsChecked = false;
            defaultRadios.forEach(radio => {
                if (radio.checked) oneIsChecked = true;
            });
            if (!oneIsChecked && defaultRadios.length > 0) {
                defaultRadios[0].checked = true;
            }
            defaultRadios.forEach(radio => {
                const card = radio.closest('.variant-card');
                if (card) {
                    const hiddenInput = card.querySelector('.is-default-hidden-input');
                    if (hiddenInput) hiddenInput.value = radio.checked ? "true" : "false";
                }
            });
        }
        function previewCoverImage(event) {
            const container = document.getElementById('coverImagePreviewContainer');
            if (!container) return;
            container.innerHTML = '';
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview';
                    div.innerHTML =
                        `<img src="${e.target.result}" alt="Cover Image Preview"><span class="remove-img-btn" onclick="removeCoverImage(event)"><i data-lucide="x" class="h-3 w-3"></i></span>`;
                    container.appendChild(div);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                };
                reader.readAsDataURL(file);
            }
        }

        function removeCoverImage(event) {
            const coverImageInput = document.getElementById('cover_image_file');
            if (coverImageInput) coverImageInput.value = '';
            const previewElement = event.target.closest('.image-preview');
            if (previewElement) previewElement.remove();
        }

        function previewGalleryImages(event) {
            const container = document.getElementById('galleryImagesPreviewContainer');
            if (!container) return;
            container.innerHTML = '';
            const files = event.target.files;
            const dataTransfer = new DataTransfer();
            
            Array.from(files).forEach((file, index) => {
                 dataTransfer.items.add(file);
                 const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview';
                     div.dataset.filename = file.name;
                    div.innerHTML =
                        `<img src="${e.target.result}" alt="Gallery Image ${index + 1}"><span class="remove-img-btn" onclick="removeSpecificGalleryImage(this, '${file.name}')"><i data-lucide="x" class="h-3 w-3"></i></span>`;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
             event.target.files = dataTransfer.files;

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function removeSpecificGalleryImage(element, fileNameToRemove) {
            const galleryInput = document.getElementById('gallery_image_files');
            if (!galleryInput) return;
            const currentFiles = Array.from(galleryInput.files);
            const newFiles = currentFiles.filter(file => file.name !== fileNameToRemove);
            const dataTransfer = new DataTransfer();
            newFiles.forEach(file => dataTransfer.items.add(file));
            galleryInput.files = dataTransfer.files;
            const previewElement = element.closest('.image-preview');
            if (previewElement) previewElement.remove();
        }

        function previewVariantImages(event, vIndex) {
            const input = event.target;
            const previewContainer = document.getElementById(`variant_${vIndex}_image_preview_container`);
            const primaryImageInput = document.getElementById(`variants_${vIndex}_primary_image_filename`);
            if (!previewContainer || !primaryImageInput) return;

            // Use DataTransfer to manage the file list, allowing removal
            const dataTransfer = new DataTransfer();
            const existingFiles = Array.from(input.files);
            existingFiles.forEach(file => dataTransfer.items.add(file));
            
            previewContainer.innerHTML = ''; // Clear current previews
            let primaryFilename = primaryImageInput.value;

            Array.from(dataTransfer.files).forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview variant-image-preview-item';
                    div.dataset.filename = file.name;
                    div.innerHTML = `
                <img src="${e.target.result}" alt="Ảnh biến thể ${idx + 1}">
                <span class="remove-img-btn" onclick="removeSpecificVariantImage(this, ${vIndex}, '${file.name}')"><i data-lucide="x" class="h-3 w-3"></i></span>
                <button type="button" class="set-primary-btn" title="Đặt làm ảnh chính" onclick="setVariantPrimaryImage(${vIndex}, '${file.name}')">
                   <i data-lucide="star"></i>
                </button>
            `;
                    previewContainer.appendChild(div);
                }
                reader.readAsDataURL(file);
            });

             // After loop, re-evaluate the primary image
            setTimeout(() => {
                const allPreviews = previewContainer.querySelectorAll('.variant-image-preview-item');
                let hasPrimary = false;

                // Check if current primary still exists
                if(primaryFilename) {
                    const primaryEl = previewContainer.querySelector(`[data-filename="${primaryFilename}"]`);
                    if(primaryEl) {
                        primaryEl.classList.add('is-primary');
                        hasPrimary = true;
                    } else {
                        primaryImageInput.value = ''; // It was removed
                    }
                }
                
                // If no primary is set (or was removed), set the first one
                if(!hasPrimary && allPreviews.length > 0) {
                     const firstPreview = allPreviews[0];
                     firstPreview.classList.add('is-primary');
                     primaryImageInput.value = firstPreview.dataset.filename;
                }

                if (typeof lucide !== 'undefined') lucide.createIcons();
            }, 150); // Delay to ensure images render
        }

        function removeSpecificVariantImage(buttonElement, vIndex, fileNameToRemove) {
            const input = document.getElementById(`variants_${vIndex}_image_files`);
            const primaryImageInput = document.getElementById(`variants_${vIndex}_primary_image_filename`);
            if (!input || !primaryImageInput) return;

            const dt = new DataTransfer();
            const files = Array.from(input.files);
            files.forEach(file => {
                if (file.name !== fileNameToRemove) dt.items.add(file);
            });
            input.files = dt.files;

            buttonElement.closest('.variant-image-preview-item').remove();

            if (primaryImageInput.value === fileNameToRemove) {
                primaryImageInput.value = '';
                const previewContainer = document.getElementById(`variant_${vIndex}_image_preview_container`);
                const remainingPreviews = previewContainer.querySelectorAll('.variant-image-preview-item');
                if (remainingPreviews.length > 0) {
                    const newPrimaryPreview = remainingPreviews[0];
                    setVariantPrimaryImage(vIndex, newPrimaryPreview.dataset.filename);
                }
            }
             if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function setVariantPrimaryImage(vIndex, fileName) {
            const primaryImageInput = document.getElementById(`variants_${vIndex}_primary_image_filename`);
            const previewContainer = document.getElementById(`variant_${vIndex}_image_preview_container`);
            if (!primaryImageInput || !previewContainer) return;

            primaryImageInput.value = fileName;

            previewContainer.querySelectorAll('.variant-image-preview-item').forEach(preview => {
                preview.classList.remove('is-primary');
                if (preview.dataset.filename === fileName) {
                    preview.classList.add('is-primary');
                }
            });
             if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        if (productNameInput && slugInput) {
            productNameInput.addEventListener('keyup', () => {
                if (slugInput.placeholder === 'Tự động tạo nếu để trống' || slugInput.dataset.auto === "true") {
                    slugInput.value = slugify(productNameInput.value);
                }
            });
            slugInput.addEventListener('input', () => {
                slugInput.dataset.auto = slugInput.value.trim() === "" ? "true" : "false";
            });
        }

        function slugify(text) {
            if (!text) return '';
            return text.toString().toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd')
                .replace(/\s+/g, '-').replace(/[^\w-]+/g, '').replace(/--+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
        }
        
        document.addEventListener('DOMContentLoaded', () => {

            // THAY ĐỔI 2: KHỞI TẠO TINYMCE
            tinymce.init({
                selector: 'textarea#description',
                plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
                menubar: 'file edit view insert format tools table help',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | fontfamily fontsize | align numlist bullist | link image media | table | lineheight | strikethrough superscript subscript | accordions | removeformat',
                height: 500,
                skin: 'oxide', // 'oxide' là skin mặc định, bạn có thể dùng 'oxide-dark' nếu có dark mode
                content_css: 'default', // Có thể trỏ đến file CSS của bạn để đồng bộ style
                content_style: "body { font-family: 'Inter', sans-serif; font-size: 16px; }",
                autosave_ask_before_unload: true,
                autosave_interval: '30s',
                autosave_prefix: '{path}{query}-{id}-',
                autosave_restore_when_empty: false,
                autosave_retention: '2m',
                image_advtab: true,
                importcss_append: true,
                // Cấu hình upload ảnh (cần có backend xử lý)
                // images_upload_url: '/admin/upload-image', // URL để xử lý upload ảnh
                // images_upload_credentials: true,
                // file_picker_types: 'image',
                // file_picker_callback: function (cb, value, meta) { /* ... */ }
                setup: function(editor) {
                    editor.on('change', function() {
                        // Bắt sự kiện change để đảm bảo form validation của trình duyệt hoạt động nếu cần
                        editor.save();
                    });
                }
            });

            toggleProductTypeFields();
            if (typeof lucide !== 'undefined') lucide.createIcons();
            if (slugInput) slugInput.dataset.auto = "true";
            updateDefaultVariantRadioAndHiddenFields();

            // Repopulate variants if old input exists
            const productTypeRadio = document.querySelector('input[name="type"]:checked');
            if (productTypeRadio && productTypeRadio.value === 'variable' && Array.isArray(oldVariantsData) &&
                oldVariantsData.length > 0) {
                let usedAttributeIdsInOld = new Set();
                oldVariantsData.forEach(oldVar => {
                    if (oldVar.attributes && typeof oldVar.attributes === 'object') {
                        Object.keys(oldVar.attributes).forEach(attrId => usedAttributeIdsInOld.add(parseInt(
                            attrId)));
                    }
                });
                document.querySelectorAll('.product-attribute-checkbox').forEach(cb => {
                    if (usedAttributeIdsInOld.has(parseInt(cb.value))) cb.checked = true;
                });
                updateSelectedAttributesForVariants();

                oldVariantsData.forEach((oldVariant, loopIndex) => {
                    if (addVariantButton) addVariantButton.click(); 
                    
                    const currentVariantIndex = variantIndexGlobal - 1; // Index của card vừa được tạo
                    const currentVariantCard = variantsContainer ? variantsContainer.querySelector(
                        `.variant-card[data-variant-index="${currentVariantIndex}"]`) :
                    null; 
                    if (currentVariantCard) {
                        const skuInput = currentVariantCard.querySelector(
                            `input[name="variants[${currentVariantIndex}][sku]"]`);
                        if (skuInput) skuInput.value = oldVariant.sku || '';
                        const priceInput = currentVariantCard.querySelector(
                            `input[name="variants[${currentVariantIndex}][price]"]`);
                        if (priceInput) priceInput.value = oldVariant.price || '';
                        const salePriceInput = currentVariantCard.querySelector(
                            `input[name="variants[${currentVariantIndex}][sale_price]"]`);
                        if (salePriceInput) salePriceInput.value = oldVariant.sale_price || '';
                        const stockInput = currentVariantCard.querySelector(
                            `input[name="variants[${currentVariantIndex}][stock_quantity]"]`);
                        if (stockInput) stockInput.value = oldVariant.stock_quantity || '';

                        if (oldVariant.attributes && typeof oldVariant.attributes === 'object') {
                            Object.entries(oldVariant.attributes).forEach(([attrId, attrValueId]) => {
                                const attrSelect = currentVariantCard.querySelector(
                                    `select[name="variants[${currentVariantIndex}][attributes][${attrId}]"]`
                                    );
                                if (attrSelect) attrSelect.value = attrValueId;
                            });
                        }
                        const defaultRadio = currentVariantCard.querySelector(
                            `input[name="variant_is_default_radio_group"][value="${currentVariantIndex}"]`);
                        const defaultHidden = currentVariantCard.querySelector(
                            `input[name="variants[${currentVariantIndex}][is_default]"]`);
                        const isDefault = oldVariant.is_default === "true" || oldVariant.is_default ===
                        true;
                        if (defaultRadio) defaultRadio.checked = isDefault;
                        if (defaultHidden) defaultHidden.value = isDefault ? "true" : "false";

                        const primaryImageHiddenInput = currentVariantCard.querySelector(
                            `input[name="variants[${currentVariantIndex}][primary_image_filename]"]`);
                        if (primaryImageHiddenInput && oldVariant.primary_image_filename) {
                            primaryImageHiddenInput.value = oldVariant.primary_image_filename;
                        }
                    }
                });
                updateDefaultVariantRadioAndHiddenFields();
            }
        });

        const form = document.getElementById('addProductForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                // Trigger save on TinyMCE editor to ensure textarea has the latest content before submission
                if (typeof tinymce !== 'undefined' && tinymce.get('description')) {
                    tinymce.get('description').save();
                }

                const typeRadio = document.querySelector('input[name="type"]:checked');
                if (!typeRadio) {
                    showMessageModal('Cảnh báo', 'Vui lòng chọn loại sản phẩm.', 'error');
                    event.preventDefault();
                    return;
                }
                const type = typeRadio.value;

                if (type === 'variable') {
                    if (!variantsContainer || variantsContainer.children.length === 0) {
                        showMessageModal('Cảnh báo',
                            'Sản phẩm có biến thể phải có ít nhất một biến thể được thêm vào.', 'error');
                        event.preventDefault();
                        return;
                    }
                    let oneDefaultRadioSelected = false;
                    document.querySelectorAll('.variant-default-radio').forEach(radio => {
                        if (radio.checked) oneDefaultRadioSelected = true;
                    });
                    if (!oneDefaultRadioSelected && variantsContainer && variantsContainer.children.length > 0) {
                        showMessageModal('Cảnh báo', 'Vui lòng chọn một biến thể làm mặc định.', 'error');
                        event.preventDefault();
                        return;
                    }

                    const variantCards = variantsContainer.querySelectorAll('.variant-card');
                    for (let i = 0; i < variantCards.length; i++) {
                        const vCard = variantCards[i];
                        const vIndexFromCard = vCard.dataset.variantIndex;
                        const imageFilesInput = vCard.querySelector(
                            `input[id="variants_${vIndexFromCard}_image_files"]`);
                        const primaryImageFilenameInput = vCard.querySelector(
                            `input[id="variants_${vIndexFromCard}_primary_image_filename"]`);

                        if (imageFilesInput && imageFilesInput.files.length > 0 && (!primaryImageFilenameInput || !
                                primaryImageFilenameInput.value)) {
                            event.preventDefault();
                            showMessageModal("Lỗi Biến Thể #" + (parseInt(vIndexFromCard) + 1),
                                "Vui lòng chọn một ảnh chính cho biến thể này (hoặc xóa tất cả ảnh của nó nếu không cần).",
                                "error");
                            vCard.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            const imageSection = vCard.querySelector(
                                `#variant_${vIndexFromCard}_image_preview_container`);
                            if (imageSection) {
                                imageSection.style.outline = '2px solid red';
                                setTimeout(() => {
                                    imageSection.style.outline = '';
                                }, 3500);
                            }
                            return;
                        }
                    }
                }
            });
        }
        const observer = new MutationObserver(mutations => {
            for (const mutation of mutations) {
                if (mutation.type === 'childList' && typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        });
        if (variantsContainer) observer.observe(variantsContainer, {
            childList: true,
            subtree: true
        });
        if (productAttributesContainer) observer.observe(productAttributesContainer, {
            childList: true
        });
        const coverPreviewContainer = document.getElementById('coverImagePreviewContainer');
        const galleryPreviewContainer = document.getElementById('galleryImagesPreviewContainer');
        if (coverPreviewContainer) observer.observe(coverPreviewContainer, {
            childList: true
        });
        if (galleryPreviewContainer) observer.observe(galleryPreviewContainer, {
            childList: true
        });
    </script>
@endpush