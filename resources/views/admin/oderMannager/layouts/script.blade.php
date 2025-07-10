<script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- MOCK DATA ---
        let mockStaff = [{
                id: 1,
                name: 'Nguyễn Thị Mai',
                email: 'mai.nt@example.com',
                avatar: 'https://placehold.co/40x40/EC4899/FFFFFF?text=M',
                status: 'active',
                createdAt: '2024-02-15T10:00:00Z',
                stats: {
                    orders_processed: 1258
                }
            },
            {
                id: 2,
                name: 'Trần Minh Quang',
                email: 'quang.tm@example.com',
                avatar: 'https://placehold.co/40x40/3B82F6/FFFFFF?text=Q',
                status: 'active',
                createdAt: '2023-12-01T11:00:00Z',
                stats: {
                    orders_processed: 2140
                }
            },
            {
                id: 3,
                name: 'Lê Thu Hà',
                email: 'ha.lt@example.com',
                avatar: 'https://placehold.co/40x40/F59E0B/FFFFFF?text=H',
                status: 'inactive',
                createdAt: '2023-10-10T09:00:00Z',
                stats: {
                    orders_processed: 890
                }
            },
            {
                id: 4,
                name: 'Phạm Văn Đồng',
                email: 'dong.pv@example.com',
                avatar: 'https://placehold.co/40x40/10B981/FFFFFF?text=D',
                status: 'active',
                createdAt: '2024-04-01T14:00:00Z',
                stats: {
                    orders_processed: 650
                }
            },
        ];

        // --- STATE ---
        let currentEditingId = null;

        // --- DOM ELEMENTS ---
        const tbody = document.getElementById('staff-tbody');
        const noResultsDiv = document.getElementById('no-results');
        const searchInput = document.getElementById('search-input');
        const statusFilter = document.getElementById('status-filter');

        // Modal elements
        const staffModal = document.getElementById('staff-modal');
        const staffForm = document.getElementById('staff-form');
        const modalTitle = document.getElementById('modal-title');

        // --- UTILITY FUNCTIONS ---
        const formatDate = (dateString) => {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        };

        const formatNumber = (num) => new Intl.NumberFormat('en-US').format(num);

        const statusMap = {
            active: {
                text: 'Hoạt động',
                class: 'status-active'
            },
            inactive: {
                text: 'Tạm nghỉ',
                class: 'status-inactive'
            },
        };

        // --- RENDER FUNCTIONS ---
        function renderStaffRow(staff, index) {
            const staffStatus = statusMap[staff.status] || {
                text: 'N/A',
                class: ''
            };

            return `
                <tr class="bg-white border-b last:border-b-0 hover:bg-gray-50">
                    <td class="p-6 font-semibold text-gray-500 text-center">${index + 1}</td>
                    <td class="p-6">
                        <div class="flex items-center">
                            <img src="${staff.avatar}" class="w-10 h-10 rounded-full mr-4 object-cover">
                            <div>
                                <div class="font-semibold text-gray-800">${staff.name}</div>
                                <div class="text-gray-500">${staff.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="p-6">${formatDate(staff.createdAt)}</td>
                    <td class="p-6"><span class="status-badge ${staffStatus.class}">${staffStatus.text}</span></td>
                    <td class="p-6 text-center">
                        <button onclick='openModal(${staff.id})' class="text-indigo-600 hover:text-indigo-900 text-lg" title="Chỉnh sửa"><i class="fas fa-edit"></i></button>
                        <button class="text-red-600 hover:text-red-900 text-lg ml-4" title="Xóa"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        }

        function renderTable(staffToRender) {
            if (!staffToRender || staffToRender.length === 0) {
                tbody.innerHTML = '';
                noResultsDiv.classList.remove('hidden');
                return;
            }
            noResultsDiv.classList.add('hidden');
            tbody.innerHTML = staffToRender.map((staff, index) => renderStaffRow(staff, index)).join('');
        }

        // --- MODAL LOGIC ---
        window.openModal = (staffId = null) => {
            staffForm.reset();
            currentEditingId = staffId;

            if (staffId !== null) {
                modalTitle.textContent = "Chỉnh sửa thông tin nhân viên";
                const staff = mockStaff.find(s => s.id === staffId);
                if (staff) {
                    document.getElementById('name').value = staff.name;
                    document.getElementById('email').value = staff.email;
                    document.getElementById('status').value = staff.status;
                    document.getElementById('password').required = false;
                }
            } else {
                modalTitle.textContent = "Thêm nhân viên mới";
                document.getElementById('password').required = true;
            }

            staffModal.classList.remove('hidden');
        };

        window.closeModal = () => {
            staffModal.classList.add('hidden');
            currentEditingId = null;
            document.getElementById('password').required = false;
        };


        // --- FILTERING LOGIC ---
        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const status = statusFilter.value;

            let filteredStaff = mockStaff.filter(staff => {
                const matchesSearch = searchTerm === '' ||
                    staff.name.toLowerCase().includes(searchTerm) ||
                    staff.email.toLowerCase().includes(searchTerm);

                const matchesStatus = status === '' || staff.status === status;

                return matchesSearch && matchesStatus;
            });

            renderTable(filteredStaff);
        }

        function clearFilters() {
            searchInput.value = '';
            statusFilter.value = '';
            renderTable(mockStaff);
        }

        document.getElementById('apply-filters-btn').addEventListener('click', applyFilters);
        document.getElementById('clear-filters-btn').addEventListener('click', clearFilters);

        // --- INITIALIZATION ---
        renderTable(mockStaff);
    });
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right", // trượt từ phải vào
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "slideDown",
        "hideMethod": "slideUp"
    };

    @if (session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if (session('error'))
        toastr.error("{{ session('error') }}");
    @endif
    // function openModal() {
    //     document.getElementById('staff-modal').classList.remove('hidden');
    // }

    // function closeModal() {
    //     document.getElementById('staff-modal').classList.add('hidden');
    // }
</script>
