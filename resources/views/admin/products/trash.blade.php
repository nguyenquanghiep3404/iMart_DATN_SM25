@extends('admin.layouts.app')

@section('title', 'Thùng rác sản phẩm')

@push('styles')
    {{-- Các style này được kế thừa từ giao diện danh sách, giữ nguyên để đảm bảo nhất quán --}}
    <style>
        .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); background-color: #fff; }
        .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; display: flex; justify-content: space-between; align-items: center; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
        .card-custom-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .card-custom-body { padding: 1.5rem; }
        .card-custom-footer { background-color: #f9fafb; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        .btn-primary { background-color: #4f46e5; color: white; } .btn-primary:hover { background-color: #4338ca; }
        .btn-success { background-color: #10b981; color: white; } .btn-success:hover { background-color: #059669; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background-color: #d1d5db; }
        .btn-danger { background-color: #ef4444; color: white; } .btn-danger:hover { background-color: #dc2626; }
        .btn-outline-secondary { color: #4a5568; border: 1px solid #d1d5db; } .btn-outline-secondary:hover { background-color: #e5e7eb; }
        .form-input, .form-select { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
        .table-custom { width: 100%; }
        .table-custom th, .table-custom td { padding: 0.75rem 1rem; vertical-align: middle !important; border-bottom-width: 1px; border-color: #e5e7eb; }
        .table-custom thead th { font-weight: 600; color: #4b5563; background-color: #f9fafb; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; text-align: left; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.03); }
        .img-thumbnail-custom { width: 60px; height: 60px; object-fit: cover; border-radius: 0.375rem; }
        .product-modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .product-modal.show { display: flex; align-items: center; justify-content: center; }
        .product-modal-content { background-color: #fff; margin: auto; border: none; width: 90%; max-width: 500px; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04); }
        .product-modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .product-modal-title { font-size: 1.25rem; font-weight: 600; }
        .product-close { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background: transparent; border: 0; cursor: pointer; }
        .product-modal-body { padding: 1.5rem; }
        .product-modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; gap: 0.5rem; }
    </style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-7xl">
        {{-- Toast notifications (giữ nguyên) --}}
        <div id="toast-container" style="position: fixed; top: 1rem; right: 1rem; z-index: 1100;"></div>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Thùng rác</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}" class="text-indigo-600 hover:text-indigo-800">Sản phẩm</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thùng rác</li>
                </ol>
            </nav>
        </div>

        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title">Sản phẩm đã xóa ({{ $trashedProducts->total() }})</h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
            <div class="card-custom-body">
                {{-- Có thể giữ lại bộ lọc nếu muốn lọc trong thùng rác --}}
                
                <div class="overflow-x-auto">
                    <table class="table-custom table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px">STT</th>
                                <th>Tên sản phẩm</th>
                                <th>Ngày xóa</th>
                                {{-- Giả sử chỉ admin mới thấy cột này --}}
                                @can('view_deleted_by_anyone')
                                    <th>Người xóa</th>
                                @endcan
                                <th style="width: 200px" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($trashedProducts as $product)
                            <tr>
                                <td>{{ ($trashedProducts->currentPage() - 1) * $trashedProducts->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="font-semibold">{{ $product->name }}</div>
                                    <small class="text-gray-500">SKU: {{ $product->variants->first()->sku ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $product->deleted_at->format('d/m/Y H:i') }}</td>
                                {{-- Chỉ hiển thị người xóa cho Admin --}}
                                @can('view_deleted_by_anyone')
                                    <td>{{ $product->deletedBy->name ?? 'Không rõ' }}</td>
                                @endcan
                                <td class="text-center">
                                    <div class="inline-flex space-x-1">
                                        {{-- Nút khôi phục --}}
                                        <button type="button" class="btn btn-success btn-sm" onclick="openModal('restoreModal{{ $product->id }}')">
                                            <i class="fas fa-undo mr-1"></i> Khôi phục
                                        </button>
                                        {{-- Nút xóa vĩnh viễn (có thể giới hạn quyền) --}}
                                        @can('forceDelete', $product)
                                            <button type="button" class="btn btn-danger btn-sm" onclick="openModal('forceDeleteModal{{ $product->id }}')">
                                                <i class="fas fa-skull-crossbones mr-1"></i> Xóa vĩnh viễn
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            
                            {{-- Modal Xác nhận Khôi phục --}}
                            <div id="restoreModal{{ $product->id }}" class="product-modal" tabindex="-1">
                                <div class="product-modal-content">
                                    <form action="{{ route('admin.products.restore', $product->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="product-modal-header">
                                            <h5 class="product-modal-title text-green-600">Xác nhận khôi phục</h5>
                                            <button type="button" class="product-close" onclick="closeModal('restoreModal{{ $product->id }}')">&times;</button>
                                        </div>
                                        <div class="product-modal-body">
                                            <p>Bạn có chắc chắn muốn khôi phục sản phẩm "<strong>{{ $product->name }}</strong>"?</p>
                                            <p class="text-sm text-gray-600 mt-2">Sản phẩm sẽ được chuyển về trạng thái "Bản nháp" để bạn xem lại.</p>
                                        </div>
                                        <div class="product-modal-footer">
                                            <button type="button" class="btn btn-secondary" onclick="closeModal('restoreModal{{ $product->id }}')">Hủy</button>
                                            <button type="submit" class="btn btn-success">Đồng ý</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Modal Xác nhận Xóa Vĩnh viễn --}}
                            <div id="forceDeleteModal{{ $product->id }}" class="product-modal" tabindex="-1">
                                <div class="product-modal-content">
                                    <form action="{{ route('admin.products.force-delete', $product->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="product-modal-header">
                                            <h5 class="product-modal-title text-red-600">CẢNH BÁO: XÓA VĨNH VIỄN</h5>
                                            <button type="button" class="product-close" onclick="closeModal('forceDeleteModal{{ $product->id }}')">&times;</button>
                                        </div>
                                        <div class="product-modal-body">
                                            <p class="text-lg font-bold text-red-700">Hành động này không thể hoàn tác!</p>
                                            <p class="mt-2">Bạn có chắc chắn muốn <strong class="text-red-700">XOÁ VĨNH VIỄN</strong> sản phẩm "<strong>{{ $product->name }}</strong>" không?</p>
                                            <p class="text-sm text-gray-600 mt-2">Tất cả dữ liệu liên quan đến sản phẩm này sẽ bị xóa khỏi hệ thống.</p>
                                        </div>
                                        <div class="product-modal-footer">
                                            <button type="button" class="btn btn-secondary" onclick="closeModal('forceDeleteModal{{ $product->id }}')">Không, hủy bỏ</button>
                                            <button type="submit" class="btn btn-danger">Tôi hiểu và muốn xóa</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-trash-alt fa-3x mb-3 text-gray-400"></i>
                                        <p class="text-lg font-medium">Thùng rác trống.</p>
                                        <p class="text-sm">Không có sản phẩm nào bị xóa.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($trashedProducts->hasPages())
            <div class="card-custom-footer">
                <div>
                     <p class="text-sm text-gray-700">
                        Hiển thị từ {{ $trashedProducts->firstItem() }} đến {{ $trashedProducts->lastItem() }} trên tổng {{ $trashedProducts->total() }}
                    </p>
                </div>
                <div>
                    {!! $trashedProducts->appends(request()->query())->links() !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Script cho Modal
    function openModal(modalId) {
        document.getElementById(modalId)?.classList.add('show');
    }
    function closeModal(modalId) {
        document.getElementById(modalId)?.classList.remove('show');
    }
    // Đóng modal khi click ra ngoài hoặc nhấn ESC
    window.addEventListener('click', e => {
        if (e.target.classList.contains('product-modal')) {
            closeModal(e.target.id);
        }
    });
    window.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.product-modal.show').forEach(modal => closeModal(modal.id));
        }
    });
</script>
@endpush
