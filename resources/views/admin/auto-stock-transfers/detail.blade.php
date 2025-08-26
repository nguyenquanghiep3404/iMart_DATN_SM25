@extends('admin.layouts.app')

@section('title', 'Chi tiết Phiếu Chuyển Kho Tự Động')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header với nút quay lại -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.auto-stock-transfers.manage') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Quay lại
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Chi tiết Phiếu Chuyển Kho Tự Động</h1>
            </div>
            <div class="flex space-x-2">
                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors" onclick="autoProcessTransfer()" id="auto-process-btn">
                    <i class="fas fa-cogs mr-1"></i>
                    Tự động xử lý
                </button>
                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" onclick="receiveTransfer()" id="receive-btn" style="display: none;">
                    <i class="fas fa-check mr-1"></i>
                    Đã nhận được hàng
                </button>
                <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" onclick="printTransfer()">
                    <i class="fas fa-print mr-1"></i>
                    In phiếu
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Thông tin chính -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Thông tin phiếu chuyển kho -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-blue-600"></i>
                        Thông tin phiếu chuyển kho
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu chuyển kho</label>
                            <p class="text-lg font-semibold text-blue-600" id="transfer-code">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" id="status-badge">
                                -
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày tạo</label>
                            <p class="text-gray-900" id="created-at">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Người tạo</label>
                            <p class="text-gray-900" id="created-by">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày xuất kho</label>
                            <p class="text-gray-900" id="shipped-at">-</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày nhận hàng</label>
                            <p class="text-gray-900" id="received-at">-</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <p class="text-gray-900 bg-gray-50 p-3 rounded-md" id="notes">-</p>
                    </div>
                </div>
            </div>

            <!-- Thông tin địa điểm -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                        Thông tin địa điểm
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Địa điểm gửi -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-arrow-up text-red-500 mr-2"></i>
                                Địa điểm gửi
                            </h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tên địa điểm</label>
                                    <p class="text-gray-900" id="from-location-name">-</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Loại</label>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" id="from-location-type">-</span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                                    <p class="text-gray-600 text-sm" id="from-location-address">-</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                                    <p class="text-gray-600 text-sm" id="from-location-phone">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Địa điểm nhận -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-arrow-down text-green-500 mr-2"></i>
                                Địa điểm nhận
                            </h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tên địa điểm</label>
                                    <p class="text-gray-900" id="to-location-name">-</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Loại</label>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" id="to-location-type">-</span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                                    <p class="text-gray-600 text-sm" id="to-location-address">-</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                                    <p class="text-gray-600 text-sm" id="to-location-phone">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chi tiết sản phẩm -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-box mr-2 text-purple-600"></i>
                        Chi tiết sản phẩm
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IMEI/Serial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn giá</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="products-table">
                                <!-- Sản phẩm sẽ được load bằng JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Timeline trạng thái -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2 text-orange-600"></i>
                        Lịch sử trạng thái
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul class="-mb-8" id="status-timeline">
                            <!-- Timeline sẽ được load bằng JavaScript -->
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>
                        Thống kê
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Tổng số sản phẩm:</span>
                            <span class="font-semibold text-gray-900" id="total-items">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Tổng giá trị:</span>
                            <span class="font-semibold text-gray-900" id="total-value">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Thời gian xử lý:</span>
                            <span class="font-semibold text-gray-900" id="processing-time">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Đơn hàng liên quan -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-shopping-cart mr-2 text-yellow-600"></i>
                        Đơn hàng liên quan
                    </h3>
                </div>
                <div class="p-6">
                    <div id="related-orders">
                        <!-- Đơn hàng liên quan sẽ được load bằng JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận tự động xử lý -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="auto-process-modal">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Xác nhận tự động xử lý</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Bạn có chắc chắn muốn tự động xử lý phiếu chuyển kho này không? Hành động này sẽ tự động xuất và nhận hàng.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirm-auto-process" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Xác nhận
                </button>
                <button id="cancel-auto-process" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Hủy bỏ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal quét IMEI/Serial -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="imei-scan-modal">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Quét IMEI/Serial</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeImeiModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Sản phẩm: <span id="scan-product-name"></span></p>
                        <p class="text-sm text-blue-600">SKU: <span id="scan-product-sku"></span></p>
                        <p class="text-sm text-blue-600">Cần quét: <span id="scan-required-quantity"></span> IMEI/Serial</p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Quét hoặc nhập IMEI/Serial</label>
                <div class="flex space-x-2">
                    <input type="text" id="imei-input" class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Quét hoặc nhập IMEI/Serial..." autocomplete="off">
                    <button type="button" onclick="addImeiSerial()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Danh sách IMEI/Serial đã quét</label>
                <div class="border border-gray-200 rounded-md max-h-40 overflow-y-auto">
                    <ul id="scanned-imei-list" class="divide-y divide-gray-200">
                        <!-- Danh sách IMEI/Serial sẽ được thêm vào đây -->
                    </ul>
                </div>
                <p class="text-sm text-gray-500 mt-1">Đã quét: <span id="scanned-count">0</span> / <span id="required-count">0</span></p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeImeiModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Hủy
                </button>
                <button type="button" onclick="saveScannedImei()" id="save-imei-btn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" disabled>
                    Lưu
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let transferId = null;
let transferData = null;

// Lấy ID từ URL
function getTransferIdFromUrl() {
    const pathParts = window.location.pathname.split('/');
    // URL format: /admin/auto-stock-transfers/{id}/detail
    // Lấy phần tử thứ 2 từ cuối (index -2)
    return pathParts[pathParts.length - 2];
}

// Load chi tiết phiếu chuyển kho
function loadTransferDetail() {
    transferId = getTransferIdFromUrl();
    
    if (!transferId) {
        showNotification('Không tìm thấy ID phiếu chuyển kho', 'error');
        return;
    }

    fetch(`/admin/auto-stock-transfers/${transferId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                transferData = data.data;
                displayTransferDetail(transferData);
                displayLocationInfo(transferData);
                displayProductItems(transferData.items);
                displayStatusTimeline(transferData);
                displayStatistics(transferData);
                displayRelatedOrders(transferData);
                updateActionButtons(transferData.status);
            } else {
                showNotification(data.message || 'Lỗi khi tải chi tiết phiếu chuyển kho', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Lỗi khi tải chi tiết phiếu chuyển kho', 'error');
        });
}

// Hiển thị thông tin phiếu chuyển kho
function displayTransferDetail(transfer) {
    document.getElementById('transfer-code').textContent = transfer.transfer_code;
    document.getElementById('created-at').textContent = formatDateTime(transfer.created_at);
    document.getElementById('created-by').textContent = transfer.created_by?.name || 'Hệ thống';
    document.getElementById('shipped-at').textContent = transfer.shipped_at ? formatDateTime(transfer.shipped_at) : 'Chưa xuất kho';
    document.getElementById('received-at').textContent = transfer.received_at ? formatDateTime(transfer.received_at) : 'Chưa nhận hàng';
    document.getElementById('notes').textContent = transfer.notes || 'Không có ghi chú';
    
    // Cập nhật trạng thái
    const statusBadge = document.getElementById('status-badge');
    const statusInfo = getStatusInfo(transfer.status);
    statusBadge.textContent = statusInfo.text;
    statusBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusInfo.class}`;
}

// Hiển thị thông tin địa điểm
function displayLocationInfo(transfer) {
    // Địa điểm gửi
    document.getElementById('from-location-name').textContent = transfer.from_location?.name || '-';
    document.getElementById('from-location-address').textContent = transfer.from_location?.address || '-';
    document.getElementById('from-location-phone').textContent = transfer.from_location?.phone || '-';
    
    const fromTypeElement = document.getElementById('from-location-type');
    const fromTypeInfo = getLocationTypeInfo(transfer.from_location?.type);
    fromTypeElement.textContent = fromTypeInfo.text;
    fromTypeElement.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${fromTypeInfo.class}`;
    
    // Địa điểm nhận
    document.getElementById('to-location-name').textContent = transfer.to_location?.name || '-';
    document.getElementById('to-location-address').textContent = transfer.to_location?.address || '-';
    document.getElementById('to-location-phone').textContent = transfer.to_location?.phone || '-';
    
    const toTypeElement = document.getElementById('to-location-type');
    const toTypeInfo = getLocationTypeInfo(transfer.to_location?.type);
    toTypeElement.textContent = toTypeInfo.text;
    toTypeElement.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${toTypeInfo.class}`;
}

// Hiển thị danh sách sản phẩm
function displayProductItems(items) {
    const tbody = document.getElementById('products-table');
    
    if (!items || items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    Không có sản phẩm nào
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = items.map(item => {
        const product = item.product_variant?.product;
        const price = item.product_variant?.price || 0;
        const total = price * item.quantity;
        
        // Kiểm tra xem sản phẩm có theo dõi IMEI/Serial không
        const hasSerialTracking = item.product_variant?.has_serial_tracking;
        let serialDisplay = '-';
        
        if (hasSerialTracking && item.serials && item.serials.length > 0) {
            const serialNumbers = item.serials.map(serial => serial.inventory_serial?.serial_number).filter(Boolean);
            if (serialNumbers.length > 0) {
                if (serialNumbers.length <= 3) {
                    // Hiển thị tất cả nếu ít hơn hoặc bằng 3
                    serialDisplay = `
                        <div class="space-y-1">
                            ${serialNumbers.map(serial => `
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-mono">${serial}</span>
                            `).join('')}
                        </div>
                    `;
                } else {
                    // Hiển thị 2 đầu và số còn lại
                    serialDisplay = `
                        <div class="space-y-1">
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-mono">${serialNumbers[0]}</span>
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-mono">${serialNumbers[1]}</span>
                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">+${serialNumbers.length - 2} khác</span>
                        </div>
                    `;
                }
            }
        } else if (hasSerialTracking) {
            serialDisplay = '<span class="text-yellow-600 text-xs">Chưa có IMEI/Serial</span>';
        }
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full object-cover" src="${product?.image || '/images/no-image.png'}" alt="${product?.name || 'Sản phẩm'}">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${product?.name || 'Không xác định'}</div>
                            <div class="text-sm text-gray-500">${item.product_variant?.variant_name || ''}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">${item.product_variant?.sku || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${item.quantity}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${serialDisplay}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    ${hasSerialTracking ? `
                        <button type="button" onclick="openImeiModal(${item.id}, '${product?.name || 'Sản phẩm'}', '${item.product_variant?.sku || ''}', ${item.quantity})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-qrcode mr-1"></i>
                            Quét IMEI
                        </button>
                    ` : '-'}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">${formatCurrency(price)}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${formatCurrency(total)}</td>
            </tr>
        `;
    }).join('');
}

// Hiển thị timeline trạng thái
function displayStatusTimeline(transfer) {
    const timeline = document.getElementById('status-timeline');
    const statuses = [
        { key: 'pending', text: 'Chờ xử lý', time: transfer.created_at },
        { key: 'dispatched', text: 'Đã xuất kho', time: transfer.shipped_at },
        { key: 'received', text: 'Đã nhận hàng', time: transfer.received_at }
    ];
    
    timeline.innerHTML = statuses.map((status, index) => {
        const isCompleted = getStatusOrder(transfer.status) >= getStatusOrder(status.key);
        const isLast = index === statuses.length - 1;
        
        return `
            <li class="${!isLast ? '-mb-8' : ''}">
                <div class="relative pb-8">
                    ${!isLast ? '<span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>' : ''}
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white ${
                                isCompleted ? 'bg-green-500' : 'bg-gray-300'
                            }">
                                <i class="fas fa-${isCompleted ? 'check' : 'clock'} text-white text-xs"></i>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                            <div>
                                <p class="text-sm text-gray-500">${status.text}</p>
                            </div>
                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                ${status.time ? formatDateTime(status.time) : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        `;
    }).join('');
}

// Hiển thị thống kê
function displayStatistics(transfer) {
    const totalItems = transfer.items?.reduce((sum, item) => sum + item.quantity, 0) || 0;
    const totalValue = transfer.items?.reduce((sum, item) => {
        const price = item.product_variant?.price || 0;
        return sum + (price * item.quantity);
    }, 0) || 0;
    
    document.getElementById('total-items').textContent = totalItems;
    document.getElementById('total-value').textContent = formatCurrency(totalValue);
    
    // Tính thời gian xử lý
    let processingTime = '-';
    if (transfer.received_at && transfer.created_at) {
        const start = new Date(transfer.created_at);
        const end = new Date(transfer.received_at);
        const diffHours = Math.round((end - start) / (1000 * 60 * 60));
        processingTime = `${diffHours} giờ`;
    }
    document.getElementById('processing-time').textContent = processingTime;
}

// Hiển thị đơn hàng liên quan
function displayRelatedOrders(transfer) {
    const container = document.getElementById('related-orders');
    
    // Trích xuất mã đơn hàng từ ghi chú
    const orderCode = extractOrderCodeFromNotes(transfer.notes);
    
    if (orderCode) {
        container.innerHTML = `
            <div class="border border-gray-200 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">${orderCode}</p>
                        <p class="text-xs text-gray-500">Đơn hàng gốc</p>
                    </div>
                    <a href="/admin/orders/${orderCode}" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
        `;
    } else {
        container.innerHTML = '<p class="text-sm text-gray-500">Không có đơn hàng liên quan</p>';
    }
}

// Cập nhật nút hành động
function updateActionButtons(status) {
    const autoProcessBtn = document.getElementById('auto-process-btn');
    const receiveBtn = document.getElementById('receive-btn');
    
    if (status === 'pending') {
        autoProcessBtn.style.display = 'inline-flex';
        receiveBtn.style.display = 'none';
    } else if (status === 'in_transit') {
        autoProcessBtn.style.display = 'none';
        receiveBtn.style.display = 'inline-flex';
    } else {
        autoProcessBtn.style.display = 'none';
        receiveBtn.style.display = 'none';
    }
}

// Tự động xử lý phiếu chuyển kho
function autoProcessTransfer() {
    document.getElementById('auto-process-modal').classList.remove('hidden');
}

// Nhận hàng ngay lập tức
function receiveTransfer() {
    if (!transferId) return;
    
    if (confirm('Bạn có chắc chắn muốn xác nhận đã nhận được hàng?')) {
        fetch(`/admin/auto-stock-transfers/${transferId}/receive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Đã xác nhận nhận hàng thành công', 'success');
                loadTransferDetail(); // Reload để cập nhật trạng thái
            } else {
                showNotification(data.message || 'Lỗi khi xác nhận nhận hàng', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Lỗi khi xác nhận nhận hàng', 'error');
        });
    }
}

// Xác nhận tự động xử lý
function confirmAutoProcess() {
    if (!transferId) return;
    
    fetch(`/admin/auto-stock-transfers/${transferId}/auto-process`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã tự động xử lý phiếu chuyển kho thành công', 'success');
            loadTransferDetail(); // Reload để cập nhật trạng thái
        } else {
            showNotification(data.message || 'Lỗi khi tự động xử lý', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi khi tự động xử lý phiếu chuyển kho', 'error');
    })
    .finally(() => {
        document.getElementById('auto-process-modal').classList.add('hidden');
    });
}

// In phiếu chuyển kho
function printTransfer() {
    window.print();
}

// Utility functions
function getStatusInfo(status) {
    const statusMap = {
        'pending': { text: 'Chờ xử lý', class: 'bg-yellow-100 text-yellow-800' },
        'dispatched': { text: 'Đã xuất kho', class: 'bg-blue-100 text-blue-800' },
        'in_transit': { text: 'Đang vận chuyển', class: 'bg-purple-100 text-purple-800' },
        'received': { text: 'Đã nhận hàng', class: 'bg-green-100 text-green-800' },
        'cancelled': { text: 'Đã hủy', class: 'bg-red-100 text-red-800' }
    };
    return statusMap[status] || { text: status, class: 'bg-gray-100 text-gray-800' };
}

function getLocationTypeInfo(type) {
    const typeMap = {
        'store': { text: 'Cửa hàng', class: 'bg-blue-100 text-blue-800' },
        'warehouse': { text: 'Kho', class: 'bg-green-100 text-green-800' },
        'service_center': { text: 'Trung tâm bảo hành', class: 'bg-purple-100 text-purple-800' }
    };
    return typeMap[type] || { text: type, class: 'bg-gray-100 text-gray-800' };
}

function getStatusOrder(status) {
    const orderMap = {
        'pending': 0,
        'dispatched': 1,
        'in_transit': 2,
        'received': 3,
        'cancelled': -1
    };
    return orderMap[status] || 0;
}

function extractOrderCodeFromNotes(notes) {
    if (!notes) return null;
    const match = notes.match(/Order:([A-Z0-9-]+)/);
    return match ? match[1] : null;
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function showNotification(message, type = 'info') {
    // Implement notification system
    alert(message);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    loadTransferDetail();
    
    // Modal event listeners
    document.getElementById('confirm-auto-process').addEventListener('click', confirmAutoProcess);
    document.getElementById('cancel-auto-process').addEventListener('click', function() {
        document.getElementById('auto-process-modal').classList.add('hidden');
    });
    
    // Close modal when clicking outside
    document.getElementById('auto-process-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
    
    // Close IMEI modal when clicking outside
    document.getElementById('imei-scan-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImeiModal();
        }
    });
    
    // Handle Enter key in IMEI input
    document.getElementById('imei-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addImeiSerial();
        }
    });
});

// Variables for IMEI scanning
let currentItemId = null;
let scannedImeiList = [];
let requiredQuantity = 0;

// Open IMEI scanning modal
function openImeiModal(itemId, productName, sku, quantity) {
    currentItemId = itemId;
    requiredQuantity = quantity;
    scannedImeiList = [];
    
    document.getElementById('scan-product-name').textContent = productName;
    document.getElementById('scan-product-sku').textContent = sku;
    document.getElementById('scan-required-quantity').textContent = quantity;
    document.getElementById('required-count').textContent = quantity;
    
    updateScannedList();
    document.getElementById('imei-scan-modal').classList.remove('hidden');
    document.getElementById('imei-input').focus();
}

// Close IMEI scanning modal
function closeImeiModal() {
    document.getElementById('imei-scan-modal').classList.add('hidden');
    document.getElementById('imei-input').value = '';
    currentItemId = null;
    scannedImeiList = [];
    requiredQuantity = 0;
}

// Add IMEI/Serial to list
function addImeiSerial() {
    const input = document.getElementById('imei-input');
    const imei = input.value.trim();
    
    if (!imei) {
        showNotification('Vui lòng nhập IMEI/Serial', 'error');
        return;
    }
    
    // Check if IMEI already exists
    if (scannedImeiList.includes(imei)) {
        showNotification('IMEI/Serial này đã được quét', 'error');
        input.value = '';
        input.focus();
        return;
    }
    
    // Check if we've reached the required quantity
    if (scannedImeiList.length >= requiredQuantity) {
        showNotification('Đã đủ số lượng IMEI/Serial cần thiết', 'error');
        return;
    }
    
    // Add to list
    scannedImeiList.push(imei);
    input.value = '';
    input.focus();
    
    updateScannedList();
    showNotification('Đã thêm IMEI/Serial thành công', 'success');
}

// Remove IMEI/Serial from list
function removeImeiSerial(index) {
    scannedImeiList.splice(index, 1);
    updateScannedList();
}

// Update scanned IMEI list display
function updateScannedList() {
    const listElement = document.getElementById('scanned-imei-list');
    const scannedCountElement = document.getElementById('scanned-count');
    const saveButton = document.getElementById('save-imei-btn');
    
    scannedCountElement.textContent = scannedImeiList.length;
    
    if (scannedImeiList.length === 0) {
        listElement.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500 text-center">Chưa có IMEI/Serial nào</li>';
        saveButton.disabled = true;
    } else {
        listElement.innerHTML = scannedImeiList.map((imei, index) => `
            <li class="px-4 py-3 flex items-center justify-between">
                <span class="text-sm font-mono text-gray-900">${imei}</span>
                <button type="button" onclick="removeImeiSerial(${index})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </li>
        `).join('');
        saveButton.disabled = scannedImeiList.length !== requiredQuantity;
    }
}

// Save scanned IMEI/Serial
function saveScannedImei() {
    if (!currentItemId || scannedImeiList.length !== requiredQuantity) {
        showNotification('Vui lòng quét đủ số lượng IMEI/Serial cần thiết', 'error');
        return;
    }
    
    // Send AJAX request to save IMEI/Serial
    fetch(`/admin/auto-stock-transfers/${transferId}/save-imei`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_variant_id: currentItemId,
            imei_serials: scannedImeiList
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã lưu IMEI/Serial thành công', 'success');
            closeImeiModal();
            loadTransferDetail(); // Reload để cập nhật hiển thị
        } else {
            showNotification(data.message || 'Lỗi khi lưu IMEI/Serial', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Lỗi khi lưu IMEI/Serial', 'error');
    });
}
</script>
@endpush

@push('styles')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
}
</style>
@endpush