<?php

namespace App\Http\Controllers\Users;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class WishlistController extends Controller
{
    public function index(Request $request)
{
    $sort = $request->query('sort', 'date');
    $user = Auth::user();
    $products = collect();

    if ($user) {
        $wishlist = Wishlist::where('user_id', $user->id)
            ->with(['items.productVariant.product'])
            ->first();

        if ($wishlist) {
            $items = $wishlist->items;

            $items = match ($sort) {
                'price-ascend' => $items->sortBy(fn($item) => $item->productVariant->price),
                'price-descend' => $items->sortByDesc(fn($item) => $item->productVariant->price),
                'rating' => $items->sortByDesc(fn($item) => $item->productVariant->product->average_rating ?? 0),
                default => $items->sortByDesc('added_at'),
            };

            $products = $items->values();
        }
    } else {
        $wishlistIds = session('wishlist', []);
        if (!empty($wishlistIds)) {
            $productVariants = ProductVariant::with(['product', 'primaryImage'])
                ->whereIn('id', $wishlistIds)
                ->get();

            $productVariants = match ($sort) {
                'price-ascend' => $productVariants->sortBy('price'),
                'price-descend' => $productVariants->sortByDesc('price'),
                'rating' => $productVariants->sortByDesc(fn($v) => $v->product->average_rating ?? 0),
                default => $productVariants->sortBy(function ($v) use ($wishlistIds) {
                    return array_search($v->id, array_reverse($wishlistIds));
                }),
            };

            $products = $productVariants->values()->map(function ($variant) {
                return (object)[
                    'product_variant_id' => $variant->id,
                    'productVariant' => $variant,
                ];
            });
        }
    }

    return view('users.wishlist.index', compact('products', 'sort'));
}


    public function removeSelected(Request $request)
    {
        $userId = auth()->id();
        $productVariantIds = $request->input('wishlist_ids');

        if (empty($productVariantIds) || !is_array($productVariantIds)) {
            return redirect()->back()->with('error', 'Không có sản phẩm yêu thích nào được chọn');
        }

        if ($userId) {
            // Người dùng đã đăng nhập, xóa trong database
            $deleted = WishlistItem::deleteByUserAndProductVariants($userId, $productVariantIds);
            return redirect()->back()->with('success', "$deleted sản phẩm đã được xóa khỏi danh sách yêu thích.");
        } else {
            // Khách vãng lai, xóa trong session
            $wishlist = session()->get('wishlist', []);

            // Lọc bỏ các product_variant_id cần xóa
            $wishlist = array_filter($wishlist, function ($id) use ($productVariantIds) {
                return !in_array($id, $productVariantIds);
            });

            // Cập nhật lại session
            session(['wishlist' => $wishlist]);

            return redirect()->back()->with('success', "Đã xóa sản phẩm khỏi danh sách yêu thích.");
        }
    }


    public function add(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'product_id' => 'required|exists:products,id',
            'variant_key' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $user = Auth::user();

        if ($user) {
            // Người dùng đã đăng nhập, xử lý như hiện tại
            $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);

            $exists = WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('product_variant_id', $request->product_variant_id)
                ->exists();

            if ($exists) {
                return response()->json(['info' => 'Sản phẩm này đã có trong danh sách yêu thích.']);
            }

            WishlistItem::create([
                'wishlist_id' => $wishlist->id,
                'product_variant_id' => $request->product_variant_id,
                'added_at' => now(),
            ]);

            return response()->json(['success' => 'Đã thêm vào danh sách yêu thích!']);
        } else {
            // Khách vãng lai: dùng session để lưu danh sách yêu thích
            $wishlist = session()->get('wishlist', []);

            if (in_array($request->product_variant_id, $wishlist)) {
                return response()->json(['info' => 'Sản phẩm này đã có trong danh sách yêu thích.']);
            }

            $wishlist[] = $request->product_variant_id;
            session(['wishlist' => $wishlist]);

            return response()->json(['success' => 'Đã thêm vào danh sách yêu thích!']);
        }
    }
}
