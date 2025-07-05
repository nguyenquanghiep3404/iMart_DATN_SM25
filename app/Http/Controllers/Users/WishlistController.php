<?php

namespace App\Http\Controllers\Users;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
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
        // dd($products->toArray());
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
        // return response()->json($request->all());
        $userId = auth()->id();
        $variantId = $request->input('product_variant_id');

        if (!$variantId) {
            return response()->json(['success' => false, 'message' => 'Thiếu sản phẩm'], 400);
        }

        // Tạo wishlist nếu chưa có
        $wishlist = Wishlist::firstOrCreate(['user_id' => $userId]);

        // Kiểm tra đã tồn tại chưa
        $exists = WishlistItem::where('wishlist_id', $wishlist->id)
                    ->where('product_variant_id', $variantId)
                    ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm đã tồn tại trong yêu thích']);
        }

        // Thêm vào wishlist
        WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'product_variant_id' => $variantId,
        ]);

        return response()->json(['success' => true]);
    }
}
