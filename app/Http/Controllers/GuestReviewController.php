<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GuestReviewController extends Controller
{
    public function verifyOrder(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
            'contact' => 'required|string'
        ]);

        $order = Order::with('items.variant.product')
            ->where('order_code', $request->order_code)
            ->where(function ($q) use ($request) {
                $q->where('customer_phone', $request->contact)
                    ->orWhere('customer_email', $request->contact);
            })
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Thông tin không đúng!'], 404);
        }

        $items = $order->items->map(function ($item) {
            return [
                'order_item_id' => $item->id,
                'product_variant_id'  => $item->product_variant_id,
                'product_name' => $item->variant->product->name ?? '',
                'variant_name' => $item->variant->name ?? '',
                'image_url' => $item->variant->image_url ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'items' => $items,
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_variant_id' => 'required|exists:product_variants,id',
            'order_item_id'      => 'required|exists:order_items,id',
            'rating'             => 'required|integer|min:1|max:5',
            'comment'            => 'required|string|min:5',
            'title'              => 'nullable|string|max:255',
            'media'              => 'nullable|array|max:6', // tối đa 6 file
            'media.*'            => 'file|mimes:jpg,jpeg,png,gif,mp4,webm,mov|max:10240', // cho phép video + 10MB
        ]);


        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        // Check đã đánh giá chưa (theo order_item_id)
        if (Review::where('order_item_id', $data['order_item_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm này đã được đánh giá rồi.'], 409);
        }

        $orderItem = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.id', $data['order_item_id'])
            ->where('order_items.product_variant_id', $data['product_variant_id'])
            ->whereIn('orders.status', ['delivered', 'completed'])
            ->first();

        if (!$orderItem) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa đủ điều kiện đánh giá.'], 403);
        }

        $review = Review::create([
            'product_variant_id'    => $data['product_variant_id'],
            'order_item_id'         => $data['order_item_id'],
            'user_id'               => null,
            'rating'                => $data['rating'],
            'title'                 => $data['title'] ?? null,
            'comment'               => $data['comment'],
            'status'                => 'pending',
            'is_verified_purchase'  => true,
        ]);

        // Lưu ảnh
        if ($request->hasFile('media')) {
            $mediaFiles = $request->file('media');
            $imageCount = 0;
            $videoCount = 0;

            foreach ($mediaFiles as $file) {
                $mimeType = $file->getMimeType();

                if (str_starts_with($mimeType, 'image/')) {
                    $imageCount++;
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $videoCount++;
                }
            }

            if ($imageCount > 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ được tải lên tối đa 5 ảnh.',
                ], 422);
            }

            if ($videoCount > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ được tải lên 1 video.',
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
        }


        return response()->json(['success' => true, 'message' => 'Đánh giá đã được gửi!']);
    }
}
