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
                                        $title = $commentable->title ?? ($commentable->name ?? 'khong-ro');
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
                                <form action="{{ route('admin.comments.updateStatus', $comment) }}" method="POST"
                                    class="flex items-center space-x-3">
                                    @csrf
                                    {{-- @method('PATCH') --}}

                                    <select name="status"
                                        class="border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @php
                                            $statuses = [
                                                'pending' => 'Chờ duyệt',
                                                'approved' => 'Đã duyệt',
                                                'rejected' => 'Từ chối',
                                                'spam' => 'Spam',
                                            ];
                                        @endphp

                                        @foreach ($statuses as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ $comment->status === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">
                                        Lưu
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <th class="py-4 px-6 font-semibold bg-gray-50 align-top">💬 Nội dung</th>
                            <td class="py-4 px-6 leading-relaxed bg-gray-50">
                                {{ $comment->content }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <th class="py-4 px-6 font-semibold bg-gray-50">🖼 Ảnh đính kèm</th>
                            <td class="py-4 px-6">
                                @if (!empty($comment->image_urls))
                                    <div class="flex flex-wrap gap-4">
                                        @foreach ($comment->image_urls as $image)
                                            <a href="{{ $image }}" target="_blank"
                                                class="block w-24 h-24 rounded overflow-hidden shadow">
                                                <img src="{{ $image }}" alt="Ảnh đính kèm"
                                                    class="w-full h-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-500 italic">Không có</span>
                                @endif
                            </td>
                        </tr>

                    </tbody>
                </table>

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
            </div>
        </div>
    </div>
@endsection
