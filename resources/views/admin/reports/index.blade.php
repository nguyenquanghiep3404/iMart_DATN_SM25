@extends('admin.layouts.app')

@section('content')
    @include('admin.reports.layouts.head')

    <body class="bg-gray-100 p-4 sm:p-6 lg:p-8">
        <main class="max-w-screen-2xl mx-auto">
            <!-- Header -->
            @include('admin.reports.layouts.header')

            <!-- Main Content Block -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <!-- Filter Bar -->
                <div class="p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-wrap flex-grow">
                        <!-- Province Filter -->
                        <select id="province-filter"
                            class="w-full sm:w-48 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <!-- Options rendered by JS -->
                        </select>
                        <!-- District Filter -->
                        <select id="district-filter"
                            class="w-full sm:w-48 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            disabled>
                            <!-- Options rendered by JS -->
                        </select>
                        <!-- Location Type Filter -->
                        <select id="location-type-filter"
                            class="w-full sm:w-48 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="all">Tất cả loại địa điểm</option>
                            <option value="warehouse">Kho tổng</option>
                            <option value="store">Cửa hàng</option>
                        </select>
                        <!-- Search Input -->
                        <div class="relative flex-grow min-w-[200px]">
                            <input id="search-input" type="text" placeholder="Tìm SKU / Tên sản phẩm..."
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs text-gray-600 uppercase">
                            <tr>
                                <th class="px-6 py-3">Sản phẩm</th>
                                <th class="px-6 py-3">Địa điểm</th>
                                <th class="px-6 py-3 text-center">Loại Tồn</th>
                                <th class="px-6 py-3 text-center">Tồn Vật Lý</th>
                                <th class="px-6 py-3 text-center">Tạm Giữ</th>
                                <th class="px-6 py-3 text-center font-bold text-indigo-600">Tồn Khả Dụng</th>
                                <th class="px-6 py-3 text-right">Giá Vốn</th>
                                <th class="px-6 py-3 text-right">Giá Trị Tồn</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table-body" class="text-gray-700">
                            <!-- Data from API will be injected here -->
                        </tbody>
                    </table>
                    <div id="no-results" class="text-center py-16 text-gray-500 hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Không tìm thấy kết quả</h3>
                        <p class="mt-1 text-sm text-gray-500">Vui lòng thử lại với bộ lọc khác.</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div id="pagination-controls" class="p-4 flex items-center justify-between border-t border-gray-200">
                    <span id="pagination-summary" class="text-sm text-gray-600"></span>
                    <div class="flex items-center space-x-2">
                        <button id="prev-page-btn"
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">Trước</button>
                        <button id="next-page-btn"
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">Sau</button>
                    </div>
                </div>
            </div>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // --- STATE ---
                let appState = {
                    filters: {
                        search: '',
                        province_code: '',
                        district_code: '',
                        location_type: 'all',
                    },
                    pagination: {
                        currentPage: 1,
                        perPage: 10,
                        totalItems: 0,
                        lastPage: 1,
                    }
                };

                // --- DOM ---
                const tableBodyEl = document.getElementById('inventory-table-body');
                const noResultsEl = document.getElementById('no-results');
                const paginationSummaryEl = document.getElementById('pagination-summary');
                const prevPageBtn = document.getElementById('prev-page-btn');
                const nextPageBtn = document.getElementById('next-page-btn');

                const searchInputEl = document.getElementById('search-input');
                const provinceFilterEl = document.getElementById('province-filter');
                const districtFilterEl = document.getElementById('district-filter');
                const locationTypeFilterEl = document.getElementById('location-type-filter');

                // --- HELPERS ---
                const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(value);

                const getInventoryTypeBadge = (type) => {
                    switch (type) {
                        case 'new':
                            return `<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Mới</span>`;
                        case 'defective':
                            return `<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Lỗi</span>`;
                        case 'returned':
                            return `<span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Hàng trả</span>`;
                        default:
                            return '';
                    }
                };

                // --- FETCH DATA FROM API ---
                async function fetchInventoryData() {
                    // Gửi 'all' thành '' hoặc null nếu backend không xử lý 'all'
                    let locType = appState.filters.location_type;
                    if (locType === 'all') locType = '';

                    const params = new URLSearchParams({
                        page: appState.pagination.currentPage,
                        search: appState.filters.search,
                        province_code: appState.filters.province_code,
                        district_code: appState.filters.district_code,
                        location_type: locType,
                    });

                    try {
                        const response = await fetch(`/admin/reports/inventory/data?${params.toString()}`);
                        if (!response.ok) throw new Error('Lỗi tải dữ liệu');
                        const data = await response.json();

                        appState.pagination.totalItems = data.total;
                        appState.pagination.lastPage = data.last_page;

                        renderTable(data.data);
                        renderPagination();
                    } catch (error) {
                        console.error(error);
                        tableBodyEl.innerHTML =
                            `<tr><td colspan="8" class="text-center py-6 text-red-500">Không thể tải dữ liệu</td></tr>`;
                        noResultsEl.classList.add('hidden');
                        paginationSummaryEl.textContent = '';
                        prevPageBtn.disabled = true;
                        nextPageBtn.disabled = true;
                    }
                }

                // --- RENDER TABLE ---
                function renderTable(items) {
                    if (!items.length) {
                        tableBodyEl.innerHTML = '';
                        noResultsEl.classList.remove('hidden');
                        return;
                    }

                    noResultsEl.classList.add('hidden');

                    let html = '';
                    items.forEach(item => {
                        const product = item.product_variant?.product ?? {};
                        const availableQty = item.available_quantity ?? (item.quantity - (item.held_quantity ??
                            0));
                        const totalValue = availableQty * parseFloat(item.product_variant?.cost_price ?? 0);

                        html += `
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="font-semibold text-gray-900">${product.name ?? 'N/A'}</div>
                                            <div class="text-xs text-gray-500">SKU: ${item.product_variant?.sku ?? 'N/A'}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">${item.store_location?.name ?? 'N/A'}</td>
                                <td class="px-6 py-4 text-center">${getInventoryTypeBadge(item.inventory_type)}</td>
                                <td class="px-6 py-4 text-center">${item.quantity}</td>
                                <td class="px-6 py-4 text-center">${item.held_quantity ?? 0}</td>
                                <td class="px-6 py-4 text-center font-bold text-lg text-indigo-600">${availableQty}</td>
                                <td class="px-6 py-4 text-right">${formatCurrency(parseFloat(item.product_variant?.cost_price ?? 0))}</td>
                                <td class="px-6 py-4 text-right font-semibold">${formatCurrency(totalValue)}</td>
                            </tr>
                        `;
                    });

                    tableBodyEl.innerHTML = html;
                }

                // --- RENDER PAGINATION ---
                function renderPagination() {
                    const {
                        currentPage,
                        totalItems,
                        lastPage
                    } = appState.pagination;

                    const startItem = totalItems === 0 ? 0 : (currentPage - 1) * appState.pagination.perPage + 1;
                    const endItem = Math.min(currentPage * appState.pagination.perPage, totalItems);

                    paginationSummaryEl.textContent = `Hiển thị ${startItem}-${endItem} của ${totalItems} kết quả`;

                    prevPageBtn.disabled = currentPage <= 1;
                    nextPageBtn.disabled = currentPage >= lastPage;
                }

                // --- LOAD PROVINCES & DISTRICTS ---
                async function loadProvinces() {
                    try {
                        const res = await fetch(
                            `/admin/reports/inventory/provinces`); // Đường dẫn API riêng để lấy tỉnh thành
                        if (!res.ok) throw new Error('Lỗi tải tỉnh/thành');
                        const provinces = await res.json();

                        // Debug: kiểm tra data nhận được
                        console.log('Provinces:', provinces);

                        provinceFilterEl.innerHTML = `<option value="">Tất cả tỉnh/thành</option>` +
                            provinces.map(p =>
                                `<option value="${p.province_code || p.code}">${p.province_name || p.name_with_type || p.name}</option>`
                            ).join('');
                    } catch (e) {
                        console.error(e);
                        provinceFilterEl.innerHTML = `<option value="">Không tải được tỉnh/thành</option>`;
                    }
                }


                async function loadDistricts(provinceCode) {
                    if (!provinceCode) {
                        districtFilterEl.innerHTML = '<option value="">Tất cả quận/huyện</option>';
                        districtFilterEl.disabled = true;
                        return;
                    }

                    try {
                        const res = await fetch(`/admin/reports/inventory/districts?province_code=${provinceCode}`);

                        if (!res.ok) throw new Error('Lỗi tải quận/huyện');
                        const districts = await res.json();

                        districtFilterEl.innerHTML = `<option value="">Tất cả quận/huyện</option>` +
                            districts.map(d => `<option value="${d.district_code}">${d.district_name}</option>`)
                            .join('');
                        districtFilterEl.disabled = false;
                    } catch (e) {
                        console.error(e);
                        districtFilterEl.innerHTML = '<option value="">Không tải được quận/huyện</option>';
                        districtFilterEl.disabled = true;
                    }
                }


                // --- EVENT LISTENERS ---
                searchInputEl.addEventListener('input', e => {
                    appState.filters.search = e.target.value.trim();
                    appState.pagination.currentPage = 1;
                    fetchInventoryData();
                });

                provinceFilterEl.addEventListener('change', e => {
                    const provinceCode = e.target.value;
                    appState.filters.province_code = provinceCode;
                    appState.filters.district_code = '';
                    districtFilterEl.value = '';
                    appState.pagination.currentPage = 1;

                    loadDistricts(provinceCode);
                    fetchInventoryData();
                });

                districtFilterEl.addEventListener('change', e => {
                    appState.filters.district_code = e.target.value;
                    appState.pagination.currentPage = 1;
                    fetchInventoryData();
                });

                locationTypeFilterEl.addEventListener('change', e => {
                    appState.filters.location_type = e.target.value;
                    appState.pagination.currentPage = 1;
                    fetchInventoryData();
                });

                prevPageBtn.addEventListener('click', () => {
                    if (appState.pagination.currentPage > 1) {
                        appState.pagination.currentPage--;
                        fetchInventoryData();
                    }
                });

                nextPageBtn.addEventListener('click', () => {
                    if (appState.pagination.currentPage < appState.pagination.lastPage) {
                        appState.pagination.currentPage++;
                        fetchInventoryData();
                    }
                });

                // --- INITIAL LOAD ---
                loadProvinces();
                fetchInventoryData();
            });
        </script>
    @endsection
