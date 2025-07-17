@extends('users.layouts.app')
@section('content')
    @include('users.messenger')
    @include('users.cart.layout.partials.css')
    <!-- Breadcrumb -->
    <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home-electronics.html">Home</a></li>
            <li class="breadcrumb-item"><a href="shop-catalog-electronics.html">Shop</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cart</li>
        </ol>
    </nav>
    <!-- Items in the cart + Order summary -->
    <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
        <h1 class="h3 mb-4">Giỏ Hàng</h1>
        <div class="row">
            <!-- Items list -->
            <div class="col-lg-8">
                <div class="pe-lg-2 pe-xl-3 me-xl-3">
                    <!-- Table of items -->
                    <table class="table position-relative z-2 mb-4">
                        <thead>
                            <tr>
                                <th scope="col" class="fs-sm fw-normal py-3 ps-0"><span class="text-body">Sản Phẩm</span>
                                </th>
                                <th scope="col" class="text-body fs-sm fw-normal py-3 d-none d-xl-table-cell"><span
                                        class="text-body">Gía</span></th>
                                <th scope="col" class="text-body fs-sm fw-normal py-3 d-none d-md-table-cell"><span
                                        class="text-body">Số Lượng</span></th>
                                <th scope="col" class="text-body fs-sm fw-normal py-3 d-none d-md-table-cell"><span
                                        class="text-body">Tổng tiền</span></th>
                                <th scope="col" class="py-0 px-0">
                                    <div class="nav justify-content-end">
                                        <button type="button" id="clear-cart-btn"
                                            class="nav-link d-inline-block text-decoration-underline text-nowrap py-3 px-0">
                                            Xóa
                                        </button>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">

                            <!-- Item -->
                            @foreach ($items as $item)
                                {{-- <pre>
                                    @dd($item)
                            </pre> --}}
                                @php
                                    $isNewProduct = $item['cartable_type'] === \App\Models\ProductVariant::class;
                                    $productSlug = $item['slug'] ?? '';
                                    $productLink = $isNewProduct
                                        ? route('users.products.show', $productSlug) . '?variant_id=' . $item['id']
                                        : route('users.tradeins.show', $productSlug); // giả sử route này có cho hàng cũ
                                @endphp

                                <tr data-item-id="{{ $item['id'] }}" data-stock="{{ $item['stock_quantity'] }}">
                                    <td class="py-3 ps-0">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($item['image'] ?: 'path/to/default.jpg') }}"
                                                alt="Ảnh sản phẩm" width="90" height="90">

                                            <div class="w-100 min-w-0 ps-2 ps-xl-3">
                                                @php
                                                    $isNewProduct =
                                                        $item['cartable_type'] === \App\Models\ProductVariant::class;
                                                @endphp

                                                <h5 class="d-flex align-items-center animate-underline mb-2">
                                                    <a
                                                        href="{{ $isNewProduct
                                                            ? route('users.products.show', $item['slug']) . '?variant_id=' . $item['id']
                                                            : route('users.tradeins.show', $item['slug']) }}">
                                                        {{ $item['name'] }}
                                                    </a>

                                                    {{-- Hiển thị loại sản phẩm --}}
                                                    @if ($isNewProduct)
                                                        <span class="badge bg-success ms-2">Sản phẩm mới</span>
                                                    @else
                                                        <span class="badge bg-warning ms-2">Hàng cũ</span>
                                                    @endif
                                                </h5>


                                                {{-- Thuộc tính biến thể (chỉ có với hàng mới) --}}
                                                @if ($isNewProduct && !empty($item['variant_attributes']))
                                                    <ul class="list-unstyled gap-1 fs-xs mb-0">
                                                        @foreach ($item['variant_attributes'] as $attrName => $attrValue)
                                                            <li>
                                                                <span
                                                                    class="text-body-secondary">{{ $attrName }}:</span>
                                                                <span
                                                                    class="text-dark-emphasis fw-medium">{{ $attrValue }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif

                                                <div class="count-input rounded-2 d-md-none mt-3">
                                                    <button type="button" class="btn btn-sm btn-icon" data-decrement=""
                                                        aria-label="Decrement quantity">
                                                        <i class="ci-minus"></i>
                                                    </button>
                                                    <input type="number" class="form-control form-control-sm"
                                                        value="{{ $item['quantity'] }}" readonly>
                                                    <button type="button" class="btn btn-sm btn-icon" data-increment=""
                                                        aria-label="Increment quantity">
                                                        <i class="ci-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="h6 py-3 d-none d-xl-table-cell">
                                        {{ number_format($item['price'], 0, ',', '.') }}đ
                                    </td>

                                    <td class="py-3 d-none d-md-table-cell">
                                        <div class="count-input d-flex align-items-center justify-content-between">
                                            <button type="button" class="btn btn-icon btn-decrement"
                                                aria-label="Decrement quantity">
                                                <i class="ci-minus"></i>
                                            </button>
                                            <input type="number" class="form-control quantity-input text-center"
                                                value="{{ $item['quantity'] }}" min="1"
                                                max="{{ $item['stock_quantity'] }}">
                                            <button type="button" class="btn btn-icon btn-increment"
                                                aria-label="Increment quantity">
                                                <i class="ci-plus"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="h6 py-3 d-none d-xl-table-cell item-subtotal"
                                        data-price="{{ $item['price'] }}">
                                        {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                                    </td>

                                    <td class="text-end py-3 px-0">
                                        <button type="button" class="btn-close fs-sm btn-remove-item"
                                            data-id="{{ $item['id'] }}" data-bs-toggle="tooltip"
                                            data-bs-custom-class="tooltip-sm" data-bs-title="Remove"
                                            aria-label="Remove from cart">
                                        </button>
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>

                    <div class="nav position-relative z-2 mb-4 mb-lg-0">
                        <a href="javascript:void(0);" onclick="window.history.back();">
                            <i class="ci-chevron-left fs-lg me-1"></i>
                            <span class="animate-target">Tiếp tục mua sắm</span>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Order summary (sticky sidebar) -->
            @include('users.cart.layout.partials.summary_oder')
        </div>
    </section>
    <!-- Trending products (Carousel) -->
    @include('users.cart.layout.partials.product_trending')
    <!-- Subscription form + Vlog -->
    @include('users.cart.layout.partials.form')
@endsection
@include('users.cart.layout.partials.script')
