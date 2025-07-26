<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Comment;
class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::withCount('comments')
            ->has('comments'); 
        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Sắp xếp theo số lượng bình luận
        if ($sort = $request->input('sort')) {
            if ($sort === 'most_commented') {
                $query->orderByDesc('comments_count');
            } elseif ($sort === 'least_commented') {
                $query->orderBy('comments_count', 'asc');
            }
        } else {
            // Mặc định: sắp xếp theo mới nhất (nếu cần)
            $query->latest();
        }

        $products = $query->paginate(10)->appends($request->query());

        return view('admin.comments.index', [
            'products' => $products,
        ]);
    }

    public function byProduct(Product $product, Request $request)
    {
        $commentsQuery = $product->comments()
            ->whereNull('parent_id')   // chỉ lấy bình luận cha
            ->with(['user', 'replies.user']);  // load user của comment và replies
    
        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $commentsQuery->where('status', $request->status);
        }
    
        // Tìm kiếm nội dung hoặc tên người bình luận
        if ($request->filled('search')) {
            $search = $request->search;
            $commentsQuery->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }
    
        // Lọc theo ngày gửi
        if ($request->filled('date')) {
            $commentsQuery->whereDate('created_at', $request->date);
        }
    
        // Sắp xếp và phân trang
        $comments = $commentsQuery->orderByDesc('created_at')->paginate(15);
    
        return view('admin.comments.comment-by-product', compact('product', 'comments'));
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

        // Kiểm tra xem bình luận có thuộc về sản phẩm không
        if ($comment->commentable_type !== \App\Models\Product::class) {
            return redirect()->back()->with('error', 'Bình luận này không thuộc về sản phẩm.');
        }

        $productId = $comment->commentable_id;

        return redirect()
            ->route('admin.comments.byProduct', ['product' => $productId])
            ->with('success', 'Trạng thái bình luận đã được cập nhật.');
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
