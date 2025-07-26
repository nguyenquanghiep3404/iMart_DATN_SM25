@extends('admin.layouts.app')

@section('title', 'Tạo Phiếu Nhập Kho')

@push('styles')
    <style>
        .card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8" x-data="pageData({
    suppliersData: {{ Js::from($suppliers) }},
    provincesData: {{ Js::from($provinces) }},
    locationsData: {{ Js::from($locations) }}
})">

    <div class="container mx-auto max-w-full">
        <form id="purchase-order-form" @submit.prevent="submitForm">
            @csrf
            <!-- Header -->
            <header class="mb-8 flex flex-col sm:flex-row items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Tạo Phiếu Nhập Kho</h1>
                    <p class="text-gray-600 mt-1">Tạo đơn hàng mới để nhập sản phẩm từ nhà cung cấp.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                     <a href="{{ route('admin.purchase-orders.index') }}" class="w-full sm:w-auto flex items-center justify-center bg-white text-gray-700 font-bold py-2 px-4 rounded-lg shadow-md hover:bg-gray-100 border border-gray-300 transition-colors">
                        Hủy Bỏ
                    </a>
                    <button type="submit" class="w-full sm:w-auto flex items-center justify-center bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Tạo Phiếu Nhập
                    </button>
                </div>
            </header>

            <!-- Main Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Product Details -->
                <div class="lg:col-span-2 space-y-8">
                    @include('admin.purchase_orders.partials._product_search')
                    @include('admin.purchase_orders.partials._product_list')
                </div>

                <!-- Right Column: General Info -->
                <div class="lg:col-span-1 space-y-8">
                    @include('admin.purchase_orders.partials._general_info')
                </div>
            </div>
        </form>

        <!-- Modals -->
        @include('admin.purchase_orders.partials._modals')
    </div>
</div>
@endsection

@push('scripts')
<script>
    function pageData(initialData) {
        return {
            // --- MODAL STATE ---
            isModalOpen: false,
            modalType: '', // 'supplier' or 'location'
            modalTitle: '',
            modalSelectedProvince: '',
            modalSelectedDistrict: '',
            modalSearchTerm: '',

            // --- FORM STATE ---
            selectedSupplier: { name: null, address: null, addressId: null },
            selectedLocation: { name: null, address: null, id: null },

            // --- INITIAL DATA from Controller ---
            provinces: initialData.provincesData,
            districts: [], // Will be populated dynamically if needed
            allSuppliers: initialData.suppliersData,
            allLocations: initialData.locationsData,

            init() {
                this.$watch('modalSelectedProvince', () => { this.modalSelectedDistrict = ''; });
                document.getElementById('order-date').valueAsDate = new Date();
            },

            get modalSourceData() {
                if (this.modalType === 'supplier') {
                    // Flatten supplier addresses for modal display
                    return this.allSuppliers.flatMap(s => s.addresses.map(addr => ({
                        ...addr,
                        id: addr.id,
                        name: s.name,
                        fullAddress: this.formatAddress(addr)
                    })));
                }
                if (this.modalType === 'location') {
                     return this.allLocations.map(loc => ({
                        ...loc,
                        fullAddress: this.formatAddress(loc)
                    }));
                }
                return [];
            },
            
            get filteredModalItems() {
                let items = this.modalSourceData;
                // Filtering logic will go here based on province, district, search term
                return items;
            },

            openModal(type) {
                this.modalType = type;
                this.modalTitle = type === 'supplier' ? 'Chọn địa chỉ nhà cung cấp' : 'Chọn kho nhận hàng';
                this.isModalOpen = true;
            },

            selectModalItem(item) {
                if (this.modalType === 'supplier') {
                    this.selectedSupplier.name = item.name;
                    this.selectedSupplier.address = item.fullAddress;
                    this.selectedSupplier.addressId = item.id;
                }
                 if (this.modalType === 'location') {
                    this.selectedLocation.name = item.name;
                    this.selectedLocation.address = item.fullAddress;
                    this.selectedLocation.id = item.id;
                }
                this.isModalOpen = false;
            },

            formatAddress(addr) {
                let parts = [
                    addr.address, 
                    addr.ward?.name_with_type, 
                    addr.district?.name_with_type, 
                    addr.province?.name_with_type
                ];
                return parts.filter(Boolean).join(', ');
            },

            submitForm() {
                if (!this.selectedSupplier.addressId || !this.selectedLocation.id) {
                    alert('Vui lòng chọn đầy đủ Nhà cung cấp và Kho nhận hàng.');
                    return;
                }
                document.getElementById('purchase-order-form').submit();
            }
        }
    }
    
    // Non-AlpineJS logic for product search and table
    document.addEventListener('DOMContentLoaded', () => {
        // Your existing JS for product search and table manipulation goes here...
        // ...
    });
</script>
@endpush
