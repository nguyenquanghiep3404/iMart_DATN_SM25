<div class="offcanvas offcanvas-end pb-sm-2 px-sm-2" id="shoppingCart" tabindex="-1" aria-labelledby="shoppingCartLabel"
    style="width: 500px">

    <!-- Header -->
    <div class="offcanvas-header flex-column align-items-start py-3 pt-lg-4">
        <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-lg-4">
            <h4 class="offcanvas-title" id="shoppingCartLabel">Giỏ hàng</h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <p class="fs-sm">Buy <span class="text-dark-emphasis fw-semibold">$183</span> more to get <span
                class="text-dark-emphasis fw-semibold">Free Shipping</span></p>
        <div class="progress w-100" role="progressbar" aria-label="Free shipping progress" aria-valuenow="75"
            aria-valuemin="0" aria-valuemax="100" style="height: 4px">
            <div class="progress-bar bg-warning rounded-pill" style="width: 75%"></div>
        </div>
    </div>

    <!-- Body: Items list -->
    <div class="offcanvas-body d-flex flex-column gap-4 pt-2" id="cart-content">
        @include('users.partials.cart_items')
    </div>

    <!-- Footer -->

</div>
