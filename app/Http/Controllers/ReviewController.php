<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Hiển thị các sản phẩm chờ đánh giá
     */
    public function index()
    {
        $user = Auth::user();

        // Lấy review kèm sản phẩm của user, keyBy theo variant id
        $userReviews = Review::with('product')->where('user_id', $user->id)->get()->keyBy('product_variant_id');

        // Lấy order items và build danh sách
        $orderItems = $user->orders()
            ->with(['items.variant.product'])
            ->get()
            ->pluck('items')
            ->flatten()
            ->unique('product_variant_id');

        $itemsForReview = $orderItems->map(function ($item) use ($userReviews) {
            $variantId = $item->product_variant_id;

            return [
                'product'       => $item->variant->product,
                'variant_id'    => $variantId,
                'order_item_id' => $item->id,
                'review'        => $userReviews->get($variantId), // review hoặc null
            ];
        });

        return view('users.profile.reviews', compact('itemsForReview'));
    }
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'order_item_id'      => 'nullable|exists:order_items,id',
            'rating'             => 'required|integer|min:1|max:5',
            'title'              => 'nullable|string|max:255',
            'comment'            => 'required|string|min:5',
        ], [
            'product_variant_id.required' => 'Vui lòng chọn phiên bản sản phẩm.',
            'product_variant_id.exists' => 'Phiên bản sản phẩm không hợp lệ.',
            'order_item_id.exists' => 'Sản phẩm trong đơn hàng không hợp lệ.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Xếp hạng phải là số nguyên.',
            'rating.min' => 'Xếp hạng tối thiểu là 1 sao.',
            'rating.max' => 'Xếp hạng tối đa là 5 sao.',
            'title.max' => 'Tiêu đề không được dài quá 255 ký tự.',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá.',
            'comment.min' => 'Nội dung đánh giá phải có ít nhất 10 ký tự.',
        ]);

        $userId = Auth::id();
        $variantId = $validated['product_variant_id'];
        $hasReviewed = Review::where('user_id', $userId)
            ->where('product_variant_id', $variantId)
            ->exists();

        if ($hasReviewed) {
            return back()->withErrors(['Bạn đã đánh giá phiên bản sản phẩm này rồi.']);
        }

        // Gắn thêm thông tin và tạo review
        $validated['user_id'] = $userId;
        $validated['status'] = 'pending';
        $validated['is_verified_purchase'] = !empty($validated['order_item_id']);

        Review::create($validated);

        return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }
    public function show($id)
    {
        $review = Review::with([
            'variant.product.defaultVariant',
            'variant.attributeValues.attribute',
            'images'
        ])->findOrFail($id);

        abort_unless($review->user_id === Auth::id(), 403);

        $variant = $review->variant;
        $product = $variant->product;

        return view('users.profile.detail_reviews', compact('review', 'product', 'variant'));
    }
}
