<form method="GET" action="{{ route('admin.order-manager.warehouse.show', $warehouse->id) }}" class="p-6 border-b border-gray-200">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Ô tìm kiếm -->
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fas fa-search text-gray-400"></i>
                </span>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Tên, Email..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Trạng thái -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select name="status" id="status"
                class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Tất cả</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động
                </option>
            </select>
        </div>

        <!-- Nút -->
        <div class="flex items-end space-x-3">
            <button type="submit"
                class="w-full px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center justify-center space-x-2">
                <i class="fas fa-filter"></i>
                <span>Áp dụng</span>
            </button>
            <a href="{{ route('admin.order-manager.warehouse.show', $warehouse->id) }}"
                class="w-full px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold text-center">
                Xóa lọc
            </a>
        </div>
    </div>
</form>
