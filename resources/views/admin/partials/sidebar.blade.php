@php
    // Xác định mục nav cha nào đang active dựa trên route hiện tại
    // Điều này giúp sidebar tự động mở đúng mục khi tải lại trang hoặc truy cập trực tiếp vào link con
    $activeParentNav = null;
    $currentRouteName = request()->route()->getName();

    if (str_starts_with($currentRouteName, 'admin.dashboard')) {
        $activeParentNav = 0;
    } elseif (
        str_starts_with($currentRouteName, 'admin.products.') ||
        str_starts_with($currentRouteName, 'admin.attributes.')
    ) {
        $activeParentNav = 1; // Index của "Sản Phẩm"
    } elseif (str_starts_with($currentRouteName, 'admin.categories.')) {
        $activeParentNav = 2; // Index của "Danh mục sản phẩm"
    } elseif (str_starts_with($currentRouteName, 'admin.orders.')) {
        $activeParentNav = 3; // Index của "Đơn hàng"
    } elseif (str_starts_with($currentRouteName, 'admin.users.')) {
        $activeParentNav = 4; // Index của "Quản lý người dùng"
    } elseif (str_starts_with($currentRouteName, 'admin.reviews.')) {
        $activeParentNav = 5; // Index của "Quản lý đánh giá"
    } elseif (str_starts_with($currentRouteName, 'admin.coupons.')) {
        $activeParentNav = 6; // Index của "Quản lý mã giảm giá"
    } elseif (str_starts_with($currentRouteName, 'admin.roles.')) {
        $activeParentNav = 7; // Index của "Quản lý phân quyền"
    } elseif (str_starts_with($currentRouteName, 'admin.banners.')) {
        $activeParentNav = 8; // Index của "Quản lý banner"
    } elseif (str_starts_with($currentRouteName, 'admin.post-categories.')) {
        $activeParentNav = 9; // Index của "Quản lý danh mục bài viết"
    } elseif (str_starts_with($currentRouteName, 'admin.shippers.')) {
        $activeParentNav = 10; // Index của "Quản lý nhân viên giao hàng"
    }
    // Thêm các điều kiện khác nếu cần
@endphp

<aside
    class="w-[300px] lg:w-[250px] xl:w-[300px] border-r border-slate-200 overflow-y-auto sidebar-scrollbar fixed left-0 top-0 h-full bg-white z-40 transition-transform duration-300 print:hidden"
    :class="sideMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div>
        <div class="flex h-[65px] items-center border-b border-slate-200 px-6">
            <a href="{{ route('admin.dashboard') }}">
                {{-- Đặt chiều cao cho logo, chiều rộng sẽ tự động điều chỉnh --}}
                <img src="{{ asset('assets/users/logo/bfc4baa4-0e46-4289-8f62-2aea6a7d2a4b.png') }}" alt=""
                    width="200px" style="margin-left: 30px;">

            </a>
        </div>
        <div class="px-3 py-5" x-data="{ openNav: {{ $activeParentNav ?? 'null' }} }">
            <ul class="space-y-1">
                {{-- 1. Trang chủ --}}
                <li>
                    @php $isDashboardActive = request()->routeIs('admin.dashboard'); @endphp
                    <a href="{{ route('admin.dashboard') }}"
                        class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out
                              {{ $isDashboardActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isDashboardActive ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M7,0H4A4,4,0,0,0,0,4V7a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V4A4,4,0,0,0,7,0ZM9,7A2,2,0,0,1,7,9H4A2,2,0,0,1,2,7V4A2,2,0,0,1,4,2H7A2,2,0,0,1,9,4Z" />
                                <path
                                    d="M20,0H17a4,4,0,0,0-4,4V7a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V4A4,4,0,0,0,20,0Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V4a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z" />
                                <path
                                    d="M7,13H4a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V17A4,4,0,0,0,7,13Zm2,7a2,2,0,0,1-2,2H4a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2H7a2,2,0,0,1,2,2Z" />
                                <path
                                    d="M20,13H17a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V17A4,4,0,0,0,20,13Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z" />
                            </svg>
                        </span>
                        Trang chủ
                    </a>
                </li>

                {{-- 2. Sản Phẩm --}}
                <li>
                    @php $isProductSectionActive = request()->routeIs('admin.products.*') || request()->routeIs('admin.attributes.*'); @endphp
                    <button @click="openNav !== 1 ? openNav = 1 : openNav = null"
                        :class="openNav === 1 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isProductSectionActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M23.621,6.836l-1.352-2.826c-.349-.73-.99-1.296-1.758-1.552L14.214,.359c-1.428-.476-3-.476-4.428,0L3.49,2.458c-.769,.256-1.41,.823-1.759,1.554L.445,6.719c-.477,.792-.567,1.742-.247,2.609,.309,.84,.964,1.49,1.802,1.796l-.005,6.314c-.002,2.158,1.372,4.066,3.418,4.748l4.365,1.455c.714,.238,1.464,.357,2.214,.357s1.5-.119,2.214-.357l4.369-1.457c2.043-.681,3.417-2.585,3.419-4.739l.005-6.32c.846-.297,1.508-.946,1.819-1.79,.317-.858,.228-1.799-.198-2.499ZM10.419,2.257c1.02-.34,2.143-.34,3.162,0l4.248,1.416-5.822,1.95-5.834-1.95,4.246-1.415ZM2.204,7.666l1.327-2.782c.048,.025,7.057,2.373,7.057,2.373l-1.621,3.258c-.239,.398-.735,.582-1.173,.434l-5.081-1.693c-.297-.099-.53-.325-.639-.619-.109-.294-.078-.616,.129-.97Zm3.841,12.623c-1.228-.409-2.052-1.554-2.051-2.848l.005-5.648,3.162,1.054c1.344,.448,2.792-.087,3.559-1.371l.278-.557-.005,10.981c-.197-.04-.391-.091-.581-.155l-4.366-1.455Zm11.897-.001l-4.37,1.457c-.19,.063-.384,.115-.581,.155l.005-10.995,.319,.64c.556,.928,1.532,1.459,2.561,1.459,.319,0,.643-.051,.96-.157l3.161-1.053-.005,5.651c0,1.292-.826,2.435-2.052,2.844Zm4-11.644c-.105,.285-.331,.504-.619,.6l-5.118,1.706c-.438,.147-.934-.035-1.136-.365l-1.655-3.323s7.006-2.351,7.054-2.377l1.393,2.901c.157,.261,.186,.574,.081,.859Z" />
                            </svg>
                        </span>
                        Sản Phẩm
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 1 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                                fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 1" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{ route('admin.products.index') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.products.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách sản phẩm
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.products.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.products.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm mới sản phẩm
                            </a>
                        </li>
                        <li>

                            <a href="{{ route('admin.attributes.index') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.attributes.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Quản lý thuộc tính
                            </a>
                        </li>
                        <li>
                            {{-- Thay 'admin.attributes.create' bằng route của bạn --}}
                            <a href="{{ route('admin.attributes.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
 {{ request()->routeIs('admin.attributes.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm thuộc tính mới
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 3. Danh mục sản phẩm --}}
                <li>
                    @php $isCategoriesActive = request()->routeIs('admin.product-categories.*'); @endphp
                    <button @click="openNav !== 2 ? openNav = 2 : openNav = null"
                        :class="openNav === 2 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isCategoriesActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z" />
                                <circle cx="7" cy="22" r="2" />
                                <circle cx="17" cy="22" r="2" />
                            </svg>
                        </span>
                        Danh mục sản phẩm
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 2 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                                fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 2" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{ route('admin.categories.index') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.product-categories.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách danh mục
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.product-categories.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm mới danh mục
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 4. Đơn hàng --}}
                <li>
                    @php $isOrdersActive = request()->routeIs('admin.orders.*'); @endphp
                    <button @click="openNav !== 3 ? openNav = 3 : openNav = null"
                        :class="openNav === 3 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isOrdersActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="m11.349,24H0V3C0,1.346,1.346,0,3,0h12c1.654,0,3,1.346,3,3v5.059c-.329-.036-.662-.059-1-.059s-.671.022-1,.059V3c0-.552-.448-1-1-1H3c-.552,0-1,.448-1,1v19h7.518c.506.756,1.125,1.429,1.831,2Zm0-14h-7.349v2h5.518c.506-.756,1.125-1.429,1.831-2Zm-7.349,7h4c0-.688.084-1.356.231-2h-4.231v2Zm20,0c0,3.859-3.141,7-7,7s-7-3.141-7-7,3.141-7,7-7,7,3.141,7,7Zm-2,0c0-2.757-2.243-5-5-5s-5,2.243-5,5,2.243,5,5,5,5-2.243,5-5ZM14,5H4v2h10v-2Zm5.589,9.692l-3.228,3.175-1.63-1.58-1.393,1.436,1.845,1.788c.314.315.733.489,1.179.489s.865-.174,1.173-.482l3.456-3.399-1.402-1.426Z" />
                            </svg>
                        </span>
                        Đơn hàng
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 3 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                                fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 3" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{-- {{ route('admin.orders.index') }}" {{-- Giả sử route --}}
                               class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.orders.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách đơn hàng
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 5. Quản lý người dùng --}}
                <li>
                    @php $isUsersActive = request()->routeIs('admin.users.*'); @endphp
                    <button @click="openNav !== 4 ? openNav = 4 : openNav = null"
                        :class="openNav === 4 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isUsersActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M12,12A6,6,0,1,0,6,6,6.006,6.006,0,0,0,12,12ZM12,2A4,4,0,1,1,8,6,4,4,0,0,1,12,2Z" />
                                <path
                                    d="M12,14a9.01,9.01,0,0,0-9,9,1,1,0,0,0,1,1H20a1,1,0,0,0,1-1A9.01,9.01,0,0,0,12,14ZM4.136,22A7.006,7.006,0,0,1,11,16H13a7.006,7.006,0,0,1,6.864,6Z" />
                            </svg>
                        </span>
                        Quản lý người dùng
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 4 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                                fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 4" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{ route('admin.users.index') }}" {{-- Giả sử route --}}
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.users.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách người dùng
                            </a>
                        </li>
                        <li>
                            <a href="{{-- {{ route('admin.users.create') }}" {{-- Giả sử route --}}
                               class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.users.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm mới người dùng
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 6. Quản lý đánh giá --}}
                <li>
                    @php $isReviewsActive = request()->routeIs('admin.reviews.*'); @endphp
                    <a href="{{-- route('admin.reviews.index') }}" {{-- Giả sử route --}}
                       class="group flex items-center px-4 py-2.5
                        text-base rounded-md transition-all duration-200 ease-in-out
                        {{ $isReviewsActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isReviewsActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M22,9.67A1,1,0,0,0,21.14,9l-5.69-.83L12.9,3a1,1,0,0,0-1.8,0L8.55,8.16,2.86,9a1,1,0,0,0-.54,1.75l4.13,4-1,5.68A1,1,0,0,0,6.9,22.25l5.1-2.68,5.1,2.68a1,1,0,0,0,1.45-1.05l-1-5.68,4.13-4A1,1,0,0,0,22,9.67ZM12,18.27,8.24,20.3,9.23,16,6.06,13.18l4.32-.63L12,8.88l1.62,3.67,4.32.63L14.77,16l1,4.3Z" />
                            </svg>
                        </span>
                        Quản lý đánh giá
                    </a>
                </li>

                {{-- 7. Quản lý mã giảm giá --}}
                <li>
                    @php $isCouponsActive = request()->routeIs('admin.coupons.*'); @endphp
                    <a href="{{-- {{ route('admin.coupons.index') }}" {{-- Giả sử route --}}
                       class="group flex items-center px-4 py-2.5
                        text-base rounded-md transition-all duration-200 ease-in-out
                        {{ $isCouponsActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isCouponsActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M19.6,4.4a8.21,8.21,0,0,0-11.41,0l-4.24,4.24a8.21,8.21,0,0,0,11.41,11.41l4.24-4.24a8.21,8.21,0,0,0,0-11.41ZM12,18.49a6.21,6.21,0,0,1-4.4-1.83L3.35,12.41a6.21,6.21,0,0,1,8.79-8.79l.17.17,4.24,4.24-4.24,4.24ZM18.17,9.83,14,5.66l1.41-1.41,4.24,4.24ZM9.83,18.17,5.66,14l-1.41,1.41,4.24,4.24ZM14.12,14A3.1,3.1,0,1,0,9.88,9.88,3.1,3.1,0,0,0,14.12,14Z" />
                            </svg>
                        </span>
                        Quản lý mã giảm giá
                    </a>
                </li>

                {{-- 8. Quản lý phân quyền --}}
                <li>
                    @php $isRolesActive = request()->routeIs('admin.roles.*'); @endphp
                    <a href="{{-- {{ route('admin.roles.index') }}" {{-- Giả sử route --}}
                       class="group flex items-center px-4 py-2.5
                        text-base rounded-md transition-all duration-200 ease-in-out
                        {{ $isRolesActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isRolesActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M12,12.75a3,3,0,1,0-3-3A3,3,0,0,0,12,12.75ZM12,8.25a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,12,8.25Z" />
                                <path d="M21.5,9.75a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,21.5,9.75Z" />
                                <path d="M21.5,2.25a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,21.5,2.25Z" />
                                <path d="M21.5,17.25a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,21.5,17.25Z" />
                                <path
                                    d="M12,21.75a3,3,0,1,0-3-3A3,3,0,0,0,12,21.75ZM12,17.25a1.5,1.5,0,1,1-1.5,1.5A1.5,1.5,0,0,1,12,17.25Z" />
                                <path
                                    d="M12,5.25a3,3,0,1,0-3-3A3,3,0,0,0,12,5.25ZM12,.75A1.5,1.5,0,1,1,10.5,2.25,1.5,1.5,0,0,1,12,.75Z" />
                                <path
                                    d="M4,11.25a3,3,0,1,0-3-3A3,3,0,0,0,4,11.25ZM4,6.75A1.5,1.5,0,1,1,2.5,8.25,1.5,1.5,0,0,1,4,6.75Z" />
                                <path
                                    d="M18.27,15.64a4.48,4.48,0,0,0-2.3-2.3,4.5,4.5,0,0,0-5.94,5.94,4.48,4.48,0,0,0,2.3,2.3A4.5,4.5,0,0,0,18.27,15.64Zm-6.73,3.82a3,3,0,0,1,3.83-3.83,3.08,3.08,0,0,1,.89.13,3,3,0,0,1,1.38,4.49,3.08,3.08,0,0,1-.13.89A3,3,0,0,1,11.54,19.46Z" />
                                <path
                                    d="M5.73,8.36a4.5,4.5,0,0,0,5.94-5.94A4.48,4.48,0,0,0,9.37.12a4.5,4.5,0,0,0-5.94,5.94A4.48,4.48,0,0,0,5.73,8.36ZM9.56,2.29a3,3,0,1,1-4.5,1.38,3,3,0,0,1,1.37-4.49A3,3,0,0,1,9.56,2.29Z" />
                            </svg>
                        </span>
                        Quản lý phân quyền
                    </a>
                </li>

                {{-- 9. Quản lý banner --}}
                <li>
                    @php $isBannersActive = request()->routeIs('admin.banners.*'); @endphp
                    <a href="{{-- {{ route('admin.banners.index') }}"  {{-- Giả sử route --}}
                       class="group flex items-center px-4 py-2.5
                        text-base rounded-md transition-all duration-200 ease-in-out
                        {{ $isBannersActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isBannersActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M21,2H3A1,1,0,0,0,2,3V21a1,1,0,0,0,1,1H21a1,1,0,0,0,1-1V3A1,1,0,0,0,21,2ZM4,4H20V16H4ZM4,20V18H20v2Z" />
                                <path d="M12.5,11.5a1,1,0,1,0-1-1A.999.999,0,0,0,12.5,11.5Z" />
                                <path d="M6,6H8V8H6Z" />
                            </svg>
                        </span>
                        Quản lý banner
                    </a>
                </li>

                {{-- 10. Quản lý danh mục bài viết --}}
                <li>
                    @php $isPostCategoriesActive = request()->routeIs('admin.post-categories.*'); @endphp
                    <button @click="openNav !== 9 ? openNav = 9 : openNav = null"
                        :class="openNav === 9 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isPostCategoriesActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M19,2H5A3,3,0,0,0,2,5V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V5A3,3,0,0,0,19,2Zm1,17a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V5A1,1,0,0,1,5,4H19a1,1,0,0,1,1,1Z" />
                                <path d="M7,7h4a1,1,0,0,0,0-2H7A1,1,0,0,0,7,7Z" />
                                <path d="M17,11H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z" />
                                <path d="M17,15H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z" />
                            </svg>
                        </span>
                        Quản lý bài viết
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 9 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                height="16" fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 9" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        
                        <li>
                            <a href="{{ route('admin.post-tags.index') }}" {{-- Giả sử route --}}
                                class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.post-tags.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách thẻ bài viết
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.posts.index') }}" {{-- Giả sử route --}}
                                class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.posts.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách bài viết
                            </a>
                        </li>
                        
                    </ul>
                </li>

                {{-- 11. Quản lý nhân viên giao hàng --}}
                <li>
                    @php $isShippersActive = request()->routeIs('admin.shippers.*'); @endphp
                    <a href="{{-- {{ route('admin.shippers.index') }}" {{-- Giả sử route --}}
                       class="group flex items-center px-4 py-2.5
                        text-base rounded-md transition-all duration-200 ease-in-out
                        {{ $isShippersActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isShippersActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M21.5,13a.5.5,0,0,0,0-.15L20.15,7.2A3.49,3.49,0,0,0,16.85,5H15V2.5a.5.5,0,0,0-1,0V5H10V2.5a.5.5,0,0,0-1,0V5H3.5a.5.5,0,0,0-.5.5v11a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,0-1H4V14h7v2.5a.5.5,0,0,0,1,0V14h9v2.5a.5.5,0,0,0,.5.5h1a.5.5,0,0,0,.5-.5V13.5A.5.5,0,0,0,21.5,13ZM9,12H4V7.5a.5.5,0,0,0-.5-.5H3.12l1-4.2A2.5,2.5,0,0,1,6.5,1H17.5a2.5,2.5,0,0,1,2.38,1.8l1,4.2H15V12H9ZM7,9.5A1.5,1.5,0,1,1,5.5,8,1.5,1.5,0,0,1,7,9.5Zm12,0a1.5,1.5,0,1,1-1.5-1.5A1.5,1.5,0,0,1,19,9.5Z" />
                            </svg>
                        </span>
                        Quản lý nhân viên giao hàng
                    </a>
                </li>

            </ul>

            {{-- Phần Cài đặt & Trang phụ (Giữ nguyên) --}}
            <div class="border-t border-gray-200 pt-3 mt-3">
                {{-- Giữ nguyên code của bạn cho "Cài đặt trang web" và "Thêm trang" --}}
            </div>
        </div>
    </div>
</aside>
