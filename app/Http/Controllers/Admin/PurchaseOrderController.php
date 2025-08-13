<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\StoreLocation;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrderItem;
use App\Models\InventoryLot;
use App\Models\InventoryLotLocation;
use App\Models\InventorySerial;
use App\Models\ProductInventory;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'storeLocation', 'items']);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('po_code', 'like', "%{$searchTerm}%")
                  ->orWhereHas('supplier', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if ($request->filled('location_id')) {
            $query->where('store_location_id', $request->input('location_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $query->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_dir', 'desc'));

        $purchaseOrders = $query->paginate(15)->withQueryString();

        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();

        // === CHANGE HERE: Updated status list for filtering ===
        $statuses = [
            'pending' => 'Chờ xử lý',
            'waiting_for_scan' => 'Chờ nhận hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.purchase_orders.index', compact('purchaseOrders', 'locations', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = ProvinceOld::orderBy('name')->get();
        $districts = DistrictOld::orderBy('name')->get();
        $locations = StoreLocation::with(['province', 'district', 'ward'])
            ->where('is_active', true)
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'fullAddress' => $location->full_address,
                    'province_id' => $location->province_code,
                    'district_id' => $location->district_code,
                ];
            });
        $suppliers = Supplier::with(['province', 'district', 'ward'])->get()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'addresses' => [[
                    'id' => $supplier->id,
                    'fullAddress' => $supplier->full_address,
                    'province_id' => $supplier->province_code,
                    'district_id' => $supplier->district_code,
                    'phone' => $supplier->phone
                ]]
            ];
        });
        return view('admin.purchase_orders.create', compact('provinces', 'districts', 'locations', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'store_location_id' => 'required|exists:store_locations,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $poCode = 'PO-' . Carbon::now()->format('Ymd') . '-' . strtoupper(uniqid());
            $purchaseOrder = PurchaseOrder::create([
                'po_code' => $poCode,
                'supplier_id' => $request->input('supplier_id'),
                'store_location_id' => $request->input('store_location_id'),
                'order_date' => $request->input('order_date'),
                'notes' => $request->input('notes'),
                'status' => 'pending',
            ]);

            foreach ($request->input('items') as $itemData) {
                $purchaseOrder->items()->create([
                    'product_variant_id' => $itemData['product_variant_id'],
                    'quantity' => $itemData['quantity'],
                    'cost_price' => $itemData['cost_price'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder->id)
                             ->with('success', "Phiếu nhập kho {$purchaseOrder->po_code} đã được tạo thành công.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create purchase order: ' . $e->getMessage());
            return redirect()->back()
                             ->with('error', 'Đã xảy ra lỗi. Không thể tạo phiếu nhập kho.')
                             ->withInput();
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier.province', 'supplier.district', 'supplier.ward',
            'storeLocation.province', 'storeLocation.district', 'storeLocation.ward',
            'items.productVariant.product', 'items.productVariant.primaryImage', 'items.productVariant.attributeValues.attribute'
        ]);
        return view('admin.purchase_orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'storeLocation',
            'items.productVariant' => function ($query) {
                $query->with(['product', 'primaryImage', 'attributeValues.attribute', 'inventories']);
            }
        ]);
        $provinces = ProvinceOld::orderBy('name')->get();
        $districts = DistrictOld::orderBy('name')->get();
        $locations = StoreLocation::with(['province', 'district', 'ward'])
            ->where('is_active', true)
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'fullAddress' => $location->full_address,
                    'province_id' => $location->province_code,
                    'district_id' => $location->district_code,
                ];
            });
        $suppliers = Supplier::with(['province', 'district', 'ward'])->get()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'addresses' => [[
                    'id' => $supplier->id,
                    'fullAddress' => $supplier->full_address,
                    'province_id' => $supplier->province_code,
                    'district_id' => $supplier->district_code,
                    'phone' => $supplier->phone
                ]]
            ];
        });
        return view('admin.purchase_orders.edit', compact('purchaseOrder', 'provinces', 'districts', 'locations', 'suppliers'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // === CHANGE HERE: Prevent editing if the PO is waiting for scan, completed, or cancelled ===
        if (in_array($purchaseOrder->status, ['waiting_for_scan', 'completed', 'cancelled'])) {
             return redirect()->route('admin.purchase-orders.show', $purchaseOrder->id)
                              ->with('error', 'Không thể cập nhật phiếu nhập ở trạng thái này.');
        }

        // === CHANGE HERE: Update validation to include the new statuses ===
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'store_location_id' => 'required|exists:store_locations,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,waiting_for_scan,cancelled', // Admin can manually set to waiting or cancel
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $purchaseOrder->update($request->only(['supplier_id', 'store_location_id', 'order_date', 'notes', 'status']));
            $purchaseOrder->items()->delete();
            foreach ($request->input('items') as $itemData) {
                $purchaseOrder->items()->create([
                    'product_variant_id' => $itemData['product_variant_id'],
                    'quantity' => $itemData['quantity'],
                    'cost_price' => $itemData['cost_price'],
                ]);
            }
            DB::commit();
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder->id)
                             ->with('success', "Phiếu nhập kho {$purchaseOrder->po_code} đã được cập nhật thành công.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update purchase order: ' . $e->getMessage());
            return redirect()->back()
                             ->with('error', 'Đã xảy ra lỗi. Không thể cập nhật phiếu nhập kho.')
                             ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // === CHANGE HERE: Prevent deletion of completed or in-progress orders ===
        if (in_array($purchaseOrder->status, ['waiting_for_scan', 'completed'])) {
            return redirect()->route('admin.purchase-orders.index')
                             ->with('error', 'Không thể xóa phiếu nhập đã hoàn thành hoặc đang xử lý.');
        }
        try {
            $purchaseOrder->delete();
            return redirect()->route('admin.purchase-orders.index')
                             ->with('success', "Đã xóa thành công phiếu nhập kho {$purchaseOrder->po_code}.");
        } catch (\Exception $e) {
            Log::error('Failed to delete purchase order: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.index')
                             ->with('error', 'Đã xảy ra lỗi. Không thể xóa phiếu nhập kho.');
        }
    }

    /**
     * Search for products for the purchase order (AJAX).
     */
    public function searchProducts(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $productsQuery = Product::query()
            ->where('status', 'published')
            ->with([
                'variants' => function ($query) {
                    $query->with(['primaryImage', 'inventories', 'attributeValues.attribute']);
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
        $products = $productsQuery->take(10)->get();
        $groupedProducts = $products->map(function ($product) {
            return [
                'parentName' => $product->name,
                'variants' => $product->variants->map(function ($variant) {
                    $variantName = $variant->attributeValues->pluck('value')->implode(' - ');
                    return [
                        'id' => $variant->id,
                        'variantName' => $variantName,
                        'sku' => $variant->sku,
                        'image_url' => optional($variant->primaryImage)->path ? Storage::url($variant->primaryImage->path) : asset('assets/admin/img/placeholder-image.png'),
                        'stock' => $variant->inventories->sum('quantity'),
                        'cost_price' => $variant->cost_price ?? 0,
                    ];
                })
            ];
        });
        $allVariants = $products->flatMap(function ($product) {
            return $product->variants->map(function ($variant) use ($product) {
                $variantName = $variant->attributeValues->map(function ($attrValue) {
                    return $attrValue->value;
                })->implode(' - ');
                return [
                   'id' => $variant->id,
                   'name' => $product->name . ' - ' . $variantName,
                   'sku' => $variant->sku,
                   'image_url' => optional($variant->primaryImage)->path ? Storage::url($variant->primaryImage->path) : asset('assets/admin/img/placeholder-image.png'),
                   'stock' => $variant->inventories->sum('quantity'),
                   'cost_price' => $variant->cost_price ?? 0,
                ];
            });
        });
        return response()->json(['groupedProducts' => $groupedProducts, 'allVariants' => $allVariants]);
    }

    /**
     * Display the goods receiving page.
     */
    public function showReceivingPage()
    {
        return view('admin.purchase_orders.receiving');
    }

    /**
     * API: Get POs that are ready to be received.
     */
    public function getPendingPurchaseOrders()
    {
        // === CHANGE HERE: Fetch orders that are pending OR waiting for scan ===
        $purchaseOrders = PurchaseOrder::whereIn('status', ['pending', 'waiting_for_scan'])
            ->with(['supplier', 'items.productVariant.product', 'storeLocation'])
            ->latest()
            ->get();

        $formattedData = $purchaseOrders->map(function ($po) {
            return [
                'id' => $po->id,
                'po_code' => $po->po_code,
                // === CHANGE HERE: Pass status and status text to the frontend ===
                'status' => $po->status,
                'status_text' => match ($po->status) {
                    'pending' => 'Chờ xử lý',
                    'waiting_for_scan' => 'Chờ nhận hàng',
                    default => ucfirst($po->status),
                },
                'supplier_name' => $po->supplier->name,
                'order_date' => $po->order_date,
                'store_location_id' => $po->store_location_id,
                'store_location_name' => $po->storeLocation->name ?? 'N/A',
                'items' => $po->items->map(function ($item) {
                    $variant = $item->productVariant;
                    $productName = $variant->product->name ?? 'Sản phẩm không xác định';
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
     * Process item reception and serial scanning.
     */
   public function receiveItems(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'scanned_serials' => 'required|array',
            'scanned_serials.*' => 'present|array',
            'scanned_serials.*.*' => 'required|string|distinct', // Added validation for each serial
        ]);

        // === START: ADDED VALIDATION LOGIC ===

        // 1. Flatten all incoming serial numbers into a single array
        $allIncomingSerials = [];
        foreach ($validated['scanned_serials'] as $serials) {
            $allIncomingSerials = array_merge($allIncomingSerials, $serials);
        }

        // 2. Check for duplicates within the request itself (e.g., same serial for different products)
        if (count($allIncomingSerials) !== count(array_unique($allIncomingSerials))) {
            $duplicates = array_diff_key($allIncomingSerials, array_unique($allIncomingSerials));
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: Tồn tại IMEI/Serial trùng lặp trong cùng một phiếu nhập: ' . implode(', ', array_unique($duplicates))
            ], 422);
        }

        // 3. Check if any of the incoming serial numbers already exist in the database
        $existingSerials = InventorySerial::whereIn('serial_number', $allIncomingSerials)->pluck('serial_number')->toArray();

        if (!empty($existingSerials)) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: Các IMEI/Serial sau đã tồn tại trong hệ thống: ' . implode(', ', $existingSerials)
            ], 422); // 422: Unprocessable Entity is a good choice for validation errors
        }

        // === END: ADDED VALIDATION LOGIC ===


        DB::beginTransaction();
        try {
            $storeLocationId = $purchaseOrder->store_location_id;
            if (!$storeLocationId) {
                throw new \Exception('Phiếu nhập hàng không có thông tin kho nhận.');
            }

            foreach ($validated['scanned_serials'] as $poItemId => $serials) {
                $poItem = PurchaseOrderItem::findOrFail($poItemId);
                $productVariantId = $poItem->product_variant_id;
                $quantity = $poItem->quantity;

                if (count($serials) !== $quantity) {
                    throw new \Exception("Số lượng serial đã quét cho sản phẩm SKU {$poItem->productVariant->sku} không khớp với số lượng trong phiếu nhập.");
                }

                $lot = InventoryLot::create([
                    'lot_code' => "PO-{$purchaseOrder->po_code}-V-{$productVariantId}-" . time(),
                    'product_variant_id' => $productVariantId,
                    'purchase_order_item_id' => $poItem->id,
                    'cost_price' => $poItem->cost_price,
                    'initial_quantity' => $quantity,
                    'quantity_on_hand' => $quantity,
                ]);

                InventoryLotLocation::create([
                    'lot_id' => $lot->id,
                    'store_location_id' => $storeLocationId,
                    'quantity' => $quantity,
                ]);

                $serialData = [];
                foreach ($serials as $serialNumber) {
                    $serialData[] = [
                        'product_variant_id' => $productVariantId, 'lot_id' => $lot->id,
                        'store_location_id' => $storeLocationId, 'serial_number' => $serialNumber,
                        'status' => 'available', 'created_at' => now(), 'updated_at' => now(),
                    ];
                }
                InventorySerial::insert($serialData);

                $inventory = ProductInventory::firstOrCreate(
                    ['product_variant_id' => $productVariantId, 'store_location_id' => $storeLocationId, 'inventory_type' => 'new'],
                    ['quantity' => 0]
                );
                $inventory->increment('quantity', $quantity);

                InventoryMovement::create([
                    'product_variant_id' => $productVariantId, 'store_location_id' => $storeLocationId,
                    'lot_id' => $lot->id, 'inventory_type' => 'new',
                    'quantity_change' => $quantity, 'quantity_after_change' => $inventory->quantity,
                    'reason' => 'Nhập hàng từ NCC', 'reference_type' => PurchaseOrder::class,
                    'reference_id' => $purchaseOrder->id, 'user_id' => auth()->id(),
                ]);
            }

            $purchaseOrder->status = 'completed';
            $purchaseOrder->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Nhập kho thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi nhập kho cho PO #{$purchaseOrder->id}: " . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }

}
