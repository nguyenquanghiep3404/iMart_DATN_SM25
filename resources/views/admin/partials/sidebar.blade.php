@php
    $activeParentNav = null;
    $currentRouteName = request()->route()->getName();
    if (str_starts_with($currentRouteName, 'admin.dashboard')) {
        $activeParentNav = 0;
    } elseif (str_starts_with($currentRouteName, 'admin.products.')) {
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
    } elseif (str_starts_with($currentRouteName, 'admin.media.')) {
        $activeParentNav = 11; // Index của "Quản lý media"
    } elseif (str_starts_with($currentRouteName, 'admin.attributes.')) {
        $activeParentNav = 12; // Index của "Thuộc tính"
    } elseif (str_starts_with($currentRouteName, 'admin.specifications.')) {
        $activeParentNav = 13; // Index của "Thuộc tính"
    } elseif (str_starts_with($currentRouteName, 'admin.content-staffs.')) {
        $activeParentNav = 14; // Index của "Quản lý nhân viên content"
    }
    // Thêm các điều kiện khác nếu cần
@endphp

<aside
    class="w-[300px] lg:w-[250px] xl:w-[300px] border-r border-slate-200 overflow-y-auto sidebar-scrollbar fixed left-0 top-0 h-full bg-white z-40 transition-transform duration-300 print:hidden"
    :class="sideMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div>
        {{-- PHẦN HEADER CỦA SIDEBAR --}}
        <div class="flex h-[65px] items-center justify-center border-b border-slate-200 px-3 lg:px-6 py-4">

<a href="{{ route('admin.dashboard') }}">

{{-- Logo đầy đủ - Hiển thị trên màn hình từ lg trở lên --}}

<img class="hidden lg:block" src="{{ asset('assets/users/logo/logo-full.svg') }}" alt="Full Logo"

style="width: 150px; ">

{{-- Logo icon - Hiển thị trên màn hình nhỏ hơn lg --}}

<img class="block lg:hidden" src="{{ asset('assets/users/logo/logo-icon.svg') }}" alt="Icon Logo"

style="width: 40px;">

</a>

</div>
        {{-- KẾT THÚC HEADER --}}

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
                    @php $isProductSectionActive = request()->routeIs('admin.products.*'); @endphp
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
                            <a href="{{ route('admin.products.trash') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.products.trash') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thùng rác
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- 3. Thuộc tính sản phẩm --}}
                <li>
                    @php $isAttributesActive = request()->routeIs('admin.attributes.*'); @endphp
                    <button @click="openNav !== 12 ? openNav = 12 : openNav = null"
                        :class="openNav === 12 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isAttributesActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M21,11H18.59A4,4,0,0,0,18,9.41V8a1,1,0,0,0-2,0v1.41A4,4,0,0,0,15.41,11H13a1,1,0,0,0,0,2h2.41A4,4,0,0,0,16,14.59V16a1,1,0,0,0,2,0V14.59A4,4,0,0,0,18.59,13H21a1,1,0,0,0,0-2ZM17,12a1,1,0,1,1,1-1A1,1,0,0,1,17,12Zm-6-1H8.59A4,4,0,0,0,8,9.41V8a1,1,0,0,0-2,0v1.41A4,4,0,0,0,5.41,11H3a1,1,0,0,0,0,2H5.41A4,4,0,0,0,6,14.59V16a1,1,0,0,0,2,0V14.59A4,4,0,0,0,8.59,13H11a1,1,0,0,0,0-2ZM7,12a1,1,0,1,1,1-1A1,1,0,0,1,7,12Z" />
                            </svg>
                        </span>
                        Thuộc tính sản phẩm
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 12 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                                fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 12" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{ route('admin.attributes.index') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.attributes.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách thuộc tính
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.attributes.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.attributes.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm thuộc tính mới
                            </a>
                        </li>
                        <li>
                            <a href="###"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('###') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thùng rác
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 4. Danh mục sản phẩm --}}
                <li>
                    @php $isCategoriesActive = request()->routeIs('admin.categories.*'); @endphp
                    <button @click="openNav !== 2 ? openNav = 2 : openNav = null"
                        :class="openNav === 2 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isCategoriesActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                                fill="currentColor">
                                <path
                                    d="M19,4H9A1,1,0,0,0,8,5V19a1,1,0,0,0,1,1H19a1,1,0,0,0,1-1V5A1,1,0,0,0,19,4ZM18,18H10V6H18ZM6,18H4V6H6ZM14,2H6A3,3,0,0,0,3,5V19a3,3,0,0,0,3,3h8a3,3,0,0,0,3-3V14.45A2.83,2.83,0,0,1,14,14a3,3,0,0,1-3-3,3,3,0,0,1,3-3,2.83,2.83,0,0,1,.45,0V5A3,3,0,0,0,14,2Z" />
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
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.categories.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách danh mục
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.categories.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm mới danh mục
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories.trash') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.categories.trash') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thùng rác
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 5. Đơn hàng --}}
                <li>
                    @php $isOrdersActive = request()->routeIs('admin.orders.index'); @endphp
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
                            <a href="{{ route('admin.orders.index') }}"
                                class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách đơn hàng
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 6. Quản lý người dùng --}}
                <li>
                    @php $isUsersActive = request()->routeIs('admin.users.*'); @endphp
                    <button @click="openNav !== 4 ? openNav = 4 : openNav = null"
                        :class="openNav === 4 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isUsersActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M12,12A6,6,0,1,0,6,6,6.006,6.006,0,0,0,12,12ZM12,2A4,4,0,1,1,8,6,4,4,0,0,1,12,2Z" />
                                <path
                                    d="M12,14a9.01,9.01,0,0,0-9,9,1,1,0,0,0,1,1H20a1,1,0,0,0,1-1A9.01,9.01,0,0,0,12,14ZM4.136,22A7.006,7.006,0,0,1,11,16H13a7.006,7.006,0,0,1,6.864,6Z" />
                            </svg>
                        </span>
                        Quản lý người dùng
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 4 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                height="16" fill="currentColor">
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


                            <!-- <a href=""
                                class="block w-full py-1.5
                            <a href="{{ route('admin.roles.index') }}" {{-- Giả sử route --}}
                               class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.roles.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Vai trò của người dùng
                            </a> -->
                        </li>
                            </li>
                        <li>

                            <a href="{{ route('admin.users.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.users.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm mới người dùng
                            </a>
                        </li>
                        <li>

                            <a href="{{ route('admin.users.trash') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.users.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thùng rác
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 7. Quản lý đánh giá --}}
                <li>
                    @php $isReviewsActive = request()->routeIs('admin.reviews.*'); @endphp
                    <a href="{{ route('admin.reviews.index') }}"
                        class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out {{ $isReviewsActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isReviewsActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M20.61,4.12a2,2,0,0,0-1.79-1H5.18a2,2,0,0,0-1.79,1L2,8.47V18a2,2,0,0,0,2,2H20a2,2,0,0,0,2-2V8.47ZM5.18,5H18.82l1.2,3H4ZM20,18H4V13H20Zm-8-3.5a.5.5,0,0,1-.5.5h-3a.5.5,0,0,1,0-1h3A.5.5,0,0,1,12,14.5Zm3.9-1.45L14.62,15,13.5,13.1a.5.5,0,0,1,.84-.52l.66.8.92-1.22a.5.5,0,1,1,.88.64Z" />
                            </svg>
                        </span>
                        Quản lý đánh giá
                    </a>
                </li>


                {{-- 8. Quản lý mã giảm giá --}}
                <li>
                    @php $isCouponsActive = request()->routeIs('admin.coupons.*'); @endphp
                    <button @click="openNav !== 6 ? openNav = 6 : openNav = null"
                        :class="openNav === 6 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isCouponsActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M19.41,10H14a1,1,0,0,0,0,2h5.41a1,1,0,0,1,0,2H14a3,3,0,0,1,0-6h5.41a3,3,0,0,0,0,6H14a5,5,0,0,0,0,10h6V20H10a1,1,0,0,0,0,2h10a1,1,0,0,0,1-1V11A5,5,0,0,0,15,6H10V8h5.41A1,1,0,0,1,14,10H10V4h6a3,3,0,0,1,3,3V9A1,1,0,0,1,19.41,10ZM4,2A2,2,0,0,0,2,4V20a2,2,0,0,0,2,2H8V2Z" />
                            </svg>
                        </span>
                        Quản lý mã giảm giá
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 6 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                height="16" fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 6" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{ route('admin.coupons.index') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                    {{ request()->routeIs('admin.coupons.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách mã giảm giá
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.coupons.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                    {{ request()->routeIs('admin.coupons.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm mã mới
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.coupons.trash') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                    {{ request()->routeIs('admin.coupons.trash') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thùng rác
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 9. Quản lý phân quyền --}}
                <li>
                    @php $isRolesActive = request()->routeIs('admin.roles.*'); @endphp
                    <a href="{{ route('admin.roles.index') }}"
                        class="group flex items-center px-4 py-2.5
                        text-base rounded-md transition-all duration-200 ease-in-out
                        {{ $isRolesActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isRolesActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M20.78,6.82,14.22,2.22A6.87,6.87,0,0,0,10,2,6.87,6.87,0,0,0,3.18,7.82,6.87,6.87,0,0,0,2,12a6.87,6.87,0,0,0,8,6.82V22a1,1,0,0,0,2,0V18.82A6.87,6.87,0,0,0,20.78,13,5.1,5.1,0,0,0,22,10.6,4.42,4.42,0,0,0,20.78,6.82ZM11,17.82A5.87,5.87,0,0,1,4,12a5.87,5.87,0,0,1,2.18-4.82A5.87,5.87,0,0,1,10,4a5.87,5.87,0,0,1,4.82,2.18,5.87,5.87,0,0,1,2,4.42,3.1,3.1,0,0,1-1.22.4A4.87,4.87,0,0,0,11,17.82Z" />
                            </svg>
                        </span>
                        Quản lý phân quyền
                    </a>
                </li>

                {{-- 10. Quản lý banner --}}
                <li>
                    @php $isBannersActive = request()->routeIs('admin.banners.*'); @endphp
                    <a href="{{ route('admin.banners.index') }}"
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

                {{-- 11. Quản lý danh mục bài viết --}}
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

                {{-- 12. Quản lý nhân viên giao hàng --}}
                <li>
                    @php $isShippersActive = request()->routeIs('admin.shippers.*'); @endphp
                    <a href="{{ route('admin.shippers.index') }}"
                        class="group flex items-center px-4 py-2.5
                        text-base rounded-md transition-all duration-200 ease-in-out
                        {{ $isShippersActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isShippersActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M19.7,11.83,18.23,5.9a3,3,0,0,0-2.81-2H8.58a3,3,0,0,0-2.81,2L4.3,11.83A2.32,2.32,0,0,0,4,12.5a1.5,1.5,0,0,0,1.5,1.5h.5a1,1,0,0,0,1-1,1.5,1.5,0,0,1,3,0,1,1,0,0,0,1,1h4a1,1,0,0,0,1-1,1.5,1.5,0,0,1,3,0,1,1,0,0,0,1,1h.5a1.5,1.5,0,0,0,1.5-1.5A2.32,2.32,0,0,0,19.7,11.83ZM9.5,11A1.5,1.5,0,1,1,11,9.5,1.5,1.5,0,0,1,9.5,11Zm8,0A1.5,1.5,0,1,1,19,9.5,1.5,1.5,0,0,1,17.5,11Z" />
                            </svg>
                        </span>
                        Quản lý nhân viên giao hàng
                    </a>
                </li>
                {{-- 14. Quản lý nhân viên content --}}
                <li>
                    @php $isContentStaffActive = request()->routeIs('admin.content-staffs.*') || request()->routeIs('admin.content_staffs.*'); @endphp
                    <a href="{{ route('admin.content-staffs.index') }}"
                        class="group flex items-center px-4 py-2.5
        text-base rounded-md transition-all duration-200 ease-in-out
        {{ $isContentStaffActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span
                            class="mr-3 text-lg {{ $isContentStaffActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M12 2a5 5 0 1 1-5 5 5 5 0 0 1 5-5Zm0 14c-4.42 0-8 1.79-8 4v2H20V20c0-2.21-3.58-4-8-4Z" />
                            </svg>
                        </span>
                        Quản lý nhân viên content
                    </a>
                </li>

                {{-- 13. Quản lý thư viện ảnh --}}
                <li>
                    @php $isMediaActive = request()->routeIs('admin.media.*'); @endphp
                    <button @click="openNav !== 11 ? openNav = 11 : openNav = null"
                        :class="openNav === 11 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isMediaActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z" />
                            </svg>
                        </span>
                        Thư viện ảnh
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 11 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                height="16" fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 11" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{ route('admin.media.index') }}"
                                class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.media.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách thư viện ảnh
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.media.trash') }}"
                                class="block w-full py-1.5
                                px-3 text-sm rounded-md
                                {{ request()->routeIs('admin.media.trash') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thùng rác
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- 14. thông số sản phẩm --}}
                <li>
                    @php $isSpecificationsSectionActive = request()->routeIs('admin.specifications.*') || request()->routeIs('admin.specification-groups.*'); @endphp
                    <button @click="openNav !== 13 ? openNav = 13 : openNav = null"
                        :class="openNav === 13 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                            'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                        class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span
                            class="mr-3 text-lg {{ $isSpecificationsSectionActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            {{-- Icon mới cho thông số --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                height="18" fill="currentColor">
                                <path
                                    d="M21.92,7.62A1,1,0,0,0,21,7H3a1,1,0,0,0,0,2H21a1,1,0,0,0,.92-1.38ZM21.92,15.62A1,1,0,0,0,21,15H3a1,1,0,0,0,0,2H21a1,1,0,0,0,.92-1.38Z" />
                                <path d="M7,11H3a1,1,0,0,0,0,2H7a1,1,0,0,0,0-2Z" />
                                <path d="M7,19H3a1,1,0,0,0,0,2H7a1,1,0,0,0,0-2Z" />
                                <path d="M21,3H3A1,1,0,0,0,3,5H21a1,1,0,0,0,0-2Z" />
                            </svg>
                        </span>
                        Thông số sản phẩm
                        <span class="ml-auto transition-transform duration-200 ease-in-out"
                            :class="openNav === 13 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                height="16" fill="currentColor">
                                <path
                                    d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                            </svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 13" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{ route('admin.specification-groups.index') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.specification-groups.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Nhóm thông số
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.specifications.index') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md {{ request()->routeIs('admin.specifications.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thông số kỹ thuật
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            {{-- Phần Cài đặt & Trang phụ (Giữ nguyên) --}}
            <div class="border-t border-gray-200 pt-3 mt-3">
                <h3 class="px-4 text-xs font-semibold uppercase text-gray-400 tracking-wider">
                    Cài đặt & Trang
                </h3>
                <ul class="space-y-1 mt-2">
                    {{-- 1. Cài đặt trang web --}}
                    <li>
                        @php $isSettingsActive = request()->routeIs('admin.settings.*'); @endphp
                        <button @click="openNav !== 100 ? openNav = 100 : openNav = null"
                            :class="openNav === 100 ? 'bg-indigo-50 text-indigo-600 font-semibold' :
                                'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all">
                            <span
                                class="mr-3 text-lg {{ $isSettingsActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M19.43,12.94a1,1,0,0,0-1.15-.36l-1.09.36a8.31,8.31,0,0,0-1.25-1.25l.36-1.09a1,1,0,0,0-.36-1.15L14,7.57a1,1,0,0,0-1.21,0L11.06,9.43a1,1,0,0,0-.36,1.15l.36,1.09a8.31,8.31,0,0,0-1.25,1.25l-1.09-.36a1,1,0,0,0-1.15.36L5.57,14a1,1,0,0,0,0,1.21l1.86,1.73a1,1,0,0,0,1.15.36l1.09-.36a8.31,8.31,0,0,0,1.25,1.25l-.36,1.09a1,1,0,0,0,.36,1.15L12,22.43a1,1,0,0,0,1.21,0l1.86-1.86a1,1,0,0,0,.36-1.15l-.36-1.09a8.31,8.31,0,0,0,1.25-1.25l1.09.36a1,1,0,0,0,1.15-.36L20.43,14a1,1,0,0,0,0-1.21ZM12,15.5A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Zm8-11.41-2.12,2.12a1,1,0,0,1-1.42,0,1,1,0,0,1,0-1.42L18.59,2.59A1,1,0,0,1,20,2.59,1,1,0,0,1,20,4.09ZM5.41,18.59,3.29,16.47a1,1,0,0,1,0-1.42,1,1,0,0,1,1.42,0L6.83,17.17a1,1,0,0,1,0,1.42A1,1,0,0,1,5.41,18.59Z" />
                                </svg>
                            </span>
                            Cài đặt trang web
                            <span class="ml-auto transition-transform" :class="openNav === 100 ? 'rotate-90' : ''">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                    height="16" fill="currentColor">
                                    <path
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </button>
                        <ul x-show="openNav === 100" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                            <li><a href="#"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50">Cài
                                    đặt chung</a></li>
                            <li><a href="#"
                                    class="block w-full py-1.5 px-3 text-sm rounded-md text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50">Thanh
                                    toán & Vận chuyển</a></li>
                        </ul>
                    </li>
                    {{-- 2. Quản lý trang --}}
                    <li>
                        <a href="#"
                            class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium">
                            <span class="mr-3 text-lg text-gray-500 group-hover:text-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                    height="18" fill="currentColor">
                                    <path
                                        d="M20,3H4A3,3,0,0,0,1,6V18a3,3,0,0,0,3,3H20a3,3,0,0,0,3-3V6A3,3,0,0,0,20,3Zm1,15a1,1,0,0,1-1,1H4a1,1,0,0,1-1-1V6A1,1,0,0,1,4,5H20a1,1,0,0,1,1,1ZM17,8H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z" />
                                </svg>
                            </span>
                            Quản lý trang
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</aside>
