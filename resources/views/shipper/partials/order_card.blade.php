<!-- {{-- File: resources/views/shipper/partials/order_card.blade.php (Phiên bản mới, gọn gàng) --}}

<div class="order-list-item">
    {{-- Phần thông tin tóm tắt bên trái --}}
    <div class="order-info">
        <div class="order-code">Mã ĐH: {{ $order->order_code }}</div>
        <div class="customer-name">{{ $order->customer_name }}</div>
    </div>

    {{-- Phần nút hành động bên phải --}}
    <div class="order-actions">
        <a href="{{ route('shipper.orders.show', $order) }}" class="btn-detail" title="Xem chi tiết">
            {{-- Icon con mắt SVG --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
            </svg>
        </a>
    </div>
</div> -->
