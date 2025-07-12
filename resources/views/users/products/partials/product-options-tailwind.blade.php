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
            class="flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800"
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
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
                        <svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 20 20">
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



    <div class="promotions mt-6">
        <h3 class="font-semibold text-gray-800 mb-2">Khuyến mãi & Ưu đãi</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex items-start gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg><span>Giảm thêm <span class="font-bold">500.000₫</span> khi thanh toán qua VNPAY.</span></li>
            <li class="flex items-start gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c1.657 0 3-1.343 3-3S13.657 2 12 2 9 3.343 9 5s1.343 3 3 3zm0 0v1m0-1c-1.657 0-3 1.343-3 3S10.343 11 12 11s3-1.343 3-3-1.343-3-3-3zm0 0c1.657 0 3 1.343 3 3s-1.343 3-3 3m0 0v7m0-7c-1.657 0-3 1.343-3 3s1.343 3 3 3">
                    </path>
                </svg><span>Tặng <span class="font-bold">Ốp lưng MagSafe</span> trị giá 1.200.000₫.</span></li>
            <li class="flex items-start gap-2 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                </svg><span>Hỗ trợ <span class="font-bold">trả góp 0%</span> qua thẻ tín dụng.</span></li>
        </ul>
    </div>

    <!-- Additional Promotions Section -->
    <div class="mt-6 p-4 border border-gray-200 rounded-lg">
        <h3 class="font-bold text-red-600">Khuyến mãi trị giá 500.000₫</h3>
        <p class="text-sm text-gray-600 mt-1">Giá và khuyến mãi có thể kết thúc sớm hơn dự kiến</p>
        <div class="mt-4 space-y-2 text-sm text-gray-800">
            <div class="flex items-start gap-2">
                <span
                    class="flex-shrink-0 flex items-center justify-center w-5 h-5 bg-blue-500 text-white rounded-full text-xs font-bold">1</span>
                <span>Phiếu mua hàng AirPods, Apple Watch, Macbook trị giá 500,000đ</span>
            </div>
            <div class="flex items-start gap-2">
                <span
                    class="flex-shrink-0 flex items-center justify-center w-5 h-5 bg-blue-500 text-white rounded-full text-xs font-bold">2</span>
                <span>Phiếu mua hàng máy lạnh trị giá 300.000đ (<a href="#"
                        class="text-blue-600 hover:underline">Xem chi tiết tại đây</a>)</span>
            </div>
            <div class="flex items-start gap-2">
                <span
                    class="flex-shrink-0 flex items-center justify-center w-5 h-5 bg-blue-500 text-white rounded-full text-xs font-bold">3</span>
                <span>Phiếu mua hàng áp dụng mua Sạc dự phòng (trừ hãng AVA+, Hydrus), đồng hồ thông minh (trừ Apple),
                    Tai nghe và Loa bluetooth (hãng JBL, Marshall, Harman Kardon, Sony) trị giá 100.000đ</span>
            </div>
            <div class="flex items-start gap-2">
                <span
                    class="flex-shrink-0 flex items-center justify-center w-5 h-5 bg-blue-500 text-white rounded-full text-xs font-bold">4</span>
                <span>Phiếu mua hàng máy lọc nước trị giá 300.000đ</span>
            </div>
            <div class="flex items-start gap-2">
                <span
                    class="flex-shrink-0 flex items-center justify-center w-5 h-5 bg-blue-500 text-white rounded-full text-xs font-bold">5</span>
                <span>Phiếu mua hàng áp dụng mua tất cả sim có gói Mobi, Itel, Local, Vina và VNMB trị giá 50,000đ. (<a
                        href="#" class="text-blue-600 hover:underline">Xem chi tiết tại đây</a>)</span>
            </div>
            <div class="flex items-start gap-2">
                <span
                    class="flex-shrink-0 flex items-center justify-center w-5 h-5 bg-blue-500 text-white rounded-full text-xs font-bold">6</span>
                <span>Trả chậm 0% lãi suất. Đặc biệt giảm đến 10% tối đa 5 triệu khi thanh toán qua Kredivo (<a
                        href="#" class="text-blue-600 hover:underline">Xem chi tiết tại đây</a>)</span>
            </div>
        </div>
        <ul class="mt-4 space-y-1 text-sm text-gray-800 list-inside">
            <li class="flex items-start gap-2"><span class="text-red-500 mt-1.5 flex-shrink-0">•</span><span>Giao hàng
                    nhanh chóng (tuỳ khu vực)</span></li>
            <li class="flex items-start gap-2"><span class="text-red-500 mt-1.5 flex-shrink-0">•</span><span>Mỗi số
                    điện thoại chỉ mua 3 sản phẩm trong 1 tháng</span></li>
        </ul>
    </div>
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
                        value="1" min="1" max="5" readonly>
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
    <div class="text-sm text-gray-500 mt-4 text-center sm:text-left"><span class="font-semibold">Giao hàng dự
            kiến:</span> Thứ Ba, 28/06 - Thứ Tư, 29/06.</div>
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
            } else {
                inputVariantId.value = '';
                inputImage.value = '';
            }
            console.log('Variant Key:', key);
            console.log('Variant ID:', inputVariantId.value);
            console.log('Image:', inputImage.value);
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

        // Sự kiện tăng/giảm số lượng
        document.querySelector('[data-increment]').addEventListener('click', () => {
            let val = parseInt(quantityInput.value) || 1;
            if (val < 5) quantityInput.value = val + 1;
        });
        document.querySelector('[data-decrement]').addEventListener('click', () => {
            let val = parseInt(quantityInput.value) || 1;
            if (val > 1) quantityInput.value = val - 1;
        });

        // Gửi form bằng fetch (POST) đúng route cart.add
        document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const postData = {
                product_variant_id: inputVariantId.value,
                variant_key: inputVariantKey.value,
                image: inputImage.value,
                product_id: this.querySelector('input[name="product_id"]').value,
                quantity: quantityInput.value,
                _token: this.querySelector('input[name="_token"]').value,
            };

            fetch("{{ route('cart.add') }}", { // đổi đúng route cart.add
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": postData._token
                    },
                    body: JSON.stringify(postData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.success);

                        // ✅ Cập nhật số lượng hiển thị trên giỏ hàng
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
                // Lấy dữ liệu từ form
                const form = document.getElementById('add-to-cart-form');
                const formData = new FormData(form);
                // Validation chi tiết hơn
                const variantKey = inputVariantKey.value?.trim();
                const quantity = parseInt(quantityInput.value) || 1;
                const productId = formData.get('product_id');
                // Kiểm tra variant key (chỉ với sản phẩm có biến thể)
                const hasVariants = Object.keys(variantData).length > 1;
                if (hasVariants && (!variantKey || variantKey === '' || variantKey === '_' || variantKey
                        .includes('undefined'))) {
                    toastr.error('Vui lòng chọn đầy đủ thông tin sản phẩm');
                    return;
                }
                // Kiểm tra product ID
                if (!productId) {
                    toastr.error('Không tìm thấy thông tin sản phẩm.');
                    return;
                }
                // Kiểm tra số lượng tồn kho
                const currentVariant = variantData[variantKey];
                if (currentVariant && currentVariant.stock_quantity !== undefined) {
                    if (quantity > currentVariant.stock_quantity) {
                        toastr.error(
                            `Số lượng vượt quá tồn kho. Chỉ còn ${currentVariant.stock_quantity} sản phẩm.`
                        );
                        return;
                    }
                }
                // Disable button và thay đổi text với loading
                buyNowBtn.disabled = true;
                buyNowBtn.innerHTML =
                    '<span class="inline-block animate-spin mr-2"></span>Đang xử lý...';
                // Tạo dữ liệu gửi với validation
                const buyNowData = {
                    product_id: parseInt(productId),
                    variant_key: variantKey,
                    quantity: quantity
                };
                console.log('Buy Now Data:', buyNowData);
                // Gửi request đến endpoint Buy Now
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
                        console.log(' Buy Now Response:', data);
                        if (data.success && data.redirect_url) {
                            // Chuyển hướng ngay lập tức
                            window.location.href = data.redirect_url;
                        } else {
                            throw new Error(data.message || 'Phản hồi không hợp lệ từ server.');
                        }
                    })
                    .catch(error => {
                        console.error(' Buy Now Error:', error);
                        toastr.error(error.message || 'Đã xảy ra lỗi khi xử lý. Vui lòng thử lại.');
                    })
                    .finally(() => {
                        // Khôi phục button sau delay để tránh spam click
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
    document.addEventListener('DOMContentLoaded', function() {
        const variantData = window.variantData;
        const attributeOrder = window.attributeOrder;

        const inputVariantId = document.getElementById('wishlist-variant-id');
        const inputVariantKey = document.getElementById('wishlist-variant-key');
        const inputImage = document.getElementById('wishlist-variant-image');

        // Lấy tất cả input radio
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
            console.log('🔍 Current selection:', selection);
            return selection;
        }

        // Xây dựng variantKey theo thứ tự
        function buildVariantKey(selection) {
            const key = attributeOrder.map(attr => selection[attr] || '').join('_');
            console.log('🔑 Built variant key:', key);
            return key;
        }

        // Cập nhật các input ẩn trong form
        function updateWishlistForm(variantKey, variantInfo) {
            if (!variantInfo) {
                console.warn('❌ Không tìm thấy variantInfo với key:', variantKey);
                return;
            }

            inputVariantId.value = variantInfo.variant_id;
            inputVariantKey.value = variantKey;
            inputImage.value = variantInfo.image;

            console.log('✅ Updated hidden inputs:', {
                variant_id: inputVariantId.value,
                variant_key: inputVariantKey.value,
                image: inputImage.value
            });
        }

        // Xử lý khi người dùng chọn biến thể
        function handleVariantChange() {
            const selection = getCurrentSelection();
            const variantKey = buildVariantKey(selection);
            const variantInfo = variantData[variantKey];
            updateWishlistForm(variantKey, variantInfo);
        }

        // Gán sự kiện change cho từng radio
        radios.forEach(radio => {
            radio.addEventListener('change', handleVariantChange);
        });

        // Gán sự kiện click cho label để đảm bảo cập nhật kịp trước submit
        document.querySelectorAll('.option-container').forEach(label => {
            label.addEventListener('click', () => {
                // Đợi radio cập nhật xong mới xử lý (không preventDefault)
                setTimeout(() => {
                    handleVariantChange();
                }, 10); // delay rất nhỏ giúp smooth, không lag
            });
        });


        // Gọi lần đầu khi trang load
        handleVariantChange();

        // Debug khi form submit
        document.getElementById('wishlist-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const variantId = inputVariantId.value;
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
                    if (!response.ok) {
                        return response.json().then(errData => Promise.reject(errData));
                    }
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
                    } else if (data.info) {
                        toastr.info(data.info);
                    } else if (data.error) {
                        toastr.error(data.error);
                    }
                })
                .catch(err => {
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

                    if (err && err.error) {
                        toastr.error(err.error);
                    } else {
                        toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
                    }
                    console.error('Lỗi AJAX:', err);
                });
        });

    });
</script>
<script>
    const wishlistVariantIds = @json($wishlistVariantIds);
    document.addEventListener('DOMContentLoaded', function() {
        const wishlistVariantIds = @json($wishlistVariantIds ?? []);

        const wishlistBtn = document.getElementById('wishlist-submit-btn');
        const variantRadios = document.querySelectorAll('.variant-radio'); // class radio biến thể bạn dùng

        function updateWishlistButton(variantId) {
            if (wishlistVariantIds.includes(variantId)) {
                wishlistBtn.classList.add('text-red-500');
                wishlistBtn.classList.remove('text-gray-500');
                wishlistBtn.classList.add('hover:text-red-600');
            } else {
                wishlistBtn.classList.remove('text-red-500');
                wishlistBtn.classList.add('text-gray-500');
                wishlistBtn.classList.remove('hover:text-red-600');
            }
        }

        // Gọi lần đầu với biến thể mặc định
        updateWishlistButton(@json($defaultVariant->id ?? null));

        // Lắng nghe sự kiện thay đổi biến thể
        variantRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedVariantId = parseInt(this.value);
                updateWishlistButton(selectedVariantId);
            });
        });
    });
</script>
