@extends('users.layouts.app')

@section('title', $product->name . ' - iMart')

@section('meta')
    <meta name="description" content="{{ $product->meta_description }}">
    <meta name="keywords" content="{{ $product->meta_keywords }}">
@endsection


@section('content')
    <div class="container mx-auto p-4 md:p-8">
        <main class="content-wrapper bg-white p-4 sm:p-6 md:p-8 rounded-xl shadow-sm">
            {{-- Breadcrumb Tailwind --}}
            <nav class="text-sm text-gray-500 mb-4">
                <a href="{{ route('users.home') }}" class="hover:underline">Trang chủ</a> &gt;
                <a href="{{ route('users.products.all') }}" class="hover:underline">Danh mục sản phẩm</a> &gt;
                <span class="font-medium text-gray-700">{{ $product->name }}
                </span>
            </nav>

            {{-- Gallery + Options --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-start">
                {{-- Cột trái: Thư viện ảnh --}}
                <div class="lg:sticky top-8 self-start">
                    @include('users.partials.show_product.product-gallery-tailwind')
                </div>

                {{-- Cột phải: Thông tin + hành động --}}
                @include('users.partials.show_product.product-options-tailwind')
            </div>

        </main>
        @include('users.partials.show_product.product-details-tailwind')
        <!-- sticky-bar.blade.php -->
    </div>
    <!-- Image Lightbox Modal -->
    <div id="image-lightbox-modal"
        class="fixed inset-0 bg-black/90 z-50 hidden flex-col items-center justify-center p-4 transition-opacity duration-300">

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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg></button>
                <button id="lightbox-next-btn"
                    class="absolute right-4 sm:right-8 text-white p-2 rounded-full bg-black/40 hover:bg-black/70 transition-colors"><svg
                        class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg></button>
            </div>
            <!-- Phần mô tả & thumbnails -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-black/70">
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

    @php
        use Illuminate\Support\Facades\Storage;
        use Carbon\Carbon;

        $variant = $defaultVariant ?? $product->variants->first();
        $now = now();

        $salePrice = (int) $variant->sale_price;
        $originalPrice = (int) $variant->price;

        $hasFlashTime =
            $variant->sale_price_starts_at instanceof Carbon && $variant->sale_price_ends_at instanceof Carbon;
        $isFlashSale = false;

        if ($salePrice && $hasFlashTime) {
            $isFlashSale = $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);
        }

        $isSale = !$isFlashSale && $salePrice && $salePrice < $originalPrice;
        $priceToDisplay = $isSale || $isFlashSale ? $salePrice : $originalPrice;

        $imageUrl = $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) : asset('images/placeholder.jpg');
    @endphp

    <!-- Sticky Add to Cart Bar -->
    <div id="sticky-bar"
        class="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-sm p-3 shadow-[0_-2px_10px_rgba(0,0,0,0.1)] transform translate-y-full transition-transform duration-300 z-40">
        <div class="container mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img id="sticky-image" src="{{ $imageUrl }}" alt="Ảnh sản phẩm"
                    class="w-12 h-12 rounded-md object-cover">
                <div>
                    <p id="sticky-name" class="font-semibold text-sm text-gray-800">{{ $product->name }}</p>
                    <p id="sticky-variant" class="text-xs text-gray-500">
                        {{ collect($initialVariantAttributes)->values()->join(', ') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div>
                    <p id="sticky-price" class="font-bold text-red-600 text-right">
                        {{ number_format($priceToDisplay) }}₫
                    </p>
                    @if ($isSale || $isFlashSale)
                        <p id="sticky-original-price" class="text-xs text-gray-500 line-through text-right">
                            {{ number_format($originalPrice) }}₫
                        </p>
                    @endif
                </div>
                <button
                    class="hidden sm:flex items-center justify-center p-3 border-2 border-red-600 text-red-600 font-bold rounded-lg hover:bg-red-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </button>
                <button
                    class="flex-1 w-full sm:w-auto px-6 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors">
                    Mua ngay
                </button>
            </div>
        </div>
    </div>

    <!-- Compare Modal -->
    <div id="compare-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden"
        data-variant-id="">
        <div class="w-full max-w-3xl bg-white rounded-xl shadow-2xl flex flex-col max-h-[95vh]">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-4 border-b border-gray-200 flex-shrink-0">
                <h3 class="text-xl font-bold text-gray-900">Chọn sản phẩm so sánh</h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Search and Suggestions -->
            <div class="p-4 sm:p-6 overflow-y-auto product-list flex-grow">
                <!-- Search Bar -->
                <div class="relative mb-6">
                    <input type="text" id="search-product" placeholder="Nhập sản phẩm bạn muốn so sánh"
                        class="w-full pl-4 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Suggestion Title -->
                <h4 class="text-base font-semibold text-gray-600 mb-4">Sản phẩm đã xem gần đây</h4>

                <!-- Suggested Product List -->
                <div id="suggested-products" class="space-y-3">
                    <!-- Dữ liệu gợi ý sẽ được đổ bằng JavaScript -->
                </div>

            </div>

            <!-- Bottom Comparison Bar -->
            <div id="comparison-bar" class="flex-shrink-0 bg-gray-800 text-white p-4 rounded-b-xl shadow-lg w-full">
                <div class="w-full max-w-6xl mx-auto space-y-4">
                    <!-- 3 ô sản phẩm -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="flex items-center justify-center gap-2 bg-gray-700 p-3 rounded-lg h-[64px]"
                            data-product-slot="1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="text-sm text-gray-400">Sản phẩm 1</span>
                        </div>
                        <div class="flex items-center justify-center gap-2 bg-gray-700 p-3 rounded-lg h-[64px]"
                            data-product-slot="2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="text-sm text-gray-400">Sản phẩm 2</span>
                        </div>
                        <div class="flex items-center justify-center gap-2 bg-gray-700 p-3 rounded-lg h-[64px]"
                            data-product-slot="3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="text-sm text-gray-400">Sản phẩm 3</span>
                        </div>
                    </div>

                    <!-- 2 nút căn giữa bên dưới -->
                    <div class="flex justify-center items-center gap-4">
                        <button id="clear-compare-btn"
                            class="text-sm font-semibold bg-red-600 hover:bg-red-700 px-5 py-2 rounded-lg text-white">
                            Xóa tất cả
                        </button>
                        <button id="compare-now-btn"
                            class="text-sm font-bold bg-white text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-200">
                            So sánh ngay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            /* Light gray background */
        }

        #gallery-prev-btn,
        #gallery-next-btn {
            opacity: 1 !important;
            /* Luôn hiển thị */
            background-color: rgba(255, 255, 255, 0.8);
            transition: background-color 0.3s;
        }

        #gallery-prev-btn:hover,
        #gallery-next-btn:hover {
            background-color: rgba(255, 255, 255, 1);
        }

        /* Gallery Thumbnail Selected */
        .thumbnail-selected {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        /* Variant Option Selected */
        .variant-selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
            color: #2563eb;
        }

        /* Carousel Scroll Hide */
        .carousel::-webkit-scrollbar {
            display: none;
        }

        .carousel {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Favorite Button Selected */
        #favorite-btn.favorited {
            color: #ef4444;
            /* red-500 */
        }

        /* Flash Sale Countdown Box */
        .timer-box {
            background: #333;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        /* Review Stars */
        .review-star {
            color: #d1d5db;
            /* gray-300 */
            cursor: pointer;
            transition: color 0.2s;
        }

        .review-star.hover,
        .review-star.selected {
            color: #f59e0b;
            /* amber-500 */
        }

        /* Sticky Add to Cart Bar */
        #sticky-bar {
            transition: transform 0.3s ease-in-out;
        }

        /* Lightbox Image Zoom */
        #lightbox-main-image {
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }

        #lightbox-main-image.zoomed {
            transform: scale(2.5);
            cursor: zoom-out;
        }

        /* Loading Spinner */
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Tab Buttons */
        .tab-button {
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease-in-out;
        }

        .tab-active {
            border-color: #3b82f6;
            color: #2563eb;
            background-color: #eff6ff;
        }

        /* Tab Content */
        .tab-content {
            display: block;
        }

        .tab-content.hidden {
            display: none;
        }

        /* Description Content (Read More) */
        .description-content.collapsed {
            max-height: 300px;
            overflow: hidden;
            position: relative;
        }

        .description-content.collapsed::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to top, white, rgba(255, 255, 255, 0));
        }

        /* Accordion */
        .accordion-button {
            transition: background-color 0.2s ease;
            background-color: #f3f4f6 !important;
            /* tương đương bg-gray-100 */
        }

        .accordion-button:hover {
            background-color: #e5e7eb;
        }

        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .accordion-icon {
            transition: transform 0.3s ease;
        }

        /* Main Thumbnails Container */
        #main-thumbnails {
            display: flex;
            /* Hiển thị dạng flex để các thumbnail xếp ngang */
            overflow-x: auto;
            /* Cho phép cuộn ngang */
            scroll-behavior: smooth;
            /* Cuộn mượt mà */
            user-select: none;
            /* Ngăn chọn văn bản khi kéo */
            -webkit-overflow-scrolling: touch;
            /* Hỗ trợ cuộn mượt trên iOS */
            scrollbar-width: none;
            /* Ẩn thanh cuộn trên Firefox */
            gap: 8px;
            /* Khoảng cách giữa các thumbnail */
            padding: 4px 0;
            /* Padding để tránh dính mép */
        }

        #main-thumbnails::-webkit-scrollbar {
            display: none;
            /* Ẩn thanh cuộn trên Chrome/Safari */
        }

        /* Thumbnail Item */
        .thumbnail-item {
            flex-shrink: 0;
            /* Ngăn thumbnail co lại */
            width: 121px;
            /* Kích thước cố định */
            height: 135px;
            /* Kích thước cố định */
            border-radius: 4px;
            /* Bo góc nhẹ */
            cursor: pointer;
            /* Con trỏ tay khi hover */
            transition: border-color 0.2s, box-shadow 0.2s;
            /* Hiệu ứng chuyển đổi */
        }

        .thumbnail-item img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            /* Giữ tỷ lệ ảnh */
            border-radius: 4px;
            /* Bo góc nhẹ */
            image-rendering: crisp-edges;
            /* Giảm mờ khi scale ảnh */
        }

        /* Lightbox Thumbnails */
        #lightbox-thumbnails {
            display: flex;
            flex-wrap: nowrap;
            /* Ngăn wrap xuống dòng */
            justify-content: center;
            /* Căn giữa các thumbnail */
            gap: 8px;
            /* Khoảng cách giữa các thumbnail */
            overflow-x: auto;
            /* Cho phép cuộn ngang nếu cần */
            padding: 4px 0;
            /* Padding để tránh dính mép */
            scrollbar-width: none;
            /* Ẩn thanh cuộn trên Firefox */
            -ms-overflow-style: none;
            /* Ẩn thanh cuộn trên IE/Edge */
        }

        #lightbox-thumbnails::-webkit-scrollbar {
            display: none;
            /* Ẩn thanh cuộn trên Chrome/Safari */
        }

        #lightbox-thumbnails img {
            width: 100px;
            /* Kích thước cố định cho thumbnail */
            height: 100px;
            /* Tỷ lệ phù hợp với hình ảnh */
            object-fit: cover;
            /* Giữ tỷ lệ ảnh */
            border-radius: 4px;
            /* Bo góc nhẹ */
            cursor: pointer;
            /* Con trỏ tay khi hover */
            transition: opacity 0.2s;
            /* Hiệu ứng hover */
        }

        #lightbox-thumbnails img:hover {
            opacity: 0.8;
            /* Hiệu ứng mờ khi hover */
        }

        /* Đảm bảo container không bị lệch khi cuộn */
        .max-w-4xl {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
@endpush


@push('scripts')
    <script>
        window.productType = @json($product->type);
        console.log('Loại sản phẩm:', window.productType);
        window.variantData = @json($variantData);
        window.attributeOrder = @json($attributeOrder);
        window.availableCombinations = @json($availableCombinations);
        window.attributes = @json($attributes);
        window.variantSpecs = @json($variantSpecs);
        window.currentProductId = @json($product->id);
        window.currentSelections = @php
            $initialSelections = $defaultVariant ? $defaultVariant->attributeValues->pluck('value', 'attribute.name')->all() : [];
            echo json_encode($initialSelections);
        @endphp;

        @php
            $initialImages = [];
            if ($defaultVariant && $defaultVariant->images->count()) {
                $initialImages = $defaultVariant->images->map(fn($img) => Storage::url($img->path))->toArray();
            } elseif ($product->coverImage) {
                $initialImages[] = Storage::url($product->coverImage->path);
            }
            foreach ($product->galleryImages as $galleryImage) {
                $initialImages[] = Storage::url($galleryImage->path);
            }
            $initialImages = array_unique($initialImages);
        @endphp

        window.initialImages = @json($initialImages);
        window.defaultVariantId = @json($defaultVariant ? $defaultVariant->id : null); // Thêm variant_id mặc định

        document.addEventListener('DOMContentLoaded', function() {
            saveRecentProduct();
            // GALLERY DATA
            let galleryData = window.initialImages.map((img, index) => ({
                thumb: img,
                main: img,
                lightbox: img,
                description: `Hình ảnh ${index + 1}`,
                type: 'image'
            }));
            let currentImageIndex = 0;

            // ELEMENTS
            const mainImage = document.getElementById('mainImage');
            const mainImageContainer = document.getElementById('main-image-container');
            const mainThumbnailsContainer = document.getElementById('main-thumbnails');
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
            const prevBtn = document.getElementById('gallery-prev-btn');
            const nextBtn = document.getElementById('gallery-next-btn');
            const thumbsPrevBtn = document.getElementById('thumbs-prev-btn');
            const thumbsNextBtn = document.getElementById('thumbs-next-btn');
            const priceEls = document.querySelectorAll('#product-price');
            const originalPriceEls = document.querySelectorAll('#original-price');
            const discountPercentEls = document.querySelectorAll('#discount-percent');
            const flashSaleBlock = document.getElementById('flash-sale-block');
            const normalPriceBlock = document.getElementById('normal-price-block');
            const statusEl = document.getElementById('variant-status');
            const selectedColorName = document.getElementById('selected-color-name');
            const tabDescBtn = document.getElementById('tab-desc-btn');
            const tabSpecsBtn = document.getElementById('tab-specs-btn');
            const tabDescContent = document.getElementById('tab-desc-content');
            const tabSpecsContent = document.getElementById('tab-specs-content');
            const descriptionWrapper = document.getElementById('description-wrapper');
            const readMoreBtn = document.getElementById('read-more-btn');
            const compareBtn = document.getElementById('compare-btn');
            const compareModal = document.getElementById('compare-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const suggestedProductsContainer = document.getElementById('suggested-products');
            const comparisonBar = document.getElementById('comparison-bar');
            const clearCompareBtn = document.getElementById('clear-compare-btn');
            const compareNowBtn = document.getElementById('compare-now-btn');

            const variantData = window.variantData || {};
            const attributeOrder = window.attributeOrder || [];
            const availableCombinations = window.availableCombinations || {};
            let currentSelections = window.currentSelections || {};

            // Hàm format giá tiền sang định dạng VND
            function formatPrice(price) {
                return price.toLocaleString('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                });
            }

            // Hàm lấy sản phẩm đã xem từ localStorage
            async function fetchSuggestedProducts(variantId) {
                try {
                    const recentProducts = JSON.parse(localStorage.getItem('recent_product_ids') || '[]');
                    console.log('Gửi danh sách sản phẩm đã xem:', recentProducts);
                    const response = await fetch('/api/compare-suggestions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            variant_id: variantId,
                            recent_product_ids: recentProducts.map(item => ({
                                id: item.id,
                                variant_key: item.variant_key
                            }))
                        })
                    });
                    const data = await response.json();
                    if (response.ok && data.suggested) {
                        renderSuggestedProducts(data.suggested);
                    } else {
                        suggestedProductsContainer.innerHTML =
                            '<p class="text-gray-500">Chưa có sản phẩm nào đã xem gần đây.</p>';
                    }
                } catch (error) {
                    console.error('Lỗi khi lấy sản phẩm đã xem:', error);
                    suggestedProductsContainer.innerHTML =
                        '<p class="text-red-500">Lỗi khi tải sản phẩm đã xem gần đây.</p>';
                }
            }

            function saveRecentProduct() {
                const productId = window.currentProductId;
                const productName = @json($product->name);
                const attributeOrder = window.attributeOrder || [];
                const currentSelections = window.currentSelections || {};
                const variantKey = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
                const variantName = attributeOrder.map(attr => currentSelections[attr]).filter(Boolean).join(' ');
                const variant = window.variantData[variantKey];

                // Fallback ảnh: ưu tiên ảnh variant, sau đó ảnh đại diện sản phẩm
                let image = variant?.image;
                if (!image || typeof image !== 'string') {
                    image = @json($product->coverImage ? Storage::url($product->coverImage->path) : '/images/no-image.png');
                }

                // Ép giá về số nguyên (nếu có)
                const price = variant?.price ? parseInt(variant.price) : parseInt(@json($product->price));
                const salePrice = variant?.sale_price ? parseInt(variant.sale_price) : null;

                // Lấy danh sách sản phẩm đã lưu
                let recentProducts = JSON.parse(localStorage.getItem('recent_product_ids') || '[]');

                // Lọc bỏ bản ghi hỏng hoặc trùng
                recentProducts = recentProducts.filter(item =>
                    typeof item === 'object' &&
                    item.id &&
                    item.variant_key !== undefined &&
                    !(item.id === productId && item.variant_key === variantKey)
                );

                // Thêm sản phẩm mới vào đầu danh sách
                recentProducts.unshift({
                    id: productId,
                    name: productName,
                    variant_key: variantKey,
                    variant_name: variantName,
                    image: image,
                    price: price,
                    sale_price: salePrice
                });

                // Giới hạn tối đa 10 sản phẩm
                recentProducts = recentProducts.slice(0, 10);

                // Lưu vào localStorage
                localStorage.setItem('recent_product_ids', JSON.stringify(recentProducts));
                console.log('✅ Đã lưu sản phẩm vào danh sách đã xem:', recentProducts);
            }

            function renderSuggestedProducts(products) {
                suggestedProductsContainer.innerHTML = '';
                products.forEach(product => {
                    const hasSale = product.sale_price && product.sale_price < product.price;
                    const discount = hasSale ? Math.round((1 - product.sale_price / product.price) * 100) :
                        0;
                    const displayPrice = hasSale ? product.sale_price : product.price;
                    const imageUrl = product.cover_image || '/images/no-image.png';
                    const productNameWithVariant = product.variant_name ?
                        `${product.name} ${product.variant_name}` :
                        product.name;
                    const productHtml = `
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-transparent hover:border-blue-500 hover:bg-white transition-all">
                <img src="${imageUrl}" 
                     alt="${productNameWithVariant}" 
                     class="w-24 h-24 object-cover rounded-md flex-shrink-0">
                <div class="flex-grow">
                    <p class="font-bold text-lg text-red-600">
                        ${formatPrice(displayPrice)}
                        ${hasSale ? `
                                    <span class="text-sm text-gray-500 line-through ml-2">${formatPrice(product.price)}</span>
                                    <span class="text-sm font-semibold text-red-500 bg-red-100 px-2 py-0.5 rounded-md">-${discount}%</span>
                                ` : ''}
                    </p>
                    <p class="font-semibold text-gray-800 mt-1">${productNameWithVariant}</p>
                </div>
                <button class="add-to-compare flex items-center gap-1.5 text-blue-600 font-semibold text-sm hover:text-blue-800 flex-shrink-0"
                        data-product-id="${product.id}"
                        data-product-name="${productNameWithVariant}"
                        data-product-image="${imageUrl}"
                        data-product-variant="${product.variant_id}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Thêm vào so sánh
                </button>
            </div>
        `;
                    suggestedProductsContainer.insertAdjacentHTML('beforeend', productHtml);
                });
            }

            // Hàm mở modal và lấy sản phẩm gợi ý
            function openCompareModal() {
                if (!window.defaultVariantId) {
                    console.error('Không tìm thấy defaultVariantId');
                    suggestedProductsContainer.innerHTML =
                        '<p class="text-red-500">Lỗi: Không xác định được biến thể sản phẩm.</p>';
                    compareModal.classList.remove('hidden');
                    return;
                }
                compareModal.dataset.variantId = window.defaultVariantId;
                compareModal.classList.remove('hidden');
                fetchSuggestedProducts(window.defaultVariantId);
            }

            // Hàm đóng modal
            function closeCompareModal() {
                compareModal.classList.add('hidden');
                suggestedProductsContainer.innerHTML = '';
            }

            // Xử lý thêm sản phẩm vào so sánh
            suggestedProductsContainer?.addEventListener('click', (e) => {
                const btn = e.target.closest('.add-to-compare');
                if (btn) {
                    const slot = Array.from(document.querySelectorAll('[data-product-slot]'))
                        .find(slot => !slot.dataset.productId);
                    if (slot) {
                        slot.dataset.productId = btn.dataset.productId;
                        slot.dataset.productName = btn.dataset.productName;
                        slot.dataset.productVariant = btn.dataset.productVariant;
                        slot.innerHTML = `
                            <img src="${btn.dataset.productImage || 'https://placehold.co/32x32/e0e0e0/333?text=Product'}" 
                                 alt="${btn.dataset.productName}" class="w-8 h-8 object-cover rounded-md">
                            <span class="text-sm text-white">${btn.dataset.productName}${btn.dataset.productVariant ? ' (Biến thể #' + btn.dataset.productVariant + ')' : ''}</span>
                        `;
                    } else {
                        alert('Đã đạt giới hạn 3 sản phẩm so sánh!');
                    }
                }
            });

            // Xóa tất cả sản phẩm so sánh
            clearCompareBtn?.addEventListener('click', () => {
                document.querySelectorAll('[data-product-slot]').forEach(slot => {
                    slot.dataset.productId = '';
                    slot.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="text-sm text-gray-400">Sản phẩm ${slot.dataset.productSlot}</span>
                    `;
                });
            });

            // Xử lý so sánh ngay
            compareNowBtn?.addEventListener('click', () => {
                const productIds = Array.from(document.querySelectorAll('[data-product-slot]'))
                    .filter(slot => slot.dataset.productId)
                    .map(slot => slot.dataset.productId);
                if (productIds.length > 0) {
                    window.location.href = `/compare?products=${productIds.join(',')}`;
                } else {
                    alert('Vui lòng chọn ít nhất một sản phẩm để so sánh!');
                }
            });

            // Hàm lấy danh sách so sánh từ localStorage
            function getCompareList() {
                try {
                    return JSON.parse(localStorage.getItem('compareList') || '[]');
                } catch {
                    return [];
                }
            }
            // Hàm lưu danh sách so sánh vào localStorage
            function setCompareList(list) {
                localStorage.setItem('compareList', JSON.stringify(list));
            }
            // Hàm render các slot trong modal so sánh
            function renderCompareSlots() {
                const slots = document.querySelectorAll('[data-product-slot]');
                const list = getCompareList();
                slots.forEach((slot, i) => {
                    const item = list[i];
                    if (item) {
                        slot.className =
                            'flex flex-col items-center justify-center h-40 bg-gray-50 relative rounded-2xl shadow border border-gray-200 mx-2';
                        slot.dataset.productId = item.id;
                        slot.dataset.productName = item.name;
                        slot.dataset.productVariant = item.variant_id;
                        slot.innerHTML = `
                <button class="remove-compare absolute top-2 right-2 text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-100 rounded-full w-7 h-7 flex items-center justify-center transition z-10" title="Xóa khỏi so sánh" data-index="${i}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg mx-auto mt-2">
                <div class="flex flex-col items-center justify-center mt-2">
                    <span class="text-base text-gray-900 font-bold leading-tight text-center">${item.name}</span>
                    ${item.variant_name ? `<span class="text-sm text-gray-500 text-center">${item.variant_name}</span>` : ''}
                </div>
            `;
                    } else {
                        slot.className =
                            'flex flex-col items-center justify-center h-40 bg-gray-50 relative rounded-2xl border-2 border-dashed border-gray-300 mx-2';
                        slot.dataset.productId = '';
                        slot.innerHTML = `
                <span class="text-3xl text-gray-300">+</span>
                <span class="text-sm text-gray-400 mt-1">Thêm sản phẩm</span>
            `;
                    }
                });
            }
            // Sự kiện xóa sản phẩm khỏi so sánh
            document.getElementById('comparison-bar')?.addEventListener('click', function(e) {
                const btn = e.target.closest('.remove-compare');
                if (btn) {
                    const idx = parseInt(btn.dataset.index);
                    let list = getCompareList();
                    list.splice(idx, 1);
                    setCompareList(list);
                    renderCompareSlots();
                }
            });
            // Sự kiện cho nút So sánh (thêm vào localStorage và mở modal)
            compareBtn?.addEventListener('click', () => {
                const productId = @json($product->id);
                const productName = @json($product->name);
                const attributeOrder = window.attributeOrder || [];
                const currentSelections = window.currentSelections || {};
                const variantKey = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
                const variant = window.variantData[variantKey];
                const productImage = variant?.image || @json(
                    $product->coverImage
                        ? Storage::url($product->coverImage->path)
                        : 'https://placehold.co/100x100/e0e0e0/333?text=Product');
                const variantName = attributeOrder.map(attr => currentSelections[attr]).filter(Boolean)
                    .join(', ');
                let list = getCompareList();
                // Không thêm trùng
                if (!list.some(item => item.id == productId && item.variant_id == variant?.variant_id)) {
                    if (list.length >= 3) {
                        alert('Bạn chỉ có thể so sánh tối đa 3 sản phẩm!');
                        return;
                    }
                    list.push({
                        id: productId,
                        name: productName,
                        image: productImage,
                        variant_id: variant?.variant_id,
                        variant_name: variantName
                    });
                    setCompareList(list);
                }
                renderCompareSlots();
                openCompareModal();
            });
            // Khi mở modal, luôn render lại slot
            compareModal?.addEventListener('show', renderCompareSlots);
            // Khi load trang, render slot nếu modal đã mở
            document.addEventListener('DOMContentLoaded', renderCompareSlots);

            // Các hàm và logic hiện có (giữ nguyên)
            function getVariantKey() {
                if (window.productType !== 'variable') {
                    console.log('Sản phẩm không có biến thể, getVariantKey trả về chuỗi rỗng');
                    return '';
                }
                const key = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
                console.log('Sinh ra variant key:', key);
                return key;
            }

            function updateCountdown(endTimeStr) {
                const timer = document.getElementById('countdown-timer');
                if (!timer || !endTimeStr) {
                    console.warn('Countdown timer or end time not found');
                    return;
                }

                function update() {
                    const endTime = new Date(endTimeStr).getTime();
                    const now = new Date().getTime();
                    const distance = endTime - now;
                    if (distance <= 0) {
                        timer.querySelector('#hours').textContent = '00';
                        timer.querySelector('#minutes').textContent = '00';
                        timer.querySelector('#seconds').textContent = '00';
                        flashSaleBlock?.classList.add('hidden');
                        normalPriceBlock?.classList.remove('hidden');
                        return;
                    }
                    const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
                    const minutes = Math.floor((distance / (1000 * 60)) % 60);
                    const seconds = Math.floor((distance / 1000) % 60);
                    timer.querySelector('#hours').textContent = String(hours).padStart(2, '0');
                    timer.querySelector('#minutes').textContent = String(minutes).padStart(2, '0');
                    timer.querySelector('#seconds').textContent = String(seconds).padStart(2, '0');
                }
                update();
                if (timer._interval) clearInterval(timer._interval);
                timer._interval = setInterval(update, 1000);
            }

            function updateAvailableOptions() {
                if (window.productType !== 'variable') {
                    console.log('Sản phẩm không có biến thể, không cần updateAvailableOptions');
                    return;
                }
                if (!availableCombinations || !attributeOrder) {
                    console.error('availableCombinations or attributeOrder is missing');
                    return;
                }
                let newlyAvailableOptions = {};
                attributeOrder.forEach((attrName, attrIndex) => {
                    newlyAvailableOptions[attrName] = new Set();
                    availableCombinations.forEach(combination => {
                        let isMatch = true;
                        for (let i = 0; i < attrIndex; i++) {
                            const prevAttr = attributeOrder[i];
                            if (
                                currentSelections[prevAttr] &&
                                currentSelections[prevAttr] !== combination[prevAttr]
                            ) {
                                isMatch = false;
                                break;
                            }
                        }
                        if (isMatch && combination[attrName]) {
                            newlyAvailableOptions[attrName].add(combination[attrName]);
                        }
                    });
                    console.log(`Các lựa chọn khả dụng cho ${attrName}:`, Array.from(newlyAvailableOptions[
                        attrName]));
                    document.querySelectorAll(`.option-container[data-attr-name="${attrName}"]`).forEach(
                        container => {
                            const value = container.getAttribute('data-attr-value');
                            const input = container.querySelector('input[type="radio"]');
                            if (newlyAvailableOptions[attrName].has(value)) {
                                container.style.display = 'inline-block';
                            } else {
                                container.style.display = 'none';
                                if (input && input.checked) {
                                    input.checked = false;
                                    console.log(`Bỏ chọn ${attrName}: ${value} vì không khả dụng`);
                                }
                            }
                        });
                    if (
                        !newlyAvailableOptions[attrName].has(currentSelections[attrName]) &&
                        newlyAvailableOptions[attrName].size > 0
                    ) {
                        const firstValue = Array.from(newlyAvailableOptions[attrName])[0];
                        console.log(`Đặt lại ${attrName} về giá trị khả dụng đầu tiên: ${firstValue}`);
                        currentSelections[attrName] = firstValue;
                        const input = document.querySelector(
                            `input[data-attr-name="${attrName}"][value="${firstValue}"]`);
                        if (input) input.checked = true;
                    }
                });
                console.log('Cập nhật currentSelections:', currentSelections);
                if (selectedColorName && currentSelections['Màu sắc']) {
                    selectedColorName.textContent = currentSelections['Màu sắc'];
                } else if (selectedColorName) {
                    selectedColorName.textContent = 'N/A';
                }
                updateSelectedStyles();
                updateVariantInfo();
            }

            function updateSpecifications(variantKey) {
                const container = document.getElementById('specs-accordion');
                if (!container) {
                    console.error('Không tìm thấy container specs-accordion');
                    return;
                }
                if (!window.variantSpecs[variantKey] && window.productType === 'variable') {
                    console.warn('Không tìm thấy dữ liệu specs cho variantKey:', variantKey);
                    container.innerHTML = '<p>Không có thông số kỹ thuật cho biến thể này.</p>';
                    return;
                }
                if (!variantKey && window.productType !== 'variable') {
                    if (window.variantSpecs['default']) {
                        variantKey = 'default';
                    } else {
                        console.warn('Không có thông số kỹ thuật cho sản phẩm đơn giản');
                        container.innerHTML = '<p>Không có thông số kỹ thuật.</p>';
                        return;
                    }
                }
                const specs = window.variantSpecs[variantKey];
                let html = '';
                for (const groupName in specs) {
                    html += `
                        <div>
                            <button class="accordion-button w-full flex justify-between items-center p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                <span class="font-semibold text-gray-800">${groupName}</span>
                                <svg class="accordion-icon w-5 h-5 text-gray-600 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="accordion-content" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease;">
                                <div class="p-4 border border-t-0 border-gray-200 rounded-b-lg">
                                    <dl class="divide-y divide-gray-100">
                    `;
                    for (const specName in specs[groupName]) {
                        html += `
                            <div class="px-1 py-2 grid grid-cols-3 gap-4">
                                <dt class="text-sm font-medium text-gray-600">${specName}</dt>
                                <dd class="text-sm text-gray-800 col-span-2">${specs[groupName][specName]}</dd>
                            </div>
                        `;
                    }
                    html += `
                                    </dl>
                                </div>
                            </div>
                        </div>
                    `;
                }
                container.innerHTML = html;
                const accordionButtons = container.querySelectorAll('.accordion-button');
                accordionButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const content = button.nextElementSibling;
                        const icon = button.querySelector('.accordion-icon');
                        if (content.style.maxHeight && content.style.maxHeight !== '0px') {
                            content.style.maxHeight = '0px';
                            icon.classList.remove('rotate-180');
                        } else {
                            content.style.maxHeight = content.scrollHeight + 'px';
                            icon.classList.add('rotate-180');
                        }
                    });
                });
                console.log('Đã cập nhật thông số kỹ thuật cho variantKey:', variantKey);
            }

            function updateVariantInfo() {
                if (window.productType !== 'variable') {
                    console.log('Sản phẩm không có biến thể, không cần updateVariantInfo');
                    return;
                }
                const key = getVariantKey();
                const variant = variantData[key];
                console.log('Biến thể cho key:', key, variant);
                if (!variant) {
                    console.error('Không tìm thấy biến thể cho key:', key);
                    return;
                }
                const now = new Date();
                let isFlashSale = false;
                let isSale = false;
                let discountPercent = 0;
                let salePrice = parseInt(variant.sale_price);
                let originalPrice = parseInt(variant.price);
                if (variant.sale_price_starts_at && variant.sale_price_ends_at) {
                    const start = new Date(variant.sale_price_starts_at);
                    const end = new Date(variant.sale_price_ends_at);
                    isFlashSale = salePrice && start <= now && now <= end;
                }
                isSale = !isFlashSale && salePrice && salePrice < originalPrice;
                discountPercent = (isFlashSale || isSale) ? Math.round(100 - (salePrice / originalPrice) * 100) : 0;
                const displayPrice = (isFlashSale || isSale) ? salePrice : originalPrice;
                priceEls.forEach(el => el.textContent = displayPrice.toLocaleString('vi-VN') + '₫');
                originalPriceEls.forEach(el => {
                    if (isFlashSale || isSale) {
                        el.textContent = originalPrice.toLocaleString('vi-VN') + '₫';
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
                discountPercentEls.forEach(el => {
                    if (discountPercent > 0) {
                        el.textContent = `(-${discountPercent}%)`;
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
                if (statusEl && variant.status) statusEl.textContent = variant.status;
                if (isFlashSale) {
                    flashSaleBlock?.classList.remove('hidden');
                    normalPriceBlock?.classList.add('hidden');
                    updateCountdown(variant.sale_price_ends_at);
                } else {
                    flashSaleBlock?.classList.add('hidden');
                    normalPriceBlock?.classList.remove('hidden');
                }
                const titleEl = document.getElementById('product-title');
                if (titleEl) {
                    const dungLuong = currentSelections['Dung lượng lưu trữ'] || '';
                    const mauSac = currentSelections['Màu sắc'] || '';
                    const selectedValues = [dungLuong, mauSac].filter(val => val).join(' ');
                    titleEl.textContent = `${@json($product->name)} ${selectedValues}`;
                    console.log('Tiêu đề sau khi cập nhật:', titleEl.textContent);
                }
                window.updateGalleryFromSelection(key);
                updateSpecifications(key);
                updateStickyBar(key);
                // Lưu sản phẩm với biến thể mới vào lịch sử
                saveRecentProduct();
            }

            function initializeGallery() {
                if (!mainThumbnailsContainer) return;
                galleryData = galleryData.filter(item => {
                    return item.main && !item.main.includes('placeholder.jpg');
                });
                mainThumbnailsContainer.innerHTML = '';
                galleryData.forEach((item, index) => {
                    const thumbDiv = document.createElement('div');
                    thumbDiv.className =
                        `thumbnail-item relative cursor-pointer rounded-md border-2 flex-shrink-0 w-[120px] h-[120px] ${index === 0 ? 'border-blue-500 thumbnail-selected' : 'border-transparent'}`;
                    thumbDiv.onclick = () => window.changeImage(index);
                    const img = document.createElement('img');
                    img.src = item.main || item.thumb;
                    img.alt = `Thumbnail ${index + 1}`;
                    img.className = 'w-[120px] h-[120px] object-cover rounded mb-2';
                    img.style.imageRendering = 'crisp-edges';
                    thumbDiv.appendChild(img);
                    if (item.type !== 'image') {
                        const overlay = document.createElement('div');
                        overlay.className =
                            'absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center rounded';
                        overlay.innerHTML = item.type === 'video' ?
                            `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>` :
                            `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992M2.985 19.644v-4.992h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0011.664 0l3.181-3.183m-11.664 0l4.992-4.993H2.985m0-4.993h4.992m-4.993 0l3.181-3.183a8.25 8.25 0 0111.664 0l3.181 3.183" /></svg>`;
                        thumbDiv.appendChild(overlay);
                    }
                    mainThumbnailsContainer.appendChild(thumbDiv);
                });
                window.changeImage(0);
                updateThumbNavigation();
            }

            window.changeImage = function(index) {
                currentImageIndex = index;
                mainImage.src = galleryData[index].main;
                const thumbnails = mainThumbnailsContainer.querySelectorAll('.thumbnail-item');
                thumbnails.forEach((thumb, i) => {
                    thumb.classList.toggle('thumbnail-selected', i === index);
                    thumb.classList.toggle('border-blue-500', i === index);
                    thumb.classList.toggle('border-transparent', i !== index);
                });
                if (thumbnails[index]) {
                    thumbnails[index].scrollIntoView({
                        behavior: 'smooth',
                        inline: 'center',
                        block: 'nearest'
                    });
                }
            };

            function updateLightboxView() {
                if (!lightboxMainImage || !lightboxDescription || !lightboxCounter) return;
                const item = galleryData[currentImageIndex];
                lightboxMainImage.src = item.lightbox;
                lightboxDescription.textContent = item.description;
                lightboxCounter.textContent = `${currentImageIndex + 1} / ${galleryData.length}`;
                lightboxMainImage.classList.remove('zoomed');
                resetZoomState();
                const thumbs = lightboxThumbnailsContainer?.querySelectorAll('img') || [];
                thumbs.forEach((thumb, i) => {
                    thumb.classList.toggle('ring-2', i === currentImageIndex);
                    thumb.classList.toggle('ring-white', i === currentImageIndex);
                    thumb.classList.toggle('opacity-60', i !== currentImageIndex);
                });
            }

            function openLightbox(index) {
                if (!lightboxModal) return;
                currentImageIndex = index;
                populateLightboxThumbnails();
                updateLightboxView();
                lightboxModal.classList.remove('hidden');
                lightboxModal.classList.add('flex');
            }

            function closeLightbox() {
                if (!lightboxModal) return;
                lightboxModal.classList.add('hidden');
                lightboxModal.classList.remove('flex');
                if (document.fullscreenElement) document.exitFullscreen();
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

            function toggleZoom() {
                if (!lightboxMainImage) return;
                if (isZoomed) {
                    zoomOut();
                } else {
                    zoomIn();
                }
            }

            lightboxMainImage?.addEventListener('mousedown', (e) => {
                if (!isZoomed || isPanning) return;
                e.preventDefault();
                isPanning = true;
                startX = e.clientX - currentTranslateX;
                startY = e.clientY - currentTranslateY;
                lightboxMainImage.style.cursor = 'grabbing';
                lightboxMainImage.style.transition = 'none';
            });

            lightboxMainImage?.addEventListener('mousemove', (e) => {
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

            lightboxMainImage?.addEventListener('mouseup', endPan);
            lightboxMainImage?.addEventListener('mouseleave', endPan);

            function populateLightboxThumbnails() {
                if (!lightboxThumbnailsContainer || lightboxThumbnailsContainer.children.length > 0) return;
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

            window.updateGalleryFromSelection = function(variantKey) {
                const variant = variantData[variantKey];
                console.log('Updating gallery for variant key:', variantKey, variant);
                let images = [];
                if (variant && variant.images && variant.images.length > 0) {
                    if (variant.primary_image_id && variant.image) {
                        images = [variant.image, ...variant.images.filter(img => img !== variant.image)];
                    } else {
                        images = [...variant.images];
                    }
                } else {
                    images = [...window.initialImages];
                }
                images = Array.from(new Set(images.filter(Boolean)));
                galleryData = images.map((img, index) => ({
                    thumb: img,
                    main: img,
                    lightbox: img,
                    description: `Hình ảnh ${index + 1}`,
                    type: 'image'
                }));
                initializeGallery();
            };

            function updateSelectedStyles() {
                attributeOrder.forEach(attrName => {
                    document.querySelectorAll(`input[data-attr-name="${attrName}"]`).forEach(input => {
                        const label = document.querySelector(`label[for="${input.id}"]`);
                        if (!label) return;
                        if (input.checked) {
                            label.classList.add('variant-selected');
                            label.classList.remove('border-gray-300', 'text-gray-700',
                                'hover:border-blue-500');
                        } else {
                            label.classList.remove('variant-selected');
                            label.classList.add('border-gray-300', 'text-gray-700',
                                'hover:border-blue-500');
                        }
                        if (attrName === 'Màu sắc') {
                            label.classList.toggle('ring-blue-500', input.checked);
                            label.classList.toggle('ring-transparent', !input.checked);
                        }
                    });
                });
            }

            function ensureAllAttributesChecked() {
                attributeOrder.forEach(attr => {
                    const checked = document.querySelector(`input[data-attr-name="${attr}"]:checked`);
                    if (!checked) {
                        const first = document.querySelector(`input[data-attr-name="${attr}"]`);
                        if (first) {
                            first.checked = true;
                            currentSelections[attr] = first.value;
                            console.log(`Đặt mặc định cho ${attr}: ${first.value}`);
                        }
                    }
                });
            }

            attributeOrder.forEach(attr => {
                document.querySelectorAll(`input[data-attr-name="${attr}"]`).forEach(input => {
                    input.addEventListener('change', function() {
                        console.log(`Input changed: ${attr} = ${this.value}`);
                        currentSelections[attr] = this.value;
                        updateAvailableOptions();
                        updateVariantInfo();
                    });
                });
            });

            if (mainImageContainer) {
                mainImageContainer.addEventListener('click', (event) => {
                    if (event.target === mainImage) {
                        openLightbox(currentImageIndex);
                    }
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    currentImageIndex = (currentImageIndex - 1 + galleryData.length) % galleryData.length;
                    window.changeImage(currentImageIndex);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    currentImageIndex = (currentImageIndex + 1) % galleryData.length;
                    window.changeImage(currentImageIndex);
                });
            }

            if (thumbsPrevBtn && thumbsNextBtn) {
                thumbsPrevBtn.addEventListener('click', () => {
                    mainThumbnailsContainer.scrollBy({
                        left: -88,
                        behavior: 'smooth'
                    });
                });
                thumbsNextBtn.addEventListener('click', () => {
                    mainThumbnailsContainer.scrollBy({
                        left: 88,
                        behavior: 'smooth'
                    });
                });
            }

            if (closeLightboxBtn) {
                closeLightboxBtn.addEventListener('click', closeLightbox);
            }
            if (lightboxNextBtn) {
                lightboxNextBtn.addEventListener('click', showNextImage);
            }
            if (lightboxPrevBtn) {
                lightboxPrevBtn.addEventListener('click', showPrevImage);
            }
            if (lightboxZoomInBtn) {
                lightboxZoomInBtn.addEventListener('click', zoomIn);
            }
            if (lightboxZoomOutBtn) {
                lightboxZoomOutBtn.addEventListener('click', zoomOut);
            }
            if (lightboxFullscreenBtn) {
                lightboxFullscreenBtn.addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        lightboxModal.requestFullscreen().catch(err => console.error(
                            `Fullscreen error: ${err.message}`));
                    } else {
                        document.exitFullscreen();
                    }
                });
            }
            if (lightboxMainImage) {
                lightboxMainImage.addEventListener('click', toggleZoom);
            }
            document.addEventListener('keydown', (e) => {
                if (lightboxModal?.classList.contains('hidden')) return;
                if (e.key === 'ArrowRight') showNextImage();
                if (e.key === 'ArrowLeft') showPrevImage();
                if (e.key === 'Escape') closeLightbox();
            });

            if (tabDescBtn && tabSpecsBtn && tabDescContent && tabSpecsContent) {
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
            }

            if (descriptionWrapper && readMoreBtn) {
                readMoreBtn.addEventListener('click', () => {
                    if (descriptionWrapper.classList.contains('collapsed')) {
                        descriptionWrapper.classList.remove('collapsed');
                        readMoreBtn.textContent = 'Thu gọn';
                    } else {
                        descriptionWrapper.classList.add('collapsed');
                        readMoreBtn.textContent = 'Xem thêm';
                    }
                });
            }

            const accordionButtons = document.querySelectorAll('.accordion-button');
            if (accordionButtons.length > 0) {
                accordionButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const content = button.nextElementSibling;
                        const icon = button.querySelector('.accordion-icon');
                        if (content.style.maxHeight) {
                            content.style.maxHeight = null;
                            icon.classList.remove('rotate-180');
                        } else {
                            content.style.maxHeight = content.scrollHeight + 'px';
                            icon.classList.add('rotate-180');
                        }
                    });
                });
            }

            function initDragScroll() {
                const mainThumbnailsContainer = document.getElementById('main-thumbnails');
                if (!mainThumbnailsContainer) {
                    console.warn('Element #main-thumbnails not found, drag functionality skipped.');
                    return;
                }
                let isDragging = false;
                let startX, scrollLeft;
                mainThumbnailsContainer.addEventListener('mousedown', (e) => {
                    isDragging = true;
                    startX = e.pageX - mainThumbnailsContainer.offsetLeft;
                    scrollLeft = mainThumbnailsContainer.scrollLeft;
                    mainThumbnailsContainer.style.cursor = 'grabbing';
                    e.preventDefault();
                });
                mainThumbnailsContainer.addEventListener('mouseleave', () => {
                    isDragging = false;
                    mainThumbnailsContainer.style.cursor = 'grab';
                });
                mainThumbnailsContainer.addEventListener('mouseup', () => {
                    isDragging = false;
                    mainThumbnailsContainer.style.cursor = 'grab';
                });
                mainThumbnailsContainer.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    e.preventDefault();
                    const x = e.pageX - mainThumbnailsContainer.offsetLeft;
                    const walk = (x - startX) * 2;
                    mainThumbnailsContainer.scrollLeft = scrollLeft - walk;
                });
                mainThumbnailsContainer.addEventListener('touchstart', (e) => {
                    isDragging = true;
                    startX = e.touches[0].pageX - mainThumbnailsContainer.offsetLeft;
                    scrollLeft = mainThumbnailsContainer.scrollLeft;
                    e.preventDefault();
                });
                mainThumbnailsContainer.addEventListener('touchend', () => {
                    isDragging = false;
                });
                mainThumbnailsContainer.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    const x = e.touches[0].pageX - mainThumbnailsContainer.offsetLeft;
                    const walk = (x - startX) * 2;
                    mainThumbnailsContainer.scrollLeft = scrollLeft - walk;
                    e.preventDefault();
                });
            }

            function updateStickyBar(variantKey) {
                console.log('▶️ Gọi updateStickyBar với key:', variantKey);
                if (!variantKey && window.productType === 'variable') {
                    console.error('⛔ Giá trị variantKey rỗng hoặc không xác định');
                    return;
                }
                if (!variantData) {
                    console.error('⛔ Không tìm thấy dữ liệu variantData');
                    return;
                }
                const variant = window.productType === 'variable' ? variantData[variantKey] : variantData[
                    'default'];
                if (!variant && window.productType === 'variable') {
                    console.error('⛔ Không tìm thấy biến thể với key:', variantKey);
                    return;
                }
                console.log('✅ Biến thể tìm được:', variant);
                const stickyImage = document.getElementById('sticky-image');
                const stickyName = document.getElementById('sticky-name');
                const stickyVariant = document.getElementById('sticky-variant');
                const stickyPrice = document.getElementById('sticky-price');
                const stickyOriginalPrice = document.getElementById('sticky-original-price');
                if (stickyImage) {
                    if (variant?.image) {
                        stickyImage.src = variant.image;
                        console.log('🖼️ Ảnh chính được cập nhật từ variant.image:', variant.image);
                    } else if (variant?.images?.length > 0) {
                        stickyImage.src = variant.images[0];
                        console.log('🖼️ Ảnh được lấy từ variant.images[0]:', variant.images[0]);
                    } else {
                        stickyImage.src = '/images/no-image.png';
                        console.warn('⚠️ Không có ảnh sản phẩm, dùng fallback /images/no-image.png');
                    }
                }
                if (stickyVariant) {
                    if (attributeOrder?.length > 0) {
                        const attrValues = attributeOrder.map(attr => {
                            const selected = document.querySelector(
                                `input[data-attr-name="${attr}"]:checked`);
                            return selected?.value || '';
                        });
                        stickyVariant.textContent = attrValues.join(', ');
                        console.log('🔤 Thuộc tính biến thể:', attrValues);
                    } else {
                        stickyVariant.textContent = '';
                        console.log('ℹ️ Không có attributeOrder hoặc rỗng');
                    }
                }
                const salePrice = parseInt(variant?.sale_price) || 0;
                const originalPrice = parseInt(variant?.price) || 0;
                const displayPrice = salePrice && salePrice < originalPrice ? salePrice : originalPrice;
                const formattedPrice = variant?.formatted_price || displayPrice.toLocaleString('vi-VN') + '₫';
                if (stickyPrice) {
                    stickyPrice.textContent = formattedPrice;
                    console.log('💰 Giá hiển thị:', formattedPrice);
                }
                let isFlashSale = false;
                const now = new Date();
                if (variant?.sale_price_starts_at && variant?.sale_price_ends_at) {
                    const start = new Date(variant.sale_price_starts_at);
                    const end = new Date(variant.sale_price_ends_at);
                    isFlashSale = salePrice && start <= now && now <= end;
                    console.log('⏰ Flash Sale?', isFlashSale,
                        `(Từ ${start.toLocaleString()} đến ${end.toLocaleString()})`);
                }
                const hasSale = salePrice && salePrice < originalPrice;
                if (stickyOriginalPrice) {
                    if ((hasSale || isFlashSale) && originalPrice > 0) {
                        stickyOriginalPrice.textContent = originalPrice.toLocaleString('vi-VN') + '₫';
                        stickyOriginalPrice.classList.remove('hidden');
                        console.log('📉 Giá gốc (gạch):', stickyOriginalPrice.textContent);
                    } else {
                        stickyOriginalPrice.classList.add('hidden');
                        console.log('📉 Không có giảm giá, ẩn giá gốc');
                    }
                }
                console.log('✅ Cập nhật sticky bar hoàn tất\n');
            }

            const stickyBar = document.getElementById('sticky-bar');
            const mainCtaButtons = document.getElementById('main-cta-buttons');
            const scrollObserver = new IntersectionObserver((entries) => {
                if (!entries[0].isIntersecting) {
                    stickyBar.classList.remove('translate-y-full');
                } else {
                    stickyBar.classList.add('translate-y-full');
                }
            }, {
                threshold: 0
            });

            function updateThumbNavigation() {
                const thumbs = mainThumbnailsContainer.querySelectorAll('.thumbnail-item');
                thumbsPrevBtn.classList.toggle('visible', thumbs.length > 5);
                thumbsNextBtn.classList.toggle('visible', thumbs.length > 5);
            }

            window.addEventListener('load', () => {
                if (window.productType === 'variable') {
                    ensureAllAttributesChecked();
                    console.log('Sau khi chạy ensureAllAttributesChecked, currentSelections:',
                        currentSelections);
                    updateAvailableOptions();
                    const defaultKey = getVariantKey();
                    console.log('Variant key khởi tạo:', defaultKey);
                    if (defaultKey) {
                        window.updateGalleryFromSelection(defaultKey);
                        updateSpecifications(defaultKey);
                    } else {
                        initializeGallery();
                    }
                    updateVariantInfo();
                    updateStickyBar(defaultKey);
                } else {
                    console.log('Khởi tạo sản phẩm đơn giản');
                    initializeGallery();
                    updateStickyBar();
                    updateSpecifications('');
                }
                initDragScroll();
                scrollObserver.observe(mainCtaButtons);
            });

            // Gắn sự kiện đóng modal (nút X)
            closeModalBtn?.addEventListener('click', () => {
                closeCompareModal();
            });
        });
    </script>
@endpush
