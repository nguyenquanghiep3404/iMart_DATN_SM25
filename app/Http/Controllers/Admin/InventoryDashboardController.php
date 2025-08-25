<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Category;
use App\Models\StoreLocation;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductProfitExport;

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
            'totalValue',
            'totalSku',
            'lowStockCount',
            'valueByStore',
            'topProducts',
            'pendingTransfers',
            'ongoingStocktakes'
        ));
    }
    // Phân tích kinh doanh
    public function businessAnalysis(Request $request)
    {
        // Lấy filter từ request
        $dateFilter = $request->input('date_filter', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $storeLocation = $request->input('store_location');
        $category = $request->input('category');
        // filter ngày
        $queryDate = $this->parseDateFilter($dateFilter, $startDate, $endDate);
        $start = $queryDate['start'];
        $end = $queryDate['end'];

        // 1. Tính toán KPIs
        $kpis = $this->calculateKPIs($start, $end, $storeLocation, $category);

        // 2. Dữ liệu cho charts
        $chartData = $this->getChartData($start, $end, $storeLocation, $category);
        // 3. Lấy danh sách store locations và categories cho filter
        $storeLocations = StoreLocation::all();
        $categories = Category::all();
        return view('admin.dashboard.business_analysis', compact(
            'kpis',
            'chartData',
            'dateFilter',
            'startDate',
            'endDate',
            'storeLocation',
            'category',
            'storeLocations',
            'categories'
        ));
    }
    private function parseDateFilter($filter, $customStart, $customEnd)
    {
        $now = Carbon::now();
        switch ($filter) {
            case 'this_month':
                $start = Carbon::create($now->year, $now->month, 1, 0, 0, 0);
                $end = Carbon::create($now->year, $now->month, $now->daysInMonth, 23, 59, 59);
                break;
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $start = Carbon::create($lastMonth->year, $lastMonth->month, 1, 0, 0, 0);
                $end = Carbon::create($lastMonth->year, $lastMonth->month, $lastMonth->daysInMonth, 23, 59, 59);
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'last_week':
                $start = $now->copy()->subWeek()->startOfWeek();
                $end = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'custom':
                $start = Carbon::parse($customStart)->startOfDay();
                $end = Carbon::parse($customEnd)->endOfDay();
                break;
            default:
                $start = Carbon::create($now->year, $now->month, 1, 0, 0, 0);
                $end = Carbon::create($now->year, $now->month, $now->daysInMonth, 23, 59, 59);
        }
        return ['start' => $start, 'end' => $end];
    }

    private function calculateKPIs($start, $end, $storeLocation = null, $category = null)
    {
        // Base query cho orders - Giao hàng thành công VÀ thanh toán thành công
        $orderQuery = Order::where('status', 'delivered')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end]);
        // Filter theo store location nếu có
        if ($storeLocation && $storeLocation !== 'all') {
            $orderQuery->where('store_location_id', $storeLocation);
        }
        // 1. Tổng doanh thu
        $totalRevenue = (clone $orderQuery)->sum('sub_total');
        // 2. Tổng giá vốn (COGS) - Tính từ cost_price của product_variants
        $cogsQuery = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->where('orders.status', 'delivered')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end]);
        if ($storeLocation && $storeLocation !== 'all') {
            $cogsQuery->where('orders.store_location_id', $storeLocation);
        }
        if ($category && $category !== 'all') {
            $cogsQuery->join('products', 'product_variants.product_id', '=', 'products.id')
                ->where('products.category_id', $category);
        }
        $totalCOGS = $cogsQuery->sum(DB::raw('order_items.quantity * product_variants.cost_price'));
        // 3. Lợi nhuận gộp - Doanh thu trừ COGS
        $grossProfit = $totalRevenue - $totalCOGS;
        // 4. Tỷ suất lợi nhuận gộp
        // $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        // 5. Số lượng đơn hàng
        $totalOrders = $orderQuery->count();
        // Tính % thay đổi so với tháng trước
        // $previousStart = $start->copy()->subMonth();
        // $previousEnd = $end->copy()->subMonth();

        // $previousRevenue = Order::where('status', 'delivered')
        //     ->whereBetween('created_at', [$previousStart, $previousEnd])
        //     ->when($storeLocation && $storeLocation !== 'all', function ($query) use ($storeLocation) {
        //         return $query->where('store_location_id', $storeLocation);
        //     })
        //     ->sum('sub_total');
        // $revenueChange = $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
        return [
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCOGS,
            'gross_profit' => $grossProfit,
            // 'gross_profit_margin' => $grossProfitMargin,
            'total_orders' => $totalOrders,
            // 'revenue_change' => $revenueChange
        ];
    }
    private function getChartData($start, $end, $storeLocation = null, $category = null)
    {
        // 1. Dữ liệu doanh thu theo thời gian (6 tháng gần nhất)
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $start->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $start->copy()->subMonths($i)->endOfMonth();
            $revenue = Order::where('status', 'delivered')
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->when($storeLocation && $storeLocation !== 'all', function ($query) use ($storeLocation) {
                    return $query->where('store_location_id', $storeLocation);
                })
                ->sum('sub_total');
            $cogs = $this->calculateCOGS($monthStart, $monthEnd, $storeLocation, $category);
            $profit = $revenue - $cogs;
            $revenueData[] = [
                'label' => $monthStart->format('M Y'),
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $profit
            ];
        }
        // 2. Dữ liệu doanh thu theo danh mục
        $categoryData = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'delivered')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end])
            ->when($storeLocation && $storeLocation !== 'all', function ($query) use ($storeLocation) {
                return $query->where('orders.store_location_id', $storeLocation);
            })
            ->groupBy('categories.id', 'categories.name')
            ->select('categories.name', DB::raw('SUM(orders.sub_total) as revenue'))
            ->get();

        return [
            'revenue_timeline' => $revenueData,
            'category_revenue' => $categoryData
        ];
    }
    private function calculateCOGS($start, $end, $storeLocation = null, $category = null)
    {
        $query = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->where('orders.status', 'delivered')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end]);
        if ($storeLocation && $storeLocation !== 'all') {
            $query->where('orders.store_location_id', $storeLocation);
        }
        if ($category && $category !== 'all') {
            $query->join('products', 'product_variants.product_id', '=', 'products.id')
                ->where('products.category_id', $category);
        }
        return $query->sum(DB::raw('order_items.quantity * product_variants.cost_price'));
    }
    // Trang báo cáo chi tiết: Lợi nhuận theo sản phẩm
    public function productProfitReport(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $storeLocation = $request->input('store_location');
        $category = $request->input('category');
        $search = trim((string) $request->input('q')); // search by SKU or product name
        $queryDate = $this->parseDateFilter($dateFilter, $startDate, $endDate);
        $start = $queryDate['start'];
        $end = $queryDate['end'];
        $query = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'delivered')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$start, $end]);
        if ($storeLocation && $storeLocation !== 'all') {
            $query->where('orders.store_location_id', $storeLocation);
        }

        if ($category && $category !== 'all') {
            $query->where('products.category_id', $category);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('product_variants.sku', 'like', "%$search%")
                    ->orWhere('products.name', 'like', "%$search%");
            });
        }
        $report = $query->groupBy('product_variants.id', 'product_variants.sku', 'products.name', 'categories.name')
            ->select(
                'product_variants.sku',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
                DB::raw('SUM(order_items.quantity * product_variants.cost_price) as cogs'),
                DB::raw('SUM(order_items.quantity * order_items.price) - SUM(order_items.quantity * product_variants.cost_price) as gross_profit'),
                DB::raw('CASE WHEN SUM(order_items.quantity * order_items.price) > 0 THEN ((SUM(order_items.quantity * order_items.price) - SUM(order_items.quantity * product_variants.cost_price)) / SUM(order_items.quantity * order_items.price)) * 100 ELSE 0 END as profit_margin')
            )
            ->orderByDesc('revenue')
            ->paginate(20)
            ->withQueryString();
        $storeLocations = StoreLocation::all();
        $categories = Category::all();
        return view('admin.reports.product_profit', [
            'report' => $report,
            'dateFilter' => $dateFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'storeLocation' => $storeLocation,
            'category' => $category,
            'search' => $search,
            'storeLocations' => $storeLocations,
            'categories' => $categories,
            'start' => $start,
            'end' => $end,
        ]);
    }
    // Xuất Excel báo cáo lợi nhuận theo sản phẩm
    public function exportProductProfit(Request $request)
    {
        $queryDate = $this->parseDateFilter(
            $request->input('date_filter', 'this_month'),
            $request->input('start_date'),
            $request->input('end_date')
        );
        $filters = [
            'start' => $queryDate['start'],
            'end' => $queryDate['end'],
            'store_location' => $request->input('store_location'),
            'category' => $request->input('category'),
            'q' => $request->input('q'),
        ];
        return Excel::download(new ProductProfitExport($filters), 'product_profit.xlsx');
    }
}
