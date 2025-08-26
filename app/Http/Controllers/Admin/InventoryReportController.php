<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\DB;
use App\Exports\InventoryExport;
use Maatwebsite\Excel\Facades\Excel;
class InventoryReportController extends Controller
{
    public function export(Request $request)
    {
        $filters = $request->only(['search','province_code','district_code','location_type']);
        return Excel::download(new InventoryExport($filters), 'inventory.xlsx');
    }
    public function index()
    {
        return view('admin.reports.index');
    }
    public function generate(Request $request)
    {
        $query = ProductInventory::with([
            'productVariant:id,product_id,sku,cost_price',
            'productVariant.product:id,name',
            'storeLocation.province',
            'storeLocation.district',
            'storeLocation:id,name,province_code,district_code,type',
        ])
        ->select('product_inventories.*');

        $heldSubquery = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->selectRaw('COALESCE(SUM(oi.quantity), 0)')
            ->whereRaw('oi.product_variant_id = product_inventories.product_variant_id')
            ->whereRaw('o.store_location_id = product_inventories.store_location_id')
            ->whereIn('o.status', ['processing']);

        $query->selectSub($heldSubquery, 'held_quantity');

        if ($request->filled('search')) {
            $query->whereHas('productVariant', function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->search . '%')
                ->orWhereHas('product', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }
        
        if ($request->filled('store_location_id')) {
            $query->where('store_location_id', $request->store_location_id);
        } else {
            if ($request->filled('province_code')) {
                $query->whereHas('storeLocation', fn($q) => $q->where('province_code', $request->province_code));
            }
        
            if ($request->filled('district_code')) {
                $query->whereHas('storeLocation', fn($q) => $q->where('district_code', $request->district_code));
            }
        
            if ($request->filled('location_type') && $request->location_type !== 'all') {
                $query->whereHas('storeLocation', fn($q) => $q->where('type', $request->location_type));
            }
        }
        

        $data = $query->paginate(10);

        $data->getCollection()->transform(function ($item) {
            $item->available_quantity = max(0, $item->quantity - ($item->held_quantity ?? 0));
            $item->province_name = $item->storeLocation->province->name ?? null;
            $item->district_name = $item->storeLocation->district->name ?? null;
            return $item;
        });

        return response()->json($data);
    }


    public function getAvailableProvinces()
    {
        $provinces = DB::table('product_inventories as pi')
            ->join('store_locations as sl', 'pi.store_location_id', '=', 'sl.id')
            ->join('provinces_old as p', 'sl.province_code', '=', 'p.code')
            ->select('sl.province_code', 'p.name_with_type as province_name')
            ->distinct()
            ->orderBy('p.name_with_type')
            ->get();

        return response()->json($provinces);
    }
    public function getAvailableDistricts(Request $request)
    {
        $provinceCode = $request->query('province_code');

        if (!$provinceCode) {
            return response()->json(['error' => 'province_code is required'], 400);
        }

        $districts = DB::table('districts_old')
            ->where('parent_code', $provinceCode) // dùng parent_code để lọc
            ->select('code', 'name_with_type as district_name') // chọn name_with_type cho đầy đủ info
            ->orderBy('name_with_type')
            ->get();

        return response()->json($districts);
    }
}
