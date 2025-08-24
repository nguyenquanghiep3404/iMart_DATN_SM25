@extends('admin.layouts.app')

@section('title', 'Chi tiết chiến dịch Flash Sale')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        @include('admin.partials.flash_message')
        <div id="campaign-detail-view">
            <div class="mb-6">
               <a href="{{ route('admin.flash-sales.index') }}" 
               class="mb-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại danh sách
            </a>
                <h1 class="text-3xl font-bold text-gray-800 mt-2">
                    {{ $flashSale->name }}
                </h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4">
                    <div class="card-custom sticky top-6">
                        <div class="card-custom-header">
                            <h3 class="card-custom-title text-lg">Thêm sản phẩm</h3>
                        </div>
                        <div class="card-custom-body">
                            <div class="relative mb-4">
                                <input type="text" id="search-variant" placeholder="Tìm sản phẩm theo tên, SKU..."
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>

                            <div id="variant-list" class="space-y-3 max-h-96 overflow-y-auto no-scrollbar border-t pt-4">
                                @foreach ($variants as $variant)
                                    <div class="variant-item flex items-center gap-4 p-2 rounded-lg hover:bg-gray-100"
                                        data-name="{{ $variant->product->name }} {{ $variant->name }}"
                                        data-sku="{{ $variant->sku }}">
                                        <img src="{{ $variant->primaryImage?->url ?? 'https://placehold.co/80x80/E2E8F0/4A5568?text=IMG' }}"
                                            alt="Product" class="w-12 h-12 rounded-md object-cover">
                                        <div class="flex-grow">
                                            <p class="font-semibold text-sm">
                                                {{ $variant->product->name }}
                                                @php
                                                    $attributes = $variant->attributeValues;
                                                    $nonColor = $attributes
                                                        ->filter(fn($v) => $v->attribute->name !== 'Màu sắc')
                                                        ->pluck('value')
                                                        ->join(' ');
                                                    $color = $attributes->firstWhere(
                                                        fn($v) => $v->attribute->name === 'Màu sắc',
                                                    )?->value;
                                                @endphp

                                                {{ $nonColor ? ' ' . $nonColor : '' }}
                                                {{ $color ? ' ' . $color : '' }}
                                            </p>

                                            <p class="text-xs text-gray-500">SKU: {{ $variant->sku }}</p>
                                        </div>
                                        <form method="POST"
                                            action="{{ route('admin.flash-sales.attachProduct', [$flashSale, $variant]) }}">
                                            @csrf
                                            <button type="button" class="add-product-btn btn btn-primary btn-sm"
                                                data-variant-id="{{ $variant->id }}"
                                                data-name="{{ $variant->product->name }}{{ $nonColor ? ' ' . $nonColor : '' }}{{ $color ? ' ' . $color : '' }}"
                                                data-image="{{ $variant->primaryImage?->url ?? 'https://placehold.co/80x80/E2E8F0/4A5568?text=IMG' }}"
                                                data-price="{{ number_format($variant->price) }}"
                                                data-available-stock="{{ $variant->available_stock }}">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </form>

                                    </div>
                                @endforeach
                            </div>

                            {{-- Phân trang --}}
                            <div class="mt-4">
                                {{ $variants->links() }}
                            </div>
                        </div>
                    </div>
                </div>


                <div class="lg:col-span-8">
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <div class="flex justify-between items-center">
                                <h3 class="card-custom-title text-lg">Sản phẩm trong chiến dịch</h3>
                                @if ($flashSale->flashSaleTimeSlots->count())
                                    <form method="GET" action="{{ route('admin.flash-sales.show', $flashSale->id) }}">
                                        <select name="time_slot_id" onchange="this.form.submit()"
                                            class="p-2 w-52 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="all"
                                                {{ $timeSlotId === 'all' || !$timeSlotId ? 'selected' : '' }}>
                                                Tất cả khung giờ
                                            </option>
                                            @foreach ($flashSale->flashSaleTimeSlots as $slot)
                                                <option value="{{ $slot->id }}"
                                                    {{ $timeSlotId == $slot->id ? 'selected' : '' }}>
                                                    {{ $slot->label ?? $slot->start_time . ' - ' . $slot->end_time }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="card-custom-body p-0">
                            <div class="overflow-x-auto">
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th>Giá Flash</th>
                                            <th>Số lượng</th>
                                            <th>Khung giờ</th>
                                            <th class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($flashSale->products as $flashProduct)
                                            @php
                                                $variant = $flashProduct->variant;
                                                $product = $variant->product;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="flex items-center gap-3">
                                                        <img src="{{ $variant->primaryImage?->url ?? $product->thumbnail_url }}"
                                                            alt="Variant Image" class="w-10 h-10 rounded-md object-cover">
                                                        <div>
                                                            <p class="font-semibold text-gray-900 text-sm">
                                                                {{ $product->name }}
                                                                @php
                                                                    $attributes = $variant->attributeValues;
                                                                    $nonColor = $attributes
                                                                        ->filter(
                                                                            fn($v) => $v->attribute->name !== 'Màu sắc',
                                                                        )
                                                                        ->pluck('value')
                                                                        ->join(' ');
                                                                    $color = $attributes->firstWhere(
                                                                        fn($v) => $v->attribute->name === 'Màu sắc',
                                                                    )?->value;
                                                                @endphp
                                                                {{ $nonColor ? ' ' . $nonColor : '' }}
                                                                {{ $color ? ' ' . $color : '' }}
                                                            </p>
                                                            <p class="text-xs text-gray-500">Gốc:
                                                                {{ number_format($variant->price) }}₫</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="font-semibold text-red-600">
                                                    {{ number_format($flashProduct->flash_price) }}₫</td>
                                                <td>
                                                    <div class="flex flex-col gap-1">
                                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                            <div class="bg-green-600 h-2.5 rounded-full"
                                                                style="width: {{ round((($flashProduct->quantity_sold ?? 0) / $flashProduct->quantity_limit) * 100) }}%">
                                                            </div>
                                                        </div>
                                                        <span class="text-xs">Đã bán
                                                            {{ $flashProduct->quantity_sold ?? 0 }} /
                                                            {{ $flashProduct->quantity_limit }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ $flashProduct->timeSlot?->label ?? ($flashProduct->timeSlot ? $flashProduct->timeSlot->start_time . ' - ' . $flashProduct->timeSlot->end_time : 'Toàn chiến dịch') }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="flex justify-center gap-2">
                                                        <button type="button"
                                                            class="edit-product-btn btn btn-primary btn-sm"
                                                            data-id="{{ $flashProduct->id }}"
                                                            data-variant="{{ $variant->id }}"
                                                            data-flash-price="{{ $flashProduct->flash_price }}"
                                                            data-quantity-limit="{{ $flashProduct->quantity_limit }}"
                                                            data-time-slot-id="{{ $flashProduct->flash_sale_time_slot_id ?? '' }}"
                                                            data-name="{{ $product->name }}{{ $nonColor ? ' ' . $nonColor : '' }}{{ $color ? ' ' . $color : '' }}"
                                                            data-image="{{ $variant->primaryImage?->url ?? $product->thumbnail_url }}"
                                                            data-original-price="{{ number_format($variant->price) }}"
                                                            data-available-stock="{{ $variant->available_stock }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST"
                                                            action="{{ route('admin.flash-sales.detachProduct', [$flashSale->id, $flashProduct->id]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Xác nhận xoá sản phẩm này khỏi chiến dịch?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-gray-500">Chưa có sản phẩm
                                                    nào trong chiến dịch.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="add-product-modal" class="custom-modal">
            <div class="custom-modal-content">
                {{-- Thêm thuộc tính novalidate để Laravel xử lý lỗi --}}
                <form method="POST" action="{{ route('admin.flash-sales.attachProduct', $flashSale->id) }}" novalidate>
                    @csrf
                    {{-- Thêm thuộc tính name --}}
                    <input type="hidden" name="form_type" value="add">
                    <input type="hidden" name="product_variant_id" id="modal-product-variant-id"
                        value="{{ old('product_variant_id') }}">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold">Thêm sản phẩm</h3>
                        <button type="button"
                            class="modal-close-btn text-gray-400 hover:text-gray-700 text-2xl">×</button>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-6">
                            <img id="modal-product-image" src="" alt="Product"
                                class="w-16 h-16 rounded-md object-cover">
                            <div>
                                <p id="modal-product-name" class="font-bold"></p>
                                <p id="modal-product-original-price" class="text-sm text-gray-500"></p>
                                <p id="modal-product-stock" class="text-sm text-gray-500 mt-1"></p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            {{-- Chọn khung giờ nếu có --}}
                            @if ($flashSale->flashSaleTimeSlots->count())
                                <div>
                                    <label for="flash_sale_time_slot_id" class="block mb-2 text-sm font-medium">Chọn khung
                                        giờ</label>
                                    <select name="flash_sale_time_slot_id"
                                        class="w-full p-2.5 border border-gray-300 rounded-lg">
                                        @foreach ($flashSale->flashSaleTimeSlots as $slot)
                                            <option value="{{ $slot->id }}"
                                                @if (old('flash_sale_time_slot_id') == $slot->id) selected @endif>
                                                {{ $slot->label ? $slot->label : $slot->start_time . ' - ' . $slot->end_time }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <label for="flash_price" class="block mb-2 text-sm font-medium">Giá Flash (VNĐ)</label>
                                <input type="number" name="flash_price"
                                    class="w-full p-2.5 border border-gray-300 rounded-lg"
                                    value="{{ old('flash_price') }}" required>
                                @error('flash_price')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="quantity_limit" class="block mb-2 text-sm font-medium">Số lượng giới
                                    hạn</label>
                                <input type="number" name="quantity_limit"
                                    class="w-full p-2.5 border border-gray-300 rounded-lg"
                                    value="{{ old('quantity_limit') }}" required>
                                {{-- Hiển thị thông báo lỗi validation ở đây --}}
                                @error('quantity_limit')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t flex justify-end space-x-2">
                        <button type="button" class="modal-close-btn btn btn-secondary">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm vào chiến dịch</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="edit-product-modal" class="custom-modal">
            <div class="custom-modal-content">
                {{-- Thêm thuộc tính novalidate để Laravel xử lý lỗi --}}
                <form method="POST" id="edit-product-form" action="" novalidate>
                    @csrf
                    @method('PUT')
                    {{-- Thêm thuộc tính name --}}
                    <input type="hidden" name="form_type" value="edit">
                    <input type="hidden" name="product_variant_id" id="edit-modal-product-variant-id"
                        value="{{ old('product_variant_id') }}">
                    <input type="hidden" name="flash_product_id" id="edit-modal-flash-product-id"
                        value="{{ old('flash_product_id') }}">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold">Sửa sản phẩm</h3>
                        <button type="button"
                            class="modal-close-btn text-gray-400 hover:text-gray-700 text-2xl">×</button>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-6">
                            <img id="edit-modal-product-image" src="" alt="Product"
                                class="w-16 h-16 rounded-md object-cover">
                            <div>
                                <p id="edit-modal-product-name" class="font-bold"></p>
                                <p id="edit-modal-product-original-price" class="text-sm text-gray-500"></p>
                                <p id="edit-modal-product-stock" class="text-sm text-gray-500 mt-1"></p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            @if ($flashSale->flashSaleTimeSlots->count())
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Khung giờ</label>
                                    <select name="flash_sale_time_slot_id" id="edit-modal-time-slot-id"
                                        class="w-full p-2.5 border border-gray-300 rounded-lg">
                                        @foreach ($flashSale->flashSaleTimeSlots as $slot)
                                            <option value="{{ $slot->id }}">
                                                {{ $slot->label ?? $slot->start_time . ' - ' . $slot->end_time }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="block mb-2 text-sm font-medium">Giá Flash (VNĐ)</label>
                                <input type="number" name="flash_price" id="edit-modal-flash-price"
                                    class="w-full p-2.5 border border-gray-300 rounded-lg"
                                    value="{{ old('flash_price') }}" required>
                                @error('flash_price')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium">Số lượng giới hạn</label>
                                <input type="number" name="quantity_limit" id="edit-modal-quantity-limit"
                                    class="w-full p-2.5 border border-gray-300 rounded-lg"
                                    value="{{ old('quantity_limit') }}" required>
                                @error('quantity_limit')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t flex justify-end space-x-2">
                        <button type="button" class="modal-close-btn btn btn-secondary">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .card-custom {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
        }

        .card-custom-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }

        .card-custom-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .card-custom-body {
            padding: 1.5rem;
        }

        .btn {
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .table-custom {
            width: 100%;
            color: #374151;
        }

        .table-custom th,
        .table-custom td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom-width: 1px;
            border-color: #e5e7eb;
            white-space: nowrap;
        }

        .table-custom thead th {
            font-weight: 600;
            color: #4b5563;
            background-color: #f9fafb;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-align: left;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .03);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .custom-modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .custom-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-modal-content {
            background-color: #fff;
            margin: auto;
            border: none;
            width: 90%;
            max-width: 500px;
            border-radius: 0.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .toast {
            opacity: 1;
            transform: translateX(0);
            transition: all 0.3s ease-in-out;
        }

        .toast.hide {
            opacity: 0;
            transform: translateX(100%);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addProductModal = document.getElementById('add-product-modal');
            const editProductModal = document.getElementById('edit-product-modal');

            const openModal = (modal) => modal.classList.add('show');
            const closeModal = (modal) => modal.classList.remove('show');

            // Xử lý nút "Thêm"
            document.querySelectorAll('.add-product-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const variantId = btn.dataset.variantId;
                    const productName = btn.dataset.name;
                    const productImage = btn.dataset.image;
                    const productPrice = btn.dataset.price;
                    const availableStock = btn.dataset.availableStock;

                    document.getElementById('modal-product-variant-id').value = variantId;
                    document.getElementById('modal-product-name').innerText = productName;
                    document.getElementById('modal-product-image').src = productImage;
                    document.getElementById('modal-product-original-price').innerText =
                        'Giá gốc: ' + productPrice + '₫';
                    document.getElementById('modal-product-stock').innerText = 'Tồn kho: ' +
                        availableStock + ' sản phẩm';

                    // Reset form và ẩn các lỗi cũ
                    addProductModal.querySelector('form').reset();
                    document.querySelectorAll('#add-product-modal .text-red-600').forEach(el => el
                        .remove());

                    openModal(addProductModal);
                });
            });

            // Xử lý nút "Sửa"
            document.querySelectorAll('.edit-product-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const flashProductId = btn.dataset.id;
                    const variantId = btn.dataset.variant;
                    const flashPrice = btn.dataset.flashPrice;
                    const quantityLimit = btn.dataset.quantityLimit;
                    const timeSlotId = btn.dataset.timeSlotId;
                    const productName = btn.dataset.name;
                    const productImage = btn.dataset.image;
                    const originalPrice = btn.dataset.originalPrice;
                    const availableStock = btn.dataset.availableStock;

                    const form = document.getElementById('edit-product-form');
                    form.action =
                        `/admin/flash-sales/{{ $flashSale->id }}/update-product/${flashProductId}`;

                    document.getElementById('edit-modal-product-variant-id').value = variantId;
                    document.getElementById('edit-modal-flash-product-id').value = flashProductId;
                    document.getElementById('edit-modal-product-name').innerText = productName ||
                    '';
                    document.getElementById('edit-modal-product-image').src = productImage || '';
                    document.getElementById('edit-modal-product-original-price').innerText =
                        'Giá gốc: ' + (originalPrice || '') + '₫';
                    document.getElementById('edit-modal-product-stock').innerText = 'Tồn kho: ' + (
                        availableStock || '') + ' sản phẩm';
                    document.getElementById('edit-modal-flash-price').value = flashPrice || '';
                    document.getElementById('edit-modal-quantity-limit').value = quantityLimit ||
                    '';
                    document.getElementById('edit-modal-time-slot-id').value = timeSlotId || '';

                    // Xóa các thông báo lỗi cũ
                    document.querySelectorAll('#edit-product-modal .text-red-600').forEach(el => el
                        .remove());

                    openModal(editProductModal);
                });
            });

            // Xử lý đóng modal
            document.querySelectorAll('.modal-close-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    closeModal(addProductModal);
                    closeModal(editProductModal);
                });
            });

            // Đóng modal khi click bên ngoài
            window.addEventListener('click', (e) => {
                if (e.target === addProductModal) {
                    closeModal(addProductModal);
                }
                if (e.target === editProductModal) {
                    closeModal(editProductModal);
                }
            });

            // Đóng modal khi nhấn Esc
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeModal(addProductModal);
                    closeModal(editProductModal);
                }
            });

            // Khắc phục lỗi hiển thị modal sau khi validation thất bại
            @if ($errors->any())
                const oldFormType = "{{ old('form_type') }}";

                // Mở lại modal thêm sản phẩm nếu lỗi đến từ form thêm
                if (oldFormType === 'add') {
                    const oldVariantId = "{{ old('product_variant_id') }}";
                    const oldPrice = "{{ old('flash_price') }}";
                    const oldQuantity = "{{ old('quantity_limit') }}";
                    const oldTimeSlotId = "{{ old('flash_sale_time_slot_id') }}";

                    const btn = document.querySelector(`.add-product-btn[data-variant-id="${oldVariantId}"]`);
                    if (btn) {
                        document.getElementById('modal-product-variant-id').value = oldVariantId;
                        document.getElementById('modal-product-name').innerText = btn.dataset.name;
                        document.getElementById('modal-product-image').src = btn.dataset.image;
                        document.getElementById('modal-product-original-price').innerText = 'Giá gốc: ' + btn
                            .dataset.price + '₫';
                        document.getElementById('modal-product-stock').innerText = 'Tồn kho: ' + btn.dataset
                            .availableStock + ' sản phẩm';
                        document.querySelector('#add-product-modal [name="flash_price"]').value = oldPrice;
                        document.querySelector('#add-product-modal [name="quantity_limit"]').value = oldQuantity;
                        if (oldTimeSlotId) {
                            document.querySelector('#add-product-modal [name="flash_sale_time_slot_id"]').value =
                                oldTimeSlotId;
                        }
                        openModal(addProductModal);
                    }
                }

                // Mở lại modal sửa sản phẩm nếu lỗi đến từ form sửa
                else if (oldFormType === 'edit') {
                    const oldFlashProductId = "{{ old('flash_product_id') }}";
                    const oldPrice = "{{ old('flash_price') }}";
                    const oldQuantity = "{{ old('quantity_limit') }}";
                    const oldTimeSlotId = "{{ old('flash_sale_time_slot_id') }}";

                    const editBtn = document.querySelector(`.edit-product-btn[data-id="${oldFlashProductId}"]`);
                    if (editBtn) {
                        const form = document.getElementById('edit-product-form');
                        form.action =
                        `/admin/flash-sales/{{ $flashSale->id }}/update-product/${oldFlashProductId}`;
                        document.getElementById('edit-modal-product-variant-id').value = editBtn.dataset.variant;
                        document.getElementById('edit-modal-flash-product-id').value = oldFlashProductId;
                        document.getElementById('edit-modal-product-name').innerText = editBtn.dataset.name || '';
                        document.getElementById('edit-modal-product-image').src = editBtn.dataset.image || '';
                        document.getElementById('edit-modal-product-original-price').innerText = 'Giá gốc: ' + (
                            editBtn.dataset.originalPrice || '') + '₫';
                        document.getElementById('edit-modal-product-stock').innerText = 'Tồn kho: ' + (editBtn
                            .dataset.availableStock || '') + ' sản phẩm';
                        document.getElementById('edit-modal-flash-price').value = oldPrice;
                        document.getElementById('edit-modal-quantity-limit').value = oldQuantity;
                        document.getElementById('edit-modal-time-slot-id').value = oldTimeSlotId;
                        openModal(editProductModal);
                    }
                }
            @endif


            const toasts = document.querySelectorAll('.toast');

            const hideToast = (toastElement) => {
                if (toastElement) {
                    toastElement.classList.add('hide');
                    setTimeout(() => {
                        toastElement.remove();
                    }, 350);
                }
            };

            toasts.forEach(toast => {
                const autoHideTimeout = setTimeout(() => {
                    hideToast(toast);
                }, 5000);

                const closeButton = toast.querySelector('[data-dismiss-target]');
                if (closeButton) {
                    closeButton.addEventListener('click', function() {
                        clearTimeout(autoHideTimeout);
                        const targetId = this.getAttribute('data-dismiss-target');
                        const toastToHide = document.querySelector(targetId);
                        hideToast(toastToHide);
                    });
                }
            });

        });
    </script>
@endsection
