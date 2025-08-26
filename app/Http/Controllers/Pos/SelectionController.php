<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\StoreLocation;
use App\Models\UserStoreLocation;
use Illuminate\Support\Facades\Auth;

class SelectionController extends Controller
{
    /**
     * Hiển thị trang lựa chọn cửa hàng.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Lớp phân quyền dữ liệu: Chỉ lấy các cửa hàng được phân công cho người dùng.
        // Đồng thời, chỉ lấy các địa điểm có type là 'store' theo yêu cầu.
        $stores = $user->storeLocations()
                       ->where('type', 'store')
                       ->get();

        return view('pos.selection', compact('stores'));
    }

    /**
     * Trả về danh sách máy POS (registers) của một cửa hàng dưới dạng JSON.
     */
    public function getRegisters(StoreLocation $store)
    {
        // Lớp phân quyền dữ liệu: Kiểm tra lại xem người dùng có quyền truy cập cửa hàng này không.
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->storeLocations()->where('store_location_id', $store->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Lấy danh sách máy POS thuộc về cửa hàng
        $registers = $store->registers()->get(['id', 'name']);

        return response()->json($registers);
    }
}