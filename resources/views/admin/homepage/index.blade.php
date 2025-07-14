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
            <button id="save-homepage-btn"
                class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                <i class="fas fa-save"></i>
                <span>Lưu tất cả thay đổi</span>
            </button>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column -->
            <div class="space-y-8">
                <!-- Banner Section -->
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Sắp xếp Banner Slider</h2>
                    <p class="text-sm text-gray-500 mb-4">Kéo thả để thay đổi thứ tự hiển thị của banner trên trang chủ.</p>
                    <ul id="banner-list" class="space-y-3">
                        <!-- Banner items injected by JS -->
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
                            <div data-id="{{ $block->id }}" class="draggable-item border rounded-xl bg-white">
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
                                        @forelse ($block->products as $product)
                                            <li class="draggable-item flex items-center space-x-4 p-2 border rounded-lg"
                                                data-id="{{ $product->id }}">
                                                <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                                                @php
                                                    $variant =
                                                        $product->variants->firstWhere('is_default', true) ??
                                                        $product->variants->first();
                                                @endphp
                                                <img src="{{ $variant?->image_url ?? '/images/no-image.png' }}"
                                                    class="w-10 h-10 object-cover rounded-md bg-gray-200">

                                                class="w-10 h-10 object-cover rounded-md bg-gray-200">
                                                <span class="font-semibold flex-grow text-sm">{{ $product->name }}</span>
                                                <button class="text-red-500 hover:text-red-700 text-xs remove-product-btn"
                                                    data-id="{{ $product->id }}">
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
                        <option value="top_selling">Top 10 sản phẩm bán chạy nhất</option>
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
                                <th class="px-4 py-2 text-center">Đã bán</th>
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
                banners: [{
                        id: 1,
                        title: 'Đại tiệc Sale Hè',
                        image_path: 'https://placehold.co/1200x400/3B82F6/FFFFFF?text=Sale+He+2025'
                    },
                    {
                        id: 4,
                        title: 'Back to School 2025',
                        image_path: 'https://placehold.co/1200x400/EC4899/FFFFFF?text=Back+to+School'
                    },
                    {
                        id: 2,
                        title: 'Laptop Gaming Giảm Sốc',
                        image_path: 'https://placehold.co/1200x400/10B981/FFFFFF?text=Laptop+Gaming'
                    }
                ],
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
                notificationModal.classList.remove('hidden');
                notificationModal.classList.remove('notification-success', 'notification-error');
                notificationModal.classList.add(`notification-${type}`);
                const autoClose = setTimeout(() => {
                    notificationModal.classList.add('hidden');
                }, 3000);
                notificationCloseBtn.addEventListener('click', () => {
                    clearTimeout(autoClose);
                    notificationModal.classList.add('hidden');
                }, {
                    once: true
                });
            };

            // --- RENDER FUNCTIONS ---
            const renderBannerList = () => {
                if (!bannerList) return;
                bannerList.innerHTML = mockData.banners.map(banner => `
                <li data-id="${banner.id}" draggable="true" class="draggable-item flex items-center space-x-4 p-3 border rounded-lg">
                    <iKinetic class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                    <img src="${banner.image_path}" class="w-24 h-10 object-cover rounded-md bg-gray-200">
                    <span class="font-semibold flex-grow">${banner.title}</span>
                    <button class="text-red-500 hover:text-red-700 text-sm"><i class="fas fa-times-circle"></i></button>
                </li>
            `).join('');
            };

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

            function formatCurrency(value) {
                return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(value);
            }

            const renderCategoryList = () => {
                if (!categoryList || !categorySortingSection) return;
                const categoriesToShow = [];
                console.log('Visible categories:', categoriesToShow); // Debug
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

            const renderProductBlocks = () => {
                if (!productBlocksContainer) return;
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
                                ${block.products.map(prod => `
                                            <li data-id="${prod.id}" draggable="true" class="draggable-item flex items-center space-x-4 p-2 border rounded-lg">
                                                <i class="fas fa-grip-vertical text-gray-400 cursor-grab"></i>
                                                <img src="${prod.image}" class="w-10 h-10 object-cover rounded-md bg-gray-200">
                                                <span class="font-semibold flex-grow text-sm">${prod.name}</span>
                                                <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-times-circle"></i></button>
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
                        setupDragAndDrop(list, block.products);
                    }
                });
                // GÁN SỰ KIỆN XOÁ BLOCK
                document.querySelectorAll('.delete-block-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const blockId = btn.dataset.id;
                        if (!blockId) return;

                        if (!confirm('Bạn có chắc chắn muốn xoá khối này?')) return;

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
                                showNotification('✅ Đã xoá khối sản phẩm', 'success');
                            })
                            .catch(err => {
                                console.error(err);
                                showNotification('❌ Xoá thất bại', 'error');
                            });
                    });
                });

                // GÁN SỰ KIỆN BẬT/TẮT KHỐI HIỂN THỊ
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

            // Hàm tải sản phẩm từ API
            async function loadProducts(query = '', filter = '') {
                try {
                    productSelectionList.innerHTML =
                        '<tr><td colspan="8" class="text-center py-4">Đang tải...</td></tr>';
                    const response = await fetch(
                        `/admin/homepage/products/search?q=${encodeURIComponent(query)}&filter=${filter}`);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const products = await response.json();
                    if (!Array.isArray(products)) {
                        throw new Error('Invalid product data');
                    }
                    renderProductSelection(products);
                } catch (err) {
                    console.error('Không tải được sản phẩm:', err);
                    productSelectionList.innerHTML =
                        '<tr><td colspan="8" class="text-center py-4 text-red-500">Không tải được sản phẩm.</td></tr>';
                }
            }

            // Hàm render danh sách sản phẩm trong modal
            function renderProductSelection(products) {
                productSelectionList.innerHTML = products.map(p => `
                <tr>
                    <td class="px-4 py-2">
                        <img src="${p.image}" class="w-12 h-12 object-cover rounded bg-gray-100" onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'" />
                    </td>
                    <td class="px-4 py-2 font-medium text-gray-800">${p.name}</td>
                    <td class="px-4 py-2 text-center">
                        ${p.sale_price && p.sale_price < p.price
                            ? `<span class="text-red-600 font-semibold">${formatCurrency(p.sale_price)}</span><br>
                                       <span class="line-through text-gray-400 text-xs">${formatCurrency(p.price)}</span>`
                            : `<span>${formatCurrency(p.price)}</span>`}
                    </td>
                    <td class="px-4 py-2 text-center">${p.sold_quantity ?? 0}</td>
                    <td class="px-4 py-2 text-center">${p.stock_quantity ?? 0}</td>
                    <td class="px-4 py-2 text-center">
                        ${p.is_featured ? '<span class="text-green-600 font-bold">✓</span>' : '<span class="text-gray-400">—</span>'}
                    </td>
                    <td class="px-4 py-2">${p.release_date ?? '—'}</td>
                    <td class="px-4 py-2 text-center">
                        <input type="checkbox" value="${p.id}" class="product-checkbox h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </td>
                </tr>
            `).join('');
            }

            // Sự kiện mở modal thêm sản phẩm
            document.addEventListener('click', function(e) {
                const addProductBtn = e.target.closest('.add-product-btn');
                if (addProductBtn) {
                    selectedBlockId = parseInt(addProductBtn.dataset.blockId);
                    productSearchInput.value = '';
                    filterType.value = '';
                    addProductModal.classList.remove('hidden');
                    loadProducts(); // Tải danh sách sản phẩm mặc định
                }
            });

            // Sự kiện tìm kiếm sản phẩm
            productSearchInput.addEventListener('input', () => {
                const query = productSearchInput.value.trim();
                const filter = filterType.value;
                loadProducts(query, filter);
            });

            // Sự kiện thay đổi bộ lọc
            filterType.addEventListener('change', () => {
                const query = productSearchInput.value.trim();
                const filter = filterType.value;
                loadProducts(query, filter);
            });

            // Sự kiện thêm sản phẩm vào khối
            confirmAddProductBtn.addEventListener('click', async () => {
                if (!selectedBlockId) {
                    showNotification('❌ Không tìm thấy khối sản phẩm.', 'error');
                    return;
                }

                const selectedIds = [...productSelectionList.querySelectorAll(
                        '.product-checkbox:checked')]
                    .map(input => parseInt(input.value));
                if (selectedIds.length === 0) {
                    showNotification('❌ Vui lòng chọn ít nhất một sản phẩm.', 'error');
                    return;
                }

                try {
                    const response = await fetch(
                        `/admin/homepage/product-blocks/${selectedBlockId}/products`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                            body: JSON.stringify({
                                product_ids: selectedIds
                            })
                        });
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();
                    const block = mockData.product_blocks.find(b => b.id === selectedBlockId);
                    if (block && data.products) {
                        block.products = data.products; // Cập nhật danh sách sản phẩm
                        renderProductBlocks();
                        showNotification('✅ Đã thêm sản phẩm', 'success');
                        addProductModal.classList.add('hidden');
                    }
                } catch (err) {
                    console.error('Lỗi khi thêm sản phẩm:', err);
                    showNotification('❌ Thêm sản phẩm thất bại', 'error');
                }
            });

            // Sự kiện đóng modal
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

            // --- EVENT LISTENERS ---
            if (categorySelectionList) {
                categorySelectionList.addEventListener('change', e => {
                    if (e.target.type === 'checkbox') {
                        const categoryId = parseInt(e.target.dataset.id);
                        const isActive = e.target.checked;
                        const totalSelected = categorySelectionList.querySelectorAll(
                            'input[type="checkbox"]:checked').length;
                        if (isActive && totalSelected > 7) {
                            e.target.checked = false;
                            showNotification('Bạn chỉ được chọn tối đa 7 danh mục hiển thị trên trang chủ.',
                                'error');
                            return;
                        }
                        let updated = false;
                        mockData.categories.forEach(cat => {
                            if (cat.id === categoryId) {
                                cat.show_on_homepage = isActive;
                                updated = true;
                            }
                            if (Array.isArray(cat.children)) {
                                cat.children.forEach(child => {
                                    if (child.id === categoryId) {
                                        child.show_on_homepage = isActive;
                                        updated = true;
                                    }
                                });
                            }
                        });
                        if (updated) renderCategoryList();
                    }
                });
            }

            // --- DRAG & DROP LOGIC ---
            function setupDragAndDrop(listElement, dataArray, onDropCallback) {
                if (!listElement) return;
                let draggedItem = null;
                listElement.addEventListener('dragstart', e => {
                    if (e.target.closest('.draggable-item')) {
                        draggedItem = e.target.closest('.draggable-item');
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
                    if (currentDragged) {
                        if (afterElement == null) {
                            listElement.appendChild(currentDragged);
                        } else {
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
                    if (onDropCallback) {
                        onDropCallback(dataArray);
                    }
                });
            }

            function getDragAfterElement(container, y) {
                if (!container) return null;
                const draggableElements = [...container.querySelectorAll('.draggable-item:not(.dragging)')];
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
            }

            const updateCategoryOrder = (sortedCategories) => {
                sortedCategories.forEach((cat, index) => {
                    const originalCat = mockData.categories.find(c => c.id === cat.id);
                    if (originalCat) originalCat.order = index + 1;
                    mockData.categories.forEach(parent => {
                        if (Array.isArray(parent.children)) {
                            parent.children.forEach(child => {
                                if (child.id === cat.id) {
                                    child.order = index + 1;
                                }
                            });
                        }
                    });
                });
                renderCategoryList();
            };

            // --- INITIALIZATION ---
            console.log('mockData:', mockData);
            renderBannerList();
            renderCategorySelectionList();
            renderCategoryList();
            renderProductBlocks();

            setupDragAndDrop(bannerList, mockData.banners);
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
            setupDragAndDrop(categoryList, getVisibleCategories(), updateCategoryOrder);
            setupDragAndDrop(productBlocksContainer, mockData.product_blocks, renderProductBlocks);

            // --- LƯU TOÀN BỘ THAY ĐỔI ---
            if (document.getElementById('save-homepage-btn')) {
                document.getElementById('save-homepage-btn').addEventListener('click', () => {
                    fetch("{{ route('admin.homepage.update') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content
                            },
                            body: JSON.stringify(mockData)
                        })
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            showNotification(data.message || '✅ Đã lưu thành công', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        })
                        .catch(err => {
                            console.error('Fetch error:', err);
                            showNotification('❌ Có lỗi xảy ra khi lưu dữ liệu', 'error');
                        });
                });
            }
        });
    </script>
@endpush
