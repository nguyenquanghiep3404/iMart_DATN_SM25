{{-- Thư viện quét mã vạch --}}
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush

<!-- Sửa lại modal, bỏ các thuộc tính x- và @ -->
<div id="scanner-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4 hidden">
    
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col">
        <header class="p-4 bg-blue-600 text-white rounded-t-xl">
            <h3 class="text-xl font-bold text-center">Quét Barcode / QR Code</h3>
        </header>
        
        <main class="p-4 flex-1">
            <div id="reader-container" class="relative">
                <div id="reader" class="w-full border-4 border-gray-300 rounded-lg overflow-hidden"></div>
                <div id="loading-message" class="absolute inset-0 flex-col items-center justify-center bg-black bg-opacity-70 text-white hidden">
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
            <button id="close-scanner-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                Đóng
            </button>
        </footer>
    </div>
</div>