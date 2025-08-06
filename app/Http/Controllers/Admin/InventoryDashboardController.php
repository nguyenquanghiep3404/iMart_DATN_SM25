<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
class InventoryDashboardController extends Controller
{
    public function index()
    {
        // Tổng giá trị tồn kho
        $totalValue = DB::table('product_inventories as pi')
            ->join('product_variants as pv', 'pi.product_variant_id', '=', 'pv.id')
            ->where('pi.inventory_type', 'new')
            ->selectRaw('SUM(pi.quantity * pv.cost_price) as total_value')
            ->value('total_value');

        // Tổng số SKU
        $totalSku = DB::table('product_inventories')
            ->where('inventory_type', 'new')
            ->distinct('product_variant_id')
            ->count('product_variant_id');

        // Số SKU dưới ngưỡng
        $lowStockCount = DB::table('product_inventories as pi')
            ->join('product_variants as pv', 'pi.product_variant_id', '=', 'pv.id')
            ->where('pi.inventory_type', 'new')
            ->groupBy('pi.product_variant_id', 'pv.low_stock_threshold')
            ->selectRaw('pi.product_variant_id, SUM(pi.quantity) as total_qty, pv.low_stock_threshold')
            ->havingRaw('SUM(pi.quantity) < pv.low_stock_threshold')
            ->get()
            ->count();

        // Số SKU sắp hết hạn (bỏ qua nếu chưa có dữ liệu hạn sử dụng)

        // Biểu đồ tròn - giá trị tồn kho theo kho
        $valueByStore = DB::table('product_inventories as pi')
            ->join('product_variants as pv', 'pi.product_variant_id', '=', 'pv.id')
            ->join('store_locations as sl', 'pi.store_location_id', '=', 'sl.id')
            ->where('pi.inventory_type', 'new')
            ->groupBy('sl.name')
            ->select('sl.name', DB::raw('SUM(pi.quantity * pv.cost_price) as total_value'))
            ->get();

        // Biểu đồ cột - top 10 sản phẩm tồn kho nhiều nhất
        $topProducts = DB::table('product_inventories as pi')
            ->join('product_variants as pv', 'pi.product_variant_id', '=', 'pv.id')
            ->where('pi.inventory_type', 'new')
            ->groupBy('pi.product_variant_id', 'pv.sku')
            ->select('pv.sku', DB::raw('SUM(pi.quantity) as total_quantity'))
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Danh sách phiếu chuyển kho chờ xử lý
        $pendingTransfers = DB::table('stock_transfers as st')
            ->leftJoin('store_locations as from_loc', 'st.from_location_id', '=', 'from_loc.id')
            ->leftJoin('store_locations as to_loc', 'st.to_location_id', '=', 'to_loc.id')
            ->where('st.status', 'pending')
            ->orderByDesc('st.created_at')
            ->limit(5)
            ->select(
                'st.transfer_code',
                'st.created_at',
                DB::raw('COALESCE(from_loc.name, "Chưa xác định") as from_location_name'),
                DB::raw('COALESCE(to_loc.name, "Chưa xác định") as to_location_name')
            )
            ->get();

        // Danh sách phiên kiểm kho đang diễn ra
        $ongoingStocktakes = DB::table('stocktakes as s')
            ->leftJoin('store_locations as sl', 's.store_location_id', '=', 'sl.id')
            ->leftJoin('users as u', 's.started_by', '=', 'u.id')
            ->where('s.status', 'in_progress')
            ->select('s.id', 's.stocktake_code', 's.created_at', 'sl.name as store_name', 'u.name as user_name')
            ->orderByDesc('s.created_at')
            ->limit(5)
            ->get();
     
        return view('admin.dashboard.inventory', compact(
            'totalValue', 'totalSku', 'lowStockCount',
            'valueByStore', 'topProducts',
            'pendingTransfers', 'ongoingStocktakes'
        ));
    }
}
