<div id="order-detail-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50 opacity-0 pointer-events-none">
    <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-2xl transform -translate-y-10">
        <div class="flex items-start justify-between p-5 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">Chi tiết Hóa đơn</h3>
                <p id="modal-order-code" class="font-mono text-blue-600"></p>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Ngày tạo:</p>
                    <p id="modal-order-date" class="font-semibold text-gray-800"></p>
                </div>
                 <div>
                    <p class="text-gray-500">Khách hàng:</p>
                    <p id="modal-order-customer" class="font-semibold text-gray-800"></p>
                </div>
                 <div>
                    <p class="text-gray-500">Nhân viên:</p>
                    <p id="modal-order-staff" class="font-semibold text-gray-800"></p>
                </div>
                 <div>
                    <p class="text-gray-500">Phương thức thanh toán:</p>
                    <p id="modal-order-payment" class="font-semibold text-gray-800"></p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Các sản phẩm đã mua</h4>
                <div id="modal-order-items" class="space-y-2">
                    {{-- Các sản phẩm sẽ được JavaScript render vào đây --}}
                </div>
            </div>
             <div class="border-t border-gray-200 pt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Tổng tiền hàng:</span>
                    <span id="modal-summary-subtotal" class="font-medium text-gray-800">0 VNĐ</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Giảm giá:</span>
                    <span id="modal-summary-discount" class="font-medium text-red-500">- 0 VNĐ</span>
                </div>
                <div class="flex justify-between items-center text-lg font-bold text-gray-900 mt-2">
                    <span>Tổng Cộng:</span>
                    <span id="modal-summary-grand-total">0 VNĐ</span>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end p-5 border-t border-gray-200 rounded-b">
            <button onclick="closeModal()" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 mr-2">Đóng</button>
            <button type="button" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                In lại hóa đơn
            </button>
        </div>
    </div>
</div>