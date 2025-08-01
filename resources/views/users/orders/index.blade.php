{{-- resources/views/users/orders/index.blade.php --}}
@extends('users.layouts.profile')

@section('styles')
<style>
    /* CSS được trích xuất và tùy chỉnh từ file mẫu */
    .order-card {
        background-color: #fff;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .order-card-header {
        background-color: #f9fafb;
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    .order-card-header-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .order-card-body {
        padding: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .order-card-footer {
        background-color: #f9fafb;
        padding: 1rem;
        text-align: right;
    }
    .product-image {
        width: 80px;
        height: 80px;
        border-radius: 0.375rem;
        object-fit: cover;
        flex-shrink: 0;
        background-color: #e5e7eb;
    }
    .total-amount {
        text-align: right;
        margin-left: auto;
    }
    .total-amount-label {
        font-size: 0.875rem;
        color: #6b7280;
    }
    .total-amount-value {
        font-size: 1.125rem;
        font-weight: bold;
        color: #dc2626;
    }
    .btn-view-details {
        background-color: #dc2626;
        color: #fff;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: background-color 0.2s;
        display: inline-block;
        border: none;
    }
    .btn-view-details:hover {
        background-color: #b91c1c;
        color: #fff;
    }
    .btn-secondary {
        background-color: #fff;
        color: #374151;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: background-color 0.2s;
        border: 1px solid #d1d5db;
        display: inline-block;
    }
    .btn-secondary:hover {
        background-color: #f3f4f6;
    }
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .status-completed { background-color: #d1fae5; color: #065f46; } /* Hoàn tất */
    .status-processing { background-color: #feefc7; color: #92400e; } /* Đang xử lý */
    .status-shipping { background-color: #dbeafe; color: #1e40af; } /* Đang giao */
    .status-cancelled { background-color: #fee2e2; color: #991b1b; } /* Đã hủy */
    .status-awaiting-pickup { background-color: #e5e0ff; color: #5b21b6; } /* Chờ lấy hàng */
    .status-returned { background-color: #e5e7eb; color: #4b5563; } /* Trả hàng */
    .tab-link {
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
        transition: border-color 0.2s, color 0.2s;
        color: #6b7280;
        text-decoration: none;
        font-weight: 600;
    }
    .tab-link.active, .tab-link:hover {
        color: #dc2626;
        border-color: #dc2626;
    }
    .empty-orders-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 60px 20px;
        background-color: #f9fafb;
        border-radius: 8px;
        margin-top: 2rem;
        text-align: center;
    }
    .empty-orders-container img {
        width: 250px;
        margin-bottom: 1.5rem;
    }
    .empty-orders-container p {
        font-size: 1.1rem;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="profile-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0 fw-bold">Đơn hàng của tôi</h3>
        <div class="search-bar" style="max-width: 400px; margin-left: auto;">
             <form action="{{ route('orders.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Tìm theo mã đơn hàng, sản phẩm..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Các tab lọc trạng thái --}}
    <div class="border-bottom mb-4">
        <nav class="d-flex flex-wrap" style="gap: 1.5rem;">
            <a href="{{ route('orders.index') }}" class="tab-link {{ empty($status) ? 'active' : '' }}">Tất cả</a>
            <a href="{{ route('orders.index', ['status' => 'pending_confirmation']) }}" class="tab-link {{ $status == 'pending_confirmation' ? 'active' : '' }}">Chờ xác nhận</a>
            <a href="{{ route('orders.index', ['status' => 'processing']) }}" class="tab-link {{ $status == 'processing' ? 'active' : '' }}">Đang xử lý</a>
            <a href="{{ route('orders.index', ['status' => 'shipped']) }}" class="tab-link {{ $status == 'shipped' ? 'active' : '' }}">Đang giao</a>
            <a href="{{ route('orders.index', ['status' => 'delivered']) }}" class="tab-link {{ $status == 'delivered' ? 'active' : '' }}">Hoàn tất</a>
            <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="tab-link {{ $status == 'cancelled' ? 'active' : '' }}">Đã hủy</a>
            <a href="{{ route('orders.index', ['status' => 'returned']) }}" class="tab-link {{ $status == 'returned' ? 'active' : '' }}">Trả hàng</a>
        </nav>
    </div>

    @if($orders->isEmpty())
        <div class="empty-orders-container">
            <img src="https://fptshop.com.vn/img/empty_state.png?w=640&q=75" alt="Không có đơn hàng">
            <p>Bạn chưa có đơn hàng nào.</p>
        </div>
    @else
        <div class="order-list-container">
            @foreach ($orders as $order)
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-card-header-info">
                            <span class="fw-bold text-dark">Đơn hàng #{{ $order->order_code }}</span>
                            <span class="text-secondary d-none d-sm-inline">|</span>
                            <span class="text-muted small">Ngày đặt: {{ $order->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div>
                             @php
                                $statusClass = '';
                                $statusText = '';
                                switch ($order->status) {
                                    case 'delivered':
                                        $statusClass = 'status-completed'; $statusText = 'Hoàn tất'; break;
                                    case 'processing':
                                        $statusClass = 'status-processing'; $statusText = 'Đang xử lý'; break;
                                    case 'shipped': case 'out_for_delivery':
                                        $statusClass = 'status-shipping'; $statusText = 'Đang giao'; break;
                                    case 'cancelled': case 'failed_delivery':
                                        $statusClass = 'status-cancelled'; $statusText = 'Đã hủy'; break;
                                    case 'awaiting_shipment': case 'awaiting_pickup':
                                        $statusClass = 'status-awaiting-pickup'; $statusText = 'Chờ lấy hàng'; break;
                                    case 'returned':
                                        $statusClass = 'status-returned'; $statusText = 'Trả hàng'; break;
                                    default:
                                        $statusClass = 'status-returned'; $statusText = ucfirst($order->status); break;
                                }
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                        </div>
                    </div>
                    <div class="order-card-body">
                        @php
                            $firstItem = $order->items->first();
                        @endphp
                        <img src="{{ $firstItem->image_url ?? 'https://placehold.co/80x80/e2e8f0/333?text=Sản+phẩm' }}" alt="Product Image" class="product-image">
                        <div class="flex-grow-1">
                            <p class="fw-bold text-dark mb-1">{{ $firstItem->product_name ?? 'Sản phẩm không có tên' }}</p>
                            @if($order->items->count() > 1)
                                <p class="small text-muted">và {{ $order->items->count() - 1 }} sản phẩm khác</p>
                            @endif
                            {{-- Bạn có thể thêm logic cho các lưu ý đặc biệt ở đây --}}
                            {{-- <p class="small text-warning fw-medium">Lưu ý: Thay đổi ngày giao</p> --}}
                        </div>
                        <div class="total-amount">
                            <p class="total-amount-label">Tổng tiền</p>
                            <p class="total-amount-value">{{ number_format($order->grand_total, 0, ',', '.') }} VNĐ</p>
                        </div>
                    </div>
                    <div class="order-card-footer">
                        @if($order->status == 'delivered' || $order->status == 'cancelled')
                            <a href="#" class="btn-secondary">Mua lại</a>
                        @endif
                        <a href="{{ route('orders.show', $order->id) }}" class="btn-view-details">Xem chi tiết</a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Hiển thị phân trang --}}
        @if($orders->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
        @endif
    @endif
</div>
@endsection
