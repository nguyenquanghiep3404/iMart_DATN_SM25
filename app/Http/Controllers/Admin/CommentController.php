<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
class CommentController extends Controller
{
      public function index(Request $request) // Truyền Request $request vào phương thức
    {
        // 1. Lấy giá trị 'status' từ request
        // Nếu không có tham số 'status' trên URL, mặc định là 'all'
        $filterStatus = $request->input('status', 'all');

        // 2. Bắt đầu truy vấn Comment
        // Sử dụng eager loading cho các mối quan hệ để tránh N+1 query problem
        $comments = Comment::with(['user', 'commentable']);

        // 3. Áp dụng bộ lọc dựa trên trạng thái
        // Chỉ áp dụng điều kiện WHERE nếu filterStatus KHÔNG phải là 'all'
        if ($filterStatus !== 'all') {
            $comments->where('status', $filterStatus);
        }

        // 4. Sắp xếp và phân trang
        $comments = $comments->latest()->paginate(10); 

        // 5. Truyền dữ liệu sang view
        // Truyền cả $filterStatus để giữ lại lựa chọn trên dropdown trong view
        return view('admin.comments.index', compact('comments', 'filterStatus'));
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
