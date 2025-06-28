@extends('admin.layouts.app')

@section('title', 'Thư viện Media')

@push('styles')
    {{-- Thêm CSS của Cropper.js --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" xintegrity="sha512-hvNR0F/e2Jb1hbIDDihMOTrdpp4vocD/vE9k/0EfAAgHDov7RUwoXg7EFeBCvoFSvdFuY4OI5mvEuTQubEj73Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; border-radius: 0.75rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .close { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
        .close:hover { opacity: 1; color: #1f2937; }
        .modal-body { position: relative; flex: 1 1 auto; padding: 1.5rem; color: #374151; }
        .modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: 1.25rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; border-bottom-left-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        .modal-footer > :not(:first-child) { margin-left: .5rem; }
        .form-input { border-radius: 0.5rem; border-color: #d1d5db; transition: all 0.2s ease-in-out; }
        .form-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); outline: none; }
        .icon-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .toast-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 1100; display: flex; flex-direction: column; gap: 0.75rem; }
        .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
        .toast.hide { opacity: 0; transform: translateX(100%); }
        .image-card.selected { box-shadow: 0 0 0 3px #4f46e5; border-color: #4f46e5; }
        #detail-modal .modal-content { max-width: 80rem; }
        .progress-bar { background-color: #e5e7eb; border-radius: 0.5rem; overflow: hidden; width: 100%; height: 1.25rem; margin-top: 0.5rem; display: none; }
        .progress-bar-fill { background-color: #4f46e5; height: 100%; width: 0%; transition: width 0.3s ease-in-out; text-align: center; color: white; font-size: 0.75rem; line-height: 1.25rem; }
        .btn-filter.active, .btn-aspect-ratio.active { background-color: #3b82f6; color: white; border-color: #3b82f6; }
        .stat-card { background-color: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); display: flex; align-items: center; }
        .stat-card .icon { width: 3.5rem; height: 3.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; font-size: 1.5rem; }
        #image-to-crop { display: block; max-width: 100%; }
    </style>
@endpush

@section('content')
<div class="body-content px-6 md:px-8 py-8">
    <div id="toast-container"></div>
    @include('admin.partials.flash_message')

    <div class="container mx-auto max-w-full">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Thư viện Media</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="stat-card">
                <div class="icon bg-blue-100 text-blue-600"><i class="fas fa-file-alt"></i></div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Tổng số Files</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon bg-green-100 text-green-600"><i class="fas fa-link"></i></div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Đã gán</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['attached'] ?? 0 }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon bg-yellow-100 text-yellow-600"><i class="fas fa-unlink"></i></div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Chưa gán (mồ côi)</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['unattached'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="card bg-white">
            <div class="bg-gray-50 p-5 border-b border-gray-200">
                <form id="filter-form" action="{{ route('admin.media.index') }}" method="GET">
                    <div class="flex flex-col md:flex-row md:items-end gap-4">
                        <div class="flex-grow">
                            <label for="search-input" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <div class="relative">
                                <input type="text" name="search" id="search-input" class="form-input w-full pl-4 pr-12 py-2.5 text-sm" placeholder="Tên file, alt text..." value="{{ request('search') }}">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <button class="btn bg-indigo-50 hover:bg-indigo-100 text-indigo-600 py-2.5 px-4 border-0 h-full" type="submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div>
                             <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                            <input type="date" name="start_date" id="start_date" class="form-input py-2.5 text-sm" value="{{ request('start_date') }}">
                        </div>
                        <div>
                             <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                            <input type="date" name="end_date" id="end_date" class="form-input py-2.5 text-sm" value="{{ request('end_date') }}">
                        </div>
                        <div class="flex items-center gap-2">
                             <button type="submit" class="btn btn-primary py-2.5 px-5 text-sm">Lọc</button>
                             <a href="{{ route('admin.media.index') }}" class="btn btn-secondary py-2.5 px-5 text-sm">Xóa lọc</a>
                        </div>
                    </div>
                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    @endif
                </form>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-4 pt-4 border-t">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.media.index', request()->except('filter')) }}" class="btn btn-filter btn-secondary py-2.5 px-5 text-sm {{ !request('filter') ? 'active' : '' }}">Tất cả</a>
                        <a href="{{ route('admin.media.index', array_merge(request()->except('filter'), ['filter' => 'unattached'])) }}" class="btn btn-filter btn-secondary py-2.5 px-5 text-sm {{ request('filter') === 'unattached' ? 'active' : '' }}">Ảnh chưa gán</a>
                    </div>
                    
                    <div class="w-full sm:w-auto flex flex-col sm:flex-row flex-wrap justify-end items-center sm:space-x-2 space-y-2 sm:space-y-0">
                        @if(request('filter') === 'unattached')
                        <button id="select-all-btn" class="btn btn-success py-2.5 px-5 inline-flex items-center text-sm justify-center">
                            <i class="fas fa-check-double mr-2"></i>
                            <span>Chọn tất cả</span>
                        </button>
                        @endif

                        <button id="delete-selected-btn" class="btn btn-danger py-2.5 px-5 inline-flex items-center text-sm justify-center hidden">
                            <i class="fas fa-trash-alt mr-2"></i>
                            <span>Xóa mục đã chọn</span>
                        </button>
                        <label for="upload-input" class="btn btn-primary py-2.5 px-5 inline-flex items-center text-sm cursor-pointer justify-center">
                            <i class="fas fa-upload mr-2"></i>
                            Tải lên file mới
                        </label>
                        <input type="file" id="upload-input" class="hidden" multiple accept="image/png, image/jpeg, image/gif, image/webp, application/pdf">
                    </div>
                </div>
                <div id="upload-progress-bar" class="progress-bar">
                    <div class="progress-bar-fill">0%</div>
                </div>
            </div>
            <div class="p-5">
                <main id="image-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-4 min-h-[400px]">
                    {{-- Dữ liệu ảnh được render bởi JavaScript --}}
                </main>
            </div>
             @if ($files->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {!! $files->appends(request()->query())->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL CHI TIẾT ẢNH -->
<div id="detail-modal" class="modal" tabindex="-1">
    <div class="modal-content !max-w-4xl flex flex-col md:flex-row max-h-[90vh]">
        <div class="w-full md:w-2/3 p-4 flex items-center justify-center bg-gray-100 rounded-t-lg md:rounded-l-lg md:rounded-t-none">
            <img id="modal-image" src="" alt="Image preview" class="max-w-full max-h-[40vh] md:max-h-[80vh] object-contain">
        </div>
        <div class="w-full md:w-1/3 flex flex-col">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết File</h5>
                <button type="button" class="close" onclick="closeModal('detail-modal')">&times;</button>
            </div>
            <div class="modal-body overflow-y-auto">
                <form id="detail-form">
                    <div class="space-y-4">
                        <div>
                            <label for="modal-alt" class="block text-sm font-medium text-gray-700">Văn bản thay thế (Alt Text)</label>
                            <input type="text" id="modal-alt" class="form-input mt-1 block w-full sm:text-sm">
                        </div>
                        <div>
                            <label for="modal-filename" class="block text-sm font-medium text-gray-700">Tên file gốc</label>
                            <input type="text" id="modal-filename" readonly class="form-input mt-1 block w-full bg-gray-100 sm:text-sm">
                        </div>
                        <div>
                            <label for="modal-url" class="block text-sm font-medium text-gray-700">URL của file</label>
                            <div class="relative">
                                <input type="text" id="modal-url" readonly class="form-input mt-1 block w-full bg-gray-100 pr-10 sm:text-sm">
                                <button type="button" id="copy-url-btn" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-indigo-600" title="Sao chép URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1 pt-2 border-t">
                            <p><strong>Ngày tải lên:</strong> <span id="modal-date"></span></p>
                            <p><strong>Loại file:</strong> <span id="modal-type"></span></p>
                            <p><strong>Kích thước:</strong> <span id="modal-size"></span></p>
                            <p><strong>Đính kèm với:</strong> <span id="modal-attachable" class="italic"></span></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="flex w-full gap-x-3">
                    <button type="button" id="recrop-btn" class="btn btn-secondary w-full py-2 px-4 text-sm flex-1">Cắt lại ảnh</button>
                    <button type="button" id="delete-btn" class="btn btn-danger w-full py-2 px-4 text-sm flex-1">Xóa</button>
                    <button type="button" id="update-btn" class="btn btn-primary w-full py-2 px-4 text-sm flex-1">Lưu</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CẮT ẢNH -->
<div id="cropper-modal" class="modal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="modal-content !max-w-3xl">
        <div class="modal-header">
            <h5 class="modal-title" id="modal-title">Cắt và Tối ưu hóa Ảnh</h5>
            <button type="button" class="close" onclick="closeModal('cropper-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <div class="w-full h-[400px] bg-gray-200 rounded-lg overflow-hidden">
                        <img id="image-to-crop" src="" alt="Image to crop" class="hidden max-w-full">
                    </div>
                </div>
                <div class="md:col-span-1 flex flex-col gap-4">
                    <p class="text-sm text-gray-600">Xem trước:</p>
                    <div id="cropper-preview" class="w-full aspect-square overflow-hidden rounded-lg bg-gray-100 mx-auto"></div>
                    <p class="text-sm text-gray-600 mt-4">Tùy chọn cắt:</p>
                    <div class="flex flex-wrap gap-2">
                        <button data-aspect-ratio="1.7777777777777777" class="btn-aspect-ratio btn btn-secondary text-xs py-1 px-2">16:9</button>
                        <button data-aspect-ratio="1.3333333333333333" class="btn-aspect-ratio btn btn-secondary text-xs py-1 px-2">4:3</button>
                        <button data-aspect-ratio="1" class="btn-aspect-ratio btn btn-secondary text-xs py-1 px-2 active">1:1</button>
                        <button data-aspect-ratio="NaN" class="btn-aspect-ratio btn btn-secondary text-xs py-1 px-2">Tự do</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button id="cancel-crop-btn" type="button" class="btn btn-secondary py-2 px-4 text-sm">Hủy bỏ</button>
            <button id="crop-and-upload-btn" type="button" class="btn btn-primary py-2 px-4 text-sm inline-flex items-center">
                <i class="fas fa-crop-alt mr-2"></i> Cắt & Tải lên
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" xintegrity="sha512-9KkIqUpN9UaPoANgrbxDLODuluxnMLvVPZlCELfgeBNiLNR570O5Kucea/wyYNEALuV5QmKkRAOI2K43x7lLVQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === CẤU HÌNH CSRF TOKEN ===
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    // === KHAI BÁO BIẾN ===
    const imageGrid = document.getElementById('image-grid');
    const uploadInput = document.getElementById('upload-input');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    const selectAllBtn = document.getElementById('select-all-btn');
    const progressBar = document.getElementById('upload-progress-bar');
    const progressBarFill = progressBar.querySelector('.progress-bar-fill');
    
    // Modal chi tiết
    const detailModal = document.getElementById('detail-modal');
    const updateBtn = document.getElementById('update-btn');
    const deleteBtn = document.getElementById('delete-btn');
    const copyUrlBtn = document.getElementById('copy-url-btn');
    const recropBtn = document.getElementById('recrop-btn');

    // Modal cropper
    const cropperModal = document.getElementById('cropper-modal');
    const imageToCrop = document.getElementById('image-to-crop');
    const cropAndUploadBtn = document.getElementById('crop-and-upload-btn');
    const cancelCropBtn = document.getElementById('cancel-crop-btn');
    
    // Biến trạng thái
    let allFiles = @json($files->items());
    let selectedFiles = new Set();
    let currentFileId = null;
    let cropper = null;
    let originalFile = null;
    let isRecropMode = false;

    // === CÁC HÀM TIỆN ÍCH ===
    window.openModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    window.closeModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
    window.addEventListener('click', (event) => {
        document.querySelectorAll('.modal.show').forEach(modal => {
            if (event.target.closest('.modal-content') === null && event.target.classList.contains('modal')) {
                closeModal(modal.id);
            }
        });
    });
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
        }
    });

    function createToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
        const toast = document.createElement('div');
        toast.className = `toast p-4 rounded-lg shadow-lg text-white ${colors[type] || 'bg-gray-800'}`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 350);
        }, 3000);
    }

    function getFileUrl(file) {
        return file?.url || (file?.path ? `/storage/${file.path}` : 'https://placehold.co/400x400/cccccc/ffffff?text=Invalid+Path');
    }

    // === RENDER VÀ CẬP NHẬT GIAO DIỆN ===
    function renderImages() {
        imageGrid.innerHTML = '';
        if (allFiles.length === 0) {
            imageGrid.innerHTML = `<p class="col-span-full text-center text-gray-500 py-10">Không tìm thấy file nào.</p>`;
            return;
        }
        allFiles.forEach(file => {
            const card = document.createElement('div');
            card.className = 'image-card relative group aspect-square bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer border-2 border-transparent';
            card.dataset.id = file.id;
            if (selectedFiles.has(file.id)) card.classList.add('selected');
            const img = document.createElement('img');
            img.src = `${getFileUrl(file)}?t=${new Date().getTime()}`; // Thêm timestamp để tránh cache
            img.alt = file.alt_text || '';
            img.className = 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-110';
            img.loading = 'lazy';
            img.onerror = () => { img.src = 'https://placehold.co/400x400/cccccc/ffffff?text=Error'; };
            const filename = document.createElement('p');
            filename.className = 'absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate';
            filename.textContent = file.original_name;
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'absolute top-2 left-2 w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500';
            checkbox.checked = selectedFiles.has(file.id);
            checkbox.dataset.id = file.id;
            checkbox.onclick = (e) => {
                e.stopPropagation();
                toggleSelection(file.id);
            };
            card.append(img, filename, checkbox);
            imageGrid.appendChild(card);
            card.addEventListener('click', () => openDetailModal(file.id));
        });
    }

    function toggleSelection(fileId) {
        selectedFiles.has(fileId) ? selectedFiles.delete(fileId) : selectedFiles.add(fileId);
        updateSelectionUI();
    }
    
    function updateSelectionUI() {
        document.querySelectorAll('.image-card').forEach(card => {
            const id = parseInt(card.dataset.id);
            const isSelected = selectedFiles.has(id);
            card.classList.toggle('selected', isSelected);
            const checkbox = card.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = isSelected;
        });
        const deleteBtnText = deleteSelectedBtn.querySelector('span');
        if (selectedFiles.size > 0) {
            deleteSelectedBtn.classList.remove('hidden');
            if (deleteBtnText) deleteBtnText.textContent = `Xóa (${selectedFiles.size}) mục`;
        } else {
            deleteSelectedBtn.classList.add('hidden');
        }
    }

    function openDetailModal(fileId) {
        currentFileId = fileId;
        const file = allFiles.find(f => f.id === fileId);
        if (!file) return;
        const fileUrl = getFileUrl(file);
        document.getElementById('modal-image').src = fileUrl;
        document.getElementById('modal-alt').value = file.alt_text || '';
        document.getElementById('modal-filename').value = file.original_name;
        document.getElementById('modal-url').value = fileUrl;
        document.getElementById('modal-date').textContent = new Date(file.created_at).toLocaleDateString('vi-VN');
        document.getElementById('modal-type').textContent = file.mime_type;
        document.getElementById('modal-size').textContent = file.formatted_size || 'N/A';
        document.getElementById('modal-attachable').textContent = file.attachable_display || 'Không đính kèm';
        
        // Ẩn/hiện nút cắt lại tùy theo loại file
        recropBtn.style.display = file.mime_type.startsWith('image/') ? 'inline-flex' : 'none';
        
        openModal('detail-modal');
    }

    // === LOGIC CHO CROPPER ===
    function setupCropper(imageSrc, fileObject) {
        originalFile = fileObject;
        imageToCrop.src = imageSrc;
        openModal('cropper-modal');
        if (cropper) cropper.destroy();
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1, viewMode: 1, preview: '#cropper-preview',
            responsive: true, autoCropArea: 0.9, background: false,
        });
    }

    // === HÀM XỬ LÝ API ===
    async function handleApiRequest(url, formData, successMessage, errorMessage) {
        progressBar.style.display = 'block';
        try {
            const response = await axios.post(url, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: progressEvent => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    progressBarFill.style.width = percentCompleted + '%';
                    progressBarFill.textContent = percentCompleted + '%';
                }
            });

            if (isRecropMode) {
                const updatedFile = response.data.file;
                const fileIndex = allFiles.findIndex(f => f.id === currentFileId);
                if (fileIndex > -1) allFiles[fileIndex] = updatedFile;
                renderImages();
                createToast(response.data.message, 'success');
            } else {
                createToast(successMessage, 'success');
                setTimeout(() => window.location.reload(), 1500);
            }
            return response.data;
        } catch (error) {
            createToast(error.response?.data?.message || errorMessage, 'error');
            throw error;
        } finally {
            setTimeout(() => {
                progressBar.style.display = 'none';
            }, 1000);
            uploadInput.value = '';
            isRecropMode = false;
            cropAndUploadBtn.disabled = false;
            cropAndUploadBtn.innerHTML = '<i class="fas fa-crop-alt mr-2"></i> Cắt & Tải lên';
        }
    }

    // === GẮN EVENT LISTENERS ===
    uploadInput.addEventListener('change', (e) => {
        const files = e.target.files;
        if (!files || files.length === 0) return;
        if (files.length === 1 && files[0].type.startsWith('image/')) {
            isRecropMode = false;
            const reader = new FileReader();
            reader.onload = (event) => setupCropper(event.target.result, files[0]);
            reader.readAsDataURL(files[0]);
        } else {
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            handleApiRequest('{{ route("admin.media.store") }}', formData, 'Tải lên thành công!', 'Lỗi khi tải lên.');
        }
    });

    recropBtn.addEventListener('click', () => {
        if (!currentFileId) return;
        const file = allFiles.find(f => f.id === currentFileId);
        isRecropMode = true;
        closeModal('detail-modal');
        setupCropper(`${getFileUrl(file)}?t=${new Date().getTime()}`, file);
    });

    cropAndUploadBtn.addEventListener('click', () => {
        if (!cropper) return;
        cropAndUploadBtn.disabled = true;
        cropAndUploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý...';
        cropper.getCroppedCanvas({ imageSmoothingQuality: 'high' }).toBlob((blob) => {
            const formData = new FormData();
            const filename = originalFile.name || originalFile.original_name || 'recropped.png';
            
            let url, successMessage, errorMessage;
            if (isRecropMode) {
                url = `/admin/media/${currentFileId}/recrop`;
                formData.append('file', blob, filename);
                successMessage = 'Cắt lại ảnh thành công!';
                errorMessage = 'Lỗi khi cắt lại ảnh.';
            } else {
                url = '{{ route("admin.media.store") }}';
                formData.append('files[]', blob, filename);
                successMessage = 'Tải ảnh lên thành công!';
                errorMessage = 'Lỗi khi tải ảnh lên.';
            }
            handleApiRequest(url, formData, successMessage, errorMessage).finally(() => closeModal('cropper-modal'));
        }, originalFile.type || 'image/jpeg');
    });

    cancelCropBtn.addEventListener('click', () => {
        closeModal('cropper-modal');
        isRecropMode = false;
    });

    document.querySelectorAll('.btn-aspect-ratio').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (cropper) {
                cropper.setAspectRatio(parseFloat(this.dataset.aspectRatio));
                document.querySelectorAll('.btn-aspect-ratio').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    updateBtn.addEventListener('click', async () => {
        if (!currentFileId) return;
        const newAltText = document.getElementById('modal-alt').value;
        try {
            const response = await axios.patch(`/admin/media/${currentFileId}`, { alt_text: newAltText });
            const fileIndex = allFiles.findIndex(f => f.id === currentFileId);
            if (fileIndex > -1) allFiles[fileIndex].alt_text = newAltText;
            renderImages();
            closeModal('detail-modal');
            createToast(response.data.message, 'success');
        } catch(error) {
            createToast(error.response?.data?.message || 'Cập nhật thất bại.', 'error');
        }
    });

    deleteBtn.addEventListener('click', async () => {
        if (!currentFileId || !confirm('Bạn có chắc chắn muốn chuyển file này vào thùng rác?')) return;
        try {
            const response = await axios.delete(`/admin/media/${currentFileId}`);
            allFiles = allFiles.filter(f => f.id !== currentFileId);
            renderImages();
            closeModal('detail-modal');
            createToast(response.data.message || 'Đã chuyển file vào thùng rác.', 'success');
        } catch (error) {
            createToast(error.response?.data?.message || 'Xóa file thất bại.', 'error');
        }
    });
    
    // ... (Các event listener còn lại của bạn)

    // === KHỞI TẠO BAN ĐẦU ===
    renderImages();
    updateSelectionUI();
});
</script>
@endpush
