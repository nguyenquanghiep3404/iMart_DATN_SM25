@extends('admin.layouts.app')

@section('title', 'Danh sách Gói hàng cần giao cho đơn vị thứ 3')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
/* Custom DataTable styles to work with Tailwind */
.dataTables_wrapper {
    font-family: inherit;
    padding: 1rem;
}
.dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
    margin: 0.75rem 0;
}
.dataTables_length {
    float: left;
}
.dataTables_filter {
    float: right;
}
.dataTables_length select, .dataTables_filter input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background: white;
    color: #374151;
}
.dataTables_filter input {
    margin-left: 0.5rem;
    width: 200px;
}
.dataTables_info {
    float: left;
    padding-top: 0.75rem;
    font-size: 0.875rem;
    color: #6b7280;
}
.dataTables_paginate {
    float: right;
}
.dataTables_paginate .paginate_button {
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
}
.dataTables_paginate .paginate_button:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #374151;
}
.dataTables_paginate .paginate_button.current {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}
.dataTables_paginate .paginate_button.disabled {
    color: #9ca3af;
    cursor: not-allowed;
}
.dataTables_paginate .paginate_button.disabled:hover {
    background: white;
    border-color: #d1d5db;
    color: #9ca3af;
}
#fulfillmentsTable_wrapper .dataTables_length label,
#fulfillmentsTable_wrapper .dataTables_filter label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}
.dataTables_empty {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    font-style: italic;
}
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Danh sách Gói hàng cần giao cho đơn vị thứ 3</h1>
        <p class="mt-2 text-sm text-gray-600">Quản lý các gói hàng cần giao cho đơn vị vận chuyển bên ngoài</p>
    </div>

    <!-- Filter và Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Lọc theo trạng thái -->
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter mr-1"></i>Lọc theo trạng thái
                </label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="packed">Đã đóng gói</option>
                    <option value="shipped">Đang vận chuyển</option>
                    <option value="delivered">Đã giao</option>
                </select>
            </div>
            
            <!-- Tìm kiếm -->
            <div class="sm:col-span-2">
                <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>Tìm kiếm
                </label>
                <input type="text" id="search-input" placeholder="Nhập mã vận đơn hoặc tên khách hàng..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            
            <!-- Nút reset -->
            <div class="flex items-end">
                <button id="reset-filter" class="w-full px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200 text-sm font-medium">
                    <i class="fas fa-undo mr-2"></i>Đặt lại
                </button>
            </div>
        </div>
    </div>

    <!-- Include bảng danh sách gói hàng -->
    @include('admin.external-shipping.partials.order-table', ['fulfillments' => $fulfillments])
</div>

<!-- Include modal chi tiết đơn hàng -->
@include('admin.external-shipping.partials.order-modal')



@push('scripts')
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<!-- Include modal scripts -->
@include('admin.external-shipping.partials.modal-scripts')
<script>
    $(document).ready(function() {
        // Khởi tạo DataTable
        var table = $('#fulfillmentsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Vietnamese.json"
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 25
        });
        
        // Xử lý filter và search
        function filterOrders() {
            var status = $('#status-filter').val();
            var search = $('#search-input').val();
            
            // Tạo URL với parameters
            var url = new URL(window.location.href);
            
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            
            if (search) {
                url.searchParams.set('search', search);
            } else {
                url.searchParams.delete('search');
            }
            
            // Reload trang với parameters mới
            window.location.href = url.toString();
        }
        
        // Event listeners
        $('#status-filter').on('change', function() {
            filterOrders();
        });
        
        $('#search-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                filterOrders();
            }
        });
        
        $('#reset-filter').on('click', function() {
            $('#status-filter').val('');
            $('#search-input').val('');
            
            // Reload trang không có parameters
            var url = new URL(window.location.href);
            url.searchParams.delete('status');
            url.searchParams.delete('search');
            window.location.href = url.toString();
        });
        
        // Giữ giá trị filter sau khi reload
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status')) {
            $('#status-filter').val(urlParams.get('status'));
        }
        if (urlParams.get('search')) {
            $('#search-input').val(urlParams.get('search'));
        }
    });
</script>
@endpush
@endsection