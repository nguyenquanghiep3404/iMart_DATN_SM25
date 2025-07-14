<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
class CommentController extends Controller
{
    public function index(Request $request)
    {
        // Lấy các tham số lọc từ request
        $filterStatus = $request->input('status');
        $search = $request->input('search');
        $filterDate = $request->input('date');

        // Tạo query lấy bình luận cha (parent_id null), kèm quan hệ cần thiết
        $parentCommentsQuery = Comment::with(['user', 'commentable', 'replies.user'])
            ->whereNull('parent_id');

        // Lọc theo status nếu có và không phải 'all' hoặc rỗng
        if ($filterStatus && $filterStatus !== 'all') {
            $parentCommentsQuery->where('status', $filterStatus);
        }

        // Lọc theo từ khóa tìm kiếm trên content hoặc tên user
        if ($search) {
            $parentCommentsQuery->where(function ($query) use ($search) {
                $query->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Lọc theo ngày gửi bình luận
        if ($filterDate) {
            $parentCommentsQuery->whereDate('created_at', $filterDate);
        }

        // Lấy bình luận cha theo thứ tự mới nhất, phân trang 10 bản ghi, giữ query string
        $parentComments = $parentCommentsQuery->latest()->paginate(10)->withQueryString();

        // Tạo collection tổng hợp bình luận cha + reply thỏa mãn lọc
        $comments = collect();

        foreach ($parentComments as $parent) {
            $comments->push($parent);

            // Lấy các reply đã load sẵn
            $replies = $parent->replies->sortBy('created_at');

            foreach ($replies as $reply) {
                // Lọc status reply
                if ($filterStatus && $filterStatus !== 'all' && $reply->status !== $filterStatus) {
                    continue;
                }

                // Lọc search reply
                if ($search) {
                    $matchContent = str_contains(strtolower($reply->content), strtolower($search));
                    $matchUser = str_contains(strtolower(optional($reply->user)->name), strtolower($search));
                    if (!$matchContent && !$matchUser) {
                        continue;
                    }
                }

                // Lọc ngày gửi reply
                if ($filterDate && $reply->created_at->toDateString() !== $filterDate) {
                    continue;
                }

                $comments->push($reply);
            }
        }

        // Trả về view kèm dữ liệu
        return view('admin.comments.index', [
            'comments' => $comments,
            'filterStatus' => $filterStatus ?? 'all',
            'parentComments' => $parentComments, // dùng để phân trang
            'search' => $search,
            'filterDate' => $filterDate,
        ]);
    }

    public function show(Comment $comment)
    {
        $commentable = $comment->commentable; 

        return view('admin.comments.show', [
            'comment' => $comment,
            'commentable' => $commentable,
        ]);
    }
    public function edit(Comment $comment)
    {
        $commentable = $comment->commentable;

        return view('admin.comments.edit', [
            'comment' => $comment,
            'commentable' => $commentable,
        ]);
    }
    public function updateStatus(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,spam'],
        ]);

        $comment->status = $validated['status'];
        $comment->save();

        return redirect()->back()->with('success', 'Trạng thái bình luận đã được cập nhật.');
    }
    public function replyStore(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:comments,id',
            'content' => 'required|string|max:1000',
        ]);

        $parentComment = Comment::findOrFail($request->comment_id);

        Comment::create([
            'commentable_id' => $parentComment->commentable_id,
            'commentable_type' => $parentComment->commentable_type,
            'user_id' => auth()->id(), // hoặc admin_id nếu dùng bảng admin
            'parent_id' => $parentComment->id,
            'content' => $request->content,
            'status' => 'approved', // hoặc 'pending' nếu bạn cần duyệt
        ]);

        return back()->with('success', 'Phản hồi bình luận thành công!');
    }

}
