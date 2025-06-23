{{-- Giả sử bạn có biến $unreadNotificationsCount và $recentNotifications từ Controller --}}
@php
$unreadNotificationsCount = 5; // Ví dụ
$recentNotifications = [
['type' => 'order', 'title' => 'Đơn hàng #12345 vừa được tạo', 'time' => '5 phút trước', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
</svg>'],
['type' => 'user', 'title' => 'Người dùng mới: An Nguyễn', 'time' => '1 giờ trước', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
</svg>'],
['type' => 'review', 'title' => 'Có một đánh giá sản phẩm mới', 'time' => '3 giờ trước', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.05 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.95-.69L11.049 2.927z" />
</svg>'],
];
// Giả lập thông tin user để hiển thị
$user = auth()->user() ?? (object)[
'name' => 'Anh Tuấn',
'email' => 'admin@gmail.com',
'avatar_url' => 'https://placehold.co/100x100/E2E8F0/4A5568?text=A',
'role' => 'Super Admin'
];
@endphp

{{-- Thêm `x-data` cho dark mode và ` :class="{'dark': darkMode}"` vào thẻ <html> của bạn --}}
<header class="relative z-30 bg-white dark:bg-slate-800 dark:border-b dark:border-slate-700 shadow-sm print:hidden">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-4">
            {{-- Nút hamburger --}}
            <button type="button" class="block lg:hidden text-2xl text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400" x-on:click="sideMenu = !sideMenu">
                <svg width="20" height="14" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M1 7H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M1 13H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
{{-- Thanh tìm kiếm --}}
            <div class="hidden md:block">
                <form action="#">
                    <div class="relative group">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M18.9999 19L14.6499 14.65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <input class="w-52 lg:w-72 h-10 pl-10 pr-4 bg-slate-100 dark:bg-slate-700 border border-transparent rounded-lg focus:bg-white dark:focus:bg-slate-600 focus:border-indigo-400 focus:ring-indigo-400 focus:ring-1 transition-all duration-300 outline-none text-slate-700 dark:text-slate-200" type="text" placeholder="Tìm kiếm...">
                    </div>
                </form>
            </div>
        </div>

        <div class="flex items-center space-x-2 sm:space-x-3">
            {{-- [MỚI] Nút "Tạo Mới" --}}
            {{-- <div class="hidden sm:block">
                <div class="relative" x-data="{ createMenuOpen: false }">
                    <button @click="createMenuOpen = !createMenuOpen" class="flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden lg:block">Tạo Mới</span>
                    </button>
                    <div x-show="createMenuOpen" @click.outside="createMenuOpen = false"
                         x-transition:enter="transition ease-out duration-200 origin-top"
                         x-transition:enter-start="opacity-0 scale-y-90"
                         x-transition:enter-end="opacity-100 scale-y-100"
                         x-transition:leave="transition ease-in duration-150 origin-top"
                         x-transition:leave-start="opacity-100 scale-y-100"
                         x-transition:leave-end="opacity-0 scale-y-90"
                         class="absolute w-52 top-full right-0 mt-2 shadow-lg rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 py-1.5"
                         style="display: none;">
<a href="{{ route('admin.products.create') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600">Thêm sản phẩm</a>
            <a href="{{ route('admin.categories.create') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600">Thêm danh mục</a>
            <a href="{{ route('admin.users.create') }}" class="flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600">Thêm người dùng</a>
        </div>
    </div>
    </div> --}}

    {{-- [MỚI] Nút xem trang chủ --}}
    <a href="{{ url('/') }}" target="_blank" class="flex items-center justify-center w-10 h-10 rounded-full text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors duration-300" title="Xem trang chủ">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
        </svg>
    </a>

    {{-- [MỚI] Nút Sáng/Tối --}}
    {{-- <button @click="darkMode = !darkMode" class="flex items-center justify-center w-10 h-10 rounded-full text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors duration-300">
                <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </button> --}}

    {{-- Thông báo --}}
    <div class="relative" x-data="{ notificationOpen: false }">
        <button @click="notificationOpen = !notificationOpen" class="flex items-center justify-center w-10 h-10 rounded-full text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if($unreadNotificationsCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex items-center justify-center text-xs text-white rounded-full h-4 w-4 bg-red-500">{{ $unreadNotificationsCount }}</span>
            </span>
            @endif
        </button>

        <div x-show="notificationOpen" @click.outside="notificationOpen = false"
            x-transition:enter="transition ease-out duration-200 origin-top"
            x-transition:enter-start="opacity-0 scale-y-90"
            x-transition:enter-end="opacity-100 scale-y-100"
            x-transition:leave="transition ease-in duration-150 origin-top"
            x-transition:leave-start="opacity-100 scale-y-100"
            x-transition:leave-end="opacity-0 scale-y-90"
            class="absolute w-80 sm:w-96 max-h-[80vh] overflow-y-auto top-full right-0 sm:-right-10 mt-2 shadow-lg rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600"
            style="display: none;">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-600">
                <h4 class="font-semibold text-slate-800 dark:text-slate-100">Thông báo</h4>
            </div>
            <ul class="divide-y divide-slate-200 dark:divide-slate-600">
                @forelse($recentNotifications as $notification)
                <li class="hover:bg-slate-50 dark:hover:bg-slate-600">
                    <a class="block p-4" href="#">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-500 flex items-center justify-center">
                                {!! $notification['icon'] !!}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-slate-700 dark:text-slate-200 leading-tight">{{ $notification['title'] }}</p>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $notification['time'] }}</span>
                            </div>
                        </div>
                    </a>
                </li>
                @empty
                <li class="p-4 text-center text-sm text-slate-500 dark:text-slate-400">
                    Không có thông báo mới.
                </li>
                @endforelse
            </ul>
            <div class="px-4 py-2 border-t border-slate-200 dark:border-slate-600">
<a href="#" class="block w-full text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                    Xem tất cả
                </a>
            </div>
        </div>
    </div>

    <div class="h-6 w-px bg-slate-200 dark:bg-slate-600 hidden sm:block"></div>

    {{-- Profile User --}}
    <div class="relative w-[70%] flex justify-end items-center" x-data="{ userOption: false }">
        <!-- Avatar button -->
        <button class="relative" type="button" @click="userOption = !userOption" aria-haspopup="true" :aria-expanded="userOption">
            <img class="w-[40px] h-[40px] rounded-md object-cover" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
            <span class="w-[12px] h-[12px] inline-block bg-green-500 rounded-full absolute -top-[4px] -right-[4px] border-[2px] border-white"></span>
        </button>

        <!-- Dropdown menu -->
        <div
            x-show="userOption"
            @click.outside="userOption = false"
            x-transition:enter="transition ease-out duration-200 origin-top"
            x-transition:enter-start="opacity-0 scale-y-90"
            x-transition:enter-end="opacity-100 scale-y-100"
            x-transition:leave="transition ease-in duration-150 origin-top"
            x-transition:leave-start="opacity-100 scale-y-100"
            x-transition:leave-end="opacity-0 scale-y-90"
            class="absolute w-[280px] top-full right-0 mt-2 shadow-lg rounded-md bg-white py-5 px-5 z-50"
            style="display: none;"
            id="userDropdown">
            <!-- User Info -->
            <div class="flex items-center space-x-3 border-b border-gray-200 pb-3 mb-2">
                <img class="w-[50px] h-[50px] rounded-md object-cover" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                <div>
                    <h5 class="text-base mb-1 leading-none text-slate-800">{{ $user->name }}</h5>
                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                </div>
            </div>

            <!-- Menu Links -->
            <ul class="text-slate-700 text-base">
                <li>
                    <a href="{{ route('profile.edit') }}" class="px-5 py-2 w-full block hover:bg-gray-100 rounded-md hover:text-indigo-600">Cài đặt tài khoản</a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-left px-5 py-2 w-full block hover:bg-red-100 rounded-md hover:text-red-600">
                            Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    </div>
    </div>

</header>
