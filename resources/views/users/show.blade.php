<!-- resources/views/users/show.blade.php -->
@extends('users.layouts.app')

@section('title', $product->name . ' - iMart')

@section('meta')
    <meta name="description" content="{{ $product->meta_description }}">
    <meta name="keywords" content="{{ $product->meta_keywords }}">
@endsection

@section('content')
    <style>
        /* Responsive cho thumbnails */
        @media (max-width: 768px) {
            .swiper-thumbs {
                width: 100% !important;
                height: auto !important;
            }

            .swiper-thumbs .swiper-slide {
                width: 60px !important;
                height: 60px !important;
                margin-right: 8px !important;
            }
        }

        .ratio-4x3 {
            --bs-aspect-ratio: 75%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 20px;
        }

        /* Điều chỉnh vị trí của nút điều hướng */
        .btn-prev,
        .btn-next {
            top: 50% !important;
            transform: translateY(-50%) !important;
        }

        /* Thêm margin-top để đẩy gallery xuống */
        .swiper {
            margin-top: 1.5rem;
        }

        /* Thêm margin-top để đẩy phần thông tin product options xuống */
        .col-md-6.col-xl-5.offset-xl-1 {
            margin-top: 100px;
        }

        /* Thêm viền khi chọn màu sắc */
        .color-swatch-option {
            border: 1px solid #8d9196;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .color-swatch-option.selected {
            border: 2px solid #0d6efd !important;
            box-shadow: 0 0 5px rgba(13, 110, 253, 0.5) !important;
        }
    </style>
    <main class="content-wrapper">
        <!-- Breadcrumb -->
        <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <!-- Page title -->
        <h1 class="h3 container mb-4">{{ $product->name }}</h1>

        <!-- Nav links + Reviews -->
        <section class="container pb-2 pb-lg-4">
            <div class="d-flex align-items-center border-bottom">
                <ul class="nav nav-underline flex-nowrap gap-4">
                    <li class="nav-item me-sm-2">
                        <a class="nav-link pe-none active" href="#!">Thông tin chung</a>
                    </li>
                    <li class="nav-item me-sm-2">
                        <a class="nav-link" href="#details">Chi tiết sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reviews">Đánh giá ({{ $product->reviews_count }})</a>
                    </li>
                </ul>
                <a class="d-none d-md-flex align-items-center gap-2 text-decoration-none ms-auto mb-1" href="#reviews">
                    <div class="d-flex gap-1 fs-sm">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="ci-star{{ $i <= $product->average_rating ? '-filled' : '' }} text-warning"></i>
                        @endfor
                    </div>
                    <span class="text-body-tertiary fs-xs">{{ $product->reviews_count }} reviews</span>
                </a>
            </div>
        </section>

        <!-- Gallery + Product options -->
        <section class="container pb-5 mb-1 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
            <div class="row align-items-start">
                <!-- Product gallery -->
                <div class="col-md-6 mb-4 mb-md-0 position-relative">
                    <!-- Preview (Large image) -->
                    <div class="swiper"
                        data-swiper='{
                            "loop": true,
                            "navigation": {
                                "prevEl": ".btn-prev",
                                "nextEl": ".btn-next"
                            },
                            "thumbs": {
                                "swiper": "#thumbs"
                            }
                        }'>
                        <div class="swiper-wrapper">
                            <!-- Hiển thị ảnh bìa trước -->
                            @if ($product->coverImage)
                                <div class="swiper-slide">
                                    <div class="ratio ratio-4x3">
                                        <img id="variant-image" src="{{ Storage::url($product->coverImage->path) }}"
                                            alt="{{ $product->name }}"
                                            style="max-height: 400px; object-fit: contain; margin: 40px 0 20px 0;">
                                    </div>
                                </div>
                            @endif
                            <!-- Hiển thị ảnh gallery -->
                            @foreach ($product->galleryImages as $image)
                                <div class="swiper-slide" style="align-items: center">
                                    <div class="ratio ratio-4x3">
                                        <img src="{{ Storage::url($image->path) }}" alt="{{ $product->name }}"
                                            style="max-height: 400px; object-fit: contain; margin: 40px 0 20px 0;">
                                    </div>
                                </div>
                            @endforeach
                            <!-- Fallback nếu không có ảnh -->
                            @if (!$product->coverImage && $product->galleryImages->isEmpty())
                                <div class="swiper-slide">
                                    <div class="ratio ratio-4x3">
                                        <img src="{{ asset('images/placeholder.jpg') }}" alt="No image available"
                                            style="max-height: 400px; object-fit: contain; margin: 40px 0 20px 0;">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Prev button -->
                        <div class="position-absolute top-50 start-0 z-2 translate-middle-y ms-sm-2 ms-lg-3">
                            <button type="button"
                                class="btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start"
                                aria-label="Prev">
                                <i class="ci-chevron-left fs-lg animate-target"></i>
                            </button>
                        </div>

                        <!-- Next button -->
                        <div class="position-absolute top-50 end-0 z-2 translate-middle-y me-sm-2 me-lg-3">
                            <button type="button"
                                class="btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end"
                                aria-label="Next">
                                <i class="ci-chevron-right fs-lg animate-target"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Thumbnails -->
                    <div class="swiper swiper-load swiper-thumbs pt-2 mt-1" id="thumbs"
                        data-swiper='{
                            "loop": true,
                            "spaceBetween": 10,
                            "slidesPerView": 4,
                            "watchSlidesProgress": true,
                            "breakpoints": {
                                "340": {
                                    "slidesPerView": 4
                                },
                                "500": {
                                    "slidesPerView": 5
                                },
                                "600": {
                                    "slidesPerView": 5
                                },
                                "768": {
                                    "slidesPerView": 4
                                },
                                "992": {
                                    "slidesPerView": 5
                                },
                                "1200": {
                                    "slidesPerView": 5
                                }
                            }
                        }'>
                        <div class="swiper-wrapper" id="variant-gallery">
                            <!-- Thumbnail cho ảnh bìa -->
                            @if ($product->coverImage)
                                <div class="swiper-slide swiper-thumb">
                                    <div class="ratio ratio-4x3" style="max-width: 80px;">
                                        <img src="{{ Storage::url($product->coverImage->path) }}" class="swiper-thumb-img"
                                            alt="{{ $product->name }}" style="object-fit: contain;">
                                    </div>
                                </div>
                            @endif
                            <!-- Thumbnail cho ảnh gallery -->
                            @foreach ($product->galleryImages as $image)
                                <div class="swiper-slide swiper-thumb">
                                    <div class="ratio ratio-4x3" style="max-width: 80px;">
                                        <img src="{{ Storage::url($image->path) }}" class="swiper-thumb-img"
                                            alt="{{ $product->name }}" style="object-fit: contain;">
                                    </div>
                                </div>
                            @endforeach
                            <!-- Fallback thumbnail nếu không có ảnh -->
                            @if (!$product->coverImage && $product->galleryImages->isEmpty())
                                <div class="swiper-slide swiper-thumb">
                                    <div class="ratio ratio-4x3" style="max-width: 80px;">
                                        <img src="{{ asset('images/placeholder.jpg') }}" class="swiper-thumb-img"
                                            alt="No image available" style="object-fit: contain;">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Product options -->
                <div class="col-md-6 col-xl-5 offset-xl-1">
                    <div class="ps-md-4 ps-xl-0 pt-md-0">
                       {{-- Tạo dữ liệu --}}
@php
    $attributesGrouped = collect();
    $variantCombinations = [];
    $variantData = [];
    $initialVariantAttributes = [];

    if ($defaultVariant) {
        foreach ($defaultVariant->attributeValues as $attrValue) {
            $initialVariantAttributes[$attrValue->attribute->name] = $attrValue->value;
        }
    }

    foreach ($product->variants as $variant) {
        $combination = [];
        foreach ($variant->attributeValues as $attrValue) {
            $attrName = $attrValue->attribute->name;
            $value = $attrValue->value;
            $combination[$attrName] = $value;

            if (!$attributesGrouped->has($attrName)) {
                $attributesGrouped[$attrName] = collect();
            }

            if (!$attributesGrouped[$attrName]->contains('value', $attrValue->value)) {
                $attributesGrouped[$attrName]->push($attrValue);
            }
        }

        $variantCombinations[] = $combination;

        $key = collect($combination)->implode('_');
        $now = now();
        $salePrice = $variant->sale_price;
        $isOnSale = $salePrice !== null &&
                    $variant->sale_price_starts_at <= $now &&
                    $variant->sale_price_ends_at >= $now;

        $variantData[$key] = [
            'price' => (int) ($isOnSale ? $salePrice : $variant->price),
            'original_price' => (int) $variant->price,
            'status' => $variant->status,
            'image' => $variant->image_url ?? null,
        ];
    }
@endphp

{{-- Hiển thị các lựa chọn thuộc tính --}}
@foreach ($attributesGrouped as $attrName => $attrValues)
    <div class="mb-4">
        <label class="form-label fw-semibold d-block mb-2">
            {{ $attrName }}
            @if (strtolower($attrName) === 'màu sắc')
                : <span id="selected-color-name" class="fw-normal"></span>
            @endif
        </label>
        <div class="d-flex flex-wrap gap-2">
            @foreach ($attrValues as $attrValue)
                @php
                    $inputName = strtolower(str_replace(' ', '-', $attrName)) . '-options';
                    $inputId = $inputName . '-' . $attrValue->id;
                    $isColor = $attrValue->attribute->display_type === 'color_swatch' && $attrValue->meta;
                    $isChecked = isset($initialVariantAttributes[$attrName]) && $initialVariantAttributes[$attrName] === $attrValue->value;
                @endphp

                <div class="option-container" data-attr-name="{{ $attrName }}" data-attr-value="{{ $attrValue->value }}">
                    <input type="radio" class="btn-check" name="{{ $inputName }}"
                        id="{{ $inputId }}" value="{{ $attrValue->value }}"
                        data-attr-name="{{ $attrName }}" {{ $isChecked ? 'checked' : '' }}>

                    @if ($isColor)
                        <label for="{{ $inputId }}" class="color-swatch-option rounded"
                            title="{{ $attrValue->value }}"
                            style="width: 36px; height: 36px; display: inline-block;
                            background-color: {{ $attrValue->meta }};
                            cursor: pointer; transition: transform 0.2s;">
                        </label>
                    @else
                        <label for="{{ $inputId }}"
                            class="btn btn-outline-secondary btn-sm text-nowrap">
                            {{ $attrValue->value }}
                        </label>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endforeach


                        <!-- Price -->
                        <div class="d-flex flex-wrap align-items-center mb-3">
                            @php
                                $variant = $defaultVariant ?? $product->variants->first();
                                $now = now();
                                $salePrice = (int) $variant->sale_price;
                                $originalPrice = (int) $variant->price;
                                $isOnSale =
                                    $variant->sale_price !== null &&
                                    $variant->sale_price_starts_at <= $now &&
                                    $variant->sale_price_ends_at >= $now;
                                $displayPrice = $isOnSale ? $salePrice : $originalPrice;
                            @endphp

                            <div class="d-flex align-items-center mb-3 flex-wrap">
                                <div class="d-flex align-items-baseline me-3">
                                    <div class="h4 mb-0 text-danger" id="product-price">
                                        {{ number_format($displayPrice) }}đ
                                    </div>
                                    <div class="ms-2 text-muted text-decoration-line-through" id="original-price"
                                        style="{{ $isOnSale && $originalPrice > $salePrice ? '' : 'display: none;' }}">
                                        {{ $isOnSale && $originalPrice > $salePrice ? number_format($originalPrice) . 'đ' : '' }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center text-success fs-sm ms-auto">
                                    <i class="ci-check-circle fs-base me-2"></i>
                                    <span id="variant-status">{{ $variant->status }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Count + Buttons -->
                        <div
                            class="d-flex flex-wrap flex-sm-nowrap flex-md-wrap flex-lg-nowrap gap-3 gap-lg-2 gap-xl-3 mb-4">
                            <div class="count-input flex-shrink-0 order-sm-1">
                                <button type="button" class="btn btn-icon btn-lg" data-decrement=""
                                    aria-label="Decrement quantity">
                                    <i class="ci-minus"></i>
                                </button>
                                <input type="number" class="form-control form-control-lg" value="1" min="1"
                                    max="5" readonly>
                                <button type="button" class="btn btn-icon btn-lg" data-increment=""
                                    aria-label="Increment quantity">
                                    <i class="ci-plus"></i>
                                </button>
                            </div>
                            <button type="button"
                                class="btn btn-icon btn-lg btn-secondary animate-pulse order-sm-3 order-md-2 order-lg-3"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add to Wishlist">
                                <i class="ci-heart fs-lg animate-target"></i>
                            </button>
                            <button type="button"
                                class="btn btn-icon btn-lg btn-secondary animate-rotate order-sm-4 order-md-3 order-lg-4"
                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Compare">
                                <i class="ci-refresh-cw fs-lg animate-target"></i>
                            </button>
                            <button type="button"
                                class="btn btn-lg btn-primary w-100 animate-slide-end order-sm-2 order-md-4 order-lg-2">
                                <i class="ci-shopping-cart fs-lg animate-target ms-n1 me-2"></i>
                                Thêm vào giỏ hàng
                            </button>
                        </div>

                        <!-- Accordion -->
                        {{-- accordion giữ nguyên như bạn cũ --}}

                        <!-- Truyền dữ liệu biến thể xuống JS -->
                        <script>
                            const variantData = @json($variantData);
                            const availableCombinations = @json($variantCombinations);
                            const attributes = @json($attributesGrouped->map(fn($values) => $values->pluck('value')));
                            const initialVariantAttributes = @json($initialVariantAttributes);
                        </script>
                    </div>
                </div>

            </div>
        </section>

        <!-- Product details and Reviews shared container -->
        <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
            <div class="row">
                <div class="col-md-7">
                    <!-- Product details -->
                    <h2 class="h3 pb-2 pb-md-3" id="details">Chi tiết sản phẩm</h2>
                    <h3 class="h6">Thông số kĩ thuật chung</h3>
                    <ul class="list-unstyled d-flex flex-column gap-3 fs-sm pb-3 m-0 mb-2 mb-sm-3">
                        <li class="d-flex align-items-center position-relative pe-4">
                            <span>Danh mục:</span>
                            <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                            <span class="text-dark-emphasis fw-medium text-end">
                                {{ $product->category->name ?? 'Chưa xác định' }}
                            </span>
                        </li>
                        <li class="d-flex align-items-center position-relative pe-4">
                            <span>Trạng thái:</span>
                            <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                            <span class="text-dark-emphasis fw-medium text-end">
                                {{ $product->status ?? 'Chưa xác định' }}
                            </span>
                        </li>

                        @php
                            $specAttributes = [
                                'Màu sắc',
                                'Dung lượng',
                                'RAM',
                                'Kích thước màn hình',
                                'Chất liệu vỏ',
                                'Bộ nhớ',
                            ];
                        @endphp

                        @foreach ($specAttributes as $attrName)
                            <li class="d-flex align-items-center position-relative pe-4">
                                <span>{{ $attrName }}:</span>
                                <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                                <span class="text-dark-emphasis fw-medium text-end">
                                    @if (isset($attributes[$attrName]) && $attributes[$attrName]->isNotEmpty())
                                        {{ $attributes[$attrName]->pluck('value')->join(', ') }}
                                    @else
                                        Chưa xác định
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Description -->
                    <div class="pb-3">
                        <h3 class="h6">Mô tả sản phẩm</h3>
                        <div class="fs-sm">
                            {!! $product->description !!}
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div class="d-flex align-items-center pt-5 mb-4 mt-2 mt-md-3 mt-lg-4" id="reviews"
                        style="scroll-margin-top: 80px">
                        <h2 class="h3 mb-0">Đánh giá</h2>
                        <button type="button" class="btn btn-secondary ms-auto" data-bs-toggle="modal"
                            data-bs-target="#reviewForm">
                            <i class="ci-edit-3 fs-base ms-n1 me-2"></i>
                            Để lại đánh giá
                        </button>
                    </div>

                    <!-- Reviews stats -->
                    <div class="row g-4 pb-3">
                        <div class="col-sm-4">
                            <div
                                class="d-flex flex-column align-items-center justify-content-center h-100 bg-body-tertiary rounded p-4">
                                <div class="h1 pb-2 mb-1">{{ $product->average_rating }}</div>
                                <div class="hstack justify-content-center gap-1 fs-sm mb-2">
                                    @php
                                        $fullStars = floor($product->average_rating);
                                        $halfStar = $product->average_rating - $fullStars >= 0.5;
                                    @endphp

                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $fullStars)
                                            <i class="ci-star-filled text-warning"></i>
                                        @elseif ($i == $fullStars + 1 && $halfStar)
                                            <i class="ci-star-half-filled text-warning"></i>
                                        @else
                                            <i class="ci-star text-body-tertiary"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="fs-sm">{{ $totalReviews }} reviews</div>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="vstack gap-3">
                                @foreach (range(5, 1) as $star)
                                    <div class="hstack gap-2">
                                        <div class="hstack fs-sm gap-1">
                                            {{ $star }}<i class="ci-star-filled text-warning"></i>
                                        </div>
                                        <div class="progress w-100" role="progressbar"
                                            aria-label="{{ $star }} stars" style="height: 4px">
                                            <div class="progress-bar bg-warning rounded-pill"
                                                style="width: {{ $ratingPercentages[$star] ?? 0 }}%"></div>
                                        </div>
                                        <div class="fs-sm text-nowrap text-end" style="width: 40px;">
                                            {{ $ratingCounts[$star] ?? 0 }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Reviews list -->
                    @foreach ($product->reviews as $review)
                        <div class="border-bottom py-3 mb-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-nowrap me-3">
                                    <span class="h6 mb-0">{{ $review->user->name ?? 'Người dùng' }}</span>
                                    <i class="ci-check-circle text-success align-middle ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-custom-class="tooltip-sm"
                                        data-bs-title="Verified customer"></i>
                                </div>
                                <span
                                    class="text-body-secondary fs-sm ms-auto">{{ $review->created_at->format('F d, Y') }}</span>
                            </div>
                            <div class="d-flex gap-1 fs-sm pb-2 mb-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="ci-star{{ $i <= $review->rating ? '-filled' : '' }} text-warning"></i>
                                @endfor
                            </div>
                            <p class="fs-sm">{{ $review->comment }}</p>
                            <div class="nav align-items-center">
                                <button type="button" class="nav-link animate-underline px-0">
                                    <i class="ci-corner-down-right fs-base ms-1 me-1"></i>
                                    <span class="animate-target">Trả lời</span>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    @if ($product->reviews->isEmpty())
                        <p class="text-center text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
                    @endif
                    <!-- Review -->
                   @include('users.review')
                    <!-- Review -->
                </div>
            </div>
        </section>
        <!-- Related Products -->
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
                                    $onSale =
                                        $variant &&
                                        $variant->sale_price &&
                                        $variant->sale_price_starts_at &&
                                        $variant->sale_price_ends_at &&
                                        $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
                                    $price = $onSale ? $variant->sale_price : $variant->price;
                                    $originalPrice = $onSale ? $variant->price : null;
                                    $discountPercent =
                                        $onSale && $variant->price > 0
                                            ? round((($variant->price - $variant->sale_price) / $variant->price) * 100)
                                            : 0;
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

        <section style="margin: 80px 0;">
        </section>

        <div>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const priceElement = document.querySelector('#product-price');
        const originalPriceElement = document.querySelector('#original-price');
        const variantImage = document.getElementById('variant-image');
        const selectedColorName = document.getElementById('selected-color-name');

        const availableCombinations = @json($variantCombinations);
        const variantData = @json($variantData);
        const attributes = @json($attributesGrouped->map(fn($values) => $values->pluck('value')));
        const attributeOrder = Object.keys(attributes);
        let currentSelections = @json($initialVariantAttributes);

        function updateAvailableOptions() {
            let newlyAvailableOptions = {};

            attributeOrder.forEach((attrName, attrIndex) => {
                newlyAvailableOptions[attrName] = new Set();

                availableCombinations.forEach(combination => {
                    let isMatch = true;
                    for (let i = 0; i < attrIndex; i++) {
                        let prevAttr = attributeOrder[i];
                        if (currentSelections[prevAttr] !== combination[prevAttr]) {
                            isMatch = false;
                            break;
                        }
                    }
                    if (isMatch && combination[attrName]) {
                        newlyAvailableOptions[attrName].add(combination[attrName]);
                    }
                });

                document.querySelectorAll(`.option-container[data-attr-name="${attrName}"]`).forEach(container => {
                    const value = container.getAttribute('data-attr-value');
                    const input = container.querySelector('input[type="radio"]');
                    if (newlyAvailableOptions[attrName].has(value)) {
                        container.style.display = 'inline-block';
                    } else {
                        container.style.display = 'none';
                        if (input && input.checked) input.checked = false;
                    }
                });

                const valuesSet = newlyAvailableOptions[attrName];
                const currentSelectedValue = currentSelections[attrName];
                if (!valuesSet.has(currentSelectedValue)) {
                    const firstValue = Array.from(valuesSet)[0];
                    currentSelections[attrName] = firstValue || null;
                }

                if (currentSelections[attrName]) {
                    const radio = document.querySelector(
                        `input[data-attr-name="${attrName}"][value="${currentSelections[attrName]}"]`
                    );
                    if (radio) radio.checked = true;
                }
            });

            if (selectedColorName && currentSelections['Màu sắc']) {
                selectedColorName.textContent = currentSelections['Màu sắc'];
            } else if (selectedColorName) {
                selectedColorName.textContent = 'N/A';
            }

            updateSelectedColorClass();
        }

        function updateSelectedColorClass() {
            document.querySelectorAll('.color-swatch-option').forEach(label => {
                label.classList.remove('selected');
            });
            const checkedColorInput = document.querySelector('input[data-attr-name="Màu sắc"]:checked');
            if (checkedColorInput) {
                const label = document.querySelector(`label[for="${checkedColorInput.id}"]`);
                if (label) label.classList.add('selected');
            }
        }

        function updateVariantInfo() {
            const variantKey = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
            const variant = variantData[variantKey];

            console.log('🟡 Variant Key:', variantKey);
            console.log('🟢 Variant Data:', variant);

            if (variant) {
                if (priceElement) {
                    priceElement.textContent = new Intl.NumberFormat('vi-VN').format(variant.price) + 'đ';
                }
                if (originalPriceElement) {
                    if (variant.original_price > variant.price) {
                        originalPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(variant.original_price) + 'đ';
                        originalPriceElement.style.display = '';
                    } else {
                        originalPriceElement.style.display = 'none';
                    }
                }

                const statusElement = document.getElementById('variant-status');
                if (statusElement) {
                    statusElement.textContent = variant.status;
                }
            }
        }

        function updateMainImageFromSelection() {
            const variantKey = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
            const variant = variantData[variantKey];

            console.log('🔵 Main Image Variant Key:', variantKey);
            console.log('🔴 Variant Image:', variant?.image);

            if (variant && variant.image && variant.image !== '') {
                updateMainImage(variant.image);
            }
        }

        function updateMainImage(src) {
            const variantImage = document.getElementById('variant-image');
            if (variantImage && src) {
                variantImage.src = src;
            }

            const mainSwiperEl = document.querySelector('.swiper');
            if (mainSwiperEl && mainSwiperEl.swiper) {
                const mainSwiper = mainSwiperEl.swiper;
                const slideIndex = Array.from(mainSwiper.slides).findIndex(slide => {
                    const img = slide.querySelector('img');
                    return img && img.src.includes(src);
                });
                if (slideIndex !== -1) {
                    mainSwiper.slideTo(slideIndex);
                }
            }
        }

        document.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', function() {
                const attrName = this.dataset.attrName;
                const attrValue = this.value;
                console.log('🧩 Changed', attrName + ':', attrValue);

                currentSelections[attrName] = attrValue;

                updateAvailableOptions();
                updateVariantInfo();
                updateMainImageFromSelection();
            });
        });

        updateAvailableOptions();
        updateVariantInfo();
        updateMainImageFromSelection();
    });
</script>
@endpush
