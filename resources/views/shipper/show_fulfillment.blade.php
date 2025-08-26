@extends('layouts.shipper')

{{-- THAY ĐỔI: Tiêu đề trang giờ là chi tiết GÓI HÀNG --}}
@section('title', 'Chi tiết Gói hàng ' . $fulfillment->tracking_code)

@push('styles')
<style>
    /* CSS giữ nguyên, không thay đổi */
    [x-cloak] { display: none !important; }
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center; align-items: flex-end;
        z-index: 50;
        transition: opacity 0.3s ease;
    }
    .modal-content {
        background: white; border-radius: 1.5rem 1.5rem 0 0;
        width: 100%; max-width: 448px;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    .modal-overlay.is-visible .modal-content {
        transform: translateY(0);
    }
    #reader {
        border-radius: 0.5rem;
        border: 4px solid #d1d5db;
        overflow: hidden;
    }
    .scan-feedback {
        transition: all 0.3s ease-in-out;
    }
    .scan-feedback.success {
        background-color: #dcfce7; color: #166534; border-left: 4px solid #22c55e;
    }
    .scan-feedback.error {
        background-color: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444;
    }
</style>
@endpush

@section('content')
{{-- THAY ĐỔI: Bọc toàn bộ trang trong Alpine.js và truyền vào tracking_code của gói hàng --}}
<div x-data="shipperFulfillmentPageData({ trackingCode: '{{ $fulfillment->tracking_code }}' })" x-cloak>
    {{-- Header của trang --}}
    <header class="sticky top-0 bg-white shadow-sm z-10 p-4 flex items-center space-x-4 border-b">
        <a href="{{ route('shipper.dashboard') }}" class="text-gray-600 h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        {{-- THAY ĐỔI: Tiêu đề hiển thị mã vận đơn của gói hàng --}}
        <h1 class="text-lg font-bold text-gray-800">Chi tiết gói hàng {{ $fulfillment->tracking_code }}</h1>
    </header>

    {{-- Nội dung chi tiết --}}
    <div class="p-5 space-y-4">
        {{-- Thông tin người nhận lấy từ đơn hàng cha --}}
        <div class="bg-white p-4 rounded-xl shadow-sm space-y-3">
            <h3 class="text-base font-bold text-gray-800">Thông tin người nhận</h3>
            {{-- THAY ĐỔI: Mọi thông tin đơn hàng đều truy cập qua $fulfillment->order --}}
            <p class="font-semibold">{{ $fulfillment->order->customer_name }}</p>
            <div class="flex items-center justify-between">
                <p class="text-gray-700">{{ $fulfillment->order->customer_phone }}</p>
                <a href="tel:{{ $fulfillment->order->customer_phone }}" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-phone-alt"></i></a>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-gray-700">{{ $fulfillment->order->shipping_full_address }}</p>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($fulfillment->order->shipping_full_address) }}" target="_blank" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-map-marker-alt"></i></a>
            </div>
        </div>

        {{-- Chi tiết sản phẩm trong GÓI HÀNG này --}}
        <div class="bg-white p-4 rounded-xl shadow-sm space-y-2">
            <h3 class="text-base font-bold text-gray-800">Sản phẩm trong gói hàng</h3>
            <ul class="divide-y divide-gray-200">
                {{-- THAY ĐỔI LỚN: Lặp qua các item của fulfillment, không phải của order --}}
                @foreach($fulfillment->items as $fulfillmentItem)
                    <li class="py-3 flex space-x-4 items-center">
                        <img src="{{ $fulfillmentItem->orderItem->image_url ?? 'https://via.placeholder.com/150?text=No+Image' }}" alt="{{ $fulfillmentItem->orderItem->product_name }}" class="w-20 h-20 object-cover rounded-lg shadow-md">
                        <div class="flex-1">
                            {{-- Truy cập thông tin sản phẩm qua orderItem --}}
                            <p class="font-semibold text-gray-800">{{ $fulfillmentItem->orderItem->product_name }}</p>
                            @php
                                $attributes = is_string($fulfillmentItem->orderItem->variant_attributes) ? json_decode($fulfillmentItem->orderItem->variant_attributes, true) : $fulfillmentItem->orderItem->variant_attributes;
                            @endphp
                            @if(!empty($attributes) && is_array($attributes))
                                <div class="text-sm text-gray-500 mt-1">
                                    @foreach($attributes as $key => $value)
                                        <span>{{ $key }}: <strong>{{ $value }}</strong></span>@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                            @endif
                            <div class="flex justify-between text-sm text-gray-600 mt-2">
                                {{-- Số lượng là số lượng trong gói hàng này --}}
                                <span>Số lượng: <span class="font-bold">{{ $fulfillmentItem->quantity }}</span></span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Thông tin thanh toán của cả đơn hàng --}}
        <div class="bg-white p-4 rounded-xl shadow-sm text-center">
            <div class="flex justify-between items-center text-base text-gray-700 mb-3 pb-3 border-b">
                <span>Phương thức thanh toán:</span>
                <span class="font-bold text-blue-600">{{ Str::upper($fulfillment->order->payment_method) }}</span>
            </div>
            @if(strtolower($fulfillment->order->payment_method) === 'cod')
                <p class="text-gray-500">Tổng tiền thu hộ (COD) cho cả đơn</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($fulfillment->order->grand_total, 0, ',', '.') }}đ</p>
            @else
                <p class="text-gray-500">Tổng tiền</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($fulfillment->order->grand_total, 0, ',', '.') }}đ</p>
                <p class="mt-1 text-sm font-semibold {{ $fulfillment->order->payment_status === 'paid' ? 'text-green-600' : 'text-orange-500' }}">
                    (Trạng thái: {{ $fulfillment->order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }})
                </p>
            @endif
        </div>
    </div>

    {{-- Footer chứa các nút hành động --}}
    {{-- THAY ĐỔI: Kiểm tra trạng thái của GÓI HÀNG --}}
    <footer class="sticky bottom-0 p-4 bg-white border-t z-20">
        @if($fulfillment->status === 'awaiting_shipment')
            <button type="button" @click="openBarcodeScanner()" class="w-full flex items-center justify-center bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-barcode mr-2"></i>
                <span>QUÉT MÃ ĐỂ LẤY GÓI HÀNG</span>
            </button>
            {{-- THAY ĐỔI: Form này sẽ được submit bởi JS, action trỏ đến route updateStatus của Order --}}
            <form id="pickup-form" action="{{ route('shipper.orders.updateStatus', $fulfillment->order) }}" method="POST" class="hidden">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="shipped">
                {{-- JS sẽ thêm barcode vào đây trước khi submit --}}
                <input type="hidden" name="barcode" id="scanned-barcode-input">
            </form>

        @elseif(in_array($fulfillment->status, ['shipped', 'out_for_delivery']))
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="isFailureModalOpen = true" class="w-full bg-orange-500 text-white font-bold py-3 rounded-lg">GIAO THẤT BẠI</button>
                <form action="{{ route('shipper.orders.updateStatus', $fulfillment->order) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg">GIAO THÀNH CÔNG</button>
                </form>
            </div>
        @endif
    </footer>

    {{-- Modal quét barcode --}}
    <div x-show="isScannerOpen" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-overlay is-visible">
        <div @click.outside="closeBarcodeScanner()" class="modal-content">
            <header class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Quét mã vận đơn gói hàng</h3>
                <button @click="closeBarcodeScanner()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
            </header>
            <main class="p-4 flex-1">
                <p class="text-center text-gray-600 mb-3">Di chuyển camera để quét mã trên gói hàng.</p>
                <div id="reader-container" class="relative">
                    <div id="reader" class="w-full"></div>
                    <div x-show="isLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-70 text-white rounded-lg">
                        <i class="fas fa-spinner fa-spin text-4xl mb-3"></i>
                        <p>Đang khởi tạo camera...</p>
                    </div>
                </div>
                <div x-show="scanMessage" class="scan-feedback mt-4 p-3 rounded-md font-semibold"
                     :class="{ 'success': scanStatus === 'success', 'error': scanStatus === 'error' }"
                     x-text="scanMessage"></div>
            </main>
            <footer class="p-4 bg-gray-50 border-t flex justify-end">
                <button @click="closeBarcodeScanner()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Đóng
                </button>
            </footer>
        </div>
    </div>
    
    {{-- Modal lý do giao hàng thất bại --}}
    @include('shipper.partials.failure_reason_modal', ['order' => $fulfillment->order])
</div>
@endsection


@push('scripts')
{{-- Thư viện quét mã vạch và âm thanh --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>

<script>
    function shipperFulfillmentPageData(initialData) {
        return {
            // Data
            trackingCode: initialData.trackingCode,
            // Modal States
            isScannerOpen: false,
            isFailureModalOpen: false, // This is already in your new file
            // Scanner State
            isLoading: false,
            html5QrCode: null,
            scanStatus: '', // 'success', 'error'
            scanMessage: '',
            // Sound
            beepSound: null,
            errorSound: null,
            soundInitialized: false,

            init() {
                this.initializeSound();
            },
            
            initializeSound() {
                if (this.soundInitialized || typeof Howl === 'undefined') return;
                this.beepSound = new Howl({ src: ['/sounds/scanner-beep.mp3'], volume: 0.8 });
                this.errorSound = new Howl({ src: ['/sounds/scanner-error.mp3'], volume: 0.8 });
                this.soundInitialized = true;
            },

            openBarcodeScanner() {
                this.isScannerOpen = true;
                this.scanStatus = '';
                this.scanMessage = '';
                this.$nextTick(() => this.startScanning());
            },

            closeBarcodeScanner() {
                this.isScannerOpen = false;
                this.stopScanning();
            },

            startScanning() {
                if (typeof Html5Qrcode === "undefined" || this.html5QrCode?.isScanning) return;
                
                this.isLoading = true;
                this.html5QrCode = new Html5Qrcode("reader");

                const config = { fps: 10, qrbox: { width: 250, height: 150 } };

                const onScanSuccess = (decodedText, decodedResult) => {
                    if (!this.isScannerOpen) return;
                    this.stopScanning();

                    if (decodedText.trim() === this.trackingCode.trim()) {
                        this.beepSound?.play();
                        try { navigator.vibrate(100); } catch (e) {}

                        this.scanStatus = 'success';
                        this.scanMessage = 'Quét thành công! Đang cập nhật trạng thái...';

                        document.getElementById('scanned-barcode-input').value = decodedText;
                        setTimeout(() => {
                            document.getElementById('pickup-form').submit();
                        }, 1000);
                    } else {
                        this.errorSound?.play();
                        try { navigator.vibrate([200, 50, 200]); } catch (e) {}
                        this.scanStatus = 'error';
                        this.scanMessage = `Mã vận đơn không khớp! (Quét được: ${decodedText})`;

                        setTimeout(() => {
                            this.scanMessage = '';
                            this.scanStatus = '';
                            if (this.isScannerOpen) {
                                this.startScanning();
                            }
                        }, 2500);
                    }
                };

                this.html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, (error) => {})
                    .then(() => { this.isLoading = false; })
                    .catch(err => {
                        this.isLoading = false;
                        this.scanStatus = 'error';
                        this.scanMessage = `Lỗi camera: ${err}. Vui lòng cấp quyền hoặc sử dụng HTTPS.`;
                    });
            },

            stopScanning() {
                if (this.html5QrCode && this.html5QrCode.isScanning) {
                    this.html5QrCode.stop().catch(err => console.error("Lỗi dừng camera:", err));
                }
            },
            
            // --- NEW: LÝ DO GIAO HÀNG THẤT BẠI ---
            submitFailureForm() {
                const reasonSelect = document.getElementById('failure-reason');
                const notesTextarea = document.getElementById('failure-notes');
                const failForm = document.getElementById('fail-delivery-form');
                
                let reason = reasonSelect.value;
                if (reason === 'other') {
                    reason = notesTextarea.value.trim();
                }

                document.getElementById('fail-reason-input').value = reason;
                document.getElementById('fail-notes-input').value = notesTextarea.value;

                // Submit form
                failForm.submit();
            }
        }
    }
</script>
@endpush