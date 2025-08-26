@extends('pos.layouts.pos')

@section('title', 'Giao diện Bán hàng POS')

@push('styles')
<style>
    /* CSS tùy chỉnh từ HTML bạn đã gửi */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a3a3a3; }
    .modal { transition: opacity 0.3s ease; }
    .modal-content { transition: transform 0.3s ease, opacity 0.3s ease; }
    .card { background-color: white; border-radius: 0.75rem; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); }
    .imei-tag { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
</style>
@endpush

@section('header')
<header class="bg-white shadow-md z-20 flex-shrink-0">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center">
            <img src="https://placehold.co/40x40/3B82F6/FFFFFF?text=iM" alt="Logo" class="h-8 w-8 rounded-full mr-3">
            <h1 class="text-xl font-bold text-gray-800">iMart POS</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right">
                <p class="font-semibold text-sm text-gray-700">{{ $user->name }}</p>
                <p class="text-xs text-gray-500">{{ $posSession->register->name }}</p>
            </div>

            {{-- Nút Xem Lịch Sử --}}
            <a href="{{ route('pos.history.index') }}" title="Xem lịch sử" class="p-2 rounded-full hover:bg-gray-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </a>
            
            {{-- Nút Quản lý/Đóng Ca --}}
            <a href="{{ route('pos.sessions.manage', ['register_id' => $posSession->register->id]) }}" title="Quản lý & Đóng ca" class="p-2 rounded-full hover:bg-gray-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
            
            {{-- Nút Đăng Xuất --}}
            <a href="{{ route('logout') }}" title="Đăng xuất" class="p-2 rounded-full hover:bg-red-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </div>
</header>
@endsection

@section('content')
<body class="bg-gray-100 h-screen overflow-hidden flex flex-col">
    <div class="flex-1 overflow-hidden">
        <main class="w-full max-w-screen-2xl mx-auto p-4 h-full">
            <div class="grid grid-cols-12 gap-4 h-full">

                <div class="col-span-12 md:col-span-5 lg:col-span-4 flex flex-col">
                    <div class="card flex-1 flex flex-col overflow-hidden">
                        <div class="p-4 border-b border-gray-200">
                            <div class="relative flex items-center">
                                <div class="absolute top-0 left-0 pl-3 pt-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <input type="text" id="product-search" placeholder="Tìm sản phẩm hoặc quét IMEI/SKU..." class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button id="barcode-scan-button" class="absolute top-0 right-0 p-2 text-gray-500 hover:text-blue-600" title="Quét mã vạch">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </button>
                                <div id="search-suggestions"
                                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-1 shadow-lg max-h-80 overflow-y-auto hidden custom-scrollbar top-full">
                                </div>
                            </div>
                            <div class="mt-3 flex space-x-2 overflow-x-auto custom-scrollbar pb-2">
                            </div>
                        </div>
                        <div id="product-grid" class="p-4 flex-1 overflow-y-auto custom-scrollbar flex flex-col space-y-3">
                        </div>
                    </div>
                </div>

                <div class="col-span-12 md:col-span-7 lg:col-span-5 flex flex-col">
                    <div class="card flex-1 flex flex-col overflow-hidden">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center flex-shrink-0">
                            <h2 class="text-lg font-bold text-gray-800">Hóa Đơn Hiện Tại</h2>
                            <button id="clear-cart-button" class="p-2 rounded-lg hover:bg-red-100 transition-colors" title="Xóa tất cả sản phẩm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                        <div id="cart-items" class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
                            <div id="empty-cart-message" class="flex flex-col items-center justify-center h-full text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="mt-4 font-semibold">Giỏ hàng đang trống</p>
                                <p class="text-sm">Chọn sản phẩm để thêm vào hóa đơn.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-3 flex flex-col space-y-4">
                    <div class="card p-4 flex-shrink-0">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-800">Khách hàng</h3>
                            <button id="add-new-customer-btn" class="text-sm font-semibold text-blue-600 hover:underline">Thêm mới</button>
                        </div>
                        <div id="customer-search-section" class="relative mt-2">
                            <input type="text" id="customer-search-input" placeholder="Tìm khách hàng (SĐT, Tên, Email)..." class="w-full pl-8 pr-4 py-2 border border-gray-300 text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute top-0 left-0 pl-2.5 pt-2.5 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div id="customer-search-results" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-1 shadow-lg max-h-60 overflow-y-auto hidden custom-scrollbar top-full">
                            </div>
                        </div>
                        <div id="selected-customer-info" class="mt-3 hidden">
                        </div>
                    </div>
                    <div class="card flex-1 flex flex-col p-4">
                        <div class="space-y-2 text-sm flex-1">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tổng tiền hàng:</span>
                                <span id="summary-subtotal" class="font-medium text-gray-800">0 VNĐ</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Giảm giá:</span>
                                <span id="summary-discount" class="font-medium text-red-500">- 0 VNĐ</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Thuế (VAT):</span>
                                <span id="summary-tax" class="font-medium text-gray-800">0 VNĐ</span>
                            </div>
                        </div>
                        <div class="border-t-2 border-dashed border-gray-300 my-3"></div>
                        <div class="flex justify-between items-center text-2xl font-extrabold text-gray-900">
                            <span>Tổng Cộng:</span>
                            <span id="summary-grand-total">0 VNĐ</span>
                        </div>
                        <button id="checkout-button" class="w-full mt-4 bg-green-600 text-white font-bold py-4 rounded-lg shadow-lg hover:bg-green-700 transition-transform transform hover:scale-105 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:scale-100" disabled>
                            THANH TOÁN
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="confirm-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-sm transform opacity-0 -translate-y-10">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium leading-6 text-gray-900" id="confirm-modal-title">Xác nhận hành động</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500" id="confirm-modal-message">Bạn có chắc chắn muốn tiếp tục?</p>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-xl">
                <button id="confirm-modal-confirm-btn" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Xác nhận
                </button>
                <button id="confirm-modal-cancel-btn" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                    Hủy bỏ
                </button>
            </div>
        </div>
    </div>

    <div id="imei-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4 hidden">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all">
            <div class="flex justify-between items-center p-4 border-b">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Quét & Nhập IMEI/Serial</h3>
                    <p id="imei-modal-product-name" class="text-sm text-gray-500"></p>
                </div>
                <button id="imei-modal-close-btn-header" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
            </div>
            
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="imei-input" id="imei-input-label" class="block font-semibold text-gray-700 mb-2">Quét mã cho sản phẩm (0/0)</label>
                    <div class="relative">
                        <input type="text" id="imei-input" placeholder="Nhập tay hoặc quét barcode..." class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <p id="imei-error" class="text-red-500 text-sm mt-1 hidden"></p>
                    <button id="add-imei-btn" class="mt-3 w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Thêm
                    </button>
                    <button id="open-imei-scanner-btn" class="mt-3 w-full bg-gray-700 text-white font-bold py-3 rounded-lg hover:bg-gray-800 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Mở Camera
                    </button>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-3">Danh sách đã quét</h4>
                    <div id="imei-list" class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar">
                        <p id="imei-list-empty" class="text-sm text-gray-500">Chưa có mã nào được quét.</p>
                        </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-3 flex justify-end rounded-b-xl">
                <button id="imei-modal-done-btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg">
                    Xong
                </button>
            </div>
        </div>
    </div>

    <div id="payment-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md transform opacity-0 -translate-y-10">
            <div class="p-6">
                <h3 class="text-2xl font-bold text-center text-gray-800">Xác Nhận Thanh Toán</h3>
                <div class="my-6 text-center">
                    <p class="text-gray-600">Tổng số tiền cần thanh toán</p>
                    <p id="modal-total-amount" class="text-5xl font-extrabold text-blue-600 my-2">0 VNĐ</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="payment-method" class="block mb-2 text-sm font-medium text-gray-700">Phương thức thanh toán</label>
                        <select id="payment-method" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Tiền mặt</option>
                            <option value="card">Thẻ ngân hàng</option>
                            <option value="qr">Chuyển khoản / QR Code</option>
                        </select>
                    </div>
                    <div id="cash-payment-section">
                        <label for="cash-received" class="block mb-2 text-sm font-medium text-gray-700">Tiền khách đưa (VNĐ)</label>
                        <input type="text" id="cash-received" class="w-full text-xl text-center font-bold p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="0">
                    </div>
                </div>
                <div class="mt-6 p-4 rounded-lg bg-gray-100 flex justify-between items-center">
                    <span class="text-gray-700 font-semibold">Tiền thừa trả khách:</span>
                    <span id="change-amount" class="text-2xl font-bold text-green-600">0 VNĐ</span>
                </div>
            </div>
            <div class="px-6 pb-6 flex space-x-3">
                <button id="payment-modal-cancel-btn" type="button" class="w-1/3 text-gray-700 bg-white hover:bg-gray-100 font-bold py-3 rounded-lg border border-gray-300 transition-colors">Hủy</button>
                <button id="confirm-payment-button" class="w-2/3 bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition-colors">XÁC NHẬN</button>
            </div>
        </div>
    </div>

    <div id="add-customer-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md">
            <form id="add-customer-form">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800">Thêm Khách Hàng Mới</h3>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label for="customer-name" class="block text-sm font-medium text-gray-700">Tên khách hàng</label>
                            <input type="text" id="customer-name" name="name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="customer-phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                            <input type="tel" id="customer-phone" name="phone" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="customer-email" class="block text-sm font-medium text-gray-700">Email (Không bắt buộc)</label>
                            <input type="email" id="customer-email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3 rounded-b-xl">
                    <button type="button" id="cancel-add-customer-btn" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Lưu & Chọn</button>
                </div>
            </form>
        </div>
    </div>

    <div id="scanner-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4 hidden">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col">
            <header class="p-4 bg-blue-600 text-white rounded-t-xl">
                <h3 class="text-xl font-bold text-center">Quét Barcode / QR Code</h3>
            </header>
            
            <main class="p-4 flex-1">
                <div id="reader-container" class="relative">
                    <div id="reader" class="w-full border-4 border-gray-300 rounded-lg overflow-hidden"></div>
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
                <button id="close-scanner-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Đóng
                </button>
            </footer>
        </div>
    </div>
</body>
@endsection

@push('scripts')
{{-- Thư viện quét mã vạch --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- DOM ELEMENTS ---
        const productGrid = document.getElementById('product-grid');
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCartMessage = document.getElementById('empty-cart-message');
        const checkoutButton = document.getElementById('checkout-button');
        const barcodeScanButton = document.getElementById('barcode-scan-button');
        const productSearchInput = document.getElementById('product-search');
        const suggestionsContainer = document.getElementById('search-suggestions');
        
        // Customer Elements
        const customerSearchInput = document.getElementById('customer-search-input');
        const customerSearchResults = document.getElementById('customer-search-results');
        const selectedCustomerInfo = document.getElementById('selected-customer-info');
        const customerSearchSection = document.getElementById('customer-search-section');
        const addNewCustomerBtn = document.getElementById('add-new-customer-btn');
        const addCustomerModal = document.getElementById('add-customer-modal');
        const addCustomerForm = document.getElementById('add-customer-form');
        const cancelAddCustomerBtn = document.getElementById('cancel-add-customer-btn');

        // Barcode Scanner Modal Elements
        const scannerModal = document.getElementById('scanner-modal');
        const loadingMessage = document.getElementById('loading-message');
        const errorMessage = document.getElementById('scan-error-message');
        const closeScannerBtn = document.getElementById('close-scanner-btn');
        let html5QrCode = null;

        // IMEI Modal Elements
        const imeiModal = document.getElementById('imei-modal');
        const imeiModalProductName = document.getElementById('imei-modal-product-name');
        const imeiInputLabel = document.getElementById('imei-input-label');
        const imeiInput = document.getElementById('imei-input');
        const imeiError = document.getElementById('imei-error');
        const addImeiBtn = document.getElementById('add-imei-btn');
        const openImeiScannerBtn = document.getElementById('open-imei-scanner-btn');
        const imeiListContainer = document.getElementById('imei-list');
        const imeiListEmpty = document.getElementById('imei-list-empty');
        const imeiModalDoneBtn = document.getElementById('imei-modal-done-btn');
        const imeiModalCloseBtnHeader = document.getElementById('imei-modal-close-btn-header');

        // Payment Modal Elements
        const paymentModal = document.getElementById('payment-modal');
        const modalTotalAmount = document.getElementById('modal-total-amount');
        const paymentMethodSelect = document.getElementById('payment-method');
        const cashPaymentSection = document.getElementById('cash-payment-section');
        const cashReceivedInput = document.getElementById('cash-received');
        const changeAmountSpan = document.getElementById('change-amount');
        const confirmPaymentButton = document.getElementById('confirm-payment-button');
        const paymentModalCancelBtn = document.getElementById('payment-modal-cancel-btn');


        // --- STATE ---
        let cart = []; 
        let searchResults = []; 
        let selectedCustomer = null;
        let currentCartItemIndexForImei = null;
        let isScanningForImei = false;

        // --- DATA FROM SERVER ---
        const initialProducts = @json($products);
        const categories = @json($categories);

        // --- CORE FUNCTIONS ---
        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
        }

        function isCartValid() {
            if (cart.length === 0) return false;
            if (!selectedCustomer) return false; // Phải chọn khách hàng
            for (const item of cart) {
                if (item.product.has_serial_tracking && item.imeis.length !== item.quantity) {
                    return false;
                }
            }
            return true;
        }

        function renderProducts(productsToRender) {
            productGrid.innerHTML = productsToRender.map(p => `
                <div class="card p-3 cursor-pointer hover:bg-gray-50 hover:shadow-md transition-all product-item flex items-center space-x-4" data-product-id="${p.id}">
                    <img src="${p.primary_image ? '/storage/' + p.primary_image.path : 'https://placehold.co/64x64/ccc/fff?text=Err'}" alt="${p.product.name}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-800 text-sm" title="${p.product.name}">${p.product.name}</h4>
                        <p class="text-xs text-gray-500 font-mono">${p.sku}</p>
                        <p class="text-base font-semibold text-blue-600 mt-1">${formatCurrency(p.price)}</p>
                        <p class="text-xs text-gray-400">Tồn kho: ${p.stock_quantity}</p>
                    </div>
                </div>
            `).join('');
        }

        function renderCategories() {
            const categoryButtonsContainer = document.querySelector('.mt-3.flex.space-x-2');
            categoryButtonsContainer.innerHTML = `
                <button class="px-3 py-1 text-sm font-semibold bg-blue-600 text-white rounded-full whitespace-nowrap category-btn active" data-category-id="all">Tất cả</button>
                ${categories.map(c => `
                    <button class="px-3 py-1 text-sm font-semibold bg-gray-200 text-gray-700 rounded-full whitespace-nowrap category-btn hover:bg-gray-300" data-category-id="${c.id}">${c.name}</button>
                `).join('')}
            `;
            const categoryButtons = document.querySelectorAll('.category-btn');
            categoryButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const categoryId = button.dataset.categoryId;
                    categoryButtons.forEach(btn => btn.classList.remove('active', 'bg-blue-600', 'text-white'));
                    button.classList.add('active', 'bg-blue-600', 'text-white');
                    
                    const filteredProducts = categoryId === 'all' 
                        ? initialProducts 
                        : initialProducts.filter(p => p.product.category_id == categoryId);
                    
                    renderProducts(filteredProducts);
                });
            });
        }
        
        function renderCart() {
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '';
                emptyCartMessage.classList.remove('hidden');
            } else {
                emptyCartMessage.classList.add('hidden');
                cartItemsContainer.innerHTML = cart.map((item, index) => {
                    const needsImei = item.product.has_serial_tracking;
                    const imeiComplete = !needsImei || item.imeis.length === item.quantity;

                    let imeiSection = '';
                    if (needsImei) {
                        imeiSection = `
                            <div class="col-span-12 mt-2 p-3 rounded-lg ${imeiComplete ? 'bg-green-50' : 'bg-yellow-50 border border-yellow-300'}">
                                <button class="add-imei-btn w-full text-left text-sm ${imeiComplete ? 'text-green-700' : 'text-yellow-800'} font-semibold" data-cart-index="${index}">
                                    ${imeiComplete ? `✓ Đã nhập đủ ${item.quantity} IMEI/Serial (Sửa)` : `+ Thêm IMEI (${item.imeis.length}/${item.quantity})`}
                                </button>
                                ${!imeiComplete ? `<p class="text-xs text-yellow-800 font-semibold mt-2">Cần nhập đủ ${item.quantity - item.imeis.length} IMEI/Serial.</p>` : ''}
                            </div>
                        `;
                    }

                    return `
                    <div class="p-3 rounded-lg cart-item bg-gray-50 border ${!needsImei || imeiComplete ? 'border-transparent' : 'border-yellow-400'}">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-5 flex items-center min-w-0">
                                <img src="${item.product.primary_image ? '/storage/' + item.product.primary_image.path : 'https://placehold.co/48x48/ccc/fff?text=Err'}" class="w-12 h-12 rounded-md object-cover mr-3 flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate" title="${item.product.product.name}">${item.product.product.name}</p>
                                    <p class="text-xs text-gray-500">${formatCurrency(item.product.price)}</p>
                                </div>
                            </div>
                            <div class="col-span-3 flex items-center justify-center space-x-1">
                                <button class="quantity-change w-7 h-7 bg-gray-200 rounded-full text-lg font-bold hover:bg-gray-300 flex items-center justify-center" data-index="${index}" data-change="-1">-</button>
                                <input type="text" value="${item.quantity}" class="w-8 text-center font-semibold border-none bg-transparent p-0" readonly>
                                <button class="quantity-change w-7 h-7 bg-gray-200 rounded-full text-lg font-bold hover:bg-gray-300 flex items-center justify-center" data-index="${index}" data-change="1">+</button>
                            </div>
                            <div class="col-span-3 text-right">
                                <p class="font-bold text-gray-900 text-sm">${formatCurrency(item.product.price * item.quantity)}</p>
                            </div>
                            <div class="col-span-1 text-right">
                                <button class="remove-item p-1 rounded-full hover:bg-red-100" data-index="${index}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            ${imeiSection}
                        </div>
                    </div>
                    `;
                }).join('');
            }
            updateSummary();
        }
        
        function updateSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
            const tax = subtotal * 0.08; 
            const discount = 0; 
            const grandTotal = subtotal + tax - discount;

            document.getElementById('summary-subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('summary-tax').textContent = formatCurrency(tax);
            document.getElementById('summary-discount').textContent = `- ${formatCurrency(discount)}`;
            document.getElementById('summary-grand-total').textContent = formatCurrency(grandTotal);

            checkoutButton.disabled = !isCartValid();
        }

        function addToCart(productObject) {
            if (!productObject) return;

            const stockQuantity = productObject.stock_quantity;
            if (stockQuantity <= 0) {
                alert('Sản phẩm này đã hết hàng.');
                return;
            }

            const existingItem = cart.find(item => item.product.id === productObject.id);
            if (existingItem) {
                if (existingItem.quantity + 1 > stockQuantity) {
                    alert(`Sản phẩm này chỉ còn ${stockQuantity} sản phẩm trong kho.`);
                    return;
                }
                existingItem.quantity++;
            } else {
                cart.push({
                    product: productObject,
                    quantity: 1,
                    imeis: [],
                    stock_quantity: stockQuantity
                });
            }
            renderCart();
        }

        function changeQuantity(index, change) {
            if (!cart[index]) return;
            const newQuantity = cart[index].quantity + change;
            const stockQuantity = cart[index].stock_quantity;
            
            if (newQuantity <= 0) {
                removeItem(index);
                return;
            }

            if (newQuantity > stockQuantity) {
                alert(`Sản phẩm này chỉ còn ${stockQuantity} sản phẩm trong kho.`);
                return;
            }
            
            if (newQuantity < cart[index].imeis.length) {
                alert('Vui lòng xóa bớt IMEI trước khi giảm số lượng.');
                return;
            }
            
            cart[index].quantity = newQuantity;
            renderCart();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            renderCart();
        }
        
        function clearCart() {
            cart = [];
            renderCart();
        }
        
        // --- SEARCH LOGIC ---
        async function fetchSearchProducts(term) {
            if (term.length < 2) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.classList.add('hidden');
                return;
            }
            try {
                const response = await fetch(`{{ route('pos.products.search') }}?term=${term}`);
                searchResults = await response.json();
                displaySuggestions(searchResults);
            } catch (error) {
                console.error('Search error:', error);
            }
        }

        function displaySuggestions(results) {
            if (results.length === 0) {
                suggestionsContainer.innerHTML = `<div class="p-3 text-center text-gray-500">Không tìm thấy sản phẩm.</div>`;
            } else {
                suggestionsContainer.innerHTML = results.map(p => `
                    <div class="flex items-center p-2 hover:bg-blue-50 cursor-pointer suggestion-item" data-product-id="${p.id}">
                        <img src="${p.primary_image ? '/storage/' + p.primary_image.path : 'https://placehold.co/40x40/ccc/fff?text=Err'}" class="w-10 h-10 object-cover rounded-md mr-3">
                        <div>
                            <div class="font-semibold text-gray-800">${p.product.name}</div>
                            <div class="text-xs text-gray-500">SKU: ${p.sku} | Tồn kho: ${p.stock_quantity}</div>
                        </div>
                    </div>
                `).join('');
            }
            suggestionsContainer.classList.remove('hidden');
        }

        // --- CUSTOMER LOGIC ---
        async function fetchCustomers(term) {
            if (term.length < 2) {
                customerSearchResults.classList.add('hidden');
                return;
            }
            try {
                const response = await fetch(`{{ route('pos.customers.search') }}?term=${term}`);
                const results = await response.json();
                displayCustomerSuggestions(results);
            } catch (error) {
                console.error('Customer search error:', error);
            }
        }

        function displayCustomerSuggestions(results) {
            if (results.length === 0) {
                customerSearchResults.innerHTML = `<div class="p-3 text-center text-gray-500">Không tìm thấy khách hàng.</div>`;
            } else {
                customerSearchResults.innerHTML = results.map(c => `
                    <div class="p-3 hover:bg-blue-50 cursor-pointer customer-suggestion-item border-b" data-customer-id="${c.id}">
                        <p class="font-semibold text-gray-800">${c.name}</p>
                        <p class="text-sm text-gray-500">${c.phone_number || 'Chưa có SĐT'}</p>
                    </div>
                `).join('');
            }
            customerSearchResults.classList.remove('hidden');
        }

        function selectCustomer(customer) {
            selectedCustomer = customer;
            customerSearchSection.classList.add('hidden');
            selectedCustomerInfo.classList.remove('hidden');
            customerSearchInput.value = '';
            customerSearchResults.classList.add('hidden');
            selectedCustomerInfo.innerHTML = `
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-bold text-blue-800">${customer.name}</p>
                            <p class="text-sm text-gray-600">${customer.phone_number}</p>
                        </div>
                        <button id="remove-customer-btn" class="text-red-500 hover:text-red-700 font-bold text-xl">&times;</button>
                    </div>
                </div>
            `;
            updateSummary();
        }

        function removeCustomer() {
            selectedCustomer = null;
            selectedCustomerInfo.classList.add('hidden');
            selectedCustomerInfo.innerHTML = '';
            customerSearchSection.classList.remove('hidden');
            customerSearchInput.value = '';
            updateSummary();
        }

        function openAddCustomerModal() {
            addCustomerModal.classList.remove('hidden');
        }

        function closeAddCustomerModal() {
            addCustomerForm.reset();
            addCustomerModal.classList.add('hidden');
        }

        // --- IMEI MODAL LOGIC ---
        function openImeiEntryModal(cartIndex) {
            currentCartItemIndexForImei = cartIndex;
            const item = cart[cartIndex];
            if (!item) return;

            imeiModalProductName.textContent = item.product.product.name;
            imeiInput.value = '';
            imeiError.classList.add('hidden');
            renderImeiList();
            imeiModal.classList.remove('hidden');
            setTimeout(() => imeiInput.focus(), 100);
        }

        function closeImeiEntryModal() {
            imeiModal.classList.add('hidden');
            currentCartItemIndexForImei = null;
            renderCart(); 
        }

        function renderImeiList() {
            const item = cart[currentCartItemIndexForImei];
            if (!item) return;

            imeiInputLabel.textContent = `Quét mã cho sản phẩm (${item.imeis.length}/${item.quantity})`;
            
            if (item.imeis.length === 0) {
                imeiListContainer.innerHTML = `<p id="imei-list-empty" class="text-sm text-gray-500">Chưa có mã nào được quét.</p>`;
            } else {
                imeiListContainer.innerHTML = item.imeis.map((imei, index) => `
                    <div class="flex items-center justify-between bg-white p-2 rounded border">
                        <span class="text-sm font-mono">${imei}</span>
                        <button class="remove-imei-from-list-btn text-red-500" data-imei-index="${index}">&times;</button>
                    </div>
                `).join('');
            }
            
            if (item.imeis.length >= item.quantity) {
                imeiInput.disabled = true;
                addImeiBtn.disabled = true;
                openImeiScannerBtn.disabled = true;
                imeiInput.placeholder = 'Đã nhập đủ số lượng';
            } else {
                imeiInput.disabled = false;
                addImeiBtn.disabled = false;
                openImeiScannerBtn.disabled = false;
                imeiInput.placeholder = 'Nhập tay hoặc quét barcode...';
            }
        }

        async function validateAndAddImei() {
            const serialNumber = imeiInput.value.trim();
            if (!serialNumber) {
                imeiInput.focus();
                return;
            }

            const item = cart[currentCartItemIndexForImei];
            if (!item || item.imeis.length >= item.quantity) return;

            const isDuplicateInCart = cart.some(cartItem => cartItem.imeis.includes(serialNumber));
            if(isDuplicateInCart) {
                imeiError.textContent = 'Lỗi: IMEI/Serial này đã được thêm vào giỏ hàng.';
                imeiError.classList.remove('hidden');
                return;
            }

            try {
                const response = await fetch('{{ route("pos.inventory.validateSerial") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        serial_number: serialNumber,
                        product_variant_id: item.product.id
                    })
                });

                if (response.ok) {
                    imeiError.classList.add('hidden');
                    item.imeis.push(serialNumber);
                    imeiInput.value = '';
                    renderImeiList();
                } else {
                    const errorData = await response.json();
                    imeiError.textContent = `Lỗi: ${errorData.message || 'IMEI không hợp lệ.'}`;
                    imeiError.classList.remove('hidden');
                }
            } catch (error) {
                imeiError.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
                imeiError.classList.remove('hidden');
            }
        }

        function removeImeiFromList(imeiIndex) {
            const item = cart[currentCartItemIndexForImei];
            if (item && item.imeis[imeiIndex]) {
                item.imeis.splice(imeiIndex, 1);
                renderImeiList();
            }
        }

        // --- BARCODE SCANNER LOGIC ---
        function openBarcodeScanner() {
            scannerModal.classList.remove('hidden');
            startScanning();
        }

        function closeBarcodeScanner() {
            stopScanning();
            scannerModal.classList.add('hidden');
        }

        function startScanning() {
            if (typeof Html5Qrcode === "undefined") {
                errorMessage.textContent = "Lỗi: Thư viện quét mã vạch chưa được tải.";
                errorMessage.classList.remove('hidden');
                return;
            }
            
            loadingMessage.classList.remove('hidden');
            errorMessage.classList.add('hidden');

            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 150 }, rememberLastUsedCamera: true };

            const onScanSuccess = (decodedText) => {
                closeBarcodeScanner();
                if (isScanningForImei) {
                    imeiInput.value = decodedText;
                    imeiModal.classList.remove('hidden');
                    imeiInput.focus();
                    validateAndAddImei();
                } else {
                    handleScannedCode(decodedText);
                }
                isScanningForImei = false;
            };

            html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
                .then(() => loadingMessage.classList.add('hidden'))
                .catch((err) => {
                    loadingMessage.classList.add('hidden');
                    let friendlyError = `Lỗi camera: ${err}.`;
                    if (String(err).includes("Permission denied")) {
                        friendlyError = "Bạn đã từ chối quyền truy cập camera. Vui lòng cấp quyền trong cài đặt trình duyệt và thử lại.";
                    } else if (location.protocol !== 'https:' && !['localhost', '127.0.0.1'].includes(location.hostname)) {
                        friendlyError += " Việc truy cập camera yêu cầu kết nối an toàn (HTTPS).";
                    }
                    errorMessage.textContent = friendlyError;
                    errorMessage.classList.remove('hidden');
                });
        }

        function stopScanning() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().catch(err => console.error("Lỗi khi dừng camera:", err));
            }
        }
        
        async function handleScannedCode(code) {
            let product = initialProducts.find(p => p.sku === code);
            if (!product) {
                try {
                    const response = await fetch(`{{ route('pos.products.search') }}?term=${code}`);
                    const results = await response.json();
                    if(results.length > 0) {
                        product = results[0]; 
                    }
                } catch (error) {
                    console.error('Error fetching scanned product:', error);
                }
            }

            if (product) {
                addToCart(product);
            } else {
                alert(`Không tìm thấy sản phẩm với mã SKU: ${code}`);
            }
        }

        // --- PAYMENT MODAL LOGIC ---
        function openPaymentModal() {
            const subtotal = cart.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
            const tax = subtotal * 0.08;
            const totalToPay = subtotal + tax;
            
            modalTotalAmount.textContent = formatCurrency(totalToPay);
            cashReceivedInput.value = '';
            changeAmountSpan.textContent = '0 VNĐ';

            paymentModal.classList.remove('opacity-0', 'pointer-events-none');
            paymentModal.querySelector('.modal-content').classList.remove('opacity-0', '-translate-y-10');
        }

        function closePaymentModal() {
            paymentModal.classList.add('opacity-0', 'pointer-events-none');
            paymentModal.querySelector('.modal-content').classList.add('opacity-0', '-translate-y-10');
        }

        async function submitSale() {
            confirmPaymentButton.disabled = true;
            confirmPaymentButton.textContent = 'Đang xử lý...';

            const saleData = {
                cart: cart,
                customer: selectedCustomer,
                payment: {
                    method: paymentMethodSelect.value,
                    amount_received: parseFloat(cashReceivedInput.value.replace(/[^0-9]/g, '')) || 0,
                }
            };

            try {
                const response = await fetch('{{ route("pos.sales.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(saleData)
                });

                const result = await response.json();

                if (response.ok) {
                    alert(`Thanh toán thành công!\nMã đơn hàng: ${result.order_code}`);
                    closePaymentModal();
                    clearCart();
                    removeCustomer();
                } else {
                    let errorMessage = result.message || 'Có lỗi xảy ra.';
                    if (result.errors) {
                        errorMessage += '\n' + Object.values(result.errors).map(e => e.join('\n')).join('\n');
                    }
                    alert(errorMessage);
                }

            } catch (error) {
                console.error('Lỗi khi gửi đơn hàng:', error);
                alert('Lỗi kết nối. Không thể hoàn tất thanh toán.');
            } finally {
                confirmPaymentButton.disabled = false;
                confirmPaymentButton.textContent = 'XÁC NHẬN';
            }
        }

        // --- EVENT LISTENERS ---
        productGrid.addEventListener('click', (e) => {
            const productItem = e.target.closest('.product-item');
            if (productItem) {
                const productId = parseInt(productItem.dataset.productId);
                const product = initialProducts.find(p => p.id === productId);
                if (product) {
                    addToCart(product);
                }
            }
        });
        
        barcodeScanButton.addEventListener('click', openBarcodeScanner);
        
        closeScannerBtn.addEventListener('click', () => {
            closeBarcodeScanner();
            if (isScanningForImei) {
                imeiModal.classList.remove('hidden');
                isScanningForImei = false;
            }
        });

        document.getElementById('clear-cart-button').addEventListener('click', clearCart);
        
        cartItemsContainer.addEventListener('click', (e) => {
            const quantityButton = e.target.closest('.quantity-change');
            const removeButton = e.target.closest('.remove-item');
            const addImeiButton = e.target.closest('.add-imei-btn');

            if (quantityButton) {
                const index = parseInt(quantityButton.dataset.index);
                const change = parseInt(quantityButton.dataset.change);
                changeQuantity(index, change);
            }
            if (removeButton) {
                const index = parseInt(removeButton.dataset.index);
                removeItem(index);
            }
            if (addImeiButton) {
                const index = parseInt(addImeiButton.dataset.cartIndex);
                openImeiEntryModal(index);
            }
        });

        let searchTimeout;
        productSearchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetchSearchProducts(productSearchInput.value);
            }, 300);
        });

        suggestionsContainer.addEventListener('click', (e) => {
            const item = e.target.closest('.suggestion-item');
            if (item) {
                const productId = parseInt(item.dataset.productId);
                const product = searchResults.find(p => p.id === productId);
                if (product) {
                    addToCart(product);
                }
                productSearchInput.value = '';
                suggestionsContainer.classList.add('hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (!productSearchInput.contains(e.target) && !suggestionsContainer.contains(e.target) && !barcodeScanButton.contains(e.target)) {
                suggestionsContainer.classList.add('hidden');
            }
        });

        // Customer events
        let customerSearchTimeout;
        customerSearchInput.addEventListener('input', () => {
            clearTimeout(customerSearchTimeout);
            customerSearchTimeout = setTimeout(() => {
                fetchCustomers(customerSearchInput.value);
            }, 300);
        });

        customerSearchResults.addEventListener('click', (e) => {
            const item = e.target.closest('.customer-suggestion-item');
            if (item) {
                const customerId = parseInt(item.dataset.customerId);
                const customer = {
                    id: customerId,
                    name: item.querySelector('.font-semibold').textContent,
                    phone_number: item.querySelector('.text-sm').textContent
                };
                selectCustomer(customer);
            }
        });
        
        selectedCustomerInfo.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('#remove-customer-btn');
            if (removeBtn) {
                removeCustomer();
            }
        });

        addNewCustomerBtn.addEventListener('click', openAddCustomerModal);
        cancelAddCustomerBtn.addEventListener('click', closeAddCustomerModal);
        addCustomerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(addCustomerForm);
            const newCustomer = {
                id: null, 
                name: formData.get('name'),
                phone_number: formData.get('phone'),
                email: formData.get('email')
            };
            selectCustomer(newCustomer);
            closeAddCustomerModal();
        });

        // IMEI Modal Listeners
        addImeiBtn.addEventListener('click', validateAndAddImei);
        imeiInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                validateAndAddImei();
            }
        });
        
        openImeiScannerBtn.addEventListener('click', () => {
            isScanningForImei = true;
            imeiModal.classList.add('hidden');
            openBarcodeScanner();
        });

        imeiModalDoneBtn.addEventListener('click', closeImeiEntryModal);
        imeiModalCloseBtnHeader.addEventListener('click', closeImeiEntryModal);
        
        imeiListContainer.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-imei-from-list-btn');
            if (removeBtn) {
                const imeiIndex = parseInt(removeBtn.dataset.imeiIndex);
                removeImeiFromList(imeiIndex);
            }
        });

        // Payment Modal Listeners
        checkoutButton.addEventListener('click', openPaymentModal);
        paymentModalCancelBtn.addEventListener('click', closePaymentModal);
        confirmPaymentButton.addEventListener('click', submitSale); // Gắn sự kiện click vào đây
        
        cashReceivedInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/[^0-9]/g, '');
            const received = parseFloat(value) || 0;

            e.target.value = new Intl.NumberFormat('vi-VN').format(received);

            const subtotal = cart.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
            const tax = subtotal * 0.08;
            const totalToPay = subtotal + tax;

            const change = received - totalToPay;
            changeAmountSpan.textContent = formatCurrency(change > 0 ? change : 0);
        });
        
        // --- INITIALIZATION ---
        renderCategories();
        renderProducts(initialProducts);
        renderCart();
    });
</script>
@endpush
