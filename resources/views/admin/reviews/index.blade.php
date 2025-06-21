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
                                    <div class="font-semibold text-gray-900">{{ $review->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $review->user->email }}</div>
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
                                onclick="openStatusModal({{ $review->id }}, '{{ $review->user->name }}', '{{ $review->status }}')"
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

        modalUserName.textContent = userName;
        statusSelect.value = currentStatus;
        modal.dataset.reviewId = reviewId;
        modal.classList.remove('hidden');
    }
    document.addEventListener("DOMContentLoaded", () => {
        lucide.createIcons();

        const modal = document.getElementById('status-modal');
        const modalUserName = document.getElementById('modal-user-name');
        const statusSelect = document.getElementById('modal-review-status');
        const saveBtn = document.getElementById('save-status-change-btn');

        document.querySelectorAll('[data-open-status-modal]').forEach(button => {
            button.addEventListener('click', () => {
                const userName = button.getAttribute('data-user');
                const reviewId = button.getAttribute('data-id');
                const currentStatus = button.getAttribute('data-status');

                modalUserName.textContent = userName;
                statusSelect.value = currentStatus;
                modal.dataset.reviewId = reviewId;
                modal.classList.remove('hidden');
            });
        });

        document.getElementById('close-status-modal-btn').addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        document.getElementById('cancel-status-change-btn').addEventListener('click', () => {
            modal.classList.add('hidden');
        });

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
                .then(response => response.json())
                .then(data => {
                    location.reload();
                });
        });
    });
</script>
@endsection