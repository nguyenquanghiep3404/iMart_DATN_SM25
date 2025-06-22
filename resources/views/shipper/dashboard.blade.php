@extends('layouts.shipper')

@section('title', 'Dashboard Giao Hàng')

@push('styles')
<style>
    /* CSS cho tab và danh sách đơn hàng */
    .tab-nav { display: flex; border-bottom: 1px solid #dee2e6; margin-bottom: 1.5rem; }
    .tab-button {
        padding: 12px 20px; border: none; background-color: transparent;
        font-size: 1rem; font-weight: 600; cursor: pointer;
        color: #6c757d; border-bottom: 3px solid transparent;
    }
    .tab-button.active { color: #007bff; border-bottom-color: #007bff; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .order-list-item {
        background: #fff; border: 1px solid #e9ecef; border-radius: 8px;
        padding: 1rem; margin-bottom: 1rem; display: flex;
        justify-content: space-between; align-items: center;
    }
    .order-info .order-code { font-weight: 600; color: #343a40; }
    .order-info .address { color: #6c757d; font-size: 0.9rem; margin-top: 5px; }
    .order-actions .btn-detail {
        background-color: #007bff; color: white; text-decoration: none;
        padding: 8px 16px; border-radius: 5px; font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
    <h2>Các đơn hàng được gán</h2>

    <nav class="tab-nav">
        <button class="tab-button active" data-tab="pickup">Cần lấy hàng ({{ $ordersToPickup->count() }})</button>
        <button class="tab-button" data-tab="transit">Đang giao ({{ $ordersInTransit->count() }})</button>
        <button class="tab-button" data-tab="history">Lịch sử</button>
    </nav>

    <div id="pickup" class="tab-content active">
        @forelse ($ordersToPickup as $order)
            <div class="order-list-item">
                <div class="order-info">
                    <div class="order-code">Mã ĐH: {{ $order->order_code }}</div>
                    <div class="address">{{ $order->customer_name }} - {{ $order->shipping_address_line1 }}</div>
                </div>
                <div class="order-actions">
                    <a href="{{ route('shipper.orders.show', $order) }}" class="btn-detail">Xem chi tiết</a>
                </div>
            </div>
        @empty
            <p>Không có đơn hàng nào cần lấy.</p>
        @endforelse
    </div>

    <div id="transit" class="tab-content">
         @forelse ($ordersInTransit as $order)
            <div class="order-list-item">
                <div class="order-info">
                    <div class="order-code">Mã ĐH: {{ $order->order_code }}</div>
                    <div class="address">{{ $order->customer_name }} - {{ $order->shipping_address_line1 }}</div>
                </div>
                <div class="order-actions">
                    <a href="{{ route('shipper.orders.show', $order) }}" class="btn-detail">Xem chi tiết</a>
                </div>
            </div>
        @empty
            <p>Không có đơn hàng nào đang giao.</p>
        @endforelse
    </div>

    <div id="history" class="tab-content">
        @forelse ($ordersHistory as $order)
            <div class="order-list-item">
                <div class="order-info">
                    <div class="order-code">Mã ĐH: {{ $order->order_code }}</div>
                    <div class="address">Trạng thái: {{ $order->status }}</div>
                </div>
                <div class="order-actions">
                    <a href="{{ route('shipper.orders.show', $order) }}" class="btn-detail">Xem lại</a>
                </div>
            </div>
        @empty
            <p>Lịch sử giao hàng trống.</p>
        @endforelse
    </div>
@endsection

@push('page-scripts')
<script>
    // CHỈ GIỮ LẠI SCRIPT CHUYỂN TAB Ở ĐÂY
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });
    });
</script>
@endpush
