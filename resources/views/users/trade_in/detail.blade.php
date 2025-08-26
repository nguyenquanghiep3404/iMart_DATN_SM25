@extends('users.layouts.app')
@include('users.trade_in.css')
@section('content')
    <div class="container mx-auto p-4 md:p-8">
        <!-- Main Product Section -->
        <main class="bg-white p-4 sm:p-6 md:p-8 rounded-xl shadow-sm">
            <!-- Breadcrumb -->
            {{-- Breadcrumbs (Đường dẫn điều hướng) --}}
            <nav class="text-sm text-gray-500 mb-6" aria-label="breadcrumb">
                <div class="flex items-center space-x-2 text-base font-semibold">
                    <a href="{{ url('/') }}" class="text-blue-600 hover:underline">Trang chủ</a>
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('public.trade-in.index') }}" class="text-blue-600 hover:underline">Sản phẩm cũ</a>
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('public.trade-in.category', $category->slug) }}"
                        class="text-blue-600 hover:underline">{{ $category->name }}</a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-700">{{ $productName }}</span>
                </div>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:items-start">
                <!-- Image Gallery -->
                <div class="image-gallery lg:sticky top-8">
                    <div id="main-image-container" class="swiper gallery-top group relative cursor-pointer">
                        <div class="swiper-wrapper">
                            <!-- Swiper.js will generate slides here -->
                        </div>
                        <!-- Main Gallery Navigation Buttons -->
                        <div id="main-gallery-prev-btn"
                            class="absolute top-1/2 -translate-y-1/2 left-2 z-10 p-2 bg-white/70 rounded-full shadow-md hover:bg-white transition opacity-0 group-hover:opacity-100 cursor-pointer">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                                </path>
                            </svg>
                        </div>
                        <div id="main-gallery-next-btn"
                            class="absolute top-1/2 -translate-y-1/2 right-2 z-10 p-2 bg-white/70 rounded-full shadow-md hover:bg-white transition opacity-0 group-hover:opacity-100 cursor-pointer">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <div class="relative mt-2">
                        <div class="swiper gallery-thumbs">
                            <div class="swiper-wrapper" id="main-thumbnails-wrapper">
                                <!-- Swiper.js will generate thumbnail slides here -->
                            </div>
                        </div>
                        <div id="thumb-prev-btn" class="thumb-nav-btn thumb-prev-btn">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                                </path>
                            </svg>
                        </div>
                        <div id="thumb-next-btn" class="thumb-nav-btn thumb-next-btn">
                            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info flex flex-col">

                    <!-- Tên sản phẩm -->
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        {{ $tradeInItem->productVariant->product->name ?? 'Không rõ tên' }}
                        @foreach ($tradeInItem->productVariant->attributeValues as $attributeValue)
                            @if ($attributeValue->attribute->name !== 'Màu sắc')
                                {{ $attributeValue->value }}
                            @endif
                        @endforeach
                        @php
                            $color = $tradeInItem->productVariant->attributeValues->firstWhere(
                                'attribute.name',
                                'Màu sắc',
                            )?->value;
                        @endphp
                        @if ($color)
                            | {{ $color }}
                        @endif
                        | {{ $tradeInItem->type === 'used' ? 'Hàng đã qua sử dụng' : 'Hàng New 99%' }}
                    </h1>

                    <!-- Nhãn trạng thái -->
                    <div class="flex items-center flex-wrap gap-x-4 gap-y-2 mt-4">
                        <span
                            class="inline-flex items-center border border-blue-500 text-blue-600 text-xs font-semibold px-2.5 py-1 rounded-full w-fit">
                            {{ $tradeInItem->type === 'used' ? 'Sản phẩm đã qua sử dụng' : 'New 99%' }}
                        </span>
                        {{-- <button id="compare-btn"
                            class="flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
                            </svg>
                            So sánh
                        </button> --}}
                        <button id="favorite-btn"
                            class="flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-red-500 transition-colors">
                            <span id="favorite-icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.5l1.318-1.182a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.318a4.5 4.5 0 010-6.364z" />
                                </svg>
                            </span>
                            <span>Yêu thích</span>
                        </button>
                        <div class="relative">
                            <button id="share-btn"
                                class="flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                </svg>
                                <span>Chia sẻ</span>
                            </button>
                            <div id="share-popover"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20 hidden">
                                <a href="#"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Facebook</a>
                                <a href="#"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Twitter</a>
                                <button id="copy-link-btn"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sao
                                    chép link</button>
                            </div>
                        </div>
                    </div>

                    <!-- Biến thể -->
                    <div class="variants mt-6 flex items-center gap-x-8 gap-y-4 flex-wrap">
                        @foreach ($tradeInItem->productVariant->attributeValues as $attributeValue)
                            <div class="flex items-center gap-3">
                                <h4 class="font-medium text-gray-800">{{ $attributeValue->attribute->name }}:</h4>
                                <button
                                    class="flex items-center gap-2 px-3 py-2 border border-gray-400 rounded-lg text-sm font-semibold text-gray-800 bg-gray-50">
                                    @if ($attributeValue->attribute->name === 'Màu sắc' && $attributeValue->color_code)
                                        <span class="w-5 h-5 rounded-full border border-gray-500"
                                            style="background-color: {{ $attributeValue->color_code }};"></span>
                                    @endif
                                    <span>{{ $attributeValue->value }}</span>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <!-- Giá cả -->
                    <div class="price-block mt-6 space-y-4">
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <div class="flex items-baseline gap-3 flex-wrap mb-2">
                                <span class="text-3xl font-bold text-red-600">
                                    {{ number_format($tradeInItem->selling_price) }}₫
                                </span>
                                @if ($tradeInItem->productVariant->price)
                                    <span class="text-lg text-gray-500 line-through">
                                        {{ number_format($tradeInItem->productVariant->price) }}₫
                                    </span>
                                    @if ($tradeInItem->productVariant->price > $tradeInItem->selling_price)
                                        <span class="text-sm font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-md">
                                            -{{ round((($tradeInItem->productVariant->price - $tradeInItem->selling_price) / $tradeInItem->productVariant->price) * 100) }}%
                                        </span>
                                    @endif
                                @endif
                            </div>
                            @if ($tradeInItem->productVariant->price)
                                <p class="text-gray-700 text-sm">
                                    Giá sản phẩm mới: <span
                                        class="font-bold">{{ number_format($tradeInItem->productVariant->price) }}₫</span>
                                    | Tiết kiệm: <span
                                        class="font-bold text-red-600">{{ number_format($tradeInItem->productVariant->price - $tradeInItem->selling_price) }}₫</span>
                                </p>
                                @if ($tradeInItem->productVariant->product->slug)
                                    <a href="{{ route('users.products.show', ['slug' => $tradeInItem->productVariant->product->slug]) }}"
                                        class="text-blue-600 font-semibold hover:underline mt-1 inline-block text-sm">Xem
                                        sản phẩm mới &gt;</a>
                                @else
                                    <span class="text-gray-600 mt-1 inline-block text-sm">Sản phẩm mới không khả
                                        dụng</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- NEW AVAILABILITY SECTION -->
                    @if ($tradeInItem->storeLocation && $tradeInItem->storeLocation->name && $tradeInItem->storeLocation->address)
                        <div class="mt-6 p-3 border border-blue-200 bg-blue-50 rounded-lg">
                            <div class="flex items-center gap-2 text-sm text-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 flex-shrink-0"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p>Sản phẩm đang có hàng tại: <a href="#"
                                        class="font-semibold text-blue-600 hover:underline">{{ $tradeInItem->storeLocation->name }}
                                        - {{ $tradeInItem->storeLocation->address }}</a></p>
                            </div>
                        </div>
                    @endif


                    <!-- UPDATED WARRANTY SECTION -->
                    <div class="promotions mt-6">
                        <div class="p-4 bg-white border border-gray-300 rounded-lg text-sm text-gray-800">

                            <h3 class="font-semibold text-gray-800 mb-3">Thông tin sản phẩm - bảo hành</h3>
                            <ul class="space-y-2.5 text-sm text-gray-700 list-disc list-inside">
                                @if ($tradeInItem->condition_grade)
                                    @php
                                        $gradeDescriptions = [
                                            'A' => 'Như mới',
                                            'B' => 'Khá tốt',
                                            'C' => 'Bình thường',
                                        ];
                                        $gradeDescription =
                                            $gradeDescriptions[$tradeInItem->condition_grade] ?? 'Không xác định';
                                    @endphp
                                    <li>Tình trạng sản phẩm: {{ $gradeDescription }}</li>
                                @endif
                                @if ($tradeInItem->condition_description)
                                    <li>Mô tả chi tiết: {{ $tradeInItem->condition_description }}</li>
                                @endif
                                <li>Bảo hành: <span class="font-semibold">1 tháng tại iMart</span>.</li>
                                <li>Bảo hành tại iMart: Khách hàng đem sản phẩm đến hệ thống Thế Giới Di Động, Điện máy
                                    Xanh để bảo hành, <span class="font-bold text-red-600">KHÔNG ÁP DỤNG BẢO HÀNH TẠI
                                        HÃNG</span>.</li>
                                <li>Đổi trả tháng đầu tiên (phí 10%) <a href="#"
                                        class="text-blue-600 hover:underline font-semibold">Xem chính sách</a></li>
                                <li>Phụ kiện: Cáp Type C - Type C (Số IMEI trên hộp có thể không trùng với số IMEI trên máy
                                    ,Phụ kiện đi kèm có thể chính hãng hoặc thuộc iMart)</li>
                                <li>Không áp dụng bảo hành phụ kiện kèm theo</li>
                                <li>Sản phẩm đã sử dụng (hàng thu mua) là sản phẩm do iMart thu vào của Khách Hàng và đã qua
                                    kiểm duyệt.</li>
                                <li>Máy có thể đã qua bảo hành Hãng hoặc sửa chữa, thay thế linh kiện tương đương linh
                                    kiện Hãng.</li>
                                @if ($tradeInItem->imei_or_serial)
                                    @php
                                        $maskedImei = '***' . substr($tradeInItem->imei_or_serial, 3);
                                    @endphp
                                    <li>IMEI: {{ $maskedImei }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{--  --}}
                    <div class="mt-6">
                        <h4 class="font-medium text-gray-800 mb-2">Số lượng</h4>
                        <div
                            class="flex flex-wrap flex-sm-nowrap flex-md-wrap flex-lg-nowrap gap-3 gap-lg-2 gap-xl-3 mb-4">
                            <div class="count-input flex-shrink-0 order-sm-1">
                                <button type="button" class="btn btn-icon btn-lg" data-decrement
                                    aria-label="Giảm số lượng">
                                    <i class="ci-minus"></i>
                                </button>
                                <input type="number" class="form-control form-control-lg" name="quantity"
                                    id="quantity_input" value="1" min="1" max="1000">
                                <button type="button" class="btn btn-icon btn-lg" data-increment
                                    aria-label="Tăng số lượng">
                                    <i class="ci-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div id="main-cta-buttons" class="mt-auto pt-6 flex flex-col sm:flex-row gap-3">
                            <button id="add-to-cart-btn" data-variant-id="{{ $tradeInItem->productVariant->id }}"
                                data-quantity="1"
                                class="flex-1 w-full flex items-center justify-center gap-2 px-6 py-3.5 border-2 border-blue-600 text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.838-6.839a1.5 1.5 0 00-1.087-1.835H4.215" />
                                </svg>
                                THÊM VÀO GIỎ HÀNG
                            </button>
                            <button type="button" id="buy-now-btn"
                                class="flex-1 w-full px-6 py-4 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors">
                                MUA NGAY
                            </button>
                        </div>
                        {{--  --}}
                        <div class="text-sm text-gray-500 mt-4 text-center sm:text-left"><span class="font-semibold">Giao
                                hàng dự kiến:</span> Thứ Ba, 30/07 - Thứ Tư, 31/07.</div>
                    </div>
                </div>
        </main>
        <div class="mt-10 md:mt-12 space-y-10 md:space-y-12">
            <section class="bg-white p-6 md:p-8 rounded-xl shadow-sm">
                <div class="flex justify-center border-2 border-gray-200 rounded-xl p-1 mb-6 max-w-md mx-auto">
                    <button id="tab-desc-btn"
                        class="tab-button w-1/2 py-2.5 px-4 rounded-lg text-sm font-semibold text-gray-600">Bài viết đánh
                        giá</button>
                    <button id="tab-specs-btn"
                        class="tab-button w-1/2 py-2.5 px-4 rounded-lg text-sm font-semibold tab-active">Thông số kỹ
                        thuật</button>
                </div>

                <div>
                    <div id="tab-desc-content" class="tab-content hidden">
                        <div id="description-wrapper" class="description-content collapsed">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Đặc điểm nổi bật của
                                {{ $tradeInItem->productVariant->product->name ?? 'Sản phẩm' }}</h2>
                            <div class="prose max-w-none">
                                @if ($description)
                                    {!! $description !!}
                                @else
                                    <p class="text-gray-600 text-center">Hiện chưa có bài viết đánh giá cho sản phẩm này.
                                    </p>
                                @endif
                            </div>
                        </div>
                        @if ($description)
                            <div class="text-center mt-4">
                                <button id="read-more-btn" class="font-semibold text-blue-600 hover:text-blue-800">
                                    Xem thêm
                                </button>
                            </div>
                        @endif
                    </div>

                    <div id="tab-specs-content" class="tab-content">
                        <div class="space-y-3" id="specs-accordion">
                            @foreach ($specifications as $category => $specs)
                                <div>
                                    <button
                                        class="accordion-button w-full flex justify-between items-center p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        <span class="font-semibold text-gray-800">{{ $category }}</span>
                                        <svg class="accordion-icon w-5 h-5 text-gray-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div class="accordion-content">
                                        <div class="p-4 border border-t-0 border-gray-200 rounded-b-lg">
                                            <dl class="divide-y divide-gray-100">
                                                @foreach ($specs as $spec)
                                                    <div class="px-1 py-2 grid grid-cols-3 gap-4">
                                                        <dt class="text-sm font-medium text-gray-600">{{ $spec['name'] }}
                                                        </dt>
                                                        <dd class="text-sm text-gray-800 col-span-2">
                                                            @if ($spec['type'] === 'boolean')
                                                                {{ $spec['value'] === '1' ? 'Có' : 'Không' }}
                                                            @else
                                                                {{ $spec['value'] }}
                                                            @endif
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if ($specifications->isEmpty())
                                <p class="text-gray-600 text-center">Hiện chưa có thông số kỹ thuật cho sản phẩm này.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            @include('users.trade_in.comment')
            @include('users.trade_in.QandA_withAI')
            @include('users.trade_in.Products_similar')
        </div>
    </div>

    <!-- MODALS SECTION -->

    <div id="image-lightbox-modal"
        class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex-col items-center justify-center p-4 transition-opacity duration-300">
        <div class="absolute top-4 left-1/2 -translate-x-1/2 z-10 flex items-center gap-4 bg-black/50 p-2 rounded-full">
            <button id="lightbox-zoom-in" class="text-white hover:text-gray-300"><svg class="w-6 h-6" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                </svg></button>
            <button id="lightbox-zoom-out" class="text-white hover:text-gray-300"><svg class="w-6 h-6" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
                </svg></button>
            <button id="lightbox-fullscreen" class="text-white hover:text-gray-300"><svg class="w-6 h-6" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 0h-4m4 0l-5-5">
                    </path>
                </svg></button>
        </div>
        <button id="close-lightbox-btn"
            class="absolute top-4 right-6 text-white text-5xl leading-none z-10 hover:text-gray-300">&times;</button>
        <div class="relative w-full h-full flex flex-col items-center justify-center">
            <div class="relative flex-1 flex items-center justify-center w-full max-h-[80vh] mt-12 mb-24 overflow-hidden">
                <img id="lightbox-main-image" src="" class="max-h-full max-w-full object-contain rounded-lg">
                <button id="lightbox-prev-btn"
                    class="absolute left-4 sm:left-8 text-white p-2 rounded-full bg-black/40 hover:bg-black/70 transition-colors"><svg
                        class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg></button>
                <button id="lightbox-next-btn"
                    class="absolute right-4 sm:right-8 text-white p-2 rounded-full bg-black/40 hover:bg-black/70 transition-colors"><svg
                        class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg></button>
            </div>
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-black bg-opacity-20">
                <div class="max-w-4xl mx-auto">
                    <div class="text-white text-left mb-4">
                        <p id="lightbox-description" class="font-semibold text-lg"></p>
                        <p id="lightbox-counter" class="text-sm text-gray-300"></p>
                    </div>
                    <div id="lightbox-thumbnails" class="flex justify-center gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="user-info-modal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-transform duration-300 scale-95">
            <div class="flex justify-between items-center p-4 border-b flex-shrink-0">
                <h3 class="text-xl font-bold text-gray-900">Thông tin người gửi</h3>
                <button id="close-user-info-modal-btn"
                    class="text-gray-500 hover:text-gray-700 text-3xl leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4 flex-grow">
                <div class="flex gap-4">
                    <label class="flex items-center"><input type="radio" name="gender"
                            class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500" checked> <span
                            class="ml-2">Anh</span></label>
                    <label class="flex items-center"><input type="radio" name="gender"
                            class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500"> <span
                            class="ml-2">Chị</span></label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" placeholder="Nhập họ và tên"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <input type="tel" placeholder="Nhập số điện thoại"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <input type="email" placeholder="Nhập Email (nhận thông báo phản hồi)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="p-6 bg-white border-t flex-shrink-0">
                <label class="flex items-center text-sm text-gray-600">
                    <input id="terms-checkbox" type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2">Tôi đồng ý với điều khoản dịch vụ, chính sách thu thập và xử lý dữ liệu cá nhân
                        của Shop</span>
                </label>
                <button id="qna-complete-btn"
                    class="mt-4 w-full bg-gray-300 text-gray-500 font-semibold py-3 rounded-lg cursor-not-allowed"
                    disabled>Hoàn tất</button>
            </div>
        </div>
    </div>

    @include('users.trade_in.sticky-bar')
    @include('users.trade_in.script')
@endsection

@push('scripts')
    @php
        $images = $tradeInItem->images->sortBy('order');
        $primaryImage = $images->firstWhere('type', 'primary_image');
        $galleryImages = $primaryImage
            ? collect([$primaryImage])->merge($images->filter(fn($img) => $img->id !== $primaryImage->id))
            : $images;
    @endphp
    <!-- Swiper.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Main Application Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- GALLERY DATA FROM PHP ---
            const galleryData = [
                @foreach ($galleryImages as $image)
                    {
                        thumb: '{{ $image->url }}',
                        main: '{{ $image->url }}',
                        lightbox: '{{ $image->url }}',
                        description: '{{ $image->alt_text ?? 'Ảnh sản phẩm' }}',
                        type: 'image'
                    },
                @endforeach
                @if ($galleryImages->isEmpty())
                    {
                        thumb: 'https://placehold.co/120x120/f8f8f8/ccc?text=Không+có+ảnh',
                        main: 'https://placehold.co/600x600/f8f8f8/ccc?text=Không+có+ảnh',
                        lightbox: 'https://placehold.co/1200x1200/f8f8f8/ccc?text=Không+có+ảnh',
                        description: 'Không có ảnh sản phẩm',
                        type: 'image'
                    }
                @endif
            ];

            let currentImageIndex = 0;

            const mainImageContainer = document.getElementById('main-image-container');
            const mainThumbnailsWrapper = document.getElementById('main-thumbnails-wrapper');
            const mainImageWrapper = mainImageContainer.querySelector('.swiper-wrapper');

            const lightboxModal = document.getElementById('image-lightbox-modal');
            const closeLightboxBtn = document.getElementById('close-lightbox-btn');
            const lightboxMainImage = document.getElementById('lightbox-main-image');
            const lightboxPrevBtn = document.getElementById('lightbox-prev-btn');
            const lightboxNextBtn = document.getElementById('lightbox-next-btn');
            const lightboxDescription = document.getElementById('lightbox-description');
            const lightboxCounter = document.getElementById('lightbox-counter');
            const lightboxThumbnailsContainer = document.getElementById('lightbox-thumbnails');
            const lightboxZoomInBtn = document.getElementById('lightbox-zoom-in');
            const lightboxZoomOutBtn = document.getElementById('lightbox-zoom-out');
            const lightboxFullscreenBtn = document.getElementById('lightbox-fullscreen');

            function initializeGallery() {
                mainImageWrapper.innerHTML = '';
                mainThumbnailsWrapper.innerHTML = '';

                galleryData.forEach(item => {
                    const mainSlide = document.createElement('div');
                    mainSlide.className = 'swiper-slide';
                    mainSlide.innerHTML =
                        `<img src="${item.main}" alt="${item.description}" class="w-full h-auto object-contain rounded-lg" onerror="this.onerror=null;this.src='https://placehold.co/600x600/f8f8f8/ccc?text=Image+Error';">`;
                    mainImageWrapper.appendChild(mainSlide);

                    const thumbSlide = document.createElement('div');
                    thumbSlide.className = 'swiper-slide relative';
                    let thumbContent =
                        `<img src="${item.thumb}" alt="Thumbnail for ${item.description}" onerror="this.onerror=null;this.src='https://placehold.co/120x120/f8f8f8/ccc?text=Error';">`;
                    thumbSlide.innerHTML = thumbContent;
                    mainThumbnailsWrapper.appendChild(thumbSlide);
                });

                var galleryThumbs = new Swiper('.gallery-thumbs', {
                    spaceBetween: 10,
                    slidesPerView: 5,
                    freeMode: true,
                    watchSlidesProgress: true,
                    navigation: {
                        nextEl: '#thumb-next-btn',
                        prevEl: '#thumb-prev-btn',
                    },
                });

                var galleryTop = new Swiper('.gallery-top', {
                    spaceBetween: 10,
                    navigation: {
                        nextEl: '#main-gallery-next-btn',
                        prevEl: '#main-gallery-prev-btn',
                    },
                    thumbs: {
                        swiper: galleryThumbs,
                    },
                });

                galleryTop.on('click', function() {
                    openLightbox(galleryTop.activeIndex);
                });
            }

            let isZoomed = false;
            let isPanning = false;
            let startX, startY;
            let currentTranslateX = 0;
            let currentTranslateY = 0;

            function resetZoomState() {
                isZoomed = false;
                isPanning = false;
                currentTranslateX = 0;
                currentTranslateY = 0;
                lightboxMainImage.style.transition = 'transform 0.3s ease';
                lightboxMainImage.style.transform = 'scale(1) translate(0, 0)';
                lightboxMainImage.style.cursor = 'zoom-in';
            }

            function zoomIn() {
                isZoomed = true;
                lightboxMainImage.style.transition = 'transform 0.3s ease';
                lightboxMainImage.style.transform = 'scale(2.5)';
                lightboxMainImage.style.cursor = 'grab';
            }

            function zoomOut() {
                resetZoomState();
            }

            function updateLightboxView() {
                const item = galleryData[currentImageIndex];
                lightboxMainImage.src = item.lightbox;
                lightboxDescription.textContent = item.description;
                lightboxCounter.textContent = `${currentImageIndex + 1} / ${galleryData.length}`;

                resetZoomState();

                const lightboxThumbs = lightboxThumbnailsContainer.querySelectorAll('img');
                lightboxThumbs.forEach((thumb, i) => {
                    thumb.classList.toggle('ring-2', i === currentImageIndex);
                    thumb.classList.toggle('ring-white', i === currentImageIndex);
                    thumb.classList.toggle('opacity-60', i !== currentImageIndex);
                });
            }

            function openLightbox(index) {
                currentImageIndex = index;
                populateLightboxThumbnails();
                updateLightboxView();
                lightboxModal.classList.remove('hidden');
                lightboxModal.classList.add('flex');
            }

            function closeLightbox() {
                lightboxModal.classList.add('hidden');
                lightboxModal.classList.remove('flex');
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
                resetZoomState();
            }

            function showNextImage() {
                currentImageIndex = (currentImageIndex + 1) % galleryData.length;
                updateLightboxView();
            }

            function showPrevImage() {
                currentImageIndex = (currentImageIndex - 1 + galleryData.length) % galleryData.length;
                updateLightboxView();
            }

            function populateLightboxThumbnails() {
                if (lightboxThumbnailsContainer.children.length > 0) return;
                galleryData.forEach((item, index) => {
                    const img = document.createElement('img');
                    img.src = item.thumb;
                    img.className =
                        'w-16 h-16 object-cover rounded-md cursor-pointer hover:opacity-100 transition-opacity';
                    img.onclick = () => {
                        currentImageIndex = index;
                        updateLightboxView();
                    };
                    lightboxThumbnailsContainer.appendChild(img);
                });
            }

            closeLightboxBtn.addEventListener('click', closeLightbox);
            lightboxNextBtn.addEventListener('click', showNextImage);
            lightboxPrevBtn.addEventListener('click', showPrevImage);
            lightboxZoomInBtn.addEventListener('click', zoomIn);
            lightboxZoomOutBtn.addEventListener('click', zoomOut);

            lightboxMainImage.addEventListener('dblclick', (e) => {
                e.preventDefault();
                if (isZoomed) zoomOut();
                else zoomIn();
            });

            lightboxMainImage.addEventListener('mousedown', (e) => {
                if (!isZoomed || isPanning) return;
                e.preventDefault();
                isPanning = true;
                startX = e.clientX - currentTranslateX;
                startY = e.clientY - currentTranslateY;
                lightboxMainImage.style.cursor = 'grabbing';
                lightboxMainImage.style.transition = 'none';
            });

            lightboxMainImage.addEventListener('mousemove', (e) => {
                if (!isPanning) return;
                e.preventDefault();
                currentTranslateX = e.clientX - startX;
                currentTranslateY = e.clientY - startY;
                lightboxMainImage.style.transform =
                    `translate(${currentTranslateX}px, ${currentTranslateY}px) scale(2.5)`;
            });

            const endPan = (e) => {
                if (!isPanning) return;
                isPanning = false;
                lightboxMainImage.style.cursor = 'grab';
            };

            lightboxMainImage.addEventListener('mouseup', endPan);
            lightboxMainImage.addEventListener('mouseleave', endPan);

            lightboxFullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    lightboxModal.requestFullscreen().catch(err => {
                        console.error(
                            `Error attempting to enable full-screen mode: ${err.message} (${err.name})`
                        );
                    });
                } else {
                    document.exitFullscreen();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (lightboxModal.classList.contains('hidden')) return;
                if (e.key === 'ArrowRight') showNextImage();
                if (e.key === 'ArrowLeft') showPrevImage();
                if (e.key === 'Escape') closeLightbox();
            });

            const accordionButtons = document.querySelectorAll('.accordion-button');
            console.log('Accordion buttons found:', accordionButtons.length);

            accordionButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const content = button.nextElementSibling;
                    const icon = button.querySelector('.accordion-icon');
                    console.log('Button clicked:', button, 'Content:', content, 'ScrollHeight:',
                        content?.scrollHeight);

                    if (!content || !icon) {
                        console.warn('Invalid accordion structure:', button);
                        return;
                    }

                    const isOpening = !content.style.maxHeight;

                    accordionButtons.forEach(otherButton => {
                        if (otherButton !== button) {
                            const otherContent = otherButton.nextElementSibling;
                            const otherIcon = otherButton.querySelector('.accordion-icon');
                            if (otherContent && otherIcon) {
                                otherContent.style.maxHeight = null;
                                otherIcon.classList.remove('rotate-180');
                            }
                        }
                    });

                    if (isOpening) {
                        content.style.maxHeight = (content.scrollHeight || 100) + 'px';
                        icon.classList.add('rotate-180');
                    } else {
                        content.style.maxHeight = null;
                        icon.classList.remove('rotate-180');
                    }
                });
            });

            function openFirstAccordion() {
                const firstButton = document.querySelector('#specs-accordion .accordion-button');
                if (firstButton) {
                    const content = firstButton.nextElementSibling;
                    const icon = firstButton.querySelector('.accordion-icon');
                    console.log('Opening first accordion, ScrollHeight:', content.scrollHeight);
                    if (content.scrollHeight > 0) {
                        content.style.maxHeight = content.scrollHeight + 'px';
                        icon.classList.add('rotate-180');
                    } else {
                        console.warn('First accordion content has no height, retrying...');
                        setTimeout(() => {
                            content.style.maxHeight = (content.scrollHeight || 100) + 'px';
                            icon.classList.add('rotate-180');
                        }, 100);
                    }
                } else {
                    console.warn('No accordion buttons found');
                }
            }

            const tabDescBtn = document.getElementById('tab-desc-btn');
            const tabSpecsBtn = document.getElementById('tab-specs-btn');
            const tabDescContent = document.getElementById('tab-desc-content');
            const tabSpecsContent = document.getElementById('tab-specs-content');
            const descriptionWrapper = document.getElementById('description-wrapper');
            const readMoreBtn = document.getElementById('read-more-btn');

            tabSpecsContent.style.display = 'block';
            tabDescContent.style.display = 'none';
            tabSpecsBtn.classList.add('tab-active');
            tabDescBtn.classList.remove('tab-active');

            tabDescBtn.addEventListener('click', () => {
                tabDescContent.style.display = 'block';
                tabSpecsContent.style.display = 'none';
                tabDescBtn.classList.add('tab-active');
                tabSpecsBtn.classList.remove('tab-active');
            });

            tabSpecsBtn.addEventListener('click', () => {
                tabSpecsContent.style.display = 'block';
                tabDescContent.style.display = 'none';
                tabSpecsBtn.classList.add('tab-active');
                tabDescBtn.classList.remove('tab-active');
            });

            readMoreBtn.addEventListener('click', () => {
                descriptionWrapper.classList.toggle('collapsed');
                readMoreBtn.textContent = descriptionWrapper.classList.contains('collapsed') ? 'Xem thêm' :
                    'Thu gọn';
            });

            // const compareBtn = document.getElementById('compare-btn');
            // compareBtn.addEventListener('click', () => {
            //     alert('Chức năng so sánh sẽ được tích hợp sau!');
            // });

            const favoriteBtn = document.getElementById('favorite-btn');
            const favoriteIconContainer = document.getElementById('favorite-icon-container');
            const outlineIcon =
                `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.5l1.318-1.182a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.318a4.5 4.5 0 010-6.364z" /></svg>`;
            const solidIcon =
                `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" /></svg>`;
            favoriteBtn.addEventListener('click', () => {
                const isFavorited = favoriteBtn.classList.toggle('favorited');
                favoriteIconContainer.innerHTML = isFavorited ? solidIcon : outlineIcon;
            });

            const shareBtn = document.getElementById('share-btn');
            const sharePopover = document.getElementById('share-popover');
            const copyLinkBtn = document.getElementById('copy-link-btn');
            shareBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                sharePopover.classList.toggle('hidden');
            });
            copyLinkBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    copyLinkBtn.textContent = 'Đã sao chép!';
                    setTimeout(() => {
                        copyLinkBtn.textContent = 'Sao chép link';
                    }, 2000);
                });
            });
            document.addEventListener('click', (event) => {
                if (!shareBtn.contains(event.target) && !sharePopover.contains(event.target)) {
                    sharePopover.classList.add('hidden');
                }
            });

            const bundleCheckboxes = document.querySelectorAll('.bundle-checkbox');
            const bundleTotalPriceEl = document.getElementById('bundle-total-price');
            const basePrice = {{ $tradeInItem->selling_price }};

            function calculateBundleTotal() {
                let total = basePrice;
                bundleCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        total += parseInt(checkbox.dataset.price);
                    }
                });
                bundleTotalPriceEl.textContent = total.toLocaleString('vi-VN') + '₫';
            }
            bundleCheckboxes.forEach(checkbox => checkbox.addEventListener('change', calculateBundleTotal));

            const userInfoModal = document.getElementById('user-info-modal');
            const closeUserInfoModalBtn = document.getElementById('close-user-info-modal-btn');
            closeUserInfoModalBtn.addEventListener('click', () => hideModal('user-info-modal'));
            userInfoModal.addEventListener('click', (event) => {
                if (event.target === userInfoModal) hideModal('user-info-modal');
            });

            const termsCheckbox = document.getElementById('terms-checkbox');
            const qnaCompleteBtn = document.getElementById('qna-complete-btn');
            termsCheckbox.addEventListener('change', () => {
                qnaCompleteBtn.disabled = !termsCheckbox.checked;
                qnaCompleteBtn.classList.toggle('bg-gray-300', !termsCheckbox.checked);
                qnaCompleteBtn.classList.toggle('text-gray-500', !termsCheckbox.checked);
                qnaCompleteBtn.classList.toggle('cursor-not-allowed', !termsCheckbox.checked);
                qnaCompleteBtn.classList.toggle('bg-blue-600', termsCheckbox.checked);
                qnaCompleteBtn.classList.toggle('text-white', termsCheckbox.checked);
                qnaCompleteBtn.classList.toggle('hover:bg-blue-700', termsCheckbox.checked);
            });

            const qnaData = [{
                    q: 'Sản phẩm này có hỗ trợ eSIM không?',
                    a: 'Chào bạn, iPhone 15 Pro Max bản VN/A chính hãng hỗ trợ 1 SIM vật lý và nhiều eSIM nhé.'
                },
                {
                    q: 'Chế độ bảo hành như thế nào?',
                    a: 'Sản phẩm được bảo hành chính hãng 12 tháng tại các trung tâm bảo hành uỷ quyền của Apple trên toàn quốc ạ.'
                },
                {
                    q: 'Phụ kiện đi kèm có những gì?',
                    a: 'Dạ trong hộp sản phẩm có máy, cáp sạc USB-C và sách hướng dẫn sử dụng ạ.'
                },
                {
                    q: 'Máy có chống nước không?',
                    a: 'Chào bạn, iPhone 15 Pro Max có chuẩn kháng nước, bụi IP68, có thể chịu được ở độ sâu tối đa 6 mét trong tối đa 30 phút ạ.'
                },
                {
                    q: 'Cổng sạc Type-C có nhanh hơn Lightning không?',
                    a: 'Dạ, cổng USB-C trên iPhone 15 Pro và Pro Max hỗ trợ tốc độ USB 3, nhanh hơn đến 20 lần so với cổng Lightning (USB 2.0) khi truyền dữ liệu bằng cáp tương thích ạ.'
                },
            ];

            const reviewData = [{
                    name: 'Nguyễn V. An',
                    avatar: 'https://placehold.co/40x40/7e22ce/ffffff?text=N',
                    text: 'Sản phẩm tuyệt vời, đúng hàng chính hãng. Giao hàng nhanh, đóng gói cẩn thận. Máy mượt, pin trâu, chụp ảnh siêu nét. Rất đáng tiền!',
                    images: ['https://placehold.co/80x80/d0d0d0/333?text=Ảnh+thật',
                        'https://placehold.co/80x80/c0c0c0/333?text=Ảnh+thật'
                    ],
                    date: '2 ngày trước'
                },
                {
                    name: 'Trần B. Tuấn',
                    avatar: 'https://placehold.co/40x40/34d399/ffffff?text=T',
                    text: 'Máy dùng tốt, tuy nhiên thời lượng pin không như kỳ vọng của mình lắm. Camera chụp đêm khá ổn. Màn hình ProMotion rất mượt.',
                    images: [],
                    date: '1 tuần trước'
                },
                {
                    name: 'Lê Thị Cẩm',
                    avatar: 'https://placehold.co/40x40/f87171/ffffff?text=C',
                    text: 'Màu Titan tự nhiên ở ngoài đẹp hơn trong ảnh nhiều. Shop tư vấn nhiệt tình. Sẽ ủng hộ lần sau.',
                    images: [],
                    date: '3 tuần trước'
                }
            ];

            const askAiBtn = document.getElementById('ask-ai-btn');
            const aiAnswerContainer = document.getElementById('ai-answer-container');
            const qnaTextarea = document.getElementById('qna-textarea');

            async function callGemini(prompt) {
                const apiKey = "";
                const apiUrl =
                    `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${apiKey}`;
                const payload = {
                    contents: [{
                        role: "user",
                        parts: [{
                            text: prompt
                        }]
                    }]
                };
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    if (!response.ok) throw new Error(`API call failed with status: ${response.status}`);
                    const result = await response.json();
                    if (result.candidates?.[0]?.content?.parts?.[0]?.text) {
                        return result.candidates[0].content.parts[0].text;
                    }
                    console.error("Unexpected API response structure:", result);
                    return "Rất tiếc, tôi không thể tạo phản hồi vào lúc này.";
                } catch (error) {
                    console.error("Error calling Gemini API:", error);
                    return "Đã xảy ra lỗi khi kết nối với AI. Vui lòng thử lại sau.";
                }
            }

            function formatAiQnaAnswer(text) {
                const parts = text.split('\n').filter(p => p.trim() !== '');
                let html = `<p class="mb-3">${parts[0]}</p><ul class="space-y-2">`;
                parts.slice(1).forEach(part => {
                    const itemText = part.replace(/^\s*[\*\-]\s*/, '').replace(/\*\*(.*?)\*\*/g,
                        '<span class="font-semibold text-gray-800">$1</span>');
                    html +=
                        `<li class="flex items-start gap-2 text-gray-700"><svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg><span>${itemText}</span></li>`;
                });
                return html + '</ul>';
            }

            askAiBtn.addEventListener('click', async () => {
                const question = qnaTextarea.value.trim();
                if (!question) {
                    alert('Vui lòng nhập câu hỏi của bạn.');
                    return;
                }
                aiAnswerContainer.innerHTML =
                    '<div class="flex items-center gap-2 text-gray-600"><div class="loader"></div><span>AI đang suy nghĩ, vui lòng chờ...</span></div>';
                aiAnswerContainer.classList.remove('hidden');
                const productTitle = document.querySelector('h1').innerText;
                const productDescription = document.querySelector('#description-wrapper')?.innerText ||
                    '';
                const promotions = Array.from(document.querySelectorAll('.promotions li')).map(li => li
                    .innerText).join(', ');
                const specs = Array.from(document.querySelectorAll('#specs-accordion dl')).map(dl => dl
                    .innerText).join('\n');
                const context =
                    `Thông tin sản phẩm: ${productTitle}.\nMô tả: ${productDescription}\nThông số kỹ thuật: ${specs}\nKhuyến mãi: ${promotions}\nCác câu hỏi đã có: ${JSON.stringify(qnaData)}`;
                const prompt =
                    `Bạn là một trợ lý bán hàng. Dựa vào thông tin sau:\n\n${context}\n\nHãy trả lời câu hỏi của khách hàng ngắn gọn, thân thiện. Chỉ sử dụng thông tin được cung cấp. Nếu không có thông tin, hãy nói rằng bạn không có thông tin đó. Định dạng câu trả lời với một câu chào và các gạch đầu dòng.\n\nCâu hỏi: "${question}"`;
                const answer = await callGemini(prompt);
                aiAnswerContainer.innerHTML = formatAiQnaAnswer(answer);
            });

            const reviewListEl = document.getElementById('review-list');
            const reviewPaginationEl = document.getElementById('review-pagination');
            let reviewCurrentPage = 1;
            const reviewItemsPerPage = 2;

            function renderReviews() {
                reviewListEl.innerHTML = '';
                const start = (reviewCurrentPage - 1) * reviewItemsPerPage;
                const paginatedItems = reviewData.slice(start, start + reviewItemsPerPage);

                for (const item of paginatedItems) {
                    const reviewItem = document.createElement('div');
                    reviewItem.className = 'border-b border-gray-200 py-4 review-item';
                    let imagesHTML = item.images.length > 0 ?
                        `<div class="flex gap-2 mt-2">${item.images.map(src => `<img src="${src}" alt="Review Image" class="w-20 h-20 rounded-md object-cover">`).join('')}</div>` :
                        '';
                    reviewItem.innerHTML = ` 
                        <div class="flex items-start gap-3"> 
                            <img src="${item.avatar}" alt="Avatar" class="w-10 h-10 rounded-full"> 
                            <div> 
                                <p class="font-semibold text-gray-800">${item.name}</p> 
                                <p class="text-sm text-gray-600 review-text mt-1">${item.text}</p> 
                                ${imagesHTML} 
                                <div class="text-xs text-gray-500 mt-2"><span>${item.date}</span></div> 
                            </div> 
                        </div> 
                    `;
                    reviewListEl.appendChild(reviewItem);
                }
            }

            function setupReviewPagination() {
                reviewPaginationEl.innerHTML = '';
                const pageCount = Math.ceil(reviewData.length / reviewItemsPerPage);
                for (let i = 1; i <= pageCount; i++) {
                    const btn = document.createElement('button');
                    btn.innerText = i;
                    btn.className =
                        `px-3 py-1 mx-1 rounded-md text-sm font-medium ${i === reviewCurrentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`;
                    btn.addEventListener('click', () => {
                        reviewCurrentPage = i;
                        renderReviews();
                        setupReviewPagination();
                    });
                    reviewPaginationEl.appendChild(btn);
                }
            }

            const qnaListEl = document.getElementById('qna-list');
            const qnaPaginationEl = document.getElementById('qna-pagination');
            let qnaCurrentPage = 1;
            const qnaItemsPerPage = 3;

            function renderQna() {
                qnaListEl.innerHTML = '';
                const start = (qnaCurrentPage - 1) * qnaItemsPerPage;
                const paginatedItems = qnaData.slice(start, start + qnaItemsPerPage);
                for (const item of paginatedItems) {
                    const qnaItem = document.createElement('div');
                    qnaItem.className = 'faq-item border-t border-gray-200 pt-4';
                    qnaItem.innerHTML =
                        `<p class="font-semibold text-gray-800">Q: ${item.q}</p><p class="text-sm text-gray-600 mt-1 pl-4 border-l-2 border-blue-200">A: ${item.a}</p>`;
                    qnaListEl.appendChild(qnaItem);
                }
            }

            function setupQnaPagination() {
                qnaPaginationEl.innerHTML = '';
                const pageCount = Math.ceil(qnaData.length / qnaItemsPerPage);
                for (let i = 1; i <= pageCount; i++) {
                    const btn = document.createElement('button');
                    btn.innerText = i;
                    btn.className =
                        `px-3 py-1 mx-1 rounded-md text-sm font-medium ${i === qnaCurrentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`;
                    btn.addEventListener('click', () => {
                        qnaCurrentPage = i;
                        renderQna();
                        setupQnaPagination();
                    });
                    qnaPaginationEl.appendChild(btn);
                }
            }

            const stickyBar = document.getElementById('sticky-bar');
            const mainCtaButtons = document.getElementById('main-cta-buttons');

            const scrollObserver = new IntersectionObserver((entries) => {
                stickyBar.classList.toggle('translate-y-full', entries[0].isIntersecting);
            }, {
                threshold: 0
            });

            if (mainCtaButtons) {
                scrollObserver.observe(mainCtaButtons);
            }

            // --- INITIALIZE ALL ---
            initializeGallery();
            calculateBundleTotal();
            renderQna();
            setupQnaPagination();
            renderReviews();
            setupReviewPagination();
            updateStickyBarInfo();
        });
    </script>
@endpush
