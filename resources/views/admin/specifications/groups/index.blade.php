@extends('admin.layouts.app')

@section('title', 'Quản lý Nhóm Thông số')

@push('styles')
    {{-- Custom Styles inspired by TailwindCSS for a modern look --}}
    <style>
        .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); background-color: #fff; }
        .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
        .card-custom-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .card-custom-body { padding: 1.5rem; }
        .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; line-height: 1.25rem; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; line-height: 1rem; }
        .btn-primary { background-color: #4f46e5; color: white; } .btn-primary:hover { background-color: #4338ca; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background-color: #d1d5db; }
        .btn-danger { background-color: #ef4444; color: white; } .btn-danger:hover { background-color: #dc2626; }
        .btn-warning { background-color: #f59e0b; color: #1f2937; } .btn-warning:hover { background-color: #d97706; }
        .btn-outline-secondary { color: #4a5568; background-color: #fff; border: 1px solid #d1d5db; } .btn-outline-secondary:hover { background-color: #f9fafb; }
        .btn-default { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; border-left: 0; } .btn-default:hover { background-color: #d1d5db; }
        .form-input, .form-select { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
        .form-input:focus, .form-select:focus { border-color: #4f46e5; outline: 0; box-shadow: 0 0 0 0.2rem rgba(79,70,229,0.25); }
        .input-group { display: flex; }
        .input-group .form-input { border-top-right-radius: 0; border-bottom-right-radius: 0; }
        .input-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .table-custom { width: 100%; min-width: 600px; color: #374151; }
        .table-custom th, .table-custom td { padding: 0.75rem 1rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; white-space: nowrap; }
        .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; border-bottom-width: 2px; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.03); }
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal.show { display: flex; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
        .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 500px; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04); }
        .modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
        .modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .icon-spin { animation: spin 0.8s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
        .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
        .toast.hide { opacity: 0; transform: translateX(100%); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal.show .animated-modal { animation: fadeInScale 0.3s ease-out forwards; }
        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-footer.justify-center { justify-content: center; gap: 0.75rem; padding-top: 0; padding-bottom: 1.5rem; border-top: none; background-color: #fff; }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-full">
        
        {{-- TOAST NOTIFICATIONS CONTAINER --}}
        <div id="toast-container" class="toast-container">
            @if (session('success'))
                <div id="toast-success" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg"><i class="fas fa-check"></i></div>
                    <div class="ml-3 text-sm font-normal">{{ session('success') }}</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-success" aria-label="Close"><span class="sr-only">Close</span><i class="fas fa-times"></i></button>
                </div>
            @endif
            @if (session('error'))
                <div id="toast-error" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="ml-3 text-sm font-normal">{{ session('error') }}</div>
                    <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" data-dismiss-target="#toast-error" aria-label="Close"><span class="sr-only">Close</span><i class="fas fa-times"></i></button>
                </div>
            @endif
        </div>

        {{-- PAGE HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Nhóm Thông số</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.specifications.index') }}" class="text-indigo-600 hover:text-indigo-800">Thông số kỹ thuật</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Nhóm Thông số</li>
                </ol>
            </nav>
        </div>

        <div class="card-custom">
            <div class="card-custom-header">
                {{-- Responsive header layout --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                    <h3 class="card-custom-title">Danh sách Nhóm Thông số ({{ $groups->total() }})</h3>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.specification-groups.trashed') }}" class="btn btn-warning btn-sm" title="Thùng rác"><i class="fas fa-trash-alt mr-1"></i>Thùng rác</a>
                        <a href="{{ route('admin.specification-groups.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-2"></i>Thêm Nhóm mới</a>
                    </div>
                </div>
            </div>
            <div class="card-custom-body">
                {{-- FILTERS --}}
                <form action="{{ route('admin.specification-groups.index') }}" method="GET" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="search_group" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <div class="input-group">
                                <input type="text" id="search_group" name="search" class="form-input" placeholder="Tên nhóm thông số..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-default -ml-px" aria-label="Search"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="flex items-end gap-x-3">
                            <a href="{{ route('admin.specification-groups.index') }}" class="btn btn-secondary w-full md:w-auto">Xóa lọc</a>
                            <button type="submit" class="btn btn-primary w-full md:w-auto"><i class="fas fa-filter mr-2"></i>Lọc</button>
                        </div>
                    </div>
                </form>
                
                {{-- TABLE WRAPPER FOR HORIZONTAL SCROLLING --}}
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="table-custom table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th>Tên Nhóm</th>
                                <th class="text-center">Số lượng thông số</th>
                                <th class="text-center">Thứ tự</th>
                                <th>Ngày tạo</th>
                                <th style="width: 100px;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groups as $group)
                            <tr>
                                <td>{{ ($groups->currentPage() - 1) * $groups->perPage() + $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('admin.specification-groups.edit', $group) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $group->name }}</a>
                                </td>
                                <td class="text-center">{{ $group->specifications->count() }}</td>
                                <td class="text-center">{{ $group->order }}</td>
                                <td>{{ $group->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <div class="inline-flex space-x-1">
                                        <a href="{{ route('admin.specification-groups.edit', $group) }}" class="btn btn-primary btn-sm" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-danger btn-sm" title="Xóa" onclick="openModal('deleteModal{{ $group->id }}')"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            
                            {{-- DELETE CONFIRMATION MODAL --}}
                            <div id="deleteModal{{ $group->id }}" class="modal" tabindex="-1">
                                <div class="modal-content animated-modal">
                                    <form action="{{ route('admin.specification-groups.destroy', $group->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-body text-center p-6">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-4">
                                                <i class="fas fa-trash-alt fa-2x text-red-500"></i>
                                            </div>
                                            <h5 class="text-xl font-semibold text-gray-800">Chuyển vào thùng rác?</h5>
                                            <p class="text-gray-600 mt-2">Bạn có chắc chắn muốn chuyển nhóm thông số<br>"<strong>{{ $group->name }}</strong>" vào thùng rác không?</p>
                                            <p class="text-red-600 mt-2 text-sm">Lưu ý: Tất cả các thông số con thuộc nhóm này cũng sẽ được chuyển vào thùng rác.</p>
                                        </div>
                                        <div class="modal-footer justify-center">
                                            <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal{{ $group->id }}')">Hủy bỏ</button>
                                            <button type="submit" class="btn btn-danger">Đồng ý, chuyển đi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-gray-400"></i>
                                        <p class="text-lg font-medium">Không tìm thấy nhóm thông số nào.</p>
                                        <p class="text-sm">Hãy <a href="{{ route('admin.specification-groups.create') }}" class="text-indigo-600 hover:underline">thêm nhóm thông số mới</a> để bắt đầu.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($groups->hasPages())
            <div class="card-custom-footer">
                {{-- Responsive pagination layout --}}
                <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center w-full">
                    <p class="text-sm text-gray-700 leading-5">
                        Hiển thị từ <span class="font-medium">{{ $groups->firstItem() }}</span> đến <span class="font-medium">{{ $groups->lastItem() }}</span> trên tổng số <span class="font-medium">{{ $groups->total() }}</span> kết quả
                    </p>
                    <div>
                        {!! $groups->appends(request()->query())->links() !!}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // MODAL SCRIPT
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }
    }
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }
    }
    
    // GENERAL DOCUMENT EVENT LISTENERS
    document.addEventListener('DOMContentLoaded', function() {
        // Close modal on outside click
        window.addEventListener('click', function(event) {
            const openModal = document.querySelector('.modal.show');
            if (openModal && event.target == openModal) {
                closeModal(openModal.id);
            }
        });

        // Close modal on Escape key press
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    closeModal(openModal.id);
                }
            }
        });

        // TOAST NOTIFICATION SCRIPT
        const toasts = document.querySelectorAll('.toast');
        const hideToast = (toastElement) => {
            if (toastElement) {
                toastElement.classList.add('hide');
                setTimeout(() => toastElement.remove(), 350);
            }
        };
        toasts.forEach(toast => {
            const autoHideTimeout = setTimeout(() => hideToast(toast), 5000);
            const closeButton = toast.querySelector('[data-dismiss-target]');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    clearTimeout(autoHideTimeout);
                    const targetId = this.getAttribute('data-dismiss-target');
                    hideToast(document.querySelector(targetId));
                });
            }
        });
    });
</script>
@endpush
