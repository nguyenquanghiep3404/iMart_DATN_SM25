<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'variant.product']);

        if ($keyword = $request->input('keyword')) {
            $query->whereHas('variant.product', function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            });
        }

        $reviews = $query->latest()->paginate(10);

        return view('admin.reviews.index', compact('reviews'));
    }


    public function show($id)
    {
        $review = Review::with(['user', 'variant.product', 'images'])->findOrFail($id);
        return view('admin.reviews.show', compact('review'));
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $review->status = $request->input('status', 'approved');
        $review->save();

        return redirect()->route('admin.reviews.index')->with('success', 'Đánh giá đã được cập nhật.');
    }

    public function destroy($id)
    {
        Review::destroy($id);
        return redirect()->route('admin.reviews.index')->with('success', 'Đánh giá đã bị xoá.');
    }
}
