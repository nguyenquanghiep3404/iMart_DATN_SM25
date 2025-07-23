@extends('admin.layouts.app')

@section('title', 'Chỉnh Sửa Sản Phẩm')

@push('styles')
    {{-- CSS styles --}}
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
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

        .input-group {
            margin-bottom: 1.25rem;
        }

        .input-group label {
            display: block;
            color: #4b5563;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .input-field,
        .select-field,
        .textarea-field {
            width: 100%;
            padding: 0.875rem 1.125rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.625rem;
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
        
        /* Style cho trình soạn thảo CKEditor 5 */
        .ck-editor__editable_inline {
            min-height: 400px;
            border-radius: 0 0 0.625rem 0.625rem !important;
            border: 1px solid #cbd5e1 !important;
            border-top: 0 !important;
        }
        .ck.ck-toolbar {
            border-radius: 0.625rem 0.625rem 0 0 !important;
            border: 1px solid #cbd5e1 !important;
        }

        .btn {
            padding: 0.875rem 1.75rem;
            border-radius: 0.625rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            border: 1px solid transparent;
            transition: all 0.2s ease-in-out;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-ai {
            background: linear-gradient(to right, #6366f1, #a855f7);
            color: white;
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

        .variant-image-preview-item {
            position: relative;
            width: 90px;
            height: 90px;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 2px solid #e2e8f0;
        }

        .variant-image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .variant-image-preview-item .remove-img-btn {
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
            z-index: 10;
        }

        .variant-image-preview-item.is-primary {
            border: 3px solid #2563eb;
        }

        .variant-image-preview-item .set-primary-btn {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 3px 5px;
            border-radius: 4px;
            font-size: 0.7rem;
            cursor: pointer;
            z-index: 10;
            display: none;
            align-items: center;
        }

        .variant-image-preview-item:hover .set-primary-btn {
            display: inline-flex;
        }

        .variant-image-preview-item.is-primary .set-primary-btn {
            display: none;
        }

        .variant-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.625rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            background-color: #f8fafc;
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
        }

        .form-check-input {
            height: 1.125rem;
            width: 1.125rem;
        }

        .required-star {
            color: #ef4444;
            font-weight: bold;
        }

        .label-with-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .svg-icon {
            width: 1.25rem;
            height: 1.25rem;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card-header .svg-icon {
            margin-right: 0.5rem;
        }

        button .svg-icon {
            margin-right: 0.5rem;
        }

        .hidden {
            display: none !important;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-group label.flex {
            display: flex;
            margin-bottom: 0;
        }

        .input-group .form-section-heading {
            margin-bottom: 0;
        }

        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #ffffff;
            min-width: 220px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.1);
            z-index: 100;
            border-radius: 0.5rem;
            padding: 0.5rem 0;
            border: 1px solid #e2e8f0;
        }

        .action-dropdown-content .dropdown-header {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }

        .action-dropdown-content a {
            color: #334155;
            padding: 0.65rem 1rem;
            text-decoration: none;
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
        }

        .action-dropdown-content a:hover {
            background-color: #f1f5f9;
        }

        .action-dropdown-content a.disabled {
            color: #94a3b8;
            cursor: not-allowed;
            background-color: transparent;
        }

        .action-dropdown:hover .action-dropdown-content {
            display: block;
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
                    Chỉnh Sửa Sản Phẩm <span class="text-2xl text-purple-600">✨AI</span>
                </h1>
                <p class="text-gray-600 mt-1">Cập nhật thông tin chi tiết cho sản phẩm: <strong>{{ $product->name }}</strong>
                </p>
            </div>
            <div class="mb-6">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <svg class="svg-icon" viewBox="0 0 24 24">
                        <polyline points="19 12 5 12"></polyline>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Quay Lại Danh Sách
                </a>
            </div>
        </header>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <div class="flex items-center">
                    <svg class="svg-icon text-red-500 mr-2" viewBox="0 0 24 24">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <h3 class="text-red-800 font-medium">Đã xảy ra lỗi. Vui lòng kiểm tra lại thông tin:</h3>
                </div>
                <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($hasBeenSold)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-4 mb-6 rounded-md shadow-md"
                role="alert">
                <div class="flex items-center">
                    <svg class="svg-icon text-yellow-500 mr-3 h-6 w-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <h3 class="font-medium">Lưu ý quan trọng</h3>
                </div>
                <p class="mt-2 text-sm ml-9">
                    Sản phẩm này đã có trong các đơn hàng cũ. Việc thay đổi thông tin (như tên, giá, SKU) sẽ chỉ áp dụng cho
                    các lần mua hàng <strong>mới</strong> và <strong>không</strong> làm thay đổi dữ liệu trong các đơn hàng
                    đã được tạo.
                </p>
            </div>
        @endif
        <form id="editProductForm" action="{{ route('admin.products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div id="image_ids_container" class="hidden"></div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-8 gap-y-6">
                {{-- Cột trái: Thông tin chính & Biến thể --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Card Thông Tin Chung --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            Thông Tin Chung
                        </div>
                        <div class="input-group">
                            <label for="name">Tên sản phẩm <span class="required-star">*</span></label>
                            <input type="text" id="name" name="name"
                                class="input-field @error('name') border-red-500 @enderror"
                                value="{{ old('name', $product->name) }}">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <label for="slug">Đường dẫn thân thiện (Slug)</label>
                            <input type="text" id="slug" name="slug"
                                class="input-field @error('slug') border-red-500 @enderror"
                                value="{{ old('slug', $product->slug) }}">
                            @error('slug')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="short_description">Mô tả ngắn</label>
                                <button type="button" id="generateShortDescAI" class="btn btn-ai btn-sm"><span
                                        class="button-text">✨ Tạo bằng AI</span><span
                                        class="loading-spinner hidden"></span></button>
                            </div>
                            <textarea id="short_description" name="short_description"
                                class="textarea-field @error('short_description') border-red-500 @enderror" rows="3">{{ old('short_description', $product->short_description) }}</textarea>
                            @error('short_description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="description">Mô tả chi tiết</label>
                                <button type="button" id="generateLongDescAI" class="btn btn-ai btn-sm"><span
                                        class="button-text">✨ Tạo bằng AI</span><span
                                        class="loading-spinner hidden"></span></button>
                            </div>
                            <textarea id="description" name="description" class="@error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Card Loại Sản Phẩm & Biến Thể --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24">
                                <circle cx="12" cy="18" r="3"></circle>
                                <circle cx="6" cy="6" r="3"></circle>
                                <circle cx="18" cy="6" r="3"></circle>
                                <path d="M18 9v1a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V9"></path>
                                <path d="M12 12v3"></path>
                            </svg>
                            Loại Sản Phẩm & Biến Thể
                        </div>
                        <div class="input-group">
                            <label>Loại sản phẩm</label>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center cursor-pointer p-2 rounded-md hover:bg-gray-50">
                                    <input type="radio" name="type" value="simple"
                                        class="form-check-input product-type-radio"
                                        {{ old('type', $product->type) == 'simple' ? 'checked' : '' }}>
                                    <span class="ml-2 font-medium text-gray-700">Đơn giản</span>
                                </label>
                                <label class="flex items-center cursor-pointer p-2 rounded-md hover:bg-gray-50">
                                    <input type="radio" name="type" value="variable"
                                        class="form-check-input product-type-radio"
                                        {{ old('type', $product->type) == 'variable' ? 'checked' : '' }}>
                                    <span class="ml-2 font-medium text-gray-700">Có biến thể</span>
                                </label>
                            </div>
                        </div>

                        {{-- Trường cho sản phẩm đơn giản --}}
                        <div id="simpleProductFields" class="space-y-4 mt-6 pt-4 border-t"
                            style="{{ old('type', $product->type) === 'simple' ? '' : 'display:none;' }}">
                            @php
                                $simpleVariant = $product->type === 'simple' ? $product->variants->first() : null;
                            @endphp
                            <h3 class="text-lg font-semibold text-gray-700">Thông tin sản phẩm đơn giản</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div class="input-group"><label for="simple_sku">SKU <span
                                            class="required-star">*</span></label><input type="text" id="simple_sku"
                                        name="simple_sku" class="input-field"
                                        value="{{ old('simple_sku', $simpleVariant?->sku ?? '') }}"></div>
                                <div class="input-group"><label for="simple_price">Giá bán <span
                                            class="required-star">*</span> (VNĐ)</label><input type="number"
                                        id="simple_price" name="simple_price" class="input-field"
                                        value="{{ old('simple_price', $simpleVariant?->price ?? '') }}"></div>
                                <div class="input-group">
                                    <div class="label-with-action">
                                        <label for="simple_sale_price">Giá khuyến mãi (VNĐ)</label>
                                        <a href="javascript:void(0);" onclick="toggleSchedule(this)"
                                            class="text-blue-600 text-sm">Lên lịch</a>
                                    </div>
                                    <input type="number" id="simple_sale_price" name="simple_sale_price"
                                        class="input-field"
                                        value="{{ old('simple_sale_price', $simpleVariant?->sale_price ?? '') }}">
                                </div>
                                <div class="input-group">
                                    <label for="simple_points_awarded_on_purchase">Điểm thưởng khi mua</label>
                                    <input type="number" id="simple_points_awarded_on_purchase" name="simple_points_awarded_on_purchase"
                                        class="input-field"
                                        value="{{ old('simple_points_awarded_on_purchase', $simpleVariant?->points_awarded_on_purchase ?? 0) }}">
                                </div>
                            </div>
                            <div
                                class="schedule-container {{ old('simple_sale_price_starts_at', $simpleVariant?->sale_price_starts_at) || old('simple_sale_price_ends_at', $simpleVariant?->sale_price_ends_at) ? '' : 'hidden' }} mt-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                    <div class="input-group">
                                        <label class="text-xs font-medium">Thời gian bắt đầu</label>
                                        <input type="datetime-local" name="simple_sale_price_starts_at"
                                            class="input-field"
                                            value="{{ old('simple_sale_price_starts_at', $simpleVariant?->sale_price_starts_at ? \Carbon\Carbon::parse($simpleVariant->sale_price_starts_at)->format('Y-m-d\TH:i') : '') }}">
                                    </div>
                                    <div class="input-group">
                                        <label class="text-xs font-medium">Thời gian kết thúc</label>
                                        <input type="datetime-local" name="simple_sale_price_ends_at" class="input-field"
                                            value="{{ old('simple_sale_price_ends_at', $simpleVariant?->sale_price_ends_at ? \Carbon\Carbon::parse($simpleVariant->sale_price_ends_at)->format('Y-m-d\TH:i') : '') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4 mt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                    <div class="input-group"><label for="simple_weight">Cân nặng (kg)</label><input
                                            type="number" step="0.01" min="0" name="simple_weight"
                                            class="input-field"
                                            value="{{ old('simple_weight', $simpleVariant?->weight ?? '') }}"></div>
                                    <div class="input-group">
                                        <label>Kích thước (D x R x C) (cm)</label>
                                        <div class="grid grid-cols-3 gap-x-2">
                                            <input type="number" step="0.1" min="0"
                                                name="simple_dimensions_length" placeholder="Dài" class="input-field"
                                                value="{{ old('simple_dimensions_length', $simpleVariant?->dimensions_length ?? '') }}">
                                            <input type="number" step="0.1" min="0"
                                                name="simple_dimensions_width" placeholder="Rộng" class="input-field"
                                                value="{{ old('simple_dimensions_width', $simpleVariant?->dimensions_width ?? '') }}">
                                            <input type="number" step="0.1" min="0"
                                                name="simple_dimensions_height" placeholder="Cao" class="input-field"
                                                value="{{ old('simple_dimensions_height', $simpleVariant?->dimensions_height ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="input-group md:col-span-2 pt-4 mt-4 border-t">
                                <label>Ảnh Sản Phẩm <span class="required-star">*</span></label>
                                <div class="flex space-x-2 mb-3">
                                    <label for="simple_product_image_input"
                                        class="form-section-heading btn btn-secondary btn-sm cursor-pointer"><i
                                            class="fas fa-upload mr-2"></i> Tải ảnh lên</label>
                                    <input type="file" id="simple_product_image_input" class="hidden"
                                        accept="image/*" multiple onchange="handleSimpleProductImages(event)">
                                    <button type="button" id="open-library-btn-simple"
                                        class="btn btn-secondary btn-sm"><i class="fas fa-photo-video mr-2"></i> Thêm từ
                                        thư viện</button>
                                </div>
                                <div id="simple_product_image_preview_container" class="image-preview-container mt-2">
                                </div>
                            </div>
                            {{-- Specifications container for simple product --}}
                            <div class="pt-4 mt-4 border-t border-gray-200">
                                <h4 class="text-md font-semibold text-gray-700 mb-2">Thông số kỹ thuật</h4>
                                <div id="simpleSpecificationsContainer" class="space-y-4">
                                    <p class="text-gray-500 text-sm">Vui lòng chọn danh mục để tải thông số.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Trường cho sản phẩm có biến thể --}}
                        <div id="variableProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200"
                            style="{{ old('type', $product->type) === 'variable' ? '' : 'display:none;' }}">
                            <h3 class="text-lg font-semibold text-gray-700 mb-1">Quản lý biến thể</h3>
                            <div class="input-group">
                                <label for="sku_prefix">Tiền tố SKU (cho biến thể)</label>
                                <input type="text" id="sku_prefix" name="sku_prefix" class="input-field"
                                    value="{{ old('sku_prefix', $product->sku_prefix) }}">
                            </div>
                            <div class="input-group">
                                <label>Thuộc tính sử dụng cho biến thể</label>
                                <div id="productAttributesContainer"
                                    class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 p-3 border border-gray-200 rounded-md bg-gray-50">
                                    {{-- Attributes populated by JS --}}
                                </div>
                            </div>
                            <div id="variantsContainer" class="space-y-5">
                                {{-- Variant cards will be added here by JS --}}
                            </div>
                            @if ($errors->has('variants') && is_string($errors->first('variants')))
                                <p class="text-red-500 text-xs mt-1">{{ $errors->first('variants') }}</p>
                            @endif
                            <button type="button" id="addVariantButton" class="btn btn-secondary mt-2">
                                <svg class="svg-icon mr-2 h-5 w-5" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                Thêm Biến Thể Mới
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Tổ chức, SEO, etc. --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24">
                                <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
                                <rect x="9" y="9" width="6" height="6"></rect>
                                <line x1="9" y1="1" x2="9" y2="4"></line>
                                <line x1="15" y1="1" x2="15" y2="4"></line>
                                <line x1="9" y1="20" x2="9" y2="23"></line>
                                <line x1="15" y1="20" x2="15" y2="23"></line>
                                <line x1="20" y1="9" x2="23" y2="9"></line>
                                <line x1="20" y1="14" x2="23" y2="14"></line>
                                <line x1="1" y1="9" x2="4" y2="9"></line>
                                <line x1="1" y1="14" x2="4" y2="14"></line>
                            </svg>
                            Xuất Bản
                        </div>
                        <div class="input-group">
                            <label for="status">Trạng thái <span class="required-star">*</span></label>
                            <select id="status" name="status" class="select-field">
                                <option value="published"
                                    {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Đã xuất bản
                                </option>
                                <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>
                                    Bản nháp</option>
                                <option value="pending_review"
                                    {{ old('status', $product->status) == 'pending_review' ? 'selected' : '' }}>Chờ duyệt
                                </option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group mt-5">
                            <label
                                class="flex items-center cursor-pointer p-2 rounded-md hover:bg-blue-50 transition-colors">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                    {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                    class="form-check-input">
                                <div class="flex items-center ml-2">
                                    <svg class="svg-icon h-5 w-5 text-yellow-500" viewBox="0 0 24 24">
                                        <polygon
                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                                        </polygon>
                                    </svg>
                                    <span class="ml-2 text-gray-700 font-medium">Sản phẩm nổi bật</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            Tổ Chức
                        </div>
                        <div class="input-group">
                            <label for="category_id">Danh mục <span class="required-star">*</span></label>
                            <select id="category_id" name="category_id" class="select-field">
                                <option value="">Chọn danh mục</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="tags">Thẻ sản phẩm (Tags)</label>
                                <button type="button" id="generateTagsAI" class="btn btn-ai btn-sm"><span
                                        class="button-text">✨ Gợi ý</span><span
                                        class="loading-spinner hidden"></span></button>
                            </div>
                            <input type="text" id="tags" name="tags"
                                value="{{ old('tags', $product->tags) }}">
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <path d="M11 8l2 2"></path>
                            </svg>
                            Tối Ưu Hóa SEO
                            <button type="button" id="generateAllSeoAI" class="btn btn-ai btn-sm ml-auto"><span
                                    class="button-text">✨ Tạo Tất Cả SEO</span><span
                                    class="loading-spinner hidden"></span></button>
                        </div>
                        <div class="input-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title" class="input-field"
                                value="{{ old('meta_title', $product->meta_title) }}">
                        </div>
                        <div class="input-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" class="textarea-field" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" class="input-field"
                                value="{{ old('meta_keywords', $product->meta_keywords) }}">
                        </div>
                    </div>
                    {{-- Card Thông Tin Bổ Sung --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>Thông Tin Bổ Sung
                        </div>
                        <div class="input-group">
                            <label for="warranty_information">Thông tin bảo hành</label>
                            <textarea id="warranty_information" name="warranty_information"
                                class="textarea-field @error('warranty_information') border-red-500 @enderror" rows="3">{{ old('warranty_information', $product->warranty_information) }}</textarea>
                            @error('warranty_information')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary w-full sm:w-auto"><svg
                        class="svg-icon mr-2 h-5 w-5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg> Hủy Bỏ</a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto"><svg class="svg-icon mr-2 h-5 w-5"
                        viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg> Cập Nhật Sản Phẩm</button>
            </div>
        </form>

        {{-- MODAL XÁC NHẬN CHUYỂN ĐỔI --}}
        <div id="typeSwitchConfirmationModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center hidden z-[1060]">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-xl font-bold text-red-600 flex items-center">
                    <svg class="svg-icon h-6 w-6 mr-2 text-red-500" viewBox="0 0 24 24">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Cảnh báo mất dữ liệu!
                </h3>
                <p class="mt-3 text-gray-600">Bạn có chắc chắn muốn chuyển sang sản phẩm "Đơn giản" không? <br>Hành động
                    này sẽ <strong class="text-red-700 font-semibold">xóa vĩnh viễn tất cả các biến thể hiện có</strong>.
                    <br><br>Thông tin từ biến thể mặc định sẽ được sao chép qua. Hành động này không thể hoàn tác.
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <button id="cancelTypeSwitch" class="btn btn-secondary">Hủy bỏ</button>
                    <button id="confirmTypeSwitch" class="btn btn-danger">Xác nhận & Chuyển đổi</button>
                </div>
            </div>
        </div>

        {{-- MODAL XÁC NHẬN TẢI LẠI TRANG --}}
        <div id="reloadConfirmationModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center hidden z-[1060]">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="svg-icon h-6 w-6 mr-2 text-blue-500" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6" />
                        <path d="M22 11.5A10 10 0 0 0 3.5 12.5" />
                        <path d="M2 12.5a10 10 0 0 0 18.5-1" />
                    </svg>
                    Xác nhận tải lại trang
                </h3>
                <p class="mt-3 text-gray-600">Bạn có muốn tải lại trang để khôi phục dữ liệu biến thể gốc không? Mọi thay
                    đổi chưa lưu sẽ bị mất.</p>
                <div class="mt-6 flex justify-end space-x-4">
                    <button id="cancelReload" class="btn btn-secondary">Hủy bỏ</button>
                    <button id="confirmReload" class="btn btn-primary">Tải lại trang</button>
                </div>
            </div>
        </div>

        {{-- Modal for messages --}}
        <div id="messageModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden px-4 z-50">
            <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div id="messageModalIconContainer"
                        class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100"></div>
                    <h3 id="messageModalTitle" class="text-lg leading-6 font-medium text-gray-900 mt-2">Thông báo</h3>
                    <div class="mt-2 px-7 py-3">
                        <p id="messageModalText" class="text-sm text-gray-500"></p>
                    </div>
                    <div class="items-center px-4 py-3"><button id="messageModalCloseButton"
                            class="btn btn-primary w-full">Đã hiểu</button></div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.media_selection_modal')
@endsection

@push('scripts')
    {{-- **ĐÃ THAY THẾ TINYMCE BẰNG CKEDITOR** --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

    <script>
        // =================================================================
        // KHỞI TẠO BIẾN TOÀN CỤC VÀ DỮ LIỆU TỪ PHP
        // =================================================================
        let variantIndexGlobal = 0;
        let tagify;
        let longDescriptionEditor; // **BIẾN MỚI CHO CKEDITOR**
        window.mediaLibraryTarget = null;
        let selectedProductAttributes = [];
        let fetchedSpecifications = [];

        @php
            $product->load('variants.attributeValues.attribute', 'variants.images', 'variants.specifications', 'coverImage', 'galleryImages');
            $jsProduct = $product->toArray();
            $jsAttributes = $attributes
                ->map(function ($attr) {
                    return [
                        'id' => $attr->id,
                        'name' => $attr->name,
                        'slug' => $attr->slug,
                        'attributeValues' => $attr->attributeValues->map(fn($val) => ['id' => $val->id, 'value' => $val->value, 'meta' => $val->meta])->values()->all(),
                    ];
                })
                ->values()
                ->all();
            $oldInput = session()->getOldInput();
        @endphp

        const allAttributesFromPHP = @json($jsAttributes, JSON_UNESCAPED_UNICODE);
        const productBeingEdited = @json($jsProduct, JSON_UNESCAPED_UNICODE);
        const oldImagesData = @json($old_images_data ?? [], JSON_UNESCAPED_UNICODE);
        const oldData = @json($oldInput, JSON_UNESCAPED_UNICODE);
        let currentProductType = productBeingEdited.type;
        const originalProductTypeFromServer = productBeingEdited.type;

        // =================================================================
        // CÁC HÀM TIỆN ÍCH, AI, UPLOAD
        // =================================================================
        
        function showMessageModal(title, text, type = 'info') {
            const modal = document.getElementById('messageModal');
            const titleEl = document.getElementById('messageModalTitle');
            const textEl = document.getElementById('messageModalText');
            const iconContainer = document.getElementById('messageModalIconContainer');
            if (!modal || !titleEl || !textEl || !iconContainer) return;
            titleEl.textContent = title;
            textEl.innerHTML = text;
            let iconSvg, iconBgClass;
            switch (type) {
                case 'success':
                    iconSvg =
                        '<svg class="h-6 w-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                    iconBgClass = 'bg-green-100';
                    break;
                case 'error':
                    iconSvg =
                        '<svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
                    iconBgClass = 'bg-red-100';
                    break;
                default:
                    iconSvg =
                        '<svg class="h-6 w-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
                    iconBgClass = 'bg-blue-100';
            }
            iconContainer.className = `mx-auto flex items-center justify-center h-12 w-12 rounded-full ${iconBgClass}`;
            iconContainer.innerHTML = iconSvg;
            modal.classList.remove('hidden');
            document.getElementById('messageModalCloseButton').onclick = () => modal.classList.add('hidden');
        }

        function toggleButtonLoading(button, isLoading) {
            if (!button) return;
            const textSpan = button.querySelector('.button-text');
            const spinnerSpan = button.querySelector('.loading-spinner');
            if (textSpan && spinnerSpan) {
                textSpan.classList.toggle('hidden', isLoading);
                spinnerSpan.classList.toggle('hidden', !isLoading);
            }
            button.disabled = isLoading;
        }

        function slugify(text) {
            if (!text) return '';
            return text.toString().toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd')
                .replace(/\s+/g, '-').replace(/[^\w-]+/g, '').replace(/--+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
        }

        async function uploadFilesViaAjax(files, context = 'products') {
            const formData = new FormData();
            Array.from(files).forEach(file => formData.append('files[]', file));
            formData.append('context', context);
            try {
                const response = await fetch("{{ route('admin.media.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Upload không thành công.');
                return result;
            } catch (error) {
                console.error('Lỗi khi upload ảnh:', error);
                showMessageModal('Lỗi Upload', error.message, 'error');
                return null;
            }
        }

        function getProductContext() {
            const productName = document.getElementById('name')?.value.trim();
            if (!productName) {
                showMessageModal("Thiếu thông tin", "Vui lòng nhập tên sản phẩm trước khi sử dụng AI.", "error");
                return null;
            }
            const categorySelect = document.getElementById('category_id');
            const categoryName = categorySelect?.options[categorySelect.selectedIndex]?.text.replace(/--/g, '').trim() ||
                "";
            return `Sản phẩm: ${productName}, thuộc danh mục: ${categoryName}.`;
        }

        async function callGeminiAPI(prompt, isStructured = false, schema = null) {
            const backendApiUrl = "{{ route('admin.products.ai.generate') }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '{{ csrf_token() }}';
            let payload = {
                prompt,
                isStructured,
                schema
            };

            try {
                const response = await fetch(backendApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || `Request failed with status ${response.status}`);
                }
                const result = await response.json();
                if (result.candidates && result.candidates[0]?.content?.parts?.[0]) {
                    const responseText = result.candidates[0].content.parts[0].text;
                    if (isStructured) {
                        try {
                            const cleanedJsonString = responseText.replace(/```json|```/g, '').trim();
                            return JSON.parse(cleanedJsonString);
                        } catch (e) {
                            console.error("Failed to parse JSON from AI response:", e);
                            throw new Error("AI returned invalid JSON format.");
                        }
                    }
                    return responseText;
                } else {
                    if (result.promptFeedback && result.promptFeedback.blockReason) {
                        throw new Error(`AI block prompt. Lý do: ${result.promptFeedback.blockReason}`);
                    }
                    throw new Error("Không nhận được nội dung hợp lệ từ AI.");
                }
            } catch (error) {
                console.error("Error calling backend for Gemini API:", error);
                showMessageModal("Lỗi Hệ Thống", `Không thể kết nối đến máy chủ AI: ${error.message}`, "error");
                return null;
            }
        }

        // =================================================================
        // LOGIC THÔNG SỐ KỸ THUẬT (SPECIFICATIONS)
        // =================================================================

        function mapSpecsToObject(specsArray) {
            if (!Array.isArray(specsArray)) return {};
            return specsArray.reduce((acc, item) => {
                if (item && item.id && item.pivot) { // Adjusted for direct variant.specifications
                    acc[item.id] = item.pivot.value;
                } else if (item && item.specification && item.specification.id && item.pivot) { // For old structure if needed
                     acc[item.specification.id] = item.pivot.value;
                }
                return acc;
            }, {});
        }

        function renderSpecifications(container, namePrefix, existingData = {}) {
            if (!container) return;
            container.innerHTML = ''; 
            if (!fetchedSpecifications || fetchedSpecifications.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">Danh mục này không có thông số kỹ thuật.</p>';
                return;
            }

            fetchedSpecifications.forEach(group => {
                const groupEl = document.createElement('div');
                groupEl.className = 'spec-group-container mb-4';
                let groupHTML =
                    `<h5 class="text-sm font-semibold text-gray-600 mb-3 mt-3">${group.name}</h5><div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">`;
                if (group.specifications && group.specifications.length) {
                    group.specifications.forEach(spec => {
                        const inputName = `${namePrefix}[${spec.id}]`;
                        const oldValue = existingData[spec.id] || '';
                        groupHTML += `
                        <div class="input-group !mb-0">
                            <label for="${namePrefix}_spec_${spec.id}" class="!text-xs !font-normal !text-gray-500">${spec.name}</label>
                            <input type="text" id="${namePrefix}_spec_${spec.id}" name="${inputName}" class="input-field !py-2 !text-sm" value="${oldValue}">
                        </div>`;
                    });
                }
                groupHTML += '</div>';
                groupEl.innerHTML = groupHTML;
                container.appendChild(groupEl);
            });
        }

        async function handleCategoryChange() {
            const categoryId = document.getElementById('category_id').value;
            const simpleSpecContainer = document.getElementById('simpleSpecificationsContainer');

            if (!categoryId) {
                fetchedSpecifications = [];
                if (simpleSpecContainer) renderSpecifications(simpleSpecContainer, 'specifications');
                document.querySelectorAll('.variant-specifications-container').forEach(container => {
                    const variantIndex = container.closest('.variant-card').dataset.variantIndex;
                    renderSpecifications(container, `variants[${variantIndex}][specifications]`);
                });
                return;
            }

            try {
                const response = await fetch(`{{ url('admin/api/specifications-by-category') }}/${categoryId}`);
                if (!response.ok) throw new Error('Network response was not ok');
                fetchedSpecifications = await response.json();

                if (simpleSpecContainer && document.getElementById('simpleProductFields').style.display !== 'none') {
                    const simpleVariant = productBeingEdited.variants.length > 0 ? productBeingEdited.variants[0] : null;
                    const existingSimpleSpecs = simpleVariant ? mapSpecsToObject(simpleVariant.specifications) : {};
                    renderSpecifications(simpleSpecContainer, 'specifications', existingSimpleSpecs);
                }

                document.querySelectorAll('.variant-card').forEach(card => {
                    const variantIndex = card.dataset.variantIndex;
                    const variantSpecContainer = card.querySelector('.variant-specifications-container');
                    const variantIdInput = card.querySelector(`input[name="variants[${variantIndex}][id]"]`);
                    const variantId = variantIdInput ? variantIdInput.value : null;
                    
                    let variantData = null;
                    if (variantId) {
                        variantData = productBeingEdited.variants.find(v => v.id == variantId);
                    }
                    const existingVariantSpecs = variantData ? mapSpecsToObject(variantData.specifications) : {};
                    renderSpecifications(variantSpecContainer, `variants[${variantIndex}][specifications]`, existingVariantSpecs);
                });

            } catch (error) {
                console.error('Error fetching specifications:', error);
                fetchedSpecifications = [];
                showMessageModal('Lỗi', 'Không thể tải thông số kỹ thuật.', 'error');
            }
        }

        function copySpecificationsFrom(sourceIndex, targetIndex) {
            const sourceCard = document.querySelector(`.variant-card[data-variant-index="${sourceIndex}"]`);
            const targetCard = document.querySelector(`.variant-card[data-variant-index="${targetIndex}"]`);
            if (!sourceCard || !targetCard) return;

            const sourceInputs = sourceCard.querySelectorAll('.variant-specifications-container input[type="text"]');
            sourceInputs.forEach(sourceInput => {
                const match = sourceInput.name.match(/\[specifications\]\[(\d+)\]$/);
                if (match && match[1]) {
                    const specId = match[1];
                    const targetInput = targetCard.querySelector(
                        `.variant-specifications-container input[name$="[${specId}]"]`);
                    if (targetInput) {
                        targetInput.value = sourceInput.value;
                    }
                }
            });
            showMessageModal('Thành công',
                `Đã sao chép thông số từ Biến thể #${parseInt(sourceIndex) + 1} sang Biến thể #${parseInt(targetIndex) + 1}.`,
                'success');
        }

        function updateAllCopySpecButtons() {
            const allVariantCards = Array.from(document.querySelectorAll('.variant-card'));
            allVariantCards.forEach(targetCard => {
                const targetIndex = targetCard.dataset.variantIndex;
                const dropdownContent = targetCard.querySelector('.action-dropdown-content');
                if (!dropdownContent) return;

                dropdownContent.innerHTML = ''; 

                const otherVariants = allVariantCards.filter(card => card.dataset.variantIndex !== targetIndex);

                if (otherVariants.length > 0) {
                    const header = document.createElement('div');
                    header.className = 'dropdown-header';
                    header.textContent = 'Sao chép thông số từ';
                    dropdownContent.appendChild(header);

                    otherVariants.forEach(sourceCard => {
                        const sourceIndex = sourceCard.dataset.variantIndex;
                        const link = document.createElement('a');
                        link.href = 'javascript:void(0);';
                        link.textContent = `Biến thể #${parseInt(sourceIndex) + 1}`;
                        link.onclick = () => copySpecificationsFrom(sourceIndex, targetIndex);
                        dropdownContent.appendChild(link);
                    });
                } else {
                    const disabledLink = document.createElement('a');
                    disabledLink.className = 'disabled';
                    disabledLink.textContent = 'Không có biến thể khác';
                    dropdownContent.appendChild(disabledLink);
                }
            });
        }
        
        // =================================================================
        // XỬ LÝ MEDIA VÀ HÌNH ẢNH
        // =================================================================
        async function handleSimpleProductImages(event) {
            const result = await uploadFilesViaAjax(event.target.files);
            if (result && result.files) {
                const previewContainer = document.getElementById('simple_product_image_preview_container');
                const idsContainer = document.getElementById('image_ids_container');
                addImagesToProductForm(result.files, previewContainer, idsContainer, 'simple');
            }
            event.target.value = '';
        }

        async function handleVariantImages(event, variantIndex) {
            const result = await uploadFilesViaAjax(event.target.files);
            if (result && result.files) {
                const previewContainer = document.getElementById(`variant_${variantIndex}_image_preview_container`);
                const idsContainer = document.getElementById(`variant_${variantIndex}_image_ids_container`);
                addImagesToProductForm(result.files, previewContainer, idsContainer, 'variant', variantIndex);
            }
            event.target.value = '';
        }

        function addImagesToProductForm(images, previewContainer, idsContainer, type = 'simple', variantIndex = null) {
            if (!images || images.length === 0 || !previewContainer || !idsContainer) return;
            const isPrimarySet = (type === 'simple') ?
                !!idsContainer.querySelector('input[name="cover_image_id"]') :
                !!idsContainer.querySelector(`input[name="variants[${variantIndex}][primary_image_id]"]`);

            images.forEach(fileData => {
                const image = fileData.file ? fileData.file : fileData;
                if (!image.url && image.path) image.url = "{{ url('storage') }}/" + image.path;
                if (!image.url || previewContainer.querySelector(
                        `.variant-image-preview-item[data-id="${image.id}"]`)) return;

                const galleryInput = document.createElement('input');
                galleryInput.type = 'hidden';
                galleryInput.dataset.id = image.id;
                galleryInput.value = image.id;
                galleryInput.name = (type === 'simple') ? 'gallery_images[]' :
                    `variants[${variantIndex}][image_ids][]`;
                idsContainer.appendChild(galleryInput);

                const previewDiv = document.createElement('div');
                previewDiv.className = 'variant-image-preview-item';
                previewDiv.dataset.id = image.id;
                const removeFunc = (type === 'simple') ? `removeSimpleProductImage(${image.id})` :
                    `removeVariantImage(this, ${variantIndex}, ${image.id})`;
                const setPrimaryFunc = (type === 'simple') ? `setSimpleProductPrimaryImage(${image.id})` :
                    `setVariantPrimaryImage(${variantIndex}, ${image.id})`;
                previewDiv.innerHTML = `
                <img src="${image.url}" alt="${image.alt_text || 'Ảnh sản phẩm'}">
                <span class="remove-img-btn" onclick="${removeFunc}">×</span>
                <button type="button" class="set-primary-btn" title="Đặt làm ảnh chính" onclick="${setPrimaryFunc}"><i class="fas fa-star" style="color: white; pointer-events: none;"></i> Đặt chính</button>
            `;
                previewContainer.appendChild(previewDiv);
            });

            if (!isPrimarySet && previewContainer.querySelectorAll('.variant-image-preview-item').length > 0) {
                const firstImageId = parseInt(previewContainer.querySelector('.variant-image-preview-item').dataset.id);
                if (type === 'simple') setSimpleProductPrimaryImage(firstImageId);
                else setVariantPrimaryImage(variantIndex, firstImageId);
            }
        }

        function setPrimaryImage(primaryImageId, previewContainer, idsContainer, inputName) {
            if (!previewContainer || !idsContainer) return;
            previewContainer.querySelectorAll('.variant-image-preview-item').forEach(p => p.classList.toggle('is-primary',
                parseInt(p.dataset.id) === primaryImageId));
            let oldInput = idsContainer.querySelector(`input[name="${inputName}"]`);
            if (oldInput) oldInput.remove();
            const newInput = document.createElement('input');
            newInput.type = 'hidden';
            newInput.name = inputName;
            newInput.value = primaryImageId;
            idsContainer.appendChild(newInput);
        }

        function setSimpleProductPrimaryImage(id) {
            setPrimaryImage(id, document.getElementById('simple_product_image_preview_container'), document.getElementById(
                'image_ids_container'), 'cover_image_id');
        }

        function setVariantPrimaryImage(index, id) {
            setPrimaryImage(id, document.getElementById(`variant_${index}_image_preview_container`), document
                .getElementById(`variant_${index}_image_ids_container`), `variants[${index}][primary_image_id]`);
        }

        function removeImage(imageId, previewContainer, idsContainer, primaryInputName, setPrimaryFunc) {
            if (!previewContainer || !idsContainer) return;
            previewContainer.querySelector(`.variant-image-preview-item[data-id="${imageId}"]`)?.remove();
            idsContainer.querySelector(`input[data-id="${imageId}"]`)?.remove();

            const primaryInput = idsContainer.querySelector(`input[name="${primaryInputName}"]`);
            if (primaryInput && parseInt(primaryInput.value) === imageId) {
                primaryInput.remove();
                const remaining = previewContainer.querySelectorAll('.variant-image-preview-item');
                if (remaining.length > 0) {
                    const newPrimaryId = parseInt(remaining[0].dataset.id);
                    setPrimaryFunc(newPrimaryId);
                }
            }
        }

        function removeSimpleProductImage(id) {
            removeImage(id,
                document.getElementById('simple_product_image_preview_container'),
                document.getElementById('image_ids_container'),
                'cover_image_id',
                setSimpleProductPrimaryImage
            );
        }

        function removeVariantImage(btn, index, id) {
            const idsContainer = document.getElementById(`variant_${index}_image_ids_container`);
            removeImage(id,
                btn.closest('.image-preview-container'),
                idsContainer,
                `variants[${index}][primary_image_id]`,
                (newId) => setVariantPrimaryImage(index, newId)
            );
        }


        // =================================================================
        // LOGIC FORM VÀ BIẾN THỂ
        // =================================================================
        function toggleSchedule(element) {
            const sectionContainer = element.closest('#simpleProductFields, .variant-card');
            if (sectionContainer) {
                const scheduleContainer = sectionContainer.querySelector('.schedule-container');
                if (scheduleContainer) {
                    scheduleContainer.classList.toggle('hidden');
                }
            }
        }

        function updateSelectedAttributesForVariants() {
            selectedProductAttributes = Array.from(document.querySelectorAll('.product-attribute-checkbox:checked'))
                .map(checkbox => {
                    const attrId = parseInt(checkbox.value);
                    return allAttributesFromPHP.find(a => a && a.id === attrId);
                })
                .filter(Boolean);
        }

        function handleDefaultVariantChange(event) {
            document.querySelectorAll('.variant-default-radio').forEach(radio => {
                const hiddenInput = radio.nextElementSibling;
                if (hiddenInput) hiddenInput.value = (radio === event.target && radio.checked) ? "true" : "false";
            });
        }

        function updateDefaultVariantRadioAndHiddenFields() {
            const radios = document.querySelectorAll('.variant-default-radio');
            if (!radios.length) return;
            const oneIsChecked = Array.from(radios).some(r => r.checked);
            if (!oneIsChecked) radios[0].checked = true;
            radios.forEach(radio => {
                const hiddenInput = radio.nextElementSibling;
                if (hiddenInput) hiddenInput.value = radio.checked ? "true" : "false";
            });
        }

        // =================================================================
        // HÀM TẠO MỚI MỘT CARD BIẾN THỂ (ĐÃ BỎ TỒN KHO)
        // =================================================================
        function addVariantCard(variantData = {}) {
            const currentVariantIndex = variantIndexGlobal;
            if (selectedProductAttributes.length === 0 && !Object.keys(variantData).length && document.getElementById('variableProductFields').style.display !== 'none') {
                showMessageModal('Thông báo', 'Vui lòng chọn ít nhất một thuộc tính cho sản phẩm trước khi thêm biến thể.', 'info');
                return;
            }
            const variantCard = document.createElement('div');
            variantCard.className = 'variant-card';
            variantCard.dataset.variantIndex = currentVariantIndex;
            
            let attributesHTML = '<div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-4">';
            selectedProductAttributes.forEach(attr => {
                const selectedValue = variantData.attribute_values ? (variantData.attribute_values.find(v => v.attribute_id === attr.id)?.id || '') : (variantData.attributes ? variantData.attributes[attr.id] : '');
                attributesHTML += `<div class="input-group"><label class="text-sm font-medium">${attr.name} <span class="required-star">*</span></label><div><select name="variants[${currentVariantIndex}][attributes][${attr.id}]" class="select-field text-sm"><option value="">Chọn ${attr.name}</option>${attr.attributeValues.map(val => `<option value="${val.id}" ${selectedValue == val.id ? 'selected' : ''}>${val.value}</option>`).join('')}</select></div></div>`;
            });
            attributesHTML += '</div>';

            const startsAtValue = variantData.sale_price_starts_at ? new Date(variantData.sale_price_starts_at).toISOString().slice(0, 16) : '';
            const endsAtValue = variantData.sale_price_ends_at ? new Date(variantData.sale_price_ends_at).toISOString().slice(0, 16) : '';

            variantCard.innerHTML = `
                <div class="variant-header">
                    <div class="flex items-center">
                        <h4 class="variant-title">Biến Thể #${currentVariantIndex + 1}</h4>
                        ${variantData.id ? `<input type="hidden" name="variants[${currentVariantIndex}][id]" value="${variantData.id}">` : ''}
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="action-dropdown">
                            <button type="button" class="btn btn-secondary btn-sm !uppercase !font-medium">Tùy chọn</button>
                            <div class="action-dropdown-content"></div>
                        </div>
                        <button type="button" class="remove-variant-btn btn btn-danger btn-sm">Xóa</button>
                    </div>
                </div>
                ${attributesHTML}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="input-group"><label class="text-sm font-medium">SKU <span class="required-star">*</span></label><div><input type="text" name="variants[${currentVariantIndex}][sku]" class="input-field text-sm" value="${variantData.sku || ''}"></div></div>
                    <div class="input-group"><label class="text-sm font-medium">Giá <span class="required-star">*</span> (VNĐ)</label><div><input type="number" name="variants[${currentVariantIndex}][price]" class="input-field text-sm" step="1000" min="0" value="${variantData.price || ''}"></div></div>
                    <div class="input-group">
                        <div class="label-with-action"><label class="text-sm font-medium">Giá KM (VNĐ)</label><a href="javascript:void(0);" onclick="toggleSchedule(this)" class="text-blue-600 text-sm font-medium">Lên lịch</a></div>
                        <div><input type="number" name="variants[${currentVariantIndex}][sale_price]" class="input-field text-sm" step="1000" min="0" value="${variantData.sale_price || ''}"></div>
                    </div>
                    <div class="input-group"><label class="text-sm font-medium">Điểm thưởng khi mua</label><div><input type="number" name="variants[${currentVariantIndex}][points_awarded_on_purchase]" class="input-field text-sm" min="0" value="${variantData.points_awarded_on_purchase || 0}"></div></div>
                </div>
                <div class="schedule-container ${variantData.sale_price_starts_at || variantData.sale_price_ends_at ? '' : 'hidden'} mt-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div class="input-group"><label class="text-xs font-medium">Thời gian bắt đầu</label><input type="datetime-local" name="variants[${currentVariantIndex}][sale_price_starts_at]" class="input-field" value="${startsAtValue}"></div>
                        <div class="input-group"><label class="text-xs font-medium">Thời gian kết thúc</label><input type="datetime-local" name="variants[${currentVariantIndex}][sale_price_ends_at]" class="input-field" value="${endsAtValue}"></div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-4 pt-4 border-t border-gray-200">
                    <div class="input-group"><label class="text-sm font-medium">Cân nặng (kg)</label><input type="number" step="0.01" min="0" name="variants[${currentVariantIndex}][weight]" class="input-field text-sm" value="${variantData.weight || ''}"></div>
                    <div class="input-group"><label class="text-sm font-medium">Kích thước (D x R x C) (cm)</label><div class="grid grid-cols-3 gap-x-2"><input type="number" step="0.1" min="0" name="variants[${currentVariantIndex}][dimensions_length]" placeholder="Dài" class="input-field text-sm" value="${variantData.dimensions_length || ''}"><input type="number" step="0.1" min="0" name="variants[${currentVariantIndex}][dimensions_width]" placeholder="Rộng" class="input-field text-sm" value="${variantData.dimensions_width || ''}"><input type="number" step="0.1" min="0" name="variants[${currentVariantIndex}][dimensions_height]" placeholder="Cao" class="input-field text-sm" value="${variantData.dimensions_height || ''}"></div></div>
                </div>
                <div class="input-group md:col-span-2 mt-4 pt-4 border-t border-gray-200">
                    <label class="text-sm font-medium">Ảnh Biến Thể</label>
                    <div class="flex space-x-2 mb-3">
                        <label for="variant_${currentVariantIndex}_image_input" class="form-section-heading btn btn-secondary btn-sm cursor-pointer"><i class="fas fa-upload mr-2"></i> Tải ảnh lên</label>
                        <input type="file" id="variant_${currentVariantIndex}_image_input" class="hidden" accept="image/*" multiple onchange="handleVariantImages(event, ${currentVariantIndex})">
                        <button type="button" class="btn btn-secondary btn-sm open-library-btn-variant" data-variant-index="${currentVariantIndex}"><i class="fas fa-photo-video mr-2"></i>Thêm từ thư viện</button>
                    </div>
                    <div id="variant_${currentVariantIndex}_image_preview_container" class="image-preview-container mt-2"></div>
                    <div id="variant_${currentVariantIndex}_image_ids_container" class="hidden"></div>
                </div>
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <h4 class="text-md font-semibold text-gray-700 mb-2">Thông số kỹ thuật biến thể</h4>
                    <div class="variant-specifications-container space-y-4" id="variant_${currentVariantIndex}_specifications_container">
                        <p class="text-gray-500 text-sm">Vui lòng chọn danh mục để tải thông số.</p>
                    </div>
                </div>
                <div class="mt-4"><label class="flex items-center text-sm cursor-pointer"><input type="radio" name="variant_is_default_radio_group" value="${currentVariantIndex}" class="form-check-input mr-2 variant-default-radio" ${variantData.is_default ? 'checked' : ''}><input type="hidden" name="variants[${currentVariantIndex}][is_default]" value="${variantData.is_default ? 'true' : 'false'}" class="is-default-hidden-input"> Đặt làm mặc định</label></div>
            `;

            document.getElementById('variantsContainer').appendChild(variantCard);
            
            const newSpecContainer = document.getElementById(`variant_${currentVariantIndex}_specifications_container`);
            const existingSpecs = mapSpecsToObject(variantData.specifications);
            renderSpecifications(newSpecContainer, `variants[${currentVariantIndex}][specifications]`, existingSpecs);

            variantCard.querySelector('.remove-variant-btn').addEventListener('click', function() {
                this.closest('.variant-card').remove();
                updateDefaultVariantRadioAndHiddenFields();
                updateAllCopySpecButtons();
            });
            variantCard.querySelector('.variant-default-radio').addEventListener('change', handleDefaultVariantChange);
            variantIndexGlobal++;
            updateDefaultVariantRadioAndHiddenFields();
            updateAllCopySpecButtons();
        }

        // =================================================================
        // LOGIC CHUYỂN ĐỔI LOẠI SẢN PHẨM (ĐÃ BỎ TỒN KHO)
        // =================================================================
        function performTypeSwitch(newType) {
            const simpleFieldsDiv = document.getElementById('simpleProductFields');
            const variableFieldsDiv = document.getElementById('variableProductFields');
            const variantsContainer = document.getElementById('variantsContainer');

            if (newType === 'simple') {
                variableFieldsDiv.style.display = 'none';
                simpleFieldsDiv.style.display = 'block';

                const defaultVariantRadio = variantsContainer.querySelector('.variant-default-radio:checked');
                const sourceVariantCard = defaultVariantRadio ? defaultVariantRadio.closest('.variant-card') :
                    variantsContainer.querySelector('.variant-card');

                if (sourceVariantCard) {
                    const sourceIndex = sourceVariantCard.dataset.variantIndex;
                    
                    // Sao chép dữ liệu
                    document.getElementById('simple_sku').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][sku]"]`)?.value || '';
                    document.getElementById('simple_price').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][price]"]`)?.value || '';
                    document.querySelector('input[name="simple_sale_price"]').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][sale_price]"]`)?.value || '';
                    document.querySelector('input[name="simple_sale_price_starts_at"]').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][sale_price_starts_at]"]`)?.value || '';
                    document.querySelector('input[name="simple_sale_price_ends_at"]').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][sale_price_ends_at]"]`)?.value || '';
                    document.querySelector('input[name="simple_weight"]').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][weight]"]`)?.value || '';
                    document.querySelector('input[name="simple_dimensions_length"]').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][dimensions_length]"]`)?.value || '';
                    document.querySelector('input[name="simple_dimensions_width"]').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][dimensions_width]"]`)?.value || '';
                    document.querySelector('input[name="simple_dimensions_height"]').value = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][dimensions_height]"]`)?.value || '';

                    const simplePreviewContainer = document.getElementById('simple_product_image_preview_container');
                    const simpleIdsContainer = document.getElementById('image_ids_container');
                    simplePreviewContainer.innerHTML = '';
                    simpleIdsContainer.innerHTML = '';

                    const variantImagePreviews = sourceVariantCard.querySelectorAll('.variant-image-preview-item');
                    if (variantImagePreviews.length > 0) {
                        const imagesToCopy = Array.from(variantImagePreviews).map(preview => ({
                            id: preview.dataset.id,
                            url: preview.querySelector('img').src,
                            alt_text: preview.querySelector('img').alt
                        }));
                        const primaryId = sourceVariantCard.querySelector(`input[name="variants[${sourceIndex}][primary_image_id]"]`)?.value;

                        addImagesToProductForm(imagesToCopy, simplePreviewContainer, simpleIdsContainer, 'simple');
                        if (primaryId) setSimpleProductPrimaryImage(parseInt(primaryId));
                    }
                }

                variantsContainer.innerHTML = '';
                variantIndexGlobal = 0;
                updateAllCopySpecButtons();

            } else if (newType === 'variable') {
                simpleFieldsDiv.style.display = 'none';
                variableFieldsDiv.style.display = 'block';

                const firstVariantData = {
                    sku: document.getElementById('simple_sku').value,
                    price: document.getElementById('simple_price').value,
                    sale_price: document.querySelector('input[name="simple_sale_price"]').value,
                    sale_price_starts_at: document.querySelector('input[name="simple_sale_price_starts_at"]').value,
                    sale_price_ends_at: document.querySelector('input[name="simple_sale_price_ends_at"]').value,
                    weight: document.querySelector('input[name="simple_weight"]').value,
                    dimensions_length: document.querySelector('input[name="simple_dimensions_length"]').value,
                    dimensions_width: document.querySelector('input[name="simple_dimensions_width"]').value,
                    dimensions_height: document.querySelector('input[name="simple_dimensions_height"]').value,
                    is_default: true
                };
                addVariantCard(firstVariantData);

                const newCardIndex = variantIndexGlobal - 1;
                const newVariantCard = document.querySelector(`.variant-card[data-variant-index="${newCardIndex}"]`);
                const simpleImagePreviews = document.querySelectorAll('#simple_product_image_preview_container .variant-image-preview-item');

                if (newVariantCard && simpleImagePreviews.length > 0) {
                    const imagesToCopy = Array.from(simpleImagePreviews).map(preview => ({
                        id: preview.dataset.id,
                        url: preview.querySelector('img').src,
                        alt_text: preview.querySelector('img').alt
                    }));
                    const primaryId = document.querySelector('#image_ids_container input[name="cover_image_id"]')?.value;

                    const variantPreviewCont = newVariantCard.querySelector('.image-preview-container');
                    const variantIdsCont = newVariantCard.querySelector(`#variant_${newCardIndex}_image_ids_container`);
                    addImagesToProductForm(imagesToCopy, variantPreviewCont, variantIdsCont, 'variant', newCardIndex);
                    if (primaryId) setVariantPrimaryImage(newCardIndex, parseInt(primaryId));
                }
            }
            currentProductType = newType;
            handleCategoryChange(); // Refresh specs for the new view
        }

        // =================================================================
        // HÀM KHỞI TẠO VÀ KHÔI PHỤC FORM
        // =================================================================
        function initializeFormWithProductData() {
            const hasOldData = oldData && Object.keys(oldData).length > 0;

            if ((hasOldData && oldData.type === 'variable') || (!hasOldData && productBeingEdited.type === 'variable')) {
                const variantsToRender = hasOldData ? (oldData.variants || []) : productBeingEdited.variants;
                
                const usedAttributeIds = new Set();
                variantsToRender.forEach(variant => {
                    if (variant.attributes) {
                        Object.keys(variant.attributes).forEach(attrId => usedAttributeIds.add(parseInt(attrId)));
                    } else if (variant.attribute_values) {
                        variant.attribute_values.forEach(av => usedAttributeIds.add(av.attribute_id));
                    }
                });
                document.querySelectorAll('.product-attribute-checkbox').forEach(cb => {
                    if (usedAttributeIds.has(parseInt(cb.value))) {
                        cb.checked = true;
                    }
                });
                updateSelectedAttributesForVariants();

                variantsToRender.forEach(variant => {
                    addVariantCard(variant);
                    const newCardIndex = variantIndexGlobal - 1;
                    const card = document.querySelector(`.variant-card[data-variant-index="${newCardIndex}"]`);
                    if (!card) return;

                    const imageIds = variant.image_ids || (variant.images ? variant.images.map(img => img.id) : []);
                    const primaryId = variant.primary_image_id || null;
                    const allIds = primaryId ? [...imageIds, primaryId] : imageIds;
                    const imagesForVariant = [];

                    [...new Set(allIds)].forEach(id => {
                        let imageData = null;
                        if (hasOldData) {
                            imageData = oldImagesData[id];
                        } else {
                            const allProductImages = productBeingEdited.variants.flatMap(v => v.images);
                            imageData = allProductImages.find(img => img && img.id == id);
                        }
                        
                        if (imageData) {
                            if (imageData.path && !imageData.url) {
                                imageData.url = `{{ url('storage') }}/${imageData.path}`;
                            }
                            imagesForVariant.push(imageData);
                        }
                    });
                    
                    if (imagesForVariant.length > 0) {
                        const previewCont = card.querySelector('.image-preview-container');
                        const idsCont = card.querySelector(`#variant_${newCardIndex}_image_ids_container`);
                        addImagesToProductForm(imagesForVariant, previewCont, idsCont, 'variant', newCardIndex);
                        if (primaryId) {
                            setVariantPrimaryImage(newCardIndex, parseInt(primaryId));
                        }
                    }
                });
                updateDefaultVariantRadioAndHiddenFields();
            } else if ((hasOldData && oldData.type === 'simple') || (!hasOldData && productBeingEdited.type === 'simple')) {
                const galleryIds = hasOldData ? (oldData.gallery_images || []) : (productBeingEdited.gallery_images.map(img => img.id));
                const coverId = hasOldData ? oldData.cover_image_id : (productBeingEdited.cover_image?.id || null);

                const allIds = coverId ? [...galleryIds, coverId] : galleryIds;
                const imagesForSimple = [];

                [...new Set(allIds)].forEach(id => {
                    const imageData = hasOldData ? oldImagesData[id] : ([...(productBeingEdited.gallery_images || []), productBeingEdited.cover_image]).find(img => img && img.id == id);
                    if (imageData) {
                        if (imageData.path && !imageData.url) {
                            imageData.url = `{{ url('storage') }}/${imageData.path}`;
                        }
                        imagesForSimple.push(imageData);
                    }
                });

                if (imagesForSimple.length > 0) {
                    const previewCont = document.getElementById('simple_product_image_preview_container');
                    const idsCont = document.getElementById('image_ids_container');
                    addImagesToProductForm(imagesForSimple, previewCont, idsCont, 'simple');
                    if (coverId) {
                        setSimpleProductPrimaryImage(parseInt(coverId));
                    }
                }
            }

            const categoryId = oldData.category_id || productBeingEdited.category_id;
            if (categoryId) {
                document.getElementById('category_id').value = categoryId;
                handleCategoryChange();
            }
        }


        // =================================================================
        // KHỞI TẠO VÀ GẮN SỰ KIỆN KHI TRANG TẢI XONG
        // =================================================================
        document.addEventListener('DOMContentLoaded', () => {

            CKEDITOR.ClassicEditor.create(document.querySelector('#description'), {
                removePlugins: [
                    'CKBox', 'CKFinder', 'EasyImage', 'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges',
                    'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments', 'TrackChanges', 'TrackChangesData',
                    'RevisionHistory', 'Pagination', 'WProofreader', 'MathType', 'SlashCommand', 'Template',
                    'DocumentOutline', 'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced',
                    'AIAssistant', 'MultiLevelList', 'CaseChange'
                ],
                language: 'vi',
                toolbar: {
                    items: [
                        'undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', '|', 
                        'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                        'bulletedList', 'numberedList', 'alignment', '|',
                        'link', 'uploadImage', 'insertTable', 'mediaEmbed', '|',
                        'blockQuote', 'codeBlock', 'sourceEditing', 'removeFormat'
                    ]
                },
                simpleUpload: {
                    uploadUrl: '{{ route('admin.media.ckeditor_upload') }}',
                    withCredentials: true,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            })
            .then(editor => {
                console.log('CKEditor đã khởi tạo thành công!', editor);
                window.longDescriptionEditor = editor;
            })
            .catch(error => {
                console.error('Đã có lỗi xảy ra khi khởi tạo CKEditor:', error);
            });

            const tagsInput = document.getElementById('tags');
            if (tagsInput) tagify = new Tagify(tagsInput);

            document.getElementById('name').addEventListener('keyup', (e) => {
                const slugInput = document.getElementById('slug');
                if (slugInput.dataset.auto !== "false") {
                    slugInput.value = slugify(e.target.value);
                }
            });
            document.getElementById('slug').addEventListener('change', (e) => {
                e.target.dataset.auto = e.target.value.trim() === "" ? "true" : "false";
            });

            const productAttributesContainer = document.getElementById('productAttributesContainer');
            if (productAttributesContainer && Array.isArray(allAttributesFromPHP)) {
                allAttributesFromPHP.forEach(attr => {
                    const labelEl = document.createElement('label');
                    labelEl.className = 'flex items-center cursor-pointer p-2 rounded-md hover:bg-gray-100';
                    labelEl.innerHTML =
                        `<input type="checkbox" value="${attr.id}" class="product-attribute-checkbox form-check-input mr-2"> ${attr.name}`;
                    labelEl.querySelector('input').onchange = updateSelectedAttributesForVariants;
                    productAttributesContainer.appendChild(labelEl);
                });
            }

            document.getElementById('addVariantButton')?.addEventListener('click', () => addVariantCard());

            document.getElementById('generateShortDescAI')?.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context) return;
                const btn = document.getElementById('generateShortDescAI');
                toggleButtonLoading(btn, true);
                const prompt = `Dựa vào thông tin sau: "${context}", hãy viết một mô tả ngắn gọn (khoảng 2-3 câu) cho sản phẩm với giọng văn bán hàng chuyên nghiệp.`;
                const result = await callGeminiAPI(prompt);
                if (result) document.getElementById('short_description').value = result.replace(/[\*#`]/g, '').trim();
                toggleButtonLoading(btn, false);
            });

            document.getElementById('generateLongDescAI')?.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context) return;
                const btn = document.getElementById('generateLongDescAI');
                toggleButtonLoading(btn, true);
                const prompt = `Dựa vào thông tin sau: "${context}", hãy viết một bài mô tả chi tiết, hấp dẫn, chuẩn SEO cho sản phẩm, sử dụng các thẻ HTML để định dạng.`;
                const result = await callGeminiAPI(prompt);
                if (result && window.longDescriptionEditor) {
                    window.longDescriptionEditor.setData(result);
                }
                toggleButtonLoading(btn, false);
            });

            document.getElementById('generateTagsAI')?.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context) return;
                const btn = document.getElementById('generateTagsAI');
                toggleButtonLoading(btn, true);
                const prompt = `Dựa vào thông tin sản phẩm sau: "${context}", hãy gợi ý 5 đến 7 từ khóa (tags) phù hợp nhất, trả về dưới dạng chuỗi cách nhau bởi dấu phẩy.`;
                const result = await callGeminiAPI(prompt);
                if (result && tagify) tagify.loadOriginalValues(result.replace(/[\*#`]/g, '').replace(/(\d+\.\s*)/g, '').trim());
                toggleButtonLoading(btn, false);
            });

            document.getElementById('generateAllSeoAI')?.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context) return;
                const btn = document.getElementById('generateAllSeoAI');
                toggleButtonLoading(btn, true);
                const schema = {
                    type: "OBJECT",
                    properties: {
                        meta_title: { type: "STRING" },
                        meta_description: { type: "STRING" },
                        meta_keywords: { type: "STRING" }
                    },
                    required: ["meta_title", "meta_description", "meta_keywords"]
                };
                const prompt = `Dựa vào thông tin sản phẩm sau: "${context}", tạo nội dung SEO (meta_title, meta_description, meta_keywords) và trả về dưới dạng một đối tượng JSON hợp lệ.`;
                const result = await callGeminiAPI(prompt, true, schema);
                if (result) {
                    document.getElementById('meta_title').value = result.meta_title || '';
                    document.getElementById('meta_description').value = result.meta_description || '';
                    document.getElementById('meta_keywords').value = result.meta_keywords || '';
                }
                toggleButtonLoading(btn, false);
            });

            document.getElementById('open-library-btn-simple')?.addEventListener('click', () => {
                window.mediaLibraryTarget = {
                    type: 'simple',
                    previewContainer: document.getElementById('simple_product_image_preview_container'),
                    idsContainer: document.getElementById('image_ids_container')
                };
                if (window.openMediaLibrary) window.openMediaLibrary({ multiple: true });
            });

            document.body.addEventListener('click', function(e) {
                if (e.target && e.target.matches('.open-library-btn-variant')) {
                    const variantIndex = e.target.dataset.variantIndex;
                    window.mediaLibraryTarget = {
                        type: 'variant',
                        variantIndex: variantIndex,
                        previewContainer: document.getElementById(`variant_${variantIndex}_image_preview_container`),
                        idsContainer: document.getElementById(`variant_${variantIndex}_image_ids_container`)
                    };
                    if (window.openMediaLibrary) window.openMediaLibrary({ multiple: true });
                }
            });

            const typeRadios = document.querySelectorAll('.product-type-radio');
            const typeSwitchModal = document.getElementById('typeSwitchConfirmationModal');
            const cancelSwitchBtn = document.getElementById('cancelTypeSwitch');
            const confirmSwitchBtn = document.getElementById('confirmTypeSwitch');
            const reloadModal = document.getElementById('reloadConfirmationModal');
            const cancelReloadBtn = document.getElementById('cancelReload');
            const confirmReloadBtn = document.getElementById('confirmReload');

            typeRadios.forEach(radio => {
                radio.addEventListener('change', (e) => {
                    const newType = e.target.value;
                    if (newType === currentProductType) return;

                    if (currentProductType === 'simple' && newType === 'variable') {
                        document.getElementById('simpleProductFields').style.display = 'none';
                        const variableFields = document.getElementById('variableProductFields');
                        variableFields.style.display = 'block';
                        document.getElementById('variantsContainer').style.display = 'none';
                        document.getElementById('addVariantButton').style.display = 'none';
                        
                        let generateBtn = document.getElementById('generate-variant-from-simple-btn');
                        if (!generateBtn) {
                            generateBtn = document.createElement('button');
                            generateBtn.type = 'button';
                            generateBtn.id = 'generate-variant-from-simple-btn';
                            generateBtn.className = 'btn btn-primary mt-4';
                            generateBtn.innerHTML = `<svg class="svg-icon mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14m-7-7h14" /></svg> Tạo biến thể đầu tiên`;
                            document.getElementById('productAttributesContainer').parentElement.insertAdjacentElement('afterend', generateBtn);

                            generateBtn.addEventListener('click', () => {
                                updateSelectedAttributesForVariants();
                                if (selectedProductAttributes.length === 0) {
                                    showMessageModal('Thiếu thuộc tính', 'Vui lòng chọn ít nhất một thuộc tính để tạo biến thể.', 'error');
                                    return;
                                }
                                performTypeSwitch(newType);
                                document.getElementById('variantsContainer').style.display = 'block';
                                document.getElementById('addVariantButton').style.display = 'inline-flex';
                                generateBtn.remove();
                            });
                        }
                        return;
                    }

                    if (originalProductTypeFromServer === 'variable' && newType === 'variable') {
                        reloadModal.classList.remove('hidden');
                        confirmReloadBtn.onclick = () => window.location.reload();
                        cancelReloadBtn.onclick = () => {
                            e.target.checked = false;
                            document.querySelector('.product-type-radio[value="simple"]').checked = true;
                            reloadModal.classList.add('hidden');
                        };
                        return;
                    }

                    if (currentProductType === 'variable' && newType === 'simple') {
                        typeSwitchModal.classList.remove('hidden');
                    } else {
                        performTypeSwitch(newType);
                    }
                });
            });

            cancelSwitchBtn.addEventListener('click', () => {
                document.querySelector(`.product-type-radio[value="${currentProductType}"]`).checked = true;
                typeSwitchModal.classList.add('hidden');
            });

            confirmSwitchBtn.addEventListener('click', () => {
                performTypeSwitch('simple');
                typeSwitchModal.classList.add('hidden');
            });

            document.getElementById('category_id').addEventListener('change', handleCategoryChange);
            
            initializeFormWithProductData();

            document.getElementById('editProductForm')?.addEventListener('submit', function(event) {
                if (window.longDescriptionEditor) {
                    const editorData = window.longDescriptionEditor.getData();
                    document.getElementById('description').value = editorData;
                }
            });
        });
    </script>
@endpush
