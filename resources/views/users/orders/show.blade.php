@extends('users.layouts.profile')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Chi tiết đơn hàng #{{ $order->order_code }}</h3>
                <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-invoice"></i> Xem hóa đơn
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Trạng thái:</strong>
                                @if($order->status == 'pending_confirmation')
                                <span class="badge bg-warning text-dark">Chờ xác nhận</span>
                                @elseif($order->status == 'processing')
                                <span class="badge bg-info text-dark">Đang xử lý</span>
                                @elseif($order->status == 'awaiting_shipment')
                                <span class="badge bg-info text-dark">Chờ lấy hàng</span>
                                @elseif($order->status == 'shipped')
                                <span class="badge bg-primary">Đang giao</span>
                                @elseif($order->status == 'out_for_delivery')
                                <span class="badge bg-primary">Đang giao</span>
                                @elseif($order->status == 'delivered')
                                <span class="badge bg-success">Đã giao</span>
                                @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">Đã hủy</span>
                                @elseif($order->status == 'returned')
                                <span class="badge bg-secondary">Trả hàng</span>
                                @elseif($order->status == 'failed_delivery')
                                <span class="badge bg-danger">Giao hàng thất bại</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </p>
                            <p><strong>Phương thức thanh toán:</strong> {{ $order->payment_method }}</p>
                            <p><strong>Trạng thái thanh toán:</strong>
                                @if($order->payment_status == 'paid')
                                <span class="badge bg-success">Đã thanh toán</span>
                                @elseif($order->payment_status == 'pending')
                                <span class="badge bg-warning">Chờ thanh toán</span>
                                @elseif($order->payment_status == 'failed')
                                <span class="badge bg-danger">Thanh toán thất bại</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phương thức vận chuyển:</strong> {{ $order->shipping_method ?? 'Chưa xác định' }}</p>
                            <p><strong>Phí vận chuyển:</strong> {{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</p>
                            <p><strong>Giảm giá:</strong> {{ number_format($order->discount_amount, 0, ',', '.') }} ₫</p>
                            <p><strong>Tổng tiền:</strong> <span class="text-danger fw-bold">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Địa chỉ nhận hàng</h6>
                            <p>{{ $order->customer_name }}</p>
                            <p>{{ $order->customer_phone }}</p>
                            <p>{{ $order->shipping_address_line1 }}</p>
                            @if($order->shipping_address_line2)
                            <p>{{ $order->shipping_address_line2 }}</p>
                            @endif
                            <p>
                                @if($order->shippingWard)
                                {{ $order->shippingWard->name }},
                                @else
                                {{ $order->shipping_ward_code }},
                                @endif
                                @if($order->shippingProvince)
                                {{ $order->shippingProvince->name }}
                                @else
                                {{ $order->shipping_province_code }}
                                @endif
                            </p>
                        </div>

                        @if($order->billing_address_line1)
                        <div class="col-md-6">
                            <h6>Địa chỉ thanh toán</h6>
                            <p>{{ $order->customer_name }}</p>
                            <p>{{ $order->billing_address_line1 }}</p>
                            @if($order->billing_address_line2)
                            <p>{{ $order->billing_address_line2 }}</p>
                            @endif
                            <p>
                                @if($order->billingWard)
                                {{ $order->billingWard->name }},
                                @else
                                {{ $order->billing_ward_code }},
                                @endif
                                @if($order->billingProvince)
                                {{ $order->billingProvince->name }}
                                @else
                                {{ $order->billing_province_code }}
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Sản phẩm đã đặt</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                    <td>Đánh giá</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->image_url)
                                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="img-thumbnail me-3" style="width: 60px;">
                                            @else
                                            <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                <i class="fas fa-box-open text-muted"></i>
                                            </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $item->product_name }}</h6>
                                                <small class="text-muted">SKU: {{ $item->sku }}</small>
                                                @if(!empty($item->variant_attributes))
                                                <div class="mt-1">
                                                    @foreach($item->variant_attributes as $key => $value)
                                                    <small class="text-muted">{{ $key }}: {{ $value }}</small><br>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($item->price, 0, ',', '.') }} ₫</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->total_price, 0, ',', '.') }} ₫</td>
                                    <td>
                                        @if(
                                        $order->status === 'delivered' &&
                                        !$item->has_reviewed &&
                                        $item->product_variant_id &&
                                        $item->product_name
                                        )
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary write-review-btn"
                                            data-order-item-id="{{ $item->id }}"
                                            data-product-variant-id="{{ $item->product_variant_id }}"
                                            data-product-name="{{ $item->product_name }}">
                                            <i class="fas fa-star me-1"></i> Viết đánh giá
                                        </button>
                                        @elseif($item->has_reviewed)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i> Đã đánh giá
                                        </span>
                                        @else
                                        @endif

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                                    <td>{{ number_format($order->sub_total, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                    <td>{{ number_format($order->shipping_fee, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Giảm giá:</strong></td>
                                    <td>-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td class="text-danger fw-bold">{{ number_format($order->grand_total, 0, ',', '.') }} ₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($order->notes_from_customer)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ghi chú từ khách hàng</h5>
                </div>
                <div class="card-body">
                    <p>{{ $order->notes_from_customer }}</p>
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-between">
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>

                @if(in_array($order->status, ['pending_confirmation', 'processing', 'awaiting_shipment']))
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                    <i class="fas fa-times"></i> Hủy đơn hàng
                </button>
                @endif
            </div>

            <!-- Modal hủy đơn hàng -->
            <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelOrderModalLabel">Hủy đơn hàng</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p>Bạn có chắc chắn muốn hủy đơn hàng <strong>{{ $order->order_code }}</strong>?</p>
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Lý do hủy đơn</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="review-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 transition-opacity duration-300">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-transform duration-300 scale-95">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900" id="product-review-title">Viết đánh giá</h3>
                        <button id="close-review-modal-btn" class="text-gray-500 hover:text-gray-700 text-3xl leading-none">&times;</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <input type="hidden" id="order_item_id">
                        <input type="hidden" id="product_variant_id">

                        <div>
                            <label class="font-semibold text-gray-700">Đánh giá của bạn</label>
                            <div id="review-stars-container" class="flex items-center gap-1 text-4xl mt-1">
                                <!-- stars render JS -->
                            </div>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Bình luận</label>
                            <textarea id="review-text" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" rows="4"></textarea>
                        </div>
                        <div>
                            <label class="font-semibold text-gray-700">Thêm hình ảnh/video</label>
                            <input id="file-upload" name="media[]" type="file" accept="image/*,video/*" multiple class="form-control">
                        </div>
                        <div class="text-right">
                            <button id="submit-review-btn" class="bg-blue-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-blue-700 transition-colors">Gửi đánh giá</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
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