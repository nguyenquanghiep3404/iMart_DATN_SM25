@extends('users.layouts.profile')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2>HÓA ĐƠN THANH TOÁN</h2>
                        <p class="mb-0">Mã đơn hàng: <strong>{{ $order->order_code }}</strong></p>
                        <p>Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Thông tin khách hàng</h5>
                            <p><strong>Tên:</strong> {{ $order->customer_name }}</p>
                            <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                            <p><strong>Điện thoại:</strong> {{ $order->customer_phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Thông tin đơn hàng</h5>
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
                    </div>

                    <div class="mb-4">
                        <h5>Địa chỉ nhận hàng</h5>
                        <p>{{ $order->shipping_address_line1 }}</p>
                        @if($order->shipping_address_line2)
                            <p>{{ $order->shipping_address_line2 }}</p>
                        @endif
                        <p>{{ $order->shipping_ward_code }}, {{ $order->shipping_province_code }}</p>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if($item->variant_attributes)
                                                <div class="mt-1">
                                                    @foreach(json_decode($item->variant_attributes, true) as $key => $value)
                                                        <small class="text-muted">{{ $key }}: {{ $value }}</small><br>
                                                    @endforeach
                                                </div>
                                            @endif
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
                                    <td colspan="4" class="text-end"><strong>Tạm tính:</strong></td>
                                    <td>{{ number_format($order->sub_total, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                    <td>{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Giảm giá:</strong></td>
                                    <td>-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td class="text-danger fw-bold">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-center mt-4">
                        <p>Cảm ơn bạn đã mua hàng tại cửa hàng của chúng tôi!</p>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> In hóa đơn
                        </button>
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection
