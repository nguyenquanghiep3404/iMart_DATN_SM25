@extends('admin.layouts.app')

@section('title', 'Cập nhật Phiếu Nhập Kho')

@push('styles')
    <style>
        .card { background-color: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 6px; }
        input[type=number] { -moz-appearance: textfield; }
        input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .status-badge { display: inline-flex; align-items: center; padding: 0.25em 0.6em; font-size: 0.875rem; font-weight: 600; line-height: 1; border-radius: 0.375rem; }
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-completed { background-color: #D1FAE5; color: #065F46; }
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; }
        .disabled-ui { pointer-events: none; opacity: 0.6; background-color: #f9fafb; }
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
    locationsData: {{ Js::from($locations) }},
    purchaseOrder: {{ Js::from($purchaseOrder) }}
})">

    <div class="container mx-auto max-w-full">
        <form id="purchase-order-form" @submit.prevent="submitForm" method="POST" action="{{ route('admin.purchase-orders.update', $purchaseOrder->id) }}">
            @csrf
            @method('PUT')
            
            <header class="mb-8 flex flex-col sm:flex-row items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Cập nhật Phiếu Nhập Kho #{{ $purchaseOrder->po_code }}</h1>
                    <p class="text-gray-600 mt-1">Chỉnh sửa thông tin chi tiết của phiếu nhập.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="w-full sm:w-auto flex items-center justify-center bg-white text-gray-700 font-bold py-2 px-4 rounded-lg shadow-md hover:bg-gray-100 border border-gray-300 transition-colors">
                        Hủy Bỏ
                    </a>
                    <button type="submit" :disabled="isLocked"
                            class="w-full sm:w-auto flex items-center justify-center bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition-colors"
                            :class="{'opacity-50 cursor-not-allowed': isLocked }">
                        <i class="fas fa-save mr-2"></i>
                        Lưu Thay Đổi
                    </button>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Cột trái --}}
                <div class="lg:col-span-2 space-y-8" :class="{ 'disabled-ui': isLocked }">
                    <div class="card">
                        <div class="p-6">
                            <label for="product-search" class="block text-lg font-semibold text-gray-800 mb-2">Tìm & Thêm Sản Phẩm</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>
                                <input type="text" id="product-search" placeholder="Gõ tên sản phẩm hoặc SKU..." class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <div @click="openBarcodeScanner()" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" title="Quét mã vạch"><i class="fas fa-barcode text-gray-400 hover:text-gray-600 transition-colors"></i></div>
                                <div id="search-suggestions" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-2 shadow-lg max-h-80 overflow-y-auto hidden custom-scrollbar"></div>
                            </div>
                        </div>
                    </div>
                    @include('admin.purchase_orders.partials._product_list')
                </div>

                {{-- Cột phải --}}
                <div class="lg:col-span-1 space-y-8">
                    <div class="card">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin Phiếu nhập</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-600">Mã phiếu:</span>
                                    <span class="font-mono text-gray-900">{{ $purchaseOrder->po_code }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-600">Trạng thái:</span>
                                    <span class="status-badge" :class="statusInfo.class" x-text="statusInfo.text"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Khối thông tin chung và ghi chú được bao bọc bởi div để áp dụng disabled-ui --}}
                    <div class="space-y-8" :class="{ 'disabled-ui': isLocked }">
                        @include('admin.purchase_orders.partials._general_info')
                    </div>
                </div>
            </div>
        </form>

        @include('admin.purchase_orders.partials._modals')
        @include('admin.purchase_orders.partials._barcode_scanner_modal')
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>

<script>
    function pageData(initialData) {
        return {
            // State
            isModalOpen: false, modalType: '', modalTitle: '', modalSelectedProvince: '', modalSelectedDistrict: '', modalSearchTerm: '',
            selectedSupplier: { name: null, address: null, addressId: null },
            selectedLocation: { name: null, address: null, id: null },
            isScannerOpen: false, html5QrCode: null,
            beepSound: null, soundInitialized: false,

            // Initial Data
            provinces: initialData.provincesData, districts: initialData.districtsData, allSuppliers: initialData.suppliersData, allLocations: initialData.locationsData,
            purchaseOrder: initialData.purchaseOrder,
            isLocked: initialData.purchaseOrder.status !== 'pending',
            statusInfo: {},

            init() {
                this.$watch('modalSelectedProvince', () => { this.modalSelectedDistrict = ''; });
                
                window.addEventListener('barcode-scanned', (event) => {
                    const scannedCode = event.detail.code;
                    document.getElementById('product-search').value = scannedCode;
                    document.getElementById('product-search').dispatchEvent(new Event('input'));
                });
                
                this.initializeProductSearch();
                this.populateInitialForm();
                this.initializeSound();
            },
            
            initializeSound() {
                if (this.soundInitialized || typeof Howl === 'undefined') return;
                this.beepSound = new Howl({
                    src: ['{{ asset('sounds/shop-scanner-beeps.mp3') }}'],
                    volume: 0.8,
                    onload: () => { this.soundInitialized = true; },
                    onloaderror: (id, err) => { console.error('Failed to load beep sound:', err); }
                });
            },

            playBeep() {
                if (this.soundInitialized && this.beepSound) this.beepSound.play();
            },

            populateInitialForm() {
                const statuses = {
                    pending: { text: 'Đang chờ', class: 'status-pending' },
                    completed: { text: 'Hoàn thành', class: 'status-completed' },
                    cancelled: { text: 'Đã hủy', class: 'status-cancelled' }
                };
                this.statusInfo = statuses[this.purchaseOrder.status] || { text: this.purchaseOrder.status, class: '' };

                if (this.purchaseOrder.supplier) {
                    this.selectedSupplier = {
                        name: this.purchaseOrder.supplier.name,
                        address: this.purchaseOrder.supplier.full_address,
                        addressId: this.purchaseOrder.supplier.id,
                    };
                }
                if (this.purchaseOrder.store_location) {
                    this.selectedLocation = {
                        name: this.purchaseOrder.store_location.name,
                        address: this.purchaseOrder.store_location.full_address,
                        id: this.purchaseOrder.store_location.id,
                    };
                }
                
                const orderDateEl = document.getElementById('order-date');
                const notesEl = document.getElementById('notes');
                if(orderDateEl) orderDateEl.value = this.purchaseOrder.order_date;
                if(notesEl) notesEl.value = this.purchaseOrder.notes || ''; // SỬA LỖI: Xử lý giá trị null

                if (this.purchaseOrder.items && this.purchaseOrder.items.length > 0) {
                    this.purchaseOrder.items.forEach(item => {
                        const variant = item.product_variant;
                        if (!variant) return;

                        const productForTable = {
                            id: variant.id,
                            name: `${variant.product.name} - ${variant.attribute_values.map(av => av.value).join(' - ')}`,
                            sku: variant.sku,
                            image_url: variant.primary_image ? `/storage/${variant.primary_image.path}` : '{{ asset('assets/admin/img/placeholder-image.png') }}',
                            stock: variant.inventories ? variant.inventories.reduce((sum, inv) => sum + inv.quantity, 0) : 0,
                            cost_price: variant.cost_price || 0
                        };
                        
                        this.addProductToTable(productForTable, {
                            quantity: item.quantity,
                            cost_price: item.cost_price
                        });
                    });
                }
            },

            get filteredDistricts() {
                if (!this.modalSelectedProvince) return [];
                return this.districts.filter(d => d.parent_code == this.modalSelectedProvince);
            },

            get modalSourceData() {
                if (this.modalType === 'supplier') return this.allSuppliers.flatMap(s => s.addresses.map(addr => ({ ...addr, name: s.name })));
                if (this.modalType === 'location') return this.allLocations;
                return [];
            },
            
            get filteredModalItems() {
                let items = this.modalSourceData;
                if (this.modalSelectedProvince) items = items.filter(i => i.province_id == this.modalSelectedProvince);
                if (this.modalSelectedDistrict) items = items.filter(i => i.district_id == this.modalSelectedDistrict);
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
                if(this.isLocked) return;
                this.modalType = type;
                this.modalTitle = type === 'supplier' ? 'Chọn địa chỉ nhà cung cấp' : 'Chọn kho nhận hàng';
                this.isModalOpen = true;
                this.modalSelectedProvince = ''; this.modalSelectedDistrict = ''; this.modalSearchTerm = '';
            },

            selectModalItem(item) {
                if (this.modalType === 'supplier') this.selectedSupplier = { name: item.name, address: item.fullAddress, addressId: item.id };
                else if (this.modalType === 'location') this.selectedLocation = { name: item.name, address: item.fullAddress, id: item.id };
                this.isModalOpen = false;
            },

            submitForm() {
                if(this.isLocked) return alert('Phiếu nhập đã ở trạng thái không thể chỉnh sửa.');
                if (!this.selectedSupplier.addressId || !this.selectedLocation.id) return alert('Vui lòng chọn đầy đủ Nhà cung cấp và Kho nhận hàng.');
                document.getElementById('purchase-order-form').submit();
            },
            
            openBarcodeScanner() { 
                if(this.isLocked) return; 
                this.isScannerOpen = true; 
                this.$nextTick(() => this.startScanning()); 
            },
            closeBarcodeScanner() { 
                this.stopScanning();
                this.isScannerOpen = false; 
            },
            
            startScanning() {
                const readerElement = document.getElementById('reader');
                const loadingMessage = document.getElementById('loading-message');
                const errorMessage = document.getElementById('scan-error-message');

                if (typeof Html5Qrcode === "undefined") {
                    errorMessage.textContent = "Lỗi: Thư viện quét mã vạch chưa được tải.";
                    errorMessage.classList.remove('hidden');
                    return;
                }
                if (!readerElement) return;

                loadingMessage.classList.remove('hidden');
                errorMessage.classList.add('hidden');
                document.getElementById('scan-result-container').classList.add('hidden');

                this.html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 150 } };

                const onScanSuccess = (decodedText, decodedResult) => {
                    if (!this.isScannerOpen) return;
                    
                    this.playBeep();
                    readerElement.classList.add('flash-success');
                    try { if (navigator.vibrate) navigator.vibrate(100); } catch (e) {}
                    
                    window.dispatchEvent(new CustomEvent('barcode-scanned', { detail: { code: decodedText } }));
                    
                    this.stopScanning();
                    setTimeout(() => { this.isScannerOpen = false; }, 300); 
                };

                this.html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, (error) => {})
                .then(() => {
                    loadingMessage.classList.add('hidden');
                })
                .catch(err => {
                    loadingMessage.classList.add('hidden');
                    let friendlyError = `Lỗi camera: ${err}.`;
                    if (String(err).includes("Permission denied")) {
                        friendlyError = "Bạn đã từ chối quyền truy cập camera. Vui lòng cấp quyền trong cài đặt trình duyệt.";
                    } else if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                         friendlyError += " Việc truy cập camera yêu cầu kết nối an toàn (HTTPS).";
                    }
                    errorMessage.textContent = friendlyError;
                    errorMessage.classList.remove('hidden');
                });
            },

            stopScanning() {
                if (this.html5QrCode && this.html5QrCode.isScanning) {
                    this.html5QrCode.stop().catch(err => { console.error("Lỗi khi dừng camera:", err); });
                }
            },

            initializeProductSearch() {
                const searchInput = document.getElementById('product-search');
                const suggestionsContainer = document.getElementById('search-suggestions');
                const purchaseItemsTableBody = document.getElementById('purchase-items-table');
                let allProductVariants = [];

                const fetchProducts = async (term) => {
                    try {
                        const response = await fetch(`{{ route('admin.purchase-orders.search-products') }}?search=${term}`);
                        const data = await response.json();
                        allProductVariants = data.allVariants;
                        displaySuggestions(data.groupedProducts);
                    } catch (error) {
                        console.error("Error fetching products:", error);
                        suggestionsContainer.innerHTML = `<div class="p-3 text-center text-red-500">Có lỗi xảy ra.</div>`;
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
                                        </div>`).join('')}
                                </div>
                            </div>`).join('');
                    }
                    suggestionsContainer.classList.remove('hidden');
                };
                
                this.addProductToTable = (product, itemData = null) => {
                    if (!product || !product.id) return;
                    if (purchaseItemsTableBody.querySelector(`tr[data-variant-id="${product.id}"]`)) return;

                    document.getElementById('no-items-row').style.display = 'none';
                    const newRow = document.createElement('tr');
                    newRow.className = 'bg-white border-b purchase-item-row';
                    newRow.dataset.variantId = product.id;
                    
                    const quantity = itemData ? itemData.quantity : 1;
                    const costPrice = itemData ? itemData.cost_price : (product.cost_price || 0);

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
                        <td class="px-4 py-3"><input type="number" name="items[${product.id}][quantity]" class="w-full p-2 border border-gray-300 rounded-md text-sm text-center quantity-input" value="${quantity}" min="1"></td>
                        <td class="px-4 py-3"><input type="number" name="items[${product.id}][cost_price]" class="w-full p-2 border border-gray-300 rounded-md text-sm text-right cost-price-input" value="${costPrice}" min="0"></td>
                        <td class="px-4 py-3 text-right font-medium subtotal">${this.formatCurrency(quantity * costPrice)}</td>
                        <td class="px-4 py-3 text-center"><button type="button" class="text-red-500 hover:text-red-700 remove-item-btn font-bold text-xl">&times;</button></td>
                    `;
                    purchaseItemsTableBody.appendChild(newRow);
                    this.updateGrandTotal();
                };

                this.updateGrandTotal = () => {
                    let total = 0;
                    purchaseItemsTableBody.querySelectorAll('.purchase-item-row').forEach(row => {
                        const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
                        const costPrice = parseFloat(row.querySelector('.cost-price-input').value) || 0;
                        const subtotal = quantity * costPrice;
                        row.querySelector('.subtotal').textContent = this.formatCurrency(subtotal);
                        total += subtotal;
                    });
                    document.getElementById('grand-total').textContent = this.formatCurrency(total);
                };

                this.formatCurrency = (number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(number);

                let debounceTimer;
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => { fetchProducts(searchInput.value); }, 300);
                });
                searchInput.addEventListener('focus', () => { if(!searchInput.value) fetchProducts(''); });

                document.addEventListener('click', (e) => {
                    if (!suggestionsContainer.contains(e.target) && e.target !== searchInput) {
                        suggestionsContainer.classList.add('hidden');
                    }
                });

                suggestionsContainer.addEventListener('click', (e) => {
                    const item = e.target.closest('.suggestion-item');
                    if (item) {
                        const productId = parseInt(item.dataset.productId);
                        const productToAdd = allProductVariants.find(p => p.id === productId);
                        this.addProductToTable(productToAdd);
                        searchInput.value = '';
                        suggestionsContainer.classList.add('hidden');
                    }
                });

                purchaseItemsTableBody.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-item-btn')) {
                        e.target.closest('tr').remove();
                        if (purchaseItemsTableBody.querySelectorAll('.purchase-item-row').length === 0) {
                            document.getElementById('no-items-row').style.display = 'table-row';
                        }
                        this.updateGrandTotal();
                    }
                });

                purchaseItemsTableBody.addEventListener('input', (e) => {
                    if(e.target.classList.contains('quantity-input') || e.target.classList.contains('cost-price-input')) {
                        this.updateGrandTotal();
                    }
                });
            }
        }
    }
</script>
@endp