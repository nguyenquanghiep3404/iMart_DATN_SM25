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

        $imageUrl = $variant->primaryImage ? Storage::url($variant->primaryImage->path) : asset('placeholder.png');
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
    <div id="compare-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
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
                    <input type="text" id="compare-search" placeholder="Nhập sản phẩm bạn muốn so sánh"
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
                <h4 class="text-base font-semibold text-gray-600 mb-4">Gợi ý sản phẩm cùng phân khúc</h4>

                <!-- Suggested Product List -->
                <div id="suggested-products" class="space-y-3">
                    <!-- Product Item 1 -->
                    <div
                        class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-transparent hover:border-blue-500 hover:bg-white transition-all">
                        <img src="https://placehold.co/100x100/e0e0e0/333?text=S25+Ultra" alt="Samsung Galaxy S25 Ultra"
                            class="w-24 h-24 object-cover rounded-md flex-shrink-0">
                        <div class="flex-grow">
                            <p class="font-bold text-lg text-red-600">28.490.000₫ <span
                                    class="text-sm text-gray-500 line-through ml-2">33.990.000₫</span> <span
                                    class="text-sm font-semibold text-red-500 bg-red-100 px-2 py-0.5 rounded-md">-16%</span>
                            </p>
                            <p class="font-semibold text-gray-800 mt-1">Samsung Galaxy S25 Ultra 5G 12GB 256GB</p>
                            <div class="flex gap-2 mt-2">
                                <span class="px-3 py-1 text-xs font-semibold border border-gray-300 rounded-md">256
                                    GB</span>
                                <span class="px-3 py-1 text-xs font-semibold border border-gray-300 rounded-md">512
                                    GB</span>
                                <span class="px-3 py-1 text-xs font-semibold border border-gray-300 rounded-md">1 TB</span>
                            </div>
                        </div>
                        <button
                            class="add-to-compare flex items-center gap-1.5 text-blue-600 font-semibold text-sm hover:text-blue-800 flex-shrink-0"
                            data-product-id="2" data-product-name="Samsung Galaxy S25 Ultra 5G 12GB 256GB"
                            data-product-image="https://placehold.co/100x100/e0e0e0/333?text=S25+Ultra"
                            data-product-variant="256GB">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Thêm vào so sánh
                        </button>
                    </div>
                    <!-- Product Item 2 -->
                    <div
                        class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-transparent hover:border-blue-500 hover:bg-white transition-all">
                        <img src="https://placehold.co/100x100/d0d0f0/333?text=iPhone+16" alt="iPhone 16 Plus"
                            class="w-24 h-24 object-cover rounded-md flex-shrink-0">
                        <div class="flex-grow">
                            <p class="font-bold text-lg text-red-600">21.990.000₫ <span
                                    class="text-sm text-gray-500 line-through ml-2">25.990.000₫</span> <span
                                    class="text-sm font-semibold text-red-500 bg-red-100 px-2 py-0.5 rounded-md">-15%</span>
                            </p>
                            <p class="font-semibold text-gray-800 mt-1">iPhone 16 Plus 128GB</p>
                            <div class="flex gap-2 mt-2">
                                <span
                                    class="px-3 py-1 text-xs font-semibold border-red-500 bg-red-50 text-red-700 rounded-md">128
                                    GB</span>
                                <span class="px-3 py-1 text-xs font-semibold border border-gray-300 rounded-md">256
                                    GB</span>
                                <span class="px-3 py-1 text-xs font-semibold border border-gray-300 rounded-md">512
                                    GB</span>
                            </div>
                        </div>
                        <button
                            class="add-to-compare flex items-center gap-1.5 text-blue-600 font-semibold text-sm hover:text-blue-800 flex-shrink-0"
                            data-product-id="3" data-product-name="iPhone 16 Plus 128GB"
                            data-product-image="https://placehold.co/100x100/d0d0f0/333?text=iPhone+16"
                            data-product-variant="128GB">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Thêm vào so sánh
                        </button>
                    </div>
                    <!-- Product Item 3 -->
                    <div
                        class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-transparent hover:border-blue-500 hover:bg-white transition-all">
                        <img src="https://placehold.co/100x100/c0c0e0/333?text=Xiaomi+15" alt="Xiaomi 15 Pro"
                            class="w-24 h-24 object-cover rounded-md flex-shrink-0">
                        <div class="flex-grow">
                            <p class="font-bold text-lg text-red-600">24.990.000₫ <span
                                    class="text-sm text-gray-500 line-through ml-2">28.990.000₫</span> <span
                                    class="text-sm font-semibold text-red-500 bg-red-100 px-2 py-0.5 rounded-md">-14%</span>
                            </p>
                            <p class="font-semibold text-gray-800 mt-1">Xiaomi 15 Pro 12GB 256GB</p>
                            <div class="flex gap-2 mt-2">
                                <span class="px-3 py-1 text-xs font-semibold border border-gray-300 rounded-md">256
                                    GB</span>
                                <span class="px-3 py-1 text-xs font-semibold border border-gray-300 rounded-md">512
                                    GB</span>
                            </div>
                        </div>
                        <button
                            class="add-to-compare flex items-center gap-1.5 text-blue-600 font-semibold text-sm hover:text-blue-800 flex-shrink-0"
                            data-product-id="4" data-product-name="Xiaomi 15 Pro 12GB 256GB"
                            data-product-image="https://placehold.co/100x100/c0c0e0/333?text=Xiaomi+15"
                            data-product-variant="256GB">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Thêm vào so sánh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Comparison Bar -->
            <div id="comparison-bar" class="flex-shrink-0 bg-gray-800 text-white p-4 rounded-b-xl shadow-lg">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3" id="selected-products">
                        <!-- Sản phẩm hiện tại sẽ được thêm động qua JavaScript -->
                        <div class="hidden md:flex items-center justify-center gap-2 bg-gray-700 p-2 rounded-lg w-48 h-[56px]"
                            data-product-slot="2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="text-sm text-gray-400">Sản phẩm 2</span>
                        </div>
                        <div class="hidden md:flex items-center justify-center gap-2 bg-gray-700 p-2 rounded-lg w-48 h-[56px]"
                            data-product-slot="3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="text-sm text-gray-400">Sản phẩm 3</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button id="clear-compare-btn"
                            class="text-sm font-semibold hover:bg-gray-700 px-4 py-2 rounded-lg">Xóa tất cả</button>
                        <button id="compare-now-btn"
                            class="text-sm font-bold bg-white text-gray-900 px-6 py-2 rounded-lg hover:bg-gray-200">So sánh
                            ngay</button>
                        <button id="toggle-compare-bar" class="p-2 hover:bg-gray-700 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
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
        window.productType = @json($product->type); // 👈 thêm dòng này
        console.log('Loại sản phẩm:', window.productType); // ✅ log kiểm tra
        window.variantData = @json($variantData);
        window.attributeOrder = @json($attributeOrder);
        window.availableCombinations = @json($availableCombinations);
        window.attributes = @json($attributes);
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

        document.addEventListener('DOMContentLoaded', function() {
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

            // Kiểm tra các phần tử cần thiết
            if (!mainImage) {
                console.error('Element #mainImage not found');
                return;
            }
            if (!mainThumbnailsContainer) {
                console.warn('Element #main-thumbnails not found');
            }
            if (!lightboxModal) {
                console.warn('Element #image-lightbox-modal not found');
            }
            if (!prevBtn || !nextBtn) {
                console.error('Không tìm thấy nút prev hoặc next trong gallery');
                return;
            }

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

            const variantData = window.variantData || {};
            const attributeOrder = window.attributeOrder || [];
            const availableCombinations = window.availableCombinations || {};
            let currentSelections = window.currentSelections || {};

            console.log('Giá trị currentSelections ban đầu:', currentSelections);
            console.log('Các tổ hợp biến thể khả dụng:', availableCombinations);

            /**
             * Lấy key của biến thể hiện tại dựa trên các thuộc tính đã chọn.
             * Trả về chuỗi key dạng 'Dung lượng_Màu sắc'...
             */
            function getVariantKey() {
                if (window.productType !== 'variable') {
                    console.log('Sản phẩm không có biến thể, getVariantKey trả về chuỗi rỗng');
                    return '';
                }

                const key = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
                console.log('Sinh ra variant key:', key);
                return key;
            }

            /**
             * Cập nhật đồng hồ đếm ngược cho Flash Sale.
             * Nhận vào thời gian kết thúc, cập nhật số giờ, phút, giây còn lại.
             */
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

            /**
             * Cập nhật các lựa chọn thuộc tính khả dụng dựa trên lựa chọn hiện tại.
             * Ẩn/hiện các option không hợp lệ, tự động chọn lại nếu giá trị hiện tại không còn hợp lệ.
             */
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
                            `input[data-attr-name="${attrName}"][value="${firstValue}"]`
                        );
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

                // Cập nhật thông tin variant và sticky bar khi có thuộc tính bị reset
                updateVariantInfo();
            }

            /**
             * Cập nhật thông tin biến thể (giá, trạng thái, ảnh, sticky bar) khi thay đổi lựa chọn.
             */
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
                discountPercent = (isFlashSale || isSale) ?
                    Math.round(100 - (salePrice / originalPrice) * 100) :
                    0;

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

                // Cập nhật sticky bar
                updateStickyBar(key);
            }

            /**
             * Khởi tạo lại gallery ảnh sản phẩm dựa trên biến thể hoặc sản phẩm đơn giản.
             */
            function initializeGallery() {
                if (!mainThumbnailsContainer) return;
                // ✅ Loại ảnh placeholder (ảnh trắng rỗng)
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

                    // ✅ Ưu tiên ảnh rõ nét hơn
                    img.src = item.main || item.thumb;
                    img.alt = `Thumbnail ${index + 1}`;
                    img.className = 'w-[120px] h-[120px] object-cover rounded mb-2';


                    // ✅ Giảm mờ khi trình duyệt scale ảnh
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

            /**
             * Đổi ảnh chính khi click vào thumbnail.
             * index: chỉ số ảnh được chọn.
             */
            window.changeImage = function(index) {
                currentImageIndex = index;
                mainImage.src = galleryData[index].main;

                const thumbnails = mainThumbnailsContainer.querySelectorAll('.thumbnail-item');
                thumbnails.forEach((thumb, i) => {
                    thumb.classList.toggle('thumbnail-selected', i === index);
                    thumb.classList.toggle('border-blue-500', i === index);
                    thumb.classList.toggle('border-transparent', i !== index);
                });

                // Cuộn thumbnail vào giữa khi chọn
                if (thumbnails[index]) {
                    thumbnails[index].scrollIntoView({
                        behavior: 'smooth',
                        inline: 'center',
                        block: 'nearest'
                    });
                }
            };

            /**
             * Cập nhật giao diện lightbox khi xem ảnh lớn.
             */
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

            /**
             * Mở lightbox xem ảnh lớn ở vị trí index.
             */
            function openLightbox(index) {
                if (!lightboxModal) return;
                currentImageIndex = index;
                populateLightboxThumbnails();
                updateLightboxView();
                lightboxModal.classList.remove('hidden');
                lightboxModal.classList.add('flex');
            }

            /**
             * Đóng lightbox xem ảnh lớn.
             */
            function closeLightbox() {
                if (!lightboxModal) return;
                lightboxModal.classList.add('hidden');
                lightboxModal.classList.remove('flex');
                if (document.fullscreenElement) document.exitFullscreen();
                resetZoomState();
            }

            /**
             * Chuyển sang ảnh tiếp theo trong lightbox.
             */
            function showNextImage() {
                currentImageIndex = (currentImageIndex + 1) % galleryData.length;
                updateLightboxView();
            }

            /**
             * Quay lại ảnh trước trong lightbox.
             */
            function showPrevImage() {
                currentImageIndex = (currentImageIndex - 1 + galleryData.length) % galleryData.length;
                updateLightboxView();
            }

            // Zoom and Pan Logic from Swiper
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

            /**
             * Phóng to ảnh trong lightbox.
             */
            function zoomIn() {
                isZoomed = true;
                lightboxMainImage.style.transition = 'transform 0.3s ease';
                lightboxMainImage.style.transform = 'scale(2.5)';
                lightboxMainImage.style.cursor = 'grab';
            }

            /**
             * Thu nhỏ ảnh trong lightbox về trạng thái ban đầu.
             */
            function zoomOut() {
                resetZoomState();
            }

            /**
             * Bật/tắt chế độ zoom cho ảnh trong lightbox.
             */
            function toggleZoom() {
                if (!lightboxMainImage) return;
                if (isZoomed) {
                    zoomOut();
                } else {
                    zoomIn();
                }
            }

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

            /**
             * Cập nhật gallery ảnh khi chọn biến thể mới.
             * variantKey: key của biến thể.
             */
            window.updateGalleryFromSelection = function(variantKey) {
                const variant = variantData[variantKey];
                console.log('Updating gallery for variant key:', variantKey, variant);

                let images = [];

                // Nếu biến thể có ảnh riêng, dùng ảnh của biến thể
                if (variant && variant.images && variant.images.length > 0) {
                    if (variant.primary_image_id && variant.image) {
                        images = [variant.image, ...variant.images.filter(img => img !== variant.image)];
                    } else {
                        images = [...variant.images];
                    }
                } else {
                    // Ngược lại fallback dùng ảnh sản phẩm chính
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


            /**
             * Cập nhật style cho các lựa chọn thuộc tính (option) khi được chọn/bỏ chọn.
             */
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

            /**
             * Đảm bảo tất cả thuộc tính đều có lựa chọn (nếu chưa thì chọn mặc định).
             */
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

            // Gắn sự kiện cho input radio
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

            // Gắn sự kiện cho gallery
            if (mainImageContainer) {
                mainImageContainer.addEventListener('click', (event) => {
                    if (event.target === mainImage) {
                        openLightbox(currentImageIndex);
                    }
                });
            }

            // Gắn sự kiện cho nút prev và next với stopPropagation
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

            // Gắn sự kiện cho thumbnail navigation
            if (thumbsPrevBtn && thumbsNextBtn) {
                thumbsPrevBtn.addEventListener('click', () => {
                    mainThumbnailsContainer.scrollBy({
                        left: -88,
                        behavior: 'smooth'
                    }); // 80px + 8px gap
                });

                thumbsNextBtn.addEventListener('click', () => {
                    mainThumbnailsContainer.scrollBy({
                        left: 88,
                        behavior: 'smooth'
                    }); // 80px + 8px gap
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

            // Tab switching event listeners
            if (tabDescBtn && tabSpecsBtn && tabDescContent && tabSpecsContent) {
                // Set initial active tab
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

            // Read More button event listener
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

            // Specs Accordion
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
            // Đóng gói mã kéo ngang trong một hàm
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

                // Hỗ trợ chạm trên thiết bị di động
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
                    } else {
                        initializeGallery(); // fallback nếu không có biến thể
                    }

                    updateVariantInfo();
                    updateStickyBar(defaultKey);
                } else {
                    // Nếu là sản phẩm đơn giản
                    console.log('Khởi tạo sản phẩm đơn giản');
                    initializeGallery(); // dùng ảnh mặc định của sản phẩm
                    updateStickyBar(); // hiển thị tên, giá sản phẩm đơn giản
                }
                initDragScroll(); // Khởi tạo kéo ngang cho thumbnail
            });


            const stickyBar = document.getElementById('sticky-bar');
            const mainCtaButtons = document.getElementById('main-cta-buttons');

            const scrollObserver = new IntersectionObserver((entries) => {
                if (!entries[0].isIntersecting) {
                    stickyBar.classList.remove('translate-y-full'); // => hiện ra
                } else {
                    stickyBar.classList.add('translate-y-full'); // => ẩn đi
                }
            }, {
                threshold: 0
            });

            /**
             * Cập nhật sticky bar (thanh mua nhanh dưới cùng) theo biến thể hiện tại.
             * variantKey: key của biến thể.
             */
            function updateStickyBar(variantKey) {
                console.log('▶️ Gọi updateStickyBar với key:', variantKey);

                if (!variantKey) {
                    console.error('⛔ Giá trị variantKey rỗng hoặc không xác định');
                    return;
                }

                if (!variantData) {
                    console.error('⛔ Không tìm thấy dữ liệu variantData');
                    return;
                }

                const variant = variantData[variantKey];
                if (!variant) {
                    console.error('⛔ Không tìm thấy biến thể với key:', variantKey);
                    return;
                }

                console.log('✅ Biến thể tìm được:', variant);

                // Lấy phần tử DOM (nếu không có thì chỉ warning)
                const stickyImage = document.getElementById('sticky-image');
                const stickyName = document.getElementById('sticky-name');
                const stickyVariant = document.getElementById('sticky-variant');
                const stickyPrice = document.getElementById('sticky-price');
                const stickyOriginalPrice = document.getElementById('sticky-original-price');

                // Cập nhật hình ảnh
                if (stickyImage) {
                    if (variant.image) {
                        stickyImage.src = variant.image;
                        console.log('🖼️ Ảnh chính được cập nhật từ variant.image:', variant.image);
                    } else if (variant.images?.length > 0) {
                        stickyImage.src = variant.images[0];
                        console.log('🖼️ Ảnh được lấy từ variant.images[0]:', variant.images[0]);
                    } else {
                        stickyImage.src = '/images/no-image.png';
                        console.warn('⚠️ Không có ảnh sản phẩm, dùng fallback /images/no-image.png');
                    }
                } else {
                    console.warn('⚠️ Không tìm thấy phần tử sticky-image');
                }

                // Cập nhật tên thuộc tính biến thể (VD: màu, dung lượng...)
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
                } else {
                    console.warn('⚠️ Không tìm thấy phần tử sticky-variant');
                }

                // Cập nhật giá hiển thị
                const salePrice = parseInt(variant.sale_price) || 0;
                const originalPrice = parseInt(variant.price) || 0;
                const displayPrice = salePrice && salePrice < originalPrice ? salePrice : originalPrice;
                const formattedPrice = variant.formatted_price || displayPrice.toLocaleString('vi-VN') + '₫';

                if (stickyPrice) {
                    stickyPrice.textContent = formattedPrice;
                    console.log('💰 Giá hiển thị:', formattedPrice);
                } else {
                    console.warn('⚠️ Không tìm thấy phần tử sticky-price');
                }

                // Kiểm tra xem có sale không để hiển thị giá gốc gạch
                let isFlashSale = false;
                const now = new Date();

                if (variant.sale_price_starts_at && variant.sale_price_ends_at) {
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
                } else {
                    console.warn('⚠️ Không tìm thấy phần tử sticky-original-price');
                }

                console.log('✅ Cập nhật sticky bar hoàn tất\n');
            }

            // Kiểm tra nút và modal có tồn tại
            const compareBtn = document.getElementById('compare-btn');
            const compareModal = document.getElementById('compare-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');

            if (!compareBtn) {
                console.error('Nút compare-btn không tồn tại trong DOM');
                return;
            }
            if (!compareModal) {
                console.error('Modal compare-modal không tồn tại trong DOM');
                return;
            }

            // Mở modal khi bấm nút "So sánh"
            compareBtn.addEventListener('click', () => {
                console.log('Nút So sánh được bấm');
                compareModal.classList.remove('hidden');
            });

            // Đóng modal khi bấm nút đóng
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', () => {
                    console.log('Nút đóng modal được bấm');
                    compareModal.classList.add('hidden');
                });
            }

            scrollObserver.observe(mainCtaButtons);

            // Cập nhật navigation thumbnail
            /**
             * Cập nhật navigation thumbnail (ẩn/hiện nút prev/next nếu số lượng thumbnail nhiều).
             */
            function updateThumbNavigation() {
                const thumbs = mainThumbnailsContainer.querySelectorAll('.thumbnail-item');
                thumbsPrevBtn.classList.toggle('visible', thumbs.length > 5);
                thumbsNextBtn.classList.toggle('visible', thumbs.length > 5);
            }
        });
    </script>
@endpush
