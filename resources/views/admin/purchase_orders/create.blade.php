@extends('admin.layouts.app')

@section('title', 'Tạo Phiếu Nhập Kho')

@push('styles')
    {{-- CSS Styles --}}
    <style>
        .card { background-color: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        input[type=number] { -moz-appearance: textfield; }
        input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        @keyframes flash-border {
            0%, 100% { border-color: #10b981; }
            50% { border-color: #34d399; box-shadow: 0 0 10px #34d399; }
        }
        .flash-success { animation: flash-border 0.7s ease-in-out; }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8" x-data="pageData({
    suppliersData: {{ Js::from($suppliers) }},
    provincesData: {{ Js::from($provinces) }},
    districtsData: {{ Js::from($districts) }},
    locationsData: {{ Js::from($locations) }}
})">

    <div class="container mx-auto max-w-full">
        <form id="purchase-order-form" @submit.prevent="submitForm" method="POST" action="{{ route('admin.purchase-orders.store') }}">
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
                    {{-- From: admin.purchase_orders.partials._product_search --}}
                    <div class="card">
                        <div class="p-6">
                            <label for="product-search" class="block text-lg font-semibold text-gray-800 mb-2">Tìm & Thêm Sản Phẩm</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="product-search" placeholder="Gõ tên sản phẩm hoặc SKU..."
                                       class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                
                                {{-- Icon quét barcode --}}
                                <div @click="openBarcodeScanner()" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" title="Quét mã vạch">
                                    <i class="fas fa-barcode text-gray-400 hover:text-gray-600 transition-colors"></i>
                                </div>
                    
                                <div id="search-suggestions"
                                     class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-2 shadow-lg max-h-80 overflow-y-auto hidden custom-scrollbar">
                                    <!-- Kết quả tìm kiếm sẽ được hiển thị ở đây bởi JS -->
                                </div>
                            </div>
                        </div>
                    </div>

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
        @include('admin.purchase_orders.partials._barcode_scanner_modal')
    </div>
</div>
@endsection

@push('scripts')
<script>
    function pageData(initialData) {
        return {
            // --- MODAL STATE ---
            isModalOpen: false,
            modalType: '',
            modalTitle: '',
            modalSelectedProvince: '',
            modalSelectedDistrict: '',
            modalSearchTerm: '',

            // --- FORM STATE ---
            selectedSupplier: { name: null, address: null, addressId: null },
            selectedLocation: { name: null, address: null, id: null },

            // --- INITIAL DATA from Controller ---
            provinces: initialData.provincesData,
            districts: initialData.districtsData,
            allSuppliers: initialData.suppliersData,
            allLocations: initialData.locationsData,
            
            // --- BARCODE SCANNER STATE ---
            isScannerOpen: false,
            html5QrCode: null,

            init() {
                this.$watch('modalSelectedProvince', () => { this.modalSelectedDistrict = ''; });
                document.getElementById('order-date').valueAsDate = new Date();
                
                // Listener để nhận mã vạch từ modal
                window.addEventListener('barcode-scanned', (event) => {
                    const scannedCode = event.detail.code;
                    document.getElementById('product-search').value = scannedCode;
                    // Tự động kích hoạt sự kiện tìm kiếm
                    document.getElementById('product-search').dispatchEvent(new Event('input'));
                });

                // Khởi tạo logic tìm kiếm sản phẩm
                this.initializeProductSearch();
            },
            
            get filteredDistricts() {
                if (!this.modalSelectedProvince) return [];
                // Sử dụng 'parent_code' và 'code' từ dữ liệu thực tế
                return this.districts.filter(d => d.parent_code == this.modalSelectedProvince);
            },

            get modalSourceData() {
                if (this.modalType === 'supplier') {
                    // Dữ liệu NCC đã được định dạng sẵn từ controller
                    return this.allSuppliers.flatMap(s => s.addresses.map(addr => ({
                        ...addr,
                        name: s.name
                    })));
                }
                if (this.modalType === 'location') {
                     // Dữ liệu kho hàng đã có sẵn full_address
                    return this.allLocations;
                }
                return [];
            },
            
            get filteredModalItems() {
                let items = this.modalSourceData;
                if (this.modalSelectedProvince) {
                    items = items.filter(i => i.province_id == this.modalSelectedProvince);
                }
                if (this.modalSelectedDistrict) {
                    items = items.filter(i => i.district_id == this.modalSelectedDistrict);
                }
                if (this.modalSearchTerm.trim()) {
                    const search = this.modalSearchTerm.toLowerCase();
                    items = items.filter(i => 
                        i.name.toLowerCase().includes(search) || 
                        (i.fullAddress && i.fullAddress.toLowerCase().includes(search)) || 
                        (i.phone && i.phone.includes(search))
                    );
                }
                return items;
            },

            openModal(type) {
                this.modalType = type;
                this.modalTitle = type === 'supplier' ? 'Chọn địa chỉ nhà cung cấp' : 'Chọn kho nhận hàng';
                this.isModalOpen = true;
                this.modalSelectedProvince = '';
                this.modalSelectedDistrict = '';
                this.modalSearchTerm = '';
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

            submitForm() {
                if (!this.selectedSupplier.addressId || !this.selectedLocation.id) {
                    alert('Vui lòng chọn đầy đủ Nhà cung cấp và Kho nhận hàng.');
                    return;
                }
                document.getElementById('purchase-order-form').submit();
            },
            
            // --- BARCODE SCANNER METHODS ---
            openBarcodeScanner() {
                this.isScannerOpen = true;
                this.$nextTick(() => { this.startScanning(); });
            },
            closeBarcodeScanner() {
                this.isScannerOpen = false;
                this.stopScanning();
            },
            startScanning() {
                const readerElement = document.getElementById('reader');
                const loadingMessage = document.getElementById('loading-message');
                const errorMessage = document.getElementById('scan-error-message');
                if (!readerElement) return;
                loadingMessage.classList.remove('hidden');
                errorMessage.classList.add('hidden');
                this.html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 150 } };
                this.html5QrCode.start({ facingMode: "environment" }, config, 
                    (decodedText, decodedResult) => {
                        loadingMessage.classList.add('hidden');
                        document.getElementById('scan-result-text').textContent = decodedText;
                        document.getElementById('scan-result-container').classList.remove('hidden');
                        document.getElementById('reader').classList.add('flash-success');
                        window.dispatchEvent(new CustomEvent('barcode-scanned', { detail: { code: decodedText } }));
                        setTimeout(() => {
                            document.getElementById('reader').classList.remove('flash-success');
                            this.closeBarcodeScanner();
                        }, 800);
                    }, 
                    (error) => {}
                ).catch(err => {
                    loadingMessage.classList.add('hidden');
                    errorMessage.textContent = `Lỗi camera: ${err}`;
                    errorMessage.classList.remove('hidden');
                });
            },
            stopScanning() {
                if (this.html5QrCode && this.html5QrCode.isScanning) {
                    this.html5QrCode.stop().catch(err => console.error("Lỗi khi dừng camera:", err));
                }
            },

            // --- PRODUCT SEARCH & TABLE LOGIC ---
            initializeProductSearch() {
                const searchInput = document.getElementById('product-search');
                const suggestionsContainer = document.getElementById('search-suggestions');
                const purchaseItemsTableBody = document.getElementById('purchase-items-table');
                const noItemsRow = document.getElementById('no-items-row');
                const grandTotalEl = document.getElementById('grand-total');
                let allProductVariants = [];

                const fetchProducts = async (term) => {
                    try {
                        const response = await fetch(`{{ route('admin.purchase-orders.search-products') }}?search=${term}`);
                        if (!response.ok) throw new Error('Network response was not ok');
                        const data = await response.json();
                        allProductVariants = data.allVariants; // Lưu danh sách phẳng
                        displaySuggestions(data.groupedProducts);
                    } catch (error) {
                        console.error("Lỗi khi tìm sản phẩm:", error);
                        suggestionsContainer.innerHTML = `<div class="p-3 text-center text-red-500">Có lỗi xảy ra khi tìm kiếm.</div>`;
                        suggestionsContainer.classList.remove('hidden');
                    }
                };
                
                const displaySuggestions = (products) => {
                    if (products.length === 0) {
                        suggestionsContainer.innerHTML = `<div class="p-3 text-center text-gray-500">Không tìm thấy sản phẩm.</div>`;
                    } else {
                        suggestionsContainer.innerHTML = products.map(p => `
                            <div class="p-2 border-b border-gray-100">
                                <h4 class="px-2 py-1 font-bold text-gray-800 text-sm">${p.parentName}</h4>
                                <div class="pl-2">
                                    ${p.variants.map(v => `
                                        <div class="flex items-center p-2 hover:bg-blue-50 cursor-pointer rounded-md suggestion-item" data-product-id="${v.id}">
                                            <img src="${v.image_url}" alt="${v.variantName}" class="w-10 h-10 object-cover rounded-md mr-3">
                                            <div>
                                                <div class="font-semibold text-gray-800">${v.variantName}</div>
                                                <div class="text-xs text-gray-500">SKU: ${v.sku} | Tồn kho: <span class="font-bold">${v.stock}</span></div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `).join('');
                    }
                    suggestionsContainer.classList.remove('hidden');
                };

                const addProductToTable = (productId) => {
                    if (purchaseItemsTableBody.querySelector(`tr[data-variant-id="${productId}"]`)) {
                        const existingRow = purchaseItemsTableBody.querySelector(`tr[data-variant-id="${productId}"]`);
                        existingRow.classList.add('bg-yellow-100');
                        setTimeout(() => { existingRow.classList.remove('bg-yellow-100'); }, 1500);
                        return;
                    }
                    const product = allProductVariants.find(p => p.id === productId);
                    if (!product) return;

                    noItemsRow.style.display = 'none';
                    const newRow = document.createElement('tr');
                    newRow.className = 'bg-white border-b purchase-item-row transition-colors';
                    newRow.dataset.variantId = product.id;
                    newRow.innerHTML = `
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <img src="${product.image_url}" alt="${product.name}" class="w-10 h-10 object-cover rounded-md mr-3">
                                <div>
                                    <div class="font-medium text-gray-800">${product.name}</div>
                                    <div class="text-xs text-gray-500">SKU: ${product.sku}</div>
                                    <input type="hidden" name="items[${product.id}][product_variant_id]" value="${product.id}">
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-blue-600">${product.stock}</td>
                        <td class="px-4 py-3">
                            <input type="number" name="items[${product.id}][quantity]" class="w-full p-2 border border-gray-300 rounded-md text-sm text-center quantity-input" value="1" min="1">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" name="items[${product.id}][cost_price]" class="w-full p-2 border border-gray-300 rounded-md text-sm text-right cost-price-input" value="${product.cost_price}" min="0">
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 subtotal">${formatCurrency(product.cost_price)}</td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" class="text-red-500 hover:text-red-700 remove-item-btn font-bold text-xl">&times;</button>
                        </td>
                    `;
                    purchaseItemsTableBody.appendChild(newRow);
                    updateGrandTotal();
                };

                const updateGrandTotal = () => {
                    let total = 0;
                    const rows = purchaseItemsTableBody.querySelectorAll('.purchase-item-row');
                    rows.forEach(row => {
                        const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
                        const costPrice = parseFloat(row.querySelector('.cost-price-input').value) || 0;
                        const subtotal = quantity * costPrice;
                        row.querySelector('.subtotal').textContent = formatCurrency(subtotal);
                        total += subtotal;
                    });
                    grandTotalEl.textContent = formatCurrency(total);
                };

                const formatCurrency = (number) => {
                    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(number);
                };

                let debounceTimer;
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        fetchProducts(searchInput.value);
                    }, 300); // Đợi 300ms sau khi người dùng ngừng gõ
                });
                
                // Hiển thị gợi ý khi focus
                searchInput.addEventListener('focus', () => {
                    if(!searchInput.value) {
                         fetchProducts('');
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!suggestionsContainer.contains(e.target) && e.target !== searchInput) {
                        suggestionsContainer.classList.add('hidden');
                    }
                });

                suggestionsContainer.addEventListener('click', (e) => {
                    const item = e.target.closest('.suggestion-item');
                    if (item) {
                        const productId = parseInt(item.dataset.productId);
                        addProductToTable(productId);
                        searchInput.value = '';
                        suggestionsContainer.classList.add('hidden');
                    }
                });

                purchaseItemsTableBody.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-item-btn')) {
                        e.target.closest('tr').remove();
                        if (purchaseItemsTableBody.querySelectorAll('.purchase-item-row').length === 0) {
                            noItemsRow.style.display = 'table-row';
                        }
                        updateGrandTotal();
                    }
                });

                purchaseItemsTableBody.addEventListener('input', (e) => {
                    if(e.target.classList.contains('quantity-input') || e.target.classList.contains('cost-price-input')) {
                        updateGrandTotal();
                    }
                });
            }
        }
    }
</script>
@endpush
