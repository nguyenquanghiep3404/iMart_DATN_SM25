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

    @foreach ($blocks as $block)
    <section class="container px-4 pt-5 mt-2 mt-sm-3 mt-lg-4">
        <h2 class="h3 pb-2 border-bottom border-primary d-inline-block">
            <i class="ci-star text-warning me-2"></i> {{ $block->title }}
        </h2>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-2">
            @forelse ($block->products as $product)
                @php
                    $displayVariant = $product->variants->first();
                    $imageToShow = $displayVariant?->primaryImage ?? $product->coverImage;
                    $mainImage = $imageToShow ? Storage::url($imageToShow->path) : asset('images/placeholder.jpg');
                    $isOnSale = $displayVariant && $displayVariant->sale_price && $displayVariant->price > 0;
                @endphp

                <div class="col">
                    <div class="product-card bg-body rounded-4 shadow-lg border-0">
                        <div class="position-relative">
                            @if ($isOnSale && $displayVariant->discount_percent > 0)
                                <div class="discount-badge">Giảm {{ $displayVariant->discount_percent }}%</div>
                            @endif
                            <a href="{{ route('users.products.show', $product->slug) }}">
                                <div class="ratio" style="--cz-aspect-ratio: calc(250 / 220 * 100%)">
                                    <img src="{{ $mainImage }}" alt="{{ $product->name }}" class="img-fluid rounded-3 shadow-sm" style="object-fit:contain; width:100%; height:100%;">
                                </div>
                            </a>
                        </div>
                        <div class="px-2 pb-3 pt-2 text-center">
                            <h3 class="fs-6 fw-bold text-truncate">
                                <a href="{{ route('users.products.show', $product->slug) }}" class="text-dark text-decoration-none">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <div class="text-primary fw-bold">
                                @if ($isOnSale)
                                    {{ number_format($displayVariant->sale_price) }}đ
                                    <del class="text-muted ms-1">{{ number_format($displayVariant->price) }}đ</del>
                                @else
                                    {{ number_format($displayVariant->price) }}đ
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">Chưa có sản phẩm</div>
            @endforelse
        </div>
    </section>
@endforeach


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

    <!-- Subscription form + Featured Blog Posts -->
<section class="bg-body-tertiary py-5">
    <div class="container px-4 pt-sm-2 pt-md-3 pt-lg-4 pt-xl-5">
        <div class="row">
            <!-- Đăng ký nhận bản tin -->
            <div class="col-md-6 col-lg-5 mb-5 mb-md-0">
                <h2 class="h4 mb-2">Đăng ký nhận bản tin của chúng tôi</h2>
                <p class="text-body pb-2 pb-ms-3">
                    Nhận thông tin cập nhật mới nhất về sản phẩm và chương trình khuyến mãi của chúng tôi
                </p>
                <form class="d-flex needs-validation pb-1 pb-sm-2 pb-md-3 pb-lg-0 mb-4 mb-lg-5" novalidate>
                    <div class="position-relative w-100 me-2">
                        <input type="email" class="form-control form-control-lg"
                            placeholder="Nhập địa chỉ email của bạn" required>
                    </div>
                    <button type="submit" class="btn btn-lg btn-primary">Đăng ký</button>
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

            <!-- Bài viết nổi bật -->
            <div class="col-md-6 col-lg-5 col-xl-4 offset-lg-1 offset-xl-2">
                <h2 class="h5 mb-4">📰 Bài viết nổi bật</h2>
                <ul class="list-unstyled d-flex flex-column gap-4 ps-md-4 ps-lg-0 mb-3">
                    @foreach ($featuredPosts as $post)
                        <li class="nav flex-nowrap align-items-center position-relative">
                            <img src="{{ $post->coverImage ? asset('storage/' . $post->coverImage->path) : asset('assets/users/img/default-thumbnail.jpg') }}"
                                class="rounded" width="140" height="90" style="object-fit: cover;" alt="{{ $post->title }}">
                            <div class="ps-3">
                                <div class="fs-xs text-body-secondary lh-sm mb-2">
                                    {{ $post->published_at ? $post->published_at->format('H:i d/m/Y') : 'Chưa đăng' }}
                                </div>
                                <a class="nav-link fs-sm hover-effect-underline stretched-link p-0"
                                   href="{{ route('users.blogs.show', $post->slug) }}">
                                    {{ Str::limit($post->title, 60) }}
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="nav ps-md-4 ps-lg-0">
                    <a class="btn nav-link animate-underline text-decoration-none px-0"
                       href="">
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
