<div class="card-custom">
    <div class="card-custom-header">
        <h3 class="card-custom-title">Lịch sử khôi phục</h3>
    </div>
    <div class="card-custom-body">
        <div class="timeline">

            {{-- Hiển thị sự kiện tạo giỏ hàng --}}
            <div class="timeline-item timeline-system">
                <p class="font-semibold">Giỏ hàng được tạo</p>
                <p class="text-sm text-gray-600">Khách hàng đã thêm sản phẩm vào giỏ.</p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ \Carbon\Carbon::parse($cart->created_at)->format('d/m/Y - H:i') }}</p>
            </div>

            {{-- Lặp qua các logs --}}
            @forelse ($logs as $log)
                @php
                    $class = 'timeline-system';
                    $title = 'Hành động hệ thống';

                    if ($log->action === 'sent_email') {
                        $class = 'timeline-email';
                        $title = 'Đã gửi Email khôi phục';
                    } elseif ($log->action === 'sent_in_app_notification') {
                        $class = 'timeline-notification';
                        $title = 'Đã gửi Thông báo In-App';
                    }
                @endphp

                <div class="timeline-item {{ $class }}">
                    <p class="font-semibold">{{ $title }}</p>
                    <p class="text-sm text-gray-600">
                        {!! $log->description !!}
                        @if ($log->causer && isset($log->causer->name))
                            bởi <span class="font-medium text-gray-800">{{ $log->causer->name }}</span>.
                        @elseif ($log->admin_name)
                            bởi <span class="font-medium text-gray-800">{{ $log->admin_name }}</span>.
                        @else
                            bởi <span class="font-medium text-gray-800">Không xác định</span>.
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y - H:i') }}
                    </p>
                </div>
            @empty
                <p class="text-center text-gray-500">Chưa có lịch sử khôi phục nào.</p>
            @endforelse
        </div>
    </div>
</div>
