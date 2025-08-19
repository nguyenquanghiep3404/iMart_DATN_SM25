@extends('layouts.shipper')

@section('title', 'Chi tiết ĐH ' . $order->order_code)

@push('styles')
<style>
    /* Ẩn các phần tử Alpine.js cho đến khi được khởi tạo */
    [x-cloak] { display: none !important; }

    /* CSS chung cho modal overlay */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center; align-items: flex-end; /* Hiện modal từ dưới lên */
        z-index: 50;
        transition: opacity 0.3s ease;
    }

    /* CSS chung cho nội dung modal */
    .modal-content {
        background: white; border-radius: 1.5rem 1.5rem 0 0;
        width: 100%; max-width: 448px;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
        max-height: 90vh; /* Giới hạn chiều cao */
        display: flex;
        flex-direction: column;
    }

    .modal-overlay.is-visible .modal-content {
        transform: translateY(0);
    }

    /* CSS riêng cho modal quét mã */
    #reader {
        border-radius: 0.5rem;
        border: 4px solid #d1d5db; /* gray-300 */
        overflow: hidden;
    }

    .scan-feedback {
        transition: all 0.3s ease-in-out;
    }
    .scan-feedback.success {
        background-color: #dcfce7; /* green-100 */
        color: #166534; /* green-800 */
        border-left: 4px solid #22c55e; /* green-500 */
    }
    .scan-feedback.error {
        background-color: #fee2e2; /* red-100 */
        color: #991b1b; /* red-800 */
        border-left: 4px solid #ef4444; /* red-500 */
    }
</style>
@endpush

@section('content')
{{-- Bọc toàn bộ trang trong Alpine.js để quản lý state --}}
<div x-data="shipperOrderPageData({ orderCode: '{{ $order->order_code }}' })" x-cloak>
    {{-- Header của trang --}}
    <header class="sticky top-0 bg-white shadow-sm z-10 p-4 flex items-center space-x-4 border-b">
        <a href="{{ route('shipper.dashboard') }}" class="text-gray-600 h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-lg font-bold text-gray-800">Chi tiết đơn hàng</h1>
    </header>

    {{-- Nội dung chi tiết đơn hàng --}}
    <div class="p-5 space-y-4">
        <div class="bg-white p-4 rounded-xl shadow-sm space-y-3">
            <h3 class="text-base font-bold text-gray-800">Thông tin người nhận</h3>
            <p class="font-semibold">{{ $order->customer_name }}</p>
            <div class="flex items-center justify-between">
                <p class="text-gray-700">{{ $order->customer_phone }}</p>
                <a href="tel:{{ $order->customer_phone }}" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-phone-alt"></i></a>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-gray-700">{{ $order->shipping_full_address }}</p>
                <a href="https://maps.google.com/?q={{ urlencode($order->shipping_full_address) }}" target="_blank" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-map-marker-alt"></i></a>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm space-y-2">
            <h3 class="text-base font-bold text-gray-800">Chi tiết sản phẩm</h3>
            <ul class="divide-y divide-gray-200">
                @foreach($order->items as $item)
                    <li class="py-3 flex space-x-4 items-center">
                        <img src="{{ $item->image_url ?? 'https://via.placeholder.com/150?text=No+Image' }}" alt="{{ $item->product_name }}" class="w-20 h-20 object-cover rounded-lg shadow-md">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">{{ $item->product_name }}</p>
                            @php
                                $attributes = is_string($item->variant_attributes) ? json_decode($item->variant_attributes, true) : $item->variant_attributes;
                            @endphp
                            @if(!empty($attributes) && is_array($attributes))
                                <div class="text-sm text-gray-500 mt-1">
                                    @foreach($attributes as $key => $value)
                                        <span>{{ $key }}: <strong>{{ $value }}</strong></span>@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                            @endif
                            <div class="flex justify-between text-sm text-gray-600 mt-2">
                                <span>Số lượng: <span class="font-bold">{{ $item->quantity }}</span></span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm text-center">
            <div class="flex justify-between items-center text-base text-gray-700 mb-3 pb-3 border-b">
                <span>Phương thức thanh toán:</span>
                <span class="font-bold text-blue-600">{{ Str::upper($order->payment_method) }}</span>
            </div>
            @if(strtolower($order->payment_method) === 'cod')
                <p class="text-gray-500">Tổng tiền thu hộ (COD)</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($order->grand_total, 0, ',', '.') }}đ</p>
            @else
                <p class="text-gray-500">Tổng tiền</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($order->grand_total, 0, ',', '.') }}đ</p>
                <p class="mt-1 text-sm font-semibold {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-orange-500' }}">
                    (Trạng thái: {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }})
                </p>
            @endif
        </div>
    </div>

    {{-- Footer chứa các nút hành động --}}
    @if(in_array($order->status, ['awaiting_shipment', 'shipped', 'out_for_delivery']))
        <footer class="sticky bottom-0 p-4 bg-white border-t z-20">
            @if($order->status === 'awaiting_shipment')
                {{-- Nút mới để mở modal quét mã --}}
                <button type="button" @click="openBarcodeScanner()" class="w-full flex items-center justify-center bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-barcode mr-2"></i>
                    <span>QUÉT MÃ ĐỂ LẤY HÀNG</span>
                </button>
                {{-- Form ẩn để submit sau khi quét thành công --}}
                <form id="pickup-form" action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST" class="hidden">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="shipped">
                </form>

            @elseif(in_array($order->status, ['shipped', 'out_for_delivery']))
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" id="btn-fail-action" class="w-full bg-orange-500 text-white font-bold py-3 rounded-lg">GIAO THẤT BẠI</button>
                    <form action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg">GIAO THÀNH CÔNG</button>
                    </form>
                </div>
            @endif
        </footer>
    @endif


    {{-- Modal quét barcode --}}

    <div x-show="isScannerOpen"

    <div x-show="isScannerOpen"

         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-overlay is-visible">
        <div @click.outside="closeBarcodeScanner()" class="modal-content">
            <header class="p-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Quét mã vạch đơn hàng</h3>
                <button @click="closeBarcodeScanner()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
            </header>
            <main class="p-4 flex-1">
                <p class="text-center text-gray-600 mb-3">Di chuyển camera để quét mã vạch trên kiện hàng.</p>
                <div id="reader-container" class="relative">
                    <div id="reader" class="w-full"></div>
                    <div x-show="isLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-70 text-white rounded-lg">
                        <i class="fas fa-spinner fa-spin text-4xl mb-3"></i>
                        <p>Đang khởi tạo camera...</p>
                    </div>
                </div>
                <div x-show="scanMessage" class="scan-feedback mt-4 p-3 rounded-md font-semibold" :class="{ 'success': scanStatus === 'success', 'error': scanStatus === 'error' }" x-text="scanMessage"></div>
            </main>
            <footer class="p-4 bg-gray-50 border-t flex justify-end">
                <button @click="closeBarcodeScanner()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Đóng
                </button>
            </footer>
        </div>
    </div>
</div>
@endsection


@push('modals')
    {{-- Modal lý do giao hàng thất bại --}}
    <div id="failure-reason-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <h2 class="text-xl font-bold text-gray-800 p-6 border-b">Lý do giao không thành công</h2>
            <form id="fail-delivery-form" action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST" class="hidden">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="failed_delivery">
                <input type="hidden" id="fail-reason-input" name="reason">
                <input type="hidden" id="fail-notes-input" name="notes">
            </form>
            <div class="modal-body p-6 space-y-4">
                <div>
                    <label for="failure-reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do chính</label>
                    <select id="failure-reason" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Không liên lạc được khách hàng">Không liên lạc được khách</option>
                        <option value="Khách hẹn giao lại">Khách hẹn giao lại</option>
                        <option value="Sai địa chỉ">Sai địa chỉ</option>
                        <option value="other">Lý do khác</option>
                    </select>
                </div>
                <div>
                    <label for="failure-notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú thêm</label>
                    <textarea id="failure-notes" rows="3" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="VD: Khách hẹn giao sau 17h"></textarea>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 gap-3 border-t">
                <button type="button" class="w-full bg-gray-200 text-gray-700 font-bold py-3 rounded-lg close-modal-btn">Hủy</button>
                <button type="button" id="confirm-failure-btn" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg">Xác nhận</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
{{-- Thư viện quét mã vạch --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
{{-- Thư viện âm thanh Howler.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>

<script>
    function shipperOrderPageData(initialData) {
        return {
            // Data
            orderCode: initialData.orderCode,
            // Scanner State
            isScannerOpen: false,
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

            // --- BARCODE SCANNER & SOUND METHODS ---
            initializeSound() {
                if (this.soundInitialized || typeof Howl === 'undefined') return;
                // Thay thế bằng đường dẫn đúng đến file âm thanh của bạn
                this.beepSound = new Howl({ src: ['/sounds/scanner-beep.mp3'], volume: 0.8 });
                this.errorSound = new Howl({ src: ['/sounds/scanner-error.mp3'], volume: 0.8 });
                this.soundInitialized = true;
            },

            openBarcodeScanner() {
                this.isScannerOpen = true;
                this.scanStatus = '';
                this.scanMessage = '';
                // Dùng nextTick để đảm bảo DOM của modal đã hiển thị trước khi quét
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



                this.isLoading = true;
                this.html5QrCode = new Html5Qrcode("reader");

                const config = { fps: 10, qrbox: { width: 250, height: 150 }, supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] };

                const onScanSuccess = (decodedText, decodedResult) => {
                    if (!this.isScannerOpen) return;
                    this.stopScanning();
                    this.stopScanning();

                    if (decodedText.trim() === this.orderCode.trim()) {
                        this.beepSound?.play();
                        try { navigator.vibrate(100); } catch (e) {}

                        this.scanStatus = 'success';
                        this.scanMessage = 'Quét thành công! Đang cập nhật trạng thái...';

                        setTimeout(() => {
                            document.getElementById('pickup-form').submit();
                        }, 1000);
                    } else {
                        this.errorSound?.play();
                        try { navigator.vibrate([200, 50, 200]); } catch (e) {}
                        this.scanStatus = 'error';
                        this.scanMessage = `Mã không khớp! (Quét được: ${decodedText})`;

                        setTimeout(() => {
                            this.scanMessage = '';
                            this.scanStatus = '';
                            if (this.isScannerOpen) {
                                this.startScanning();
                            }
                        }, 2500);
                    }
                };

                const onScanFailure = (error) => {
                    // Bỏ qua lỗi này vì nó xảy ra liên tục khi không tìm thấy mã
                };

                this.html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
                    .then(() => { this.isLoading = false; })
                    .catch(err => {
                        this.isLoading = false;
                        this.scanStatus = 'error';
                        this.scanMessage = `Lỗi camera: ${err}. Vui lòng cấp quyền hoặc sử dụng camera sau.`;
                    });
            },

            stopScanning() {
                if (this.html5QrCode && this.html5QrCode.isScanning) {
                    this.html5QrCode.stop().catch(err => console.error("Lỗi dừng camera:", err));
                }
            }
        }
    }

    // Code xử lý cho modal giao hàng thất bại
    document.addEventListener('DOMContentLoaded', function() {
        const failActionButton = document.getElementById('btn-fail-action');
        const modal = document.getElementById('failure-reason-modal');
        if (failActionButton && modal) {
            const closeButtons = modal.querySelectorAll('.close-modal-btn');
            const confirmButton = modal.querySelector('#confirm-failure-btn');
            const failForm = document.getElementById('fail-delivery-form');
            const reasonSelect = modal.querySelector('#failure-reason');
            const notesTextarea = modal.querySelector('#failure-notes');

            const openModal = () => {
                modal.classList.remove('hidden');
                // Thêm is-visible để kích hoạt animation
                setTimeout(() => modal.classList.add('is-visible'), 10);
                setTimeout(() => modal.classList.add('is-visible'), 10);
            };

            const closeModal = () => {
                modal.classList.remove('is-visible');
                // Đợi animation kết thúc rồi mới thêm class hidden
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            failActionButton.addEventListener('click', openModal);

            closeButtons.forEach(button => {
                button.addEventListener('click', closeModal);
            });

            confirmButton.addEventListener('click', function() {
                let reason = reasonSelect.value;
                // Nếu lý do là 'other', lấy giá trị từ textarea
                if (reason === 'other') {
                    reason = notesTextarea.value.trim();
                }

                // Gán giá trị vào các input ẩn trong form
                document.getElementById('fail-reason-input').value = reason;
                document.getElementById('fail-notes-input').value = notesTextarea.value;
                // Submit form
                failForm.submit();
            });
        }
    });
</script>
@endpush
