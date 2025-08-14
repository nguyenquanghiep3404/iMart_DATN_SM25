@extends('users.layouts.profile')
@include('users.messenger')
@section('content')
    <div class="col-lg-9">
        <div class="ps-lg-3 ps-xl-0">
            <!-- Page title + Add list button-->
            <div class="d-flex align-items-center justify-content-between pb-3 mb-1 mb-sm-2 mb-md-3">
                <h1 class="h2 me-3 mb-0">Danh sách sản phẩm yêu thích</h1>
            </div>

            <!-- Wishlist selector -->
            <div class="border-bottom pb-4 mb-3">
                <div class="row align-items-center justify-content-between">
                    <div class="col-sm-7 col-md-8 col-xxl-9 d-flex align-items-center mb-3 mb-sm-0">
                        <h5 class="me-2 mb-0">Interesting offers</h5>
                        <div class="dropdown ms-auto ms-sm-0">
                            <button type="button" class="btn btn-icon btn-ghost btn-secondary border-0"
                                id="wishlist-selector" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                aria-haspopup="true" aria-expanded="false" aria-label="Select wishlist">
                                <i class="ci-more-vertical fs-xl"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="d-flex flex-column gap-1 mb-2">
                                    @foreach (['Interesting offers', 'Top picks collection', 'Family stuff', 'My must-haves', 'For my husband'] as $index => $label)
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" id="wishlist-{{ $index + 1 }}"
                                                name="wishlist" {{ $index === 0 ? 'checked' : '' }}>
                                            <label for="wishlist-{{ $index + 1 }}"
                                                class="form-check-label text-body">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-dark w-100"
                                    onclick="document.getElementById('wishlist-selector').click()">Select wishlist</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-5 col-md-4 col-xxl-3">
                        <form method="GET" action="{{ route('wishlist.index') }}" class="mb-3">
                            <select name="sort" class="form-select" aria-label="Wishlist sorting"
                                onchange="this.form.submit()">
                                <option value="date" {{ request('sort') == 'date' ? 'selected' : '' }}>Theo ngày thêm
                                </option>
                                <option value="price-ascend" {{ request('sort') == 'price-ascend' ? 'selected' : '' }}>Theo
                                    giá
                                    tăng dần</option>
                                <option value="price-descend" {{ request('sort') == 'price-descend' ? 'selected' : '' }}>
                                    Theo
                                    giá giảm dần</option>
                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Theo đánh giá
                                </option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Master checkbox + Action buttons -->
            <div class="nav align-items-center mb-4">
                <div class="form-checkl nav-link animate-underline fs-lg ps-0 pe-2 py-2 mt-n1 me-4"
                    data-master-checkbox='{"container": "#wishlistSelection", "label": "Chọn tất cả", "labelChecked": "Bỏ chọn tất cả", "showOnCheck": "#action-buttons"}'>
                    <input type="checkbox" class="form-check-input" id="wishlist-master" checked>
                    <label for="wishlist-master" class="form-check-label animate-target mt-1 ms-2">Bỏ chọn tất cả</label>
                </div>
                <div class="d-flex flex-wrap" id="action-buttons">
                    <a id="add-selected-to-cart" class="nav-link animate-underline px-0 pe-sm-2 py-2 me-4" href="#!">
                        <i class="ci-shopping-cart fs-base me-2"></i>
                        <span class="animate-target d-none d-md-inline">Thêm vào giỏ hàng</span>
                    </a>
                    <button type="submit" form="wishlist-form" class="nav-link animate-underline px-0 py-2 btn btn-link">
                        <i class="ci-trash fs-base me-1"></i>
                        <span class="animate-target d-none d-md-inline">Xóa đã chọn</span>
                    </button>
                </div>
            </div>

            <!-- Wishlist items -->
            <form id="wishlist-form" method="POST" action="{{ route('wishlist.removeSelected') }}">
                @csrf
                <div class="row row-cols-2 row-cols-md-3 g-4" id="wishlistSelection">
                    @forelse ($products as $product)
                        <div class="col">
                            <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
                                <div class="position-relative">
                                    <div class="position-absolute top-0 end-0 z-1 pt-1 pe-1 mt-2 me-2">
                                        <div class="form-check fs-lg">
                                            <input type="checkbox" class="form-check-input select-card-check"
                                                name="wishlist_ids[]" value="{{ $product->product_variant_id }}" checked>
                                        </div>
                                    </div>

                                    <!-- Link gồm cả ảnh và tên -->
                                    <a class="d-block rounded-top overflow-hidden text-center p-3 p-sm-4"
                                        href="{{ route('users.products.show', $product->productVariant->product->slug) }}?variant_id={{ $product->productVariant->id }}">
                                        @if ($product->productVariant->discount_percent)
                                            <span
                                                class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2 mt-lg-3 ms-lg-3">
                                                -{{ $product->productVariant->discount_percent }}%
                                            </span>
                                        @endif

                                        <div class="ratio mb-2" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                                            @php
                                                $variant = $product->productVariant;
                                                $imageUrl = $variant && $variant->primaryImage && file_exists(storage_path('app/public/' . $variant->primaryImage->path)) ? Storage::url($variant->primaryImage->path) : ($variant && $variant->product && $variant->product->coverImage && file_exists(storage_path('app/public/' . $variant->product->coverImage->path)) ? Storage::url($variant->product->coverImage->path) : asset('images/placeholder.jpg'));
                                            @endphp
                                            <img src="{{ $imageUrl }}"
                                                alt="{{ $product->productVariant->name ?? $product->productVariant->product->name }}">

                                        </div>

                                        <h3 class="fs-sm fw-medium text-truncate mb-0 text-dark">
                                            {{ $product->productVariant->name ?? $product->productVariant->product->name }}
                                        </h3>
                                    </a>
                                </div>

                                <div class="w-100 min-w-0 px-1 pb-2 px-sm-3 pb-sm-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="d-flex gap-1 fs-xs">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= floor($product->productVariant->rating))
                                                    <i class="ci-star-filled text-warning"></i>
                                                @else
                                                    <i class="ci-star text-body-tertiary opacity-75"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="text-body-tertiary fs-xs">
                                            ({{ $product->productVariant->reviews_count ?? 0 }})
                                        </span>
                                    </div>

                                    <div class="d-flex flex-column align-items-start">
                                        <div class="d-flex justify-content-between w-100">
                                            @php
                                                $salePrice = $product->productVariant->sale_price;
                                                $originalPrice = $product->productVariant->price;
                                            @endphp

                                            {{-- Hiển thị giá (sale price nếu có) --}}
                                            <div class="h5 lh-1 mb-0" style="font-size: 0.875rem;">
                                                @if ($salePrice && $salePrice < $originalPrice)
                                                    <span
                                                        class="text-danger">{{ number_format($salePrice, 0, ',', '.') }}₫</span>
                                                    <del
                                                        class="text-muted">{{ number_format($originalPrice, 0, ',', '.') }}₫</del>
                                                @else
                                                    <span>{{ number_format($originalPrice, 0, ',', '.') }}₫</span>
                                                @endif
                                            </div>

                                            {{-- Nút thêm vào giỏ --}}
                                            <button type="button" class="btn btn-secondary ms-2 add-to-cart-btn"
                                                data-variant-id="{{ $product->product_variant_id }}"
                                                aria-label="Add to Cart">
                                                <i class="ci-shopping-cart fs-base"></i>
                                            </button>
                                        </div>

                                        {{-- Hiển thị biến thể (nếu có) --}}
                                        @if ($product->productVariant->attributeValues->isNotEmpty())
                                            <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                                @foreach ($product->productVariant->attributeValues as $attr)
                                                    <div>{{ $attr->attribute->name }}: {{ $attr->value }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    @empty
                        <p>Không có sản phẩm nào trong danh sách yêu thích</p>
                    @endforelse
                </div>
            </form>
            @if (session('success'))
                <script>
                    toastr.success("{{ session('success') }}");
                </script>
            @endif

            @if (session('error'))
                <script>
                    toastr.error("{{ session('error') }}");
                </script>
            @endif
        </div>
        <!-- Bắt buộc: Thêm trước khi dùng toastr -->
        {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}
        {{-- <script>
            // ✅ Cấu hình toastr với hiệu ứng Animate.css
            toastr.options = {
                closeButton: true,
                progressBar: true,
                escapeHtml: false,
                positionClass: "toast-bottom-right", // 👈 nằm dưới cùng bên phải
                timeOut: 3000,
                extendedTimeOut: 1000,

                // 👇 Dùng hiệu ứng Animate.css (KHÔNG dùng showMethod)
                showClass: 'animate__animated animate__fadeInRight',
                hideClass: 'animate__animated animate__fadeOutRight'
            };

            document.addEventListener('DOMContentLoaded', function() {
                const csrfToken = '{{ csrf_token() }}';

                // ✅ Chức năng: Chọn / Bỏ chọn tất cả checkbox
                const masterCheckbox = document.getElementById('wishlist-master');
                if (masterCheckbox) {
                    masterCheckbox.addEventListener('change', function() {
                        const checked = this.checked;
                        document.querySelectorAll('.select-card-check').forEach(cb => cb.checked = checked);
                        const label = this.nextElementSibling;
                        if (label) {
                            label.textContent = checked ? 'Bỏ chọn tất cả' : 'Chọn tất cả';
                        }
                    });
                }

                // ✅ Thêm 1 sản phẩm vào giỏ
                document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const variantId = this.dataset.variantId;

                        fetch("{{ route('cart.add') }}", {
                                method: "POST",
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    product_variant_id: variantId,
                                    quantity: 1
                                })
                            })
                            .then(async res => {
                                const data = await res.json();
                                
                                if (res.ok && data.success) {
                                    const cartUrl = "{{ route('cart.index') }}";
                                    const message =
                                        `${data.success} <br><a href="${cartUrl}" class="btn btn-sm btn-primary mt-2">Xem giỏ hàng</a>`;
                                    toastr.success(message);
                                    updateCartBadge(data.cartItemCount);
                                } else {
                                    toastr.error(data.message || "Đã vượt quá tồn kho");
                                }

                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            })
                            .catch(err => {
                                toastr.error("Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.");
                                console.error(err);
                            });
                    });
                });

                // ✅ Thêm nhiều sản phẩm đã chọn
                const addMultipleBtn = document.getElementById('add-selected-to-cart');
                if (addMultipleBtn) {
                    addMultipleBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        const selectedCheckboxes = document.querySelectorAll('.select-card-check:checked');
                        if (selectedCheckboxes.length === 0) {
                            toastr.warning('Vui lòng chọn ít nhất một sản phẩm.');
                            return;
                        }

                        const products = Array.from(selectedCheckboxes).map(cb => ({
                            product_variant_id: cb.value,
                            quantity: 1
                        }));

                        fetch('/cart/add-multiple', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    products
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (Array.isArray(data.results)) {
                                    data.results.forEach(item => {
                                        if (item.success) {
                                            toastr.success(item.message);
                                        } else {
                                            toastr.error(item.message);
                                        }
                                    });
                                }
                                updateCartBadge(data.cartItemCount);
                            })
                            .catch(() => {
                                toastr.error('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
                            });
                    });
                }

                // ✅ Hàm cập nhật số lượng badge
                function updateCartBadge(count) {
                    const cartBadge = document.getElementById('cart-badge');
                    if (!cartBadge) return;

                    if (count && count > 0) {
                        cartBadge.textContent = count;
                        cartBadge.style.display = 'flex';
                    } else {
                        cartBadge.style.display = 'none';
                    }
                }
            });
        </script> --}}
        <!-- Các thư viện cần thiết -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

        <script>
            // ✅ Cấu hình toastr
            toastr.options = {
                closeButton: true,
                progressBar: true,
                escapeHtml: false, // Cho phép HTML (để hiển thị nút)
                positionClass: "toast-bottom-right",
                timeOut: 3000,
                extendedTimeOut: 1000,
                showClass: 'animate__animated animate__fadeInRight',
                hideClass: 'animate__animated animate__fadeOutRight'
            };

            document.addEventListener('DOMContentLoaded', () => {
                const csrfToken = '{{ csrf_token() }}';

                // ✅ Chọn tất cả
                const masterCheckbox = document.getElementById('wishlist-master');
                if (masterCheckbox) {
                    masterCheckbox.addEventListener('change', function() {
                        const checked = this.checked;
                        document.querySelectorAll('.select-card-check').forEach(cb => cb.checked = checked);
                        const label = this.nextElementSibling;
                        if (label) {
                            label.textContent = checked ? 'Bỏ chọn tất cả' : 'Chọn tất cả';
                        }
                    });
                }

                // ✅ Thêm 1 sản phẩm
                document.querySelectorAll('.add-to-cart-btn').forEach(button => {
                    button.addEventListener('click', () => {
                        const variantId = button.dataset.variantId;

                        fetch("{{ route('cart.add') }}", {
                                method: "POST",
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    product_variant_id: variantId,
                                    quantity: 1
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    const cartUrl = "{{ route('cart.index') }}";
                                    const message = `
                            <div>
                                ${data.success}
                                <br>
                                <a href="${cartUrl}" class="btn btn-sm btn-primary mt-2">Xem giỏ hàng</a>
                            </div>
                        `;
                                    toastr.success(message);
                                    updateCartBadge(data.cartItemCount);
                                } else {
                                    toastr.error(data.error || 'Đã xảy ra lỗi khi thêm sản phẩm.');
                                }

                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            })
                            .catch(err => {
                                toastr.error('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
                                console.error(err);
                            });
                    });
                });

                // ✅ Thêm nhiều sản phẩm
                const addMultipleBtn = document.getElementById('add-selected-to-cart');
                if (addMultipleBtn) {
                    addMultipleBtn.addEventListener('click', e => {
                        e.preventDefault();

                        const selectedCheckboxes = document.querySelectorAll('.select-card-check:checked');
                        if (selectedCheckboxes.length === 0) {
                            toastr.warning('Vui lòng chọn ít nhất một sản phẩm.');
                            return;
                        }

                        const products = Array.from(selectedCheckboxes).map(cb => ({
                            product_variant_id: cb.value,
                            quantity: 1
                        }));

                        fetch('/cart/add-multiple', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    products
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (Array.isArray(data.results)) {
                                    data.results.forEach(item => {
                                        if (item.success) {
                                            const cartUrl = "{{ route('cart.index') }}";
                                            const msg = `
                                    <div>
                                        ${item.message}
                                        <br>
                                        <a href="${cartUrl}" class="btn btn-sm btn-primary mt-2">Xem giỏ hàng</a>
                                    </div>
                                `;
                                            toastr.success(msg);
                                        } else {
                                            toastr.error(item.message);
                                        }
                                    });
                                }
                                updateCartBadge(data.cartItemCount);
                            })
                            .catch(() => {
                                toastr.error('Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.');
                            });
                    });
                }

                // ✅ Cập nhật badge giỏ hàng
                function updateCartBadge(count) {
                    const cartBadge = document.getElementById('cart-badge');
                    if (!cartBadge) return;
                    if (count && count > 0) {
                        cartBadge.textContent = count;
                        cartBadge.style.display = 'flex';
                    } else {
                        cartBadge.style.display = 'none';
                    }
                }
            });
        </script>

    @endsection
