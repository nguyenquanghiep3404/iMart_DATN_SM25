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
                    {{-- <p class="fs-sm">Buy <span class="text-dark-emphasis fw-semibold">$183</span> more to get <span
                            class="text-dark-emphasis fw-semibold">Free Shipping</span></p> --}}
                    <div class="progress w-100 overflow-visible mb-4" role="progressbar" aria-label="Free shipping progress"
                        aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
                        <div class="progress-bar bg-warning rounded-pill position-relative overflow-visible"
                            style="width: 75%; height: 4px">
                            <div class="position-absolute top-50 end-0 d-flex align-items-center justify-content-center translate-middle-y bg-body border border-warning rounded-circle me-n1"
                                style="width: 1.5rem; height: 1.5rem">
                                <i class="ci-star-filled text-warning"></i>
                            </div>
                        </div>
                    </div>

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
                                <tr data-item-id="{{ $item['id'] }}" data-stock="{{ $item['stock_quantity'] }}">
                                    <td class="py-3 ps-0">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($item['image'] ?: 'path/to/default.jpg') }}"
                                                alt="Ảnh sản phẩm" width="90" height="90">

                                            <div class="w-100 min-w-0 ps-2 ps-xl-3">
                                                <h5 class="d-flex animate-underline mb-2">
                                                    <a
                                                        href="{{ route('users.products.show', $item['slug']) }}?variant_id={{ $item['id'] }}">
                                                        {{ $item['name'] }}
                                                    </a>
                                                </h5>
                                                <!-- Nếu có thuộc tính, hiển thị ở đây -->
                                                <ul class="list-unstyled gap-1 fs-xs mb-0">
                                                    <!-- ví dụ: -->
                                                    <li><span class="text-body-secondary">Price:</span>
                                                        <span
                                                            class="text-dark-emphasis fw-medium">{{ number_format($item['price'], 0, ',', '.') }}đ</span>
                                                    </li>
                                                </ul>
                                                <div class="count-input rounded-2 d-md-none mt-3">
                                                    <!-- Số lượng -->
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
                                        {{ number_format($item['price'], 0, ',', '.') }}đ</td>
                                    <td class="py-3 d-none d-md-table-cell">
                                        <div class="count-input d-flex align-items-center justify-content-between">
                                            <button type="button" class="btn btn-icon btn-decrement"
                                                aria-label="Decrement quantity">
                                                <i class="ci-minus"></i>
                                            </button>
                                            <input type="number" class="form-control quantity-input text-center"
                                                value="{{ $item['quantity'] }}" readonly>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clearBtn = document.getElementById('clear-cart-btn');
        clearBtn.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
                fetch("{{ route('cart.clear') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success(data.message);
                            // Cập nhật UI giỏ hàng thành rỗng
                            // Ví dụ: xóa tất cả dòng sản phẩm trong bảng giỏ hàng
                            const tbody = document.querySelector('table tbody');
                            if (tbody) {
                                tbody.innerHTML = ''; // xoá hết các sản phẩm
                            }
                            // Cập nhật tổng tiền và số lượng thành 0 (nếu có)
                            const totalPriceElem = document.querySelector(
                            '.total-price'); // sửa selector cho đúng với trang bạn
                            const totalQtyElem = document.querySelector('.total-quantity');
                            if (totalPriceElem) totalPriceElem.textContent = '0₫';
                            if (totalQtyElem) totalQtyElem.textContent = '0';

                            // Nếu bạn có phần hiển thị "giỏ hàng rỗng" có thể hiển thị ở đây
                        } else {
                            toastr.error('Xảy ra lỗi khi xóa giỏ hàng');
                        }
                    })
                    .catch(error => {
                        toastr.error('Lỗi kết nối server');
                        console.error(error);
                    });
            }
        });
    });

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: "3000",
        showDuration: "300",
        hideDuration: "1000",
        showMethod: "slideDown",
        hideMethod: "slideUp"
    };
</script>
