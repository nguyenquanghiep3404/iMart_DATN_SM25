@extends('admin.layouts.app')
@include('admin.post_categories.css')
@section('content')
    <div class="px-4 sm:px-6 md:px-8 py-8">
        <div class="container mx-auto max-w-full">
            <!-- PAGE HEADER -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Quản lý Danh mục Bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li><a href="#" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="mx-2 text-gray-400">/</li>
                        <li class="text-gray-700 font-medium">Danh mục Bài viết</li>
                    </ol>
                </nav>
            </header>

            <div class="card-custom">
                <div class="card-custom-header">
                    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                        <h3 class="card-custom-title">
                            Danh sách danh mục ({{ $categories_post->total() }})
                        </h3>
                        <a href="{{ route('admin.categories_post.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i> Thêm danh mục mới
                        </a>
                    </div>
                </div>

                <div class="card-custom-body">
                    <!-- SEARCH BAR -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('admin.categories_post.index') }}" class="flex w-full gap-2">
                            <input type="text" name="keyword" value="{{ request('keyword') }}"
                                placeholder="Tìm kiếm theo tên danh mục..."
                                class="form-input flex-grow rounded border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <button type="submit" class="btn btn-secondary px-4 min-w-[60px]">Tìm</button>
                            <a href="{{ route('admin.categories_post.index') }}"
                                class="btn btn-danger px-4 min-w-[80px]">Hủy tìm</a>
                        </form>
                    </div>

                    <!-- TABLE -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Tên Danh mục</th>
                                    <th>Slug</th>
                                    <th class="text-center">Số mục con</th>
                                    <th>Mô tả</th>
                                    <th class="text-center" style="width: 120px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories_post as $category)
                                    <!-- Danh mục cha -->
                                    <tr class="hover:bg-gray-50">
                                        <td class="font-semibold text-gray-800">{{ $category->name }}</td>
                                        <td class="font-mono text-sm">{{ $category->slug }}</td>
                                        <td class="text-center">{{ $category->children->count() }}</td>
                                        <td class="text-gray-600">{{ $category->description ?? '—' }}</td>

                                        <td class="text-center">
                                            <div
                                                style="display: flex; justify-content: center; align-items: center; gap: 6px;">
                                                <!-- Nút Sửa -->
                                                <a href="{{ route('admin.categories_post.edit', $category->id) }}"
                                                    style="
                                                        display: inline-flex;
                                                        justify-content: center;
                                                        align-items: center;
                                                        width: 30px;
                                                        height: 30px;
                                                        background-color: #4F46E5;
                                                        color: white;
                                                        border-radius: 6px;
                                                        text-decoration: none;
                                                        font-size: 16px;
                                                        border: none;
                                                        cursor: pointer;
                                                    "
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit" style="line-height: 1;"></i>
                                                </a>

                                                <!-- Nút Xóa -->
                                                <form action="{{ route('admin.categories_post.destroy', $category->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn xoá danh mục này không?');"
                                                    style="margin:0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        style="
                                                            display: inline-flex;
                                                            justify-content: center;
                                                            align-items: center;
                                                            width: 30px;
                                                            height: 30px;
                                                            background-color: #DC2626;
                                                            color: white;
                                                            border-radius: 6px;
                                                            border: none;
                                                            font-size: 16px;
                                                            cursor: pointer;
                                                            padding: 0;
                                                            margin: 0;
                                                        "
                                                        title="Xóa">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            style="width: 16px; height: 16px;" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Danh mục con -->
                                    @foreach ($category->children as $child)
                                        <tr class="hover:bg-gray-50">
                                            <td class="text-gray-600 ps-6">↳ {{ $child->name }}</td>
                                            <td class="font-mono text-sm">{{ $child->slug }}</td>
                                            <td class="text-center">{{ $child->children->count() }}</td>
                                            <td class="text-gray-600">{{ $child->description ?? '—' }}</td>

                                            <td class="text-center">
                                                <div
                                                    style="display: flex; justify-content: center; align-items: center; gap: 6px;">
                                                    <!-- Nút Sửa -->
                                                    <a href="{{ route('admin.categories_post.edit', $child->id) }}"
                                                        style="
                                                            display: inline-flex;
                                                            justify-content: center;
                                                            align-items: center;
                                                            width: 30px;
                                                            height: 30px;
                                                            background-color: #4F46E5;
                                                            color: white;
                                                            border-radius: 6px;
                                                            text-decoration: none;
                                                            font-size: 16px;
                                                            border: none;
                                                            cursor: pointer;
                                                        "
                                                        title="Chỉnh sửa">
                                                        <i class="fas fa-edit" style="line-height: 1;"></i>
                                                    </a>

                                                    <!-- Nút Xóa -->
                                                    <form action="{{ route('admin.categories_post.destroy', $child->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Bạn có chắc chắn muốn xoá danh mục này không?');"
                                                        style="margin:0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            style="
                                                                display: inline-flex;
                                                                justify-content: center;
                                                                align-items: center;
                                                                width: 30px;
                                                                height: 30px;
                                                                background-color: #DC2626;
                                                                color: white;
                                                                border-radius: 6px;
                                                                border: none;
                                                                font-size: 16px;
                                                                cursor: pointer;
                                                                padding: 0;
                                                                margin: 0;
                                                            "
                                                            title="Xóa">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                style="width: 16px; height: 16px;" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-6 text-gray-500">
                                            Không tìm thấy danh mục nào.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            Hiển thị {{ $categories_post->firstItem() }} – {{ $categories_post->lastItem() }} /
                            Tổng: {{ $categories_post->total() }}
                        </div>
                        <div>
                            {{ $categories_post->links('pagination::tailwind') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
