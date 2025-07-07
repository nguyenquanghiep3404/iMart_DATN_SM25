<!DOCTYPE html><html lang="en" data-bs-theme="light" data-pwa="true">
<!-- Mirrored from cartzilla-html.createx.studio/account-wishlist.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 07 May 2025 16:31:24 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->

    @include('users.wishlist.layouts.partials.head')

  <!-- Body -->
<body>
    <!-- Customizer offcanvas -->
    @include('users.wishlist.layouts.partials.customizer')
    <!-- Shopping cart offcanvas -->
    @include('users.partials.cart_offcanvas')
    <!-- Bonuses info modal -->
    @include('users.wishlist.layouts.partials.bonus')
    <!-- Create new wishlist modal -->
    @include('users.wishlist.layouts.partials.create-whishlist')
    <!-- Navigation bar (Page header) -->
    @include('users.partials.header')
    {{-- @include('users.wishlist.layouts.partials.header') --}}
    <!-- Page content -->
    @yield('content')
    <!-- Page footer -->
    @include('users.partials.footer')
    <!-- Sidebar navigation offcanvas toggle that is visible on screens < 992px wide (lg breakpoint) -->
    <button type="button" class="fixed-bottom z-sticky w-100 btn btn-lg btn-dark border-0 border-top border-light border-opacity-10 rounded-0 pb-4 d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#accountSidebar" aria-controls="accountSidebar" data-bs-theme="light">
      <i class="ci-sidebar fs-base me-2"></i>
      Account menu
    </button>
    <!-- Back to top button -->
    @include('users.wishlist.layouts.partials.floating-buttons')
    <!-- Vendor scripts -->
    <script src="{{ asset('assets/users/vendor/choices.js/choices.min.js') }}"></script>
    <!-- Bootstrap + Theme scripts -->
    <script src="{{ asset('assets/users/js/theme.min.js') }}"></script>
</body>
<!-- Mirrored from cartzilla-html.createx.studio/account-wishlist.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 07 May 2025 16:31:24 GMT -->
</html>