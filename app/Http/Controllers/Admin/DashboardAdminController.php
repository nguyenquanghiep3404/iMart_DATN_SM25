<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAdminController extends Controller
{
    public function index(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $queryDate = $this->parseDateFilter($dateFilter, $startDate, $endDate);
        $start = $queryDate['start'];
        $end = $queryDate['end'];

        $totalRevenue = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->sum('sub_total');

        $totalOrders = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $newCustomers = User::whereBetween('created_at', [$start, $end])->count();

        $totalProductsSold = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$start, $end])
            ->sum('order_items.quantity');

        $revenueData = $this->getRevenueChartData($start, $end);
        $categorySalesData = $this->getCategorySalesData($start, $end);
        $topSellingProducts = $this->getTopSellingProducts($start, $end, 5);
        $latestOrders = Order::with('user')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'newCustomers',
            'totalProductsSold',
            'latestOrders',
            'revenueData',
            'categorySalesData',
            'topSellingProducts',
            'dateFilter',
            'startDate',
            'endDate'
        ));
    }

    private function parseDateFilter($filter, $customStart, $customEnd)
    {
        $now = Carbon::now();
        switch ($filter) {
            case 'today':
                return ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'last_7_days':
                return ['start' => $now->copy()->subDays(6)->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'this_year':
                return ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()];
            case 'custom':
                $start = $customStart ? Carbon::parse($customStart)->startOfDay() : $now->copy()->startOfMonth();
                $end = $customEnd ? Carbon::parse($customEnd)->endOfDay() : $now->copy()->endOfMonth();
                return ['start' => $start, 'end' => $end];
            case 'this_month':
            default:
                return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()];
        }
    }

    private function getRevenueChartData(Carbon $start, Carbon $end)
    {
        $diffDays = $start->diffInDays($end);

        if ($diffDays <= 31) {
            $query = DB::table('orders')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(sub_total) as total')
                )
                ->where('status', 'delivered')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $labels = [];
            $data = [];

            for ($date = $start->copy(); $date <= $end; $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');
                $labels[] = $date->format('d/m');
                $data[] = $query->get($formattedDate)->total ?? 0;
            }
        } else {
            $query = DB::table('orders')
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(sub_total) as total')
                )
                ->where('status', 'delivered')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('year', 'month')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) => $item->total
                    ];
                });

            $labels = [];
            $data = [];

            for ($date = $start->copy()->startOfMonth(); $date <= $end; $date->addMonth()) {
                $formattedDate = $date->format('Y-m');
                $labels[] = 'Tháng ' . $date->format('m/Y');
                $data[] = $query->get($formattedDate) ?? 0;
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getCategorySalesData(Carbon $start, Carbon $end, $limit = 5)
    {
        $topCategories = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select('categories.name', DB::raw('SUM(order_items.quantity * order_items.price) as revenue'))
            ->groupBy('categories.name')
            ->orderBy('revenue', 'desc')
            ->limit($limit)
            ->get();

        return [
            'labels' => $topCategories->pluck('name'),
            'data' => $topCategories->pluck('revenue')
        ];
    }

    private function getTopSellingProducts(Carbon $start, Carbon $end, $limit = 5)
    {
        return Product::with([
            // Tải sẵn các relationship ảnh để tối ưu tốc độ
            'coverImage',
            'variants.primaryImage'
        ])
        ->select('products.*') // Lấy tất cả các cột từ bảng products
        ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
        ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.status', 'delivered')
        ->whereBetween('orders.created_at', [$start, $end])
        // Thêm cột total_quantity được tính toán
        ->addSelect(DB::raw('SUM(order_items.quantity) as total_quantity'))
        // Group by ID của sản phẩm để tổng hợp số lượng bán
        ->groupBy('products.id', 'products.name', 'products.slug', /* ... thêm các cột khác của products ở đây nếu cần */)
        ->orderBy('total_quantity', 'desc')
        ->limit($limit)
        ->get();
    }
}
