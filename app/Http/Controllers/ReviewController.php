<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewReviewOrCommentPending;
use App\Models\User;
use App\Models\ProductVariant;

class ReviewController extends Controller
{
    /**
     * Hiển thị các sản phẩm chờ đánh giá
     */
    public function index()
    {
        $user = Auth::user();

        $userReviews = Review::with('product')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('product_variant_id');

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
                'review'        => $userReviews->get($variantId),
            ];
        });

        return view('users.profile.reviews', compact('itemsForReview'));
    }

    /**
     * Lưu đánh giá mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_variant_id' => 'required|exists:product_variants,id',
            'order_item_id'      => 'nullable|exists:order_items,id',
            'rating'             => 'required|integer|min:1|max:5',
            'title'              => 'nullable|string|max:255',
            'comment'            => 'required|string|min:5',
            'media'              => 'nullable|array|max:3',
            'media.*'            => 'file|mimes:jpg,jpeg,png,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $data = $validator->validated();
        $userId = Auth::id();

        // Check đã đánh giá chưa
        if (Review::where('user_id', $userId)->where('product_variant_id', $data['product_variant_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá phiên bản sản phẩm này rồi.'
            ], 409);
        }

        // Check đã mua chưa (bắt buộc)
        $hasOrdered = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_variant_id', $data['product_variant_id'])
            ->exists();

        if (!$hasOrdered) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể đánh giá sản phẩm đã mua.'
            ], 403);
        }

        // Gán dữ liệu đánh giá
        $review = Review::create([
            'product_variant_id' => $data['product_variant_id'],
            'order_item_id'      => $data['order_item_id'] ?? null,
            'user_id'            => $userId,
            'rating'             => $data['rating'],
            'title'              => $data['title'] ?? null,
            'comment'            => $data['comment'],
            'status'             => 'pending',
            'is_verified_purchase' => !empty($data['order_item_id']),
        ]);

        // Lưu ảnh nếu có
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $index => $file) {
                $path = $file->store('review_images', 'public');

                $review->images()->create([
                    'path'          => $path,
                    'filename'      => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                    'disk'          => 'public',
                    'type'          => 'review_image',
                    'order'         => $index,
                ]);
            }
        }

        $productVariant = ProductVariant::with('product')->find($data['product_variant_id']);
        $product = $productVariant->product ?? null;


        if ($review->status === 'pending') {
            $productVariant = ProductVariant::with('product')->find($data['product_variant_id']);
            $product = $productVariant->product ?? null;

            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'content_manager']);
            })->get();

            foreach ($admins as $admin) {
                $admin->notify(new NewReviewOrCommentPending(
                    'đánh giá',
                    $product?->name ?? 'sản phẩm',
                    route('admin.reviews.index')
                ));
            }
        }


        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã đánh giá!'
        ]);
    }

    /**
     * Xem chi tiết đánh giá
     */
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
