<!-- Modal chi tiết đơn hàng -->
<div id="order-detail-modal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <!-- Lớp phủ nền đen mờ -->
    <div class="modal-overlay absolute inset-0 bg-black bg-opacity-60"></div>
    
    <!-- Nội dung Modal -->
    <div class="modal-content w-full max-w-3xl bg-white rounded-2xl shadow-lg p-6 md:p-8 space-y-6 transform max-h-[90vh] overflow-y-auto">
        
        <!-- Header của Modal -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-500 text-white p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Xử lý Đơn hàng</h2>
            </div>
            <button class="close-modal-btn text-gray-400 hover:text-gray-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Nội dung chi tiết -->
        <div id="modal-content-body" class="space-y-6">
            <!-- Loading state -->
            <div id="modal-loading" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                <p class="mt-4 text-gray-600">Đang tải thông tin đơn hàng...</p>
            </div>

            <!-- Content will be loaded here -->
            <div id="modal-order-content" class="hidden space-y-6">
                <!-- Thông tin chi tiết đơn hàng -->
                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <h3 class="text-lg font-semibold text-gray-700">Thông tin chung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm text-gray-600">
                        <div><strong>Mã đơn hàng:</strong> <span id="modal-order-id" class="font-mono text-blue-600"></span></div>
                        <div><strong>Mã vận đơn:</strong> <span id="modal-package-id" class="font-mono text-purple-600"></span></div>
                        <div id="shipping-unit-info" style="display: none;"><strong>Đơn vị vận chuyển:</strong> <span id="modal-shipping-unit-name" class="font-mono text-green-600"></span></div>
                        <div><strong>Tên khách hàng:</strong> <span id="modal-customer-name"></span></div>
                        <div class="col-span-1 md:col-span-2"><strong>Địa chỉ:</strong> <span id="modal-address"></span></div>
                        <div class="flex items-center">
                            <strong>Trạng thái:</strong> 
                            <span id="modal-order-status" class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Chờ xử lý</span>
                        </div>
                    </div>
                </div>

                <!-- Sản phẩm trong gói hàng -->
                <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-700">Sản phẩm trong gói hàng</h3>
                    <!-- Header bảng sản phẩm -->
                    <div class="hidden md:grid grid-cols-12 gap-4 text-xs font-bold text-gray-500 uppercase px-4">
                        <div class="col-span-6">Sản phẩm</div>
                        <div class="col-span-2 text-center">Số lượng</div>
                        <div class="col-span-2 text-right">Đơn giá</div>
                        <div class="col-span-2 text-right">Thành tiền</div>
                    </div>
                    <!-- Danh sách sản phẩm -->
                    <div id="modal-products" class="space-y-4">
                        <!-- Products will be loaded here -->
                    </div>
                </div>

                <!-- Chọn đơn vị vận chuyển -->
                <div class="space-y-2">
                    <label for="modal-shipping-unit" class="block text-lg font-semibold text-gray-700">Chọn Đơn vị Vận chuyển</label>
                    <select id="modal-shipping-unit" class="block w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>-- Vui lòng chọn --</option>
                        <option value="GHN">Giao Hàng Nhanh (GHN)</option>
                        <option value="GHTK">Giao Hàng Tiết Kiệm (GHTK)</option>
                        <option value="ViettelPost">Viettel Post</option>
                    </select>
                </div>

                <!-- Các nút hành động -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-3 pt-4">
                    <button id="modal-assign-btn" class="w-full md:w-auto bg-blue-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-blue-700 disabled:bg-gray-400">Giao cho ĐVVC</button>
                    <button id="modal-success-btn" class="w-full md:w-auto bg-green-600 text-white font-bold py-2 px-5 rounded-lg hover:bg-green-700 disabled:bg-gray-400" disabled>Đánh dấu Giao thành công</button>
                </div>
                
                <!-- Thông báo -->
                <div id="modal-success-message" class="hidden mt-4 p-4 bg-green-100 text-green-800 rounded-lg text-center"></div>
                <div id="modal-error-message" class="hidden mt-4 p-4 bg-red-100 text-red-800 rounded-lg text-center"></div>
            </div>
        </div>
    </div>
</div>