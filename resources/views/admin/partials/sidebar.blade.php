@php
    // Xác định mục nav cha nào đang active dựa trên route hiện tại
    // Điều này giúp sidebar tự động mở đúng mục khi tải lại trang hoặc truy cập trực tiếp vào link con
    $activeParentNav = null;
    if (request()->routeIs('admin.admin.dashboard')) {
        $activeParentNav = 0;
    } elseif (request()->routeIs('admin.products.*') || request()->routeIs('admin.attributes.*') || request()->routeIs('admin.product-categories.*') /* Thêm route cho danh mục sản phẩm nếu có */) {
        $activeParentNav = 1; // Index của "Sản Phẩm"
    } elseif (request()->routeIs('admin.orders.*')) {
        $activeParentNav = 4; // Index của "Đơn hàng"
    } elseif (request()->routeIs('admin.customers.*')) {
        $activeParentNav = 3; // Index của "Khách hàng" (đang ẩn)
    }
    // Thêm các điều kiện khác cho các mục menu khác nếu cần
@endphp

<aside class="w-[300px] lg:w-[250px] xl:w-[300px] border-r border-gray-200 overflow-y-auto sidebar-scrollbar fixed left-0 top-0 h-full bg-white z-50 transition-transform duration-300 print:hidden"
       :class="sideMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div>
        <div class="py-4 px-8 border-b border-gray-200 h-[78px] flex items-center">
            <a href="{{ route('admin.admin.dashboard') }}"> {{-- Giả sử bạn có route tên 'admin.admin.dashboard' --}}
                <img class="w-[140px]" src="{{ asset('assets/admin/img/logo/logo.svg') }}" alt="Logo">
            </a>
        </div>

        <div class="px-3 py-5" x-data="{ openNav: {{ $activeParentNav ?? 'null' }} }">
            <ul class="space-y-1">
                {{-- 1. Trang chủ --}}
                <li>
                    @php $isDashboardActive = request()->routeIs('admin.admin.dashboard'); @endphp
                    <a href="{{ route('admin.admin.dashboard') }}"
                       class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out
                              {{ $isDashboardActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span class="mr-3 text-lg {{ $isDashboardActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                <path d="M7,0H4A4,4,0,0,0,0,4V7a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V4A4,4,0,0,0,7,0ZM9,7A2,2,0,0,1,7,9H4A2,2,0,0,1,2,7V4A2,2,0,0,1,4,2H7A2,2,0,0,1,9,4Z" />
                                <path d="M20,0H17a4,4,0,0,0-4,4V7a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V4A4,4,0,0,0,20,0Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V4a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z" />
                                <path d="M7,13H4a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V17A4,4,0,0,0,7,13Zm2,7a2,2,0,0,1-2,2H4a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2H7a2,2,0,0,1,2,2Z" />
                                <path d="M20,13H17a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V17A4,4,0,0,0,20,13Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z" />
                            </svg>
                        </span>
                        Trang chủ
                    </a>
                </li>

                {{-- 2. Sản Phẩm (Dropdown) --}}
                <li>
                    @php $isProductSectionActive = request()->routeIs('admin.products.*') || request()->routeIs('admin.attributes.*') || request()->routeIs('admin.product-categories.*') ; @endphp
                    <button @click="openNav !== 1 ? openNav = 1 : openNav = null"
                            :class="openNav === 1 || ({{ $isProductSectionActive ? 'true' : 'false' }} && openNav === null) ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                        <span class="mr-3 text-lg {{ ( $isProductSectionActive) ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                <path d="M23.621,6.836l-1.352-2.826c-.349-.73-.99-1.296-1.758-1.552L14.214,.359c-1.428-.476-3-.476-4.428,0L3.49,2.458c-.769,.256-1.41,.823-1.759,1.554L.445,6.719c-.477,.792-.567,1.742-.247,2.609,.309,.84,.964,1.49,1.802,1.796l-.005,6.314c-.002,2.158,1.372,4.066,3.418,4.748l4.365,1.455c.714,.238,1.464,.357,2.214,.357s1.5-.119,2.214-.357l4.369-1.457c2.043-.681,3.417-2.585,3.419-4.739l.005-6.32c.846-.297,1.508-.946,1.819-1.79,.317-.858,.228-1.799-.198-2.499ZM10.419,2.257c1.02-.34,2.143-.34,3.162,0l4.248,1.416-5.822,1.95-5.834-1.95,4.246-1.415ZM2.204,7.666l1.327-2.782c.048,.025,7.057,2.373,7.057,2.373l-1.621,3.258c-.239,.398-.735,.582-1.173,.434l-5.081-1.693c-.297-.099-.53-.325-.639-.619-.109-.294-.078-.616,.129-.97Zm3.841,12.623c-1.228-.409-2.052-1.554-2.051-2.848l.005-5.648,3.162,1.054c1.344,.448,2.792-.087,3.559-1.371l.278-.557-.005,10.981c-.197-.04-.391-.091-.581-.155l-4.366-1.455Zm11.897-.001l-4.37,1.457c-.19,.063-.384,.115-.581,.155l.005-10.995,.319,.64c.556,.928,1.532,1.459,2.561,1.459,.319,0,.643-.051,.96-.157l3.161-1.053-.005,5.651c0,1.292-.826,2.435-2.052,2.844Zm4-11.644c-.105,.285-.331,.504-.619,.6l-5.118,1.706c-.438,.147-.934-.035-1.136-.365l-1.655-3.323s7.006-2.351,7.054-2.377l1.393,2.901c.157,.261,.186,.574,.081,.859Z" />
                            </svg>
                        </span>
                        Sản Phẩm
                        <span class="ml-auto transition-transform duration-200 ease-in-out" :class="openNav === 1 ? 'rotate-90' : ''">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" /></svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 1" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            {{-- Thay 'admin.products.index' bằng route của bạn --}}
                            <a href="{{ route('admin.products.index') }}"
                               class="block w-full py-1.5 px-3 text-sm rounded-md
                                      {{ request()->routeIs('admin.products.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách sản phẩm
                            </a>
                        </li>
                        <li>
                             {{-- Thay 'admin.products.create' bằng route của bạn --}}
                            <a href="{{ route('admin.products.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                      {{ request()->routeIs('admin.products.create') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm mới sản phẩm
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.attributes.index') }}"
                               class="block w-full py-1.5 px-3 text-sm rounded-md
                                      {{ request()->routeIs('admin.attributes.*') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Quản lý thuộc tính
                            </a>
                        </li>
                        <li>
                             {{-- Thay 'admin.attributes.create' bằng route của bạn --}}
                            <a href="{{ route('admin.attributes.create') }}"
                                class="block w-full py-1.5 px-3 text-sm rounded-md
                                      {{ request()->routeIs('admin.attributes.create') && !request()->routeIs('admin.attributes.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Thêm thuộc tính mới
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- 3. Danh mục sản phẩm --}}
                <li>
                    @php $isCategoriesActive = request()->routeIs('admin.product-categories.*'); @endphp
                    <a href=" {{ route('admin.categories.index') }}" {{-- Giả sử route là 'admin.product-categories.index' --}}
                        class="group flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out
                                {{ $isCategoriesActive ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium' }}">
                        <span class="mr-3 text-lg {{ $isCategoriesActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                <path d="M22.713,4.077A2.993,2.993,0,0,0,20.41,3H4.242L4.2,2.649A3,3,0,0,0,1.222,0H1A1,1,0,0,0,1,2h.222a1,1,0,0,1,.993.883l1.376,11.7A5,5,0,0,0,8.557,19H19a1,1,0,0,0,0-2H8.557a3,3,0,0,1-2.82-2h11.92a5,5,0,0,0,4.921-4.113l.785-4.354A2.994,2.994,0,0,0,22.713,4.077ZM21.4,6.178l-.786,4.354A3,3,0,0,1,17.657,13H5.419L4.478,5H20.41A1,1,0,0,1,21.4,6.178Z" />
                                <circle cx="7" cy="22" r="2" />
                                <circle cx="17" cy="22" r="2" />
                            </svg>
                        </span>
                        Danh mục sản phẩm
                    </a>
                </li>
                
                {{-- Các mục menu khác tương tự --}}
                {{-- Ví dụ: Đơn hàng --}}
                 <li>
                    @php $isOrdersActive = request()->routeIs('admin.orders.*'); @endphp
                    <button @click="openNav !== 4 ? openNav = 4 : openNav = null"
                            :class="openNav === 4 || ({{ $isOrdersActive ? 'true' : 'false' }} && openNav === null) ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:text-indigo-600 hover:bg-indigo-50/50 font-medium'"
                            class="group w-full flex items-center px-4 py-2.5 text-base rounded-md transition-all duration-200 ease-in-out">
                         <span class="mr-3 text-lg {{ $isOrdersActive ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-500' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                <path d="m11.349,24H0V3C0,1.346,1.346,0,3,0h12c1.654,0,3,1.346,3,3v5.059c-.329-.036-.662-.059-1-.059s-.671.022-1,.059V3c0-.552-.448-1-1-1H3c-.552,0-1,.448-1,1v19h7.518c.506.756,1.125,1.429,1.831,2Zm0-14h-7.349v2h5.518c.506-.756,1.125-1.429,1.831-2Zm-7.349,7h4c0-.688.084-1.356.231-2h-4.231v2Zm20,0c0,3.859-3.141,7-7,7s-7-3.141-7-7,3.141-7,7-7,7,3.141,7,7Zm-2,0c0-2.757-2.243-5-5-5s-5,2.243-5,5,2.243,5,5,5,5-2.243,5-5ZM14,5H4v2h10v-2Zm5.589,9.692l-3.228,3.175-1.63-1.58-1.393,1.436,1.845,1.788c.314.315.733.489,1.179.489s.865-.174,1.173-.482l3.456-3.399-1.402-1.426Z" />
                            </svg>
                        </span>
                        Đơn hàng
                        <span class="ml-auto transition-transform duration-200 ease-in-out" :class="openNav === 4 ? 'rotate-90' : ''">
                             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" /></svg>
                        </span>
                    </button>
                    <ul x-show="openNav === 4" class="pl-8 pr-2 py-1 space-y-1 mt-1">
                        <li>
                            <a href="{{-- {{ route('admin.orders.index') }}" {{-- Giả sử route --}}
                               class="block w-full py-1.5 px-3 text-sm rounded-md
                                      {{ request()->routeIs('admin.orders.index') ? 'bg-indigo-100 text-indigo-700 font-medium' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                                Danh sách đơn hàng
                            </a>
                        </li>
                         {{-- Thêm các link con khác cho Đơn hàng nếu có --}}
                    </ul>
                </li>

            </ul>
                    <div class="border-t border-gray pt-3 mt-3" x-data="{moreNav:null}">
                        <a href="site-settings.html"
                            class="group rounded-md relative text-black text-lg font-medium inline-flex items-center w-full transition-colors ease-in-out duration-300 px-5 py-[9px] mb-3 hover:bg-gray sidebar-link-active">
                            <span class="inline-block translate-y-[1px] mr-[10px] text-xl">
                                <svg class="-translate-y-[3px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    width="16" height="16">
                                    <path fill="currentColor"
                                        d="M24,10a.988.988,0,0,0-.024-.217l-1.3-5.868A4.968,4.968,0,0,0,17.792,0H6.208a4.968,4.968,0,0,0-4.88,3.915L.024,9.783A.988.988,0,0,0,0,10v1a3.984,3.984,0,0,0,1,2.643V19a5.006,5.006,0,0,0,5,5H18a5.006,5.006,0,0,0,5-5V13.643A3.984,3.984,0,0,0,24,11ZM2,10.109l1.28-5.76A2.982,2.982,0,0,1,6.208,2H7V5A1,1,0,0,0,9,5V2h6V5a1,1,0,0,0,2,0V2h.792A2.982,2.982,0,0,1,20.72,4.349L22,10.109V11a2,2,0,0,1-2,2H19a2,2,0,0,1-2-2,1,1,0,0,0-2,0,2,2,0,0,1-2,2H11a2,2,0,0,1-2-2,1,1,0,0,0-2,0,2,2,0,0,1-2,2H4a2,2,0,0,1-2-2ZM18,22H6a3,3,0,0,1-3-3V14.873A3.978,3.978,0,0,0,4,15H5a3.99,3.99,0,0,0,3-1.357A3.99,3.99,0,0,0,11,15h2a3.99,3.99,0,0,0,3-1.357A3.99,3.99,0,0,0,19,15h1a3.978,3.978,0,0,0,1-.127V19A3,3,0,0,1,18,22Z" />
                                </svg>
                            </span>
                            Cài đặt trang web
                        </a>

                        <a @click="moreNav !== 1 ? moreNav = 1 : moreNav = null"
                            :class="moreNav == 1 ? 'bg-themeLight hover:bg-themeLight text-theme' : ''"
                            href="javascript:void(0);"
                            class="group rounded-md relative text-black text-lg font-medium inline-flex items-center w-full transition-colors ease-in-out duration-300 px-5 py-[9px] mb-3 hover:bg-gray sidebar-link-active">
                            <span class="inline-block translate-y-[1px] mr-[10px] text-xl">
                                <svg class="-translate-y-[5px]" height="16" viewBox="-78 -18 560 560.00187" width="16"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill="currentColor"
                                        d="m260.722656 3.878906-.128906-.125c-2.355469-2.429687-5.605469-3.7890622-8.996094-3.74999975-.417968-.01953125-.839844.01953125-1.25.12890575h-191.953125c-34.503906.007813-62.46875 27.976563-62.484375 62.484376v399.898437c.015625 34.503906 27.980469 62.472656 62.484375 62.484375h278.308594c34.503906-.011719 62.472656-27.980469 62.484375-62.484375v-315.175781c-.023438-3.308594-1.320312-6.484375-3.625-8.867188zm3.371094 38.742188 92.726562 92.476562h-55.234374c-20.679688-.0625-37.433594-16.8125-37.492188-37.488281zm72.734375 457.261718h-278.433594c-20.679687-.058593-37.425781-16.8125-37.492187-37.492187v-399.902344c.066406-20.675781 16.8125-37.429687 37.492187-37.488281h180.707031v72.609375c.011719 34.5 27.980469 62.46875 62.484376 62.484375h72.609374v302.296875c.039063 20.671875-16.695312 37.464844-37.367187 37.492187zm0 0" />
                                    <path fill="currentColor"
                                        d="m76.015625 241.195312h135.09375c6.898437 0 12.5-5.59375 12.5-12.496093 0-6.902344-5.601563-12.496094-12.5-12.496094h-135.09375c-6.902344 0-12.496094 5.59375-12.496094 12.496094 0 6.902343 5.59375 12.496093 12.496094 12.496093zm0 0" />
                                    <path fill="currentColor"
                                        d="m319.207031 310.679688h-243.191406c-6.902344 0-12.496094 5.59375-12.496094 12.496093 0 6.898438 5.59375 12.496094 12.496094 12.496094h243.191406c6.898438 0 12.5-5.597656 12.5-12.496094 0-6.902343-5.601562-12.496093-12.5-12.496093zm0 0" />
                                    <path fill="currentColor"
                                        d="m319.207031 405.28125h-243.191406c-6.902344 0-12.496094 5.59375-12.496094 12.496094s5.59375 12.496094 12.496094 12.496094h243.191406c6.898438 0 12.5-5.59375 12.5-12.496094s-5.601562-12.496094-12.5-12.496094zm0 0" />
                                </svg>
                            </span>
                            Thêm trang

                            <span
                                class="absolute right-4 top-[52%] transition-transform duration-300 origin-center w-4 h-4"
                                :class="moreNav == 1 ? 'translate-y-[-10px] rotate-90' : 'translate-y-[-10px]'">
                                <svg class="-translate-y-[5px]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    width="16" height="16">
                                    <path fill="currentColor"
                                        d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                </svg>
                            </span>
                        </a>
                        <ul x-show="moreNav == 1" class="pl-[42px] pr-[20px] pb-3">
                            <li>
                                <a href="blank.html"
                                    class="block font-normal w-full text-[#6D6F71] hover:text-theme nav-dot">Trang trống</a>
                            </li>
                            <li>
                                <a href="register.html"
                                    class="block font-normal w-full text-[#6D6F71] hover:text-theme nav-dot">Đăng kí</a>
                            </li>
                            <li>
                                <a href="login.html"
                                    class="block font-normal w-full text-[#6D6F71] hover:text-theme nav-dot">Đăng nhập</a>
                            </li>
                            <li>
                                <a href="forgot.html"
                                    class="block font-normal w-full text-[#6D6F71] hover:text-theme nav-dot">Quên mật khẩu</a>
                            </li>
                            <li>
                                <a href="otp.html"
                                    class="block font-normal w-full text-[#6D6F71] hover:text-theme nav-dot">Xác minh OTP</a>
                            </li>
                            <li>
                                <a href="verify-mail.html"
                                    class="block font-normal w-full text-[#6D6F71] hover:text-theme nav-dot">Xác minh qua Mail</a>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>

        </aside>
