@extends('admin.layouts.app')

@section('title', 'Tạo Phiếu Chuyển Kho')

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
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        .btn-primary:disabled {
            background-color: #a5b4fc;
            cursor: not-allowed;
        }
        @keyframes flash-border {
            0%, 100% { border-color: #10b981; }
            50% { border-color: #34d399; box-shadow: 0 0 10px #34d399; }
        }
        .flash-success { animation: flash-border 0.7s ease-in-out; }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8" x-data="stockTransferPageData({
    locationsData: {{ Js::from($locations) }},
    provincesData: {{ Js::from($provinces ?? []) }},
    districtsData: {{ Js::from($districts ?? []) }}
})">

    <div class="container mx-auto max-w-full">
        <form id="stock-transfer-form" @submit.prevent="submitForm" method="POST" action="{{ route('admin.stock-transfers.store') }}">
            @csrf
            <!-- Header -->
            <header class="mb-8 flex flex-col sm:flex-row items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Tạo Phiếu Chuyển Kho</h1>
                    <p class="text-gray-600 mt-1">Tạo phiếu để chuyển sản phẩm giữa các kho.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-2">
                    <a href="{{ route('admin.stock-transfers.index') }}" class="w-full sm:w-auto flex items-center justify-center bg-white text-gray-700 font-bold py-2 px-4 rounded-lg shadow-md hover:bg-gray-100 border border-gray-300 transition-colors">
                        Hủy Bỏ
                    </a>
                    <button type="submit" class="w-full sm:w-auto flex items-center justify-center bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition-colors"
                            :disabled="items.length === 0 || !fromLocationId || !toLocationId">
                        <i class="fas fa-save mr-2"></i>
                        Tạo Phiếu Chuyển
                    </button>
                </div>
            </header>

            <!-- Main Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Product Details -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="card">
                        <div class="p-6">
                            <label for="product-search" class="block text-lg font-semibold text-gray-800 mb-2">Tìm & Thêm Sản Phẩm</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="product-search" placeholder="Gõ tên sản phẩm hoặc SKU..."
                                       class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                       x-model="searchTerm"
                                       @input.debounce.300ms="searchProducts()"
                                       @focus="showSearchResults = true"
                                       :disabled="!fromLocationId">
                                <div @click="openBarcodeScanner()" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" title="Quét mã vạch">
                                    <i class="fas fa-barcode text-gray-400 hover:text-gray-600 transition-colors"></i>
                                </div>
                                <div id="search-suggestions" x-show="showSearchResults" @click.away="showSearchResults = false"
                                     class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-2 shadow-lg max-h-80 overflow-y-auto custom-scrollbar">
                                    <template x-if="isLoading">
                                        <div class="p-4 text-center text-gray-500">Đang tìm kiếm...</div>
                                    </template>
                                    <template x-if="!isLoading && searchResults.length === 0 && searchTerm.length > 1">
                                        <div class="p-4 text-center text-gray-500">Không tìm thấy sản phẩm.</div>
                                    </template>
                                    <template x-for="product in searchResults" :key="product.id">
                                        <div @click="addProduct(product)" class="flex items-center p-3 hover:bg-gray-100 cursor-pointer">
                                            <img :src="product.image_url" alt="" class="w-12 h-12 object-cover rounded mr-4">
                                            <div class="flex-grow">
                                                <p class="font-semibold text-gray-800" x-text="product.name"></p>
                                                <p class="text-sm text-gray-500">SKU: <span x-text="product.sku"></span> - Tồn kho: <span class="font-bold" x-text="product.stock"></span></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <p x-show="!fromLocationId" class="text-sm text-yellow-600 mt-2" x-cloak>Vui lòng chọn 'Kho Gửi' trước khi tìm kiếm sản phẩm.</p>
                        </div>
                    </div>

                    <!-- Product List -->
                    <div class="card">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 w-1/2">Sản Phẩm</th>
                                        <th scope="col" class="px-4 py-3 text-center">Tồn Kho Gửi</th>
                                        <th scope="col" class="px-4 py-3 text-center w-1/4">Số Lượng Chuyển</th>
                                        <th scope="col" class="px-4 py-3 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="transfer-items-table">
                                    <template x-if="items.length > 0">
                                        <template x-for="(item, index) in items" :key="item.product_variant_id">
                                            <tr class="bg-white border-b transition-colors">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center">
                                                        <img :src="item.image_url" :alt="item.name" class="w-10 h-10 object-cover rounded-md mr-3">
                                                        <div>
                                                            <div class="font-medium text-gray-800" x-text="item.name"></div>
                                                            <div class="text-xs text-gray-500" x-text="`SKU: ${item.sku}`"></div>
                                                            <input type="hidden" :name="`items[${index}][product_variant_id]`" :value="item.product_variant_id">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-blue-600" x-text="item.stock"></td>
                                                <td class="px-4 py-3">
                                                    <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" 
                                                           class="w-24 p-2 border border-gray-300 rounded-md text-sm text-center mx-auto" 
                                                           min="1" :max="item.stock" @input="validateQuantity(item)">
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 font-bold text-xl" title="Xóa sản phẩm">&times;</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                    <template x-if="items.length === 0">
                                        <tr id="no-items-row">
                                            <td colspan="4" class="text-center py-10 text-gray-500">
                                                <i class="fas fa-box-open fa-3x mb-3"></i>
                                                <p>Chưa có sản phẩm nào được thêm.</p>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: General Info & Summary -->
                <div class="lg:col-span-1 space-y-8">
                     <div class="card sticky top-8">
                        <div class="p-6 border-b">
                            <h2 class="text-lg font-semibold text-gray-800">Thông Tin Phiếu</h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">Kho Gửi <span class="text-red-500">*</span></label>
                                <input type="hidden" name="from_location_id" x-model="fromLocationId">
                                <div @click="openModal('from_location')" class="w-full p-2.5 bg-white border border-gray-300 rounded-lg cursor-pointer min-h-[42px] flex items-center">
                                    <span x-show="!selectedFromLocation.name" class="text-gray-500">Chọn kho gửi...</span>
                                    <div x-show="selectedFromLocation.name" class="text-sm" x-cloak>
                                        <p class="font-semibold text-gray-800" x-text="selectedFromLocation.name"></p>
                                        <p class="text-gray-600" x-text="selectedFromLocation.address"></p>
                                    </div>
                                </div>
                                @error('from_location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">Kho Nhận <span class="text-red-500">*</span></label>
                                <input type="hidden" name="to_location_id" x-model="toLocationId">
                                <div @click="openModal('to_location')" class="w-full p-2.5 bg-white border border-gray-300 rounded-lg cursor-pointer min-h-[42px] flex items-center" :class="{'bg-gray-100 cursor-not-allowed': !fromLocationId}">
                                    <span x-show="!selectedToLocation.name" class="text-gray-500">Chọn kho nhận...</span>
                                    <div x-show="selectedToLocation.name" class="text-sm" x-cloak>
                                        <p class="font-semibold text-gray-800" x-text="selectedToLocation.name"></p>
                                        <p class="text-gray-600" x-text="selectedToLocation.address"></p>
                                    </div>
                                </div>
                                @error('to_location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                             <div>
                                <label for="notes" class="block mb-1 text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea id="notes" name="notes" rows="4" class="w-full p-2.5 border border-gray-300 rounded-lg" placeholder="Thêm ghi chú cho phiếu chuyển..."></textarea>
                                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="p-6 border-t bg-gray-50 rounded-b-lg">
                             <h3 class="text-md font-semibold text-gray-800 mb-4">Tóm Tắt</h3>
                             <div class="space-y-2">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Tổng số loại sản phẩm:</span>
                                    <span class="font-bold text-gray-800" x-text="totalProducts"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Tổng số lượng chuyển:</span>
                                    <span class="font-bold text-gray-800" x-text="totalQuantity"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Location Selection Modal -->
        <div x-show="isModalOpen" x-cloak x-transition class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div @click.outside="isModalOpen = false" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <header class="p-4 border-b flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800" x-text="modalTitle"></h3>
                    <button @click="isModalOpen = false" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
                </header>
                <div class="p-5 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Tỉnh/Thành</label>
                            <select x-model="modalSelectedProvince" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả tỉnh thành</option>
                                <template x-for="province in provinces" :key="province.code">
                                    <option :value="province.code" x-text="province.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Quận/Huyện</label>
                            <select x-model="modalSelectedDistrict" :disabled="!modalSelectedProvince" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-200">
                                <option value="">Tất cả quận huyện</option>
                                <template x-for="district in filteredDistricts" :key="district.code">
                                    <option :value="district.code" x-text="district.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Tìm kiếm</label>
                            <input type="text" x-model="modalSearchTerm" placeholder="Tên kho, địa chỉ..." class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                <main class="flex-1 p-5 overflow-y-auto custom-scrollbar">
                    <div class="space-y-3">
                        <template x-if="filteredModalItems.length === 0">
                            <div class="text-center py-10 text-gray-500">Không có kết quả nào phù hợp.</div>
                        </template>
                        <template x-for="item in filteredModalItems" :key="item.id">
                             <div @click="selectModalItem(item)" class="p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-400 cursor-pointer transition-colors">
                                 <p class="font-bold text-gray-800" x-text="item.name"></p>
                                 <p class="text-sm text-gray-600" x-text="item.fullAddress"></p>
                             </div>
                        </template>
                    </div>
                </main>
            </div>
        </div>

        <!-- Barcode Scanner Modal -->
        <div x-show="isScannerOpen" x-cloak x-transition class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4">
            <div @click.outside="closeBarcodeScanner()" class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col">
                <header class="p-4 bg-blue-600 text-white rounded-t-xl">
                    <h3 class="text-xl font-bold text-center">Quét Barcode / QR Code</h3>
                </header>
                <main class="p-4 flex-1">
                    <div id="reader-container" class="relative">
                        <div id="reader" class="w-full border-4 border-gray-300 rounded-lg overflow-hidden"></div>
                        <div id="loading-message" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-70 text-white hidden">
                            <i class="fas fa-spinner fa-spin text-4xl mb-3"></i>
                            <p>Đang khởi tạo camera...</p>
                        </div>
                    </div>
                    <div id="scan-error-message" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg hidden"></div>
                </main>
                <footer class="p-4 bg-gray-50 border-t rounded-b-xl flex justify-end">
                    <button @click="closeBarcodeScanner()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                        Đóng
                    </button>
                </footer>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Thư viện quét mã vạch --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
{{-- Thư viện âm thanh Howler.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
<script>
    function stockTransferPageData(initialData) {
        return {
            // State
            fromLocationId: '',
            toLocationId: '',
            searchTerm: '',
            searchResults: [],
            showSearchResults: false,
            isLoading: false,
            items: [],
            isScannerOpen: false, 
            html5QrCode: null,
            beepSound: null, 
            soundInitialized: false,

            // Modal State
            isModalOpen: false,
            modalType: '', // 'from_location' or 'to_location'
            modalTitle: '',
            modalSelectedProvince: '',
            modalSelectedDistrict: '',
            modalSearchTerm: '',
            
            // Initial Data
            allLocations: initialData.locationsData,
            provinces: initialData.provincesData,
            districts: initialData.districtsData,

            init() {
                this.$watch('modalSelectedProvince', () => { this.modalSelectedDistrict = ''; });
                window.addEventListener('barcode-scanned', (event) => {
                    const scannedCode = event.detail.code;
                    this.searchTerm = scannedCode;
                    this.searchProducts();
                });
                this.initializeSound();
            },
            
            // Computed Properties
            get availableToLocations() {
                if (!this.fromLocationId) return [];
                return this.allLocations.filter(loc => loc.id != this.fromLocationId);
            },
            get totalProducts() {
                return this.items.length;
            },
            get totalQuantity() {
                return this.items.reduce((total, item) => total + (parseInt(item.quantity) || 0), 0);
            },
            get selectedFromLocation() {
                return this.allLocations.find(l => l.id == this.fromLocationId) || { name: null, address: null };
            },
            get selectedToLocation() {
                return this.allLocations.find(l => l.id == this.toLocationId) || { name: null, address: null };
            },
            get filteredDistricts() {
                if (!this.modalSelectedProvince) return [];
                return this.districts.filter(d => d.parent_code == this.modalSelectedProvince);
            },
            get filteredModalItems() {
                let items = this.allLocations;
                if (this.modalType === 'to_location') {
                    items = items.filter(i => i.id != this.fromLocationId);
                }
                if (this.modalSelectedProvince) items = items.filter(i => i.province_id == this.modalSelectedProvince);
                if (this.modalSelectedDistrict) items = items.filter(i => i.district_id == this.modalSelectedDistrict);
                if (this.modalSearchTerm.trim()) {
                    const search = this.modalSearchTerm.toLowerCase();
                    items = items.filter(i => 
                        i.name.toLowerCase().includes(search) || 
                        (i.fullAddress && i.fullAddress.toLowerCase().includes(search))
                    );
                }
                return items;
            },

            // Methods
            onFromLocationChange() {
                this.toLocationId = '';
                if (this.items.length > 0) {
                    if (confirm('Thay đổi kho gửi sẽ xóa các sản phẩm đã chọn. Bạn có muốn tiếp tục?')) {
                        this.items = [];
                        this.searchTerm = '';
                        this.searchResults = [];
                    } else {
                        this.fromLocationId = this._x_prev_fromLocationId || '';
                    }
                }
                this._x_prev_fromLocationId = this.fromLocationId;
            },

            searchProducts() {
                if (this.searchTerm.length < 2 || !this.fromLocationId) {
                    this.searchResults = [];
                    this.showSearchResults = this.searchTerm.length > 0;
                    return;
                }
                this.isLoading = true;
                this.showSearchResults = true;
                
                fetch(`{{ route('admin.stock-transfers.search-products') }}?search=${this.searchTerm}&location_id=${this.fromLocationId}`)
                    .then(response => response.json())
                    .then(data => {
                        this.searchResults = data.allVariants.filter(p => !this.items.some(item => item.product_variant_id === p.id));
                    })
                    .catch(error => console.error('Error fetching products:', error))
                    .finally(() => this.isLoading = false);
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

            validateQuantity(item) {
                let qty = parseInt(item.quantity);
                if (isNaN(qty) || qty < 1) {
                    item.quantity = 1;
                } else if (qty > item.stock) {
                    item.quantity = item.stock;
                } else {
                    item.quantity = qty;
                }
            },

            submitForm() {
                if (!this.fromLocationId || !this.toLocationId || this.items.length === 0) {
                    alert('Vui lòng điền đầy đủ thông tin: Kho gửi, Kho nhận và ít nhất một sản phẩm.');
                    return;
                }
                document.getElementById('stock-transfer-form').submit();
            },

            // --- MODAL METHODS ---
            openModal(type) {
                if (type === 'to_location' && !this.fromLocationId) {
                    alert('Vui lòng chọn kho gửi trước.');
                    return;
                }
                this.modalType = type;
                this.modalTitle = type === 'from_location' ? 'Chọn Kho Gửi' : 'Chọn Kho Nhận';
                this.isModalOpen = true;
                this.modalSelectedProvince = '';
                this.modalSelectedDistrict = '';
                this.modalSearchTerm = '';
            },
            selectModalItem(item) {
                if (this.modalType === 'from_location') {
                    const oldFromId = this.fromLocationId;
                    this.fromLocationId = item.id;
                    if (oldFromId !== item.id) {
                        this.onFromLocationChange();
                    }
                } else if (this.modalType === 'to_location') {
                    this.toLocationId = item.id;
                }
                this.isModalOpen = false;
            },

            // --- BARCODE SCANNER & SOUND METHODS ---
            initializeSound() {
                if (this.soundInitialized || typeof Howl === 'undefined') return;
                this.beepSound = new Howl({
                    src: ['{{ asset('sounds/shop-scanner-beeps.mp3') }}'],
                    volume: 0.8,
                    onload: () => { this.soundInitialized = true; },
                    onloaderror: (id, err) => { console.error('Sound load error:', err); }
                });
            },
            playBeep() {
                if (this.soundInitialized && this.beepSound) this.beepSound.play();
            },
            openBarcodeScanner() {
                if (!this.fromLocationId) {
                    alert('Vui lòng chọn kho gửi trước khi quét barcode.');
                    return;
                }
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

                if (typeof Html5Qrcode === "undefined" || !readerElement) return;

                loadingMessage.classList.remove('hidden');
                errorMessage.classList.add('hidden');

                this.html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 150 } };

                const onScanSuccess = (decodedText, decodedResult) => {
                    if (!this.isScannerOpen) return;
                    this.playBeep();
                    try { if (navigator.vibrate) navigator.vibrate(100); } catch (e) {}
                    window.dispatchEvent(new CustomEvent('barcode-scanned', { detail: { code: decodedText } }));
                    this.closeBarcodeScanner();
                };

                this.html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, (error) => {})
                .then(() => loadingMessage.classList.add('hidden'))
                .catch(err => {
                    loadingMessage.classList.add('hidden');
                    errorMessage.textContent = `Lỗi camera: ${err}. Vui lòng cấp quyền hoặc sử dụng HTTPS.`;
                    errorMessage.classList.remove('hidden');
                });
            },
            stopScanning() {
                if (this.html5QrCode && this.html5QrCode.isScanning) {
                    this.html5QrCode.stop().catch(err => console.error("Error stopping camera:", err));
                }
            }
        }
    }
</script>
@endpush
