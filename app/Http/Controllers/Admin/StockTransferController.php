<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StoreLocation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Bắt đầu query từ model StockTransfer và eager load các relationship cần thiết
        $query = StockTransfer::with(['fromLocation', 'toLocation', 'createdBy']);

        // Xử lý tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('transfer_code', 'like', "%{$searchTerm}%");
        }

        // Lọc theo kho gửi
        if ($request->filled('from_location_id')) {
            $query->where('from_location_id', $request->input('from_location_id'));
        }

        // Lọc theo kho nhận
        if ($request->filled('to_location_id')) {
            $query->where('to_location_id', $request->input('to_location_id'));
        }
        
        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sắp xếp kết quả
        $query->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_dir', 'desc'));

        // Phân trang
        $stockTransfers = $query->paginate(15)->withQueryString();

        // Lấy danh sách các địa điểm (kho) để hiển thị trong bộ lọc
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();

        // Định nghĩa các trạng thái để hiển thị trong bộ lọc và bảng
        $statuses = [
            'pending' => 'Chờ chuyển',
            'shipped' => 'Đang chuyển',
            'received' => 'Đã nhận',
            'cancelled' => 'Đã hủy',
        ];

        // Trả về view cùng với dữ liệu
        return view('admin.stock_transfers.index', compact('stockTransfers', 'locations', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
{
    $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
    
    // START: THÊM 2 DÒNG NÀY
    $provinces = ProvinceOld::orderBy('name')->get();
    $districts = DistrictOld::orderBy('name')->get();
    // END: THÊM 2 DÒNG NÀY

    // Cập nhật lại hàm compact() để truyền biến mới sang view
    return view('admin.stock_transfers.create', compact('locations', 'provinces', 'districts'));
}


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_location_id' => 'required|exists:store_locations,id',
            'to_location_id' => 'required|exists:store_locations,id|different:from_location_id',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Tạo mã phiếu chuyển kho
            $transferCode = 'ST-' . Carbon::now()->format('Ymd') . '-' . strtoupper(uniqid());

            $stockTransfer = StockTransfer::create([
                'transfer_code' => $transferCode,
                'from_location_id' => $request->input('from_location_id'),
                'to_location_id' => $request->input('to_location_id'),
                'notes' => $request->input('notes'),
                'status' => 'pending', // Trạng thái ban đầu
                'created_by' => Auth::id(),
            ]);

            foreach ($request->input('items') as $itemData) {
                // TODO: Kiểm tra tồn kho tại kho gửi trước khi tạo item
                $stockTransfer->items()->create([
                    'product_variant_id' => $itemData['product_variant_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.stock-transfers.index') // Chuyển hướng đến trang danh sách
                         ->with('success', "Phiếu chuyển kho {$stockTransfer->transfer_code} đã được tạo thành công.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create stock transfer: ' . $e->getMessage());
            return redirect()->back()
                         ->with('error', 'Đã xảy ra lỗi. Không thể tạo phiếu chuyển kho.')
                         ->withInput();
        }
    }
    
    /**
     * Search for products for the stock transfer (AJAX).
     */
    public function searchProducts(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $locationId = $request->input('location_id');

        if (!$locationId) {
            return response()->json(['allVariants' => []]);
        }

        $productsQuery = Product::query()
            ->where('status', 'published')
            ->whereHas('variants.inventories', function ($query) use ($locationId) {
                $query->where('store_location_id', $locationId)->where('quantity', '>', 0);
            })
            ->with([
                'variants' => function ($query) use ($locationId) {
                    $query->whereHas('inventories', function ($subQuery) use ($locationId) {
                        $subQuery->where('store_location_id', $locationId)->where('quantity', '>', 0);
                    })->with(['primaryImage', 'attributeValues.attribute', 'inventories' => function($q) use ($locationId) {
                        $q->where('store_location_id', $locationId);
                    }]);
                }
            ]);

        if (!empty($searchTerm)) {
            $productsQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhereHas('variants', function ($subQuery) use ($searchTerm) {
                          $subQuery->where('sku', 'LIKE', "%{$searchTerm}%");
                      });
            });
        }

        $products = $productsQuery->take(15)->get();
        
        $allVariants = $products->flatMap(function ($product) use ($locationId) {
            return $product->variants->map(function ($variant) use ($product, $locationId) {
                $variantName = $variant->attributeValues->pluck('value')->implode(' - ');
                $stock = $variant->inventories->where('store_location_id', $locationId)->sum('quantity');

                return [
                   'id' => $variant->id,
                   'name' => $product->name . ($variantName ? " - {$variantName}" : ''),
                   'sku' => $variant->sku,
                   'image_url' => optional($variant->primaryImage)->path ? Storage::url($variant->primaryImage->path) : asset('assets/admin/img/placeholder-image.png'),
                   'stock' => $stock,
                ];
            });
        })->filter(function($variant){
            return $variant['stock'] > 0;
        });

        return response()->json(['allVariants' => $allVariants->values()]);
    }
}
