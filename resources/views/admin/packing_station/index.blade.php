@extends('admin.layouts.app')

@section('title', 'Trạm Đóng Gói')

@push('styles')
    {{-- CSS Styles for Packing Station --}}
    <style>
        .card { background-color: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
        [x-cloak] { display: none !important; }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

        .order-list-item.active { background-color: #eef2ff; border-left-color: #4f46e5; }
        .imei-input:focus {
            border-color: #4f46e5;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(79,70,229,.25);
        }

        /* Thêm style cho modal quét mã vạch */
        @keyframes flash-border {
            0%, 100% { border-color: #10b981; }
            50% { border-color: #34d399; box-shadow: 0 0 10px #34d399; }
        }
        .flash-success { animation: flash-border 0.7s ease-in-out; }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8" x-data="packingStation()" x-init="init()">
    <div class="container mx-auto max-w-full">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Trạm Đóng Gói</h1>
            <p class="text-gray-600 mt-1">Quét mã vận đơn để đóng gói gói hàng.</p>
        </header>

        <div class="card h-[calc(100vh-220px)] overflow-hidden">
            <div class="flex h-full">
                <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
                    <div class="p-4 border-b">
                        <h2 class="text-xl font-bold text-gray-800">Kho: <span class="text-indigo-600">{{ $storeLocation->name ?? 'N/A' }}</span></h2>
                        <p class="text-sm text-gray-500">Số gói hàng chờ xử lý: <span x-text="getTotalPackages()"></span></p>
                    </div>
                    <div class="p-4 border-b">
                        <div class="relative">
                            <div @click="openTrackingCodeScanner()" class="absolute inset-y-0 left-0 pl-3 flex items-center cursor-pointer" title="Quét mã vận đơn">
                                <i class="fas fa-barcode text-gray-400 hover:text-indigo-600 transition-colors"></i>
                            </div>
                            <input type="text" 
                                   x-model="trackingCodeInput" 
                                   @keyup.enter="searchByTrackingCode()"
                                   @input="handleTrackingCodeInput()"
                                   class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Quét hoặc nhập mã vận đơn...">
                        </div>
                        <button @click="searchByTrackingCode()" 
                                :disabled="!trackingCodeInput.trim()"
                                class="w-full mt-3 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="fas fa-search mr-2"></i>
                            Tìm kiếm
                        </button>
                    </div>
                    
                    <!-- Danh sách đơn hàng chờ đóng gói -->
                    <div class="p-4 border-b">
                        <h3 class="font-bold text-gray-800 mb-2">Danh sách gói hàng chờ xử lý</h3>
                    </div>
                    
                    <div class="flex-grow overflow-y-auto custom-scrollbar">
                        <template x-if="isLoadingOrders">
                            <div class="p-4 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Đang tải danh sách gói hàng...
                            </div>
                        </template>
                        
                        <template x-if="!isLoadingOrders && pendingOrders.length === 0">
                            <div class="p-4 text-center text-gray-500">
                                <i class="fas fa-box-open fa-2x mb-2"></i>
                                <p>Không có gói hàng nào chờ xử lý</p>
                            </div>
                        </template>
                        
                        <template x-if="!isLoadingOrders && pendingOrders.length > 0">
                            <div class="divide-y divide-gray-200">
                                <template x-for="order in pendingOrders" :key="order.id">
                                    <div class="flex justify-between items-center p-3 border-b hover:bg-gray-50 cursor-pointer" 
                                         @click="selectOrderFromList(order.first_tracking_code)"
                                         :class="{ 'bg-blue-50 border-blue-200': fulfillmentInfo && fulfillmentInfo.tracking_code === order.first_tracking_code }">
                                        <div class="flex-1">
                                            <div class="flex justify-between items-start mb-1">
                                                <span class="font-medium text-gray-900" x-text="order.order_code"></span>
                                                <span class="text-xs text-gray-500" x-text="order.created_at"></span>
                                            </div>
                                            <div class="text-sm text-gray-600 mb-1" x-text="order.customer_name"></div>
                                            <div class="text-xs text-gray-500" x-text="order.shipping_address"></div>
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded" x-text="order.first_tracking_code"></span>
                                                <span class="text-xs text-gray-500" x-text="'Gói: ' + order.total_packages"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        <!-- Kết quả tìm kiếm -->
                        <template x-if="isSearching">
                             <div class="p-4 text-center text-gray-500">Đang tìm kiếm...</div>
                        </template>
                        <template x-if="!isSearching && searchError">
                             <div class="p-4 text-center text-red-500" x-text="searchError"></div>
                        </template>
                        <template x-if="!isSearching && !searchError && fulfillmentInfo">
                            <div class="p-4">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <h3 class="font-bold text-green-800">Gói hàng tìm thấy</h3>
                                    <p class="text-sm text-green-600" x-text="`Mã vận đơn: ${fulfillmentInfo.tracking_code}`"></p>
                                    <p class="text-sm text-green-600" x-text="`Đơn hàng: ${fulfillmentInfo.order_code}`"></p>
                                    <p class="text-sm text-green-600" x-text="`Khách hàng: ${fulfillmentInfo.customer_name}`"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="w-2/3 flex flex-col bg-gray-50">
                    <main class="flex-grow p-6 overflow-y-auto custom-scrollbar">
                        <template x-if="!fulfillmentInfo">
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center text-gray-500">
                                    <i class="fas fa-box-open fa-4x mb-4"></i>
                                    <p class="text-xl">Vui lòng quét mã vận đơn để tìm đơn hàng</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="fulfillmentInfo">
                            <div x-cloak>
                                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2" x-text="`Chi tiết gói hàng: ${fulfillmentInfo.tracking_code}`"></h3>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-500">Mã đơn hàng:</p>
                                            <p class="font-semibold text-gray-800" x-text="fulfillmentInfo.order_code"></p>
                                        </div>
                        <div>
                            <p class="text-gray-500">Khách hàng:</p>
                            <p class="font-semibold text-gray-800" x-text="fulfillmentInfo.customer_name"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Số điện thoại:</p>
                            <p class="font-semibold text-gray-800" x-text="fulfillmentInfo.customer_phone"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Kho xử lý:</p>
                            <p class="font-semibold text-gray-800" x-text="fulfillmentInfo.store_location_name"></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-500">Địa chỉ giao hàng:</p>
                            <p class="font-semibold text-gray-800" x-text="fulfillmentInfo.shipping_address_full || 'N/A'"></p>
                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-6 rounded-lg shadow-md">
                                    <h4 class="text-lg font-bold text-gray-800 mb-4">Sản phẩm cần đóng gói và quét mã</h4>
                                    <div class="space-y-4">
                                        <template x-for="item in fulfillmentInfo.items" :key="item.id">
                                            <div class="flex items-start p-4 border rounded-lg space-x-4" :class="{ 'bg-green-50 border-green-200': item.imei_scanned }">
                                                <!-- Ảnh sản phẩm -->
                                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                                    <img :src="item.product_image || '/images/no-image.png'" 
                                                         :alt="item.product_name" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                
                                                <!-- Thông tin sản phẩm -->
                                                <div class="flex-grow">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <div>
                                                            <p class="font-bold text-gray-900 text-sm" x-text="item.product_name"></p>
                                                            <p class="text-xs text-gray-500 mt-1" x-text="item.variant_name || 'Phiên bản mặc định'"></p>
                                                        </div>
                                                        <template x-if="item.requires_imei">
                                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                                Cần quét mã
                                                            </span>
                                                        </template>
                                                    </div>
                                                    
                                                    <!-- Thông tin chi tiết -->
                                                    <div class="grid grid-cols-3 gap-4 text-xs text-gray-600 mb-3">
                                                        <div>
                                                            <span class="font-medium">SKU:</span>
                                                            <span class="ml-1" x-text="item.sku || item.product_variant?.sku || 'N/A'"></span>
                                                        </div>
                                                        <div>
                                                            <span class="font-medium">Đơn giá:</span>
                                                            <span class="ml-1 text-blue-600 font-medium" x-text="formatPrice(item.price)"></span>
                                                        </div>
                                                        <div>
                                                            <span class="font-medium">Số lượng:</span>
                                                            <span class="ml-1 font-medium" x-text="item.quantity"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="w-1/2 ml-4">
                                                    <template x-if="item.requires_imei">
                                                        <div>
                                                            <label :for="'imei-' + item.id" class="text-sm font-medium text-gray-700">Quét IMEI/Serial*</label>
                                                            <div class="mt-1 relative">
                                                                {{-- ICON BARCODE CÓ THỂ CLICK --}}
                                                                <div @click="openBarcodeScanner(item)" class="absolute inset-y-0 left-0 pl-3 flex items-center cursor-pointer" title="Quét mã vạch">
                                                                    <i class="fas fa-barcode text-gray-400 hover:text-indigo-600 transition-colors"></i>
                                                                </div>
                                                                <div class="flex">
                                                                    <input :id="'imei-' + item.id" type="text"
                                                                        x-model="item.imei_input"
                                                                        @keyup.enter="validateImei(item)"
                                                                        @input="item.imei_error = null; item.imei_scanned = false;"
                                                                        @blur="if(item.imei_input && item.imei_input.trim()) validateImei(item)"
                                                                        class="imei-input block w-full pl-10 pr-4 py-2 border rounded-md shadow-sm transition-colors"
                                                                        :class="item.imei_error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : (item.imei_scanned ? 'border-green-300 focus:border-green-500 focus:ring-green-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500')"
                                                                        :placeholder="item.imei_scanned ? 'Đã quét thành công' : 'Vui lòng quét hoặc nhập mã...'"
                                                                        :disabled="item.imei_scanned">
                                                                </div>
                                                                <!-- Thông báo lỗi IMEI -->
                                                                <div x-show="item.imei_error" x-transition class="mt-2">
                                                                    <p class="text-sm text-red-600 flex items-center">
                                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                        <span x-text="item.imei_error"></span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!item.requires_imei">
                                                        <div class="flex items-center justify-center h-full bg-gray-100 rounded-md">
                                                            <p class="text-sm text-gray-500">Không yêu cầu IMEI</p>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="w-10 text-center ml-4">
                                                    <i x-show="!item.requires_imei"
                                                        class="fas fa-minus-circle text-2xl text-gray-300"></i>
                                                    <i x-show="item.imei_scanned && item.requires_imei"
                                                        class="far fa-check-circle text-2xl text-indigo-500"></i>
                                                    <i x-show="!item.imei_scanned && item.requires_imei"
                                                        @click="validateImei(item)"
                                                        class="far fa-circle text-2xl text-gray-400 cursor-pointer hover:text-indigo-500"
                                                        title="Nhấn để xác nhận mã đã nhập"></i>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button @click="confirmPackaging()"
                                            :disabled="!isFulfillmentReady()"
                            class="px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <i class="fas fa-box mr-2"></i>
                        <span x-text="isFulfillmentReady() ? 'Xác nhận đóng gói' : 'Cần hoàn tất quét mã'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <div x-show="isScannerOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4">

        <div @click.outside="closeBarcodeScanner()" class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col">
            <header class="p-4 bg-indigo-600 text-white rounded-t-xl flex justify-between items-center">
                <h3 class="text-xl font-bold">Quét Barcode / QR Code</h3>
                <button @click="closeBarcodeScanner()" class="text-indigo-200 hover:text-white">&times;</button>
            </header>

            <main class="p-4 flex-1">
                <div id="reader-container" class="relative">
                    <div id="reader" class="w-full border-4 border-gray-300 rounded-lg overflow-hidden transition-all duration-300"></div>
                    <div id="loading-message" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-70 text-white hidden">
                        <svg class="animate-spin h-10 w-10 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Đang khởi tạo camera...</p>
                    </div>
                </div>
                <div id="scan-error-message" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg hidden"></div>
            </main>

            <footer class="p-4 bg-gray-50 border-t rounded-b-xl flex justify-end">
                <button @click="closeBarcodeScanner()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors">
                    Hủy
                </button>
            </footer>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Thư viện quét mã vạch và âm thanh --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>

<script>
    function packingStation() {
        return {
            // State cho tìm kiếm gói hàng
            trackingCodeInput: '',
            fulfillmentInfo: null,
            isSearching: false,
            searchError: null,
            // State cho danh sách đơn hàng
            pendingOrders: [],
            isLoadingOrders: false,
            // State cho modal scanner
            isScannerOpen: false,
            html5QrCode: null,
            currentItemForScan: null,
            isTrackingCodeScanner: false,
            beepSound: null,
            soundInitialized: false,

            init() {
                  this.initializeSound();
                  this.loadPendingOrders();
                  
                  // Tự động tải lại danh sách gói hàng mỗi 30 giây
                  setInterval(() => {
                      this.loadPendingOrders();
                  }, 30000);
            },

            // Hàm tính tổng số gói hàng
            getTotalPackages() {
                return this.pendingOrders.reduce((total, order) => {
                    return total + (order.total_packages || 0);
                }, 0);
            },
              
            async loadPendingOrders() {
                this.isLoadingOrders = true;
                try {
                    const response = await fetch('{{ route('admin.packing-station.pending-orders') }}');
                    const data = await response.json();
                    
                    if (response.ok) {
                        this.pendingOrders = data;
                        
                        // Tự động chọn đơn hàng đầu tiên nếu có và chưa có fulfillment nào được chọn
                        if (this.pendingOrders.length > 0 && !this.fulfillmentInfo) {
                            await this.selectOrderFromList(this.pendingOrders[0].first_tracking_code);
                        }
                    } else {
                        console.error('Lỗi khi tải danh sách gói hàng:', data.message || data.error);
                    }
                } catch (error) {
                    console.error('Lỗi khi tải danh sách gói hàng:', error);
                } finally {
                    this.isLoadingOrders = false;
                }
            },
              
            selectOrderFromList(trackingCode) {
                if (trackingCode) {
                    this.trackingCodeInput = trackingCode;
                    this.searchByTrackingCode();
                }
            },

            // Tìm kiếm gói hàng theo mã vận đơn
            searchByTrackingCode() {
                if (!this.trackingCodeInput.trim()) return;
                
                this.isSearching = true;
                this.searchError = null;
                this.packageInfo = null;
                
                fetch(`{{ url('admin/packing-station/packages') }}/${encodeURIComponent(this.trackingCodeInput.trim())}`)
                    .then(res => res.json().then(data => ({ status: res.status, body: data })))
                    .then(({ status, body }) => {
                        this.isSearching = false;
                        if (status === 200 && body.success) {
                            this.fulfillmentInfo = body.data;
                            this.searchError = null;
                        } else {
                            this.searchError = body.message || 'Không tìm thấy gói hàng';
                            this.fulfillmentInfo = null;
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi khi tìm kiếm gói hàng:', error);
                        this.searchError = 'Đã xảy ra lỗi khi tìm kiếm gói hàng.';
                        this.fulfillmentInfo = null;
                        this.isSearching = false;
                    });
            },

            // Xử lý khi nhập mã vận đơn
            handleTrackingCodeInput() {
                this.fulfillmentInfo = null;
                this.searchError = null;
                
                // Tự động tìm kiếm sau khi nhập đủ ký tự (thường mã vận đơn có độ dài cố định)
                if (this.trackingCodeInput.trim().length >= 10) {
                    this.searchByTrackingCode();
                }
            },
            
            // Xác thực IMEI/Serial qua API
            validateImei(item) {
                // Reset trạng thái trước khi validate
                item.imei_scanned = false;
                item.imei_error = null;

                // Kiểm tra input có rỗng không
                if (!item.imei_input || item.imei_input.trim() === '') {
                    item.imei_error = 'Vui lòng nhập mã IMEI/Serial';
                    return;
                }

                const trimmedInput = item.imei_input.trim();

                // Gửi request validate lên server
                fetch('{{ route("admin.packing-station.validate-imei") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        serial_number: trimmedInput,
                        product_variant_id: item.product_variant_id
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        item.imei_scanned = true;
                        item.imei_error = null;
                        this.playBeep();
                    } else {
                        item.imei_scanned = false;
                        item.imei_error = data.message || 'Mã IMEI/Serial không hợp lệ';
                    }
                })
                .catch(error => {
                    console.error('Lỗi validate IMEI:', error);
                    item.imei_scanned = false;
                    item.imei_error = 'Lỗi kết nối API. Vui lòng thử lại.';
                });
            },

            // Kiểm tra xem gói hàng đã sẵn sàng để đóng gói chưa
            isFulfillmentReady() {
                if (!this.fulfillmentInfo || !this.fulfillmentInfo.items) return false;
                return this.fulfillmentInfo.items.every(item => !item.requires_imei || item.imei_scanned);
            },

            // Gửi yêu cầu xác nhận đóng gói
            confirmPackaging() {
                if (!this.isFulfillmentReady()) return;

                const payload = {
                    tracking_code: this.fulfillmentInfo.tracking_code,
                    items: this.fulfillmentInfo.items
                        .filter(item => item.requires_imei) // Chỉ gửi những item có serial
                        .map(item => ({
                            order_item_id: item.id,
                            product_variant_id: item.product_variant_id,
                            serial_number: item.imei_input
                        })),
                };

                fetch(`{{ url('admin/packing-station/packages') }}/${encodeURIComponent(this.fulfillmentInfo.tracking_code)}/confirm-packaging`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        this.fulfillmentInfo = null;
                        this.trackingCodeInput = '';
                        this.searchError = null;
                    } else {
                        alert(`Xác nhận thất bại: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Lỗi API:', error);
                    alert('Đã xảy ra lỗi khi xác nhận đóng gói.');
                });
            },

            // --- LOGIC CHO SOUND ---
            initializeSound() {
                if (this.soundInitialized || typeof Howl === 'undefined') return;
                this.beepSound = new Howl({
                    src: ['{{ asset('sounds/scanner-beep.mp3') }}'], // Đảm bảo bạn có file này trong public/sounds
                    volume: 0.7
                });
                this.soundInitialized = true;
            },
            playBeep() {
                if (this.soundInitialized && this.beepSound) {
                    this.beepSound.play();
                }
            },

            // --- LOGIC CHO BARCODE SCANNER ---
            openBarcodeScanner(item) {
                this.currentItemForScan = item;
                this.isTrackingCodeScanner = false;
                this.isScannerOpen = true;
                this.$nextTick(() => this.startScanning());
            },
            
            openTrackingCodeScanner() {
                this.currentItemForScan = null;
                this.isTrackingCodeScanner = true;
                this.isScannerOpen = true;
                this.$nextTick(() => this.startScanning());
            },
            closeBarcodeScanner() {
                this.stopScanning();
                this.isScannerOpen = false;
                this.currentItemForScan = null;
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

                this.html5QrCode = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 150 }, supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] };

                const onScanSuccess = (decodedText, decodedResult) => {
                    if (!this.isScannerOpen) return;
                    
                    if(navigator.vibrate) navigator.vibrate(100);

                    if (this.isTrackingCodeScanner) {
                        // Scan mã vận đơn
                        this.trackingCodeInput = decodedText;
                        this.searchByTrackingCode();
                    } else if (this.currentItemForScan) {
                        // Scan IMEI/Serial
                        this.currentItemForScan.imei_input = decodedText;
                        this.validateImei(this.currentItemForScan);
                    }
                    
                    this.closeBarcodeScanner();
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
                    }
                    errorMessage.textContent = friendlyError;
                    errorMessage.classList.remove('hidden');
                });
            },
            stopScanning() {
                if (this.html5QrCode && this.html5QrCode.isScanning) {
                    this.html5QrCode.stop().catch(err => console.error("Lỗi khi dừng camera:", err));
                }
            },

            // Định dạng giá tiền
            formatPrice(price) {
                if (!price) return '0 ₫';
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(price);
            }
        };
    }
</script>
@endpush