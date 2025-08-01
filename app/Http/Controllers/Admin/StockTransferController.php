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

        DB::beginTransaction();
        try {
            $transferCode = 'ST-' . Carbon::now()->format('Ymd') . '-' . strtoupper(uniqid());

            $stockTransfer = StockTransfer::create([
                'transfer_code' => $transferCode,
                'from_location_id' => $request->input('from_location_id'),
                'to_location_id' => $request->input('to_location_id'),
                'notes' => $request->input('notes'),
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->input('items') as $itemData) {
                // TODO: Validate stock availability before creating
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
        
        return view('admin.stock_transfers.show', compact('stockTransfer'));
    }
    
    /**
     * Search for products for the stock transfer (AJAX).
     */
    public function searchProducts(Request $request)
    {
        // This function remains the same
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
     * Can optionally receive a pre-selected transfer.
     */
    public function showDispatchPage(StockTransfer $stockTransfer = null)
    {
        $preselectedTransferData = null;
        if ($stockTransfer) {
            // Ensure the transfer is in a dispatchable state
            if ($stockTransfer->status !== 'pending') {
                return redirect()->route('admin.stock-transfers.show', $stockTransfer->id)
                                 ->with('error', 'Phiếu này không ở trạng thái chờ chuyển.');
            }

            $stockTransfer->load([
                'fromLocation', 
                'toLocation', 
                'items.productVariant.product', 
                'items.productVariant.attributeValues.attribute'
            ]);
            
            // Format it to match the API response structure for consistency on the frontend
            $preselectedTransferData = [
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
                    ];
                }),
            ];
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

        $formattedData = $transfers->map(function ($st) {
            return [
                'id' => $st->id,
                'transfer_code' => $st->transfer_code,
                'from_location_name' => $st->fromLocation->name,
                'to_location_name' => $st->toLocation->name,
                'created_at' => $st->created_at,
                'items' => $st->items->map(function ($item) {
                    $variant = $item->productVariant;
                    $productName = $variant->product->name ?? 'N/A';
                    $variantName = $variant->attributeValues->pluck('value')->implode(' - ');
                    return [
                        'id' => $item->id,
                        'product_variant_id' => $item->product_variant_id,
                        'name' => $productName . ($variantName ? " ({$variantName})" : ''),
                        'sku' => $variant->sku,
                        'quantity' => $item->quantity,
                    ];
                }),
            ];
        });

        return response()->json($formattedData);
    }

    /**
     * Process the dispatch of items and scanned serials.
     */
    public function processDispatch(Request $request, StockTransfer $stockTransfer)
    {
        $validated = $request->validate([
            'scanned_serials' => 'required|array',
            'scanned_serials.*' => 'present|array',
        ]);

        DB::beginTransaction();
        try {
            if ($stockTransfer->status !== 'pending') {
                throw new \Exception("Phiếu chuyển kho không ở trạng thái chờ.");
            }

            $fromLocationId = $stockTransfer->from_location_id;

            foreach ($validated['scanned_serials'] as $stItemId => $serials) {
                $stItem = StockTransferItem::findOrFail($stItemId);
                $productVariantId = $stItem->product_variant_id;
                $quantity = $stItem->quantity;

                if (count($serials) !== $quantity) {
                    throw new \Exception("Số lượng serial cho SKU {$stItem->productVariant->sku} không khớp.");
                }

                $updatedCount = InventorySerial::where('product_variant_id', $productVariantId)
                    ->where('store_location_id', $fromLocationId)
                    ->where('status', 'available')
                    ->whereIn('serial_number', $serials)
                    ->update(['status' => 'transferred']);

                if ($updatedCount !== $quantity) {
                    throw new \Exception("Một vài serial cho SKU {$stItem->productVariant->sku} không hợp lệ hoặc không có sẵn tại kho gửi.");
                }

                $inventory = ProductInventory::where('product_variant_id', $productVariantId)
                    ->where('store_location_id', $fromLocationId)
                    ->where('inventory_type', 'new')->firstOrFail();
                
                $inventory->decrement('quantity', $quantity);

                // Create inventory movement record for dispatch
                InventoryMovement::create([
                    'product_variant_id' => $productVariantId,
                    'store_location_id' => $fromLocationId,
                    'inventory_type' => 'new', // <-- FIX: Added missing field
                    'quantity_change' => -$quantity,
                    'quantity_after_change' => $inventory->quantity,
                    'reason' => 'Chuyển kho đi',
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $stockTransfer->id,
                    'user_id' => auth()->id(),
                ]);
            }

            // Update stock transfer status
            $stockTransfer->status = 'shipped';
            $stockTransfer->shipped_at = now();
            $stockTransfer->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Xuất kho thành công!']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi xuất kho cho ST #{$stockTransfer->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
}
