@extends('layouts.shipper')

@section('title', 'Chi tiết ĐH ' . $order->order_code)

@push('styles')
<style>
    /* CSS cho modal (đã kiểm tra lại) */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.6); display: none;
        justify-content: center; align-items: flex-end; /* Hiện modal từ dưới lên */
        z-index: 50;
    }
    .modal-overlay.is-visible {
        display: flex;
    }
    .modal-content {
        background: white; border-radius: 1.5rem 1.5rem 0 0;
        width: 100%; max-width: 448px;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
        transform: translateY(100%);
        transition: transform 0.3s ease-out;
        max-height: 80vh; /* Giới hạn chiều cao */
        display: flex;
        flex-direction: column;
    }
    .modal-overlay.is-visible .modal-content {
        transform: translateY(0);
    }
    .modal-body {
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
    {{-- Header của trang --}}
    <header class="sticky top-0 bg-white shadow-sm z-10 p-4 flex items-center space-x-4 border-b">
        <a href="{{ route('shipper.dashboard') }}" class="text-gray-600 h-10 w-10 flex items-center justify-center rounded-full hover:bg-gray-100">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-lg font-bold text-gray-800">Chi tiết đơn hàng</h1>
    </header>

    {{-- Nội dung chi tiết đơn hàng --}}
    <div class="p-5 space-y-4">
        <div class="bg-white p-4 rounded-xl shadow-sm space-y-3">
            <h3 class="text-base font-bold text-gray-800">Thông tin người nhận</h3>
            <p class="font-semibold">{{ $order->customer_name }}</p>
            <div class="flex items-center justify-between">
                <p class="text-gray-700">{{ $order->customer_phone }}</p>
                <a href="tel:{{ $order->customer_phone }}" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-phone-alt"></i></a>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-gray-700">{{ $order->shipping_address_line1 }}, {{ $order->shipping_ward }}, {{ $order->shipping_district }}</p>
                <a href="https://maps.google.com/?q={{ urlencode($order->shipping_address_line1 . ', ' . $order->shipping_ward . ', ' . $order->shipping_district) }}" target="_blank" class="h-10 w-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-map-marker-alt"></i></a>
            </div>
        </div>

        {{-- === BẮT ĐẦU THAY ĐỔI === --}}
        <div class="bg-white p-4 rounded-xl shadow-sm space-y-2">
            <h3 class="text-base font-bold text-gray-800">Chi tiết sản phẩm</h3>
            <ul class="divide-y divide-gray-200">
                @foreach($order->items as $item)
                    <li class="py-3 flex space-x-4 items-center">
                        {{-- Hiển thị ảnh sản phẩm --}}
                        <img src="{{ $item->image_url ?? 'https://via.placeholder.com/150?text=No+Image' }}" alt="{{ $item->product_name }}" class="w-20 h-20 object-cover rounded-lg shadow-md">

                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">{{ $item->product_name }}</p>

                            {{-- Hiển thị biến thể sản phẩm --}}
                            @php
                                // Chuyển đổi chuỗi JSON thành mảng để xử lý, phòng trường hợp dữ liệu là null hoặc chuỗi 'null'
                                $attributes = is_string($item->variant_attributes) ? json_decode($item->variant_attributes, true) : $item->variant_attributes;
                            @endphp
                            @if(!empty($attributes) && is_array($attributes))
                                <div class="text-sm text-gray-500 mt-1">
                                    @foreach($attributes as $key => $value)
                                        <span>{{ $key }}: <strong>{{ $value }}</strong></span>@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex justify-between text-sm text-gray-600 mt-2">
                                <span>Số lượng: <span class="font-bold">{{ $item->quantity }}</span></span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm text-center">
            {{-- Hiển thị Phương thức thanh toán --}}
            <div class="flex justify-between items-center text-base text-gray-700 mb-3 pb-3 border-b">
                <span>Phương thức thanh toán:</span>
                <span class="font-bold text-blue-600">{{ Str::upper($order->payment_method) }}</span>
            </div>

            {{-- Kiểm tra nếu là COD thì hiển thị tiền cần thu --}}
            @if(strtolower($order->payment_method) === 'cod')
                <p class="text-gray-500">Tổng tiền thu hộ (COD)</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($order->grand_total, 0, ',', '.') }}đ</p>
            @else
                <p class="text-gray-500">Tổng tiền</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($order->grand_total, 0, ',', '.') }}đ</p>
                <p class="mt-1 text-sm font-semibold {{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-orange-500' }}">
                    (Trạng thái: {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }})
                </p>
            @endif
        </div>
        {{-- === KẾT THÚC THAY ĐỔI === --}}
    </div>

    {{-- Footer chứa các nút hành động (nếu có) --}}
    @if(in_array($order->status, ['awaiting_shipment', 'shipped', 'out_for_delivery']))
        <footer class="sticky bottom-0 p-4 bg-white border-t">
            @if($order->status === 'awaiting_shipment')
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
@endsection

@push('modals')
    {{-- Modal không thay đổi --}}
    <div id="failure-reason-modal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="text-xl font-bold text-gray-800 p-6 border-b">Lý do giao không thành công</h2>
            <form id="fail-delivery-form" action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST" style="display: none;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="failed_delivery">
                <input type="hidden" id="fail-reason-input" name="reason">
                <input type="hidden" id="fail-notes-input" name="notes">
            </form>
            <div class="modal-body p-6 space-y-4">
                <div>
                    <label for="failure-reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do chính</label>
                    <select id="failure-reason" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Không liên lạc được khách hàng">Không liên lạc được khách</option>
                        <option value="Khách hẹn giao lại">Khách hẹn giao lại</option>
                        <option value="Sai địa chỉ">Sai địa chỉ</option>
                        <option value="other">Lý do khác</option>
                    </select>
                </div>
                <div>
                    <label for="failure-notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú thêm</label>
                    <textarea id="failure-notes" rows="3" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="VD: Khách hẹn giao sau 17h"></textarea>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 gap-3 border-t">
                <button type="button" class="w-full bg-gray-200 text-gray-700 font-bold py-3 rounded-lg close-modal-btn">Hủy</button>
                <button type="button" id="confirm-failure-btn" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg">Xác nhận</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    {{-- Scripts không thay đổi --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const failActionButton = document.getElementById('btn-fail-action');
        const modal = document.getElementById('failure-reason-modal');
        if (failActionButton && modal) {
            const closeButtons = modal.querySelectorAll('.close-modal-btn');
            const confirmButton = modal.querySelector('#confirm-failure-btn');
            const failForm = document.getElementById('fail-delivery-form');
            const reasonSelect = modal.querySelector('#failure-reason');
            const notesTextarea = modal.querySelector('#failure-notes');
            failActionButton.addEventListener('click', () => {
                modal.classList.add('is-visible');
            });
            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    modal.classList.remove('is-visible');
                });
            });
            confirmButton.addEventListener('click', function() {
                let reason = reasonSelect.value;
                if (reason === 'other') {
                    reason = notesTextarea.value.trim();
                }
                document.getElementById('fail-reason-input').value = reason;
                document.getElementById('fail-notes-input').value = notesTextarea.value;
                failForm.submit();
            });
        }
    });
</script>
@endpush
