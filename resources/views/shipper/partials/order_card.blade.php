<div class="relative block bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
    @if($order->status === 'awaiting_shipment_assigned')
        <button @click.stop="openBarcodeScanner('{{ $order->id }}')" 
                class="absolute top-2 right-2 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full shadow-lg transition-colors z-10"
                title="Quét mã để xác nhận lấy hàng">
            <i class="fas fa-qrcode text-sm"></i>
        </button>
    @endif
    
    <a href="{{ route('shipper.orders.show', $order) }}" class="block">
        <div class="flex justify-between items-center mb-2 {{ $order->status === 'awaiting_shipment_assigned' ? 'pr-12' : '' }}">
            <span class="font-bold text-gray-800">{{ $order->order_code }}</span>
            <span class="font-bold text-indigo-600 text-lg">{{ number_format($order->grand_total, 0, ',', '.') }}đ</span>
        </div>
    <p class="text-sm font-semibold text-gray-700 truncate">{{ $order->customer_name }}</p>
    <p class="text-sm text-gray-500 truncate">{{ $order->shipping_full_address }}</p>

        @if ($order->delivery_attempt && $order->status === 'shipped')
            <div class="mt-2 pt-2 border-t border-dashed">
                <p class="text-xs text-orange-600 font-semibold">
                    <i class="fas fa-clock mr-1"></i> Hẹn giao lại: {{ $order->delivery_attempt['note'] ?? 'Khách hẹn lại' }}
                </p>
            </div>
        @endif
    </a>
</div>
