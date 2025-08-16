@extends('layouts.shipper')

@section('title', 'Đơn hàng hôm nay')

@push('styles')
<style>
    .tab-indicator {
        transition: left 0.3s ease-in-out, width 0.3s ease-in-out;
    }
</style>
@endpush

@section('content')
<div x-data="shipperDashboard()">
<header class="page-header p-5 bg-white flex justify-between items-center">
    <div>
        <p class="text-sm text-gray-500">Xin chào,</p>
        <h1 class="text-xl font-bold text-gray-800">{{ $shipper->name }}</h1>
    </div>
    <div class="relative"
        x-data="{
        notificationOpen: false,
        unreadCount: {{ $unreadNotificationsCount }},
        markAsRead() {
            if (this.unreadCount > 0) {
                fetch('{{ route('notifications.markAsRead') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    this.unreadCount = 0;
                });
            }
        }
    }">
        <!-- Nút chuông -->
        <button
            @click.stop="notificationOpen = !notificationOpen; if (notificationOpen) markAsRead()"
            class="flex items-center justify-center w-10 h-10 rounded-full text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors duration-300 relative"
            aria-label="Thông báo">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 
                14.158V11a6.002 6.002 0 00-4-5.659V5a2 
                2 0 10-4 0v.341C7.67 6.165 6 8.388 
                6 11v3.159c0 .538-.214 1.055-.595 
                1.436L4 17h5m6 0v1a3 3 0 
                11-6 0v-1m6 0H9" />
            </svg>

            <!-- Badge thông báo chưa đọc -->
            <template x-if="unreadCount > 0">
                <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex items-center justify-center text-xs text-white rounded-full h-4 w-4 bg-red-500"
                        x-text="unreadCount"></span>
                </span>
            </template>
        </button>

        <!-- Dropdown nội dung -->
        <div x-show="notificationOpen"
            @click.outside="notificationOpen = false"
            x-transition:enter="transition ease-out duration-200 origin-top"
            x-transition:enter-start="opacity-0 scale-y-90"
            x-transition:enter-end="opacity-100 scale-y-100"
            x-transition:leave="transition ease-in duration-150 origin-top"
            x-transition:leave-start="opacity-100 scale-y-100"
            x-transition:leave-end="opacity-0 scale-y-90"
            class="absolute right-0 sm:-right-10 mt-2 w-80 sm:w-96 max-h-96 overflow-y-auto shadow-lg rounded-lg bg-white border border-slate-200 z-50">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-600">
                <h4 class="font-semibold text-slate-800 black:text-slate-100">Thông báo</h4>
            </div>
            <ul class="divide-y divide-slate-200 light:divide-slate-600">
                @forelse($recentNotifications ?? [] as $notification)
                <a href="#" class="flex items-start p-3 hover:bg-gray-700/50 transition-colors">
                    <div class="flex-shrink-0 w-10 h-10 bg-{{ $notification['color'] ?? 'gray' }}-500/20 text-{{ $notification['color'] ?? 'gray' }}-400 rounded-full flex items-center justify-center">
                        @if (isset($notification['icon']) && $notification['icon'] === "check")
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        @elseif (isset($notification['icon']) && $notification['icon'] === "warning")
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                            <line x1="12" x2="12" y1="9" y2="13"></line>
                            <line x1="12" x2="12.01" y1="17" y2="17"></line>
                        </svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <line x1="12" x2="12" y1="2" y2="22" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-black-200">{{ $notification['title'] ?? 'Không có tiêu đề' }}</p>
                        <p class="text-sm font-medium text-black-200">{{ $notification['message'] ?? 'Không có tiêu đề' }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification['time'] ?? '' }}</p>
                    </div>
                </a>
                @empty
                <div class="text-center text-white-400 py-8 px-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-white-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="mt-4 text-sm font-semibold">Không có thông báo mới</p>
                    <p class="mt-1 text-xs text-white-500">Chúng tôi sẽ cho bạn biết khi có tin tức.</p>
                </div>
                @endforelse
            </ul>
            <div class="px-4 py-2 border-t border-slate-200 dark:border-slate-600 text-center">
                <a href="#" class="block w-full text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                    Xem tất cả
                </a>
            </div>
        </div>
    </div>
</header>

<nav class="page-header sticky top-0 bg-white z-10">
    <div class="relative flex border-b border-gray-200">
        <button data-tab="pickup" class="tab-btn flex-1 p-4 text-sm font-semibold text-indigo-600">Cần Lấy ({{ $ordersToPickup->count() }})</button>
        <button data-tab="shipping" class="tab-btn flex-1 p-4 text-sm font-semibold text-gray-500">Đang Giao ({{ $ordersInTransit->count() }})</button>
        <div id="tab-indicator" class="tab-indicator absolute bottom-0 h-1 bg-indigo-600 rounded-t-full"></div>
    </div>
</nav>

<main class="page-content p-4 space-y-3 bg-gray-50">
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div id="pickup-list" class="tab-content space-y-3">
        @forelse($ordersToPickup as $order)
        @include('shipper.partials.order_card', ['order' => $order])
        @empty
        <p class="text-center text-gray-500 pt-10">Không có đơn hàng nào cần lấy.</p>
        @endforelse
    </div>
    <div id="shipping-list" class="tab-content hidden space-y-3">
        @forelse($ordersInTransit as $order)
        @include('shipper.partials.order_card', ['order' => $order])
        @empty
        <p class="text-center text-gray-500 pt-10">Không có đơn hàng nào đang giao.</p>
        @endforelse
    </div>
</main>

@include('shipper.partials._barcode_scanner_modal')
</div>
@endsection

@push('scripts')
{{-- Thư viện quét mã vạch --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
{{-- Thư viện âm thanh Howler.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>

<script>
    function shipperDashboard() {
        return {
            isScannerOpen: false,
            currentOrderId: null,
            html5QrCode: null,
            beepSound: null,
            soundInitialized: false,

            init() {
                this.initializeSound();
                window.addEventListener('barcode-scanned', (event) => {
                    this.handleBarcodeScanned(event.detail.code);
                });
            },

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

            openBarcodeScanner(orderId) {
                this.currentOrderId = orderId;
                this.isScannerOpen = true;
                this.$nextTick(() => this.startScanning());
            },

            closeBarcodeScanner() {
                this.stopScanning();
                this.isScannerOpen = false;
                this.currentOrderId = null;
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
            },

            handleBarcodeScanned(code) {
                if (!this.currentOrderId) return;
                
                console.log('Scanning barcode:', code, 'for order:', this.currentOrderId);
                
                // Gửi request để cập nhật trạng thái đơn hàng
                fetch(`/shipper/orders/${this.currentOrderId}/update-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        barcode: code,
                        status: 'shipped'
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        // Log chi tiết lỗi
                        return response.text().then(text => {
                            console.error('Error response:', text);
                            throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Success response:', data);
                    if (data.success) {
                        this.showAlert(data.message, 'success');
                        // Reload trang để cập nhật danh sách đơn hàng
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showAlert(data.message || 'Có lỗi xảy ra', 'error');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    this.showAlert('Lỗi kết nối: ' + error.message, 'error');
                })
                .finally(() => {
                    this.closeBarcodeModal();
                });
            },
            
            showAlert(message, type = 'info') {
                // Tạo alert đơn giản
                const alertDiv = document.createElement('div');
                alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg text-white ${
                    type === 'success' ? 'bg-green-500' : 
                    type === 'error' ? 'bg-red-500' : 'bg-blue-500'
                }`;
                alertDiv.textContent = message;
                document.body.appendChild(alertDiv);
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        const tabIndicator = document.getElementById('tab-indicator');

        function updateTabIndicator(selectedTab) {
            tabIndicator.style.left = `${selectedTab.offsetLeft}px`;
            tabIndicator.style.width = `${selectedTab.offsetWidth}px`;
        }

        // Set initial indicator position
        const initialTab = document.querySelector('.tab-btn[data-tab="pickup"]');
        if (initialTab) {
            updateTabIndicator(initialTab);
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;

                // Update button styles
                tabButtons.forEach(btn => btn.classList.replace('text-indigo-600', 'text-gray-500'));
                button.classList.replace('text-gray-500', 'text-indigo-600');

                // Update indicator
                updateTabIndicator(button);

                // Show content
                tabContents.forEach(content => content.classList.add('hidden'));
                document.getElementById(`${tabId}-list`).classList.remove('hidden');
            });
        });
    });
</script>
@endpush