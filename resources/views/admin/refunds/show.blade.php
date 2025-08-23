@extends('admin.layouts.app')

@section('title', 'Danh sách sản phẩm')

@push('styles')
{{-- Custom Styles inspired by TailwindCSS for a modern look --}}
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
@endpush

@section('content')
<div class="container mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Yêu cầu #{{ $returnRequest->return_code }}</h1>
        <div class="flex items-center space-x-2 mb-4">
            <span id="status-badge" class="px-3 py-1 text-sm font-semibold rounded-full"></span>
            <select id="status-switcher" class="bg-white border border-gray-300 rounded-md shadow-sm py-1 text-sm">
                <option value="pending" {{ $returnRequest->status === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="approved" {{ $returnRequest->status === 'approved' ? 'selected' : '' }}>Đã phê duyệt</option>
                <option value="processing" {{ $returnRequest->status === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                <option value="completed" {{ $returnRequest->status === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                <option value="rejected" {{ $returnRequest->status === 'rejected' ? 'selected' : '' }}>Từ chối</option>
            </select>
        </div>


        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Chi tiết sản phẩm trả -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Chi tiết sản phẩm trả</h2>
                    @foreach ($returnRequest->returnItems as $item)
                    <div class="flex items-start space-x-4">
                        @php
                        $variant = $item->orderItem?->variant;
                        $product = $variant?->product;
                        $cover = $variant?->coverImage;
                        $imageUrl = $cover ? $cover->url : 'https://placehold.co/80x80';
                        @endphp

                        <img src="{{ $variant?->image_url ?? $imageUrl }}" alt="{{ $product?->name }}" class="w-24 h-24 rounded-md">
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
            <div class="space-y-6">
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
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold text-gray-800 mb-2">Ghi chú nội bộ</h3>
                    <form method="POST" action="{{ route('admin.refunds.note', $returnRequest->id) }}">
                        @csrf
                        @method('PUT')
                        <textarea name="admin_note" class="w-full border border-gray-300 rounded-md p-2" rows="3" placeholder="Thêm ghi chú...">{{ old('admin_note', $returnRequest->admin_note) }}</textarea>
                        <button type="submit" class="mt-2 w-full bg-gray-600 text-white py-2 rounded-md hover:bg-gray-700 transition">Lưu ghi chú</button>
                    </form>
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

    <!-- Modal từ chối -->
    <div id="rejection-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 hidden opacity-0">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Lý do từ chối yêu cầu</h2>
            <textarea id="rejection-reason" class="w-full border border-gray-300 rounded-md p-2" rows="4" placeholder="Nhập lý do... (sẽ được gửi đến khách hàng)"></textarea>
            <div class="flex justify-end space-x-3 mt-4">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Hủy</button>
                <button onclick="submitRejection()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Xác nhận từ chối</button>
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
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

        function updateStatus(status) {
            const refundAmount = refundAmountInput.value.replace(/[^\d]/g, ''); // Xóa dấu . và chữ
            const rejectionReason = document.getElementById('rejection-reason')?.value ?? null;

            fetch(`{{ route('admin.refunds.update_status', $returnRequest->id) }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: status,
                        refund_amount: refundAmount,
                        rejection_reason: rejectionReason,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        statusSwitcher.value = status;
                        renderActions(status);
                    } else {
                        toastr.error(data.message || 'Đã có lỗi xảy ra.');
                    }
                })
                .catch(() => {
                    toastr.error('Lỗi khi gửi yêu cầu đến máy chủ.');
                });
        }


        const statusBadge = document.getElementById('status-badge');
        const actionsPanel = document.getElementById('actions-panel');
        const statusSwitcher = document.getElementById('status-switcher');
        const refundAmountInput = document.getElementById('refund-amount-input');
        const rejectionModal = document.getElementById('rejection-modal');

        function renderActions(status) {
            // Cập nhật badge
            statusBadge.textContent = statusConfig[status].text;
            statusBadge.className = `px-3 py-1 text-sm font-semibold rounded-full ${statusConfig[status].badge}`;

            let content = `<h2 class="text-xl font-semibold text-gray-800 mb-4">Hành động</h2>`;
            refundAmountInput.disabled = true; // Vô hiệu hóa input mặc định

            switch (status) {
                case 'pending':
                    content += `
                    <p class="text-sm text-gray-500 mb-4">Vui lòng kiểm tra kỹ thông tin trước khi phê duyệt.</p>
                    <div class="flex space-x-3">
                        <button onclick="updateStatus('approved')" class="flex-1 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">Phê duyệt</button>
                        <button onclick="openModal()" class="flex-1 bg-white border border-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-50 transition">Từ chối</button>
                    </div>
                `;
                    break;
                case 'approved':
                    content += `
        <p class="text-sm text-gray-500 mb-4">Yêu cầu đã được duyệt. Chờ nhận hàng từ khách.</p>
        <button onclick="updateStatus('processing')" class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition">
            Xác nhận Đã nhận hàng
        </button>
    `;
                    break;

                case 'processing':
                    refundAmountInput.disabled = false;
                    content += `<p class="text-sm text-gray-500 mb-2">Hàng đã được nhận. Vui lòng kiểm tra thông tin hoàn tiền.</p>`;

                    const method = "{{ $returnRequest->refund_method }}";
                    const amountFormatted = refundAmountInput.value;

                    if (method === 'points') {
                        content += `
            <button onclick="updateStatus('completed')" class="w-full bg-green-600 text-white py-3 rounded-md hover:bg-green-700 transition font-bold">
                Hoàn điểm (${Math.floor({{ $returnRequest->refund_amount }})} điểm)
            </button>
        `;
                    } else if (method === 'bank') {
                        content += `
            <button onclick="updateStatus('completed')" class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition font-bold">
                Chuyển khoản ${amountFormatted} VNĐ
            </button>
        `;
                    } else if (method === 'coupon') {
                        content += `
            <button onclick="updateStatus('completed')" class="w-full bg-yellow-500 text-white py-3 rounded-md hover:bg-yellow-600 transition font-bold">
                Gửi mã giảm giá trị giá ${amountFormatted} VNĐ
            </button>
        `;
                    } else {
                        content += `<p class="text-sm text-red-600">Không xác định được phương thức hoàn tiền.</p>`;
                    }
                    break;

                case 'completed':
                    content += `<p class="text-sm text-green-600 text-center font-semibold">Yêu cầu đã được xử lý và hoàn tất.</p>`;
                    break;
                case 'rejected':
                    content += `
                    <p class="text-sm text-red-600 text-center font-semibold">Yêu cầu đã bị từ chối.</p>
                    <p class="text-sm text-gray-600 mt-2"><strong>Lý do: {{ $returnRequest->rejection_reason }}</strong> .</p>
                 `;
                    break;
            }
            actionsPanel.innerHTML = content;
        }

        // Modal functions
        function openModal() {
            rejectionModal.classList.remove('hidden');
            setTimeout(() => rejectionModal.classList.remove('opacity-0'), 20);
        }

        function closeModal() {
            rejectionModal.classList.add('opacity-0');
            setTimeout(() => rejectionModal.classList.add('hidden'), 250);
        }

        function submitRejection() {
            const reason = document.getElementById('rejection-reason').value.trim();

            if (!reason) {
                toastr.warning('Vui lòng nhập lý do từ chối');
                return;
            }

            // Gửi API gọi Laravel updateStatus
            updateStatus('rejected');

            closeModal();
        }


        // Initial render
        statusSwitcher.addEventListener('change', (e) => renderActions(e.target.value));
        renderActions(statusSwitcher.value);
    </script>
    @endpush