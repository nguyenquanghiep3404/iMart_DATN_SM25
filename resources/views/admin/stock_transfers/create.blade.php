@extends('admin.layouts.app')

@section('title', 'Tạo Phiếu Chuyển Kho')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 42px;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.5rem 0.75rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-dropdown {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="stockTransferForm()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tạo Phiếu Chuyển Kho Mới</h1>
        <a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Quay Lại
        </a>
    </div>

    <form action="{{ route('admin.stock-transfers.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cột trái: Thông tin phiếu và sản phẩm -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Thông tin chung -->
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h2 class="text-xl font-semibold text-gray-700">Thông Tin Chung</h2>
                    </div>
                    <div class="card-custom-body grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="from_location_id" class="form-label">Kho Gửi <span class="text-red-500">*</span></label>
                            <select id="from_location_id" name="from_location_id" class="form-select select2-location" x-model.number="fromLocationId" @change="onFromLocationChange()">
                                <option value="">Chọn kho gửi</option>
                                @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                            <template x-if="errors.from_location_id"><p class="text-red-500 text-sm mt-1" x-text="errors.from_location_id[0]"></p></template>
                        </div>
                        <div>
                            <label for="to_location_id" class="form-label">Kho Nhận <span class="text-red-500">*</span></label>
                            <select id="to_location_id" name="to_location_id" class="form-select select2-location" x-model.number="toLocationId">
                                <option value="">Chọn kho nhận</option>
                                <template x-for="location in availableToLocations" :key="location.id">
                                    <option :value="location.id" x-text="location.name"></option>
                                </template>
                            </select>
                            <template x-if="errors.to_location_id"><p class="text-red-500 text-sm mt-1" x-text="errors.to_location_id[0]"></p></template>
                        </div>
                        <div class="md:col-span-2">
                            <label for="notes" class="form-label">Ghi Chú</label>
                            <textarea id="notes" name="notes" rows="3" class="form-input" placeholder="Thêm ghi chú cho phiếu chuyển kho..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Thêm sản phẩm -->
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h2 class="text-xl font-semibold text-gray-700">Sản Phẩm Chuyển Kho</h2>
                    </div>
                    <div class="card-custom-body">
                        <div class="relative">
                            <label for="product_search" class="form-label">Tìm kiếm sản phẩm</label>
                            <div class="flex items-center">
                                <i class="fas fa-search absolute left-3 text-gray-400"></i>
                                <input type="text" id="product_search"
                                    x-model="searchTerm"
                                    @input.debounce.300ms="searchProducts()"
                                    @focus="showSearchResults = true"
                                    :disabled="!fromLocationId"
                                    class="form-input pl-10"
                                    placeholder="Quét mã vạch hoặc tìm theo Tên, SKU">
                            </div>
                            <div x-show="showSearchResults" @click.away="showSearchResults = false" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 max-h-60 overflow-y-auto shadow-lg">
                                <template x-if="isLoading">
                                    <div class="p-4 text-center text-gray-500">Đang tìm kiếm...</div>
                                </template>
                                <template x-if="!isLoading && searchResults.length === 0 && searchTerm.length > 2">
                                    <div class="p-4 text-center text-gray-500">Không tìm thấy sản phẩm.</div>
                                </template>
                                <template x-for="product in searchResults" :key="product.id">
                                    <div @click="addProduct(product)" class="flex items-center p-3 hover:bg-gray-100 cursor-pointer">
                                        <img :src="product.image_url" alt="" class="w-12 h-12 object-cover rounded mr-4">
                                        <div class="flex-grow">
                                            <p class="font-semibold text-gray-800" x-text="product.name"></p>
                                            <p class="text-sm text-gray-500">SKU: <span x-text="product.sku"></span> - Tồn kho: <span x-text="product.stock"></span></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm đã chọn -->
                    <div class="overflow-x-auto border-t">
                        <table class="table-custom w-full">
                            <thead>
                                <tr>
                                    <th class="w-1/2">Sản Phẩm</th>
                                    <th class="w-1/4">SKU</th>
                                    <th class="w-1/4 text-center">Số Lượng</th>
                                    <th class="w-auto"></th>
                                </tr>
                            </thead>
                            <tbody x-show="items.length > 0">
                                <template x-for="(item, index) in items" :key="item.product_variant_id">
                                    <tr>
                                        <td>
                                            <div class="flex items-center">
                                                <img :src="item.image_url" class="w-12 h-12 object-cover rounded mr-4">
                                                <div>
                                                    <p class="font-semibold" x-text="item.name"></p>
                                                    <p class="text-sm text-gray-500">Tồn kho khả dụng: <span x-text="item.stock"></span></p>
                                                </div>
                                            </div>
                                            <input type="hidden" :name="`items[${index}][product_variant_id]`" :value="item.product_variant_id">
                                        </td>
                                        <td x-text="item.sku"></td>
                                        <td>
                                            <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" 
                                                   class="form-input text-center w-24 mx-auto" min="1" :max="item.stock">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div x-show="items.length === 0" class="text-center py-10 text-gray-500">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>Chưa có sản phẩm nào được thêm.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Tóm tắt và hành động -->
            <div class="lg:col-span-1">
                <div class="card-custom sticky top-8">
                    <div class="card-custom-header">
                        <h2 class="text-xl font-semibold text-gray-700">Tóm Tắt</h2>
                    </div>
                    <div class="card-custom-body space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tổng số sản phẩm:</span>
                            <span class="font-bold text-lg text-gray-800" x-text="totalProducts"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tổng số lượng:</span>
                            <span class="font-bold text-lg text-gray-800" x-text="totalQuantity"></span>
                        </div>
                    </div>
                    <div class="card-custom-footer">
                        <button type="submit" class="btn btn-primary w-full" :disabled="items.length === 0 || !fromLocationId || !toLocationId">
                            <i class="fas fa-save mr-2"></i> Tạo Phiếu Chuyển Kho
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('stockTransferForm', () => ({
            fromLocationId: '',
            toLocationId: '',
            allLocations: @json($locations),
            availableToLocations: [],
            searchTerm: '',
            searchResults: [],
            showSearchResults: false,
            isLoading: false,
            items: [],
            errors: {},

            init() {
                $('.select2-location').select2({
                    placeholder: "Chọn một kho",
                    width: '100%'
                });
                $('#from_location_id').on('change', (e) => {
                    this.fromLocationId = e.target.value;
                    this.onFromLocationChange();
                });
                $('#to_location_id').on('change', (e) => {
                    this.toLocationId = e.target.value;
                });
            },

            onFromLocationChange() {
                this.availableToLocations = this.allLocations.filter(loc => loc.id != this.fromLocationId);
                this.toLocationId = '';
                $('#to_location_id').val('').trigger('change');
                this.items = []; // Xóa sản phẩm đã chọn khi đổi kho gửi
                this.searchTerm = '';
                this.searchResults = [];
            },

            searchProducts() {
                if (this.searchTerm.length < 2 || !this.fromLocationId) {
                    this.searchResults = [];
                    return;
                }
                this.isLoading = true;
                fetch(`{{ route('admin.stock-transfers.search-products') }}?search=${this.searchTerm}&location_id=${this.fromLocationId}`)
                    .then(response => response.json())
                    .then(data => {
                        this.searchResults = data.allVariants.filter(p => !this.items.some(item => item.product_variant_id === p.id));
                        this.isLoading = false;
                    });
            },

            addProduct(product) {
                if (!this.items.some(item => item.product_variant_id === product.id)) {
                    this.items.push({
                        product_variant_id: product.id,
                        name: product.name,
                        sku: product.sku,
                        image_url: product.image_url,
                        quantity: 1,
                        stock: product.stock,
                    });
                }
                this.searchTerm = '';
                this.searchResults = [];
                this.showSearchResults = false;
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },
            
            get totalProducts() {
                return this.items.length;
            },

            get totalQuantity() {
                return this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
            },

            submitForm(event) {
                // Xóa lỗi cũ
                this.errors = {};

                // Tạo FormData
                const formData = new FormData(event.target);
                
                // Chuyển đổi items array thành định dạng mà Laravel có thể đọc
                this.items.forEach((item, index) => {
                    formData.append(`items[${index}][product_variant_id]`, item.product_variant_id);
                    formData.append(`items[${index}][quantity]`, item.quantity);
                });

                // Gửi request
                fetch(event.target.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (response.status === 422) { // Validation error
                        return response.json().then(data => {
                            this.errors = data.errors;
                            throw new Error('Validation failed');
                        });
                    }
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    // Nếu thành công, chuyển hướng trang
                    window.location.href = "{{ route('admin.stock-transfers.index') }}";
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    // Có thể hiển thị thông báo lỗi chung ở đây
                });
            }
        }));
    });
</script>
@endpush
