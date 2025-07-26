@extends('admin.layouts.app')

@section('title', 'Quản lý Gói Sản Phẩm - Chỉnh sửa Deal Bán Kèm')

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

        /* Modal Styles */
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
                <h1 class="text-3xl font-bold text-gray-800">Chỉnh sửa Deal Bán Kèm</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.bundle-products.index') }}"
                                class="text-indigo-600 hover:text-indigo-800">Deal bán kèm</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Chỉnh sửa</li>
                    </ol>
                </nav>
            </header>

            <form action="{{ route('admin.bundle-products.update', $bundle) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')
                <!-- General Information Card -->
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h3 class="card-custom-subtitle">Thông tin chung</h3>
                    </div>
                    <div class="card-custom-body grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bundle_name" class="block text-sm font-medium text-gray-700 mb-1">Tên nội bộ của
                                Deal</label>
                            <input type="text" id="bundle_name" name="bundle_name" class="form-input"
                                placeholder="Ví dụ: Deal ra mắt iPhone 15" value="{{ old('bundle_name', $bundle->name) }}">
                            @error('bundle_name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Tên này chỉ dùng để quản lý, không hiển thị cho khách
                                hàng.</p>
                        </div>
                        <div>
                            <label for="bundle_title" class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề hiển
                                thị</label>
                            <input type="text" id="bundle_title" name="bundle_title" class="form-input"
                                placeholder="Ví dụ: Mua Kèm Deal Sốc"
                                value="{{ old('bundle_title', $bundle->display_title) }}">
                            @error('bundle_title')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Tiêu đề này sẽ hiển thị trên trang sản phẩm.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label for="bundle_description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả (tùy
                                chọn)</label>
                            <textarea id="bundle_description" name="bundle_description" rows="3" class="form-textarea">{{ old('bundle_description', $bundle->description) }}</textarea>
                            @error('bundle_description')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt
                                đầu</label>
                            <input type="date" id="start_date" name="start_date" class="form-input"
                                value="{{ old('start_date', $bundle->start_date?->format('Y-m-d')) }}">
                            @error('start_date')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
                            <input type="date" id="end_date" name="end_date" class="form-input"
                                value="{{ old('end_date', $bundle->end_date?->format('Y-m-d')) }}">
                            @error('end_date')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <label class="form-switch">
                                <input type="checkbox" name="status"
                                    {{ old('status', $bundle->status === 'active') ? 'checked' : '' }}>
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
                        <button @click="openModal('main')" type="button" class="btn btn-primary btn-sm"><i
                                class="fas fa-plus mr-2"></i>Thêm sản phẩm</button>
                    </div>
                    <div class="card-custom-body">
                        <p class="text-sm text-gray-600 mb-4">Đây là các sản phẩm mà khi khách hàng xem, deal này sẽ được
                            hiển thị. (Ví dụ: iPhone 15 Pro Max)</p>
                        <!-- Product List -->
                        <div class="space-y-3" x-ref="mainProductsList">
                            <template x-for="product in mainProducts" :key="product.id">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                                    <div class="flex items-center">
                                        <img :src="product.image" class="w-12 h-12 rounded-md object-cover mr-4">
                                        <div>
                                            <p class="font-semibold text-gray-800" x-text="product.name"></p>
                                            <p class="text-xs text-gray-500" x-text="'SKU: ' + product.sku"></p>
                                            <input type="hidden" name="main_products[]" :value="product.id">
                                        </div>
                                    </div>
                                    <button type="button" class="text-gray-400 hover:text-red-600" title="Xóa"
                                        @click="removeMainProduct(product.id)"><i class="fas fa-times-circle"></i></button>
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
        <button @click="openModal('suggested')" type="button" class="btn btn-primary btn-sm"><i
                class="fas fa-plus mr-2"></i>Thêm sản phẩm kèm</button>
    </div>
    <div class="card-custom-body">
        <p class="text-sm text-gray-600 mb-4">Đây là danh sách các sản phẩm được gợi ý mua kèm với giá ưu đãi.</p>
        <!-- Suggested Product List -->
        <div class="space-y-4" x-ref="suggestedProductsList">
            <template x-for="(product, index) in suggestedProducts" :key="product.id">
                <div class="p-4 border rounded-lg" x-data="{ open: true, discountType: product.discount_type }">
                    <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                        <div class="flex items-center">
                            <i class="fas fa-grip-vertical text-gray-400 mr-3 cursor-move"></i>
                            <img :src="product.image" class="w-12 h-12 rounded-md object-cover mr-4">
                            <p class="font-semibold text-gray-800" x-text="product.name"></p>
                        </div>
                        <div class="flex items-center gap-4">
                            <button type="button" class="text-red-500 hover:text-red-700" @click="removeSuggestedProduct(product.id)"><i
                                    class="fas fa-trash"></i></button>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                        </div>
                    </div>
                    <div x-show="open" class="mt-4 pt-4 border-t grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loại giảm giá</label>
                            <select class="form-select" :name="'suggested_products[' + index + '][discount_type]'" x-model="discountType">
                                <option value="fixed_price" :selected="product.discount_type === 'fixed_price'">Giá cố định</option>
                                <option value="percentage_discount" :selected="product.discount_type === 'percentage_discount'">Giảm theo %</option>
                            </select>
                            
                        </div>
                        <div class="input-group">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị giảm</label>
                            <input type="number" class="form-input" :name="'suggested_products[' + index + '][discount_value]'"
                                :value="product.discount_value || 0" min="0"
                                :max="discountType === 'percentage_discount' ? 100 : null"
                                x-on:input="discountType === 'percentage_discount' && $event.target.value > 100 ? $event.target.value = 100 : null">
                            <span class="input-unit" x-text="discountType === 'fixed_price' ? 'VNĐ' : '%'"></span>
                           
                            <p x-show="discountType === 'percentage_discount' && product.discount_value > 100"
                               class="error-text">Giá trị giảm phần trăm phải từ 0-100.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                    :name="'suggested_products[' + index + '][is_preselected]'" :checked="product.is_preselected" value="1">
                                <span class="ml-2 text-sm text-gray-700">Chọn sẵn cho khách hàng</span>
                            </label>
                           
                        </div>
                        <input type="hidden" :name="'suggested_products[' + index + '][id]'" :value="product.id">
                        <input type="hidden" :name="'suggested_products[' + index + '][display_order]'" :value="index">
                    </div>
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
                    <button type="submit" class="btn btn-primary">Cập nhật Deal</button>
                </div>
            </form>
        </div>

        <!-- Product Selection Modal -->
        <div x-show="isModalOpen" x-cloak @keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 modal-backdrop" aria-hidden="true">
                </div>

                <!-- Modal Panel -->
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
                                <div @click="toggleSelection(product)"
                                    :class="{ 'bg-indigo-50 border-indigo-300': isSelected(product.id) }"
                                    class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <img :src="product.image" class="w-12 h-12 rounded-md object-cover mr-4">
                                    <div class="flex-grow">
                                        <p class="font-semibold text-gray-800" x-text="product.name"></p>
                                        <p class="text-xs text-gray-500" x-text="'SKU: ' + product.sku"></p>
                                    </div>
                                    <div x-show="isSelected(product.id)" class="text-indigo-600">
                                        <i class="fas fa-check-circle"></i>
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
    </div>
@endsection

@push('scripts')
    <script>
        function dealManager() {
            return {
                isModalOpen: false,
                modalTitle: '',
                modalType: '', // 'main' or 'suggested'
                searchQuery: '',
                mainProducts: @json($mainProducts),
                suggestedProducts: @json($suggestedProducts),
                allProducts: @json($productVariants),
                selectedProducts: [],

                get filteredProducts() {
                    if (this.searchQuery.trim() === '') {
                        return this.allProducts;
                    }
                    const searchLower = this.searchQuery.toLowerCase();
                    return this.allProducts.filter(p =>
                        p.name.toLowerCase().includes(searchLower) ||
                        p.sku.toLowerCase().includes(searchLower)
                    );
                },

                isSelected(productId) {
                    return this.selectedProducts.includes(productId);
                },

                toggleSelection(product) {
                    const index = this.selectedProducts.indexOf(product.id);
                    if (index === -1) {
                        this.selectedProducts.push(product.id);
                    } else {
                        this.selectedProducts.splice(index, 1);
                    }
                },

                openModal(type) {
                    this.modalType = type;
                    this.modalTitle = type === 'main' ? 'Chọn sản phẩm chính' : 'Chọn sản phẩm bán kèm';
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                    this.searchQuery = '';
                    this.selectedProducts = [];
                },

                addSelectedProducts() {
                    const selected = this.allProducts.filter(p => this.selectedProducts.includes(p.id));
                    if (this.modalType === 'main') {
                        this.mainProducts = [...this.mainProducts, ...selected];
                    } else {
                        this.suggestedProducts = [...this.suggestedProducts, ...selected.map(product => ({
                            ...product,
                            discount_type: 'fixed_price',
                            discount_value: 0,
                            is_preselected: true
                        }))];
                    }
                    this.closeModal();
                },

                removeMainProduct(productId) {
                    this.mainProducts = this.mainProducts.filter(p => p.id !== productId);
                },

                removeSuggestedProduct(productId) {
                    this.suggestedProducts = this.suggestedProducts.filter(p => p.id !== productId);
                }
            }
        }
    </script>
@endpush
