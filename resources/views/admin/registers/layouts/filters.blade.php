<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
    <div class="flex space-x-2">
        <a href="{{ route('admin.registers.index') }}" class="btn btn-primary">Danh sách</a>
        <a href="{{ route('admin.registers.trashed') }}" class="btn btn-danger flex items-center">
            <i class="fas fa-trash-alt mr-2"></i>Thùng rác
        </a>
    </div>
</div>

<form action="{{ route('admin.registers.index') }}" method="GET">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4" x-data="{
        searchQuery: '{{ request('search') }}',
        locationFilter: '{{ request('location_id', 'all') }}',
        statusFilter: '{{ request('status', 'all') }}'
    }">
        <!-- Tìm kiếm -->
        <div class="lg:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
            <input type="text" name="search" id="search" x-model="searchQuery" class="form-input"
                placeholder="Tìm theo tên máy, Device UID...">
        </div>

        <!-- Lọc cửa hàng -->
        <div>
            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Cửa hàng</label>
            <select name="location_id" id="location" x-model="locationFilter" class="form-select">
                <option value="all">Tất cả cửa hàng</option>
                @foreach ($locations as $location)
                    <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                @endforeach
            </select>
        </div>

        <!-- Lọc trạng thái -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select name="status" id="status" x-model="statusFilter" class="form-select">
                <option value="all">Tất cả trạng thái</option>
                <option value="active">Đang hoạt động</option>
                <option value="inactive">Không hoạt động</option>
            </select>
        </div>
    </div>

    <!-- Nút hành động -->
    <div class="flex justify-end gap-x-3 pt-2 mb-6">
        <a href="{{ route('admin.registers.index') }}" class="btn btn-secondary">Xóa lọc</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter mr-2"></i>Lọc
        </button>
    </div>
</form>
