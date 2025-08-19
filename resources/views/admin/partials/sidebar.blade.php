@php
    // --- SINGLE SOURCE OF TRUTH FOR SIDEBAR NAVIGATION ---
    // This array defines the entire sidebar structure.
    // To add, remove, or reorder items, you only need to modify this array.
    $currentRouteName = request()->route()->getName();

    // Helper function to check for active state in dropdowns
    function is_active_section($children, $currentRoute)
    {
        foreach ($children as $child) {
            // Ensure 'active_check' key exists before iterating
            if (isset($child['active_check']) && is_array($child['active_check'])) {
                foreach ($child['active_check'] as $prefix) {
                    if (str_starts_with($currentRoute, $prefix)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    $navigation = [
        // 1. Dashboard
        [
            'label' => 'Trang chủ',
            'type' => 'link',
            'route' => 'admin.dashboard',
            'active_check' => ['admin.dashboard'],
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7,0H4A4,4,0,0,0,0,4V7a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V4A4,4,0,0,0,7,0ZM9,7A2,2,0,0,1,7,9H4A2,2,0,0,1,2,7V4A2,2,0,0,1,4,2H7A2,2,0,0,1,9,4Z" /><path d="M20,0H17a4,4,0,0,0-4,4V7a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V4A4,4,0,0,0,20,0Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V4a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z" /><path d="M7,13H4a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V17A4,4,0,0,0,7,13Zm2,7a2,2,0,0,1-2,2H4a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2H7a2,2,0,0,1,2,2Z" /><path d="M20,13H17a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V17A4,4,0,0,0,20,13Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z" /></svg>',
        ],

        // 2. Sales
        [
            'label' => 'Bán hàng',
            'type' => 'dropdown',
            'id' => 'sales',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M21.08,7a2,2,0,0,0-1.7-1H6.58L6,3.74A1,1,0,0,0,5,3H3A1,1,0,0,0,3,5H4.24L7,15.26A1,1,0,0,0,8,16H18a1,1,0,0,0,.93-.66L21.23,9.34A2,2,0,0,0,21.08,7Zm-2.39,7H8.74L7.22,7H19.38Z" /><circle cx="8.5" cy="19.5" r="1.5" /><circle cx="17.5" cy="19.5" r="1.5" /></svg>',
            'children' => [
                ['label' => 'Đơn hàng', 'route' => 'admin.orders.index', 'active_check' => ['admin.orders.']],
                ['label' => 'Hoàn tiền', 'route' => 'admin.refunds.index', 'active_check' => ['admin.refunds.']],
                [
                    'label' => 'Giỏ hàng bỏ lỡ',
                    'route' => 'admin.abandoned-carts.index',
                    'active_check' => ['admin.abandoned-carts.'],
                ],
            ],
        ],

        // 3. Point of Sale (POS)
        [
            'label' => 'POS - Bán tại quầy',
            'type' => 'dropdown',
            'id' => 'pos',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12a1 1 0 011 1v3H5V4a1 1 0 011-1zm0 5h12v13a1 1 0 01-1 1H7a1 1 0 01-1-1V8zm2 3h2m4 0h2m-8 4h6" /></svg>',
            'children' => [
                ['label' => 'Máy POS', 'route' => 'admin.registers.index', 'active_check' => ['admin.registers.']],
                [
                    'label' => 'Nhân viên bán hàng',
                    'route' => 'admin.sales-staff.index',
                    'active_check' => ['admin.sales-staff.'],
                ],
                [
                    'label' => 'Địa điểm cửa hàng',
                    'route' => 'admin.store-locations.index',
                    'active_check' => ['admin.store-locations.'],
                ],
            ],
        ],

        // 4. Inventory
        [
            'label' => 'Kho & Tồn kho',
            'type' => 'dropdown',
            'id' => 'inventory',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20.54,5.23l-1.39-1.39A3,3,0,0,0,17,3H7A3,3,0,0,0,4.85,3.84L3.46,5.23A3,3,0,0,0,3,7.35V19a3,3,0,0,0,3,3H18a3,3,0,0,0,3-3V7.35A3,3,0,0,0,20.54,5.23ZM5.41,5H18.59l.6,0.6V7H5V5.6ZM19,19a1,1,0,0,1-1,1H6a1,1,0,0,1-1-1V9H19Z" /><path d="M10.5,13.5h3a1,1,0,0,0,0-2h-3a1,1,0,0,0,0,2Z" /></svg>',
            'children' => [
                ['label' => 'Nhà cung cấp', 'route' => 'admin.suppliers.index', 'active_check' => ['admin.suppliers.']],
                [
                    'label' => 'Nhập kho (PO)',
                    'route' => 'admin.purchase-orders.index',
                    'active_check' => ['admin.purchase-orders.'],
                ],
                [
                    'label' => 'Chuyển kho',
                    'route' => 'admin.stock-transfers.index',
                    'active_check' => ['admin.stock-transfers.'],
                ],
                [
                    'label' => 'Chuyển kho tự động',
                    'route' => 'admin.auto-stock-transfers.manage',
                    'active_check' => ['admin.auto-stock-transfers.'],
                ],
                [
                    'label' => 'Trạm đóng gói',
                    'route' => 'admin.packing-station.index',
                    'active_check' => ['admin.packing-station.'],
                ],
                // MỚI: Thêm route tra cứu serial
                [
                    'label' => 'Tra cứu Serial',
                    'route' => 'admin.serial.lookup.form',
                    'active_check' => ['admin.serial.lookup.'],
                ],
                // MỚI: Thêm route báo cáo tồn kho
                [
                    'label' => 'Sổ kho (Báo cáo)',
                    'route' => 'admin.inventory-ledger.index',
                    'active_check' => ['admin.inventory-ledger.'],
                ],
            ],
        ],

        // 5. Catalog
        [
            'label' => 'Sản phẩm',
            'type' => 'dropdown',
            'id' => 'catalog',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M23.621,6.836l-1.352-2.826c-.349-.73-.99-1.296-1.758-1.552L14.214,.359c-1.428-.476-3-.476-4.428,0L3.49,2.458c-.769,.256-1.41,.823-1.759,1.554L.445,6.719c-.477,.792-.567,1.742-.247,2.609,.309,.84,.964,1.49,1.802,1.796l-.005,6.314c-.002,2.158,1.372,4.066,3.418,4.748l4.365,1.455c.714,.238,1.464,.357,2.214,.357s1.5-.119,2.214-.357l4.369-1.457c2.043-.681,3.417-2.585,3.419-4.739l.005-6.32c.846-.297,1.508-.946,1.819-1.79,.317-.858,.228-1.799-.198-2.499ZM10.419,2.257c1.02-.34,2.143-.34,3.162,0l4.248,1.416-5.822,1.95-5.834-1.95,4.246-1.415ZM2.204,7.666l1.327-2.782c.048,.025,7.057,2.373,7.057,2.373l-1.621,3.258c-.239,.398-.735,.582-1.173,.434l-5.081-1.693c-.297-.099-.53-.325-.639-.619-.109-.294-.078-.616,.129-.97Zm3.841,12.623c-1.228-.409-2.052-1.554-2.051-2.848l.005-5.648,3.162,1.054c1.344,.448,2.792-.087,3.559-1.371l.278-.557-.005,10.981c-.197-.04-.391-.091-.581-.155l-4.366-1.455Zm11.897-.001l-4.37,1.457c-.19,.063-.384,.115-.581,.155l.005-10.995,.319,.64c.556,.928,1.532,1.459,2.561,1.459,.319,0,.643-.051,.96-.157l3.161-1.053-.005,5.651c0,1.292-.826,2.435-2.052,2.844Zm4-11.644c-.105,.285-.331,.504-.619,.6l-5.118,1.706c-.438,.147-.934-.035-1.136-.365l-1.655-3.323s7.006-2.351,7.054-2.377l1.393,2.901c.157,.261,.186,.574,.081,.859Z" /></svg>',
            'children' => [
                [
                    'label' => 'Tất cả sản phẩm',
                    'route' => 'admin.products.index',
                    'active_check' => ['admin.products.'],
                ],
                [
                    'label' => 'Gói sản phẩm',
                    'route' => 'admin.bundle-products.index',
                    'active_check' => ['admin.bundle-products.'],
                ],
                [
                    'label' => 'Thu cũ & Mở hộp',
                    'route' => 'admin.trade-in-items.index',
                    'active_check' => ['admin.trade-in-items.'],
                ],
                ['label' => 'Danh mục', 'route' => 'admin.categories.index', 'active_check' => ['admin.categories.']],
                ['label' => 'Thuộc tính', 'route' => 'admin.attributes.index', 'active_check' => ['admin.attributes.']],
                [
                    'label' => 'Thông số',
                    'route' => 'admin.specifications.index',
                    'active_check' => ['admin.specifications.', 'admin.specification-groups.'],
                ],
            ],
        ],

        // 6. Marketing
        [
            'label' => 'Marketing',
            'type' => 'dropdown',
            'id' => 'marketing',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20.2,6.2a1,1,0,0,0-1.1.2L14.9,9.6A4.47,4.47,0,0,0,8,7.5,4.5,4.5,0,0,0,3.5,12,4.5,4.5,0,0,0,8,16.5a4.47,4.47,0,0,0,6.9-2.1l4.2,3.2a1,1,0,0,0,1.3-.2,1,1,0,0,0-.2-1.3L16,13.2a4.49,4.49,0,0,0-1-.7V10.1l5.4-4.1a1,1,0,0,0,.2-1.4A.87.87,0,0,0,20.2,6.2ZM8,14.5a2.5,2.5,0,1,1,2.5-2.5A2.5,2.5,0,0,1,8,14.5Z" /><path d="M8,13a1,1,0,0,0,1-1V8a1,1,0,0,0-2,0v4A1,1,0,0,0,8,13Z" /></svg>',
            'children' => [
                [
                    'label' => 'Chiến dịch',
                    'route' => 'admin.marketing_campaigns.index',
                    'active_check' => ['admin.marketing_campaigns.'],
                ],
                ['label' => 'Mã giảm giá', 'route' => 'admin.coupons.index', 'active_check' => ['admin.coupons.']],
                [
                    'label' => 'Flash Sales',
                    'route' => 'admin.flash-sales.index',
                    'active_check' => ['admin.flash-sales.'],
                ],
                ['label' => 'Quản lý Banner', 'route' => 'admin.banners.index', 'active_check' => ['admin.banners.']],
                [
                    'label' => 'Quản lý Trang chủ',
                    'route' => 'admin.homepage.index',
                    'active_check' => ['admin.homepage.'],
                ],
            ],
        ],

        // 7. Customers
        [
            'label' => 'Khách hàng',
            'type' => 'dropdown',
            'id' => 'customers',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12,12A6,6,0,1,0,6,6,6.006,6.006,0,0,0,12,12Zm0-10a4,4,0,1,1-4,4A4,4,0,0,1,12,2Z" /><path d="M12,14a9.01,9.01,0,0,0-9,9,1,1,0,0,0,1,1H20a1,1,0,0,0,1-1A9.01,9.01,0,0,0,12,14Zm-7,8a7.012,7.012,0,0,1,14,0Z" /></svg>',
            'children' => [
                ['label' => 'Danh sách khách hàng', 'route' => 'admin.users.index', 'active_check' => ['admin.users.']],
                [
                    'label' => 'Nhóm khách hàng',
                    'route' => 'admin.customer-groups.index',
                    'active_check' => ['admin.customer-groups.'],
                ],
                [
                    'label' => 'Điểm thưởng',
                    'route' => 'admin.purchase-orders.loyalty.index',
                    'active_check' => ['admin.purchase-orders.loyalty.'],
                ],
            ],
        ],

        // 8. Content
        [
            'label' => 'Nội dung',
            'type' => 'dropdown',
            'id' => 'content',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19,2H5A3,3,0,0,0,2,5V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V5A3,3,0,0,0,19,2Zm1,17a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V5A1,1,0,0,1,5,4H19a1,1,0,0,1,1,1Z" /><path d="M7,7h4a1,1,0,0,0,0-2H7A1,1,0,0,0,7,7Z" /><path d="M17,11H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z" /><path d="M17,15H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z" /></svg>',
            'children' => [
                ['label' => 'Bài viết', 'route' => 'admin.posts.index', 'active_check' => ['admin.posts.']],
                [
                    'label' => 'Danh mục bài viết',
                    'route' => 'admin.categories_post.index',
                    'active_check' => ['admin.categories_post.'],
                ],
                ['label' => 'Thẻ bài viết', 'route' => 'admin.post-tags.index', 'active_check' => ['admin.post-tags.']],
                ['label' => 'Bình luận', 'route' => 'admin.comment.index', 'active_check' => ['admin.comment.']],
                ['label' => 'Đánh giá', 'route' => 'admin.reviews.index', 'active_check' => ['admin.reviews.']],
            ],
        ],

        // 9. Staff (New Section)
        [
            'label' => 'Nhân sự',
            'type' => 'dropdown',
            'id' => 'staff',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16,14a5,5,0,1,0-5-5A5,5,0,0,0,16,14Zm0-8a3,3,0,1,1-3,3A3,3,0,0,1,16,6Z" /><path d="M16,16a6.991,6.991,0,0,0-5.83,3H21.83A6.991,6.991,0,0,0,16,16Z" /><path d="M8,9a5,5,0,1,0-5,5A5,5,0,0,0,8,9ZM8,1a3,3,0,1,1-3,3A3,3,0,0,1,8,1Z" /><path d="M8,11a6.991,6.991,0,0,0-5.83,3H13.83A6.991,6.991,0,0,0,8,11Z" /></svg>',
            'children' => [
                ['label' => 'NV Giao hàng', 'route' => 'admin.shippers.index', 'active_check' => ['admin.shippers.']],
                [
                    'label' => 'NV Nội dung',
                    'route' => 'admin.content-staffs.index',
                    'active_check' => ['admin.content-staffs.'],
                ],
                [
                    'label' => 'NV Đơn hàng',
                    'route' => 'admin.order-manager.index',
                    'active_check' => ['admin.order-manager.'],
                ],
            ],
        ],

        // 10. Media Library
        [
            'label' => 'Thư viện Media',
            'type' => 'link',
            'route' => 'admin.media.index',
            'active_check' => ['admin.media.'],
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z" /></svg>',
        ],

        // 11. Settings
        [
            'label' => 'Cài đặt',
            'type' => 'dropdown',
            'id' => 'settings',
            'icon' =>
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19.43,12.94a1,1,0,0,0-1.15-.36l-1.09.36a8.31,8.31,0,0,0-1.25-1.25l.36-1.09a1,1,0,0,0-.36-1.15L14,7.57a1,1,0,0,0-1.21,0L11.06,9.43a1,1,0,0,0-.36,1.15l.36,1.09a8.31,8.31,0,0,0-1.25,1.25l-1.09-.36a1,1,0,0,0-1.15.36L5.57,14a1,1,0,0,0,0,1.21l1.86,1.73a1,1,0,0,0,1.15.36l1.09-.36a8.31,8.31,0,0,0,1.25,1.25l-.36,1.09a1,1,0,0,0,.36,1.15L12,22.43a1,1,0,0,0,1.21,0l1.86-1.86a1,1,0,0,0,.36-1.15l-.36-1.09a8.31,8.31,0,0,0,1.25-1.25l1.09.36a1,1,0,0,0,1.15-.36L20.43,14a1,1,0,0,0,0-1.21ZM12,15.5A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" /></svg>',
            'children' => [
                ['label' => 'Chat Admin', 'route' => 'admin.chat.dashboard', 'active_check' => ['admin.chat.']],
                ['label' => 'Vai trò & Quyền', 'route' => 'admin.roles.index', 'active_check' => ['admin.roles.']],
            ],
        ],
    ];

    // Determine the active parent nav for Alpine.js
    $activeParentNav = '';
    foreach ($navigation as $navItem) {
        if ($navItem['type'] === 'dropdown') {
            if (is_active_section($navItem['children'], $currentRouteName)) {
                $activeParentNav = $navItem['id'];
                break;
            }
        } else {
            foreach ($navItem['active_check'] as $prefix) {
                if (str_starts_with($currentRouteName, $prefix)) {
                    // For single links, we don't need to open a dropdown
                    break 2;
                }
            }
        }
    }
@endphp

<aside id="adminSidebar"
    class="w-[280px] border-r border-slate-200 overflow-y-auto sidebar-scrollbar fixed left-0 top-0 h-full bg-white z-40 transition-transform duration-300 print:hidden flex flex-col"
    x-show="(window.innerWidth >= 1024) ? true : sideMenu"
    :class="(window.innerWidth >= 1024 && !sideMenu) ? '-translate-x-full' : ((sideMenu || window.innerWidth >= 1024) ?
        'translate-x-0' : '-translate-x-full')"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">

    <div class="flex flex-col h-full" x-data="{ openNav: '{{ $activeParentNav }}' }">
        {{-- SIDEBAR HEADER --}}
        <div class="flex h-16 items-center justify-center border-b border-slate-200 px-4">
            <a href="{{ route('admin.dashboard') }}">
                <img class="h-10 w-auto" src="{{ asset('assets/users/logo/logo-full.svg') }}" alt="iMart Logo">
            </a>
        </div>
        {{-- END HEADER --}}

        {{-- NAVIGATION --}}
        <div class="flex-1 overflow-y-auto">
            <nav class="px-3 py-4">
                <ul class="space-y-1">
                    @foreach ($navigation as $navItem)
                        {{-- RENDER A SINGLE LINK --}}
                        @if ($navItem['type'] === 'link')
                            @php
                                $isLinkActive = false;
                                foreach ($navItem['active_check'] as $prefix) {
                                    if (str_starts_with($currentRouteName, $prefix)) {
                                        $isLinkActive = true;
                                        break;
                                    }
                                }
                            @endphp
                            <li>
                                <a href="{{ route($navItem['route']) }}"
                                    class="group relative flex items-center px-3 py-2 text-sm rounded-md transition-all duration-200 ease-in-out
                                        {{ $isLinkActive ? 'text-indigo-600 font-semibold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                                    {{-- THAY ĐỔI: Thêm chỉ báo active --}}
                                    @if ($isLinkActive)
                                        <span
                                            class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-1 bg-indigo-600 rounded-r-full"></span>
                                    @endif
                                    <span
                                        class="mr-3 text-lg {{ $isLinkActive ? 'text-indigo-600' : 'text-slate-500 group-hover:text-slate-600' }}">
                                        {!! $navItem['icon'] !!}
                                    </span>
                                    {{ $navItem['label'] }}
                                </a>
                            </li>
                            {{-- RENDER A DROPDOWN MENU --}}
                        @elseif ($navItem['type'] === 'dropdown')
                            @php
                                $isDropdownActive = is_active_section($navItem['children'], $currentRouteName);
                            @endphp
                            <li class="relative">
                                <button
                                    @click="openNav = (openNav === '{{ $navItem['id'] }}' ? null : '{{ $navItem['id'] }}')"
                                    class="group w-full flex items-center px-3 py-2 text-sm rounded-md transition-all duration-200 ease-in-out font-medium
                                            {{ $isDropdownActive ? 'text-slate-900' : 'text-slate-700' }} hover:text-slate-900 hover:bg-slate-100">
                                    {{-- THAY ĐỔI: Thêm chỉ báo active --}}
                                    @if ($isDropdownActive)
                                        <span
                                            class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-1 bg-indigo-600 rounded-r-full"></span>
                                    @endif
                                    <span
                                        class="mr-3 text-lg {{ $isDropdownActive ? 'text-indigo-600' : 'text-slate-500' }} group-hover:text-slate-600">
                                        {!! $navItem['icon'] !!}
                                    </span>
                                    {{ $navItem['label'] }}
                                    <span class="ml-auto transition-transform duration-200"
                                        :class="{ 'rotate-90': openNav === '{{ $navItem['id'] }}' }">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16"
                                            height="16" fill="currentColor">
                                            <path
                                                d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z" />
                                        </svg>
                                    </span>
                                </button>
                                <ul x-show="openNav === '{{ $navItem['id'] }}'" x-transition
                                    class="pl-7 pr-2 py-1 space-y-1 mt-1" style="display: none;">
                                    @foreach ($navItem['children'] as $child)
                                        @php
                                            $isChildActive = false;
                                            foreach ($child['active_check'] as $prefix) {
                                                if (str_starts_with($currentRouteName, $prefix)) {
                                                    $isChildActive = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        <li>
                                            {{-- THAY ĐỔI: Giao diện active cho menu con --}}
                                            <a href="{{ route($child['route']) }}"
                                                class="relative flex items-center w-full py-1.5 px-3 text-sm rounded-md transition-colors duration-150
                                                    {{ $isChildActive ? 'text-indigo-600 font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                                                <span
                                                    class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-1 rounded-full {{ $isChildActive ? 'bg-indigo-600' : 'bg-slate-400' }}"></span>
                                                <span class="ml-4">{{ $child['label'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>
        </div>

        {{-- FOOTER (LOGOUT) --}}
        <div class="mt-auto p-4 border-t border-slate-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                    class="group flex items-center px-3 py-2 text-sm rounded-md font-medium text-slate-700 hover:text-red-600 hover:bg-red-50 transition-all duration-200 ease-in-out">
                    <span class="mr-3 text-lg text-slate-500 group-hover:text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                            fill="currentColor">
                            <path
                                d="M16,13H15V11h1a1,1,0,0,0,0-2H15V7h1a1,1,0,0,0,0-2H15V4a1,1,0,0,0-1-1H4A1,1,0,0,0,3,4V20a1,1,0,0,0,1,1h9a1,1,0,0,0,1-1V19h1a1,1,0,0,0,0-2H15V15h1a1,1,0,0,0,0-2ZM13,19H5V5h8V19Z" />
                            <path d="M21,12l-4-4v3H11v2h6v3Z" />
                        </svg>
                    </span>
                    Đăng xuất
                </a>
            </form>
        </div>
    </div>
</aside>