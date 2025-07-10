@extends('users.layouts.profile')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Chi tiết đơn hàng #{{ $order->order_code }}</h3>
                <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-invoice"></i> Xem hóa đơn
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Trạng thái:</strong>
                                @if($order->status == 'pending_confirmation')
                                    <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                @elseif($order->status == 'processing')
                                    <span class="badge bg-info text-dark">Đang xử lý</span>
                                @elseif($order->status == 'awaiting_shipment')
                                    <span class="badge bg-info text-dark">Chờ lấy hàng</span>
                                @elseif($order->status == 'shipped')
                                    <span class="badge bg-primary">Đang giao</span>
                                @elseif($order->status == 'out_for_delivery')
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
                            </p>
                            <p><strong>Phương thức thanh toán:</strong> {{ $order->payment_method }}</p>
                            <p><strong>Trạng thái thanh toán:</strong>
                                @if($order->payment_status == 'paid')
                                    <span class="badge bg-success">Đã thanh toán</span>
                                @elseif($order->payment_status == 'pending')
                                    <span class="badge bg-warning">Chờ thanh toán</span>
                                @elseif($order->payment_status == 'failed')
                                    <span class="badge bg-danger">Thanh toán thất bại</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phương thức vận chuyển:</strong> {{ $order->shipping_method ?? 'Chưa xác định' }}</p>
                            <p><strong>Phí vận chuyển:</strong> {{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</p>
                            <p><strong>Giảm giá:</strong> {{ number_format($order->discount_amount, 0, ',', '.') }} ₫</p>
                            <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Địa chỉ nhận hàng</h6>
                            <p>{{ $order->customer_name }}</p>
                            <p>{{ $order->customer_phone }}</p>
                            <p>{{ $order->shipping_address_line1 }}</p>
                            @if($order->shipping_address_line2)
                                <p>{{ $order->shipping_address_line2 }}</p>
                            @endif
                            <p>{{ $order->shipping_ward_code }}, {{ $order->shipping_province_code }}</p>
                        </div>
                        @if($order->billing_address_line1)
                        <div class="col-md-6">
                            <h6>Địa chỉ thanh toán</h6>
                            <p>{{ $order->customer_name }}</p>
                            <p>{{ $order->billing_address_line1 }}</p>
                            @if($order->billing_address_line2)
                                <p>{{ $order->billing_address_line2 }}</p>
                            @endif
                            <p>{{ $order->billing_ward_code }}, {{ $order->billing_province_code }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Sản phẩm đã đặt</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->image_url)
                                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="img-thumbnail me-3" style="width: 60px;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                    <i class="fas fa-box-open text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $item->product_name }}</h6>
                                                <small class="text-muted">SKU: {{ $item->sku }}</small>
                                                @if(!empty($item->variant_attributes))
                                                    <div class="mt-1">
                                                        @foreach($item->variant_attributes as $key => $value)
                                                            <small class="text-muted">{{ $key }}: {{ $value }}</small><br>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->total_price, 0, ',', '.') }} ₫</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                                    <td>{{ number_format($order->sub_total, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                    <td>{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Giảm giá:</strong></td>
                                    <td>-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td class="text-danger fw-bold">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($order->notes_from_customer)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ghi chú từ khách hàng</h5>
                </div>
                <div class="card-body">
                    <p>{{ $order->notes_from_customer }}</p>
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-between">
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>

                @if(in_array($order->status, ['pending_confirmation', 'processing', 'awaiting_shipment']))
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                    <i class="fas fa-times"></i> Hủy đơn hàng
                </button>
                @endif
            </div>

            <!-- Modal hủy đơn hàng -->
            <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelOrderModalLabel">Hủy đơn hàng</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p>Bạn có chắc chắn muốn hủy đơn hàng <strong>{{ $order->order_code }}</strong>?</p>
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Lý do hủy đơn</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
