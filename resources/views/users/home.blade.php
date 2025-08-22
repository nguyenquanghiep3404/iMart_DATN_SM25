@extends('users.layouts.app')


@section('title', 'Trang chủ - iMart')

@push('styles')
    <style>
        /* Đảm bảo container của slider không bị che khuất */
        .container {
            position: relative;
        }

        /* Hero Banner */
        .hero-banner {
            margin-top: -16px;
        }

        /* Card Hover Effects */
        .card-hover img {
            transition: transform 0.3s ease;
        }

        .card-hover:hover img {
            transform: scale(1.05);
        }

        /* Discount Badge */
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

        /* Product Card */
        .product-card {
            transition: transform 0.25s, box-shadow 0.25s, border 0.25s;
            position: relative;
            border: 1.5px solid #e5e9f2;
            overflow: hidden;
        }


        .product-card .badge-sale {
            border-radius: 0 !important;
        }

        .product-card .ratio {
            --cz-aspect-ratio: calc(200 / 260 * 100%);
        }


        /* Swiper Navigation */
        .swiper-button-next,
        .swiper-button-prev {
            color: #fff;
            width: 60px;
            height: 60px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            transition: all 0.3s;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Flash Sale Styles */
        .flash-sale-header {
            background: linear-gradient(90deg, #2e2d2d 0%);
            color: #fff;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: -12px;
            box-shadow: 0 4px 16px #ff980022;
            position: relative;
            z-index: 2;
            width: 100%;
            min-width: 220px;
            min-height: 56px;
        }

        .flash-sale-icon {
            font-size: 2rem;
            color: #fff700;
            animation: flash-bounce 1s infinite alternate;
        }

        @keyframes flash-bounce {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.15);
            }
        }

        .flash-sale-title {
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: #fff;
            text-shadow: 0 2px 8px #f0981955;
            margin: 0;
        }

        .flash-sale-timer {
            font-size: 1rem;
            color: #fffbe7;
            font-weight: 500;
        }

        .flash-sale-main-card {
            background: #fff;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 24px #ff980033;
            padding: 0;
            /* Loại bỏ padding ngang để full width */
            margin: 0;
            /* Loại bỏ margin thừa */
            width: 100%;
            /* Đảm bảo full width */
        }

        .flash-sale-campaign-tabs {
            width: 100%;
            /* Full width */
            display: flex;
            justify-content: space-between;
            /* Phân bố đều các phần tử và full width */
            align-items: center;
            padding: 0;
            /* Loại bỏ padding */
            margin: 0 0 1.5rem 0;
            /* Chỉ giữ margin dưới */
            border-bottom: 1.5px solid #e0e0e0;
            flex-wrap: nowrap;
            /* Ngăn wrap */
            overflow-x: auto;
            /* Cho phép scroll nếu cần */
            white-space: nowrap;
            /* Ngăn text wrap */
        }

        .flash-sale-campaign-tab {
            flex: 1;
            /* Mỗi phần tử chiếm đều không gian */
            border: none;
            background: transparent;
            font-size: 1.1rem;
            color: #e30613;
            text-align: center;
            padding: 8px 12px;
            /* Padding cho mỗi phần tử */
            white-space: nowrap;
            /* Ngăn text wrap trong tab */
            transition: background 0.2s, color 0.2s;
        }

        .flash-sale-campaign-tab.active,
        .flash-sale-campaign-tab.active:hover {
            background: #ffe0b2;
            color: #e65100 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .flash-sale-campaign-tab:hover {
            background: transparent !important;
            color: #e30613 !important;
        }

        .flash-sale-campaign-tab span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .flash-sale-card {
            background: linear-gradient(135deg, #fffbe7 0%, #ffe5e5 100%);
            border-radius: 1.25rem !important;
            box-shadow: 0 6px 24px #ff512f22;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid #ff512f22;
            min-width: 240px;
            max-width: 260px;
        }

        .flash-sale-card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 12px 32px #ff512f33, 0 0 12px #f0981944;
            border: 2px solid #ff512f;
        }

        .flash-sale-badge {
            font-size: 0.9rem;
            font-weight: bold;
            letter-spacing: 1px;
            z-index: 2;
            box-shadow: 0 2px 8px #e3061344;
        }

        .time-slot-btn {
            border-radius: 2rem !important;
            min-width: 110px;
            font-size: 1rem;
            box-shadow: 0 2px 8px #ff512f11;
            border: none;
            transition: background 0.2s, color 0.2s;
        }

        .slot-active {
            background: linear-gradient(90deg, #e30613 60%, #ff512f 100%) !important;
            color: #fff !important;
            border: none;
            box-shadow: 0 2px 12px #e3061344;
        }

        .slot-upcoming {
            background: linear-gradient(90deg, #fff700 60%, #ffb347 100%) !important;
            color: #222 !important;
            border: none;
        }

        .slot-past {
            background: #e5e5e5 !important;
            color: #aaa !important;
            border: none;
            text-decoration: line-through;
        }

        .flash-sale-tabs {
            background: #fffbe7;
            border-radius: 1.5rem;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 8px #ff512f11;
            overflow-x: auto;
        }

        .flash-sale-tab {
            border: none;
            background: transparent;
            border-radius: 1.5rem !important;
            transition: background 0.2s, color 0.2s;
            font-size: 1.1rem;
            color: #e30613;
            position: relative;
        }

        .flash-sale-tab.active,
        .flash-sale-tab:hover {
            background: linear-gradient(90deg, #ff512f 0%, #f09819 100%);
            color: #fff !important;
            box-shadow: 0 2px 8px #ff512f33;
        }

        .flash-sale-tab .text-danger {
            color: inherit !important;
        }

        .flash-sale-slot-row {
            border-radius: 1.5rem;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        .flash-sale-countdown-box {
            background: linear-gradient(90deg, #ffa726 0%, #ff9800 100%);
            border-radius: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 120px;
            box-shadow: 0 2px 8px #ff980033;
            padding: 8px 12px !important;
        }

        .countdown-sep {
            color: #fff;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0 2px;
            line-height: 1;
        }

        .countdown-box {
            background: #ffffff;
            /* Mặc định xám sáng */
            color: #000;
            /* Mặc định chữ đen */
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 8px;
            min-width: 36px;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            margin: 0 2px;
            box-shadow: none;
        }


        .flash-sale-slot-info {
            background: #f3f4f6;
            border-radius: 1.5rem;
            min-width: 100px;
            min-height: 48px;
            max-width: 110px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 1px 4px #e0e0e0aa;
            padding: 6px 0;
        }

        .slot-label {
            color: #ff9800;
            font-weight: 500;
            line-height: 1;
        }

        .slot-time {
            color: #222;
            font-size: 1.15rem;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1;
        }


        .flash-sale-slot-box.slot-active .slot-label,
        .flash-sale-slot-box.slot-active .slot-time {
            color: #fff !important;
        }

        .flash-sale-slot-box.slot-active {
            background: #2e2d2d !important;
            color: #fff !important;
        }

        .flash-sale-slot-box.slot-upcoming {
            border: 2px dashed #ff9800;
            background: #fffbe7;
        }

        .slot-time-label {
            color: #ff9800;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1;
        }

        .slot-status-label {
            color: #ff9800;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1;
        }

        .slot-countdown-box {
            margin-bottom: 0;
        }

        .flash-sale-title-bar {
            background: linear-gradient(90deg, #ff9800 0%, #ff512f 100%);
            border-radius: 1.5rem 1.5rem 0 0;
            padding: 0.75rem 2rem;
            color: #fff;
            font-size: 1.4rem;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: -12px;
            box-shadow: 0 4px 16px #ff980022;
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            width: fit-content;
            min-width: 220px;
        }

        .flash-sale-title-icon {
            font-size: 2rem;
            color: #fff700;
            animation: flash-bounce 1s infinite alternate;
        }

        @media (max-width: 576px) {
            .flash-sale-header {
                font-size: 1rem;
                padding: 0.5rem 1rem;
            }

            .flash-sale-title {
                font-size: 1.1rem;
            }

            .flash-sale-card {
                min-width: 180px;
                max-width: 200px;
            }

            .time-slot-btn {
                font-size: 0.9rem;
                min-width: 80px;
            }

            .flash-sale-campaign-tabs {
                flex-wrap: nowrap;
                gap: 4px;
            }

            .flash-sale-campaign-tab {
                min-width: 80px;
                font-size: 0.95rem;
                padding: 6px 8px;
            }

            .flash-sale-countdown-box,
            .flash-sale-slot-info {
                min-width: 80px;
                max-width: 90px;
                min-height: 38px;
                padding: 4px 0 !important;
            }

            .slot-time {
                font-size: 1rem;
            }
        }

        .flash-sale-campaign-tabs {
            margin: 0 auto !important;
            border-bottom: none !important;
            box-shadow: none !important;
            background: transparent !important;
            width: auto !important;
        }

        .flash-sale-campaign-tabs td {
            padding: 0 !important;
        }

        .flash-sale-campaign-tab {
            border: none;
            background: transparent;
            font-size: 1.1rem;
            color: #e30613;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 0 !important;
            margin: 0 !important;
            transition: background 0.2s, color 0.2s;
            box-shadow: none !important;
        }

        .flash-sale-campaign-tab.active {
            background: #ffe0b2;
            color: #e65100 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        /* Không hiệu ứng hover */
        .flash-sale-campaign-tab:hover {
            background: transparent !important;
            color: #e30613 !important;
        }

        .flash-sale-slot-box {
            min-width: 180px;
            min-height: 70px;
            background: #e3e4e5;
            border-radius: 16px;
            margin-right: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, color 0.2s;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: none;
            border: none;
        }

        .flash-sale-slot-box.slot-upcoming {
            background: #f5f6fa;
            color: #222;
            border: none;
        }


        .countdown-sep {
            color: #fff;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0 2px;
            line-height: 1;
        }

        .flash-sale-slot-group {
            background: #f5f6fa;
            border-radius: 18px;
            padding: 18px 0 18px 0;
            margin: 0 auto 24px auto;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .slot-box {
            min-width: 140px;
            min-height: 70px;
            background: transparent;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, color 0.2s;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: none;
            border: none;
            padding: 8px 0;
        }

        .slot-active {
            background: #ffa000 !important;
            color: #fff !important;
            border: none !important;
        }

        .slot-active .slot-label,
        .slot-active {
            color: #fff !important;
        }

        .slot-active {
            background: #ffb300 !important;
            color: #fff !important;
        }

        .slot-upcoming {
            color: #222;
            border: none;
            background: transparent;
        }


        .slot-countdown {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }


        .countdown-sep {
            color: #fff;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0 2px;
            line-height: 1;
        }


        .slot-label,
        .slot-time {
            color: #000 !important;
        }

        .slot-upcoming,
        .slot-upcoming .slot-label,
        .slot-upcoming .slot-time {
            color: #222 !important;
        }

        .flash-sale-slot-box span {
            color: #000;
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Khi active, đổi dấu ":" sang trắng */
        .slot-active span {
            color: #fff !important;
        }

        .progress-wrapper {
            height: 26px;
            background: #e5e5e5;
            border-radius: 20px;
            overflow: hidden;
            font-size: 13px;
            font-weight: bold;
            color: #000;
            margin-top: 6px;
            position: relative;
        }

        .progress-bar-inner {
            background: linear-gradient(to right, #ffd55f, #ffa500);
            height: 100%;
            display: flex;
            align-items: center;
            /* căn giữa theo chiều dọc */
            justify-content: center;
            /* căn giữa theo chiều ngang */
            padding: 0 10px;
            white-space: nowrap;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
            transition: width 0.3s ease;
            color: #000;
        }

        .progress-text {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .flash-sale-title-img {
            height: 125px;
            /* chỉnh chiều cao phù hợp */
            object-fit: contain;
        }
    </style>
@endpush

@section('content')
    <!-- Hero slider -->
    <section class="w-100">
        <div class="position-relative">
            <!-- Slider ảnh (controlled) -->
            <div class="col-12">
                <div class="swiper user-select-none position-relative" id="sliderImages"
                    data-swiper='{
                    "allowTouchMove": false,
                    "loop": true,
                    "speed": 1200,
                    "effect": "fade",
                    "fadeEffect": { "crossFade": true },
                    "autoplay": {
                        "delay": 3000,
                        "disableOnInteraction": false
                    },
                    "navigation": {
                        "nextEl": ".swiper-button-next",
                        "prevEl": ".swiper-button-prev"
                    }
                }'>

                    <div class="swiper-wrapper">
                        @foreach ($banners as $banner)
                            <div class="swiper-slide">
                                <a href="{{ $banner->link_url }}" target="_blank" style="display: block; width: 100%;">
                                    @if ($banner->desktopImage)
                                        <img src="{{ Storage::url($banner->desktopImage->path) }}"
                                            alt="{{ $banner->title }}" width="1280" height="400"
                                            style="width: 100%; height: auto;">
                                    @else
                                        <img src="{{ asset('images/default-banner.jpg') }}" alt="Default Banner"
                                            width="1280" height="400" style="width: 100%; height: auto;">
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <!-- Nút điều hướng -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
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
    </section>

    @if ($flashSales->count())
        <section class="container px-4 pt-5 mt-2 mt-sm-3 mt-lg-4 bg-body-tertiary">
            <div class="flash-sale-header d-flex align-items-center justify-content-between px-4">
                <div class="d-flex align-items-center">
                    <span class="flash-sale-icon me-2"><i class="ci-bolt"></i></span>
                    <img src="{{ asset('assets/users/logo/icon-fs.png') }}" alt="Flash Sale" class="flash-sale-title-img"
                        id="flash-sale-campaign-title">
                    <span class="flash-sale-timer ms-3 d-none d-md-inline">
                        <img src="{{ asset('assets/users/logo/—Pngtree—50 parcent off sale offer_5949710.png') }}"
                            alt="Flash Sale" class="img-fluid" style="height:67px; margin-left: 45px;">
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-link p-0 m-0" id="flash-sale-prev-campaign"
                        style="font-size: 2rem; color: #fff;">
                        <i class="ci-arrow-left"></i>
                    </button>
                    <button type="button" class="btn btn-link p-0 m-0" id="flash-sale-next-campaign"
                        style="font-size: 2rem; color: #fff;">
                        <i class="ci-arrow-right"></i>
                    </button>
                </div>
            </div>
            <div class="flash-sale-main-card p-4 rounded-4 shadow-lg mb-5">
                <div class="flash-sale-campaign-content">
                    @foreach ($flashSales as $idx => $sale)
                        <div class="flash-sale-campaign-block @if ($idx !== 0) d-none @endif"
                            data-campaign-idx="{{ $idx }}">
                            <div
                                class="d-flex gap-3 align-items-center flash-sale-slot-row justify-content-center flex-wrap">
                                @foreach ($sale->flashSaleTimeSlots as $slotIdx => $slot)
                                    @php
                                        $start = \Carbon\Carbon::parse($slot->start_time);
                                        $end = \Carbon\Carbon::parse($slot->end_time);
                                        $now = now();
                                        $isActive = $now->between($start, $end);
                                        $isUpcoming = $now->lt($start);
                                        $isPast = $now->gt($end);
                                    @endphp
                                    @if ($isPast)
                                        @continue
                                    @endif
                                    <div class="flash-sale-slot-box d-flex flex-column align-items-center justify-content-center {{ $isActive ? 'slot-active' : '' }}"
                                        data-slot-id="{{ $slot->id }}" data-slot-idx="{{ $slotIdx }}"
                                        data-start="{{ \Carbon\Carbon::parse($slot->start_time)->toIso8601String() }}"
                                        data-end="{{ \Carbon\Carbon::parse($slot->end_time)->toIso8601String() }}"
                                        style="min-width: 180px; min-height: 90px; border-radius: 16px; margin-right: 12px;">
                                        <div class="slot-label mb-2">
                                            {{ $isActive ? 'Còn lại' : 'Sắp diễn ra' }}
                                        </div>
                                        @if ($isActive)
                                            <div class="slot-countdown-box d-flex align-items-center gap-1 mb-1">
                                                <div class="countdown-box countdown-flat"
                                                    id="countdown-hour-{{ $sale->id }}-{{ $slot->id }}">00</div>
                                                <span class="countdown-sep">:</span>
                                                <div class="countdown-box countdown-flat"
                                                    id="countdown-min-{{ $sale->id }}-{{ $slot->id }}">00</div>
                                                <span class="countdown-sep">:</span>
                                                <div class="countdown-box countdown-flat"
                                                    id="countdown-sec-{{ $sale->id }}-{{ $slot->id }}">00</div>
                                            </div>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    let endTime = new Date(@json($slot->end_time));
                                                    function updateCountdown_{{ $sale->id }}_{{ $slot->id }}() {
                                                        let now = new Date();
                                                        let diff = Math.max(0, Math.floor((endTime - now) / 1000));
                                                        let h = Math.floor(diff / 3600).toString().padStart(2, '0');
                                                        let m = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
                                                        let s = (diff % 60).toString().padStart(2, '0');
                                                        document.getElementById('countdown-hour-{{ $sale->id }}-{{ $slot->id }}').textContent = h;
                                                        document.getElementById('countdown-min-{{ $sale->id }}-{{ $slot->id }}').textContent = m;
                                                        document.getElementById('countdown-sec-{{ $sale->id }}-{{ $slot->id }}').textContent = s;
                                                    }
                                                    updateCountdown_{{ $sale->id }}_{{ $slot->id }}();
                                                    setInterval(updateCountdown_{{ $sale->id }}_{{ $slot->id }}, 1000);
                                                });
                                            </script>
                                        @else
                                            <div class="slot-time fw-bold">{{ $start->format('H:i') }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @foreach ($sale->flashSaleTimeSlots as $slotIdx => $slot)
                                @php
                                    $isActiveSlot = $slot->id == $sale->active_slot_id;
                                @endphp
                                <div class="flash-sale-slot-products @if (!$isActiveSlot) d-none @endif"
                                    data-slot-idx="{{ $slotIdx }}"
                                    data-start="{{ \Carbon\Carbon::parse($slot->start_time)->toIso8601String() }}"
                                    data-end="{{ \Carbon\Carbon::parse($slot->end_time)->toIso8601String() }}">
                                    <div class="swiper flash-sale-swiper-{{ $sale->id }}-{{ $slot->id }}">
                                        <div class="swiper-wrapper">
                                            @forelse ($slot->products as $fsProduct)
                                                @php
                                                    $variant = $fsProduct->productVariant;
                                                    $product = $variant->product ?? null;
                                                    $mainImage = $variant?->primaryImage?->url ?? ($product?->thumbnail_url ?? asset('images/placeholder.jpg'));
                                                    $attributes = $variant->attributeValues ?? collect();
                                                    $nonColor = $attributes->filter(fn($v) => $v->attribute->name !== 'Màu sắc')->pluck('value')->join(' ');
                                                    $color = $attributes->firstWhere(fn($v) => $v->attribute->name === 'Màu sắc')?->value;
                                                    $variantName = trim($nonColor . ' ' . $color);
                                                    $quantityLeft = max(0, $fsProduct->quantity_limit - $fsProduct->quantity_sold);
                                                    $total = $fsProduct->quantity_limit;
                                                    $sold = $fsProduct->quantity_sold ?? 0;
                                                    $remaining = $total - $sold;
                                                    $percent = $total > 0 ? ($remaining / $total) * 100 : 0;
                                                @endphp
                                                <div class="swiper-slide">
                                                    <div class="product-card bg-body shadow-lg border-1">
                                                        <div class="position-relative">
                                                            <a href="{{ route('users.products.show', $variant->slug) }}">
                                                                <div class="ratio" style="--cz-aspect-ratio: calc(250 / 220 * 100%)">
                                                                    <img src="{{ $mainImage }}" alt="{{ $product?->name }}"
                                                                        class="img-fluid rounded-3 shadow-sm" style="object-fit:contain; width:100%; height:100%;">
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="px-3 pb-3 pt-2 text-center">
                                                            <h2 class="fw-bold" style="line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.8em;">
                                                                <a href="{{ route('users.products.show', $product?->slug) }}" class="text-dark text-decoration-none">
                                                                    {{ $product?->name }} {{ $variantName }}
                                                                </a>
                                                            </h2>
                                                            <div class="fw-bold js-flash-sale-price" data-flash-price="{{ $fsProduct->flash_price }}"
                                                                style="color:#da272a; font-size: 20px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                                @if ($isUpcoming)
                                                                    @php
                                                                        $price = (int) $fsProduct->flash_price;
                                                                        if ($price < 1000000) {
                                                                            $shortPrice = 'xxx.000đ';
                                                                        } elseif ($price < 10000000) {
                                                                            $millions = floor($price / 1000000);
                                                                            $shortPrice = $millions . '.xxx.000đ';
                                                                        } else {
                                                                            $tens = floor($price / 10000000);
                                                                            $shortPrice = $tens . 'x.xxx.000đ';
                                                                        }
                                                                    @endphp
                                                                    {{ $shortPrice }}
                                                                @else
                                                                    {{ number_format($fsProduct->flash_price, 0, ',', '.') }}₫
                                                                @endif
                                                            </div>
                                                            <div class="text-muted" style="font-size: 14px; text-decoration: line-through;">
                                                                {{ number_format($variant->price) }}₫
                                                            </div>
                                                            <div class="js-flash-sale-progress">
                                                                <div class="progress-wrapper">
                                                                    <div class="progress-bar-inner" style="width: {{ $percent }}%">
                                                                        <span class="progress-text">
                                                                            🔥 Còn {{ $remaining }}/{{ $total }} suất
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="swiper-slide">
                                                    <div class="col-12 text-center text-muted py-4">Chưa có sản phẩm</div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
            <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
            <script>
                function updateFlashSaleSlotProducts(slotProducts) {
                    if (!slotProducts) return;
                    var start = new Date(slotProducts.getAttribute('data-start'));
                    var end = new Date(slotProducts.getAttribute('data-end'));
                    var now = new Date();
                    var isUpcoming = now < start;
                    var isActive = now >= start && now <= end;
                    slotProducts.querySelectorAll('.js-flash-sale-price').forEach(function(priceEl) {
                        var flashPrice = parseInt(priceEl.getAttribute('data-flash-price'));
                        var shortPrice = '';
                        if (isUpcoming) {
                            if (flashPrice < 1000000) {
                                shortPrice = 'xxx.000đ';
                            } else if (flashPrice < 10000000) {
                                var millions = Math.floor(flashPrice / 1000000);
                                shortPrice = millions + '.xxx.000đ';
                            } else {
                                var tens = Math.floor(flashPrice / 10000000);
                                shortPrice = tens + 'x.xxx.000đ';
                            }
                            priceEl.textContent = shortPrice;
                        } else {
                            priceEl.textContent = flashPrice.toLocaleString('vi-VN') + 'đ';
                        }
                    });
                    slotProducts.querySelectorAll('.js-flash-sale-progress').forEach(function(progressEl) {
                        progressEl.style.display = isActive ? '' : 'none';
                    });
                }

                function bindSlotBoxClick(campaignBlock) {
                    const slotBoxes = campaignBlock.querySelectorAll('.flash-sale-slot-box');
                    slotBoxes.forEach(function(box) {
                        box.addEventListener('click', function() {
                            slotBoxes.forEach(function(b) {
                                b.classList.remove('slot-active');
                            });
                            this.classList.add('slot-active');
                            campaignBlock.querySelectorAll('.flash-sale-slot-products').forEach(function(p) {
                                p.classList.add('d-none');
                            });
                            var idx = this.getAttribute('data-slot-idx');
                            var slotProducts = campaignBlock.querySelector(
                                '.flash-sale-slot-products[data-slot-idx="' + idx + '"]');
                            if (slotProducts) {
                                slotProducts.classList.remove('d-none');
                                updateFlashSaleSlotProducts(slotProducts);
                            }
                        });
                    });
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const flashSales = @json($flashSales->pluck('name'));
                    const campaignBlocks = document.querySelectorAll('.flash-sale-campaign-block');
                    let currentIdx = 0;
                    const titleEl = document.getElementById('flash-sale-campaign-title');
                    const prevBtn = document.getElementById('flash-sale-prev-campaign');
                    const nextBtn = document.getElementById('flash-sale-next-campaign');

                    // Initialize Swiper for each flash sale slot
                    document.querySelectorAll('.flash-sale-slot-products').forEach(function(slot) {
                        new Swiper(slot.querySelector('.swiper'), {
                            slidesPerView: 2,
                            breakpoints: {
                                576: { slidesPerView: 3 },
                                768: { slidesPerView: 4 },
                                992: { slidesPerView: 6 }
                            },
                            navigation: false,
                            pagination: false,
                            mousewheel: false,
                            touchRatio: 1.5,
                            freeMode: true
                        });
                    });

                    function showCampaign(idx) {
                        campaignBlocks.forEach((block, i) => {
                            block.classList.toggle('d-none', i !== idx);
                        });
                        if (flashSales[idx]) {
                            titleEl.src = "{{ asset('assets/users/logo/icon-fs.png') }}";
                            titleEl.alt = flashSales[idx];
                        }
                        bindSlotBoxClick(campaignBlocks[idx]);
                        const activeSlot = campaignBlocks[idx].querySelector('.flash-sale-slot-box.slot-active') ||
                            campaignBlocks[idx].querySelector('.flash-sale-slot-box');
                        if (activeSlot) activeSlot.click();
                    }

                    prevBtn.addEventListener('click', function() {
                        currentIdx = (currentIdx - 1 + flashSales.length) % flashSales.length;
                        showCampaign(currentIdx);
                    });
                    nextBtn.addEventListener('click', function() {
                        currentIdx = (currentIdx + 1) % flashSales.length;
                        showCampaign(currentIdx);
                    });

                    bindSlotBoxClick(campaignBlocks[0]);
                    const firstActiveSlot = campaignBlocks[0].querySelector('.flash-sale-slot-box.slot-active') ||
                        campaignBlocks[0].querySelector('.flash-sale-slot-box');
                    if (firstActiveSlot) firstActiveSlot.click();
                });
            </script>
        </section>
    @endif

    @foreach ($blocks as $block)
        <section class="container px-4 pt-4 mt-2 mt-sm-3 mt-lg-4 position-relative">
            <div class="d-flex justify-content-center mb-4">
                <h1 class="h1 pb-2 d-inline-block text-center">
                    <i class="fab fa-apple fa-lg me-2"></i>
                    {{ $block->title }}
                </h1>
            </div>
            <div class="position-relative">
                <div class="swiper product-slider"
                    data-swiper='{
                    "slidesPerView": 2,
                    "spaceBetween": 16,
                    "loop": false,
                    "navigation": {
                        "nextEl": ".product-next-{{ $block->id }}",
                        "prevEl": ".product-prev-{{ $block->id }}"
                    },
                    "breakpoints": {
                        "576": { "slidesPerView": 3 },
                        "768": { "slidesPerView": 4 },
                        "992": { "slidesPerView": 5 } 
                    }
                }'>
                    <div class="swiper-wrapper">
                        @forelse ($block->productVariants as $variant)
                            @php
                                $product = $variant->product;
                                // Sử dụng ảnh chính của biến thể, nếu không có thì dùng ảnh cover của sản phẩm
                                $imageToShow = $variant->primaryImage ?? $product->coverImage;
                                $mainImage = $imageToShow
                                    ? Storage::url($imageToShow->path)
                                    : asset('images/placeholder.jpg');
                                $isOnSale = $variant && $variant->sale_price && $variant->price > 0;
                                $discountPercent = $isOnSale
                                    ? round(100 - ($variant->sale_price / $variant->price) * 100)
                                    : 0;
                                // Lấy thuộc tính Dung lượng
                                $capacityAttr = $variant->attributeValues->firstWhere('attribute.name', 'Dung lượng');
                                $capacityValue = $capacityAttr ? $capacityAttr->value : '';
                            @endphp
                            <div class="swiper-slide p-2 h-100">
                                <div class="product-card bg-body rounded-7 border-0 h-100 py-4 position-relative">
                                    @if ($isOnSale && $discountPercent > 0)
                                        <div class="discount-badge">Giảm {{ $discountPercent }}%</div>
                                    @endif

                                    {{-- Ảnh sản phẩm --}}
                                    <div class="position-relative">
                                        <a href="{{ route('users.products.show', $variant->slug) }}">
                                            <div class="ratio" style="--cz-aspect-ratio: calc(200 / 180 * 100%)">
                                                <img src="{{ $mainImage }}"
                                                    alt="{{ $product->name }} {{ $capacityValue }}"
                                                    class="img-fluid rounded-3 shadow-sm"
                                                    style="object-fit:contain; width:100%; height:100%;">
                                            </div>
                                        </a>
                                    </div>

                                    {{-- Thông tin sản phẩm --}}
                                    <div class="px-3 pb-3 pt-2 text-center">
                                        <h3 class="fs-6 fw-bold text-truncate">
                                            <a href="{{ route('users.products.show', $variant->slug) }}"
                                                class="text-dark text-decoration-none">
                                                {{ $product->name }} {{ $capacityValue }}
                                            </a>
                                        </h3>
                                        <div class="text-primary fw-bold">
                                            @if ($isOnSale)
                                                {{ number_format($variant->sale_price) }}đ
                                                <del class="text-muted ms-1">{{ number_format($variant->price) }}đ</del>
                                            @else
                                                {{ number_format($variant->price) }}đ
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="swiper-slide">
                                <div class="col-12 text-center text-muted py-4">Chưa có sản phẩm</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    <section class="container py-5 bg-body-tertiary">
        <div class="d-flex justify-content-center mb-5">
            <h1 class="h1 text-center">
                <i class="ci-newspaper fa-lg me-2"></i>
                Bài viết nổi bật
            </h1>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-5"> {{-- g-5 = khoảng cách rộng hơn --}}
            @foreach ($featuredPosts->take(4) as $post)
                <div class="col d-flex">
                    <div class="post-card shadow-sm flex-fill d-flex flex-column">
                        <a href="{{ route('users.blogs.show', $post->slug) }}">
                            <img src="{{ $post->coverImage ? asset('storage/' . $post->coverImage->path) : asset('assets/users/img/default-thumbnail.jpg') }}"
                                alt="{{ $post->title }}" class="img-fluid w-100 post-card-img">
                        </a>
                        <div class="post-card-body flex-grow-1 d-flex flex-column">
                            <div class="post-meta mb-2">
                                {{ $post->published_at ? $post->published_at->format('H:i d/m/Y') : 'Chưa đăng' }}
                            </div>
                            <h3 class="post-title flex-grow-1">
                                <a href="{{ route('users.blogs.show', $post->slug) }}"
                                    class="text-dark text-decoration-none">
                                    {{ Str::limit($post->title, 60) }}
                                </a>
                            </h3>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center mt-5">
            <a class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" href="/blog">
                <span>Xem tất cả</span>
                <i class="ci-chevron-right fs-base ms-2"></i>
            </a>
        </div>
    </section>


    <style>
        .post-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .post-card-img {
            object-fit: cover;
            height: 200px;
        }

        .post-card-body {
            padding: 16px;
        }

        .post-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .post-title {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
        }
    </style>


@endsection
@push('scripts')
    <script>
        // Additional scripts for homepage if needed
    </script>
@endpush
