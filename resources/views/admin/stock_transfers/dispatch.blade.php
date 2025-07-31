@extends('admin.layouts.app')

@section('title', 'Xuất Kho Chuyển Hàng')

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

    /* Status Badge Styles */
    .status-badge { font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 9999px; }
    .status-pending { background-color: #FEF9C3; color: #713F12; } /* Yellow for Pending */
</style>
@endpush

@section('content')
<div class="container mx-auto p-4 md:p-8" x-data="dispatchPageData({{ Js::from($preselectedTransfer) }})">

    <!-- Header -->
    <header class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
             <div class="mb-4">
                <a href="{{ route('admin.stock-transfers.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Quay lại danh sách
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Xuất Kho Chuyển Hàng</h1>
            <p class="text-gray-600 mt-1">Chọn Phiếu Chuyển Kho và quét IMEI/Serial để gửi hàng.</p>
        </div>
        <div x-show="selectedTransfer" x-cloak class="mt-4 sm:mt-0 w-full sm:w-auto">
             <button @click="confirmDispatch()"
                     :disabled="!isFullyScanned || isLoading"
                     class="flex w-full sm:w-auto items-center justify-center text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors"
                     :class="{
                         'bg-indigo-600 hover:bg-indigo-700': isFullyScanned && !isLoading,
                         'bg-gray-400 cursor-not-allowed': !isFullyScanned || isLoading,
                         'opacity-75 cursor-wait': isLoading
                     }">
                <i x-show="isLoading" class="fas fa-spinner fa-spin mr-2"></i>
                <i class="fas fa-shipping-fast mr-2" x-show="isFullyScanned && !isLoading"></i>
                <span x-text="isLoading ? 'Đang xử lý...' : (isFullyScanned ? 'Xác nhận & Gửi hàng' : 'Cần quét đủ số lượng')"></span>
            </button>
        </div>
    </header>

    <!-- Main Card Content -->
    <div class="card p-6 min-h-[400px] flex items-center justify-center">
        <!-- Loading State -->
        <div x-show="isLoading && stockTransfers.length === 0" class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
            <p class="mt-3 text-gray-600">Đang tải danh sách phiếu chuyển kho...</p>
        </div>

        <!-- Transfer Selection -->
        <template x-if="!selectedTransfer && !isLoading">
            <div class="w-full max-w-7xl mx-auto text-center">
                <h2 class="text-xl font-semibold text-gray-800">Chọn Phiếu Chuyển Kho để bắt đầu</h2>
                <p class="text-gray-500 mt-2">Chọn phiếu bạn muốn xuất hàng đi.</p>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="st in stockTransfers" :key="st.id">
                        <div @click="selectTransfer(st)" class="p-4 border rounded-lg hover:bg-indigo-50 hover:border-indigo-400 cursor-pointer transition-colors text-left">
                            <div class="flex justify-between items-center">
                                <p class="font-bold text-indigo-600" x-text="st.transfer_code"></p>
                                <span class="status-badge status-pending">Chờ chuyển</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-2">Từ: <span class="font-medium" x-text="st.from_location_name"></span></p>
                            <p class="text-sm text-gray-700">Đến: <span class="font-medium" x-text="st.to_location_name"></span></p>
                            <p class="text-sm text-gray-500">Ngày tạo: <span x-text="new Date(st.created_at).toLocaleDateString('vi-VN')"></span></p>
                        </div>
                    </template>
                     <template x-if="stockTransfers.length === 0">
                        <div class="pt-4 text-center md:col-span-2 lg:col-span-3">
                            <i class="fas fa-box-open text-4xl text-gray-400"></i>
                            <p class="text-gray-500 mt-3">Không có phiếu nào đang chờ chuyển kho.</p>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Scanning Interface -->
        <template x-if="selectedTransfer">
            <div x-cloak class="w-full">
                <div class="flex flex-col md:flex-row justify-between items-start mb-6 pb-6 border-b">
                    <div>
                        <p class="text-gray-500">Đang xuất kho cho phiếu:</p>
                        <h2 class="text-2xl font-bold text-gray-800" x-text="selectedTransfer.transfer_code"></h2>
                        <p class="text-gray-700">Từ kho: <span class="font-semibold" x-text="selectedTransfer.from_location_name"></span></p>
                        <p class="text-gray-700">Đến kho: <span class="font-semibold" x-text="selectedTransfer.to_location_name"></span></p>
                    </div>
                    <button @click="resetSelection()" class="mt-4 md:mt-0 text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                       <i class="fas fa-arrow-left mr-1"></i> Quay lại chọn phiếu
                    </button>
                </div>
                <div class="space-y-6">
                    <template x-for="item in selectedTransfer.items" :key="item.id">
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
                                         :class="scannedData[item.id].length === item.quantity ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200'">
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

    <!-- Modals -->
    @include('admin.stock_transfers.partials._dispatch_modals')
</div>
@endsection

@push('scripts')
{{-- Thư viện quét mã vạch --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
{{-- Thư viện âm thanh Howler.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
<script>
function dispatchPageData(preselectedTransfer = null) {
    return {
        // State
        stockTransfers: [],
        selectedTransfer: null,
        scannedData: {},
        isLoading: false,

        // Modals State
        isScanningModalOpen: false,
        currentItemForScanning: null,
        currentSerialInput: '',
        scanError: '',
        isScannerOpen: false,
        html5QrCode: null,
        isSuccessModalOpen: false,
        successMessage: '',

        // Sound State
        beepSound: null,
        soundInitialized: false,

        init() {
            this.initializeSound();
            
            if (preselectedTransfer) {
                this.selectTransfer(preselectedTransfer);
            } else {
                this.fetchPendingTransfers();
            }
            
            window.addEventListener('barcode-scanned', (event) => {
                const scannedCode = event.detail.code;
                if (this.isScanningModalOpen) {
                    this.currentSerialInput = scannedCode;
                    this.addSerial();
                }
            });
        },

        // --- SOUND METHODS ---
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

        // --- DATA FETCHING ---
        fetchPendingTransfers() {
            this.isLoading = true;
            fetch('{{ route('admin.stock-transfers.api.pending') }}')
                .then(response => response.json())
                .then(data => {
                    this.stockTransfers = data;
                })
                .catch(error => console.error('Error fetching transfers:', error))
                .finally(() => this.isLoading = false);
        },

        // --- SELECTION LOGIC ---
        selectTransfer(transfer) {
            this.selectedTransfer = transfer;
            this.scannedData = {};
            this.selectedTransfer.items.forEach(item => {
                this.scannedData[item.id] = [];
            });
        },
        resetSelection() {
            this.selectedTransfer = null;
            this.scannedData = {};
            // If the page was loaded for a specific transfer, redirect to the general dispatch page
            if (preselectedTransfer) {
                window.location.href = '{{ route('admin.stock-transfers.dispatch.index') }}';
            }
        },
        get isFullyScanned() {
            if (!this.selectedTransfer) return false;
            return this.selectedTransfer.items.every(item =>
                this.scannedData[item.id] && (this.scannedData[item.id].length === item.quantity)
            );
        },

        // --- SCANNING MODAL LOGIC ---
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
            this.playBeep();
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
                window.dispatchEvent(new CustomEvent('barcode-scanned', { detail: { code: decodedText } }));
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
        },

        // --- FORM SUBMISSION ---
        confirmDispatch() {
            if(!this.isFullyScanned || this.isLoading) return;
            this.isLoading = true;
            const url = `{{ url('admin/stock-transfers') }}/${this.selectedTransfer.id}/dispatch`;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
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
                    this.successMessage = `Đã xuất kho thành công cho phiếu ${this.selectedTransfer.transfer_code}!`;
                    this.isSuccessModalOpen = true;
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error confirming dispatch:', error);
                alert('Đã xảy ra lỗi. Chi tiết: ' + (error.message || 'Lỗi không xác định'));
            })
            .finally(() => this.isLoading = false);
        },
        closeSuccessModal() {
            this.isSuccessModalOpen = false;
            // Redirect to the index page after success
            window.location.href = '{{ route('admin.stock-transfers.index') }}';
        }
    }
}
</script>
@endpush
