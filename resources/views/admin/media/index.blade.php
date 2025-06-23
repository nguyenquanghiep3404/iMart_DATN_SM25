@extends('admin.layouts.app')

@section('title', 'Thư viện Media')

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
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
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

        /* --- CSS RIÊNG CHO THƯ VIỆN MEDIA --- */
        .image-card.selected {
            box-shadow: 0 0 0 3px #4f46e5; /* Màu primary */
            border-color: #4f46e5;
        }
        #detail-modal .modal-content {
            max-width: 80rem; /* Tăng chiều rộng modal chi tiết */
        }
        .progress-bar {
            background-color: #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            width: 100%;
            height: 1.25rem;
            margin-top: 0.5rem;
            display: none;
        }
        .progress-bar-fill {
            background-color: #4f46e5;
            height: 100%;
            width: 0%;
            transition: width 0.3s ease-in-out;
            text-align: center;
            color: white;
            font-size: 0.75rem;
            line-height: 1.25rem;
        }
        /* === UPDATED ACTIVE FILTER BUTTON COLOR === */
        .btn-filter.active {
            background-color: #3b82f6; /* Blue-500 */
            color: white;
            border-color: #3b82f6;
        }
        /* Style cho card thống kê */
        .stat-card {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
        }
        .stat-card .icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
        }
    </style>
@endpush

@section('content')
<div class="body-content px-6 md:px-8 py-8">
    {{-- Container cho Toast Notification --}}
    <div id="toast-container"></div>
    @include('admin.partials.flash_message')

    <div class="container mx-auto max-w-full">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Thư viện Media</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    {{-- <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li> --}}
                    <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Media</li>
                </ol>
            </nav>
        </div>

        {{-- === KHU VỰC THỐNG KÊ === --}}
        {{-- Ghi chú: Cần truyền biến $stats từ Controller, ví dụ: $stats = ['total' => 100, 'attached' => 80, 'unattached' => 20] --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Card Tổng số files -->
            <div class="stat-card">
                <div class="icon bg-blue-100 text-blue-600">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Tổng số Files</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</p>
                </div>
            </div>
            <!-- Card Files đã gán -->
            <div class="stat-card">
                <div class="icon bg-green-100 text-green-600">
                    <i class="fas fa-link"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Đã gán</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['attached'] ?? 0 }}</p>
                </div>
            </div>
            <!-- Card Files chưa gán -->
            <div class="stat-card">
                <div class="icon bg-yellow-100 text-yellow-600">
                    <i class="fas fa-unlink"></i>
                </div>
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
                        {{-- Form tìm kiếm --}}
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

                        {{-- BỘ LỌC NGÀY --}}
                        <div>
                             <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                            <input type="date" name="start_date" id="start_date" class="form-input py-2.5 text-sm" value="{{ request('start_date') }}">
                        </div>
                        <div>
                             <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                            <input type="date" name="end_date" id="end_date" class="form-input py-2.5 text-sm" value="{{ request('end_date') }}">
                        </div>
                        
                        {{-- Nút lọc và xóa lọc --}}
                        <div class="flex items-center gap-2">
                             <button type="submit" class="btn btn-primary py-2.5 px-5 text-sm">Lọc</button>
                             <a href="{{ route('admin.media.index') }}" class="btn btn-secondary py-2.5 px-5 text-sm">Xóa lọc</a>
                        </div>
                    </div>

                    {{-- Giữ lại các tham số khác khi submit --}}
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
                        <input type="file" id="upload-input" class="hidden" multiple accept="image/*,application/pdf">
                    </div>
                </div>
                <div id="upload-progress-bar" class="progress-bar">
                    <div class="progress-bar-fill">0%</div>
                </div>
            </div>
            <div class="p-5">
                <main id="image-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-4 min-h-[400px]">
                    {{-- Dữ liệu ảnh sẽ được render bởi JavaScript vào đây --}}
                </main>
            </div>
             @if ($files->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {{-- Đảm bảo link phân trang giữ lại query string (search, filter, dates) --}}
                {!! $files->appends(request()->query())->links() !!}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL HIỂN THỊ CHI TIẾT ẢNH -->
<div id="detail-modal" class="modal" tabindex="-1">
    <div class="modal-content !max-w-4xl flex flex-col md:flex-row max-h-[90vh]">
        <!-- Phần hiển thị ảnh -->
        <div class="w-full md:w-2/3 p-4 flex items-center justify-center bg-gray-100 rounded-t-lg md:rounded-l-lg md:rounded-t-none">
            <img id="modal-image" src="" alt="Image preview" class="max-w-full max-h-[40vh] md:max-h-[80vh] object-contain">
        </div>
        <!-- Phần thông tin và form chỉnh sửa -->
        <div class="w-full md:w-1/3 flex flex-col">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết File</h5>
                <button type="button" class="close" onclick="closeModal('detail-modal')"><span aria-hidden="true">&times;</span></button>
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
            <!-- === UPDATED BUTTON LAYOUT === -->
            <div class="modal-footer">
                <div class="flex w-full gap-x-3">
                    <button type="button" id="delete-btn" class="btn btn-danger w-full py-2 px-4 text-sm flex-1">Chuyển vào thùng rác</button>
                    <button type="button" id="update-btn" class="btn btn-primary w-full py-2 px-4 text-sm flex-1">Lưu thay đổi</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === BẮT BUỘC: CẤU HÌNH CSRF TOKEN CHO TẤT CẢ REQUESTS CỦA AXIOS ===
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    } else {
        console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
        alert('Lỗi bảo mật: CSRF token không được tìm thấy. Vui lòng kiểm tra layout chính.');
    }

    // === SCRIPT ĐẦY ĐỦ CHO MODAL ===
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
    window.addEventListener('click', function(event) {
        document.querySelectorAll('.modal.show').forEach(modal => {
            if (event.target.closest('.modal-content') === null && event.target.classList.contains('modal')) {
                closeModal(modal.id);
            }
        });
    });
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
        }
    });

    // === SCRIPT RIÊNG CHO THƯ VIỆN MEDIA ===
    
    // NẠP DỮ LIỆU BAN ĐẦU TỪ CONTROLLER
    let allFiles = @json($files->items());
    
    const imageGrid = document.getElementById('image-grid');
    const uploadInput = document.getElementById('upload-input');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    const selectAllBtn = document.getElementById('select-all-btn'); // Nút chọn tất cả mới
    const progressBar = document.getElementById('upload-progress-bar');
    const progressBarFill = progressBar.querySelector('.progress-bar-fill');
    
    // Modal elements
    const detailModal = document.getElementById('detail-modal');
    const updateBtn = document.getElementById('update-btn');
    const deleteBtn = document.getElementById('delete-btn');
    const copyUrlBtn = document.getElementById('copy-url-btn');

    let selectedFiles = new Set();
    let currentFileId = null;

    // --- CÁC HÀM TIỆN ÍCH ---
    function formatBytes(bytes, decimals = 2) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    function getFileUrl(file) {
        if (file && file.url) {
            return file.url;
        }
        if (file && file.path) {
            return `/storage/${file.path}`;
        }
        return 'https://placehold.co/400x400/cccccc/ffffff?text=Invalid+Path';
    }

    function createToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
        };

        const toast = document.createElement('div');
        toast.className = `toast p-4 rounded-lg shadow-lg text-white ${colors[type] || 'bg-gray-800'}`;
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 350);
        }, 3000);
    }

    // --- CÁC HÀM RENDER VÀ CẬP NHẬT GIAO DIỆN ---
    function renderImages() {
        imageGrid.innerHTML = ''; // Xóa lưới cũ
        if (allFiles.length === 0) {
            const searchTerm = "{{ request('search', '') }}";
            const filter = "{{ request('filter', '') }}";
            const startDate = "{{ request('start_date', '') }}";
            let message = 'Không tìm thấy file nào.';
            if (searchTerm || startDate) {
                 message = `Không tìm thấy file nào phù hợp với tiêu chí lọc.`;
            } else if (filter === 'unattached') {
                message = 'Không có ảnh mồ côi nào.';
            }
            imageGrid.innerHTML = `<p class="col-span-full text-center text-gray-500 py-10">${message}</p>`;
            return;
        }

        allFiles.forEach(file => {
            const card = document.createElement('div');
            card.className = 'image-card relative group aspect-square bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer border-2 border-transparent';
            card.dataset.id = file.id;
            if (selectedFiles.has(file.id)) {
                card.classList.add('selected');
            }

            const img = document.createElement('img');
            img.src = getFileUrl(file);
            img.alt = file.alt_text || '';
            img.className = 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-110';
            img.loading = 'lazy'; // Thêm lazy loading
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
        if (selectedFiles.has(fileId)) {
            selectedFiles.delete(fileId);
        } else {
            selectedFiles.add(fileId);
        }
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
            if (deleteBtnText) {
               deleteBtnText.textContent = `Xóa (${selectedFiles.size}) mục`;
            }
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
        document.getElementById('modal-size').textContent = file.formatted_size || formatBytes(file.size);
        document.getElementById('modal-attachable').textContent = file.attachable_display || 'Không đính kèm';
        
        openModal('detail-modal');
    }
    
    // --- CÁC HÀM XỬ LÝ SỰ KIỆN VÀ GỌI API ---
    async function handleUpload(files) {
        if (files.length === 0) return;

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
        formData.append('context', 'general'); // Mặc định context
        progressBar.style.display = 'block';
        
        try {
            const response = await axios.post('{{ route("admin.media.store") }}', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: progressEvent => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    progressBarFill.style.width = percentCompleted + '%';
                    progressBarFill.textContent = percentCompleted + '%';
                }
            });

            // Sau khi upload thành công, làm mới trang để hiển thị file mới nhất
            createToast(`Đã tải lên thành công ${response.data.files.length} file! Đang làm mới...`, 'success');
            setTimeout(() => window.location.reload(), 1500);

        } catch (error) {
            console.error('Upload error details:', error.response?.data || error);
            let errorMessage = 'Đã có lỗi xảy ra khi tải lên.';
            if (error.response && error.response.data && error.response.data.message) {
                 errorMessage = error.response.data.message;
            }
            createToast(errorMessage, 'error');
        } finally {
             setTimeout(() => {
                 progressBar.style.display = 'none';
                 progressBarFill.style.width = '0%';
                 progressBarFill.textContent = '0%';
               }, 1000);
             uploadInput.value = ''; // Reset input
        }
    }

    async function handleUpdate() {
        if (!currentFileId) return;
        const newAltText = document.getElementById('modal-alt').value;
        
        try {
            const response = await axios.patch(`/admin/media/${currentFileId}`, {
                alt_text: newAltText
            });

            const fileIndex = allFiles.findIndex(f => f.id === currentFileId);
            if (fileIndex > -1) {
                allFiles[fileIndex].alt_text = newAltText;
                // Cập nhật alt text trên card ảnh luôn
                const card = imageGrid.querySelector(`.image-card[data-id="${currentFileId}"]`);
                if(card) {
                    card.querySelector('img').alt = newAltText;
                }
            }
            closeModal('detail-modal');
            createToast(response.data.message, 'success');
        } catch(error) {
            const errorMessage = error.response?.data?.message || 'Cập nhật thất bại.';
            createToast(errorMessage, 'error');
        }
    }

    // Sửa lại handleDelete để dùng soft delete
    async function handleDelete(fileId) {
        if (!confirm('Bạn có chắc chắn muốn chuyển file này vào thùng rác?')) return;

        try {
            // Controller đã dùng soft delete, nên chỉ cần gọi API
            const response = await axios.delete(`/admin/media/${fileId}`);
            
            // Xóa file khỏi mảng và render lại
            allFiles = allFiles.filter(f => f.id !== fileId);
            renderImages();

            if (detailModal.classList.contains('show')) {
                closeModal('detail-modal');
            }
            createToast(response.data.message || 'Đã chuyển file vào thùng rác.', 'success');
        } catch (error) {
            const errorMessage = error.response?.data?.message || 'Xóa file thất bại.';
            createToast(errorMessage, 'error');
        }
    }

    async function handleDeleteSelected() {
        if (selectedFiles.size === 0) return;
        if (!confirm(`Bạn có chắc chắn muốn chuyển ${selectedFiles.size} file đã chọn vào thùng rác?`)) return;

        const idsToDelete = Array.from(selectedFiles);
        
        try {
            // Thay vì lặp, gửi một request duy nhất để xóa hàng loạt
            const response = await axios.post('{{ route("admin.media.bulk-delete") }}', {
                ids: idsToDelete
            });

            allFiles = allFiles.filter(f => !idsToDelete.includes(f.id));
            selectedFiles.clear();
            renderImages();
            updateSelectionUI();
            createToast(response.data.message, 'success');

        } catch (error) {
            console.error('Bulk delete failed:', error);
            const errorMessage = error.response?.data?.message || 'Xóa hàng loạt thất bại.';
            createToast(errorMessage, 'error');
        }
    }

    function handleSelectAll() {
        // Lấy tất cả ID từ `allFiles` (danh sách file hiện tại trên trang)
        const allCurrentFileIds = allFiles.map(file => file.id);
        
        // Kiểm tra xem tất cả đã được chọn hay chưa
        const allAreSelected = allCurrentFileIds.every(id => selectedFiles.has(id));

        if (allAreSelected) {
            // Nếu tất cả đã được chọn -> Bỏ chọn tất cả
             allCurrentFileIds.forEach(id => selectedFiles.delete(id));
             createToast('Đã bỏ chọn tất cả file trên trang này.', 'info');
        } else {
            // Nếu chưa -> Chọn tất cả
            allCurrentFileIds.forEach(id => selectedFiles.add(id));
            createToast(`Đã chọn tất cả ${allCurrentFileIds.length} file trên trang này.`, 'success');
        }
        updateSelectionUI();
    }
    
    // --- GẮN CÁC EVENT LISTENER ---
    uploadInput.addEventListener('change', () => handleUpload(uploadInput.files));
    updateBtn.addEventListener('click', handleUpdate);
    deleteBtn.addEventListener('click', () => handleDelete(currentFileId));
    deleteSelectedBtn.addEventListener('click', handleDeleteSelected);
    
    // Chỉ gắn event cho nút "Chọn tất cả" nếu nó tồn tại
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', handleSelectAll);
    }
    
    copyUrlBtn.addEventListener('click', (e) => {
        const urlInput = document.getElementById('modal-url');
        navigator.clipboard.writeText(urlInput.value).then(() => {
            const button = e.currentTarget;
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check text-green-500"></i>';
            setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 2000);
        }).catch(err => {
            createToast('Không thể sao chép!', 'error');
        });
    });

    // --- KHỞI TẠO BAN ĐẦU ---
    renderImages();
    updateSelectionUI(); // Cập nhật nút xóa nếu có file được chọn sẵn (hiếm)
});
</script>
@endpush
