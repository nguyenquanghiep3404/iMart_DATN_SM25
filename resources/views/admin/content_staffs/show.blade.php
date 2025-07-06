@extends('admin.layouts.app')

@section('title', 'Chi tiết nhân viên Content')

@section('content')
    <div class="max-w-screen-xl mx-auto p-4 md:p-8">
        <header class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Thông tin nhân viên Content</h1>
        </header>

        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
    <h2 class="text-xl font-bold mb-4 text-indigo-700">Thông tin nhân viên Content</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-700">
        {{-- Cột trái: Thông tin cá nhân --}}
        <div>
            <table class="w-full">
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="py-2 font-medium w-1/3">Họ tên</td>
                        <td class="py-2">{{ $contentStaff->name }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium">Email</td>
                        <td class="py-2">{{ $contentStaff->email }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium">Số điện thoại</td>
                        <td class="py-2">{{ $contentStaff->phone_number }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium">Trạng thái</td>
                        <td class="py-2">
                            <span class="inline-block px-2 py-1 rounded text-sm font-semibold
                                @if ($contentStaff->status === 'active') bg-green-100 text-green-800
                                @elseif ($contentStaff->status === 'inactive') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($contentStaff->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium">Ngày tham gia</td>
                        <td class="py-2">{{ $contentStaff->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium">Cập nhật gần nhất</td>
                        <td class="py-2">{{ $contentStaff->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Cột phải: Thống kê nội dung --}}
        <div>
            <table class="w-full">
                <tbody class="divide-y divide-gray-200">
                    <tr>
                        <td class="py-2 font-medium w-1/3">Số bài viết</td>
                        <td class="py-2">{{ $postsCount }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-medium">Tổng lượt xem</td>
                        <td class="py-2">{{ number_format($viewsCount) }}</td>
                    </tr>
                    {{-- <tr>
                        <td class="py-2 font-medium">Trung bình lượt xem</td>
                        <td class="py-2">{{ number_format($averageViews) }}</td>
                    </tr> --}}
                </tbody>
            </table>
        </div>
    </div>
</div>


        <div class="bg-white p-6 rounded-xl shadow-sm">
    <h2 class="text-xl font-bold mb-4 text-indigo-700">Bài viết đã tạo</h2>

    @if ($posts->count())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm font-semibold text-gray-700">
                        <th class="px-4 py-3">Ảnh</th>
                        <th class="px-4 py-3">Tiêu đề</th>
                        <th class="px-4 py-3">Danh mục</th>
                        <th class="px-4 py-3">Lượt xem</th>
                        <th class="px-4 py-3">Ngày tạo</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach ($posts as $post)
                        <tr>
                            <td class="px-4 py-3">
                                <img src="{{ $post->coverImage ? Storage::url($post->coverImage->path) : asset('images/default.png') }}"
                                    alt="Ảnh bài viết"
                                    class="w-24 h-16 rounded border object-contain bg-gray-100">
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <a href="{{ route('admin.posts.edit', $post->id) }}" class="hover:underline">
                                    {{ $post->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $post->category->name ?? 'Không có' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ number_format($post->view_count) }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $post->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs rounded font-semibold
                                    @if ($post->status === 'published') bg-green-100 text-green-800
                                    @elseif($post->status === 'draft') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.posts.preview', $post->id) }}"
                                    target="_blank"
                                    class="inline-flex items-center justify-center px-3 py-1 text-sm rounded bg-blue-50 hover:bg-blue-100 text-blue-600 transition"
                                    title="Xem bài viết">
                                    <i class="fas fa-eye mr-1"></i> Xem
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    @else
        <p class="text-gray-500">Chưa có bài viết nào được tạo bởi nhân viên này.</p>
    @endif
</div>


        <div class="mt-6 flex justify-end">
            <a href="{{ route('admin.content-staffs.index') }}"
                class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Quay lại</a>
        </div>
    </div>
@endsection
