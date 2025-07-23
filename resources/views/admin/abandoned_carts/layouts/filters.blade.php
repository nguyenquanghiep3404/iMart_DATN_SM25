<form action="{{ route('admin.abandoned-carts.index') }}" method="GET">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-4">
        <!-- Tìm kiếm -->
        <div class="lg:col-span-2">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-input"
                placeholder="Tên khách hàng, email, SĐT...">
        </div>

        <!-- Trạng thái khôi phục -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái giỏ hàng</label>
            <select id="status" name="status" class="form-select">
                <option value="">Tất cả</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chưa khôi phục</option>
                <option value="recovered" {{ request('status') == 'recovered' ? 'selected' : '' }}>Đã khôi phục</option>
                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Đã lưu trữ</option>
            </select>
        </div>

        <!-- Trạng thái liên hệ -->
        <div>
            <label for="contact_status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái liên hệ</label>
            <select id="contact_status" name="contact_status" class="form-select">
                <option value="">Tất cả</option>
                <option value="not_sent_email" {{ request('contact_status') == 'not_sent_email' ? 'selected' : '' }}>
                    Chưa gửi Email</option>
                <option value="sent_email" {{ request('contact_status') == 'sent_email' ? 'selected' : '' }}>Đã gửi
                    Email</option>
                <option value="not_sent_in_app" {{ request('contact_status') == 'not_sent_in_app' ? 'selected' : '' }}>
                    Chưa gửi In-App</option>
                <option value="sent_in_app" {{ request('contact_status') == 'sent_in_app' ? 'selected' : '' }}>Đã gửi
                    In-App</option>
            </select>
        </div>

        <!-- Từ ngày -->
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-input">
        </div>

        <!-- Đến ngày -->
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-input">
        </div>
    </div>

    <div class="flex justify-end gap-x-3 pt-2 mb-6">
        <a href="{{ route('admin.abandoned-carts.index') }}" class="btn btn-secondary">Xóa lọc</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter mr-2"></i>Lọc giỏ hàng
        </button>
    </div>
</form>
