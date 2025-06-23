@extends('users.layouts.app')

@section('title', isset($currentCategory) ? $currentCategory->name . ' - iMart' : 'Tất cả sản phẩm - iMart')

@section('content')
    <style>
        .category-sidebar {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            height: 100%;
            overflow-y: auto;
        }

        .category-sidebar .parent-category {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2c3e50;
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s, color 0.3s;
        }

        .category-sidebar .parent-category:hover {
            background-color: #f1f5f9;
            color: #2563eb;
        }

        .category-sidebar ul {
            list-style: none;
            padding-left: 15px;
            margin: 0;
            transition: max-height 0.3s ease;
        }

        .category-sidebar li a {
            display: block;
            padding: 8px 15px;
            font-size: 0.95rem;
            color: #4b5563;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.2s, color 0.2s;
        }

        .category-sidebar li a:hover,
        .category-sidebar li a.active {
            background-color: #2563eb;
            color: #ffffff;
        }

        .category-sidebar .collapse-icon::after {
            content: '\f078';
            /* FontAwesome chevron-down */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.8rem;
            transition: transform 0.3s;
        }

        .category-sidebar .collapse-icon[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        .sort-section {
            margin-bottom: 20px;
            text-align: right;
        }

        .sort-section .btn {
            font-size: 1rem;
            padding: 10px 16px;
            height: 46px;
            border-radius: 8px;
            min-width: 180px;
            border-color: #2563eb;
            color: #2563eb;
            transition: background-color 0.2s, color 0.2s;
        }

        .sort-section .btn:hover {
            background-color: #2563eb;
            color: #ffffff;
        }

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

        body {
            background-color: #f8fafc;
        }
        .collapse {
       visibility: hidden !important;
     }
     .collapse.show {
       visibility: visible !important;
     }

        /* Smooth transition for collapse */
        .collapse {
            visibility: visible !important;
        }
        .collapse:not(.show) {
            display: none !important;
        }
        .collapse.show {
            display: block !important;
        }
    </style>

    @php
        use Illuminate\Support\Str;
        $parentCategories = $categories->whereNull('parent_id');
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
                <div class="category-sidebar">
                    @foreach ($parentCategories as $parent)
                        @php
                            $childCategories = $categories->where('parent_id', $parent->id);
                            $hasChildren = $childCategories->isNotEmpty();
                            $isExpanded =
                                request()->route('id') == $parent->id ||
                                $childCategories->pluck('id')->contains(request()->route('id'));
                        @endphp

                        <button class="parent-category {{ $hasChildren ? 'collapse-icon' : '' }}"
                            @if ($hasChildren)
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ $parent->id }}"
                                aria-expanded="{{ $isExpanded ? 'true' : 'false' }}"
                                aria-controls="collapse-{{ $parent->id }}"
                            @endif
                            style="width: 100%; text-align: left; background: none; border: none; padding: 0;"
                        >
                            {{ $parent->name }}
                        </button>

                        <div class="collapse" id="collapse-{{ $parent->id }}">
                            <ul>
                                <li>
                                    <a href="{{ route('products.byCategory', ['id' => $parent->id, 'slug' => Str::slug($parent->name)]) }}"
                                        class="{{ request()->route('id') == $parent->id ? 'active' : '' }}">
                                        Tất cả {{ $parent->name }}
                                    </a>
                                </li>
                                @foreach ($childCategories as $child)
                                    <li>
                                        <a href="{{ route('products.byCategory', ['id' => $child->id, 'slug' => Str::slug($child->name)]) }}"
                                            class="{{ request()->route('id') == $child->id ? 'active' : '' }}">
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                </div>
            </div>

            {{-- Danh sách sản phẩm --}}
            <div class="col-lg-9">

                @php
                    $sortOptions = [
                        'nổi_bật' => 'Nổi bật',
                        'mới_ra_mắt' => 'Mới ra mắt',
                        'giá_thấp_đến_cao' => 'Giá thấp đến cao',
                        'giá_cao_đến_thấp' => 'Giá cao đến thấp',
                        'dang_giam_gia' => 'Đang giảm giá',
                    ];
                    $currentSort = request('sort') ?? 'nổi_bật';
                @endphp

                <div class="sort-section">
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-outline-dark dropdown-toggle shadow-sm" type="button" id="sortDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ci-filter me-1"></i> Xếp theo: {{ $sortOptions[$currentSort] ?? 'Nổi bật' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                            @foreach ($sortOptions as $key => $label)
                                <li>
                                    <a class="dropdown-item {{ $currentSort === $key ? 'active' : '' }}"
                                        href="{{ request()->fullUrlWithQuery(['sort' => $key]) }}">
                                        {{ $label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 g-4 pt-2">
                    @forelse ($products as $product)
                        <div class="col">
                            <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
                                <div class="position-relative">
                                    @php
                                        $variant = $product->variants->first();
                                        $now = now();
                                        $onSale = false;
                                        $price = null;
                                        $originalPrice = null;

                                        if ($variant && $variant->price !== null) {
                                            $onSale =
                                                $variant->sale_price &&
                                                $variant->sale_price_starts_at &&
                                                $variant->sale_price_ends_at &&
                                                $now->between(
                                                    $variant->sale_price_starts_at,
                                                    $variant->sale_price_ends_at,
                                                );
                                            $price = $onSale ? $variant->sale_price : $variant->price;
                                            $originalPrice = $onSale ? $variant->price : null;
                                        }
                                    @endphp

                                    @if (
                                        $onSale &&
                                            $variant->discount_percent > 0 &&
                                            request('sort') !== 'giá_thấp_đến_cao' &&
                                            request('sort') !== 'giá_cao_đến_thấp')
                                        <div class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 rounded-bottom-end"
                                            style="z-index: 10; font-weight: 600; font-size: 0.85rem; min-width: 105px; text-align: center;">
                                            Giảm {{ $variant->discount_percent }}%
                                        </div>
                                    @endif

                                    <a class="d-block rounded-top overflow-hidden p-3 p-sm-4"
                                        href="{{ route('users.products.show', $product->slug) }}">
                                        <div class="ratio" style="--cz-aspect-ratio: calc(200 / 220 * 100%)">
                                            <img src="{{ $product->coverImage ? asset('storage/' . $product->coverImage->path) : asset('assets/users/img/shop/electronics/thumbs/placeholder.png') }}"
                                                alt="{{ $product->name }}" loading="lazy">
                                        </div>
                                    </a>
                                </div>

                                <div class="w-100 min-w-0 px-1 pb-2 px-sm-3 pb-sm-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="d-flex gap-1 fs-xs">
                                            @php
                                                $rating = $product->average_rating ?? 0;
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $rating >= $i
                                                        ? '<i class="ci-star-filled text-warning"></i>'
                                                        : ($rating > $i - 1
                                                            ? '<i class="ci-star-half text-warning"></i>'
                                                            : '<i class="ci-star text-body-tertiary opacity-75"></i>');
                                                }
                                            @endphp
                                        </div>
                                        <span
                                            class="text-body-tertiary fs-xs">({{ $product->approved_reviews_count ?? 0 }})</span>
                                    </div>

                                    <h3 class="pb-1 mb-2">
                                        <a class="d-block fs-sm fw-medium text-truncate"
                                            href="{{ route('users.products.show', $product->slug) }}">
                                            <span class="animate-target">{{ $product->name }}</span>
                                        </a>
                                    </h3>

                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="h5 lh-1 mb-0">
                                            @if ($variant && $variant->price)
                                                @if (
                                                    $onSale &&
                                                        $variant->discount_percent > 0 &&
                                                        request('sort') !== 'giá_thấp_đến_cao' &&
                                                        request('sort') !== 'giá_cao_đến_thấp')
                                                    <span
                                                        class="text-danger">{{ number_format($variant->sale_price) }}đ</span>
                                                    <del
                                                        class="text-muted fs-sm ms-2">{{ number_format($variant->price) }}đ</del>
                                                @else
                                                    {{ number_format($variant->price) }}đ
                                                @endif
                                            @else
                                                <span class="text-muted">Giá không khả dụng</span>
                                            @endif
                                        </div>

                                        <button type="button"
                                            class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2"
                                            aria-label="Add to Cart">
                                            <i class="ci-shopping-cart fs-base animate-target"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">Không tìm thấy sản phẩm nào.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>

    <section style="margin: 80px 0;"></section>

    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentId = {{ request()->route('id') ?? 'null' }};
            if (currentId) {
                const activeLink = document.querySelector(`a[href*="/category/${currentId}/"]`);
                if (activeLink) {
                    const parentCollapse = activeLink.closest('.collapse');
                    if (parentCollapse) {
                        parentCollapse.classList.add('show');
                        const toggle = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            }
        });
    </script> --}}

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentId = {{ request()->route('id') ?? 'null' }};
        if (currentId) {
            const activeLink = document.querySelector(`a[href*="/category/${currentId}/"]`);
            if (activeLink) {
                const collapseEl = activeLink.closest('.collapse');
                if (collapseEl && !collapseEl.classList.contains('show')) {
                    collapseEl.classList.add('show');
                    collapseEl.style.height = 'auto';
                }

                const toggle = document.querySelector(`[data-bs-target="#${collapseEl?.id}"]`);
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });
</script>

<button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#testCollapse" aria-expanded="false" aria-controls="testCollapse">
  Test Collapse
</button>
<div class="collapse" id="testCollapse">
  <div class="card card-body">
    Nội dung test
  </div>
</div>
