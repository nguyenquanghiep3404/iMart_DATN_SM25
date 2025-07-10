@extends('users.layouts.profile')

@section('styles')
<style>
    /* CSS tùy chỉnh để giao diện giống với ảnh mẫu */
    .profile-content .nav-tabs {
        border-bottom: 1px solid #dee2e6;
    }

    .profile-content .nav-tabs .nav-item {
        margin-bottom: -1px;
    }

    .profile-content .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.25rem;
        transition: all 0.2s ease-in-out;
    }

    .profile-content .nav-tabs .nav-link.active,
    .profile-content .nav-tabs .nav-link:hover {
        border-bottom-color: #dc3545; /* Màu đỏ nổi bật cho tab active */
        color: #343a40;
        background-color: transparent;
    }

    .search-bar {
        max-width: 400px;
        margin-left: auto;
    }

    .search-bar .form-control {
        border-radius: 20px;
        border-right: none;
    }

    .search-bar .input-group-text {
        background-color: transparent;
        border-left: none;
        border-radius: 20px;
    }

    .empty-orders-container {
        display: flex;
        flex-direction: column; /* sắp xếp ảnh và text theo chiều dọc */
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 60px 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-top: 2rem;
    }

    .empty-orders-container img {
        width: 250px;
        max-width: 100%;
        height: auto;
        margin-bottom: 1.5rem;
    }
    .empty-orders-container p {
    font-size: 1.1rem;
    color: #6c757d;
    }

    .table thead th {
        font-weight: 600;
        color: #495057;
    }
</style>
@endsection

@section('content')
<div class="profile-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Đơn hàng của tôi</h3>
        <div class="search-bar">
            <form action="{{ route('orders.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Tìm theo tên, mã đơn hoặc sản phẩm" value="{{ request('search') }}">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i> </span>
                </div>
            </form>
        </div>
    </div>

    {{-- Các tab lọc trạng thái --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ empty($status) ? 'active' : '' }}" href="{{ route('orders.index') }}">Tất cả</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'processing' ? 'active' : '' }}" href="{{ route('orders.index', 'processing') }}">Đang xử lý</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'shipped' ? 'active' : '' }}" href="{{ route('orders.index', 'shipped') }}">Đang giao</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'delivered' ? 'active' : '' }}" href="{{ route('orders.index', 'delivered') }}">Hoàn tất</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'cancelled' ? 'active' : '' }}" href="{{ route('orders.index', 'cancelled') }}">Đã hủy</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status == 'returned' ? 'active' : '' }}" href="{{ route('orders.index', 'returned') }}">Trả hàng</a>
        </li>
    </ul>

    @if($orders->isEmpty())
        <div class="empty-orders-container">
            {{-- Thay thế bằng đường dẫn đến ảnh của bạn --}}
            <img src="https://fptshop.com.vn/img/empty_state.png?w=640&q=75" alt="Không có đơn hàng">
            <p class="text-muted">Bạn chưa có đơn hàng nào.</p>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Mã đơn hàng</th>
                                <th scope="col">Ngày đặt</th>
                                <th scope="col">Tổng tiền</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td><strong>#{{ $order->order_code }}</strong></td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td>{{ number_format($order->grand_total, 0, ',', '.') }} ₫</td>
                                    <td>
                                        @if($order->status == 'pending_confirmation')
                                            <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                        @elseif($order->status == 'processing')
                                            <span class="badge bg-info text-dark">Đang xử lý</span>
                                        @elseif($order->status == 'awaiting_shipment')
                                            <span class="badge bg-info text-dark">Chờ lấy hàng</span>
                                        @elseif($order->status == 'shipped' || $order->status == 'out_for_delivery')
                                            <span class="badge bg-primary">Đang giao</span>
                                        @elseif($order->status == 'delivered')
                                            <span class="badge bg-success">Đã giao</span>
                                        @elseif($order->status == 'cancelled')
                                            <span class="badge bg-danger">Đã hủy</span>
                                        @elseif($order->status == 'returned')
                                            <span class="badge bg-secondary">Trả hàng</span>
                                        @elseif($order->status == 'failed_delivery')
                                            <span class="badge bg-danger">Giao hàng thất bại</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                        @if(in_array($order->status, ['pending_confirmation', 'processing', 'awaiting_shipment']))
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal{{ $order->id }}">Hủy đơn</button>
                                        @endif
                                    </td>
                                </tr>

                                <div class="modal fade" id="cancelOrderModal{{ $order->id }}" tabindex="-1" aria-labelledby="cancelOrderModalLabel{{ $order->id }}" aria-hidden="true">
                                      <div class="modal-dialog">
                                          <div class="modal-content">
                                              <div class="modal-header">
                                                  <h5 class="modal-title" id="cancelOrderModalLabel{{ $order->id }}">Xác nhận hủy đơn hàng</h5>
                                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                              </div>
                                              <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                                  @csrf
                                                  <div class="modal-body">
                                                      <p>Bạn có chắc chắn muốn hủy đơn hàng <strong>#{{ $order->order_code }}</strong>?</p>
                                                      <div class="mb-3">
                                                          <label for="reason{{ $order->id }}" class="form-label">Lý do hủy (bắt buộc)</label>
                                                          <textarea class="form-control" id="reason{{ $order->id }}" name="reason" rows="3" required></textarea>
                                                      </div>
                                                  </div>
                                                  <div class="modal-footer">
                                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không</button>
                                                      <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                                                  </div>
                                              </form>
                                          </div>
                                      </div>
                                  </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Hiển thị phân trang --}}
                @if($orders->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
