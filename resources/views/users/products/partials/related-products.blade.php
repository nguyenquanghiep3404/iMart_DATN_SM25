<section class="container px-4 pt-5 mt-2 mt-sm-3 mt-lg-4">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 pb-md-4">
        <h2 class="h3 mb-0">Sản phẩm liên quan</h2>
        <div class="nav ms-3">
            <a class="nav-link animate-underline px-0 py-2" href="#">
                <span class="animate-target">Xem tất cả</span>
                <i class="ci-chevron-right fs-base ms-1"></i>
            </a>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-4">
        @foreach ($relatedProducts as $relatedProduct)
            <div class="col">
                <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
                    <div class="position-relative">
                        @php
                            $variant = $relatedProduct->variants->first();
                            $now = now();
                            $onSale = $variant && $variant->sale_price && $variant->sale_price_starts_at && $variant->sale_price_ends_at && $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
                            $price = $onSale ? $variant->sale_price : $variant->price;
                            $originalPrice = $onSale ? $variant->price : null;
                            $discountPercent = $onSale && $variant->price > 0 ? round((($variant->price - $variant->sale_price) / $variant->price) * 100) : 0;
                        @endphp

                        @if ($onSale && $discountPercent > 0)
                            <div class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 rounded-bottom-end"
                                style="z-index: 10; font-weight: 600; font-size: 0.85rem;">
                                Giảm {{ $discountPercent }}%
                            </div>
                        @endif

                        <a class="d-block rounded-top overflow-hidden p-3 p-sm-4"
                            href="{{ route('users.products.show', $relatedProduct->slug) }}">
                            <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                                <img src="{{ $relatedProduct->coverImage ? Storage::url($relatedProduct->coverImage->path) : asset('images/placeholder.jpg') }}"
                                    alt="{{ $relatedProduct->name }}" loading="lazy">
                            </div>
                        </a>
                    </div>

                    <div class="w-100 min-w-0 px-1 pb-2 px-sm-3 pb-sm-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="d-flex gap-1 fs-xs">
                                @php
                                    $rating = $relatedProduct->average_rating ?? 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $rating >= $i
                                            ? '<i class="ci-star-filled text-warning"></i>'
                                            : ($rating > $i - 1
                                                ? '<i class="ci-star-half text-warning"></i>'
                                                : '<i class="ci-star text-body-tertiary opacity-75"></i>');
                                    }
                                @endphp
                            </div>
                            <span
                                class="text-body-tertiary fs-xs">({{ $relatedProduct->reviews_count ?? 0 }})</span>
                        </div>

                        <h3 class="pb-1 mb-2">
                            <a class="d-block fs-sm fw-medium text-truncate"
                                href="{{ route('users.products.show', $relatedProduct->slug) }}">
                                <span class="animate-target">{{ $relatedProduct->name }}</span>
                            </a>
                        </h3>

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="h5 lh-1 mb-0">
                                @if ($price)
                                    @if ($onSale)
                                        <span class="text-danger">{{ number_format($price) }}đ</span>
                                        <del
                                            class="text-muted fs-sm ms-2">{{ number_format($originalPrice) }}đ</del>
                                    @else
                                        {{ number_format($price) }}đ
                                    @endif
                                @else
                                    <span class="text-muted">Giá không khả dụng</span>
                                @endif
                            </div>

                            <button type="button"
                                class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2"
                                aria-label="Add to Cart">
                                <i class="ci-shopping-cart fs-base animate-target"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @if ($relatedProducts->isEmpty())
            <p class="text-center text-muted">Không có sản phẩm liên quan nào.</p>
        @endif
    </div>
</section>