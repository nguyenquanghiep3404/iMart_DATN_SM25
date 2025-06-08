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

}
