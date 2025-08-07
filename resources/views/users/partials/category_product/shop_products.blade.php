<div id="ajax-products-list">
    @php
        $sortOptions = [
            'noi_bat' => 'Nổi bật',
            'moi_nhat' => 'Mới nhất',
            'gia_thap_den_cao' => 'Giá tăng dần',
            'gia_cao_den_thap' => 'Giá giảm dần',
        ];
        $currentSort = request('sort', 'tat_ca');
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
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-2">
        @forelse ($products as $product)
            @php
                $variant = $product->variants->first();
                $displayVariant = $product->variants->firstWhere('is_default', true) ?? $variant;
                $imageToShow = $displayVariant?->primaryImage ?? $product->coverImage;
                $mainImage = $imageToShow ? Storage::url($imageToShow->path) : asset('images/placeholder.jpg');

                $onSale =
                    $displayVariant &&
                    $displayVariant->sale_price &&
                    $displayVariant->sale_price < $displayVariant->price;

                if ($onSale) {
                    $displayVariant->discount_percent = round(
                        100 - ($displayVariant->sale_price / $displayVariant->price) * 100,
                    );
                } else {
                    $displayVariant->discount_percent = 0;
                }
            @endphp

            <div class="col">
                <div class="product-card animate-underline hover-effect-opacity bg-body rounded-4 shadow-lg border-0">
                    <div class="position-relative">
                        @if ($onSale && $displayVariant->discount_percent > 0)
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
                                href="{{ route('users.products.show', $product->slug) }}" style="margin-top: 10px;">
                                @php
                                    $storage = $displayVariant?->attributeValues->firstWhere(
                                        'attribute.name',
                                        'Dung lượng',
                                    )?->value;
                                @endphp

                                {{ $product->name }}{{ $storage ? ' ' . $storage : '' }}
                            </a>
                        </h3>

                        <div class="lh-1 mb-0" style="line-height: 1.2; text-align: center;">
                            @if ($displayVariant && $displayVariant->price)
                                @if ($onSale && $displayVariant->discount_percent > 0)
                                    <span class="text-primary fw-semibold fs-base" style="color: #0d6efd !important;">
                                        {{ number_format($displayVariant->sale_price) }}đ
                                    </span>
                                    <del class="text-muted fs-sm ms-2">
                                        {{ number_format($displayVariant->price) }}đ
                                    </del>
                                @else
                                    <span
                                        class="fw-semibold fs-base">{{ number_format($displayVariant->price) }}đ</span>
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
    /* --- Sắp xếp theo (Sort Filter) --- */
    .sort-section {
        background-color: #fcfcfc;
        /* Consistent with sidebar background (very light off-white) */
        border-radius: 12px;
        /* Consistent with sidebar rounded corners */
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        /* Consistent with sidebar shadow */
        border: 1px solid #e9ecef;
        /* Subtle border for definition (light grey) */
        padding: 15px 25px;
        /* Adjust padding for better look, similar to sidebar filter sections */
        display: flex;
        /* Use flexbox for alignment of label and options */
        align-items: center;
        /* Vertically center items */
        gap: 20px;
        /* Space between label and sort options */
    }

    .sort-section .sort-label {
        font-size: 1.1rem;
        /* Clearer heading for filters */
        font-weight: 600;
        color: #343a40;
        /* Consistent with filter section headings (dark grey) */
        display: flex;
        align-items: center;
        gap: 8px;
        /* Space between icon and text */
        white-space: nowrap;
        /* Prevent label from wrapping */
    }

    .sort-section .sort-label i {
        font-size: 1.3rem;
        /* Consistent with filter icons */
        color: #dc3545;
        /* Consistent with price filter icon (danger red) */
    }

    .sort-section .sort-options {
        display: flex;
        /* Use flex for the nav links */
        flex-wrap: wrap;
        /* Allow options to wrap if space is limited */
        gap: 8px;
        /* Space between sort options */
        margin-bottom: 0;
        /* Remove default nav margin */
    }

    .sort-section .nav-link {
        padding: 10px 18px;
        /* More balanced padding for links */
        color: #495057;
        /* Consistent dark grey text */
        text-decoration: none;
        transition: background-color 0.25s ease-out, color 0.25s ease-out, box-shadow 0.25s ease-out;
        border-radius: 8px;
        /* Consistent with sidebar category links */
        font-size: 1.02rem;
        /* Slightly larger text */
        font-weight: 500;
        /* Medium weight for good readability */
        border: 1px solid #dee2e6;
        /* Subtle border for each option (light grey) */
        background-color: #ffffff;
        /* White background for inactive options */
    }

    .sort-section .nav-link:hover {
        background-color: #f7f9fb;
        /* Very light grey-blue on hover (from original sidebar child-category hover) */
        color: #212529;
        /* Darker text on hover */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        /* Subtle shadow on hover (reduced intensity, neutral color) */
        border-color: #d1d9e6;
        /* Slightly darker grey border on hover */
    }

    .sort-section .nav-link.active {
        background-color: #dc3545;
        /* **Primary red for active sort option (from your 'danger' color)** */
        color: #ffffff !important;
        /* White text for active sort option */
        font-weight: 600;
        /* Bolder active */
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25);
        /* More prominent shadow for active (red based) */
        transform: translateY(-1px);
        /* Slight lift effect */
        border-color: #dc3545;
        /* Solid red border for active state */
    }

    /* Responsive adjustments (optional, but good practice) */
    @media (max-width: 768px) {
        .sort-section {
            flex-direction: column;
            /* Stack label and options on smaller screens */
            align-items: flex-start;
            /* Align items to the start when stacked */
            gap: 10px;
            /* Reduce gap when stacked */
            padding: 15px 20px;
            /* Slightly adjust padding for smaller screens */
        }

        .sort-section .sort-options {
            width: 100%;
            /* Make options take full width */
            justify-content: center;
            /* Center options horizontally */
        }

        .sort-section .nav-link {
            flex-grow: 1;
            /* Allow links to grow to fill space */
            text-align: center;
            /* Center text within links */
        }
    }

    /* Keep your existing product card styles and discount badge styles as they are separate concerns */
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
