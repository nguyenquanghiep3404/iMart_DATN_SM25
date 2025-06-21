@extends('admin.comments.layouts.main')

@section('content')
<div class="max-w-5xl mx-auto mt-10">
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
        <div class="bg-blue-100 px-6 py-5 border-b border-blue-300">
            <h2 class="text-3xl font-bold text-blue-800">📝 Chi tiết bình luận</h2>
        </div>

        <div class="p-8 overflow-x-auto text-lg">
            <table class="w-full text-left table-auto border-collapse">
                <tbody class="text-gray-800">

                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50 w-1/3">👤 Người bình luận</th>
                        <td class="py-4 px-6">{{ $comment->user->name ?? 'Ẩn danh' }}</td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50">📧 Email</th>
                        <td class="py-4 px-6">{{ $comment->user->email ?? 'N/A' }}</td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50">🔁 Bình luận cha</th>
                        <td class="py-4 px-6">
                            @if ($comment->parent_id && $comment->parent)
                                <div class="italic text-gray-800">"{{ $comment->parent->content }}"</div>
                            @else
                                <span class="text-gray-500 italic">Không có</span>
                            @endif
                        </td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50">📦 Loại nội dung</th>
                        <td class="py-4 px-6">{{ class_basename($comment->commentable_type) }}</td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50">🔗 Đối tượng</th>
                        <td class="py-4 px-6">
                            @if ($comment->commentable)
                                @php
                                    $commentable = $comment->commentable;
                                    $type = strtolower(class_basename($comment->commentable_type)); // "product" hoặc "post"
                                    $title = $commentable->title ?? $commentable->name ?? 'khong-ro';
                                    $slug = $commentable->slug ?? \Illuminate\Support\Str::slug($title);
                                @endphp
                    
                                @if ($type === 'product')
                                    <a href="{{ route('users.products.show', ['slug' => $slug]) }}"
                                       class="text-blue-600 font-medium hover:underline" target="_blank">
                                        {{ $title }}
                                    </a>
                                @elseif ($type === 'post')
                                    {{-- <a href="{{ route('posts.show', ['slug' => $slug]) }}"
                                       class="text-blue-600 font-medium hover:underline" target="_blank">
                                        {{ $title }}
                                    </a> --}}
                                @else
                                    <span class="text-red-500 font-semibold">Không xác định loại</span>
                                @endif
                            @else
                                <span class="text-red-500 font-semibold">Không xác định</span>
                            @endif
                        </td>
                    </tr>
                    
                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50">⏱ Ngày bình luận</th>
                        <td class="py-4 px-6">{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50">♻️ Cập nhật lúc</th>
                        <td class="py-4 px-6">{{ $comment->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>

                    <tr class="border-b">
                        <th class="py-4 px-6 font-semibold bg-gray-50">📌 Trạng thái</th>
                        <td class="py-4 px-6">
                            <span class="inline-block px-3 py-1 rounded-full text-white 
                                {{ $comment->status === 'pending' ? 'bg-yellow-500' : ($comment->status === 'spam' ? 'bg-red-500' : 'bg-green-600') }}">
                                {{ ucfirst($comment->status) }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th class="py-4 px-6 font-semibold bg-gray-50 align-top">💬 Nội dung</th>
                        <td class="py-4 px-6 leading-relaxed bg-gray-50">
                            {{ $comment->content }}
                        </td>
                    </tr>

                </tbody>
            </table>

            <div class="mt-8">
                <a href="{{ route('admin.comment.index') }}"
                   class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition">
                    ← Quay lại danh sách
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
