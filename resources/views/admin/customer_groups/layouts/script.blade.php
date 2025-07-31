<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- SVG Icons Object ---
        const icons = {
            plusCircle: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>`,
            pencil: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path><path d="m15 5 4 4"></path></svg>`,
            trash: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>`,
            trashLink: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>`,
            close: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`,
            shoppingBag: `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>`,
            wallet: `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path></svg>`
        };

        // --- Inject static icons ---
        document.getElementById('addGroupBtn').insertAdjacentHTML('afterbegin', icons.plusCircle);
        document.getElementById('viewTrashBtn').innerHTML = icons.trashLink;
        document.getElementById('closeModalBtn').innerHTML = icons.close;

        // --- State Management ---
        let groups = @json($groups).map(g => ({
            id: g.id,
            name: g.name,
            description: g.description,
            condition_orders: g.min_order_count || 0,
            condition_spend: parseFloat(g.min_total_spent) || 0,
            priority: g.priority || 0, // Lấy trường priority
            count: g.users_count || 0,
        }));

        // --- DOM Elements ---
        const modal = document.getElementById('groupModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('groupForm');
        const tableBody = document.getElementById('groups-table-body');

        // --- Helper Functions ---
        const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(value);

        const openModal = (group = null) => {
            form.reset();
            if (group) {
                modalTitle.textContent = 'Chỉnh Sửa Nhóm';
                document.getElementById('groupId').value = group.id;
                document.getElementById('name').value = group.name;
                document.getElementById('description').value = group.description;
                document.getElementById('condition_orders').value = group.condition_orders;
                document.getElementById('condition_spend').value = group.condition_spend;
                document.getElementById('priority').value = group.priority || 0;
            } else {
                modalTitle.textContent = 'Tạo Nhóm Khách Hàng Mới';
                document.getElementById('groupId').value = '';
                document.getElementById('priority').value = 0;
            }
            modal.classList.remove('hidden');
            setTimeout(() => modal.querySelector('.animate-fade-in-scale').classList.remove('opacity-0'),
                10);
        };

        const closeModal = () => {
            modal.querySelector('.animate-fade-in-scale').classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        };

        // const renderGroups = () => {
        //     tableBody.innerHTML = '';
        //     groups.forEach(group => {
        //         const row = document.createElement('tr');
        //         row.className =
        //             'bg-white border-b last:border-b-0 border-slate-200 hover:bg-slate-50 transition';
        //         row.innerHTML = `
        //         <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">${group.name}</td>
        //         <td class="px-6 py-4">
        //             <div class="flex flex-col space-y-1">
        //                 ${group.condition_orders > 0 ? `<span class="text-xs inline-flex items-center font-bold leading-sm uppercase px-3 py-1 bg-blue-100 text-blue-700 rounded-full">${icons.shoppingBag} ${group.condition_orders}+ đơn hàng</span>` : ''}
        //                 ${group.condition_spend > 0 ? `<span class="text-xs inline-flex items-center font-bold leading-sm uppercase px-3 py-1 bg-green-100 text-green-700 rounded-full">${icons.wallet} ${formatCurrency(group.condition_spend)}+ chi tiêu</span>` : ''}
        //             </div>
        //         </td>
        //         <td class="px-6 py-4 text-center font-medium">${group.count.toLocaleString('vi-VN')}</td>
        //         <td class="px-6 py-4 text-center">
        //             <div class="flex items-center justify-center space-x-2">
        //                 <button data-action="edit" data-id="${group.id}" class="p-2.5 bg-slate-200 text-slate-600 rounded-lg hover:bg-slate-300 transition-colors duration-200" title="Sửa">${icons.pencil}</button>
        //                 <button data-action="delete" data-id="${group.id}" class="p-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200" title="Xóa">${icons.trash}</button>
        //             </div>
        //         </td>
        //     `;
        //         tableBody.appendChild(row);
        //     });
        // };
        const renderGroups = () => {
            tableBody.innerHTML = '';
            groups.forEach(group => {
                const row = document.createElement('tr');
                row.className =
                    'bg-white border-b last:border-b-0 border-slate-200 hover:bg-slate-50 transition';
                row.innerHTML = `
            <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">${group.name}</td>
            <td class="px-6 py-4">
                <div class="flex flex-col space-y-1">
                    ${group.condition_orders > 0 ? `<span class="text-xs inline-flex items-center font-bold leading-sm uppercase px-3 py-1 bg-blue-100 text-blue-700 rounded-full">${icons.shoppingBag} ${group.condition_orders}+ đơn hàng</span>` : ''}
                    ${group.condition_spend > 0 ? `<span class="text-xs inline-flex items-center font-bold leading-sm uppercase px-3 py-1 bg-green-100 text-green-700 rounded-full">${icons.wallet} ${formatCurrency(group.condition_spend)}+ chi tiêu</span>` : ''}
                </div>
            </td>
            <td class="px-6 py-4 text-center font-medium">${group.priority}</td>  <!-- Thêm cột priority -->
            <td class="px-6 py-4 text-center font-medium">${group.count.toLocaleString('vi-VN')}</td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center space-x-2">
                    <button data-action="edit" data-id="${group.id}" class="p-2.5 bg-slate-200 text-slate-600 rounded-lg hover:bg-slate-300 transition-colors duration-200" title="Sửa">${icons.pencil}</button>
                    <button data-action="delete" data-id="${group.id}" class="p-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200" title="Xóa">${icons.trash}</button>
                </div>
            </td>
        `;
                tableBody.appendChild(row);
            });
        };


        // --- Event Listeners ---
        document.getElementById('addGroupBtn').addEventListener('click', () => openModal());
        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('cancelModalBtn').addEventListener('click', closeModal);

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const id = document.getElementById('groupId').value;
            const name = document.getElementById('name').value.trim();
            const minOrders = document.getElementById('condition_orders').value;
            const minSpend = document.getElementById('condition_spend').value;
            const priority = document.getElementById('priority').value;
            let errors = [];
            if (!name) errors.push("Tên nhóm không được để trống.");
            if (minOrders === '') errors.push("Số đơn hàng tối thiểu không được để trống.");
            if (minSpend === '') errors.push("Tổng chi tiêu tối thiểu không được để trống.");

            if (errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: errors.join('\n'),
                });
                return;
            }

            const formData = {
                name,
                description: document.getElementById('description').value,
                min_order_count: parseInt(minOrders) || 0,
                min_total_spent: parseFloat(minSpend) || 0,
                priority: parseInt(priority) || 0,
            };

            const url = id ? `/admin/customer-groups/${id}` : '/admin/customer-groups';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                    },
                    body: JSON.stringify(formData)
                })
                .then(res => {
                    if (!res.ok) return res.json().then(err => Promise.reject(err));
                    return res.json();
                })
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: data.message || 'Đã lưu nhóm khách hàng!',
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch(err => {
                    let message = 'Đã xảy ra lỗi khi lưu nhóm.';
                    if (err && err.errors) {
                        const allErrors = Object.values(err.errors).flat();
                        message = allErrors.join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: message
                    });

                    console.error(err);
                });
        });


        tableBody.addEventListener('click', (e) => {
            const button = e.target.closest('button');
            if (!button) return;

            const action = button.dataset.action;
            const id = button.dataset.id;

            if (action === 'edit') {
                const groupToEdit = groups.find(g => g.id == id);
                openModal(groupToEdit);
            }

            if (action === 'delete') {
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: 'Nhóm này sẽ bị xóa (bạn có thể khôi phục sau)',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/admin/customer-groups/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Accept': 'application/json',
                                },
                            })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire('Đã xóa!', data.message, 'success');
                                groups = groups.filter(g => g.id != id);
                                renderGroups();
                            })
                            .catch(err => {
                                Swal.fire('Lỗi!', 'Không thể xóa nhóm khách hàng.',
                                    'error');
                                console.error(err);
                            });
                    }
                });
            }

        });

        // khôi phục
        document.addEventListener('click', (e) => {
            const restoreBtn = e.target.closest('.btn-restore');
            if (restoreBtn) {
                const id = restoreBtn.dataset.id;
                fetch(`/admin/customer-groups/${id}/restore`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json',
                        },
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire('Thành công', data.message, 'success').then(() => {
                            location.reload();
                        });
                    })
                    .catch(err => {
                        Swal.fire('Lỗi', 'Không thể khôi phục nhóm', 'error');
                    });
            }
        });

        // --- Initial Render ---
        renderGroups();
    });
</script>
