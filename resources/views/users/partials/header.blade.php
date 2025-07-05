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
        transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none; /* Ngăn tương tác khi ẩn */
    }

    /* Hiển thị dropdown khi có class 'open' */
    #user-dropdown-menu.open {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto; /* Cho phép tương tác khi hiện */
    }

    /* Style cho avatar người dùng khi đã đăng nhập */
    #user-avatar {
        background-color: rgba(255, 255, 255, 0.1); /* Màu nền xám nhạt ban đầu */
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
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
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

<header id="page-header" class="bg-gray-900 text-white z-50 border-b border-transparent transition-all duration-300 @unless(Route::is('users.products.show')) sticky top-0 @endunless">
    <div class="container mx-auto px-4 h-20">
        <div id="header-main" class="flex items-center justify-between h-full">
            <div class="flex justify-start">
                <a href="/" class="flex-shrink-0">
                    <img class="h-12 sm:h-14 w-auto" src="{{ asset('assets/users/logo/logo-full.svg') }}" alt="Logo">
                </a>
            </div>

            <div class="flex-1 flex justify-center">
                <nav class="hidden lg:flex items-center space-x-8">
                    <a href="/" class="text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200 whitespace-nowrap">Trang chủ</a>
                    <a href="/danh-muc-san-pham" class="text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200 whitespace-nowrap">Danh mục</a>
                    <a href="/blog" class="text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200 whitespace-nowrap">Tin Tức</a>
                    <a href="#" class="text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200 whitespace-nowrap">Về chúng tôi</a>
                    <a href="#" class="text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200 whitespace-nowrap">Liên hệ</a>
                </nav>
            </div>
            
            <div class="flex justify-end">
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <button class="js-search-trigger p-2 rounded-full text-gray-300 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                    <a href="#" class="hidden md:block p-2 rounded-full text-gray-300 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </a>
                    
                    <div class="relative">
                        <button id="user-menu-trigger" class="w-9 h-9 rounded-full flex items-center justify-center text-gray-300 hover:bg-white/10 transition-colors">
                            @guest
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </div>
                            @else
                                <div>
                                    <div class="relative">
                                        <div id="user-avatar" class="w-7 h-7 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-semibold text-white uppercase">{{ strtoupper(Auth::user()->name[0]) }}</span>
                                        </div>
                                        </div>
                                </div>
                            @endguest
                        </button>
                        
                        <div id="user-dropdown-menu" class="absolute top-full right-0 mt-2 w-56 bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-20 overflow-hidden">
                            <div class="py-2">
                                @guest
                                    <a href="{{ route('login') }}" class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                        <span>Đăng nhập</span>
                                    </a>
                                    <a href="{{ route('register') }}" class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></path><line x1="19" x2="19" y1="8" y2="14"></line><line x1="22" x2="16" y1="11" y2="11"></line></svg>
                                        <span>Đăng ký</span>
                                    </a>
                                @else
                                    @php $user = Auth::user(); @endphp
                                    <div class="px-4 py-2 border-b border-gray-700">
                                        <p class="text-sm text-white font-semibold" role="none">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-400 truncate" role="none">{{ Auth::user()->email }}</p>
                                    </div>
                                    <div class="py-2">
                                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            <span>Tài khoản của tôi</span>
                                        </a>
                                        
                                        @if ($user->roles->contains('id', 1) || $user->roles->contains('id', 4) || $user->roles->contains('id', 5))
                                            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-gray-200 hover:bg-gray-700 hover:text-white transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 12h10M7 7h2M7 17h5"/></svg>
                                                <span>Trang Quản Trị</span>
                                            </a>
                                        @endif

                                        <div class="border-t border-gray-700 my-2"></div>
                                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-400 hover:bg-gray-700 hover:text-red-300 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                                <span>Đăng xuất</span>
                                            </button>
                                        </form>
                                    </div>
                                @endguest
                            </div>
                        </div>
                    </div>

                    <a href="#" class="p-2 rounded-full text-gray-300 hover:text-white transition-colors relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                        <span class="absolute top-0 right-0 flex justify-center items-center h-4 w-4 bg-red-500 text-white text-[10px] font-semibold rounded-full transform translate-x-1/3 -translate-y-1/3">3</span>
                    </a>

                    <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-full text-gray-300 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
        </div>

         <div id="header-search" class="hidden items-center justify-center h-full">
            <div class="w-full max-w-2xl flex items-center space-x-4">
                 <div class="search-wrapper w-full rounded-full p-1 bg-white/10">
                     <div class="relative bg-transparent rounded-full">
                         <input type="search" placeholder="Tìm kiếm sản phẩm..." class="search-input w-full bg-transparent text-gray-300 rounded-full py-2.5 pl-6 pr-16 text-base placeholder-gray-400 focus:outline-none">
                         <button type="submit" class="search-submit-btn absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-all duration-300 transform hover:scale-110 focus:outline-none">
                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                         </button>
                     </div>
                 </div>
                 <button id="search-close-btn" class="text-gray-400 hover:text-white transition-colors flex-shrink-0">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                 </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="lg:hidden bg-gray-900/95 backdrop-blur-lg border-t border-gray-700/60">
        <div class="px-5 py-4 space-y-4">
             <div class="space-y-1">
                 <a href="#" class="flex items-center px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                     <span>Tài khoản</span>
                 </a>
                 <a href="#" class="flex items-center px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-3"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                     <span>Yêu thích</span>
                 </a>
             </div>

             <div class="border-t border-gray-700/60 pt-4 mt-4">
                 <h3 class="px-3 text-xs font-semibold uppercase text-gray-500 tracking-wider mb-2">Điều hướng</h3>
                 <div class="space-y-1">
                     <a href="/" class="block px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">Trang chủ</a>
                     <a href="/danh-muc-san-pham" class="block px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">Danh mục</a>
                     <a href="/blog" class="block px-3 py-2 text-gray-200 font-semibold hover:bg-white/10 rounded-md">Tin Tức</a>
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
            const searchInputInHeader = headerSearch.querySelector('.search-input');
            
            const userMenuTrigger = document.getElementById('user-menu-trigger');
            const userDropdownMenu = document.getElementById('user-dropdown-menu');
            
            // --- User Dropdown Menu Toggle ---
            if (userMenuTrigger) {
                userMenuTrigger.addEventListener('click', (event) => {
                    event.stopPropagation();
                    userDropdownMenu.classList.toggle('open');
                });
            }

            // --- Close dropdown when clicking outside ---
            window.addEventListener('click', (event) => {
                if (userDropdownMenu && userDropdownMenu.classList.contains('open') && !userMenuTrigger.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    userDropdownMenu.classList.remove('open');
                }
            });

            // --- Mobile Menu Toggle ---
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', () => {
                    const isSearchActive = !headerMain.classList.contains('flex');
                    if (isSearchActive) {
                        closeSearch();
                    }
                    mobileMenu.classList.toggle('open');
                });
            }

            // --- Header Scroll Effect ---
            window.addEventListener('scroll', () => {
                if (window.scrollY > 10) {
                    if(pageHeader) pageHeader.classList.add('scrolled');
                } else {
                    if(pageHeader) pageHeader.classList.remove('scrolled');
                }
            });

            // --- Header Search State Logic ---
            const openSearch = () => {
                if (mobileMenu && mobileMenu.classList.contains('open')) {
                    mobileMenu.classList.remove('open');
                }
                if(headerMain) {
                    headerMain.classList.remove('flex');
                    headerMain.classList.add('hidden');
                }
                if(headerSearch) {
                    headerSearch.classList.remove('hidden');
                    headerSearch.classList.add('flex');
                }
                setTimeout(() => { if(searchInputInHeader) searchInputInHeader.focus(); }, 50);
            };

            const closeSearch = () => {
                if(headerSearch) {
                    headerSearch.classList.remove('flex');
                    headerSearch.classList.add('hidden');
                }
                if(headerMain) {
                    headerMain.classList.remove('hidden');
                    headerMain.classList.add('flex');
                }
            };
            
            if(searchTriggerBtn) searchTriggerBtn.addEventListener('click', openSearch);
            if(searchCloseBtn) searchCloseBtn.addEventListener('click', closeSearch);
            
            document.addEventListener('keydown', (e) => {
                if(headerSearch) {
                    const isSearchActive = headerSearch.classList.contains('flex');
                    if (e.key === 'Escape' && isSearchActive) {
                        closeSearch();
                    }
                }
            });

            // --- Animated Search Bar Logic ---
            document.querySelectorAll('.search-input').forEach(input => {
                const wrapper = input.closest('.search-wrapper');
                if (!wrapper) return;
                
                const innerBg = wrapper.querySelector('.relative');
                const button = wrapper.querySelector('.search-submit-btn');
                if (!innerBg || !button) return;

                input.addEventListener('focus', () => {
                    wrapper.classList.add('is-focused');
                    innerBg.classList.add('is-focused-inner');
                    button.classList.add('is-focused');
                });

                input.addEventListener('blur', () => {
                    wrapper.classList.remove('is-focused');
                    innerBg.classList.remove('is-focused-inner');
                    button.classList.remove('is-focused');
                });
            });
        });
    }
</script>