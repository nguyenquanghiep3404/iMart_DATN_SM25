@php
    $currentRouteName = request()->route()->getName();
    $activeParentNav = null;
    $user = auth()->user() ?? (object) [];
    // Define navigation sections with their corresponding route prefixes for active state detection.
    $navSections = [
        'dashboard' => ['admin.dashboard'],
        'sales' => ['admin.orders.', 'admin.purchase-orders.', 'admin.abandoned-carts.'],
        'stores' => ['admin.store-locations.', 'admin.chat.'],
        'catalog' => [
            'admin.products.',
            'admin.categories.',
            'admin.attributes.',
            'admin.specification-groups.',
            'admin.specifications.',
            'admin.bundle-products.',
            'admin.trade-in-items.',
        ],
        'marketing' => ['admin.coupons.', 'admin.flash-sales.', 'admin.homepage.', 'admin.banners.'],
        'content' => ['admin.posts.', 'admin.categories_post.', 'admin.post-tags.', 'admin.comment.', 'admin.reviews.'],
        'customers' => ['admin.users.'],
        'employees' => ['admin.shippers.', 'admin.content-staffs.', 'admin.order-manager.', 'admin.roles.'],
        'suppliers' => ['admin.suppliers.'],
        'media' => ['admin.media.'],
    ];
    // Determine the currently active parent navigation section.
    foreach ($navSections as $key => $prefixes) {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($currentRouteName, $prefix)) {
                $activeParentNav = $key;
                break 2; // Exit both loops once the active section is found.
            }
        }
    }
@endphp

<aside id="adminSidebar"
    class="w-[300px] border-r border-slate-200 overflow-y-auto sidebar-scrollbar fixed left-0 top-0 h-full bg-white z-40 transition-transform duration-300 print:hidden flex flex-col"
    {{-- Updated x-show and :class logic for responsive behavior and persistence --}}
    x-show="(window.innerWidth >= 1024) ? true : sideMenu"
    :class="(window.innerWidth >= 1024 && !sideMenu) ? '-translate-x-full lg:-translate-x-full' : ((sideMenu || window.innerWidth >= 1024) ? 'translate-x-0' : '-translate-x-full')"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full">
    <div class="flex flex-col h-full" x-data="{ openNav: '{{ $activeParentNav }}' }">
        {{-- Search box --}}
        {{-- <div class="p-4">
            <input
                class="w-full h-10 pl-10 pr-4 bg-slate-100 dark:bg-slate-700 border border-transparent rounded-lg focus:bg-white dark:focus:bg-slate-600 focus:border-indigo-400 focus:ring-indigo-400 focus:ring-1 transition-all duration-300 outline-none text-slate-700 dark:text-slate-200"
                type="text" placeholder="Tìm kiếm...">
        </div> --}}
        {{-- SIDEBAR HEADER --}}
        <div class="flex h-[65px] items-center justify-center border-b border-slate-200 px-3 lg:px-6 py-4">
            <a href="{{ route('admin.dashboard') }}">
                {{-- Full Logo --}}
                <img class="hidden lg:block" src="{{ asset('assets/users/logo/logo-full.svg') }}" alt="Full Logo"
                    style="width: 150px;">
                {{-- Icon Logo --}}
                <img class="block lg:hidden" src="{{ asset('assets/users/logo/logo-icon.svg') }}" alt="Icon Logo"
                    style="width: 40px;">
            </a>
        </div>
        {{-- END HEADER --}}
        {{-- Navigation --}}
        <div class="flex-1 overflow-y-auto">
            <div class="px-3 py-5">
                <ul class="space-y-1">
                    {{-- 1. Dashboard --}}
                    <li>
                        @php $isDashboardActive = request()->routeIs('admin.dashboard'); @endphp
                        <a href="{{ route('admin.dashboard') }}"
                            class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out
                                   {{ $isDashboardActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                            <span
                                class="mr-3 text-lg {{ $isDashboardActive ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/home -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            Trang chủ
                        </a>
                    </li>
                    {{-- 2. Sales Management --}}
                    <li>
                        <button @click="openNav !== 'sales' ? openNav = 'sales' : openNav = null"
                            :class="openNav === 'sales' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'sales' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/squares-2x2 -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </span>
                            Quản lý Bán hàng
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'sales' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'sales'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="{{ route('admin.orders.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Đơn
                                    hàng</a>
                            </li>
                            <li><a href="{{ route('admin.purchase-orders.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.purchase-orders.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Nhập
                                    kho</a>
                            </li>
                            <li><a href="{{ route('admin.abandoned-carts.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.abandoned-carts.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Giỏ
                                    hàng bỏ lỡ</a>
                            </li>
                        </ul>
                    </li>
                    {{-- 3. Store Management --}}
                    <li>
                        <button @click="openNav !== 'stores' ? openNav = 'stores' : openNav = null"
                            :class="openNav === 'stores' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'stores' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/building-store -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M21,8H19V5H16V3h3a2,2,0,0,1,2,2ZM5,8H3V5A2,2,0,0,1,5,3H8V5H5Zm13,9v3H6V17H3a2,2,0,0,0-2,2v2H23V19a2,2,0,0,0-2-2Z" />
                                </svg>
                            </span>
                            Quản lý Cửa hàng
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'stores' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'stores'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="{{ route('admin.store-locations.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.store-locations.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Địa
                                    điểm Cửa hàng</a>
                            </li>
                            <li><a href="{{ route('admin.chat.dashboard') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.chat.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Chat
                                    với Khách hàng</a>
                            </li>
                        </ul>
                    </li>
                    {{-- 4. Catalog Management --}}
                    <li>
                        <button @click="openNav !== 'catalog' ? openNav = 'catalog' : openNav = null"
                            :class="openNav === 'catalog' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'catalog' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/document-text -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M23.621,6.836l-1.352-2.826c-.349-.73-.99-1.296-1.758-1.552L14.214,.359c-1.428-.476-3-.476-4.428,0L3.49,2.458c-.769,.256-1.41,.823-1.759,1.554L.445,6.719c-.477,.792-.567,1.742-.247,2.609,.309,.84,.964,1.49,1.802,1.796l-.005,6.314c-.002,2.158,1.372,4.066,3.418,4.748l4.365,1.455c.714,.238,1.464,.357,2.214,.357s1.5-.119,2.214-.357l4.369-1.457c2.043-.681,3.417-2.585,3.419-4.739l.005-6.32c.846-.297,1.508-.946,1.819-1.79,.317-.858,.228-1.799-.198-2.499ZM10.419,2.257c1.02-.34,2.143-.34,3.162,0l4.248,1.416-5.822,1.95-5.834-1.95,4.246-1.415ZM2.204,7.666l1.327-2.782c.048,.025,7.057,2.373,7.057,2.373l-1.621,3.258c-.239,.398-.735,.582-1.173,.434l-5.081-1.693c-.297-.099-.53-.325-.639-.619-.109-.294-.078-.616,.129-.97Zm3.841,12.623c-1.228-.409-2.052-1.554-2.051-2.848l.005-5.648,3.162,1.054c1.344,.448,2.792-.087,3.559-1.371l.278-.557-.005,10.981c-.197-.04-.391-.091-.581-.155l-4.366-1.455Zm11.897-.001l-4.37,1.457c-.19,.063-.384,.115-.581,.155l.005-10.995,.319,.64c.556,.928,1.532,1.459,2.561,1.459,.319,0,.643-.051,.96-.157l3.161-1.053-.005,5.651c0,1.292-.826,2.435-2.052,2.844Zm4-11.644c-.105,.285-.331,.504-.619,.6l-5.118,1.706c-.438,.147-.934-.035-1.136-.365l-1.655-3.323s7.006-2.351,7.054-2.377l1.393,2.901c.157,.261,.186,.574.081,.859Z" />
                                </svg>
                            </span>
                            Quản lý Catalogue
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'catalog' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'catalog'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="{{ route('admin.products.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.products.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Sản
                                    phẩm</a>
                            </li>
                            <li><a href="{{ route('admin.bundle-products.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.bundle-products.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Gói
                                    sản phẩm</a>
                            </li>
                            <li><a href="{{ route('admin.categories.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Danh
                                    mục</a>
                            </li>
                            <li><a href="{{ route('admin.attributes.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.attributes.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Thuộc
                                    tính</a>
                            </li>
                            <li><a href="{{ route('admin.specifications.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.specifications.*') || request()->routeIs('admin.specification-groups.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Thông
                                    số</a>
                            </li>
                            <li><a href="{{ route('admin.trade-in-items.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.trade-in-items.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Thu
                                    cũ & Mở hộp</a>
                            </li>
                        </ul>
                    </li>
                    {{-- 5. Marketing Management --}}
                    <li>
                        <button @click="openNav !== 'marketing' ? openNav = 'marketing' : openNav = null"
                            :class="openNav === 'marketing' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'marketing' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/tag -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z" />
                                    <path
                                        d="M12.71,6.29a1,1,0,0,0-1.42,0l-3,3a1,1,0,0,0,1.42,1.42L11,9.41V17a1,1,0,0,0,2,0V9.41l1.29,1.3a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42Z" />
                                </svg>
                            </span>
                            Quản lý Marketing
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'marketing' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'marketing'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="{{ route('admin.coupons.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.coupons.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Mã
                                    giảm giá</a>
                            </li>
                            <li><a href="{{ route('admin.flash-sales.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.flash-sales.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Flash
                                    Sales</a>
                            </li>
                            <li><a href="{{ route('admin.homepage.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.homepage.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Quản
                                    lý Trang chủ</a>
                            </li>
                            <li><a href="{{ route('admin.banners.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.banners.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Quản
                                    lý Banner</a>
                            </li>
                        </ul>
                    </li>
                    {{-- 6. Content Management --}}
                    <li>
                        <button @click="openNav !== 'content' ? openNav = 'content' : openNav = null"
                            :class="openNav === 'content' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'content' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/document-text -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M19,2H5A3,3,0,0,0,2,5V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V5A3,3,0,0,0,19,2Zm1,17a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V5A1,1,0,0,1,5,4H19a1,1,0,0,1,1,1V19ZM7,7h4a1,1,0,0,0,0-2H7A1,1,0,0,0,7,7Z" />
                                    <path d="M17,11H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z" />
                                    <path d="M17,15H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z" />
                                </svg>
                            </span>
                            Quản lý Nội dung
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'content' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'content'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="{{ route('admin.posts.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.posts.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Bài
                                    viết</a>
                            </li>
                            <li><a href="{{ route('admin.categories_post.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.categories_post.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Danh
                                    mục bài viết</a>
                            </li>
                            <li><a href="{{ route('admin.post-tags.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.post-tags.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Thẻ
                                    bài viết</a>
                            </li>
                            <li><a href="{{ route('admin.comment.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.comment.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Bình
                                    luận</a>
                            </li>
                            <li><a href="{{ route('admin.reviews.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.reviews.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Đánh
                                    giá</a>
                            </li>
                        </ul>
                    </li>
                    {{-- 7. Customer Management --}}
                    <li>
                        <button @click="openNav !== 'customers' ? openNav = 'customers' : openNav = null"
                            :class="openNav === 'customers' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'customers' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/users -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M12,12A6,6,0,1,0,6,6,6.006,6.006,0,0,0,12,12Zm0-10a4,4,0,1,1-4,4A4,4,0,0,1,12,2Z" />
                                    <path
                                        d="M12,14a9.01,9.01,0,0,0-9,9,1,1,0,0,0,1,1H20a1,1,0,0,0,1-1A9.01,9.01,0,0,0,12,14Zm-7,8a7.012,7.012,0,0,1,14,0Z" />
                                </svg>
                            </span>
                            Quản lý Khách hàng
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'customers' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'customers'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="{{ route('admin.users.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Danh
                                    sách khách hàng</a>
                            </li>
                        </ul>
                    </li>
                    {{-- 8. Employee Management --}}
                    <li>
                        <button @click="openNav !== 'employees' ? openNav = 'employees' : openNav = null"
                            :class="openNav === 'employees' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'employees' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/briefcase -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path d="M16,14a5,5,0,1,0-5-5A5,5,0,0,0,16,14Zm0-8a3,3,0,1,1-3,3A3,3,0,0,1,16,6Z" />
                                    <path d="M16,16a6.991,6.991,0,0,0-5.83,3H21.83A6.991,6.991,0,0,0,16,16Z" />
                                    <path d="M8,9a5,5,0,1,0-5,5A5,5,0,0,0,8,9ZM8,1a3,3,0,1,1-3,3A3,3,0,0,1,8,1Z" />
                                    <path d="M8,11a6.991,6.991,0,0,0-5.83,3H13.83A6.991,6.991,0,0,0,8,11Z" />
                                </svg>
                            </span>
                            Quản lý Nhân viên
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'employees' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'employees'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="{{ route('admin.shippers.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.shippers.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">NV
                                    Giao hàng</a>
                            </li>
                            <li><a href="{{ route('admin.content-staffs.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.content-staffs.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">NV
                                    Nội dung</a>
                            </li>
                            <li><a href="{{ route('admin.order-manager.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.order-manager.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">NV
                                    Đơn hàng</a>
                            </li>
                            <li><a href="{{ route('admin.roles.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Vai
                                    trò & Quyền</a>
                            </li>
                        </ul>
                    </li>
                    {{-- 9. Supplier Management --}}
                    <li>
                        @php $isSuppliersActive = request()->routeIs('admin.suppliers.*'); @endphp
                        <a href="{{ route('admin.suppliers.index') }}"
                            class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out
                                   {{ $isSuppliersActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                            <span
                                class="mr-3 text-lg {{ $isSuppliersActive ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/handshake -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path d="M20.707,7.293l-1.414-1.414L12,13.586,4.707,6.293,3.293,7.707,12,16.414Z" />
                                    <path
                                        d="M12,2A10,10,0,0,0,2,12a9.951,9.951,0,0,0,1.293,4.707l1.414-1.414A7.951,7.951,0,0,1,4,12,8,8,0,0,1,12,4a7.951,7.951,0,0,1,4.707,1.293l1.414-1.414A9.951,9.951,0,0,0,12,2Z" />
                                </svg>
                            </span>
                            Nhà cung cấp
                        </a>
                    </li>
                    {{-- 10. Media Library --}}
                    <li>
                        @php $isMediaActive = request()->routeIs('admin.media.*'); @endphp
                        <a href="{{ route('admin.media.index') }}"
                            class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out
                                   {{ $isMediaActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                            <span
                                class="mr-3 text-lg {{ $isMediaActive ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Heroicon name: mini/photo -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z" />
                                </svg>
                            </span>
                            Thư viện Media
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        {{-- Settings & Pages Section --}}
        <div class="border-t border-gray-200 pt-3 mt-3">
            <h3 class="px-4 text-xs font-semibold uppercase text-gray-400 tracking-wider">
                Cài đặt & Trang
            </h3>
            <ul class="space-y-1 mt-2">
                {{-- Web Settings --}}
                <li>
                    <button @click="openNav !== 'settings' ? openNav = 'settings' : openNav = null"
                        :class="openNav === 'settings' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all">
                        <span
                            class="mr-3 text-lg {{ $activeParentNav === 'settings' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                            <!-- Heroicon name: mini/cog -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M19.43,12.94a1,1,0,0,0-1.15-.36l-1.09.36a8.31,8.31,0,0,0-1.25-1.25l.36-1.09a1,1,0,0,0-.36-1.15L14,7.57a1,1,0,0,0-1.21,0L11.06,9.43a1,1,0,0,0-.36,1.15l.36,1.09a8.31,8.31,0,0,0-1.25,1.25l-1.09-.36a1,1,0,0,0-1.15.36L5.57,14a1,1,0,0,0,0,1.21l1.86,1.73a1,1,0,0,0,1.15.36l1.09-.36a8.31,8.31,0,0,0,1.25,1.25l-.36,1.09a1,1,0,0,0,.36,1.15L12,22.43a1,1,0,0,0,1.21,0l1.86-1.86a1,1,0,0,0,.36-1.15l-.36-1.09a8.31,8.31,0,0,0,1.25-1.25l1.09.36a1,1,0,0,0,1.15-.36L20.43,14a1,1,0,0,0,0-1.21ZM12,15.5A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Zm8-11.41-2.12,2.12a1,1,0,0,1-1.42,0,1,1,0,0,1,0-1.42L18.59,2.59A1,1,0,0,1,20,2.59,1,1,0,0,1,20,4.09ZM5.41,18.59,3.29,16.47a1,1,0,0,1,0-1.42,1,1,0,0,1,1.42,0L6.83,17.17a1,1,0,0,1,0,1.42A1,1,0,0,1,5.41,18.59Z" />
                            </svg>
                        </span>
                        Cài đặt trang web
                        <span class="ml-auto transition-transform"
                            :class="openNav === 'settings' ? 'rotate-90' : ''">
                            <!-- Heroicon name: mini/chevron-right -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                height="16" fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                    </button>
                    <ul x-show="openNav === 'settings'" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li><a href="#"
                                class="block w-full py-1.5 px-3 text-sm rounded-md text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50">Cài
                                đặt chung</a>
                        </li>
                        <li><a href="#"
                                class="block w-full py-1.5 px-3 text-sm rounded-md text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50">Thanh
                                toán & Vận chuyển</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</aside>