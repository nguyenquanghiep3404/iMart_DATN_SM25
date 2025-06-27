@extends('admin.layouts.app')

@section('title', 'Quản lý Thuộc tính')

@push('styles')
    <style>
        .card { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; }
        .btn-primary { background-color: #4f46e5; color: white; }
        .btn-primary:hover { background-color: #4338ca; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; }
        .btn-secondary:hover { background-color: #d1d5db; }
        .btn-danger { background-color: #ef4444; color: white; }
        .btn-danger:hover { background-color: #dc2626; }
        .btn-success { background-color: #10b981; color: white; }
        .btn-success:hover { background-color: #059669; }
        .table td, .table th { vertical-align: middle !important; padding: 0.75rem 1rem; }
        .table th { background-color: #f9fafb; font-weight: 600; color: #4b5563; border-bottom: 1px solid #e5e7eb; text-transform: uppercase; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; font-weight: 500; padding: 0.3em 0.65em; display: inline-block; }
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 550px; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .close { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
        .close:hover { opacity: 1; color: #1f2937; }
        .modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
        .modal-footer { display: flex; align-items: center; justify-content: flex-end; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .modal-footer > :not(:first-child) { margin-left: .5rem; }
        .form-input { border-radius: 0.5rem; border-color: #d1d5db; transition: all 0.2s ease-in-out; }
        .form-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); outline: none; }

        /* === CSS CHO HIỆU ỨNG QUAY === */
        .icon-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* === CSS CHO TOAST NOTIFICATION === */
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
        .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
        .toast.hide { opacity: 0; transform: translateX(100%); }

    </style>
@endpush

@section('content')
<div class="body-content px-6 md:px-8 py-8">
    {{-- Hiển thị thông báo (flash message) dưới dạng TOAST ở góc trên bên phải --}}
    @include('admin.partials.flash_message')

    <div class="container mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Quản lý Thuộc tính</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    {{-- <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li> --}}
                    <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thuộc tính</li>
                </ol>
            </nav>
        </div>

        <div class="card bg-white">
            <div class="bg-gray-50 p-5 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-700 mb-3 sm:mb-0">Tất cả thuộc tính ({{ $attributes->total() }})</h3>
                    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary py-2.5 px-5 inline-flex items-center text-sm">
                        <i class="fas fa-plus-circle mr-2"></i> Thêm thuộc tính mới
                    </a>
                </div>
            </div>
            <div class="p-5">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-3 md:space-y-0">
                    <div class="w-full md:w-2/5">
                        {{-- Form tìm kiếm --}}
                        <form action="{{ route('admin.attributes.index') }}" method="GET" class="flex">
                            <div class="relative flex-grow">
                                <input type="text" name="search" class="form-input w-full pl-4 pr-12 py-2.5 text-sm" placeholder="Tìm kiếm theo tên thuộc tính..." value="{{ request('search') }}">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <button class="btn bg-indigo-50 hover:bg-indigo-100 text-indigo-600 py-2.5 px-4 border-0" type="submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="w-full md:w-auto text-right">
                        {{-- Nút Làm mới đã được thêm ID --}}
                        <a href="{{ route('admin.attributes.index') }}" id="refresh-button" class="btn btn-secondary py-2.5 px-5 inline-flex items-center text-sm">
                            <i class="fas fa-sync-alt mr-2"></i> Làm mới
                        </a>
                    </div>
                </div>
                

                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="table w-full table-auto text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left w-12">STT</th>
                                <th class="px-4 py-3 text-left">Tên thuộc tính</th>
                                <th class="px-4 py-3 text-left">Slug</th>
                                <th class="px-4 py-3 text-left">Kiểu hiển thị</th>
                                <th class="px-4 py-3 text-left">Số giá trị</th>
                                <th class="px-4 py-3 text-left w-40">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Vòng lặp để hiển thị dữ liệu --}}
                            @forelse ($attributes as $attribute)
                                <tr>
                                    <td class="px-4 py-3">{{ ($attributes->currentPage() - 1) * $attributes->perPage() + $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.attributes.show', $attribute) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">{{ $attribute->name }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $attribute->slug }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ Str::ucfirst(str_replace('_', ' ', $attribute->display_type)) }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.attributes.show', $attribute) }}" class="badge-info rounded-full">
                                            {{ $attribute->attribute_values_count }} giá trị
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.attributes.show', $attribute) }}" class="btn btn-success p-2 text-xs" title="Quản lý giá trị"><i class="fas fa-list-ul text-base"></i></a>
                                            <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-primary p-2 text-xs" title="Chỉnh sửa thuộc tính"><i class="fas fa-edit text-base"></i></a>
                                            <button type="button" class="btn btn-danger p-2 text-xs" title="Xóa thuộc tính" onclick="openModal('deleteAttributeModal{{ $attribute->id }}')"><i class="fas fa-trash text-base"></i></button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal xóa được tạo động cho mỗi thuộc tính --}}
                                <div id="deleteAttributeModal{{ $attribute->id }}" class="modal" tabindex="-1">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Xác nhận xóa Thuộc tính</h5>
                                            <button type="button" class="close" onclick="closeModal('deleteAttributeModal{{ $attribute->id }}')"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="flex items-start">
                                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                                </div>
                                                <div class="ml-4 text-left">
                                                    <p class="text-base text-gray-700">Bạn có chắc chắn muốn xóa thuộc tính "<strong>{{ $attribute->name }}</strong>"?</p>
                                                    <p class="mt-1 text-sm text-gray-500"><strong class="font-semibold text-red-600">Cảnh báo:</strong> Tất cả các giá trị của thuộc tính này cũng sẽ bị xóa. Hành động này không thể hoàn tác.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary py-2 px-4 text-sm" onclick="closeModal('deleteAttributeModal{{ $attribute->id }}')">Hủy</button>
                                            {{-- Form xóa --}}
                                            <form action="{{ route('admin.attributes.destroy', $attribute) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger py-2 px-4 text-sm">Xóa Thuộc tính</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                {{-- Trường hợp không có dữ liệu --}}
                                <tr>
                                    <td colspan="6" class="text-center px-4 py-10 text-gray-500">
                                        @if (request('search'))
                                            Không tìm thấy thuộc tính nào với từ khóa "<strong>{{ request('search') }}</strong>".
                                        @else
                                            Không có thuộc tính nào.
                                        @endif
                                        <a href="{{ route('admin.attributes.create') }}" class="text-indigo-600 hover:underline ml-2">Thêm thuộc tính mới?</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- PHẦN PHÂN TRANG --}}
            @if ($attributes->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                {{-- Thông tin số lượng kết quả --}}
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-start">
                    <p class="text-sm text-gray-700 leading-5">
                        Hiển thị từ
                        <span class="font-medium">{{ $attributes->firstItem() }}</span>
                        đến
                        <span class="font-medium">{{ $attributes->lastItem() }}</span>
                        trên tổng số
                        <span class="font-medium">{{ $attributes->total() }}</span>
                        kết quả
                    </p>
                </div>
                {{-- Các liên kết phân trang --}}
                <div>
                     {{-- Thêm `appends(['search' => request('search')])` để giữ lại query string khi tìm kiếm --}}
                    {!! $attributes->appends(['search' => request('search')])->links() !!}
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // === SCRIPT CHO TOAST NOTIFICATION ===
        const toasts = document.querySelectorAll('.toast');

        const hideToast = (toastElement) => {
            if (toastElement) {
                toastElement.classList.add('hide');
                // Xóa hẳn element khỏi DOM sau khi animation kết thúc
                setTimeout(() => {
                    toastElement.remove();
                }, 350); // Phải khớp với thời gian transition trong CSS
            }
        };

        toasts.forEach(toast => {
            // Tự động ẩn sau 5 giây
            const autoHideTimeout = setTimeout(() => {
                hideToast(toast);
            }, 5000);

            // Xử lý khi click nút đóng
            const closeButton = toast.querySelector('[data-dismiss-target]');
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    clearTimeout(autoHideTimeout); // Hủy tự động ẩn nếu người dùng tự đóng
                    const targetId = this.getAttribute('data-dismiss-target');
                    const toastToHide = document.querySelector(targetId);
                    hideToast(toastToHide);
                });
            }
        });

        // === SCRIPT CHO MODAL ===
        // Hàm mở modal
        window.openModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        // Hàm đóng modal
        window.closeModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        }

        // Đóng modal khi nhấp chuột bên ngoài modal-content
        window.addEventListener('click', function(event) {
            document.querySelectorAll('.modal.show').forEach(modal => {
                if (event.target.closest('.modal-content') === null && event.target.classList.contains('modal')) {
                    closeModal(modal.id);
                }
            });
        });

        // Đóng modal khi nhấn phím Escape
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
            }
        });

        // === SCRIPT CHO NÚT LÀM MỚI ===
        const refreshButton = document.getElementById('refresh-button');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                const icon = this.querySelector('i.fa-sync-alt');
                if (icon) {
                    icon.classList.add('icon-spin');
                }
            });
        }
    });
</script>
@endpush
