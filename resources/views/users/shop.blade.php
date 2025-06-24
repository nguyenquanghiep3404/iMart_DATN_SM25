@extends('users.layouts.app')

@section('title', isset($currentCategory) ? $currentCategory->name . ' - iMart' : 'Tất cả sản phẩm - iMart')
@section('content')
    <style>
        /* General Styles */
        body {
            background-color: #f8fafc;
        }

        /* Category Sidebar Styles */
        .category-sidebar {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 8px;
        }

        .category-sidebar .sidebar-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .category-sidebar .sidebar-title i {
            font-size: 1.2rem;
        }

        .category-sidebar .category-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .category-sidebar .category-list a {
            display: block;
            padding: 8px 10px;
            font-size: 0.9rem;
            color: #374151;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.2s, color 0.2s;
        }

        .category-sidebar .category-list .parent-category a {
            font-weight: 500;
        }

        .category-sidebar .category-list .parent-category a.active {
            color: #ef4444;
            font-weight: 600;
        }

        .category-sidebar .category-list .parent-category a.active::before {
            content: '▸';
            color: #ef4444;
            margin-right: 6px;
            font-size: 14px;
        }

        .category-sidebar .category-list .child-categories {
            padding-left: 25px;
        }

        .category-sidebar .category-list .child-categories a:hover,
        .category-sidebar .category-list .child-categories a.active {
            color: #ef4444;
        }

        .filter-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .filter-section h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .price-filter .form-control {
            text-align: center;
        }

        .price-filter .btn {
            background-color: #ef4444;
            border-color: #ef4444;
            font-weight: 600;
        }

        .price-filter .btn:hover {
            background-color: #d73737;
            border-color: #d73737;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .rating-filter ul {
            list-style: none;
            padding-left: 0;
        }

        .rating-filter li a {
            display: block;
            padding: 5px 0;
            color: #374151;
            text-decoration: none;
        }

        /* Sort Section Styles */
        .sort-section {
            display: flex;
            align-items: center;
            background-color: #f3f4f6;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .sort-section .sort-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #4b5563;
            margin-right: 15px;
        }

        .sort-section .sort-options .nav-link,
        .sort-section .dropdown .nav-link {
            font-size: 0.9rem;
            color: #374151;
            background-color: transparent;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            margin: 0 4px;
            transition: background-color 0.2s, color 0.2s;
        }

        .sort-section .sort-options .nav-link:hover,
        .sort-section .dropdown .nav-link:hover {
            background-color: #e5e7eb;
        }

        .sort-section .sort-options .nav-link.active,
        .sort-section .dropdown .nav-link.active {
            color: #ffffff;
            background-color: #ef4444;
        }

        /* Product Card Styles */
        .product-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .collapse:not(.show) {
            display: none;
        }
    </style>

    @php
        use Illuminate\Support\Str;
        $parentCategories = $categories->whereNull('parent_id');
    @endphp

    <section class="container mt-4">
        {{-- Tiêu đề và Breadcrumb --}}
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Trang chủ</a></li>
                    @if (isset($currentCategory))
                        <li class="breadcrumb-item active" aria-current="page">{{ $currentCategory->name }}</li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">Tất cả sản phẩm</li>
                    @endif
                </ol>
            </nav>

        </div>

        <div class="row">
            {{-- Sidebar bên trái --}}
            <div class="col-lg-3">
                @include('users.partials.category_product.product_sidebar')
            </div>


            {{-- Danh sách sản phẩm --}}
            <div class="col-lg-9" id="ajax-products-list">
                @include('users.partials.category_product.shop_products')
            </div>
        </div>
    </section>

    <section style="margin: 80px 0;"></section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const priceForm = document.getElementById('price-filter-form');
        const minPriceInput = document.getElementById('min_price');
        const maxPriceInput = document.getElementById('max_price');
        const priceError = document.getElementById('price-error');
        const productsContainer = document.getElementById('ajax-products-list');

        if (!priceForm || !productsContainer) return;

        function ajaxLoad(url, method = 'GET', data = null) {
            productsContainer.classList.add('opacity-50');
            fetch(url, {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: method === 'POST' ? data : null
            })
            .then(response => response.text())
            .then(html => {
                productsContainer.innerHTML = html;
                productsContainer.classList.remove('opacity-50');

                // ✅ Cập nhật URL trên trình duyệt
                window.history.pushState({}, '', url);

                window.scrollTo({
                    top: productsContainer.offsetTop - 80,
                    behavior: 'smooth'
                });
            });
        }

        priceForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const minPrice = minPriceInput.value.trim();
            const maxPrice = maxPriceInput.value.trim();

            priceError.style.display = 'none';
            minPriceInput.classList.remove('is-invalid');
            maxPriceInput.classList.remove('is-invalid');

            if (minPrice === '' && maxPrice === '') {
                priceError.textContent = 'Vui lòng nhập khoảng giá phù hợp';
                priceError.style.display = 'block';
                minPriceInput.classList.add('is-invalid');
                maxPriceInput.classList.add('is-invalid');
                return;
            }

            const params = new URLSearchParams(new FormData(priceForm)).toString();
            ajaxLoad(`${window.location.pathname}?${params}`);
        });

        document.querySelectorAll('.rating-filter a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                ajaxLoad(this.href);
            });
        });

        document.querySelectorAll('.sort-options .nav-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                ajaxLoad(this.href);
            });
        });

        document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                ajaxLoad(this.href);
            });
        });

        document.addEventListener('click', function (e) {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                ajaxLoad(paginationLink.href);
            }
        });

        const clearBtn = document.querySelector('a.btn.btn-outline-secondary.w-100');
        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                ajaxLoad(this.href);
            });
        }
    });
</script>
@endpush


