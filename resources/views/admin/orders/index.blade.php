@extends('admin.layouts.app')

@section('content')
<style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f8f9fa;
        }
        .modal {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .modal:not(.is-open) {
            opacity: 0;
            visibility: hidden;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .status-pending_confirmation { background-color: #e0e7ff; color: #4338ca; }
        .status-processing { background-color: #cffafe; color: #0891b2; }
        .status-awaiting_shipment_packed { background-color: #fef3c7; color: #d97706; }
        .status-awaiting_shipment_assigned { background-color: #ddd6fe; color: #7c3aed; }
        .status-shipped { background-color: #d1fae5; color: #059669; }
        .status-delivered { background-color: #dcfce7; color: #16a34a; }
        .status-cancelled { background-color: #fee2e2; color: #dc2626; }
        .payment-pending { background-color: #fef3c7; color: #d97706; }
        .payment-paid { background-color: #dcfce7; color: #16a34a; }
        .payment-failed { background-color: #fee2e2; color: #dc2626; }
        
        /* Thanh cuộn tùy chỉnh cho modal */
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }
        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .modal-content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .modal-content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Kiểu phân trang */
        #pagination-controls button {
            transition: all 0.2s ease;
        }
        #pagination-controls button:hover:not(:disabled) {
            transform: translateY(-1px);
            shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        /* Kiểu thông báo (toast) - Giao diện giống mẫu */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 500px;
        }
        .toast {
            background: #10b981;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
            padding: 16px 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            transform: translateX(520px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .toast::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(0, 0, 0, 0.1);
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast.success {
            background: #10b981;
        }
        
        .toast.error {
            background: #ef4444;
        }
        
        .toast.warning {
            background: #f59e0b;
        }
        
        .toast-icon {
            width: 32px;
            height: 32px;
            margin-right: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #10b981;
            font-size: 16px;
            font-weight: 600;
            flex-shrink: 0;
            background: white;
        }
        
        .toast.success .toast-icon {
            color: #10b981;
            background: white;
        }
        
        .toast.error .toast-icon {
            color: #ef4444;
            background: white;
        }
        
        .toast.warning .toast-icon {
            color: #f59e0b;
            background: white;
        }
        
        .toast-content {
            flex: 1;
            min-width: 0;
        }
        
        .toast-message {
            font-size: 15px;
            color: white;
            line-height: 1.4;
            font-weight: 500;
        }
        
        .toast-close {
            margin-left: 16px;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        
        .toast-close:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Animation cho toast */
        @keyframes toastSlideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes toastSlideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .toast.show {
            animation: toastSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        .toast.hide {
            animation: toastSlideOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Kiểu cho sản phẩm */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .product-image-placeholder {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #9ca3af;
        }
        
        /* Kiểu bảng trong modal nâng cao */
        #modal-order-items tr:hover {
            background-color: #f9fafb;
        }
        
        #modal-order-items img {
            transition: transform 0.2s ease;
        }
        
        #modal-order-items img:hover {
            transform: scale(1.05);
        }
        
        /* Bảng trong modal đáp ứng trên thiết bị nhỏ */
        @media (max-width: 768px) {
            .modal-content table {
                font-size: 14px;
            }
            
            .modal-content .w-16.h-16 {
                width: 48px;
                height: 48px;
            }
        }

        /* Đánh dấu đơn hàng mới */
        .new-order-row {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid #0ea5e9;
            font-weight: 600;
            position: relative;
        }

        .new-order-row:hover {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        }

        .new-order-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 9999px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }
        /* Làm chữ đơn hàng mới đậm hơn */
        .new-order-row .font-bold {
            font-weight: 800;
        }
        .new-order-row .font-semibold {
            font-weight: 700;
        }
        /* Progress Bar Styles - Beautiful Design */
        .order-progress-section {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
            margin-bottom: 40px;
        }
                 .order-progress-title {
             font-size: 20px;
             font-weight: 700;
             color: #1e293b;
             margin-bottom: 32px;
             text-align: center;
         }
        .progress-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            padding: 0;
        }
        .progress-line {
            position: absolute;
            top: 50%;
            left: 24px;
            right: 24px;
            height: 3px;
            background: #e2e8f0;
            border-radius: 2px;
            z-index: 1;
            transform: translateY(-50%);
        }
        .progress-line-filled {
            height: 100%;
            background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
            border-radius: 2px;
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
            min-width: 0;
        }
        .progress-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 3px solid #e2e8f0;
            background: #ffffff;
            color: #9ca3af;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .progress-step.completed .progress-icon {
            background: #22c55e;
            border-color: #22c55e;
            color: white;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }
        .progress-step.current .progress-icon {
            background: #6366f1;
            border-color: #6366f1;
            color: white;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
            transform: scale(1.05);
        }
        .progress-step.cancelled .progress-icon {
            background: #ef4444;
            border-color: #ef4444;
            color: white;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }
        .progress-step.failed .progress-icon {
            background: #ef4444;
            border-color: #ef4444;
            color: white;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }
        .progress-label {
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            color: #9ca3af;
            max-width: 120px;
            line-height: 1.3;
            margin-top: 4px;
            word-wrap: break-word;
            hyphens: auto;
        }
        .progress-step.completed .progress-label {
            color: #22c55e;
            font-weight: 700;
        }
        .progress-step.current .progress-label {
            color: #6366f1;
            font-weight: 700;
        }
        .progress-step.cancelled .progress-label {
            color: #ef4444;
            font-weight: 700;
        }
        .progress-step.failed .progress-label {
            color: #ef4444;
            font-weight: 700;
        }
        /* Responsive design cho progress bar */
        @media (max-width: 768px) {
            .order-progress-section {
                padding: 24px 16px;
                margin-bottom: 24px;
            }
            .order-progress-title {
                font-size: 18px;
                margin-bottom: 24px;
            }
            .progress-container {
                flex-direction: column;
                gap: 16px;
                padding: 0;
            }
            
            .progress-line {
                display: none;
            }
            .progress-step {
                flex-direction: row;
                justify-content: flex-start;
                width: 100%;
                align-items: center;
                padding: 16px;
                border-radius: 12px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                transition: all 0.3s ease;
            }
            .progress-step.completed {
                background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
                border-color: #22c55e;
            }
            .progress-step.current {
                background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
                border-color: #6366f1;
                box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
            }
            .progress-step.cancelled {
                background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
                border-color: #ef4444;
            }
            .progress-step.failed {
                background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
                border-color: #ef4444;
            }
            .progress-icon {
                margin-right: 16px;
                margin-bottom: 0;
                width: 44px;
                height: 44px;
                font-size: 16px;
                flex-shrink: 0;
            }
            .progress-step.current .progress-icon {
                transform: scale(1.0);
            }
            .progress-label {
                max-width: none;
                text-align: left;
                flex: 1;
                margin-top: 0;
                font-size: 14px;
                line-height: 1.4;
            }
        }
        /* Cải thiện hiển thị trên màn hình nhỏ hơn */
        @media (max-width: 480px) {
            .order-progress-section {
                padding: 20px 12px;
            }
            
            .progress-step {
                padding: 12px;
            }
            
            .progress-icon {
                width: 40px;
                height: 40px;
                font-size: 14px;
                margin-right: 12px;
            }
            
            .progress-label {
                font-size: 13px;
            }
        }
</style>
    <div class="max-w-screen-2xl mx-auto">
        <header class="mb-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Quản Lý Đơn Hàng</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Danh Sách</li>
                    </ol>
                </nav>
            </div>
        </header>

        <!-- Filter Section -->
        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Bộ lọc đơn hàng</h3>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </span>
                        <input type="text" id="search" placeholder="Mã ĐH, Tên, Email, SĐT..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="order-status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái đơn hàng</label>
                    <select id="order-status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tất cả</option>
                        <option value="pending_confirmation">Chờ xác nhận</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="awaiting_shipment">Chờ giao hàng</option>
                        <option value="shipped">Đã xuất kho</option>
                        <option value="out_for_delivery">Đang giao hàng</option>
                        <option value="delivered">Giao thành công</option>
                        <option value="cancelled">Đã hủy</option>
                        <option value="returned">Đã trả hàng</option>
                        <option value="failed_delivery">Giao hàng thất bại</option>
                    </select>
                </div>
                 <div>
                    <label for="payment-status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái thanh toán</label>
                    <select id="payment-status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tất cả</option>
                        <option value="pending">Chờ thanh toán</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="failed">Thất bại</option>
                        <option value="refunded">Đã hoàn tiền</option>
                        <option value="partially_refunded">Hoàn tiền một phần</option>
                    </select>
                </div>
                <div>
                    <label for="from-date" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                    <input type="date" id="from-date" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="to-date" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                    <input type="date" id="to-date" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="new-orders-only" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2">
                        <span class="ml-2 text-sm font-medium text-gray-700">Chỉ xem đơn hàng trong 24h</span>
                        <span id="new-orders-count" class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full hidden">0</span>
                    </label>
                </div>
                <div class="flex space-x-3">
                    <button id="refresh-orders" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Làm mới</span>
                    </button>
                    <button id="clear-filters" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Xóa lọc</button>
                    <button id="apply-filters" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>Áp dụng</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="p-6">Mã ĐH</th>
                        <th scope="col" class="p-6">Khách hàng</th>
                        <th scope="col" class="p-6">Tổng tiền</th>
                        <th scope="col" class="p-6">Trạng thái ĐH</th>
                        <th scope="col" class="p-6">TT Thanh toán</th>
                        <th scope="col" class="p-6">Ngày tạo</th>
                        <th scope="col" class="p-6 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <!-- Order rows will be inserted here by JavaScript -->
                </tbody>
            </table>
            <div id="pagination" class="p-6 flex justify-between items-center">
                <div id="pagination-info" class="text-sm text-gray-700">
                    <!-- Pagination info will be inserted here -->
                </div>
                <div id="pagination-controls" class="flex items-center space-x-2">
                    <!-- Pagination controls will be inserted here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Detail Modal -->
    <div id="order-detail-modal" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-6xl h-full max-h-[95vh] flex flex-col transform transition-transform duration-300 scale-95">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Chi tiết đơn hàng: <span id="modal-order-code" class="text-indigo-600"></span></h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times fa-2x"></i>
                </button>
            </div>
            <div class="p-8 flex-grow overflow-y-auto modal-content">
                <!-- Progress Bar Trạng thái đơn hàng -->
                <div id="order-progress-bar" class="order-progress-section">
                    <!--Nội dung được chèn -->
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!--  Khách hàng & Địa chỉ -->
                    <div class="lg:col-span-1 space-y-6">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Thông tin khách hàng</h3>
                            <div class="space-y-2">
                                <p><strong>Tên:</strong> <span id="modal-customer-name"></span></p>
                                <p><strong>Email:</strong> <span id="modal-customer-email"></span></p>
                                <p><strong>SĐT:</strong> <span id="modal-customer-phone"></span></p>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Địa chỉ giao hàng</h3>
                            <address class="not-italic text-gray-700 leading-relaxed" id="modal-shipping-address"></address>
                        </div>
                        
                        <!-- Thông tin cửa hàng nhận hàng -->
                        <div id="modal-store-info" class="hidden">
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Cửa hàng nhận hàng</h3>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-store text-green-600"></i>
                                    <span class="font-medium text-green-800">Thông tin cửa hàng:</span>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <p><strong>Tên cửa hàng:</strong> <span id="modal-store-name"></span></p>
                                    <p><strong>Địa chỉ:</strong> <span id="modal-store-address"></span></p>
                                    <p><strong>Số điện thoại:</strong> <span id="modal-store-phone"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin shipper -->
                        <div id="modal-shipper-info" class="hidden">
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Thông tin shipper</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-shipping-fast text-blue-600"></i>
                                    <span class="font-medium text-blue-800">Shipper được gán:</span>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <p><strong>Tên:</strong> <span id="modal-shipper-name"></span></p>
                                    <p><strong>Email:</strong> <span id="modal-shipper-email"></span></p>
                                    <p><strong>SĐT:</strong> <span id="modal-shipper-phone"></span></p>
                                </div>
                            </div>
                        </div>

                         <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Ghi chú</h3>    
                            <!-- Ghi chú từ khách hàng -->
                            <div id="customer-notes-section" class="mb-4 hidden">
                                <h4 class="font-medium text-gray-700 mb-2">Ghi chú từ khách hàng:</h4>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-blue-800" id="modal-customer-notes"></p>
                                </div>
                            </div>
                            <!-- Ghi chú từ admin -->
                            <div id="admin-notes-section" class="mb-4 hidden">
                                <h4 class="font-medium text-gray-700 mb-2">Ghi chú từ admin:</h4>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-green-800" id="modal-admin-notes"></p>
                                </div>
                            </div>
                            <!-- Ghi chú dành cho shipper -->
                            <div id="shipper-notes-section" class="mb-4 hidden">
                                <h4 class="font-medium text-gray-700 mb-2">Ghi chú dành cho shipper:</h4>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <p class="text-yellow-800" id="modal-shipper-notes"></p>
                                </div>
                            </div>
                        </div>
                        <!-- Thông tin lý do hủy/thất bại -->
                        <div id="modal-cancellation-info" class="hidden">
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Lý do hủy/thất bại</h3>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div>
                                    <p class="text-red-800 font-medium mb-1" id="modal-cancellation-title">Lý do hủy đơn hàng:</p>
                                    <p class="text-red-700" id="modal-cancellation-reason">Không có thông tin.</p>
                                    <p class="text-red-600 text-sm mt-2" id="modal-cancellation-date"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Order Info & Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="font-bold text-lg text-gray-800 mb-6">Tổng quan đơn hàng</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                <div>
                                        <p class="text-sm text-gray-500 mb-1">Ngày đặt</p>
                                    <p class="font-semibold text-gray-800" id="modal-order-date"></p>
                                </div>
                                <div>
                                        <p class="text-sm text-gray-500 mb-1">Trạng thái đơn hàng</p>
                                    <span id="modal-order-status" class="status-badge"></span>
                                    </div>
                                    <!-- Thông tin thời gian giao hàng -->
                                    <div id="modal-delivery-time-info" class="hidden">
                                        <p class="text-sm text-gray-500 mb-1">Ngày nhận hàng mong muốn</p>
                                        <p class="font-semibold text-gray-800" id="modal-desired-date"></p>
                                    </div>
                                    <!-- Thông tin mã giảm giá -->
                                    <div id="modal-coupon-info" class="hidden">
                                        <p class="text-sm text-gray-500 mb-1">Mã giảm giá đã sử dụng</p>
                                        <div class="flex items-center space-x-2">
                                            <span id="modal-coupon-code" class="font-semibold text-indigo-600"></span>
                                            <span id="modal-coupon-discount" class="text-sm text-red-600 font-medium"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                <div>
                                        <p class="text-sm text-gray-500 mb-1">Trạng thái thanh toán</p>
                                    <span id="modal-payment-status" class="status-badge"></span>
                                </div>
                                <div>
                                        <p class="text-sm text-gray-500 mb-1">Phương thức thanh toán</p>
                                    <p class="font-semibold text-gray-800" id="modal-payment-method"></p>
                                    </div>
                                    <!-- Thông tin khung giờ giao hàng -->
                                    <div id="modal-delivery-slot-info" class="hidden">
                                        <p class="text-sm text-gray-500 mb-1">Khung giờ nhận hàng</p>
                                        <p class="font-semibold text-gray-800" id="modal-desired-time-slot"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin các gói hàng (Fulfillments) -->
                        <div id="modal-fulfillments-section" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6" style="display: none;">
                            <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-boxes text-blue-600 mr-2"></i>
                                Thông tin các gói hàng
                            </h3>
                            <div id="modal-fulfillments-list" class="space-y-4">
                                <!-- Danh sách fulfillments sẽ được chèn vào đây -->
                            </div>
                        </div>




                    </div>
                </div>
            </div>
             <div class="p-4 bg-gray-50 border-t flex justify-end space-x-3">
                <button onclick="closeModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Đóng</button>
                <button class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center space-x-2">
                     <i class="fas fa-print"></i>
                    <span>In hóa đơn</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>



    <!-- Update Status Modal -->
    <div id="update-status-modal" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-transform duration-300 scale-95">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Cập nhật trạng thái đơn hàng</h2>
                <p class="text-sm text-gray-600 mt-1">Mã đơn hàng: <span id="update-order-code" class="font-medium text-indigo-600"></span></p>
            </div>
            <form id="update-status-form" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label for="new-status" class="block text-sm font-medium text-gray-700 mb-2">Trạng thái mới</label>
                        <select id="new-status" name="status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="pending_confirmation">Chờ xác nhận</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="out_for_delivery" class="delivery-only">Đang giao hàng</option>
                            <option value="delivered">Giao hàng thành công</option>
                            <option value="cancelled">Hủy</option>
                            <option value="failed_delivery" class="delivery-only">Giao hàng thất bại</option>
                            <option value="returned">Trả hàng</option>
                        </select>
                    </div>
                    <div>
                        <label for="admin-note" class="block text-sm font-medium text-gray-700 mb-2">Ghi chú (tùy chọn)</label>
                        <textarea id="admin-note" name="admin_note" rows="3" 
                                  class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Thêm ghi chú về việc thay đổi trạng thái..."></textarea>
                    </div>
                    <div id="cancellation-reason-field" style="display: none;">
                        <label for="cancellation-reason" class="block text-sm font-medium text-gray-700 mb-2">Lý do hủy đơn <span class="text-red-500">*</span></label>
                        <textarea id="cancellation-reason" name="cancellation_reason" rows="3" 
                                  class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Nhập lý do hủy đơn hàng..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeUpdateStatusModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                        Hủy
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium flex items-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span>Cập nhật</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // --- CẤU HÌNH ---
    const CONFIG = {
        routes: {
            index: '{{ route("admin.orders.index") }}',
            show: '{{ route("admin.orders.show", ":id") }}',
            updateStatus: '{{ route("admin.orders.updateStatus", ":id") }}',
            getShippers: '{{ route("admin.orders.shippers") }}',
            assignShipper: '{{ route("admin.orders.assignShipper", ":id") }}',
        },
        csrfToken: '{{ csrf_token() }}'
    };

    // Trạng thái phân trang toàn cục
    let currentPage = 1;
    let totalPages = 1;

    // --- HÀM TIỆN ÍCH ---
    const formatCurrency = (amount) => {
        const formatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        return formatted.replace('₫', 'VNĐ');
    };
    
    // --- HÀM TẠO PROGRESS BAR ---
    function createOrderProgressBar(currentStatus, fulfillments = null) {
        // Định nghĩa các bước tiến trình chính
        const progressSteps = [
            { status: 'pending_confirmation', label: 'Chờ xác nhận', icon: '<i class="fas fa-clipboard-check"></i>' },
            { status: 'processing', label: 'Đang xử lý', icon: '<i class="fas fa-cogs"></i>' },
            { status: 'out_for_delivery', label: 'Đang giao hàng', icon: '<i class="fas fa-truck"></i>' },
            { status: 'delivered', label: 'Giao hàng thành công', icon: '<i class="fas fa-check-circle"></i>' }
        ];
        // Xác định vị trí của trạng thái hiện tại trong mảng
        const currentStepIndex = progressSteps.findIndex(step => step.status === currentStatus);
        // Xử lý trạng thái "đã hủy hoặc giao hàng thất bại"
        let stepsToShow = [...progressSteps]; // luôn hiển thị đủ trạng thái
        let progressTitle = 'Tiến trình đơn hàng';
        let progressPercentage = 0;
        if (currentStatus === 'cancelled') {
            // Trạng thái bị hủy - thay thế bước thích hợp bằng "Đã hủy"
            let cancelledStepIndex = Math.max(0, currentStepIndex >= 0 ? currentStepIndex : 1); // Nếu không tìm thấy, mặc định là bước 2
            // Thay thế bước tại vị trí hiện tại bằng "Đã hủy"
            stepsToShow[cancelledStepIndex] = { status: 'cancelled', label: 'Đã hủy', icon: '<i class="fas fa-times-circle"></i>' };
            progressTitle = 'Đơn hàng đã bị hủy';
            progressPercentage = (cancelledStepIndex / (stepsToShow.length - 1)) * 100;
        } else if (currentStatus === 'failed_delivery') {
            // Trạng thái giao hàng thất bại - thay thế bước "Giao thành công" bằng "Giao hàng thất bại"
            stepsToShow[3] = { status: 'failed_delivery', label: 'Giao hàng thất bại', icon: '<i class="fas fa-exclamation-triangle"></i>' };
            progressTitle = 'Giao hàng thất bại';
            progressPercentage = (3 / (stepsToShow.length - 1)) * 100;
        } else if (currentStatus === 'returned') {
            // Trạng thái trả hàng - thay thế bước "Giao thành công" bằng "Đã trả hàng"
            stepsToShow[3] = { status: 'returned', label: 'Đã trả hàng', icon: '<i class="fas fa-undo-alt"></i>' };
            progressTitle = 'Đơn hàng đã được trả lại';
            progressPercentage = (3 / (stepsToShow.length - 1)) * 100;
        } else {
            // Trạng thái bình thường - tính toán phần trăm tiến trình
            if (currentStepIndex >= 0) {
                progressPercentage = (currentStepIndex / (stepsToShow.length - 1)) * 100;
            }
            // Tùy chỉnh tiêu đề theo trạng thái
            if (currentStatus === 'delivered') {
                progressTitle = 'Đơn hàng đã được giao thành công';
            } else if (currentStatus === 'out_for_delivery') {
                progressTitle = 'Đơn hàng đang được giao';
            } else if (currentStatus === 'processing') {
                progressTitle = 'Đơn hàng đang được xử lý';
            } else if (currentStatus === 'pending_confirmation') {
                progressTitle = 'Đơn hàng đang chờ xác nhận';
            }
        }
                // Tạo HTML cho progress bar
                        const progressBarHTML = `
            <div class="order-progress-title">${progressTitle}</div>
            <div class="progress-container">
                <div class="progress-line">
                    <div class="progress-line-filled" style="width: ${progressPercentage}%"></div>
                </div>
                ${stepsToShow.map((step, index) => {
                    let stepClass = 'progress-step';
                    // Xác định trạng thái của từng bước
                    if (step.status === currentStatus) {
                        // Bước hiện tại - kiểm tra loại trạng thái để gán màu đúng
                        if (currentStatus === 'cancelled') {
                            stepClass += ' cancelled';
                        } else if (currentStatus === 'failed_delivery') {
                            stepClass += ' failed';
                        } else if (currentStatus === 'returned') {
                            stepClass += ' cancelled';
                        } else {
                            stepClass += ' current';
                        }
                    } else if (step.status === 'cancelled') {
                        // Bước hủy
                        stepClass += ' cancelled';
                    } else if (step.status === 'failed_delivery') {
                        // Bước thất bại
                        stepClass += ' failed';
                    } else if (step.status === 'returned') {
                        // Bước trả hàng
                        stepClass += ' cancelled'; // Sử dụng style tương tự cancelled
                    } else {
                        // Kiểm tra xem bước này đã hoàn thành chưa
                        const stepIndex = progressSteps.findIndex(s => s.status === step.status);
                        
                        // Đối với trạng thái bình thường
                        if (currentStepIndex >= 0 && stepIndex >= 0 && stepIndex < currentStepIndex) {
                            stepClass += ' completed';
                        }
                        
                        // Đối với trạng thái đặc biệt (cancelled, failed_delivery, returned)
                        if (currentStatus === 'cancelled' || currentStatus === 'failed_delivery' || currentStatus === 'returned') {
                            const currentSpecialIndex = stepsToShow.findIndex(s => s.status === currentStatus);
                            
                            // Nếu bước này xuất hiện trước bước hiện tại trong danh sách hiển thị
                            if (index < currentSpecialIndex) {
                                stepClass += ' completed';
                            }
                        }
                    }
                    return `
                        <div class="${stepClass}">
                            <div class="progress-icon">
                                ${step.icon}
                            </div>
                            <div class="progress-label">
                                ${step.label}
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        
        // Thêm thông tin fulfillments nếu có
        if (fulfillments && fulfillments.length > 0) {
            const fulfillmentsInfo = `
                <div class="fulfillments-progress mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-shipping-fast text-blue-600 mr-2"></i>
                        Trạng thái các gói hàng (${fulfillments.length} gói)
                    </h4>
                    <div class="space-y-3">
                        ${fulfillments.map((fulfillment, index) => {
                            const statusMap = {
                                'pending_confirmation': { text: 'Chờ xác nhận', class: 'bg-indigo-100 text-indigo-800 border-indigo-200' },
                                'processing': { text: 'Đang xử lý', class: 'bg-blue-100 text-blue-800 border-blue-200' },
                                'packed': { text: 'Chờ vận chuyển: đã đóng gói xong', class: 'bg-yellow-100 text-yellow-800 border-yellow-200' },
                                'awaiting_shipment_assigned': { text: 'Đã gán shipper: chờ vận chuyển', class: 'bg-cyan-100 text-cyan-800 border-cyan-200' },
                                'out_for_delivery': { text: 'Đang giao hàng', class: 'bg-purple-100 text-purple-800 border-purple-200' },
                                'delivered': { text: 'Giao hàng thành công', class: 'bg-green-100 text-green-800 border-green-200' },
                                'cancelled': { text: 'Hủy', class: 'bg-red-100 text-red-800 border-red-200' },
                                'failed_delivery': { text: 'Giao thất bại', class: 'bg-red-100 text-red-800 border-red-200' },
                                'returned': { text: 'Trả hàng', class: 'bg-gray-100 text-gray-800 border-gray-200' }
                            };
                            
                            // Nếu đơn hàng đang ở trạng thái 'processing', tất cả gói hàng sẽ hiển thị trạng thái 'Đang xử lý'
                            let displayStatus = fulfillment.status;
                            if (currentStatus === 'processing') {
                                displayStatus = 'processing';
                            }
                            
                            const status = statusMap[displayStatus] || { text: displayStatus, class: 'bg-gray-100 text-gray-800 border-gray-200' };
                            const store = fulfillment.store_location;
                            
                            return `
                                <div class="flex items-center justify-between p-3 border rounded-lg ${status.class}">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-box text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-sm">
                                                Gói #${index + 1} - ${store ? store.name : 'Kho không xác định'}
                                            </div>
                                            ${fulfillment.tracking_code ? 
                                                `<div class="text-xs font-mono text-blue-600 mt-1">${fulfillment.tracking_code}</div>` : 
                                                '<div class="text-xs text-gray-500 mt-1">Chưa có mã vận đơn</div>'
                                            }
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                            ${status.text}
                                        </span>
                                        ${fulfillment.shipped_at ? 
                                            `<div class="text-xs text-gray-600 mt-1">Gửi: ${formatDate(fulfillment.shipped_at)}</div>` : ''
                                        }
                                        ${fulfillment.delivered_at ? 
                                            `<div class="text-xs text-gray-600 mt-1">Giao: ${formatDate(fulfillment.delivered_at)}</div>` : ''
                                        }
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
            
            return progressBarHTML + fulfillmentsInfo;
        }
        
        return progressBarHTML;
	}
    
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const formatDateTime = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    };

    const formatDeliveryDate = (dateString) => {
        // Xử lý trường hợp ngày có thể là string hoặc date
        if (!dateString) return 'N/A';
        
        // Nếu đã là định dạng dd/mm/yyyy thì trả về luôn
        if (typeof dateString === 'string' && dateString.includes('/')) {
            return dateString;
        }
        
        // Nếu là ISO date hoặc timestamp thì format lại
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return dateString; // Trả về nguyên bản nếu không parse được
        }
        
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    // Theo dõi đơn hàng đã xem (đơn đã cập nhật trạng thái)
    let viewedOrders = new Set(JSON.parse(localStorage.getItem('viewedOrders') || '[]'));

    // Kiểm tra đơn hàng mới: chỉ dựa vào việc đã 'được xử lý/xem' hay chưa
    const isNewOrder = (createdAt, orderId) => {
        // Force JavaScript tính toán viewedOrders để tránh tối ưu hóa
        const viewedOrdersArray = Array.from(viewedOrders);
        const isNew = !viewedOrders.has(orderId);
        return isNew;
    };

    // Đánh dấu đơn hàng đã xem
    const markOrderAsViewed = (orderId) => {
        viewedOrders.add(orderId);
        localStorage.setItem('viewedOrders', JSON.stringify([...viewedOrders]));
    };

    // Cập nhật số lượng đơn hàng mới
    const updateNewOrdersCount = (orders) => {
        const newOrdersCount = orders.filter(order => isNewOrder(order.created_at, order.id)).length;
        const countElement = document.getElementById('new-orders-count');
        
        if (newOrdersCount > 0) {
            countElement.textContent = newOrdersCount;
            countElement.classList.remove('hidden');
            
            // Thay đổi màu dựa trên số lượng
            countElement.classList.remove('bg-red-100', 'text-red-800', 'bg-orange-500', 'text-white', 'bg-red-500');
            if (newOrdersCount >= 10) {
                countElement.classList.add('bg-red-500', 'text-white');
            } else if (newOrdersCount >= 5) {
                countElement.classList.add('bg-orange-500', 'text-white');
            } else {
                countElement.classList.add('bg-red-100', 'text-red-800');
            }
        } else {
            countElement.classList.add('hidden');
        }
    };

    const statusMap = {
        pending_confirmation: { text: "Chờ xác nhận", class: "status-pending_confirmation" },
        processing: { text: "Đang xử lý", class: "status-processing" },
        awaiting_shipment: { text: "Chờ giao hàng", class: "status-processing" },
        awaiting_shipment_packed: { text: "Chờ vận chuyển: đã đóng gói xong", class: "status-awaiting_shipment_packed" },
        awaiting_shipment_assigned: { text: "Chờ vận chuyển: Đã gán shipper", class: "status-awaiting_shipment_assigned" },
        shipped: { text: "Đã xuất kho", class: "status-shipped" },
        out_for_delivery: { text: "Đang giao hàng", class: "status-shipped" },
        delivered: { text: "Giao thành công", class: "status-delivered" },
        cancelled: { text: "Đã hủy", class: "status-cancelled" },
        returned: { text: "Đã trả hàng", class: "status-cancelled" },
        failed_delivery: { text: "Giao hàng thất bại", class: "status-cancelled" }
    };

     const paymentStatusMap = {
        pending: { text: "Chờ thanh toán", class: "payment-pending" },
        paid: { text: "Đã thanh toán", class: "payment-paid" },
        failed: { text: "Thất bại", class: "payment-failed" },
        refunded: { text: "Đã hoàn tiền", class: "payment-failed" },
        partially_refunded: { text: "Hoàn tiền một phần", class: "payment-pending" }
    };

    // --- HÀM HIỂN THỊ ---
    function renderOrderRow(order) {
        const orderStatus = statusMap[order.status] || { text: 'N/A', class: '' };
        const paymentStatus = paymentStatusMap[order.payment_status] || { text: 'N/A', class: '' };
        
        // Kiểm tra đơn hàng mới
        const isNew = isNewOrder(order.created_at, order.id);
        

        
        // Huy hiệu đơn hàng mới
        const newOrderBadge = isNew ? '<span class="new-order-badge">Mới</span>' : '';
        
        // Lớp dòng
        const rowClass = isNew ? 'new-order-row border-b hover:bg-blue-50' : 'bg-white border-b hover:bg-gray-50';
        
        return `
            <tr class="${rowClass}" style="position: relative;">
                ${newOrderBadge}
                <td class="p-6 font-bold text-indigo-600">${order.order_code}</td>
                <td class="p-6">
                    <div class="font-semibold">${order.customer_name}</div>
                    <div class="text-gray-500">${order.customer_email}</div>
                </td>
                <td class="p-6 font-semibold">${formatCurrency(order.grand_total)}</td>
                <td class="p-6"><span class="status-badge ${orderStatus.class}">${orderStatus.text}</span></td>
                <td class="p-6"><span class="status-badge ${paymentStatus.class}">${paymentStatus.text}</span></td>
                <td class="p-6 ${isNew ? 'font-bold' : ''}"><strong>${formatDateTime(order.created_at)}</strong></td>
                <td class="p-6 text-center">
                    <button onclick='viewOrder(${order.id})' class="text-indigo-600 hover:text-indigo-900 font-medium text-lg" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick='showUpdateStatusModal(${order.id}, "${order.status}")' class="text-green-600 hover:text-green-900 font-medium text-lg ml-4" title="Cập nhật trạng thái">
                         <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    const tbody = document.getElementById('orders-tbody');
    function renderTable(orders) {
        // Cập nhật số lượng đơn hàng mới
        updateNewOrdersCount(orders);
        
        if (orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center p-12 text-gray-500">Không tìm thấy đơn hàng nào.</td></tr>`;
            return;
        }
        
        // Áp dụng lọc phía client cho đơn hàng trong 24h
        let filteredOrders = orders;
        if (newOrdersOnlyFilter.checked) {
            filteredOrders = orders.filter(order => {
                const orderDate = new Date(order.created_at);
                const now = new Date();
                const diffInHours = (now - orderDate) / (1000 * 60 * 60);
                return diffInHours <= 24;
            });
        }
        
        if (filteredOrders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center p-12 text-gray-500">Không tìm thấy đơn hàng nào phù hợp với bộ lọc.</td></tr>`;
            return;
        }
        
        tbody.innerHTML = filteredOrders.map(renderOrderRow).join('');
    }

    // --- HÀM PHÂN TRANG ---
    function renderPagination(paginationData) {
        const paginationInfo = document.getElementById('pagination-info');
        const paginationControls = document.getElementById('pagination-controls');
        
        // Cập nhật thông tin phân trang
        if (paginationData.total > 0) {
            paginationInfo.innerHTML = `
                Hiển thị ${paginationData.from} đến ${paginationData.to} trong tổng số ${paginationData.total} kết quả
            `;
        } else {
            paginationInfo.innerHTML = 'Không có dữ liệu';
        }
        
        // Cập nhật trạng thái toàn cục
        currentPage = paginationData.current_page;
        totalPages = paginationData.last_page;
        
        // Tạo nút điều khiển phân trang
        let paginationHtml = '';
        
        // Nút trước
        if (currentPage > 1) {
            paginationHtml += `
                <button onclick="goToPage(${currentPage - 1})" class="px-3 py-2 text-sm leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;
        } else {
            paginationHtml += `
                <button disabled class="px-3 py-2 text-sm leading-tight text-gray-300 bg-gray-100 border border-gray-300 rounded-l-lg cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;
        }
        
        // Số trang
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHtml += `
                    <button class="px-3 py-2 text-sm leading-tight text-blue-600 bg-blue-50 border border-gray-300 hover:bg-blue-100 hover:text-blue-700">
                        ${i}
                    </button>
                `;
            } else {
                paginationHtml += `
                    <button onclick="goToPage(${i})" class="px-3 py-2 text-sm leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">
                        ${i}
                    </button>
                `;
            }
        }
        
        // Nút tiếp
        if (currentPage < totalPages) {
            paginationHtml += `
                <button onclick="goToPage(${currentPage + 1})" class="px-3 py-2 text-sm leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;
        } else {
            paginationHtml += `
                <button disabled class="px-3 py-2 text-sm leading-tight text-gray-300 bg-gray-100 border border-gray-300 rounded-r-lg cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;
        }
        
        paginationControls.innerHTML = paginationHtml;
    }

    async function goToPage(page) {
        if (page < 1 || page > totalPages || page === currentPage) return;
        
        const formData = new FormData();
        formData.append('page', page);
        
        // Thêm bộ lọc hiện tại
        if (searchInput.value) formData.append('search', searchInput.value);
        if (orderStatusFilter.value) formData.append('status', orderStatusFilter.value);
        if (paymentStatusFilter.value) formData.append('payment_status', paymentStatusFilter.value);
        if (fromDateFilter.value) formData.append('date_from', fromDateFilter.value);
        if (toDateFilter.value) formData.append('date_to', toDateFilter.value);

        try {
            const response = await fetch(CONFIG.routes.index + '?' + new URLSearchParams(formData), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });
            
            const result = await response.json();
            if (result.success) {
                // Lưu dữ liệu hiện tại để lọc phía client
                sessionStorage.setItem('currentOrdersData', JSON.stringify(result.data));
                renderTable(result.data);
                renderPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error loading page:', error);
            showToast('Không thể tải trang này. Vui lòng thử lại hoặc về trang trước.', 'warning', 'Tải trang thất bại');
        }
    }

    // --- XỬ LÝ MODAL ---
    const modal = document.getElementById('order-detail-modal');

    async function viewOrder(orderId) {
        try {
            const response = await fetch(CONFIG.routes.show.replace(':id', orderId));
            const result = await response.json();
            
            if (result.success) {
                const order = result.data;
                populateModal(order);
                modal.classList.add('is-open');
                modal.querySelector('div').classList.remove('scale-95');        
                // Đánh dấu đơn hàng đã xem khi mở modal (chỉ với đơn đã hủy)
                if (order.status === 'cancelled') {
                    markOrderAsViewed(orderId);
                    // Làm mới bảng để cập nhật hiển thị highlight
                    refreshCurrentPage();
                }
            }
        } catch (error) {
            console.error('Error fetching order details:', error);
            if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                showToast('Không thể kết nối đến server. Kiểm tra mạng và thử lại.', 'error', 'Lỗi kết nối');
            } else {
                showToast('Không thể tải chi tiết đơn hàng. Đơn hàng có thể đã bị xóa hoặc bạn không có quyền truy cập.', 'error', 'Tải dữ liệu thất bại');
            }
        }
    }

    function populateModal(order) {
        console.log('DEBUG: Order data received:', order);
        console.log('DEBUG: Order fulfillments:', order.fulfillments);
        if (order.fulfillments && order.fulfillments.length > 0) {
            console.log('DEBUG: First fulfillment items:', order.fulfillments[0].items);
        }
        
        document.getElementById('modal-order-code').textContent = order.order_code || 'N/A';
        document.getElementById('modal-customer-name').textContent = order.customer_name || 'N/A';
        document.getElementById('modal-customer-email').textContent = order.customer_email || 'N/A';
        document.getElementById('modal-customer-phone').textContent = order.customer_phone || 'N/A';

        // Hiển thị địa chỉ chi tiết
        let addressParts = [];
        // Hiển thị thông tin đầy đủ từ Ward (bao gồm cả District thông qua path_with_type)
        // Hiển thị gộp thông tin
        // if (order.shipping_ward && order.shipping_ward.path_with_type) {
        //     const wardPath = order.shipping_ward.path_with_type;
        //     // path_with_type thường có format: "Tỉnh/Thành phố, Quận/Huyện, Phường/Xã"
        //     addressParts.push(`<strong>Vị trí:</strong> ${wardPath}`);
        // } else {
        //     // Fallback nếu không có path_with_type
        //     if (order.shipping_ward && order.shipping_ward.name_with_type) {
        //         addressParts.push(`<strong>Phường/Xã:</strong> ${order.shipping_ward.name_with_type}`);
        //     }
        //     if (order.shipping_province && order.shipping_province.name_with_type) {
        //         addressParts.push(`<strong>Tỉnh/Thành phố:</strong> ${order.shipping_province.name_with_type}`);
        //     }
        // }

        // Hiển thị riêng 
        if (order.shipping_province && order.shipping_province.name_with_type) {
            addressParts.push(`<strong>Tỉnh/Thành phố:</strong> ${order.shipping_province.name_with_type}`);
        }
        if (order.shipping_ward && order.shipping_ward.name_with_type) {
            addressParts.push(`<strong>Phường/Xã:</strong> ${order.shipping_ward.name_with_type}`);
        }
        if (order.shipping_address_line1) {
            addressParts.push(`<strong>Địa chỉ chi tiết :</strong> ${order.shipping_address_line1}`);
        }
        if (order.shipping_address_line2) {
            addressParts.push(`<strong>Địa chỉ 2:</strong> ${order.shipping_address_line2}`);
        }
        

        
        document.getElementById('modal-shipping-address').innerHTML = 
            addressParts.length > 0 ? addressParts.join('<br>') : 'Không có thông tin địa chỉ';

        // Hiển thị thông tin cửa hàng nếu là đơn hàng nhận tại cửa hàng
        const storeInfo = document.getElementById('modal-store-info');
        if (order.store_location && order.store_location.id) {
            const store = order.store_location;
            document.getElementById('modal-store-name').textContent = store.name || 'N/A';
            document.getElementById('modal-store-phone').textContent = store.phone || 'N/A';
            
            // Tạo địa chỉ đầy đủ của cửa hàng
            let storeAddressParts = [];
            if (store.address) {
                storeAddressParts.push(store.address);
            }
            if (store.ward && store.ward.name_with_type) {
                storeAddressParts.push(store.ward.name_with_type);
            }
            if (store.district && store.district.name_with_type) {
                storeAddressParts.push(store.district.name_with_type);
            }
            if (store.province && store.province.name_with_type) {
                storeAddressParts.push(store.province.name_with_type);
            }
            
            document.getElementById('modal-store-address').textContent = 
                storeAddressParts.length > 0 ? storeAddressParts.join(', ') : 'N/A';
            
            storeInfo.classList.remove('hidden');
        } else {
            storeInfo.classList.add('hidden');
        }

        // Hiển thị thông tin shipper nếu có
        const shipperInfo = document.getElementById('modal-shipper-info');
        if (order.shipper && order.shipper.name) {
            document.getElementById('modal-shipper-name').textContent = order.shipper.name;
            document.getElementById('modal-shipper-email').textContent = order.shipper.email || 'N/A';
            document.getElementById('modal-shipper-phone').textContent = order.shipper.phone_number || 'N/A';
            shipperInfo.classList.remove('hidden');
        } else {
            shipperInfo.classList.add('hidden');
        }
        // Hiển thị các loại ghi chú
        const customerNotesSection = document.getElementById('customer-notes-section');
        const adminNotesSection = document.getElementById('admin-notes-section');
        const shipperNotesSection = document.getElementById('shipper-notes-section');
        const customerNotesElement = document.getElementById('modal-customer-notes');
        const adminNotesElement = document.getElementById('modal-admin-notes');
        const shipperNotesElement = document.getElementById('modal-shipper-notes');
        // Xử lý ghi chú từ khách hàng
        const customerNotes = order.notes_from_customer;
        if (customerNotes && customerNotes.trim() !== '') {
            customerNotesElement.textContent = customerNotes;
            customerNotesSection.classList.remove('hidden');
        } else {
            customerNotesSection.classList.add('hidden');
        }
        // Xử lý ghi chú từ admin
        const adminNotes = order.admin_note;
        if (adminNotes && adminNotes.trim() !== '') {
            adminNotesElement.textContent = adminNotes;
            adminNotesSection.classList.remove('hidden');
        } else {
            adminNotesSection.classList.add('hidden');
        }
        // Xử lý ghi chú dành cho shipper
        const shipperNotes = order.notes_for_shipper;
        if (shipperNotes && shipperNotes.trim() !== '') {
            shipperNotesElement.textContent = shipperNotes;
            shipperNotesSection.classList.remove('hidden');
        } else {
            shipperNotesSection.classList.add('hidden');
        }
        document.getElementById('modal-order-date').textContent = order.created_at ? formatDate(order.created_at) : 'N/A';
        
        const orderStatus = statusMap[order.status] || { text: 'N/A', class: '' };
        const modalOrderStatusEl = document.getElementById('modal-order-status');
        modalOrderStatusEl.textContent = orderStatus.text;
        modalOrderStatusEl.className = `status-badge ${orderStatus.class}`;

        const paymentStatus = paymentStatusMap[order.payment_status] || { text: 'N/A', class: '' };
        const modalPaymentStatusEl = document.getElementById('modal-payment-status');
        modalPaymentStatusEl.textContent = paymentStatus.text;
        modalPaymentStatusEl.className = `status-badge ${paymentStatus.class}`;

        document.getElementById('modal-payment-method').textContent = order.payment_method || 'N/A';

        // Hiển thị thông tin thời gian giao hàng nếu có
        const deliveryTimeInfo = document.getElementById('modal-delivery-time-info');
        const deliverySlotInfo = document.getElementById('modal-delivery-slot-info');
        
        if (order.desired_delivery_date) {
            document.getElementById('modal-desired-date').textContent = formatDeliveryDate(order.desired_delivery_date);
            deliveryTimeInfo.classList.remove('hidden');
        } else {
            deliveryTimeInfo.classList.add('hidden');
        }
        
        if (order.desired_delivery_time_slot) {
            document.getElementById('modal-desired-time-slot').textContent = order.desired_delivery_time_slot;
            deliverySlotInfo.classList.remove('hidden');
        } else {
            deliverySlotInfo.classList.add('hidden');
        }

        // Hiển thị thông tin mã giảm giá nếu có
        const couponInfo = document.getElementById('modal-coupon-info');
        if (order.coupon_usages && order.coupon_usages.length > 0) {
            const couponUsage = order.coupon_usages[0]; // Lấy mã giảm giá đầu tiên
            const coupon = couponUsage.coupon;
            if (coupon) {
                document.getElementById('modal-coupon-code').textContent = coupon.code;
                // Hiển thị thông tin giảm giá
                let discountText = '';
                if (coupon.type === 'percentage') {
                    discountText = `-${coupon.value}%`;
                } else {
                    discountText = `-${formatCurrency(coupon.value)}`;
                }
                document.getElementById('modal-coupon-discount').textContent = discountText;
                couponInfo.classList.remove('hidden');
            } else {
                couponInfo.classList.add('hidden');
            }
        } else {
            couponInfo.classList.add('hidden');
        }
        // Hiển thị thông tin lý do hủy/thất bại nếu có
        const cancellationInfo = document.getElementById('modal-cancellation-info');
        const cancellationTitle = document.getElementById('modal-cancellation-title');
        const cancellationReason = document.getElementById('modal-cancellation-reason');
        const cancellationDate = document.getElementById('modal-cancellation-date');
        if ((order.status === 'cancelled' || order.status === 'failed_delivery' || order.status === 'returned') && 
            (order.cancellation_reason || order.failed_delivery_reason)) { 
            // Xác định tiêu đề và lý do phù hợp
            let title = 'Lý do hủy đơn hàng:';
            let reason = order.cancellation_reason || 'Không có thông tin.';
            let dateInfo = '';
            if (order.status === 'failed_delivery') {
                title = 'Lý do giao hàng thất bại:';
                reason = order.failed_delivery_reason || order.cancellation_reason || 'Không có thông tin.';
            } else if (order.status === 'returned') {
                title = 'Lý do trả hàng:';
                reason = order.cancellation_reason || 'Trả hàng theo yêu cầu khách hàng.';
            }
            // Hiển thị thời gian hủy nếu có
            if (order.cancelled_at) {
                dateInfo = `Thời gian: ${formatDateTime(order.cancelled_at)}`;
            }
            cancellationTitle.textContent = title;
            cancellationReason.textContent = reason;
            cancellationDate.textContent = dateInfo;
            cancellationInfo.classList.remove('hidden');
        } else {
            cancellationInfo.classList.add('hidden');
        }

        // Hiển thị progress bar trạng thái đơn hàng
        const progressBarContainer = document.getElementById('order-progress-bar');
        progressBarContainer.innerHTML = createOrderProgressBar(order.status, order.fulfillments);

        // Thông tin sản phẩm đã được tích hợp vào phần fulfillments


        // Code hiển thị sản phẩm đã được loại bỏ - thông tin sản phẩm hiện được hiển thị trong phần fulfillments

        // Hiển thị thông tin fulfillments (gói hàng)
        const fulfillmentsSection = document.getElementById('modal-fulfillments-section');
        const fulfillmentsList = document.getElementById('modal-fulfillments-list');
        
        if (order.fulfillments && order.fulfillments.length > 0) {
            fulfillmentsList.innerHTML = order.fulfillments.map((fulfillment, index) => {
                const store = fulfillment.store_location;
                let storeAddress = 'N/A';
                if (store) {
                    const addressParts = [];
                    if (store.address) addressParts.push(store.address);
                    if (store.ward && store.ward.name_with_type) addressParts.push(store.ward.name_with_type);
                    if (store.district && store.district.name_with_type) addressParts.push(store.district.name_with_type);
                    if (store.province && store.province.name_with_type) addressParts.push(store.province.name_with_type);
                    storeAddress = addressParts.join(', ');
                }
                
                const statusMap = {
                    'pending_confirmation': { text: 'Chờ xác nhận', class: 'bg-indigo-100 text-indigo-800' },
                    'processing': { text: 'Đang xử lý', class: 'bg-blue-100 text-blue-800' },
                    'packed': { text: 'Chờ vận chuyển: đã đóng gói xong', class: 'bg-yellow-100 text-yellow-800' },
                    'awaiting_shipment_assigned': { text: 'Chờ vận chuyển: đã gán shipper', class: 'bg-cyan-100 text-cyan-800' },
                    'out_for_delivery': { text: 'Đang giao hàng', class: 'bg-purple-100 text-purple-800' },
                    'delivered': { text: 'Giao hàng thành công', class: 'bg-green-100 text-green-800' },
                    'cancelled': { text: 'Hủy', class: 'bg-red-100 text-red-800' },
                    'failed_delivery': { text: 'Giao thất bại', class: 'bg-red-100 text-red-800' },
                    'returned': { text: 'Trả hàng', class: 'bg-gray-100 text-gray-800' }
                };
                
                // Nếu đơn hàng đang ở trạng thái 'processing', tất cả gói hàng sẽ hiển thị trạng thái 'Đang xử lý'
                let displayStatus = fulfillment.status;
                if (order.status === 'processing') {
                    displayStatus = 'processing';
                }
                
                const status = statusMap[displayStatus] || { text: displayStatus, class: 'bg-gray-100 text-gray-800' };
                
                // Lấy danh sách sản phẩm trong fulfillment này
                console.log('DEBUG: Fulfillment data:', fulfillment);
                console.log('DEBUG: Fulfillment items:', fulfillment.items);
                const fulfillmentItems = fulfillment.items || [];
                
                let productsHtml = '';
                if (fulfillmentItems.length > 0) {
                    const itemsHtml = fulfillmentItems.map(fulfillmentItem => {
                        // Thử cả hai cách truy cập: snake_case và camelCase
                        const orderItem = fulfillmentItem.order_item || fulfillmentItem.orderItem;
                        if (!orderItem) {
                            console.log('No order item found in fulfillment item');
                            return '';
                        }
                        
                        // Chuẩn bị ảnh sản phẩm
                        let productImage = null;
                        const productVariant = orderItem.product_variant || orderItem.productVariant;
                        if (productVariant?.primary_image?.path || productVariant?.primaryImage?.path) {
                            const primaryImage = productVariant.primary_image || productVariant.primaryImage;
                            productImage = `/storage/${primaryImage.path}`;
                        } else if (productVariant?.product?.cover_image?.path || productVariant?.product?.coverImage?.path) {
                            const coverImage = productVariant.product.cover_image || productVariant.product.coverImage;
                            productImage = `/storage/${coverImage.path}`;
                        }
                        
                        // Chuẩn bị thông tin biến thể
                        let variantInfo = '';
                        if (orderItem.variant_attributes && orderItem.variant_attributes !== null) {
                            let variantAttrs = null;
                            if (typeof orderItem.variant_attributes === 'string') {
                                try {
                                    variantAttrs = JSON.parse(orderItem.variant_attributes);
                                } catch (e) {
                                    console.log('Failed to parse variant_attributes:', orderItem.variant_attributes);
                                }
                            } else if (typeof orderItem.variant_attributes === 'object') {
                                variantAttrs = orderItem.variant_attributes;
                            }
                            
                            if (variantAttrs && Object.keys(variantAttrs).length > 0) {
                                const variants = Object.entries(variantAttrs)
                                    .filter(([key, value]) => value !== null && value !== '' && value !== undefined)
                                    .map(([key, value]) => `${key}: ${value}`)
                                    .join(' • ');
                                if (variants) {
                                    variantInfo = `<div class="text-xs text-gray-500 mt-1">${variants}</div>`;
                                }
                            }
                        }
                        
                        return `
                            <tr class="border-b last:border-none hover:bg-gray-50">
                                <td class="p-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            ${productImage ? 
                                                `<img src="${productImage}" 
                                                      alt="${orderItem.product_name || 'Sản phẩm'}" 
                                                      class="w-full h-full object-cover"
                                                      onerror="this.parentElement.innerHTML='<div class=\'product-image-placeholder\' style=\'width:100%;height:100%\'><i class=\'fas fa-image text-lg\'></i></div>'">` :
                                                `<div class="product-image-placeholder" style="width:100%;height:100%">
                                                    <i class="fas fa-box text-lg"></i>
                                                 </div>`
                                            }
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="font-medium text-gray-800 line-clamp-2">${orderItem.product_name || 'N/A'}</span>
                                            ${variantInfo}
                                            ${orderItem.product_variant?.sku ? `<div class="text-xs text-gray-400 mt-1">SKU: ${orderItem.product_variant.sku}</div>` : ''}
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-center font-medium">${fulfillmentItem.quantity || 0}</td>
                                <td class="p-3 text-right font-medium">${formatCurrency(orderItem.price || 0)}</td>
                                <td class="p-3 text-right font-semibold text-indigo-600">${formatCurrency((orderItem.price || 0) * (fulfillmentItem.quantity || 0))}</td>
                            </tr>
                        `;
                    }).join('');
                    
                    productsHtml = `
                        <div class="mt-4">
                            <h5 class="font-medium text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-box text-blue-600 mr-2"></i>
                                Sản phẩm trong gói hàng
                            </h5>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                            <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                            <th class="p-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn giá</th>
                                            <th class="p-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    productsHtml = `
                        <div class="mt-4">
                            <p class="text-gray-500 text-sm italic">Không có sản phẩm trong gói hàng này</p>
                        </div>
                    `;
                }
                
                return `
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <h4 class="font-semibold text-gray-800">Gói hàng #${index + 1}</h4>
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${status.class}">
                                ${status.text}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <p class="text-gray-600 mb-1"><strong>Mã vận đơn:</strong></p>
                                <p class="font-mono text-blue-600 font-semibold">${fulfillment.tracking_code || 'Chưa có'}</p>
                            </div>
                            
                            <div>
                                <p class="text-gray-600 mb-1"><strong>Đơn vị vận chuyển:</strong></p>
                                <p>${fulfillment.shipping_carrier || 'N/A'}</p>
                            </div>
                            
                            <div>
                                <p class="text-gray-600 mb-1"><strong>Người vận chuyển:</strong></p>
                                <p>${order.shipper ? order.shipper.name : 'Chưa phân công'}</p>
                                ${order.shipper && order.shipper.phone_number ? `<p class="text-xs text-gray-500">SĐT: ${order.shipper.phone_number}</p>` : ''}
                                ${order.shipper && order.shipper.email ? `<p class="text-xs text-gray-500">Email: ${order.shipper.email}</p>` : ''}
                            </div>
                            
                            <div>
                                <p class="text-gray-600 mb-1"><strong>Kho xuất hàng:</strong></p>
                                <p class="font-medium">${store ? store.name : 'N/A'}</p>
                                <p class="text-gray-500 text-xs">${storeAddress}</p>
                            </div>
                            
                            ${fulfillment.shipped_at ? `
                                <div>
                                    <p class="text-gray-600 mb-1"><strong>Ngày gửi hàng:</strong></p>
                                    <p>${formatDate(fulfillment.shipped_at)}</p>
                                </div>
                            ` : ''}
                            
                            ${fulfillment.delivered_at ? `
                                <div>
                                    <p class="text-gray-600 mb-1"><strong>Ngày giao hàng:</strong></p>
                                    <p>${formatDate(fulfillment.delivered_at)}</p>
                                </div>
                            ` : ''}
                        </div>
                        
                        ${productsHtml}
                    </div>
                `;
            }).join('');
            
            fulfillmentsSection.style.display = 'block';
        } else {
            fulfillmentsSection.style.display = 'none';
        }

        // Thông tin tổng tiền đã được tích hợp vào phần fulfillments
    }



    function closeModal() {
        modal.classList.remove('is-open');
        modal.querySelector('div').classList.add('scale-95');
    }
    
    // Đóng modal khi nhấn phím Escape
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // --- XỬ LÝ BỘ LỌC ---
    const searchInput = document.getElementById('search');
    const orderStatusFilter = document.getElementById('order-status');
    const paymentStatusFilter = document.getElementById('payment-status');
    const fromDateFilter = document.getElementById('from-date');
    const toDateFilter = document.getElementById('to-date');
    const newOrdersOnlyFilter = document.getElementById('new-orders-only');
    
    async function refreshCurrentPage() {
        // Giữ nguyên trang và bộ lọc khi làm mới
        const formData = new FormData();
        formData.append('page', currentPage);
        
        if (searchInput.value) formData.append('search', searchInput.value);
        if (orderStatusFilter.value) formData.append('status', orderStatusFilter.value);
        if (paymentStatusFilter.value) formData.append('payment_status', paymentStatusFilter.value);
        if (fromDateFilter.value) formData.append('date_from', fromDateFilter.value);
        if (toDateFilter.value) formData.append('date_to', toDateFilter.value);

        try {
            const response = await fetch(CONFIG.routes.index + '?' + new URLSearchParams(formData), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });
            
            const result = await response.json();
            if (result.success) {
                // Lưu dữ liệu hiện tại để lọc phía client
                sessionStorage.setItem('currentOrdersData', JSON.stringify(result.data));
                renderTable(result.data);
                renderPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error refreshing page:', error);
            showToast('Không thể làm mới dữ liệu. Vui lòng tải lại trang.', 'warning', 'Cảnh báo');
        }
    }
    
    async function applyFilters() {
        const formData = new FormData();
        
        // Đặt lại về trang 1 khi áp dụng bộ lọc
        formData.append('page', 1);
        
        if (searchInput.value) formData.append('search', searchInput.value);
        if (orderStatusFilter.value) formData.append('status', orderStatusFilter.value);
        if (paymentStatusFilter.value) formData.append('payment_status', paymentStatusFilter.value);
        if (fromDateFilter.value) formData.append('date_from', fromDateFilter.value);
        if (toDateFilter.value) formData.append('date_to', toDateFilter.value);

        try {
            const response = await fetch(CONFIG.routes.index + '?' + new URLSearchParams(formData), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });
            
            const result = await response.json();
            if (result.success) {
                // Lưu dữ liệu hiện tại để lọc phía client
                sessionStorage.setItem('currentOrdersData', JSON.stringify(result.data));
                renderTable(result.data);
                renderPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error applying filters:', error);
            showToast('Không thể áp dụng bộ lọc. Vui lòng thử lại hoặc làm mới trang.', 'warning', 'Lọc dữ liệu thất bại');
        }
    }
    
    document.getElementById('apply-filters').addEventListener('click', applyFilters);
    document.getElementById('clear-filters').addEventListener('click', () => {
        searchInput.value = '';
        orderStatusFilter.value = '';
        paymentStatusFilter.value = '';
        fromDateFilter.value = '';
        toDateFilter.value = '';
        newOrdersOnlyFilter.checked = false;
        loadOrders();
    });

    // Nút làm mới thủ công
    document.getElementById('refresh-orders').addEventListener('click', () => {
        const refreshButton = document.getElementById('refresh-orders');
        const icon = refreshButton.querySelector('i');
        
        // Thêm hiệu ứng quay
        icon.classList.add('fa-spin');
        refreshButton.disabled = true;
        
        // Làm mới dữ liệu
        refreshCurrentPage();
        
        // Gỡ hiệu ứng quay sau 1 giây
        setTimeout(() => {
            icon.classList.remove('fa-spin');
            refreshButton.disabled = false;
        }, 1000);
    });

    // Tự động áp dụng bộ lọc khi checkbox "chỉ đơn hàng mới" thay đổi
    newOrdersOnlyFilter.addEventListener('change', function() {
        // Hiển thị lại dữ liệu hiện tại với bộ lọc mới
        const currentData = JSON.parse(sessionStorage.getItem('currentOrdersData') || '[]');
        renderTable(currentData);
    });

    // --- TẢI DỮ LIỆU BAN ĐẦU ---
    async function loadOrders() {
        try {
            const response = await fetch(CONFIG.routes.index, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });
            
            const result = await response.json();
            if (result.success) {
                // Lưu dữ liệu hiện tại để lọc phía client
                sessionStorage.setItem('currentOrdersData', JSON.stringify(result.data));
                renderTable(result.data);
                renderPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            showToast('Không thể tải danh sách đơn hàng từ server. Hiển thị dữ liệu cache.', 'warning', 'Tải dữ liệu thất bại');
            // Dự phòng: hiển thị dữ liệu ban đầu từ server
            @if(isset($orders))
                renderTable(@json($orders->items()));
            @endif
        }
    }

    // --- HỆ THỐNG THÔNG BÁO TOAST ---
    function showToast(message, type = 'success', title = null) {
        const toastContainer = document.getElementById('toast-container');
        
        // Xác định tiêu đề và biểu tượng dựa trên loại
        let toastTitle = title;
        let icon = '';
        
        if (!toastTitle) {
            switch(type) {
                case 'success':
                    toastTitle = 'Thành công';
                    icon = '✓';
                    break;
                case 'error':
                    toastTitle = 'Lỗi';
                    icon = '✕';
                    break;
                case 'warning':
                    toastTitle = 'Cảnh báo';
                    icon = '⚠';
                    break;
                default:
                    toastTitle = 'Thông báo';
                    icon = 'ℹ';
            }
        }
        
        // Tạo phần tử toast
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas fa-check"></i>
            </div>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="removeToast(this.parentElement)">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M13 1L1 13M1 1l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        `;
        
        // Thêm vào vùng chứa
        toastContainer.appendChild(toast);
        
        // Kích hoạt hiệu ứng
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Tự động xóa sau 5 giây
        setTimeout(() => {
            removeToast(toast);
        }, 5000);
    }
    
    function removeToast(toast) {
        if (toast && toast.parentElement) {
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }
    }

    // --- XỬ LÝ MODAL CẬP NHẬT TRẠNG THÁI ---
    const updateStatusModal = document.getElementById('update-status-modal');
    let currentOrderId = null;

    function showUpdateStatusModal(orderId, currentStatus) {
        currentOrderId = orderId;
        
        // Tìm dữ liệu đơn hàng từ sessionStorage
        const currentData = JSON.parse(sessionStorage.getItem('currentOrdersData') || '[]');
        const orderData = currentData.find(order => order.id == orderId);
        
        let orderCode = '';
        let isPickup = false;
        let hasShipper = false;
        
        if (orderData) {
            orderCode = orderData.order_code;
            isPickup = isPickupOrder(orderData);
            hasShipper = orderData.shipper && orderData.shipper.name;
        } else {
            // Fallback: tìm từ DOM
            const orderRows = document.querySelectorAll('#orders-tbody tr');
            orderRows.forEach(row => {
                const button = row.querySelector(`button[onclick*="${orderId}"]`);
                if (button) {
                    orderCode = row.querySelector('td').textContent.trim();
                }
            });
        }

        document.getElementById('update-order-code').textContent = orderCode;
        document.getElementById('new-status').value = currentStatus;
        document.getElementById('new-status').setAttribute('data-current-status', currentStatus);
        document.getElementById('admin-note').value = '';
        document.getElementById('cancellation-reason').value = '';
        
        // Điều chỉnh dropdown dựa trên loại đơn hàng
        adjustStatusDropdownByOrderType(isPickup);
        
        // Lưu thông tin để validation
        updateStatusModal.dataset.isPickup = isPickup;
        updateStatusModal.dataset.hasShipper = hasShipper;
        
        // Hiện/ẩn trường lý do hủy
        toggleCancellationField(currentStatus);
        
        updateStatusModal.classList.add('is-open');
        updateStatusModal.querySelector('div').classList.remove('scale-95');
    }

    function closeUpdateStatusModal() {
        updateStatusModal.classList.remove('is-open');
        updateStatusModal.querySelector('div').classList.add('scale-95');
        currentOrderId = null;
    }

    // Helper function để kiểm tra đơn hàng nhận tại cửa hàng dựa trên store_location_id
    function isPickupOrder(order) {
        // Đơn hàng nhận tại cửa hàng sẽ có delivery_method = 'pickup'
        // Không dựa vào store_location_id vì warehouser cũng có thể có store_location_id
        return order.delivery_method === 'pickup';
    }

    // Helper function để kiểm tra đơn hàng giao tận nơi cần shipper
    function isDeliveryOrderNeedShipper(order) {
        // Đơn hàng giao tận nơi sẽ không có store_location_id
        return !isPickupOrder(order);
    }

    // Điều chỉnh dropdown trạng thái dựa trên loại đơn hàng
    function adjustStatusDropdownByOrderType(isPickup) {
        const statusSelect = document.getElementById('new-status');
        const deliveryOnlyOptions = statusSelect.querySelectorAll('.delivery-only');
        
        if (isPickup) {
            // Ẩn các trạng thái chỉ dành cho giao hàng
            deliveryOnlyOptions.forEach(option => {
                option.style.display = 'none';
            });
        } else {
            // Hiện tất cả trạng thái cho giao hàng tận nơi
            deliveryOnlyOptions.forEach(option => {
                option.style.display = 'block';
            });
        }
    }

    function toggleCancellationField(status) {
        const cancellationField = document.getElementById('cancellation-reason-field');
        const cancellationTextarea = document.getElementById('cancellation-reason');
        const fieldLabel = cancellationField.querySelector('label');
        
        if (status === 'cancelled') {
            cancellationField.style.display = 'block';
            cancellationTextarea.setAttribute('required', 'required');
            cancellationTextarea.setAttribute('name', 'cancellation_reason');
            fieldLabel.innerHTML = 'Lý do hủy đơn <span class="text-red-500">*</span>';
            cancellationTextarea.setAttribute('placeholder', 'Nhập lý do hủy đơn hàng...');
        } else if (status === 'failed_delivery') {
            cancellationField.style.display = 'block';
            cancellationTextarea.setAttribute('required', 'required');
            cancellationTextarea.setAttribute('name', 'failed_delivery_reason');
            fieldLabel.innerHTML = 'Lý do giao hàng thất bại <span class="text-red-500">*</span>';
            cancellationTextarea.setAttribute('placeholder', 'Nhập lý do giao hàng thất bại...');
        } else {
            cancellationField.style.display = 'none';
            cancellationTextarea.removeAttribute('required');
            cancellationTextarea.setAttribute('name', 'cancellation_reason');
            fieldLabel.innerHTML = 'Lý do hủy đơn <span class="text-red-500">*</span>';
            cancellationTextarea.setAttribute('placeholder', 'Nhập lý do hủy đơn hàng...');
        }
    }

    // Kiểm tra form trước khi gửi
    function validateStatusForm() {
        const newStatus = document.getElementById('new-status').value;
        const currentStatus = document.getElementById('new-status').getAttribute('data-current-status');
        const isPickup = updateStatusModal.dataset.isPickup === 'true';
        const hasShipper = updateStatusModal.dataset.hasShipper === 'true';
        
        if (!newStatus) {
            showToast('Vui lòng chọn trạng thái hợp lệ', 'error');
            return false;
        }
        
        // Kiểm tra shipper cho đơn hàng giao tận nơi khi chuyển sang 'out_for_delivery'
        if (!isPickup && newStatus === 'out_for_delivery' && !hasShipper) {
            showToast('Vui lòng gán shipper trước khi chuyển sang trạng thái "Đang giao hàng"', 'error');
            return false;
        }
        
        if (newStatus === 'cancelled') {
            const cancellationReason = document.getElementById('cancellation-reason').value;
            if (!cancellationReason.trim()) {
                showToast('Vui lòng nhập lý do hủy đơn hàng', 'error');
                return false;
            }
        }
        
        if (newStatus === 'failed_delivery') {
            const reasonField = document.getElementById('cancellation-reason').value;
            if (!reasonField.trim()) {
                showToast('Vui lòng nhập lý do giao hàng thất bại', 'error');
                return false;
            }
        }
        
        return true;
    }

    // Lắng nghe thay đổi trạng thái để hiện/ẩn trường lý do hủy
    document.getElementById('new-status').addEventListener('change', function() {
        toggleCancellationField(this.value);
    });

    // Xử lý gửi form cập nhật trạng thái
    document.getElementById('update-status-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!currentOrderId) {
            showToast('Không xác định được đơn hàng cần cập nhật.', 'error', 'Lỗi hệ thống');
            return;
        }

        // Kiểm tra form
        if (!validateStatusForm()) {
            return;
        }

        const formData = new FormData(e.target);
        const status = formData.get('status');
        // Chuẩn bị dữ liệu gửi
        const requestData = {
            status: status,
            admin_note: formData.get('admin_note')
        };
        // Thêm lý do hủy/thất bại tùy theo trạng thái
        if (status === 'cancelled') {
            requestData.cancellation_reason = formData.get('cancellation_reason');
        } else if (status === 'failed_delivery') {
            requestData.failed_delivery_reason = formData.get('failed_delivery_reason') || formData.get('cancellation_reason');
        }
        
        try {
            const response = await fetch(CONFIG.routes.updateStatus.replace(':id', currentOrderId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });

            const result = await response.json();
            
            if (response.ok && result.success) {
                // Hiển thị thông báo ngắn gọn
                showToast('Cập nhật trạng thái thành công', 'success');
                
                // Đánh dấu đơn hàng này đã xem (bỏ đánh dấu "mới")
                markOrderAsViewed(currentOrderId);
                
                // Đóng modal
                closeUpdateStatusModal();
                
                // Làm mới trang hiện tại thay vì về trang 1
                refreshCurrentPage();
            } else {
                // Xử lý các loại lỗi khác nhau
                if (response.status === 422) {
                    // Lỗi xác thực
                    showToast('Vui lòng chọn trạng thái hợp lệ', 'error');
                } else if (response.status === 403) {
                    showToast('Bạn không có quyền thực hiện hành động này.', 'error');
                } else if (response.status === 404) {
                    showToast('Không tìm thấy đơn hàng. Đơn hàng có thể đã bị xóa.', 'error');
                } else if (response.status >= 500) {
                    showToast('Lỗi server. Vui lòng thử lại sau hoặc liên hệ IT Support.', 'error');
                } else {
                    showToast('Vui lòng chọn trạng thái hợp lệ', 'error');
                }
            }
        } catch (error) {
            console.error('Error updating status:', error);
            if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                showToast('Mất kết nối mạng. Vui lòng kiểm tra internet và thử lại.', 'error', 'Lỗi kết nối');
            } else {
                showToast('Lỗi hệ thống không xác định. Vui lòng liên hệ IT Support hoặc thử lại sau.', 'error', 'Lỗi hệ thống');
            }
        }
    });

    // Đóng modal khi nhấn phím escape
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && updateStatusModal.classList.contains('is-open')) {
            closeUpdateStatusModal();
        }
        if (event.key === 'Escape' && assignShipperModal.classList.contains('is-open')) {
            closeAssignShipperModal();
        }
    });



    document.addEventListener('DOMContentLoaded', () => {
        @if(isset($orders))
            const initialData = @json($orders->items());
            sessionStorage.setItem('currentOrdersData', JSON.stringify(initialData));
            renderTable(initialData);
            renderPagination({
                current_page: {{ $orders->currentPage() }},
                last_page: {{ $orders->lastPage() }},
                per_page: {{ $orders->perPage() }},
                total: {{ $orders->total() }},
                from: {{ $orders->firstItem() ?? 0 }},
                to: {{ $orders->lastItem() ?? 0 }}
            });
        @else
            loadOrders();
        @endif

        // Auto-refresh orders every 30 seconds to catch status updates from packing station
        setInterval(() => {
            loadOrders();
        }, 30000);
    });

    </script>
@endsection