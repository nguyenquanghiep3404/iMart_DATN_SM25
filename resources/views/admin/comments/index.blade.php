@extends('admin.comments.layouts.main')

@section('content')
    <div class="bg-white rounded-md shadow p-8">

        <!-- Page Header -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Danh sách sản phẩm có bình luận</h1>
            <p class="text-gray-600 mt-2 max-w-2xl">
                Xem các sản phẩm có bình luận và số lượng bình luận tương ứng.
            </p>
        </header>

        <!-- Filter Form -->
        <form action="{{ route('admin.comment.index') }}" method="GET">
            <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Search Field -->
                    <div class="w-full">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                        <div class="relative w-full">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                            <input type="text" id="search" name="search" value="{{ request('search') }}"
                                placeholder="Nhập tên sản phẩm..."
                                class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="w-full">
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sắp xếp theo</label>
                        <select id="sort" name="sort"
                            class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Chọn --</option>
                            <option value="most_commented" {{ request('sort') == 'most_commented' ? 'selected' : '' }}>
                                Từ nhiều đến ít
                            </option>
                            <option value="least_commented" {{ request('sort') == 'least_commented' ? 'selected' : '' }}>
                                Từ ít đến nhiều
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.comment.index') }}"
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


        <!-- Products Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-300 shadow-md">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-indigo-50">
                    <tr>
                        <th class="px-8 py-4 font-semibold tracking-wide text-indigo-700">Tên sản phẩm</th>
                        <th class="px-8 py-4 w-40 font-semibold tracking-wide text-indigo-700 text-center">Số bình luận</th>
                        <th class="px-8 py-4 w-48 font-semibold tracking-wide text-indigo-700 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="border-b hover:bg-indigo-50 transition">
                            <td class="px-8 py-5 font-medium">{{ $product->name }}</td>
                            <td class="px-8 py-5 text-center font-semibold text-indigo-600">
                                {{ $product->comments_count }}
                            </td>
                            <td class="px-8 py-5 text-center">
                                <a href="{{ route('admin.comments.byProduct', $product->id) }}"
                                    class="inline-block px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 font-semibold transition">
                                    Xem bình luận
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-12 text-gray-400 italic font-medium">
                                Không tìm thấy sản phẩm nào có bình luận.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-between items-center text-gray-600 text-sm font-medium">
            <div>
                Hiển thị {{ $products->firstItem() ?? 0 }} đến {{ $products->lastItem() ?? 0 }} trong tổng số
                {{ $products->total() }} sản phẩm
            </div>
            <div>
                {{ $products->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection
