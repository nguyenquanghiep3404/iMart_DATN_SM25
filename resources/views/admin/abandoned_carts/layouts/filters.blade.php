<form action="#" method="GET">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-4">
        <div class="lg:col-span-2">
            <label for="search_customer" class="block text-sm font-medium text-gray-700 mb-1">Tìm
                kiếm</label>
            <input type="text" id="search_customer" x-model="searchQuery" class="form-input"
                placeholder="Tên khách hàng, email, SĐT...">
        </div>
        <div>
            <label for="filter_cart_status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái giỏ
                hàng</label>
            <select id="filter_cart_status" x-model="cartStatusFilter" class="form-select">
                <option value="all">Tất cả</option>
                <option value="abandoned">Chưa khôi phục</option>
                <option value="recovered">Đã khôi phục</option>
            </select>
        </div>
        <div>
            <label for="filter_contact_status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái liên
                hệ</label>
            <select id="filter_contact_status" x-model="contactStatusFilter" class="form-select">
                <option value="all">Tất cả</option>
                <option value="not_sent_email">Chưa gửi Email</option>
                <option value="not_sent_in_app">Chưa gửi In-App</option>
                <option value="sent_email">Đã gửi Email</option>
                <option value="sent_in_app">Đã gửi In-App</option>
            </select>
        </div>
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Từ
                ngày</label>
            <input type="date" name="date_from" id="date_from" class="form-input">
        </div>
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Đến
                ngày</label>
            <input type="date" name="date_to" id="date_to" class="form-input">
        </div>
    </div>
    <div class="flex justify-end gap-x-3 pt-2 mb-6">
        <a href="#" class="btn btn-secondary">Xóa lọc</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-2"></i>Lọc giỏ
            hàng</button>
    </div>
</form>
