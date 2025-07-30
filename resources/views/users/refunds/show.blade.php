@extends('users.layouts.profile')

@section('content')
<style>
    body {
        font-family: 'Inter', sans-serif;
    }

    /* Style cho modal */
    .modal {
        transition: opacity 0.25s ease;
    }

    /* Timeline styles */
    .timeline-item .timeline-dot {
        position: absolute;
        left: -0.43rem;
        top: 0.5rem;
        width: 0.875rem;
        height: 0.875rem;
        border-radius: 50%;
        border: 2px solid white;
    }

    .timeline-item:last-child .timeline-line {
        display: none;
    }
</style>
<div class="container mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Chi tiết yêu cầu trả hàng #{{ $returnRequest->return_code }}</h1>
        <div class="flex items-center space-x-2 mb-4">
            <span id="status-badge" class="px-3 py-1 text-sm font-semibold rounded-full"></span>
        </div>

        <!-- Left: Thông tin sản phẩm và lý do -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Chi tiết sản phẩm trả -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Chi tiết sản phẩm trả</h2>
                    @foreach ($returnRequest->returnItems as $item)
                    <div class="flex items-start space-x-4">
                        <img src="{{ $item->orderItem->variant->product->thumbnail_url ?? 'https://placehold.co/100x100' }}" class="w-24 h-24 rounded-md">
                        <div class="flex-grow">
                            <p class="font-bold">{{ $item->orderItem->variant->product->name ?? 'Sản phẩm đã xóa' }}</p>
                            <p class="text-sm text-gray-500">SKU: {{ $item->orderItem->variant->sku ?? 'N/A' }}</p>

                            {{-- ✅ Đây là dòng bạn đang báo lỗi --}}
                            <p class="text-sm">Số lượng trả: <span class="font-semibold">{{ $item->quantity }}</span></p>

                            <p class="text-lg font-bold text-red-600 mt-2">
                                {{ number_format($item->orderItem->price * $item->quantity) }} VNĐ
                            </p>
                        </div>
                    </div>
                    @endforeach


                    <div class="mt-4 border-t pt-4 space-y-2">
                        <p><strong class="font-semibold">Lý do từ khách hàng:</strong> {{ $returnRequest->reason ?? 'Không có lý do' }}</p>
                        <p><strong class="font-semibold">Mô tả: </strong>{{ $returnRequest->reason_details }}.</p>
                    </div>
                    <div class="mt-4">
                        <h3 class="font-semibold mb-2">Hình ảnh/Video bằng chứng:</h3>
                        <div class="flex space-x-2">
                            @foreach ($returnRequest->files as $file)
                            <img src="{{ Storage::url($file->path) }}" alt="{{ $file->original_name }}" class="w-24 h-24 rounded-md cursor-pointer hover:opacity-80 transition">
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Thông tin hoàn tiền -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Thông tin hoàn tiền</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phương thức khách chọn:</span>
                            <span class="font-semibold text-blue-600">{{ $returnRequest->refund_method_text }}</span>
                        </div>

                        @if ($returnRequest->refund_method === 'bank')
                        {{-- ✅ Chỉ hiển thị nếu là Chuyển khoản --}}
                        <div id="bank-info-display" class="bg-gray-50 p-3 rounded-md border border-gray-200">
                            <p><strong class="font-medium">Ngân hàng:</strong> {{ $returnRequest->bank_name }}</p>
                            <p><strong class="font-medium">Chủ tài khoản:</strong> {{ $returnRequest->bank_account_name }}</p>
                            <p><strong class="font-medium">Số tài khoản:</strong> {{ $returnRequest->bank_account_number }}</p>
                        </div>
                        @elseif ($returnRequest->refund_method === 'points')
                        {{-- ✅ Nếu là Hoàn điểm --}}
                        <p class="text-green-700 text-sm font-medium">
                            Khách hàng sẽ được hoàn bằng <strong>{{ number_format($returnRequest->refunded_points) }} điểm</strong> vào tài khoản.
                        </p>
                        @elseif ($returnRequest->refund_method === 'coupon')
                        {{-- ✅ Nếu là Mã giảm giá --}}
                        <p class="text-yellow-600 text-sm font-medium">
                            Khách sẽ nhận <strong>mã giảm giá trị giá {{ number_format($returnRequest->refund_amount) }} VNĐ</strong> qua email hoặc SMS.
                        </p>
                        @endif

                        {{-- Tổng tiền luôn hiển thị --}}
                        <div class="flex justify-between items-center border-t pt-3">
                            <span class="text-lg font-semibold text-gray-800">Tổng tiền hoàn trả:</span>
                            <input type="text" id="refund-amount-input" value="{{ number_format($returnRequest->refund_amount) }}" class="text-right text-lg font-bold text-red-600 border border-gray-300 rounded-md px-2 py-1 w-48" disabled>
                        </div>
                    </div>
                </div>


                <!-- Hành động của Admin -->
                <div id="actions-panel" class="bg-white p-6 rounded-lg shadow">
                    <!-- Nội dung hành động sẽ được JS chèn vào đây -->
                </div>
            </div>

            <!-- Right Column -->
            <div class="w-full space-y-6">
                <!-- Thông tin Khách hàng & Đơn hàng -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Khách hàng</h3>
                        <p class="text-blue-600 font-semibold">
                            {{ $returnRequest->order->user->name ?? 'Ẩn danh' }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ $returnRequest->order->user->email ?? 'Không có email' }}
                        </p>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Đơn hàng gốc</h3>
                        <a href="{{ route('admin.orders.show', $returnRequest->order->id) }}" class="text-blue-600 hover:underline font-semibold">
                            #{{ $returnRequest->order->order_code }}
                        </a>
                        <p class="text-sm text-gray-500">
                            Ngày đặt: {{ $returnRequest->order->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                </div>

                <!-- Ghi chú nội bộ -->
                <div class="w-full bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-gray-800 mb-2">Ghi chú từ hệ thống</h3>

                    @if ($returnRequest->admin_note)
                    <p class="text-sm text-gray-700 whitespace-pre-line">
                        {{ $returnRequest->admin_note }}
                    </p>
                    @else
                    <p class="text-sm text-gray-500 italic">Không có ghi chú nào.</p>
                    @endif
                </div>

                <!-- Lịch sử hoạt động -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-gray-800 mb-4">Lịch sử hoạt động</h3>
                    <div class="relative pl-4 border-l border-gray-200 space-y-6">
                        @if ($returnRequest->refunded_at && $returnRequest->refund_processed_by)
                        <div class="timeline-item">
                            <div class="timeline-dot bg-purple-500"></div>
                            <p class="font-semibold text-sm">Đã hoàn tiền</p>
                            <p class="text-xs text-gray-500">
                                {{ $returnRequest->refundProcessor->name ?? 'Chưa xác định' }} <strong class="font-medium"></strong> xác nhận vào:
                                {{ \Carbon\Carbon::parse($returnRequest->refunded_at)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const statusBadge = document.getElementById('status-badge');
    const actionsPanel = document.getElementById('actions-panel');
    const statusSwitcher = document.getElementById('status-switcher');
    const refundAmountInput = document.getElementById('refund-amount-input');
    const rejectionModal = document.getElementById('rejection-modal');
    const statusConfig = {
        pending: {
            text: 'Chờ xử lý',
            badge: 'bg-yellow-100 text-yellow-800'
        },
        approved: {
            text: 'Đã phê duyệt',
            badge: 'bg-blue-100 text-blue-800'
        },
        processing: {
            text: 'Đang xử lý',
            badge: 'bg-orange-100 text-orange-800'
        },
        completed: {
            text: 'Hoàn tất',
            badge: 'bg-green-100 text-green-800'
        },
        rejected: {
            text: 'Đã từ chối',
            badge: 'bg-red-100 text-red-800'
        }
    };

    // Dữ liệu status từ server
    const currentStatus = "{{ $returnRequest->status }}";

    renderActions(currentStatus);

    function renderActions(status) {
        // Cập nhật badge
        statusBadge.textContent = statusConfig[status].text;
        statusBadge.className = `px-3 py-1 text-sm font-semibold rounded-full ${statusConfig[status].badge}`;

        let content = `<h2 class="text-xl font-semibold text-gray-800 mb-4">Hành động</h2>`;
        refundAmountInput.disabled = true; // Vô hiệu hóa input mặc định

        switch (status) {
            case 'pending':
                content += `
            <p class="text-sm text-yellow-700">Yêu cầu đang chờ được xử lý.</p>
        `;
                break;

            case 'approved':
                content += `
            <p class="text-sm text-blue-700">Yêu cầu đã được phê duyệt. Đang chờ nhận hàng từ khách.</p>
        `;
                break;

            case 'processing':
                content += `
            <p class="text-sm text-orange-700 mb-2">Đã nhận hàng từ khách. Đang kiểm tra thông tin hoàn tiền.</p>
        `;

                const method = "{{ $returnRequest->refund_method }}";
                const amountFormatted = refundAmountInput.value;

                if (method === 'points') {
                    content += `<p class="text-green-600 font-medium">Sẽ hoàn bằng {{ number_format($returnRequest->refunded_points) }} điểm.</p>`;
                } else if (method === 'bank') {
                    content += `<p class="text-blue-600 font-medium">Sẽ hoàn bằng chuyển khoản: ${amountFormatted} VNĐ.</p>`;
                } else if (method === 'coupon') {
                    content += `<p class="text-yellow-600 font-medium">Sẽ hoàn bằng mã giảm giá: ${amountFormatted} VNĐ.</p>`;
                } else {
                    content += `<p class="text-red-600">Không xác định được phương thức hoàn tiền.</p>`;
                }
                break;

            case 'completed':
                content += `<p class="text-sm text-green-700 text-center font-semibold">Yêu cầu đã được xử lý và hoàn tất.</p>`;
                break;

            case 'rejected':
                content += `
            <p class="text-sm text-red-600 text-center font-semibold">Yêu cầu đã bị từ chối.</p>
            <p class="text-sm text-gray-600 mt-2"><strong>Lý do:</strong> {{ $returnRequest->rejection_reason }}</p>
        `;
                break;
        }

        actionsPanel.innerHTML = content;
    }
</script>
@endsection