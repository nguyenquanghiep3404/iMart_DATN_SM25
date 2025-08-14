@extends('admin.layouts.app')
@section('title', 'Điều chỉnh tồn kho')

@section('content')
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f0f2f5;
    }

    [x-cloak] {
        display: none !important;
    }

    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* Custom select arrow */
    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }

    .location-button-placeholder {
        color: #6b7280;
        /* gray-500 */
    }
</style>
<div class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto"
    x-data="adjustmentApp"
    x-init="
    form.product = '{{ $variant->product->name }} - {{ $variant->name }}';
    form.quantity = null;
">

    <header class="mb-8 text-center">
        <h1 class="text-3xl font-semibold text-gray-800">
            Điều Chỉnh Tồn Kho Thủ Công
        </h1>

        <div class="mt-3 text-base text-gray-700">
            <p>
                Sản phẩm:
                <span class="font-medium text-gray-900">
                    {{ $variant->product->name }}
                </span>
            </p>
            <p class="mt-1">
                Tổng tồn kho hiện tại:
                <span class="font-medium text-green-700">
                    {{ $totalStock }}
                </span>
            </p>
        </div>
    </header>


    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-md border border-gray-200">
        <form @submit.prevent="submitAdjustment" class="space-y-6">
            <!-- Địa điểm (kho hàng) -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Chọn kho để điều chỉnh <span class="text-red-500">*</span></label>

                @foreach($storeLocations as $location)
                <label class="p-4 border rounded mb-3 block cursor-pointer"
                    :class="form.locationId === {{ $location['id'] }} ? 'border-indigo-500 bg-indigo-50' : ''"
                    @click="form.locationId = {{ $location['id'] }};
                    form.locationName = '{{ $location['name'] }}';
                    form.locationAddress = '{{ $location['fullAddress'] }}';">

                    <input type="radio" class="mr-2"
                        :checked="form.locationId === {{ $location['id'] }}"
                        name="store_location"
                        value="{{ $location['id'] }}"
                        @change="form.locationId = {{ $location['id'] }}" />

                    <span class="font-semibold text-lg">{{ $location['name'] }} ({{ $location['type'] }})</span><br>
                    <span>Địa chỉ: {{ $location['fullAddress'] }}</span><br>
                    <span>Điện thoại: {{ $location['phone'] }}</span><br>
                    <span>Tồn kho hiện tại: {{ $location['stock_quantity'] }}</span>
                </label>
                @endforeach

                <template x-if="form.locationId">
                    <p class="text-sm text-gray-600 mt-2">
                        Đã chọn: <strong x-text="form.locationName"></strong> – <span x-text="form.locationAddress"></span>
                    </p>
                </template>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Chọn biến thể sản phẩm <span class="text-red-500">*</span></label>
                <select x-model="form.variantId" class="mt-1 w-full border px-3 py-2.5 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Chọn biến thể --</option>
                    @foreach($variants as $v)
                    <option value="{{ $v->id }}">
                        {{ $v->product->name }} - {{ $v->sku }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Số lượng mới -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Số lượng mới <span class="text-red-500">*</span></label>
                <input type="number" min="0" x-model.number="form.quantity" class="mt-1 w-full border px-3 py-2.5 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>

            <!-- Lý do -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lý do điều chỉnh <span class="text-red-500">*</span></label>
                <select x-model="form.reason" class="w-full border rounded px-3 py-2.5">
                    <option value="">-- Chọn lý do --</option>
                    <option value="damaged">Hàng hỏng</option>
                    <option value="lost">Thất lạc</option>
                    <option value="found">Tìm thấy hàng thất lạc</option>
                    <option value="other">Khác</option>
                </select>
            </div>

            <!-- Ghi chú -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                <textarea x-model="form.notes" class="w-full border rounded px-3 py-2"></textarea>
            </div>

            <!-- Nút xác nhận -->
            <div class="flex justify-end border-t pt-4">
                <button
                    type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                    x-bind:disabled="loading">
                    <span x-show="!loading">Xác nhận điều chỉnh</span>
                    <span x-show="loading">Đang xử lý...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script khai báo AlpineJS component -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adjustmentApp', () => ({
            loading: false,
            form: {
                locationId: null,
                locationName: '',
                locationAddress: '',
                product: '',
                quantity: null,
                reason: '',
                notes: ''
            },
            showSuccessMessage: false,
            successMessage: '',
            showErrorMessage: false,
            errorMessage: '',
            isModalOpen: false,
            modalType: '',
            modalTitle: '',
            modalSelectedProvince: '',
            modalSelectedDistrict: '',
            modalSearchTerm: '',
            provinces: [],
            districts: [],
            allLocations: @json($storeLocations), // hoặc convert PHP sang đúng định dạng JS
            init() {
                this.modalTitle = 'Chọn Kho hàng';
            },
            openModal(type) {
                this.modalType = type;
                this.isModalOpen = true;
            },
            selectModalItem(item) {
                this.form.locationId = item.id;
                this.form.locationName = item.name;
                this.form.locationAddress = item.fullAddress || '';
                this.isModalOpen = false;
            },
            submitAdjustment() {
                console.log('variantId được gửi đi:', this.form.variantId);
                if (!this.form.locationId || this.form.quantity === null || !this.form.reason) {
                    this.showError('Vui lòng điền đầy đủ thông tin.');
                    return;
                }

                this.loading = true;
               fetch(`/admin/product-variants/${this.form.variantId}/adjust-stock`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            product_variant_id: this.form.variantId,
                            new_quantity: this.form.quantity,
                            reason: this.form.reason,
                            note: this.form.notes,
                            store_location_id: this.form.locationId
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.redirect) {
                            window.location.href = data.redirect; // chuyển trang thủ công
                        } else {
                            this.showSuccess(data.message || 'Thành công');
                        }
                        this.loading = false;
                    })
            },
            showSuccess(msg) {
                this.successMessage = msg;
                this.showSuccessMessage = true;
                setTimeout(() => this.showSuccessMessage = false, 400);
            },
            showError(msg) {
                this.errorMessage = msg;
                this.showErrorMessage = true;
                setTimeout(() => this.showErrorMessage = false, 400);
            }
        }));
    });
</script>
@endsection