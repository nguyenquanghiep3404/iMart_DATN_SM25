@extends('admin.layouts.app')

@section('title', 'Thùng rác Media')

@push('styles')
<style>
    .card { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }
    .btn-secondary:hover { background-color: #cbd5e1; }
    .btn-danger { background-color: #ef4444; color: white; }
    .btn-danger:hover { background-color: #dc2626; }
    .btn-success { background-color: #10b981; color: white; }
    .btn-success:hover { background-color: #059669; }
    .table-auto { width: 100%; }
    .table-auto th, .table-auto td { padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
    .table-auto th { text-align: left; font-weight: 600; color: #4b5563; background-color: #f9fafb;}
    .table-auto tbody tr:hover { background-color: #f9fafb; }
    .table-auto tbody tr.selected { background-color: #eff6ff; } /* Màu khi chọn */

    /* Style cho image preview popup */
    #image-preview-popup {
        position: absolute;
        display: none;
        z-index: 1060;
        pointer-events: none; /* Quan trọng để không bị che các element khác */
        border: 3px solid white;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        border-radius: 0.5rem;
        max-width: 300px;
        max-height: 300px;
        background-color: #fff;
    }
    #image-preview-popup img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 0.375rem;
    }

    /* Style cho nút filter active */
    .btn-filter.active {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
</style>
@endpush

@section('content')
<div class="body-content px-6 md:px-8 py-8">
    @include('admin.partials.flash_message')

    {{-- Div cho ảnh preview khi hover --}}
    <div id="image-preview-popup"><img src="" alt="Preview"></div>

    <div class="container mx-auto max-w-full">
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Thùng rác Media</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li><a href="{{ route('admin.media.index') }}" class="text-indigo-600 hover:text-indigo-800">Thư viện Media</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="active text-gray-700 font-medium" aria-current="page">Thùng rác</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.media.index') }}" class="btn btn-secondary py-2 px-4 inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại Thư viện
            </a>
        </div>

        <div class="card bg-white">
            <div class="p-5 border-b">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="w-full sm:w-auto">
                        {{-- Nút xóa hàng loạt --}}
                        <button id="bulk-delete-btn" class="btn btn-danger hidden">
                            <i class="fas fa-trash-alt mr-2"></i>
                            <span id="bulk-delete-count">Xóa mục đã chọn (0)</span>
                        </button>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                        {{-- Ô tìm kiếm --}}
                        <form action="{{ route('admin.media.trash') }}" method="GET" class="w-full sm:w-auto">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-search text-gray-400"></i>
                                </span>
                                <input type="text" name="search" class="w-full rounded-md border-gray-300 shadow-sm pl-10 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tìm kiếm file..." value="{{ request('search') }}">
                            </div>
                            {{-- Giữ lại trạng thái filter khi tìm kiếm --}}
                            @if (request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif
                        </form>

                        {{-- Bộ lọc --}}
                        <div class="flex-shrink-0 flex items-center gap-2">
                            {{-- Giữ lại trạng thái tìm kiếm khi dùng filter --}}
                            <a href="{{ route('admin.media.trash', ['search' => request('search')]) }}" class="btn btn-filter btn-secondary py-2 px-4 text-sm {{ !request('filter') ? 'active' : '' }}">Tất cả</a>
                            <a href="{{ route('admin.media.trash', ['filter' => 'unattached', 'search' => request('search')]) }}" class="btn btn-filter btn-secondary py-2 px-4 text-sm {{ request('filter') === 'unattached' ? 'active' : '' }}">Ảnh chưa gán</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-5">
                @if($trashedFiles->isEmpty())
                    <div class="text-center py-16">
                        <i class="fas fa-trash fa-4x text-gray-300"></i>
                        <p class="mt-4 text-lg text-gray-500">Thùng rác trống.</p>
                        {{-- --- BẮT ĐẦU SỬA LỖI --- --}}
                        @if(request()->filled('search') || request()->filled('filter'))
                           <p class="mt-2 text-sm text-gray-400">Không tìm thấy file nào khớp với tiêu chí của bạn.</p>
                           <a href="{{ route('admin.media.trash') }}" class="mt-4 btn btn-secondary">Xóa bộ lọc</a>
                        @endif
                        {{-- --- KẾT THÚC SỬA LỖI --- --}}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-auto">
                            <thead>
                                <tr>
                                    <th class="w-10 px-4"><input type="checkbox" id="select-all-checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"></th>
                                    <th class="w-20">Xem trước</th>
                                    <th>Tên file gốc</th>
                                    <th>Ngày xóa</th>
                                    <th>Người xóa</th>
                                    <th class="w-48 text-right">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trashedFiles as $file)
                                <tr id="row-{{$file->id}}">
                                    <td class="px-4"><input type="checkbox" class="row-checkbox h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" data-id="{{ $file->id }}"></td>
                                    <td>
                                        <img src="{{ $file->url }}" alt="{{ $file->alt_text }}" class="thumbnail-image w-12 h-12 object-cover rounded-md bg-gray-100 cursor-pointer">
                                    </td>
                                    <td class="text-gray-700">
                                        <p class="font-medium">{{ $file->original_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $file->formatted_size }}</p>
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ $file->deleted_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-sm text-gray-600">
                                        {{ $file->deletedBy->name ?? 'Không rõ' }}
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <form action="{{ route('admin.media.restore', $file->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục file này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-success text-xs py-1 px-3 inline-flex items-center">
                                                    <i class="fas fa-undo-alt mr-1"></i>
                                                    Khôi phục
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.media.forceDelete', $file->id) }}" method="POST" onsubmit="return confirm('Hành động này không thể hoàn tác! Bạn có chắc chắn muốn XÓA VĨNH VIỄN file này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger text-xs py-1 px-3 inline-flex items-center">
                                                    <i class="fas fa-times-circle mr-1"></i>
                                                    Xóa vĩnh viễn
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
             @if ($trashedFiles->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {!! $trashedFiles->appends(request()->query())->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === CẤU HÌNH CSRF TOKEN CHO AXIOS ===
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    // === XỬ LÝ ẢNH PREVIEW KHI HOVER ===
    const previewPopup = document.getElementById('image-preview-popup');
    const previewImage = previewPopup.querySelector('img');
    const thumbnails = document.querySelectorAll('.thumbnail-image');

    document.body.addEventListener('mousemove', function(e) {
        if (previewPopup.style.display === 'block') {
            // Thêm offset để popup không che con trỏ
            previewPopup.style.left = e.pageX + 15 + 'px';
            previewPopup.style.top = e.pageY + 15 + 'px';
        }
    });

    thumbnails.forEach(thumb => {
        thumb.addEventListener('mouseover', function(e) {
            previewImage.src = this.src;
            previewPopup.style.display = 'block';
        });
        thumb.addEventListener('mouseout', function() {
            previewPopup.style.display = 'none';
        });
    });

    // === XỬ LÝ CHỌN VÀ XÓA HÀNG LOẠT ===
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const bulkDeleteCount = document.getElementById('bulk-delete-count');
    let selectedIds = new Set();

    function updateSelectionUI() {
        // Cập nhật trạng thái của từng checkbox
        rowCheckboxes.forEach(checkbox => {
            const id = parseInt(checkbox.dataset.id);
            const row = document.getElementById(`row-${id}`);
            if (row) { // Thêm kiểm tra nếu hàng tồn tại
                if (selectedIds.has(id)) {
                    checkbox.checked = true;
                    row.classList.add('selected');
                } else {
                    checkbox.checked = false;
                    row.classList.remove('selected');
                }
            }
        });

        // Cập nhật trạng thái của checkbox "chọn tất cả"
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = rowCheckboxes.length > 0 && selectedIds.size === rowCheckboxes.length;
        }

        // Cập nhật nút xóa hàng loạt
        const count = selectedIds.size;
        if (count > 0) {
            bulkDeleteBtn.classList.remove('hidden');
            bulkDeleteCount.textContent = `Xóa vĩnh viễn (${count})`;
        } else {
            bulkDeleteBtn.classList.add('hidden');
        }
    }

    // Sự kiện cho checkbox "chọn tất cả"
    if(selectAllCheckbox){
        selectAllCheckbox.addEventListener('change', function() {
            if (this.checked) {
                rowCheckboxes.forEach(checkbox => selectedIds.add(parseInt(checkbox.dataset.id)));
            } else {
                selectedIds.clear();
            }
            updateSelectionUI();
        });
    }

    // Sự kiện cho các checkbox của từng hàng
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = parseInt(this.dataset.id);
            if (this.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
            updateSelectionUI();
        });
    });

    // Sự kiện cho nút xóa hàng loạt
    bulkDeleteBtn.addEventListener('click', async function() {
        const count = selectedIds.size;
        if (count === 0) return;

        if (!confirm(`Hành động này không thể hoàn tác! Bạn có chắc chắn muốn XÓA VĨNH VIỄN ${count} file đã chọn?`)) {
            return;
        }

        try {
            const response = await axios.post("{{ route('admin.media.bulk-delete') }}?force=1", { 
                ids: Array.from(selectedIds) 
            });
            
            alert(response.data.message || `${count} file đã được xóa vĩnh viễn.`);
            window.location.reload(); 

        } catch (error) {
            console.error('Bulk force delete failed:', error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi xóa hàng loạt.';
            alert(errorMessage);
        }
    });

});
</script>
@endpush
