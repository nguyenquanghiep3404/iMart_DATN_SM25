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

<!-- Add Customer Modal -->
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
                        <!-- === THAY ĐỔI Ở ĐÂY === -->
                        <label for="customer-email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="customer-email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
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