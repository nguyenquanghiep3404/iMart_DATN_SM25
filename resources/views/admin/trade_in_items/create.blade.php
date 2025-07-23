@extends('admin.layouts.app')

@section('title', 'Thêm Sản phẩm Cũ & Mở Hộp')

@push('styles')
<style>
    /* Copy CSS từ file HTML mẫu vào đây */
    .card-custom { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06); background-color: #fff; }
    .card-custom-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; }
    .card-custom-subtitle { font-size: 1.125rem; font-weight: 600; color: #374151; }
    .card-custom-body { padding: 1.5rem; }
    .btn { border-radius: 0.5rem; transition: all 0.2s ease-in-out; font-weight: 500; padding: 0.625rem 1.25rem; font-size: 0.875rem; display: inline-flex; align-items: center; justify-content: center; line-height: 1.25rem; border: 1px solid transparent; }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #e5e7eb; color: #374151; border-color: #d1d5db; }
    .btn-secondary:hover { background-color: #d1d5db; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 0.625rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: #4f46e5; outline: 0; box-shadow: 0 0 0 0.2rem rgba(79,70,229,.25); }
    [x-cloak] { display: none !important; }
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

        <form action="{{ route('admin.trade-in-items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <!-- Main Details Card -->
            <div class="card-custom">
                <div class="card-custom-header"><h3 class="card-custom-subtitle">Thông tin sản phẩm</h3></div>
                <div class="card-custom-body grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="product_variant_id" class="block text-sm font-medium text-gray-700 mb-1">Sản phẩm gốc (mới) <span class="text-red-500">*</span></label>
                        <select id="product_variant_id" name="product_variant_id" class="form-select @error('product_variant_id') border-red-500 @enderror">
                            <option value="">Chọn một sản phẩm gốc...</option>
                            @foreach($productVariants as $variant)
                                <option value="{{ $variant->id }}" @selected(old('product_variant_id') == $variant->id)>{{ $variant->name }} - {{ $variant->sku }}</option>
                            @endforeach
                        </select>
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
            
            <!-- Image Upload Card -->
            <div class="card-custom" x-data="imageUploader()">
                <div class="card-custom-header"><h3 class="card-custom-subtitle">Hình ảnh thực tế</h3></div>
                <div class="card-custom-body">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <input type="file" name="images[]" multiple class="hidden" x-ref="fileInput" @change="handleFileSelect">
                        <button type="button" @click="$refs.fileInput.click()" class="btn btn-secondary">
                            <i class="fas fa-upload mr-2"></i> Chọn ảnh
                        </button>
                    </div>
                    @error('images.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mt-4" x-show="images.length > 0">
                        <template x-for="(image, index) in images" :key="index">
                            <div class="relative group">
                                <img :src="image.url" class="w-full h-32 object-cover rounded-lg">
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="removeImage(index)" type="button" class="text-white text-2xl"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </template>
                    </div>
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
    function imageUploader() {
        return {
            images: [],
            handleFileSelect(event) {
                this.images = []; // Xóa ảnh cũ khi chọn ảnh mới
                for (const file of event.target.files) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.images.push({ url: e.target.result });
                        };
                        reader.readAsDataURL(file);
                    }
                }
            },
            removeImage(index) {
                this.images.splice(index, 1);
                // Cần logic phức tạp hơn để xóa file đã chọn khỏi input,
                // cách đơn giản nhất là thông báo người dùng chọn lại.
            }
        }
    }
</script>
@endpush
@endsection
