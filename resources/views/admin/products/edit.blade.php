@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa sản phẩm: ' . $product->name)

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
    .input-field, .select-field, .textarea-field {
        width: 100%;
        padding: 0.875rem 1.125rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.625rem;
        box-shadow: inset 0 1px 2px 0 rgba(0,0,0,0.03);
        transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        background-color: #f8fafc;
    }
    .input-field:focus, .select-field:focus, .textarea-field:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        background-color: white;
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
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.75rem;
    }
    .image-preview {
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
    .image-preview .remove-img-btn, .image-preview .delete-existing-img-btn {
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
        z-index:10;
    }
    .image-preview .remove-img-btn:hover, .image-preview .delete-existing-img-btn:hover {
        background-color: #dc2626;
    }
    .rich-text-placeholder {
        min-height: 250px;
    }
    .variant-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.625rem;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        background-color: #f8fafc;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
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
    .input-with-icon .input-field, .input-with-icon .select-field {
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
                <i data-lucide="edit" class="inline-block mr-3 text-blue-600" style="width:36px; height:36px;"></i>Chỉnh Sửa Sản Phẩm <span class="text-2xl text-purple-600">✨AI</span>
            </h1>
            <p class="text-gray-600 mt-1">Cập nhật thông tin chi tiết cho sản phẩm: <strong>{{ $product->name }}</strong></p>
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

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-md shadow-md" role="alert">
            <div class="flex items-center">
                <i data-lucide="check-circle" class="h-6 w-6 mr-3"></i>
                <div>
                    <p class="font-bold">Thành công!</p>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif
    @if(session('error'))
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

    <form id="editProductForm" action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-8 gap-y-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="card">
                    <div class="card-header">
                        <i data-lucide="file-text"></i>Thông Tin Chung
                    </div>
                    <div class="input-group">
                        <label for="name">Tên sản phẩm <span class="required-star">*</span></label>
                        <div class="input-with-icon">
                            <i data-lucide="type" class="icon-prefix"></i>
                            <input type="text" id="name" name="name" class="input-field @error('name') border-red-500 @enderror" value="{{ old('name', $product->name) }}" required>
                        </div>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="input-group">
                        <label for="slug">Đường dẫn thân thiện (Slug)</label>
                        <div class="input-with-icon">
                            <i data-lucide="link-2" class="icon-prefix"></i>
                            <input type="text" id="slug" name="slug" class="input-field @error('slug') border-red-500 @enderror" value="{{ old('slug', $product->slug) }}" placeholder="Tự động tạo nếu để trống">
                        </div>
                        @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="input-group">
                        <div class="label-with-action">
                            <label for="short_description">Mô tả ngắn</label>
                            <button type="button" id="generateShortDescAI" class="btn btn-ai btn-sm">
                                <span class="button-text">✨ Tạo bằng AI</span>
                                <span class="loading-spinner hidden"></span>
                            </button>
                        </div>
                        <textarea id="short_description" name="short_description" class="textarea-field @error('short_description') border-red-500 @enderror" rows="3">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="input-group">
                         <div class="label-with-action">
                            <label for="description">Mô tả chi tiết</label>
                            <button type="button" id="generateLongDescAI" class="btn btn-ai btn-sm">
                               <span class="button-text">✨ Tạo bằng AI</span>
                               <span class="loading-spinner hidden"></span>
                            </button>
                        </div>
                        <textarea id="description" name="description" class="textarea-field rich-text-placeholder @error('description') border-red-500 @enderror" rows="8">{{ old('description', $product->description) }}</textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <small class="text-gray-500 mt-1 block">Nên sử dụng trình soạn thảo WYSIWYG để có trải nghiệm tốt nhất.</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i data-lucide="git-fork"></i>Loại Sản Phẩm & Biến Thể
                    </div>
                    <div class="input-group">
                        <label>Loại sản phẩm <span class="required-star">*</span> (Không thể thay đổi sau khi tạo)</label>
                        <div class="flex items-center space-x-6 mt-2">
                            <label class="flex items-center cursor-not-allowed p-2 rounded-md text-gray-500">
                                <input type="radio" name="type_display" value="simple" class="form-check-input mr-2" {{ $product->type == 'simple' ? 'checked' : '' }} disabled> Đơn giản
                            </label>
                            <label class="flex items-center cursor-not-allowed p-2 rounded-md text-gray-500">
                                <input type="radio" name="type_display" value="variable" class="form-check-input mr-2" {{ $product->type == 'variable' ? 'checked' : '' }} disabled> Có biến thể
                            </label>
                            <input type="hidden" name="type" value="{{ $product->type }}">
                        </div>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="simpleProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200" style="{{ $product->type == 'simple' ? '' : 'display:none;' }}">
                        <h3 class="text-lg font-semibold text-gray-700 mb-1">Thông tin sản phẩm đơn giản</h3>
                        @php
                            $defaultVariant = $product->variants->where('is_default', true)->first() ?? $product->variants->first();
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="input-group">
                                <label for="simple_sku">SKU <span class="required-star">*</span></label>
                                <div class="input-with-icon">
                                    <i data-lucide="scan-barcode" class="icon-prefix"></i>
                                    <input type="text" id="simple_sku" name="simple_sku" class="input-field @error('simple_sku') border-red-500 @enderror" value="{{ old('simple_sku', $defaultVariant->sku ?? '') }}">
                                </div>
                                @error('simple_sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="input-group">
                                <label for="simple_price">Giá bán <span class="required-star">*</span> (VNĐ)</label>
                                 <div class="input-with-icon">
                                    <i data-lucide="dollar-sign" class="icon-prefix"></i>
                                    <input type="number" id="simple_price" name="simple_price" class="input-field @error('simple_price') border-red-500 @enderror" step="1000" min="0" value="{{ old('simple_price', $defaultVariant->price ?? '') }}">
                                </div>
                                @error('simple_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="input-group">
                                <label for="simple_sale_price">Giá khuyến mãi (VNĐ)</label>
                                 <div class="input-with-icon">
                                    <i data-lucide="badge-percent" class="icon-prefix"></i>
                                    <input type="number" id="simple_sale_price" name="simple_sale_price" class="input-field @error('simple_sale_price') border-red-500 @enderror" step="1000" min="0" value="{{ old('simple_sale_price', $defaultVariant->sale_price ?? '') }}">
                                </div>
                                @error('simple_sale_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="input-group">
                                <label for="simple_stock_quantity">Số lượng tồn kho <span class="required-star">*</span></label>
                                 <div class="input-with-icon">
                                    <i data-lucide="boxes" class="icon-prefix"></i>
                                    <input type="number" id="simple_stock_quantity" name="simple_stock_quantity" class="input-field @error('simple_stock_quantity') border-red-500 @enderror" min="0" value="{{ old('simple_stock_quantity', $defaultVariant->stock_quantity ?? '') }}">
                                </div>
                                @error('simple_stock_quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div id="variableProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200" style="{{ $product->type == 'variable' ? '' : 'display:none;' }}">
                        <h3 class="text-lg font-semibold text-gray-700 mb-1">Quản lý biến thể</h3>
                        <div class="input-group">
                            <label for="sku_prefix">Tiền tố SKU (cho biến thể)</label>
                            <div class="input-with-icon">
                                <i data-lucide="scan-line" class="icon-prefix"></i>
                                <input type="text" id="sku_prefix" name="sku_prefix" class="input-field @error('sku_prefix') border-red-500 @enderror" value="{{ old('sku_prefix', $product->sku_prefix) }}" placeholder="Ví dụ: APPL-IP15P-">
                            </div>
                             @error('sku_prefix') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="input-group">
                            <label class="flex items-center mb-2">
                                <i data-lucide="list-filter" class="mr-2 h-5 w-5 text-gray-500"></i>
                                Thuộc tính sử dụng cho biến thể (Không thể thay đổi các thuộc tính đã chọn, chỉ có thể thêm/sửa giá trị)
                            </label>
                            <div id="productAttributesContainer" class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 p-3 border border-gray-200 rounded-md bg-gray-50">
                                {{-- Attributes populated by JS --}}
                            </div>
                            @error('variants.*.attributes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div id="variantsContainer" class="space-y-5">
                           {{-- Xử lý old('variants') và $product->variants bằng JavaScript trong DOMContentLoaded --}}
                        </div>
                        @error('variants') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                         @foreach ($errors->get('variants.*') as $variantErrorKey => $messages)
                            @foreach ($messages as $message)
                                <p class="text-red-500 text-xs mt-1">{{ $message }} (Lỗi tại: {{ $variantErrorKey }})</p>
                            @endforeach
                        @endforeach

                        <button type="button" id="addVariantButton" class="btn btn-secondary mt-2">
                            <i data-lucide="plus-circle" class="mr-2 h-5 w-5"></i> Thêm Biến Thể Mới
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="card">
                     <div class="card-header">
                        <i data-lucide="send-to-back"></i>Xuất Bản
                    </div>
                     <div class="input-group">
                        <label for="status">Trạng thái <span class="required-star">*</span></label>
                        <div class="input-with-icon">
                            <i data-lucide="activity" class="icon-prefix"></i>
                            <select id="status" name="status" class="select-field @error('status') border-red-500 @enderror">
                                <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                                <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                <option value="pending_review" {{ old('status', $product->status) == 'pending_review' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="trashed" {{ old('status', $product->status) == 'trashed' ? 'selected' : '' }}>Đã xóa (ẩn)</option>
                            </select>
                        </div>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="input-group mt-5">
                        <label class="flex items-center cursor-pointer p-2 rounded-md hover:bg-blue-50 transition-colors">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" class="form-check-input mr-3" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <i data-lucide="star" class="mr-2 h-5 w-5 text-yellow-500"></i> Sản phẩm nổi bật
                        </label>
                        @error('is_featured') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i data-lucide="folder-tree"></i>Tổ Chức
                    </div>
                    <div class="input-group">
                        <label for="category_id">Danh mục <span class="required-star">*</span></label>
                        <div class="input-with-icon">
                            <i data-lucide="folder-open" class="icon-prefix"></i>
                            <select id="category_id" name="category_id" class="select-field @error('category_id') border-red-500 @enderror" required>
                                <option value="">Chọn danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                            <input type="text" id="tags" name="tags" class="input-field @error('tags') border-red-500 @enderror" value="{{ old('tags', $product->tags) }}" placeholder="Ví dụ: iphone 15, apple, new">
                        </div>
                        @error('tags') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <small class="text-gray-500 mt-1 block">Cách nhau bởi dấu phẩy.</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i data-lucide="image"></i>Hình Ảnh Sản Phẩm
                    </div>
                    <div class="input-group">
                        <label for="cover_image_file">Ảnh bìa (Chọn ảnh mới sẽ thay thế ảnh cũ)</label>
                        <input type="file" id="cover_image_file" name="cover_image_file" class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('cover_image_file') border-red-500 @enderror" accept="image/*" onchange="previewCoverImage(event)">
                        <div id="coverImagePreviewContainer" class="image-preview-container mt-3">
                            @if($product->coverImage)
                                <div class="image-preview existing-image" id="existing_cover_image">
                                    <img src="{{ $product->coverImage->url }}" alt="{{ $product->coverImage->original_name ?? 'Ảnh bìa' }}">
                                    {{-- Nút xóa ảnh bìa hiện tại có thể được thêm ở đây nếu muốn, nhưng logic xóa file cần được xử lý cẩn thận ở backend --}}
                                </div>
                            @endif
                        </div>
                        @error('cover_image_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="input-group">
                        <label for="gallery_image_files">Thư viện ảnh (Thêm ảnh mới)</label>
                        <input type="file" id="gallery_image_files" name="gallery_image_files[]" class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('gallery_image_files.*') border-red-500 @enderror" accept="image/*" multiple onchange="previewGalleryImages(event)">
                        <div id="galleryImagesPreviewContainer" class="image-preview-container mt-3">
                             @if($product->galleryImages->isNotEmpty())
                                @foreach($product->galleryImages as $gImage)
                                <div class="image-preview existing-gallery-image" data-image-id="{{ $gImage->id }}">
                                    <img src="{{ $gImage->url }}" alt="{{ $gImage->original_name ?? 'Ảnh gallery' }}">
                                    <span class="delete-existing-img-btn" data-id="{{ $gImage->id }}" title="Xóa ảnh này">
                                        <i data-lucide="x" class="h-3 w-3"></i>
                                    </span>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <input type="hidden" name="deleted_gallery_image_ids" id="deleted_gallery_image_ids">
                        @error('gallery_image_files.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                         @foreach ($errors->get('gallery_image_files.*') as $key => $messages)
                            @foreach ($messages as $message)
                                <p class="text-red-500 text-xs mt-1">Lỗi ảnh thư viện (ảnh {{ $key + 1 }}): {{ $message }}</p>
                            @endforeach
                        @endforeach
                    </div>
                </div>

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
                        <input type="text" id="meta_title" name="meta_title" class="input-field @error('meta_title') border-red-500 @enderror" value="{{ old('meta_title', $product->meta_title) }}">
                        @error('meta_title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="input-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" class="textarea-field @error('meta_description') border-red-500 @enderror" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
                        @error('meta_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                     <div class="input-group">
                        <label for="meta_keywords">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" class="input-field @error('meta_keywords') border-red-500 @enderror" value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="Từ khóa 1, Từ khóa 2">
                        @error('meta_keywords') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i data-lucide="info"></i>Thông Tin Bổ Sung
                    </div>
                    <div class="input-group">
                        <label for="warranty_information">Thông tin bảo hành</label>
                        <textarea id="warranty_information" name="warranty_information" class="textarea-field @error('warranty_information') border-red-500 @enderror" rows="3">{{ old('warranty_information', $product->warranty_information) }}</textarea>
                        @error('warranty_information') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary w-full sm:w-auto">
                <i data-lucide="x-circle" class="mr-2 h-5 w-5"></i> Hủy Bỏ
            </a>
            <button type="submit" class="btn btn-primary w-full sm:w-auto">
                <i data-lucide="save" class="mr-2 h-5 w-5"></i> Cập Nhật Sản Phẩm
            </button>
        </div>
    </form>
    
    <div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden px-4 z-50">
        <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div id="messageModalIcon" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                     <i data-lucide="info" class="h-6 w-6 text-blue-600"></i>
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
<script>
    let selectedProductAttributes = [];
    let variantIndex = 0; // Sẽ được cập nhật nếu có biến thể cũ

    @php
        $jsCategoriesData = [];
        if (isset($categories) && $categories instanceof \Illuminate\Support\Collection) {
            $jsCategoriesData = $categories->map(function($cat) {
                return ['id' => $cat->id, 'name' => $cat->name];
            })->values()->all();
        }

        $jsAttributesData = [];
        if (isset($attributes) && $attributes instanceof \Illuminate\Support\Collection) {
            $jsAttributesData = $attributes->map(function($attr) {
                $icon = 'tag'; 
                if (isset($attr->name)) {
                    if (str_contains(strtolower($attr->name), 'màu') || (isset($attr->display_type) && $attr->display_type === 'color_swatch')) $icon = 'palette';
                    elseif (str_contains(strtolower($attr->name), 'dung lượng') || str_contains(strtolower($attr->name), 'bộ nhớ')) $icon = 'hard-drive';
                    elseif (str_contains(strtolower($attr->name), 'kích thước')) $icon = 'maximize';
                }
                $attributeValuesData = [];
                if (isset($attr->attributeValues) && ($attr->attributeValues instanceof \Illuminate\Support\Collection || is_array($attr->attributeValues))) {
                     $attributeValuesData = collect($attr->attributeValues)->map(function($val) { // Đảm bảo là collection để map
                        return ['id' => $val->id ?? $val['id'] ?? null, 'value' => $val->value ?? $val['value'] ?? '', 'meta' => $val->meta ?? $val['meta'] ?? null];
                    })->values()->all();
                }
                return [
                    'id' => $attr->id,
                    'name' => $attr->name ?? 'N/A',
                    'slug' => $attr->slug ?? '',
                    'icon' => $icon,
                    'attributeValues' => $attributeValuesData
                ];
            })->values()->all();
        }

        $jsProductVariantsData = [];
        if (isset($product) && $product->variants instanceof \Illuminate\Support\Collection) {
            $jsProductVariantsData = $product->variants->map(function($variant, $idx) {
                $attrs = [];
                if ($variant->attributeValues instanceof \Illuminate\Support\Collection) {
                    foreach ($variant->attributeValues as $attrVal) {
                        $attrs[$attrVal->attribute_id] = $attrVal->id;
                    }
                }
                return [
                    'id' => $variant->id, // ID của biến thể hiện tại để update
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'stock_quantity' => $variant->stock_quantity,
                    'is_default' => $variant->is_default,
                    'attributes' => $attrs, // { attribute_id: attribute_value_id, ... }
                    'status' => $variant->status ?? 'active' // Thêm status nếu có
                ];
            })->values()->all();
        }
    @endphp

    const categoriesFromPHP = @json($jsCategoriesData, JSON_UNESCAPED_UNICODE);
    const allAttributesFromPHP = @json($jsAttributesData, JSON_UNESCAPED_UNICODE);
    const productVariantsFromPHP = @json($jsProductVariantsData, JSON_UNESCAPED_UNICODE);


    // DOM Elements
    const productNameInput = document.getElementById('name');
    const categorySelectElement = document.getElementById('category_id');
    const shortDescriptionTextarea = document.getElementById('short_description');
    const longDescriptionTextarea = document.getElementById('description');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionTextarea = document.getElementById('meta_description');
    const metaKeywordsInput = document.getElementById('meta_keywords');
    const tagsInput = document.getElementById('tags');

    const generateShortDescBtn = document.getElementById('generateShortDescAI');
    const generateLongDescBtn = document.getElementById('generateLongDescAI');
    const generateTagsBtn = document.getElementById('generateTagsAI');
    const generateAllSeoBtn = document.getElementById('generateAllSeoAI');
    
    const messageModal = document.getElementById('messageModal');
    const messageModalIcon = document.getElementById('messageModalIcon');
    const messageModalTitle = document.getElementById('messageModalTitle');
    const messageModalText = document.getElementById('messageModalText');
    const messageModalCloseButton = document.getElementById('messageModalCloseButton');

    const productAttributesContainer = document.getElementById('productAttributesContainer');
    const variantsContainer = document.getElementById('variantsContainer');
    const addVariantButton = document.getElementById('addVariantButton');
    const deletedGalleryImageIdsInput = document.getElementById('deleted_gallery_image_ids');
    let deletedGalleryImageIds = [];


    function showMessageModal(title, text, type = 'info') {
        if (!messageModal || !messageModalTitle || !messageModalText || !messageModalIcon) return;
        messageModalTitle.textContent = title;
        messageModalText.textContent = text;
        messageModalIcon.innerHTML = '';
        let iconName = 'info', iconColorClass = 'text-blue-600', iconBgClass = 'bg-blue-100';
        if (type === 'success') { iconName = 'check-circle'; iconColorClass = 'text-green-600'; iconBgClass = 'bg-green-100'; }
        else if (type === 'error') { iconName = 'alert-triangle'; iconColorClass = 'text-red-600'; iconBgClass = 'bg-red-100'; }
        const icon = document.createElement('i');
        icon.dataset.lucide = iconName;
        icon.className = `h-6 w-6 ${iconColorClass}`;
        messageModalIcon.className = `mx-auto flex items-center justify-center h-12 w-12 rounded-full ${iconBgClass}`;
        messageModalIcon.appendChild(icon);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        messageModal.classList.remove('hidden');
    }

    if(messageModalCloseButton) {
        messageModalCloseButton.addEventListener('click', () => messageModal.classList.add('hidden'));
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

    async function callGeminiAPI(prompt, isStructured = false, schema = null) {
        const apiKey = ""; 
        const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${apiKey}`;
        let payload = { contents: [{ role: "user", parts: [{ text: prompt }] }] };
        if (isStructured && schema) {
            payload.generationConfig = { responseMimeType: "application/json", responseSchema: schema };
        }
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
        const productName = productNameInput ? productNameInput.value.trim() : "";
        const categoryName = categorySelectElement ? (categorySelectElement.options[categorySelectElement.selectedIndex]?.text || "") : "";
        let attributesString = "";
        const productTypeRadio = document.querySelector('input[name="type"]:checked'); // Dùng name="type" vì type_display bị disabled
        if (productTypeRadio && productTypeRadio.value === 'variable') {
            const firstVariantCard = variantsContainer ? variantsContainer.querySelector('.variant-card') : null;
            if (firstVariantCard) {
                const attributeSelects = firstVariantCard.querySelectorAll('select[name*="[attributes]"]');
                let tempAttrs = [];
                attributeSelects.forEach(select => {
                    const attrNameLabel = select.closest('.input-group')?.querySelector('label');
                    if (attrNameLabel) {
                       const attrName = attrNameLabel.textContent.replace('*','').trim();
                       const attrValue = select.options[select.selectedIndex]?.text;
                       if (attrValue && attrValue !== `Chọn ${attrName}`) {
                          tempAttrs.push(`${attrName}: ${attrValue}`);
                       }
                    }
                });
                if(tempAttrs.length > 0) attributesString = ` với các thuộc tính nổi bật: ${tempAttrs.join(', ')}`;
            }
        }
        if (!productName) {
            showMessageModal("Thiếu thông tin", "Vui lòng nhập tên sản phẩm trước khi sử dụng tính năng AI.", "error");
            return null;
        }
        return `Sản phẩm: ${productName}, thuộc danh mục: ${categoryName}${attributesString}. Tập trung vào các sản phẩm của Apple.`;
    }

    if (generateShortDescBtn) {
        generateShortDescBtn.addEventListener('click', async () => {
            const context = getProductContext();
            if (!context || !shortDescriptionTextarea) return;
            toggleButtonLoading(generateShortDescBtn, true);
            const prompt = `Viết một mô tả ngắn (khoảng 1-2 câu, tối đa 50 từ) thật hấp dẫn cho sản phẩm Apple sau: ${context}. Tập trung vào điểm nổi bật nhất, giọng văn chuyên nghiệp.`;
            const generatedText = await callGeminiAPI(prompt);
            if (generatedText) {
                shortDescriptionTextarea.value = generatedText;
                showMessageModal("Hoàn tất", "Đã tạo mô tả ngắn bằng AI!", "success");
            }
            toggleButtonLoading(generateShortDescBtn, false);
        });
    }
    
    if (generateLongDescBtn) {
        generateLongDescBtn.addEventListener('click', async () => {
            const context = getProductContext();
            if (!context || !longDescriptionTextarea || !shortDescriptionTextarea) return;
            const shortDesc = shortDescriptionTextarea.value.trim();
            toggleButtonLoading(generateLongDescBtn, true);
            let prompt = `Viết một mô tả chi tiết (khoảng 3-5 đoạn văn, tập trung vào các tính năng, lợi ích chính và trải nghiệm người dùng) cho sản phẩm Apple sau: ${context}.`;
            prompt += shortDesc ? `\nCó thể dựa trên mô tả ngắn sau: "${shortDesc}".` : '';
            prompt += `\nHãy viết bằng giọng văn chuyên nghiệp, phù hợp để đăng bán trên website thương mại điện tử. Tránh các từ ngữ quá quảng cáo, tập trung vào thông tin hữu ích.`;
            const generatedText = await callGeminiAPI(prompt);
            if (generatedText) {
                longDescriptionTextarea.value = generatedText;
                showMessageModal("Hoàn tất", "Đã tạo mô tả chi tiết bằng AI!", "success");
            }
            toggleButtonLoading(generateLongDescBtn, false);
        });
    }

    const seoSchema = {
        type: "OBJECT",
        properties: {
            meta_title: { type: "STRING", description: "Tiêu đề meta tối ưu SEO, khoảng 50-60 ký tự, chứa từ khóa chính." },
            meta_description: { type: "STRING", description: "Mô tả meta tối ưu SEO, khoảng 150-160 ký tự, hấp dẫn người dùng click, chứa từ khóa chính." },
            meta_keywords: { type: "STRING", description: "Danh sách 5-7 từ khóa meta liên quan nhất, cách nhau bởi dấu phẩy." }
        },
        required: ["meta_title", "meta_description", "meta_keywords"]
    };

    if (generateAllSeoBtn) {
        generateAllSeoBtn.addEventListener('click', async () => {
            const context = getProductContext();
            if (!context || !longDescriptionTextarea || !shortDescriptionTextarea || !metaTitleInput || !metaDescriptionTextarea || !metaKeywordsInput) return;
            const productDescription = longDescriptionTextarea.value.trim() || shortDescriptionTextarea.value.trim();
            if (!productDescription) {
                showMessageModal("Thiếu thông tin", "Vui lòng có mô tả sản phẩm trước khi tạo SEO.", "error"); return;
            }
            toggleButtonLoading(generateAllSeoBtn, true);
            const prompt = `Dựa trên thông tin sản phẩm Apple: "${context}" và mô tả: "${productDescription}", hãy tạo các thẻ meta SEO (meta title, meta description, meta keywords) cho một trang web bán sản phẩm này.`;
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
            const prompt = `Gợi ý 5-7 thẻ (tags) phù hợp nhất cho sản phẩm Apple sau: ${context}. Các thẻ nên ngắn gọn, tập trung vào tên sản phẩm, dòng sản phẩm, tính năng chính hoặc đối tượng người dùng. Trả về dưới dạng danh sách các từ khóa cách nhau bởi dấu phẩy.`;
            const generatedTags = await callGeminiAPI(prompt);
            if (generatedTags) {
                tagsInput.value = generatedTags;
                showMessageModal("Hoàn tất", "Đã gợi ý thẻ sản phẩm bằng AI!", "success");
            }
            toggleButtonLoading(generateTagsBtn, false);
        });
    }
    
    function updateSelectedAttributesForVariants() {
        selectedProductAttributes = []; 
        document.querySelectorAll('.product-attribute-checkbox:checked').forEach(checkbox => {
            const attrId = parseInt(checkbox.value);
            if (Array.isArray(allAttributesFromPHP)) {
                const attribute = allAttributesFromPHP.find(a => a && typeof a.id !== 'undefined' && a.id === attrId);
                if (attribute) {
                    selectedProductAttributes.push(attribute);
                }
            }
        });
    }
    
    function toggleProductTypeFields() {
        // Trên trang edit, loại sản phẩm thường không được thay đổi.
        // Hàm này chủ yếu để ẩn/hiện các trường tương ứng dựa trên loại sản phẩm đã có.
        const productType = "{{ $product->type }}"; // Lấy loại sản phẩm từ PHP
        const simpleFields = document.getElementById('simpleProductFields');
        const variableFields = document.getElementById('variableProductFields');

        if (simpleFields) simpleFields.style.display = (productType === 'simple' ? 'block' : 'none');
        if (variableFields) variableFields.style.display = (productType === 'variable' ? 'block' : 'none');
        
        if (typeof lucide !== 'undefined') lucide.createIcons(); 
    }
    
    function populateAttributeCheckboxes() {
        if (productAttributesContainer && Array.isArray(allAttributesFromPHP)) {
            // Xác định các attribute_id đã được sử dụng bởi các biến thể hiện tại của sản phẩm
            let existingAttributeIds = new Set();
            if (Array.isArray(productVariantsFromPHP)) {
                productVariantsFromPHP.forEach(variant => {
                    if (variant.attributes && typeof variant.attributes === 'object') {
                        Object.keys(variant.attributes).forEach(attrId => existingAttributeIds.add(parseInt(attrId)));
                    }
                });
            }
             // Hoặc lấy từ old input nếu có
            const oldVariants = @json(old('variants', []));
             if (Array.isArray(oldVariants) && oldVariants.length > 0) {
                 oldVariants.forEach(oldVariant => {
                    if (oldVariant.attributes && typeof oldVariant.attributes === 'object') {
                        Object.keys(oldVariant.attributes).forEach(attrId => existingAttributeIds.add(parseInt(attrId)));
                    }
                });
            }


            allAttributesFromPHP.forEach(attr => {
                if (!attr || typeof attr.id === 'undefined' || typeof attr.name === 'undefined') return; 

                const labelEl = document.createElement('label');
                labelEl.className = 'flex items-center cursor-pointer p-2 rounded-md hover:bg-gray-100 transition-colors';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox'; checkbox.id = `attr_${attr.id}`; checkbox.value = attr.id;
                checkbox.dataset.attributeName = attr.name;
                checkbox.className = 'product-attribute-checkbox form-check-input mr-2';
                
                // Check và disable nếu attribute đã được sử dụng cho sản phẩm biến thể hiện tại
                if (existingAttributeIds.has(attr.id)) {
                    checkbox.checked = true;
                    checkbox.disabled = true; // Không cho bỏ chọn các thuộc tính đã có của biến thể
                    labelEl.classList.add('opacity-70', 'cursor-not-allowed');
                }
                checkbox.onchange = updateSelectedAttributesForVariants;
                
                const iconEl = document.createElement('i');
                iconEl.dataset.lucide = attr.icon || 'tag'; 
                iconEl.className = 'mr-1.5 h-4 w-4 text-gray-500';
                const textNode = document.createTextNode(attr.name);
                labelEl.appendChild(checkbox); labelEl.appendChild(iconEl); labelEl.appendChild(textNode);
                productAttributesContainer.appendChild(labelEl);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
            updateSelectedAttributesForVariants(); 
        }
    }


    function addVariantCard(variantData = null, existingIndex = null) {
        const productTypeRadio = document.querySelector('input[name="type"]:checked'); // Dùng name="type"
        if (!productTypeRadio || (productTypeRadio.value === 'variable' && selectedProductAttributes.length === 0 && !variantData)) {
             // Nếu là tạo mới và không có thuộc tính nào được chọn
            if (!variantData) { // Chỉ hiển thị thông báo nếu không phải đang load variant cũ
                showMessageModal('Thông báo', 'Vui lòng chọn ít nhất một thuộc tính cho sản phẩm trước khi thêm biến thể.', 'info');
                return;
            }
        }

        const currentVariantIndex = (existingIndex !== null) ? existingIndex : variantIndex;

        const variantCard = document.createElement('div');
        variantCard.className = 'variant-card';
        variantCard.dataset.variantIndex = currentVariantIndex; // Sử dụng currentVariantIndex

        // Input ẩn để lưu ID của biến thể (nếu là biến thể đã có)
        const variantIdInput = variantData && variantData.id ? `<input type="hidden" name="variants[${currentVariantIndex}][id]" value="${variantData.id}">` : '';

        let variantHeaderHTML = `
            <div class="variant-header">
                <div class="flex items-center">
                    <i data-lucide="puzzle" class="mr-2 h-5 w-5 text-blue-600"></i>
                    <h4 class="variant-title">Biến Thể #${currentVariantIndex + 1} ${variantData && variantData.id ? `(ID: ${variantData.id})` : '(Mới)'}</h4>
                </div>
                <button type="button" class="remove-variant-btn btn btn-danger btn-sm p-1.5 rounded-md" data-variant-id="${variantData?.id || ''}">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                </button>
            </div>
            ${variantIdInput} 
        `;
        
        let attributesHTML = '<div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-4">';
        // Sử dụng selectedProductAttributes để tạo select box, nhưng chọn giá trị từ variantData nếu có
        let attributesToDisplay = selectedProductAttributes;
        if (variantData && variantData.attributes) { // Nếu đang load variant cũ, ưu tiên các thuộc tính của nó
            attributesToDisplay = allAttributesFromPHP.filter(attrPHP => 
                Object.keys(variantData.attributes).map(Number).includes(attrPHP.id)
            );
        }


        attributesToDisplay.forEach(attr => {
            if (!attr || !Array.isArray(attr.attributeValues)) return; 
            const selectedValue = variantData?.attributes?.[attr.id.toString()] || '';
            attributesHTML += `
                <div class="input-group">
                    <label for="variants_${currentVariantIndex}_attr_${attr.id}" class="text-sm font-medium">${attr.name} <span class="required-star">*</span></label>
                    <div class="input-with-icon">
                        <i data-lucide="${attr.icon || 'tag'}" class="icon-prefix"></i>
                        <select name="variants[${currentVariantIndex}][attributes][${attr.id}]" id="variants_${currentVariantIndex}_attr_${attr.id}" class="select-field text-sm" required ${variantData && variantData.id ? 'disabled' : ''}>
                            <option value="">Chọn ${attr.name}</option>
                            ${attr.attributeValues.map(val => `<option value="${val.id}" ${selectedValue == val.id ? 'selected' : ''}>${val.value}</option>`).join('')}
                        </select>
                    </div>
                </div>`;
        });
        attributesHTML += '</div>';

        let variantFieldsHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="input-group">
                    <label for="variants_${currentVariantIndex}_sku" class="text-sm font-medium">SKU Biến Thể <span class="required-star">*</span></label>
                    <div class="input-with-icon">
                        <i data-lucide="scan-barcode" class="icon-prefix"></i>
                        <input type="text" name="variants[${currentVariantIndex}][sku]" id="variants_${currentVariantIndex}_sku" class="input-field text-sm" value="${variantData?.sku || ''}" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="variants_${currentVariantIndex}_price" class="text-sm font-medium">Giá Biến Thể <span class="required-star">*</span> (VNĐ)</label>
                    <div class="input-with-icon">
                        <i data-lucide="dollar-sign" class="icon-prefix"></i>
                        <input type="number" name="variants[${currentVariantIndex}][price]" id="variants_${currentVariantIndex}_price" class="input-field text-sm" step="1000" min="0" value="${variantData?.price || ''}" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="variants_${currentVariantIndex}_sale_price" class="text-sm font-medium">Giá KM Biến Thể (VNĐ)</label>
                    <div class="input-with-icon">
                        <i data-lucide="badge-percent" class="icon-prefix"></i>
                        <input type="number" name="variants[${currentVariantIndex}][sale_price]" id="variants_${currentVariantIndex}_sale_price" class="input-field text-sm" step="1000" min="0" value="${variantData?.sale_price || ''}">
                    </div>
                </div>
                <div class="input-group">
                    <label for="variants_${currentVariantIndex}_stock_quantity" class="text-sm font-medium">Tồn Kho <span class="required-star">*</span></label>
                    <div class="input-with-icon">
                        <i data-lucide="boxes" class="icon-prefix"></i>
                        <input type="number" name="variants[${currentVariantIndex}][stock_quantity]" id="variants_${currentVariantIndex}_stock_quantity" class="input-field text-sm" min="0" value="${variantData?.stock_quantity || ''}" required>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <label class="flex items-center text-sm cursor-pointer p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <input type="radio" name="variant_is_default_radio_group" value="${currentVariantIndex}" class="form-check-input mr-2 variant-default-radio" ${variantData?.is_default ? 'checked' : ''}>
                    <input type="hidden" name="variants[${currentVariantIndex}][is_default]" value="${variantData?.is_default ? 'true' : 'false'}" class="is-default-hidden-input">
                    Đặt làm biến thể mặc định
                </label>
            </div>`;
        
        variantCard.innerHTML = variantHeaderHTML + attributesHTML + variantFieldsHTML;
        if (variantsContainer) variantsContainer.appendChild(variantCard);
        
        const removeBtn = variantCard.querySelector('.remove-variant-btn');
        if(removeBtn) {
            removeBtn.addEventListener('click', function() { 
                const variantIdToRemove = this.dataset.variantId;
                if (variantIdToRemove) { // Nếu là biến thể đã tồn tại, thêm ID vào danh sách xóa
                    const deletedVariantIdsInput = document.getElementById('deleted_variant_ids'); // Cần thêm input này vào form
                    if (deletedVariantIdsInput) {
                        let currentDeletedIds = deletedVariantIdsInput.value ? deletedVariantIdsInput.value.split(',') : [];
                        if (!currentDeletedIds.includes(variantIdToRemove)) {
                            currentDeletedIds.push(variantIdToRemove);
                            deletedVariantIdsInput.value = currentDeletedIds.join(',');
                        }
                    }
                }
                this.closest('.variant-card').remove(); 
                updateDefaultVariantRadioAndHiddenFields(); 
            });
        }
        const defaultRadioEl = variantCard.querySelector('.variant-default-radio');
        if(defaultRadioEl) defaultRadioEl.addEventListener('change', handleDefaultVariantChange);
        
        if (existingIndex === null) { // Chỉ tăng variantIndex nếu là biến thể mới
            variantIndex++;
        }
        if (typeof lucide !== 'undefined') lucide.createIcons(); 
        updateDefaultVariantRadioAndHiddenFields(); 
    }


    if (addVariantButton) {
        addVariantButton.addEventListener('click', () => addVariantCard(null, null));
    }

    function handleDefaultVariantChange(event) {
        document.querySelectorAll('.variant-default-radio').forEach(radio => {
            const card = radio.closest('.variant-card');
            if (card) {
                const hiddenInput = card.querySelector('.is-default-hidden-input');
                if (hiddenInput) hiddenInput.value = (radio === event.target && radio.checked) ? "true" : "false";
            }
        });
    }
    
    function updateDefaultVariantRadioAndHiddenFields() {
        const defaultRadios = document.querySelectorAll('.variant-default-radio');
        let oneIsChecked = false;
        defaultRadios.forEach(radio => { if (radio.checked) oneIsChecked = true; });
        if (!oneIsChecked && defaultRadios.length > 0) defaultRadios[0].checked = true; 
        defaultRadios.forEach(radio => {
            const card = radio.closest('.variant-card');
            if (card) {
                const hiddenInput = card.querySelector('.is-default-hidden-input');
                if(hiddenInput) hiddenInput.value = radio.checked ? "true" : "false";
            }
        });
    }

    function previewCoverImage(event) {
        const container = document.getElementById('coverImagePreviewContainer');
        if (!container) return; 
        // Xóa preview cũ nếu có, nhưng giữ lại ảnh hiện tại nếu chưa chọn ảnh mới
        const existingCover = container.querySelector('#existing_cover_image');

        const file = event.target.files[0];
        if (file) {
            if(existingCover) existingCover.style.display = 'none'; // Ẩn ảnh cũ khi có ảnh mới
            // Xóa các preview ảnh mới cũ (nếu có)
            container.querySelectorAll('.new-image-preview').forEach(el => el.remove());

            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview new-image-preview'; // Thêm class để phân biệt
                div.innerHTML = `<img src="${e.target.result}" alt="Cover Image Preview"><span class="remove-img-btn" onclick="removeNewCoverImage(event)"><i data-lucide="x" class="h-3 w-3"></i></span>`;
                container.appendChild(div);
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
            reader.readAsDataURL(file);
        } else {
            // Nếu không chọn file mới, hiển thị lại ảnh cũ (nếu có)
            if(existingCover) existingCover.style.display = 'flex';
            container.querySelectorAll('.new-image-preview').forEach(el => el.remove());
        }
    }
    function removeNewCoverImage(event) { // Đổi tên hàm để chỉ xóa preview ảnh mới
        const coverImageInput = document.getElementById('cover_image_file');
        if(coverImageInput) coverImageInput.value = ''; 
        const previewElement = event.target.closest('.new-image-preview');
        if(previewElement) previewElement.remove(); 
        // Hiển thị lại ảnh cũ nếu có
        const existingCover = document.getElementById('existing_cover_image');
        if (existingCover) existingCover.style.display = 'flex';
    }

    function previewGalleryImages(event) {
        const container = document.getElementById('galleryImagesPreviewContainer');
        if (!container) return; 
        // Không xóa ảnh cũ, chỉ thêm preview cho ảnh mới
        const files = event.target.files;
        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'image-preview new-gallery-image-preview'; // Class để phân biệt
                div.innerHTML = `<img src="${e.target.result}" alt="Gallery Image ${index + 1}"><span class="remove-img-btn" onclick="removeSpecificNewGalleryImage(this, '${file.name}')"><i data-lucide="x" class="h-3 w-3"></i></span>`;
                container.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    
    function removeSpecificNewGalleryImage(element, fileNameToRemove) {
        const galleryInput = document.getElementById('gallery_image_files');
        if (!galleryInput) return;
        const currentFiles = Array.from(galleryInput.files);
        const newFiles = currentFiles.filter(file => file.name !== fileNameToRemove);
        const dataTransfer = new DataTransfer();
        newFiles.forEach(file => dataTransfer.items.add(file));
        galleryInput.files = dataTransfer.files;
        const previewElement = element.closest('.new-gallery-image-preview');
        if (previewElement) previewElement.remove();
    }

    document.querySelectorAll('.delete-existing-img-btn').forEach(button => {
        button.addEventListener('click', function() {
            const imageId = this.dataset.id;
            if (imageId && deletedGalleryImageIdsInput) {
                if (!deletedGalleryImageIds.includes(imageId)) {
                    deletedGalleryImageIds.push(imageId);
                }
                deletedGalleryImageIdsInput.value = deletedGalleryImageIds.join(',');
                this.closest('.existing-gallery-image').remove();
                showMessageModal('Thông báo', `Đã đánh dấu xóa ảnh gallery ID: ${imageId}. Thay đổi sẽ được áp dụng khi bạn cập nhật sản phẩm.`, 'info');
            }
        });
    });


    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    if (nameInput && slugInput) {
        nameInput.addEventListener('keyup', () => {
            if (slugInput.placeholder === 'Tự động tạo nếu để trống' || slugInput.dataset.auto === "true") {
                 slugInput.value = slugify(nameInput.value);
            }
        });
        slugInput.addEventListener('input', () => {
            slugInput.dataset.auto = slugInput.value.trim() === "" ? "true" : "false";
        });
    }
    function slugify(text) {
        if (!text) return '';
        let str = text.toString().toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/\s+/g, '-').replace(/[^\w-]+/g, '').replace(/--+/g, '-').replace(/^-+/, '').replace(/-+$/, '');            
        return str;
    }

    document.addEventListener('DOMContentLoaded', () => {
        populateAttributeCheckboxes(); // Gọi trước để các checkbox sẵn sàng
        toggleProductTypeFields(); 
        if (typeof lucide !== 'undefined') lucide.createIcons();
        if (slugInput) slugInput.dataset.auto = "true"; 
        
        // Load existing variants or old variants data
        const productType = "{{ $product->type }}";
        const oldVariantsData = @json(old('variants', []));
        
        let variantsToLoad = [];
        if (Array.isArray(oldVariantsData) && oldVariantsData.length > 0) {
            variantsToLoad = oldVariantsData; // Ưu tiên old data nếu có lỗi validation
        } else if (Array.isArray(productVariantsFromPHP) && productVariantsFromPHP.length > 0) {
            variantsToLoad = productVariantsFromPHP;
        }

        if (productType === 'variable' && variantsToLoad.length > 0) {
            let maxExistingIndex = -1;
            variantsToLoad.forEach((variantData, index) => {
                addVariantCard(variantData, index); // Truyền index để giữ đúng thứ tự
                maxExistingIndex = index;
            });
            variantIndex = maxExistingIndex + 1; // Đặt variantIndex cho các biến thể mới
        }
        updateDefaultVariantRadioAndHiddenFields(); // Cập nhật trạng thái mặc định ban đầu
    });

    const form = document.getElementById('editProductForm'); // Đổi ID form
    if(form){
        form.addEventListener('submit', function(event) {
            const productType = "{{ $product->type }}"; // Lấy loại sản phẩm từ PHP
            if (productType === 'variable') { // Chỉ kiểm tra nếu là sản phẩm biến thể
                if (!variantsContainer || variantsContainer.children.length === 0) {
                    showMessageModal('Cảnh báo', 'Sản phẩm có biến thể phải có ít nhất một biến thể được thêm vào.', 'error'); event.preventDefault(); return;
                }
                let oneDefault = false;
                document.querySelectorAll('.variant-default-radio').forEach(radio => { if (radio.checked) oneDefault = true; });
                if (!oneDefault && variantsContainer && variantsContainer.children.length > 0) {
                     showMessageModal('Cảnh báo', 'Vui lòng chọn một biến thể làm mặc định.', 'error'); event.preventDefault(); return;
                }
            }
        });
    }
    
    const observer = new MutationObserver(mutations => {
        for (const mutation of mutations) { if (mutation.type === 'childList' && typeof lucide !== 'undefined') { lucide.createIcons(); } }
    });
    if(variantsContainer) observer.observe(variantsContainer, { childList: true });
    if(productAttributesContainer) observer.observe(productAttributesContainer, { childList: true });
</script>
@endpush

