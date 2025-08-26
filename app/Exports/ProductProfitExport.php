<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductProfitExport implements FromCollection, WithHeadings, WithMapping
{
    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            'SKU', 'Tên sản phẩm', 'Danh mục', 'Đơn vị bán', 'Doanh thu', 'Giá vốn (COGS)', 'Lợi nhuận gộp', 'Tỷ suất LN (%)'
        ];
    }

    public function collection()
    {
        // Build query giống controller
        $start = $this->filters['start'];
        $end = $this->filters['end'];
        $storeLocation = $this->filters['store_location'] ?? null;
        $category = $this->filters['category'] ?? null;
        $search = trim((string)($this->filters['q'] ?? ''));

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

        return $query->groupBy('product_variants.id', 'product_variants.sku', 'products.name', 'categories.name')
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
            ->get();
    }

    public function map($row): array
    {
        return [
            $row->sku,
            $row->product_name,
            $row->category_name,
            (int) $row->total_quantity,
            (float) $row->revenue,
            (float) $row->cogs,
            (float) $row->gross_profit,
            round((float) $row->profit_margin, 1),
        ];
    }
}


