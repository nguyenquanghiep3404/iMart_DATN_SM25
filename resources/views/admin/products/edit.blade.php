@extends('admin.layouts.app')

@section('title', 'Chỉnh Sửa Sản Phẩm')

@push('styles')
    {{-- 1. CSS CỦA TAGIFY --}}
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

    {{-- Link Font Awesome để hiển thị icon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

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
        
        .input-group { margin-bottom: 1.25rem; }
        .input-group label { display: block; color: #4b5563; font-weight: 500; margin-bottom: 0.5rem; }
        .input-group label.flex { display: flex; margin-bottom: 0; }
        .input-group .form-section-heading { margin-bottom: 0.5rem; }
        
        .input-field, .select-field, .textarea-field {
            width: 100%; padding: 0.875rem 1.125rem; border: 1px solid #cbd5e1; border-radius: 0.625rem;
            box-shadow: inset 0 1px 2px 0 rgba(0, 0, 0, 0.03); transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out; background-color: #f8fafc;
        }
        .input-field:focus, .select-field:focus, .textarea-field:focus {
            outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); background-color: white;
        }

        /* Style cho trình soạn thảo TinyMCE */
        .tox-tinymce { border-radius: 0.625rem !important; border: 1px solid #cbd5e1 !important; }
        .tox:not(.tox-fullscreen) .tox-toolbar-overlord { border-top-right-radius: 0.625rem !important; border-top-left-radius: 0.625rem !important; }
        .tox .tox-statusbar { border-bottom-right-radius: 0.625rem !important; border-bottom-left-radius: 0.625rem !important; }

        /* 2. CUSTOM CSS CHO TAGIFY */
        .tagify { width: 100%; --tags-border-color: #cbd5e1; --tag-bg: #2563eb; --tag-hover: #1d4ed8; --tag-text-color: white; --tag-remove-btn-color: white; border-radius: 0.625rem; background-color: #f8fafc; }
        .tagify:hover { --tags-border-color: #94a3b8; }
        .tagify.tagify--focus { --tags-border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); background-color: white; }
        .tagify__input { padding: 0.875rem 1.125rem; }
        .tagify__tag { border-radius: 0.375rem; margin: 4px; }
        .tagify-error .tagify { --tags-border-color: #ef4444 !important; }

        .btn { padding: 0.875rem 1.75rem; border-radius: 0.625rem; font-weight: 600; transition: all 0.2s ease-in-out; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-transform: uppercase; letter-spacing: 0.025em; border: 1px solid transparent; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.875rem; }
        .btn-primary { background-color: #2563eb; color: white; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3), 0 2px 4px -2px rgba(37, 99, 235, 0.2); }
        .btn-primary:hover { background-color: #1d4ed8; box-shadow: 0 6px 10px -1px rgba(29, 78, 216, 0.4), 0 4px 6px -2px rgba(29, 78, 216, 0.3); transform: translateY(-1px); }
        .btn-secondary { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-secondary:hover { background-color: #e2e8f0; border-color: #cbd5e1; transform: translateY(-1px); }
        .btn-danger { background-color: #ef4444; color: white; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3), 0 2px 4px -2px rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background-color: #dc2626; box-shadow: 0 6px 10px -1px rgba(220, 38, 38, 0.4), 0 4px 6px -2px rgba(220, 38, 38, 0.3); transform: translateY(-1px); }
        .btn-ai { background: linear-gradient(to right, #6366f1, #a855f7); color: white; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.4), 0 2px 4px -2px rgba(168, 85, 247, 0.3); }
        .btn-ai:hover { box-shadow: 0 6px 10px -1px rgba(99, 102, 241, 0.5), 0 4px 6px -2px rgba(168, 85, 247, 0.4); transform: translateY(-1px); }
        .btn-ai .loading-spinner { width: 16px; height: 16px; border: 2px solid rgba(255, 255, 255, 0.3); border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .image-preview-container { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.75rem; }
        .image-preview { position: relative; width: 120px; height: 120px; border-radius: 0.5rem; overflow: hidden; border: 2px dashed #cbd5e1; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; }
        .image-preview img { width: 100%; height: 100%; object-fit: cover; }
        .image-preview .remove-img-btn { position: absolute; top: 6px; right: 6px; background-color: rgba(220, 38, 38, 0.8); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; transition: background-color 0.2s; z-index: 10; }
        .image-preview .remove-img-btn:hover { background-color: #dc2626; }

        /* Styles for variant and simple product image previews */
        .variant-image-preview-item { position: relative; width: 90px; height: 90px; border-radius: 0.5rem; overflow: hidden; border: 2px solid #e2e8f0; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; }
        .variant-image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .variant-image-preview-item.is-primary { border: 3px solid #2563eb; box-shadow: 0 0 8px rgba(37, 99, 235, 0.5); }
        .variant-image-preview-item .set-primary-btn { position: absolute; bottom: 4px; left: 4px; background-color: rgba(0, 0, 0, 0.6); color: white; padding: 3px 5px; border-radius: 4px; font-size: 0.7rem; cursor: pointer; z-index: 10; display: none; align-items: center; }
        .variant-image-preview-item:hover .set-primary-btn { display: inline-flex; }
        .variant-image-preview-item.is-primary .set-primary-btn { display: none; }
        .variant-image-preview-item .set-primary-btn:hover { background-color: rgba(37, 99, 235, 0.9); }
        .variant-image-preview-item .set-primary-btn i { width: 12px; height: 12px; margin-right: 3px; }

        .variant-card { border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 1.25rem; margin-bottom: 1.25rem; background-color: #f8fafc; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03); }
        .variant-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
        .variant-title { font-weight: 600; color: #0f172a; font-size: 1.1rem; }
        
        .form-check-input { height: 1.125rem; width: 1.125rem; margin-top: 0.125rem; border-color: #94a3b8; }
        .form-check-input:checked { background-color: #2563eb; border-color: #2563eb; }
        .required-star { color: #ef4444; font-weight: bold; }
        .input-with-icon { position: relative; }
        .input-with-icon .icon-prefix { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .input-with-icon .input-field, .input-with-icon .select-field { padding-left: 2.75rem; }
        .label-with-action { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.625rem; }
        .label-with-action label { margin-bottom: 0; }
        .svg-icon { width: 1.25rem; height: 1.25rem; stroke-width: 1.5; stroke: currentColor; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .icon-prefix { width: 1rem; height: 1rem; }
        .card-header .svg-icon { width: 1.25rem; height: 1.25rem; margin-right: 0.5rem; }
        button .svg-icon { margin-right: 0.5rem; }

        /* CSS cho Media Library Modal */
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.6); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border-radius: 0.75rem; display: flex; flex-direction: column; }
        .modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .close-btn { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
        .close-btn:hover { opacity: 1; color: #1f2937; }
        .modal-body { position: relative; flex: 1 1 auto; padding: 0; color: #374151; overflow-y: hidden; }
        .modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; }
        #selection-modal .modal-content { max-width: 90vw; width: 1280px; height: 90vh; }
        .image-card.selected { box-shadow: 0 0 0 3px #2563eb; border-color: #2563eb; }
        .tab-link { padding: 0.75rem 1.25rem; border-bottom: 3px solid transparent; color: #6b7280; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .tab-link.active { color: #2563eb; border-color: #2563eb; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        #drop-zone { border: 2px dashed #d1d5db; border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.2s; background-color: #f8fafc; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        #drop-zone.drag-over { border-color: #3b82f6; background-color: #eff6ff; }
        .hidden { display: none !important; }
    </style>
@endpush

@section('content')

    <div class="container mx-auto p-4 md:p-8 max-w-7xl">
        <header class="mb-10 flex items-center justify-between">
            <div >
                <h1 class="text-4xl font-bold text-gray-800">
                    Chỉnh Sửa Sản Phẩm
                </h1>
                <p class="text-gray-600 mt-1">Cập nhật thông tin cho sản phẩm: <span class="font-semibold text-blue-700">{{ $product->name }}</span></p>
            </div>
            <!-- Nút quay lại -->
			<div class="mb-6">
				<a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
					<svg class="svg-icon" viewBox="0 0 24 24"><polyline points="19 12 5 12"></polyline><polyline points="12 19 5 12 12 5"></polyline></svg>
					Quay Lại Danh Sách
				</a>
			</div>
        </header>
        
        {{-- Display Errors and Success Messages --}}
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
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <div class="flex items-center">
                     <svg class="svg-icon text-green-500 mr-2" viewBox="0 0 24 24">
                          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                          <polyline points="22 4 12 14.01 9 11.01"></polyline>
                      </svg>
                    <div>
                        <p class="font-bold">Thành công!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
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
                            <svg class="svg-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            Thông Tin Chung
                        </div>
                        <div class="input-group">
                            <label for="name">Tên sản phẩm <span class="required-star">*</span></label>
                            <div class="input-with-icon">
                                <svg class="svg-icon icon-prefix" viewBox="0 0 24 24"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg>
                                <input type="text" id="name" name="name"
                                    class="input-field @error('name') border-red-500 @enderror" value="{{ old('name', $product->name) }}">
                            </div>
                            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="input-group">
                            <label for="slug">Đường dẫn thân thiện (Slug)</label>
                            <div class="input-with-icon">
                                <svg class="svg-icon icon-prefix" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                <input type="text" id="slug" name="slug"
                                    class="input-field @error('slug') border-red-500 @enderror" value="{{ old('slug', $product->slug) }}">
                            </div>
                            @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="input-group">
                            <label for="short_description">Mô tả ngắn</label>
                            <textarea id="short_description" name="short_description"
                                class="textarea-field @error('short_description') border-red-500 @enderror" rows="3">{{ old('short_description', $product->short_description) }}</textarea>
                            @error('short_description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="input-group">
                            <label for="description">Mô tả chi tiết</label>
                            <textarea id="description" name="description"
                                class="@error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                    </div>

                    {{-- Card Loại Sản Phẩm & Biến Thể --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><circle cx="18" cy="6" r="3"></circle><path d="M18 9v1a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V9"></path><path d="M12 12v3"></path></svg>
                            Loại Sản Phẩm & Biến Thể
                        </div>
                        <div class="input-group">
                            <label>Loại sản phẩm <span class="required-star">*</span> <span class="text-sm text-red-600 font-normal">(Không thể thay đổi)</span></label>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center cursor-not-allowed p-2 rounded-md opacity-70">
                                    <input type="radio" name="type" value="simple" class="form-check-input mr-2"
                                        {{ $product->type == 'simple' ? 'checked' : '' }} disabled> Đơn giản
                                </label>
                                <label class="flex items-center cursor-not-allowed p-2 rounded-md opacity-70">
                                    <input type="radio" name="type" value="variable" class="form-check-input mr-2"
                                        {{ $product->type == 'variable' ? 'checked' : '' }} disabled> Có biến thể
                                </label>
                            </div>
                            {{-- Giữ lại input ẩn để gửi giá trị type đi --}}
                            <input type="hidden" name="type" value="{{ $product->type }}">
                        </div>

                        {{-- Trường cho sản phẩm đơn giản --}}
                        <div id="simpleProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200"
                             style="{{ $product->type == 'simple' ? '' : 'display:none;' }}">
                            @php
                                $simpleVariant = $product->type === 'simple' ? $product->variants->first() : null;
                            @endphp
                            <h3 class="text-lg font-semibold text-gray-700 mb-1">Thông tin sản phẩm đơn giản</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                <div class="input-group">
                                    <label for="simple_sku">SKU <span class="required-star">*</span></label>
                                    <input type="text" id="simple_sku" name="simple_sku" class="input-field" value="{{ old('simple_sku', $simpleVariant->sku ?? '') }}">
                                </div>
                                <div class="input-group">
                                    <label for="simple_price">Giá bán <span class="required-star">*</span> (VNĐ)</label>
                                    <input type="number" id="simple_price" name="simple_price" class="input-field" step="1000" min="0" value="{{ old('simple_price', $simpleVariant->price ?? '') }}">
                                </div>
                                <div class="input-group">
                                    <label for="simple_sale_price">Giá khuyến mãi (VNĐ)</label>
                                    <input type="number" id="simple_sale_price" name="simple_sale_price" class="input-field" step="1000" min="0" value="{{ old('simple_sale_price', $simpleVariant->sale_price ?? '') }}">
                                </div>
                                <div class="input-group">
                                    <label for="simple_stock_quantity">Số lượng tồn kho <span class="required-star">*</span></label>
                                    <input type="number" id="simple_stock_quantity" name="simple_stock_quantity" class="input-field" min="0" value="{{ old('simple_stock_quantity', $simpleVariant->stock_quantity ?? '') }}">
                                </div>
                            </div>
                            <div class="input-group md:col-span-2 pt-4 mt-4 border-t border-gray-200">
                                <label class="form-section-heading">Ảnh Sản Phẩm <span class="required-star">*</span></label>
                                <div class="flex space-x-2 mb-3">
                                    <label for="simple_product_image_input" class="btn btn-secondary btn-sm cursor-pointer"><i class="fas fa-upload mr-2"></i> Tải ảnh lên</label>
                                    <input type="file" id="simple_product_image_input" class="hidden" accept="image/*" multiple onchange="handleSimpleProductImages(event)">
                                    <button type="button" id="open-library-btn-simple" class="btn btn-secondary btn-sm"><i class="fas fa-photo-video mr-2"></i> Thêm từ thư viện</button>
                                </div>
                                <div id="simple_product_image_preview_container" class="image-preview-container mt-2"></div>
                                <div id="simple_product_image_ids_container" class="hidden"></div>
                            </div>
                        </div>

                        {{-- Trường cho sản phẩm có biến thể --}}
                        <div id="variableProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200"
                             style="{{ $product->type == 'variable' ? '' : 'display:none;' }}">
                            <h3 class="text-lg font-semibold text-gray-700 mb-1">Quản lý biến thể</h3>
                            <div class="input-group">
                                <label for="sku_prefix">Tiền tố SKU (cho biến thể)</label>
                                <input type="text" id="sku_prefix" name="sku_prefix" class="input-field" value="{{ old('sku_prefix', $product->sku_prefix) }}" placeholder="Ví dụ: APPL-IP15P-">
                            </div>
                            <div class="input-group">
                                <label class="flex items-center mb-2">Thuộc tính sử dụng cho biến thể</label>
                                <div id="productAttributesContainer" class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2 p-3 border border-gray-200 rounded-md bg-gray-50"></div>
                            </div>
                            <div id="variantsContainer" class="space-y-5"></div>
                            <button type="button" id="addVariantButton" class="btn btn-secondary mt-2"><i class="fas fa-plus-circle mr-2"></i> Thêm Biến Thể Mới</button>
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Tổ chức, SEO, etc. --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="card">
                        <div class="card-header"><svg class="svg-icon" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>Xuất Bản</div>
                        <div class="input-group">
                            <label for="status">Trạng thái <span class="required-star">*</span></label>
                            <select id="status" name="status" class="select-field">
                                <option value="published" @selected(old('status', $product->status) == 'published')>Đã xuất bản</option>
                                <option value="draft" @selected(old('status', $product->status) == 'draft')>Bản nháp</option>
                                <option value="pending_review" @selected(old('status', $product->status) == 'pending_review')>Chờ duyệt</option>
                                <option value="trashed" @selected(old('status', $product->status) == 'trashed')>Đã xóa (ẩn)</option>
                            </select>
                        </div>
                        <div class="input-group mt-5">
                            <label class="flex items-center cursor-pointer p-2 rounded-md hover:bg-blue-50 transition-colors">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" class="form-check-input" @checked(old('is_featured', $product->is_featured))>
                                <div class="flex items-center ml-2">
                                    <svg class="svg-icon h-5 w-5 text-yellow-500" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    <span class="ml-2 text-gray-700 font-medium">Sản phẩm nổi bật</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><svg class="svg-icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>Tổ Chức</div>
                        <div class="input-group">
                            <label for="category_id">Danh mục <span class="required-star">*</span></label>
                            <select id="category_id" name="category_id" class="select-field">
                                <option value="">Chọn danh mục</option>
                                @if (!empty($categories))
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="tags">Thẻ sản phẩm (Tags)</label>
                            <input type="text" id="tags" name="tags" value="{{ old('tags', $product->tags) }}" placeholder="Nhập thẻ và nhấn Enter">
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><svg class="svg-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><path d="M11 8l2 2"></path></svg>Tối Ưu Hóa SEO</div>
                        <div class="input-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" id="meta_title" name="meta_title" class="input-field" value="{{ old('meta_title', $product->meta_title) }}">
                        </div>
                        <div class="input-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" class="textarea-field" rows="3">{{ old('meta_description', $product->meta_description) }}</textarea>
                        </div>
                        <div class="input-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" class="input-field" value="{{ old('meta_keywords', $product->meta_keywords) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-end space-x-4">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Hủy Bỏ</a>
                <button type="submit" class="btn btn-primary">Cập Nhật Sản Phẩm</button>
            </div>
        </form>
    </div>
    
    @include('admin.partials.media_selection_modal')
@endsection

@push('scripts')
    {{-- Scripts for TinyMCE and Tagify --}}
    <script src="https://cdn.tiny.cloud/1/polil4haaavbgscm984gn9lw0zb9xx9hjopkrx9k2ofql26b/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

    <script>
        // =================================================================
        // KHỞI TẠO BIẾN TOÀN CỤC VÀ DỮ LIỆU TỪ PHP
        // =================================================================
        let variantIndexGlobal = 0;
        let tagify;
        window.mediaLibraryTarget = null;
        let selectedProductAttributes = [];

        // --- Data from PHP (Laravel Backend) ---
        const productData = @json($product->load('variants.attributeValues', 'coverImage', 'galleryImages', 'variants.images'), JSON_UNESCAPED_UNICODE);
        const allAttributesFromPHP = @json($attributes, JSON_UNESCAPED_UNICODE);
        const oldVariantsData = @json(old('variants'), JSON_UNESCAPED_UNICODE); // Keep this for validation errors

        // Helper function to get all image data for repopulation
        function getAllImagesFromProduct(product) {
            const allImages = {};
            if (product.cover_image) {
                allImages[product.cover_image.id] = {id: product.cover_image.id, url: product.cover_image.url, alt_text: product.cover_image.alt_text};
            }
            product.gallery_images.forEach(img => {
                allImages[img.id] = {id: img.id, url: img.url, alt_text: img.alt_text};
            });
            product.variants.forEach(variant => {
                if (variant.primary_image_id) {
                    const primaryImg = variant.images.find(img => img.id === variant.primary_image_id);
                     if(primaryImg) {
                        allImages[primaryImg.id] = {id: primaryImg.id, url: primaryImg.url, alt_text: primaryImg.alt_text};
                    }
                }
                variant.images.forEach(img => {
                    allImages[img.id] = {id: img.id, url: img.url, alt_text: img.alt_text};
                });
            });
            return allImages;
        }

        const oldImagesData = @json($old_images_data ?? getAllImagesFromProduct($product), JSON_UNESCAPED_UNICODE);
        const oldCoverImageId = @json(old('cover_image_id', $product->cover_image_id), JSON_UNESCAPED_UNICODE) || null;
        
        // (The rest of the JS functions: uploadFilesViaAjax, addImagesToProductForm, etc. remain the same as in create.blade.php)
        // ... PASTE ALL HELPER JS FUNCTIONS FROM `create.blade.php` HERE ...
        // For brevity, I'll omit them here, but you should copy them from your create file.
        // The important part is the initialization logic in DOMContentLoaded.
        async function uploadFilesViaAjax(files, context = 'products') {
            // ... (Same as create.blade.php)
        }
        function addImagesToProductForm(images, previewContainer, idsContainer, type = 'simple', variantIndex = null) {
            // ... (Same as create.blade.php)
        }
        // ... All other helper functions ...

        // =================================================================
        // PHẦN DOMContentLoaded: KHỞI TẠO CÁC THÀNH PHẦN
        // =================================================================
        document.addEventListener('DOMContentLoaded', () => {
            // --- KHỞI TẠO CÁC THÀNH PHẦN CHUNG ---
            tinymce.init({ selector: 'textarea#description', /* ... options ... */ });
            const tagsInput = document.getElementById('tags');
            if (tagsInput) {
                tagify = new Tagify(tagsInput, { /* ... options ... */ });
            }

            // --- MAIN LOGIC FOR POPULATING THE FORM ---
            
            // Check if there's old data from a validation error
            const hasOldVariantData = Array.isArray(oldVariantsData) && oldVariantsData.length > 0;

            if (productData.type === 'simple') {
                repopulateSimpleProduct(hasOldVariantData);
            } else if (productData.type === 'variable') {
                repopulateVariableProduct(hasOldVariantData);
            }

            // ... (Rest of the event listeners for AI buttons, slugify etc. from create.blade.php) ...
        });

        function repopulateSimpleProduct(hasOldData) {
            const previewContainer = document.getElementById('simple_product_image_preview_container');
            const idsContainer = document.getElementById('image_ids_container');
            let imageIdsToLoad, primaryImageId;

            if (hasOldData) { // If validation failed, use old data
                imageIdsToLoad = @json(old('gallery_images', []), JSON_UNESCAPED_UNICODE);
                primaryImageId = @json(old('cover_image_id'), JSON_UNESCAPED_UNICODE);
            } else { // Otherwise, use data from the database
                imageIdsToLoad = productData.gallery_images.map(img => img.id);
                if (productData.cover_image) {
                    imageIdsToLoad.push(productData.cover_image.id);
                }
                primaryImageId = productData.cover_image_id;
            }

            const uniqueImageIds = [...new Set(imageIdsToLoad)];
            const images = uniqueImageIds.map(id => oldImagesData[id]).filter(Boolean);
            
            if (images.length > 0) {
                addImagesToProductForm(images, previewContainer, idsContainer, 'simple');
                if (primaryImageId) {
                    setSimpleProductPrimaryImage(parseInt(primaryImageId));
                }
            }
        }

        function repopulateVariableProduct(hasOldData) {
            const variantsToPopulate = hasOldData ? oldVariantsData : productData.variants;
            
            // Determine which attributes are checked based on the data
            const usedAttributeIds = new Set();
            variantsToPopulate.forEach(variant => {
                const attributes = hasOldData ? variant.attributes : variant.attribute_values.map(av => av.attribute_id);
                if (attributes) {
                    const attrKeys = hasOldData ? Object.keys(attributes) : attributes;
                    attrKeys.forEach(id => usedAttributeIds.add(parseInt(id)));
                }
            });
            
            document.querySelectorAll('.product-attribute-checkbox').forEach(cb => {
                if (usedAttributeIds.has(parseInt(cb.value))) {
                    cb.checked = true;
                }
            });
            updateSelectedAttributesForVariants();

            // Create and populate variant cards
            variantsToPopulate.forEach(variantData => {
                addVariantButton.click(); // Create a new empty card
                const currentVariantIndex = variantIndexGlobal - 1;
                const card = document.querySelector(`.variant-card[data-variant-index="${currentVariantIndex}"]`);
                if (!card) return;

                // Populate fields
                card.querySelector(`input[name="variants[${currentVariantIndex}][id]"]`).value = hasOldData ? (variantData.id || '') : variantData.id;
                card.querySelector(`input[name="variants[${currentVariantIndex}][sku]"]`).value = variantData.sku;
                card.querySelector(`input[name="variants[${currentVariantIndex}][price]"]`).value = variantData.price;
                card.querySelector(`input[name="variants[${currentVariantIndex}][stock_quantity]"]`).value = variantData.stock_quantity;
                
                // Populate attributes
                const attributes = hasOldData ? variantData.attributes : variantData.attribute_values.reduce((acc, av) => {
                    acc[av.attribute_id] = av.id;
                    return acc;
                }, {});

                if (attributes) {
                    for (const [attrId, attrValueId] of Object.entries(attributes)) {
                        const select = card.querySelector(`select[name="variants[${currentVariantIndex}][attributes][${attrId}]"]`);
                        if (select) select.value = attrValueId;
                    }
                }

                // Populate images
                let imageIdsToLoad, primaryImageId;
                if (hasOldData) {
                    imageIdsToLoad = variantData.image_ids || [];
                    primaryImageId = variantData.primary_image_id;
                } else {
                    imageIdsToLoad = variantData.images.map(img => img.id);
                    primaryImageId = variantData.primary_image_id;
                }

                const uniqueImageIds = [...new Set(imageIdsToLoad)];
                const images = uniqueImageIds.map(id => oldImagesData[id]).filter(Boolean);
                if (images.length > 0) {
                    const previewContainer = document.getElementById(`variant_${currentVariantIndex}_image_preview_container`);
                    const idsContainer = document.getElementById(`variant_${currentVariantIndex}_image_ids_container`);
                    addImagesToProductForm(images, previewContainer, idsContainer, 'variant', currentVariantIndex);
                    if (primaryImageId) {
                        setVariantPrimaryImage(currentVariantIndex, parseInt(primaryImageId));
                    }
                }

                // Set default radio
                const isDefault = hasOldData ? (variantData.is_default === 'true') : variantData.is_default;
                if (isDefault) {
                     card.querySelector(`input[name="variant_is_default_radio_group"]`).checked = true;
                }
            });
            updateDefaultVariantRadioAndHiddenFields();
        }

    </script>
@endpush

