@extends('users.layouts.app')

@push('styles')
    <style>
        /* Tùy chỉnh để ẩn thanh cuộn ngang */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Tùy chỉnh giao diện */
        .banner-container {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .banner-container img {
            transition: transform 0.3s ease;
        }

        .banner-container img:hover {
            transform: scale(1.05);
        }

        .header-container {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .select-container select {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            transition: all 0.2s ease;
        }

        .select-container select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .nav-link {
            position: relative;
            padding-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #2563eb;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #2563eb;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .section-header {
            background: linear-gradient(135deg, #3b82f6, #7e22ce);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .section-header h2 {
            font-size: 1.5rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .section-header a {
            transition: all 0.3s ease;
        }

        .section-header a:hover {
            transform: translateX(5px);
        }
    </style>
@endpush

@section('content')
    <div class="container mx-auto p-6">
        <!-- Banner khuyến mãi đầu trang -->
        <div class="banner-container grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <img src="{{ asset('assets/users/logo/2393aa475c2b0e4e992ae0eafb7f12b7.jpg') }}" alt="Banner 1"
                class="rounded-lg w-full object-cover">
            <img src="{{ asset('assets/users/logo/44eec25a1b9c40f0449408586f1c142b.jpg') }}" alt="Banner 2"
                class="rounded-lg w-full object-cover">
        </div>

        <header class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center mb-4">
                <span class="mr-2 text-base text-gray-700">Đang xem tại:</span>
                <div class="relative">
                    <select
                        class="border border-gray-300 rounded-md py-2 px-4 pr-10 text-base appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option selected>Toàn quốc</option>
                        <option>Hà Nội</option>
                        <option>TP. Hồ Chí Minh</option>
                        <option>Đà Nẵng</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                        <svg class="fill-current h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                        </svg>
                    </div>
                </div>
            </div>

            <nav class="flex items-center space-x-6 overflow-x-auto no-scrollbar pb-2">
                @foreach ($parentCategories as $category)
                    <a href="" class="text-base font-medium text-gray-700 hover:text-blue-600 whitespace-nowrap">
                        {{ $category->name }}
                    </a>
                @endforeach
            </nav>
        </header>

        @foreach ($parentCategories as $category)
            @if ($category->tradeInItems->isNotEmpty())
                <section class="bg-white rounded-lg shadow-lg p-6 mb-10">
                    <div class="section-header flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <h2 class="text-white font-bold">
                                {{ $category->name }} đã sử dụng
                            </h2>
                        </div>
                        <a href="{{ route('public.trade-in.category', $category->slug) }}"
                            class="text-white text-sm font-semibold hover:underline">Xem tất cả &rarr;</a>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-0">
                        @foreach ($category->tradeInItems as $item)
                            <a href="{{ route('public.trade-in.show', [
                                'category' => $category->slug,
                                'product' => $item->productVariant->product->slug,
                            ]) }}"
                                class="block border p-4 flex flex-col hover:shadow-xl transition-shadow">
                                @php
                                    $variant = $item->productVariant;
                                    $product = $variant->product ?? null;

                                    $imageToShow = $variant->primaryImage ?? $product?->coverImage;
                                    $imageUrl = $imageToShow
                                        ? Storage::url($imageToShow->path)
                                        : asset('assets/admin/img/placeholder-image.png');
                                    $altText = $imageToShow?->alt_text ?? ($product?->name ?? 'Ảnh sản phẩm');
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $altText }}"
                                    class="w-full h-60 object-contain mx-auto mb-4">
                                <p class="text-base text-gray-400 mb-1">{{ $item->item_count }} sản phẩm</p>
                                <h3 class="font-bold text-lg text-gray-800 mb-2 flex-grow">
                                    {{ $item->productVariant->product->name ?? 'Không rõ tên' }}
                                    @foreach ($item->productVariant->attributeValues ?? [] as $attributeValue)
                                        @php
                                            $attrName = strtolower($attributeValue->attribute->name);
                                        @endphp
                                        @if (!in_array($attrName, ['màu', 'màu sắc', 'color']))
                                            {{ $attributeValue->value }}
                                        @endif
                                    @endforeach
                                </h3>

                                <div class="mt-auto">
                                    <p class="text-red-600 font-bold text-xl">
                                        Từ: {{ number_format($item->selling_price) }}₫
                                        @if ($item->productVariant->price && $item->productVariant->price > $item->selling_price)
                                            <span
                                                class="text-base bg-red-100 text-red-600 font-semibold px-1.5 py-0.5 rounded">
                                                -{{ round((($item->productVariant->price - $item->selling_price) / $item->productVariant->price) * 100) }}%
                                            </span>
                                        @endif
                                    </p>
                                    @if ($item->productVariant->price)
                                        <p class="text-gray-500 text-base">Giá sản phẩm mới:
                                            {{ number_format($item->productVariant->price) }}₫</p>
                                        <p class="text-gray-500 text-base">Tiết kiệm:
                                            {{ number_format($item->productVariant->price - $item->selling_price) }}₫</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
@endsection