
@extends('admin.layouts.app')

@section('title', 'Quản lý Gói Sản Phẩm - Tạo Deal Bán Kèm Mới')

@push('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }

        .card-custom {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
        }

        .card-custom-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .card-custom-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .card-custom-subtitle {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
        }

        .card-custom-body {
            padding: 1.5rem;
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
            line-height: 1.25rem;
            border: 1px solid transparent;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .btn-danger-outline {
            color: #ef4444;
            background-color: #fff;
            border: 1px solid #ef4444;
        }

        .btn-danger-outline:hover {
            background-color: #fef2f2;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
            background-color: white;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: #4f46e5;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .form-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .form-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #4f46e5;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        .modal-backdrop {
            transition: opacity 0.3s ease;
        }

        .modal-panel {
            transition: all 0.3s ease;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="px-4 sm:px-6 md:px-8 py-8" x-data="dealManager()">
        <div class="container mx-auto max-w-4xl">
            <!-- PAGE HEADER -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Tạo Deal Bán Kèm Mới</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.bundle-products.index') }}"
                                class="text-indigo-600 hover:text-indigo-800">Deal bán kèm</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Tạo mới</li>
                    </ol>
                </nav>
            </header>

            <form action="{{ route('admin.bundle-products.store') }}" method="POST" class="space-y-8">
                @csrf
                <!-- General Information Card -->
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h3 class="card-custom-subtitle">Thông tin chung</h3>
                    </div>
                    <div class="card-custom-body grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bundle_name" class="block text-sm font-medium text-gray-700 mb-1">Tên nội bộ của Deal</label>
                            <input type="text" id="bundle_name" name="bundle_name" class="form-input"
                                placeholder="Ví dụ: Deal ra mắt iPhone 15" value="{{ old('bundle_name') }}">
                            @error('bundle_name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Tên này chỉ dùng để quản lý, không hiển thị cho khách hàng.</p>
                        </div>
                        <div>
                            <label for="bundle_title" class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề hiển thị</label>
                            <input type="text" id="bundle_title" name="bundle_title" class="form-input"
                                placeholder="Ví dụ: Mua Kèm Deal Sốc" value="{{ old('bundle_title') }}">
                            @error('bundle_title')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Tiêu đề này sẽ hiển thị trên trang sản phẩm.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label for="bundle_description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả (tùy chọn)</label>
                            <textarea id="bundle_description" name="bundle_description" rows="3" class="form-textarea">{{ old('bundle_description') }}</textarea>
                            @error('bundle_description')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <label class="form-switch">
                                <input type="checkbox" name="status" {{ old('status') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                            <span class="ml-2 text-sm text-gray-600">Kích hoạt</span>
                            @error('status')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Main Products Card -->
                <div class="card-custom">
                    <div class="card-custom-header flex justify-between items-center">
                        <h3 class="card-custom-subtitle">Sản phẩm chính</h3>
                        <button @click="openModal('main')" type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-2"></i>Thêm sản phẩm
                        </button>
                    </div>
                    <div class="card-custom-body">
                        <p class="text-sm text-gray-600 mb-4">Sản phẩm chính để hiển thị combo (ví dụ: iPhone 16)</p>
                        <div class="space-y-2" x-ref="mainProductsList">
                            <template x-for="product in mainProducts" :key="product.id">
                                <div class="border rounded-lg">
                                    <div class="flex items-center p-3 cursor-pointer hover:bg-gray-50">
                                        <div @click="product.showVariants = !product.showVariants" class="flex-grow">
                                            <p class="font-semibold text-gray-800" x-text="product.name"></p>
                                        </div>
                                        <div class="flex items-center">
                                            <button type="button" class="text-gray-400 hover:text-red-600 mr-3"
                                                title="Xóa sản phẩm" @click="removeMainProduct(product.id)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <div class="text-gray-600">
                                                <i class="fas"
                                                    :class="product.showVariants ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="product.showVariants" class="ml-6 space-y-2">
                                        <template x-for="variant in product.variants" :key="variant.id">
                                            <div class="flex items-center p-3 border rounded-lg">
                                                <img :src="variant.image" class="w-12 h-12 rounded-md object-cover mr-4">
                                                <div class="flex-grow">
                                                    <p class="font-semibold text-gray-800" x-text="variant.display_name"></p>
                                                    <p class="text-xs text-gray-500" x-text="'SKU: ' + variant.sku"></p>
                                                    <input type="hidden" name="main_products[]" :value="variant.id">
                                                </div>
                                                <button type="button" class="text-gray-400 hover:text-red-600" title="Xóa"
                                                    @click="removeMainProduct(product.id, variant.id)">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            @error('main_products')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <div x-show="mainProducts.length === 0" class="text-gray-500 text-center">
                                Chưa có sản phẩm chính nào được chọn.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suggested Products Card -->
                <div class="card-custom">
                    <div class="card-custom-header flex justify-between items-center">
                        <h3 class="card-custom-subtitle">Sản phẩm bán kèm</h3>
                        <button @click="openSuggestedModal()" type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-2"></i>Thêm sản phẩm kèm
                        </button>
                    </div>
                    <div class="card-custom-body">
                        <p class="text-sm text-gray-600 mb-4">Danh sách sản phẩm được gợi ý mua kèm.</p>
                        <div class="space-y-4" x-ref="suggestedProductsList">
                            <template x-for="(variant, index) in suggestedProducts" :key="variant.id">
                                <div class="p-4 border rounded-lg flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img :src="variant.image" class="w-12 h-12 rounded-md object-cover mr-4">
                                        <div>
                                            <p class="font-semibold text-gray-800" x-text="variant.display_name"></p>
                                            <p class="text-xs text-gray-500" x-text="'SKU: ' + variant.sku"></p>
                                        </div>
                                    </div>
                                    <button type="button" class="text-red-500 hover:text-red-700"
                                        @click="removeSuggestedProduct(variant.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <input type="hidden" :name="'suggested_products[' + index + '][id]'"
                                        :value="variant.id">
                                    <input type="hidden" :name="'suggested_products[' + index + '][display_order]'"
                                        :value="index">
                                </div>
                            </template>
                            @error('suggested_products')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <div x-show="suggestedProducts.length === 0" class="text-gray-500 text-center">
                                Chưa có sản phẩm gợi ý nào được chọn.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('admin.bundle-products.index') }}" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary">Lưu Combo</button>
                </div>
            </form>

            <!-- Modal for Main Products -->
            <div x-show="isModalOpen" x-cloak @keydown.escape.window="closeModal()"
                class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 modal-backdrop" aria-hidden="true">
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
                    <div x-show="isModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="modal-panel inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                        <div class="px-6 py-4 border-b">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title" x-text="modalTitle">
                            </h3>
                        </div>
                        <div class="p-6">
                            <input type="text" x-model="searchQuery" placeholder="Tìm kiếm sản phẩm theo tên hoặc SKU..."
                                class="form-input">
                        </div>
                        <div class="px-6 pb-4 h-96 overflow-y-auto">
                            <div class="space-y-2" x-show="filteredProducts.length > 0">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <div class="border rounded-lg">
                                        <div class="flex items-center p-3 cursor-pointer hover:bg-gray-50">
                                            <div @click="toggleAllVariants(product)" class="mr-3">
                                                <i class="fas"
                                                    :class="areAllVariantsSelected(product) ? 'fa-check-circle text-indigo-600' :
                                                        'fa-circle text-gray-400'"></i>
                                            </div>
                                            <div @click="product.showVariants = !product.showVariants" class="flex-grow">
                                                <p class="font-semibold text-gray-800" x-text="product.name"></p>
                                            </div>
                                            <div class="text-gray-600">
                                                <i class="fas"
                                                    :class="product.showVariants ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                                            </div>
                                        </div>
                                        <div x-show="product.showVariants" class="ml-6 space-y-2">
                                            <template x-for="variant in product.variants" :key="variant.id">
                                                <div @click="toggleSelection(variant)"
                                                    :class="{ 'bg-indigo-50 border-indigo-300': isSelected(variant.id) }"
                                                    class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                                    <img :src="variant.image"
                                                        class="w-12 h-12 rounded-md object-cover mr-4">
                                                    <div class="flex-grow">
                                                        <p class="font-semibold text-gray-800" x-text="variant.display_name">
                                                        </p>
                                                        <p class="text-xs text-gray-500" x-text="'SKU: ' + variant.sku"></p>
                                                    </div>
                                                    <div x-show="isSelected(variant.id)" class="text-indigo-600">
                                                        <i class="fas fa-check-circle"></i>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="filteredProducts.length === 0" class="text-gray-500 text-center">
                                Không tìm thấy sản phẩm nào.
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                            <p class="text-sm text-gray-600">
                                Đã chọn <span x-text="selectedProducts.length" class="font-bold"></span> sản phẩm.
                            </p>
                            <div>
                                <button @click="closeModal()" type="button" class="btn btn-secondary mr-2">Hủy</button>
                                <button @click="addSelectedProducts()" type="button" class="btn btn-primary"
                                    :disabled="selectedProducts.length === 0">Thêm sản phẩm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Suggested Products -->
            <div x-show="isSuggestedModalOpen" x-cloak @keydown.escape.window="closeSuggestedModal()"
                class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="suggested-modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="isSuggestedModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeSuggestedModal()"
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 modal-backdrop" aria-hidden="true">
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"></span>
                    <div x-show="isSuggestedModalOpen" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="modal-panel inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                        <div class="px-6 py-4 border-b">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="suggested-modal-title">
                                Chọn sản phẩm bán kèm
                            </h3>
                        </div>
                        <div class="p-6">
                            <!-- Dropdown chọn danh mục -->
                            <div class="mb-4">
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Chọn danh mục</label>
                                <select x-model="selectedCategory" id="category_id" class="form-input"
                                    @change="fetchProductsByCategory()">
                                    <option value="">-- Chọn danh mục --</option>
                                    <template x-for="category in categories" :key="category.id">
                                        <option :value="category.id" x-text="category.name"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Tìm kiếm -->
                            <input type="text" x-model="suggestedSearchQuery" placeholder="Tìm kiếm sản phẩm theo tên hoặc SKU..."
                                class="form-input mb-4" @input.debounce.500ms="fetchProductsByCategory()">
                        </div>
                        <div class="px-6 pb-4 h-96 overflow-y-auto">
                            <div class="space-y-2" x-show="filteredSuggestedProducts.length > 0 && selectedCategory">
                                <template x-for="variant in filteredSuggestedProducts" :key="variant.id">
                                    <div @click="toggleSuggestedSelection(variant)"
                                        :class="{ 'bg-indigo-50 border-indigo-300': isSuggestedSelected(variant.id) }"
                                        class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                        <img :src="variant.image" class="w-12 h-12 rounded-md object-cover mr-4">
                                        <div class="flex-grow">
                                            <p class="font-semibold text-gray-800" x-text="variant.display_name"></p>
                                            <p class="text-xs text-gray-500" x-text="'SKU: ' + variant.sku"></p>
                                        </div>
                                        <div x-show="isSuggestedSelected(variant.id)" class="text-indigo-600">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="!selectedCategory" class="text-gray-500 text-center">
                                Vui lòng chọn danh mục để hiển thị sản phẩm.
                            </div>
                            <div x-show="selectedCategory && filteredSuggestedProducts.length === 0" class="text-gray-500 text-center">
                                Không tìm thấy sản phẩm nào trong danh mục này.
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                            <p class="text-sm text-gray-600">
                                Đã chọn <span x-text="selectedSuggestedProducts.length" class="font-bold"></span> sản phẩm.
                            </p>
                            <div>
                                <button @click="closeSuggestedModal()" type="button" class="btn btn-secondary mr-2">Hủy</button>
                                <button @click="addSelectedSuggestedProducts()" type="button" class="btn btn-primary"
                                    :disabled="selectedSuggestedProducts.length === 0">Thêm sản phẩm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function dealManager() {
            return {
                isModalOpen: false,
                modalTitle: '',
                modalType: '',
                searchQuery: '',
                mainProducts: [],
                suggestedProducts: [],
                allProducts: @json($products),
                selectedProducts: [],
                categories: @json($categories),
                isSuggestedModalOpen: false,
                selectedCategory: '',
                suggestedSearchQuery: '',
                filteredSuggestedProducts: [],
                selectedSuggestedProducts: [],

                init() {
                    this.allProducts.forEach(product => {
                        product.showVariants = false;
                    });
                    this.mainProducts.forEach(product => {
                        product.showVariants = false;
                    });
                    console.log("Categories loaded:", this.categories);
                },

                // Modal sản phẩm chính
                get filteredProducts() {
                    if (this.searchQuery.trim() === '') {
                        return this.allProducts;
                    }
                    const searchLower = this.searchQuery.toLowerCase();
                    return this.allProducts
                        .map(product => {
                            const matchesProduct = product.name.toLowerCase().includes(searchLower);
                            const matchingVariants = product.variants.filter(v =>
                                v.display_name.toLowerCase().includes(searchLower) ||
                                v.sku.toLowerCase().includes(searchLower)
                            );
                            if (matchesProduct || matchingVariants.length > 0) {
                                return {
                                    ...product,
                                    variants: matchesProduct ? product.variants : matchingVariants
                                };
                            }
                            return null;
                        })
                        .filter(product => product !== null);
                },

                isSelected(variantId) {
                    return this.selectedProducts.includes(variantId);
                },

                areAllVariantsSelected(product) {
                    return product.variants.every(variant => this.selectedProducts.includes(variant.id));
                },

                toggleSelection(variant) {
                    const index = this.selectedProducts.indexOf(variant.id);
                    if (index === -1) {
                        this.selectedProducts.push(variant.id);
                    } else {
                        this.selectedProducts.splice(index, 1);
                    }
                },

                toggleAllVariants(product) {
                    const allSelected = this.areAllVariantsSelected(product);
                    if (allSelected) {
                        product.variants.forEach(variant => {
                            const index = this.selectedProducts.indexOf(variant.id);
                            if (index !== -1) {
                                this.selectedProducts.splice(index, 1);
                            }
                        });
                    } else {
                        product.variants.forEach(variant => {
                            if (!this.selectedProducts.includes(variant.id)) {
                                this.selectedProducts.push(variant.id);
                            }
                        });
                    }
                },

                openModal(type) {
                    this.modalType = type;
                    this.modalTitle = 'Chọn sản phẩm chính';
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                    this.searchQuery = '';
                    this.selectedProducts = [];
                },

                addSelectedProducts() {
                    if (this.modalType === 'main') {
                        const groupedProducts = {};
                        this.allProducts.forEach(product => {
                            const selectedVariants = product.variants.filter(variant =>
                                this.selectedProducts.includes(variant.id)
                            );
                            if (selectedVariants.length > 0) {
                                groupedProducts[product.id] = {
                                    id: product.id,
                                    name: product.name,
                                    variants: selectedVariants.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)),
                                    showVariants: false
                                };
                            }
                        });
                        const newProducts = Object.values(groupedProducts);
                        this.mainProducts = [
                            ...this.mainProducts.filter(p => !newProducts.some(np => np.id === p.id)),
                            ...newProducts
                        ].sort((a, b) => {
                            const latestA = a.variants.length > 0 ? new Date(a.variants[0].created_at) : new Date(0);
                            const latestB = b.variants.length > 0 ? new Date(b.variants[0].created_at) : new Date(0);
                            return latestB - latestA;
                        });
                    }
                    this.closeModal();
                },

                removeMainProduct(productId, variantId = null) {
                    if (variantId) {
                        this.mainProducts = this.mainProducts.map(product => {
                            if (product.id === productId) {
                                product.variants = product.variants.filter(v => v.id !== variantId);
                            }
                            return product;
                        }).filter(product => product.variants.length > 0);
                    } else {
                        this.mainProducts = this.mainProducts.filter(p => p.id !== productId);
                    }
                },

                // Modal sản phẩm bán kèm
                openSuggestedModal() {
                    this.isSuggestedModalOpen = true;
                    this.selectedCategory = '';
                    this.suggestedSearchQuery = '';
                    this.filteredSuggestedProducts = [];
                    this.selectedSuggestedProducts = [];
                    console.log("Opening suggested modal");
                },

                closeSuggestedModal() {
                    this.isSuggestedModalOpen = false;
                    this.selectedCategory = '';
                    this.suggestedSearchQuery = '';
                    this.filteredSuggestedProducts = [];
                    this.selectedSuggestedProducts = [];
                    console.log("Closing suggested modal");
                },

                async fetchProductsByCategory() {
                    if (!this.selectedCategory) {
                        console.log("No category selected, clearing variants");
                        this.filteredSuggestedProducts = [];
                        return;
                    }

                    try {
                        console.log(`Fetching variants for category: ${this.selectedCategory}, search: ${this.suggestedSearchQuery}`);
                        const response = await fetch(`/admin/bundle-products/products?category_id=${this.selectedCategory}&search=${encodeURIComponent(this.suggestedSearchQuery)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (!response.ok) {
                            console.error(`HTTP error! Status: ${response.status}`);
                            const text = await response.text();
                            console.error('Response content:', text);
                            return;
                        }
                        const data = await response.json();
                        console.log("Received variants:", data.variants);
                        this.filteredSuggestedProducts = data.variants;
                    } catch (error) {
                        console.error("Error fetching variants:", error);
                    }
                },

                isSuggestedSelected(variantId) {
                    return this.selectedSuggestedProducts.includes(variantId);
                },

                toggleSuggestedSelection(variant) {
                    const index = this.selectedSuggestedProducts.indexOf(variant.id);
                    if (index === -1) {
                        this.selectedSuggestedProducts.push(variant.id);
                    } else {
                        this.selectedSuggestedProducts.splice(index, 1);
                    }
                },

                addSelectedSuggestedProducts() {
                    const selected = this.filteredSuggestedProducts.filter(variant =>
                        this.selectedSuggestedProducts.includes(variant.id)
                    ).map(variant => ({
                        ...variant,
                        discount_type: 'fixed_price',
                        discount_value: 0,
                        is_preselected: true
                    }));
                    this.suggestedProducts = [...this.suggestedProducts, ...selected]
                        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    this.closeSuggestedModal();
                },

                removeSuggestedProduct(variantId) {
                    this.suggestedProducts = this.suggestedProducts.filter(p => p.id !== variantId);
                }
            }
        }
    </script>
@endpush