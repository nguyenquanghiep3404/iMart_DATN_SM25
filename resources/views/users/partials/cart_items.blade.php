@if (isset($items) && $items->isNotEmpty())
    @foreach ($items as $item)
        <div class="d-flex align-items-center mb-3 border-bottom pb-2">
            <a class="flex-shrink-0" href="{{ route('users.products.show', ['slug' => $item['slug']]) }}">
                <img src="{{ asset($item['image'] ?: 'assets/users/img/shop/electronics/thumbs/08.png') }}" width="80"
                    alt="{{ $item['name'] }}">
            </a>
            </a>
            <div class="w-100 min-w-0 ps-2 ps-sm-3">
                <h5 class="d-flex animate-underline mb-2">
                    <a class="d-block fs-sm fw-medium text-truncate animate-target"
                        href="{{ route('users.products.show', ['slug' => $item['slug']]) }}">
                        {{ $item['name'] }}
                    </a>
                </h5>
                <div class="h6 pb-1 mb-2">{{ number_format($item['price'], 0, ',', '.') }} đ</div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="count-input rounded-2">
                        <button type="button" class="btn btn-icon btn-sm" data-decrement aria-label="Giảm số lượng"
                            data-item-id="{{ $item['id'] }}">
                            <i class="ci-minus"></i>
                        </button>
                        <input type="number" class="form-control form-control-sm" value="{{ $item['quantity'] }}"
                            readonly>
                        <button type="button" class="btn btn-icon btn-sm" data-increment aria-label="Tăng số lượng"
                            data-item-id="{{ $item['id'] }}">
                            <i class="ci-plus"></i>
                        </button>
                    </div>
                    <button type="button" class="btn-close fs-sm" data-bs-toggle="tooltip"
                        data-bs-custom-class="tooltip-sm" data-bs-title="Xóa" aria-label="Xóa khỏi giỏ hàng"
                        data-item-id="{{ $item['id'] }}"></button>
                </div>
            </div>
        </div>
    @endforeach
    <div class="offcanvas-header flex-column align-items-start">
        <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-md-4">
            <span class="text-light-emphasis">Tổng tiền:</span>
            <span class="h6 mb-0">{{ isset($subtotal) ? number_format($subtotal, 0, ',', '.') . ' đ' : '0 đ' }}</span>
        </div>
        <div class="d-flex w-100 gap-3">
            <a class="btn btn-lg btn-secondary w-100" href="/cart">Xem giỏ hàng</a>
            <a class="btn btn-lg btn-primary w-100" href="/checkout">Tiến hành thanh toán</a>
        </div>
    </div>
@else
    <p>Giỏ hàng trống.</p>
    <div class="offcanvas-header flex-column align-items-start">
        <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-md-4">
            <span class="text-light-emphasis">Tổng tiền:</span>
            <span class="h6 mb-0">{{ isset($subtotal) ? number_format($subtotal, 0, ',', '.') . ' đ' : '0 đ' }}</span>
        </div>
        <div class="d-flex w-100 gap-3">
            <a class="btn btn-lg btn-secondary w-100" href="/cart">Xem giỏ hàng</a>
            <a class="btn btn-lg btn-primary w-100" href="/checkout">Tiến hành thanh toán</a>
        </div>
    </div>
@endif
