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
        /* Kiểu thông báo (toast) */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }
        .toast {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 16px 20px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid;
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast.success {
            border-left-color: #10b981;
        }
        
        .toast.error {
            border-left-color: #ef4444;
        }
        
        .toast.warning {
            border-left-color: #f59e0b;
        }
        
        .toast-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        
        .toast.success .toast-icon {
            background: #10b981;
        }
        
        .toast.error .toast-icon {
            background: #ef4444;
        }
        
        .toast.warning .toast-icon {
            background: #f59e0b;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 2px;
        }
        
        .toast-message {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.4;
        }
        
        .toast-close {
            margin-left: 12px;
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .toast-close:hover {
            color: #6b7280;
            background: #f3f4f6;
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
                        <span class="ml-2 text-sm font-medium text-gray-700">Chỉ xem đơn hàng mới (24h)</span>
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
                        <th scope="col" class="p-6">Shipper</th>
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
                        <div id="modal-shipper-info" class="hidden">
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Thông tin shipper</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-center space-x-2 mb-2">
                                    <i class="fas fa-shipping-fast text-blue-600"></i>
                                    <span class="font-medium text-blue-800">Shipper được gán:</span>
                                </div>
                                <div class="space-y-1 text-sm">
                                    <p><strong>Tên:</strong> <span id="modal-shipper-name"></span></p>
                                    <p><strong>Email:</strong> <span id="modal-shipper-email"></span></p>
                                    <p><strong>SĐT:</strong> <span id="modal-shipper-phone"></span></p>
                                </div>
                            </div>
                        </div>
                         <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Ghi chú</h3>
                            <p class="text-gray-600 italic" id="modal-customer-notes">Không có ghi chú.</p>
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
                                </div>
                            </div>
                        </div>

                        <h3 class="font-bold text-lg text-gray-800 mb-3">Sản phẩm trong đơn</h3>
                        <div class="border rounded-lg overflow-hidden">
                           <table class="w-full">
                               <thead class="bg-gray-50 text-left text-sm text-gray-600">
                                   <tr>
                                       <th class="p-3">Sản phẩm</th>
                                       <th class="p-3 text-center">Số lượng</th>
                                       <th class="p-3 text-right">Đơn giá</th>
                                       <th class="p-3 text-right">Thành tiền</th>
                                   </tr>
                               </thead>
                               <tbody id="modal-order-items"></tbody>
                           </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <div class="w-full md:w-1/2">
                                <dl class="space-y-2 text-gray-700">
                                    <div class="flex justify-between">
                                        <dt>Tổng tiền hàng:</dt>
                                        <dd class="font-medium" id="modal-sub-total"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt>Phí vận chuyển:</dt>
                                        <dd class="font-medium" id="modal-shipping-fee"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt>Giảm giá:</dt>
                                        <dd class="font-medium text-red-500" id="modal-discount"></dd>
                                    </div>
                                    <div class="flex justify-between text-xl font-bold text-gray-900 border-t pt-2 mt-2">
                                        <dt>Tổng cộng:</dt>
                                        <dd id="modal-grand-total"></dd>
                                    </div>
                                </dl>
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

    <!-- Assign Shipper Modal -->
    <div id="assign-shipper-modal" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md transform transition-transform duration-300 scale-95">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Gán Shipper</h2>
                <p class="text-sm text-gray-600 mt-1">Mã đơn hàng: <span id="assign-shipper-order-code" class="font-medium text-indigo-600"></span></p>
            </div>
            <form id="assign-shipper-form" class="p-6">
                <div class="space-y-4">
                    <div>
                        <label for="shipper-select" class="block text-sm font-medium text-gray-700 mb-2">Chọn Shipper <span class="text-red-500">*</span></label>
                        <select id="shipper-select" name="shipper_id" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">-- Chọn Shipper --</option>
                        </select>
                        <div id="shipper-loading" class="text-sm text-gray-500 mt-1" style="display: none;">
                            <i class="fas fa-spinner fa-spin mr-1"></i>
                            Đang tải danh sách shipper...
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAssignShipperModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                        Hủy
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium flex items-center space-x-2">
                        <i class="fas fa-user-check"></i>
                        <span>Gán Shipper</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
    const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    
    // --- HÀM TẠO PROGRESS BAR ---
    function createOrderProgressBar(currentStatus) {
        // Định nghĩa các bước tiến trình chính
        const progressSteps = [
            { status: 'pending_confirmation', label: 'Chờ xác nhận', icon: '<i class="fas fa-clipboard-check"></i>' },
            { status: 'processing', label: 'Đang xử lý', icon: '<i class="fas fa-cogs"></i>' },
            { status: 'awaiting_shipment', label: 'Chờ giao hàng', icon: '<i class="fas fa-box"></i>' },
            { status: 'shipped', label: 'Đã xuất kho', icon: '<i class="fas fa-shipping-fast"></i>' },
            { status: 'out_for_delivery', label: 'Đang giao', icon: '<i class="fas fa-truck"></i>' },
            { status: 'delivered', label: 'Giao thành công', icon: '<i class="fas fa-check-circle"></i>' }
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
            stepsToShow[5] = { status: 'failed_delivery', label: 'Giao hàng thất bại', icon: '<i class="fas fa-exclamation-triangle"></i>' };
            progressTitle = 'Giao hàng thất bại';
            progressPercentage = (5 / (stepsToShow.length - 1)) * 100;
        } else if (currentStatus === 'returned') {
            // Trạng thái trả hàng - thay thế bước "Giao thành công" bằng "Đã trả hàng"
            stepsToShow[5] = { status: 'returned', label: 'Đã trả hàng', icon: '<i class="fas fa-undo-alt"></i>' };
            progressTitle = 'Đơn hàng đã được trả lại';
            progressPercentage = (5 / (stepsToShow.length - 1)) * 100;
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
            } else if (currentStatus === 'shipped') {
                progressTitle = 'Đơn hàng đã xuất kho';
            } else if (currentStatus === 'awaiting_shipment') {
                progressTitle = 'Đơn hàng đang chờ giao';
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

    // Theo dõi đơn hàng đã xem (đơn đã cập nhật trạng thái)
    let viewedOrders = new Set(JSON.parse(localStorage.getItem('viewedOrders') || '[]'));

    // Kiểm tra đơn hàng mới (tạo trong 24h qua và chưa xem)
    const isNewOrder = (createdAt, orderId) => {
        if (viewedOrders.has(orderId)) {
            return false; // Đơn hàng đã được xem/cập nhật
        }
        const orderDate = new Date(createdAt);
        const now = new Date();
        const diffInHours = (now - orderDate) / (1000 * 60 * 60);
        return diffInHours <= 24;
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
        
        // Xác định hiển thị shipper
        let shipperDisplay = '<span class="text-gray-400 italic">Chưa gán</span>';
        if (order.shipper && order.shipper.name) {
            shipperDisplay = `<span class="text-gray-700 font-medium">${order.shipper.name}</span>`;
        }
        
        // Chỉ hiển thị nút gán shipper cho trạng thái "chờ giao hàng"
        let assignShipperButton = '';
        if (order.status === 'awaiting_shipment') {
            assignShipperButton = `
                <button onclick='showAssignShipperModal(${order.id}, "${order.order_code}")' 
                        class="text-blue-600 hover:text-blue-900 font-medium text-lg ml-4" 
                        title="Gán Shipper">
                    <i class="fas fa-user-plus"></i>
                </button>
            `;
        }
        
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
                <td class="p-6">${shipperDisplay}</td>
                <td class="p-6 text-center">
                    <button onclick='viewOrder(${order.id})' class="text-indigo-600 hover:text-indigo-900 font-medium text-lg" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick='showUpdateStatusModal(${order.id}, "${order.status}")' class="text-green-600 hover:text-green-900 font-medium text-lg ml-4" title="Cập nhật trạng thái">
                         <i class="fas fa-edit"></i>
                    </button>
                    ${assignShipperButton}
                </td>
            </tr>
        `;
    }

    const tbody = document.getElementById('orders-tbody');
    function renderTable(orders) {
        // Cập nhật số lượng đơn hàng mới
        updateNewOrdersCount(orders);
        
        if (orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center p-12 text-gray-500">Không tìm thấy đơn hàng nào.</td></tr>`;
            return;
        }
        
        // Áp dụng lọc phía client chỉ cho đơn hàng mới
        let filteredOrders = orders;
        if (newOrdersOnlyFilter.checked) {
            filteredOrders = orders.filter(order => isNewOrder(order.created_at, order.id));
        }
        
        if (filteredOrders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center p-12 text-gray-500">Không tìm thấy đơn hàng nào phù hợp với bộ lọc.</td></tr>`;
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

        // Hiển thị tổng tiền và các khoản
        document.getElementById('modal-customer-notes').textContent = order.notes_from_customer || "Không có ghi chú.";
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

        // Hiển thị progress bar trạng thái đơn hàng
        const progressBarContainer = document.getElementById('order-progress-bar');
        progressBarContainer.innerHTML = createOrderProgressBar(order.status);

        // Hiển thị sản phẩm
        const itemsTbody = document.getElementById('modal-order-items');
        if (order.items && Array.isArray(order.items)) {

        itemsTbody.innerHTML = order.items.map(item => {
            // Chuẩn bị ảnh sản phẩm - kiểm tra nhiều nguồn
            let productImage = null;
            if (item.product_variant?.primary_image?.path) {
                productImage = `/storage/${item.product_variant.primary_image.path}`;
            } else if (item.product_variant?.product?.cover_image?.path) {
                productImage = `/storage/${item.product_variant.product.cover_image.path}`;
            } else if (item.image_url) {
                productImage = item.image_url;
            } else if (item.product_image) {
                productImage = item.product_image;
            }
            
            // Chuẩn bị link sản phẩm cho admin - liên kết đến trang chỉnh sửa sản phẩm
            let productLink = '#';
            if (item.product_variant?.product?.id) {
                productLink = '/admin/products/' + item.product_variant.product.id + '/edit';
            } else if (item.product_id) {
                productLink = '/admin/products/' + item.product_id + '/edit';
            }

            // Chuẩn bị thông tin biến thể từ variant_attributes
            let variantInfo = '';
            if (item.variant_attributes && item.variant_attributes !== null) {
                let variantAttrs = null;
                
                                 // Parse JSON string if needed
                 if (typeof item.variant_attributes === 'string') {
                     try {
                         // Thử phân tích JSON đầu tiên
                         variantAttrs = JSON.parse(item.variant_attributes);
                     } catch (e) {
                         // Nếu phân tích JSON thất bại, thử giải mã ký tự HTML trước
                         try {
                             const decodedString = item.variant_attributes.replace(/\\u([0-9a-fA-F]{4})/g, (match, grp) => 
                                 String.fromCharCode(parseInt(grp, 16))
                             );
                             variantAttrs = JSON.parse(decodedString);
                         } catch (e2) {
                             console.log('Failed to parse variant_attributes:', item.variant_attributes);
                             variantAttrs = null;
                         }
                     }
                 } else if (typeof item.variant_attributes === 'object') {
                     variantAttrs = item.variant_attributes;
                 }
                
                if (variantAttrs && Object.keys(variantAttrs).length > 0) {
                    const variants = Object.entries(variantAttrs)
                        .filter(([key, value]) => value !== null && value !== '' && value !== undefined)
                        .map(([key, value]) => {
                            // Dịch tiếng Việt cho các loại biến thể phổ biến
                            const translations = {
                                'color': 'Màu sắc',
                                'size': 'Kích cỡ', 
                                'material': 'Chất liệu',
                                'style': 'Kiểu dáng',
                                'weight': 'Trọng lượng',
                                'capacity': 'Dung tích',
                                'ram': 'RAM',
                                'storage': 'Bộ nhớ',
                                'screen_size': 'Màn hình',
                                'processor': 'Bộ xử lý',
                                'brand': 'Thương hiệu',
                                'dung lượng lưu trữ': 'Dung lượng',
                                'kích thước màn hình': 'Màn hình'
                            };
                            const translatedKey = translations[key.toLowerCase()] || key;
                            return `${translatedKey}: <span class="font-medium">${value}</span>`;
                        })
                        .join(' • ');
                    if (variants) {
                        variantInfo = `<div class="text-xs text-gray-500 mt-1">${variants}</div>`;
                    }
                }
            }
            
            // Lấy SKU từ biến thể nếu có
            const productSku = item.product_variant?.sku || item.sku || item.product_sku || null;
            
            return `
                <tr class="border-b last:border-none hover:bg-gray-50">
                    <td class="p-3">
                                                 <div class="flex items-center space-x-3">
                             <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                 ${productImage ? 
                                     `<img src="${productImage}" 
                                           alt="${item.product_name || 'Sản phẩm'}" 
                                           class="w-full h-full object-cover"
                                           onerror="this.parentElement.innerHTML='<div class=\\'product-image-placeholder\\' style=\\'width:100%;height:100%\\'><i class=\\'fas fa-image text-2xl\\'></i></div>'">` :
                                     `<div class="product-image-placeholder" style="width:100%;height:100%">
                                         <i class="fas fa-box text-2xl"></i>
                                      </div>`
                                 }
                             </div>
                                                         <div class="flex-1 min-w-0">
                                 ${productLink !== '#' ? 
                                     `<a href="${productLink}" 
                                        target="_blank" 
                                        class="font-medium text-indigo-600 hover:text-indigo-900 hover:underline line-clamp-2"
                                        title="Chỉnh sửa sản phẩm (mở tab mới)"
                                        onclick="event.stopPropagation();">
                                         ${item.product_name || 'N/A'}
                                      </a>` :
                                     `<span class="font-medium text-gray-800 line-clamp-2">${item.product_name || 'N/A'}</span>`
                                 }
                                 ${variantInfo}
                                 ${productSku ? `<div class="text-xs text-gray-400 mt-1">SKU: ${productSku}</div>` : ''}
                             </div>
                        </div>
                    </td>
                    <td class="p-3 text-center font-medium">${item.quantity || 0}</td>
                    <td class="p-3 text-right font-medium">${formatCurrency(item.price || 0)}</td>
                    <td class="p-3 text-right font-semibold text-indigo-600">${formatCurrency(item.total_price || 0)}</td>
                </tr>
            `;
        }).join('');
        } else {
            itemsTbody.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-gray-500">Không có sản phẩm</td></tr>';
        }

        // Hiển thị tổng tiền
        document.getElementById('modal-sub-total').textContent = formatCurrency(order.sub_total || 0);
        document.getElementById('modal-shipping-fee').textContent = formatCurrency(order.shipping_fee || 0);
        document.getElementById('modal-discount').textContent = `- ${formatCurrency(order.discount_amount || 0)}`;
        document.getElementById('modal-grand-total').textContent = formatCurrency(order.grand_total || 0);
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
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-title">${toastTitle}</div>
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
            toast.classList.remove('show');
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
        
        // Tìm dữ liệu đơn hàng để lấy mã đơn
        const orderRows = document.querySelectorAll('#orders-tbody tr');
        let orderCode = '';
        orderRows.forEach(row => {
            const button = row.querySelector(`button[onclick*="${orderId}"]`);
            if (button) {
                orderCode = row.querySelector('td').textContent.trim();
            }
        });

        document.getElementById('update-order-code').textContent = orderCode;
        document.getElementById('new-status').value = currentStatus;
        document.getElementById('admin-note').value = '';
        document.getElementById('cancellation-reason').value = '';
        
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

    function toggleCancellationField(status) {
        const cancellationField = document.getElementById('cancellation-reason-field');
        const cancellationTextarea = document.getElementById('cancellation-reason');
        const fieldLabel = cancellationField.querySelector('label');
        
        if (status === 'cancelled') {
            cancellationField.style.display = 'block';
            cancellationTextarea.setAttribute('required', 'required');
            fieldLabel.innerHTML = 'Lý do hủy đơn <span class="text-red-500">*</span>';
            cancellationTextarea.setAttribute('placeholder', 'Nhập lý do hủy đơn hàng...');
        } else if (status === 'failed_delivery') {
            cancellationField.style.display = 'block';
            cancellationTextarea.setAttribute('required', 'required');
            fieldLabel.innerHTML = 'Lý do giao hàng thất bại <span class="text-red-500">*</span>';
            cancellationTextarea.setAttribute('placeholder', 'Nhập lý do giao hàng thất bại...');
        } else {
            cancellationField.style.display = 'none';
            cancellationTextarea.removeAttribute('required');
            fieldLabel.innerHTML = 'Lý do hủy đơn <span class="text-red-500">*</span>';
            cancellationTextarea.setAttribute('placeholder', 'Nhập lý do hủy đơn hàng...');
        }
    }

    // Kiểm tra form trước khi gửi
    function validateStatusForm() {
        const newStatus = document.getElementById('new-status').value;
        
        if (!newStatus) {
            showToast('Vui lòng chọn trạng thái mới cho đơn hàng.', 'warning', 'Thiếu thông tin');
            return false;
        }
        
        if (newStatus === 'cancelled') {
            const cancellationReason = document.getElementById('cancellation-reason').value;
            if (!cancellationReason.trim()) {
                showToast('Vui lòng nhập lý do hủy đơn hàng.', 'warning', 'Thiếu thông tin');
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
        
        try {
            const response = await fetch(CONFIG.routes.updateStatus.replace(':id', currentOrderId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status: formData.get('status'),
                    admin_note: formData.get('admin_note'),
                    cancellation_reason: formData.get('cancellation_reason')
                })
            });

            const result = await response.json();
            
            if (response.ok && result.success) {
                // Hiển thị thông báo cải thiện
                const statusText = result.data?.status_text || 'trạng thái mới';
                showToast(`Đơn hàng đã được cập nhật thành "${statusText}" thành công!`, 'success', 'Cập nhật thành công');
                
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
                    if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat();
                        showToast(errorMessages.join('. '), 'error', 'Dữ liệu không hợp lệ');
                    } else {
                        showToast('Dữ liệu gửi lên không hợp lệ. Vui lòng kiểm tra lại.', 'error', 'Validation Error');
                    }
                } else if (response.status === 403) {
                    showToast('Bạn không có quyền thực hiện hành động này.', 'error', 'Không có quyền');
                } else if (response.status === 404) {
                    showToast('Không tìm thấy đơn hàng. Đơn hàng có thể đã bị xóa.', 'error', 'Không tìm thấy');
                } else if (response.status >= 500) {
                    showToast('Lỗi server. Vui lòng thử lại sau hoặc liên hệ IT Support.', 'error', 'Lỗi server');
                } else {
                    showToast(result.message || 'Không thể cập nhật trạng thái. Vui lòng thử lại.', 'error', 'Cập nhật thất bại');
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

    // --- XỬ LÝ MODAL GÁN SHIPPER ---
    const assignShipperModal = document.getElementById('assign-shipper-modal');
    let currentAssignOrderId = null;
    let shippersCache = null; // Bộ nhớ tạm cho danh sách shipper

    async function showAssignShipperModal(orderId, orderCode) {
        currentAssignOrderId = orderId;
        
        // Đặt mã đơn hàng
        document.getElementById('assign-shipper-order-code').textContent = orderCode;
        
        // Đặt lại form
        document.getElementById('shipper-select').value = '';
        
        // Hiện modal
        assignShipperModal.classList.add('is-open');
        assignShipperModal.querySelector('div').classList.remove('scale-95');
        
        // Tải danh sách shipper
        await loadShippers();
    }

    function closeAssignShipperModal() {
        assignShipperModal.classList.remove('is-open');
        assignShipperModal.querySelector('div').classList.add('scale-95');
        currentAssignOrderId = null;
    }

    async function loadShippers() {
        const shipperSelect = document.getElementById('shipper-select');
        const loadingDiv = document.getElementById('shipper-loading');
        
        // Hiện loading
        loadingDiv.style.display = 'block';
        shipperSelect.disabled = true;
        
        try {
            // Dùng cache nếu có
            if (shippersCache) {
                populateShipperSelect(shippersCache);
                return;
            }
            
            const response = await fetch(CONFIG.routes.getShippers, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                shippersCache = result.data; // Lưu kết quả vào cache
                populateShipperSelect(result.data);
            } else {
                showToast('Không thể tải danh sách shipper.', 'error', 'Lỗi tải dữ liệu');
            }
        } catch (error) {
            console.error('Error loading shippers:', error);
            showToast('Lỗi kết nối khi tải danh sách shipper.', 'error', 'Lỗi kết nối');
        } finally {
            loadingDiv.style.display = 'none';
            shipperSelect.disabled = false;
        }
    }

    function populateShipperSelect(shippers) {
        const shipperSelect = document.getElementById('shipper-select');
        
        // Xóa các lựa chọn cũ trừ lựa chọn đầu tiên
        shipperSelect.innerHTML = '<option value="">-- Chọn Shipper --</option>';
        
        // Thêm lựa chọn shipper
        shippers.forEach(shipper => {
            const option = document.createElement('option');
            option.value = shipper.id;
            option.textContent = `${shipper.name} - ${shipper.email}`;
            shipperSelect.appendChild(option);
        });
    }

    // Xử lý gửi form gán shipper
    document.getElementById('assign-shipper-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!currentAssignOrderId) {
            showToast('Không xác định được đơn hàng cần gán shipper.', 'error', 'Lỗi hệ thống');
            return;
        }

        const formData = new FormData(e.target);
        const shipperId = formData.get('shipper_id');
        
        if (!shipperId) {
            showToast('Vui lòng chọn shipper để gán.', 'warning', 'Thiếu thông tin');
            return;
        }
        
        try {
            const response = await fetch(CONFIG.routes.assignShipper.replace(':id', currentAssignOrderId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    shipper_id: shipperId
                })
            });

            const result = await response.json();
            
            if (response.ok && result.success) {
                showToast(`Đã gán shipper "${result.data.shipper.name}" cho đơn hàng thành công!`, 'success', 'Gán shipper thành công');
                
                // Đánh dấu đơn hàng này đã xem (bỏ đánh dấu "mới")
                markOrderAsViewed(currentAssignOrderId);
                
                // Đóng modal
                closeAssignShipperModal();
                
                // Làm mới trang hiện tại
                refreshCurrentPage();
            } else {
                if (response.status === 422) {
                    showToast(result.message || 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', 'error', 'Dữ liệu không hợp lệ');
                } else if (response.status === 403) {
                    showToast('Bạn không có quyền thực hiện hành động này.', 'error', 'Không có quyền');
                } else if (response.status === 404) {
                    showToast('Không tìm thấy đơn hàng hoặc shipper.', 'error', 'Không tìm thấy');
                } else {
                    showToast(result.message || 'Không thể gán shipper. Vui lòng thử lại.', 'error', 'Gán shipper thất bại');
                }
            }
        } catch (error) {
            console.error('Error assigning shipper:', error);
            if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                showToast('Mất kết nối mạng. Vui lòng kiểm tra internet và thử lại.', 'error', 'Lỗi kết nối');
            } else {
                showToast('Lỗi hệ thống không xác định. Vui lòng thử lại sau.', 'error', 'Lỗi hệ thống');
            }
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


    });

    </script>
@endsection