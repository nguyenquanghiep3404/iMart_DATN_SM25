<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductInventory;
use App\Models\StoreLocation;
use App\Models\Province;
use App\Models\District;


class InventoryAdjustmentController extends Controller
{
  public function showAdjustForm($id)
{
    $variant = ProductVariant::with('product')->findOrFail($id);

    // Lấy inventory liên quan tới variant này
    $inventories = ProductInventory::where('product_variant_id', $id)->get()->keyBy('store_location_id');

    // Load danh sách kho + địa chỉ đầy đủ + số lượng tồn
    $storeLocations = StoreLocation::with(['province', 'district', 'ward'])->get()->map(function ($loc) use ($inventories) {
        $inventory = $inventories->get($loc->id); // có thể null nếu chưa có tồn kho

        return [
            'id' => $loc->id,
            'name' => $loc->name,
            'phone' => $loc->phone,
            'type' => $loc->type,
            'province_id' => $loc->province_id,
            'district_id' => $loc->district_id,
            'fullAddress' => collect([
                $loc->address,
                optional($loc->ward)->name_with_type,
                optional($loc->district)->name_with_type,
                optional($loc->province)->name_with_type,
            ])->filter()->implode(', '),

            // Gắn tồn kho (nếu không có thì = 0)
            'stock_quantity' => $inventory ? $inventory->quantity : 0,
        ];
    });
    $totalStock = $storeLocations->sum('stock_quantity');
    $variants = ProductVariant::with('product')
    ->where('product_id', $variant->product_id)
    ->get();

    return view('admin.inventory.adjust', compact('variants','variant', 'storeLocations', 'totalStock'));
}

public function adjustStock(Request $request, $id)
{
    $request->validate([
        'new_quantity' => 'required|integer|min:0',
        'reason' => 'nullable|string|max:255',
        'note' => 'nullable|string|max:1000',
        'store_location_id' => 'required|exists:store_locations,id',
    ]);

    $variant = ProductVariant::findOrFail($id);

    $storeLocationId = $request->store_location_id;

    // Tìm tồn kho hiện tại ở kho này
    $inventory = ProductInventory::firstOrNew([
        'product_variant_id' => $variant->id,
        'store_location_id' => $storeLocationId
    ]);

    $oldQty = $inventory->quantity ?? 0;
    $newQty = $request->new_quantity;

    // Cập nhật tồn kho mới
    $inventory->quantity = $newQty;
    $inventory->inventory_type = 'new'; // nếu muốn tracking loại cập nhật
    $inventory->save();

    // Ghi log điều chỉnh
    InventoryAdjustment::create([
        'product_variant_id' => $variant->id,
        'store_location_id' => $storeLocationId,
        'user_id' => Auth::id(),
        'old_quantity' => $oldQty,
        'new_quantity' => $newQty,
        'difference' => $newQty - $oldQty,
        'reason' => $request->reason,
        'note' => $request->note,
    ]);

   return response()->json([
    'message' => 'Đã điều chỉnh tồn kho thành công',
    'redirect' => route('admin.products.index'),
]);
}

}
