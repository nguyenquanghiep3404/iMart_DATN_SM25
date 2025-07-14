{{--
    Tệp header này đã được hợp nhất.
    Nó bao gồm các chức năng từ header cũ của bạn (menu di động, tìm kiếm, hiệu ứng cuộn)
    và đã được tích hợp thêm hệ thống thông báo từ header mới.
    JavaScript cũng đã được kết hợp để xử lý tất cả các tương tác,
    bao gồm cả việc chuyển đổi giữa menu chính và chế độ xem thông báo trong dropdown của người dùng.
--}}

<style>
    /* --- CSS cho hiệu ứng chuyển động --- */
    #mobile-menu {
        transition: max-height 0.3s ease-in-out;
        max-height: 0;
        overflow: hidden;
    }

    #mobile-menu.open {
        max-height: 100vh;
    }

    /* Dropdown menu styling */
    #user-dropdown-menu {
        transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out, width 0.3s ease-in-out;
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none;
        /* Ngăn tương tác khi ẩn */
    }

    /* Hiển thị dropdown khi có class 'open' */
    #user-dropdown-menu.open {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
        /* Cho phép tương tác khi hiện */
    }

    /* Style cho avatar người dùng khi đã đăng nhập */
    #user-avatar {
        background-color: rgba(255, 255, 255, 0.1);
        /* Màu nền xám nhạt ban đầu */
        transition: background-color 0.3s ease-in-out;
    }

    /* Khi header được cuộn, nền của avatar sẽ nhạt hơn */
    #page-header.scrolled #user-avatar {
        background-color: rgba(255, 255, 255, 0.05);
    }

    /* --- Styles for Animated Search Bar --- */
    .search-wrapper.is-focused {
        background-image: linear-gradient(to right, #9aadf9, #b0d4d2, #f2adda, #b0d4d2, #9aadf9);
        background-size: 300% auto;
        animation: animated-gradient 8s linear infinite;
    }

    .search-wrapper .relative.is-focused-inner {
        background-color: white;
    }

    .search-wrapper .relative.is-focused-inner .search-input {
        color: #1f2937;
    }

    .search-wrapper .relative.is-focused-inner .search-input::placeholder {
        color: #6b7280;
    }

    @keyframes animated-gradient {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .search-submit-btn.is-focused {
        background-image: linear-gradient(to right, #9aadf9, #b0d4d2, #f2adda, #b0d4d2, #9aadf9);
        background-size: 300% auto;
        animation: animated-gradient 8s linear infinite;
    }

    /* --- Style cho header khi cuộn --- */
    #page-header.scrolled {
        background-color: rgba(17, 24, 39, 0.85);
        -webkit-backdrop-filter: blur(16px);
        backdrop-filter: blur(16px);
        border-bottom-color: rgba(55, 65, 81, 0.6);
    }
</style>
<header id="page-header"
    class="bg-gray-900 text-white z-50 border-b border-transparent transition-all duration-300 @unless (Route::is('users.products.show')) sticky top-0 @endunless">
    <div class="container mx-auto px-4 h-20">
        <div id="header-main" class="flex items-center justify-between h-full">
            <div class="flex justify-start">
                <a href="/" class="flex-shrink-0">
                    <img class="h-12 sm:h-14 w-auto" src="{{ asset('assets/users/logo/logo-full.svg') }}" alt="Logo">
                </a>
            </div>

            <div class="flex-1 flex justify-center">
                <nav class="hidden lg:flex items-center space-x-8">
                    @foreach ($menuCategories ?? [] as $cat)
                        <a href="{{ route('products.byCategory', ['id' => $cat->id, 'slug' => $cat->slug]) }}"
                            class="text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200 whitespace-nowrap">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </nav>
            </div>


            <div class="flex justify-end">
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <button class="js-search-trigger p-2 rounded-full text-gray-300 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-5 h-5">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <a href="/wishlist"
                        class="hidden md:block p-2 rounded-full text-gray-300 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-5 h-5">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                            </path>
                        </svg>
                    </a>

                    <!-- User Menu Container -->
                    <div class="relative">
                        <button id="user-menu-trigger"
                            class="w-7 h-7 rounded-full flex items-center justify-center text-gray-300 hover:bg-white/10 transition-colors overflow-hidden">

                            @guest
                                {{-- Icon user mặc định cho khách chưa đăng nhập --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            @else
                                {{-- Avatar người dùng hoặc chữ cái đầu --}}
                                @if (Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="Avatar"
                                        class="w-full h-full object-cover rounded-full">
                                @else
                                    <span class="text-sm font-semibold text-white uppercase">
                                        {{ strtoupper(Auth::user()->name[0]) }}
                                    </span>
                                @endif
                            @endguest

                        </button>


                        <!-- Dropdown Container -->
                        <div id="user-dropdown-menu"
                            class="absolute top-full right-0 mt-2 w-56 bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-20 overflow-hidden">

                            <!-- Main Menu View -->
                            <div id="main-menu-view">
                                @guest
                                    <div class="py-2">
                                        <a href="{{ route('login') }}"
                                            class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3">
                                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                                <polyline points="10 17 15 12 10 7"></polyline>
                                                <line x1="15" y1="12" x2="3" y2="12"></line>
                                            </svg>
                                            <span>Đăng nhập</span>
                                        </a>
                                        <a href="{{ route('register') }}"
                                            class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <line x1="19" x2="19" y1="8" y2="14"></line>
                                                <line x1="22" x2="16" y1="11" y2="11">
                                                </line>
                                            </svg>
                                            <span>Đăng ký</span>
                                        </a>
                                    </div>
                                @else
                                    @php $user = Auth::user(); @endphp
                                    <div class="px-4 py-3 border-b border-gray-700">
                                        <p class="text-sm text-white font-semibold" role="none">
                                            {{ $user->name }}
                                        </p>
                                        <p class="text-xs text-gray-400 truncate" role="none">{{ $user->email }}
                                        </p>
                                    </div>
                                    <div class="py-2">
                                        {{-- Nút chuyển đến tab thông báo --}}
                                        <a href="#" id="notification-trigger"
                                            class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                            </svg>
                                            <span>Thông báo</span>
                                            {{-- Giả sử bạn truyền biến $unreadNotificationsCount từ controller --}}
                                            @if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                                                <span
                                                    class="ml-auto text-xs bg-red-500 text-white rounded-full h-5 w-5 flex items-center justify-center">{{ $unreadNotificationsCount }}</span>
                                            @endif
                                        </a>

                                        {{-- Tài khoản --}}
                                        <a href="{{ route('profile.edit') }}"
                                            class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="w-5 h-5 mr-3">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            <span>Tài khoản của tôi</span>
                                        </a>

                                        {{-- Trang quản trị --}}
                                        @if ($user->roles->contains('id', 1) || $user->roles->contains('id', 4) || $user->roles->contains('id', 5))
                                            <a href="{{ route('admin.dashboard') }}"
                                                class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="w-5 h-5 mr-3">
                                                    <rect width="18" height="18" x="3" y="3" rx="2" />
                                                    <path d="M7 12h10M7 7h2M7 17h5" />
                                                </svg>
                                                <span>Trang Quản Trị</span>
                                            </a>
                                        @endif

                                        <div class="border-t border-gray-700 my-2"></div>

                                        {{-- Logout --}}
                                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit"
                                                class="w-full flex items-center px-4 py-2 text-sm text-red-400 hover:bg-gray-700 hover:text-red-300 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="w-5 h-5 mr-3">
                                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                    <polyline points="16 17 21 12 16 7"></polyline>
                                                    <line x1="21" y1="12" x2="9" y2="12">
                                                    </line>
                                                </svg>
                                                <span>Đăng xuất</span>
                                            </button>
                                        </form>
                                    </div>
                                @endauth
                            </div>

                            <!-- Notification Detail View (initially hidden) -->
                            <div id="notification-detail-view" class="hidden">
                                <div class="p-3 border-b border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <button id="back-to-menu-btn"
                                            class="p-1 -ml-1 text-gray-400 hover:text-white rounded-full transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="w-5 h-5">
                                                <line x1="19" y1="12" x2="5" y2="12">
                                                </line>
                                                <polyline points="12 19 5 12 12 5"></polyline>
                                            </svg>
                                        </button>
                                        <h3 class="text-base font-semibold text-white">Thông báo</h3>
                                        <a href="#"
                                            class="text-xs text-blue-400 hover:text-blue-300 transition-colors">Xem tất
                                            cả</a>
                                    </div>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    {{-- Giả sử bạn truyền biến $recentNotifications từ controller --}}
                                    @forelse($recentNotifications ?? [] as $notification)
                                        <a href="#"
                                            class="flex items-start p-3 hover:bg-gray-700/50 transition-colors">
                                            <div
                                                class="flex-shrink-0 w-10 h-10 bg-{{ $notification['color'] ?? 'gray' }}-500/20 text-{{ $notification['color'] ?? 'gray' }}-400 rounded-full flex items-center justify-center">
                                                @if (isset($notification['icon']) && $notification['icon'] === 'check')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                        height="24" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" class="w-5 h-5">
                                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                                    </svg>
                                                @elseif (isset($notification['icon']) && $notification['icon'] === 'warning')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path
                                                            d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z">
                                                        </path>
                                                        <line x1="12" x2="12" y1="9"
                                                            y2="13"></line>
                                                        <line x1="12" x2="12.01" y1="17"
                                                            y2="17"></line>
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <line x1="12" x2="12" y1="2"
                                                            y2="22" />
                                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-200">
                                                    {{ $notification['title'] ?? 'Không có tiêu đề' }}</p>
                                                <p class="text-sm font-medium text-gray-200">
                                                    {{ $notification['message'] ?? 'Không có tiêu đề' }}</p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    {{ $notification['time'] ?? '' }}</p>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center text-gray-400 py-8 px-4">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="mx-auto h-12 w-12 text-gray-500" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                            </svg>
                                            <p class="mt-4 text-sm font-semibold">Không có thông báo mới</p>
                                            <p class="mt-1 text-xs text-gray-500">Chúng tôi sẽ cho bạn biết khi có tin
                                                tức.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="relative inline-block cursor-pointer" data-bs-toggle="offcanvas"
                        data-bs-target="#shoppingCart" aria-controls="shoppingCart" aria-label="Shopping cart">

                        <!-- SVG giỏ hàng -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-6 h-6 text-gray-300 hover:text-white transition-colors">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>

                        <!-- Badge số lượng -->
                        <span id="cart-badge"
                            class="absolute top-0 right-0 flex justify-center items-center h-4 w-4 bg-red-500 text-white text-[10px] font-semibold rounded-full transform translate-x-1/3 -translate-y-1/3"
                            style="{{ $cartItemCount > 0 ? '' : 'display: none;' }}">
                            {{ $cartItemCount }}
                        </span>
                    </div>
                    <button id="mobile-menu-btn"
                        class="lg:hidden p-2 rounded-full text-gray-300 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-5 h-5">
                            <line x1="4" x2="20" y1="12" y2="12" />
                            <line x1="4" x2="20" y1="6" y2="6" />
                            <line x1="4" x2="20" y1="18" y2="18" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="header-search" class="hidden items-center justify-center h-full">
            <div class="w-full max-w-2xl flex items-center space-x-4">
                <div class="search-wrapper w-full rounded-full p-1 bg-white/10">
                    <form action="{{ route('users.products.search') }}" method="GET">
                        <div class="relative bg-transparent rounded-full">
                            <input type="search" name="q" placeholder="Tìm kiếm sản phẩm..."
                                class="search-input w-full bg-transparent text-gray-300 rounded-full py-2.5 pl-6 pr-16 text-base placeholder-gray-400 focus:outline-none" />

                            <button type="submit"
                                class="search-submit-btn absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-all duration-300 transform hover:scale-110 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
                <button id="search-close-btn" class="text-gray-400 hover:text-white transition-colors flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-7 h-7">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="lg:hidden bg-gray-900/95 backdrop-blur-lg border-t border-gray-700/60">
        <div class="px-5 py-4 space-y-4">
            <div class="space-y-1">
                <a href="#"
                    class="flex items-center px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5 mr-3">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Tài khoản</span>
                </a>
                <a href="/wishlist"
                    class="flex items-center px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5 mr-3">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                        </path>
                    </svg>
                    <span>Yêu thích</span>
                </a>
            </div>

            <div class="border-t border-gray-700/60 pt-4 mt-4">
                <h3 class="px-3 text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Điều hướng</h3>
                <div class="space-y-1">
                    <a href="/"
                        class="block px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">Trang chủ</a>
                    <a href="/danh-muc-san-pham"
                        class="block px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">Danh mục</a>
                    <a href="/blog"
                        class="block px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">Tin Tức</a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    if (!window.headerScriptLoaded) {
        window.headerScriptLoaded = true;

        document.addEventListener('DOMContentLoaded', () => {
            // --- Elements ---
            const pageHeader = document.getElementById('page-header');
            const headerMain = document.getElementById('header-main');
            const headerSearch = document.getElementById('header-search');

            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');

            const searchTriggerBtn = document.querySelector('.js-search-trigger');
            const searchCloseBtn = document.getElementById('search-close-btn');
            const searchInputInHeader = headerSearch ? headerSearch.querySelector('.search-input') : null;

            const userMenuTrigger = document.getElementById('user-menu-trigger');
            const userDropdownMenu = document.getElementById('user-dropdown-menu');

            // Dropdown Views
            const mainMenuView = document.getElementById('main-menu-view');
            const notificationDetailView = document.getElementById('notification-detail-view');

            // View Triggers
            const notificationTrigger = document.getElementById('notification-trigger');
            const backToMenuBtn = document.getElementById('back-to-menu-btn');

            // --- User Dropdown Menu Logic ---
            if (userMenuTrigger && userDropdownMenu) {
                userMenuTrigger.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const isOpening = !userDropdownMenu.classList.contains('open');
                    userDropdownMenu.classList.toggle('open');

                    // Reset to main menu view and original size when opening
                    if (isOpening && mainMenuView && notificationDetailView) {
                        mainMenuView.classList.remove('hidden');
                        notificationDetailView.classList.add('hidden');
                        userDropdownMenu.classList.remove('w-80', 'sm:w-96');
                        userDropdownMenu.classList.add('w-56');
                    }
                });
            }

            // --- Switch to Notification View ---
            if (notificationTrigger && userDropdownMenu && mainMenuView && notificationDetailView) {
                notificationTrigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    userDropdownMenu.classList.remove('w-56');
                    userDropdownMenu.classList.add('w-80', 'sm:w-96');
                    mainMenuView.classList.add('hidden');
                    notificationDetailView.classList.remove('hidden');
                    // Optional: Call Laravel to mark as read + hide the red dot
                    // Make sure you have a meta tag for CSRF token: <meta name="csrf-token" content="{{ csrf_token() }}">
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        fetch('/notifications/mark-as-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        }).then(res => {
                            if (res.ok) {
                                const badge = document.querySelector(
                                    '#notification-trigger span.ml-auto');
                                if (badge) badge.remove();
                            }
                        }).catch(err => console.error('Error marking notifications as read:', err));
                    }
                });
            }

            // --- Switch back to Main Menu View ---
            if (backToMenuBtn && userDropdownMenu && mainMenuView && notificationDetailView) {
                backToMenuBtn.addEventListener('click', () => {
                    userDropdownMenu.classList.remove('w-80', 'sm:w-96');
                    userDropdownMenu.classList.add('w-56');
                    notificationDetailView.classList.add('hidden');
                    mainMenuView.classList.remove('hidden');
                });
            }

            // --- Close dropdown when clicking outside ---
            window.addEventListener('click', (event) => {
                if (userDropdownMenu && userDropdownMenu.classList.contains('open') && !userMenuTrigger
                    .contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    userDropdownMenu.classList.remove('open');
                }
            });

            // --- Mobile Menu Toggle ---
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    const isSearchActive = headerMain && !headerMain.classList.contains('flex');
                    if (isSearchActive) {
                        closeSearch();
                    }
                    mobileMenu.classList.toggle('open');
                });
            }

            // --- Header Scroll Effect ---
            window.addEventListener('scroll', () => {
                if (window.scrollY > 10) {
                    if (pageHeader) pageHeader.classList.add('scrolled');
                } else {
                    if (pageHeader) pageHeader.classList.remove('scrolled');
                }
            });

            // --- Header Search State Logic ---
            const openSearch = () => {
                if (mobileMenu && mobileMenu.classList.contains('open')) {
                    mobileMenu.classList.remove('open');
                }
                if (headerMain) {
                    headerMain.classList.remove('flex');
                    headerMain.classList.add('hidden');
                }
                if (headerSearch) {
                    headerSearch.classList.remove('hidden');
                    headerSearch.classList.add('flex');
                }
                setTimeout(() => {
                    if (searchInputInHeader) searchInputInHeader.focus();
                }, 50);
            };

            const closeSearch = () => {
                if (headerSearch) {
                    headerSearch.classList.remove('flex');
                    headerSearch.classList.add('hidden');
                }
                if (headerMain) {
                    headerMain.classList.remove('hidden');
                    headerMain.classList.add('flex');
                }
            };

            if (searchTriggerBtn) searchTriggerBtn.addEventListener('click', openSearch);
            if (searchCloseBtn) searchCloseBtn.addEventListener('click', closeSearch);

            document.addEventListener('keydown', (e) => {
                if (headerSearch) {
                    const isSearchActive = headerSearch.classList.contains('flex');
                    if (e.key === 'Escape' && isSearchActive) {
                        closeSearch();
                    }
                }
            });

            // --- Animated Search Bar Logic ---
            if (searchInputInHeader) {
                const wrapper = searchInputInHeader.closest('.search-wrapper');
                if (wrapper) {
                    const innerBg = wrapper.querySelector('.relative');
                    const button = wrapper.querySelector('.search-submit-btn');
                    if (innerBg && button) {
                        searchInputInHeader.addEventListener('focus', () => {
                            wrapper.classList.add('is-focused');
                            innerBg.classList.add('is-focused-inner');
                            button.classList.add('is-focused');
                        });

                        searchInputInHeader.addEventListener('blur', () => {
                            wrapper.classList.remove('is-focused');
                            innerBg.classList.remove('is-focused-inner');
                            button.classList.remove('is-focused');
                        });
                    }
                }
            }
        });
    }
    let cachedCartHtml = null;

    document.querySelector('[data-bs-target="#shoppingCart"]').addEventListener('click', function() {
        if (cachedCartHtml) {
            document.getElementById('cart-content').innerHTML = cachedCartHtml;
            return;
        }
        fetch('/cart/offcanvas', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                cachedCartHtml = html; // lưu cache
                document.getElementById('cart-content').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('cart-content').innerHTML = '<p>Có lỗi xảy ra.</p>';
            });
    });
</script>
