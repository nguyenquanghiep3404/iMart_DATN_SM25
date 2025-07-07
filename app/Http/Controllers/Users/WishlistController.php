<?php

namespace App\Http\Controllers\Users;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\WishlistItem;
class WishlistController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $wishlist = Wishlist::where('user_id', $userId)
            ->with('items.productVariant')  
            ->first();

        $products = $wishlist ? $wishlist->items : collect();
        return view('users.wishlist.index', compact('products'));
    }

public function removeSelected(Request $request)
{
    $userId = auth()->id();
    $productVariantIds = $request->input('wishlist_ids'); 

        if (empty($productVariantIds) || !is_array($productVariantIds)) {
            return redirect()->back()->with('error', 'không có sản phẩm yêu thích nào được chọn');
        }

        $deleted = WishlistItem::deleteByUserAndProductVariants($userId, $productVariantIds);

        return redirect()->back()->with('success', "$deleted item(s) removed from wishlist.");
    }
    public function add(Request $request)
{
    $request->validate([
        'product_id'     => 'required|exists:products,id',
        'variant_key'    => 'nullable|string',
        'product_variant_id' => 'nullable|integer|exists:product_variants,id',
    ]);

    $userId = auth()->id();
    $productId = $request->product_id;
    $variantId = $request->product_variant_id;
    $variantKey = $request->variant_key;
    dd([
        'product_id' => $request->input('product_id'),
        'variant_id' => $request->input('variant_id'),
        'variant_key' => $request->input('variant_key'),
        'image' => $request->input('image'),
    ]);
    // Nếu thiếu variant_id thì tìm bằng variant_key
    if (!$variantId && $variantKey) {
        $product = Product::findOrFail($productId);

        $variant = ProductVariant::where('product_id', $product->id)->get()
            ->first(function ($variant) use ($variantKey) {
                $attributes = $variant->attributeValues->pluck('value')->toArray();
                return implode('_', $attributes) === $variantKey;
            });

        if (!$variant) {
            // return response()->json(['success' => false, 'message' => 'Không tìm thấy biến thể'], 422);
        }

        $variantId = $variant->id;
    }

    if (!$variantId) {
        // return response()->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 422);
    }

    // Tìm hoặc tạo wishlist
    $wishlist = Wishlist::firstOrCreate(['user_id' => $userId]);

    // Kiểm tra xem item đã tồn tại chưa
    $existing = WishlistItem::where('wishlist_id', $wishlist->id)
        ->where('product_variant_id', $variantId)
        ->first();

    if ($existing) {
        // return response()->json(['success' => true, 'message' => 'Sản phẩm đã có trong danh sách yêu thích']);
    }

    // Thêm mới
    WishlistItem::create([
        'wishlist_id'        => $wishlist->id,
        'product_variant_id' => $variantId,
        'added_at'           => now(),
    ]);

    // return response()->json(['success' => true, 'message' => 'Đã thêm vào yêu thích']);
}
    
}
