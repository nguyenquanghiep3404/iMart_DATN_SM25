<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Tự động áp dụng ReviewPolicy
        $this->authorizeResource(Review::class, 'review');
    }
    public function index(Request $request)
    {
        $query = Review::query()->with(['user', 'variant.product']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%$keyword%"))
                ->orWhereHas('variant.product', fn($q) => $q->where('name', 'like', "%$keyword%"))
                ->orWhere('comment', 'like', "%$keyword%")
                ->orWhere('title', 'like', "%$keyword%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $reviews = $query->latest()->paginate(10);

        return view('admin.reviews.index', compact('reviews'));
    }



    public function show($id)
    {
        $review = Review::with(['user', 'variant.product', 'images'])->findOrFail($id);
        return view('admin.reviews.show', compact('review'));
    }

    public function updateStatus(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if (in_array($review->status, ['approved', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi trạng thái đã duyệt hoặc đã từ chối.'
            ]);
        }

        $review->status = $request->input('status');
        $review->save();

        return response()->json(['success' => true]);
    }
}
