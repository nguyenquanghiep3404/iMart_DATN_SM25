<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
class WishlistController extends Controller
{
    public function index()
    {
        // Lấy danh sách product_id trong session, mặc định là mảng rỗng
        $wishlist = session()->get('wishlist', []);

        // Lấy dữ liệu chi tiết sản phẩm từ CSDL
        $products = Product::whereIn('id', $wishlist)->get();

        // Trả về view wishlist.index với dữ liệu sản phẩm
        // var_dump($products);

        return view('users.wishlist.index', compact('products'));
    }
}
