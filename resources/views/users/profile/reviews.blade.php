@extends('users.layouts.profile')

@section('content')
<div class="offcanvas-body d-flex flex-column gap-4 pt-2">
    <h1 class="h2 mb-4">Sản phẩm chờ đánh giá</h1>
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @foreach($itemsForReview as $entry)
    @php
    $product = $entry['product'];
    $variantId = $entry['variant_id'];
    $orderItemId = $entry['order_item_id'];
    $review = $entry['review'];
    @endphp

    <div>
        <div class="d-md-flex align-items-center justify-content-between gap-4 border-bottom py-3">
            <div class="d-flex align-items-center">
                <img src="{{ $product->coverImageUrl ?? asset($product->cover_image_url) }}" width="64" alt="">
                <a href="{{ route('users.products.show', $product->slug) }}" class="ms-3 text-decoration-none">
                    {{ $product->name }}
                </a>
            </div>

            @if($review)
            {{-- Đã đánh giá --}}
            <div class="text-warning fw-bold fs-5">
                {!! str_repeat('★', $review->rating) !!}
                {!! str_repeat('☆', 5 - $review->rating) !!}
                <a href="{{ route('reviews.show', $review->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="ci-eye me-1"></i> Xem chi tiết
                </a>
            </div>
            @else
            <button
                class="btn btn-secondary btn-review"
                data-bs-toggle="modal"
                data-bs-target="#modalReview"
                data-variant-id="{{ $variantId }}"
                data-order-item-id="{{ $orderItemId }}"
                data-name="{{ $product->name }}"
                data-img="{{ asset($product->coverImageUrl) }}"
                data-price="{{ number_format($product->defaultVariant->price ?? 0, 2) }}"
                data-url="{{ route('users.products.show', $product->slug) }}">
                Đánh giá
            </button>
            @endif
        </div>
    </div>
    @endforeach

    <!-- Modal đánh giá -->
    <div class="modal fade" id="modalReview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('reviews.store') }}" method="POST" class="modal-content needs-validation" novalidate>
                @csrf
                <input type="hidden" name="product_variant_id" id="inputVariantId">
                <input type="hidden" name="order_item_id" id="inputOrderItemId">

                <div class="modal-header">
                    <h5 class="modal-title">Đánh giá sản phẩm: <span id="modalProductName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="text-center mb-3">
                        <a href="#" id="modalProductLink">
                            <img id="modalProductImg" src="" width="100" alt="">
                        </a>
                        <div class="mt-2">Giá: $<span id="modalProductPrice"></span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Xếp hạng <span class="text-danger">*</span></label>
                        <select class="form-select" name="rating" required>
                            <option value="">Chọn...</option>
                            @for($i=1; $i<=5; $i++)
                                <option value="{{ $i }}">{!! str_repeat('★', $i) . str_repeat('☆', 5-$i) !!}</option>
                                @endfor
                        </select>
                        @error('rating')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tiêu đề</label>
                        <input type="text" name="title" class="form-control">
                        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nội dung đánh giá <span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control" rows="4" required></textarea>
                        @error('comment')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalReview');
        modal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('inputVariantId').value = btn.getAttribute('data-variant-id');
            document.getElementById('inputOrderItemId').value = btn.getAttribute('data-order-item-id');
            document.getElementById('modalProductName').textContent = btn.getAttribute('data-name');
            document.getElementById('modalProductImg').src = btn.getAttribute('data-img');
            document.getElementById('modalProductPrice').textContent = btn.getAttribute('data-price');
            document.getElementById('modalProductLink').href = btn.getAttribute('data-url');
        });
    });
</script>
@endpush
@endsection