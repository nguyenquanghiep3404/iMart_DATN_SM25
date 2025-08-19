@extends('users.layouts.app')

@section('title', isset($searchQuery) ? 'Tìm kiếm: ' . $searchQuery . ' - iMart' : (isset($currentCategory) ?
    $currentCategory->name . ' - iMart' : 'Tất cả sản phẩm - iMart'))

    @push('styles')
        <style>
            /* Glassmorphism & Modern Silver Styles */
            body {
                background: linear-gradient(135deg, #f8fafc 60%, #e5e9f2 100%);
            }

            /* Trong file CSS của bạn */
            /* Thêm đoạn CSS này vào file style.css của bạn */
            @keyframes sparkle {
                0% {
                    color: #ff0000;
                    text-shadow: 0 0 5px #ff0000;
                }

                25% {
                    color: #dede83;
                    text-shadow: 0 0 5px #e8e8b0;
                }

                50% {
                    color: #00ff00;
                    text-shadow: 0 0 5px #00ff00;
                }

                75% {
                    color: #0000ff;
                    text-shadow: 0 0 5px #0000ff;
                }

                100% {
                    color: #ff0000;
                    text-shadow: 0 0 5px #ff0000;
                }
            }

            .sparkle-link {
                animation: sparkle 2s infinite;
                /* Các thuộc tính định dạng chữ */
                font-size: 0.875rem;
                /* Kích thước chữ nhỏ hơn */
                text-transform: uppercase;
                /* Chữ viết hoa */
            }

            .category-banner {
                position: relative;
                overflow: hidden;
                border-radius: 16px;
                border: 1px solid #ebe6e6;
                /* viền xám nhạt */
                /* bo góc container */
            }

            .category-banner img {
                display: block;
                width: 100%;
                /* luôn full chiều ngang */
                height: auto;
                /* giữ tỉ lệ gốc */
                object-fit: cover;
                /* nếu cần crop ảnh */
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
                /* bóng đổ nhẹ */
                transition: transform 0.3s ease;
            }

            .category-banner img:hover {
                transform: scale(1.03);
                /* zoom nhẹ khi hover */
            }


            /* Product Card Styles */
            .product-card {
                background: rgba(250, 251, 253, 0.95);
                border-radius: 28px;
                box-shadow: 0 8px 32px #bfc9d133;
                transition: transform 0.25s, box-shadow 0.25s, border 0.25s;
                position: relative;
                border: 1.5px solid #e5e9f2;
                overflow: hidden;
            }

            .product-card:hover {
                transform: translateY(-7px) scale(1.04);
                box-shadow: 0 16px 40px #bfc9d133, 0 0 16px #e5e9f299;
                border: 1.5px solid #bfc9d1;
            }

            .product-card .badge-sale {
                position: absolute;
                top: 12px;
                left: 12px;
                background: linear-gradient(90deg, #bfc9d1 60%, #e5e9f2 100%);
                color: #222;
                padding: 7px 16px;
                border-radius: 10px;
                font-size: 0.95rem;
                font-weight: 700;
                box-shadow: 0 2px 8px #bfc9d133;
                border: 1.5px solid #bfc9d1;
                z-index: 2;
            }

            .product-card .badge-sale i {
                color: #bfc9d1;
            }

            .product-card .product-card-button {
                background: linear-gradient(90deg, #f5f7fa 60%, #e5e9f2 100%);
                color: #222;
                border-radius: 50%;
                border: 1.5px solid #bfc9d1;
                box-shadow: 0 2px 8px #bfc9d133;
                transition: box-shadow 0.2s, border 0.2s, background 0.2s;
            }

            .product-card .product-card-button:hover {
                background: linear-gradient(90deg, #bfc9d1 60%, #e5e9f2 100%);
                color: #fff;
                box-shadow: 0 4px 16px #bfc9d133, 0 0 8px #e5e9f299;
                border: 1.5px solid #bfc9d1;
            }

            .product-card .product-card-button i {
                color: #bfc9d1;
            }

            .product-card .product-card-button:hover i {
                color: #fff;
            }

            /* Shine effect for card */
            .product-card::after {
                content: '';
                position: absolute;
                top: -60%;
                left: -60%;
                width: 120%;
                height: 120%;
                background: linear-gradient(120deg, rgba(255, 255, 255, 0.13) 0%, rgba(255, 255, 255, 0.0) 60%);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.5s;
            }

            .product-card:hover::after {
                opacity: 1;
                animation: shine 0.8s linear;
            }

            @keyframes shine {
                0% {
                    left: -60%;
                    opacity: 0.2;
                }

                60% {
                    left: 60%;
                    opacity: 0.5;
                }

                100% {
                    left: 120%;
                    opacity: 0;
                }
            }

            /* Responsive Styles */
            @media (max-width: 768px) {
                .category-sidebar {
                    position: fixed;
                    top: 0;
                    left: -100%;
                    width: 90%;
                    height: 100%;
                    transition: left 0.3s;
                    z-index: 1000;
                    overflow-y: auto;
                    box-shadow: 0 8px 32px #bfc9d199;
                }

                .category-sidebar.active {
                    left: 0;
                }

                .col-lg-9 {
                    width: 100%;
                }

                .sort-section {
                    flex-wrap: wrap;
                }

                .sort-section .sort-label {
                    width: 100%;
                    margin-bottom: 10px;
                }

                .price-filter .d-flex {
                    flex-direction: column;
                    gap: 10px;
                }

                .price-filter .mx-2 {
                    display: none;
                }
            }

            /* --- Sắp xếp theo (Sort Filter) --- */
            .sort-section {
                background-color: #fcfcfc;
                /* Consistent with sidebar background (very light off-white) */
                border-radius: 12px;
                /* Consistent with sidebar rounded corners */
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
                /* Consistent with sidebar shadow */
                border: 1px solid #e9ecef;
                /* Subtle border for definition (light grey) */
                padding: 15px 25px;
                /* Adjust padding for better look, similar to sidebar filter sections */
                display: flex;
                /* Use flexbox for alignment of label and options */
                align-items: center;
                /* Vertically center items */
                gap: 20px;
                /* Space between label and sort options */
            }

            .sort-section .sort-label {
                font-size: 1.1rem;
                /* Clearer heading for filters */
                font-weight: 600;
                color: #343a40;
                /* Consistent with filter section headings (dark grey) */
                display: flex;
                align-items: center;
                gap: 8px;
                /* Space between icon and text */
                white-space: nowrap;
                /* Prevent label from wrapping */
            }

            .sort-section .sort-label i {
                font-size: 1.3rem;
                /* Consistent with filter icons */
                color: #dc3545;
                /* Consistent with price filter icon (danger red) */
            }

            .sort-section .sort-options {
                display: flex;
                /* Use flex for the nav links */
                flex-wrap: wrap;
                /* Allow options to wrap if space is limited */
                gap: 8px;
                /* Space between sort options */
                margin-bottom: 0;
                /* Remove default nav margin */
            }

            .sort-section .nav-link {
                padding: 10px 18px;
                /* More balanced padding for links */
                color: #495057;
                /* Consistent dark grey text */
                text-decoration: none;
                transition: background-color 0.25s ease-out, color 0.25s ease-out, box-shadow 0.25s ease-out;
                border-radius: 8px;
                /* Consistent with sidebar category links */
                font-size: 1.02rem;
                /* Slightly larger text */
                font-weight: 500;
                /* Medium weight for good readability */
                border: 1px solid #dee2e6;
                /* Subtle border for each option (light grey) */
                background-color: #ffffff;
                /* White background for inactive options */
            }

            .sort-section .nav-link:hover {
                background-color: #f7f9fb;
                /* Very light grey-blue on hover (from original sidebar child-category hover) */
                color: #212529;
                /* Darker text on hover */
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                /* Subtle shadow on hover (reduced intensity, neutral color) */
                border-color: #d1d9e6;
                /* Slightly darker grey border on hover */
            }

            .sort-section .nav-link.active {
                background-color: #dc3545;
                /* **Primary red for active sort option (from your 'danger' color)** */
                color: #ffffff !important;
                /* White text for active sort option */
                font-weight: 600;
                /* Bolder active */
                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.25);
                /* More prominent shadow for active (red based) */
                transform: translateY(-1px);
                /* Slight lift effect */
                border-color: #dc3545;
                /* Solid red border for active state */
            }

            /* Responsive adjustments (optional, but good practice) */
            @media (max-width: 768px) {
                .sort-section {
                    flex-direction: column;
                    /* Stack label and options on smaller screens */
                    align-items: flex-start;
                    /* Align items to the start when stacked */
                    gap: 10px;
                    /* Reduce gap when stacked */
                    padding: 15px 20px;
                    /* Slightly adjust padding for smaller screens */
                }

                .sort-section .sort-options {
                    width: 100%;
                    /* Make options take full width */
                    justify-content: center;
                    /* Center options horizontally */
                }

                .sort-section .nav-link {
                    flex-grow: 1;
                    /* Allow links to grow to fill space */
                    text-align: center;
                    /* Center text within links */
                }

                .clear-all-filters {
                    margin-left: 10px;
                    font-size: 14px;
                }
            }
        </style>
    @endpush
@section('content')
    @php
        use Illuminate\Support\Str;
        $parentCategories = $categories->whereNull('parent_id');
    @endphp

    <section class="container mt-4">
        {{-- Tiêu đề và Breadcrumb (Include partial) --}}
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2 text-truncate"
                    style="font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Trang chủ</a></li>

                    @if (!empty($searchQuery))
                        <li class="breadcrumb-item active" aria-current="page">Tìm kiếm: "{{ $searchQuery }}"</li>
                    @elseif (isset($currentCategory))
                        <li class="breadcrumb-item">
                            <a href="{{ route('users.products.all') }}">Danh mục sản phẩm</a>
                        </li>

                        @php
                            $ancestors = collect([]);
                            $cat = $currentCategory;
                            while ($cat->parent_id) {
                                $parent = $categories->firstWhere('id', $cat->parent_id);
                                if ($parent) {
                                    $ancestors->prepend($parent);
                                    $cat = $parent;
                                } else {
                                    break;
                                }
                            }
                        @endphp

                        @foreach ($ancestors as $ancestor)
                            <li class="breadcrumb-item">
                                <a href="{{ route('products.byCategory', ['id' => $ancestor->id, 'slug' => Str::slug($ancestor->name)]) }}"
                                    title="{{ $ancestor->name }}">{{ $ancestor->name }}</a>
                            </li>
                        @endforeach

                        <li class="breadcrumb-item active" aria-current="page" title="{{ $currentCategory->name }}">
                            {{ $currentCategory->name }}
                        </li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('users.products.all') }}">Danh mục sản phẩm</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tất cả sản phẩm</li>
                    @endif
                </ol>
            </nav>

            {{-- Banner khu vực danh mục --}}
            <div class="category-banner mb-4 text-center mt-4 rounded-lg shadow-lg overflow-hidden">
                <img src="{{ asset('assets/users/logo/hihi.png') }}" alt="Banner danh mục"
                    class="img-fluid rounded shadow-sm">
            </div>
        </div>

        <div class="row">
            {{-- Sidebar bên trái --}}
            <div class="col-lg-3">
                <button class="btn btn-outline-secondary d-lg-none mb-3"
                    onclick="document.querySelector('.category-sidebar').classList.toggle('active')">
                    <i class="fas fa-bars"></i> Danh mục
                </button>
                @include('users.partials.category_product.product_sidebar')
            </div>

            {{-- Danh sách sản phẩm --}}
            <div class="col-lg-9">
                <div class="loading-spinner"></div>
                @php
                    $sortOptions = [
                        'moi_nhat' => 'Mới nhất',
                        'noi_bat' => 'Nổi bật',
                        'gia_thap_den_cao' => 'Giá tăng dần',
                        'gia_cao_den_thap' => 'Giá giảm dần',
                    ];
                    request('sort', 'moi_nhat');
                @endphp

                <div class="sort-section mb-4 shadow-sm d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="sort-label me-2"><i class="ci-filter me-2 text-danger"></i>Sắp xếp theo</span>
                        <nav class="sort-options nav">
                            @foreach ($sortOptions as $key => $label)
                                <a class="nav-link {{ $currentSort === $key ? 'active' : '' }} px-3 py-2 shadow-none"
                                    href="{{ $currentCategory
                                        ? route(
                                            'products.byCategory',
                                            array_merge(
                                                ['id' => $currentCategory->id, 'slug' => Str::slug($currentCategory->name)],
                                                request()->except('sort', 'page'),
                                                ['sort' => $key],
                                            ),
                                        )
                                        : route('users.products.all', array_merge(request()->except('sort', 'page'), ['sort' => $key])) }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    {{-- Liên kết sản phẩm cũ ở góc phải --}}
                    <div>
                        <a href="{{ route('public.trade-in.index') }}" class="sparkle-link">
                            <i class="ci-rotate-right me-1"></i> Xem sản phẩm cũ tại đây >>>
                        </a>
                    </div>
                </div>

                <div id="applied-filters" class="mb-3 d-flex flex-wrap gap-2 align-items-center"></div>
                @include('users.partials.category_product.shop_products')
            </div>
        </div>
    </section>

    <section style="margin: 80px 0;"></section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productsContainer = document.getElementById('ajax-products-list');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!csrfToken || !productsContainer) {
                console.log('Missing CSRF token or products container');
                return;
            }

            // ===================================
            // Hàm AJAX và các hàm tiện ích
            // ===================================

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            function updateSortLinks(currentUrl) {
                const baseUrl = new URL(currentUrl, window.location.origin);
                document.querySelectorAll('.sort-options .nav-link').forEach(link => {
                    const sortKey = new URL(link.href, window.location.origin).searchParams.get('sort');
                    baseUrl.searchParams.set('sort', sortKey);
                    link.href = baseUrl.toString();
                });
            }

            function ajaxLoad(url, method = 'GET', data = null) {
                productsContainer.classList.add('ajax-loading');
                fetch(url, {
                        method: method,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        cache: 'no-store',
                        body: method === 'POST' ? data : null
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        if (response.ok && contentType && contentType.includes('application/json')) {
                            const json = await response.json();
                            if (json.products && document.getElementById('ajax-products-list')) {
                                document.getElementById('ajax-products-list').innerHTML = json.products;
                            }
                            if (json.sidebar && document.getElementById('ajax-sidebar')) {
                                document.getElementById('ajax-sidebar').innerHTML = json.sidebar;
                            }
                            if (json.title && document.querySelector('h1.page-title')) {
                                document.querySelector('h1.page-title').textContent = json.title;
                            }
                            if (json.breadcrumb_html && document.querySelector('.breadcrumb')) {
                                document.querySelector('.breadcrumb').innerHTML = json.breadcrumb_html;
                            }
                            if (json.currentSort) {
                                document.querySelectorAll('.sort-options .nav-link').forEach(link => {
                                    const sortKey = new URL(link.href, window.location.origin)
                                        .searchParams.get('sort');
                                    link.classList.toggle('active', sortKey === json.currentSort);
                                });
                            }
                            updateSortLinks(url);
                            productsContainer.classList.remove('ajax-loading');
                            window.scrollTo({
                                top: productsContainer.offsetTop - 80,
                                behavior: 'smooth'
                            });
                            updateFilterUI(); // Đồng bộ UI sau khi cập nhật sidebar
                        } else if (response.ok) {
                            const html = await response.text();
                            productsContainer.innerHTML = html;
                            updateSortLinks(url);
                            productsContainer.classList.remove('ajax-loading');
                            window.scrollTo({
                                top: productsContainer.offsetTop - 80,
                                behavior: 'smooth'
                            });
                            updateFilterUI(); // Đồng bộ UI sau khi cập nhật HTML
                        } else {
                            const errorText = await response.text();
                            console.log('AJAX error response:', errorText);
                            productsContainer.classList.remove('ajax-loading');
                            productsContainer.innerHTML = '<p>Đã có lỗi xảy ra. Vui lòng thử lại.<br>' +
                                errorText + '</p>';
                        }
                    })
                    .catch(error => {
                        console.error('AJAX error:', error);
                        productsContainer.classList.remove('ajax-loading');
                        productsContainer.innerHTML = '<p>Đã có lỗi xảy ra. Vui lòng thử lại.</p>';
                    });
            }

            function slideDown(element, duration = 300) {
                element.style.removeProperty('display');
                let display = window.getComputedStyle(element).display;
                if (display === 'none') display = 'block';
                element.style.display = display;
                const height = element.scrollHeight + 'px';
                element.style.overflow = 'hidden';
                element.style.height = '0px';
                element.style.transition = `height ${duration}ms ease`;
                requestAnimationFrame(() => {
                    element.style.height = height;
                });
                setTimeout(() => {
                    element.style.removeProperty('height');
                    element.style.removeProperty('overflow');
                    element.style.removeProperty('transition');
                }, duration);
            }

            function slideUp(element, duration = 300) {
                const height = element.scrollHeight + 'px';
                element.style.height = height;
                element.style.overflow = 'hidden';
                element.style.transition = `height ${duration}ms ease`;
                requestAnimationFrame(() => {
                    element.style.height = '0px';
                });
                setTimeout(() => {
                    element.style.display = 'none';
                    element.style.removeProperty('height');
                    element.style.removeProperty('overflow');
                    element.style.removeProperty('transition');
                }, duration);
            }

            // ===================================
            // Các biến và hàm xử lý bộ lọc
            // ===================================

            const priceToggle = document.querySelector(".price-toggle");
            const priceContent = document.querySelector(".price-filter-content");

            // Mở "Mức giá" ngay khi load trang
            if (priceToggle && priceContent) {
                priceToggle.classList.add("active");
                priceContent.style.maxHeight = priceContent.scrollHeight + "px";
            }

            const storageToggle = document.querySelector(".storage-filter .filter-toggle");
            const storageContent = document.querySelector(".storage-filter .filter-content");

            if (storageToggle && storageContent) {
                storageToggle.classList.add("active");
                storageContent.style.maxHeight = storageContent.scrollHeight + "px";
            }

            // Cập nhật selector
            const priceCheckboxes = document.querySelectorAll('input[name="muc-gia[]"]');
            const storageItems = document.querySelectorAll(".storage-item");
            const storageInput = document.querySelector("#storage-input");
            const appliedFiltersContainer = document.getElementById("applied-filters");
            const priceQuickRanges = document.querySelector('.price-quick-ranges');
            const priceRangeSlider = document.getElementById('price-range-slider');

            function createFilterTag(label, value, type) {
                if (!appliedFiltersContainer) return;
                const tag = document.createElement('div');
                tag.className = 'filter-tag';
                tag.innerHTML = `
            <span>${label}</span>
            <span class="remove-tag" data-value="${value}" data-type="${type}">&times;</span>
        `;
                appliedFiltersContainer.appendChild(tag);
            }

            function updateAppliedFilters() {
                if (!appliedFiltersContainer) {
                    console.error('appliedFiltersContainer không tồn tại');
                    return;
                }
                console.log('Cập nhật thẻ lọc, nội dung trước khi xóa:', appliedFiltersContainer.innerHTML);
                appliedFiltersContainer.innerHTML = ''; // Xóa tất cả các thẻ lọc cũ
                console.log('Đã xóa thẻ cũ, bắt đầu tạo thẻ mới');

                const url = new URL(window.location.href);
                console.log('Tham số URL:', {
                    'muc-gia': url.searchParams.getAll('muc-gia[]'),
                    min_price: url.searchParams.get('min_price'),
                    max_price: url.searchParams.get('max_price'),
                    storage: url.searchParams.get('storage')
                });

                // Xử lý bộ lọc giá từ thanh trượt
                const minPrice = url.searchParams.get('min_price');
                const maxPrice = url.searchParams.get('max_price');
                if (minPrice && maxPrice) {
                    const minPriceFormatted = new Intl.NumberFormat('vi-VN').format(minPrice);
                    const maxPriceFormatted = new Intl.NumberFormat('vi-VN').format(maxPrice);
                    createFilterTag(
                        `${minPriceFormatted}đ - ${maxPriceFormatted}đ`,
                        `${minPrice}-${maxPrice}`,
                        'price-range'
                    );
                    if (priceQuickRanges) {
                        priceQuickRanges.style.display = 'none';
                    }
                } else {
                    const priceRanges = url.searchParams.getAll('muc-gia[]');
                    priceRanges.forEach(value => {
                        if (value !== 'all') {
                            const checkbox = document.querySelector(
                                `input[name="muc-gia[]"][value="${value}"]`);
                            if (checkbox) {
                                const label = checkbox.closest('label').textContent.trim();
                                createFilterTag(label, value, 'price');
                            }
                        }
                    });
                    if (priceQuickRanges) {
                        priceQuickRanges.style.display = 'block';
                    }
                }

                // Xử lý bộ lọc dung lượng
                const storages = url.searchParams.get('storage')?.split(',') || [];
                storages.forEach(value => {
                    const storageItem = document.querySelector(`.storage-item[data-value="${value}"]`);
                    if (storageItem) {
                        const label = storageItem.textContent.trim();
                        createFilterTag(label, value, 'storage');
                    }
                });

                // Thêm nút "Xóa tất cả" nếu có từ 2 bộ lọc trở lên
                const filterCount = countActiveFilters();
                if (filterCount >= 2) {
                    const clearAllButton = document.createElement('button');
                    clearAllButton.className = 'btn btn-outline-danger btn-sm clear-all-filters rounded-pill';
                    clearAllButton.textContent = 'Xóa tất cả';
                    appliedFiltersContainer.appendChild(clearAllButton);
                }
            }

            function updateFilterUI() {
                const url = new URL(window.location.href);
                const priceRanges = url.searchParams.getAll('muc-gia[]');
                const allCheckbox = document.querySelector('input[name="muc-gia[]"][value="all"]');
                const otherCheckboxes = [...priceCheckboxes].filter(cb => cb.value !== 'all');
                const allPriceRanges = otherCheckboxes.map(cb => cb.value);

                console.log('priceRanges from URL:', priceRanges); // Debug

                // Đồng bộ trạng thái check/active cho từng checkbox giá
                priceCheckboxes.forEach(cb => {
                    const isChecked = priceRanges.includes(cb.value);
                    cb.checked = isChecked;
                    const label = cb.closest('label');
                    if (label) {
                        label.classList.toggle('active', isChecked);
                    }
                });

                // Chỉ tick "Tất cả" khi KHÔNG có muc-gia[], min_price và max_price
                if (
                    (priceRanges.length === 0 || priceRanges.length === allPriceRanges.length) &&
                    !url.searchParams.has('min_price') &&
                    !url.searchParams.has('max_price')
                ) {
                    if (allCheckbox) {
                        allCheckbox.checked = true;
                        const allLabel = allCheckbox.closest('label');
                        if (allLabel) {
                            allLabel.classList.add('active');
                        }
                        if (priceRanges.length === 0) {
                            otherCheckboxes.forEach(cb => {
                                cb.checked = false;
                                const label = cb.closest('label');
                                if (label) {
                                    label.classList.remove('active');
                                }
                            });
                        }
                    }
                } else {
                    // Nếu đang lọc giá thì bỏ tick "Tất cả"
                    if (allCheckbox) {
                        allCheckbox.checked = false;
                        const allLabel = allCheckbox.closest('label');
                        if (allLabel) {
                            allLabel.classList.remove('active');
                        }
                    }
                }

                // Cập nhật thanh trượt giá
                if (priceRangeSlider && priceRangeSlider.noUiSlider && minPriceDisplay && maxPriceDisplay) {
                    const minPrice = parseInt(url.searchParams.get('min_price')) || 149000;
                    const maxPrice = parseInt(url.searchParams.get('max_price')) || 141990000;
                    priceRangeSlider.noUiSlider.set([minPrice, maxPrice]);
                    minPriceDisplay.value = new Intl.NumberFormat('vi-VN').format(minPrice) + 'đ';
                    maxPriceDisplay.value = new Intl.NumberFormat('vi-VN').format(maxPrice) + 'đ';
                }

                // Cập nhật bộ lọc dung lượng lưu trữ
                const storages = url.searchParams.get('storage')?.split(',') || [];
                storageItems.forEach(item => {
                    const itemValue = item.getAttribute('data-value');
                    if (storages.includes(itemValue)) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }

            function countActiveFilters() {
                const url = new URL(window.location.href);
                let filterCount = 0;

                // Đếm bộ lọc giá từ muc-gia[]
                if (url.searchParams.getAll('muc-gia[]').length > 0) {
                    filterCount += url.searchParams.getAll('muc-gia[]').length;
                }

                // Đếm bộ lọc giá từ thanh trượt (min_price và max_price)
                if (url.searchParams.has('min_price') && url.searchParams.has('max_price')) {
                    filterCount += 1; // Thanh trượt được tính là 1 bộ lọc
                }

                // Đếm bộ lọc dung lượng
                const storages = url.searchParams.get('storage')?.split(',') || [];
                if (storages.length > 0) {
                    filterCount += storages.length;
                }

                return filterCount;
            }

            // ===================================
            // Khởi tạo các sự kiện
            // ===================================

            // Khởi tạo toggle cho category
            document.querySelectorAll('.toggle-category').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const childUl = this.closest('li').querySelector('.child-categories');
                    if (childUl) {
                        childUl.classList.toggle('open');
                    }
                });
            });

            // Khởi tạo trạng thái active và thẻ lọc ban đầu
            updateAppliedFilters();

            // Xử lý toggle cho phần price-filter
            if (priceToggle && priceContent) {
                priceToggle.addEventListener("click", function() {
                    this.classList.toggle("active");
                    if (priceContent.style.maxHeight && priceContent.style.maxHeight !== "0px") {
                        priceContent.style.maxHeight = "0";
                    } else {
                        priceContent.style.maxHeight = priceContent.scrollHeight + "px";
                    }
                });
            }

            // Xử lý toggle cho phần storage-filter
            if (storageToggle && storageContent) {
                storageToggle.addEventListener("click", function() {
                    this.classList.toggle("active");
                    if (storageContent.style.maxHeight && storageContent.style.maxHeight !== "0px") {
                        storageContent.style.maxHeight = "0";
                    } else {
                        storageContent.style.maxHeight = storageContent.scrollHeight + "px";
                    }
                });
            }

            // Lắng nghe sự kiện click trên các nút xóa thẻ
            if (appliedFiltersContainer) {
                appliedFiltersContainer.addEventListener('click', function(event) {
                    if (event.target.classList.contains('remove-tag')) {
                        const valueToRemove = event.target.getAttribute('data-value');
                        const type = event.target.getAttribute('data-type');
                        const url = new URL(window.location.href);

                        if (type === 'price') {
                            // Cập nhật tham số
                            const priceRanges = url.searchParams.getAll('muc-gia[]');
                            url.searchParams.delete('muc-gia[]');
                            priceRanges.filter(v => v !== valueToRemove).forEach(v => {
                                url.searchParams.append('muc-gia[]', v);
                            });

                            // Cập nhật selector
                            const allCheckbox = document.querySelector(
                                'input[name="muc-gia[]"][value="all"]');
                            if (url.searchParams.getAll('muc-gia[]').length === 0) {
                                if (allCheckbox) {
                                    allCheckbox.checked = true;
                                }
                            }
                        } else if (type === 'storage') {
                            const selectedStorages = url.searchParams.get('storage')?.split(',') || [];
                            const newStorages = selectedStorages.filter(s => s !== valueToRemove).join(',');
                            if (newStorages) {
                                url.searchParams.set('storage', newStorages);
                            } else {
                                url.searchParams.delete('storage');
                            }
                        } else if (type === 'price-range') {
                            url.searchParams.delete('min_price');
                            url.searchParams.delete('max_price');

                            // Đảm bảo checkbox "Tất cả" được chọn sau khi xóa thanh trượt
                            const allCheckbox = document.querySelector(
                                'input[name="muc-gia[]"][value="all"]');
                            if (allCheckbox) {
                                allCheckbox.checked = true;
                            }
                        }

                        window.history.pushState({}, '', url.toString());
                        updateFilterUI();
                        updateAppliedFilters();
                        ajaxLoad(url.toString());
                    }
                });
            }

            // Lắng nghe sự kiện thay đổi trên các checkbox giá
            priceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('min_price');
                    url.searchParams.delete('max_price');
                    url.searchParams.delete('muc-gia[]');

                    const allCheckbox = document.querySelector(
                        'input[name="muc-gia[]"][value="all"]');
                    const otherCheckboxes = [...priceCheckboxes].filter(cb => cb.value !== 'all');
                    const allPriceRanges = otherCheckboxes.map(cb => cb.value);

                    if (this.value === 'all') {
                        if (this.checked) {
                            otherCheckboxes.forEach(otherCheckbox => {
                                otherCheckbox.checked = false;
                            });
                            url.searchParams.delete('muc-gia[]'); // Không thêm all vào URL
                        }
                    } else {
                        if (this.checked) {
                            if (allCheckbox) allCheckbox.checked = false;
                        }
                        if (otherCheckboxes.every(cb => !cb.checked)) {
                            if (allCheckbox) {
                                allCheckbox.checked = true;
                                url.searchParams.delete('muc-gia[]'); // Không thêm all vào URL
                            }
                        } else {
                            const selectedRanges = otherCheckboxes.filter(cb => cb.checked).map(
                                cb => cb.value);
                            if (selectedRanges.length === allPriceRanges.length) {
                                otherCheckboxes.forEach(cb => cb.checked = false);
                                if (allCheckbox) allCheckbox.checked = true;
                                url.searchParams.delete('muc-gia[]'); // Không thêm all vào URL
                            } else {
                                selectedRanges.forEach(value => {
                                    url.searchParams.append('muc-gia[]', value);
                                });
                            }
                        }
                    }

                    console.log('Updated URL:', url.toString());
                    window.history.pushState({}, '', url.toString());
                    updateFilterUI();
                    updateAppliedFilters();
                    ajaxLoad(url.toString());
                });
            });

            // Xử lý chọn nhiều storage-item
            storageItems.forEach(item => {
                item.addEventListener("click", function() {
                    const url = new URL(window.location.href);
                    this.classList.toggle("active");

                    const selectedValues = [...document.querySelectorAll(".storage-item.active")]
                        .map(el => el.getAttribute("data-value"));

                    if (selectedValues.length > 0) {
                        url.searchParams.set('storage', selectedValues.join(','));
                    } else {
                        url.searchParams.delete('storage');
                    }

                    window.history.pushState({}, '', url.toString());
                    updateAppliedFilters();
                    ajaxLoad(url.toString());
                });
            });

            // Tự động chọn ô "Tất cả" khi tải trang (nếu chưa có bộ lọc giá nào)
            const url = new URL(window.location.href);
            if (
                !url.searchParams.has('muc-gia[]') &&
                !url.searchParams.has('min_price') &&
                !url.searchParams.has('max_price')
            ) {
                const allCheckbox = document.querySelector('input[name="muc-gia[]"][value="all"]');
                if (allCheckbox) {
                    allCheckbox.checked = true;
                    const allLabel = allCheckbox.closest('label');
                    if (allLabel) {
                        allLabel.classList.add('active');
                    }
                }
            }

            // Cập nhật UI ban đầu cho cả giá và dung lượng
            updateFilterUI();

            // ===================================
            // LOGIC THANH TRƯỢT
            // ===================================
            const minPriceDisplay = document.getElementById('min-price-display');
            const maxPriceDisplay = document.getElementById('max-price-display');

            if (priceRangeSlider) {
                const initialMin = url.searchParams.get('min_price') || 149000;
                const initialMax = url.searchParams.get('max_price') || 141990000;

                noUiSlider.create(priceRangeSlider, {
                    start: [initialMin, initialMax],
                    connect: true,
                    range: {
                        'min': 149000,
                        'max': 141990000
                    },
                    format: {
                        to: function(value) {
                            return (Math.round(value));
                        },
                        from: function(value) {
                            return Number(value);
                        }
                    }
                });

                priceRangeSlider.noUiSlider.on('update', function(values, handle) {
                    const value = values[handle];
                    const formattedValue = new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                    if (handle === 0) {
                        minPriceDisplay.value = formattedValue;
                    } else {
                        maxPriceDisplay.value = formattedValue;
                    }
                });

                priceRangeSlider.noUiSlider.on('change', function(values, handle) {
                    const minPrice = Math.round(values[0]);
                    const maxPrice = Math.round(values[1]);
                    const url = new URL(window.location.href);

                    url.searchParams.delete('muc-gia[]');
                    url.searchParams.set('min_price', minPrice);
                    url.searchParams.set('max_price', maxPrice);

                    window.history.pushState({}, '', url.toString());
                    updateFilterUI();

                    // Ép bỏ tick "Tất cả" sau khi UI update
                    const allCheckbox = document.querySelector('input[name="muc-gia[]"][value="all"]');
                    if (allCheckbox) {
                        allCheckbox.checked = false;
                        const allLabel = allCheckbox.closest('label');
                        if (allLabel) {
                            allLabel.classList.remove('active');
                        }
                    }

                    updateAppliedFilters();
                    ajaxLoad(url.toString());
                });
            }

            // ===================================
            // Lắng nghe sự kiện click chung
            // ===================================

            document.addEventListener('submit', function(event) {
                const form = event.target.closest('form[data-ajax-filter]');
                if (form) {
                    event.preventDefault();
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData).toString();
                    const url = `${form.action}?${params}`;
                    ajaxLoad(url);
                    window.history.pushState({}, '', url);
                }
            });

            document.addEventListener('click', function(e) {
                const target = e.target.closest('a') || e.target;

                if (!target) return;

                let url = target.href || window.location.href;

                // Xử lý các liên kết lọc
                if (target.classList.contains('category-link') ||
                    target.closest('.rating-filter') ||
                    target.closest('.sort-options') ||
                    target.classList.contains('dropdown-item') ||
                    target.closest('.pagination')
                ) {
                    e.preventDefault();

                    // Nếu là liên kết danh mục, giữ các bộ lọc hiện tại
                    if (target.classList.contains('category-link')) {
                        const currentUrl = new URL(window.location.href);
                        const newUrl = new URL(target.href, window.location.origin);

                        // Giữ các tham số lọc, nếu cần
                        currentUrl.searchParams.forEach((value, key) => {
                            if (key === 'muc-gia[]' || key === 'min_price' || key === 'max_price' ||
                                key === 'storage') {
                                // Thay đổi từ set() sang append() cho các tham số có thể lặp lại
                                if (key === 'muc-gia[]') {
                                    newUrl.searchParams.append(key, value);
                                } else {
                                    newUrl.searchParams.set(key, value);
                                }
                            }
                        });

                        // Đặt lại tham số sort về mặc định khi chuyển danh mục
                        newUrl.searchParams.delete('sort');
                        newUrl.searchParams.set('sort', 'moi_nhat'); // Thêm sort mặc định

                        url = newUrl.toString();
                    }
                    // Nếu là liên kết sắp xếp, giữ nguyên pathname của danh mục hiện tại
                    else if (target.closest('.sort-options') || target.classList.contains(
                            'dropdown-item')) {
                        const currentUrl = new URL(window.location.href);
                        const targetUrl = new URL(target.href, window.location.origin);
                        const sortKey = targetUrl.searchParams.get('sort');

                        const newUrl = new URL(currentUrl.pathname, window.location.origin);
                        newUrl.search = currentUrl.search; // Giữ các query params hiện tại
                        if (sortKey) {
                            newUrl.searchParams.set('sort', sortKey); // Cập nhật sort
                        } else {
                            newUrl.searchParams.delete('sort');
                        }
                        url = newUrl.toString();

                        const sortOptionsContainer = target.closest('.sort-options');
                        if (sortOptionsContainer) {
                            sortOptionsContainer.querySelectorAll('.nav-link').forEach(link => {
                                link.classList.remove('active');
                            });
                            target.classList.add('active');
                        }
                    }

                    ajaxLoad(url);
                    window.history.pushState({}, '', url);
                    return;
                }

                // Xử lý nút "Clear" (nút Clear cũ)
                if (target.classList.contains('btn-outline-secondary') && target.textContent.trim() ===
                    'Clear') {
                    e.preventDefault();
                    const newUrl = new URL(url);
                    newUrl.searchParams.delete('muc-gia[]');
                    newUrl.searchParams.delete('min_price');
                    newUrl.searchParams.delete('max_price');
                    newUrl.searchParams.delete('storage');

                    window.history.pushState({}, '', newUrl.toString());
                    updateFilterUI();
                    updateAppliedFilters();
                    ajaxLoad(newUrl.toString());
                    return;
                }

                // Xử lý nút "Xóa tất cả" (nút mới)
                if (target.classList.contains('clear-all-filters')) {
                    e.preventDefault();
                    const url = new URL(window.location.href);
                    url.searchParams.delete('muc-gia[]');
                    url.searchParams.delete('min_price');
                    url.searchParams.delete('max_price');
                    url.searchParams.delete('storage');

                    window.history.pushState({}, '', url.toString());
                    updateFilterUI();
                    updateAppliedFilters();
                    ajaxLoad(url.toString());
                    return;
                }
            });

            // Lắng nghe sự kiện PopState để xử lý nút Back/Forward của trình duyệt
            window.addEventListener('popstate', function(e) {
                updateFilterUI();
                updateAppliedFilters();
                ajaxLoad(window.location.href);
            });
        });
    </script>
@endpush
