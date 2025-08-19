@extends('admin.layouts.app')

@section('title', 'Thùng rác Mã giảm giá')

@push('styles')
<style>
    /* Card styles */
    .card-custom { background: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    .card-custom-header { padding: 1.5rem; border-bottom: 1px solid #e5e7eb; }
    .card-custom-body { padding: 1.5rem; }
    .card-custom-footer { padding: 1rem 1.5rem; background-color: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 0.75rem 0.75rem; }

    /* Button styles */
    .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.5rem 1rem; display: inline-flex; align-items: center; justify-content: center; }
    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
    .btn-primary { background-color: #4f46e5; color: white; border: 1px solid #4f46e5; }
    .btn-primary:hover { background-color: #4338ca; border-color: #4338ca; }
    .btn-secondary { background-color: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }
    .btn-secondary:hover { background-color: #cbd5e1; }
    .btn-danger { background-color: #ef4444; color: white; border: 1px solid #ef4444; }
    .btn-danger:hover { background-color: #dc2626; border-color: #dc2626; }
    .btn-success { background-color: #10b981; color: white; border: 1px solid #10b981; }
    .btn-success:hover { background-color: #059669; border-color: #059669; }

    /* Form styles */
    .form-select, .form-input { border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; transition: all 0.2s ease-in-out; }
    .form-select:focus, .form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
    .input-group { display: flex; }
    .input-group .form-input { border-top-right-radius: 0; border-bottom-right-radius: 0; }
    .input-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }

    /* Table styles */
    .table-custom { width: 100%; color: #374151; }
    .table-custom th, .table-custom td { padding: 0.5rem 0.75rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; }
    .table-custom th:last-child, .table-custom td:last-child { white-space: nowrap; }
    .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; border-bottom-width: 2px; }
    .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.03); }
    .table-custom tbody tr:hover { background-color: #f9fafb; }

    /* Badge styles */
    .badge-custom { font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.75rem; border-radius: 9999px; display: inline-flex; align-items: center; }
    .badge-success-custom { background-color: #dcfce7; color: #166534; }
    .badge-warning-custom { background-color: #fef3c7; color: #92400e; }
    .badge-danger-custom { background-color: #fecaca; color: #991b1b; }
    .badge-blue-custom { background-color: #dbeafe; color: #1e40af; }

    /* Toast styles */
    .toast { position: fixed; top: 1rem; right: 1rem; z-index: 50; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transition: all 0.3s ease-in-out; }
    .toast.success { background-color: #dcfce7; color: #166534; border-left: 4px solid #10b981; }
    .toast.error { background-color: #fecaca; color: #991b1b; border-left: 4px solid #ef4444; }
    .toast.hide { transform: translateX(100%); opacity: 0; }

    /* Modal styles */
    .coupon-modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .coupon-modal.show { display: flex; align-items: center; justify-content: center; }
    .coupon-modal-content { background-color: white; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); width: 90%; max-width: 400px; }
    .coupon-modal-body { padding: 1.5rem; text-align: center; }
    .coupon-modal-footer { padding: 1rem 1.5rem; background-color: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 0.75rem 0.75rem; display: flex; justify-content: flex-end; gap: 0.75rem; }
    .animated-modal { animation: modalSlideIn 0.3s ease-out; }
    @keyframes modalSlideIn { from { opacity: 0; transform: translateY(-50px) scale(0.9); } to { opacity: 1; transform: translateY(0) scale(1); } }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-custom th, .table-custom td { padding: 0.5rem 0.5rem; font-size: 0.875rem; }
    }
</style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="w-full">
        
        {{-- TOAST NOTIFICATIONS CONTAINER --}}
        <div id="toast-container"></div>

        {{-- Flash messages as toasts --}}
        @if(session('success'))
            <div class="toast success">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="ml-auto text-green-600 hover:text-green-800" data-dismiss-target=".toast">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast error">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="ml-auto text-red-600 hover:text-red-800" data-dismiss-target=".toast">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="card-custom mb-6">
            <div class="card-custom-header">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Thùng rác Mã giảm giá</h1>
                        <nav aria-label="breadcrumb" class="mt-2">
                            <ol class="flex text-sm text-gray-500">
                                <li><a href="{{ route('admin.coupons.index') }}" class="text-indigo-600 hover:text-indigo-800">Danh sách Mã giảm giá</a></li>
                                <li class="text-gray-400 mx-2">/</li>
                                <li class="text-gray-700 font-medium" aria-current="page">Thùng rác</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại Danh sách
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="card-custom">
            <div class="card-custom-body">
                {{-- Filters Section --}}
                <form action="{{ route('admin.coupons.trash') }}" method="GET" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        {{-- Search Input --}}
                        <div class="flex-1">
                            <label for="search_coupon" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm mã giảm giá</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search_coupon" class="form-input" placeholder="Nhập mã hoặc mô tả..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Sort Filter --}}
                        <div class="w-full md:w-48">
                            <label for="filter_sort" class="block text-sm font-medium text-gray-700 mb-1">Sắp xếp theo</label>
                            <select id="filter_sort" name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="deleted_at" {{ request('sort') == 'deleted_at' ? 'selected' : '' }}>Ngày xóa mới nhất</option>
                                <option value="code" {{ request('sort') == 'code' ? 'selected' : '' }}>Mã A-Z</option>
                            </select>
                        </div>

                        {{-- Clear Filter --}}
                        @if(request()->filled(['search', 'sort']))
                            <div>
                                <a href="{{ route('admin.coupons.trash') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-2"></i>Xóa lọc
                                </a>
                            </div>
                        @endif
                    </div>
                </form>

                {{-- COUPONS TABLE --}}
                <div class="border border-gray-200 rounded-lg shadow-sm bg-white">
                    @if($trashedCoupons->isEmpty())
                        <div class="text-center py-16">
                            <i class="fas fa-ticket-alt fa-4x text-gray-300 mb-4"></i>
                            <h5 class="text-lg font-medium text-gray-500 mb-2">Thùng rác trống</h5>
                            @if(request()->filled('search'))
                                <p class="text-sm text-gray-400 mb-4">Không tìm thấy mã giảm giá nào với từ khóa "{{ request('search') }}"</p>
                                <a href="{{ route('admin.coupons.trash') }}" class="btn btn-secondary">Xóa tìm kiếm</a>
                            @else
                                <p class="text-sm text-gray-400">Chưa có mã giảm giá nào bị xóa.</p>
                            @endif
                        </div>
                    @else
                        <table class="table-custom table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th style="width: 20%;">Mã giảm giá</th>
                                    <th style="width: 15%;" class="hidden md:table-cell">Loại & Giá trị</th>
                                    <th style="width: 20%;" class="hidden md:table-cell">Ngày xóa</th>
                                    <th style="width: 15%;" class="hidden md:table-cell">Người xóa</th>
                                    <th style="width: 140px;" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trashedCoupons as $coupon)
                                <tr>
                                    <td>{{ $coupon->id }}</td>
                                    <td>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $coupon->code }}</p>
                                            <p class="text-xs text-gray-500">{{ Str::limit($coupon->description ?? '', 60, '...') }}</p>
                                            <div class="text-xs text-gray-500 mt-1 md:hidden">
                                                @if($coupon->type == 'percentage')
                                                    <span class="badge-custom badge-blue-custom">{{ $coupon->value }}%</span>
                                                @else
                                                    <span class="badge-custom badge-warning-custom">{{ number_format($coupon->value) }}đ</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 md:hidden mt-1">
                                                <span class="font-medium">Xóa:</span> {{ $coupon->deleted_at->format('d/m/Y H:i') }} - 
                                                <span class="font-medium">Bởi:</span> {{ $coupon->deletedBy->name ?? 'Không rõ' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600 hidden md:table-cell">
                                        @if($coupon->type == 'percentage')
                                            <span class="badge-custom badge-blue-custom">{{ $coupon->value }}%</span>
                                        @else
                                            <span class="badge-custom badge-warning-custom">{{ number_format($coupon->value) }}đ</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-gray-600 hidden md:table-cell">
                                        {{ $coupon->deleted_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-sm text-gray-600 hidden md:table-cell">
                                        {{ $coupon->deletedBy->name ?? 'Không rõ' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="flex justify-center items-center space-x-1" style="min-width: 120px;">
                                            <button type="button" class="btn btn-success btn-sm" title="Khôi phục" onclick="confirmRestore({{ $coupon->id }}, '{{ $coupon->code }}')">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                            
                                            <button type="button" class="btn btn-danger btn-sm" title="Xóa vĩnh viễn" onclick="confirmForceDelete({{ $coupon->id }}, '{{ $coupon->code }}')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            
            {{-- PAGINATION --}}
            @if ($trashedCoupons->hasPages())
            <div class="card-custom-footer">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Hiển thị {{ $trashedCoupons->firstItem() }} - {{ $trashedCoupons->lastItem() }} trong tổng số {{ $trashedCoupons->total() }} kết quả
                    </div>
                    <div>
                        {{ $trashedCoupons->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- RESTORE CONFIRMATION MODAL --}}
<div id="restoreModal" class="coupon-modal">
    <div class="coupon-modal-content animated-modal">
        <div class="coupon-modal-body text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <i class="fas fa-undo-alt text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Xác nhận khôi phục</h3>
            <p class="text-sm text-gray-500 mb-4">Bạn có chắc chắn muốn khôi phục mã giảm giá <strong id="couponCodeToRestore"></strong>?</p>
            <p class="text-xs text-gray-400">Mã giảm giá sẽ được đưa trở lại danh sách chính.</p>
        </div>
        <div class="coupon-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeRestoreModal()">Hủy</button>
            <button type="button" class="btn btn-success" id="confirmRestoreBtn" onclick="executeRestore()">
                <i class="fas fa-undo-alt mr-2"></i>Khôi phục
            </button>
        </div>
    </div>
</div>

{{-- FORCE DELETE CONFIRMATION MODAL --}}
<div id="forceDeleteModal" class="coupon-modal">
    <div class="coupon-modal-content animated-modal">
        <div class="coupon-modal-body text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Xác nhận xóa vĩnh viễn</h3>
            <p class="text-sm text-gray-500 mb-4">Bạn có chắc chắn muốn xóa vĩnh viễn mã giảm giá <strong id="couponCodeToForceDelete"></strong>?</p>
            <p class="text-xs text-red-500 font-medium">Hành động này không thể hoàn tác!</p>
        </div>
        <div class="coupon-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeForceDeleteModal()">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmForceDeleteBtn" onclick="executeForceDelete()">
                <i class="fas fa-times-circle mr-2"></i>Xóa vĩnh viễn
            </button>
        </div>
    </div>
</div>

{{-- HIDDEN FORMS --}}
<form id="restoreForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="forceDeleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide toasts after 5 seconds
    setTimeout(function() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(toast) {
            toast.classList.add('hide');
            setTimeout(function() {
                toast.remove();
            }, 300);
        });
    }, 5000);

    // Toast close buttons
    document.querySelectorAll('[data-dismiss-target]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-dismiss-target'));
            if (target) {
                target.classList.add('hide');
                setTimeout(function() {
                    target.remove();
                }, 300);
            }
        });
    });
});

// Global variables for modal actions
let couponIdToRestore = null;
let couponIdToForceDelete = null;

// RESTORE MODAL FUNCTIONS
function confirmRestore(couponId, couponCode) {
    couponIdToRestore = couponId;
    document.getElementById('couponCodeToRestore').textContent = couponCode;
    document.getElementById('restoreModal').classList.add('show');
}

function closeRestoreModal() {
    document.getElementById('restoreModal').classList.remove('show');
    couponIdToRestore = null;
}

function executeRestore() {
    if (couponIdToRestore) {
        const form = document.getElementById('restoreForm');
        form.action = `/admin/coupons/restore/${couponIdToRestore}`;
        form.submit();
    }
}

// FORCE DELETE MODAL FUNCTIONS
function confirmForceDelete(couponId, couponCode) {
    couponIdToForceDelete = couponId;
    document.getElementById('couponCodeToForceDelete').textContent = couponCode;
    document.getElementById('forceDeleteModal').classList.add('show');
}

function closeForceDeleteModal() {
    document.getElementById('forceDeleteModal').classList.remove('show');
    couponIdToForceDelete = null;
}

function executeForceDelete() {
    if (couponIdToForceDelete) {
        const form = document.getElementById('forceDeleteForm');
        form.action = `/admin/coupons/force-delete/${couponIdToForceDelete}`;
        form.submit();
    }
}

// Close modals when clicking outside
document.getElementById('restoreModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRestoreModal();
    }
});

document.getElementById('forceDeleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeForceDeleteModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRestoreModal();
        closeForceDeleteModal();
    }
});
</script>
@endpush
