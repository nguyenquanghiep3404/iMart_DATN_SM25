@if (!isset($store))
    @php $store = null; @endphp
@endif
<!-- Add/Edit Staff Modal -->
<div id="add-staff-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-2xl m-4" id="modal-content">
        <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-6">Thêm Nhân Viên Mới</h2>
        <form id="add-staff-form">
            <div id="staff-form-errors" class="mb-2"></div>
            <input type="hidden" id="editing-staff-id">
            <input type="hidden" id="modal-store-id" name="store_location_id" value="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="new-staff-name">Họ và Tên <span
                            class="text-danger">*</span></label>
                    <input id="new-staff-name" name="name" type="text"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Nhập họ và tên">
                    <div id="error-name" class="text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="new-staff-email">Email <span
                            class="text-danger">*</span></label>
                    <input id="new-staff-email" name="email" type="email"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="example@email.com">
                    <div id="error-email" class="text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="new-staff-phone">Số Điện Thoại
                        <span class="text-danger">*</span></label>
                    <input id="new-staff-phone" name="phone" type="tel"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="09xxxxxxxx">
                    <div id="error-phone" class="text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="new-staff-status">Trạng
                        thái</label>
                    <select id="new-staff-status" name="status"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Không hoạt động</option>
                        <option value="banned">Đã khóa</option>
                    </select>
                    <div id="error-status" class="text-red-500 text-xs mt-1"></div>
                </div>
            </div>
            <!-- Phần mật khẩu -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="new-staff-password">Mật Khẩu <span
                            class="text-danger">*</span></label>
                    <input id="new-staff-password" name="password" type="password"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Nhập mật khẩu">
                    <div id="error-password" class="text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="new-staff-password-confirmation">Xác Nhận Mật Khẩu <span
                            class="text-danger">*</span></label>
                    <input id="new-staff-password-confirmation" name="password_confirmation" type="password"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Nhập lại mật khẩu">
                    <div id="error-password_confirmation" class="text-red-500 text-xs mt-1"></div>
                </div>
            </div>
            <div id="address-fields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="modal-province-select">Tỉnh/Thành
                        phố <span class="text-danger">*</span></label>
                    <select id="modal-province-select" name="province"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Chọn tỉnh/thành --</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province->code }}">{{ $province->name_with_type }}</option>
                        @endforeach
                    </select>
                    <div id="error-province" class="text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="modal-district-select">Quận/Huyện
                        <span class="text-danger">*</span></label>
                    <select id="modal-district-select" name="district"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        disabled>
                        <option value="">-- Chọn quận/huyện --</option>
                    </select>
                    <div id="error-district" class="text-red-500 text-xs mt-1"></div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="modal-store-select">Cửa Hàng
                        <span class="text-danger">*</span></label>
                    <select id="modal-store-select" name="store_location_id_select"
                        class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        disabled>
                        <option value="">-- Chọn cửa hàng --</option>
                    </select>
                    <div id="error-store_location_id" class="text-red-500 text-xs mt-1"></div>
                </div>
            </div>
            <div class="flex justify-end gap-4 mt-8">
                <button type="button" id="cancel-modal-btn"
                    class="px-6 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300 font-semibold">Hủy</button>
                <button type="submit" id="modal-submit-btn"
                    class="px-6 py-2 rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 font-semibold">Thêm
                    Mới</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cấu hình toastr (chỉ khi toastr có sẵn)
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            };
        }
        // Kiểm tra và hiển thị thông báo từ session storage
        const successMessage = sessionStorage.getItem('staff_success_message');
        if (successMessage) {
            if (typeof toastr !== 'undefined') {
                toastr.success(successMessage);
            } else {
                alert(successMessage); // Fallback nếu không có toastr
            }
            sessionStorage.removeItem('staff_success_message');
        }
        const modalProvinceSelect = document.getElementById('modal-province-select');
        const modalDistrictSelect = document.getElementById('modal-district-select');
        const modalStoreSelect = document.getElementById('modal-store-select');
        // Tải các quận khi tỉnh thay đổi
        modalProvinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;
            if (provinceCode) {
                fetch(`/api/locations/old/districts/${provinceCode}`)
                    .then(response => response.json())
                    .then(data => {
                        modalDistrictSelect.innerHTML =
                            '<option value="">-- Chọn quận/huyện --</option>';
                        data.data.forEach(district => {
                            modalDistrictSelect.innerHTML +=
                                `<option value="${district.code}">${district.name_with_type}</option>`;
                        });
                        modalDistrictSelect.disabled = false;
                    });
            } else {
                modalDistrictSelect.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
                modalDistrictSelect.disabled = true;
            }
            modalStoreSelect.innerHTML = '<option value="">-- Chọn cửa hàng --</option>';
            modalStoreSelect.disabled = true;
        });
        // Tải cửa hàng khi quận thay đổi
        modalDistrictSelect.addEventListener('change', function() {
            const provinceCode = modalProvinceSelect.value;
            const districtCode = this.value;
            if (provinceCode && districtCode) {
                fetch(
                        `/api/store-locations/stores?province_code=${provinceCode}&district_code=${districtCode}`)
                    .then(response => response.json())
                    .then(data => {
                        modalStoreSelect.innerHTML =
                        '<option value="">-- Chọn cửa hàng --</option>';
                        if (data.success && data.data) {
                            data.data.forEach(store => {
                                modalStoreSelect.innerHTML +=
                                    `<option value="${store.id}">${store.name}</option>`;
                            });
                        }
                        modalStoreSelect.disabled = false;
                    });
            } else {
                modalStoreSelect.innerHTML = '<option value="">-- Chọn cửa hàng --</option>';
                modalStoreSelect.disabled = true;
            }
        });
        const addStaffForm = document.getElementById('add-staff-form');
        addStaffForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const errorFields = ['name', 'email', 'phone', 'status', 'store_location_id', 'province', 'district', 'password', 'password_confirmation'];
            errorFields.forEach(field => {
                const el = document.getElementById('error-' + field);
                if (el) el.innerHTML = '';
            });
            document.getElementById('staff-form-errors').innerHTML = '';
            // Validate mật khẩu
            const password = document.getElementById('new-staff-password').value;
            const passwordConfirmation = document.getElementById('new-staff-password-confirmation').value;
            
            if (password && password.length < 8) {
                document.getElementById('error-password').innerHTML = '<div class="text-red-500 text-xs">Mật khẩu phải có ít nhất 8 ký tự</div>';
                return;
            }
            
            if (password && passwordConfirmation && password !== passwordConfirmation) {
                document.getElementById('error-password_confirmation').innerHTML = '<div class="text-red-500 text-xs">Mật khẩu xác nhận không khớp</div>';
                return;
            }
            const formData = new FormData(this);
            const staffId = document.getElementById('editing-staff-id').value;
            const url = staffId ? `/admin/sales-staff/api/employees/${staffId}` :
                '{{ route('admin.sales-staff.api.employees.store') }}';
            const method = staffId ? 'PUT' : 'POST';
            const storeIdInput = document.getElementById('modal-store-id');
            const storeSelect = document.getElementById('modal-store-select');
            if (!storeIdInput.value && storeSelect) {
                storeIdInput.value = storeSelect.value;
            }
            fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    let data = {};
                    try {
                        const text = await response.clone().text();
                        data = text ? JSON.parse(text) : {};
                    } catch (e) {
                        data = {};
                    }
                    const errorFields = ['name', 'email', 'phone', 'status',
                        'store_location_id', 'province', 'district', 'password', 'password_confirmation'
                    ];
                    errorFields.forEach(field => {
                        const el = document.getElementById('error-' + field);
                        if (el) el.innerHTML = '';
                    });
                    const errorDiv = document.getElementById('staff-form-errors');
                    errorDiv.innerHTML = '';
                    if (!response.ok && data && data.errors) {
                        Object.entries(data.errors).forEach(([field, arr]) => {
                            const el = document.getElementById('error-' + field);
                            if (el) el.innerHTML = `<div class="text-red-500 text-xs">${arr[0]}</div>`;
                        });
                        return; // Giữ nguyên popup, không đóng
                    } else if (!response.ok && data && data.message) {
                        errorDiv.innerHTML =
                            `<div class=\"text-red-600 text-sm mb-1\">${data.message}</div>`;
                        return;
                    } else if (!response.ok) {
                        errorDiv.innerHTML =
                            '<div class="text-red-600 text-sm mb-1">Lỗi không xác định. Vui lòng thử lại.</div>';
                        return;
                    } else {
                        errorDiv.innerHTML = '';
                        // Lưu thông báo vào sessionStorage để hiển thị sau khi reload
                        const successMessage = data.message || 'Thêm nhân viên thành công!';
                        sessionStorage.setItem('staff_success_message', successMessage);
                        closeModal();
                        location.reload();
                    }
                })
                .catch(() => {
                    document.getElementById('staff-form-errors').innerHTML =
                        '<div class="text-red-600 text-sm mb-1">Lỗi kết nối server hoặc lỗi JS.</div>';
                });
        });
        // Xử lý mở modal cho cả hai trang
        function openAddStaffModal(storeId = null) {
            const form = document.getElementById('add-staff-form');
            const modal = document.getElementById('add-staff-modal');
            const modalContent = document.getElementById('modal-content');
            const addressFields = document.getElementById('address-fields');
            const storeIdInput = document.getElementById('modal-store-id');
            if (!form || !modal || !modalContent) {
                return;
            }
            form.reset();
            ['name', 'email', 'phone', 'status', 'store_location_id', 'province', 'district', 'password', 'password_confirmation'].forEach(field => {
                const el = document.getElementById('error-' + field);
                if (el) el.innerHTML = '';
            });
            const errorDiv = document.getElementById('staff-form-errors');
            if (errorDiv) errorDiv.innerHTML = '';
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
            if (storeId) {
                storeIdInput.value = storeId;
                addressFields.style.display = 'none';
            } else {
                storeIdInput.value = '';
                addressFields.style.display = '';
            }
        }
        function closeModal() {
            document.getElementById('modal-content').classList.add('scale-95', 'opacity-0');
            setTimeout(() => document.getElementById('add-staff-modal').classList.add('hidden'), 200);
        }
        // Nút mở modal ở trang index
        const btnGlobal = document.getElementById('add-staff-btn-global');
        if (btnGlobal) {
            btnGlobal.addEventListener('click', function() {
                openAddStaffModal();
            });
        }
        // Nút mở modal ở trang employees
        const btnInView = document.getElementById('add-staff-btn-in-view');
        if (btnInView) {
            btnInView.addEventListener('click', function() {
                const storeId = this.getAttribute('data-store-id');
                openAddStaffModal(storeId);
            });

        }
        // Nút đóng modal
        const btnCancel = document.getElementById('cancel-modal-btn');
        if (btnCancel) btnCancel.addEventListener('click', closeModal);
        // Đóng modal khi click ra ngoài
        document.getElementById('add-staff-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    });
</script>
