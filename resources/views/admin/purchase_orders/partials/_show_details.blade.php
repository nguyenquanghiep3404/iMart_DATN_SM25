<div class="card">
    <div class="p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Thông tin Phiếu Nhập</h3>
        <dl class="info-grid">
            <dt>Trạng thái:</dt>
            <dd>
                @switch($purchaseOrder->status)
                    @case('completed')
                        <span class="status-badge status-completed">Hoàn thành</span>
                        @break
                    @case('cancelled')
                        <span class="status-badge status-cancelled">Đã hủy</span>
                        @break
                    @default
                        <span class="status-badge status-pending">Đang chờ</span>
                @endswitch
            </dd>

            <dt>Nhà cung cấp:</dt>
            <dd>{{ $purchaseOrder->supplier->name ?? 'N/A' }}</dd>

            <dt>Kho nhận hàng:</dt>
            <dd>{{ $purchaseOrder->storeLocation->name ?? 'N/A' }}</dd>

            <dt>Ngày tạo phiếu:</dt>
            <dd>{{ $purchaseOrder->order_date->format('d/m/Y') }}</dd>

            {{-- Ghi chú: Cần thêm trường "expected_date" vào bảng purchase_orders nếu muốn dùng --}}
            {{-- <dt>Ngày dự kiến nhận:</dt>
            <dd>29/07/2025</dd> --}}
            
            {{-- Ghi chú: Cần thêm mối quan hệ createdBy() trong model PurchaseOrder và cột created_by trong bảng --}}
            {{-- <dt>Người tạo:</dt>
            <dd>{{ $purchaseOrder->createdBy->name ?? 'N/A' }}</dd> --}}
        </dl>
    </div>
</div>

<div class="card">
    <div class="p-6">
         <h3 class="text-xl font-semibold text-gray-800 mb-4">Ghi chú</h3>
         <p class="text-gray-600 italic">
             {{ $purchaseOrder->notes ?? 'Không có ghi chú.' }}
         </p>
    </div>
</div>