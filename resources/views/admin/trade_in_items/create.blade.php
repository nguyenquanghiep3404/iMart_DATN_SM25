@extends('admin.layouts.app')

@section('title', 'Thêm Sản phẩm Cũ & Mở Hộp')

@push('styles')
<style>
    /* CSS cũ của bạn */
    .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06); background-color: #fff; }
    .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; }
    .card-custom-subtitle { font-size: 1.125rem; font-weight: 600; color: #374151; }
    .card-custom-body { padding: 1.5rem; }
    .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; line-height: 1.25rem; border: 1px solid transparent; }
    .btn-primary { background-color: #4f46e5; color: white; } .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #e5e7eb; color: #374151; border-color: #d1d5db; } .btn-secondary:hover { background-color: #d1d5db; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #4f46e5; outline: 0; box-shadow: 0 0 0 0.2rem rgba(79,70,229,.25); }
    [x-cloak] { display: none !important; }

    /* === CSS MỚI CHO PHẦN ẢNH === */
    .image-preview-container { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.75rem; }
    .image-preview-item { position: relative; width: 120px; height: 120px; border-radius: 0.5rem; overflow: hidden; border: 2px solid #e2e8f0; background-color: #f8fafc; display: flex; align-items: center; justify-content: center; }
    .image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .image-preview-item.is-primary { border: 3px solid #4f46e5; box-shadow: 0 0 8px rgba(79, 70, 229, 0.5); }
    .remove-img-btn { position: absolute; top: 6px; right: 6px; background-color: rgba(220, 38, 38, 0.8); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; transition: background-color 0.2s; z-index: 10; }
    .remove-img-btn:hover { background-color: #dc2626; }
    .set-primary-btn { position: absolute; bottom: 4px; left: 4px; background-color: rgba(0, 0, 0, 0.6); color: white; padding: 3px 5px; border-radius: 4px; font-size: 0.7rem; cursor: pointer; z-index: 10; display: none; align-items: center; }
    .image-preview-item:hover .set-primary-btn { display: inline-flex; }
    .image-preview-item.is-primary .set-primary-btn { display: none; }
    .set-primary-btn:hover { background-color: rgba(79, 70, 229, 0.9); }
    .set-primary-btn i { width: 12px; height: 12px; margin-right: 3px; }
</style>
@endpush

@section('content')
<div class="body-content px-4 sm:px-6 md:px-8 py-8">
    <div class="container mx-auto max-w-4xl">
        <!-- PAGE HEADER -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Thêm Sản phẩm Cũ Mới</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.trade-in-items.index') }}" class="text-indigo-600 hover:text-indigo-800">Máy Cũ & Mở Hộp</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Thêm mới</li>
                </ol>
            </nav>
        </header>

        <form action="{{ route('admin.trade-in-items.store') }}" method="POST" class="space-y-8">
            @csrf
            <!-- Main Details Card -->
            <div class="card-custom">
                <div class="card-custom-header"><h3 class="card-custom-subtitle">Thông tin sản phẩm</h3></div>
                <div class="card-custom-body grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="product_variant_id" class="block text-sm font-medium text-gray-700 mb-1">Sản phẩm gốc (mới) <span class="text-red-500">*</span></label>
                        <div class="border border-gray-200 rounded-lg max-h-72 overflow-y-auto">
                            <div class="space-y-1 p-2">
                                @forelse($products as $product)
                                    <div x-data="{ open: {{ in_array(old('product_variant_id'), $product->variants->pluck('id')->toArray()) ? 'true' : 'false' }} }">
                                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-left p-2 rounded-md bg-gray-50 hover:bg-gray-100 focus:outline-none">
                                            <span class="font-semibold text-gray-800">{{ $product->name }}</span>
                                            <i class="fas transition-transform duration-200" :class="open ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
                                        </button>
                                        <div x-show="open" x-transition class="mt-2 pl-4 space-y-2 border-l-2 border-indigo-200">
                                            @foreach($product->variants as $variant)
                                                <label class="flex items-center p-2 rounded-md hover:bg-indigo-50 cursor-pointer">
                                                    <input type="radio" name="product_variant_id" value="{{ $variant->id }}" @checked(old('product_variant_id') == $variant->id) class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                                    <span class="ml-3 text-sm text-gray-700">
                                                        @php
                                                            $attributes = $variant->attributeValues->map(function ($value) {
                                                                return $value->attribute->name . ': ' . $value->value;
                                                            })->implode(', ');
                                                        @endphp
                                                        {{ $attributes ?: $variant->sku }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-500 p-4">Không có sản phẩm nào để chọn.</p>
                                @endforelse
                            </div>
                        </div>
                        @error('product_variant_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU (Mã sản phẩm cũ)</label>
                        <input type="text" id="sku" name="sku" class="form-input" placeholder="Để trống để tự tạo" value="{{ old('sku') }}">
                    </div>
                    <div>
                        <label for="imei_or_serial" class="block text-sm font-medium text-gray-700 mb-1">IMEI / Số Serial <span class="text-red-500">*</span></label>
                        <input type="text" id="imei_or_serial" name="imei_or_serial" class="form-input @error('imei_or_serial') border-red-500 @enderror" value="{{ old('imei_or_serial') }}">
                        @error('imei_or_serial') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1">Giá bán <span class="text-red-500">*</span></label>
                        <input type="number" id="selling_price" name="selling_price" class="form-input @error('selling_price') border-red-500 @enderror" value="{{ old('selling_price') }}">
                        @error('selling_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Loại sản phẩm</label>
                        <select id="type" name="type" class="form-select">
                            <option value="used" @selected(old('type') == 'used')>Máy đã qua sử dụng</option>
                            <option value="open_box" @selected(old('type') == 'open_box')>Hàng mở hộp</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select id="status" name="status" class="form-select">
                            <option value="pending_inspection" @selected(old('status') == 'pending_inspection')>Chờ kiểm tra</option>
                            <option value="available" @selected(old('status') == 'available')>Sẵn sàng bán</option>
                            <option value="sold" @selected(old('status') == 'sold')>Đã bán</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Condition & Location Card -->
            <div class="card-custom">
                <div class="card-custom-header"><h3 class="card-custom-subtitle">Tình trạng & Tồn kho</h3></div>
                <div class="card-custom-body grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="condition_grade" class="block text-sm font-medium text-gray-700 mb-1">Xếp loại tình trạng <span class="text-red-500">*</span></label>
                        <select id="condition_grade" name="condition_grade" class="form-select">
                            <option value="A" @selected(old('condition_grade') == 'A')>Loại A (Như mới)</option>
                            <option value="B" @selected(old('condition_grade') == 'B')>Loại B (Trầy xước nhẹ)</option>
                            <option value="C" @selected(old('condition_grade') == 'C')>Loại C (Trầy xước nhiều)</option>
                        </select>
                    </div>
                    <div>
                        <label for="store_location_id" class="block text-sm font-medium text-gray-700 mb-1">Tồn kho tại <span class="text-red-500">*</span></label>
                        <select id="store_location_id" name="store_location_id" class="form-select @error('store_location_id') border-red-500 @enderror">
                             <option value="">Chọn cửa hàng...</option>
                            @foreach($storeLocations as $location)
                                <option value="{{ $location->id }}" @selected(old('store_location_id') == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                         @error('store_location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="condition_description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả chi tiết tình trạng <span class="text-red-500">*</span></label>
                        <textarea id="condition_description" name="condition_description" rows="4" class="form-textarea @error('condition_description') border-red-500 @enderror" placeholder="Ví dụ: Máy đẹp như mới, pin 99%...">{{ old('condition_description') }}</textarea>
                        @error('condition_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            
            <!-- === KHỐI UPLOAD ẢNH ĐÃ ĐƯỢC THAY THẾ HOÀN TOÀN === -->
            <div class="card-custom">
                <div class="card-custom-header"><h3 class="card-custom-subtitle">Hình ảnh thực tế</h3></div>
                <div class="card-custom-body">
                    <div class="flex space-x-2 mb-3">
                        <label for="image_upload_input" class="btn btn-secondary cursor-pointer">
                            <i class="fas fa-upload mr-2"></i> Tải ảnh lên
                        </label>
                        <input type="file" id="image_upload_input" class="hidden" accept="image/*" multiple>
                        
                        {{-- Nếu bạn có media library, có thể thêm nút ở đây --}}
                    </div>
                    <p class="text-sm text-gray-500">Ảnh đầu tiên sẽ được chọn làm ảnh đại diện. Bạn có thể thay đổi bằng cách nhấn nút <i class="fas fa-star text-xs"></i> trên ảnh khác.</p>

                    <div id="image_preview_container" class="image-preview-container">
                        {{-- Previews sẽ được thêm vào đây bằng JS --}}
                    </div>
                    
                    {{-- Hidden inputs để lưu ID ảnh cho form --}}
                    <div id="image_ids_container" class="hidden">
                        {{-- Input cho ảnh chính và các ảnh album sẽ được thêm vào đây --}}
                    </div>

                    @error('primary_image_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    @error('image_ids') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    @error('image_ids.*') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.trade-in-items.index') }}" class="btn btn-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // --- Dữ liệu từ PHP để khôi phục form khi validation lỗi ---
    const oldImagesData = @json($old_images_data ?? [], JSON_UNESCAPED_UNICODE);
    const oldPrimaryImageId = @json(old('primary_image_id'), JSON_UNESCAPED_UNICODE) || null;

    document.addEventListener('DOMContentLoaded', () => {
        const uploadInput = document.getElementById('image_upload_input');
        
        if (uploadInput) {
            uploadInput.addEventListener('change', handleImageUpload);
        }

        // Khôi phục lại các ảnh đã chọn nếu có lỗi validation
        repopulateImagesFromOldData();
    });

    // --- CÁC HÀM XỬ LÝ ẢNH ---

    async function handleImageUpload(event) {
        const files = event.target.files;
        if (!files.length) return;
        
        try {
            const formData = new FormData();
            Array.from(files).forEach(file => formData.append('files[]', file));
            formData.append('context', 'trade-in-items');

            const response = await fetch("{{ route('admin.media.store') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Upload không thành công.');

            if (result.files && result.files.length > 0) {
                addImagesToPreview(result.files);
            }
        } catch (error) {
            console.error('Lỗi khi upload ảnh:', error);
            alert('Lỗi: ' + error.message);
        } finally {
            event.target.value = ''; // Reset input để có thể chọn lại file giống nhau
        }
    }

    function addImagesToPreview(imagesData) {
        const previewContainer = document.getElementById('image_preview_container');
        const idsContainer = document.getElementById('image_ids_container');
        if (!previewContainer || !idsContainer) return;

        imagesData.forEach(fileData => {
            if (document.querySelector(`.image-preview-item[data-id="${fileData.id}"]`)) return;

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'image_ids[]';
            idInput.value = fileData.id;
            idsContainer.appendChild(idInput);

            const previewDiv = document.createElement('div');
            previewDiv.className = 'image-preview-item';
            previewDiv.dataset.id = fileData.id;
            previewDiv.innerHTML = `
                <img src="${fileData.url}" alt="${fileData.alt_text || 'Ảnh sản phẩm'}">
                <span class="remove-img-btn" onclick="removeImage(${fileData.id})">×</span>
                <button type="button" class="set-primary-btn" title="Đặt làm ảnh chính" onclick="setPrimaryImage(${fileData.id})">
                    <i class="fas fa-star"></i>
                </button>
            `;
            previewContainer.appendChild(previewDiv);
        });
        
        updatePrimaryImageState();
    }

    function setPrimaryImage(primaryImageId) {
        const idsContainer = document.getElementById('image_ids_container');
        if (!idsContainer) return;

        let oldPrimaryInput = idsContainer.querySelector('input[name="primary_image_id"]');
        if (oldPrimaryInput) oldPrimaryInput.remove();

        const newPrimaryInput = document.createElement('input');
        newPrimaryInput.type = 'hidden';
        newPrimaryInput.name = 'primary_image_id';
        newPrimaryInput.value = primaryImageId;
        idsContainer.appendChild(newPrimaryInput);

        updatePrimaryImageState();
    }

    function removeImage(imageIdToRemove) {
        const previewContainer = document.getElementById('image_preview_container');
        const idsContainer = document.getElementById('image_ids_container');
        if (!previewContainer || !idsContainer) return;

        const previewToRemove = previewContainer.querySelector(`.image-preview-item[data-id="${imageIdToRemove}"]`);
        if (previewToRemove) previewToRemove.remove();

        const idInputToRemove = idsContainer.querySelector(`input[name="image_ids[]"][value="${imageIdToRemove}"]`);
        if (idInputToRemove) idInputToRemove.remove();

        const primaryInput = idsContainer.querySelector('input[name="primary_image_id"]');
        if (primaryInput && parseInt(primaryInput.value) === imageIdToRemove) {
            primaryInput.remove();
        }

        updatePrimaryImageState();
    }

    function updatePrimaryImageState() {
        const previewContainer = document.getElementById('image_preview_container');
        const idsContainer = document.getElementById('image_ids_container');
        if (!previewContainer || !idsContainer) return;

        let primaryInput = idsContainer.querySelector('input[name="primary_image_id"]');
        const allPreviews = previewContainer.querySelectorAll('.image-preview-item');
        
        if (!primaryInput && allPreviews.length > 0) {
            const firstImageId = parseInt(allPreviews[0].dataset.id);
            setPrimaryImage(firstImageId);
            primaryInput = idsContainer.querySelector('input[name="primary_image_id"]');
        }

        const primaryImageId = primaryInput ? parseInt(primaryInput.value) : null;

        allPreviews.forEach(preview => {
            preview.classList.toggle('is-primary', parseInt(preview.dataset.id) === primaryImageId);
        });
    }

    function repopulateImagesFromOldData() {
        const imagesToRepopulate = Object.values(oldImagesData);
        if (imagesToRepopulate.length > 0) {
            addImagesToPreview(imagesToRepopulate);
            if (oldPrimaryImageId) {
                setPrimaryImage(parseInt(oldPrimaryImageId));
            }
        }
    }
</script>
@endpush
@endsection
