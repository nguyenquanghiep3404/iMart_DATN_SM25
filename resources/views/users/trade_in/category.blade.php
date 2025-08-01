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
                <span class="text-gray-700">{{ $category->name }}</span>
            </div>
        </nav>
        <!-- Banner khuyến mãi đầu trang -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white">
            <img src="{{ asset('assets/users/logo/2393aa475c2b0e4e992ae0eafb7f12b7.jpg') }}" alt="Banner 1"
                class="rounded-lg shadow-md w-full object-cover">
            <img src="{{ asset('assets/users/logo/44eec25a1b9c40f0449408586f1c142b.jpg') }}" alt="Banner 2"
                class="rounded-lg shadow-md w-full object-cover">
        </div>


        <!-- Danh sách tất cả sản phẩm của danh mục -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-6">{{ $category->name }} đã sử dụng</h2>

            {{-- Flex container cho các bộ lọc và dropdown "Xếp theo" --}}
            <div class="flex items-center justify-between mb-4">
                {{-- Các nút lọc hiện có (Tất cả, Đã sử dụng...) --}}
                <div class="flex items-center gap-4">
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

                {{-- Bộ lọc "Xếp theo" (Dropdown) --}}
                <div class="relative inline-block text-left" id="sort-dropdown-container">
                    <div>
                        <button type="button"
                            class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            id="sort-dropdown-button" aria-expanded="false" aria-haspopup="true">
                            Xếp theo: <span class="font-semibold ml-1" id="selected-sort-text">Chọn giá</span>
                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    {{-- Dropdown menu --}}
                    <div id="sort-dropdown-menu"
                        class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                        role="menu" aria-orientation="vertical" aria-labelledby="sort-dropdown-button" tabindex="-1">
                        <div class="py-1" role="none">
                            {{-- Giá cao đến thấp --}}
                            <a href="?sort=1" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100"
                                role="menuitem" tabindex="-1">
                                <label class="inline-flex items-center cursor-pointer text-base">
                                    <input type="radio" name="sort_by" value="1"
                                        class="w-4 h-4 text-orange-500 focus:ring-orange-500 accent-orange-500 sort-radio"
                                        {{ request()->input('sort') == '1' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-800">Giá cao đến thấp</span>
                                </label>
                            </a>
                            {{-- Giá thấp đến cao --}}
                            <a href="?sort=2" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100"
                                role="menuitem" tabindex="-1">
                                <label class="inline-flex items-center cursor-pointer text-base">
                                    <input type="radio" name="sort_by" value="2"
                                        class="w-4 h-4 text-orange-500 focus:ring-orange-500 accent-orange-500 sort-radio"
                                        {{ request()->input('sort') == '2' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-800">Giá thấp đến cao</span>
                                </label>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-0">
                @foreach ($tradeInItems as $item)
                    {{-- Thêm thẻ neo bao quanh thẻ sản phẩm --}}
                    <a href="{{ route('public.trade-in.show', [
                        'category' => $category->slug, // Đảm bảo tên tham số là 'category'
                        'product' => $item->productVariant->product->slug, // Đảm bảo tên tham số là 'product'
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
                    </a> {{-- Đóng thẻ neo tại đây --}}
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

        {{-- **QUAN TRỌNG:** Thêm đoạn script này vào cuối file Blade của bạn, hoặc trong một file JS riêng --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dropdownButton = document.getElementById('sort-dropdown-button');
                const dropdownMenu = document.getElementById('sort-dropdown-menu');
                const dropdownContainer = document.getElementById('sort-dropdown-container');
                const selectedSortText = document.getElementById('selected-sort-text');
                const sortRadios = document.querySelectorAll('.sort-radio');
                const productTypeRadios = document.querySelectorAll('input[name="product_type"]');

                // Mapping giữa giá trị số của sort và văn bản hiển thị
                const sortMap = {
                    '1': 'Giá cao đến thấp',
                    '2': 'Giá thấp đến cao'
                };

                // Hàm để cập nhật text hiển thị trên nút dropdown
                function updateSelectedSortText() {
                    let currentSort = new URLSearchParams(window.location.search).get('sort');
                    if (!currentSort || !sortMap[
                            currentSort]) { // Kiểm tra cả currentSort có tồn tại trong sortMap không
                        selectedSortText.textContent = 'Chọn giá';
                    } else {
                        selectedSortText.textContent = sortMap[currentSort]; // Lấy văn bản từ map
                    }
                }

                // Hàm để cập nhật trạng thái radio type dựa trên URL
                function updateProductTypeRadios() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const currentType = urlParams.get('type');

                    productTypeRadios.forEach(radio => {
                        if (!currentType && radio.value === 'all') {
                            radio.checked = true;
                        } else if (radio.value === 'tgdd' && currentType === '4') {
                            radio.checked = true;
                        } else if (radio.value === 'trade-in' && currentType === '5') {
                            radio.checked = true;
                        } else {
                            radio.checked = false;
                        }
                    });
                }

                // Đóng/mở dropdown khi click vào nút
                dropdownButton.addEventListener('click', function() {
                    const isExpanded = dropdownButton.getAttribute('aria-expanded') === 'true';
                    dropdownButton.setAttribute('aria-expanded', !isExpanded);
                    dropdownMenu.classList.toggle('hidden');
                });

                // Đóng dropdown khi click ra ngoài container
                document.addEventListener('click', function(event) {
                    if (!dropdownContainer.contains(event.target) && !dropdownMenu.classList.contains(
                            'hidden')) {
                        dropdownMenu.classList.add('hidden');
                        dropdownButton.setAttribute('aria-expanded', 'false');
                    }
                });

                // Xử lý khi chọn một tùy chọn trong dropdown XẾP THEO GIÁ
                sortRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked) {
                            const newSortValue = this.value; // Lấy giá trị số mới (1 hoặc 2)
                            const currentUrl = new URL(window.location.href);

                            currentUrl.searchParams.set('sort', newSortValue);

                            window.location.href = currentUrl.toString();
                        }
                    });
                });

                // Xử lý khi chọn một tùy chọn BỘ LỌC KIỂU SẢN PHẨM
                productTypeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const newType = this.value;
                        const currentUrl = new URL(window.location.href);

                        if (newType === 'all') {
                            currentUrl.searchParams.delete('type');
                        } else if (newType === 'tgdd') {
                            currentUrl.searchParams.set('type', '4');
                        } else if (newType === 'trade-in') {
                            currentUrl.searchParams.set('type', '5');
                        }
                        window.location.href = currentUrl.toString();
                    });
                });

                // --- Khởi tạo ban đầu khi trang tải ---
                updateSelectedSortText();
                updateProductTypeRadios();

                // Đảm bảo radio button 'sort' được chọn đúng khi tải trang
                const currentSortParam = new URLSearchParams(window.location.search).get('sort');
                if (currentSortParam) {
                    sortRadios.forEach(radio => {
                        if (radio.value === currentSortParam) {
                            radio.checked = true;
                        }
                    });
                }
            });
        </script>
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
