<div id="failure-reason-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Chọn lý do giao hàng thất bại</h4>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="reason-option">
                <input type="radio" id="reason1" name="failure_reason_option" value="Không liên lạc được khách hàng">
                <label for="reason1">Không liên lạc được khách hàng</label>
            </div>
            <div class="reason-option">
                <input type="radio" id="reason2" name="failure_reason_option" value="Khách hẹn lại ngày giao">
                <label for="reason2">Khách hẹn lại ngày giao</label>
            </div>
            <div class="reason-option">
                <input type="radio" id="reason3" name="failure_reason_option" value="Khách từ chối nhận hàng (thay đổi ý định)">
                <label for="reason3">Khách từ chối nhận hàng (thay đổi ý định)</label>
            </div>
             <div class="reason-option">
                <input type="radio" id="reason4" name="failure_reason_option" value="Sai thông tin giao hàng (SĐT, địa chỉ)">
                <label for="reason4">Sai thông tin giao hàng (SĐT, địa chỉ)</label>
            </div>
            <div class="reason-option">
                <input type="radio" id="reason_other" name="failure_reason_option" value="other">
                <label for="reason_other">Khác (Vui lòng ghi rõ)</label>
            </div>
            <textarea id="other-reason-text" rows="3" placeholder="Nhập lý do khác vào đây..." style="display: none;"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel close-modal-btn">Hủy bỏ</button>
            <button type="button" id="confirm-failure-btn" class="btn-confirm">Xác nhận</button>
        </div>
    </div>
</div>
