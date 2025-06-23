<div id="ajax-products-list">
    @php
        $sortOptions = [
            'tat_ca' => 'Tất Cả',
            'pho_bien' => 'Phổ Biến',
            'moi_nhat' => 'Mới Nhất',
            'ban_chay' => 'Bán Chạy',
        ];
        $currentSort = request('sort', 'tat_ca');
        $isPriceSort = in_array($currentSort, ['gia_thap_den_cao', 'gia_cao_den_thap']);
    @endphp

    <div class="sort-section">
        <span class="sort-label">Sắp xếp theo</span>
        <nav class="sort-options nav">
            @foreach ($sortOptions as $key => $label)
                <a class="nav-link {{ $currentSort === $key ? 'active' : '' }}"
                    href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="dropdown">
            <a class="nav-link dropdown-toggle {{ $isPriceSort ? 'active' : '' }}" href="#"
                role="button" id="priceSortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Giá
            </a>
            <ul class="dropdown-menu" aria-labelledby="priceSortDropdown">
                <li><a class="dropdown-item {{ $currentSort === 'gia_thap_den_cao' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['sort' => 'gia_thap_den_cao']) }}">Giá: Thấp
                        đến Cao</a></li>
                <li><a class="dropdown-item {{ $currentSort === 'gia_cao_den_thap' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['sort' => 'gia_cao_den_thap']) }}">Giá: Cao
                        đến Thấp</a></li>
            </ul>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-2">
        @forelse ($products as $product)
            <div class="col">
                <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
                    <div class="position-relative">
                        @php
                            $variant = $product->variants->first();
                            $now = now();
                            $onSale = false;
                            $price = null;
                            $originalPrice = null;

                            if ($variant && $variant->price !== null) {
                                $onSale =
                                    $variant->sale_price &&
                                    $variant->sale_price_starts_at &&
                                    $variant->sale_price_ends_at &&
                                    $now->between(
                                        $variant->sale_price_starts_at,
                                        $variant->sale_price_ends_at,
                                    );
                                $price = $onSale ? $variant->sale_price : $variant->price;
                                $originalPrice = $onSale ? $variant->price : null;
                            }
                        @endphp

                        @if (
                            $onSale &&
                                $variant->discount_percent > 0 &&
                                request('sort') !== 'giá_thấp_đến_cao' &&
                                request('sort') !== 'giá_cao_đến_thấp')
                            <div class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 rounded-bottom-end"
                                style="z-index: 10; font-weight: 600; font-size: 0.85rem; min-width: 105px; text-align: center;">
                                Giảm {{ $variant->discount_percent }}%
                            </div>
                        @endif

                        <a class="d-block rounded-top overflow-hidden p-3 p-sm-4"
                            href="{{ route('users.products.show', $product->slug) }}">
                            <div class="ratio" style="--cz-aspect-ratio: calc(200 / 220 * 100%)">
                                <img src="{{ $product->coverImage ? asset('storage/' . $product->coverImage->path) : asset('assets/users/img/shop/electronics/thumbs/placeholder.png') }}"
                                    alt="{{ $product->name }}" loading="lazy">
                            </div>
                        </a>
                    </div>

                    <div class="w-100 min-w-0 px-1 pb-2 px-sm-3 pb-sm-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="d-flex gap-1 fs-xs">
                                @php
                                    $rating = $product->average_rating ?? 0;
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
                                class="text-body-tertiary fs-xs">({{ $product->approved_reviews_count ?? 0 }})</span>
                        </div>

                        <h3 class="pb-1 mb-2">
                            <a class="d-block fs-sm fw-medium text-truncate"
                                href="{{ route('users.products.show', $product->slug) }}">
                                <span class="animate-target">{{ $product->name }}</span>
                            </a>
                        </h3>

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="h5 lh-1 mb-0">
                                @if ($variant && $variant->price)
                                    @if (
                                        $onSale &&
                                            $variant->discount_percent > 0 &&
                                            request('sort') !== 'giá_thấp_đến_cao' &&
                                            request('sort') !== 'giá_cao_đến_thấp')
                                        <span
                                            class="text-danger">{{ number_format($variant->sale_price) }}đ</span>
                                        <del
                                            class="text-muted fs-sm ms-2">{{ number_format($variant->price) }}đ</del>
                                    @else
                                        {{ number_format($variant->price) }}đ
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
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">Không tìm thấy sản phẩm nào.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->withQueryString()->links() }}
    </div>
</div> 