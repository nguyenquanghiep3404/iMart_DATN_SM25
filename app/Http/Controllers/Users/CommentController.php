<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để bình luận.',
                ], 401);
            }
    
            return redirect()->route('login')->with('warning', 'Bạn cần đăng nhập để bình luận.');
        }
    
        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id'   => 'required|integer',
            'content'          => 'required|string|max:1000',
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id'        => 'nullable|exists:comments,id',
        ]);
    
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('comments', 'public');
            }
        }
    
        $comment = Comment::create([
            'commentable_type' => $validated['commentable_type'],
            'commentable_id'   => $validated['commentable_id'],
            'user_id'          => Auth::id(),
            'parent_id'        => $validated['parent_id'] ?? null,
            'content'          => $validated['content'],
            'image_paths'      => $imagePaths,
            'status'           => 'approved',
        ]);
    
        $comment->load('user');
    
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $comment->parent_id ? 'Phản hồi đã được gửi.' : 'Bình luận đã được gửi.',
                'comment' => [
                    'id'        => $comment->id,
                    'name'      => $comment->user->name ?? 'Khách',
                    'initial'   => strtoupper(substr($comment->user->name ?? 'K', 0, 1)),
                    'content'   => $comment->content,
                    'time'      => $comment->created_at->diffForHumans(),
                    'images'    => $comment->image_urls,
                    'parent_id' => $comment->parent_id,
                    'is_admin'  => (bool) $comment->user->is_admin,
                ],
            ]);
        }
    
        // Nếu không phải AJAX => redirect về lại trang hiện tại
        return redirect()->back()->with('success', 'Bình luận đã được gửi thành công.');
    }
    

    public function fetch(Request $request)
    {
        $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id'   => 'required|integer',
        ]);

        $comments = Comment::where('commentable_type', $request->commentable_type)
            ->where('commentable_id', $request->commentable_id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with(['user', 'repliesRecursive'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }
}
