@extends('users.layouts.profile')

@section('styles')
<style>
    /* CSS đầy đủ để tạo giao diện như mẫu */
    body { background-color: #f9fafb; }
    .main-content { max-width: 1200px; margin: auto; }
    .details-card { background-color: #fff; border-radius: 0.75rem; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); overflow: hidden; }
    .details-header { padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;}
    .details-main { padding: 1.5rem; }
    .details-footer { padding: 1rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; text-align: right; }

    .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; white-space: nowrap;}
    .status-completed { background-color: #d1fae5; color: #065f46; }
    .status-processing { background-color: #feefc7; color: #92400e; }
    .status-shipping { background-color: #dbeafe; color: #1e40af; }
    .status-cancelled { background-color: #fee2e2; color: #991b1b; }
    .status-awaiting-pickup { background-color: #e5e0ff; color: #5b21b6; }
    .status-returned { background-color: #e5e7eb; color: #4b5563; }

    /* Status Tracker Styles */
    .tracker-container { display: flex; align-items: flex-start; }
    .tracker-item { text-align: center; flex: 1; }
    .tracker-line-container { flex-grow: 1; padding: 0 0.5rem; margin-top: 0.6rem; }
    .tracker-line { height: 2px; background-color: #e5e7eb; }
    .tracker-line-filled { background-color: #10b981; }
    .tracker-dot { width: 1.5rem; height: 1.5rem; border-radius: 9999px; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; color: white; transition: background-color 0.3s; font-size: 0.75rem; font-weight: bold; margin: auto; }
    .tracker-dot.active { background-color: #10b981; }
    .tracker-label { font-size: 0.75rem; margin-top: 0.5rem; color: #6b7280;}
    .tracker-label.active { color: #dc2626; font-weight: bold; }

    .info-box { background-color: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; }

    .product-list-item { display: flex; align-items: flex-start; gap: 1rem; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid #f3f4f6;}
    .product-list-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0;}
    .product-image { width: 60px; height: 60px; border-radius: 0.375rem; object-fit: cover; flex-shrink: 0; }

    .btn-action {
        background-color: #dc2626; color: #fff; font-weight: 600; padding: 0.5rem 1rem;
        border-radius: 0.5rem; text-decoration: none; transition: background-color 0.2s;
        display: inline-block; border: none;
    }
    .btn-action:hover { background-color: #b91c1c; color: #fff; }
    .btn-action-secondary {
        background-color: #fff; color: #374151; border: 1px solid #d1d5db;
    }
    .btn-action-secondary:hover { background-color: #f3f4f6; }
    .status-dot-cancelled { background-color: #dc2626 !important; }
</style>
@endsection

@section('content')
<div class="main-content">
    <div class="mb-4">
        <a href="{{ route('orders.index') }}" class="text-secondary fw-medium text-decoration-none d-flex align-items-center">
            <i class="fas fa-arrow-left me-2"></i>
            Quay lại danh sách đơn hàng
        </a>
    </div>

    @php
        //============= LOGIC TRUNG TÂM =============//
        $statusSteps = [
            'pending_confirmation' => 1, 'processing' => 2,
            'awaiting_shipment' => 2, 'shipped' => 3, 'out_for_delivery' => 3,
            'delivered' => 4, 'cancelled' => -1, 'failed_delivery' => -1,
            'returned' => -1
        ];
        $currentStep = $statusSteps[$order->status] ?? 0;
        $statusInfo = match ($order->status) {
            'delivered' => ['text' => 'Hoàn tất', 'class' => 'status-completed'],
            'processing' => ['text' => 'Đang xử lý', 'class' => 'status-processing'],
            'shipped', 'out_for_delivery' => ['text' => 'Đang giao', 'class' => 'status-shipping'],
            'cancelled', 'failed_delivery' => ['text' => 'Đã hủy', 'class' => 'status-cancelled'],
            'awaiting_shipment' => ['text' => 'Chờ lấy hàng', 'class' => 'status-awaiting-pickup'],
            'returned' => ['text' => 'Trả hàng', 'class' => 'status-returned'],
            default => ['text' => ucfirst($order->status), 'class' => 'status-returned'],
        };
        $isPickupOrder = !empty($order->store_location_id);

        function renderDeliveryDate($dateString) {
            if (empty($dateString)) return 'Chưa xác định';
            try { return \Carbon\Carbon::parse($dateString)->format('d/m/Y'); } catch (\Exception $e) { return e($dateString); }
        }
    @endphp

    <div class="details-card">
        <header class="details-header">
            <div>
                <h4 class="fw-bold mb-1">Chi tiết đơn hàng #{{ $order->order_code }}</h4>
                <p class="text-muted small mb-0">Đặt hàng ngày {{ $order->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-invoice me-1"></i> Xem hóa đơn
                </a>
                <span class="status-badge {{ $statusInfo['class'] }}">{{ $statusInfo['text'] }}</span>
            </div>
        </header>

        <main class="details-main">
           <div class="mb-5">
                <h5 class="fw-bold mb-4">Trạng thái đơn hàng</h5>
                @if($order->status == 'cancelled')
                {{-- Giao diện đặc biệt cho đơn hàng BỊ HỦY --}}
                <div class="tracker-container">
                    <div class="tracker-item">
                        <div class="tracker-dot active status-dot-cancelled">
                            <i class="fas fa-times"></i>
                        </div>
                        <p class="tracker-label active">Đã hủy</p>
                    </div>
                    {{-- Các item sau được thêm chữ nhưng làm mờ đi --}}
                    <div class="tracker-line-container"><div class="tracker-line"></div></div>
                    <div class="tracker-item"><div class="tracker-dot"></div><p class="tracker-label text-muted">Đang xử lý</p></div>
                    <div class="tracker-line-container"><div class="tracker-line"></div></div>
                    <div class="tracker-item"><div class="tracker-dot"></div><p class="tracker-label text-muted">Đang giao</p></div>
                    <div class="tracker-line-container"><div class="tracker-line"></div></div>
                    <div class="tracker-item"><div class="tracker-dot"></div><p class="tracker-label text-muted">Hoàn tất</p></div>
                </div>
                @elseif($currentStep > 0)
                    {{-- Giao diện cho các trạng thái khác --}}
                    <div class="tracker-container">
                        <div class="tracker-item"><div class="tracker-dot {{ $currentStep >= 1 ? 'active' : '' }}">✓</div><p class="tracker-label {{ $currentStep == 1 ? 'active' : '' }}">Chờ xác nhận</p></div>
                        <div class="tracker-line-container"><div class="tracker-line {{ $currentStep >= 2 ? 'tracker-line-filled' : '' }}"></div></div>
                        <div class="tracker-item"><div class="tracker-dot {{ $currentStep >= 2 ? 'active' : '' }}">@if($currentStep >= 2) ✓ @else 2 @endif</div><p class="tracker-label {{ $currentStep == 2 ? 'active' : '' }}">Đang xử lý</p></div>
                        <div class="tracker-line-container"><div class="tracker-line {{ $currentStep >= 3 ? 'tracker-line-filled' : '' }}"></div></div>
                        <div class="tracker-item"><div class="tracker-dot {{ $currentStep >= 3 ? 'active' : '' }}">@if($currentStep >= 3) ✓ @else 3 @endif</div><p class="tracker-label {{ $currentStep == 3 ? 'active' : '' }}">Đang giao</p></div>
                        <div class="tracker-line-container"><div class="tracker-line {{ $currentStep >= 4 ? 'tracker-line-filled' : '' }}"></div></div>
                        <div class="tracker-item"><div class="tracker-dot {{ $currentStep >= 4 ? 'active' : '' }}">@if($currentStep >= 4) ✓ @else 4 @endif</div><p class="tracker-label {{ $currentStep == 4 ? 'active' : '' }}">Hoàn tất</p></div>
                    </div>
                @endif
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <h5 class="fw-bold mb-4">Danh sách sản phẩm</h5>
                    <div>
                        @foreach($order->items as $item)
                        <div class="product-list-item">
                            <img src="{{ $item->image_url ?? 'https://placehold.co/80x80/e2e8f0/333?text=Ảnh' }}" alt="{{ $item->product_name }}" class="product-image">
                            <div class="flex-grow-1">
                                <p class="fw-bold text-dark mb-1">{{ $item->product_name }}</p>
                                @if(!empty($item->variant_attributes) && is_iterable($item->variant_attributes))
                                    @foreach($item->variant_attributes as $key => $value)
                                        <p class="text-muted small mb-0">{{ $key }}: {{ $value }}</p>
                                    @endforeach
                                @endif
                                <p class="text-muted small mb-1">SL: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-end">
                                <p class="fw-semibold text-dark">{{ number_format($item->total_price, 0, ',', '.') }} VNĐ</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="d-flex flex-column" style="gap: 1rem;">

                        {{-- Box 1: Thời gian nhận hàng / Thông tin vận chuyển --}}
                        @if($isPickupOrder)
                            <div class="info-box">
                                <h6 class="fw-bold mb-3">Thời gian nhận hàng</h6>
                                <p class="small mb-1"><strong>Ngày nhận:</strong> {{ renderDeliveryDate($order->desired_delivery_date) }}</p>
                                @if($order->desired_delivery_time_slot)
                                     <p class="small mb-0"><strong>Khung giờ:</strong> {{ $order->desired_delivery_time_slot }}</p>
                                @endif
                            </div>
                        @else
                            <div class="info-box">
                                <h6 class="fw-bold mb-3">Thông tin vận chuyển</h6>
                                @if($order->shipping_method)
                                    <p class="small mb-1"><strong>Đơn vị:</strong> {{ $order->shipping_method }}</p>
                                @endif
                                <p class="small mb-1"><strong>Ngày giao dự kiến:</strong> {{ renderDeliveryDate($order->desired_delivery_date) }}</p>
                                @if($order->desired_delivery_time_slot)
                                    <p class="small mb-0"><strong>Khung giờ giao:</strong> {{ $order->desired_delivery_time_slot }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Box 2: Địa chỉ giao hàng --}}
                        <div class="info-box">
                            <h6 class="fw-bold mb-3">Địa chỉ giao hàng</h6>
                            <div class="small text-secondary">
                                <p class="fw-bold mb-1">{{ $order->customer_name ?? '' }}</p>
                                <p class="mb-1">SĐT: {{ $order->customer_phone ?? '' }}</p>
                                @if(!empty($order->shipping_address_line1))<p class="mb-1">{{ $order->shipping_address_line1 }}</p>@endif
                                @if($order->shippingWard)<p class="mb-1"><strong>Phường/Xã:</strong> {{ $order->shippingWard->name }}</p>@endif
                                @if($order->shippingDistrict)<p class="mb-1"><strong>Quận/Huyện:</strong> {{ $order->shippingDistrict->name }}</p>@endif
                                @if($order->shippingProvince)<p class="mb-0"><strong>Tỉnh/TP:</strong> {{ $order->shippingProvince->name }}</p>@endif
                            </div>
                        </div>

                        {{-- Box 3: Thông tin thanh toán --}}
                        <div class="info-box">
                            <h6 class="fw-bold mb-3">Thông tin thanh toán</h6>
                            <div class="small text-secondary">
                                <p class="mb-2"><strong>Phương thức:</strong> {{ $order->payment_method }}</p>
                                <p class="mb-0"><strong>Tình trạng:</strong>
                                    @if($order->payment_status == 'paid') <span class="badge bg-success">Đã thanh toán</span>
                                    @elseif($order->payment_status == 'pending') <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                                    @elseif($order->payment_status == 'failed') <span class="badge bg-danger">Thất bại</span>
                                    @else <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Box 4: Tổng cộng --}}
                        <div class="info-box">
                            <h6 class="fw-bold mb-3">Tổng cộng</h6>
                            <div class="d-flex flex-column small" style="gap: 0.5rem;">
                                <div class="d-flex justify-content-between"><span>Tạm tính:</span><span class="fw-medium">{{ number_format($order->sub_total, 0, ',', '.') }} ₫</span></div>
                                <div class="d-flex justify-content-between"><span>Phí vận chuyển:</span><span class="fw-medium">{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</span></div>
                                @if($order->discount_amount > 0)
                                <div class="d-flex justify-content-between text-danger"><span>Giảm giá:</span><span class="fw-medium">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</span></div>
                                @endif
                                <hr class="my-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Tổng cộng:</span>
                                    <span class="fw-bold text-danger fs-5">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="details-footer">
            @if($order->status == 'delivered')
                {{-- Lấy thông tin sản phẩm đầu tiên trong đơn hàng --}}
                @php
                    $firstItem = $order->items->first();
                    $canReview = $firstItem && !$firstItem->has_reviewed && $firstItem->product_variant_id;
                @endphp

                <a href="#" class="btn-action btn-action-secondary">Yêu cầu trả hàng</a>

                {{-- Nút "Viết đánh giá" sẽ xuất hiện nếu có thể đánh giá sản phẩm đầu tiên --}}
                @if($canReview)
                    <button type="button" class="btn-action write-review-btn"
                        data-order-item-id="{{ $firstItem->id }}"
                        data-product-variant-id="{{ $firstItem->product_variant_id }}"
                        data-product-name="{{ $firstItem->product_name }}">
                        Viết đánh giá
                    </button>
                @endif
            @endif

            @if(in_array($order->status, ['pending_confirmation', 'processing', 'awaiting_shipment']))
                <button class="btn-action" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">Hủy đơn hàng</button>
            @endif
        </footer>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initReviewModal();
    });

    function initReviewModal() {
        const modal = document.getElementById('review-modal');
        const closeBtn = document.getElementById('close-review-modal-btn');
        const starsContainer = document.getElementById('review-stars-container');
        const submitBtn = document.getElementById('submit-review-btn');
        const reviewText = document.getElementById('review-text');
        const fileInput = document.getElementById('file-upload');
        let selectedRating = 0;

        // Gán sự kiện mở modal cho từng nút
        document.querySelectorAll('.write-review-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedRating = 0;
                reviewText.value = '';
                fileInput.value = '';

                const productName = btn.dataset.productName;
                const orderItemId = btn.dataset.orderItemId;
                const productVariantId = btn.dataset.productVariantId;

                document.getElementById('product-review-title').textContent = `Đánh giá: ${productName}`;
                document.getElementById('order_item_id').value = orderItemId;
                document.getElementById('product_variant_id').value = productVariantId;

                renderStars();
                showModal(modal);
            });
        });

        // Đóng modal
        closeBtn.addEventListener('click', () => hideModal(modal));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) hideModal(modal);
        });

        // Gửi đánh giá
        submitBtn.addEventListener('click', () => {
            const orderItemId = document.getElementById('order_item_id').value;
            const productVariantId = document.getElementById('product_variant_id').value;
            const comment = reviewText.value.trim();
            const files = fileInput.files;

            if (!selectedRating) {
                return toastr.warning('Vui lòng chọn số sao');
            }

            const formData = new FormData();
            formData.append('rating', selectedRating);
            formData.append('comment', comment);
            formData.append('product_variant_id', productVariantId);
            formData.append('order_item_id', orderItemId);

            for (let i = 0; i < files.length && i < 3; i++) {
                formData.append('media[]', files[i]);
            }

            fetch("{{ route('reviews.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(async res => {
                    const contentType = res.headers.get("content-type");
                    if (res.ok && contentType?.includes("application/json")) {
                        return res.json();
                    }
                    const text = await res.text();
                    throw new Error('Phản hồi không hợp lệ: ' + text);
                })
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message || 'Cảm ơn bạn đã đánh giá sản phẩm!');
                        hideModal(modal);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(data.message || 'Gửi đánh giá thất bại.');
                    }
                })
                .catch(err => {
                    toastr.error(err.message || 'Lỗi kết nối máy chủ.');
                });
        });

        function renderStars() {
            starsContainer.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                star.setAttribute('class', 'review-star w-8 h-8 text-gray-300 cursor-pointer transition-colors');
                star.setAttribute('fill', 'currentColor');
                star.setAttribute('viewBox', '0 0 20 20');
                star.dataset.rating = i;
                star.innerHTML = `<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>`;
                starsContainer.appendChild(star);

                star.addEventListener('mouseover', () => {
                    document.querySelectorAll('.review-star').forEach(s => {
                        s.classList.toggle('text-yellow-400', s.dataset.rating <= star.dataset.rating);
                    });
                });
                star.addEventListener('mouseout', () => {
                    document.querySelectorAll('.review-star').forEach(s => {
                        s.classList.remove('text-yellow-400');
                        s.classList.add(s.dataset.rating <= selectedRating ? 'text-yellow-400' : 'text-gray-300');
                    });
                });
                star.addEventListener('click', () => {
                    selectedRating = parseInt(star.dataset.rating);
                    document.querySelectorAll('.review-star').forEach(s => {
                        s.classList.remove('text-yellow-400', 'text-gray-300');
                        s.classList.add(s.dataset.rating <= selectedRating ? 'text-yellow-400' : 'text-gray-300');
                    });
                });
            }
        }

        function showModal(modal) {
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('opacity-100', 'scale-100'), 10);
        }

        function hideModal(modal) {
            modal.classList.remove('opacity-100', 'scale-100');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }
</script>

@endsection
