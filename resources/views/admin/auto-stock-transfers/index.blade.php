@extends('admin.layouts.app')

@section('title', 'Quản lý Phiếu Chuyển Kho Tự Động')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-exchange-alt mr-2 text-blue-600"></i>
                Phiếu Chuyển Kho Tự Động
            </h3>
            <div class="flex space-x-2">
                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" onclick="loadStatistics()">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Thống kê
                </button>
                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors" onclick="refreshTable()">
                    <i class="fas fa-sync-alt mr-1"></i>
                    Làm mới
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Bộ lọc -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="status-filter">
                        <option value="">Tất cả</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="dispatched">Đã xuất kho</option>
                        <option value="received">Đã nhận hàng</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <div>
                    <label for="date-from" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="date-from">
                </div>
                <div>
                    <label for="date-to" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="date-to">
                </div>
                <div class="flex items-end">
                    <button type="button" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" onclick="applyFilters()">
                        <i class="fas fa-filter mr-1"></i>
                        Lọc
                    </button>
                </div>
            </div>

            <!-- Bảng dữ liệu -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="transfers-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Từ kho</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đến kho</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="transfers-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- Dữ liệu sẽ được load bằng JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <div id="pagination-container" class="flex justify-center mt-6">
                <!-- Pagination sẽ được load bằng JavaScript -->
            </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thống kê -->
<!-- Modal thống kê -->
<div id="statisticsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-5/6 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                Thống kê Phiếu Chuyển Kho Tự Động
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="closeStatisticsModal()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="py-4 max-h-96 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4" id="statistics-content">
                <!-- Nội dung thống kê sẽ được load bằng JavaScript -->
            </div>
        </div>
        <div class="flex justify-end pt-3 border-t border-gray-200">
            <button type="button" class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors" onclick="closeStatisticsModal()">Đóng</button>
        </div>
    </div>
</div>

<!-- Modal chi tiết -->
<div id="detailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                Chi tiết Phiếu Chuyển Kho
            </h5>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="closeDetailModal()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="py-4 max-h-96 overflow-y-auto" id="detail-content">
            <!-- Nội dung chi tiết sẽ được load bằng JavaScript -->
        </div>
        <div class="flex justify-end pt-3 border-t border-gray-200">
            <button type="button" class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors" onclick="closeDetailModal()">Đóng</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentFilters = {};

// Load dữ liệu khi trang được tải
$(document).ready(function() {
    loadTransfers();
});

// Load danh sách phiếu chuyển kho
function loadTransfers(page = 1) {
    currentPage = page;
    
    let params = {
        page: page,
        per_page: 15,
        ...currentFilters
    };
    
    $.ajax({
        url: '{{ route("admin.auto-stock-transfers.index") }}',
        method: 'GET',
        data: params,
        success: function(response) {
            if (response.success) {
                renderTransfersTable(response.data.data);
                renderPagination(response.data);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Lỗi khi tải dữ liệu');
        }
    });
}

// Render bảng dữ liệu
function renderTransfersTable(transfers) {
    let tbody = $('#transfers-tbody');
    tbody.empty();
    
    if (transfers.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center text-gray-500 py-8">
                    <i class="fas fa-inbox text-4xl mb-2 block"></i>
                    Không có dữ liệu
                </td>
            </tr>
        `);
        return;
    }
    
    transfers.forEach(function(transfer) {
        let statusBadge = getStatusBadge(transfer.status);
        let workflowBadge = getWorkflowBadge(transfer);
        let productInfo = transfer.items && transfer.items.length > 0 
            ? `${transfer.items[0].product_variant.product.name} (${transfer.items[0].quantity})` 
            : 'N/A';
        
        let actions = `
            <div class="flex space-x-1">
                <button type="button" class="inline-flex items-center px-2 py-1 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors" onclick="viewDetail(${transfer.id})" title="Xem chi tiết">
                    <i class="fas fa-eye"></i>
                </button>
        `;
        
        if (transfer.status === 'pending') {
            actions += `
                <button type="button" class="inline-flex items-center px-2 py-1 border border-green-300 text-xs font-medium rounded text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors" onclick="autoProcess(${transfer.id})" title="Tự động xử lý workflow">
                    <i class="fas fa-cogs"></i>
                </button>
                <button type="button" class="inline-flex items-center px-2 py-1 border border-red-300 text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors" onclick="cancelTransfer(${transfer.id})" title="Hủy">
                    <i class="fas fa-times"></i>
                </button>
            `;
        } else if (transfer.status === 'in_transit') {
            actions += `
                <button type="button" class="inline-flex items-center px-2 py-1 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors" onclick="receiveTransferFromList(${transfer.id})" title="Đã nhận được hàng">
                    <i class="fas fa-check"></i>
                </button>
            `;
        } else if (transfer.status === 'dispatched' || transfer.status === 'received') {
            actions += `
                <button type="button" class="inline-flex items-center px-2 py-1 border border-yellow-300 text-xs font-medium rounded text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-colors" onclick="cancelTransfer(${transfer.id})" title="Hủy và hoàn tồn kho">
                    <i class="fas fa-undo"></i>
                </button>
            `;
        }
        
        actions += '</div>';
        
        tbody.append(`
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${transfer.transfer_code}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${transfer.from_location ? transfer.from_location.name : 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${transfer.to_location ? transfer.to_location.name : 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${productInfo}</td>
                <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap">${workflowBadge}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDateTime(transfer.created_at)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${actions}</td>
            </tr>
        `);
    });
}

// Render phân trang
function renderPagination(data) {
    let container = $('#pagination-container');
    container.empty();
    
    if (data.last_page <= 1) return;
    
    let pagination = '<nav><ul class="pagination pagination-sm">';
    
    // Previous
    if (data.current_page > 1) {
        pagination += `<li class="page-item"><a class="page-link" href="#" onclick="loadTransfers(${data.current_page - 1})">‹</a></li>`;
    }
    
    // Pages
    for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
        let active = i === data.current_page ? 'active' : '';
        pagination += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadTransfers(${i})">${i}</a></li>`;
    }
    
    // Next
    if (data.current_page < data.last_page) {
        pagination += `<li class="page-item"><a class="page-link" href="#" onclick="loadTransfers(${data.current_page + 1})">›</a></li>`;
    }
    
    pagination += '</ul></nav>';
    container.html(pagination);
}

// Áp dụng bộ lọc
function applyFilters() {
    currentFilters = {
        status: $('#status-filter').val(),
        date_from: $('#date-from').val(),
        date_to: $('#date-to').val()
    };
    
    loadTransfers(1);
}

// Làm mới bảng
function refreshTable() {
    loadTransfers(currentPage);
}

// Load thống kê
function loadStatistics() {
    $.ajax({
        url: '{{ route("admin.auto-stock-transfers.statistics") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderStatistics(response.data);
                $('#statisticsModal').removeClass('hidden');
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Lỗi khi tải thống kê');
        }
    });
}

// Render thống kê
function renderStatistics(stats) {
    $('#statistics-content').html(`
        <div class="bg-blue-500 text-white rounded-lg p-6 text-center">
            <h3 class="text-3xl font-bold mb-2">${stats.total}</h3>
            <p class="text-blue-100">Tổng số phiếu</p>
        </div>
        <div class="bg-yellow-500 text-white rounded-lg p-6 text-center">
            <h3 class="text-3xl font-bold mb-2">${stats.pending}</h3>
            <p class="text-yellow-100">Chờ xử lý</p>
        </div>
        <div class="bg-orange-500 text-white rounded-lg p-6 text-center">
            <h3 class="text-3xl font-bold mb-2">${stats.shipped}</h3>
            <p class="text-orange-100">Đã xuất kho</p>
        </div>
        <div class="bg-green-500 text-white rounded-lg p-6 text-center">
            <h3 class="text-3xl font-bold mb-2">${stats.received}</h3>
            <p class="text-green-100">Đã hoàn thành</p>
        </div>
        <div class="bg-cyan-500 text-white rounded-lg p-6 text-center">
            <h3 class="text-3xl font-bold mb-2">${stats.today}</h3>
            <p class="text-cyan-100">Hôm nay</p>
        </div>
        <div class="bg-gray-500 text-white rounded-lg p-6 text-center">
            <h3 class="text-3xl font-bold mb-2">${stats.this_week}</h3>
            <p class="text-gray-100">Tuần này</p>
        </div>
        <div class="bg-indigo-500 text-white rounded-lg p-6 text-center">
            <h3 class="text-3xl font-bold mb-2">${stats.this_month}</h3>
            <p class="text-indigo-100">Tháng này</p>
        </div>
    `);
}

// Xem chi tiết
function viewDetail(id) {
    $.ajax({
        url: `{{ url('admin/auto-stock-transfers') }}/${id}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderDetail(response.data);
                $('#detailModal').removeClass('hidden');
            } else {
                showAlert('error', response.message);
            }
        },
        error: function() {
            showAlert('error', 'Lỗi khi tải chi tiết');
        }
    });
}

// Đóng modal chi tiết
function closeDetailModal() {
    $('#detailModal').addClass('hidden');
}

// Đóng modal thống kê
function closeStatisticsModal() {
    $('#statisticsModal').addClass('hidden');
}

// Render chi tiết
function renderDetail(transfer) {
    let itemsHtml = '';
    if (transfer.items && transfer.items.length > 0) {
        transfer.items.forEach(function(item) {
            itemsHtml += `
                <tr class="border-b border-gray-200">
                    <td class="py-2 px-3">${item.product_variant.product.name}</td>
                    <td class="py-2 px-3">${item.product_variant.sku}</td>
                    <td class="py-2 px-3">${item.quantity}</td>
                </tr>
            `;
        });
    }
    
    $('#detail-content').html(`
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h6 class="text-sm font-semibold text-gray-700 mb-3">Thông tin chung</h6>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="font-medium">Mã phiếu:</span><span>${transfer.transfer_code}</span></div>
                        <div class="flex justify-between"><span class="font-medium">Trạng thái:</span><span>${getStatusBadge(transfer.status)}</span></div>
                        <div class="flex justify-between"><span class="font-medium">Ngày tạo:</span><span>${formatDateTime(transfer.created_at)}</span></div>
                        <div class="flex justify-between"><span class="font-medium">Người tạo:</span><span>${transfer.created_by ? transfer.created_by.name : 'Hệ thống'}</span></div>
                    </div>
                </div>
            </div>
            <div>
                <h6 class="text-sm font-semibold text-gray-700 mb-3">Thông tin kho</h6>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="font-medium">Từ kho:</span><span>${transfer.from_location ? transfer.from_location.name : 'N/A'}</span></div>
                        <div class="flex justify-between"><span class="font-medium">Đến kho:</span><span>${transfer.to_location ? transfer.to_location.name : 'N/A'}</span></div>
                        <div class="flex justify-between"><span class="font-medium">Ghi chú:</span><span>${transfer.notes || 'Không có'}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6">
            <h6 class="text-sm font-semibold text-gray-700 mb-3">Danh sách sản phẩm</h6>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên sản phẩm</th>
                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>
        </div>
    `);
}

// Tự động xử lý workflow
function processWorkflow(id) {
    if (!confirm('Bạn có chắc chắn muốn tự động xử lý workflow cho phiếu chuyển kho này?')) {
        return;
    }
    
    $.ajax({
        url: `{{ url('admin/auto-stock-transfers') }}/${id}/auto-process`,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            showAlert(response.success ? 'success' : 'error', response.message);
            if (response.success) {
                loadTransfers(currentPage);
            }
        },
        error: function() {
            showAlert('error', 'Lỗi khi xử lý workflow phiếu chuyển kho');
        }
    });
}

// Tự động xử lý (legacy)
function autoProcess(id) {
    processWorkflow(id);
}

// Hủy và hoàn tồn kho
function cancelAndRestore(id) {
    if (!confirm('Bạn có chắc chắn muốn hủy phiếu chuyển kho này và hoàn tồn kho?')) {
        return;
    }
    
    $.ajax({
        url: `{{ url('admin/auto-stock-transfers') }}/${id}/cancel`,
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            showAlert(response.success ? 'success' : 'error', response.message);
            if (response.success) {
                loadTransfers(currentPage);
            }
        },
        error: function() {
            showAlert('error', 'Lỗi khi hủy và hoàn tồn kho');
        }
    });
}

// Hủy phiếu chuyển kho (legacy)
function cancelTransfer(id) {
    cancelAndRestore(id);
}

// Xem chi tiết phiếu chuyển kho
function viewDetail(id) {
    window.location.href = `{{ url('admin/auto-stock-transfers') }}/${id}/detail`;
}

// Nhận hàng ngay lập tức từ danh sách
function receiveTransferFromList(id) {
    if (confirm('Bạn có chắc chắn muốn xác nhận đã nhận được hàng?')) {
        $.ajax({
            url: `{{ url('admin/auto-stock-transfers') }}/${id}/receive`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showAlert(response.success ? 'success' : 'error', response.message);
                if (response.success) {
                    loadTransfers(currentPage);
                }
            },
            error: function() {
                showAlert('error', 'Lỗi khi xác nhận nhận hàng');
            }
        });
    }
}

// Helper functions
function getStatusBadge(status) {
    const statusMap = {
        'pending': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Chờ xử lý</span>',
        'dispatched': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Đã xuất kho</span>',
        'in_transit': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Đang vận chuyển</span>',
        'received': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Đã nhận hàng</span>',
        'cancelled': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Đã hủy</span>'
    };
    return statusMap[status] || '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Không xác định</span>';
}

function getWorkflowBadge(transfer) {
    let workflow = '';
    
    // Kiểm tra xem có thông tin đơn hàng không
    let hasOrderInfo = transfer.notes && transfer.notes.includes('Order:');
    
    if (transfer.status === 'pending') {
        workflow = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-clock mr-1"></i> Chờ xuất kho</span>';
    } else if (transfer.status === 'dispatched') {
        workflow = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-shipping-fast mr-1"></i> Đã xuất kho</span>';
    } else if (transfer.status === 'in_transit') {
        workflow = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-truck mr-1"></i> Đang vận chuyển</span>';
    } else if (transfer.status === 'received') {
        if (hasOrderInfo) {
            workflow = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i> Sẵn sàng giao hàng</span>';
        } else {
            workflow = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-warehouse mr-1"></i> Đã nhập kho</span>';
        }
    } else if (transfer.status === 'cancelled') {
        workflow = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i> Đã hủy</span>';
    }
    
    return workflow;
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
}

function showAlert(type, message) {
    const bgClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
    const iconClass = type === 'success' ? 'fa-check-circle text-green-400' : 'fa-exclamation-circle text-red-400';
    
    const alertHtml = `
        <div class="alert-notification border-l-4 p-4 mb-4 ${bgClass} rounded-md" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex rounded-md p-1.5 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('.p-6').first().prepend(alertHtml);
    
    // Tự động ẩn sau 5 giây
    setTimeout(function() {
        $('.alert-notification').fadeOut();
    }, 5000);
}
</script>
@endpush