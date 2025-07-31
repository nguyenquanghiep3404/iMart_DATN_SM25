@php($disableMainCss = true)
@extends('admin.layouts.app')

@section('content')
    @include('admin.customer_groups.layouts.css')
    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Phân Nhóm Khách Hàng đã bị xóa</h1>
                    <p class="mt-1 text-slate-500">Quản lý và tạo các chiến dịch marketing mục tiêu.</p>
                </div>
                <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                    <a href="{{ route('admin.customer-groups.index') }}" id="addGroupBtn"
                        class="inline-flex items-center justify-center px-5 py-3 text-sm font-medium text-white bg-indigo-500 rounded-lg shadow-md hover:bg-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition-all duration-200 transform hover:scale-105">
                        <!-- SVG Icon will be injected here -->
                        Quay lại trang danh sách
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                            <tr>
                                <th class="px-6 py-4">Tên Nhóm</th>
                                <th class="px-6 py-4">Điều kiện</th>
                                <th class="px-6 py-4 text-center">Số lượng khách</th>
                                <th class="px-6 py-4 text-center">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="groups-table-body">
                            <!-- JS sẽ render dữ liệu vào đây -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            const icons = {
                shoppingBag: `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>`,
                wallet: `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5" viewBox="0 0 24 24"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path></svg>`,
            };

            const groups = @json($groups).map(g => ({
                id: g.id,
                name: g.name,
                condition_orders: g.min_order_count || 0,
                condition_spend: parseFloat(g.min_total_spent) || 0,
                count: g.users_count || 0,
            }));

            const tableBody = document.getElementById('groups-table-body');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(value);

            const renderGroups = () => {
                tableBody.innerHTML = '';

                if (groups.length === 0) {
                    tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center px-6 py-4 text-slate-500">Không có nhóm nào bị xóa.</td>
                    </tr>
                `;
                    return;
                }

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
                    <td class="px-6 py-4 text-center font-medium">${group.count.toLocaleString('vi-VN')}</td>
                    <td class="px-6 py-4 text-center">
                        <button data-action="restore" data-id="${group.id}" class="p-2 bg-green-600 text-white rounded mr-2">Khôi phục</button>
                        <button data-action="forceDelete" data-id="${group.id}" class="p-2 bg-red-600 text-white rounded">Xóa vĩnh viễn</button>
                    </td>
                `;
                    tableBody.appendChild(row);
                });
            };

            tableBody.addEventListener('click', e => {
                const btn = e.target.closest('button');
                if (!btn) return;

                const action = btn.dataset.action;
                const id = btn.dataset.id;

                if (action === 'restore') {
                    Swal.fire({
                        title: 'Bạn có chắc chắn muốn khôi phục nhóm này?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Khôi phục',
                        cancelButtonText: 'Hủy'
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch(`/admin/customer-groups/${id}/restore`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    Swal.fire('Thành công', data.message, 'success').then(() => location
                                        .reload());
                                })
                                .catch(() => {
                                    Swal.fire('Lỗi', 'Không thể khôi phục nhóm', 'error');
                                });
                        }
                    });
                }

                if (action === 'forceDelete') {
                    Swal.fire({
                        title: 'Bạn có chắc chắn muốn xóa vĩnh viễn nhóm này?',
                        text: 'Hành động này không thể hoàn tác!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Xóa vĩnh viễn',
                        cancelButtonText: 'Hủy'
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch(`/admin/customer-groups/${id}/force-delete`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    Swal.fire('Đã xóa!', data.message, 'success').then(() => location
                                        .reload());
                                })
                                .catch(() => {
                                    Swal.fire('Lỗi', 'Không thể xóa nhóm', 'error');
                                });
                        }
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                renderGroups();
            });
        </script>
    @endsection
