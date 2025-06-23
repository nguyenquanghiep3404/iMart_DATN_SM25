<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
class CommentController extends Controller
{
    public function index(Request $request)
    {
        $filterStatus = $request->input('status', 'all');
        $search = $request->input('search');
        $filterDate = $request->input('date');
    
        $parentCommentsQuery = Comment::with(['user', 'commentable', 'replies.user'])
            ->whereNull('parent_id');
    
        // Lọc theo status
        if ($filterStatus !== 'all') {
            $parentCommentsQuery->where('status', $filterStatus);
        }
    
        // Lọc theo từ khóa tìm kiếm
        if ($search) {
            $parentCommentsQuery->where(function ($query) use ($search) {
                $query->where('content', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            });
        }
    
        // Lọc theo ngày gửi
        if ($filterDate) {
            $parentCommentsQuery->whereDate('created_at', $filterDate);
        }
    
        // Lấy các bình luận cha
        $parentComments = $parentCommentsQuery->latest()->paginate(10)->withQueryString();
    
        // Gộp cha + reply
        $comments = collect();
    
        foreach ($parentComments as $parent) {
            // Apply filter cho từng cha
            $comments->push($parent);
    
            $replies = $parent->replies->sortBy('created_at');
    
            foreach ($replies as $reply) {
                // Lọc status
                if ($filterStatus !== 'all' && $reply->status !== $filterStatus) {
                    continue;
                }
    
                // Lọc search
                if ($search) {
                    $matchContent = str_contains(strtolower($reply->content), strtolower($search));
                    $matchUser = str_contains(strtolower(optional($reply->user)->name), strtolower($search));
                    if (!$matchContent && !$matchUser) {
                        continue;
                    }
                }
    
                // Lọc ngày gửi
                if ($filterDate && $reply->created_at->toDateString() !== $filterDate) {
                    continue;
                }
    
                $comments->push($reply);
            }
        }
    
        return view('admin.comments.index', [
            'comments' => $comments,
            'filterStatus' => $filterStatus,
            'parentComments' => $parentComments, // dùng cho phân trang
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
