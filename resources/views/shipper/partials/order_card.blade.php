<div class="relative block bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
    @if($fulfillment->status === 'awaiting_shipment')
        {{-- Truyền tracking_code vào hàm Javascript --}}
        <button @click.stop="openBarcodeScanner('{{ $fulfillment->id }}')"
                class="absolute top-2 right-2 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full shadow-lg transition-colors z-10"
                title="Quét mã để xác nhận lấy hàng">
            <i class="fas fa-qrcode text-sm"></i>
        </button>
    @endif

    <a href="{{ route('shipper.fulfillments.show', $fulfillment) }}" class="block">
        <div class="flex justify-between items-center mb-2 {{ $fulfillment->status === 'awaiting_shipment' ? 'pr-12' : '' }}">
            {{-- Hiển thị MÃ VẬN ĐƠN (từ cột tracking_code) --}}
            <span class="font-bold text-gray-800">{{ $fulfillment->tracking_code }}</span>
            <span class="font-bold text-indigo-600 text-lg">{{ number_format($fulfillment->order->grand_total, 0, ',', '.') }}đ</span>
        </div>
        <p class="text-xs text-gray-500 mb-2">Đơn hàng: {{ $fulfillment->order->order_code }}</p>
        <p class="text-sm font-semibold text-gray-700 truncate">{{ $fulfillment->order->customer_name }}</p>
        <p class="text-sm text-gray-500 truncate">{{ $fulfillment->order->shipping_full_address }}</p>
    </a>
</div>