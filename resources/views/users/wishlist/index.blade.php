@extends('users.layouts.profile')

@section('content')
    <div class="col-lg-9">
        <div class="ps-lg-3 ps-xl-0">
            <!-- Page title + Add list button-->
            <div class="d-flex align-items-center justify-content-between pb-3 mb-1 mb-sm-2 mb-md-3">
                <h1 class="h2 me-3 mb-0">Wishlist</h1>
                <div class="nav">
                    <a class="nav-link animate-underline px-0 py-1 py-ms-2" href="#wishlistModal" data-bs-toggle="modal">
                        <i class="ci-plus fs-base me-1"></i>
                        <span class="animate-target">Add wishlist</span>
                    </a>
                </div>
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
                        <select class="form-select" aria-label="Wishlist sorting">
                            <option value="date">By date added</option>
                            <option value="price-ascend">By price ascending</option>
                            <option value="price-descend">By price descending</option>
                            <option value="rating">By rating</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Master checkbox + Action buttons -->
            <div class="nav align-items-center mb-4">
                <div class="form-checkl nav-link animate-underline fs-lg ps-0 pe-2 py-2 mt-n1 me-4"
                    data-master-checkbox='{"container": "#wishlistSelection", "label": "Select all", "labelChecked": "Unselect all", "showOnCheck": "#action-buttons"}'>
                    <input type="checkbox" class="form-check-input" id="wishlist-master" checked>
                    <label for="wishlist-master" class="form-check-label animate-target mt-1 ms-2">Unselect all</label>
                </div>
                <div class="d-flex flex-wrap" id="action-buttons">
                    <a class="nav-link animate-underline px-0 pe-sm-2 py-2 me-4" href="#!">
                        <i class="ci-shopping-cart fs-base me-2"></i>
                        <span class="animate-target d-none d-md-inline">Add to cart</span>
                    </a>
                    <a class="nav-link animate-underline px-0 pe-sm-2 py-2 me-4" href="#!">
                        <i class="ci-repeat fs-base me-2"></i>
                        <span class="animate-target d-none d-md-inline">Relocate</span>
                    </a>
                    <button type="submit" form="wishlist-form" class="nav-link animate-underline px-0 py-2 btn btn-link">
                        <i class="ci-trash fs-base me-1"></i>
                        <span class="animate-target d-none d-md-inline">Remove selected</span>
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
                                        href="{{ route('shop.product.show', $product->productVariant->id) }}">
                                        @if ($product->productVariant->discount_percent)
                                            <span
                                                class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2 mt-lg-3 ms-lg-3">
                                                -{{ $product->productVariant->discount_percent }}%
                                            </span>
                                        @endif

                                        <div class="ratio mb-2" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                                            <img src="{{ asset('storage/' . $product->productVariant->image) }}"
                                                alt="...">
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

                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="h5 lh-1 mb-0">
                                            ${{ number_format($product->productVariant->price, 2) }}
                                            @if ($product->productVariant->original_price > $product->productVariant->price)
                                                <del class="text-body-tertiary fs-sm fw-normal">
                                                    ${{ number_format($product->productVariant->original_price, 2) }}
                                                </del>
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
                        <p>No products in your wishlist.</p>
                    @endforelse
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('wishlist-master').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.select-card-check').forEach(cb => cb.checked = checked);

            const label = this.nextElementSibling;
            if (label) {
                label.textContent = checked ? 'Unselect all' : 'Select all';
            }
        });
    </script>
@endsection
