{{-- Header của trang --}}
<header class="page-header sticky top-0 bg-white shadow-sm z-10 p-4 flex items-center space-x-4">
    <a href="{{ route('shipper.dashboard') }}" class="text-gray-600 h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100">
        <i class="fas fa-arrow-left fa-lg"></i>
    </a>
    <h1 class="text-lg font-bold text-gray-800">Chi tiết đơn hàng</h1>
</header>

{{-- Phần nội dung chính có thể cuộn --}}
<main class="page-content p-5 space-y-4">
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white p-4 rounded-xl shadow-sm space-y-3">
        <h3 class="text-base font-bold text-gray-800">Thông tin người nhận</h3>
        <p class="font-semibold">{{ $order->customer_name }}</p>
        <div class="flex items-center justify-between">
            <p class="text-gray-700">{{ $order->customer_phone }}</p>
            <a href="tel:{{ $order->customer_phone }}" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-phone-alt"></i></a>
        </div>
        <div class="flex items-center justify-between">
            <p class="text-gray-700">{{ $order->shipping_full_address }}</p>
                <a href="https://maps.google.com/?q={{ urlencode($order->shipping_full_address) }}" target="_blank" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-map-marker-alt"></i></a>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm space-y-2">
        <h3 class="text-base font-bold text-gray-800">Chi tiết sản phẩm</h3>
        <ul class="divide-y divide-gray-200">
            @foreach($order->items as $item)
                <li class="py-3">
                    <p class="font-semibold text-gray-800">{{ $item->product_name }}</p>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Số lượng: <span class="font-bold">{{ $item->quantity }}</span></span>
                        <span>{{ number_format($item->price, 0, ',', '.') }}đ</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm text-center">
        <p class="text-gray-500">Tổng tiền thu hộ (COD)</p>
        <p class="text-3xl font-bold text-green-600">{{ number_format($order->grand_total, 0, ',', '.') }}đ</p>
    </div>
</main>

{{-- Footer chứa các nút hành động (nếu có) --}}
@if(in_array($order->status, ['processing', 'shipped', 'out_for_delivery']))
    <footer class="page-header p-4 bg-white border-t">
        @if($order->status === 'processing')
            <form action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="shipped">
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg">ĐÃ LẤY HÀNG</button>
            </form>
        @elseif(in_array($order->status, ['shipped', 'out_for_delivery']))
            <div class="grid grid-cols-2 gap-3">
                <button type="button" id="btn-fail-action" class="w-full bg-orange-500 text-white font-bold py-3 rounded-lg">GIAO THẤT BẠI</button>
                <form action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg">GIAO THÀNH CÔNG</button>
                </form>
            </div>
        @endif
    </footer>
@endif
