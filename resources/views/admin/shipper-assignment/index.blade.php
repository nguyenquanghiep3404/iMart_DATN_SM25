@extends('admin.layouts.app')

@section('title', 'Gán Shipper')

@section('content')
<!-- Header -->
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gán Shipper</h1>
                <p class="mt-1 text-sm text-gray-600">Quản lý và gán shipper cho các đơn hàng</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Tổng đơn hàng: <span id="total-orders" class="font-semibold text-blue-600">0</span></span>
                <span class="text-sm text-gray-500">Đã chọn: <span id="selected-orders" class="font-semibold text-green-600">0</span></span>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Order List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm">
            <!-- Filters -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bộ lọc</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                         <label class="block text-base font-medium text-gray-700 mb-3">Tỉnh/Thành phố</label>
                         <select id="province-filter" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                             <option value="">Tất cả tỉnh/thành</option>
                         </select>
                     </div>
                     <div>
                         <label class="block text-base font-medium text-gray-700 mb-3">Quận/Huyện</label>
                         <select id="district-filter" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                             <option value="">Tất cả quận/huyện</option>
                         </select>
                     </div>
                     <div>
                         <label class="block text-base font-medium text-gray-700 mb-3">Hạn giao</label>
                         <select id="deadline-filter" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                             <option value="">Tất cả</option>
                             <option value="today">Hôm nay</option>
                             <option value="tomorrow">Ngày mai</option>
                             <option value="overdue">Quá hạn</option>
                         </select>
                     </div>
                     <div>
                         <label class="block text-base font-medium text-gray-700 mb-3">Tìm kiếm</label>
                         <input type="text" id="search-input" placeholder="Mã đơn hàng..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                     </div>
                </div>
            </div>

            <!-- Order List Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <button id="select-all-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-check-square mr-2"></i>
                            Chọn tất cả
                        </button>
                        <button id="clear-selection-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-2"></i>
                            Bỏ chọn
                        </button>
                    </div>
                    <div class="text-sm text-gray-500">
                        Hiển thị: <span id="showing-count" class="font-medium">0</span> đơn hàng
                    </div>
                </div>
            </div>

            <!-- Order List -->
            <div class="relative">
                <!-- Loading State -->
                <div id="loading-orders" class="hidden p-8 text-center">
                    <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-blue-500 bg-blue-100">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Đang tải dữ liệu...
                    </div>
                </div>

                <!-- Empty State -->
                <div id="empty-orders" class="hidden p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-box-open text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Không có đơn hàng nào</h3>
                    <p class="text-gray-500">Hiện tại không có đơn hàng nào cần gán shipper.</p>
                </div>

                <!-- Order List Container -->
                <div id="order-list" class="divide-y divide-gray-200">
                    <!-- Orders will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Shipper Assignment -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Gán Shipper</h3>
            </div>
            
            <div class="p-6">
                <!-- Selected Summary -->
                <div id="selected-summary" class="mb-6 p-4 bg-blue-50 rounded-lg hidden">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Đã chọn</h4>
                    <p class="text-sm text-blue-700">Bạn đã chọn <span id="selected-count">0</span> đơn hàng để gán shipper.</p>
                </div>

                <!-- Shipper Selection -->
                 <div class="mb-6">
                     <label class="block text-base font-medium text-gray-700 mb-3">Chọn Shipper</label>
                     <div class="relative">
                         <select id="shipper-select" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base py-3 px-4">
                             <option value="">Chọn shipper...</option>
                         </select>
                        <div id="shipper-loading" class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Shipper Info -->
                <div id="shipper-info" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Thông tin Shipper</h4>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div>Tên: <span id="shipper-name" class="font-medium"></span></div>
                        <div>SĐT: <span id="shipper-phone" class="font-medium"></span></div>
                        <div>Khu vực: <span id="shipper-area" class="font-medium"></span></div>
                    </div>
                </div>

                <!-- Assign Button -->
                <button id="assign-btn" disabled class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    <i class="fas fa-truck mr-2"></i>
                    Gán Shipper
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <div id="toast-icon" class="h-5 w-5"></div>
            </div>
            <div class="ml-3 w-0 flex-1">
                <p id="toast-message" class="text-sm font-medium text-gray-900"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button id="toast-close" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
console.log('=== SCRIPT LOADED ===');
// Configuration
const CONFIG = {
    routes: {
        getOrders: '{{ route("admin.shipper-assignment.orders") }}',
        getShippers: '{{ route("admin.shipper-assignment.shippers") }}',
        assignShipper: '{{ route("admin.shipper-assignment.assign") }}',
        getProvinces: '{{ route("admin.shipper-assignment.provinces") }}',
        getDistricts: '{{ route("admin.shipper-assignment.districts", ":province") }}'
    },
    csrfToken: '{{ csrf_token() }}'
};

// Global variables
let orders = [];
let filteredOrders = [];
let selectedOrders = new Set();
let shippers = [];
let provinces = [];
let districts = [];

// DOM elements
const orderList = document.getElementById('order-list');
const loadingOrders = document.getElementById('loading-orders');
const emptyOrders = document.getElementById('empty-orders');
const totalOrdersSpan = document.getElementById('total-orders');
const selectedOrdersSpan = document.getElementById('selected-orders');
const showingCountSpan = document.getElementById('showing-count');
const selectedSummary = document.getElementById('selected-summary');
const shipperSelect = document.getElementById('shipper-select');
const shipperLoading = document.getElementById('shipper-loading');
const shipperInfo = document.getElementById('shipper-info');
const assignBtn = document.getElementById('assign-btn');
const clearSelectionBtn = document.getElementById('clear-selection-btn');
const selectAllBtn = document.getElementById('select-all-btn');
const provinceFilter = document.getElementById('province-filter');
const districtFilter = document.getElementById('district-filter');
const deadlineFilter = document.getElementById('deadline-filter');
const searchInput = document.getElementById('search-input');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CONTENT LOADED ===');
    loadProvinces();
    loadShippers();
    loadOrders();
    setupEventListeners();
});

// Event listeners
function setupEventListeners() {
    // Filter events
    provinceFilter.addEventListener('change', handleProvinceChange);
    districtFilter.addEventListener('change', applyFilters);
    deadlineFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
    
    // Selection events
    selectAllBtn.addEventListener('click', selectAllFiltered);
    clearSelectionBtn.addEventListener('click', clearSelection);
    
    // Shipper events
    shipperSelect.addEventListener('change', handleShipperChange);
    assignBtn.addEventListener('click', assignShipper);
    
    // Toast events
    document.getElementById('toast-close').addEventListener('click', hideToast);
}

// Load data functions
async function loadProvinces() {
    console.log('=== LOADING PROVINCES ===');
    console.log('URL:', CONFIG.routes.getProvinces);
    console.log('CSRF Token:', CONFIG.csrfToken);
    
    try {
        const response = await fetch(CONFIG.routes.getProvinces, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (response.ok) {
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success && data.data) {
                provinces = data.data;
                console.log('Provinces loaded:', provinces);
                populateProvinceFilter();
            } else {
                console.error('Invalid response format:', data);
            }
        } else {
            const errorText = await response.text();
            console.error('Failed to load provinces:', response.status, response.statusText);
            console.error('Error response:', errorText);
        }
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

async function loadDistricts(provinceId) {
    try {
        const response = await fetch(CONFIG.routes.getDistricts.replace(':province', provinceId));
        const result = await response.json();
        
        if (result.success) {
            districts = result.data;
            populateDistrictFilter();
        }
    } catch (error) {
        console.error('Error loading districts:', error);
    }
}

async function loadShippers() {
    shipperLoading.classList.remove('hidden');
    
    try {
        const response = await fetch(CONFIG.routes.getShippers);
        const result = await response.json();
        
        if (result.success) {
            shippers = result.data;
            populateShipperSelect();
        }
    } catch (error) {
        console.error('Error loading shippers:', error);
        showToast('Không thể tải danh sách shipper', 'error');
    } finally {
        shipperLoading.classList.add('hidden');
    }
}

async function loadOrders() {
    showLoading();
    
    try {
        const response = await fetch(CONFIG.routes.getOrders);
        const result = await response.json();
        
        if (result.success) {
            orders = result.data;
            filteredOrders = [...orders];
            renderOrders();
            updateCounts();
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        showToast('Không thể tải danh sách đơn hàng', 'error');
    } finally {
        hideLoading();
    }
}

// Populate functions
function populateProvinceFilter() {
    console.log('=== POPULATING PROVINCE FILTER ===');
    console.log('Provinces array:', provinces);
    
    const filter = document.getElementById('province-filter');
    console.log('Filter element:', filter);
    
    if (!filter) {
        console.error('Province filter element not found!');
        return;
    }
    
    // Clear existing options except the first one
    while (filter.children.length > 1) {
        filter.removeChild(filter.lastChild);
    }
    
    provinces.forEach(province => {
        console.log('Adding province:', province);
        const option = document.createElement('option');
        option.value = province.id;
        option.textContent = province.name;
        filter.appendChild(option);
    });
    
    console.log('Province filter populated with', provinces.length, 'provinces');
}

function populateDistrictFilter() {
    const filter = document.getElementById('district-filter');
    
    // Clear existing options except the first one
    while (filter.children.length > 1) {
        filter.removeChild(filter.lastChild);
    }
    
    districts.forEach(district => {
        const option = document.createElement('option');
        option.value = district.id;
        option.textContent = district.name;
        filter.appendChild(option);
    });
}

function populateShipperSelect() {
    const select = document.getElementById('shipper-select');
    
    // Clear existing options except the first one
    while (select.children.length > 1) {
        select.removeChild(select.lastChild);
    }
    
    shippers.forEach(shipper => {
        const option = document.createElement('option');
        option.value = shipper.id;
        option.textContent = `${shipper.name} - ${shipper.phone}`;
        option.dataset.shipper = JSON.stringify(shipper);
        select.appendChild(option);
    });
}

// Event handlers
function handleProvinceChange() {
    const provinceId = provinceFilter.value;
    
    // Reset district filter
    districtFilter.innerHTML = '<option value="">Tất cả quận/huyện</option>';
    
    if (provinceId) {
        loadDistricts(provinceId);
    }
    
    applyFilters();
}

function handleShipperChange() {
    const selectedOption = shipperSelect.selectedOptions[0];
    
    if (selectedOption && selectedOption.dataset.shipper) {
        const shipper = JSON.parse(selectedOption.dataset.shipper);
        showShipperInfo(shipper);
        updateAssignButton();
    } else {
        hideShipperInfo();
        updateAssignButton();
    }
}

// Filter functions
function applyFilters() {
    const provinceId = provinceFilter.value;
    const districtId = districtFilter.value;
    const deadline = deadlineFilter.value;
    const searchTerm = searchInput.value.toLowerCase();
    
    filteredOrders = orders.filter(order => {
        // Province filter
        if (provinceId && order.province_id != provinceId) return false;
        
        // District filter
        if (districtId && order.district_id != districtId) return false;
        
        // Deadline filter
        if (deadline) {
            const today = new Date();
            const orderDeadline = new Date(order.deadline);
            
            switch (deadline) {
                case 'today':
                    if (orderDeadline.toDateString() !== today.toDateString()) return false;
                    break;
                case 'tomorrow':
                    const tomorrow = new Date(today);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    if (orderDeadline.toDateString() !== tomorrow.toDateString()) return false;
                    break;
                case 'overdue':
                    if (orderDeadline >= today) return false;
                    break;
            }
        }
        
        // Search filter
        if (searchTerm && !order.order_code.toLowerCase().includes(searchTerm)) return false;
        
        return true;
    });
    
    renderOrders();
    updateCounts();
}

// Render functions
function renderOrders() {
    if (filteredOrders.length === 0) {
        showEmpty();
        return;
    }
    
    hideEmpty();
    
    const html = filteredOrders.map(order => `
        <div class="order-item p-4 hover:bg-gray-50 cursor-pointer" data-order-id="${order.id}">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <input type="checkbox" class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                           data-order-id="${order.id}" ${selectedOrders.has(order.id) ? 'checked' : ''}>
                    <div>
                        <div class="font-medium text-gray-900">${order.order_code}</div>
                        <div class="text-sm text-gray-500">${order.customer_name} - ${order.customer_phone}</div>
                        <div class="text-sm text-gray-500">${order.address}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">${formatCurrency(order.total_amount)}</div>
                    <div class="text-sm text-gray-500">Hạn: ${formatDate(order.deadline)}</div>
                    <div class="text-xs ${getDeadlineClass(order.deadline)}">${getDeadlineText(order.deadline)}</div>
                </div>
            </div>
        </div>
    `).join('');
    
    orderList.innerHTML = html;
    
    // Add event listeners
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', handleOrderSelection);
    });
    
    document.querySelectorAll('.order-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.order-checkbox');
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    });
}

// Selection functions
function handleOrderSelection(e) {
    const orderId = parseInt(e.target.dataset.orderId);
    
    if (e.target.checked) {
        selectedOrders.add(orderId);
    } else {
        selectedOrders.delete(orderId);
    }
    
    updateCounts();
    updateSelectedSummary();
    updateAssignButton();
}

function selectAllFiltered() {
    filteredOrders.forEach(order => {
        selectedOrders.add(order.id);
    });
    
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    
    updateCounts();
    updateSelectedSummary();
    updateAssignButton();
}

function clearSelection() {
    selectedOrders.clear();
    
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    updateCounts();
    updateSelectedSummary();
    updateAssignButton();
}

// Update functions
function updateCounts() {
    totalOrdersSpan.textContent = orders.length;
    selectedOrdersSpan.textContent = selectedOrders.size;
    showingCountSpan.textContent = filteredOrders.length;
}

function updateSelectedSummary() {
    const selectedCount = document.getElementById('selected-count');
    selectedCount.textContent = selectedOrders.size;
    
    if (selectedOrders.size > 0) {
        selectedSummary.classList.remove('hidden');
    } else {
        selectedSummary.classList.add('hidden');
    }
}

function updateAssignButton() {
    const hasSelection = selectedOrders.size > 0;
    const hasShipper = shipperSelect.value !== '';
    
    assignBtn.disabled = !(hasSelection && hasShipper);
}

// Shipper info functions
function showShipperInfo(shipper) {
    document.getElementById('shipper-name').textContent = shipper.name;
    document.getElementById('shipper-phone').textContent = shipper.phone;
    document.getElementById('shipper-area').textContent = shipper.area || 'Chưa xác định';
    shipperInfo.classList.remove('hidden');
}

function hideShipperInfo() {
    shipperInfo.classList.add('hidden');
}

// Assignment function
async function assignShipper() {
    if (selectedOrders.size === 0 || !shipperSelect.value) {
        showToast('Vui lòng chọn đơn hàng và shipper', 'error');
        return;
    }
    
    const orderIds = Array.from(selectedOrders);
    const shipperId = shipperSelect.value;
    
    try {
        assignBtn.disabled = true;
        assignBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang gán...';
        
        const response = await fetch(CONFIG.routes.assignShipper, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_ids: orderIds,
                shipper_id: shipperId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Gán shipper thành công!', 'success');
            clearSelection();
            loadOrders(); // Reload orders
        } else {
            showToast(result.message || 'Có lỗi xảy ra khi gán shipper', 'error');
        }
    } catch (error) {
        console.error('Error assigning shipper:', error);
        showToast('Có lỗi xảy ra khi gán shipper', 'error');
    } finally {
        assignBtn.disabled = false;
        assignBtn.innerHTML = '<i class="fas fa-truck mr-2"></i>Gán Shipper';
        updateAssignButton();
    }
}

// Utility functions
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
    return new Date(dateString).toLocaleDateString('vi-VN');
}

function getDeadlineClass(deadline) {
    const today = new Date();
    const deadlineDate = new Date(deadline);
    
    if (deadlineDate < today) {
        return 'text-red-600 font-medium';
    } else if (deadlineDate.toDateString() === today.toDateString()) {
        return 'text-yellow-600 font-medium';
    } else {
        return 'text-green-600';
    }
}

function getDeadlineText(deadline) {
    const today = new Date();
    const deadlineDate = new Date(deadline);
    
    if (deadlineDate < today) {
        return 'Quá hạn';
    } else if (deadlineDate.toDateString() === today.toDateString()) {
        return 'Hôm nay';
    } else {
        const diffTime = deadlineDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return `Còn ${diffDays} ngày`;
    }
}

// Toast functions
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    const toastIcon = document.getElementById('toast-icon');
    
    toastMessage.textContent = message;
    
    // Set icon based on type
    if (type === 'success') {
        toastIcon.innerHTML = '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
    } else if (type === 'error') {
        toastIcon.innerHTML = '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
    } else {
        toastIcon.innerHTML = '<svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
    }
    
    toast.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}
</script>
@endpush