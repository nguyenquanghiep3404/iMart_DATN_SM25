@extends('admin.comments.layouts.main')

@section('content')
    <div class="bg-white rounded-md shadow p-6">
        <!-- Tìm kiếm & lọc -->
        <form action="{{ route('admin.comments.byProduct', $product->id) }}" method="GET" class="mb-8">
            <header class="mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Quản lý Bình luận sản phẩm: {{ $product->name }}</h1>
                <p class="text-gray-500 mt-1">
                    Xem, duyệt và quản lý tất cả các bình luận cho sản phẩm này.
                </p>
            </header>

            <div class="bg-white p-6 rounded-xl shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                            <input type="text" id="search" name="search" value="{{ request('search') }}"
                                placeholder="Nội dung, tên người gửi..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái bình
                            luận</label>
                        <select id="status" name="status"
                            class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt
                            </option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối
                            </option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Ngày gửi</label>
                        <input type="date" id="date" name="date" value="{{ request('date') }}"
                            class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                </div>

                <div class="mt-4 flex justify-end space-x-3">
                    <a href="{{ route('admin.comments.byProduct', $product->id) }}"
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
                        Xóa lọc
                    </a>
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold flex items-center space-x-2">
                        <i class="fas fa-filter"></i>
                        <span>Áp dụng</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Bảng bình luận dạng cây -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-gray-700 text-sm">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="px-4 py-3"></th>
                        <th class="px-4 py-3 font-semibold">Người bình luận</th>
                        <th class="px-4 py-3 font-semibold">Nội dung</th>
                        <th class="px-4 py-3 font-semibold">Phản hồi cho</th>
                        <th class="px-4 py-3 font-semibold">Ngày gửi</th>
                        <th class="px-4 py-3 font-semibold">Trạng thái</th>
                        <th class="px-4 py-3 font-semibold">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($comments->count())
                        @foreach ($comments as $comment)
                            @include('admin.comments.comments-row', [
                                'comment' => $comment,
                                'level' => 0,
                            ])
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-500 italic">Không có bình luận nào phù hợp.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="mb-4 mt-6">
            <button type="button" onclick="window.history.back()"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 font-semibold inline-flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Quay lại</span>
            </button>
        </div>
        <!-- Phân trang -->
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Hiển thị {{ $comments->firstItem() }} đến {{ $comments->lastItem() }} trong tổng số
                {{ $comments->total() }} bình luận
            </div>
            <div>
                {{ $comments->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection
