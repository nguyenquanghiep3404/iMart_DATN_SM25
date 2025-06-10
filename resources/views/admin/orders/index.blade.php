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
        
        /* Custom scrollbar for modal */
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

        /* Pagination styles */
        #pagination-controls button {
            transition: all 0.2s ease;
        }
        #pagination-controls button:hover:not(:disabled) {
            transform: translateY(-1px);
            shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                        <option value="shipped">Đã giao hàng</option>
                        <option value="delivered">Giao thành công</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                 <div>
                    <label for="payment-status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái thanh toán</label>
                    <select id="payment-status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tất cả</option>
                        <option value="pending">Chờ thanh toán</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="failed">Thất bại</option>
                    </select>
                </div>
                <div>
                    <label for="date-range" class="block text-sm font-medium text-gray-700 mb-1">Khoảng ngày</label>
                    <input type="date" id="date-range" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
             <div class="mt-4 flex justify-end space-x-3">
                <button id="clear-filters" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Xóa lọc</button>
                <button id="apply-filters" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Áp dụng</span>
                </button>
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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Customer & Address -->
                    <div class="lg:col-span-1 space-y-6">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Thông tin khách hàng</h3>
                            <p><strong>Tên:</strong> <span id="modal-customer-name"></span></p>
                            <p><strong>Email:</strong> <span id="modal-customer-email"></span></p>
                            <p><strong>SĐT:</strong> <span id="modal-customer-phone"></span></p>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Địa chỉ giao hàng</h3>
                            <address class="not-italic" id="modal-shipping-address"></address>
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

    <script>
    // --- CONFIGURATION ---
    const CONFIG = {
        routes: {
            index: '{{ route("admin.orders.index") }}',
            show: '{{ route("admin.orders.show", ":id") }}',
        },
        csrfToken: '{{ csrf_token() }}'
    };

    // Global pagination state
    let currentPage = 1;
    let totalPages = 1;

    // --- UTILITY FUNCTIONS ---
    const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const statusMap = {
        pending_confirmation: { text: "Chờ xác nhận", class: "status-pending_confirmation" },
        processing: { text: "Đang xử lý", class: "status-processing" },
        shipped: { text: "Đã giao hàng", class: "status-shipped" },
        delivered: { text: "Giao thành công", class: "status-delivered" },
        cancelled: { text: "Đã hủy", class: "status-cancelled" }
    };

     const paymentStatusMap = {
        pending: { text: "Chờ thanh toán", class: "payment-pending" },
        paid: { text: "Đã thanh toán", class: "payment-paid" },
        failed: { text: "Thất bại", class: "payment-failed" }
    };

    // --- RENDER FUNCTIONS ---
    function renderOrderRow(order) {
        const orderStatus = statusMap[order.status] || { text: 'N/A', class: '' };
        const paymentStatus = paymentStatusMap[order.payment_status] || { text: 'N/A', class: '' };
        
        return `
            <tr class="bg-white border-b hover:bg-gray-50">
                <td class="p-6 font-bold text-indigo-600">${order.order_code}</td>
                <td class="p-6">
                    <div class="font-semibold">${order.customer_name}</div>
                    <div class="text-gray-500">${order.customer_email}</div>
                </td>
                <td class="p-6 font-semibold">${formatCurrency(order.grand_total)}</td>
                <td class="p-6"><span class="status-badge ${orderStatus.class}">${orderStatus.text}</span></td>
                <td class="p-6"><span class="status-badge ${paymentStatus.class}">${paymentStatus.text}</span></td>
                <td class="p-6">${formatDate(order.created_at)}</td>
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
        if (orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center p-12 text-gray-500">Không tìm thấy đơn hàng nào.</td></tr>`;
            return;
        }
        tbody.innerHTML = orders.map(renderOrderRow).join('');
    }

    // --- PAGINATION FUNCTIONS ---
    function renderPagination(paginationData) {
        const paginationInfo = document.getElementById('pagination-info');
        const paginationControls = document.getElementById('pagination-controls');
        
        // Update pagination info
        if (paginationData.total > 0) {
            paginationInfo.innerHTML = `
                Hiển thị ${paginationData.from} đến ${paginationData.to} trong tổng số ${paginationData.total} kết quả
            `;
        } else {
            paginationInfo.innerHTML = 'Không có dữ liệu';
        }
        
        // Update global state
        currentPage = paginationData.current_page;
        totalPages = paginationData.last_page;
        
        // Generate pagination controls
        let paginationHtml = '';
        
        // Previous button
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
        
        // Page numbers
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
        
        // Next button
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
        
        // Add current filters
        if (searchInput.value) formData.append('search', searchInput.value);
        if (orderStatusFilter.value) formData.append('status', orderStatusFilter.value);
        if (paymentStatusFilter.value) formData.append('payment_status', paymentStatusFilter.value);
        if (dateFilter.value) formData.append('date_range', dateFilter.value);

        try {
            const response = await fetch(CONFIG.routes.index + '?' + new URLSearchParams(formData), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });
            
            const result = await response.json();
            if (result.success) {
                renderTable(result.data);
                renderPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error loading page:', error);
        }
    }

    // --- MODAL LOGIC ---
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
            alert('Có lỗi xảy ra khi tải thông tin đơn hàng');
        }
    }

    function populateModal(order) {
        document.getElementById('modal-order-code').textContent = order.order_code || 'N/A';
        document.getElementById('modal-customer-name').textContent = order.customer_name || 'N/A';
        document.getElementById('modal-customer-email').textContent = order.customer_email || 'N/A';
        document.getElementById('modal-customer-phone').textContent = order.customer_phone || 'N/A';
        
        document.getElementById('modal-shipping-address').innerHTML = `
            ${order.shipping_address_line1 || 'N/A'}<br>
            ${order.shipping_ward || 'N/A'}, ${order.shipping_district || 'N/A'},<br>
            ${order.shipping_city || 'N/A'}
        `;

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

        // Render items
        const itemsTbody = document.getElementById('modal-order-items');
        if (order.items && Array.isArray(order.items)) {
            itemsTbody.innerHTML = order.items.map(item => `
                <tr class="border-b last:border-none">
                    <td class="p-3 font-medium">${item.product_name || 'N/A'}</td>
                    <td class="p-3 text-center">${item.quantity || 0}</td>
                    <td class="p-3 text-right">${formatCurrency(item.price || 0)}</td>
                    <td class="p-3 text-right font-semibold">${formatCurrency(item.total_price || 0)}</td>
                </tr>
            `).join('');
        } else {
            itemsTbody.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-gray-500">Không có sản phẩm</td></tr>';
        }

        // Render totals
        document.getElementById('modal-sub-total').textContent = formatCurrency(order.sub_total || 0);
        document.getElementById('modal-shipping-fee').textContent = formatCurrency(order.shipping_fee || 0);
        document.getElementById('modal-discount').textContent = `- ${formatCurrency(order.discount_amount || 0)}`;
        document.getElementById('modal-grand-total').textContent = formatCurrency(order.grand_total || 0);
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.querySelector('div').classList.add('scale-95');
    }
    
    // Close modal on escape key press
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // --- FILTERING LOGIC ---
    const searchInput = document.getElementById('search');
    const orderStatusFilter = document.getElementById('order-status');
    const paymentStatusFilter = document.getElementById('payment-status');
    const dateFilter = document.getElementById('date-range');
    
    async function applyFilters() {
        const formData = new FormData();
        
        // Reset to page 1 when applying filters
        formData.append('page', 1);
        
        if (searchInput.value) formData.append('search', searchInput.value);
        if (orderStatusFilter.value) formData.append('status', orderStatusFilter.value);
        if (paymentStatusFilter.value) formData.append('payment_status', paymentStatusFilter.value);
        if (dateFilter.value) formData.append('date_range', dateFilter.value);

        try {
            const response = await fetch(CONFIG.routes.index + '?' + new URLSearchParams(formData), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });
            
            const result = await response.json();
            if (result.success) {
                renderTable(result.data);
                renderPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error applying filters:', error);
        }
    }
    
    document.getElementById('apply-filters').addEventListener('click', applyFilters);
    document.getElementById('clear-filters').addEventListener('click', () => {
        searchInput.value = '';
        orderStatusFilter.value = '';
        paymentStatusFilter.value = '';
        dateFilter.value = '';
        loadOrders();
    });

    // --- INITIAL LOAD ---
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
                renderTable(result.data);
                renderPagination(result.pagination);
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            // Fallback to show initial data from server
            @if(isset($orders))
                renderTable(@json($orders->items()));
            @endif
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if(isset($orders))
            renderTable(@json($orders->items()));
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