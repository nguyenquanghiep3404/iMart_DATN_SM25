<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use Illuminate\Support\Carbon;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Yêu cầu không hợp lệ.',
            ], 400);
        }

        $isGuest = !Auth::check();

        $rules = [
            'commentable_type' => 'required|string',
            'commentable_id'   => 'required|integer',
            'content'          => 'required|string|max:3000',
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'parent_id'        => 'nullable|exists:comments,id',
            
        ];
        
        if ($isGuest) {
            $rules = array_merge($rules, [
                'guest_name'  => 'required|string|max:255',
                'guest_email' => 'required|email:rfc,dns|max:255',
                'guest_phone' => [
                    'required',
                    'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-5]|9[0-9])[0-9]{7}$/',
                ],
                'gender'      => 'required|string|in:Anh,Chị',
            ]);
        }
        

        $messages = [
            'guest_name.required' => 'Vui lòng nhập họ và tên.',
            'guest_email.required' => 'Vui lòng nhập email.',
            'guest_email.email' => 'Email không hợp lệ.',
            'guest_phone.required' => 'Vui lòng nhập số điện thoại.',
            'guest_phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'content.required' => 'Vui lòng nhập nội dung bình luận.',
            'content.max' => 'Nội dung bình luận không được vượt quá 3000 ký tự.',
            'images.*.image' => 'Mỗi tệp phải là một hình ảnh.',
            'images.*.mimes' => 'Ảnh chỉ được có định dạng jpeg, png, jpg hoặc gif.',
            'images.*.max' => 'Kích thước ảnh không được vượt quá 2MB.',
        ];
    
        // ✅ Validate với messages tiếng Việt
        $validated = $request->validate($rules, $messages);

        // ✅ Kiểm tra trùng lặp bình luận trong 30 giây gần nhất
        $duplicateQuery = Comment::where('commentable_type', $validated['commentable_type'])
            ->where('commentable_id', $validated['commentable_id'])
            ->where('content', $validated['content'])
            ->where('created_at', '>=', Carbon::now()->subSeconds(30));

        if ($isGuest) {
            $duplicateQuery->where('guest_email', $validated['guest_email']);
        } else {
            $duplicateQuery->where('user_id', Auth::id());
        }

        $existingComment = $duplicateQuery->latest()->first();

        if ($existingComment) {
            $existingComment->load('user');

            $images = [];
            foreach ($existingComment->image_paths ?? [] as $path) {
                $images[] = asset('storage/' . $path);
            }

            return response()->json([
                'success' => true,
                // 'message' => 'Bình luận đã tồn tại.',
                'is_guest' => $isGuest,
                'comment' => [
                    'id'        => $existingComment->id,
                    'name'      => $isGuest ? $existingComment->guest_name : ($existingComment->user->name ?? 'Khách'),
                    'initial'   => strtoupper(mb_substr($isGuest ? $existingComment->guest_name : ($existingComment->user->name ?? 'K'), 0, 1)),
                    'content'   => $existingComment->content,
                    'time'      => $existingComment->created_at->diffForHumans(),
                    'images'    => $images,
                    'parent_id' => $existingComment->parent_id,
                    'status'    => $existingComment->status,
                    'is_owner'  => $isGuest ? true : (Auth::id() === $existingComment->user_id),
                    'is_admin'  => !$isGuest && $existingComment->user ? $existingComment->user->hasRole('admin') : false,
                ],
            ]);
        }

        // ✅ Xử lý ảnh
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('comments', 'public');
            }
        }

        // ✅ Tạo bình luận mới
        $comment = new Comment();
        $comment->commentable_type = $validated['commentable_type'];
        $comment->commentable_id   = $validated['commentable_id'];
        $comment->user_id          = Auth::id(); // null nếu guest
        $comment->parent_id        = $validated['parent_id'] ?? null;
        $comment->content          = $validated['content'];
        $comment->image_paths      = $imagePaths;

        if (!$isGuest && Auth::user()->hasRole('admin')) {
            $comment->status = 'approved';
        } else {
            $comment->status = 'pending';
        }

        if ($isGuest) {
            $comment->guest_name  = $validated['guest_name'];
            $comment->guest_email = $validated['guest_email'];
            $comment->guest_phone = $validated['guest_phone'];
            $comment->gender      = $validated['gender'];
        }

        $comment->save();
        $comment->load('user');

        $images = [];
        foreach ($imagePaths as $path) {
            $images[] = asset('storage/' . $path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bình luận đã được gửi.',
            'is_guest' => $isGuest,
            'comment' => [
                'id'        => $comment->id,
                'name'      => $isGuest ? $comment->guest_name : ($comment->user->name ?? 'Khách'),
                'initial'   => strtoupper(mb_substr($isGuest ? $comment->guest_name : ($comment->user->name ?? 'K'), 0, 1)),
                'content'   => $comment->content,
                'time'      => $comment->created_at->diffForHumans(),
                'images'    => $images,
                'parent_id' => $comment->parent_id,
                'status'    => $comment->status,
                'is_owner'  => $isGuest ? true : (Auth::id() === $comment->user_id),
                'is_admin'  => !$isGuest && $comment->user ? $comment->user->hasRole('admin') : false,
            ],
        ]);
    }

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
