@foreach ($comments->whereNull('parent_id') as $comment)
    <div class="border-bottom py-3 mb-3">
        {{-- Thông tin người bình luận và thời gian --}}
        <div class="d-flex align-items-center mb-3">
            <div class="text-nowrap me-3">
                <span class="h6 mb-0">{{ $comment->user->name ?? 'Ẩn danh' }}</span>
                <i class="ci-check-circle text-success align-middle ms-1"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   data-bs-custom-class="tooltip-sm"
                   data-bs-title="Verified customer"></i>
            </div>
            <span class="text-body-secondary fs-sm ms-auto">{{ $comment->created_at->format('F d, Y') }}</span>
        </div>

        {{-- Đánh giá sao --}}
        <div class="d-flex gap-1 fs-sm pb-2 mb-1">
            @for ($i = 1; $i <= 5; $i++)
                <i class="ci-star{{ $i <= ($comment->rating ?? 0) ? '-filled text-warning' : '' }}"></i>
            @endfor
        </div>

        {{-- Màu và dung lượng --}}
        <ul class="list-inline gap-2 pb-2 mb-1">
            @if (!empty($comment->color))
                <li class="fs-sm me-4">
                    <span class="text-dark-emphasis fw-medium">Color:</span> {{ $comment->color }}
                </li>
            @endif
            @if (!empty($comment->storage))
                <li class="fs-sm">
                    <span class="text-dark-emphasis fw-medium">Model:</span> {{ $comment->storage }}
                </li>
            @endif
        </ul>

        {{-- Nội dung bình luận --}}
        <p class="fs-sm">{{ $comment->content }}</p>

        {{-- Ưu & Nhược điểm --}}
        <ul class="list-unstyled fs-sm pb-2 mb-1">
            @if (!empty($comment->pros))
                <li><span class="text-dark-emphasis fw-medium">Pros:</span> {{ $comment->pros }}</li>
            @endif
            @if (!empty($comment->cons))
                <li><span class="text-dark-emphasis fw-medium">Cons:</span> {{ $comment->cons }}</li>
            @endif
        </ul>

        {{-- Nút hành động --}}
        <div class="nav align-items-center">
            <button type="button" class="nav-link animate-underline px-0 reply-toggle-btn" data-target="#reply-form-{{ $comment->id }}">
                <i class="ci-corner-down-right fs-base ms-1 me-1"></i>
                <span class="animate-target">Reply</span>
            </button>
            <button type="button" class="nav-link text-body-secondary animate-scale px-0 ms-auto me-n1">
                <i class="ci-thumbs-up fs-base animate-target me-1"></i>
                {{ $comment->likes ?? 0 }}
            </button>
            <hr class="vr my-2 mx-3">
            <button type="button" class="nav-link text-body-secondary animate-scale px-0 ms-n1">
                <i class="ci-thumbs-down fs-base animate-target me-1"></i>
                {{ $comment->dislikes ?? 0 }}
            </button>
        </div>

        {{-- Form trả lời --}}
        <div id="reply-form-{{ $comment->id }}" class="reply-form mt-2" style="display: none;">
            <form action="{{ route('admin.replies.store') }}" method="POST">
                @csrf
                <input type="hidden" name="comment_id" value="{{ $comment->id }}">
                <div class="mb-2">
                    <textarea name="content" class="form-control" rows="2" placeholder="Nhập câu trả lời của admin..."></textarea>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Gửi trả lời</button>
            </form>
        </div>

        {{-- Hiển thị trả lời --}}
        @if ($comment->replies->count())
            <div class="ms-4 border-start ps-3 mt-3">
                @foreach ($comment->replies as $reply)
                    <div class="mb-2">
                        <strong class="text-primary">{{ $reply->user->name ?? 'Admin' }}:</strong> {{ $reply->content }}
                        <small class="text-muted ms-2">{{ $reply->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endforeach

{{-- JS toggle form trả lời --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.reply-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const target = btn.getAttribute('data-target');
                const form = document.querySelector(target);
                if (form) {
                    form.style.display = form.style.display === 'none' ? 'block' : 'none';
                }
            });
        });
    });
</script>
