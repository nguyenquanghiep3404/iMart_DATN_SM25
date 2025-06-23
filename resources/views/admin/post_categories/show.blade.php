@extends('admin.comments.layouts.main')

@section('content')
<div class="max-w-4xl mx-auto mt-10">
    <div class="bg-white shadow-md rounded-lg border border-gray-200">
        <div class="bg-blue-100 px-6 py-5 border-b border-blue-300">
            <h2 class="text-2xl font-bold text-blue-800">📁 Chi tiết danh mục bài viết</h2>
        </div>

        <div class="p-6 sm:p-8 overflow-x-auto text-base">
            <table class="w-full text-left table-auto border-collapse">
                <tbody class="text-gray-800">

                    <tr class="border-b">
                        <th class="py-3 px-6 bg-gray-50 font-semibold w-1/3">📝 Tên danh mục</th>
                        <td class="py-3 px-6">{{ $categories_post->name }}</td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-3 px-6 bg-gray-50 font-semibold">🔗 Slug</th>
                        <td class="py-3 px-6">{{ $categories_post->slug }}</td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-3 px-6 bg-gray-50 font-semibold">🧾 Mô tả</th>
                        <td class="py-3 px-6">
                            {{ $categories_post->description ?: 'Không có mô tả' }}
                        </td>
                    </tr>


                    <tr class="border-b">
                        <th class="py-3 px-6 bg-gray-50 font-semibold">🔝 Danh mục cha</th>
                        <td class="py-3 px-6">
                            @if ($categories_post->parent)
                                <a href="{{ route('admin.categories_post.show', $categories_post->parent->id) }}"
                                   class="text-blue-600 hover:underline">
                                    {{ $categories_post->parent->name }}
                                </a>
                            @else
                                <span class="text-gray-500 italic">Không có</span>
                            @endif
                        </td>
                    </tr>

                    <tr class="border-b align-top">
                        <th class="py-3 px-6 bg-gray-50 font-semibold">📂 Danh mục con</th>
                        <td class="py-3 px-6">
                            @if ($categories_post->children && $categories_post->children->isNotEmpty())
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($categories_post->children as $child)
                                        <li>
                                            <a href="{{ route('admin.categories_post.show', $child->id) }}"
                                               class="text-blue-600 hover:underline">
                                                {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500 italic">Không có danh mục con</span>
                            @endif
                        </td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-3 px-6 bg-gray-50 font-semibold">📅 Ngày tạo</th>
                        <td class="py-3 px-6">{{ $categories_post->created_at->format('d/m/Y H:i') }}</td>
                    </tr>

                    <tr>
                        <th class="py-3 px-6 bg-gray-50 font-semibold">🛠 Cập nhật lần cuối</th>
                        <td class="py-3 px-6">{{ $categories_post->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>

                </tbody>
            </table>

            <div class="mt-6 flex justify-between items-center">
                <a href="{{ route('admin.categories_post.index') }}"
                   class="inline-block px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-md shadow transition">
                    ← Quay lại danh sách
                </a>

                <a href="{{ route('admin.categories_post.edit', $categories_post->id) }}"
                   class="inline-block px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-md shadow transition">
                    ✏️ Sửa danh mục
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
