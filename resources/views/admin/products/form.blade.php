@extends('admin.layouts.app')

{{-- Tiêu đề sẽ thay đổi tùy thuộc vào trang create hay edit --}}
@section('title', isset($product) ? 'Chỉnh sửa Sản phẩm' : 'Thêm Sản phẩm mới')

@push('styles')
    {{-- Copy style từ các trang khác để đồng bộ --}}
    <style>
        .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; }
        /* ... các style khác từ file products/index ... */

        /* --- CSS RIÊNG CHO FORM SẢN PHẨM --- */
        /* Khu vực chọn ảnh */
        .image-placeholder {
            width: 150px;
            height: 150px;
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }
        .image-placeholder:hover {
            border-color: #4f46e5;
            background-color: #f0f1ff;
        }
        .image-preview-container {
            position: relative;
            width: 150px;
            height: 150px;
        }
        .image-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .remove-image-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #ef4444;
            color: white;
            border-radius: 9999px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .remove-image-btn:hover {
            background-color: #dc2626;
            transform: scale(1.1);
        }

        /* Gallery */
        #gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
        }
        .gallery-item .remove-image-btn {
            width: 24px;
            height: 24px;
            top: -8px;
            right: -8px;
        }

        /* Modal cho thư viện media */
        .media-modal {
             display: none; position: fixed; z-index: 1060; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.6);
        }
        .media-modal.show { display: flex; }
        .media-modal-content {
            height: 90vh;
            width: 90vw;
            max-width: 1400px;
            display: flex;
            flex-direction: column;
        }
    </style>
@endpush

@section('content')
<div class="body-content px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ isset($product) ? 'Chỉnh sửa Sản phẩm' : 'Thêm Sản phẩm mới' }}</h1>
            {{-- Breadcrumb --}}
        </div>

        <form action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST">
            @csrf
            @if(isset($product))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Cột trái - Thông tin chính --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="card-custom">
                        {{-- Tên, mô tả, nội dung sản phẩm --}}
                    </div>
                     <div class="card-custom">
                        {{-- Giá, biến thể, kho hàng --}}
                    </div>
                </div>

                {{-- Cột phải - Ảnh, danh mục, trạng thái --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- === KHU VỰC QUẢN LÝ ẢNH === --}}
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h3 class="card-custom-title text-lg">Ảnh đại diện</h3>
                        </div>
                        <div class="card-custom-body">
                            {{-- Input ẩn để lưu ID của ảnh bìa --}}
                            <input type="hidden" name="cover_image_id" id="cover_image_id" value="{{ $product->coverImage->id ?? '' }}">

                            <div id="cover-image-preview-container" class="{{ isset($product) && $product->coverImage ? '' : 'hidden' }}">
                                <div class="image-preview-container">
                                    <img id="cover-image-preview" src="{{ isset($product) && $product->coverImage ? $product->coverImage->url : '' }}" class="image-preview">
                                    <div id="remove-cover-image" class="remove-image-btn" title="Xóa ảnh">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="cover-image-placeholder" class="image-placeholder {{ isset($product) && $product->coverImage ? 'hidden' : '' }}">
                                <i class="fas fa-image fa-2x mb-2"></i>
                                <span class="text-sm">Chọn ảnh đại diện</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h3 class="card-custom-title text-lg">Thư viện ảnh</h3>
                        </div>
                        <div class="card-custom-body">
                            {{-- Container để chứa các input ẩn cho gallery --}}
                            <div id="gallery-inputs-container">
                                @if(isset($product))
                                    @foreach($product->galleryImages as $image)
                                        <input type="hidden" name="gallery_images[]" value="{{ $image->id }}">
                                    @endforeach
                                @endif
                            </div>
                            
                            <div id="gallery-grid" class="mb-4">
                                @if(isset($product))
                                    @foreach($product->galleryImages as $image)
                                        <div class="image-preview-container gallery-item" data-id="{{ $image->id }}">
                                            <img src="{{ $image->url }}" class="image-preview">
                                            <div class="remove-image-btn remove-gallery-image" title="Xóa ảnh">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <button type="button" id="add-gallery-images" class="btn btn-secondary w-full">
                                <i class="fas fa-plus mr-2"></i> Thêm ảnh từ thư viện
                            </button>
                        </div>
                    </div>
                     {{-- Các card khác: Danh mục, Trạng thái, SEO... --}}
                </div>
            </div>
            
            <div class="mt-8 text-right">
                <button type="submit" class="btn btn-primary btn-lg">
                    {{ isset($product) ? 'Cập nhật Sản phẩm' : 'Lưu Sản phẩm' }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL THƯ VIỆN MEDIA -->
<div id="media-library-modal" class="media-modal">
    <div class="bg-white rounded-lg shadow-xl media-modal-content">
        {{-- Iframe để tải trang thư viện media --}}
        <iframe src="{{ route('admin.media.index') }}?context=modal" style="width: 100%; height: 100%; border: none;"></iframe>
    </div>
</div>

@endsection

@push('scripts')
{{-- Thư viện để kéo thả sắp xếp --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // === KHỞI TẠO CÁC BIẾN ===
    const coverImagePlaceholder = document.getElementById('cover-image-placeholder');
    const coverImagePreviewContainer = document.getElementById('cover-image-preview-container');
    const coverImagePreview = document.getElementById('cover-image-preview');
    const coverImageIdInput = document.getElementById('cover_image_id');
    const removeCoverImageBtn = document.getElementById('remove-cover-image');

    const addGalleryImagesBtn = document.getElementById('add-gallery-images');
    const galleryGrid = document.getElementById('gallery-grid');
    const galleryInputsContainer = document.getElementById('gallery-inputs-container');

    const mediaModal = document.getElementById('media-library-modal');
    let selectionContext = 'cover'; // 'cover' or 'gallery'

    // === XỬ LÝ ẢNH BÌA ===
    function setCoverImage(file) {
        coverImagePreview.src = file.url;
        coverImageIdInput.value = file.id;
        coverImagePreviewContainer.classList.remove('hidden');
        coverImagePlaceholder.classList.add('hidden');
    }

    function clearCoverImage() {
        coverImagePreview.src = '';
        coverImageIdInput.value = '';
        coverImagePreviewContainer.classList.add('hidden');
        coverImagePlaceholder.classList.remove('hidden');
    }

    // Mở modal để chọn ảnh bìa
    coverImagePlaceholder.addEventListener('click', () => {
        selectionContext = 'cover';
        mediaModal.classList.add('show');
    });

    // Xóa ảnh bìa
    removeCoverImageBtn.addEventListener('click', clearCoverImage);


    // === XỬ LÝ THƯ VIỆN ẢNH (GALLERY) ===
    function addImageToGallery(file) {
        // Kiểm tra xem ảnh đã có trong gallery chưa
        if (galleryInputsContainer.querySelector(`input[value="${file.id}"]`)) {
            return; // Không thêm nếu đã tồn tại
        }

        // Thêm input ẩn
        const newInput = document.createElement('input');
        newInput.type = 'hidden';
        newInput.name = 'gallery_images[]';
        newInput.value = file.id;
        galleryInputsContainer.appendChild(newInput);

        // Thêm ảnh preview vào grid
        const newImageDiv = document.createElement('div');
        newImageDiv.className = 'image-preview-container gallery-item';
        newImageDiv.dataset.id = file.id;
        newImageDiv.innerHTML = `
            <img src="${file.url}" class="image-preview">
            <div class="remove-image-btn remove-gallery-image" title="Xóa ảnh">
                <i class="fas fa-times"></i>
            </div>
        `;
        galleryGrid.appendChild(newImageDiv);
    }

    function removeGalleryImage(element) {
        const id = element.dataset.id;
        // Xóa input ẩn
        galleryInputsContainer.querySelector(`input[value="${id}"]`)?.remove();
        // Xóa thẻ div chứa ảnh
        element.remove();
    }
    
    // Mở modal để chọn ảnh gallery
    addGalleryImagesBtn.addEventListener('click', () => {
        selectionContext = 'gallery';
        mediaModal.classList.add('show');
    });
    
    // Bắt sự kiện click nút xóa cho các ảnh gallery (dùng event delegation)
    galleryGrid.addEventListener('click', function(e) {
        if (e.target.closest('.remove-gallery-image')) {
            removeGalleryImage(e.target.closest('.gallery-item'));
        }
    });

    // Kích hoạt sắp xếp kéo thả cho gallery
    new Sortable(galleryGrid, {
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: function () {
            // Cập nhật lại thứ tự các input ẩn sau khi kéo thả
            galleryInputsContainer.innerHTML = ''; // Xóa hết input cũ
            galleryGrid.querySelectorAll('.gallery-item').forEach(item => {
                const id = item.dataset.id;
                const newInput = document.createElement('input');
                newInput.type = 'hidden';
                newInput.name = 'gallery_images[]';
                newInput.value = id;
                galleryInputsContainer.appendChild(newInput);
            });
        }
    });


    // === GIAO TIẾP VỚI MODAL THƯ VIỆN MEDIA ===
    window.addEventListener('message', function(event) {
        // Kiểm tra nguồn gốc để bảo mật
        // if (event.origin !== window.location.origin) return;

        const data = event.data;
        if (data.action === 'fileSelected') {
            if (selectionContext === 'cover') {
                setCoverImage(data.file);
            } else if (selectionContext === 'gallery') {
                data.files.forEach(file => addImageToGallery(file));
            }
            // Đóng modal sau khi chọn
            mediaModal.classList.remove('show');
        }
        if (data.action === 'closeModal') {
            mediaModal.classList.remove('show');
        }
    });

});
</script>
@endpush