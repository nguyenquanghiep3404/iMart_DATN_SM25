<div class="my-6">
    <form id="comment-form" action="{{ route('comments.store') }}" method="POST"
        data-commentable-type="App\\Models\\Product" data-commentable-id="{{ $product->id }}"
        enctype="multipart/form-data" class="relative">
        @csrf
        <input type="hidden" name="commentable_type" value="App\Models\Product">
        <input type="hidden" name="commentable_id" value="{{ $product->id }}">
        <input type="file" name="images[]" id="comment-image" accept="image/*" multiple
            class="mt-1 mb-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
            file:rounded-lg file:border-0
            file:text-sm file:font-semibold
            file:bg-gray-100 file:text-gray-700
            hover:file:bg-gray-200" />
        <div class="flex items-center gap-2">
            <textarea id="comment-textarea" name="content" maxlength="3000" placeholder="Nhập nội dung bình luận..."
                class="w-full px-4 py-3 pr-24 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>
            <span id="char-counter" class="absolute right-32 bottom-3 text-sm text-gray-400">0/3000</span>
            <button type="submit" id="comment-submit-btn"
                class="absolute right-2 bottom-1.5 bg-gray-800 text-white font-semibold py-2 px-5 rounded-lg hover:bg-gray-900 transition-colors">
                Gửi bình luận
            </button>
        </div>
    </form>
</div>

@forelse ($comments as $comment)
    <div class="border-b border-gray-200 py-4">
        <div class="flex items-start gap-3">
            {{-- Avatar --}}
            <img src="{{ $comment->user->avatar ?? 'https://placehold.co/40x40/7e22ce/ffffff?text=' . strtoupper(substr($comment->user->name ?? 'N', 0, 1)) }}"
                alt="Avatar" class="w-10 h-10 rounded-full object-cover">

            <div>
                {{-- Tên người dùng --}}
                <p class="font-semibold text-gray-800">{{ $comment->user->name ?? 'Khách' }}</p>

                {{-- Nội dung --}}
                <p class="text-sm text-gray-600">{{ $comment->content }}</p>

                {{-- Hình ảnh --}}
                @if ($comment->image_urls)
                    <div class="flex gap-2 mt-2">
                        @foreach ($comment->image_urls as $url)
                            <img src="{{ $url }}" alt="Review Image" class="w-20 h-20 rounded-md object-cover">
                        @endforeach
                    </div>
                @endif

                {{-- Thời gian và nút Trả lời --}}
                <div class="text-xs text-gray-500 mt-2 flex items-center gap-4">
                    <span>{{ $comment->created_at->diffForHumans() }}</span>

                    @auth
                        @if ($comment->user_id !== auth()->id())
                            <button onclick="toggleReplyForm({{ $comment->id }})"
                                class="reply-btn text-sm text-blue-600 hover:underline">Trả lời</button>
                        @endif
                    @endauth
                </div>

                {{-- ==== Form trả lời bình luận cha ==== --}}
                <div id="reply-form-{{ $comment->id }}" class="mt-3 hidden">
                    <form class="reply-form" action="{{ route('comments.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="commentable_type" value="{{ get_class($product) }}">
                        <input type="hidden" name="commentable_id" value="{{ $product->id }}">
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">

                        <textarea name="content" rows="2" class="w-full text-sm border rounded p-2 mt-1"
                            placeholder="Trả lời bình luận này..."></textarea>

                        <div class="mt-2 flex justify-between items-center">
                            <input type="file" name="images[]" multiple class="text-xs">
                            <button type="submit"
                                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                Gửi phản hồi
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ==== Danh sách phản hồi ==== --}}
                @if ($comment->replies->count())
                    <div class="mt-4 border-l border-gray-200 pl-4">
                        @foreach ($comment->replies as $reply)
                            <div class="flex items-start gap-3 mb-3">
                                <img src="{{ $reply->user->avatar ?? 'https://placehold.co/32x32/7e22ce/ffffff?text=' . strtoupper(substr($reply->user->name ?? 'K', 0, 1)) }}"
                                    alt="Avatar" class="w-8 h-8 rounded-full object-cover">

                                <div>
                                    <p class="font-semibold text-sm">{{ $reply->user->name ?? 'Khách' }}
                                    </p>
                                    <p class="text-sm text-gray-700">{{ $reply->content }}</p>

                                    <div class="text-xs text-gray-500 mt-1 flex items-center gap-4">
                                        <span>{{ $reply->created_at->diffForHumans() }}</span>

                                        @auth
                                            @if ($reply->user_id !== auth()->id())
                                                <button onclick="toggleReplyForm({{ $reply->id }})"
                                                    class="reply-btn text-sm text-blue-600 hover:underline">Trả
                                                    lời</button>
                                            @endif
                                        @endauth
                                    </div>

                                    {{-- ==== Form trả lời phản hồi ==== --}}
                                    <div id="reply-form-{{ $reply->id }}" class="mt-2 hidden">
                                        <form action="{{ route('comments.store') }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="commentable_type"
                                                value="{{ get_class($product) }}">
                                            <input type="hidden" name="commentable_id" value="{{ $product->id }}">
                                            <input type="hidden" name="parent_id" value="{{ $reply->id }}">

                                            <textarea name="content" rows="2" class="w-full text-sm border rounded p-2 mt-1"
                                                placeholder="Trả lời bình luận này..."></textarea>

                                            <div class="mt-2 flex justify-between items-center">
                                                <input type="file" name="images[]" multiple class="text-xs">
                                                <button type="submit"
                                                    class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                                    Gửi phản hồi
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <p>Chưa có bình luận nào.</p>
@endforelse
