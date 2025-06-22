<!-- {{-- Đây là bộ khung HTML cho modal, ban đầu sẽ bị ẩn --}}
<div id="order-detail-view" class="order-detail-view">
    <header class="detail-header">
        <button class="back-button" id="back-to-list-btn">←</button>
        {{-- JS sẽ điền mã đơn hàng vào đây --}}
        <h2 id="detail-order-code"></h2>
    </header>
    <div class="detail-content">
        <div class="detail-section">
            <h3>Thông tin người nhận</h3>
            {{-- Các span dưới đây sẽ được JS điền thông tin --}}
            <div class="info-row"><span>Tên:</span> <span id="detail-customer-name"></span></div>
            <div class="info-row"><span>SĐT:</span> <span id="detail-customer-phone"></span></div>
            <div class="info-row">
                <span>Địa chỉ:</span>
                {{-- JS sẽ điền địa chỉ và link Google Maps vào thẻ <a> --}}
                <span id="detail-address"><a href="#" target="_blank"></a></span>
            </div>
        </div>

        <div class="detail-section">
            <h3>Thông tin đơn hàng</h3>
            <div class="info-row"><span>Thanh toán:</span> <span id="detail-payment-method"></span></div>
            <div class="info-row">
                <span>Tiền cần thu (COD):</span>
                <span class="cod-amount" id="detail-cod-amount"></span>
            </div>
        </div>

         <div class="detail-section">
            <h3>Sản phẩm</h3>
            {{-- JS sẽ điền danh sách sản phẩm vào đây --}}
            <ul class="product-list" id="detail-product-list">
            </ul>
        </div>

        <div class="detail-section">
            <h3>Ghi chú cho Shipper</h3>
            {{-- JS sẽ điền ghi chú vào đây --}}
            <div class="shipper-note-box" id="detail-shipper-note">
            </div>
        </div>
    </div>
    <div class="detail-actions">
        {{-- Các nút này ban đầu sẽ bị ẩn, JS sẽ hiện ra tùy theo trạng thái đơn hàng --}}
        <button id="btn-picked-up" class="action-button" style="display: none;">ĐÃ LẤY HÀNG</button>
        <button id="btn-delivered" class="action-button" style="display: none;">GIAO THÀNH CÔNG</button>
        <button id="btn-failed" class="action-button" style="display: none;">GIAO THẤT BẠI</button>
    </div>
</div> -->
