@extends('users.layouts.app')

@section('title', 'Tất cả sản phẩm - iMart')

@section('content')
    <section class="container px-4 pt-5 mt-2 mt-sm-3 mt-lg-4">
        {{-- TIÊU ĐỀ & FORM LỌC --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between border-bottom pb-3 pb-md-4">
            <h2 class="h3 mb-3 mb-md-0">Tất cả sản phẩm</h2>
        </div>

        {{-- FORM LỌC --}}
        <form method="GET" action="{{ route('users.products.all') }}" class="row g-3 mb-4 mt-1">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="min_price" class="form-control" placeholder="Giá từ"
                    value="{{ request('min_price') }}">
            </div>
            <div class="col-md-2">
                <input type="number" name="max_price" class="form-control" placeholder="Đến"
                    value="{{ request('max_price') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>

        {{-- DANH SÁCH SẢN PHẨM --}}
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4 pt-2">
            @forelse ($products as $product)
                <div class="col">
                    <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
                        <div class="position-relative">
                            @php
                                $variant = $product->variants->first();
                                $now = now();
                                $onSale =
                                    $variant &&
                                    $variant->sale_price &&
                                    $variant->sale_price_starts_at &&
                                    $variant->sale_price_ends_at &&
                                    $now->between($variant->sale_price_starts_at, $variant->sale_price_ends_at);

                                $price = $onSale ? $variant->sale_price : $variant->price;
                                $originalPrice = $onSale ? $variant->price : null;
                            @endphp

                            @if ($onSale && $variant->discount_percent > 0)
                                <div class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 rounded-bottom-end"
                                    style="z-index: 10; font-weight: 600; font-size: 0.85rem;">
                                    Giảm {{ $variant->discount_percent }}%
                                </div>
                            @endif

                            <a class="d-block rounded-top overflow-hidden p-3 p-sm-4"
                                href="{{ route('users.products.show', $product->slug) }}">
                                <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
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
                                    @if ($price)
                                        @if ($onSale)
                                            <span class="text-danger">{{ number_format($price) }}đ</span>
                                            <del class="text-muted fs-sm ms-2">{{ number_format($originalPrice) }}đ</del>
                                        @else
                                            {{ number_format($price) }}đ
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
@endsection
