<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

        // Lấy tất cả review của user, key theo product_variant_id để tra cứu nhanh
        $userReviews = Review::where('user_id', $user->id)
            ->get()
            ->keyBy('product_variant_id');

        // Lấy các order items thuộc đơn đã giao/thành công
        $orderItems = $user->orders()
            ->whereIn('status', ['delivered', 'completed'])
            ->with(['items.variant.product'])
            ->get()
            ->pluck('items')
            ->flatten()
            ->filter(fn($item) => $item->variant && $item->variant->product) // loại bỏ item lỗi
            ->unique('product_variant_id')
            ->values();

        // Chuyển thành collection có thông tin cần thiết cho view
        $itemsForReview = $orderItems->map(function ($item) use ($userReviews) {
            $variantId = $item->product_variant_id;

            return [
                'product'       => $item->variant->product,
                'variant'       => $item->variant,
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
            'media'              => 'nullable|array|max:6',
            'media.*'            => 'file|mimes:jpg,jpeg,png,gif,mp4,webm,mov|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $data = $validator->validated();
        $userId = Auth::id();

        // Kiểm tra đã đánh giá chưa
        if (
            !empty($data['order_item_id']) && Review::where('user_id', $userId)
            ->where('order_item_id', $data['order_item_id'])
            ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá cho lần mua này rồi.'
            ], 409);
        }


        // Kiểm tra đã mua chưa
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

        // Tạo review
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

        // Xử lý media
        $mediaFiles = $request->file('media', []);
        $imageCount = collect($mediaFiles)->filter(fn($file) => str_starts_with($file->getMimeType(), 'image/'))->count();
        $videoCount = collect($mediaFiles)->filter(fn($file) => str_starts_with($file->getMimeType(), 'video/'))->count();

        if ($imageCount > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ được tải lên tối đa 5 ảnh.'
            ], 422);
        }

        if ($videoCount > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ được tải lên 1 video.'
            ], 422);
        }

        foreach ($mediaFiles as $index => $file) {
            $path = $file->store('review_media', 'public');

            $review->images()->create([
                'path'          => $path,
                'filename'      => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'disk'          => 'public',
                'type'          => str_starts_with($file->getMimeType(), 'image/') ? 'review_image' : 'review_video',
                'order'         => $index,
            ]);
        }

        // Gửi notify nếu cần
        if ($review->status === 'pending') {
            $productName = optional(ProductVariant::with('product')->find($data['product_variant_id'])->product)->name;

            $admins = User::whereHas(
                'roles',
                fn($q) =>
                $q->whereIn('name', ['admin', 'content_manager'])
            )->get();

            foreach ($admins as $admin) {
                $admin->notify(new NewReviewOrCommentPending(
                    'đánh giá',
                    $productName ?? 'sản phẩm',
                    route('admin.reviews.index')
                ));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã đánh giá!',
            'review_id' => $review->id,
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
