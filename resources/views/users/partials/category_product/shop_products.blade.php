<div id="ajax-products-list">
    @php
        $sortOptions = [
            'noi_bat' => 'Nổi Bật',
            'moi_nhat' => 'Mới Nhất',
        ];
        $currentSort = request('sort', 'tat_ca');
        $isPriceSort = in_array($currentSort, ['gia_thap_den_cao', 'gia_cao_den_thap']);
    @endphp

    <div class="sort-section mb-4 shadow-sm">
        <span class="sort-label"><i class="ci-filter me-2 text-danger"></i>Sắp xếp theo</span>
        <nav class="sort-options nav">
            @foreach ($sortOptions as $key => $label)
                <a class="nav-link {{ $currentSort === $key ? 'active' : '' }} px-3 py-2 shadow-none"
                    href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="dropdown ms-2">
            <a class="nav-link dropdown-toggle {{ $isPriceSort ? 'active' : '' }} px-3 py-2 shadow-none" href="#"
                role="button" id="priceSortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ci-dollar me-1"></i> Giá
            </a>
            <ul class="dropdown-menu" aria-labelledby="priceSortDropdown">
                <li><a class="dropdown-item {{ $currentSort === 'gia_thap_den_cao' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['sort' => 'gia_thap_den_cao']) }}">Giá: Thấp đến Cao</a>
                </li>
                <li><a class="dropdown-item {{ $currentSort === 'gia_cao_den_thap' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['sort' => 'gia_cao_den_thap']) }}">Giá: Cao đến Thấp</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-2">
        @forelse ($products as $product)
            @php
                $variant = $product->variants->first();
                $displayVariant = $product->variants->firstWhere('is_default', true) ?? $variant;
                $imageToShow = $displayVariant?->primaryImage ?? $product->coverImage;
                $mainImage = $imageToShow ? Storage::url($imageToShow->path) : asset('images/placeholder.jpg');

                $onSale = $displayVariant && $displayVariant->sale_price && $displayVariant->sale_price < $displayVariant->price;

                if ($onSale) {
                    $displayVariant->discount_percent = round(100 - ($displayVariant->sale_price / $displayVariant->price) * 100);
                } else {
                    $displayVariant->discount_percent = 0;
                }
            @endphp

            <div class="col">
                <div class="product-card animate-underline hover-effect-opacity bg-body rounded-4 shadow-lg border-0">
                    <div class="position-relative">
                        @if (
                            $onSale &&
                            $displayVariant->discount_percent > 0 &&
                            !in_array($currentSort, ['gia_thap_den_cao', 'gia_cao_den_thap']))
                            <div class="discount-badge">
                                Giảm {{ $displayVariant->discount_percent }}%
                            </div>
                        @endif
                        <a class="d-block rounded-top overflow-hidden bg-white bg-opacity-75 position-relative"
                            style="backdrop-filter: blur(4px); padding-bottom: 0px;"
                            href="{{ route('users.products.show', $product->slug) }}">
                            <div class="ratio" style="--cz-aspect-ratio: calc(250 / 220 * 100%)">
                                <img src="{{ $mainImage }}" alt="{{ $product->name }}" loading="lazy"
                                    class="img-fluid rounded-3 shadow-sm"
                                    style="object-fit:contain; width:100%; height:100%; background:#fff;">
                            </div>
                        </a>
                    </div>

                    <div class="w-100 min-w-0 px-2 pb-3 pt-2 px-sm-3 pb-sm-3 d-flex flex-column justify-content-between"
                        style="min-height: 100px;">
                        <h3 class="pb-2 mb-3 text-center">
                            <a class="d-block fs-base fw-semibold text-truncate mb-2 no-underline-link"
                                href="{{ route('users.products.show', $product->slug) }}" 
                                style="margin-top: 10px;">
                                @php
                                    $storage = $displayVariant?->attributeValues->firstWhere(
                                        'attribute.name',
                                        'Dung lượng lưu trữ',
                                    )?->value;
                                @endphp

                                {{ $product->name }}{{ $storage ? ' ' . $storage : '' }}
                            </a>
                        </h3>

                        <div class="lh-1 mb-0" style="line-height: 1.2; text-align: center;">
                            @if ($displayVariant && $displayVariant->price)
                                @if (
                                    $onSale &&
                                    $displayVariant->discount_percent > 0 &&
                                    !in_array($currentSort, ['gia_thap_den_cao', 'gia_cao_den_thap']))
                                    <span class="text-primary fw-semibold fs-base" style="color: #0d6efd !important;">
                                        {{ number_format($displayVariant->sale_price) }}đ
                                    </span>
                                    <del class="text-muted fs-sm ms-2">
                                        {{ number_format($displayVariant->price) }}đ
                                    </del>
                                @else
                                    <span class="fw-semibold fs-base">{{ number_format($displayVariant->price) }}đ</span>
                                @endif
                            @else
                                <span class="text-muted">Giá không khả dụng</span>
                            @endif
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


<style>
.discount-badge {
    position: absolute;
    top: 0;
    left: 0;
    background: #e30613;
    color: #fff;
    font-weight: bold;
    padding: 8px 28px 8px 16px;
    border-radius: 0 32px 32px 0;
    font-size: 0.8rem;
    box-shadow: none;
    z-index: 10;
    min-width: 0;
    text-align: left;
    line-height: 1.1;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
}
.product-card {
    background: rgba(250, 251, 253, 0.95);
    border-radius: 0 !important;
    box-shadow: 0 8px 32px #bfc9d133;
    transition: transform 0.25s, box-shadow 0.25s, border 0.25s;
    position: relative;
    border: 1.5px solid #e5e9f2;
    overflow: hidden;
}
.product-card:hover {
    transform: translateY(-7px) scale(1.04);
    box-shadow: 0 16px 40px #bfc9d133, 0 0 16px #e5e9f299;
    border: 1.5px solid #bfc9d1;
    border-radius: 0 !important;
}
.product-card .badge-sale {
    border-radius: 0 !important;
}
.product-card .ratio {
    --cz-aspect-ratio: calc(200 / 260 * 100%);
}
</style>