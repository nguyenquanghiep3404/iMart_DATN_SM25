<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\DistrictOld;
use App\Models\ProvinceOld;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use App\Models\StoreLocation;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryLedgerExport;


class InventoryLedgerController extends Controller
{
    /**
     * Hiển thị báo cáo lịch sử giao dịch tồn kho.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Xây dựng query cơ bản với eager loading để tối ưu hiệu suất
        $query = InventoryMovement::query()
            ->orderBy('created_at', 'desc')
            ->with(['productVariant.product', 'storeLocation', 'user', 'reference']);

        // Áp dụng các bộ lọc từ request
        $this->applyFilters($query, $request);

        // Phân trang kết quả, mỗi trang 20 dòng
        $movements = $query->paginate(20);

        // Biến đổi collection để thêm cột reference_code
        $movements->getCollection()->transform(function ($movement) {
            $movement->reference_code = $this->getReferenceCode($movement);
            return $movement;
        });

        // Lấy danh sách tỉnh/thành có cửa hàng phát sinh giao dịch
        $provinces = ProvinceOld::whereHas('storeLocations', function ($q) {
            $q->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('inventory_movements')
                    ->whereColumn('inventory_movements.store_location_id', 'store_locations.id');
            });
        })->get(['code', 'name']);

        // Trả về view Blade với dữ liệu
        return view('admin.reports.inventory-ledger', [
            'movements' => $movements,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Lấy danh sách quận/huyện cho một tỉnh/thành có cửa hàng phát sinh giao dịch.
     *
     * @param string $provinceCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistricts($provinceCode)
    {
        // Kiểm tra mã tỉnh hợp lệ
        if (!ProvinceOld::where('code', $provinceCode)->exists()) {
            return response()->json([], 404);
        }

        // Lấy danh sách quận/huyện có cửa hàng phát sinh giao dịch
        $districts = DistrictOld::where('parent_code', $provinceCode)
            ->whereHas('storeLocations', function ($q) {
                $q->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('inventory_movements')
                        ->whereColumn('inventory_movements.store_location_id', 'store_locations.id');
                });
            })
            ->get(['code', 'name']);

        return response()->json($districts);
    }

    /**
     * Áp dụng các bộ lọc cho query dựa trên tham số request.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applyFilters($query, Request $request)
    {
        // Bộ lọc khoảng thời gian
        if ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('created_at', [$request->date_start, $request->date_end]);
        }

        // Bộ lọc địa điểm (tỉnh/thành và quận/huyện)
        if ($request->filled('province_code')) {
            $query->whereHas('storeLocation', function ($q) use ($request) {
                $q->where('province_code', $request->province_code);
                if ($request->filled('district_code')) {
                    $q->where('district_code', $request->district_code);
                }
            });
        }

        // Bộ lọc loại giao dịch (mapping cả dạng chuẩn lẫn dạng mô tả)
        if ($request->filled('transaction_type')) {
            $query->where('reason', $request->transaction_type);
        }



        // Bộ lọc tìm kiếm (tên sản phẩm, SKU, hoặc mã tham chiếu)
       if ($request->filled('search')) {
    $search = $request->search;

    $query->where(function ($q) use ($search) {
        // Search by product name or SKU
      $q->whereHas('productVariant', function ($q) use ($search) {
    $q->where('sku', 'like', "%$search%")
      ->orWhereHas('product', function ($q) use ($search) {
          $q->where('name', 'like', "%$search%");
      });
});


        // Search if reference is Order
        $q->orWhere(function ($sub) use ($search) {
            $sub->where('reference_type', Order::class)
                ->whereHasMorph('reference', [Order::class], function ($q) use ($search) {
                    $q->where('order_code', 'like', "%$search%");
                });
        });

        // Search if reference is PurchaseOrder
        $q->orWhere(function ($sub) use ($search) {
            $sub->where('reference_type', \App\Models\PurchaseOrder::class)
                ->whereHasMorph('reference', [\App\Models\PurchaseOrder::class], function ($q) use ($search) {
                    $q->where('po_code', 'like', "%$search%");
                });
        });

        // Search if reference is StockTransfer
        $q->orWhere(function ($sub) use ($search) {
            $sub->where('reference_type', \App\Models\StockTransfer::class)
                ->whereHasMorph('reference', [\App\Models\StockTransfer::class], function ($q) use ($search) {
                    $q->where('transfer_code', 'like', "%$search%");
                });
        });
    });
}

    }

    /**
     * Lấy mã tham chiếu từ quan hệ đa hình.
     *
     * @param \App\Models\InventoryMovement $movement
     * @return string
     */
    private function getReferenceCode($movement)
    {
        if (!$movement->reference) {
            return 'N/A';
        }

        // Giả định rằng các model tham chiếu (Order, PurchaseOrder, StockTransfer, v.v.) có phương thức getCode()
        return method_exists($movement->reference, 'getCode')
            ? $movement->reference->getCode()
            : 'N/A';
    }

    public function export(Request $request)
    {
        return Excel::download(new InventoryLedgerExport($request), 'inventory-ledger.xlsx');
    }
}
