<a href="{{ route('shipper.orders.show', $order) }}" class="block bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
    <div class="flex justify-between items-center mb-2">
        <span class="font-bold text-gray-800">{{ $order->order_code }}</span>
        <span class="font-bold text-indigo-600 text-lg">{{ number_format($order->grand_total, 0, ',', '.') }}đ</span>
    </div>
    <p class="text-sm font-semibold text-gray-700 truncate">{{ $order->customer_name }}</p>
    <p class="text-sm text-gray-500 truncate">{{ $order->shipping_address_line1 }}, {{ $order->shipping_district }}</p>

    @if ($order->delivery_attempt && $order->status === 'shipped')
        <div class="mt-2 pt-2 border-t border-dashed">
            <p class="text-xs text-orange-600 font-semibold">
                <i class="fas fa-clock mr-1"></i> Hẹn giao lại: {{ $order->delivery_attempt['note'] ?? 'Khách hẹn lại' }}
            </p>
        </div>
    @endif
</a>
