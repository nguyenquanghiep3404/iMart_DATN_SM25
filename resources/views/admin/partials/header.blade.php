@php
    // Dữ liệu giả lập được giữ nguyên để component có thể hiển thị
    $user =
        auth()->user() ??
        (object) [
            'name' => 'Anh Tuấn',
            'email' => 'admin@gmail.com',
            'avatar_url' => 'https://placehold.co/100x100/E2E8F0/4A5568?text=A',
            'role' => 'Super Admin',
        ];
    $unreadNotificationsCount = 3;
    $recentNotifications = [
        [
            'id' => 1,
            'title' => 'Đơn hàng mới',
            'message' => 'Bạn có một đơn hàng mới #12345.',
            'time' => '2 phút trước',
            'color' => 'green',
            'icon' => 'check',
        ],
        [
            'id' => 2,
            'title' => 'Bình luận mới',
            'message' => 'Có bình luận mới trên bài viết "Hướng dẫn sử dụng".',
            'time' => '15 phút trước',
            'color' => 'blue',
            'icon' => 'comment',
        ],
        [
            'id' => 3,
            'title' => 'Cảnh báo tồn kho',
            'message' => 'Sản phẩm iPhone 15 Pro đang gần hết hàng.',
            'time' => '1 giờ trước',
            'color' => 'yellow',
            'icon' => 'warning',
        ],
    ];
@endphp

<header x-data="{ searchOpen: false }"
    class="relative z-30 bg-white dark:bg-slate-800 dark:border-b dark:border-slate-700 shadow-sm print:hidden">

    {{-- THANH HEADER CHÍNH --}}
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-4">
            <button type="button"
                class="text-2xl text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400"
                @click="toggleSideMenu()">
                <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M1 7H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M1 13H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>

            {{-- Thanh tìm kiếm (Chỉ hiển thị trên màn hình lớn) --}}
            <div class="hidden lg:block">
                <form action="#">
                    <div class="relative group">
                        <span
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <input
                            class="w-52 xl:w-72 h-10 pl-10 pr-4 bg-slate-100 dark:bg-slate-700 border border-transparent rounded-lg focus:bg-white dark:focus:bg-slate-600 focus:border-indigo-400 focus:ring-indigo-400 focus:ring-1 transition-all duration-300 outline-none text-slate-700 dark:text-slate-200"
                            type="text" placeholder="Tìm kiếm...">
                    </div>
                </form>
            </div>
        </div>

        <div class="flex items-center space-x-1 sm:space-x-2">
            {{-- [MỚI] Nút tìm kiếm (Chỉ hiển thị trên màn hình nhỏ) --}}
            <button type="button" @click="searchOpen = true" aria-label="Mở tìm kiếm"
                class="lg:hidden flex items-center justify-center w-10 h-10 rounded-full text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors duration-300">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            {{-- Nút xem trang chủ --}}
            <a href="{{ url('/') }}" target="_blank"
                class="flex items-center justify-center w-10 h-10 rounded-full text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors duration-300"
                title="Xem trang chủ">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>

            {{-- Thông báo --}}
            <div class="relative" x-data="{ notificationOpen: false, unreadCount: {{ $unreadNotificationsCount }} }">
                <button @click.stop="notificationOpen = !notificationOpen"
                    class="flex items-center justify-center w-10 h-10 rounded-full text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors duration-300 relative"
                    aria-label="Thông báo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <template x-if="unreadCount > 0">
                        <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span
                                class="relative inline-flex items-center justify-center text-xs text-white rounded-full h-4 w-4 bg-red-500"
                                x-text="unreadCount"></span>
                        </span>
                    </template>
                </button>
                <div x-show="notificationOpen" @click.outside="notificationOpen = false"
                    x-transition:enter="transition ease-out duration-200 origin-top"
                    x-transition:enter-start="opacity-0 scale-y-90" x-transition:enter-end="opacity-100 scale-y-100"
                    x-transition:leave="transition ease-in duration-150 origin-top"
                    x-transition:leave-start="opacity-100 scale-y-100" x-transition:leave-end="opacity-0 scale-y-90"
                    class="absolute right-0 sm:-right-10 mt-2 w-80 sm:w-96 max-h-96 overflow-y-auto shadow-lg rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 z-50"
                    style="display: none;">
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-600">
                        <h4 class="font-semibold text-slate-800 dark:text-slate-100">Thông báo</h4>
                    </div>
                    <ul class="divide-y divide-slate-200 dark:divide-slate-600">
                        @forelse($recentNotifications ?? [] as $notification)
                            <a href="#" class="flex items-start p-3 hover:bg-gray-700/50 transition-colors">
                                <div
                                    class="flex-shrink-0 w-10 h-10 bg-{{ $notification['color'] ?? 'gray' }}-500/20 text-{{ $notification['color'] ?? 'gray' }}-400 rounded-full flex items-center justify-center">
                                    @if (isset($notification['icon']) && $notification['icon'] === 'check')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="w-5 h-5">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    @elseif (isset($notification['icon']) && $notification['icon'] === 'warning')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path
                                                d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z">
                                            </path>
                                            <line x1="12" x2="12" y1="9" y2="13">
                                            </line>
                                            <line x1="12" x2="12.01" y1="17" y2="17">
                                            </line>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <line x1="12" x2="12" y1="2" y2="22" />
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                        {{ $notification['title'] ?? 'Không có tiêu đề' }}</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">
                                        {{ $notification['message'] ?? 'Không có nội dung' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notification['time'] ?? '' }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-gray-400 py-8 px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                <p class="mt-4 text-sm font-semibold">Không có thông báo mới</p>
                                <p class="mt-1 text-xs text-gray-500">Chúng tôi sẽ cho bạn biết khi có tin tức.</p>
                            </div>
                        @endforelse
                    </ul>
                    <div class="px-4 py-2 border-t border-slate-200 dark:border-slate-600 text-center">
                        <a href="#"
                            class="block w-full text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">Xem
                            tất cả</a>
                    </div>
                </div>
            </div>

            {{-- Profile User --}}
            <div class="relative flex items-center" x-data="{ userOption: false }">
                <button class="relative" type="button" @click="userOption = !userOption" aria-haspopup="true"
                    :aria-expanded="userOption">
                    <img class="w-[40px] h-[40px] rounded-md object-cover" src="{{ $user->avatar_url }}"
                        alt="{{ $user->name }}">
                    <span
                        class="w-[12px] h-[12px] inline-block bg-green-500 rounded-full absolute -top-[4px] -right-[4px] border-[2px] border-white"></span>
                </button>
                <div x-show="userOption" @click.outside="userOption = false"
                    x-transition:enter="transition ease-out duration-200 origin-top"
                    x-transition:enter-start="opacity-0 scale-y-90" x-transition:enter-end="opacity-100 scale-y-100"
                    x-transition:leave="transition ease-in duration-150 origin-top"
                    x-transition:leave-start="opacity-100 scale-y-100" x-transition:leave-end="opacity-0 scale-y-90"
                    class="absolute w-[280px] top-full right-0 mt-2 shadow-lg rounded-md bg-white dark:bg-slate-700 py-5 px-5 z-50 border border-slate-200 dark:border-slate-600"
                    style="display: none;">
                    <div class="flex items-center space-x-3 border-b border-gray-200 dark:border-slate-600 pb-3 mb-2">
                        <img class="w-[50px] h-[50px] rounded-md object-cover" src="{{ $user->avatar_url }}"
                            alt="{{ $user->name }}">
                        <div>
                            <h5 class="text-base mb-1 leading-none text-slate-800 dark:text-slate-100">
                                {{ $user->name }}</h5>
                            <p class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                    <ul class="text-slate-700 dark:text-slate-200 text-base">
                        <li>
                            <a href="#"
                                class="px-3 py-2 w-full block hover:bg-gray-100 dark:hover:bg-slate-600 rounded-md hover:text-indigo-600 dark:hover:text-indigo-400">Cài
                                đặt tài khoản</a>
                        </li>
                        <li>
                            <form method="POST" action="#">
                                @csrf
                                <button type="submit"
                                    class="text-left px-3 py-2 w-full block hover:bg-red-100 dark:hover:bg-red-500/20 rounded-md hover:text-red-600 dark:hover:text-red-500">
                                    Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- [MỚI] OVERLAY TÌM KIẾM --}}
    <div x-show="searchOpen" x-trap.noscroll="searchOpen" style="display: none;"
        class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-8"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50" @click="searchOpen = false"></div>

        <div @click.away="searchOpen = false" class="relative w-full max-w-lg mt-12 transform"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <form action="#">
                <div class="relative">
                    <input
                        class="w-full h-14 pl-14 pr-14 text-base bg-white border border-slate-300 rounded-lg shadow-lg focus:border-indigo-500 focus:ring-indigo-500"
                        type="search" placeholder="Nhập từ khóa tìm kiếm..." x-ref="searchInput"
                        @keydown.escape.window="searchOpen = false">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <button type="button" @click="searchOpen = false" aria-label="Đóng tìm kiếm"
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</header>
