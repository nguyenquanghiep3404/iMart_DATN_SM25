<div class="card-custom">
    <div class="card-custom-header">
        <h3 class="card-custom-title">Thông tin khách hàng</h3>
    </div>
    <div class="card-custom-body text-sm">
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-gray-500">Tên KH:</span>
                <span
                    class="font-semibold text-gray-800">{{ $cart->customer_name ?? ($cart->user->name ?? 'Không rõ') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Email:</span>
                @php
                    $email = $cart->customer_email ?? ($cart->user->email ?? null);
                @endphp
                @if ($email)
                    <a href="mailto:{{ $email }}"
                        class="font-semibold text-indigo-600 hover:underline">{{ $email }}</a>
                @else
                    <span class="text-gray-400 italic">Chưa có</span>
                @endif
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Điện thoại:</span>
                @if (!empty($cart->user?->phone_number))
                    <a href="tel:{{ $cart->user->phone_number }}" class="font-semibold text-indigo-600 hover:underline">
                        {{ $cart->user->phone_number }}
                    </a>
                @else
                    <span class="text-gray-400 italic">Chưa có</span>
                @endif
            </div>
            @if (!empty($cart->customer_address))
                <div class="flex justify-between">
                    <span class="text-gray-500">Địa chỉ:</span>
                    <span class="font-semibold text-gray-800 text-right">{{ $cart->customer_address }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="text-gray-500">Loại khách:</span>
                <span class="font-semibold text-gray-800">
                    {{ $cart->user_id ? 'Đã đăng nhập' : 'Khách vãng lai' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tổng sản phẩm:</span>
                <span class="font-semibold text-gray-800">{{ $cart->items->sum('quantity') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Ngày tạo giỏ:</span>
                <span class="font-semibold text-gray-800">
                    {{ $cart->created_at ? $cart->created_at->format('d/m/Y H:i') : 'Không rõ' }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Trạng thái giỏ hàng:</span>
                <span class="font-semibold {{ $cart->is_recovered ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $cart->is_recovered ? 'Đã khôi phục' : 'Chưa khôi phục' }}
                </span>
            </div>
        </div>

        <div class="border-t border-gray-200 mt-4 pt-4 space-y-3">
            <h4 class="text-sm font-semibold text-gray-600 mb-2">Trạng thái liên hệ</h4>

            @php
                $logs = $cart->logs->groupBy('type');
            @endphp

            <div class="flex items-center text-gray-700">
                <i class="fas fa-check-circle text-green-500 w-5 text-center"></i>
                <span class="ml-2">
                    Email:
                    @if ($logs->has('email'))
                        <span class="font-medium">
                            Đã gửi ({{ \Carbon\Carbon::parse($logs['email']->last()->sent_at)->format('d/m') }})
                        </span>
                    @else
                        <span class="text-red-500 font-medium">Chưa gửi</span>
                    @endif
                </span>
            </div>

            <div class="flex items-center text-gray-700">
                <i class="fas fa-check-circle text-green-500 w-5 text-center"></i>
                <span class="ml-2">
                    In-App:
                    @if ($logs->has('in_app'))
                        <span class="font-medium">
                            Đã gửi ({{ \Carbon\Carbon::parse($logs['in_app']->last()->sent_at)->format('d/m') }})
                        </span>
                    @else
                        <span class="text-red-500 font-medium">Chưa gửi</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
