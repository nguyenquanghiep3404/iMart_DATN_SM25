@extends('admin.layouts.app')
@section('content')
    @include('admin.marketing_campaigns.layouts.css')

    <body class="antialiased text-slate-700">

        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <!-- Page Header -->
            @include('admin.marketing_campaigns.layouts.header')

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                            <tr>
                                <th scope="col" class="px-6 py-4">Tên Chiến Dịch</th>
                                <th scope="col" class="px-6 py-4">Đối Tượng</th>
                                <th scope="col" class="px-6 py-4 text-center">Trạng Thái</th>
                                <th scope="col" class="px-6 py-4">Ngày Gửi</th>
                                <th scope="col" class="px-6 py-4 text-center">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="campaign-list-body">
                            <!-- Campaign rows will be inserted here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const icons = {
                    plusCircle: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>`,
                    eye: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>`,
                    pencil: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path><path d="m15 5 4 4"></path></svg>`,
                    trash: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>`,
                    trashLink: `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>`,
                    checkCircle: `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`,
                    edit3: `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>`,
                    clock: `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`,
                    alertCircle: `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`
                };

                document.getElementById('createCampaignBtn').insertAdjacentHTML('afterbegin', icons.plusCircle);
                document.getElementById('viewTrashBtn').innerHTML = icons.trashLink;

                // Dữ liệu campaigns được lấy từ PHP Controller
                const campaigns = @json($campaignData);

                const getStatusBadge = (status) => {
                    switch (status) {
                        case 'sent':
                            return {
                                text: 'Đã gửi', icon: icons.checkCircle, classes: 'bg-green-100 text-green-800'
                            };
                        case 'draft':
                            return {
                                text: 'Nháp', icon: icons.edit3, classes: 'bg-yellow-100 text-yellow-800'
                            };
                        case 'scheduled':
                            return {
                                text: 'Đã lên lịch', icon: icons.clock, classes: 'bg-blue-100 text-blue-800'
                            };
                        default:
                            return {
                                text: 'Không xác định', icon: icons.alertCircle, classes:
                                    'bg-slate-100 text-slate-800'
                            };
                    }
                };

                const renderCampaigns = () => {
                    const tbody = document.getElementById('campaign-list-body');
                    tbody.innerHTML = '';

                    campaigns.forEach(campaign => {
                        const statusInfo = getStatusBadge(campaign.status);
                        const isSent = campaign.status === 'sent';

                        const editButtonClass = isSent ?
                            'p-2.5 bg-slate-100 text-slate-400 rounded-lg cursor-not-allowed' :
                            'p-2.5 bg-slate-200 text-slate-600 rounded-lg hover:bg-slate-300 transition-colors duration-200';
                        const editButtonTitle = isSent ? 'Không thể sửa chiến dịch đã gửi' : 'Sửa';
                        const editButtonDisabled = isSent ? 'disabled' : '';

                        const row = `
                            <tr class="bg-white border-b last:border-b-0 border-slate-200 hover:bg-slate-50 transition">
                                <th scope="row" class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">${campaign.name}</th>
                                <td class="px-6 py-4">${campaign.target}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full ${statusInfo.classes}">
                                        ${statusInfo.icon} ${statusInfo.text}
                                    </span>
                                </td>
                                <td class="px-6 py-4">${campaign.sentDate || 'Chưa gửi'}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="/admin/marketing_campaigns/${campaign.id}" 
                                        class="p-2.5 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors duration-200" 
                                        title="Xem chi tiết">
                                        ${icons.eye}
                                        </a>
                                        <a href="/admin/marketing_campaigns/${campaign.id}/edit"
                                            class="${editButtonClass}"
                                            title="${editButtonTitle}"
                                            ${editButtonDisabled}>
                                            ${icons.pencil}
                                        </a>
                                        <button class="btn-delete p-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200"
                                        title="Xóa"
                                        data-id="${campaign.id}">${icons.trash}</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });
                };

                renderCampaigns();
            });
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete')) {
                    const id = e.target.closest('.btn-delete').dataset.id;

                    Swal.fire({
                        title: 'Bạn chắc chắn muốn xóa?',
                        text: "Chiến dịch sẽ được chuyển vào thùng rác!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#aaa',
                        confirmButtonText: 'Xóa'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/admin/marketing_campaigns/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                }).then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Đã xóa!', data.message, 'success').then(() => {
                                            // Cập nhật lại danh sách
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire('Lỗi!', data.message || 'Không thể xóa.', 'error');
                                    }
                                });
                        }
                    });
                }
            });
        </script>
    @endsection
