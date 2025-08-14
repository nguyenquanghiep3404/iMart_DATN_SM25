@extends('admin.layouts.app')

@section('title', 'Quản lý Trang chủ')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #f8f9fa;
        }

        .draggable-item {
            cursor: grab;
            transition: background-color 0.2s ease;
        }

        .draggable-item:hover {
            background-color: #f9fafb;
        }

        .dragging {
            opacity: 0.5;
            background-color: #eef2ff;
            border: 1px dashed #6366f1;
        }

        /* Toggle Switch CSS */
        .toggle-checkbox {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-label {
            display: block;
            width: 37px;
            height: 24px;
            background: #ccc;
            border-radius: 9999px;
            left: 4px;
            position: relative;
            transition: background 0.3s;
            border: 1.5px solid #d1d5db;
        }

        .toggle-label::after {
            content: "";
            position: absolute;
            border: 1.5px solid #d1d5db;
            box-sizing: border-box;
            width: 19px;
            height: 19px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.3s;
        }

        .toggle-checkbox:checked+.toggle-label {
            background: #4f46e5;
        }

        .toggle-checkbox:checked+.toggle-label::after {
            transform: translateX(16px);
        }

        /* Modal Styles */
        .modal {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .modal-content {
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }

        .modal:not(.hidden) .modal-content {
            transform: scale(1);
        }

        /* Notification Modal Styles */
        .notification-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1200;
            transition: opacity 0.3s ease;
        }

        .notification-modal.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .notification-content {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        .notification-success .notification-content {
            border-left: 4px solid #10b981;
        }

        .notification-error .notification-content {
            border-left: 4px solid #ef4444;
        }

        .notification-content p {
            margin: 0.5rem 0;
            font-size: 1rem;
            color: #1f2937;
        }

        .notification-content button {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #4f46e5;
            color: white;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .notification-content button:hover {
            background: #4338ca;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-screen-2xl mx-auto p-4 md:p-8">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Quản lý Trang chủ</h1>
                <p class="text-gray-500 mt-1">Sắp xếp và quản lý các thành phần hiển thị trên trang chủ.</p>
            </div>
            {{-- <button id="save-homepage-btn"
                class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Lưu tất cả thay đổi</span>
            </button> --}}
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column -->
            <div class="space-y-8">
                <!-- Banner Section -->
                <!-- Banner Section -->
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Danh sách Banner Slider</h2>
                    <p class="text-sm text-gray-500 mb-4">Danh sách các banner hiển thị trên trang chủ, sắp xếp theo thứ tự.
                    </p>
                    <ul id="banner-list" class="space-y-3">
                        @forelse ($banners as $banner)
                            <li data-id="{{ $banner->id }}" draggable="true"
                                class="draggable-item flex items-center space-x-4 p-3 border rounded-lg {{ $banner->status !== 'active' ? 'opacity-50' : '' }}">
                                <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                                <img src="{{ $banner->desktopImage ? asset('storage/' . $banner->desktopImage->path) : '/images/no-image.png' }}"
                                    class="w-24 h-10 object-cover rounded-md bg-gray-200"
                                    onerror="this.src='/images/no-image.png'">
                                <span class="font-semibold flex-grow">
                                    {{ $banner->title }}
                                    @if ($banner->status !== 'active')
                                        <span class="text-red-500 text-xs">(Không hoạt động)</span>
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="text-center text-gray-400 text-sm py-4">Chưa có banner nào.</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Category Section -->
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Quản lý Danh mục Trang chủ</h2>
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Chọn các danh mục bạn muốn hiển thị trên trang chủ.</p>
                        <div id="category-selection-list" class="space-y-2 max-h-80 overflow-y-auto border p-3 rounded-lg">
                            @foreach ($categories as $cat)
                                <label class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox"
                                        class="category-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        data-id="{{ $cat->id }}" {{ $cat->show_on_homepage ? 'checked' : '' }}>
                                    <span class="text-gray-700 font-semibold">{{ $cat->name }}</span>
                                </label>
                                @foreach ($cat->children as $child)
                                    <label
                                        class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-50 cursor-pointer ml-6">
                                        <input type="checkbox"
                                            class="category-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            data-id="{{ $child->id }}" {{ $child->show_on_homepage ? 'checked' : '' }}>
                                        <span class="text-gray-700">{{ $child->name }}</span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    <div id="category-sorting-section" class="mt-6 hidden">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sắp xếp thứ tự</h3>
                        <p class="text-sm text-gray-500 mb-4">Kéo thả để thay đổi thứ tự các danh mục đã chọn.</p>
                        <ul id="category-list" class="space-y-3">
                            @php
                                $allVisibleCategories = collect();
                                foreach ($categories as $cat) {
                                    if ($cat->show_on_homepage) {
                                        $allVisibleCategories->push($cat);
                                    }
                                    foreach ($cat->children as $child) {
                                        if ($child->show_on_homepage) {
                                            $allVisibleCategories->push($child);
                                        }
                                    }
                                }
                                $allVisibleCategories = $allVisibleCategories->sortBy('order');
                            @endphp
                            @foreach ($allVisibleCategories as $cat)
                                <li data-id="{{ $cat->id }}" draggable="true"
                                    class="draggable-item flex items-center space-x-4 p-3 border rounded-lg">
                                    <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                                    <span class="font-semibold flex-grow">{{ $cat->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Featured Product Blocks Section -->
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <!-- Phần bên trái -->
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-2">Các khối Sản phẩm</h2>
                            <p class="text-sm text-gray-500">Các khối này sẽ hiển thị ở trang chủ.</p>
                        </div>

                        <!-- Nút Thêm khối mới bên phải -->
                        <button id="add-new-block-btn"
                            class="text-indigo-600 font-semibold text-sm flex items-center space-x-1">
                            <i class="fas fa-plus-circle"></i><span>Thêm khối mới</span>
                        </button>
                    </div>

                    <div id="product-blocks-container" class="space-y-6">
    @foreach ($productBlocks as $block)
        <div data-id="{{ $block->id }}" draggable="true" class="draggable-item border rounded-xl bg-white">
            <div class="flex justify-between items-center p-4 border-b">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                    <h3 class="font-bold text-gray-800">{{ $block->title }}</h3>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="relative inline-block w-10 align-middle select-none">
                        <input type="checkbox"
                            class="toggle-block-active absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                            data-id="{{ $block->id }}" {{ $block->is_visible ? 'checked' : '' }}>
                        <span
                            class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></span>
                    </label>
                    <button class="delete-block-btn text-gray-400 hover:text-red-500"
                        data-id="{{ $block->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <ul class="product-list space-y-3" data-block-id="{{ $block->id }}">
                    {{-- ✅ Sửa từ $block->products sang $block->productVariants --}}
                    @forelse ($block->productVariants as $variant)
                        <li class="draggable-item flex items-center space-x-4 p-2 border rounded-lg"
                            {{-- ✅ Sử dụng data-id là ID của biến thể --}}
                            data-id="{{ $variant->id }}">
                            <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                            {{-- ✅ Hiển thị ảnh của biến thể --}}
                            <img src="{{ $variant->primaryImage ? asset('storage/' . $variant->primaryImage->path) : '/images/no-image.png' }}"
                                class="w-10 h-10 object-cover rounded-md bg-gray-200">
                            {{-- ✅ Hiển thị tên sản phẩm từ mối quan hệ và dung lượng của biến thể --}}
                            <span class="font-semibold flex-grow text-sm">
                                {{ $variant->product->name }}
                                @php
                                    $capacityAttr = $variant->attributeValues->firstWhere('attribute.name', 'Dung lượng');
                                @endphp
                                @if ($capacityAttr)
                                    ({{ $capacityAttr->value }})
                                @endif
                            </span>
                            <button class="text-red-500 hover:text-red-700 text-xs remove-product-btn"
                                {{-- ✅ data-id của nút xóa cũng là ID của biến thể --}}
                                data-id="{{ $variant->id }}">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </li>
                    @empty
                        <li class="text-center text-gray-400 text-sm py-4">Chưa có sản phẩm nào.</li>
                    @endforelse
                </ul>
                <div class="mt-4 pt-4 border-t">
                    <button
                        class="text-indigo-600 font-semibold text-sm w-full text-left flex items-center space-x-1 add-product-btn"
                        data-id="{{ $block->id }}">
                        <i class="fas fa-search"></i><span>Tìm & Thêm sản phẩm...</span>
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

                </div>
            </div>
        </div>
    </div>

    <!-- Add New Block Modal -->
    <div id="add-block-modal"
        class="modal hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-md">
            <form id="add-block-form">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800">Thêm khối sản phẩm mới</h2>
                </div>
                <div class="p-8">
                    <label for="block-title" class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề khối <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="block-title" required
                        class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="VD: Hàng mới về">
                </div>
                <div class="p-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-2xl">
                    <button type="button" id="cancel-add-block-btn"
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Hủy</button>
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Thêm
                        khối</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Product to Block Modal -->
    <div id="add-product-modal" class="modal hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-6xl">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Chọn sản phẩm để thêm</h2>
                <button id="cancel-add-product-btn" class="text-gray-500 hover:text-gray-700 text-sm">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Nội dung modal -->
            <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
                <!-- 🔍 Bộ lọc + tìm kiếm -->
                <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-3 md:space-y-0">
                    <!-- 🔽 Dropdown bộ lọc -->
                    <select id="filter-type"
                        class="w-full md:w-1/3 py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Chọn bộ lọc sản phẩm</option>
                        <option value="all">Tất cả sản phẩm</option>
                        <option value="featured">Các sản phẩm nổi bật</option>
                        <option value="latest_10">Top 10 sản phẩm mới ra mắt</option>
                    </select>

                    <!-- 🔍 Ô tìm kiếm sản phẩm -->
                    <input type="text" id="product-search-input"
                        class="w-full md:w-2/3 py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Nhập tên sản phẩm để tìm...">
                </div>

                <!-- Bảng sản phẩm -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-sm text-left border rounded-lg">
                        <thead class="bg-gray-100 text-gray-700 font-semibold">
                            <tr>
                                <th class="px-4 py-2">Ảnh</th>
                                <th class="px-4 py-2">Tên sản phẩm</th>
                                <th class="px-4 py-2 text-center">Giá</th>
                                <th class="px-4 py-2 text-center">Tồn kho</th>
                                <th class="px-4 py-2 text-center">Nổi bật</th>
                                <th class="px-4 py-2">Ngày ra mắt</th>
                                <th class="px-4 py-2 text-center">Chọn</th>
                            </tr>
                        </thead>
                        <tbody id="product-selection-list" class="divide-y divide-gray-200">
                            <!-- Các dòng sản phẩm sẽ được inject bằng JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <div id="pagination-controls" class="mt-4 flex justify-center">
                    <nav aria-label="Pagination">
                        <ul class="inline-flex -space-x-px text-sm"></ul>
                    </nav>
                </div>
            </div>

            <div class="p-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-2xl">
                <button type="button" id="confirm-add-product-btn"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
                    Thêm vào khối
                </button>
            </div>
        </div>
    </div>





    <!-- Notification Modal -->
    <div id="notification-modal" class="notification-modal hidden">
        <div class="notification-content">
            <p id="notification-message"></p>
            <button id="notification-close-btn">Đóng</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- MOCK DATA ---
            let mockData = {
                banners: (() => {
                    try {
                        return @json($bannersForJs) || [];
                    } catch (e) {
                        console.error('Error parsing bannersForJs:', e);
                        return [];
                    }
                })(),
                categories: (() => {
                    try {
                        return @json($categoriesForJs) || [];
                    } catch (e) {
                        console.error('Error parsing categoriesForJs:', e);
                        return [];
                    }
                })(),
                product_blocks: (() => {
                    try {
                        return @json($productBlocksForJs) || [];
                    } catch (e) {
                        console.error('Error parsing productBlocksForJs:', e);
                        return [];
                    }
                })(),
            };

            // --- DOM ELEMENTS ---
            const bannerList = document.getElementById('banner-list');
            const categorySelectionList = document.getElementById('category-selection-list');
            const categorySortingSection = document.getElementById('category-sorting-section');
            const categoryList = document.getElementById('category-list');
            const productBlocksContainer = document.getElementById('product-blocks-container');
            const addBlockModal = document.getElementById('add-block-modal');
            const addBlockForm = document.getElementById('add-block-form');
            const addNewBlockBtn = document.getElementById('add-new-block-btn');
            const cancelAddBlockBtn = document.getElementById('cancel-add-block-btn');
            const notificationModal = document.getElementById('notification-modal');
            const notificationMessage = document.getElementById('notification-message');
            const notificationCloseBtn = document.getElementById('notification-close-btn');
            const addProductModal = document.getElementById('add-product-modal');
            const productSelectionList = document.getElementById('product-selection-list');
            const productSearchInput = document.getElementById('product-search-input');
            const filterType = document.getElementById('filter-type');
            const confirmAddProductBtn = document.getElementById('confirm-add-product-btn');
            const cancelAddProductBtn = document.getElementById('cancel-add-product-btn');

            // --- NOTIFICATION MODAL LOGIC ---
            const showNotification = (message, type = 'success') => {
                if (!notificationModal || !notificationMessage) return;
                notificationMessage.textContent = message;
                notificationModal.classList.remove('hidden', 'notification-success', 'notification-error');
                notificationModal.classList.add(`notification-${type}`);
                const autoClose = setTimeout(() => {
                    notificationModal.classList.add('hidden');
                }, 3000);
                if (notificationCloseBtn) {
                    notificationCloseBtn.addEventListener('click', () => {
                        clearTimeout(autoClose);
                        notificationModal.classList.add('hidden');
                    }, {
                        once: true
                    });
                }
            };

            // --- RENDER CATEGORY SELECTION LIST ---
            const renderCategorySelectionList = () => {
                if (!categorySelectionList) return;
                categorySelectionList.innerHTML = mockData.categories.map(cat => {
                    let html = `
                <label class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" data-id="${cat.id}" class="category-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" ${cat.show_on_homepage ? 'checked' : ''}>
                    <span class="text-gray-700 font-semibold">${cat.name}</span>
                </label>
            `;
                    if (Array.isArray(cat.children)) {
                        html += cat.children.map(child => `
                    <label class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-50 cursor-pointer ml-6">
                        <input type="checkbox" data-id="${child.id}" class="category-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" ${child.show_on_homepage ? 'checked' : ''}>
                        <span class="text-gray-700">${child.name}</span>
                    </label>
                `).join('');
                    }
                    return html;
                }).join('');
            };

            // --- RENDER CATEGORY LIST ---
            const renderCategoryList = () => {
                if (!categoryList || !categorySortingSection) return;
                const categoriesToShow = [];
                mockData.categories.forEach(cat => {
                    if (cat.show_on_homepage) categoriesToShow.push({
                        ...cat,
                        isChild: false
                    });
                    if (Array.isArray(cat.children)) {
                        cat.children.forEach(child => {
                            if (child.show_on_homepage) categoriesToShow.push({
                                ...child,
                                isChild: true
                            });
                        });
                    }
                });
                categoriesToShow.sort((a, b) => a.order - b.order);
                if (categoriesToShow.length > 0) {
                    categorySortingSection.classList.remove('hidden');
                    categoryList.innerHTML = categoriesToShow.map(cat => `
                <li data-id="${cat.id}" draggable="true" class="draggable-item flex items-center space-x-4 p-3 border rounded-lg">
                    <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                    <span class="font-semibold flex-grow">${cat.name}</span>
                </li>
            `).join('');
                } else {
                    categorySortingSection.classList.add('hidden');
                    categoryList.innerHTML = '';
                }
            };

            // --- CATEGORY CHECKBOX EVENT ---
            const setupCategoryCheckboxEvent = () => {
                if (!categorySelectionList) return;
                categorySelectionList.addEventListener('change', async (e) => {
                    if (e.target.classList.contains('category-checkbox')) {
                        const categoryId = parseInt(e.target.dataset.id);
                        const isActive = e.target.checked;
                        const totalSelected = categorySelectionList.querySelectorAll(
                            'input[type="checkbox"]:checked').length;

                        if (isActive && totalSelected > 7) {
                            e.target.checked = false;
                            showNotification(
                                'Bạn chỉ được chọn tối đa 7 danh mục hiển thị trên trang chủ.',
                                'error');
                            return;
                        }

                        try {
                            const response = await fetch(
                                `/admin/homepage/categories/${categoryId}/toggle`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        show_on_homepage: isActive
                                    })
                                });
                            const data = await response.json();
                            console.log('Dữ liệu sản phẩm trả về từ server:', data.products);

                            if (!response.ok || !data.success) {
                                e.target.checked = !isActive;
                                showNotification(data.message ||
                                    '❌ Cập nhật trạng thái danh mục thất bại', 'error');
                                return;
                            }

                            mockData.categories.forEach(cat => {
                                if (cat.id === categoryId) cat.show_on_homepage = isActive;
                                if (Array.isArray(cat.children)) {
                                    cat.children.forEach(child => {
                                        if (child.id === categoryId) child
                                            .show_on_homepage = isActive;
                                    });
                                }
                            });

                            renderCategoryList();
                            showNotification(data.message ||
                                '✅ Cập nhật trạng thái danh mục thành công', 'success');
                        } catch (err) {
                            console.error('Lỗi khi cập nhật danh mục:', err);
                            e.target.checked = !isActive;
                            showNotification('❌ Lỗi kết nối máy chủ', 'error');
                        }
                    }
                });
            };

            // --- CATEGORY DRAG & DROP ---
            const getVisibleCategories = () => {
                const list = [];
                mockData.categories.forEach(cat => {
                    if (cat.show_on_homepage) list.push(cat);
                    if (Array.isArray(cat.children)) {
                        cat.children.forEach(child => {
                            if (child.show_on_homepage) list.push(child);
                        });
                    }
                });
                return list;
            };

            const setupCategoryDragAndDrop = () => {
                if (!categoryList) return;
                let draggedItem = null;

                categoryList.addEventListener('dragstart', e => {
                    draggedItem = e.target.closest('.draggable-item');
                    if (draggedItem && categoryList.contains(draggedItem)) {
                        setTimeout(() => draggedItem.classList.add('dragging'), 0);
                    }
                });

                categoryList.addEventListener('dragend', () => {
                    if (draggedItem) {
                        draggedItem.classList.remove('dragging');
                        draggedItem = null;
                    }
                });

                categoryList.addEventListener('dragover', e => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(categoryList, e.clientY);
                    const currentDragged = document.querySelector('.dragging');
                    if (currentDragged && categoryList.contains(currentDragged)) {
                        if (afterElement == null) {
                            categoryList.appendChild(currentDragged);
                        } else if (categoryList.contains(afterElement)) {
                            categoryList.insertBefore(currentDragged, afterElement);
                        }
                    }
                });

                categoryList.addEventListener('drop', async e => {
                    e.preventDefault();
                    if (!draggedItem) return;
                    const newOrderIds = [...categoryList.querySelectorAll('.draggable-item')].map(
                        item => parseInt(item.dataset.id));
                    const visibleCategories = getVisibleCategories();
                    visibleCategories.sort((a, b) => newOrderIds.indexOf(a.id) - newOrderIds
                        .indexOf(b.id));
                    visibleCategories.forEach((cat, index) => {
                        mockData.categories.forEach(parent => {
                            if (parent.id === cat.id) parent.order = index + 1;
                            if (Array.isArray(parent.children)) {
                                parent.children.forEach(child => {
                                    if (child.id === cat.id) child.order =
                                        index + 1;
                                });
                            }
                        });
                    });

                    try {
                        const response = await fetch('/admin/homepage/categories/update-order', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                category_ids: newOrderIds
                            })
                        });
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        const data = await response.json();
                        showNotification(data.message || '✅ Cập nhật thứ tự danh mục thành công',
                            'success');
                        renderCategoryList();
                    } catch (err) {
                        console.error('Lỗi cập nhật thứ tự danh mục:', err);
                        showNotification('❌ Cập nhật thứ tự danh mục thất bại', 'error');
                    }
                });
            };

            // --- HELPER FUNCTION FOR DRAG & DROP ---
            const getDragAfterElement = (container, y) => {
                if (!container) return null;
                const draggableElements = [...container.children].filter(child => child.classList.contains(
                    'draggable-item') && !child.classList.contains('dragging'));
                return draggableElements.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        return {
                            offset,
                            element: child
                        };
                    }
                    return closest;
                }, {
                    offset: Number.NEGATIVE_INFINITY
                }).element;
            };

            // --- FORMAT CURRENCY ---
            function formatCurrency(value) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(value);
            }

            // --- RENDER PRODUCT BLOCKS ---
            const renderProductBlocks = () => {
                if (!productBlocksContainer) return;

                // --- THÊM DÒNG NÀY ĐỂ DEBUG ---
                mockData.product_blocks.forEach(block => {
                    console.log(
                        `Block ID: ${block.id}, Số sản phẩm trước khi render: ${block.products.length}`
                    );
                });

                productBlocksContainer.innerHTML = mockData.product_blocks
                    .sort((a, b) => a.order - b.order)
                    .map(block => `
            <div data-id="${block.id}" draggable="true" class="draggable-item border rounded-xl bg-white">
                <div class="flex justify-between items-center p-4 border-b">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                        <h3 class="font-bold text-gray-800">${block.title}</h3>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="toggle" id="toggle-${block.id}" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" ${block.is_visible ? 'checked' : ''}/>
                            <label for="toggle-${block.id}" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                        <button class="delete-block-btn text-gray-400 hover:text-red-500" data-id="${block.id}"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="p-4">
                    <ul data-block-id="${block.id}" class="product-list space-y-3">
                        ${block.products.sort((a, b) => a.order - b.order).map(prod => `
                                    <li data-id="${prod.id}" draggable="true" class="draggable-item flex items-center space-x-4 p-2 border rounded-lg">
                                        <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                                        <img src="${prod.image}" class="w-10 h-10 object-cover rounded-md bg-gray-200">
                                        <span class="font-semibold flex-grow text-sm">${prod.name}</span>
                                        <button class="text-red-500 hover:text-red-700 text-xs remove-product-btn" data-id="${prod.id}"><i class="fas fa-times-circle"></i></button>
                                    </li>
                                `).join('')}
                        ${block.products.length === 0 ? `<li class="text-center text-gray-400 text-sm py-4">Chưa có sản phẩm nào.</li>` : ''}
                    </ul>
                    <div class="mt-4 pt-4 border-t">
                        <button class="text-indigo-600 font-semibold text-sm w-full text-left flex items-center space-x-1 add-product-btn" data-block-id="${block.id}">
                            <i class="fas fa-search"></i><span>Tìm & Thêm sản phẩm...</span>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

                document.querySelectorAll('.product-list').forEach(list => {
                    const blockId = parseInt(list.dataset.blockId);
                    const block = mockData.product_blocks.find(b => b.id === blockId);
                    if (block) {
                        setupDragAndDrop(list, block.products, () => renderProductBlocks());
                    }
                });

                setupDragAndDrop(productBlocksContainer, mockData.product_blocks, () => renderProductBlocks());

                document.querySelectorAll('.delete-block-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const blockId = btn.dataset.id;
                        if (!blockId) return;
                        if (!confirm('Bạn có chắc chắn muốn xóa khối này?')) return;
                        fetch(`/admin/homepage/product-blocks/${blockId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                }
                            })
                            .then(res => {
                                if (!res.ok) throw new Error(`Lỗi HTTP: ${res.status}`);
                                return res.json();
                            })
                            .then(data => {
                                mockData.product_blocks = mockData.product_blocks.filter(
                                    b => b.id != blockId);
                                renderProductBlocks();
                                showNotification('✅ Đã xóa khối sản phẩm', 'success');
                            })
                            .catch(err => {
                                console.error(err);
                                showNotification('❌ Xóa thất bại', 'error');
                            });
                    });
                });

                document.querySelectorAll('.toggle-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', () => {
                        const blockId = checkbox.id.replace('toggle-', '');
                        fetch(`/admin/homepage/blocks/${blockId}/toggle-visibility`, {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                }
                            })
                            .then(res => {
                                if (!res.ok) throw new Error(`Lỗi HTTP: ${res.status}`);
                                return res.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    showNotification(data.message ||
                                        '✅ Cập nhật trạng thái hiển thị thành công',
                                        'success');
                                    const block = mockData.product_blocks.find(b => b.id ==
                                        blockId);
                                    if (block) block.is_visible = data.is_visible;
                                    renderProductBlocks();
                                } else {
                                    checkbox.checked = !checkbox.checked;
                                    showNotification(
                                        '❌ Không thể cập nhật trạng thái hiển thị',
                                        'error');
                                }
                            })
                            .catch(err => {
                                console.error('Toggle visibility error:', err);
                                checkbox.checked = !checkbox.checked;
                                showNotification('❌ Lỗi kết nối máy chủ', 'error');
                            });
                    });
                });
            };

            // --- FETCH & RENDER PRODUCTS IN MODAL ---
            let selectedBlockId = null;
            let currentPage = 1; // Biến theo dõi trang hiện tại
            const perPage = 15; // 15 biến thể mỗi trang, giống FlashSaleController

            async function loadProducts(query = '', filter = '', page = 1) {
                try {
                    productSelectionList.innerHTML =
                        '<tr><td colspan="8" class="text-center py-4">Đang tải...</td></tr>';

                    const response = await fetch(
                        `/admin/homepage/products/search?q=${encodeURIComponent(query)}&filter=${encodeURIComponent(filter)}&page=${page}&per_page=${perPage}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }
                    );

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log('API Response:', data);

                    // Kiểm tra cấu trúc dữ liệu chính
                    if (!data || !Array.isArray(data.data)) {
                        throw new Error('Dữ liệu không hợp lệ: data không phải mảng');
                    }

                    // Cập nhật biến trang hiện tại
                    currentPage = data.current_page || 1;

                    // Hiển thị danh sách sản phẩm
                    renderProductSelection(data.data);

                    // Hiển thị phân trang với logic đã sửa
                    renderPagination(data.current_page || 1, data.last_page || 1);

                } catch (err) {
                    console.error('Không tải được sản phẩm:', err);
                    productSelectionList.innerHTML =
                        `<tr><td colspan="8" class="text-center py-4 text-red-500">Không tải được sản phẩm: ${err.message}</td></tr>`;
                }
            }

            function renderProductSelection(variants) {
                productSelectionList.innerHTML = variants.map(v => `
        <tr>
            <td class="px-4 py-2">
                <img src="${v.image}" class="w-12 h-12 object-cover rounded bg-gray-100"/>
            </td>
            <td class="px-4 py-2 font-medium text-gray-800">${v.name}</td>
            <td class="px-4 py-2 text-center">
                ${v.sale_price && v.sale_price < v.price
                    ? `<span class="text-red-600 font-semibold">${formatCurrency(v.sale_price)}</span><br>
                           <span class="line-through text-gray-400 text-xs">${formatCurrency(v.price)}</span>`
                    : `<span>${formatCurrency(v.price)}</span>`}
            </td>
            <td class="px-4 py-2 text-center">${v.stock_quantity ?? 0}</td>
            <td class="px-4 py-2 text-center">
                ${v.is_featured ? '<span class="text-green-600 font-bold">✓</span>' : '<span class="text-gray-400">—</span>'}
            </td>
            <td class="px-4 py-2">${v.release_date ?? '—'}</td>
            <td class="px-4 py-2 text-center">
                <input type="checkbox" value="${v.id}" class="product-checkbox h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
            </td>
        </tr>
    `).join('');
            }

            function renderPagination(currentPage, lastPage) {
                const paginationContainer = document.querySelector('#pagination-controls ul');
                if (!paginationContainer) return;

                paginationContainer.innerHTML = '';

                // Tạo nút Previous
                const prevButton = document.createElement('li');
                prevButton.innerHTML = `
        <button class="px-3 py-1 rounded-l border border-gray-300 ${currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50'}"
                ${currentPage === 1 ? 'disabled' : ''}>
            Previous
        </button>
    `;
                prevButton.querySelector('button').addEventListener('click', () => {
                    loadProducts(productSearchInput.value.trim(), filterType.value, currentPage - 1);
                });
                paginationContainer.appendChild(prevButton);

                // Tạo các nút số trang
                for (let i = 1; i <= lastPage; i++) {
                    const pageItem = document.createElement('li');
                    pageItem.innerHTML = `
            <button class="px-3 py-1 border border-gray-300 ${i === currentPage ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-gray-50'}">
                ${i}
            </button>
        `;
                    pageItem.querySelector('button').addEventListener('click', () => {
                        loadProducts(productSearchInput.value.trim(), filterType.value, i);
                    });
                    paginationContainer.appendChild(pageItem);
                }

                // Tạo nút Next
                const nextButton = document.createElement('li');
                nextButton.innerHTML = `
        <button class="px-3 py-1 rounded-r border border-gray-300 ${currentPage === lastPage ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50'}"
                ${currentPage === lastPage ? 'disabled' : ''}>
            Next
        </button>
    `;
                nextButton.querySelector('button').addEventListener('click', () => {
                    loadProducts(productSearchInput.value.trim(), filterType.value, currentPage + 1);
                });
                paginationContainer.appendChild(nextButton);
            }

            document.addEventListener('click', function(e) {
                const addProductBtn = e.target.closest('.add-product-btn');
                if (addProductBtn) {
                    selectedBlockId = parseInt(addProductBtn.dataset.blockId);
                    productSearchInput.value = '';
                    filterType.value = '';
                    currentPage = 1; // Reset trang khi mở modal
                    addProductModal.classList.remove('hidden');
                    loadProducts();
                }
            });

            productSearchInput.addEventListener('input', () => {
                currentPage = 1; // Reset về trang 1 khi tìm kiếm
                const query = productSearchInput.value.trim();
                const filter = filterType.value;
                loadProducts(query, filter, currentPage);
            });

            filterType.addEventListener('change', () => {
                currentPage = 1; // Reset về trang 1 khi thay đổi bộ lọc
                const query = productSearchInput.value.trim();
                const filter = filterType.value;
                loadProducts(query, filter, currentPage);
            });

            confirmAddProductBtn.addEventListener('click', async () => {
                // Kiểm tra xem đã chọn block sản phẩm chưa
                if (!selectedBlockId) {
                    showNotification('❌ Không tìm thấy khối sản phẩm.', 'error');
                    return;
                }

                // ✅ Lấy ID của các biến thể đã được chọn từ thuộc tính 'value'
                const selectedVariantIds = [...productSelectionList.querySelectorAll(
                        '.product-checkbox:checked')]
                    .map(input => parseInt(input.value));

                // Kiểm tra xem đã chọn ít nhất một sản phẩm chưa
                if (selectedVariantIds.length === 0) {
                    showNotification('❌ Vui lòng chọn ít nhất một sản phẩm.', 'error');
                    return;
                }

                try {
                    // Gửi yêu cầu POST để thêm sản phẩm vào block
                    const response = await fetch(
                        `/admin/homepage/product-blocks/${selectedBlockId}/products`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                            body: JSON.stringify({
                                // ✅ Gửi mảng các ID của biến thể
                                product_variant_ids: selectedVariantIds
                            })
                        });

                    // Xử lý lỗi nếu phản hồi không thành công
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    // Lấy dữ liệu từ phản hồi
                    const data = await response.json();

                    // Cập nhật dữ liệu và hiển thị lại giao diện
                    const block = mockData.product_blocks.find(b => b.id === selectedBlockId);
                    if (block && data.products) {
                        block.products = data.products;
                        renderProductBlocks();
                        showNotification('✅ Đã thêm sản phẩm', 'success');
                        addProductModal.classList.add('hidden'); // Ẩn modal sau khi thành công
                    }
                } catch (err) {
                    // Xử lý lỗi nếu có vấn đề trong quá trình fetch
                    console.error('Lỗi khi thêm sản phẩm:', err);
                    showNotification('❌ Thêm sản phẩm thất bại', 'error');
                }
            });

            cancelAddProductBtn.addEventListener('click', () => {
                addProductModal.classList.add('hidden');
            });

            // --- MODAL LOGIC ---
            const openNewBlockModal = () => {
                if (!addBlockForm || !addBlockModal) return;
                addBlockForm.reset();
                addBlockModal.classList.remove('hidden');
            };

            const closeNewBlockModal = () => {
                if (!addBlockModal) return;
                addBlockModal.classList.add('hidden');
            };

            if (addNewBlockBtn) addNewBlockBtn.addEventListener('click', openNewBlockModal);
            if (cancelAddBlockBtn) cancelAddBlockBtn.addEventListener('click', closeNewBlockModal);

            if (addBlockForm) {
                addBlockForm.addEventListener('submit', e => {
                    e.preventDefault();
                    const newTitle = document.getElementById('block-title')?.value.trim();
                    if (!newTitle) return;
                    fetch("{{ route('admin.homepage.blocks.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                            body: JSON.stringify({
                                title: newTitle
                            })
                        })
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            if (data && data.block) {
                                const newBlock = {
                                    id: data.block.id,
                                    title: data.block.title,
                                    is_visible: data.block.is_visible,
                                    order: data.block.order,
                                    products: []
                                };
                                mockData.product_blocks.push(newBlock);
                                renderProductBlocks();
                                closeNewBlockModal();
                                showNotification('✅ Đã thêm khối sản phẩm mới');
                            }
                        })
                        .catch(err => {
                            console.error('Lỗi khi thêm khối:', err);
                            showNotification('❌ Thêm khối thất bại', 'error');
                        });
                });
            }

            // --- DRAG & DROP LOGIC ---
            function setupDragAndDrop(listElement, dataArray, onDropCallback) {
                if (!listElement) {
                    console.error('Container không tồn tại:', listElement);
                    return;
                }
                let draggedItem = null;
                listElement.addEventListener('dragstart', e => {
                    draggedItem = e.target.closest('.draggable-item');
                    if (draggedItem && listElement.contains(draggedItem)) {
                        setTimeout(() => draggedItem.classList.add('dragging'), 0);
                    }
                });

                listElement.addEventListener('dragend', () => {
                    if (draggedItem) {
                        draggedItem.classList.remove('dragging');
                        draggedItem = null;
                    }
                });

                listElement.addEventListener('dragover', e => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(listElement, e.clientY);
                    const currentDragged = document.querySelector('.dragging');
                    if (currentDragged && listElement.contains(currentDragged)) {
                        if (afterElement == null) {
                            listElement.appendChild(currentDragged);
                        } else if (listElement.contains(afterElement)) {
                            listElement.insertBefore(currentDragged, afterElement);
                        }
                    }
                });

                listElement.addEventListener('drop', e => {
                    e.preventDefault();
                    if (!draggedItem) return;
                    const newOrderIds = [...listElement.querySelectorAll('.draggable-item')].map(item =>
                        parseInt(item.dataset.id));
                    dataArray.sort((a, b) => newOrderIds.indexOf(a.id) - newOrderIds.indexOf(b.id));
                    dataArray.forEach((item, index) => {
                        item.order = index + 1;
                    });
                    onDropCallback();
                    if (listElement.id === 'product-blocks-container') {
                        fetch("{{ route('admin.homepage.blocks.update-order') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content
                                },
                                body: JSON.stringify({
                                    block_ids: newOrderIds
                                })
                            })
                            .then(res => {
                                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                                return res.json();
                            })
                            .then(data => {
                                showNotification('✅ Cập nhật thứ tự khối sản phẩm thành công',
                                    'success');
                            })
                            .catch(err => {
                                console.error('Lỗi cập nhật thứ tự khối:', err);
                                showNotification('❌ Cập nhật thứ tự khối thất bại', 'error');
                            });
                    } else if (listElement.classList.contains('product-list')) {
                        const blockId = listElement.dataset.blockId;
                        const block = mockData.product_blocks.find(b => b.id == blockId);
                        if (block) {
                            block.products = dataArray;
                            block.products.forEach((prod, index) => {
                                prod.order = index + 1;
                            });
                            fetch(`{{ route('admin.homepage.blocks.products.update-order', ['blockId' => ':blockId']) }}`
                                    .replace(':blockId', blockId), {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]').content
                                        },
                                        body: JSON.stringify({
                                            product_ids: newOrderIds
                                        })
                                    })
                                .then(res => {
                                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                                    return res.json();
                                })
                                .then(data => {
                                    showNotification('✅ Cập nhật thứ tự sản phẩm thành công',
                                        'success');
                                })
                                .catch(err => {
                                    console.error('Lỗi cập nhật thứ tự sản phẩm:', err);
                                    showNotification('❌ Cập nhật thứ tự sản phẩm thất bại', 'error');
                                });
                        }
                    }
                });
            }

            function setupBannerDragAndDrop() {
                if (!bannerList) return;
                let draggedItem = null;
                bannerList.addEventListener('dragstart', e => {
                    draggedItem = e.target.closest('.draggable-item');
                    if (draggedItem && bannerList.contains(draggedItem)) {
                        setTimeout(() => draggedItem.classList.add('dragging'), 0);
                    }
                });
                bannerList.addEventListener('dragend', () => {
                    if (draggedItem) {
                        draggedItem.classList.remove('dragging');
                        draggedItem = null;
                    }
                });
                bannerList.addEventListener('dragover', e => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(bannerList, e.clientY);
                    const currentDragged = document.querySelector('.dragging');
                    if (currentDragged && bannerList.contains(currentDragged)) {
                        if (afterElement == null) {
                            bannerList.appendChild(currentDragged);
                        } else if (bannerList.contains(afterElement)) {
                            bannerList.insertBefore(currentDragged, afterElement);
                        }
                    }
                });
                bannerList.addEventListener('drop', e => {
                    e.preventDefault();
                    if (!draggedItem) return;
                    const newOrderIds = [...bannerList.querySelectorAll('.draggable-item')].map(item =>
                        parseInt(item.dataset.id));
                    mockData.banners.sort((a, b) => newOrderIds.indexOf(a.id) - newOrderIds.indexOf(b.id));
                    mockData.banners.forEach((banner, index) => {
                        banner.order = index + 1;
                    });
                    fetch('/admin/homepage/banners/update-order', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                            body: JSON.stringify({
                                banner_ids: newOrderIds
                            })
                        })
                        .then(res => {
                            if (!res.ok) throw new Error(`Lỗi HTTP: ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            showNotification('✅ Cập nhật thứ tự banner thành công', 'success');
                        })
                        .catch(err => {
                            console.error('Lỗi cập nhật thứ tự banner:', err);
                            showNotification('❌ Cập nhật thứ tự banner thất bại', 'error');
                        });
                });
            }

            // --- INITIALIZATION ---
            renderCategorySelectionList();
            renderCategoryList();
            setupCategoryCheckboxEvent();
            setupCategoryDragAndDrop();
            renderProductBlocks();
            setupBannerDragAndDrop();
        });
    </script>
@endpush
