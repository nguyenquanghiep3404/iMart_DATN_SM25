@extends('users.layouts.app')

@push('styles')
    <style>
        /* Tùy chỉnh để ẩn thanh cuộn ngang */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
@endpush

@section('content')
    <div class="container mx-auto p-4">
        <!-- Banner khuyến mãi đầu trang -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white">
            <img src="{{ asset('assets/users/logo/2393aa475c2b0e4e992ae0eafb7f12b7.jpg') }}" alt="Banner 1"
                class="rounded-lg shadow-md w-full object-cover">
            <img src="{{ asset('assets/users/logo/44eec25a1b9c40f0449408586f1c142b.jpg') }}" alt="Banner 2"
                class="rounded-lg shadow-md w-full object-cover">
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
        </header>

        <!-- Danh sách tất cả sản phẩm của danh mục -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-6">{{ $category->name }} đã sử dụng</h2>
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
                    <div class="border p-4 flex flex-col hover:shadow-xl transition-shadow">
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
                            @if ($item->productVariant->attributeValues)
                                @foreach ($item->productVariant->attributeValues as $attributeValue)
                                    {{ $attributeValue->value }}
                                @endforeach
                            @endif
                        </h3>
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
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($tradeInItems->hasMorePages())
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
