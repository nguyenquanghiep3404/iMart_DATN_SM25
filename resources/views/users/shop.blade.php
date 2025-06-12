@extends('users.layouts.app')

@section('title', 'Tất cả sản phẩm - iMart')

@section('content')
    <style>
        .filter-section {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-section .form-control,
        .filter-section .form-select,
        .filter-section .btn {
            height: 40px;
            font-size: 14px;
            padding: 0 12px;
        }

        .filter-section .col-md-3,
        .filter-section .col-md-2,
        .filter-section .col-md-1,
        .filter-section .col-md-5 {
            flex: 0 0 auto;
            width: auto;
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
        }


        .sort-section .dropdown-menu a {
            font-size: 14px;
        }

        .category-buttons .btn {
            font-size: 1.2rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
        }

        .category-buttons {
            padding-bottom: 12px;
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        .category-buttons::-webkit-scrollbar {
            height: 8px;
        }

        .category-buttons::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 10px;
        }

        .category-buttons::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Nền toàn trang */
        section.container {
            background-color: #f8f9fb;
            /* Màu xám nhạt hơn trắng (#ffffff) */
            border-radius: 8px;
        }

        /* Card sản phẩm */
        .product-card {
            background-color: #ffffff;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s ease-in-out;
        }

        .product-card:hover {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
        }

        body {
            background-color: #eef1f5;
            /* Toàn trang cũng hơi xám nhẹ */
        }
    </style>

    <section class="container px-5 pt-3 mt-3 mt-sm-3 mt-lg-4">
        {{-- DANH MỤC HIỂN THỊ THEO HÀNG NGANG --}}
        <div class="my-3 pb-2 border-bottom">
            <div class="d-flex flex-nowrap overflow-auto gap-3 category-buttons">
                <a href="{{ route('users.products.all') }}"
                    class="btn {{ request('category_id') ? 'btn-outline-secondary' : 'btn-primary' }}">
                    Tất cả
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('users.products.all', ['category_id' => $category->id]) }}"
                        class="btn {{ request('category_id') == $category->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- DROPDOWN XẾP THEO --}}
        @php
            $sortOptions = [
                'nổi_bật' => 'Nổi bật',
                'mới_ra_mắt' => 'Mới ra mắt',
                'giá_thấp_đến_cao' => 'Giá thấp đến cao',
                'giá_cao_đến_thấp' => 'Giá cao đến thấp',
                'dang_giam_gia' => 'Đang giảm giá', // ✅ thêm dòng này
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


        {{-- DANH SÁCH SẢN PHẨM --}}
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4 pt-2">
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
                                        $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);

                                    $price = $onSale ? $variant->sale_price : $variant->price;
                                    $originalPrice = $onSale ? $variant->price : null;
                                }
                            @endphp


                            @if ($onSale && $variant->discount_percent > 0 && request('sort') !== 'giá_thấp_đến_cao' && request('sort') !== 'giá_cao_đến_thấp')
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
                                <span class="text-body-tertiary fs-xs">({{ $product->approved_reviews_count ?? 0 }})</span>
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
                                            <span class="text-danger">{{ number_format($variant->sale_price) }}đ</span>
                                            <del class="text-muted fs-sm ms-2">{{ number_format($variant->price) }}đ</del>
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

        {{-- PHÂN TRANG --}}
        <div class="mt-4">
            {{ $products->withQueryString()->links() }}
        </div>
    </section>

    <section style="margin: 80px 0;"></section>
@endsection
