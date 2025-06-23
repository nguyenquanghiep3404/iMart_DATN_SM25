<div class="detail-container">
    <div class="detail-header">
        <h2>Chi tiết đơn hàng</h2>
        <a href="{{ route('shipper.dashboard') }}" class="back-link">← Quay lại danh sách</a>
    </div>

    @if ($order->status === 'failed_delivery' && !empty($order->failed_delivery_reason))
        <div class="status-alert alert-danger">
            <strong>Lý do giao hàng thất bại:</strong>
            <p>{{ $order->failed_delivery_reason }}</p>
        </div>
    @endif

    @if ($order->status === 'cancelled' && !empty($order->cancellation_reason))
        <div class="status-alert alert-secondary">
            <strong>Lý do hủy đơn:</strong>
            <p>{{ $order->cancellation_reason }}</p>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="detail-section">
        <h3>Thông tin người nhận</h3>
        <div class="info-grid">
            <div>Tên người nhận:</div>   <div>{{ $order->customer_name }}</div>
            <div>Số điện thoại:</div>   <div>{{ $order->customer_phone }}</div>
            <div>Địa chỉ giao:</div>
            <div>
                {{ $order->shipping_address_line1 }}, {{ $order->shipping_ward }}, {{ $order->shipping_district }}, {{ $order->shipping_city }}
                <a href="https://www.google.com/maps/search/?api=1&query=${urlencode($order->shipping_address_line1 . ', ' . $order->shipping_ward . ', ' . $order->shipping_district . ', ' . $order->shipping_city)}}" target="_blank" class="maps-link">(Xem trên bản đồ)</a>
            </div>
        </div>
    </div>

    <div class="detail-section">
        <h3>Thông tin đơn hàng</h3>
        <div class="info-grid">
            <div>Mã đơn hàng:</div>    <div>{{ $order->order_code }}</div>
            <div>Trạng thái:</div>     <div>{{ $order->status }}</div>
            @if($order->payment_method === 'COD')
                <div>Tiền cần thu (COD):</div> <div class="cod-amount">{{ number_format($order->grand_total, 0, ',', '.') }} đ</div>
            @endif
        </div>
    </div>

    <div class="detail-section">
        <h3>Chi tiết sản phẩm</h3>
        @if($order->items && $order->items->count() > 0)
            <table class="product-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th class="text-right">Số lượng</th>
                        <th class="text-right">Đơn giá</th>
                        <th class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td data-label="STT">{{ $loop->iteration }}</td>
                        <td data-label="Tên sản phẩm">{{ $item->product_name }}</td>
                        <td data-label="Số lượng" class="text-right">{{ $item->quantity }}</td>
                        <td data-label="Đơn giá" class="text-right">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                        <td data-label="Thành tiền" class="text-right">{{ number_format($item->total_price, 0, ',', '.') }} đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Không có thông tin chi tiết sản phẩm cho đơn hàng này.</p>
        @endif
    </div>

    @if ($order->notes_for_shipper)
        <div class="detail-section">
            <h3>Ghi chú cho shipper</h3>
            <div class="shipper-note-box">{{ $order->notes_for_shipper }}</div>
        </div>
    @endif

    <div class="action-buttons">
        @if ($order->status == 'awaiting_shipment')
            <form action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST" style="flex-grow: 1;">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="shipped">
                <button type="submit" class="btn btn-pickup">ĐÃ LẤY HÀNG</button>
            </form>
        @endif

        @if (in_array($order->status, ['shipped', 'out_for_delivery']))
            <form action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST" style="flex-grow: 1;">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="delivered">
                <button type="submit" class="btn btn-success">GIAO THÀNH CÔNG</button>
            </form>

            <button type="button" id="btn-fail-action" class="btn btn-fail">GIAO THẤT BẠI</button>

            <form id="fail-delivery-form" action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST" style="display: none;">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="failed_delivery">
                <input type="hidden" name="reason" id="fail-reason-input">
            </form>
        @endif
    </div>
</div>
