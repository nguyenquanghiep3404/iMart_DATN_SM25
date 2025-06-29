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
                        <!-- Slider nội dung -->
                        <div class="swiper-wrapper">
                            @foreach ($banners as $banner)
                                <div class="swiper-slide text-center text-xl-start pt-5 py-xl-5">
                                    <p class="text-body">{{ $banner->position }}</p>
                                    <h2 class="display-4 pb-2 pb-xl-4">{{ $banner->title }}</h2>
                                    @if ($banner->link_url)
                                        <a class="btn btn-lg btn-primary" href="{{ $banner->link_url }}">
                                            Xem ngay <i class="ci-arrow-up-right fs-lg ms-2 me-n1"></i>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
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
                            @foreach ($banners as $banner)
                                <div class="swiper-slide d-flex justify-content-end">
                                    <div class="ratio rtl-flip"
                                        style="max-width: 495px; --cz-aspect-ratio: calc(537 / 495 * 100%)">
                                        @if ($banner->desktopImage)
                                            <img src="{{ Storage::url($banner->desktopImage->path) }}"
                                                alt="{{ $banner->title }}">
                                        @elseif($banner->mobileImage)
                                            <img src="{{ Storage::url($banner->mobileImage->path) }}"
                                                alt="{{ $banner->title }}">
                                        @else
                                            <img src="{{ asset('images/default-banner.jpg') }}" alt="Default Banner">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
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
    <style>
        .card-hover img {
            transition: transform 0.3s ease;
        }

        .card-hover:hover img {
            transform: scale(1.05);
        }
        
    </style>

    <section class="container px-4 pt-5 mt-1 mt-sm-2 mt-md-3 mt-lg-4">
    <h2 class="h3 pb-2 pb-sm-3 border-bottom border-primary d-inline-block">
        <i class="ci-star text-warning me-2"></i> Sản phẩm nổi bật
    </h2>

    <div class="row g-4 pt-3">
        <!-- Banner -->
        <div class="col-lg-4" data-bs-theme="dark">
            <div class="d-flex flex-column align-items-center justify-content-end h-100 text-center overflow-hidden rounded-5 px-4 px-lg-3 pt-4 pb-5 shadow"
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

        <!-- Product list -->
        @foreach ([0, 4] as $offset)
            <div class="col-sm-6 col-lg-4 d-flex flex-column gap-3">
                @foreach ($featuredProducts->skip($offset)->take(4) as $product)
                    @php
                        $displayVariant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();
                        $imageToShow = $displayVariant?->primaryImage ?? $product->coverImage;
                        $mainImage = $imageToShow ? Storage::url($imageToShow->path) : asset('images/placeholder.jpg');
                        $isOnSale = $displayVariant && $displayVariant->sale_price && $displayVariant->price > 0;
                    @endphp

                    <div class="card border-0 shadow-sm h-100 card-hover position-relative">
                       @if ($isOnSale && $displayVariant->discount_percent > 0)
    <div class="position-absolute top-0 end-0 bg-danger text-white px-2 py-1 rounded-top-start"
         style="z-index: 10; font-weight: 600; font-size: 0.85rem; padding: 8px 16px 8px 28px; border-radius: 32px 0 0 32px;">
        Giảm {{ $displayVariant->discount_percent }}%
    </div>
@endif

                        <div class="card-body d-flex align-items-center p-3">
                            <div class="ratio ratio-1x1 flex-shrink-0" style="width: 110px">
                                <img src="{{ $mainImage }}" alt="{{ $product->name }}" class="rounded">
                            </div>
                            <div class="w-100 min-w-0 ps-3">
                                <!-- 1. Tên sản phẩm -->
                                @php
                                    $storage = $displayVariant?->attributeValues->firstWhere('attribute.name', 'Dung lượng lưu trữ')?->value;
                                @endphp
                                <h4 class="fs-5 fw-semibold mb-2 text-truncate text-dark">
                                    <a class="stretched-link text-decoration-none text-dark"
                                        href="{{ route('users.products.show', $product->slug) }}">
                                        {{ $product->name }}{{ $storage ? ' ' . $storage : '' }}
                                    </a>
                                </h4>

                                <!-- 2. Giá -->
                                <div class="h6 mb-2">
                                    @if ($isOnSale && $displayVariant->discount_percent > 0)
                                        <span class="text-primary fw-bold fs-lg" style="color: #0d6efd !important;">
                                            {{ number_format($displayVariant->sale_price) }}đ
                                        </span>
                                        <del class="text-muted fs-sm ms-2">{{ number_format($displayVariant->price) }}đ</del>
                                    @elseif ($displayVariant)
                                        <span class="fw-bold fs-lg">{{ number_format($displayVariant->price) }}đ</span>
                                    @else
                                        <span class="text-muted">Giá không khả dụng</span>
                                    @endif
                                </div>

                                <!-- 3. Đánh giá -->
                                <div class="d-flex align-items-center gap-2">
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
                                    <span class="text-muted fs-xs">{{ $product->approved_reviews_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
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

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-2">
            @forelse ($latestProducts as $product)
                @php
                    $variant = $product->variants->first();
                    $displayVariant = $product->variants->firstWhere('is_default', true) ?? $variant;
                    $imageToShow = $displayVariant?->primaryImage ?? $product->coverImage;
                    $mainImage = $imageToShow ? Storage::url($imageToShow->path) : asset('images/placeholder.jpg');

                    $onSale = $displayVariant && $displayVariant->sale_price && $displayVariant->price > 0;
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
                                <a class="d-block fs-5 fw-bold text-truncate mb-2 no-underline-link"
                                    href="{{ route('users.products.show', $product->slug) }}" style="margin-top: 10px;">
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
                                    @if ($onSale && $displayVariant->discount_percent > 0)
                                        <span class="text-primary fw-bold fs-lg" style="color: #0d6efd !important;">
                                            {{ number_format($displayVariant->sale_price) }}đ
                                        </span>
                                        <del
                                            class="text-muted fs-md ms-2">{{ number_format($displayVariant->price) }}đ</del>
                                    @else
                                        <span class="fw-bold fs-lg">{{ number_format($displayVariant->price) }}đ</span>
                                    @endif
                                @else
                                    <span class="text-muted fs-md">Giá không khả dụng</span>
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
    </section>

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

    <!-- Special offers (Carousel) -->
    <!-- (Giữ nguyên vì chưa có logic ảnh cần sửa) -->

    <!-- Subscription form + Vlog -->
    <section class="bg-body-tertiary py-5">
        <div class="container px-4 pt-sm-2 pt-md-3 pt-lg-4 pt-xl-5">
            <div class="row">
                <div class="col-md-6 col-lg-5 mb-5 mb-md-0">
                    <h2 class="h4 mb-2">Sign up to our newsletter</h2>
                    <p class="text-body pb-2 pb-ms-3">Receive our latest updates about our products & promotions</p>
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
                            <span class="animate-target">Xem tất cả</span>
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
