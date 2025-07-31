@php
    $currentRouteName = request()->route()->getName();
    $user = auth()->user() ?? (object) [];
    $activeParentNav = null; // Initialize active nav variable

    // Define navigation sections with their corresponding route prefixes for active state detection.
    // I've created a new 'inventory' section for better organization.
    $navSections = [
        'dashboard' => ['admin.dashboard'],
        'sales' => ['admin.orders.', 'admin.packing-station.', 'admin.abandoned-carts.'],
        'inventory' => ['admin.purchase-orders.', 'admin.stock-transfers.'], // New Inventory Section
        'stores' => ['admin.store-locations.', 'admin.chat.'],
        'catalog' => [
            'admin.products.',
            'admin.categories.',
            'admin.attributes.',
            'admin.specification-groups.',
            'admin.specifications.',
            'admin.bundle-products.',
            'admin.trade-in-items.',
            'admin.suppliers.',
        ],
        'marketing' => ['admin.coupons.', 'admin.flash-sales.', 'admin.homepage.', 'admin.banners.'],
        'content' => ['admin.posts.', 'admin.categories_post.', 'admin.post-tags.', 'admin.comment.', 'admin.reviews.'],
        'customers' => ['admin.users.'],
        'employees' => ['admin.shippers.', 'admin.content-staffs.', 'admin.order-manager.', 'admin.roles.'],
        'media' => ['admin.media.'],
        // Note: 'settings' is handled separately below.
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
    {{-- Updated x-show and :class logic for responsive behavior and persistence --}} x-show="(window.innerWidth >= 1024) ? true : sideMenu"
    :class="(window.innerWidth >= 1024 && !sideMenu) ? '-translate-x-full lg:-translate-x-full' : ((sideMenu || window
        .innerWidth >= 1024) ? 'translate-x-0' : '-translate-x-full')"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
    <div class="flex flex-col h-full" x-data="{ openNav: '{{ $activeParentNav }}' }">
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
                                    <path
                                        d="M20,8h-3V5.66a2,2,0,0,0-2-2H9a2,2,0,0,0-2,2V8H4a2,2,0,0,0-2,2v9a2,2,0,0,0,2,2H20a2,2,0,0,0,2-2V10A2,2,0,0,0,20,8ZM9,5.66a.34.34,0,0,1,.33-.33h5.34a.34.34,0,0,1,.33.33V8H9ZM20.33,19a.34.34,0,0,1-.33.33H4a.34.34,0,0,1-.33-.33V10a.34.34,0,0,1,.33-.33H20a.34.34,0,0,1,.33.33Z" />
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
                                <!-- Icon: Shopping Cart -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M21.08,7a2,2,0,0,0-1.7-1H6.58L6,3.74A1,1,0,0,0,5,3H3A1,1,0,0,0,3,5H4.24L7,15.26A1,1,0,0,0,8,16H18a1,1,0,0,0,.93-.66L21.23,9.34A2,2,0,0,0,21.08,7Zm-2.39,7H8.74L7.22,7H19.38Z" />
                                    <circle cx="8.5" cy="19.5" r="1.5" />
                                    <circle cx="17.5" cy="19.5" r="1.5" />
                                </svg>
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
                        <ul x-show="openNav === 'sales'" class="pl-8 pr-2 py-1 space-y-1 mt-1" style="display: none;">
                            <li><a href="{{ route('admin.orders.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.orders.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Đơn
                                    hàng</a>
                            </li>
                            <li><a href="{{ route('admin.packing-station.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.packing-station.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Trạm
                                    đóng gói</a>
                            </li>
                            <li><a href="{{ route('admin.abandoned-carts.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.abandoned-carts.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Giỏ
                                    hàng bỏ lỡ</a>
                            </li>
                        </ul>
                    </li>
                    {{-- NEW: Inventory Management --}}
                    <li>
                        <button @click="openNav !== 'inventory' ? openNav = 'inventory' : openNav = null"
                            :class="openNav === 'inventory' ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                            <span
                                class="mr-3 text-lg {{ $activeParentNav === 'inventory' ? 'text-indigo-600' : 'text-slate-500 group-hover:text-indigo-500' }}">
                                <!-- Icon: Archive Box -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M20.54,5.23l-1.39-1.39A3,3,0,0,0,17,3H7A3,3,0,0,0,4.85,3.84L3.46,5.23A3,3,0,0,0,3,7.35V19a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V7.35A3,3,0,0,0,20.54,5.23ZM5.41,5H18.59l.6,0.6V7H5V5.6ZM19,19a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V9H19Z" />
                                    <path d="M10.5,13.5h3a1,1,0,0,0,0-2h-3a1,1,0,0,0,0,2Z" />
                                </svg>
                            </span>
                            Quản lý Kho
                            <span class="ml-auto transition-transform duration-200"
                                :class="openNav === 'inventory' ? 'rotate-90' : ''">
                                <!-- Heroicon name: mini/chevron-right -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 'inventory'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                            style="display: none;">
                            <li><a href="{{ route('admin.purchase-orders.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.purchase-orders.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Nhập
                                    kho</a>
                            </li>
                            <li><a href="{{ route('admin.stock-transfers.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.stock-transfers.index') || request()->routeIs('admin.stock-transfers.create') || request()->routeIs('admin.stock-transfers.edit') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Phiếu
                                    chuyển kho</a>
                            </li>
                            <li><a href="{{ route('admin.stock-transfers.dispatch.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.stock-transfers.dispatch.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Xuất
                                    kho</a>
                            </li>
                            {{-- Assuming you will create a route named 'admin.stock-transfers.receive.index' for this --}}
                            <li><a href="#" {{-- href="{{ route('admin.stock-transfers.receive.index') }}" --}}
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.stock-transfers.receive.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Nhận
                                    kho</a>
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
                        <ul x-show="openNav === 'stores'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                            style="display: none;">
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
                                <!-- Icon: Book Open -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M19,2H5A3,3,0,0,0,2,5V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V5A3,3,0,0,0,19,2Zm-7,2h5V19H12Zm-2,0h1V19H10ZM5,4H9V19H5a1,1,0,0,1-1-1V5A1,1,0,0,1,5,4Z" />
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
                        <ul x-show="openNav === 'catalog'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                            style="display: none;">
                            <li><a href="{{ route('admin.products.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.products.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Sản
                                    phẩm</a>
                            </li>
                            <li><a href="{{ route('admin.bundle-products.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.bundle-products.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Gói
                                    sản phẩm</a>
                            </li>
                            <li><a href="{{ route('admin.trade-in-items.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.trade-in-items.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Thu
                                    cũ & Mở hộp</a>
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
                            <li><a href="{{ route('admin.suppliers.index') }}"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.suppliers.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">Nhà
                                    cung cấp</a>
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
                                <!-- Icon: Megaphone -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M20.2,6.2a1,1,0,0,0-1.1.2L14.9,9.6A4.47,4.47,0,0,0,8,7.5,4.5,4.5,0,0,0,3.5,12,4.5,4.5,0,0,0,8,16.5a4.47,4.47,0,0,0,6.9-2.1l4.2,3.2a1,1,0,0,0,1.3-.2,1,1,0,0,0-.2-1.3L16,13.2a4.49,4.49,0,0,0-1-.7V10.1l5.4-4.1a1,1,0,0,0,.2-1.4A.87.87,0,0,0,20.2,6.2ZM8,14.5a2.5,2.5,0,1,1,2.5-2.5A2.5,2.5,0,0,1,8,14.5Z" />
                                    <path d="M8,13a1,1,0,0,0,1-1V8a1,1,0,0,0-2,0v4A1,1,0,0,0,8,13Z" />
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
                        <ul x-show="openNav === 'marketing'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                            style="display: none;">
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
                        <ul x-show="openNav === 'content'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                            style="display: none;">
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
                        <ul x-show="openNav === 'customers'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                            style="display: none;">
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
                                <!-- Icon: Briefcase -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M20,6H18V5a3,3,0,0,0-3-3H9A3,3,0,0,0,6,5V6H4A3,3,0,0,0,1,9v9a3,3,0,0,0,3,3H20a3,3,0,0,0,3-3V9A3,3,0,0,0,20,6ZM8,5A1,1,0,0,1,9,4h6a1,1,0,0,1,1,1V6H8ZM21,18a1,1,0,0,1-1,1H4a1,1,0,0,1-1-1V9A1,1,0,0,1,4,8H20a1,1,0,0,1,1,1Z" />
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
                        <ul x-show="openNav === 'employees'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                            style="display: none;">
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
        <div class="border-t border-gray-200 pt-3 mt-auto mb-4">
            <h3 class="px-4 text-xs font-semibold uppercase text-gray-400 tracking-wider">
                Cài đặt & Trang
            </h3>
            <ul class="space-y-1 mt-2 px-3">
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
                        </span>
                    </button>
                    <ul x-show="openNav === 'settings'" class="pl-8 pr-2 py-1 space-y-1 mt-1"
                        style="display: none;">
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
