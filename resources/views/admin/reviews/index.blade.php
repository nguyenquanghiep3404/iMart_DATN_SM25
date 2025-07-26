@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Quản lý Đánh giá</h1>
        <p class="mt-1 text-sm text-gray-600">Xem, duyệt và quản lý tất cả các đánh giá sản phẩm của bạn.</p>
    </header>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Tổng số đánh giá</h3>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $reviews->total() }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Chờ duyệt</h3>
            <p class="mt-2 text-3xl font-semibold text-yellow-500">{{ $reviews->where('status', 'pending')->count() }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Đã duyệt</h3>
            <p class="mt-2 text-3xl font-semibold text-green-500">{{ $reviews->where('status', 'approved')->count() }}</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-sm font-medium text-gray-500">Đã từ chối</h3>
            <p class="mt-2 text-3xl font-semibold text-red-500">{{ $reviews->where('status', 'rejected')->count() }}</p>
        </div>
    </div>

    <!-- Bộ lọc nâng cao -->
    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
        <form method="GET" action="{{ route('admin.reviews.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </span>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Tên sản phẩm, người đánh giá..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" id="status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    </select>
                </div>
                <div>
                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Xếp hạng</label>
                    <select name="rating" id="rating" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Tất cả</option>
                        @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Ngày đánh giá</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="mt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.reviews.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Xóa lọc</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                    <i class="fas fa-filter"></i>
                    <span>Áp dụng</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người đánh giá</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sản phẩm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày gửi</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reviews as $review)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img src="{{ $review->user->avatar_url ?? 'https://placehold.co/40x40' }}" alt="Avatar" class="w-10 h-10 rounded-full mr-4">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $review->user->name ?? 'Khách vãng lai' }}</div>
                                    <div class="text-xs text-gray-500">{{ $review->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-normal">
                            <div class="flex items-center space-x-1 mb-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i data-lucide="star" class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                            </div>
                            <div class="font-medium text-gray-800">{{ $review->title }}</div>
                            <p class="text-sm text-gray-600 max-w-sm" title="{{ $review->comment }}">{{ $review->comment }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img src="{{ $review->variant->product->image_url ?? 'https://placehold.co/60x60' }}" class="w-12 h-12 rounded mr-3">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $review->variant->product->name }}</div>
                                    @if ($review->is_verified_purchase)
                                    <span class="text-xs text-green-500 font-semibold">Đã xác thực</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                            $badge = match($review->status) {
                            'approved' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800',
                            };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                <i data-lucide="{{ $review->status === 'approved' ? 'check-circle' : ($review->status === 'rejected' ? 'x-circle' : 'clock') }}" class="w-4 h-4 mr-1"></i>
                                {{ ucfirst($review->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $review->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <button
                                onclick="openStatusModal( '{{ $review->id }}', '{{ $review->user->name ?? 'Khách vãng lai' }}', '{{ $review->status }}')"
                                class="text-indigo-600 hover:text-indigo-900 text-lg"
                                title="Thay đổi trạng thái">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Không có đánh giá nào phù hợp.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Modal thay đổi trạng thái -->
        <div id="status-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
            <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Thay đổi trạng thái Đánh giá</h2>
                    <button id="close-status-modal-btn" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div id="modal-warning" class="hidden text-sm text-red-600 mb-3">
                        Không thể thay đổi trạng thái đã duyệt hoặc đã từ chối.
                    </div>
                    <p class="mb-4 text-gray-700">Bạn đang thay đổi trạng thái cho đánh giá của <strong id="modal-user-name" class="text-gray-900"></strong>.</p>
                    <div class="mb-2">
                        <label for="modal-review-status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái mới</label>
                        <select id="modal-review-status" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="pending">Chờ duyệt</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="rejected">Từ chối</option>
                        </select>
                    </div>
                    <div class="p-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-2xl">
                        <button id="cancel-status-change-btn" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Hủy</button>
                        <button id="save-status-change-btn" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Lưu thay đổi</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="p-4">
            {{ $reviews->links() }}
        </div>
    </div>
</div>

<style>
    body {
        font-family: 'Be Vietnam Pro', sans-serif;
        background-color: #f8f9fa;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .status-approved {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #d97706;
    }

    .status-rejected {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .modal {
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .modal.hidden {
        opacity: 0;
        visibility: hidden;
    }

    .modal-content {
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }

    .modal:not(.hidden) .modal-content {
        transform: scale(1);
    }
</style>

<script>
    function openStatusModal(reviewId, userName = '', currentStatus = 'pending') {
        const modal = document.getElementById('status-modal');
        const modalUserName = document.getElementById('modal-user-name');
        const statusSelect = document.getElementById('modal-review-status');
        const warning = document.getElementById('modal-warning');
        const saveBtn = document.getElementById('save-status-change-btn');

        modalUserName.textContent = userName;
        statusSelect.value = currentStatus;
        modal.dataset.reviewId = reviewId;
        modal.dataset.currentStatus = currentStatus; // lưu lại trạng thái gốc
        modal.classList.remove('hidden');

        if (currentStatus !== 'pending') {
            warning.classList.remove('hidden');
            statusSelect.disabled = true;
            saveBtn.disabled = true;
        } else {
            warning.classList.add('hidden');
            statusSelect.disabled = false;
            saveBtn.disabled = false;
        }
    }


    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('status-modal');
        const statusSelect = document.getElementById('modal-review-status');
        const saveBtn = document.getElementById('save-status-change-btn');
        const cancelBtn = document.getElementById('cancel-status-change-btn');
        const closeBtn = document.getElementById('close-status-modal-btn');

        cancelBtn?.addEventListener('click', () => modal.classList.add('hidden'));
        closeBtn?.addEventListener('click', () => modal.classList.add('hidden'));

        saveBtn.addEventListener('click', () => {
            const newStatus = statusSelect.value;
            const reviewId = modal.dataset.reviewId;

            fetch(`/admin/reviews/${reviewId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        // Không hiển thị thông báo lỗi nữa, chỉ return
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data?.success) {
                        location.reload();
                    }
                });
        });
    });
    if (currentStatus !== 'pending') {
        document.getElementById('modal-review-status').disabled = true;
        saveBtn.disabled = true;
    } else {
        document.getElementById('modal-review-status').disabled = false;
        saveBtn.disabled = false;
    }
    const warning = document.getElementById('modal-warning');
    if (currentStatus !== 'pending') {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
</script>

@endsection