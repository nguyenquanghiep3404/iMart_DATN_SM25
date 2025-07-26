<div class="card">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin chung</h3>
        <div class="space-y-6">
            
            <!-- Supplier Selection -->
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Nhà cung cấp <span class="text-red-500">*</span></label>
                <input type="hidden" name="supplier_address_id" x-model="selectedSupplier.addressId">
                <div @click="openModal('supplier')" class="w-full p-2.5 bg-white border border-gray-300 rounded-lg cursor-pointer min-h-[42px] flex items-center">
                    <span x-show="!selectedSupplier.name" class="text-gray-500">Chọn địa chỉ nhà cung cấp...</span>
                    <div x-show="selectedSupplier.name" class="text-sm" x-cloak>
                        <p class="font-semibold text-gray-800" x-text="selectedSupplier.name"></p>
                        <p class="text-gray-600" x-text="selectedSupplier.address"></p>
                    </div>
                </div>
            </div>

            <!-- Location Selection (Updated Layout) -->
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Nhập về kho <span class="text-red-500">*</span></label>
                <input type="hidden" name="store_location_id" x-model="selectedLocation.id">
                <div @click="openModal('location')" class="w-full p-2.5 bg-white border border-gray-300 rounded-lg cursor-pointer min-h-[42px] flex items-center">
                    <span x-show="!selectedLocation.name" class="text-gray-500">Chọn kho nhận hàng...</span>
                    <div x-show="selectedLocation.name" class="text-sm" x-cloak>
                        <p class="font-semibold text-gray-800" x-text="selectedLocation.name"></p>
                        <p class="text-gray-600" x-text="selectedLocation.address"></p>
                    </div>
                </div>
            </div>

            <!-- Order Date -->
            <div>
                <label for="order-date" class="block mb-1 text-sm font-medium text-gray-700">Ngày đặt hàng</label>
                <input type="date" id="order-date" name="order_date" class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" style="color-scheme: light;">
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ghi chú</h3>
        <textarea name="notes" rows="4" class="w-full p-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Thêm ghi chú cho phiếu nhập kho..."></textarea>
    </div>
</div>
