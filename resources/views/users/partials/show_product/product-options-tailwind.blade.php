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
            <input type="hidden" name="product_variant_id" id="product_variant_id_input">
            <input type="hidden" name="image" id="variant-image">
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variant_key" id="variant_key_input">

            <button type="submit" id="favorite-btn"
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
    <div class="variants mt-6 space-y-4">
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
        <input type="hidden" name="product_variant_id" id="product_variant_id_input">
        <input type="hidden" name="image" id="variant-image">
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="variant_key" id="variant_key_input">


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
                <button type="submit" name="buy_now" value="1"
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
        const variantData = @json($variantData);
        const attributeOrder = @json($attributeOrder);
        const initialVariantAttributes = @json($initialVariantAttributes);

        const variantKeyInput = document.getElementById('variant_key_input');
        const variantImageInput = document.getElementById('variant-image');
        const quantityInput = document.getElementById('quantity_input');

        let currentSelections = {
            ...initialVariantAttributes
        };

        function updateVariantKeyAndImage() {
            const key = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
            variantKeyInput.value = key;

            if (variantData[key]) {
                variantImageInput.value = variantData[key].image || '';
            } else {
                variantImageInput.value = '';
            }
        }

        // Gắn event khi chọn thuộc tính bằng radio
        document.querySelectorAll('input[type="radio"][data-attr-name]').forEach(input => {
            input.addEventListener('change', function() {
                const attrName = this.dataset.attrName;
                const attrValue = this.value;
                currentSelections[attrName] = attrValue;
                updateVariantKeyAndImage();
            });
        });

        // Gắn event khi click button chọn (nếu có dùng nút thay radio)
        document.querySelectorAll('[data-attribute-name][data-attribute-value]').forEach(btn => {
            btn.addEventListener('click', function() {
                const attr = this.dataset.attributeName;
                const value = this.dataset.attributeValue;
                currentSelections[attr] = value;
                updateVariantKeyAndImage();
            });
        });

        // Tăng/giảm số lượng
        document.querySelector('[data-increment]').addEventListener('click', () => {
            let val = parseInt(quantityInput.value);
            if (val < 5) {
                quantityInput.value = val + 1;
            }
        });
        document.querySelector('[data-decrement]').addEventListener('click', () => {
            let val = parseInt(quantityInput.value);
            if (val > 1) {
                quantityInput.value = val - 1;
            }
        });

        // Khởi tạo ban đầu
        updateVariantKeyAndImage();
    });
    window.addEventListener('beforeunload', function() {
        sessionStorage.removeItem('toast_success_shown');
        sessionStorage.removeItem('toast_error_shown');
    });
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
            // Xóa sessionStorage để ngăn toastr hiện lại
            sessionStorage.removeItem('toast_success_shown');
            sessionStorage.removeItem('toast_error_shown');

            // Reload trang nhẹ để tránh cache
            location.reload();
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('add-to-cart-form');
        if (!form) return;

        let clickedBtn = null;

        // Ghi lại nút submit nào được click
        form.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.addEventListener('click', function() {
                clickedBtn = this;
            });
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);

            // Nếu nút được bấm có name/value, append vào formData
            if (clickedBtn?.name) {
                formData.append(clickedBtn.name, clickedBtn.value);
            }

            const submitBtn = clickedBtn || form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang xử lý...';

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async res => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;

                    const data = await res.json();

                    if (!res.ok) {
                        throw new Error(data.message || 'Lỗi khi thêm sản phẩm vào giỏ hàng.');
                    }

                    // ✅ Nếu controller trả về redirect → chuyển trang
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }

                    // ✅ Nếu không có redirect → chỉ hiện thông báo
                    showSlideAlert('success', data.message || 'Đã thêm sản phẩm vào giỏ hàng!');
                    const cartBadge = document.getElementById('cart-badge');
                    const quantityInput = document.getElementById('quantity_input');
                    const addedQty = parseInt(quantityInput?.value || 1);

                    if (cartBadge) {
                        const currentQty = parseInt(cartBadge.textContent) || 0;
                        const newQty = currentQty + addedQty;

                        cartBadge.textContent = newQty;
                        cartBadge.style.display = newQty > 0 ? 'flex' : 'none';
                    }

                })

                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    showSlideAlert('error', error.message || 'Đã xảy ra lỗi.');
                });
        });


        // Hàm thông báo toastr
        window.showSlideAlert = window.showSlideAlert || function(type = 'info', message = '', duration =
            4000) {
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: duration,
                    positionClass: 'toast-top-right'
                };
                toastr[type](message);
            } else {
                alert(`[${type.toUpperCase()}] ${message}`);
            }
        };
    });
    document.getElementById('favorite-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Ngăn form submit mặc định

        const form = this;
        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Đã thêm vào yêu thích!');
                    // Đổi màu icon, v.v...
                } else {
                    toastr.warning(data.message || 'Thêm thất bại!');
                }
            })
            .catch(err => {
                console.error('Lỗi:', err);
                toastr.error('Đã xảy ra lỗi khi gửi yêu cầu!');
            });
    });

    // Toastr cấu hình
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right", // Hiển thị góc trên bên phải
        "timeOut": "3000",
        "showDuration": "300",
        "hideDuration": "1000",
        "showMethod": "slideDown",
        "hideMethod": "slideUp"
    };
    const variantData = @json($variantData);
    const attributeOrder = @json($attributeOrder);

    const variantKeyInput = document.getElementById('variant_key_input');
    const variantImageInput = document.getElementById('variant-image');
    const productVariantIdInput = document.getElementById('product_variant_id_input');

    const currentSelections = {};

    document.querySelectorAll('[data-attribute-name]').forEach(select => {
        select.addEventListener('change', function() {
            const attr = this.dataset.attributeName;
            currentSelections[attr] = this.value;
            updateVariantFields();
        });
    });

    function updateVariantFields() {
        const key = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
        variantKeyInput.value = key;

        if (variantData[key]) {
            productVariantIdInput.value = variantData[key].id || '';
            variantImageInput.value = variantData[key].image || '';
        } else {
            productVariantIdInput.value = '';
            variantImageInput.value = '';
        }

        // DEBUG: in ra để kiểm tra
        console.log("KEY:", key);
        console.log("ID:", productVariantIdInput.value);
        console.log("Image:", variantImageInput.value);
    }

    document.addEventListener('DOMContentLoaded', updateVariantFields);
</script>




{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        // XỬ LÝ ĐẾM NGƯỢC FLASH SALE
        const timer = document.getElementById('countdown-timer');
        if (timer) {
            const endTimeStr = timer.dataset.endTime;
            if (endTimeStr) {
                const endTime = new Date(endTimeStr).getTime();

                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = endTime - now;

                    if (distance <= 0) {
                        // Hết thời gian flash sale
                        document.getElementById('hours').textContent = '00';
                        document.getElementById('minutes').textContent = '00';
                        document.getElementById('seconds').textContent = '00';

                        // Ẩn khối flash sale
                        const flashBlock = timer.closest('.bg-orange-500');
                        if (flashBlock) flashBlock.remove();

                        // Hiện khối giá thường nếu có
                        const normalBlock = document.getElementById('normal-price-block');
                        if (normalBlock) normalBlock.classList.remove('hidden');

                        return;
                    }

                    const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
                    const minutes = Math.floor((distance / (1000 * 60)) % 60);
                    const seconds = Math.floor((distance / 1000) % 60);

                    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            }
        }

        // XỬ LÝ TĂNG GIẢM SỐ LƯỢNG
        const minusBtn = document.getElementById('minusBtn');
        const plusBtn = document.getElementById('plusBtn');
        const quantityInput = document.getElementById('quantityInput');

        if (minusBtn && plusBtn && quantityInput) {
            plusBtn.addEventListener('click', () => {
                let current = parseInt(quantityInput.value) || 1;
                quantityInput.value = current + 1;
            });

            minusBtn.addEventListener('click', () => {
                let current = parseInt(quantityInput.value) || 1;
                if (current > 1) {
                    quantityInput.value = current - 1;
                }
            });
        }
    });
</script> --}}
