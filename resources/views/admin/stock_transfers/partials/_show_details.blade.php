<div class="card">
    <div class="p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Thông tin Phiếu Chuyển</h3>
        <dl class="info-grid">
            <dt>Trạng thái:</dt>
            <dd>
                @php
                    $statusClasses = [
                        'pending' => 'status-pending',
                        'shipped' => 'status-waiting_for_scan', // Using blue color
                        'received' => 'status-completed',
                        'cancelled' => 'status-cancelled',
                    ];
                    $statusTexts = [
                        'pending' => 'Chờ chuyển',
                        'shipped' => 'Đang chuyển',
                        'received' => 'Đã nhận',
                        'cancelled' => 'Đã hủy',
                    ];
                @endphp
                <span class="status-badge {{ $statusClasses[$stockTransfer->status] ?? '' }}">
                    {{ $statusTexts[$stockTransfer->status] ?? ucfirst($stockTransfer->status) }}
                </span>
            </dd>

            <dt>Kho Gửi:</dt>
            <dd>{{ $stockTransfer->fromLocation->name ?? 'N/A' }}</dd>

            <dt>Kho Nhận:</dt>
            <dd>{{ $stockTransfer->toLocation->name ?? 'N/A' }}</dd>

            <dt>Ngày tạo phiếu:</dt>
            <dd>{{ $stockTransfer->created_at->format('d/m/Y H:i') }}</dd>
            
            <dt>Người tạo:</dt>
            <dd>{{ $stockTransfer->createdBy->name ?? 'N/A' }}</dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="p-6">
         <h3 class="text-xl font-semibold text-gray-800 mb-4">Ghi chú</h3>
         <p class="text-gray-600 italic">
             {{ $stockTransfer->notes ?? 'Không có ghi chú.' }}
         </p>
    </div>
</div>
