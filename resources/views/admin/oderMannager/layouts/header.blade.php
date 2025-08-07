<header class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Quản lý Nhân viên Xử lý Đơn hàng</h1>
        <p class="text-gray-500 mt-1">Thêm mới, tìm kiếm và quản lý thông tin nhân viên.</p>
    </div>

    <a href="{{ route('admin.order-manager.create', isset($warehouse) ? ['warehouse_id' => $warehouse->id] : []) }}"
        class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Thêm nhân viên mới</span>
    </a>
</header>
