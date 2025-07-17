@extends('admin.comments.layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto mt-10">
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            <div class="bg-blue-100 px-6 py-5 border-b border-blue-300">
                <h2 class="text-3xl font-bold text-blue-800">📝 Chi tiết bình luận</h2>
            </div>

            <div class="p-8 overflow-x-auto text-base text-gray-800">
                <table class="w-full table-auto border-collapse text-left">
                    <tbody>

                        {{-- Thông tin người bình luận --}}
                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 w-1/3 font-semibold">👤 Người bình luận</th>
                            <td class="py-4 px-6">
                                @if ($comment->user)
                                    {{ $comment->user->name }}
                                @else
                                    {{ $comment->guest_name ?? 'Ẩn danh' }}
                                    <span class="ml-2 px-2 py-0.5 text-xs text-gray-600 bg-gray-200 rounded-full">
                                        (Khách vãng lai)
                                    </span>
                                @endif
                            </td>
                        </tr>


                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">📧 Email</th>
                            <td class="py-4 px-6">
                                @if ($comment->user)
                                    {{ $comment->user->email }}
                                @else
                                    {{ $comment->guest_email ?? 'N/A' }}
                                @endif
                            </td>
                        </tr>

                        @if (!$comment->user)
                            <tr class="border-b">
                                <th class="py-4 px-6 bg-gray-50 font-semibold">📞 Số điện thoại</th>
                                <td class="py-4 px-6">{{ $comment->guest_phone ?? 'N/A' }}</td>
                            </tr>
                        @endif

                        {{-- Thông tin liên quan --}}
                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">🔁 Bình luận cha</th>
                            <td class="py-4 px-6">
                                @if ($comment->parent)
                                    <div class="italic text-gray-800">"{{ $comment->parent->content }}"</div>
                                @else
                                    <span class="text-gray-500 italic">Không có</span>
                                @endif
                            </td>
                        </tr>

                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">📦 Loại nội dung</th>
                            <td class="py-4 px-6">{{ class_basename($comment->commentable_type) }}</td>
                        </tr>

                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">🔗 Đối tượng</th>
                            <td class="py-4 px-6">
                                @php
                                    $commentable = $comment->commentable;
                                    $type = strtolower(class_basename($comment->commentable_type));
                                    $title = $commentable->title ?? ($commentable->name ?? 'Không rõ');
                                    $slug = $commentable->slug ?? \Illuminate\Support\Str::slug($title);
                                @endphp

                                @if ($type === 'product')
                                    <a href="{{ route('users.products.show', ['slug' => $slug]) }}"
                                        class="text-blue-600 font-medium hover:underline" target="_blank">
                                        {{ $title }}
                                    </a>
                                @else
                                    <span class="text-red-500">Không xác định</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Thời gian và trạng thái --}}
                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">⏱ Ngày bình luận</th>
                            <td class="py-4 px-6">{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>

                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">♻️ Cập nhật lúc</th>
                            <td class="py-4 px-6">{{ $comment->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>

                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">📌 Trạng thái</th>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-white 
                                    {{ match ($comment->status) {
                                        'pending' => 'bg-yellow-500',
                                        'spam' => 'bg-red-500',
                                        'rejected' => 'bg-gray-600',
                                        default => 'bg-green-600',
                                    } }}">
                                    {{ ucfirst($comment->status) }}
                                </span>
                            </td>
                        </tr>

                        {{-- Nội dung và ảnh --}}
                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 align-top font-semibold">💬 Nội dung</th>
                            <td class="py-4 px-6 leading-relaxed">{{ $comment->content }}</td>
                        </tr>

                        <tr class="border-b">
                            <th class="py-4 px-6 bg-gray-50 font-semibold">🖼 Ảnh đính kèm</th>
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

                {{-- Nút quay lại --}}
                <div class="mt-8">
                    <button type="button" onclick="window.history.back()"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition inline-flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Quay lại</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
