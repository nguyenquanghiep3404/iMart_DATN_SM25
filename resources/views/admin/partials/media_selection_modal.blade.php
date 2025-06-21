
@push('styles')
    <style>
        /* CSS cần thiết cho modal được đặt ngay tại đây để đảm bảo tính đóng gói. */
        /* Bạn cũng có thể chuyển CSS này vào file CSS chung nếu muốn. */
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.6); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border-radius: 0.75rem; display: flex; flex-direction: column; }
        .modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .close-btn { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
        .close-btn:hover { opacity: 1; color: #1f2937; }
        .modal-body { position: relative; flex: 1 1 auto; padding: 0; color: #374151; overflow-y: hidden; }
        .modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; }
        #selection-modal .modal-content { max-width: 90vw; width: 1280px; height: 90vh; }
        .image-card.selected { box-shadow: 0 0 0 3px #4f46e5; border-color: #4f46e5; }
        .tab-link { padding: 0.75rem 1.25rem; border-bottom: 3px solid transparent; color: #6b7280; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .tab-link.active { color: #4f46e5; border-color: #4f46e5; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        #drop-zone { border: 2px dashed #d1d5db; border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.2s; background-color: #f8fafc; height: 100%; }
        #drop-zone.drag-over { border-color: #4f46e5; background-color: #eef2ff; }
    </style>
@endpush

<!-- HTML CỦA MODAL -->
<div id="selection-modal" class="modal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Chọn file từ thư viện</h5>
            <button type="button" class="close-btn" onclick="closeModal('selection-modal')"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body flex flex-col">
            <div class="border-b border-gray-200 px-4">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <a href="#" class="tab-link active" data-tab="library-tab"><i class="fas fa-photo-film mr-2"></i>Thư viện</a>
                    <a href="#" class="tab-link" data-tab="upload-tab"><i class="fas fa-upload mr-2"></i>Tải file mới</a>
                </nav>
            </div>
            <div class="flex-grow overflow-y-auto">
                <div id="library-tab" class="tab-content active p-4">
                    <div class="flex flex-col sm:flex-row gap-4 mb-4">
                        <input type="text" id="selection-search-input" class="input-field" placeholder="Tìm kiếm...">
                        <input type="date" id="selection-date-filter" class="input-field">
                        <select id="selection-type-filter" class="select-field">
                            <option value="">Tất cả loại file</option>
                            <option value="image">Chỉ ảnh</option>
                        </select>
                    </div>
                    <div id="selection-image-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-8 gap-4"></div>
                    <div id="selection-image-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-8 gap-4"></div>

<div id="pagination-container" class="mt-6 flex items-center justify-center"></div>
                </div>
                <div id="upload-tab" class="tab-content p-4 h-full">
                     <div id="upload-progress-bar" class="progress-bar mb-4"><div class="progress-bar-fill">0%</div></div>
                    <div id="drop-zone" class="flex flex-col items-center justify-center">
                        <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-4"></i>
                        <p class="text-gray-700 font-medium">Kéo và thả file vào đây</p>
                        <p class="text-gray-500 text-sm mt-1">hoặc</p>
                        <label for="modal-upload-input" class="btn btn-primary mt-4 cursor-pointer">Chọn file từ máy tính</label>
                        <input type="file" id="modal-upload-input" class="hidden" multiple accept="image/*">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div><span id="selection-counter" class="text-sm font-medium text-gray-700">Đã chọn: 0 file</span></div>
            <div>
                <button type="button" class="btn btn-secondary" onclick="closeModal('selection-modal')">Hủy</button>
                <button type="button" id="insert-btn" class="btn btn-primary"><i class="fas fa-plus mr-2"></i> Chèn file đã chọn</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tránh khởi tạo lại script nếu nó đã tồn tại
    if (window.mediaLibraryInitialized) return;
    window.mediaLibraryInitialized = true;

    // === CÁC BIẾN DOM VÀ TRẠNG THÁI CỦA MODAL ===
    const selectionModal = document.getElementById('selection-modal');
    const selectionImageGrid = document.getElementById('selection-image-grid');
    const selectionCounter = document.getElementById('selection-counter');
    const insertBtn = document.getElementById('insert-btn');
    const searchInput = document.getElementById('selection-search-input');
    const modalUploadInput = document.getElementById('modal-upload-input');
    // === ADDED: Lấy container phân trang ===
    const paginationContainer = document.getElementById('pagination-container');


    let allMediaFiles = [];
    let selectedForInsert = new Set();
    let isLoading = false;
    let currentPage = 1;
    // lastPage không còn quá cần thiết khi có links từ Laravel, nhưng giữ lại cũng không sao
    let lastPage = 1;

    // === CÁC HÀM XỬ LÝ MODAL (MỞ/ĐÓNG, TAB) ===
    window.openModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };
    window.closeModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    };

    // === HÀM CHÍNH ĐỂ MỞ THƯ VIỆN ===
    window.openMediaLibrary = () => {
        selectedForInsert.clear();
        updateSelectionCounter();
        openModal('selection-modal');
        // Luôn fetch lại trang 1 khi mở modal để có dữ liệu mới nhất
        fetchMediaFiles(1, searchInput.value.trim());
    };

    // === CÁC HÀM GỌI API VÀ RENDER ===
    async function fetchMediaFiles(page = 1, search = '') {
        if (isLoading) return;
        isLoading = true;
        // Hiển thị loading spinner
        selectionImageGrid.innerHTML = `<div class="col-span-full flex justify-center items-center py-10"><i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i></div>`;
        paginationContainer.innerHTML = ''; // Xóa phân trang cũ

        const url = `{{ route('admin.media.fetchForModal') }}?page=${page}&search=${search}`;
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Network response was not ok.');

            const data = await response.json();

            // === MODIFIED: Luôn thay thế dữ liệu, không nối chuỗi ===
            allMediaFiles = data.data; // Chỉ chứa ảnh của trang hiện tại
            currentPage = data.current_page;
            lastPage = data.last_page;

            renderImageGrid();
            // === ADDED: Render các nút phân trang ===
            renderPagination(data);

        } catch (error) {
            console.error("Lỗi khi fetch media:", error);
            selectionImageGrid.innerHTML = `<p class="col-span-full text-center text-red-500">Không thể tải thư viện.</p>`;
        } finally {
            isLoading = false;
        }
    }

    const renderImageGrid = () => {
        // === MODIFIED: Đã chuyển việc xóa grid ra ngoài trước khi fetch ===
        // Giờ hàm này chỉ tập trung vào việc render
        selectionImageGrid.innerHTML = '';
        if (allMediaFiles.length === 0) {
            selectionImageGrid.innerHTML = `<p class="col-span-full text-center text-gray-500 py-10">Không tìm thấy file nào.</p>`;
            return;
        }
        allMediaFiles.forEach(file => {
            const isSelected = selectedForInsert.has(file.id);
            const card = document.createElement('div');
            card.className = `image-card relative group aspect-square bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer border-2 ${isSelected ? 'selected' : 'border-transparent'}`;
            card.dataset.id = file.id;
            card.innerHTML = `
                <img src="${file.url}" alt="${file.alt_text || ''}" class="w-full h-full object-cover">
                <p class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate">${file.original_name}</p>
                <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-white/50 flex items-center justify-center transition-all ${isSelected ? 'opacity-100' : 'opacity-0'}">
                    <i class="fas fa-check-circle text-2xl text-indigo-600"></i>
                </div>
            `;
            card.addEventListener('click', () => toggleSelection(file.id));
            selectionImageGrid.appendChild(card);
        });
    };

    // === ADDED: Hàm mới để render phân trang ===
    const renderPagination = (paginator) => {
        paginationContainer.innerHTML = ''; // Dọn dẹp trước khi render
        if (!paginator || !paginator.links || paginator.last_page <= 1) {
            return; // Không hiển thị nếu chỉ có 1 trang
        }

        const nav = document.createElement('nav');
        nav.setAttribute('aria-label', 'Pagination');
        const list = document.createElement('ul');
        list.className = 'inline-flex items-center -space-x-px';

        paginator.links.forEach(link => {
            const listItem = document.createElement('li');
            const linkTag = document.createElement('a');
            linkTag.href = '#';
            linkTag.innerHTML = link.label; // Dùng innerHTML để render các ký tự như &laquo;
            linkTag.className = 'px-3 py-2 leading-tight border transition-colors duration-150';

            // Styling chung
            if (link.active) {
                linkTag.className += ' bg-indigo-600 border-indigo-600 text-white cursor-default';
                linkTag.setAttribute('aria-current', 'page');
            } else if (!link.url) {
                linkTag.className += ' bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed';
            } else {
                linkTag.className += ' bg-white border-gray-300 text-gray-500 hover:bg-gray-100 hover:text-gray-700';
                linkTag.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (!isLoading) {
                        const url = new URL(link.url);
                        const page = url.searchParams.get('page');
                        fetchMediaFiles(page, searchInput.value.trim());
                    }
                });
            }
            listItem.appendChild(linkTag);
            list.appendChild(listItem);
        });
        nav.appendChild(list);
        paginationContainer.appendChild(nav);
    }


    // === CÁC HÀM XỬ LÝ HÀNH ĐỘNG (CHỌN, CHÈN, UPLOAD) ===
    const toggleSelection = (fileId) => {
        const card = selectionImageGrid.querySelector(`.image-card[data-id="${fileId}"]`);
        if (selectedForInsert.has(fileId)) {
            selectedForInsert.delete(fileId);
            card.classList.remove('selected');
        } else {
            selectedForInsert.add(fileId);
            card.classList.add('selected');
        }
        updateSelectionCounter();
    };

    const updateSelectionCounter = () => {
        const count = selectedForInsert.size;
        if (selectionCounter) selectionCounter.textContent = `Đã chọn: ${count} file`;
        if (insertBtn) insertBtn.disabled = count === 0;
    };

    const handleInsert = () => {
        if (!window.mediaLibraryTarget) {
            alert('Lỗi: Không xác định được nơi để chèn ảnh!');
            return;
        }
        const { previewContainer, idsContainer, type, variantIndex } = window.mediaLibraryTarget;
        // Lọc lại `allMediaFiles` để chắc chắn chỉ lấy những file đang hiển thị trên trang hiện tại
        const selectedImages = allMediaFiles.filter(f => selectedForInsert.has(f.id));
        if (previewContainer && idsContainer) {
            if (window.addImagesToProductForm) {
                window.addImagesToProductForm(selectedImages, previewContainer, idsContainer, type, variantIndex);
            } else {
                alert('Lỗi: Không tìm thấy hàm addImagesToProductForm.');
            }
        } else {
            console.error('Không tìm thấy container xem trước hoặc container ID.', window.mediaLibraryTarget);
            alert('Lỗi: Không tìm thấy vùng chứa ảnh trên form.');
        }
        closeModal('selection-modal');
    };

    const handleModalUpload = async (files) => {
        if (!files.length) return;
        try {
            const result = await uploadFilesViaAjax(files);
            if (result.files && result.files.length > 0) {
                // Tự động chuyển về tab thư viện và tải lại trang 1 để thấy ảnh mới nhất
                document.querySelector('.tab-link[data-tab="library-tab"]').click();
                fetchMediaFiles(1, searchInput.value.trim()); // Tải lại trang 1
            }
        } catch (error) {
            alert('Upload thất bại: ' + error.message);
        } finally {
            modalUploadInput.value = '';
        }
    }


    // === GẮN SỰ KIỆN ===
    if (insertBtn) insertBtn.addEventListener('click', handleInsert);
    if (modalUploadInput) modalUploadInput.onchange = (e) => handleModalUpload(e.target.files);

    // Tìm kiếm
    let searchTimeout;
    if (searchInput) searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchMediaFiles(1, searchInput.value.trim()); // Luôn tìm kiếm từ trang 1
        }, 500);
    });
    // Khởi tạo tab
    document.querySelectorAll('#selection-modal .tab-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const tabId = link.dataset.tab;
            document.querySelectorAll('#selection-modal .tab-link').forEach(l => l.classList.remove('active'));
            document.querySelectorAll('#selection-modal .tab-content').forEach(c => c.classList.remove('active'));
            link.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Drag-drop
    const dropZone = document.getElementById('drop-zone');
    if (dropZone) {
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('drag-over'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('drag-over');
            handleModalUpload(e.dataTransfer.files);
        });
    }

    updateSelectionCounter();
});
</script>
@endpush

