@extends('users.layouts.app')

@section('title', 'Trang chủ - iMart')

@section('content')
    <!-- Hero slider -->
    <section class="w-100">
        <div class="position-relative">
            <span class="position-absolute top-0 start-0 w-100 h-100 rounded-0 d-none-dark rtl-flip"
                style="background: linear-gradient(90deg, #accbee 0%, #e7f0fd 100%)"></span>
            <span class="position-absolute top-0 start-0 w-100 h-100 rounded-0 d-none d-block-dark rtl-flip"
                style="background: linear-gradient(90deg, #1b273a 0%, #1f2632 100%)"></span>
            <div class="row justify-content-center position-relative z-2 mx-0">
                <div class="col-xl-5 col-xxl-4 d-flex align-items-center mt-xl-n3">

                    <!-- Text content master slider -->
                    <div class="swiper px-5 pe-xl-0 ps-xxl-0 me-xl-n5"
                        data-swiper='{
                "spaceBetween": 64,
                "loop": true,
                "speed": 400,
                "controlSlider": "#sliderImages",
                "autoplay": {
                  "delay": 5500,
                  "disableOnInteraction": false
                },
                "scrollbar": {
                  "el": ".swiper-scrollbar"
                }
              }'>
                        <div class="swiper-wrapper">
                            <div class="swiper-slide text-center text-xl-start pt-5 py-xl-5">
                                <p class="text-body">Feel the real quality sound</p>
                                <h2 class="display-4 pb-2 pb-xl-4">Headphones ProMax</h2>
                                <a class="btn btn-lg btn-primary" href="shop-product-general-electronics.html">
                                    Shop now
                                    <i class="ci-arrow-up-right fs-lg ms-2 me-n1"></i>
                                </a>
                            </div>
                            <div class="swiper-slide text-center text-xl-start pt-5 py-xl-5">
                                <p class="text-body">Deal of the week</p>
                                <h2 class="display-4 pb-2 pb-xl-4">Powerful iPad Pro M2</h2>
                                <a class="btn btn-lg btn-primary" href="shop-product-general-electronics.html">
                                    Shop now
                                    <i class="ci-arrow-up-right fs-lg ms-2 me-n1"></i>
                                </a>
                            </div>
                            <div class="swiper-slide text-center text-xl-start pt-5 py-xl-5">
                                <p class="text-body">Virtual reality glasses</p>
                                <h2 class="display-4 pb-2 pb-xl-4">Experience New Reality</h2>
                                <a class="btn btn-lg btn-primary" href="shop-catalog-electronics.html">
                                    Shop now
                                    <i class="ci-arrow-up-right fs-lg ms-2 me-n1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-9 col-sm-7 col-md-6 col-lg-5 col-xl-7">

                    <!-- Binded images (controlled slider) -->
                    <div class="swiper user-select-none" id="sliderImages"
                        data-swiper='{
                "allowTouchMove": false,
                "loop": true,
                "effect": "fade",
                "fadeEffect": {
                  "crossFade": true
                }
              }'>
                        <div class="swiper-wrapper">
                            <div class="swiper-slide d-flex justify-content-end">
                                <div class="ratio rtl-flip"
                                    style="max-width: 495px; --cz-aspect-ratio: calc(537 / 495 * 100%)">
                                    <img src="{{ asset('assets/users/img/home/electronics/hero-slider/01.png') }}"
                                        alt="Image">
                                </div>
                            </div>
                            <div class="swiper-slide d-flex justify-content-end">
                                <div class="ratio rtl-flip"
                                    style="max-width: 495px; --cz-aspect-ratio: calc(537 / 495 * 100%)">
                                    <img src="{{ asset('assets/users/img/home/electronics/hero-slider/02.png') }}"
                                        alt="Image">
                                </div>
                            </div>
                            <div class="swiper-slide d-flex justify-content-end">
                                <div class="ratio rtl-flip"
                                    style="max-width: 495px; --cz-aspect-ratio: calc(537 / 495 * 100%)">
                                    <img src="{{ asset('assets/users/img/home/electronics/hero-slider/03.png') }}"
                                        alt="Image">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scrollbar -->
            <div class="row justify-content-center mx-0" data-bs-theme="dark">
                <div class="col-xxl-10">
                    <div class="position-relative mx-5 mx-xxl-0">
                        <div class="swiper-scrollbar mb-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SẢN PHẨM NỔI BẬT -->
    <section class="container px-4 pt-5 mt-1 mt-sm-2 mt-md-3 mt-lg-4">
        <h2 class="h3 pb-2 pb-sm-3">Sản phẩm nổi bật</h2>
        <div class="d-none">
            Total featured products: {{ $featuredProducts->count() }}
            First 4 products: {{ $featuredProducts->take(4)->count() }}
            Last 4 products: {{ $featuredProducts->skip(4)->take(4)->count() }}
        </div>
        <div class="row">

            <!-- Banner -->
            <div class="col-lg-4" data-bs-theme="dark">
                <div class="d-flex flex-column align-items-center justify-content-end h-100 text-center overflow-hidden rounded-5 px-4 px-lg-3 pt-4 pb-5"
                    style="background: #1d2c41 url({{ asset('assets/users/img/home/electronics/banner/background.jpg') }}) center/cover no-repeat">
                    <div class="ratio animate-up-down position-relative z-2 me-lg-4"
                        style="max-width: 320px; margin-bottom: -19%; --cz-aspect-ratio: calc(690 / 640 * 100%)">
                        <img src="{{ asset('assets/users/img/home/electronics/banner/laptop.png') }}" alt="Laptop"
                            loading="lazy">
                    </div>
                    <h3 class="display-2 mb-2">MacBook</h3>
                    <p class="text-body fw-medium mb-4">Be Pro Anywhere</p>
                    <a class="btn btn-sm btn-primary" href="#!">
                        From $1,199
                        <i class="ci-arrow-up-right fs-base ms-1 me-n1"></i>
                    </a>
                </div>
            </div>

            <!-- Product list (First 4 products) -->
            <div class="col-sm-6 col-lg-4 d-flex flex-column gap-3 pt-4 py-lg-4">
                @foreach ($featuredProducts->take(4) as $product)
                    @php
                        $variant = $product->variants->first();
                        $now = now();
                        $isOnSale = false;
                        
                        // Debug raw data
                        echo "<!-- Debug for {$product->name}:\n";
                        echo "Variant exists: " . ($variant ? 'Yes' : 'No') . "\n";
                        if ($variant) {
                            echo "Raw data:\n";
                            echo "- Price: " . $variant->price . "\n";
                            echo "- Sale price: " . $variant->sale_price . "\n";
                            echo "- Sale starts: " . $variant->sale_price_starts_at . "\n";
                            echo "- Sale ends: " . $variant->sale_price_ends_at . "\n";
                            echo "- Now: " . $now . "\n";
                            echo "- Discount percent: " . ($variant->discount_percent ?? 'Not set') . "\n";
                        }
                        
                        if ($variant 
                            && $variant->sale_price 
                            && $variant->sale_price_starts_at 
                            && $variant->sale_price_ends_at) {
                            try {
                                $startDate = \Carbon\Carbon::parse($variant->sale_price_starts_at);
                                $endDate = \Carbon\Carbon::parse($variant->sale_price_ends_at);
                                $isOnSale = $now->between($startDate, $endDate);
                                
                                echo "Date comparison:\n";
                                echo "- Start date parsed: " . $startDate . "\n";
                                echo "- End date parsed: " . $endDate . "\n";
                                echo "- Is between: " . ($isOnSale ? 'Yes' : 'No') . "\n";
                            } catch (\Exception $e) {
                                echo "Error parsing dates: " . $e->getMessage() . "\n";
                                $isOnSale = false;
                            }
                        }
                        echo "-->";
                    @endphp
                    <div class="position-relative animate-underline d-flex align-items-center ps-xl-3">

                        {{-- Label giảm giá --}}
                        @if ($isOnSale && $variant && $variant->discount_percent > 0)
                            <div class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 rounded-bottom-end"
                                style="z-index: 10; font-weight: 600; font-size: 0.85rem;">
                                Giảm {{ $variant->discount_percent }}%
                            </div>
                        @endif

                        <div class="ratio ratio-1x1 flex-shrink-0" style="width: 110px">
                            <img src="{{ $product->coverImage ? asset('storage/' . $product->coverImage->path) : asset('assets/users/img/shop/electronics/thumbs/placeholder.png') }}"
                                alt="{{ $product->name }}" loading="lazy">
                        </div>
                        <div class="w-100 min-w-0 ps-2 ps-sm-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="d-flex gap-1 fs-xs">
                                    @php
                                        $rating = $product->average_rating ?? 0;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($rating >= $i) {
                                                echo '<i class="ci-star-filled text-warning"></i>';
                                            } elseif ($rating > $i - 1 && $rating < $i) {
                                                echo '<i class="ci-star-half text-warning"></i>';
                                            } else {
                                                echo '<i class="ci-star text-body-tertiary opacity-75"></i>';
                                            }
                                        }
                                    @endphp
                                </div>
                                <span class="text-body-tertiary fs-xs">{{ $product->approved_reviews_count ?? 0 }}</span>
                            </div>
                            <h4 class="mb-2">
                                <a class="stretched-link d-block fs-sm fw-medium text-truncate"
                                    href="{{ route('users.products.show', $product->slug) }}">
                                    <span class="animate-target">{{ $product->name }}</span>
                                </a>
                            </h4>
                            <div class="h5 mb-0">
                                @if ($isOnSale)
                                    <span class="text-danger">{{ number_format($variant->sale_price) }}đ</span>
                                    <del class="text-muted fs-sm ms-2">{{ number_format($variant->price) }}đ</del>
                                @elseif ($variant)
                                    {{ number_format($variant->price) }}đ
                                @else
                                    <span class="text-muted">Giá không khả dụng</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Product list (Last 4 products) -->
            <div class="col-sm-6 col-lg-4 d-flex flex-column gap-3 pt-3 py-lg-4">
                @foreach ($featuredProducts->skip(4)->take(4) as $product)
                    @php
                        $variant = $product->variants->first();
                        $now = now();
                        $isOnSale = false;
                        
                        // Debug raw data
                        echo "<!-- Debug for {$product->name}:\n";
                        echo "Variant exists: " . ($variant ? 'Yes' : 'No') . "\n";
                        if ($variant) {
                            echo "Raw data:\n";
                            echo "- Price: " . $variant->price . "\n";
                            echo "- Sale price: " . $variant->sale_price . "\n";
                            echo "- Sale starts: " . $variant->sale_price_starts_at . "\n";
                            echo "- Sale ends: " . $variant->sale_price_ends_at . "\n";
                            echo "- Now: " . $now . "\n";
                            echo "- Discount percent: " . ($variant->discount_percent ?? 'Not set') . "\n";
                        }
                        
                        if ($variant 
                            && $variant->sale_price 
                            && $variant->sale_price_starts_at 
                            && $variant->sale_price_ends_at) {
                            try {
                                $startDate = \Carbon\Carbon::parse($variant->sale_price_starts_at);
                                $endDate = \Carbon\Carbon::parse($variant->sale_price_ends_at);
                                $isOnSale = $now->between($startDate, $endDate);
                                
                                echo "Date comparison:\n";
                                echo "- Start date parsed: " . $startDate . "\n";
                                echo "- End date parsed: " . $endDate . "\n";
                                echo "- Is between: " . ($isOnSale ? 'Yes' : 'No') . "\n";
                            } catch (\Exception $e) {
                                echo "Error parsing dates: " . $e->getMessage() . "\n";
                                $isOnSale = false;
                            }
                        }
                        echo "-->";
                    @endphp
                    <div class="position-relative animate-underline d-flex align-items-center ps-xl-3">

                        {{-- Label giảm giá --}}
                        @if ($isOnSale && $variant && $variant->discount_percent > 0)
                            <div class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 rounded-bottom-end"
                                style="z-index: 10; font-weight: 600; font-size: 0.85rem;">
                                Giảm {{ $variant->discount_percent }}%
                            </div>
                        @endif

                        <div class="ratio ratio-1x1 flex-shrink-0" style="width: 110px">
                            <img src="{{ $product->coverImage ? asset('storage/' . $product->coverImage->path) : asset('assets/users/img/shop/electronics/thumbs/placeholder.png') }}"
                                alt="{{ $product->name }}" loading="lazy">
                        </div>
                        <div class="w-100 min-w-0 ps-2 ps-sm-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="d-flex gap-1 fs-xs">
                                    @php
                                        $rating = $product->average_rating ?? 0;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($rating >= $i) {
                                                echo '<i class="ci-star-filled text-warning"></i>';
                                            } elseif ($rating > $i - 1 && $rating < $i) {
                                                echo '<i class="ci-star-half text-warning"></i>';
                                            } else {
                                                echo '<i class="ci-star text-body-tertiary opacity-75"></i>';
                                            }
                                        }
                                    @endphp
                                </div>
                                <span class="text-body-tertiary fs-xs">{{ $product->approved_reviews_count ?? 0 }}</span>
                            </div>
                            <h4 class="mb-2">
                                <a class="stretched-link d-block fs-sm fw-medium text-truncate"
                                    href="{{ route('users.products.show', $product->slug) }}">
                                    <span class="animate-target">{{ $product->name }}</span>
                                </a>
                            </h4>
                            <div class="h5 mb-0">
                                @if ($isOnSale)
                                    <span class="text-danger">{{ number_format($variant->sale_price) }}đ</span>
                                    <del class="text-muted fs-sm ms-2">{{ number_format($variant->price) }}đ</del>
                                @elseif ($variant)
                                    {{ number_format($variant->price) }}đ
                                @else
                                    <span class="text-muted">Giá không khả dụng</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>


    <!-- SẢN PHẨM MỚI NHẤT -->
    <section class="container px-4 pt-5 mt-2 mt-sm-3 mt-lg-4">
        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 pb-md-4">
            <h2 class="h3 mb-0">Sản phẩm mới nhất của chúng tôi</h2>
            <div class="nav ms-3">
                <a class="nav-link animate-underline px-0 py-2" href="/products">
                    <span class="animate-target">Xem tất cả</span>
                    <i class="ci-chevron-right fs-base ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-4">
            @foreach ($latestProducts as $product)
                <div class="col">
                    <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
                        <div class="position-relative">
                            @php
                                $variant = $product->variants->first();
                                $now = now();
                                $onSale =
                                    $variant &&
                                    $variant->sale_price &&
                                    $variant->sale_price_starts_at &&
                                    $variant->sale_price_ends_at &&
                                    $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);

                                $price = $onSale ? $variant->sale_price : $variant->price;
                                $originalPrice = $onSale ? $variant->price : null;
                            @endphp

                            @if ($onSale && $variant->discount_percent > 0)
                                <div class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 rounded-bottom-end"
                                    style="z-index: 10; font-weight: 600; font-size: 0.85rem;">
                                    Giảm {{ $variant->discount_percent }}%
                                </div>
                            @endif

                            <a class="d-block rounded-top overflow-hidden p-3 p-sm-4"
                                href="{{ route('users.products.show', $product->slug) }}">
                                <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
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
                                    @if ($price)
                                        @if ($onSale)
                                            <span class="text-danger">{{ number_format($price) }}đ</span>
                                            <del class="text-muted fs-sm ms-2">{{ number_format($originalPrice) }}đ</del>
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
        </div>
    </section>


    <!-- Special offers (Carousel) -->

    <!-- Subscription form + Vlog -->
    <section class="bg-body-tertiary py-5">
        <div class="container px-4 pt-sm-2 pt-md-3 pt-lg-4 pt-xl-5">
            <div class="row">
                <div class="col-md-6 col-lg-5 mb-5 mb-md-0">
                    <h2 class="h4 mb-2">Sign up to our newsletter</h2>
                    <p class="text-body pb-2 pb-ms-3">Receive our latest updates about our products &amp; promotions</p>
                    <form class="d-flex needs-validation pb-1 pb-sm-2 pb-md-3 pb-lg-0 mb-4 mb-lg-5" novalidate="">
                        <div class="position-relative w-100 me-2">
                            <input type="email" class="form-control form-control-lg" placeholder="Your email"
                                required="">
                        </div>
                        <button type="submit" class="btn btn-lg btn-primary">Subscribe</button>
                    </form>
                    <div class="d-flex gap-3">
                        <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Instagram">
                            <i class="ci-instagram fs-base"></i>
                        </a>
                        <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Facebook">
                            <i class="ci-facebook fs-base"></i>
                        </a>
                        <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="YouTube">
                            <i class="ci-youtube fs-base"></i>
                        </a>
                        <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Telegram">
                            <i class="ci-telegram fs-base"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-4 offset-lg-1 offset-xl-2">
                    <ul class="list-unstyled d-flex flex-column gap-4 ps-md-4 ps-lg-0 mb-3">
                        <li class="nav flex-nowrap align-items-center position-relative">
                            <img src="{{ asset('assets/users/img/home/electronics/vlog/01.jpg') }}" class="rounded"
                                width="140" alt="Video cover">
                            <div class="ps-3">
                                <div class="fs-xs text-body-secondary lh-sm mb-2">6:16</div>
                                <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">5 New
                                    Cool Gadgets You Must See on Cartzilla - Cheap Budget</a>
                            </div>
                        </li>
                        <li class="nav flex-nowrap align-items-center position-relative">
                            <img src="{{ asset('assets/users/img/home/electronics/vlog/02.jpg') }}" class="rounded"
                                width="140" alt="Video cover">
                            <div class="ps-3">
                                <div class="fs-xs text-body-secondary lh-sm mb-2">10:20</div>
                                <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">5 Super
                                    Useful Gadgets on Cartzilla You Must Have in 2023</a>
                            </div>
                        </li>
                        <li class="nav flex-nowrap align-items-center position-relative">
                            <img src="{{ asset('assets/users/img/home/electronics/vlog/03.jpg') }}" class="rounded"
                                width="140" alt="Video cover">
                            <div class="ps-3">
                                <div class="fs-xs text-body-secondary lh-sm mb-2">8:40</div>
                                <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">Top 5
                                    New Amazing Gadgets on Cartzilla You Must See</a>
                            </div>
                        </li>
                    </ul>
                    <div class="nav ps-md-4 ps-lg-0">
                        <a class="btn nav-link animate-underline text-decoration-none px-0" href="#!">
                            <span class="animate-target">View all</span>
                            <i class="ci-chevron-right fs-base ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection

@push('styles')
    <style>
        .hero-banner {
            margin-top: -16px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Additional scripts for homepage if needed
    </script>
@endpush
