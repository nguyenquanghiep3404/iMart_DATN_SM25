@extends('users.layouts.app')

@section('content')
<style>
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }

    /* --- PHẦN ĐÃ SỬA --- */

    /* Container chính của timeline */
    .timeline {
        position: relative;
        /* Cần thiết để định vị đường kẻ dọc */
        list-style: none;
        padding: 0.5rem 0;
        /* Đã bỏ 'display: flex' để các item tự động xếp dọc */
    }

    /* Đường kẻ dọc */
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 11px;
        /* Căn đường kẻ vào giữa chấm tròn (24px / 2 - 1px) */
        width: 2px;
        /* Đổi 'height' thành 'width' */
        background-color: #e5e7eb;
        z-index: 1;
    }

    /* Mỗi mục trong timeline */
    .timeline-item {
        position: relative;
        z-index: 2;
        padding-left: 40px;
        /* Tạo không gian cho chấm tròn và đường kẻ */
        margin-bottom: 2rem;
        /* Thêm khoảng cách dọc giữa các bước */
        width: 100%;
        /* Bỏ 'width: 25%' */
        text-align: left;
        /* Chuyển về căn lề trái */
    }

    /* Bỏ khoảng cách cho mục cuối cùng */
    .timeline-item:last-child {
        margin-bottom: 0;
    }

    /* Chấm tròn trên đường kẻ */
    .timeline-dot {
        position: absolute;
        /* Định vị nó trên đường kẻ */
        left: 0;
        top: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #e5e7eb;
        border: 2px solid #e5e7eb;
        margin: 0;
        /* Bỏ margin cũ */
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
    }

    /* --- HẾT PHẦN SỬA (Các style bên dưới giữ nguyên) --- */

    .timeline-label {
        font-size: 0.9rem;
        /* Tăng kích thước chữ một chút cho dễ đọc */
        color: #6b7280;
        margin-bottom: 4px;
        /* Thêm khoảng cách nhỏ */
    }

    .timeline-date {
        font-size: 0.8rem;
        color: #9ca3af;
        min-height: 1.05rem;
    }

    .timeline-item.completed .timeline-dot {
        background-color: #dc2626;
        border-color: #dc2626;
        color: white;
    }

    .timeline-item.completed .timeline-label {
        font-weight: 600;
        color: #374151;
    }

    .timeline-item.completed .timeline-date {
        color: #4b5563;
    }

    .timeline-item.current .timeline-dot {
        border-color: #dc2626;
        background-color: #fff;
        transform: scale(1.2);
    }

    .timeline-item.current .timeline-dot::after {
        content: '';
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #dc2626;
        display: block;
    }

    .timeline-item.current .timeline-label {
        font-weight: 600;
        color: #111827;
    }

    /* Thêm đoạn này vào cuối thẻ <style> của bạn */

    .timeline-item.current .timeline-dot>.fas {
        display: none;
    }
</style>
<div class="container mx-auto p-4 sm:p-6 lg:p-8 max-w-4xl">

    <header class="text-center mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Tra Cứu Thông Tin Đơn Hàng</h1>
        <p class="mt-2 text-gray-600">Dành cho khách hàng mua sắm không cần tài khoản.</p>
    </header>

    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form id="order-lookup-form">
            <label for="order_code" class="block text-sm font-medium text-gray-700 mb-2">Nhập mã đơn hàng của bạn</label>
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="text" id="order_code" name="order_code" class="flex-grow w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-red-500 focus:border-red-500 transition" placeholder="Ví dụ: IMART1001, IMART1002, IMART1003" required>
                <button type="submit" id="lookup-button" class="w-full sm:w-auto bg-red-600 text-white font-semibold px-6 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                    <span id="button-text">Tra cứu</span>
                    <span id="button-spinner" class="hidden">
                        <svg class="animate-spin h-5 w-5 text-white mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </div>

    <div id="order-result" class="hidden">
        <div id="order-details-card" class="bg-white p-6 sm:p-8 rounded-lg shadow-md fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Chi tiết đơn hàng: <span id="display-order-code" class="text-red-600"></span></h2>

            <div class="mb-8">
                <h3 class="font-semibold text-lg mb-4">Lịch sử đơn hàng</h3>
                <ol id="order-timeline" class="timeline"></ol>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8 pt-6 border-t">
                <div>
                    <h3 class="font-semibold text-lg mb-2">Thông tin khách hàng</h3>
                    <div class="space-y-1 text-gray-600">
                        <p><strong>Họ tên:</strong> <span id="customer-name"></span></p>
                        <p><strong>Email:</strong> <span id="customer-email"></span></p>
                        <p><strong>Điện thoại:</strong> <span id="customer-phone"></span></p>
                    </div>
                </div>
                <div>
                    <h3 id="shipping-address-title" class="font-semibold text-lg mb-2">Địa chỉ giao hàng</h3>
                    <p class="text-gray-600" id="shipping-address"></p>
                    <p class="font-semibold text-gray-600">Phường/Xã: <span id="shipping-ward"></span></p>
                    <p class="font-semibold text-gray-600">Quận/Huyện: <span id="shipping-district"></span></p>
                    <p class="font-semibold text-gray-600">Tỉnh/TP: <span id="shipping-province"></span></p>
                </div>
            </div>

            <!-- Delivery/Pickup Information -->
            <div id="delivery-info-section" class="mt-6 hidden">
                <h3 class="font-semibold text-lg mb-2" id="delivery-info-title">Thông tin giao/nhận hàng</h3>
                <div class="text-gray-600 space-y-1">
                    <p><strong>Ngày nhận dự kiến:</strong> <span id="delivery-date"></span></p>
                    <p><strong>Khung giờ:</strong> <span id="delivery-time-slot"></span></p>
                </div>
                <div id="pickup-warning" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-md text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Đơn hàng của quý khách đã quá hẹn lấy. Đơn hàng sẽ được giữ thêm 3 ngày trước khi tự động hủy.
                </div>
            </div>
            <div class="mt-8 pt-6 border-t">
                <h3 class="font-semibold text-lg mb-4">Thông tin thanh toán</h3>
                <dl class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">Phương thức</dt>
                        <dd id="payment-method" class="font-medium"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-semibold text-gray-600">Trạng thái</dt>
                        <dd id="payment-status" class="inline-block text-sm font-semibold px-3 py-1 rounded-full"></dd>
                    </div>
                </dl>
            </div>

            <div class="mt-8">
                <h3 class="font-semibold text-lg mb-4">Sản phẩm đã mua</h3>
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Sản phẩm</th>
                                        <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Số lượng</th>
                                        <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Đơn giá</th>
                                        <th scope="col" class="py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900 sm:pr-0">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody id="order-items-table" class="divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t pt-6">
                <dl class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                        <dt>Tạm tính</dt>
                        <dd class="font-medium" id="sub-total"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Phí vận chuyển</dt>
                        <dd class="font-medium" id="shipping-fee"></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>Giảm giá</dt>
                        <dd class="font-medium text-red-500" id="discount-amount"></dd>
                    </div>
                    <div class="flex justify-between text-base font-bold text-gray-900">
                        <dt>Tổng cộng</dt>
                        <dd id="grand-total"></dd>
                    </div>
                </dl>
            </div>

            <div id="action-buttons" class="mt-8 pt-6 border-t flex flex-col sm:flex-row gap-3 justify-end">
                <a href="mailto:support@imart.com" class="w-full sm:w-auto text-center px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition">
                    <i class="fas fa-headset mr-2"></i>Yêu cầu hỗ trợ
                </a>
                <button id="print-button" class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition">
                    <i class="fas fa-print mr-2"></i>In đơn hàng
                </button>
            </div>
        </div>
    </div>

    <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative fade-in" role="alert">
        <strong class="font-bold">Lỗi!</strong>
        <span class="block sm:inline" id="error-text"></span>
    </div>
    {{-- Hiển thị thông báo --}}
    @if (session('success'))
    <div class="mb-4 p-4 rounded-md bg-green-100 text-green-700">
        {{ session('success') }}
    </div>
    @endif

    @if (session('warning'))
    <div class="mb-4 p-4 rounded-md bg-yellow-100 text-yellow-700">
        {{ session('warning') }}
    </div>
    @endif

    @if (session('error'))
    <div class="mb-4 p-4 rounded-md bg-red-100 text-red-700">
        {{ session('error') }}
    </div>
    @endif

</div>
<script>
    // --- MOCK DATA ---
    async function fetchOrderData(code) {
        const response = await fetch("{{ route('guest.orders.ajax') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                order_code: code
            })
        });

        if (!response.ok) {
            return null;
        }

        return await response.json();
    }


    const form = document.getElementById('order-lookup-form');
    const orderCodeInput = document.getElementById('order_code');
    const resultDiv = document.getElementById('order-result');
    const errorDiv = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');
    const lookupButton = document.getElementById('lookup-button');
    const buttonText = document.getElementById('button-text');
    const buttonSpinner = document.getElementById('button-spinner');

    function formatCurrency(number) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(number);
    }

    function formatTimelineDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes} ${day}/${month}`;
    }

    //--- CODE JAVASCRIPT ĐÃ SỬA ---
    //--- PHIÊN BẢN SỬA LỖI CUỐI CÙNG ---
    function renderOrderTimeline(status, timestamps, shippingMethod) {
        const timelineContainer = document.getElementById('order-timeline');
        timelineContainer.innerHTML = '';

        const isPickup = shippingMethod === 'pickup_at_store';

        const statusToStageMap = {
            pending_confirmation: 'pending_confirmation',
            processing: 'processing',
            awaiting_shipment: 'shipped',
            shipped: 'shipped',
            out_for_delivery: 'shipped',
            delivered: 'delivered'
        };

        const stages = [{
                id: 'pending_confirmation',
                label: 'Đã xác nhận',
                icon: 'fa-check',
                time: timestamps.created_at
            },
            {
                id: 'processing',
                label: 'Đang xử lý',
                icon: 'fa-box-open',
                time: timestamps.processed_at
            },
            {
                id: 'shipped',
                label: isPickup ? 'Sẵn sàng để nhận' : 'Đang giao',
                icon: isPickup ? 'fa-store' : 'fa-truck',
                time: timestamps.shipped_at
            },
            {
                id: 'delivered',
                label: isPickup ? 'Đã nhận hàng' : 'Đã giao',
                icon: 'fa-house-chimney',
                time: timestamps.delivered_at
            }
        ];

        const currentStageId = statusToStageMap[status];
        const currentStageIndex = stages.findIndex(s => s.id === currentStageId);

        stages.forEach((stage, index) => {
            const item = document.createElement('li');
            item.classList.add('timeline-item');

            // --- LOGIC ĐÃ ĐƯỢC SỬA ĐÚNG ---
            if (currentStageIndex > -1) {
                if (index < currentStageIndex) {
                    // Các bước trong quá khứ
                    item.classList.add('completed');
                } else if (index === currentStageIndex) {
                    // Bước hiện tại -> CHỈ CÓ 'current'
                    // Dòng này là thay đổi quan trọng nhất.
                    item.classList.add('current');
                }
                // Các bước tương lai không thêm class nào, sẽ dùng style mặc định.
            }
            // --- KẾT THÚC SỬA LỖI ---

            item.innerHTML = `
                <div class="timeline-dot">
                    <i class="fas ${stage.icon}"></i>
                </div>
                <div class="timeline-label">${stage.label}</div>
                <div class="timeline-date">${formatTimelineDate(stage.time)}</div>
            `;
            timelineContainer.appendChild(item);
        });
    }

    function displayOrderDetails(orderData) {
        document.getElementById('display-order-code').textContent = orderData.order_code;
        document.getElementById('customer-name').textContent = orderData.customer_name;
        document.getElementById('customer-email').textContent = orderData.customer_email;
        document.getElementById('customer-phone').textContent = orderData.customer_phone;
        document.getElementById('shipping-address').textContent = `${orderData.shipping_address_line1}`;
        document.getElementById('shipping-ward').textContent = orderData.shipping_ward || '';
        document.getElementById('shipping-district').textContent = orderData.shipping_district || '';
        document.getElementById('shipping-province').textContent = orderData.shipping_province || '';

        // Hiển thị thông tin thanh toán
        document.getElementById('payment-method').textContent = orderData.payment_method || '---';
        const paymentStatusEl = document.getElementById('payment-status');
        if (orderData.payment_status === 'paid') {
            paymentStatusEl.textContent = 'Đã thanh toán';
            paymentStatusEl.className = 'inline-block text-sm font-semibold px-3 py-1 rounded-full bg-green-100 text-green-800';
        } else if (orderData.payment_status === 'pending') {
            paymentStatusEl.textContent = 'Chưa thanh toán';
            paymentStatusEl.className = 'inline-block text-sm font-semibold px-3 py-1 rounded-full bg-yellow-100 text-yellow-800';
        } else if (orderData.payment_status === 'failed') {
            paymentStatusEl.textContent = 'Thanh toán thất bại';
            paymentStatusEl.className = 'inline-block text-sm font-semibold px-3 py-1 rounded-full bg-red-100 text-red-800';
        } else if (orderData.payment_status === 'refunded') {
            paymentStatusEl.textContent = 'Đã hoàn tiền';
            paymentStatusEl.className = 'inline-block text-sm font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-800';
        } else if (orderData.payment_status === 'partially_refunded') {
            paymentStatusEl.textContent = 'Hoàn tiền một phần';
            paymentStatusEl.className = 'inline-block text-sm font-semibold px-3 py-1 rounded-full bg-purple-100 text-purple-800';
        } else {
            paymentStatusEl.textContent = '---';
            paymentStatusEl.className = 'inline-block text-sm font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-800';
        }


        renderOrderTimeline(orderData.status, orderData.timestamps, orderData.shipping_method);

        const deliveryInfoSection = document.getElementById('delivery-info-section');
        const deliveryInfoTitle = document.getElementById('delivery-info-title');
        const deliveryDateEl = document.getElementById('delivery-date');
        const deliveryTimeSlotEl = document.getElementById('delivery-time-slot');
        const pickupWarningEl = document.getElementById('pickup-warning');
        const shippingAddressTitle = document.getElementById('shipping-address-title');

        if (orderData.desired_delivery_date) {
            deliveryDateEl.textContent = new Date(orderData.desired_delivery_date).toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            deliveryTimeSlotEl.textContent = orderData.desired_delivery_time_slot;
            deliveryInfoSection.classList.remove('hidden');

            if (orderData.shipping_method === 'pickup_at_store') {
                deliveryInfoTitle.textContent = 'Thông tin nhận hàng';
                shippingAddressTitle.textContent = 'Địa điểm nhận hàng';

                const pickupDate = new Date(orderData.desired_delivery_date);
                const today = new Date();

                pickupDate.setHours(0, 0, 0, 0);
                today.setHours(0, 0, 0, 0);

                if (today > pickupDate && orderData.status !== 'delivered' && orderData.status !== 'cancelled') {
                    pickupWarningEl.classList.remove('hidden');
                } else {
                    pickupWarningEl.classList.add('hidden');
                }
            } else {
                deliveryInfoTitle.textContent = 'Thông tin giao hàng';
                shippingAddressTitle.textContent = 'Địa chỉ giao hàng';
                pickupWarningEl.classList.add('hidden');
            }
        } else {
            deliveryInfoSection.classList.add('hidden');
            pickupWarningEl.classList.add('hidden');
        }

        const itemsTable = document.getElementById('order-items-table');
        itemsTable.innerHTML = '';
        orderData.items.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
    <td class="py-4 pl-4 pr-3 text-sm sm:pl-0">
        <div class="flex items-center gap-3">
            <img src="${item.image_url}" alt="${item.product_name}" class="w-12 h-12 rounded object-cover border" onerror="this.src='/images/placeholder.png'">
            <div class="font-medium text-gray-900">${item.product_name}</div>
        </div>
    </td>
    <td class="px-3 py-4 text-sm text-center text-gray-500">${item.quantity}</td>
    <td class="px-3 py-4 text-sm text-right text-gray-500">${formatCurrency(item.price)}</td>
    <td class="py-4 pl-3 pr-4 text-sm text-right font-medium text-gray-900 sm:pr-0">${formatCurrency(item.price * item.quantity)}</td>
`;

            itemsTable.appendChild(row);
        });

        document.getElementById('sub-total').textContent = formatCurrency(orderData.sub_total);
        document.getElementById('shipping-fee').textContent = formatCurrency(orderData.shipping_fee);
        document.getElementById('discount-amount').textContent = `- ${formatCurrency(orderData.discount_amount)}`;
        document.getElementById('grand-total').textContent = formatCurrency(orderData.grand_total);

        const actionButtons = document.getElementById('action-buttons');
        actionButtons.replaceChildren();

        const reorderForm = document.createElement('form');
        reorderForm.action = `/orders/reorder/${orderData.order_code}`;
        reorderForm.method = 'POST';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';

        const reorderButton = document.createElement('button');
        reorderButton.type = 'submit';
        reorderButton.id = 'reorder-button';
        reorderButton.className = 'w-full sm:w-auto px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-md hover:bg-red-700 transition';
        reorderButton.innerHTML = '<i class="fas fa-redo mr-2"></i>Đặt lại đơn hàng';

        reorderForm.appendChild(csrfInput);
        reorderForm.appendChild(reorderButton);

        document.getElementById('action-buttons').appendChild(reorderForm);


        errorDiv.classList.add('hidden');
        resultDiv.classList.remove('hidden');

    }


    function displayError(message) {
        errorText.textContent = message;
        resultDiv.classList.add('hidden');
        errorDiv.classList.remove('hidden');
    }

    function toggleLoading(isLoading) {
        lookupButton.disabled = isLoading;
        buttonText.classList.toggle('hidden', isLoading);
        buttonSpinner.classList.toggle('hidden', !isLoading);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const orderCode = orderCodeInput.value.trim();
        if (!orderCode) {
            displayError("Vui lòng nhập mã đơn hàng.");
            return;
        }
        toggleLoading(true);
        try {
            const orderData = await fetchOrderData(orderCode);
            if (orderData) {
                displayOrderDetails(orderData);
            } else {
                displayError(`Không tìm thấy đơn hàng với mã "${orderCode}". Vui lòng kiểm tra lại.`);
            }
        } catch (err) {
            console.error("Lookup failed:", err);
            displayError("Đã có lỗi xảy ra trong quá trình tra cứu. Vui lòng thử lại sau.");
        } finally {
            toggleLoading(false);
        }
    });

    document.getElementById('print-button').addEventListener('click', () => {
        window.print();
    });
    document.getElementById('reorder-button').addEventListener('click', () => {
        alert('Trong ứng dụng thực tế, chức năng này sẽ thêm các sản phẩm của đơn hàng vào lại giỏ hàng của bạn.');
    });
</script>
@endsection