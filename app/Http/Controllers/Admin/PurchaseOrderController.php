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
        // Start query with eager loading for optimization
        $query = PurchaseOrder::with(['supplier', 'storeLocation', 'items']);

        // 1. Filter by keyword (PO code or supplier name)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('po_code', 'like', "%{$searchTerm}%")
                  ->orWhereHas('supplier', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // 2. Filter by receiving location
        if ($request->filled('location_id')) {
            $query->where('store_location_id', $request->input('location_id'));
        }

        // 3. Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting
        $query->orderBy($request->input('sort_by', 'order_date'), $request->input('sort_dir', 'desc'));

        // Pagination
        $purchaseOrders = $query->paginate(15)->withQueryString();

        // Get data for filter dropdowns
        $locations = StoreLocation::where('is_active', true)->orderBy('name')->get();

        // Note: Your DB schema only has 'pending' for status. 
        // You might want to add more statuses like 'completed', 'cancelled'.
        $statuses = [
            'pending' => 'Đang chờ',
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

        // To make this work, add a `getFullAddressAttribute` accessor to your StoreLocation model.
        // See the "Required Model Accessors" section below.
        $locations = StoreLocation::with(['province', 'district', 'ward'])
            ->where('is_active', true)
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'fullAddress' => $location->full_address, // Uses accessor
                    'province_id' => $location->province_code,
                    'district_id' => $location->district_code,
                ];
            });
        
        // To make this work, add a `getFullAddressAttribute` accessor to your Supplier model.
        // See the "Required Model Accessors" section below.
        $suppliers = Supplier::with(['province', 'district', 'ward'])->get()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                // The supplier itself is the address
                'addresses' => [[
                    'id' => $supplier->id, // Use supplier ID as the address ID
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

            // Generate a unique PO code
            $poCode = 'PO-' . Carbon::now()->format('Ymd') . '-' . strtoupper(uniqid());

            $purchaseOrder = PurchaseOrder::create([
                'po_code' => $poCode,
                'supplier_id' => $request->input('supplier_id'),
                'store_location_id' => $request->input('store_location_id'),
                'order_date' => $request->input('order_date'),
                'notes' => $request->input('notes'),
                'status' => 'pending', // Default status
            ]);

            foreach ($request->input('items') as $variantId => $itemData) {
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

        // You will need to create a `show.blade.php` view for this method.
        return view('admin.purchase_orders.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
{
    // Tải tất cả các mối quan hệ cần thiết cho trang edit
    $purchaseOrder->load([
        'supplier', 
        'storeLocation', 
        'items.productVariant' => function ($query) {
            $query->with([
                'product', 
                'primaryImage', 
                'attributeValues.attribute', 
                'inventories' // Quan trọng: Lấy dữ liệu tồn kho
            ]);
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
        // Add business logic: prevent editing if the PO is already 'completed' or 'cancelled'
        if (in_array($purchaseOrder->status, ['completed', 'cancelled'])) {
             return redirect()->route('admin.purchase-orders.show', $purchaseOrder->id)
                              ->with('error', 'Không thể cập nhật phiếu nhập đã hoàn thành hoặc đã hủy.');
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'store_location_id' => 'required|exists:store_locations,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:pending,completed,cancelled', // Add statuses as needed
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

            // Simple approach: Delete old items and create new ones
            $purchaseOrder->items()->delete();

            foreach ($request->input('items') as $variantId => $itemData) {
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
        // Business logic: prevent deletion of completed orders.
        if ($purchaseOrder->status === 'completed') {
            return redirect()->route('admin.purchase-orders.index')
                             ->with('error', 'Không thể xóa phiếu nhập đã hoàn thành.');
        }

        try {
            // The database schema has ON DELETE CASCADE for items, so they will be deleted automatically.
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
                    // Eager load all necessary relationships for the variant
                    $query->with([
                        'primaryImage', 
                        'inventories', 
                        // Optimize attribute loading to prevent N+1 queries
                        'attributeValues.attribute' 
                    ]); 
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

        // Grouped for display with parent product headers
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
                        'stock' => $variant->inventories->sum('quantity'), // Assumes inventories table sums up stock
                        'cost_price' => $variant->cost_price ?? 0,
                    ];
                })
            ];
        });
        
        // A flat list of all variants for easy lookup in JS after selection
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

        return response()->json([
            'groupedProducts' => $groupedProducts,
            'allVariants' => $allVariants,
        ]);
    }
}