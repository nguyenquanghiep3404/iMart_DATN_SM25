@extends('admin.layouts.app')

@section('title', 'Tiếp nhận Hàng hóa')

@push('styles')
{{-- Thêm các thư viện cần thiết --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
    .card { background-color: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); }
    [x-cloak] { display: none !important; }
    .progress-bar-bg { background-color: #e5e7eb; }
    .progress-bar-fill { background-color: #4f46e5; transition: width 0.3s ease-in-out; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    @keyframes flash-border {
        0%, 100% { border-color: #10b981; }
        50% { border-color: #34d399; box-shadow: 0 0 10px #34d399; }
    }
    .flash-success { animation: flash-border 0.7s ease-in-out; }
</style>
@endpush

@section('content')
<div class="container mx-auto p-4 md:p-8" x-data="pageData()">

    <!-- Header -->
    <header class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tiếp nhận Hàng hóa</h1>
            <p class="text-gray-600 mt-1">Chọn Đơn Mua Hàng và quét IMEI/Serial cho sản phẩm.</p>
        </div>
        <div x-show="selectedPO" x-cloak class="mt-4 sm:mt-0 w-full sm:w-auto">
             <button @click="confirmReception()"
                    :disabled="!isPOFullyScanned || isLoading"
                    class="flex w-full sm:w-auto items-center justify-center text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors"
                    :class="{
                        'bg-indigo-600 hover:bg-indigo-700': isPOFullyScanned && !isLoading,
                        'bg-gray-400 cursor-not-allowed': !isPOFullyScanned || isLoading,
                        'opacity-75 cursor-wait': isLoading
                    }">
                <i x-show="isLoading" class="fas fa-spinner fa-spin mr-2"></i>
                <i class="fas fa-check-circle mr-2" x-show="isPOFullyScanned && !isLoading"></i>
                <span x-text="isLoading ? 'Đang xử lý...' : (isPOFullyScanned ? 'Hoàn thành Nhập kho' : 'Cần quét đủ số lượng')"></span>
            </button>
        </div>
    </header>

    <!-- Main Card Content -->
    <div class="card p-6 min-h-[400px] flex items-center justify-center">
        <!-- Loading State -->
        <div x-show="isLoading && purchaseOrders.length === 0" class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
            <p class="mt-3 text-gray-600">Đang tải danh sách đơn hàng...</p>
        </div>

        <!-- PO Selection -->
        <template x-if="!selectedPO && !isLoading">
            <div class="max-w-xl mx-auto text-center">
                <h2 class="text-xl font-semibold text-gray-800">Chọn Đơn Mua Hàng để bắt đầu</h2>
                <p class="text-gray-500 mt-2">Chọn phiếu nhập kho bạn muốn tiếp nhận hàng hóa.</p>
                <div class="mt-6 space-y-3">
                    <template x-for="po in purchaseOrders" :key="po.id">
                        <div @click="selectPO(po.id)" class="p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-400 cursor-pointer transition-colors text-left">
                            <div class="flex justify-between items-center">
                                <p class="font-bold text-blue-600" x-text="po.po_code"></p>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-yellow-200 text-yellow-800">Chờ nhận hàng</span>
                            </div>
                            <p class="text-sm text-gray-700">NCC: <span class="font-medium" x-text="po.supplier_name"></span></p>
                            <p class="text-sm text-gray-500">Ngày đặt: <span x-text="new Date(po.order_date).toLocaleDateString('vi-VN')"></span></p>
                        </div>
                    </template>
                     <template x-if="purchaseOrders.length === 0">
                        <div class="pt-4 text-center">
                            <i class="fas fa-box-open text-4xl text-gray-400"></i>
                            <p class="text-gray-500 mt-3">Không có đơn hàng nào chờ nhận.</p>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Scanning Interface -->
        <template x-if="selectedPO">
            <div x-cloak class="w-full">
                <div class="flex justify-between items-start mb-6 pb-6 border-b">
                    <div>
                        <p class="text-gray-500">Đang tiếp nhận cho đơn hàng:</p>
                        <h2 class="text-2xl font-bold text-gray-800" x-text="selectedPO.po_code"></h2>
                        <p class="text-gray-700">Nhà cung cấp: <span class="font-semibold" x-text="selectedPO.supplier_name"></span></p>
                        <p class="text-gray-700">Kho nhận: <span class="font-semibold" x-text="selectedPO.store_location_name"></span></p>
                    </div>
                    <button @click="resetSelection()" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                       <i class="fas fa-arrow-left mr-1"></i> Quay lại chọn đơn
                    </button>
                </div>

                <div class="space-y-6">
                    <template x-for="item in selectedPO.items" :key="item.id">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center p-3 rounded-md hover:bg-gray-50">
                            <div class="md:col-span-1">
                                <p class="font-semibold text-gray-900" x-text="item.name"></p>
                                <p class="text-xs text-gray-500" x-text="'SKU: ' + item.sku"></p>
                            </div>
                            <div class="md:col-span-1">
                                 <div class="flex justify-between items-center mb-1">
                                     <p class="text-sm font-medium text-gray-700">Tiến độ quét</p>
                                     <p class="text-sm font-bold" :class="scannedData[item.id].length === item.quantity ? 'text-green-600' : 'text-gray-800'">
                                         <span x-text="scannedData[item.id].length"></span> / <span x-text="item.quantity"></span>
                                     </p>
                                 </div>
                                <div class="w-full progress-bar-bg rounded-full h-2.5">
                                    <div class="progress-bar-fill h-2.5 rounded-full" :style="`width: ${(scannedData[item.id].length / item.quantity) * 100}%`"></div>
                                </div>
                            </div>
                            <div class="md:col-span-1 text-right">
                                 <button @click="openScanningModal(item)"
                                        class="w-full md:w-auto px-5 py-2.5 text-sm font-semibold rounded-lg transition-colors"
                                        :class="scannedData[item.id].length === item.quantity ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'">
                                    <i class="fas" :class="scannedData[item.id].length === item.quantity ? 'fa-check-circle' : 'fa-barcode'"></i>
                                    <span x-text="scannedData[item.id].length === item.quantity ? 'Xem lại' : 'Bắt đầu quét'"></span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- ===== MODALS ===== -->
    <!-- Manual/Scanning Modal -->
    <div x-show="isScanningModalOpen" x-cloak x-transition class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div @click.outside="closeScanningModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <header class="p-5 border-b flex justify-between items-center">
                <div>
                     <h3 class="text-xl font-bold text-gray-800">Quét & Nhập IMEI/Serial</h3>
                     <p x-text="currentItemForScanning.name" class="text-sm text-gray-600"></p>
                </div>
                <button @click="closeScanningModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
            </header>
            <main class="flex-1 p-5 grid grid-cols-1 md:grid-cols-2 gap-5 overflow-hidden">
                <!-- Left: Input Area -->
                <div class="flex flex-col space-y-3">
                     <form @submit.prevent="addSerial()">
                         <label for="serial-input" class="font-semibold text-gray-700">
                             Quét mã cho sản phẩm (<span x-text="scannedData[currentItemForScanning?.id]?.length || 0"></span>/<span x-text="currentItemForScanning.quantity"></span>)
                         </label>
                         <input type="text" id="serial-input" x-model="currentSerialInput"
                                class="w-full p-3 mt-1 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Nhập tay hoặc quét barcode...">
                         <p x-show="scanError" x-text="scanError" class="text-sm text-red-600 h-5 mt-1"></p>
                     </form>
                     <div class="flex-1 flex flex-col space-y-2">
                        <button @click="addSerial()" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-keyboard mr-2"></i> Thêm
                        </button>
                        <button @click="openBarcodeScanner()" class="w-full bg-gray-700 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition-colors">
                            <i class="fas fa-camera mr-2"></i> Mở Camera
                        </button>
                     </div>
                </div>
                <!-- Right: Scanned List -->
                <div class="bg-gray-50 rounded-lg flex flex-col overflow-hidden">
                    <p class="p-3 font-semibold text-gray-700 border-b">Danh sách đã quét</p>
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
                        <template x-if="!scannedData[currentItemForScanning?.id] || scannedData[currentItemForScanning?.id].length === 0">
                            <p class="text-center text-gray-500 pt-10">Chưa có mã nào được quét.</p>
                        </template>
                        <template x-for="serial in scannedData[currentItemForScanning?.id]" :key="serial">
                             <div class="flex justify-between items-center bg-white p-2 rounded-md border text-sm">
                                <span class="font-mono text-gray-700" x-text="serial"></span>
                                <button @click="removeSerial(currentItemForScanning.id, serial)" class="text-red-500 hover:text-red-700 text-xs">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                             </div>
                        </template>
                    </div>
                </div>
            </main>
            <footer class="p-4 bg-gray-50 border-t text-right">
                <button @click="closeScanningModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Đóng</button>
            </footer>
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
                    <div id="reader" class="w-full border-4 border-gray-300 rounded-lg overflow-hidden transition-all duration-300"></div>
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

    <!-- Success Modal -->
    <div x-show="isSuccessModalOpen" x-cloak x-transition class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div @click.outside="closeSuccessModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-md text-center p-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                 <i class="fas fa-check text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mt-5">Thành công!</h3>
            <p x-text="successMessage" class="text-gray-600 mt-2"></p>
            <button @click="closeSuccessModal()" class="mt-8 w-full px-5 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold transition-colors">OK</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Thư viện quét mã vạch --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
{{-- Thư viện âm thanh Howler.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
<script>
function pageData() {
    return {
        // State
        purchaseOrders: [],
        selectedPO: null,
        scannedData: {},
        isLoading: false,

        // Manual Input Modal State
        isScanningModalOpen: false,
        currentItemForScanning: null,
        currentSerialInput: '',
        scanError: '',
        
        // Barcode Camera Modal State
        isScannerOpen: false,
        html5QrCode: null,

        // Success Modal State
        isSuccessModalOpen: false,
        successMessage: '',

        // Sound State
        beepSound: null,
        soundInitialized: false,

        init() {
            this.fetchPurchaseOrders();
            this.initializeSound(); // Tải âm thanh sẵn sàng
            
            // Lắng nghe sự kiện khi có mã được quét thành công từ camera
            window.addEventListener('barcode-scanned', (event) => {
                const scannedCode = event.detail.code;
                if (this.isScanningModalOpen) {
                    this.currentSerialInput = scannedCode;
                    this.addSerial(); // Tự động thêm serial vừa quét
                }
            });
        },

        // --- SOUND METHODS ---
        initializeSound() {
            if (this.soundInitialized || typeof Howl === 'undefined') return;
            this.beepSound = new Howl({
                src: ['{{ asset('sounds/shop-scanner-beeps.mp3') }}'], // Đảm bảo bạn có file này trong public/sounds
                volume: 0.8,
                onload: () => { this.soundInitialized = true; },
                onloaderror: (id, err) => { console.error('Lỗi tải âm thanh:', err); }
            });
        },
        playBeep() {
            if (this.soundInitialized && this.beepSound) this.beepSound.play();
        },

        // --- DATA FETCHING ---
        fetchPurchaseOrders() {
            this.isLoading = true;
            fetch('{{ route('admin.purchase-orders.api.pending') }}')
                .then(response => response.json())
                .then(data => this.purchaseOrders = data)
                .catch(error => console.error('Lỗi khi tải đơn hàng:', error))
                .finally(() => this.isLoading = false);
        },

        // --- PO SELECTION LOGIC ---
        selectPO(poId) {
            this.selectedPO = this.purchaseOrders.find(p => p.id === poId);
            this.scannedData = {};
            this.selectedPO.items.forEach(item => {
                this.scannedData[item.id] = [];
            });
        },
        resetSelection() {
            this.selectedPO = null;
            this.scannedData = {};
        },
        get isPOFullyScanned() {
            if (!this.selectedPO) return false;
            return this.selectedPO.items.every(item =>
                this.scannedData[item.id] && (this.scannedData[item.id].length === item.quantity)
            );
        },

        // --- MANUAL INPUT MODAL LOGIC ---
        openScanningModal(item) {
            this.currentItemForScanning = item;
            this.isScanningModalOpen = true;
            this.$nextTick(() => document.getElementById('serial-input').focus());
        },
        closeScanningModal() {
            this.isScanningModalOpen = false;
            this.currentItemForScanning = null;
            this.currentSerialInput = '';
            this.scanError = '';
        },
        addSerial() {
            const trimmedSerial = this.currentSerialInput.trim();
            if (!trimmedSerial) {
                this.scanError = 'IMEI/Serial không được để trống.';
                return;
            }
            const currentList = this.scannedData[this.currentItemForScanning.id];
            
            if (currentList.length >= this.currentItemForScanning.quantity) {
                this.scanError = 'Đã quét đủ số lượng cho sản phẩm này.';
                return;
            }
            if (currentList.includes(trimmedSerial)) {
                this.scanError = 'IMEI/Serial này đã được quét.';
                return;
            }
            
            currentList.push(trimmedSerial);
            this.currentSerialInput = '';
            this.scanError = '';
            // Tự động focus lại vào input để quét/nhập tiếp
            this.$nextTick(() => document.getElementById('serial-input').focus());
        },
        removeSerial(itemId, serialToRemove) {
            const list = this.scannedData[itemId];
            const index = list.indexOf(serialToRemove);
            if (index > -1) list.splice(index, 1);
        },
        
        // --- BARCODE CAMERA MODAL LOGIC ---
        openBarcodeScanner() {
            this.isScannerOpen = true;
            this.$nextTick(() => { this.startScanning(); });
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
                
                // Gửi sự kiện chứa mã đã quét
                window.dispatchEvent(new CustomEvent('barcode-scanned', { detail: { code: decodedText } }));
                
                // Không đóng modal ngay, để người dùng có thể quét liên tục
                // this.closeBarcodeScanner();
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
                this.html5QrCode.stop().catch(err => console.error("Lỗi khi dừng camera:", err));
            }
        },

        // --- FORM SUBMISSION ---
        confirmReception() {
            if(!this.isPOFullyScanned || this.isLoading) return;
            this.isLoading = true;
            const url = `{{ url('admin/purchase-orders') }}/${this.selectedPO.id}/receive`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ scanned_serials: this.scannedData })
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => { throw err; });
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.successMessage = `Đã nhập kho thành công cho đơn hàng ${this.selectedPO.po_code}!`;
                    this.isSuccessModalOpen = true;
                } else {
                    alert('Lỗi từ máy chủ: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Lỗi khi xác nhận nhập kho:', error);
                alert('Đã có lỗi xảy ra. Chi tiết: ' + (error.message || 'Lỗi không xác định'));
            })
            .finally(() => this.isLoading = false);
        },
        closeSuccessModal() {
            this.isSuccessModalOpen = false;
            this.purchaseOrders = this.purchaseOrders.filter(p => p.id !== this.selectedPO.id);
            this.resetSelection();
        }
    }
}
</script>
@endpush
