<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StoreLocation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockTransferItem;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use App\Models\InventoryMovement;
use App\Models\InventorySerial;
use App\Models\ProductInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\StockTransferItemSerial;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromLocation', 'toLocation', 'createdBy']);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('transfer_code', 'like', "%{$searchTerm}%");
        }
        if ($request->filled('from_location_id')) {
            $query->where('from_location_id', $request->input('from_location_id'));
        }
        if ($request->filled('to_location_id')) {
            $query->where('to_location_id', $request->input('to_location_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_dir', 'desc'));
        $stockTransfers = $query->paginate(15)->withQueryString();
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
        $statuses = [
            'pending' => 'Chờ chuyển',
            'shipped' => 'Đang chuyển',
            'received' => 'Đã nhận',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.stock_transfers.index', compact('stockTransfers', 'locations', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();
        $provinces = ProvinceOld::orderBy('name')->get();
        $districts = DistrictOld::orderBy('name')->get();
        return view('admin.stock_transfers.create', compact('locations', 'provinces', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
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
        ], [
            'to_location_id.different' => 'Kho nhận phải khác kho gửi.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // **BẮT ĐẦU: KIỂM TRA TỒN KHO TRƯỚC KHI TẠO**
        $fromLocationId = $request->input('from_location_id');
        $items = $request->input('items');
        
        // Lấy tất cả các ID biến thể để truy vấn một lần, tăng hiệu suất
        $variantIds = array_column($items, 'product_variant_id');
        $productVariants = ProductVariant::with('product')->findMany($variantIds);
        $inventories = ProductInventory::where('store_location_id', $fromLocationId)
                                       ->whereIn('product_variant_id', $variantIds)
                                       ->where('inventory_type', 'new')
                                       ->get()
                                       ->keyBy('product_variant_id');
        
        foreach ($items as $itemData) {
            $inventory = $inventories->get($itemData['product_variant_id']);
            $productVariant = $productVariants->find($itemData['product_variant_id']);
            $productFullName = $productVariant->product->name . ' (' . $productVariant->sku . ')';

            if (!$inventory || $inventory->quantity < $itemData['quantity']) {
                $availableStock = $inventory->quantity ?? 0;
                $errorMessage = "Không đủ tồn kho cho sản phẩm '{$productFullName}'. Tồn kho hiện tại: {$availableStock}, Yêu cầu chuyển: {$itemData['quantity']}.";
                return redirect()->back()->with('error', $errorMessage)->withInput();
            }
        }
        // **KẾT THÚC: KIỂM TRA TỒN KHO**

        DB::beginTransaction();
        try {
            $transferCode = 'ST-' . Carbon::now()->format('Ymd') . '-' . strtoupper(uniqid());

            $stockTransfer = StockTransfer::create([
                'transfer_code' => $transferCode,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $request->input('to_location_id'),
                'notes' => $request->input('notes'),
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            foreach ($items as $itemData) {
                $stockTransfer->items()->create([
                    'product_variant_id' => $itemData['product_variant_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.stock-transfers.show', $stockTransfer->id)
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
     * Display the specified resource.
     */
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load([
            'fromLocation', 
            'toLocation', 
            'createdBy', 
            'items.productVariant.product', 
            'items.productVariant.primaryImage', 
            'items.productVariant.attributeValues.attribute'
        ]);

        $statuses = [
            'pending' => 'Chờ chuyển',
            'shipped' => 'Đang chuyển',
            'received' => 'Đã nhận',
            'cancelled' => 'Đã hủy',
        ];
        
        return view('admin.stock_transfers.show', compact('stockTransfer', 'statuses'));
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

    /**
     * Show the dispatch page for scanning items.
     */
    public function showDispatchPage(StockTransfer $stockTransfer = null)
    {
        $preselectedTransferData = null;
        if ($stockTransfer) {
            if ($stockTransfer->status !== 'pending') {
                return redirect()->route('admin.stock-transfers.show', $stockTransfer->id)
                                 ->with('error', 'Phiếu này không ở trạng thái chờ chuyển.');
            }

            $stockTransfer->load([
                'fromLocation', 
                'toLocation', 
                'items.productVariant' => function ($query) {
                    $query->with(['product', 'attributeValues.attribute']);
                }
            ]);
            
            $preselectedTransferData = $this->formatTransferDataForFrontend($stockTransfer);
        }
        
        return view('admin.stock_transfers.dispatch', ['preselectedTransfer' => $preselectedTransferData]);
    }

    /**
     * API: Get pending stock transfers.
     */
    public function getPendingTransfers()
    {
        $transfers = StockTransfer::where('status', 'pending')
            ->with(['fromLocation', 'toLocation', 'items.productVariant.product', 'items.productVariant.attributeValues.attribute'])
            ->latest()
            ->get();

        $formattedData = $transfers->map(fn($st) => $this->formatTransferDataForFrontend($st));

        return response()->json($formattedData);
    }

    /**
     * Process the dispatch of items and scanned serials.
     */
    public function processDispatch(Request $request, StockTransfer $stockTransfer)
    {
        $validated = $request->validate([
            'scanned_serials' => 'present|array',
            'scanned_serials.*' => 'present|array',
        ]);

        DB::beginTransaction();
        try {
            if ($stockTransfer->status !== 'pending') {
                throw new \Exception("Phiếu chuyển kho không ở trạng thái chờ.");
            }

            $fromLocationId = $stockTransfer->from_location_id;
            
            foreach ($stockTransfer->items as $stItem) {
                $productVariant = $stItem->productVariant;
                $quantity = $stItem->quantity;

                if ($productVariant->has_serial_tracking) {
                    $serials = $validated['scanned_serials'][$stItem->id] ?? [];
                    if (count($serials) !== $quantity) throw new \Exception("Số lượng serial cho SKU {$productVariant->sku} không khớp.");

                    $inventorySerials = InventorySerial::where('product_variant_id', $productVariant->id)
                        ->where('store_location_id', $fromLocationId)
                        ->where('status', 'available')
                        ->whereIn('serial_number', $serials)->get();

                    if ($inventorySerials->count() !== $quantity) throw new \Exception("Một vài serial cho SKU {$productVariant->sku} không hợp lệ.");

                    $serialsToInsert = $inventorySerials->map(fn($s) => [
                        'stock_transfer_item_id' => $stItem->id, 'inventory_serial_id' => $s->id, 'created_at' => now(), 'updated_at' => now()
                    ])->all();

                    StockTransferItemSerial::insert($serialsToInsert);
                    InventorySerial::whereIn('id', $inventorySerials->pluck('id'))->update(['status' => 'transferred']);
                }

                $inventory = ProductInventory::where('product_variant_id', $productVariant->id)
                    ->where('store_location_id', $fromLocationId)
                    ->where('inventory_type', 'new')->firstOrFail();
                
                $inventory->decrement('quantity', $quantity);

                InventoryMovement::create([
                    'product_variant_id' => $productVariant->id, 'store_location_id' => $fromLocationId, 'inventory_type' => 'new',
                    'quantity_change' => -$quantity, 'quantity_after_change' => $inventory->quantity, 'reason' => 'Xuất kho chuyển đi',
                    'reference_type' => StockTransfer::class, 'reference_id' => $stockTransfer->id, 'user_id' => auth()->id(),
                ]);
            }

            $stockTransfer->update(['status' => 'shipped', 'shipped_at' => now()]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Xuất kho thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xuất kho cho ST #{$stockTransfer->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }

    // =================================================================
    // **CÁC HÀM MỚI CHO CHỨC NĂNG NHẬN KHO**
    // =================================================================

    /**
     * Show the receive page for a specific stock transfer.
     */
    public function showReceivePage(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'shipped') {
            return redirect()->route('admin.stock-transfers.show', $stockTransfer->id)
                             ->with('error', 'Phiếu này không ở trạng thái "Đang chuyển".');
        }

        $stockTransfer->load([
            'fromLocation', 
            'toLocation', 
            'items.productVariant' => fn($q) => $q->with(['product', 'attributeValues.attribute'])
        ]);

        $transferData = $this->formatTransferDataForFrontend($stockTransfer);

        return view('admin.stock_transfers.receive', ['transfer' => $transferData]);
    }

    /**
     * Process the reception of items and scanned serials.
     */
    public function processReceive(Request $request, StockTransfer $stockTransfer)
    {
        $validated = $request->validate([
            'scanned_serials' => 'present|array',
            'scanned_serials.*' => 'present|array',
        ]);

        DB::beginTransaction();
        try {
            if ($stockTransfer->status !== 'shipped') {
                throw new \Exception("Phiếu chuyển kho không ở trạng thái đang vận chuyển.");
            }

            $toLocationId = $stockTransfer->to_location_id;

            foreach ($stockTransfer->items as $stItem) {
                $productVariant = $stItem->productVariant;
                $quantity = $stItem->quantity;

                if ($productVariant->has_serial_tracking) {
                    $receivedSerials = $validated['scanned_serials'][$stItem->id] ?? [];
                    if (count($receivedSerials) !== $quantity) {
                        throw new \Exception("Số lượng serial nhận được cho SKU {$productVariant->sku} không khớp.");
                    }
                    
                    // Lấy ra các serial đã được gửi đi cho item này
                    $shippedSerials = $stItem->serials()->with('inventorySerial')->get();

                    // Kiểm tra xem các serial nhận được có khớp với serial đã gửi không
                    $shippedSerialNumbers = $shippedSerials->pluck('inventorySerial.serial_number')->all();
                    $diff = array_diff($receivedSerials, $shippedSerialNumbers);
                    if (!empty($diff)) {
                        throw new \Exception("Serial '" . reset($diff) . "' không thuộc phiếu chuyển này.");
                    }
                    
                    // Cập nhật trạng thái và vị trí mới cho serial
                    $shippedSerialIds = $shippedSerials->pluck('inventory_serial_id');
                    InventorySerial::whereIn('id', $shippedSerialIds)
                                    ->update([
                                        'status' => 'available',
                                        'store_location_id' => $toLocationId
                                    ]);
                    
                    // Cập nhật trạng thái trong bảng trung gian
                    $stItem->serials()->update(['status' => 'received']);
                }

                // Tăng tồn kho tại kho nhận cho TẤT CẢ sản phẩm
                $inventory = ProductInventory::firstOrCreate(
                    [
                        'product_variant_id' => $productVariant->id,
                        'store_location_id' => $toLocationId,
                        'inventory_type' => 'new',
                    ],
                    ['quantity' => 0]
                );
                $inventory->increment('quantity', $quantity);

                // Ghi lại lịch sử nhập kho
                InventoryMovement::create([
                    'product_variant_id' => $productVariant->id,
                    'store_location_id' => $toLocationId,
                    'inventory_type' => 'new',
                    'quantity_change' => +$quantity,
                    'quantity_after_change' => $inventory->quantity,
                    'reason' => 'Nhận kho chuyển đến',
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $stockTransfer->id,
                    'user_id' => auth()->id(),
                ]);
            }

            $stockTransfer->update([
                'status' => 'received',
                'received_at' => now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Nhận hàng và nhập kho thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi nhận kho cho ST #{$stockTransfer->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Helper function to format transfer data for frontend consistency.
     */
    private function formatTransferDataForFrontend(StockTransfer $stockTransfer)
    {
        return [
            'id' => $stockTransfer->id,
            'transfer_code' => $stockTransfer->transfer_code,
            'from_location_name' => $stockTransfer->fromLocation->name,
            'to_location_name' => $stockTransfer->toLocation->name,
            'created_at' => $stockTransfer->created_at,
            'items' => $stockTransfer->items->map(function ($item) {
                $variant = $item->productVariant;
                $productName = $variant->product->name ?? 'N/A';
                $variantName = $variant->attributeValues->pluck('value')->implode(' - ');
                return [
                    'id' => $item->id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $productName . ($variantName ? " ({$variantName})" : ''),
                    'sku' => $variant->sku,
                    'quantity' => $item->quantity,
                    'has_serial_tracking' => $variant->has_serial_tracking,
                ];
            }),
        ];
    }
}