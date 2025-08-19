{{-- resources/views/admin/purchase_orders/partials/_general_info.blade.php (Bản sửa lỗi cuối cùng) --}}

<div class="card">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin Nhà Cung Cấp</h3>
        <input type="hidden" name="supplier_id" :value="selectedSupplier.addressId">

        <div x-show="!selectedSupplier.name" class="text-center text-gray-500 py-4">
            Vui lòng chọn một nhà cung cấp
        </div>
        <div x-show="selectedSupplier.name" class="space-y-2">
            <p class="font-bold text-gray-900" x-text="selectedSupplier.name"></p>
            <p class="text-gray-600 text-sm" x-text="selectedSupplier.address"></p>
        </div>
        <button type="button" @click="openModal('supplier')"
                :disabled="isLocked"
                :class="{'opacity-50 cursor-not-allowed': isLocked }"
                class="mt-4 w-full text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors">
            Chọn Nhà Cung Cấp
        </button>
    </div>
</div>

<div class="card">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Kho Nhận Hàng</h3>
        <input type="hidden" name="store_location_id" :value="selectedLocation.id">

        <div x-show="!selectedLocation.name" class="text-center text-gray-500 py-4">
            Vui lòng chọn kho nhận hàng
        </div>
        <div x-show="selectedLocation.name" class="space-y-2">
            <p class="font-bold text-gray-900" x-text="selectedLocation.name"></p>
            <p class="text-gray-600 text-sm" x-text="selectedLocation.address"></p>
        </div>
        <button type="button" @click="openModal('location')"
                :disabled="isLocked"
                :class="{'opacity-50 cursor-not-allowed': isLocked }"
                class="mt-4 w-full text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors">
            Chọn Kho Nhận Hàng
        </button>
    </div>
</div>

<div class="card">
    <div class="p-6 space-y-4">
        <div>
            <label for="order-date" class="block text-sm font-medium text-gray-700 mb-1">Ngày nhập hàng</label>
            {{-- SỬA LỖI: Ưu tiên hàm old() để giữ lại giá trị sau khi validation thất bại --}}
            <input type="date" id="order-date" name="order_date"
                   value="{{ old('order_date', isset($purchaseOrder) ? \Carbon\Carbon::parse($purchaseOrder->order_date)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d')) }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   :disabled="isLocked">
        </div>

        @if(isset($purchaseOrder))
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select id="status" name="status"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :disabled="isLocked">
                {{-- SỬA LỖI: Ưu tiên hàm old() cho thẻ select --}}
                <option value="pending" @if(old('status', $purchaseOrder->status) == 'pending') selected @endif>Chờ xử lý</option>
                <option value="waiting_for_scan" @if(old('status', $purchaseOrder->status) == 'waiting_for_scan') selected @endif>Chờ nhận hàng</option>
                <option value="cancelled" @if(old('status', $purchaseOrder->status) == 'cancelled') selected @endif>Đã hủy</option>
                @if($purchaseOrder->status == 'completed')
                    <option value="completed" @if(old('status', $purchaseOrder->status) == 'completed') selected @endif>Hoàn thành</option>
                @endif
            </select>
        </div>
        @endif
        
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            {{-- SỬA LỖI: Ưu tiên hàm old() để giữ lại giá trị sau khi validation thất bại --}}
            <textarea id="notes" name="notes" rows="4"
                      class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      :disabled="isLocked">{{ old('notes', isset($purchaseOrder) ? $purchaseOrder->notes : '') }}</textarea>
        </div>
    </div>
</div>