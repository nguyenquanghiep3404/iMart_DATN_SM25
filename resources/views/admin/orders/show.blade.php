@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->order_code)

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

    .status-pending_confirmation {
        background-color: #e0e7ff;
        color: #4338ca;
    }

    .status-processing {
        background-color: #cffafe;
        color: #0891b2;
    }

    .status-shipped {
        background-color: #d1fae5;
        color: #059669;
    }

    .status-delivered {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .status-awaiting_shipment_packed {
        background-color: #fef3c7;
        color: #d97706;
    }

    .status-awaiting_shipment_assigned {
        background-color: #ddd6fe;
        color: #7c3aed;
    }

    .status-cancelled {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .payment-pending {
        background-color: #fef3c7;
        color: #d97706;
    }

    .payment-paid {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .payment-failed {
        background-color: #fee2e2;
        color: #dc2626;
    }

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
        shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Toast notification styles */
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
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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

    /* Product item styles */
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

    /* Enhanced modal table styling */
    #modal-order-items tr:hover {
        background-color: #f9fafb;
    }

    #modal-order-items img {
        transition: transform 0.2s ease;
    }

    #modal-order-items img:hover {
        transform: scale(1.05);
    }

    /* Responsive table for modal */
    @media (max-width: 768px) {
        .modal-content table {
            font-size: 14px;
        }

        .modal-content .w-16.h-16 {
            width: 48px;
            height: 48px;
        }
    }
</style>
<div class="p-6 mx-auto bg-white rounded-2xl shadow-lg" id="order-details-content">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Chi tiết đơn hàng</h1>
            <p class="text-lg text-indigo-600 font-semibold mt-1">#{{ $order->order_code }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.orders.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold flex items-center space-x-2">
                <i class="fas fa-print"></i><span>In hóa đơn</span>
            </button>
        </div>
    </div>

    <div class="py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-8">
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Thông tin khách hàng</h3>
                    <p><strong>Tên:</strong> <span class="text-gray-700">{{ $order->customer_name ?? 'N/A' }}</span></p>
                    <p><strong>Email:</strong> <span class="text-gray-700">{{ $order->customer_email ?? 'N/A' }}</span></p>
                    <p><strong>SĐT:</strong> <span class="text-gray-700">{{ $order->customer_phone ?? 'N/A' }}</span></p>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Địa chỉ giao hàng</h3>
                    <address class="not-italic text-gray-700">
                        {{ $order->shipping_address_line1 ?? 'N/A' }}<br>
                        {{ $order->shipping_ward ?? 'N/A' }}, {{ $order->shipping_district ?? 'N/A' }},<br>
                        {{ $order->shipping_city ?? 'N/A' }}
                    </address>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Ghi chú</h3>
                    <p class="text-gray-600 italic">{{ $order->notes_from_customer ?? 'Không có ghi chú.' }}</p>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-6">Tổng quan đơn hàng</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Ngày đặt</p>
                                <p class="font-semibold text-gray-800">{{ $order->created_at ? $order->created_at->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Trạng thái đơn hàng</p>
                                <span id="modal-order-status" class="status-badge status-{{ $order->status ?? 'na' }}">{{ ($order->status) }}</span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Trạng thái thanh toán</p>
                                <span class="status-badge payment-{{ $order->payment_status ?? 'na' }}">{{ ($order->payment_status) }}</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Phương thức thanh toán</p>
                                <p class="font-semibold text-gray-800">{{ $order->payment_method ?? 'N/A' }}</p>
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
                        <tbody>
                            @forelse($order->items as $item)
                            <tr class="border-b last:border-none hover:bg-gray-50">
                                <td class="p-3">
                                    {{ $item->productVariant->product->name ?? 'Sản phẩm không tồn tại' }}
                                </td>
                                <td class="p-3 text-center font-medium">{{ $item->quantity ?? 0 }}</td>
                                <td class="p-3 text-right font-medium">{{ number_format($item->price ?? 0, 0, ',', '.') }} ₫</td>
                                <td class="p-3 text-right font-semibold text-indigo-600">{{ number_format($item->total_price ?? 0, 0, ',', '.') }} ₫</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-3 text-center text-gray-500">Không có sản phẩm trong đơn hàng.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex justify-end">
                    <div class="w-full md:w-1/2">
                        <dl class="space-y-2 text-gray-700">
                            <div class="flex justify-between">
                                <dt>Tổng tiền hàng:</dt>
                                <dd class="font-medium">{{ number_format($order->sub_total ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Phí vận chuyển:</dt>
                                <dd class="font-medium">{{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Giảm giá:</dt>
                                <dd class="font-medium text-red-500">- {{ number_format($order->discount_amount ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                            <div class="flex justify-between text-xl font-bold text-gray-900 border-t pt-2 mt-2">
                                <dt>Tổng cộng:</dt>
                                <dd>{{ number_format($order->grand_total ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // --- CONFIGURATION ---
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

    // Global pagination state
    let currentPage = 1;
    let totalPages = 1;

    // --- UTILITY FUNCTIONS ---
    const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const statusMap = {
        pending_confirmation: {
            text: "Chờ xác nhận",
            class: "status-pending_confirmation"
        },
        processing: {
            text: "Đang xử lý",
            class: "status-processing"
        },
        awaiting_shipment: {
            text: "Chờ giao hàng",
            class: "status-processing"
        },
        awaiting_shipment_packed: {
            text: "Chờ vận chuyển: đã đóng gói xong",
            class: "status-awaiting_shipment_packed"
        },
        awaiting_shipment_assigned: {
            text: "Chờ vận chuyển: Đã gán shipper",
            class: "status-awaiting_shipment_assigned"
        },
        shipped: {
            text: "Đã xuất kho",
            class: "status-shipped"
        },
        out_for_delivery: {
            text: "Đang giao hàng",
            class: "status-shipped"
        },
        delivered: {
            text: "Giao thành công",
            class: "status-delivered"
        },
        cancelled: {
            text: "Đã hủy",
            class: "status-cancelled"
        },
        returned: {
            text: "Đã trả hàng",
            class: "status-cancelled"
        },
        failed_delivery: {
            text: "Giao hàng thất bại",
            class: "status-cancelled"
        }
    };

    const paymentStatusMap = {
        pending: {
            text: "Chờ thanh toán",
            class: "payment-pending"
        },
        paid: {
            text: "Đã thanh toán",
            class: "payment-paid"
        },
        failed: {
            text: "Thất bại",
            class: "payment-failed"
        },
        refunded: {
            text: "Đã hoàn tiền",
            class: "payment-failed"
        },
        partially_refunded: {
            text: "Hoàn tiền một phần",
            class: "payment-pending"
        }
    };

    // --- RENDER FUNCTIONS ---
    function renderOrderRow(order) {
        const orderStatus = statusMap[order.status] || {
            text: 'N/A',
            class: ''
        };
        const paymentStatus = paymentStatusMap[order.payment_status] || {
            text: 'N/A',
            class: ''
        };

        // Determine shipper display
        let shipperDisplay = '<span class="text-gray-400 italic">Chưa gán</span>';
        if (order.shipper && order.shipper.name) {
            shipperDisplay = `<span class="text-gray-700 font-medium">${order.shipper.name}</span>`;
        }

        // Show assign shipper button only for "awaiting_shipment_packed" status
        let assignShipperButton = '';
        if (order.status === 'awaiting_shipment_packed') {
            assignShipperButton = `
                <button onclick='showAssignShipperModal(${order.id}, "${order.order_code}")' 
                        class="text-blue-600 hover:text-blue-900 font-medium text-lg ml-4" 
                        title="Gán Shipper">
                    <i class="fas fa-user-plus"></i>
                </button>
            `;
        }

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
        if (orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center p-12 text-gray-500">Không tìm thấy đơn hàng nào.</td></tr>`;
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
            showToast('Không thể tải trang này. Vui lòng thử lại hoặc về trang trước.', 'warning', 'Tải trang thất bại');
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

        document.getElementById('modal-shipping-address').innerHTML = `
        ${order.shipping_address_line1 || 'N/A'}<br>
        ${order.shipping_ward || 'N/A'}, ${order.shipping_district || 'N/A'},<br>
        ${order.shipping_city || 'N/A'}
    `;

        document.getElementById('modal-customer-notes').textContent = order.notes_from_customer || "Không có ghi chú.";
        document.getElementById('modal-order-date').textContent = order.created_at ? formatDate(order.created_at) : 'N/A';

        const orderStatus = statusMap[order.status] || {
            text: 'N/A',
            class: ''
        };
        const modalOrderStatusEl = document.getElementById('modal-order-status');
        modalOrderStatusEl.textContent = orderStatus.text;
        modalOrderStatusEl.className = `status-badge ${orderStatus.class}`;

        const paymentStatus = paymentStatusMap[order.payment_status] || {
            text: 'N/A',
            class: ''
        };
        const modalPaymentStatusEl = document.getElementById('modal-payment-status');
        modalPaymentStatusEl.textContent = paymentStatus.text;
        modalPaymentStatusEl.className = `status-badge ${paymentStatus.class}`;

        document.getElementById('modal-payment-method').textContent = order.payment_method || 'N/A';

        // Render items
        const itemsTbody = document.getElementById('modal-order-items');
        if (order.items && Array.isArray(order.items)) {
            itemsTbody.innerHTML = order.items.map(item => {
                // Prepare product image
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

                // Prepare product link
                let productLink = '#';
                if (item.product_variant?.product?.id) {
                    productLink = '/admin/products/' + item.product_variant.product.id + '/edit';
                } else if (item.product_id) {
                    productLink = '/admin/products/' + item.product_id + '/edit';
                }

                return `
            <tr class="border-b last:border-none hover:bg-gray-50">
                <td class="p-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            ${productImage ? 
                                `<img src="${productImage}" alt="${item.product_name || 'Sản phẩm'}"
                                    class="w-full h-full object-cover"
                                    onerror="this.parentElement.innerHTML='<div class=\\'product-image-placeholder\\' style=\\'width:100%;height:100%\\'><i class=\\'fas fa-image text-2xl\\'></i></div>'">`
                                : `<div class="product-image-placeholder" style="width:100%;height:100%">
                                    <i class="fas fa-box text-2xl"></i>
                                  </div>`}
                        </div>
                        <div class="flex-1 min-w-0">
                            ${productLink !== '#' ? 
                                `<a href="${productLink}" 
                                    target="_blank" 
                                    class="font-medium text-indigo-600 hover:text-indigo-900 hover:underline line-clamp-2"
                                    title="Chỉnh sửa sản phẩm (mở tab mới)"
                                    onclick="event.stopPropagation();">
                                    ${item.product_name || 'N/A'}
                                </a>`
                                : `<span class="font-medium text-gray-800 line-clamp-2">${item.product_name || 'N/A'}</span>`}
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

    async function refreshCurrentPage() {
        // Keep current page and filters when refreshing
        const formData = new FormData();
        formData.append('page', currentPage);

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
            console.error('Error refreshing page:', error);
            showToast('Không thể làm mới dữ liệu. Vui lòng tải lại trang.', 'warning', 'Cảnh báo');
        }
    }

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
            showToast('Không thể áp dụng bộ lọc. Vui lòng thử lại hoặc làm mới trang.', 'warning', 'Lọc dữ liệu thất bại');
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
            showToast('Không thể tải danh sách đơn hàng từ server. Hiển thị dữ liệu cache.', 'warning', 'Tải dữ liệu thất bại');
            // Fallback to show initial data from server
            @if(isset($orders))
            renderTable(@json($orders->items()));
            @endif
        }
    }

    // --- TOAST NOTIFICATION SYSTEM ---
    function showToast(message, type = 'success', title = null) {
        const toastContainer = document.getElementById('toast-container');

        // Determine title and icon based on type
        let toastTitle = title;
        let icon = '';

        if (!toastTitle) {
            switch (type) {
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

        // Create toast element
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

        // Add to container
        toastContainer.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto remove after 5 seconds
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

    // --- UPDATE STATUS MODAL LOGIC ---
    const updateStatusModal = document.getElementById('update-status-modal');
    let currentOrderId = null;

    function showUpdateStatusModal(orderId, currentStatus) {
        currentOrderId = orderId;

        // Find order data to get order code
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
        document.getElementById('new-status').setAttribute('data-current-status', currentStatus);
        document.getElementById('admin-note').value = '';
        document.getElementById('cancellation-reason').value = '';

        // Show/hide cancellation reason field
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

        if (status === 'cancelled') {
            cancellationField.style.display = 'block';
            cancellationTextarea.setAttribute('required', 'required');
        } else {
            cancellationField.style.display = 'none';
            cancellationTextarea.removeAttribute('required');
        }
    }

    // Validate form before submit
    function validateStatusForm() {
        const newStatus = document.getElementById('new-status').value;
        const currentStatus = document.getElementById('new-status').getAttribute('data-current-status');

        if (!newStatus) {
            showToast('Vui lòng chọn trạng thái mới cho đơn hàng.', 'warning', 'Thiếu thông tin');
            return false;
        }
        
        // Ngăn chuyển từ 'processing' sang trạng thái khác mà không qua trạm đóng gói
        if (currentStatus === 'processing' && newStatus !== 'processing' && newStatus !== 'cancelled') {
            showToast('Đơn hàng đang xử lý phải được xác nhận tại Trạm Đóng Gói trước khi chuyển sang trạng thái khác', 'error');
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

    // Listen for status change to show/hide cancellation field
    document.getElementById('new-status').addEventListener('change', function() {
        toggleCancellationField(this.value);
    });

    // Handle update status form submission
    document.getElementById('update-status-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!currentOrderId) {
            showToast('Không xác định được đơn hàng cần cập nhật.', 'error', 'Lỗi hệ thống');
            return;
        }

        // Validate form
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
                // Show enhanced success message
                const statusText = result.data?.status_text || 'trạng thái mới';
                showToast(`Đơn hàng đã được cập nhật thành "${statusText}" thành công!`, 'success', 'Cập nhật thành công');

                // Close modal
                closeUpdateStatusModal();

                // Refresh current page instead of going to page 1
                refreshCurrentPage();
            } else {
                // Handle different types of errors
                if (response.status === 422) {
                    // Validation errors
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

    // Close modal on escape key
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && updateStatusModal.classList.contains('is-open')) {
            closeUpdateStatusModal();
        }
        if (event.key === 'Escape' && assignShipperModal.classList.contains('is-open')) {
            closeAssignShipperModal();
        }
    });

    // --- ASSIGN SHIPPER MODAL LOGIC ---
    const assignShipperModal = document.getElementById('assign-shipper-modal');
    let currentAssignOrderId = null;
    let shippersCache = null; // Cache for shippers list

    async function showAssignShipperModal(orderId, orderCode) {
        currentAssignOrderId = orderId;

        // Set order code
        document.getElementById('assign-shipper-order-code').textContent = orderCode;

        // Reset form
        document.getElementById('shipper-select').value = '';

        // Show modal
        assignShipperModal.classList.add('is-open');
        assignShipperModal.querySelector('div').classList.remove('scale-95');

        // Load shippers theo warehouse của đơn hàng
        await loadShippers(orderId);
    }

    function closeAssignShipperModal() {
        assignShipperModal.classList.remove('is-open');
        assignShipperModal.querySelector('div').classList.add('scale-95');
        currentAssignOrderId = null;
    }

    async function loadShippers(orderId = null) {
        const shipperSelect = document.getElementById('shipper-select');
        const loadingDiv = document.getElementById('shipper-loading');

        // Show loading
        loadingDiv.style.display = 'block';
        shipperSelect.disabled = true;

        try {
            // Tạo URL với order_id nếu có
            let url = CONFIG.routes.getShippers;
            if (orderId) {
                url += `?order_id=${orderId}`;
                // Reset cache khi có order_id để lấy shipper theo warehouse
                shippersCache = null;
            }
            
            // Use cache if available và không có order_id
            if (shippersCache && !orderId) {
                populateShipperSelect(shippersCache);
                return;
            }

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                if (!orderId) {
                    shippersCache = result.data; // Chỉ cache khi không có order_id
                }
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

        // Clear existing options except the first one
        shipperSelect.innerHTML = '<option value="">-- Chọn Shipper --</option>';

        // Add shipper options
        shippers.forEach(shipper => {
            const option = document.createElement('option');
            option.value = shipper.id;
            option.textContent = `${shipper.name} - ${shipper.email}`;
            shipperSelect.appendChild(option);
        });
    }

    // Handle assign shipper form submission
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

                // Close modal
                closeAssignShipperModal();

                // Refresh current page
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
        
        // Auto-refresh orders every 30 seconds to catch status updates from packing station
        setInterval(() => {
            loadOrders();
        }, 30000);
    });
</script>
@endsection