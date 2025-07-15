<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;

class CommentController extends Controller
{
    // public function store(Request $request)
    // {
    //     if (!$request->ajax()) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Gửi bình luận thành công',
    //         ], 200);
    //     }

    //     if (!Auth::check()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Bạn cần đăng nhập để bình luận.',
    //         ], 401);
    //     }

    //     $validated = $request->validate([
    //         'commentable_type' => 'required|string',
    //         'commentable_id'   => 'required|integer',
    //         'content'          => 'required|string|max:1000',
    //         'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'parent_id'        => 'nullable|exists:comments,id',
    //     ]);

    //     $imagePaths = [];
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $image) {
    //             $imagePaths[] = $image->store('comments', 'public');
    //         }
    //     }

    //     $comment = Comment::create([
    //         'commentable_type' => $validated['commentable_type'],
    //         'commentable_id'   => $validated['commentable_id'],
    //         'user_id'          => Auth::id(),
    //         'parent_id'        => $validated['parent_id'] ?? null,
    //         'content'          => $validated['content'],
    //         'image_paths'      => $imagePaths,
    //         'status'           => 'pending',
    //     ]);

    //     $comment->load('user');

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Bình luận đã được gửi.',
    //         'comment' => [
    //         'id'        => $comment->id,
    //         'name'      => $comment->user->name ?? 'Khách',
    //         'initial'   => strtoupper(substr($comment->user->name ?? 'K', 0, 1)),
    //         'content'   => $comment->content,
    //         'time'      => $comment->created_at->diffForHumans(),
    //         'images'    => $comment->image_urls,
    //         'parent_id' => $comment->parent_id,
    //         'is_admin'  => $comment->user ? $comment->user->hasRole('admin') : false,
    //     ],
    //     ]);
    // }
    public function store(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gửi bình luận thành công',
            ], 200);
        }

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để bình luận.',
            ], 401);
        }

        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id'   => 'required|integer',
            'content'          => 'required|string|max:3000',
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
            'status'           => 'pending', // Mặc định chờ duyệt
        ]);

        $comment->load('user');

        // Lấy URL ảnh nếu chưa có accessor image_urls trong model
        $images = [];
        foreach ($imagePaths as $path) {
            $images[] = asset('storage/' . $path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bình luận đã được gửi.',
            'comment' => [
                'id'        => $comment->id,
                'name'      => $comment->user->name ?? 'Khách',
                'initial'   => strtoupper(substr($comment->user->name ?? 'K', 0, 1)),
                'content'   => $comment->content,
                'time'      => $comment->created_at->diffForHumans(),
                'images'    => $images,
                'parent_id' => $comment->parent_id,
                'status'    => $comment->status,
                'is_owner'  => Auth::id() === $comment->user_id,
                'is_admin'  => $comment->user ? $comment->user->hasRole('admin') : false,
            ],
        ]);
    }


    // public function fetch(Request $request)
    // {
    //     $request->validate([
    //         'commentable_type' => 'required|string',
    //         'commentable_id'   => 'required|integer',
    //     ]);

    //     $comments = Comment::where('commentable_type', $request->commentable_type)
    //         ->where('commentable_id', $request->commentable_id)
    //         ->whereNull('parent_id')
    //         ->where('status', 'approved')
    //         ->with(['user', 'repliesRecursive'])
    //         ->orderByDesc('created_at')
    //         ->get();

    //     return response()->json([
    //         'comments' => $comments,
    //     ]);
    // }
    public function fetch(Request $request)
    {
        $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id'   => 'required|integer',
        ]);

        $query = Comment::where('commentable_type', $request->commentable_type)
                        ->where('commentable_id', $request->commentable_id)
                        ->whereNull('parent_id')
                        ->with(['user', 'repliesRecursive']);

        if (Auth::check()) {
            $userId = Auth::id();

            // Lấy comment đã approved hoặc comment của chính user (dù chưa duyệt)
            $query->where(function ($q) use ($userId) {
                $q->where('status', 'approved')
                ->orWhere(function ($q2) use ($userId) {
                    $q2->where('user_id', $userId);
                });
            });
        } else {
            // Nếu chưa đăng nhập, chỉ lấy bình luận đã approved
            $query->where('status', 'approved');
        }

        $comments = $query->orderByDesc('created_at')->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }
}
