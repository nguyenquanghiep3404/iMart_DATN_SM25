@extends('admin.comments.layouts.main') {{-- Hoặc layout khác nếu bạn dùng --}}

@section('content')
<div class="bg-white rounded-md shadow p-6">
    <h2 class="text-xl font-bold mb-6">Danh mục bài viết</h2>

    <div class="overflow-x-auto">
        <div class="mb-4 flex justify-between max-w-4xl gap-2 w-full">
            <form method="GET" action="{{ route('admin.categories_post.index') }}" class="flex gap-2 flex-grow">
                <input type="text" name="keyword" value="{{ request('keyword') }}"
                       class="flex-grow border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="Tìm kiếm danh mục...">
                <button type="submit"
                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 shadow whitespace-nowrap">
                    Tìm kiếm
                </button>
            </form>
        
            <a href="{{ route('admin.categories_post.create') }}"
               class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 shadow whitespace-nowrap">
                ➕ Thêm danh mục bài viết
            </a>
        </div>
        
        <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase font-semibold text-gray-600">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Tên danh mục</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Mô tả</th>
                    <th class="px-4 py-3 text-right">Parent ID</th>
                    <th class="px-4 py-3 text-right">Tạo lúc</th>
                    <th class="px-4 py-3 text-right">Cập nhật</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories_post as $category)
                    {{-- Danh mục cha --}}
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $category->id }}</td>
                        <td class="px-4 py-3 font-bold text-gray-800">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->slug }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->description }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $category->parent_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $category->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $category->updated_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2 whitespace-nowrap">
                                <a href="{{ route('admin.categories_post.show', $category->id) }}"
                                   class="px-3 py-2 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 shadow"
                                   title="Xem">Xem</a>

                                <a href="{{ route('admin.categories_post.edit', $category->id) }}"
                                   class="px-3 py-2 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600 shadow"
                                   title="Sửa">Sửa</a>

                                <form action="{{ route('admin.categories_post.destroy', $category->id) }}" method="POST"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xoá danh mục này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-2 bg-red-500 text-white text-xs font-medium rounded hover:bg-red-600 shadow"
                                            title="Xóa">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- Danh mục con --}}
                    @foreach($category->children as $child)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $child->id }}</td>
                            <td class="px-4 py-3 ps-8 text-gray-600">↳ {{ $child->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $child->slug }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $child->description }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $child->parent_id }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $child->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $child->updated_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2 whitespace-nowrap">
                                    <a href="{{ route('admin.categories_post.show', $child->id) }}"
                                       class="px-3 py-2 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 shadow"
                                       title="Xem">Xem</a>
                            
                                    <a href="{{ route('admin.categories_post.edit', $child->id) }}"
                                       class="px-3 py-2 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600 shadow"
                                       title="Sửa">Sửa</a>
                            
                                    <form action="{{ route('admin.categories_post.destroy', $child->id) }}" method="POST"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xoá danh mục này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-2 bg-red-500 text-white text-xs font-medium rounded hover:bg-red-600 shadow"
                                                title="Xóa">Xóa</button>
                                    </form>
                                </div>
                            </td>
                            
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Hiển thị {{ $categories_post->firstItem() }} – {{ $categories_post->lastItem() }} / Tổng: {{ $categories_post->total() }}
        </div>
        <div>
            {{ $categories_post->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
