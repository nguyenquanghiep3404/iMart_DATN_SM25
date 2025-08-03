<div class="product-info flex flex-col">
    @php
        $productName = $product->name; // Ví dụ: "iPhone 16"
        $dungLuong = $initialVariantAttributes['Dung lượng lưu trữ'] ?? '';
        $mauSac = $initialVariantAttributes['Màu sắc'] ?? '';
    @endphp
    <!-- CDN Toastr -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @include('users.cart.layout.partials.css')
    <h1 id="product-title" class="text-2xl md:text-3xl font-bold text-gray-900">
        {{ $productName }} {{ $dungLuong }} {{ $mauSac }}
    </h1>
    <div class="flex items-center flex-wrap gap-4 mt-2">
        <span
            class="inline-flex items-center bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full w-fit">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 mr-1">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                    clip-rule="evenodd" />
            </svg>
            Chính hãng
        </span>
        <button id="compare-btn"
            class="flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-black hover:bg-gray-100 rounded-md"
            data-default-variant-id="{{ $defaultVariant->id }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path
                    d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
            </svg>
            So sánh
        </button>
        <form action="{{ route('wishlist.add') }}" method="POST" id="wishlist-form">
            @csrf
            <input type="hidden" name="product_variant_id" id="wishlist-variant-id">
            <input type="hidden" name="image" id="wishlist-variant-image">
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variant_key" id="wishlist-variant-key">
            <!-- Nút yêu thích -->
            <button type="submit" id="wishlist-submit-btn"
                class="flex items-center gap-1.5 text-sm font-semibold transition-colors
    {{ $wishlistVariantIds ? 'text-red-500' : 'text-gray-500 hover:text-red-500' }}">
                <span id="wishlist-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.5l1.318-1.182a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.318a4.5 4.5 0 010-6.364z" />
                    </svg>
                </span>
                <span>Yêu thích</span>
            </button>

        </form>


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
            <div id="share-popover" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl z-20 hidden">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Facebook</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Twitter</a>
                <button id="copy-link-btn"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sao chép
                    link</button>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 mt-3 text-sm text-gray-600">
        <div class="flex items-center gap-2">
            {{-- Điểm trung bình --}}
            <span class="font-bold text-yellow-500 text-base">
                {{ number_format($product->average_rating, 1) }}
            </span>

            {{-- Sao đánh giá --}}
            <div class="flex text-yellow-400">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($product->average_rating >= $i)
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path
                                d="M10 15l-5.878 3.09L5.82 12.18 1.64 8.09l6.084-.878L10 2l2.276 5.212 6.084.878-4.18 4.09 1.698 5.91z" />
                        </svg>
                    @elseif ($product->average_rating >= $i - 0.5)
                        {{-- Sao nửa --}}
                        <svg class="w-4 h-4" viewBox="0 0 20 20">
                            <defs>
                                <linearGradient id="half-grad" x1="0" x2="1" y1="0"
                                    y2="0">
                                    <stop offset="50%" stop-color="currentColor" />
                                    <stop offset="50%" stop-color="#e5e7eb" />
                                </linearGradient>
                            </defs>
                            <path
                                d="M10 15l-5.878 3.09L5.82 12.18 1.64 8.09l6.084-.878L10 2l2.276 5.212 6.084.878-4.18 4.09 1.698 5.91z"
                                fill="url(#half-grad)" />
                        </svg>
                    @else
                        <svg class="w-4 h-4 fill-current text-gray-200" viewBox="0 0 20 20">
                            <path
                                d="M10 15l-5.878 3.09L5.82 12.18 1.64 8.09l6.084-.878L10 2l2.276 5.212 6.084.878-4.18 4.09 1.698 5.91z" />
                        </svg>
                    @endif
                @endfor
            </div>

            {{-- Số lượt đánh giá --}}
            <div class="h-4 w-px bg-gray-300 mx-2"></div>
            <span><span class="font-semibold">{{ number_format($totalReviews) }}</span> đánh giá</span>
        </div>


        <div class="h-4 w-px bg-gray-300"></div><span><span class="font-semibold">4.6k</span> đã bán</span>
    </div>
    <div class="variants mt-6 space-y-4" tabindex="-1">
        {{-- Thuộc tính KHÔNG phải "Màu sắc" --}}
        @foreach ($attributes as $attrName => $attrValues)
            @continue(strtolower($attrName) === 'màu sắc')

            @php
                $inputName = strtolower(str_replace(' ', '-', $attrName)) . '-options';
            @endphp
            <div>
                <h4 class="font-medium text-gray-800 mb-2">{{ $attrName }}</h4>
                <div class="flex gap-2 flex-wrap">
                    @foreach ($attrValues as $attrValue)
                        @php
                            $inputId = $inputName . '-' . $attrValue->id;
                            $isColor = $attrValue->attribute->display_type === 'color_swatch' && $attrValue->meta;
                            $isChecked =
                                isset($initialVariantAttributes[$attrName]) &&
                                $initialVariantAttributes[$attrName] === $attrValue->value;
                        @endphp

                        <label for="{{ $inputId }}"
                            class="option-container px-4 py-2 border-2 rounded-lg text-sm font-semibold cursor-pointer
                    {{ $isChecked ? 'variant-selected' : 'border-gray-300 text-gray-700 hover:border-blue-500' }}"
                            data-attr-name="{{ $attrName }}" data-attr-value="{{ $attrValue->value }}">
                            <input type="radio" name="{{ $inputName }}" id="{{ $inputId }}"
                                value="{{ $attrValue->value }}" data-attr-name="{{ $attrName }}" class="hidden"
                                {{ $isChecked ? 'checked' : '' }}>
                            {{ $attrValue->value }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Màu sắc luôn ở dưới --}}
        @if (isset($attributes['Màu sắc']))
            @php
                $attrName = 'Màu sắc';
                $attrValues = $attributes['Màu sắc'];
                $inputName = 'mau-sac-options';
            @endphp
            <div>
                <h4 class="font-medium text-gray-800 mb-2">
                    Màu sắc:
                    <span id="selected-color-name" class="font-bold">
                        {{ $initialVariantAttributes['Màu sắc'] ?? '' }}
                    </span>
                </h4>
                <div class="flex gap-2 flex-wrap">
                    @foreach ($attrValues as $attrValue)
                        @php
                            $inputId = $inputName . '-' . $attrValue->id;
                            $isChecked =
                                isset($initialVariantAttributes[$attrName]) &&
                                $initialVariantAttributes[$attrName] === $attrValue->value;
                        @endphp

                        <label for="{{ $inputId }}"
                            class="option-container w-8 h-8 rounded-full border ring-2 {{ $isChecked ? 'ring-blue-500' : 'ring-transparent' }} ring-offset-1 cursor-pointer"
                            title="{{ $attrValue->value }}" style="background-color: {{ $attrValue->meta }};"
                            data-attr-name="{{ $attrName }}" data-attr-value="{{ $attrValue->value }}">
                            <input type="radio" name="{{ $inputName }}" id="{{ $inputId }}"
                                value="{{ $attrValue->value }}" data-attr-name="{{ $attrName }}" class="hidden"
                                {{ $isChecked ? 'checked' : '' }}>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

    </div>


    @php
        use Carbon\Carbon;
        $variant = $defaultVariant ?? $product->variants->first();
        $now = now();
        $salePrice = (int) $variant->sale_price;
        $originalPrice = (int) $variant->price;

        // Kiểm tra đủ điều kiện Flash Sale
        $hasFlashTime =
            $variant->sale_price_starts_at instanceof Carbon && $variant->sale_price_ends_at instanceof Carbon;
        $isFlashSale = false;
        if ($salePrice && $hasFlashTime) {
            $isFlashSale = $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
        }

        // Nếu không phải Flash Sale mà vẫn có sale_price < original => là Sale thường
        $isSale = !$isFlashSale && $salePrice && $salePrice < $originalPrice;

        $discountPercent = $isFlashSale || $isSale ? round(100 - ($salePrice / $originalPrice) * 100) : 0;
    @endphp



    <div id="price-section" class="mt-4">
        @if ($isFlashSale)
            <!-- 🔥 Flash Sale Block -->
            <div id="flash-sale-block" class="bg-orange-500 text-white p-4 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold">⚡ Online Giá Rẻ Quá</p>
                        <p id="product-price" class="text-3xl font-bold">
                            {{ number_format($salePrice) }}₫
                        </p>
                        <p class="text-sm opacity-80">
                            <span id="original-price"
                                class="line-through">{{ number_format($originalPrice) }}₫</span>
                            <span id="discount-percent">
                                ({{ $discountPercent > 0 ? "-$discountPercent%" : '' }})
                            </span>
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-semibold">Kết thúc sau</p>
                        <div id="countdown-timer" data-end-time="{{ $variant->sale_price_ends_at }}"
                            class="flex gap-1 mt-1 text-lg">
                            <span id="hours" class="timer-box">00</span>:
                            <span id="minutes" class="timer-box">00</span>:
                            <span id="seconds" class="timer-box">00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💡 Normal Price Block (ẩn đi ban đầu) -->
            <div id="normal-price-block" class="price-block bg-gray-100 p-4 rounded-lg hidden">

                <div class="flex items-baseline gap-3">
                    <span id="product-price" class="text-3xl font-bold text-red-600">
                        {{ number_format($isSale ? $salePrice : $originalPrice) }}₫
                    </span>

                    @if ($isSale)
                        <span id="original-price" class="text-lg text-gray-500 line-through">
                            {{ number_format($originalPrice) }}₫
                        </span>
                        <span id="discount-percent"
                            class="bg-red-200 text-red-800 text-sm font-semibold px-2 py-0.5 rounded-md">
                            -{{ $discountPercent }}%
                        </span>
                    @else
                        <span id="original-price" class="text-lg text-gray-500 line-through hidden"></span>
                        <span id="discount-percent" class="hidden"></span>
                    @endif
                </div>
            </div>
        @else
            <!-- ✅ Normal Price Block (hiển thị khi không phải Flash Sale) -->
            <div id="normal-price-block" class="price-block bg-gray-100 p-4 rounded-lg">

                <div class="flex items-baseline gap-3">
                    <span id="product-price" class="text-3xl font-bold text-red-600">
                        {{ number_format($isSale ? $salePrice : $originalPrice) }}₫
                    </span>

                    @if ($isSale)
                        <span id="original-price" class="text-lg text-gray-500 line-through">
                            {{ number_format($originalPrice) }}₫
                        </span>
                        <span id="discount-percent"
                            class="bg-red-200 text-red-800 text-sm font-semibold px-2 py-0.5 rounded-md">
                            -{{ $discountPercent }}%
                        </span>
                    @else
                        <span id="original-price" class="text-lg text-gray-500 line-through hidden"></span>
                        <span id="discount-percent" class="hidden"></span>
                    @endif
                </div>
            </div>
        @endif
    </div>



    <!-- Thay đổi hiển thị chi nhánh -->
    <section class="mt-6 p-4 sm:p-5 bg-gray-50 rounded-xl border border-gray-200">
        <div>
            <h3 class="font-semibold text-gray-900">Xem chi nhánh có hàng</h3>
            <p class="text-sm text-gray-600 mt-1">Có <span id="store-count"
                    class="font-bold text-blue-600">{{ $storeLocations->count() }}</span> cửa hàng có sản phẩm</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
            <select id="province-select"
                class="w-full p-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tất cả tỉnh/thành phố</option> {{-- Option mặc định --}}
                @foreach ($provinces as $province)
                    <option value="{{ $province->code }}">{{ $province->name }}</option>
                @endforeach
            </select>
            <select id="district-select"
                class="w-full p-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                disabled>
                <option value="">Tất cả Quận/Huyện</option> {{-- Option mặc định --}}
                {{-- Districts sẽ được load động bằng JavaScript --}}
            </select>
        </div>
        <!-- Store List Carousel -->
        <div class="relative mt-4">
            <div id="store-swiper" class="swiper -mx-1 px-1 pb-2">
                <div class="swiper-wrapper">
                    {{-- Vòng lặp Blade để hiển thị các cửa hàng động --}}
                    @forelse($storeLocations as $store)
                        <div class="swiper-slide w-64 sm:w-72">
                            <div
                                class="store-card h-full flex flex-col bg-white p-4 border border-gray-200 rounded-lg">
                                {{-- Hiển thị địa chỉ của cửa hàng --}}
                                <p class="font-medium text-sm text-gray-800 leading-snug flex-grow">
                                    {{-- Hiển thị địa chỉ chi tiết --}}
                                    {{ $store->address }}
                                    {{-- Thêm xã/phường --}}
                                    @if ($store->ward)
                                        , {{ $store->ward->name }}
                                    @endif
                                    {{-- Thêm quận/huyện --}}
                                    @if ($store->district)
                                        , {{ $store->district->name }}
                                    @endif
                                    {{-- Thêm tỉnh/thành phố --}}
                                    @if ($store->province)
                                        , {{ $store->province->name }}
                                    @endif
                                </p>
                                <div class="flex gap-2 mt-3 text-center">
                                    {{-- Kiểm tra nếu có số điện thoại thì mới hiển thị liên kết gọi --}}
                                    @if ($store->phone)
                                        <a href="tel:{{ $store->phone }}"
                                            class="flex-1 text-sm text-red-600 font-semibold border border-red-200 bg-red-50 rounded-full py-1.5 px-2 hover:bg-red-100 transition-colors flex items-center justify-center gap-1.5">
                                            <span>📞</span>
                                            <span>{{ $store->phone }}</span>
                                        </a>
                                    @endif
                                    {{-- Liên kết đến Google Maps --}}
                                    {{-- Lưu ý: URL của Google Maps cần được xây dựng chuẩn hơn nếu muốn hiển thị chính xác trên bản đồ.
                                 'https://www.google.com/maps/search/?api=1&query=' không phải là định dạng chuẩn.
                                 Bạn nên dùng 'https://www.google.com/maps/search/?api=1&query=' và truyền địa chỉ đầy đủ vào.
                            --}}
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($store->address . ', ' . ($store->ward->name ?? '') . ', ' . ($store->district->name ?? '') . ', ' . ($store->province->name ?? '')) }}"
                                        target="_blank"
                                        class="flex-1 text-sm text-gray-700 font-semibold border border-gray-300 rounded-full py-1.5 px-2 hover:bg-gray-100 transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 24 24">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38
                                                    0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                                        </svg>
                                        <span>Bản đồ</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Hiển thị thông báo nếu không có cửa hàng nào --}}
                        <div class="swiper-slide w-full text-center py-4 text-gray-500">
                            Sản phẩm này hiện không có sẵn tại hệ thống cửa hàng. Mong quý khách thông cảm!
                        </div>
                    @endforelse
                </div>
            </div>
            <button id="store-prev-btn"
                class="absolute top-1/2 -translate-y-1/2 -left-3.5 bg-white rounded-full p-1.5 shadow-lg hover:bg-gray-100 transition-colors z-10">
                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7">
                    </path>
                </svg>
            </button>
            <button id="store-next-btn"
                class="absolute top-1/2 -translate-y-1/2 -right-3.5 bg-white rounded-full p-1.5 shadow-lg hover:bg-gray-100 transition-colors z-10">
                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </section>

    <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
        @csrf
        <input type="hidden" name="product_variant_id" id="wishlist-variant-id">
        <input type="hidden" name="image" id="wishlist-variant-image">
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="variant_key" id="wishlist-variant-key">


        <div class="mt-6">
            <h4 class="font-medium text-gray-800 mb-2">Số lượng</h4>
            <div class="flex flex-wrap flex-sm-nowrap flex-md-wrap flex-lg-nowrap gap-3 gap-lg-2 gap-xl-3 mb-4">
                <div class="count-input flex-shrink-0 order-sm-1">
                    <button type="button" class="btn btn-icon btn-lg" data-decrement aria-label="Giảm số lượng">
                        <i class="ci-minus"></i>
                    </button>
                    <input type="number" class="form-control form-control-lg" name="quantity" id="quantity_input"
                        value="1" min="1" max="1000">
                    <button type="button" class="btn btn-icon btn-lg" data-increment aria-label="Tăng số lượng">
                        <i class="ci-plus"></i>
                    </button>
                </div>
            </div>
            <div id="main-cta-buttons" class="mt-auto pt-6 flex flex-col sm:flex-row gap-3">
                <button type="submit"
                    class="flex-1 w-full flex items-center justify-center gap-2 px-6 py-3.5 border-2 border-blue-600 text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-6 h-6">
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
        </div>
    </form>
    <div id="slide-alert"
        class="fixed top-5 right-5 hidden translate-x-full transition-all duration-300 ease-in-out bg-red-500 text-white px-4 py-2 rounded shadow z-50">
        <span id="slide-alert-message">Thông báo</span>
        <button id="slide-alert-close" class="ml-2 font-bold focus:outline-none">&times;</button>
    </div>
</div>
@if (session('success'))
    <script>
        if (!sessionStorage.getItem('toast_success_shown')) {
            toastr.success("{{ session('success') }}");
            sessionStorage.setItem('toast_success_shown', 'true');

            // Gọi route để xóa session success từ server
            fetch("{{ route('session.flush.message') }}");
        }
    </script>
@endif

@if (session('error'))
    <script>
        if (!sessionStorage.getItem('toast_error_shown')) {
            toastr.error("{{ session('error') }}");
            sessionStorage.setItem('toast_error_shown', 'true');

            // Gọi route để xóa session error từ server
            fetch("{{ route('session.flush.message') }}");
        }
    </script>
@endif

@include('users.cart.layout.partials.script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputVariantId = document.getElementById('wishlist-variant-id'); // id đồng nhất với form
        const inputVariantKey = document.getElementById('wishlist-variant-key');
        const inputImage = document.getElementById('wishlist-variant-image');
        const quantityInput = document.getElementById('quantity_input');

        const variantData = window.variantData || {};
        const attributeOrder = window.attributeOrder || [];

        let currentSelections = {}; // khởi tạo rỗng, hoặc từ biến global nếu có

        // Hàm cập nhật variant fields
        function updateVariantFields() {
            const key = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
            inputVariantKey.value = key;

            if (variantData[key]) {
                inputVariantId.value = variantData[key].id || '';
                inputImage.value = variantData[key].image || '';

                // Cập nhật max số lượng theo tồn kho biến thể
                if (variantData[key].stock_quantity !== undefined) {
                    quantityInput.max = variantData[key].stock_quantity;
                } else {
                    quantityInput.removeAttribute('max');
                }
            } else {
                inputVariantId.value = '';
                inputImage.value = '';
                quantityInput.removeAttribute('max');
            }

            // Reset số lượng về 1 mỗi khi chọn biến thể mới
            quantityInput.value = 1;

            console.log('Variant Key:', key);
            console.log('Variant ID:', inputVariantId.value);
            console.log('Image:', inputImage.value);
            console.log('Max Quantity:', quantityInput.max);
        }

        // Bắt sự kiện radio chọn thuộc tính
        document.querySelectorAll('input[type="radio"][data-attr-name]').forEach(input => {
            input.addEventListener('change', function() {
                const attrName = this.dataset.attrName;
                const attrValue = this.value;
                currentSelections[attrName] = attrValue;
                updateVariantFields();
            });
        });

        // Xử lý submit form Thêm vào giỏ
        document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Lấy giá trị số lượng hiện tại và giới hạn min/max
            let quantity = parseInt(quantityInput.value);
            const min = parseInt(quantityInput.min) || 1;
            const max = parseInt(quantityInput.max) || 1000;

            if (isNaN(quantity) || quantity < min) quantity = min;
            if (quantity > max) quantity = max;
            quantityInput.value = quantity; // cập nhật lại input số lượng nếu vượt giới hạn

            const token = this.querySelector('input[name="_token"]').value;

            const postData = {
                product_variant_id: inputVariantId.value,
                variant_key: inputVariantKey.value,
                image: inputImage.value,
                product_id: this.querySelector('input[name="product_id"]').value,
                quantity: quantity,
                _token: token,
            };

            fetch("{{ route('cart.add') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                    body: JSON.stringify(postData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const cartUrl = "{{ route('cart.index') }}";
                        const message =
                            `${data.success} <br><a href="${cartUrl}" class="btn btn-sm btn-primary mt-2">Xem giỏ hàng</a>`;

                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            escapeHtml: false,
                            timeOut: 3000,
                            positionClass: 'toast-bottom-right'
                        };

                        toastr.success(message);

                        const cartBadge = document.getElementById('cart-badge');
                        if (cartBadge) {
                            if (data.cartItemCount > 0) {
                                cartBadge.textContent = data.cartItemCount;
                                cartBadge.style.display = 'flex';
                            } else {
                                cartBadge.style.display = 'none';
                            }
                        }
                    } else if (data.error) {
                        toastr.error(data.error);
                    }
                })
                .catch(err => {
                    toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
                    console.error(err);
                });
        });

        // Xử lý nút "Mua ngay"
        const buyNowBtn = document.getElementById('buy-now-btn');
        if (buyNowBtn) {
            buyNowBtn.addEventListener('click', function() {
                const form = document.getElementById('add-to-cart-form');
                const formData = new FormData(form);

                const variantKey = inputVariantKey.value?.trim();
                let quantity = parseInt(quantityInput.value) || 1;
                const min = parseInt(quantityInput.min) || 1;
                const max = parseInt(quantityInput.max) || 5;
                if (quantity < min) quantity = min;
                if (quantity > max) quantity = max;
                quantityInput.value = quantity;

                const productId = formData.get('product_id');
                const hasVariants = Object.keys(variantData).length > 1;

                if (hasVariants && (!variantKey || variantKey === '' || variantKey === '_' || variantKey
                        .includes('undefined'))) {
                    toastr.error('Vui lòng chọn đầy đủ thông tin sản phẩm');
                    return;
                }
                if (!productId) {
                    toastr.error('Không tìm thấy thông tin sản phẩm.');
                    return;
                }

                const currentVariant = variantData[variantKey];
                if (currentVariant && currentVariant.stock_quantity !== undefined) {
                    if (quantity > currentVariant.stock_quantity) {
                        toastr.error(
                            `Số lượng vượt quá tồn kho. Chỉ còn ${currentVariant.stock_quantity} sản phẩm.`
                        );
                        return;
                    }
                }

                buyNowBtn.disabled = true;
                buyNowBtn.innerHTML =
                    '<span class="inline-block animate-spin mr-2"></span>Đang xử lý...';

                const buyNowData = {
                    product_id: parseInt(productId),
                    variant_key: variantKey,
                    quantity: quantity,
                };

                fetch('{{ route('buy-now.checkout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(buyNowData)
                    })
                    .then(async res => {
                        const contentType = res.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('Server trả về định dạng không hợp lệ');
                        }
                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || `Lỗi server: ${res.status}`);
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            throw new Error(data.message || 'Phản hồi không hợp lệ từ server.');
                        }
                    })
                    .catch(error => {
                        toastr.error(error.message || 'Đã xảy ra lỗi khi xử lý. Vui lòng thử lại.');
                        console.error(error);
                    })
                    .finally(() => {
                        setTimeout(() => {
                            buyNowBtn.disabled = false;
                            buyNowBtn.innerHTML = 'MUA NGAY';
                        }, 1000);
                    });
            });
        }

        // Khởi tạo cập nhật lần đầu
        updateVariantFields();
    });
</script>
<script>
    window.variantData = @json($variantData);
    window.attributeOrder = @json($attributeOrder);
</script>
<script>
    const wishlistVariantIds = @json($wishlistVariantIds ?? []);

    document.addEventListener('DOMContentLoaded', function() {
        const variantData = window.variantData;
        const attributeOrder = window.attributeOrder;

        const inputVariantId = document.getElementById('wishlist-variant-id');
        const inputVariantKey = document.getElementById('wishlist-variant-key');
        const inputImage = document.getElementById('wishlist-variant-image');
        const wishlistBtn = document.getElementById('wishlist-submit-btn');
        const radios = document.querySelectorAll('.variants input[type="radio"]');

        // Lấy selection hiện tại
        function getCurrentSelection() {
            const selection = {};
            radios.forEach(radio => {
                if (radio.checked) {
                    const attrName = radio.getAttribute('data-attr-name');
                    const value = radio.value;
                    selection[attrName] = value;
                }
            });
            return selection;
        }

        // Xây variant key
        function buildVariantKey(selection) {
            return attributeOrder.map(attr => selection[attr] || '').join('_');
        }

        // Cập nhật input hidden
        function updateWishlistForm(variantKey, variantInfo) {
            if (!variantInfo) return;
            inputVariantId.value = variantInfo.variant_id;
            inputVariantKey.value = variantKey;
            inputImage.value = variantInfo.image;
        }

        // Cập nhật màu nút yêu thích
        function updateWishlistButton(variantId) {
            if (wishlistVariantIds.includes(Number(variantId))) {
                wishlistBtn.classList.add('text-red-500', 'hover:text-red-600');
                wishlistBtn.classList.remove('text-gray-500');
            } else {
                wishlistBtn.classList.remove('text-red-500', 'hover:text-red-600');
                wishlistBtn.classList.add('text-gray-500');
            }
        }

        // Khi đổi biến thể
        function handleVariantChange() {
            const selection = getCurrentSelection();
            const variantKey = buildVariantKey(selection);
            const variantInfo = variantData[variantKey];
            updateWishlistForm(variantKey, variantInfo);
            if (variantInfo) {
                updateWishlistButton(variantInfo.variant_id);
            }
        }

        // Gắn sự kiện
        radios.forEach(radio => {
            radio.addEventListener('change', handleVariantChange);
        });
        document.querySelectorAll('.option-container').forEach(label => {
            label.addEventListener('click', () => {
                setTimeout(() => handleVariantChange(), 10);
            });
        });

        // Gọi khi trang load
        handleVariantChange();

        // Gửi form AJAX
        document.getElementById('wishlist-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const variantId = Number(inputVariantId.value);
            const variantKey = inputVariantKey.value;
            const image = inputImage.value;
            const productId = this.querySelector('input[name="product_id"]').value;
            const token = this.querySelector('input[name="_token"]').value;

            const postData = {
                product_variant_id: variantId,
                variant_key: variantKey,
                image: image,
                product_id: productId,
                _token: token
            };

            fetch("{{ route('wishlist.add') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": token
                    },
                    body: JSON.stringify(postData)
                })
                .then(response => {
                    if (!response.ok) return response.json().then(err => Promise.reject(err));
                    return response.json();
                })
                .then(data => {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: "3000",
                        showDuration: "300",
                        hideDuration: "1000",
                        showMethod: "slideDown",
                        hideMethod: "slideUp"
                    };

                    if (data.success) {
                        toastr.success(data.success);

                        const idx = wishlistVariantIds.indexOf(variantId);
                        if (data.success.includes('xóa')) {
                            if (idx > -1) wishlistVariantIds.splice(idx, 1);
                        } else {
                            if (idx === -1) wishlistVariantIds.push(variantId);
                        }

                        updateWishlistButton(variantId);
                    } else if (data.error) {
                        toastr.error(data.error);
                    }
                })
                .catch(err => {
                    toastr.error(err?.error || 'Có lỗi xảy ra, vui lòng thử lại.');
                    console.error('Lỗi AJAX:', err);
                });
        });
    });
</script>

<script>
    // Script xử lý cập nhật danh sách cửa hàng tồn kho theo biến thể sản phẩm
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo Swiper cho danh sách cửa hàng
        let swiper = new Swiper('#store-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 12,
            freeMode: true,
            navigation: {
                nextEl: '#store-next-btn',
                prevEl: '#store-prev-btn',
            },
            on: {
                // Ẩn/hiện nút điều hướng khi không cần thiết
                init: function() {
                    const container = this.el.parentElement;
                    container.classList.toggle('navigation-hidden', this.isLocked);
                },
                resize: function() {
                    const container = this.el.parentElement;
                    container.classList.toggle('navigation-hidden', this.isLocked);
                }
            }
        });

        // Lưu swiper vào window để truy cập từ các hàm khác
        window.storeSwiper = swiper;

        // Flag để ngăn gọi API trùng lặp
        let isUpdatingStores = false;

        // Hàm chính để cập nhật danh sách cửa hàng dựa trên biến thể
        function updateStoreLocations(variantId) {
            // Ngăn gọi API nếu đang xử lý
            if (isUpdatingStores) return;

            const provinceSelect = document.getElementById('province-select');
            const districtSelect = document.getElementById('district-select');
            const storeWrapper = document.getElementById('store-swiper')?.querySelector('.swiper-wrapper');
            const storeCount = document.getElementById('store-count');

            if (!storeWrapper || !storeCount) return;

            // Cập nhật danh sách tỉnh/thành phố theo biến thể
            async function updateProvincesForVariant() {
                try {
                    const response = await fetch(
                        `/api/provinces-by-variant?product_variant_id=${variantId}`);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                    const provinces = await response.json();
                    const currentProvinceValue = provinceSelect.value;

                    // Cập nhật danh sách tỉnh
                    provinceSelect.innerHTML = '<option value="">Tất cả tỉnh/thành phố</option>';
                    provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.code;
                        option.textContent = province.name;
                        provinceSelect.appendChild(option);
                    });

                    // Reset quận/huyện
                    districtSelect.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
                    districtSelect.disabled = true;

                    // Reset tỉnh nếu không tồn tại trong danh sách mới
                    const provinceExists = provinces.some(p => p.code === currentProvinceValue);
                    if (!provinceExists) provinceSelect.value = '';
                } catch (error) {
                    // Giữ nguyên danh sách tỉnh nếu lỗi
                }
            }

            // Lọc và hiển thị danh sách cửa hàng
            async function filterStores(provinceCode, districtCode) {
                try {
                    const query = new URLSearchParams();
                    if (provinceCode) query.append('province_code', provinceCode);
                    if (districtCode) query.append('district_code', districtCode);
                    query.append('product_variant_id', variantId);

                    const response = await fetch(`/api/filter-stores?${query.toString()}`);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                    const {
                        stores,
                        count
                    } = await response.json();
                    storeCount.textContent = count;
                    storeWrapper.innerHTML = '';

                    if (stores.length === 0) {
                        storeWrapper.innerHTML =
                            '<div class="swiper-slide w-full text-center py-4 text-gray-500">Sản phẩm này hiện không có sẵn tại hệ thống cửa hàng. Mong quý khách thông cảm!</div>';
                    } else {
                        stores.forEach(store => {
                            const slide = document.createElement('div');
                            slide.className = 'swiper-slide w-64 sm:w-72';
                            slide.innerHTML = `
                            <div class="store-card h-full flex flex-col bg-white p-4 border border-gray-200 rounded-lg">
                                <p class="font-medium text-sm text-gray-800 leading-snug flex-grow">
                                    ${store.address}${store.ward ? `, ${store.ward}` : ''}${store.district ? `, ${store.district}` : ''}${store.province ? `, ${store.province}` : ''}
                                </p>
                                <div class="flex gap-2 mt-3 text-center">
                                    ${store.phone ? `
                                        <a href="tel:${store.phone}" class="flex-1 text-sm text-red-600 font-semibold border border-red-200 bg-red-50 rounded-full py-1.5 px-2 hover:bg-red-100 transition-colors flex items-center justify-center gap-1.5">
                                            <span>📞</span>
                                            <span>${store.phone}</span>
                                        </a>
                                    ` : ''}
                                    <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(store.address + (store.ward ? `, ${store.ward}` : '') + (store.district ? `, ${store.district}` : '') + (store.province ? `, ${store.province}` : ''))}" target="_blank" class="flex-1 text-sm text-gray-700 font-semibold border border-gray-300 rounded-full py-1.5 px-2 hover:bg-gray-100 transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 
                                                     0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                                        </svg>
                                        <span>Bản đồ</span>
                                    </a>
                                </div>
                            </div>
                        `;
                            storeWrapper.appendChild(slide);
                        });
                    }

                    // Cập nhật Swiper sau khi thêm slides
                    if (window.storeSwiper) window.storeSwiper.update();
                } catch (error) {
                    storeWrapper.innerHTML =
                        '<div class="swiper-slide w-full text-center py-4 text-gray-500">Đã xảy ra lỗi khi tải danh sách cửa hàng.</div>';
                    storeCount.textContent = '0';
                    if (window.storeSwiper) window.storeSwiper.update();
                }
            }

            // Chỉ cập nhật tỉnh khi thay đổi biến thể
            if (variantId !== window.lastVariantId) {
                window.lastVariantId = variantId;
                isUpdatingStores = true;
                updateProvincesForVariant().then(() => {
                    const currentProvince = provinceSelect ? provinceSelect.value : '';
                    const currentDistrict = districtSelect ? districtSelect.value : '';
                    filterStores(currentProvince, currentDistrict).finally(() => {
                        isUpdatingStores = false;
                    });
                });
            } else {
                const currentProvince = provinceSelect ? provinceSelect.value : '';
                const currentDistrict = districtSelect ? districtSelect.value : '';
                isUpdatingStores = true;
                filterStores(currentProvince, currentDistrict).finally(() => {
                    isUpdatingStores = false;
                });
            }
        }

        // Lắng nghe thay đổi biến thể sản phẩm
        function listenForVariantChanges() {
            const radioButtons = document.querySelectorAll('.variants input[type="radio"][data-attr-name]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    setTimeout(() => {
                        const variantIdInput = document.querySelector(
                            '[name="product_variant_id"]');
                        if (variantIdInput && variantIdInput.value) {
                            updateStoreLocations(variantIdInput.value);
                        }
                    }, 100);
                });
            });

            const optionLabels = document.querySelectorAll('.option-container');
            optionLabels.forEach(label => {
                label.addEventListener('click', function() {
                    setTimeout(() => {
                        const variantIdInput = document.querySelector(
                            '[name="product_variant_id"]');
                        if (variantIdInput && variantIdInput.value) {
                            updateStoreLocations(variantIdInput.value);
                        }
                    }, 200);
                });
            });
        }

        // Tạo listener cho province select
        function createProvinceSelectListener() {
            document.addEventListener('change', async function(event) {
                if (event.target.id === 'province-select') {
                    const provinceCode = event.target.value;
                    const districtSelect = document.getElementById('district-select');
                    const variantIdInput = document.querySelector('[name="product_variant_id"]');
                    const productVariantId = variantIdInput ? variantIdInput.value : '';

                    districtSelect.innerHTML = '<option value="">Tất cả Quận/Huyện</option>';
                    districtSelect.disabled = true;

                    if (provinceCode && productVariantId) {
                        try {
                            const response = await fetch(
                                `/api/districts-by-province?province_code=${encodeURIComponent(provinceCode)}&product_variant_id=${encodeURIComponent(productVariantId)}`
                            );
                            if (!response.ok) throw new Error(
                                `HTTP error! status: ${response.status}`);

                            const districts = await response.json();
                            if (districts.length === 0) {
                                districtSelect.innerHTML =
                                    '<option value="">Không có quận/huyện</option>';
                            } else {
                                districts.forEach(district => {
                                    const option = document.createElement('option');
                                    option.value = district.code;
                                    option.textContent = district.name;
                                    districtSelect.appendChild(option);
                                });
                                districtSelect.disabled = false;
                            }
                        } catch (error) {
                            districtSelect.innerHTML =
                                '<option value="">Không có quận/huyện</option>';
                        }
                    }

                    updateStoreLocations(productVariantId);
                }
            });
        }

        // Khởi tạo các listener
        listenForVariantChanges();

        // Theo dõi thay đổi variantId input
        const variantIdInput = document.querySelector('[name="product_variant_id"]');
        if (variantIdInput) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        const newValue = variantIdInput.value;
                        if (newValue) updateStoreLocations(newValue);
                    }
                });
            });
            observer.observe(variantIdInput, {
                attributes: true,
                attributeFilter: ['value']
            });
            variantIdInput.addEventListener('input', function() {
                if (this.value) updateStoreLocations(this.value);
            });
        }

        // Listener cho district select
        const districtSelect = document.getElementById('district-select');
        if (districtSelect) {
            districtSelect.addEventListener('change', async function() {
                const provinceSelect = document.getElementById('province-select');
                const provinceCode = provinceSelect ? provinceSelect.value : '';
                const districtCode = this.value;
                const variantIdInput = document.querySelector('[name="product_variant_id"]');
                const productVariantId = variantIdInput ? variantIdInput.value : '';
                updateStoreLocations(productVariantId);
            });
        }

        // Cập nhật lần đầu khi trang tải
        setTimeout(() => {
            const variantIdInput = document.querySelector('[name="product_variant_id"]');
            if (variantIdInput && variantIdInput.value) {
                window.lastVariantId = variantIdInput.value;
                updateStoreLocations(variantIdInput.value);
            }
            createProvinceSelectListener();
        }, 200);
    });
</script>
