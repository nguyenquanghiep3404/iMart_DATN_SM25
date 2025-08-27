@extends('admin.layouts.app')

@section('title', 'Gán Shipper')

@section('content')
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gán Shipper</h1>
                    <p class="mt-1 text-sm text-gray-600">Quản lý và gán shipper cho các đơn hàng</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">Tổng đơn hàng: <span id="total-orders"
                            class="font-semibold text-blue-600">0</span></span>
                    <span class="text-sm text-gray-500">Đã chọn: <span id="selected-orders"
                            class="font-semibold text-green-600">0</span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bộ lọc</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-base font-medium text-gray-700 mb-3">Tỉnh/Thành phố</label>
                            <select id="province-filter"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                                <option value="">Tất cả tỉnh/thành</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-base font-medium text-gray-700 mb-3">Quận/Huyện</label>
                            <select id="district-filter"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                                <option value="">Tất cả quận/huyện</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-base font-medium text-gray-700 mb-3">Hạn giao</label>
                            <select id="deadline-filter"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                                <option value="">Tất cả</option>
                                <option value="today">Hôm nay</option>
                                <option value="tomorrow">Ngày mai</option>
                                <option value="overdue">Quá hạn</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-base font-medium text-gray-700 mb-3">Tìm kiếm</label>
                            <input type="text" id="search-input" placeholder="Mã đơn hàng..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <button id="select-all-btn"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-check-square mr-2"></i>
                                Chọn tất cả
                            </button>
                            <button id="clear-selection-btn"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-times mr-2"></i>
                                Bỏ chọn
                            </button>
                        </div>
                        <div class="text-sm text-gray-500">
                            Hiển thị: <span id="showing-count" class="font-medium">0</span> đơn hàng
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div id="loading-orders" class="hidden p-8 text-center">
                        <div
                            class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-blue-500 bg-blue-100">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Đang tải dữ liệu...
                        </div>
                    </div>

                    <div id="empty-orders" class="hidden p-8 text-center">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-box-open text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Không có đơn hàng nào</h3>
                        <p class="text-gray-500">Hiện tại không có đơn hàng nào cần gán shipper.</p>
                    </div>

                    <div id="order-list" class="divide-y divide-gray-200">
                        </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Gán Shipper</h3>
                </div>

                <div class="p-6">
                    <div id="selected-summary" class="mb-6 p-4 bg-blue-50 rounded-lg hidden">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Đã chọn</h4>
                        <p class="text-sm text-blue-700">Bạn đã chọn <span id="selected-count">0</span> đơn hàng để gán
                            shipper.</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-base font-medium text-gray-700 mb-3">Chọn Shipper</label>
                        <div class="relative">
                            <select id="shipper-select"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                                <option value="">Chọn shipper...</option>
                            </select>
                            <div id="shipper-loading" class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div id="shipper-info" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Thông tin Shipper</h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div>Tên: <span id="shipper-name" class="font-medium"></span></div>
                            <div>SĐT: <span id="shipper-phone" class="font-medium"></span></div>
                            <div>Khu vực: <span id="shipper-area" class="font-medium"></span></div>
                        </div>
                    </div>

                    <button id="assign-btn" disabled
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        <i class="fas fa-truck mr-2"></i>
                        Gán Shipper
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed top-4 right-4 z-50 hidden">
        <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
            {{-- Thay đổi ở đây: items-start -> items-center --}}
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div id="toast-icon" class="h-5 w-5"></div>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p id="toast-message" class="text-sm font-medium text-gray-900"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button id="toast-close"
                        class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        console.log('=== SCRIPT LOADED - VERSION 2 ===');

        // =================================================================
        // 1. CẤU HÌNH & BIẾN TOÀN CỤC
        // =================================================================
        const CONFIG = {
            routes: {
                getOrders: '{{ route('admin.shipper-assignment.orders') }}',
                getShippers: '{{ route('admin.shipper-assignment.shippers', ['province' => ':province']) }}',
                assignShipper: '{{ route('admin.shipper-assignment.assign') }}',
                getProvinces: '{{ route('admin.shipper-assignment.provinces') }}',
                getDistricts: '{{ route('admin.shipper-assignment.districts', ':province') }}'
            },
            csrfToken: '{{ csrf_token() }}'
        };

        let allFulfillments = [];
        let filteredFulfillments = [];
        let selectedFulfillments = new Set();
        let shippers = [];

        // DOM Elements
        const provinceFilter = document.getElementById('province-filter');
        const districtFilter = document.getElementById('district-filter');
        const deadlineFilter = document.getElementById('deadline-filter');
        const searchInput = document.getElementById('search-input');
        const orderList = document.getElementById('order-list');
        const loadingOrders = document.getElementById('loading-orders');
        const emptyOrders = document.getElementById('empty-orders');
        const shipperSelect = document.getElementById('shipper-select');
        const shipperLoading = document.getElementById('shipper-loading');
        const assignBtn = document.getElementById('assign-btn');
        const totalOrdersSpan = document.getElementById('total-orders');
        const selectedOrdersSpan = document.getElementById('selected-orders');
        const showingCountSpan = document.getElementById('showing-count');

        // =================================================================
        // 2. KHỞI TẠO & LẮNG NGHE SỰ KIỆN
        // =================================================================
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            initializePage();
        });

        function setupEventListeners() {
            provinceFilter.addEventListener('change', handleProvinceChange);
            ['change', 'input'].forEach(evt => {
                districtFilter.addEventListener(evt, applyFilters);
                deadlineFilter.addEventListener(evt, applyFilters);
                searchInput.addEventListener(evt, applyFilters);
            });
            shipperSelect.addEventListener('change', handleShipperChange);
            assignBtn.addEventListener('click', assignShipper);
            document.getElementById('select-all-btn').addEventListener('click', selectAllFiltered);
            document.getElementById('clear-selection-btn').addEventListener('click', clearSelection);
            document.getElementById('toast-close').addEventListener('click', hideToast);
        }

        async function initializePage() {
            showLoading();
            await loadProvinces();
            await loadAllFulfillments();
            applyFilters();
            hideLoading();
        }

        // =================================================================
        // 3. CÁC HÀM TẢI DỮ LIỆU (API CALLS)
        // =================================================================

        async function loadProvinces() {
            try {
                const response = await fetch(CONFIG.routes.getProvinces);
                const result = await response.json();
                if (result.success) {
                    const provinces = result.data;
                    provinceFilter.innerHTML = '<option value="">-- Chọn Tỉnh/Thành phố --</option>';
                    provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name;
                        provinceFilter.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading provinces:', error);
            }
        }

        async function loadDistricts(provinceId) {
            if (!provinceId) {
                districtFilter.innerHTML = '<option value="">Tất cả quận/huyện</option>';
                return;
            }
            try {
                const url = CONFIG.routes.getDistricts.replace(':province', provinceId);
                const response = await fetch(url);
                const result = await response.json();
                if (result.success) {
                    const districts = result.data;
                    districtFilter.innerHTML = '<option value="">Tất cả quận/huyện</option>';
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtFilter.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading districts:', error);
            }
        }

        async function loadShippers(provinceId) {
            shipperLoading.classList.remove('hidden');
            shipperSelect.innerHTML = '<option value="">Đang tải...</option>';
            if (!provinceId) {
                shipperSelect.innerHTML = '<option value="">-- Chọn tỉnh để xem shipper --</option>';
                shipperLoading.classList.add('hidden');
                shippers = [];
                updateAssignButton();
                return;
            }
            try {
                const url = CONFIG.routes.getShippers.replace(':province', provinceId);
                const response = await fetch(url);
                const result = await response.json();
                shipperSelect.innerHTML = '<option value="">-- Chọn shipper --</option>';
                if (result.success) {
                    shippers = result.data;
                    if (shippers.length === 0) {
                        shipperSelect.innerHTML = '<option value="">Không có shipper cho khu vực này</option>';
                    }
                    shippers.forEach(shipper => {
                        const option = document.createElement('option');
                        option.value = shipper.id;
                        option.textContent = `${shipper.name} (${shipper.area})`;
                        option.dataset.shipper = JSON.stringify(shipper);
                        shipperSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading shippers:', error);
                shipperSelect.innerHTML = '<option value="">Lỗi tải shipper</option>';
            } finally {
                shipperLoading.classList.add('hidden');
                updateAssignButton();
            }
        }

        async function loadAllFulfillments() {
            try {
                const response = await fetch(CONFIG.routes.getOrders);
                const result = await response.json();
                if (result.success) {
                    allFulfillments = result.data;
                } else {
                    showToast('Không thể tải danh sách gói hàng.', 'error');
                }
            } catch (error) {
                console.error('Error loading fulfillments:', error);
                showToast('Lỗi mạng khi tải gói hàng.', 'error');
            }
        }

        // =================================================================
        // 4. XỬ LÝ SỰ KIỆN & LOGIC GIAO DIỆN
        // =================================================================

        function handleProvinceChange() {
            const provinceId = provinceFilter.value;
            loadDistricts(provinceId);
            loadShippers(provinceId);
            applyFilters();
        }

        function handleShipperChange() {
            const selectedOption = shipperSelect.selectedOptions[0];
            const shipperInfoDiv = document.getElementById('shipper-info');
            if (selectedOption && selectedOption.dataset.shipper) {
                const shipper = JSON.parse(selectedOption.dataset.shipper);
                document.getElementById('shipper-name').textContent = shipper.name;
                document.getElementById('shipper-phone').textContent = shipper.phone;
                document.getElementById('shipper-area').textContent = shipper.area || 'N/A';
                shipperInfoDiv.classList.remove('hidden');
            } else {
                shipperInfoDiv.classList.add('hidden');
            }
            updateAssignButton();
        }

        function applyFilters() {
            const provinceId = provinceFilter.value;
            const districtId = districtFilter.value;
            const deadline = deadlineFilter.value;
            const searchTerm = searchInput.value.toLowerCase();

            filteredFulfillments = allFulfillments.filter(ff => {
                if (provinceId && ff.province_id != provinceId) return false;
                if (districtId && ff.district_id != districtId) return false;
                if (searchTerm &&
                    !ff.order_code.toLowerCase().includes(searchTerm) &&
                    !(ff.tracking_code && ff.tracking_code.toLowerCase().includes(searchTerm))
                ) {
                    return false;
                }
                if (deadline) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const ffDeadline = new Date(ff.deadline);
                    ffDeadline.setHours(0, 0, 0, 0);
                    const tomorrow = new Date(today);
                    tomorrow.setDate(today.getDate() + 1);
                    switch (deadline) {
                        case 'today':
                            if (ffDeadline.getTime() !== today.getTime()) return false;
                            break;
                        case 'tomorrow':
                            if (ffDeadline.getTime() !== tomorrow.getTime()) return false;
                            break;
                        case 'overdue':
                            if (ffDeadline >= today) return false;
                            break;
                    }
                }
                return true;
            });

            renderFulfillments();
            updateCounts();
        }

        function renderFulfillments() {
            if (!provinceFilter.value) {
                emptyOrders.querySelector('p').textContent =
                    'Vui lòng chọn Tỉnh/Thành phố để xem các gói hàng cần gán.';
                showEmpty();
                return;
            }
            if (filteredFulfillments.length === 0) {
                emptyOrders.querySelector('p').textContent = 'Không có gói hàng nào phù hợp với bộ lọc.';
                showEmpty();
                return;
            }
            hideEmpty();

            const html = filteredFulfillments.map(ff => {
                const displayCode = ff.tracking_code ?
                    `<span class="font-semibold text-blue-600">Mã VĐ: ${ff.tracking_code}</span>` :
                    `<span class="font-medium text-gray-900">Đơn hàng: ${ff.order_code}</span>`;
                return `
                <div class="order-item p-4 hover:bg-gray-50 cursor-pointer" data-fulfillment-id="${ff.id}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                data-fulfillment-id="${ff.id}" ${selectedFulfillments.has(ff.id) ? 'checked' : ''}>
                            <div>
                                ${displayCode} 
                                <div class="text-sm text-gray-500">${ff.customer_name} - ${ff.customer_phone}</div>
                                <div class="text-sm text-gray-500">${ff.address}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">${formatCurrency(ff.total_amount)}</div>
                            <div class="text-sm text-gray-500">Hạn: ${formatDate(ff.deadline)}</div>
                            <div class="text-xs ${getDeadlineClass(ff.deadline)}">${getDeadlineText(ff.deadline)}</div>
                        </div>
                    </div>
                </div>
                `
            }).join('');

            orderList.innerHTML = html;
            orderList.querySelectorAll('.order-checkbox').forEach(cb => cb.addEventListener('change', handleSelection));
            orderList.querySelectorAll('.order-item').forEach(item => item.addEventListener('click', e => {
                if (e.target.type !== 'checkbox') {
                    const checkbox = item.querySelector('.order-checkbox');
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            }));
        }

        // =================================================================
        // 5. XỬ LÝ CHỌN & GÁN SHIPPER
        // =================================================================

        function handleSelection(e) {
            const ffId = parseInt(e.target.dataset.fulfillmentId);
            e.target.checked ? selectedFulfillments.add(ffId) : selectedFulfillments.delete(ffId);
            updateCounts();
            updateSelectedSummary();
            updateAssignButton();
        }

        function selectAllFiltered() {
            filteredFulfillments.forEach(ff => selectedFulfillments.add(ff.id));
            orderList.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = true);
            updateCounts();
            updateSelectedSummary();
            updateAssignButton();
        }

        function clearSelection() {
            selectedFulfillments.clear();
            orderList.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
            updateCounts();
            updateSelectedSummary();
            updateAssignButton();
        }

        async function assignShipper() {
            if (selectedFulfillments.size === 0 || !shipperSelect.value) {
                showToast('Vui lòng chọn gói hàng và shipper.', 'error');
                return;
            }
            const fulfillmentIds = Array.from(selectedFulfillments);
            const shipperId = shipperSelect.value;
            assignBtn.disabled = true;
            assignBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang gán...';
            try {
                const response = await fetch(CONFIG.routes.assignShipper, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        fulfillment_ids: fulfillmentIds,
                        shipper_id: shipperId
                    })
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    showToast(result.message, 'success');
                    clearSelection();
                    await loadAllFulfillments();
                    applyFilters();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra khi gán shipper.', 'error');
                }
            } catch (error) {
                console.error('Error assigning shipper:', error);
                showToast('Lỗi mạng, không thể gán shipper.', 'error');
            } finally {
                assignBtn.disabled = false;
                assignBtn.innerHTML = '<i class="fas fa-truck mr-2"></i>Gán Shipper';
                updateAssignButton();
            }
        }

        // =================================================================
        // 6. CÁC HÀM TIỆN ÍCH & CẬP NHẬT GIAO DIỆN
        // =================================================================

        function updateCounts() {
            totalOrdersSpan.textContent = allFulfillments.length;
            selectedOrdersSpan.textContent = selectedFulfillments.size;
            showingCountSpan.textContent = filteredFulfillments.length;
        }

        function updateSelectedSummary() {
            const selectedSummaryDiv = document.getElementById('selected-summary');
            if (selectedFulfillments.size > 0) {
                document.getElementById('selected-count').textContent = selectedFulfillments.size;
                selectedSummaryDiv.classList.remove('hidden');
            } else {
                selectedSummaryDiv.classList.add('hidden');
            }
        }

        function updateAssignButton() {
            assignBtn.disabled = !(selectedFulfillments.size > 0 && shipperSelect.value !== '');
        }

        function showLoading() {
            loadingOrders.classList.remove('hidden');
            orderList.classList.add('hidden');
            emptyOrders.classList.add('hidden');
        }

        function hideLoading() {
            loadingOrders.classList.add('hidden');
            orderList.classList.remove('hidden');
        }

        function showEmpty() {
            emptyOrders.classList.remove('hidden');
            orderList.classList.add('hidden');
        }

        function hideEmpty() {
            emptyOrders.classList.add('hidden');
            orderList.classList.remove('hidden');
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        function formatDate(dateString) {
            return dateString ? new Date(dateString).toLocaleDateString('vi-VN') : 'N/A';
        }

        function getDeadlineClass(deadline) {
            if (!deadline) return 'text-gray-500';
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const deadlineDate = new Date(deadline);
            deadlineDate.setHours(0, 0, 0, 0);
            if (deadlineDate < today) return 'text-red-600 font-medium';
            if (deadlineDate.getTime() === today.getTime()) return 'text-yellow-600 font-medium';
            return 'text-green-600';
        }

        function getDeadlineText(deadline) {
            if (!deadline) return 'Không có hạn';
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const deadlineDate = new Date(deadline);
            deadlineDate.setHours(0, 0, 0, 0);
            if (deadlineDate < today) return 'Quá hạn';
            if (deadlineDate.getTime() === today.getTime()) return 'Hôm nay';
            const diffTime = deadlineDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return `Còn ${diffDays} ngày`;
        }

        let toastTimeout;

        function showToast(message, type = 'info') {
            clearTimeout(toastTimeout);
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').textContent = message;
            const icon = document.getElementById('toast-icon');

            // Thay đổi ở đây: h-5 w-5 -> w-full h-full
            if (type === 'success') icon.innerHTML =
                '<svg class="w-full h-full text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
            else if (type === 'error') icon.innerHTML =
                '<svg class="w-full h-full text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
            else icon.innerHTML =
                '<svg class="w-full h-full text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
            toast.classList.remove('hidden');
            toastTimeout = setTimeout(() => hideToast(), 5000);
        }

        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }
    </script>
@endpush