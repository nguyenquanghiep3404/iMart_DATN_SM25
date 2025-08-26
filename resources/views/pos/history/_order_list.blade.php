{{-- resources/views/pos/history/_order_list.blade.php --}}

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <form method="GET" action="{{ route('pos.history.index') }}">
            <div class="relative">
                <input type="text" name="search" id="order-search" placeholder="Tìm kiếm theo mã hóa đơn..." 
                       value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="absolute top-0 left-0 pl-3 pt-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Mã Hóa Đơn</th>
                    <th scope="col" class="px-6 py-3">Thời Gian</th>
                    <th scope="col" class="px-6 py-3">Khách Hàng</th>
                    <th scope="col" class="px-6 py-3">Thanh Toán</th>
                    <th scope="col" class="px-6 py-3 text-right">Tổng Tiền</th>
                    <th scope="col" class="px-6 py-3 text-center">Hành Động</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                @forelse ($orders as $order)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-gray-800">{{ $order->order_code }}</td>
                        <td class="px-6 py-4">{{ $order->created_at->format('H:i:s') }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $order->customer_name ?? 'Khách lẻ' }}</td>
                        <td class="px-6 py-4">
                            @switch($order->payment_method)
                                @case('cash')
                                    <span class="payment-badge payment-cash">Tiền mặt</span>
                                    @break
                                @case('card')
                                    <span class="payment-badge payment-card">Thẻ ngân hàng</span>
                                    @break
                                @case('qr')
                                    <span class="payment-badge payment-qr">QR Code</span>
                                    @break
                                @default
                                    <span>{{ $order->payment_method }}</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-right font-semibold">{{ number_format($order->grand_total, 0, ',', '.') }} đ</td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <button class="font-medium text-blue-600 hover:underline view-order-btn" 
                                    data-order-json="{{ $order->toJson() }}">
                                Xem
                            </button>
                            <button class="font-medium text-green-600 hover:underline">In lại</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500">Không tìm thấy hóa đơn nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-6 border-t border-gray-200">
        {{ $orders->links() }}
    </div>
</div>