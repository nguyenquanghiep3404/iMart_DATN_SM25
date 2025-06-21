<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Lựa chọn File</title>

    <!-- Thư viện ngoài (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* CSS được copy từ file chính để đảm bảo hoạt động độc lập */
        body { background-color: #f3f4f6; }
        .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; display: inline-flex; align-items: center; justify-content: center; }
        .btn-primary { background-color: #4f46e5; color: white; }
        .btn-primary:hover { background-color: #4338ca; }
        .btn-secondary { background-color: #e5e7eb; color: #374151; border: 1px solid #d1d5db; }
        .btn-secondary:hover { background-color: #d1d5db; }
        .form-input { border-radius: 0.5rem; border: 1px solid #d1d5db; transition: all 0.2s ease-in-out; padding: 0.625rem 1rem; width: 100%; }
        .form-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); outline: none; }
        .toast-container { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 1150; display: flex; flex-direction: column; gap: 0.75rem; }
        .toast { opacity: 1; transform: translateX(0); transition: all 0.3s ease-in-out; }
        .toast.hide { opacity: 0; transform: translateX(100%); }
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.6); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background-color: #fff; margin: auto; border: none; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border-radius: 0.75rem; display: flex; flex-direction: column; }
        .modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { margin-bottom: 0; line-height: 1.5; font-size: 1.25rem; font-weight: 600; color: #1f2937; }
        .close-btn { font-size: 1.75rem; font-weight: 500; color: #6b7280; opacity: .75; background-color: transparent; border: 0; cursor: pointer; }
        .close-btn:hover { opacity: 1; color: #1f2937; }
        .modal-body { position: relative; flex: 1 1 auto; padding: 0; color: #374151; overflow-y: hidden; }
        .modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; }
        .modal-footer-actions > :not(:first-child) { margin-left: .5rem; }
        .image-card.selected { box-shadow: 0 0 0 3px #4f46e5; border-color: #4f46e5; }
        #selection-modal .modal-content { max-width: 90vw; width: 1280px; height: 90vh; }
        .progress-bar { background-color: #e5e7eb; border-radius: 0.5rem; overflow: hidden; width: 100%; height: 1.25rem; margin-top: 0.5rem; display: none; }
        .progress-bar-fill { background-color: #4f46e5; height: 100%; width: 0%; transition: width 0.3s ease-in-out; text-align: center; color: white; font-size: 0.75rem; line-height: 1.25rem; }
        .tab-link { padding: 0.75rem 1.25rem; border-bottom: 3px solid transparent; color: #6b7280; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .tab-link:hover { color: #1f2937; }
        .tab-link.active { color: #4f46e5; border-color: #4f46e5; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        #drop-zone { border: 2px dashed #d1d5db; border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.2s; background-color: #f9fafb; }
        #drop-zone.drag-over { border-color: #4f46e5; background-color: #eef2ff; }
    </style>
</head>
<body>

    <div id="toast-container"></div>
    
    <div class="p-8 text-center">
        <button id="open-library-btn" type="button" class="btn btn-primary px-6 py-3">
            <i class="fas fa-photo-film mr-2"></i>Mở Thư viện Media
        </button>
    </div>

    <!-- MODAL LỰA CHỌN FILE -->
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
                            <input type="text" id="selection-search-input" class="form-input flex-grow" placeholder="Tìm kiếm theo tên file, alt text...">
                            <input type="date" id="selection-date-filter" class="form-input">
                            <select id="selection-type-filter" class="form-input">
                                <option value="">Tất cả loại file</option>
                                <option value="image">Chỉ ảnh</option>
                                <option value="application/pdf">Chỉ PDF</option>
                                <option value="video">Chỉ Video</option>
                            </select>
                        </div>
                        <div id="selection-image-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-8 gap-4"></div>
                    </div>
                    <div id="upload-tab" class="tab-content p-4 h-full">
                         <div id="upload-progress-bar" class="progress-bar mb-4"><div class="progress-bar-fill">0%</div></div>
                        <div id="drop-zone" class="flex flex-col items-center justify-center h-full">
                            <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-4"></i>
                            <p class="text-gray-700 font-medium">Kéo và thả file vào đây</p>
                            <p class="text-gray-500 text-sm mt-1">hoặc</p>
                            <label for="modal-upload-input" class="btn btn-primary mt-4 cursor-pointer">Chọn file từ máy tính</label>
                            <input type="file" id="modal-upload-input" class="hidden" multiple accept="image/*,application/pdf,video/*">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div>
                    <span id="selection-counter" class="text-sm font-medium text-gray-700">Đã chọn: 0 file</span>
                </div>
                <div class="modal-footer-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('selection-modal')">Hủy</button>
                    <button type="button" id="insert-btn" class="btn btn-primary"><i class="fas fa-plus mr-2"></i> Chèn file đã chọn</button>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // JS cho modal hoạt động độc lập
    const initialData = [
        { id: 1, url: 'https://placehold.co/600x400/a3e635/ffffff?text=Image+1', original_name: 'nature_photo_1.jpg', alt_text: 'A beautiful landscape', created_at: '2023-10-27T10:00:00Z', mime_type: 'image/jpeg', size: 123456 },
        { id: 2, url: 'https://placehold.co/600x400/60a5fa/ffffff?text=Image+2', original_name: 'city_at_night.png', alt_text: 'City skyline at night', created_at: '2023-10-26T15:30:00Z', mime_type: 'image/png', size: 234567 },
        { id: 4, url: 'https://placehold.co/400x400/f87171/ffffff?text=PDF', original_name: 'document.pdf', alt_text: 'Important document', created_at: '2023-10-24T18:05:00Z', mime_type: 'application/pdf', size: 345678 },
        { id: 5, url: 'https://placehold.co/600x400/34d399/ffffff?text=Image+5', original_name: 'beach_vacation.jpg', alt_text: 'Sunny beach with palm trees', created_at: '2023-10-23T11:20:00Z', mime_type: 'image/jpeg', size: 456789 },
        { id: 7, url: 'https://placehold.co/400x400/93c5fd/ffffff?text=VIDEO', original_name: 'intro.mp4', alt_text: 'Intro video', created_at: '2023-10-21T16:00:00Z', mime_type: 'video/mp4', size: 5123456 },
    ];
    let allFiles = [...initialData];
    let selectionForInsert = new Set();
    
    const openLibraryBtn = document.getElementById('open-library-btn');
    const selectionModal = document.getElementById('selection-modal');
    const selectionImageGrid = document.getElementById('selection-image-grid');
    const selectionCounter = document.getElementById('selection-counter');
    const insertBtn = document.getElementById('insert-btn');

    window.openModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if(modal) { modal.classList.add('show'); document.body.style.overflow = 'hidden'; }
    };
    window.closeModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if(modal) { modal.classList.remove('show'); document.body.style.overflow = 'auto'; }
    };
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
        }
    });

    const createToast = (message, type = 'success') => {
        const container = document.getElementById('toast-container');
        const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500' };
        const toast = document.createElement('div');
        toast.className = `toast p-4 rounded-lg shadow-lg text-white ${colors[type] || 'bg-gray-800'}`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 350);
        }, 3000);
    };
    
    const getFileThumbnail = (file) => {
        if (file.mime_type.startsWith('image/')) return file.url;
        if (file.mime_type === 'application/pdf') return 'https://placehold.co/400x400/f87171/ffffff?text=PDF';
        if (file.mime_type.startsWith('video/')) return 'https://placehold.co/400x400/93c5fd/ffffff?text=VIDEO';
        return 'https://placehold.co/400x400/d1d5db/ffffff?text=FILE';
    };

    const renderImageGrid = (container, files, selectionSet) => {
        container.innerHTML = '';
        if (files.length === 0) {
            container.innerHTML = `<p class="col-span-full text-center text-gray-500 py-10">Không tìm thấy file nào.</p>`;
            return;
        }
        files.forEach(file => {
            const isSelected = selectionSet.has(file.id);
            const card = document.createElement('div');
            card.className = `image-card relative group aspect-square bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer border-2 ${isSelected ? 'selected' : 'border-transparent'}`;
            card.dataset.id = file.id;
            const img = document.createElement('img');
            img.src = getFileThumbnail(file);
            img.alt = file.alt_text;
            img.className = 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-110';
            const filename = document.createElement('p');
            filename.className = 'absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate';
            filename.textContent = file.original_name;
            const checkIcon = document.createElement('div');
            checkIcon.className = `absolute top-2 right-2 w-6 h-6 rounded-full bg-white/50 backdrop-blur-sm flex items-center justify-center transition-all ${isSelected ? 'opacity-100 scale-100' : 'opacity-0 scale-50'}`;
            checkIcon.innerHTML = `<i class="fas fa-check-circle text-2xl text-indigo-600"></i>`;
            card.append(img, filename, checkIcon);
            card.addEventListener('click', () => toggleSelection(file.id, selectionSet, container));
            container.appendChild(card);
        });
    };
    
    const toggleSelection = (fileId, selectionSet, container) => {
        selectionSet.has(fileId) ? selectionSet.delete(fileId) : selectionSet.add(fileId);
        const { searchTerm, type, date } = getSelectionFilters();
        const filteredFiles = filterFiles(allFiles, searchTerm, type, date);
        renderImageGrid(container, filteredFiles, selectionSet);
        updateSelectionCounter();
    };

    const updateSelectionCounter = () => {
        const count = selectionForInsert.size;
        selectionCounter.textContent = `Đã chọn: ${count} file`;
        insertBtn.disabled = count === 0;
        insertBtn.classList.toggle('opacity-50', count === 0);
    };

    const handleInsert = () => {
        if (selectionForInsert.size === 0) return;
        const selected = allFiles.filter(f => selectionForInsert.has(f.id));
        console.log('Các file đã được chọn để chèn:', selected);
        createToast(`Đã chọn ${selected.length} file. Kiểm tra console để xem chi tiết.`, 'info');
        closeModal('selection-modal');
    };
    
    const filterFiles = (files, searchTerm, type, date) => {
        let filtered = files;
        if (searchTerm) { filtered = filtered.filter(f => f.original_name.toLowerCase().includes(searchTerm) || (f.alt_text && f.alt_text.toLowerCase().includes(searchTerm)));}
        if (type) { filtered = filtered.filter(f => f.mime_type.startsWith(type)); }
        if (date) { filtered = filtered.filter(f => f.created_at.startsWith(date)); }
        return filtered;
    };
    
    const getSelectionFilters = () => ({
        searchTerm: document.getElementById('selection-search-input').value.toLowerCase().trim(),
        type: document.getElementById('selection-type-filter').value,
        date: document.getElementById('selection-date-filter').value
    });
    const applyFiltersAndRender = () => renderImageGrid(selectionImageGrid, filterFiles(allFiles, ...Object.values(getSelectionFilters())), selectionForInsert);

    const setupTabs = () => {
        selectionModal.querySelectorAll('.tab-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabId = link.dataset.tab;
                selectionModal.querySelectorAll('.tab-link').forEach(l => l.classList.remove('active'));
                selectionModal.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                link.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    };
    
    const setupUpload = (input, dropZone, progressBarEl) => {
        const handleFiles = (files) => {
            if (files.length === 0) return;
            const barFill = progressBarEl.querySelector('.progress-bar-fill');
            progressBarEl.style.display = 'block'; let currentProgress = 0;
            const interval = setInterval(() => {
                currentProgress += 10; if(currentProgress > 100) currentProgress = 100;
                barFill.style.width = currentProgress + '%'; barFill.textContent = currentProgress + '%';
                if(currentProgress === 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        const newFiles = Array.from(files).map((f, i) => ({ id: Date.now() + i, url: URL.createObjectURL(f), original_name: f.name, alt_text: '', created_at: new Date().toISOString(), mime_type: f.type, size: f.size }));
                        allFiles.unshift(...newFiles); initialData.unshift(...newFiles);
                        selectionModal.querySelector('.tab-link[data-tab="library-tab"]').click();
                        selectionForInsert.clear();
                        newFiles.forEach(nf => selectionForInsert.add(nf.id));
                        applyFiltersAndRender(); updateSelectionCounter();
                        createToast(`Đã tải lên ${newFiles.length} file mới.`, 'success');
                        progressBarEl.style.display = 'none'; barFill.style.width = '0%'; barFill.textContent = '0%';
                    }, 500);
                }
            }, 100);
        };
        input.addEventListener('change', () => handleFiles(input.files));
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.classList.remove('drag-over'); });
        dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.classList.remove('drag-over'); handleFiles(e.dataTransfer.files); });
    };

    openLibraryBtn.addEventListener('click', () => {
        selectionForInsert.clear();
        applyFiltersAndRender();
        updateSelectionCounter();
        openModal('selection-modal');
    });
    insertBtn.addEventListener('click', handleInsert);
    
    ['input', 'change'].forEach(evt => {
        document.getElementById('selection-search-input').addEventListener(evt, applyFiltersAndRender);
        document.getElementById('selection-date-filter').addEventListener(evt, applyFiltersAndRender);
        document.getElementById('selection-type-filter').addEventListener(evt, applyFiltersAndRender);
    });

    setupTabs();
    setupUpload(document.getElementById('modal-upload-input'), document.getElementById('drop-zone'), document.getElementById('upload-progress-bar'));
    updateSelectionCounter();
    applyFiltersAndRender(); // Render lần đầu
});
</script>

</body>
</html>
