@extends('users.layouts.profile') {{-- Sử dụng layout chính của bạn --}}

@section('styles')
{{-- Thêm một chút CSS để modal và sao hoạt động --}}
<style>
    .review-star {
        font-size: 2rem;
        color: #d1d5db; /* gray-300 */
        cursor: pointer;
        transition: color 0.2s;
    }
    .review-star:hover,
    .review-star.active {
        color: #f59e0b; /* amber-400 */
    }
    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 50;
        background-color: rgba(0,0,0,0.5);
    }
</style>
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Viết đánh giá</h3>
            <p class="text-muted">Cho đơn hàng <a href="{{ route('orders.show', $order->id) }}">#{{ $order->order_code }}</a></p>
        </div>
        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary">Quay lại đơn hàng</a>
    </div>

    {{-- Lặp qua từng sản phẩm trong đơn hàng --}}
    @foreach($order->items as $item)
        <div class="card mb-3">
            <div class="card-body d-flex align-items-center gap-3">
                <a href="{{-- route('products.show', $item->variant->product->slug) --}}">
                    <img src="{{ $item->variant->product->coverImage->url ?? 'https://placehold.co/80x80' }}" alt="{{ $item->product_name }}"
                         style="width: 80px; height: 80px; object-fit: cover;" class="rounded">
                </a>
                <div class="flex-grow-1">
                    <h6 class="mb-1">
                        <a href="{{-- route('products.show', $item->variant->product->slug) --}}" class="text-dark text-decoration-none">
                            {{ $item->product_name }}
                        </a>
                    </h6>
                    <p class="small text-muted mb-1">Mua ngày: {{ $order->created_at->format('d/m/Y') }}</p>
                </div>
                <div class="ms-auto text-end" style="min-width: 150px;">
                    @if($item->has_reviewed)
                        <span class="badge bg-success">Đã đánh giá</span>
                    @else
                        <button type="button" class="btn btn-danger write-review-btn"
                                data-order-item-id="{{ $item->id }}"
                                data-product-variant-id="{{ $item->product_variant_id }}"
                                data-product-name="{{ $item->product_name }}">
                            Viết đánh giá
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ========================================================== --}}
{{-- === TOÀN BỘ CODE HTML CHO MODAL ĐÁNH GIÁ === --}}
{{-- ========================================================== --}}
<div id="review-modal" class="modal-backdrop" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md m-auto">
        <div class="p-4 border-b d-flex justify-content-between align-items-center">
            <h5 class="fw-bold" id="product-review-title">Đánh giá sản phẩm</h5>
            <button id="close-review-modal-btn" type="button" class="btn-close"></button>
        </div>
        <div class="p-4">
            <form id="review-form">
                <input type="hidden" id="order_item_id" name="order_item_id">
                <input type="hidden" id="product_variant_id" name="product_variant_id">
                <div class="mb-3">
                    <label class="form-label">Chất lượng sản phẩm</label>
                    <div id="review-stars-container" class="d-flex align-items-center gap-1">
                        {{-- Các ngôi sao sẽ được JS tạo ra ở đây --}}
                    </div>
                </div>
                <div class="mb-3">
                    <label for="review-text" class="form-label">Bình luận của bạn</label>
                    <textarea id="review-text" name="comment" rows="4" class="form-control"></textarea>
                </div>
                <div>
                    <label for="file-upload" class="form-label">Thêm hình ảnh/video (Tối đa 6)</label>
                    <input id="file-upload" name="media[]" type="file" multiple accept="image/*,video/*" class="form-control">
                </div>
            </form>
        </div>
        <div class="p-3 bg-light text-end">
            <button id="submit-review-btn" class="btn btn-danger">Gửi đánh giá</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- ========================================================== --}}
{{-- === TOÀN BỘ CODE JAVASCRIPT ĐIỀU KHIỂN MODAL === --}}
{{-- ========================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('review-modal');
    if (!modal) return;

    const closeBtn = document.getElementById('close-review-modal-btn');
    const starsContainer = document.getElementById('review-stars-container');
    const submitBtn = document.getElementById('submit-review-btn');
    const reviewText = document.getElementById('review-text');
    const fileInput = document.getElementById('file-upload');
    let selectedRating = 0;

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
            showModal();
        });
    });

    closeBtn.addEventListener('click', hideModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) hideModal();
    });

    submitBtn.addEventListener('click', () => {
        const formData = new FormData();
        formData.append('rating', selectedRating);
        formData.append('comment', reviewText.value.trim());
        formData.append('order_item_id', document.getElementById('order_item_id').value);
        formData.append('product_variant_id', document.getElementById('product_variant_id').value);
        Array.from(fileInput.files).forEach(file => {
            formData.append('media[]', file);
        });

        if (!selectedRating) {
            alert('Vui lòng chọn số sao đánh giá.');
            return;
        }
        if (!reviewText.value.trim()) {
            alert('Vui lòng nhập bình luận.');
            return;
        }

        fetch("{{ route('reviews.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Cảm ơn bạn đã đánh giá!');
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi kết nối. Vui lòng kiểm tra lại.');
        });
    });

    function showModal() {
        modal.style.display = 'flex';
    }

    function hideModal() {
        modal.style.display = 'none';
    }

    function renderStars() {
        starsContainer.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('span');
            star.innerHTML = '☆';
            star.classList.add('review-star');
            star.dataset.rating = i;
            starsContainer.appendChild(star);

            star.addEventListener('mouseover', () => handleStarHover(i));
            star.addEventListener('mouseout', updateStars);
            star.addEventListener('click', () => handleStarClick(i));
        }
        updateStars();
    }

    function updateStars() {
        document.querySelectorAll('.review-star').forEach(s => {
            s.classList.toggle('active', s.dataset.rating <= selectedRating);
            s.innerHTML = s.dataset.rating <= selectedRating ? '★' : '☆';
        });
    }

    function handleStarHover(rating) {
        document.querySelectorAll('.review-star').forEach(s => {
            s.classList.toggle('active', s.dataset.rating <= rating);
            s.innerHTML = s.dataset.rating <= rating ? '★' : '☆';
        });
    }

    function handleStarClick(rating) {
        selectedRating = rating;
        updateStars();
    }
});
</script>
@endpush
