{{-- 
    Đây là modal chung cho thư viện media.
    Nó sẽ được điều khiển hoàn toàn bằng JavaScript.
--}}
<div id="media-library-modal" class="modal" tabindex="-1">
    <div class="modal-content !max-w-6xl flex flex-col h-[90vh] max-h-[900px]">
        <div class="modal-header">
            <h5 class="modal-title">Thư viện Media</h5>
            <button type="button" class="close" onclick="mediaLibraryModal.close()"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body flex-grow overflow-y-auto p-0">
             {{-- Nội dung media sẽ được tải bằng AJAX vào đây --}}
             <div id="media-library-grid" class="p-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                {{-- Placeholder khi đang tải --}}
                <div id="media-library-loading" class="col-span-full text-center py-20">
                    <i class="fas fa-spinner fa-spin text-4xl text-indigo-500"></i>
                    <p class="mt-2 text-gray-600">Đang tải thư viện...</p>
                </div>
             </div>
        </div>
        <div class="modal-footer justify-between">
            <div id="media-library-pagination" class="flex-grow">
                {{-- Các nút phân trang sẽ được render vào đây --}}
            </div>
            <button type="button" id="media-library-select-btn" class="btn btn-primary py-2 px-6 text-sm" disabled>
                <i class="fas fa-check-circle mr-2"></i>
                <span id="media-library-select-text">Chọn ảnh</span>
            </button>
        </div>
    </div>
</div>
