// Đặt đoạn mã này trong một file JS riêng và tải nó trong layout chính.
// Hoặc đặt trong thẻ <script> của admin/layouts/app.blade.php

const mediaLibraryModal = {
    // --- State Management ---
    isOpen: false,
    allowMultiple: false,
    selectedIds: new Set(),
    onSelectCallback: null,

    // --- DOM Elements ---
    elements: {
        modal: document.getElementById('media-library-modal'),
        grid: document.getElementById('media-library-grid'),
        loading: document.getElementById('media-library-loading'),
        selectBtn: document.getElementById('media-library-select-btn'),
        selectBtnText: document.getElementById('media-library-select-text'),
        paginationContainer: document.getElementById('media-library-pagination'),
    },

    /**
     * Mở modal với các tùy chọn.
     * @param {object} options - Tùy chọn: { multiple: boolean, onSelect: function }
     */
    open(options = {}) {
        this.allowMultiple = options.multiple || false;
        this.onSelectCallback = options.onSelect || function() {};
        
        this.selectedIds.clear();
        this.updateSelectButton();
        this.elements.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        this.isOpen = true;

        this.fetchAndRender(); // Tải dữ liệu trang đầu tiên
    },

    close() {
        this.elements.modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        this.isOpen = false;
        this.onSelectCallback = null;
    },
    
    /**
     * Tải dữ liệu ảnh từ server và render ra lưới.
     * @param {string} url - URL để fetch, mặc định là trang 1.
     */
    async fetchAndRender(url = "{{ route('admin.media.fetch') }}") {
        this.elements.loading.style.display = 'block';
        this.elements.grid.innerHTML = ''; // Xóa ảnh cũ
        this.elements.grid.appendChild(this.elements.loading);

        try {
            const response = await fetch(url);
            const data = await response.json();
            
            this.elements.loading.style.display = 'none';

            if (data.data.length === 0) {
                 this.elements.grid.innerHTML = `<p class="col-span-full text-center text-gray-500 py-10">Thư viện trống.</p>`;
            } else {
                 data.data.forEach(file => this.elements.grid.appendChild(this.createImageCard(file)));
            }
            
            this.renderPagination(data);
        } catch (error) {
            console.error('Lỗi khi tải thư viện media:', error);
            this.elements.grid.innerHTML = `<p class="col-span-full text-center text-red-500 py-10">Không thể tải thư viện. Vui lòng thử lại.</p>`;
        }
    },

    /**
     * Tạo một card ảnh cho lưới.
     * @param {object} file - Dữ liệu file từ server.
     * @returns {HTMLElement} - Phần tử div của card.
     */
    createImageCard(file) {
        const card = document.createElement('div');
        card.className = 'image-card relative group aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 border-transparent';
        card.dataset.id = file.id;

        const img = document.createElement('img');
        img.src = file.url;
        img.alt = file.alt_text || '';
        img.className = 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-110';
        img.onerror = () => { img.src = 'https://placehold.co/400x400/cccccc/ffffff?text=Error'; };

        const overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all';
        
        const checkIcon = document.createElement('div');
        checkIcon.className = 'absolute top-2 right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity';
        checkIcon.innerHTML = `<i class="fas fa-check text-indigo-600"></i>`;
        
        if (this.selectedIds.has(file.id)) {
            card.classList.add('selected');
        }

        card.append(img, overlay, checkIcon);
        card.addEventListener('click', () => this.handleImageClick(file.id));
        return card;
    },
    
    /**
     * Xử lý khi người dùng click vào một ảnh.
     * @param {number} fileId - ID của file được click.
     */
    handleImageClick(fileId) {
        if (this.selectedIds.has(fileId)) {
            this.selectedIds.delete(fileId);
        } else {
            if (!this.allowMultiple) {
                this.selectedIds.clear(); // Chỉ cho phép chọn 1
            }
            this.selectedIds.add(fileId);
        }
        
        // Cập nhật lại giao diện cho tất cả các card
        this.elements.grid.querySelectorAll('.image-card').forEach(card => {
            card.classList.toggle('selected', this.selectedIds.has(parseInt(card.dataset.id)));
        });
        
        this.updateSelectButton();
    },
    
    /**
     * Cập nhật trạng thái và nội dung của nút Chọn ảnh.
     */
    updateSelectButton() {
        const count = this.selectedIds.size;
        if (count > 0) {
            this.elements.selectBtn.disabled = false;
            this.elements.selectBtnText.textContent = `Chọn (${count}) ảnh`;
        } else {
            this.elements.selectBtn.disabled = true;
            this.elements.selectBtnText.textContent = `Chọn ảnh`;
        }
    },
    
    /**
     * Xử lý khi người dùng xác nhận lựa chọn.
     */
    handleConfirmSelection() {
        if (this.selectedIds.size === 0 || typeof this.onSelectCallback !== 'function') return;

        // Gọi API để lấy thông tin chi tiết của các ảnh đã chọn
        const ids = Array.from(this.selectedIds);
        fetch(`{{ route('admin.media.fetch') }}?ids[]=${ids.join('&ids[]=')}`)
            .then(res => res.json())
            .then(data => {
                this.onSelectCallback(data.data || data); // Trả về mảng dữ liệu ảnh
                this.close();
            })
            .catch(error => console.error("Lỗi khi lấy chi tiết ảnh đã chọn:", error));
    },

    /**
     * Render các nút phân trang.
     * @param {object} paginationData - Dữ liệu phân trang từ Laravel.
     */
     renderPagination(paginationData) {
        this.elements.paginationContainer.innerHTML = '';
        if (!paginationData.links) return;

        paginationData.links.forEach(link => {
            const button = document.createElement('button');
            button.innerHTML = link.label;
            button.className = 'px-3 py-1 mx-1 text-sm rounded-md transition-colors ';
            
            if (link.active) {
                button.className += 'bg-indigo-600 text-white cursor-default';
            } else if (!link.url) {
                 button.className += 'bg-gray-200 text-gray-500 cursor-not-allowed';
            } else {
                 button.className += 'bg-white text-gray-700 hover:bg-gray-100 border';
                 button.onclick = () => this.fetchAndRender(link.url);
            }
            
            this.elements.paginationContainer.appendChild(button);
        });
    }
};

// Gắn sự kiện cho nút "Chọn ảnh" trong modal
mediaLibraryModal.elements.selectBtn.addEventListener('click', () => mediaLibraryModal.handleConfirmSelection());

