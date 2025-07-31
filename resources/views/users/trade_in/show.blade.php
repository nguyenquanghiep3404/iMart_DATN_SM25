@extends('users.layouts.app')

@push('styles')
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .breadcrumb-item:not(:last-child)::after {
            content: '/';
            /* Use a slash as a separator */
            margin: 0 0.75rem;
            /* Adjust horizontal spacing */
            color: #d1d5db;
            /* Light gray color for separator */
        }
    </style>
@endpush

@section('content')
    <div class="container mx-auto p-4">
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

        <!-- Banner khuyến mãi đầu trang -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white mb-6 rounded-lg shadow">
            <img src="{{ asset('assets/users/logo/2393aa475c2b0e4e992ae0eafb7f12b7.jpg') }}" alt="Banner 1"
                class="rounded-lg shadow-md w-full object-cover">
            <img src="{{ asset('assets/users/logo/44eec25a1b9c40f0449408586f1c142b.jpg') }}" alt="Banner 2"
                class="rounded-lg shadow-md w-full object-cover">
        </div>


        <!-- Danh sách sản phẩm thu cũ/mở hộp -->
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 mb-2">
                {{ $tradeInItems->count() }} sản phẩm {{ $productName }} đã sử dụng
            </h1>
            @php
                $originalPrice = optional($tradeInItems->first()->productVariant)->price;
            @endphp
            @if ($originalPrice)
                <p class="text-base text-gray-600">
                    Giá sản phẩm mới:
                    <span class="text-red-600 font-semibold">{{ number_format($originalPrice) }}₫</span>
                    <a href="{{ route('users.products.show', ['slug' => $tradeInItems->first()->productVariant->product->slug]) }}"
           class="text-blue-600 hover:underline ml-2">Xem sản phẩm mới</a>
                </p>
            @endif
            <div class="mb-3"></div>
            <div class="flex items-center gap-4 mb-4">
                <label class="inline-flex items-center cursor-pointer text-base">
                    <input type="radio" name="product_type" value="all"
                        class="w-5 h-5 text-orange-500 focus:ring-orange-500 accent-orange-500"
                        {{ !request()->has('type') ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-800">Tất cả</span>
                </label>
                <label class="inline-flex items-center cursor-pointer text-base">
                    <input type="radio" name="product_type" value="tgdd"
                        class="w-5 h-5 text-orange-500 focus:ring-orange-500 accent-orange-500"
                        {{ request()->input('type') == '4' ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-800">Đã sử dụng (hàng mở hộp mới 99%)</span>
                </label>
                <label class="inline-flex items-center cursor-pointer text-base">
                    <input type="radio" name="product_type" value="trade-in"
                        class="w-5 h-5 text-orange-500 focus:ring-orange-500 accent-orange-500"
                        {{ request()->input('type') == '5' ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-800">Đã sử dụng (hàng thu mua)</span>
                </label>
            </div>
            <div class="grid grid-cols-5 gap-0">
                @foreach ($tradeInItems as $item)
                    <a href="{{ route('public.trade-in.detail', [
                        'category' => $category->slug, // Sử dụng slug của danh mục cha từ hàm show
                        'product' => $item->productVariant->product->slug,
                        'oldid' => $item->id,
                    ]) }}"
                        class="block">

                        {{-- Thẻ sản phẩm --}}
                        <div class="border p-4 flex flex-col hover:shadow-xl transition-shadow relative">
                            @if ($item->type === 'used')
                                <div
                                    class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded">
                                    Đã sử dụng
                                </div>
                            @elseif ($item->type === 'open_box')
                                <div
                                    class="absolute top-2 left-2 bg-green-600 text-white text-xs font-bold px-2 py-1 rounded">
                                    New 99%
                                </div>
                            @endif

                            {{-- Ảnh sản phẩm --}}
                            <img src="{{ $item->images->firstWhere('type', 'primary_image')?->url ?? ($item->images->first()?->url ?? 'https://via.placeholder.com/300') }}"
                                alt="{{ $item->productVariant->product->name ?? 'Sản phẩm' }}"
                                class="w-full max-w-[300px] h-auto object-contain mx-auto mb-4 rounded-lg">


                            {{-- Tên sản phẩm và thuộc tính --}}
                            <h3 class="font-bold text-lg text-gray-800 mb-2 flex-grow">
                                {{ $item->productVariant->product->name ?? 'Không rõ tên' }}
                                @if ($item->productVariant->attributeValues)
                                    @foreach ($item->productVariant->attributeValues as $attributeValue)
                                        {{ $attributeValue->value }}
                                    @endforeach
                                @endif
                            </h3>

                            {{-- Giá và tiết kiệm --}}
                            <div class="mt-auto">
                                <p class="text-red-600 font-bold text-xl">
                                    Từ: {{ number_format($item->selling_price) }}₫
                                    @if ($item->productVariant->price && $item->productVariant->price > $item->selling_price)
                                        <span class="text-base bg-red-100 text-red-600 font-semibold px-1.5 py-0.5 rounded">
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
                                @if ($item->storeLocation)
                                    <p class="text-gray-500 text-base">
                                        Có tại: {{ $item->storeLocation->name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>


            {{-- Nếu có nhiều hơn 1 trang --}}
            @if (method_exists($tradeInItems, 'hasMorePages') && $tradeInItems->hasMorePages())
                <div class="text-center mt-6">
                    <a href="{{ $tradeInItems->nextPageUrl() }}"
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
                        Xem thêm {{ $tradeInItems->perPage() }} sản phẩm
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('input[name="product_type"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    const productType = this.value;
                    const url = new URL(window.location.href);

                    // Xóa tham số type trước
                    url.searchParams.delete('type');

                    // Thêm tham số type nếu không phải 'all'
                    if (productType === 'tgdd') {
                        url.searchParams.set('type', '4');
                    } else if (productType === 'trade-in') {
                        url.searchParams.set('type', '5');
                    }

                    window.location.href = url.toString();
                });
            });
        });
    </script>
@endpush
