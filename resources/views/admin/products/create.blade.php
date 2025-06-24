@extends('admin.layouts.app')

@section('title', 'Thêm sản phẩm mới')

@push('styles')
    {{-- 1. CSS CỦA TAGIFY --}}
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

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
        .input-group label.flex {display: flex;margin-bottom: 0;}
        .input-group .form-section-heading {
        margin-bottom: 0;
        }
    </style>
@endpush

@section('content')

    <div class="container mx-auto p-4 md:p-8 max-w-7xl">
        <header class="mb-10 flex items-center justify-between">
            <div >
                <h1 class="text-4xl font-bold text-gray-800">
                    Thêm Sản Phẩm Mới <span class="text-2xl text-purple-600">✨AI</span>
                </h1>
                <p class="text-gray-600 mt-1">Cung cấp thông tin chi tiết để tạo sản phẩm Apple mới, với sự trợ giúp của AI!
                </p>
            </div>
            <!-- Nút quay lại -->
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
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-md shadow-md"
                role="alert">
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
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-md shadow-md" role="alert">
                <div class="flex items-center">
                    <svg class="svg-icon text-red-500 mr-2" viewBox="0 0 24 24">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    <div>
                        <p class="font-bold">Lỗi!</p>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
        <form id="addProductForm" action="{{ route('admin.products.store') }}" method="POST">
            @csrf
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
                                <div class="input-with-icon">
                                    <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                        <polyline points="4 7 4 4 20 4 20 7"></polyline>
                                        <line x1="9" y1="20" x2="15" y2="20"></line>
                                        <line x1="12" y1="4" x2="12" y2="20"></line>
                                    </svg>
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
                                    <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                    </svg>
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

                        {{-- Tích hợp trình soạn thảo WYSIWYG --}}
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="description">Mô tả chi tiết</label>
                                <button type="button" id="generateLongDescAI" class="btn btn-ai btn-sm">
                                    <span class="button-text">✨ Tạo bằng AI</span>
                                    <span class="loading-spinner hidden"></span>
                                </button>
                            </div>
                            <textarea id="description" name="description"
                                class="@error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
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
                                        <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                                <path d="M4 7V4h16v3"></path>
                                                <path d="M9 20h6"></path>
                                                <path d="M12 4v16"></path>
                                            </svg>
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
                                        <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                            </svg>
                                        <input type="number" id="simple_price" name="simple_price"
                                            class="input-field @error('simple_price') border-red-500 @enderror"
                                            step="1000" min="0" value="{{ old('simple_price') }}">
                                    </div>
                                    @error('simple_price')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <div class="label-with-action">
                                        <label for="simple_sale_price">Giá khuyến mãi (VNĐ)</label>
                                        <a href="javascript:void(0);" onclick="toggleSchedule(this)" class="text-blue-600 text-sm font-medium hover:underline">Lên lịch</a>
                                    </div>
                                    <div class="input-with-icon">
                                        <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M8 15l8-8"></path>
                                            <path d="M9.5 9.5h.01"></path>
                                            <path d="M14.5 14.5h.01"></path>
                                        </svg>
                                        <input type="number" id="simple_sale_price" name="simple_sale_price" class="input-field @error('simple_sale_price') border-red-500 @enderror" step="1000" min="0" value="{{ old('simple_sale_price') }}">
                                    </div>
                                    <div class="schedule-container {{ old('simple_sale_price_starts_at') || old('simple_sale_price_ends_at') ? '' : 'hidden' }} mt-2 grid grid-cols-2 gap-x-4">
                                        <div>
                                            <label for="simple_sale_price_starts_at" class="text-xs">Ngày bắt đầu</label>
                                            <input type="date" name="simple_sale_price_starts_at" id="simple_sale_price_starts_at" class="input-field text-sm" value="{{ old('simple_sale_price_starts_at') }}">
                                        </div>
                                        <div>
                                            <label for="simple_sale_price_ends_at" class="text-xs">Ngày kết thúc</label>
                                            <input type="date" name="simple_sale_price_ends_at" id="simple_sale_price_ends_at" class="input-field text-sm" value="{{ old('simple_sale_price_ends_at') }}">
                                        </div>
                                    </div>
                                    @error('simple_sale_price')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <label for="simple_stock_quantity">Số lượng tồn kho <span class="required-star">*</span></label>
                                    <div class="input-with-icon">
                                        <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                        </svg>
                                        <input type="number" id="simple_stock_quantity" name="simple_stock_quantity" class="input-field @error('simple_stock_quantity') border-red-500 @enderror" min="0" value="{{ old('simple_stock_quantity') }}">
                                    </div>
                                    @error('simple_stock_quantity')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Weight and Dimensions for Simple Product --}}
                            <div class="pt-4 mt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                     <div class="input-group">
                                        <label for="simple_weight">Cân nặng (kg)</label>
                                        <input type="number" step="0.01" min="0" id="simple_weight" name="simple_weight" class="input-field" value="{{ old('simple_weight') }}">
                                        @error('simple_weight')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>
                                    <div class="input-group">
                                        <label>Kích thước (D x R x C) (cm)</label>
                                        <div class="grid grid-cols-3 gap-x-2">
                                            <input type="number" step="0.1" min="0" name="simple_dimensions_length" placeholder="Dài" class="input-field" value="{{ old('simple_dimensions_length') }}">
                                            <input type="number" step="0.1" min="0" name="simple_dimensions_width" placeholder="Rộng" class="input-field" value="{{ old('simple_dimensions_width') }}">
                                            <input type="number" step="0.1" min="0" name="simple_dimensions_height" placeholder="Cao" class="input-field" value="{{ old('simple_dimensions_height') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="input-group md:col-span-2 pt-4 mt-4 border-t border-gray-200">
                                <label >Ảnh Sản Phẩm <span class="required-star">*</span></label>
                                <div class="flex space-x-2 mb-3">
                                    <label  for="simple_product_image_input" class="form-section-heading btn btn-secondary btn-sm cursor-pointer">
                                        <i class="fas fa-upload mr-2"></i> Tải ảnh lên
                                    </label>
                                    <input type="file" id="simple_product_image_input" class="hidden" accept="image/*" multiple onchange="handleSimpleProductImages(event)">

                                    <button type="button" id="open-library-btn-simple" class="btn btn-secondary btn-sm">
                                    <svg class="svg-icon mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                                    Thêm từ thư viện
                                    </button>
                                </div>
                                
                                <div id="simple_product_image_preview_container" class="image-preview-container mt-2">
                                </div>
                                
                                <div id="simple_product_image_ids_container" class="hidden"></div>
                                
                                @error('cover_image_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Trường cho sản phẩm có biến thể --}}
                        <div id="variableProductFields" class="space-y-4 mt-6 pt-4 border-t border-gray-200"
                            style="{{ old('type') == 'variable' ? '' : 'display:none;' }}">
                            <h3 class="text-lg font-semibold text-gray-700 mb-1">Quản lý biến thể</h3>
                            <div class="input-group">
                                <label for="sku_prefix">Tiền tố SKU (cho biến thể)</label>
                                <div class="input-with-icon">
                                    <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                        <path d="M17 7l-10 10"></path>
                                        <path d="M8 7h9v9"></path>
                                    </svg>
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
                                    <svg class="svg-icon mr-2 h-5 w-5 text-gray-500" viewBox="0 0 24 24">
                                        <line x1="4" y1="21" x2="4" y2="14"></line>
                                        <line x1="4" y1="10" x2="4" y2="3"></line>
                                        <line x1="12" y1="21" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12" y2="3"></line>
                                        <line x1="20" y1="21" x2="20" y2="16"></line>
                                        <line x1="20" y1="12" x2="20" y2="3"></line>
                                        <line x1="1" y1="14" x2="7" y2="14"></line>
                                        <line x1="9" y1="8" x2="15" y2="8"></line>
                                        <line x1="17" y1="16" x2="23" y2="16"></line>
                                    </svg>
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
                                 <svg class="svg-icon mr-2 h-5 w-5" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="16"></line>
                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                    </svg> Thêm Biến Thể Mới
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Tổ chức, SEO, etc. --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Card Xuất Bản --}}
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
                            </svg>Xuất Bản
                        </div>
                        <div class="input-group">
                            <label for="status">Trạng thái <span class="required-star">*</span></label>
                            <div class="input-with-icon">
                                <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
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
    <label class="flex items-center cursor-pointer p-2 rounded-md hover:bg-blue-50 transition-colors">
        
        {{-- Checkbox --}}
        <input type="checkbox" id="is_featured" name="is_featured" value="1" 
               class="form-check-input">

        {{-- Thêm một DIV bọc icon và chữ, cũng sử dụng flex và items-center --}}
        <div class="flex items-center ml-2">
            {{-- Icon SVG --}}
            <svg class="svg-icon h-5 w-5 text-yellow-500" viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            
            {{-- Chữ --}}
            <span class="ml-2 text-gray-700 font-medium">
                Sản phẩm nổi bật
            </span>
        </div>

    </label>
</div>

                    </div>

                    {{-- Card Tổ Chức --}}
                    <div class="card">
                        <div class="card-header">
                            <svg class="svg-icon" viewBox="0 0 24 24">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>Tổ Chức
                        </div>
                        <div class="input-group">
                            <label for="category_id">Danh mục <span class="required-star">*</span></label>
                            <div class="input-with-icon">
                                <svg class="svg-icon icon-prefix" viewBox="0 0 24 24">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <select id="category_id" name="category_id"
                                    class="select-field @error('category_id') border-red-500 @enderror">
                                    <option value="">Chọn danh mục</option>
                                    @if (isset($categories) && count($categories) > 0) 
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

                        {{-- PHẦN TAGIFY --}}
                        <div class="input-group @error('tags') tagify-error @enderror">
                            <div class="label-with-action">
                                <label for="tags">Thẻ sản phẩm (Tags)</label>
                                <button type="button" id="generateTagsAI" class="btn btn-ai btn-sm">
                                    <span class="button-text">✨ Gợi ý</span>
                                    <span class="loading-spinner hidden"></span>
                                </button>
                            </div>
                            <input type="text" id="tags" name="tags"
                                value="{{ old('tags') }}"
                                placeholder="Nhập thẻ và nhấn Enter">
                            @error('tags')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <small class="text-gray-500 mt-1 block">Nhấn Enter hoặc dấu phẩy để thêm thẻ mới.</small>
                        </div>
                    </div>

                    {{-- Card SEO --}}
                    <div class="card">
                        <div class="card-header">
                             <svg class="svg-icon" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <path d="M11 8l2 2"></path>
                            </svg>Tối Ưu Hóa SEO
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
                             <svg class="svg-icon" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>Thông Tin Bổ Sung
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
                    <svg class="svg-icon mr-2 h-5 w-5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg> Hủy Bỏ
                </a>
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
                    <svg class="svg-icon mr-2 h-5 w-5" viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg> Lưu Sản Phẩm
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
     @include('admin.partials.media_selection_modal')
@endsection

@push('scripts')

    {{-- THÊM SCRIPT CỦA TINYMCE TỪ CDN --}}
    <script src="https://cdn.tiny.cloud/1/polil4haaavbgscm984gn9lw0zb9xx9hjopkrx9k2ofql26b/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    {{-- THÊM SCRIPT CỦA TAGIFY TỪ CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

  <script>
    // =================================================================
    // KHỞI TẠO BIẾN TOÀN CỤC VÀ DỮ LIỆU TỪ PHP
    // =================================================================
    let variantIndexGlobal = 0;
    let tagify;
    // Biến để theo dõi "ngữ cảnh" khi mở thư viện media
    window.mediaLibraryTarget = null;

    // --- Data from PHP (Laravel Backend) ---
    @php
        $jsCategoriesData = [];
        if (isset($categories) && $categories instanceof \Illuminate\Support\Collection) {
            $jsCategoriesData = $categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])->values()->all();
        }
        $jsAttributesData = [];
        if (isset($attributes) && $attributes instanceof \Illuminate\Support\Collection) {
            $jsAttributesData = $attributes->map(function ($attr) {
                $attributeValuesData = collect($attr->attributeValues ?? [])->map(fn($val) => [
                    'id' => $val->id ?? ($val['id'] ?? null),
                    'value' => $val->value ?? ($val['value'] ?? ''),
                    'meta' => $val->meta ?? ($val['meta'] ?? null),
                ])->all();
                return [
                    'id' => $attr->id,
                    'name' => $attr->name ?? 'N/A',
                    'slug' => $attr->slug ?? '',
                    'attributeValues' => $attributeValuesData,
                ];
            })->values()->all();
        }
    @endphp
    const categoriesFromPHP = @json($jsCategoriesData, JSON_UNESCAPED_UNICODE);
    const allAttributesFromPHP = @json($jsAttributesData, JSON_UNESCAPED_UNICODE);
    const oldVariantsData = @json(old('variants', []), JSON_UNESCAPED_UNICODE);

    // *** DỮ LIỆU MỚI: Nhận dữ liệu ảnh đã được server chuẩn bị sẵn để khôi phục ***
    const oldImagesData = @json($old_images_data ?? [], JSON_UNESCAPED_UNICODE);
    const oldCoverImageId = @json(old('cover_image_id'), JSON_UNESCAPED_UNICODE) || null;


    // =================================================================
    // KHỐI LOGIC UPLOAD ẢNH VÀ QUẢN LÝ ẢNH (KHÔNG ĐỔI)
    // =================================================================

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
                    'Accept': 'application/json',
                }
            });
            const result = await response.json();
            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    const errorMessages = Object.values(result.errors).flat().join('\n');
                    throw new Error(errorMessages);
                }
                throw new Error(result.error || 'Upload không thành công.');
            }
            return result;
        } catch (error) {
            console.error('Lỗi khi upload ảnh bằng AJAX:', error);
            throw error;
        }
    }

    function addImagesToProductForm(images, previewContainer, idsContainer, type = 'simple', variantIndex = null) {
        if (!images || images.length === 0 || !previewContainer || !idsContainer) {
             console.error("Thiếu thông tin để thêm ảnh vào form", {images, previewContainer, idsContainer});
             return;
        }

        const hasPrimaryAlready = (type === 'simple') ?
            !!document.querySelector('#image_ids_container input[name="cover_image_id"]') :
            !!idsContainer.querySelector(`input[name="variants[${variantIndex}][primary_image_id]"]`);

        images.forEach(fileData => {
            if (previewContainer.querySelector(`.variant-image-preview-item[data-id="${fileData.id}"]`)) {
                return;
            }

            const galleryInput = document.createElement('input');
            galleryInput.type = 'hidden';
            galleryInput.dataset.id = fileData.id;
            galleryInput.value = fileData.id;
            
            if (type === 'simple') {
                galleryInput.name = 'gallery_images[]';
                galleryInput.className = 'simple-image-id';
            } else {
                galleryInput.name = `variants[${variantIndex}][image_ids][]`;
                galleryInput.className = 'variant-image-id';
            }
            idsContainer.appendChild(galleryInput);


            const previewDiv = document.createElement('div');
            previewDiv.className = 'image-preview variant-image-preview-item';
            previewDiv.dataset.id = fileData.id;

            const removeFunction = type === 'simple' ? `removeSimpleProductImage(${fileData.id})` : `removeVariantImage(this, ${variantIndex}, ${fileData.id})`;
            const setPrimaryFunction = type === 'simple' ? `setSimpleProductPrimaryImage(${fileData.id})` : `setVariantPrimaryImage(${variantIndex}, ${fileData.id})`;

            previewDiv.innerHTML = `
                <img src="${fileData.url}" alt="${fileData.alt_text || 'Ảnh sản phẩm'}">
                <span class="remove-img-btn" onclick="${removeFunction}">×</span>
                <button type="button" class="set-primary-btn" title="Đặt làm ảnh chính" onclick="${setPrimaryFunction}">
                    <i class="fas fa-star" style="color: white; pointer-events: none;"></i> Đặt chính
                </button>
            `;
            previewContainer.appendChild(previewDiv);
        });

        const allPreviews = previewContainer.querySelectorAll('.variant-image-preview-item');
        if (!hasPrimaryAlready && allPreviews.length > 0) {
             const firstImageId = parseInt(allPreviews[0].dataset.id);
             if (type === 'simple') {
                 setSimpleProductPrimaryImage(firstImageId);
             } else {
                 setVariantPrimaryImage(variantIndex, firstImageId);
             }
        }
    }

    async function handleSimpleProductImages(event) {
        const files = event.target.files;
        if (!files.length) return;
        try {
            const result = await uploadFilesViaAjax(files);
            if (result.files && result.files.length > 0) {
                const previewContainer = document.getElementById('simple_product_image_preview_container');
                const idsContainer = document.getElementById('image_ids_container');
                addImagesToProductForm(result.files, previewContainer, idsContainer, 'simple');
                showMessageModal('Thành công', `${result.files.length} ảnh đã được tải lên!`, 'success');
            }
        } catch (error) {
            showMessageModal('Lỗi Upload', error.message, 'error');
        } finally {
            event.target.value = '';
        }
    }

    function setSimpleProductPrimaryImage(primaryImageId) {
        const previewContainer = document.getElementById('simple_product_image_preview_container');
        const imageIdsContainer = document.getElementById('image_ids_container');
        if (!previewContainer || !imageIdsContainer) return;

        previewContainer.querySelectorAll('.variant-image-preview-item').forEach(preview => {
            preview.classList.toggle('is-primary', parseInt(preview.dataset.id) === primaryImageId);
        });

        let oldCoverInput = imageIdsContainer.querySelector('input[name="cover_image_id"]');
        if (oldCoverInput) oldCoverInput.remove();

        const newCoverInput = document.createElement('input');
        newCoverInput.type = 'hidden';
        newCoverInput.name = 'cover_image_id';
        newCoverInput.value = primaryImageId;
        imageIdsContainer.appendChild(newCoverInput);
    }

    function removeSimpleProductImage(imageIdToRemove) {
        const previewContainer = document.getElementById('simple_product_image_preview_container');
        const imageIdsContainer = document.getElementById('image_ids_container');
        const previewToRemove = previewContainer.querySelector(`.variant-image-preview-item[data-id="${imageIdToRemove}"]`);
        const galleryInputToRemove = imageIdsContainer.querySelector(`input.simple-image-id[value="${imageIdToRemove}"]`);
        const coverInput = imageIdsContainer.querySelector('input[name="cover_image_id"]');

        if (!previewToRemove) return;
        const wasPrimary = coverInput && (parseInt(coverInput.value) === imageIdToRemove);

        previewToRemove.remove();
        if (galleryInputToRemove) galleryInputToRemove.remove();

        if (wasPrimary) {
            if (coverInput) coverInput.remove();
            const remainingPreviews = previewContainer.querySelectorAll('.variant-image-preview-item');
            if (remainingPreviews.length > 0) {
                const newPrimaryId = parseInt(remainingPreviews[0].dataset.id);
                setSimpleProductPrimaryImage(newPrimaryId);
            }
        }
    }

    async function handleVariantImages(event, variantIndex) {
        const files = event.target.files;
        if (!files.length) return;
        try {
            const result = await uploadFilesViaAjax(files);
            if (result.files && result.files.length > 0) {
                const previewContainer = document.getElementById(`variant_${variantIndex}_image_preview_container`);
                const idsContainer = document.getElementById(`variant_${variantIndex}_image_ids_container`);
                addImagesToProductForm(result.files, previewContainer, idsContainer, 'variant', variantIndex);
                showMessageModal('Thành công', `${result.files.length} ảnh đã được tải lên!`, 'success');
            }
        } catch (error) {
            showMessageModal('Lỗi Upload', error.message, 'error');
        } finally {
            event.target.value = '';
        }
    }

    function setVariantPrimaryImage(variantIndex, primaryImageId) {
        const previewContainer = document.getElementById(`variant_${variantIndex}_image_preview_container`);
        const idsContainer = document.getElementById(`variant_${variantIndex}_image_ids_container`);
        if (!previewContainer || !idsContainer) return;

        previewContainer.querySelectorAll('.variant-image-preview-item').forEach(preview => {
            preview.classList.toggle('is-primary', parseInt(preview.dataset.id) === primaryImageId);
        });

        let oldPrimaryInput = idsContainer.querySelector(`input[name="variants[${variantIndex}][primary_image_id]"]`);
        if (oldPrimaryInput) oldPrimaryInput.remove();

        const primaryInput = document.createElement('input');
        primaryInput.type = 'hidden';
        primaryInput.name = `variants[${variantIndex}][primary_image_id]`;
        primaryInput.value = primaryImageId;
        idsContainer.appendChild(primaryInput);
    }

    function removeVariantImage(buttonElement, variantIndex, imageIdToRemove) {
        const idsContainer = document.getElementById(`variant_${variantIndex}_image_ids_container`);
        const previewItem = buttonElement.closest('.variant-image-preview-item');

        if (!previewItem || !idsContainer) return;

        const inputToRemove = idsContainer.querySelector(`input.variant-image-id[value="${imageIdToRemove}"]`);
        const primaryInput = idsContainer.querySelector(`input[name="variants[${variantIndex}][primary_image_id]"]`);
        const wasPrimary = primaryInput && parseInt(primaryInput.value) === imageIdToRemove;

        previewItem.remove();
        if (inputToRemove) inputToRemove.remove();

        if (wasPrimary) {
            if (primaryInput) primaryInput.remove();
            const remainingInputs = idsContainer.querySelectorAll('.variant-image-id');
            if (remainingInputs.length > 0) {
                const newPrimaryId = parseInt(remainingInputs[0].value);
                setVariantPrimaryImage(variantIndex, newPrimaryId);
            }
        }
    }


    // =================================================================
    // KHỐI LOGIC KHỞI TẠO VÀ XỬ LÝ FORM CHUNG (AI, Slug, Variants, etc.) (KHÔNG ĐỔI)
    // =================================================================
    const productNameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const categorySelectElement = document.getElementById('category_id');
    const shortDescriptionTextarea = document.getElementById('short_description');
    const variantsContainer = document.getElementById('variantsContainer');
    const addVariantButton = document.getElementById('addVariantButton');

    function showMessageModal(title, text, type = 'info') {
        const messageModal = document.getElementById('messageModal');
        const messageModalTitle = document.getElementById('messageModalTitle');
        const messageModalText = document.getElementById('messageModalText');
        const messageModalIconContainer = document.getElementById('messageModalIconContainer');

        if (!messageModal || !messageModalTitle || !messageModalText || !messageModalIconContainer) return;
        messageModalTitle.textContent = title;
        messageModalText.textContent = text;
        messageModalIconContainer.innerHTML = '';
        let iconSvg = '';
        let iconBgClass = 'bg-blue-100';

        if (type === 'success') {
            iconSvg = '<svg class="h-6 w-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            iconBgClass = 'bg-green-100';
        } else if (type === 'error') {
            iconSvg = '<svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            iconBgClass = 'bg-red-100';
        } else {
            iconSvg = '<svg class="h-6 w-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        }
        messageModalIconContainer.className = `mx-auto flex items-center justify-center h-12 w-12 rounded-full ${iconBgClass}`;
        messageModalIconContainer.innerHTML = iconSvg;
        messageModal.classList.remove('hidden');
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
        const backendApiUrl = "{{ route('admin.products.ai.generate') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let payload = { prompt, isStructured, schema };

        try {
            const response = await fetch(backendApiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (!response.ok) {
                const errorData = await response.json();
                const errorMessage = errorData.details?.error?.message || errorData.error || `Request failed with status ${response.status}`;
                throw new Error(errorMessage);
            }
            const result = await response.json();
            if (result.candidates && result.candidates[0]?.content?.parts?.[0]) {
                const responseText = result.candidates[0].content.parts[0].text;
                return isStructured ? JSON.parse(responseText) : responseText;
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

    function getProductContext() {
        const productName = productNameInput ? productNameInput.value.trim() : "";
        const categoryName = categorySelectElement ? (categorySelectElement.options[categorySelectElement.selectedIndex]?.text || "") : "";
        if (!productName) {
            showMessageModal("Thiếu thông tin", "Vui lòng nhập tên sản phẩm trước khi sử dụng tính năng AI.", "error");
            return null;
        }
        return `Sản phẩm: ${productName}, thuộc danh mục: ${categoryName}. Tập trung vào các sản phẩm của Apple.`;
    }

    function slugify(text) {
        if (!text) return '';
        return text.toString().toLowerCase().trim().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd')
            .replace(/\s+/g, '-').replace(/[^\w-]+/g, '').replace(/--+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
    }

    function toggleSchedule(element) {
        const container = element.closest('.input-group').querySelector('.schedule-container');
        if (container) {
            container.classList.toggle('hidden');
        }
    }

    function toggleProductTypeFields() {
        const typeRadio = document.querySelector('input[name="type"]:checked');
        if (!typeRadio) return;
        const type = typeRadio.value;
        const simpleFields = document.getElementById('simpleProductFields');
        const variableFields = document.getElementById('variableProductFields');

        if (simpleFields) simpleFields.style.display = (type === 'simple' ? 'block' : 'none');
        if (variableFields) variableFields.style.display = (type === 'variable' ? 'block' : 'none');

        if (type === 'simple' && variantsContainer) {
            variantsContainer.innerHTML = '';
            variantIndexGlobal = 0;
            document.querySelectorAll('.product-attribute-checkbox').forEach(cb => cb.checked = false);
            updateSelectedAttributesForVariants();
        }
    }

    function updateSelectedAttributesForVariants() {
        selectedProductAttributes = [];
        document.querySelectorAll('.product-attribute-checkbox:checked').forEach(checkbox => {
            const attrId = parseInt(checkbox.value);
            if (Array.isArray(allAttributesFromPHP)) {
                const attribute = allAttributesFromPHP.find(a => a && typeof a.id !== 'undefined' && a.id === attrId);
                if (attribute) selectedProductAttributes.push(attribute);
            }
        });
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


    // =================================================================
    // *** HÀM MỚI: KHÔI PHỤC DỮ LIỆU "OLD" TỪ BIẾN CÓ SẴN ***
    // =================================================================
    function repopulateFormFromOldData() {
        // --- Xử lý sản phẩm đơn giản ---
        const simpleGalleryIds = @json(old('gallery_images', []), JSON_UNESCAPED_UNICODE);
        if (simpleGalleryIds.length > 0 || oldCoverImageId) {
            const imagesForSimpleProduct = [];
            let allSimpleIds = [...simpleGalleryIds];
            if(oldCoverImageId) allSimpleIds.push(oldCoverImageId);
            
            // Lấy thông tin ảnh từ biến `oldImagesData` đã có sẵn
            [...new Set(allSimpleIds)].forEach(id => {
                if (oldImagesData[id]) {
                    imagesForSimpleProduct.push(oldImagesData[id]);
                }
            });

            if (imagesForSimpleProduct.length > 0) {
                const previewContainer = document.getElementById('simple_product_image_preview_container');
                const idsContainer = document.getElementById('image_ids_container');
                addImagesToProductForm(imagesForSimpleProduct, previewContainer, idsContainer, 'simple');
                if (oldCoverImageId) {
                    setSimpleProductPrimaryImage(parseInt(oldCoverImageId));
                }
            }
        }

        // --- Xử lý sản phẩm có biến thể ---
        const productTypeRadio = document.querySelector('input[name="type"]:checked');
        if (productTypeRadio && productTypeRadio.value === 'variable' && Array.isArray(oldVariantsData) && oldVariantsData.length > 0) {
            
            if (variantsContainer) variantsContainer.innerHTML = '';
            variantIndexGlobal = 0;

            let usedAttributeIdsInOld = new Set();
            oldVariantsData.forEach(oldVar => { if (oldVar.attributes && typeof oldVar.attributes === 'object') { Object.keys(oldVar.attributes).forEach(attrId => usedAttributeIdsInOld.add(parseInt(attrId))); } });
            document.querySelectorAll('.product-attribute-checkbox').forEach(cb => { if (usedAttributeIdsInOld.has(parseInt(cb.value))) cb.checked = true; });
            updateSelectedAttributesForVariants();

            oldVariantsData.forEach(oldVariant => {
                if (addVariantButton) addVariantButton.click();
                const currentVariantIndex = variantIndexGlobal - 1;
                const currentVariantCard = variantsContainer ? variantsContainer.querySelector(`.variant-card[data-variant-index="${currentVariantIndex}"]`) : null;

                if (currentVariantCard) {
                    // Populate các trường input
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][sku]"]`).value = oldVariant.sku || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][price]"]`).value = oldVariant.price || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][sale_price]"]`).value = oldVariant.sale_price || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][stock_quantity]"]`).value = oldVariant.stock_quantity || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][weight]"]`).value = oldVariant.weight || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][dimensions_length]"]`).value = oldVariant.dimensions_length || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][dimensions_width]"]`).value = oldVariant.dimensions_width || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][dimensions_height]"]`).value = oldVariant.dimensions_height || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][sale_price_starts_at]"]`).value = oldVariant.sale_price_starts_at || '';
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][sale_price_ends_at]"]`).value = oldVariant.sale_price_ends_at || '';
                    
                    if (oldVariant.sale_price_starts_at || oldVariant.sale_price_ends_at) {
                        const scheduleContainer = currentVariantCard.querySelector('.schedule-container');
                        if (scheduleContainer) scheduleContainer.classList.remove('hidden');
                    }
                    
                    if (oldVariant.attributes && typeof oldVariant.attributes === 'object') {
                        Object.entries(oldVariant.attributes).forEach(([attrId, attrValueId]) => {
                            const attrSelect = currentVariantCard.querySelector(`select[name="variants[${currentVariantIndex}][attributes][${attrId}]"]`);
                            if (attrSelect) attrSelect.value = attrValueId;
                        });
                    }
                    
                    const isDefault = oldVariant.is_default === "true" || oldVariant.is_default === true;
                    currentVariantCard.querySelector(`input[name="variant_is_default_radio_group"][value="${currentVariantIndex}"]`).checked = isDefault;
                    currentVariantCard.querySelector(`input[name="variants[${currentVariantIndex}][is_default]"]`).value = isDefault ? "true" : "false";

                    // *** PHẦN KHÔI PHỤC ẢNH CHO BIẾN THỂ ***
                    const variantImageIds = oldVariant.image_ids || [];
                    const variantPrimaryId = oldVariant.primary_image_id || null;
                    const imagesForThisVariant = [];
                    
                    let allVariantImageIds = [...variantImageIds];
                    if(variantPrimaryId) allVariantImageIds.push(variantPrimaryId);

                    [...new Set(allVariantImageIds)].forEach(id => {
                        if (oldImagesData[id]) {
                            imagesForThisVariant.push(oldImagesData[id]);
                        }
                    });

                    if (imagesForThisVariant.length > 0) {
                        const previewContainer = document.getElementById(`variant_${currentVariantIndex}_image_preview_container`);
                        const idsContainer = document.getElementById(`variant_${currentVariantIndex}_image_ids_container`);
                        addImagesToProductForm(imagesForThisVariant, previewContainer, idsContainer, 'variant', currentVariantIndex);
                        
                        if (variantPrimaryId) {
                            setVariantPrimaryImage(currentVariantIndex, parseInt(variantPrimaryId));
                        }
                    }
                }
            });
            updateDefaultVariantRadioAndHiddenFields();
        }
    }


    // =================================================================
    // PHẦN DOMContentLoaded: KHỞI TẠO CÁC THÀNH PHẦN
    // =================================================================
    document.addEventListener('DOMContentLoaded', () => {

        // --- KHỞI TẠO CÁC THÀNH PHẦN CHUNG ---
        const messageModalCloseButton = document.getElementById('messageModalCloseButton');
        const messageModal = document.getElementById('messageModal');
        if (messageModalCloseButton && messageModal) {
            messageModalCloseButton.addEventListener('click', () => messageModal.classList.add('hidden'));
            messageModal.addEventListener('click', (event) => { if (event.target === messageModal) messageModal.classList.add('hidden'); });
        }

        tinymce.init({
            selector: 'textarea#description',
            plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons accordion',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | fontfamily fontsize | align numlist bullist | link image media | table | lineheight | strikethrough superscript subscript | accordions | removeformat',
            height: 500,
            skin: 'oxide',
            content_css: 'default',
            content_style: "body { font-family: 'Inter', sans-serif; font-size: 16px; }",
            autosave_ask_before_unload: true,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',
            image_advtab: true,
            importcss_append: true,
            setup: function(editor) { editor.on('change', function() { editor.save(); }); }
        });

        const tagsInput = document.getElementById('tags');
        if (tagsInput) {
            tagify = new Tagify(tagsInput, {
                duplicates: false,
                dropdown: { maxItems: 20, classname: "tags-look", enabled: 0, closeOnSelect: false }
            });
        }

        // --- GẮN SỰ KIỆN CHO CÁC NÚT AI ---
        const generateShortDescBtn = document.getElementById('generateShortDescAI');
        if (generateShortDescBtn) {
             generateShortDescBtn.addEventListener('click', async () => {
                 const context = getProductContext();
                 if (!context) return;
                 toggleButtonLoading(generateShortDescBtn, true);
                 try {
                     const prompt = `Dựa vào thông tin sau: "${context}", hãy viết một mô tả ngắn gọn (khoảng 2-3 câu) cho sản phẩm với giọng văn bán hàng chuyên nghiệp. Yêu cầu: - Tập trung vào các điểm nổi bật chính, thu hút khách hàng. - KHÔNG sử dụng Markdown (như dấu ** hay #). - Chỉ trả về duy nhất phần nội dung mô tả, không có lời dẫn như "Đây là mô tả:" hay tương tự.`;
                     const result = await callGeminiAPI(prompt);
                     if (result) {
                         shortDescriptionTextarea.value = result.replace(/[\*#`]/g, '').trim();
                     }
                 } catch (error) {
                     showMessageModal('Lỗi AI', `Không thể tạo mô tả ngắn: ${error.message}`, 'error');
                 } finally {
                     toggleButtonLoading(generateShortDescBtn, false);
                 }
             });
        }

        const generateLongDescBtn = document.getElementById('generateLongDescAI');
        if (generateLongDescBtn) {
            generateLongDescBtn.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context) return;
                toggleButtonLoading(generateLongDescBtn, true);
                try {
                    const prompt = `Dựa vào thông tin sau: "${context}", hãy viết một bài mô tả chi tiết, hấp dẫn, chuẩn SEO cho sản phẩm. Yêu cầu: - Sử dụng các thẻ HTML để định dạng bài viết một cách chuyên nghiệp. Ví dụ: <h3> cho tiêu đề các phần, <ul> và <li> cho danh sách liệt kê, <strong> để nhấn mạnh các tính năng quan trọng. - Chia bài viết thành các đoạn logic, có tiêu đề rõ ràng (ví dụ: Thiết kế sang trọng, Màn hình Super Retina XDR, Hiệu năng vượt trội với chip A17 Pro, Hệ thống Camera chuyên nghiệp). - KHÔNG bao gồm các thẻ <html>, <body>, <head>. Chỉ trả về phần nội dung HTML cho phần thân bài viết để chèn vào trình soạn thảo. - Giọng văn phải chuyên nghiệp, thuyết phục, hướng tới người mua hàng.`;
                    const result = await callGeminiAPI(prompt);
                    if (result && typeof tinymce !== 'undefined' && tinymce.get('description')) {
                        tinymce.get('description').setContent(result);
                    }
                } catch (error) {
                    showMessageModal('Lỗi AI', `Không thể tạo mô tả chi tiết: ${error.message}`, 'error');
                } finally {
                    toggleButtonLoading(generateLongDescBtn, false);
                }
            });
        }

        const generateAllSeoBtn = document.getElementById('generateAllSeoAI');
        if (generateAllSeoBtn) {
            generateAllSeoBtn.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context) return;
                toggleButtonLoading(generateAllSeoBtn, true);
                try {
                     const schema = {
                         type: "OBJECT",
                         properties: {
                             meta_title: { type: "STRING", description: "Tiêu đề SEO, khoảng 50-60 ký tự, chứa từ khóa chính." },
                             meta_description: { type: "STRING", description: "Mô tả SEO, khoảng 150-160 ký tự, hấp dẫn và kêu gọi hành động." },
                             meta_keywords: { type: "STRING", description: "Chuỗi các từ khóa liên quan, cách nhau bởi dấu phẩy." }
                         },
                         required: ["meta_title", "meta_description", "meta_keywords"]
                     };
                    const prompt = `Dựa vào thông tin sản phẩm sau: "${context}", hãy tạo nội dung tối ưu hóa SEO. Yêu cầu: - Meta Title: Ngắn gọn, súc tích, chứa từ khóa chính và tên thương hiệu. - Meta Description: Viết một đoạn mô tả hấp dẫn, tóm tắt điểm nổi bật của sản phẩm và có lời kêu gọi hành động (ví dụ: "Mua ngay", "Khám phá ngay"). - Meta Keywords: Liệt kê các từ khóa chính, từ khóa phụ, từ khóa liên quan. - Trả về kết quả dưới dạng một đối tượng JSON hợp lệ theo schema đã cung cấp. KHÔNG trả về bất cứ thứ gì khác ngoài JSON.`;

                    const result = await callGeminiAPI(prompt, true, schema);
                    if (result) {
                        document.getElementById('meta_title').value = result.meta_title || '';
                        document.getElementById('meta_description').value = result.meta_description || '';
                        document.getElementById('meta_keywords').value = result.meta_keywords || '';
                    }
                } catch (error) {
                     showMessageModal('Lỗi AI', `Không thể tạo dữ liệu SEO: ${error.message}`, 'error');
                } finally {
                    toggleButtonLoading(generateAllSeoBtn, false);
                }
            });
        }

        const generateTagsBtn = document.getElementById('generateTagsAI');
        if (generateTagsBtn) {
            generateTagsBtn.addEventListener('click', async () => {
                const context = getProductContext();
                if (!context) return;
                toggleButtonLoading(generateTagsBtn, true);
                try {
                    const prompt = `Dựa vào thông tin sản phẩm sau: "${context}", hãy gợi ý 5 đến 7 từ khóa (tags) phù hợp nhất để phân loại sản phẩm. Yêu cầu: - Các từ khóa phải ngắn gọn, liên quan trực tiếp đến sản phẩm hoặc tính năng nổi bật. - Trả về dưới dạng một chuỗi duy nhất, các từ khóa cách nhau bởi dấu phẩy. - Ví dụ: iPhone 15 Pro, Titan, USB-C, A17 Pro - KHÔNG dùng Markdown, KHÔNG dùng đánh số, và KHÔNG có lời dẫn. Chỉ trả về chuỗi các thẻ.`;
                    const result = await callGeminiAPI(prompt);
                    if (result && tagify) {
                        const cleanedResult = result.replace(/[\*#`]/g, '').replace(/(\d+\.\s*)/g, '').trim();
                        tagify.loadOriginalValues(cleanedResult);
                    }
                } catch (error) {
                    showMessageModal('Lỗi AI', `Không thể tạo thẻ: ${error.message}`, 'error');
                } finally {
                    toggleButtonLoading(generateTagsBtn, false);
                }
            });
        }

        // --- LOGIC CHO BIẾN THỂ ---
        const productAttributesContainer = document.getElementById('productAttributesContainer');
        if (productAttributesContainer && Array.isArray(allAttributesFromPHP)) {
             allAttributesFromPHP.forEach(attr => {
                 if (!attr || typeof attr.id === 'undefined' || typeof attr.name === 'undefined') return;
                 const labelEl = document.createElement('label');
                 labelEl.className = 'flex items-center cursor-pointer p-2 rounded-md hover:bg-gray-100 transition-colors';
                 const checkbox = document.createElement('input');
                 checkbox.type = 'checkbox';
                 checkbox.id = `attr_${attr.id}`;
                 checkbox.value = attr.id;
                 checkbox.className = 'product-attribute-checkbox form-check-input mr-2';
                 checkbox.onchange = updateSelectedAttributesForVariants;
                 labelEl.append(checkbox, attr.name);
                 productAttributesContainer.appendChild(labelEl);
             });
             updateSelectedAttributesForVariants();
        }

        if (addVariantButton) {
            addVariantButton.addEventListener('click', () => {
                const currentVariantIndex = variantIndexGlobal;
                if (selectedProductAttributes.length === 0) {
                    showMessageModal('Thông báo', 'Vui lòng chọn ít nhất một thuộc tính cho sản phẩm trước khi thêm biến thể.', 'info');
                    return;
                }
                const variantCard = document.createElement('div');
                variantCard.className = 'variant-card';
                variantCard.dataset.variantIndex = currentVariantIndex;
                let variantHeaderHTML = `<div class="variant-header"><div class="flex items-center"><h4 class="variant-title">Biến Thể #${currentVariantIndex + 1}</h4></div><button type="button" class="remove-variant-btn btn btn-danger btn-sm">Xóa</button></div>`;
                let attributesHTML = '<div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-4">';
                selectedProductAttributes.forEach(attr => {
                    if (!attr || !Array.isArray(attr.attributeValues)) return;
                    attributesHTML += `<div class="input-group"><label for="variants_${currentVariantIndex}_attr_${attr.id}" class="text-sm font-medium">${attr.name} <span class="required-star">*</span></label><div><select name="variants[${currentVariantIndex}][attributes][${attr.id}]" id="variants_${currentVariantIndex}_attr_${attr.id}" class="select-field text-sm" ><option value="">Chọn ${attr.name}</option>${attr.attributeValues.map(val => `<option value="${val.id}">${val.value}</option>`).join('')}</select></div></div>`;
                });
                attributesHTML += '</div>';
                let variantFieldsHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div class="input-group"><label for="variants_${currentVariantIndex}_sku" class="text-sm font-medium">SKU Biến Thể <span class="required-star">*</span></label><div><input type="text" name="variants[${currentVariantIndex}][sku]" class="input-field text-sm" ></div></div>
                        <div class="input-group"><label for="variants_${currentVariantIndex}_stock_quantity" class="text-sm font-medium">Tồn Kho <span class="required-star">*</span></label><div><input type="number" name="variants[${currentVariantIndex}][stock_quantity]" class="input-field text-sm" min="0" ></div></div>
                        <div class="input-group"><label for="variants_${currentVariantIndex}_price" class="text-sm font-medium">Giá Biến Thể <span class="required-star">*</span> (VNĐ)</label><div><input type="number" name="variants[${currentVariantIndex}][price]" class="input-field text-sm" step="1000" min="0" ></div></div>
                        <div class="input-group">
                            <div class="label-with-action">
                                <label for="variants_${currentVariantIndex}_sale_price" class="text-sm font-medium">Giá KM (VNĐ)</label>
                                <a href="javascript:void(0);" onclick="toggleSchedule(this)" class="text-blue-600 text-sm font-medium hover:underline">Lên lịch</a>
                            </div>
                            <div><input type="number" name="variants[${currentVariantIndex}][sale_price]" class="input-field text-sm" step="1000" min="0"></div>
                            <div class="schedule-container hidden mt-2 grid grid-cols-2 gap-x-4">
                                <div><label for="variants_${currentVariantIndex}_sale_price_starts_at" class="text-xs">Bắt đầu</label><input type="date" name="variants[${currentVariantIndex}][sale_price_starts_at]" class="input-field text-sm"></div>
                                <div><label for="variants_${currentVariantIndex}_sale_price_ends_at" class="text-xs">Kết thúc</label><input type="date" name="variants[${currentVariantIndex}][sale_price_ends_at]" class="input-field text-sm"></div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-4 pt-4 border-t border-gray-200">
                         <div class="input-group"><label for="variants_${currentVariantIndex}_weight" class="text-sm font-medium">Cân nặng (kg)</label><input type="number" step="0.01" min="0" name="variants[${currentVariantIndex}][weight]" class="input-field text-sm"></div>
                        <div class="input-group"><label class="text-sm font-medium">Kích thước (D x R x C) (cm)</label><div class="grid grid-cols-3 gap-x-2"><input type="number" step="0.1" min="0" name="variants[${currentVariantIndex}][dimensions_length]" placeholder="Dài" class="input-field text-sm"><input type="number" step="0.1" min="0" name="variants[${currentVariantIndex}][dimensions_width]" placeholder="Rộng" class="input-field text-sm"><input type="number" step="0.1" min="0" name="variants[${currentVariantIndex}][dimensions_height]" placeholder="Cao" class="input-field text-sm"></div></div>
                    </div>
                    <div class="input-group md:col-span-2 mt-4 pt-4 border-t border-gray-200">
                        <label class="text-sm font-medium">Ảnh Biến Thể</label>
                        <div class="flex space-x-2 mb-3">
                            <label for="variant_${currentVariantIndex}_image_input" class="form-section-heading btn btn-secondary btn-sm cursor-pointer"><i class="fas fa-upload mr-2"></i> Tải ảnh lên</label>
                            <input type="file" id="variant_${currentVariantIndex}_image_input" class="hidden" accept="image/*" multiple onchange="handleVariantImages(event, ${currentVariantIndex})">
                            <button type="button" class="btn btn-secondary btn-sm open-library-btn-variant" data-variant-index="${currentVariantIndex}"><svg class="svg-icon mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg> Thêm từ thư viện</button>
                        </div>
                        <div id="variant_${currentVariantIndex}_image_preview_container" class="image-preview-container mt-2"></div>
                        <div id="variant_${currentVariantIndex}_image_ids_container" class="hidden"></div>
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center text-sm cursor-pointer"><input type="radio" name="variant_is_default_radio_group" value="${currentVariantIndex}" class="form-check-input mr-2 variant-default-radio"><input type="hidden" name="variants[${currentVariantIndex}][is_default]" value="false" class="is-default-hidden-input"> Đặt làm biến thể mặc định</label>
                    </div>
                `;
                variantCard.innerHTML = variantHeaderHTML + attributesHTML + variantFieldsHTML;
                if (variantsContainer) variantsContainer.appendChild(variantCard);
                variantCard.querySelector('.remove-variant-btn').addEventListener('click', function() { this.closest('.variant-card').remove(); updateDefaultVariantRadioAndHiddenFields(); });
                variantCard.querySelector('.variant-default-radio').addEventListener('change', handleDefaultVariantChange);
                variantIndexGlobal++;
                updateDefaultVariantRadioAndHiddenFields();
            });
        }

        // --- GẮN SỰ KIỆN CHO CÁC NÚT "THÊM TỪ THƯ VIỆN" ---
        const openLibraryBtnSimple = document.getElementById('open-library-btn-simple');
        if (openLibraryBtnSimple) {
            openLibraryBtnSimple.addEventListener('click', () => {
                window.mediaLibraryTarget = {
                    type: 'simple',
                    previewContainer: document.getElementById('simple_product_image_preview_container'),
                    idsContainer: document.getElementById('image_ids_container')
                };
                if (window.openMediaLibrary) { window.openMediaLibrary(); }
                else { alert('Lỗi: Không tìm thấy hàm thư viện media (window.openMediaLibrary).'); }
            });
        }
        document.body.addEventListener('click', function(e) {
            if (e.target && e.target.matches('.open-library-btn-variant')) {
                const variantIndex = e.target.dataset.variantIndex;
                window.mediaLibraryTarget = {
                    type: 'variant',
                    variantIndex: variantIndex,
                    previewContainer: document.getElementById(`variant_${variantIndex}_image_preview_container`),
                    idsContainer: document.getElementById(`variant_${variantIndex}_image_ids_container`)
                };
                if (window.openMediaLibrary) { window.openMediaLibrary(); }
                else { alert('Lỗi: Không tìm thấy hàm thư viện media (window.openMediaLibrary).'); }
            }
        });

        // --- KHỞI TẠO FORM VÀ VALIDATION ---
        toggleProductTypeFields();
        if (slugInput) {
            slugInput.dataset.auto = "true";
            productNameInput.addEventListener('keyup', () => { if (slugInput.dataset.auto === "true") { slugInput.value = slugify(productNameInput.value); } });
            slugInput.addEventListener('input', () => { slugInput.dataset.auto = slugInput.value.trim() === "" ? "true" : "false"; });
        }
        
        // *** GỌI HÀM KHÔI PHỤC DỮ LIỆU "OLD" ***
        repopulateFormFromOldData();

        // Xử lý submit form
        const form = document.getElementById('addProductForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                if (typeof tinymce !== 'undefined' && tinymce.get('description')) { tinymce.get('description').save(); }
                const type = document.querySelector('input[name="type"]:checked')?.value;
                if (!type) {
                    showMessageModal('Cảnh báo', 'Vui lòng chọn loại sản phẩm.', 'error');
                    event.preventDefault(); return;
                }
                if (type === 'simple') {
                    if (document.querySelectorAll('#image_ids_container .simple-image-id').length === 0) { showMessageModal('Cảnh báo', 'Sản phẩm đơn giản phải có ít nhất một hình ảnh.', 'error'); event.preventDefault(); return; }
                    if (!document.querySelector('#image_ids_container input[name="cover_image_id"]')) { showMessageModal('Cảnh báo', 'Vui lòng chọn một ảnh làm ảnh chính cho sản phẩm.', 'error'); event.preventDefault(); return; }
                }
                if (type === 'variable') {
                    if (!variantsContainer || variantsContainer.children.length === 0) { showMessageModal('Cảnh báo', 'Sản phẩm có biến thể phải có ít nhất một biến thể được thêm vào.', 'error'); event.preventDefault(); return; }
                    if (!document.querySelector('.variant-default-radio:checked') && variantsContainer.children.length > 0) { showMessageModal('Cảnh báo', 'Vui lòng chọn một biến thể làm mặc định.', 'error'); event.preventDefault(); return; }
                }
            });
        }
    });
</script>

@endpush
