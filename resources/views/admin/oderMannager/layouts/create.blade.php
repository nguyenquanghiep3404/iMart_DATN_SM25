<div class="p-8 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên <span
                    class="text-red-500">*</span></label>
            <input type="text" id="name" required
                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Nguyễn Văn A">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span
                    class="text-red-500">*</span></label>
            <input type="email" id="email" required
                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="example@email.com">
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
            <input type="password" id="password"
                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Để trống nếu không đổi">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nhập lại mật khẩu</label>
            <input type="password" id="password"
                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Để trống nếu không đổi">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span
                    class="text-red-500">*</span></label>
            <input type="email" id="email" required
                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="example@email.com">
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
            <select id="status"
                class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="active">Đang hoạt động</option>
                <option value="inactive">Không hoạt động</option>
            </select>
        </div>
    </div>
</div>
