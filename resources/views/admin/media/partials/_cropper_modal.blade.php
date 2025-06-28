{{-- resources/views/admin/media/partials/_cropper_modal.blade.php --}}
<div id="cropper-modal" class="modal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="modal-content !max-w-3xl">
        <div class="modal-header">
            <h5 class="modal-title" id="modal-title">
                Cắt và Tối ưu hóa Ảnh
            </h5>
            <button type="button" class="close" onclick="closeModal('cropper-modal')"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Vùng hiển thị ảnh để cắt --}}
                <div class="md:col-span-2">
                    <div class="w-full h-[400px] bg-gray-200 rounded-lg overflow-hidden">
                        <img id="image-to-crop" src="" alt="Image to crop" class="hidden max-w-full">
                    </div>
                </div>

                {{-- Vùng xem trước và các tùy chọn --}}
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
            <button id="cancel-crop-btn" type="button" class="btn btn-secondary py-2 px-4 text-sm">
                Hủy bỏ
            </button>
            <button id="crop-and-upload-btn" type="button" class="btn btn-primary py-2 px-4 text-sm inline-flex items-center">
                <i class="fas fa-crop-alt mr-2"></i> Cắt & Tải lên
            </button>
        </div>
    </div>
</div>
