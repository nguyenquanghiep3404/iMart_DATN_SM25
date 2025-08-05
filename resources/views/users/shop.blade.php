@extends('users.layouts.app')

@section('title', isset($searchQuery) ? 'Tìm kiếm: ' . $searchQuery . ' - iMart' : (isset($currentCategory) ?
    $currentCategory->name . ' - iMart' : 'Tất cả sản phẩm - iMart'))

    @push('styles')
        <style>
            /* Glassmorphism & Modern Silver Styles */
            body {
                background: linear-gradient(135deg, #f8fafc 60%, #e5e9f2 100%);
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
        </style>
    @endpush
@section('content')
    @php
        use Illuminate\Support\Str;
        $parentCategories = $categories->whereNull('parent_id');
    @endphp

    <section class="container mt-4">
        {{-- Tiêu đề và Breadcrumb (Include partial) --}}
        @include('users.partials.category_product.breadcrumb')

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
            <div class="col-lg-9" id="ajax-products-list">
                <div class="loading-spinner"></div>
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

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
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
                        body: method === 'POST' ? data : null
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        console.log('AJAX response:', response.status, response.statusText, contentType);
                        if (response.ok && contentType && contentType.indexOf('application/json') !== -1) {
                            const json = await response.json();
                            console.log('AJAX JSON:', json);
                            if (json.products && document.getElementById('ajax-products-list')) {
                                document.getElementById('ajax-products-list').innerHTML = json.products;
                            }
                            if (json.sidebar && document.getElementById('ajax-sidebar')) {
                                document.getElementById('ajax-sidebar').innerHTML = json.sidebar;
                                // Xóa tất cả active trước khi gán lại
                                document.querySelectorAll('.category-link').forEach(el => {
                                    el.classList.remove('active', 'fw-bold', 'text-primary',
                                        'text-dark', 'bg-light', 'border');
                                });
                                // Gán active cho link khớp với URL
                                const currentUrl = new URL(url, window.location.origin);
                                const currentPath = currentUrl.pathname;
                                console.log('Current Path:', currentPath);
                                let activeLink = document.querySelector(
                                    `.category-link[href="${currentPath}"]`);
                                if (!activeLink) {
                                    const pathSegments = currentPath.split('/').filter(Boolean);
                                    const baseSegment = pathSegments[1];
                                    activeLink = document.querySelector(
                                        `.category-link[href*="${baseSegment}"]`);
                                }
                                if (activeLink) {
                                    activeLink.classList.add('active', 'fw-bold', 'text-primary');
                                    console.log('Active state updated for:', activeLink.href);
                                } else {
                                    console.log('No matching active link for:', currentPath,
                                        'Available links:', Array.from(document.querySelectorAll(
                                            '.category-link')).map(link => link.href));
                                }
                            }
                            // Cập nhật tiêu đề
                            if (json.title && document.querySelector('h1.page-title')) {
                                document.querySelector('h1.page-title').textContent = json.title;
                            }
                            // Cập nhật breadcrumb (sử dụng breadcrumb_html)
                            if (json.breadcrumb_html && document.querySelector('.breadcrumb')) {
                                document.querySelector('.breadcrumb').innerHTML = json.breadcrumb_html;
                            }
                            productsContainer.classList.remove('ajax-loading');
                            window.scrollTo({
                                top: productsContainer.offsetTop - 80,
                                behavior: 'smooth'
                            });
                        } else if (response.ok) {
                            const html = await response.text();
                            productsContainer.innerHTML = html;
                            productsContainer.classList.remove('ajax-loading');
                            window.scrollTo({
                                top: productsContainer.offsetTop - 80,
                                behavior: 'smooth'
                            });
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
                        alert('AJAX error: ' + error);
                        productsContainer.classList.remove('ajax-loading');
                        productsContainer.innerHTML = '<p>Đã có lỗi xảy ra. Vui lòng thử lại.</p>';
                    });
            }

            // Khởi tạo trạng thái active khi load trang
            function initializeActiveState() {
                const currentUrl = new URL(window.location.href, window.location.origin);
                const currentPath = currentUrl.pathname;
                console.log('Initial Path:', currentPath);
                if (currentPath === '/' || currentPath === '/products' || !currentPath.includes('byCategory')) {
                    document.querySelectorAll('.category-link').forEach(el => {
                        el.classList.remove('active', 'fw-bold', 'text-primary', 'text-dark', 'bg-light',
                            'border');
                    });
                    console.log('No active category set for initial load');
                } else {
                    let activeLink = document.querySelector(`.category-link[href="${currentPath}"]`);
                    if (!activeLink) {
                        const pathSegments = currentPath.split('/').filter(Boolean);
                        const baseSegment = pathSegments[1];
                        activeLink = document.querySelector(`.category-link[href*="${baseSegment}"]`);
                    }
                    if (activeLink) {
                        activeLink.classList.add('active', 'fw-bold', 'text-primary');
                        console.log('Initial active state set for:', activeLink.href);
                    } else {
                        console.log('No matching active link for:', currentPath, 'Available links:', Array.from(
                            document.querySelectorAll('.category-link')).map(link => link.href));
                    }
                }
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

                // Đợi một frame để trình duyệt apply height trước
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

            function slideToggle(element, duration = 300) {
                const isHidden = window.getComputedStyle(element).display === 'none';
                if (isHidden) {
                    slideDown(element, duration);
                } else {
                    slideUp(element, duration);
                }
            }

            function initializeToggle() {
                document.querySelectorAll('.toggle-category').forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        const parentLi = toggle.closest('li');
                        const childUl = parentLi.querySelector('.child-categories');
                        if (childUl) {
                            slideToggle(childUl, 300);
                        }
                    });
                });
            }

            // Khởi tạo khi load trang
            initializeToggle();
            initializeActiveState();

            document.addEventListener('submit', function(event) {
                const form = event.target.closest('form[data-ajax-filter]');
                if (form) {
                    event.preventDefault();
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData).toString();
                    ajaxLoad(`${form.action}?${params}`);
                }
            });

            document.addEventListener('click', function(e) {
                const categoryLink = e.target.closest('.category-link');
                if (categoryLink) {
                    e.preventDefault();
                    ajaxLoad(categoryLink.href);
                    window.history.pushState({}, '', categoryLink.href);
                    return;
                }
                const ratingLink = e.target.closest('.rating-filter a');
                if (ratingLink) {
                    e.preventDefault();
                    ajaxLoad(ratingLink.href);
                    return;
                }
                const sortLink = e.target.closest('.sort-options .nav-link');
                if (sortLink) {
                    e.preventDefault();
                    const url = sortLink.href;
                    ajaxLoad(url);
                    window.history.pushState({}, '', url); // <--- Thêm dòng này để cập nhật ?sort= vào URL
                    return;
                }

                const dropdownLink = e.target.closest('.dropdown-menu .dropdown-item');
                if (dropdownLink) {
                    e.preventDefault();
                    ajaxLoad(dropdownLink.href);
                    return;
                }
                const paginationLink = e.target.closest('.pagination a');
                if (paginationLink) {
                    e.preventDefault();
                    ajaxLoad(paginationLink.href);
                    return;
                }
                const clearBtn = e.target.closest('a.btn.btn-outline-secondary.w-100');
                if (clearBtn) {
                    e.preventDefault();
                    const url = clearBtn.href;
                    ajaxLoad(url);
                    window.history.pushState({}, '', url); // ✅ Cập nhật URL đúng với trang reset
                    return;
                }

            });
            // ✅ Xử lý khi người dùng bấm nút Back hoặc Forward
            window.addEventListener('popstate', function(event) {
                ajaxLoad(window.location.href);
            });
        });

        // Reload lại nếu trình duyệt dùng bfcache (back/forward cache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                location.reload();
            }
        });
    </script>
@endpush
