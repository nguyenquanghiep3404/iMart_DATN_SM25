<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductInventory;
use App\Models\StoreLocation;
use App\Models\ShippingTransitTime;
use App\Models\ProvinceOld;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    /**
     * Tính toán shipments cho giỏ hàng
     */
    public function calculateShipments(Request $request)
    {
        try {
            $cartItems = $request->input('cart_items', []);
            $destinationProvinceCode = $request->input('destination_province_code');
            
            if (empty($cartItems) || !$destinationProvinceCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu thông tin giỏ hàng hoặc địa chỉ giao hàng'
                ], 400);
            }

            // Lấy thông tin vùng miền của địa chỉ giao hàng
            $destinationProvince = ProvinceOld::where('code', $destinationProvinceCode)->first();
            $destinationRegion = $destinationProvince->region ?? null;

            $shipments = [];
            $unavailableItems = [];

            foreach ($cartItems as $item) {
                $productVariantId = $item['product_variant_id'];
                $quantity = $item['quantity'];

                // Tìm kho có đủ hàng, ưu tiên cùng vùng miền
                $inventoryQuery = ProductInventory::where('product_variant_id', $productVariantId)
                    ->where('quantity', '>=', $quantity)
                    ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
                    ->join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                    ->where('store_locations.is_active', true);

                // Ưu tiên kho cùng vùng miền
                if ($destinationRegion) {
                    $inventoryQuery->orderByRaw("CASE WHEN provinces_old.region = ? THEN 0 ELSE 1 END", [$destinationRegion]);
                }

                $inventory = $inventoryQuery->select(
                    'product_inventories.*',
                    'store_locations.name as store_name',
                    'store_locations.province_code',
                    'provinces_old.name as province_name'
                )->first();

                if (!$inventory) {
                    $unavailableItems[] = $item;
                    continue;
                }

                $storeLocationId = $inventory->store_location_id;
                
                // Nhóm sản phẩm theo kho
                if (!isset($shipments[$storeLocationId])) {
                    // Tính thời gian vận chuyển
                    $transitTime = ShippingTransitTime::getTransitTime(
                        'store_shipper',
                        $inventory->province_code,
                        $destinationProvinceCode
                    );

                    $shipments[$storeLocationId] = [
                        'store_location_id' => $storeLocationId,
                        'store_name' => $inventory->store_name,
                        'province_name' => $inventory->province_name,
                        'items' => [],
                        'transit_days_min' => $transitTime ? $transitTime->transit_days_min : 3,
                        'transit_days_max' => $transitTime ? $transitTime->transit_days_max : 7,
                        'estimated_delivery_date' => $transitTime ? 
                            now()->addDays($transitTime->transit_days_max)->format('Y-m-d') : 
                            now()->addDays(7)->format('Y-m-d')
                    ];
                }

                $shipments[$storeLocationId]['items'][] = $item;
            }

            if (!empty($unavailableItems)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Một số sản phẩm không có đủ hàng trong kho',
                    'unavailable_items' => $unavailableItems
                ], 400);
            }

            return response()->json([
                'success' => true,
                'shipments' => array_values($shipments),
                'total_packages' => count($shipments)
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating shipments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi tính toán shipments'
            ], 500);
        }
    }

    /**
     * Tính phí vận chuyển cho một shipment
     */
    public function calculateShippingFee(Request $request)
    {
        try {
            $storeLocationId = $request->input('store_location_id');
            $destinationProvinceCode = $request->input('destination_province_code');
            $totalWeight = $request->input('total_weight', 0);
            
            $storeLocation = StoreLocation::find($storeLocationId);
            if (!$storeLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy cửa hàng'
                ], 404);
            }

            // Logic tính phí vận chuyển
            $baseFee = 30000; // Phí cơ bản 30k
            $weightFee = max(0, ($totalWeight - 1000) * 5); // 5 VND/gram cho phần vượt 1kg
            
            // Phí theo khoảng cách vùng miền
            $originProvince = ProvinceOld::where('code', $storeLocation->province_code)->first();
            $destinationProvince = ProvinceOld::where('code', $destinationProvinceCode)->first();
            
            $distanceFee = 0;
            if ($originProvince && $destinationProvince) {
                if ($originProvince->region !== $destinationProvince->region) {
                    $distanceFee = 20000; // Phí liên vùng
                }
            }

            $totalFee = $baseFee + $weightFee + $distanceFee;

            return response()->json([
                'success' => true,
                'shipping_fee' => $totalFee,
                'breakdown' => [
                    'base_fee' => $baseFee,
                    'weight_fee' => $weightFee,
                    'distance_fee' => $distanceFee
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating shipping fee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi tính phí vận chuyển'
            ], 500);
        }
    }

    /**
     * Tính toán shipments cho pickup method
     * Sử dụng logic chuyển hàng từ các stores và warehouses về store được chọn
     */
    public function calculatePickupShipments(Request $request)
    {
        try {
            Log::info('calculatePickupShipments called', ['request_data' => $request->all()]);
            
            $cartItems = $request->input('cart_items', []);
            $pickupStoreId = $request->input('pickup_store_id');
            
            Log::info('Parsed input', [
                'cart_items_count' => count($cartItems),
                'pickup_store_id' => $pickupStoreId
            ]);
            
            if (empty($cartItems) || !$pickupStoreId) {
                Log::warning('Missing required data', [
                    'cart_items_empty' => empty($cartItems),
                    'pickup_store_id_empty' => !$pickupStoreId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu thông tin giỏ hàng hoặc cửa hàng nhận hàng'
                ], 400);
            }

            $pickupStore = StoreLocation::find($pickupStoreId);
            Log::info('Pickup store found', ['store' => $pickupStore]);
            
            if (!$pickupStore || $pickupStore->type !== 'store') {
                Log::warning('Invalid pickup store', [
                    'store_found' => !!$pickupStore,
                    'store_type' => $pickupStore ? $pickupStore->type : null
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Cửa hàng nhận hàng không hợp lệ'
                ], 400);
            }

            $shipments = [];
            $unavailableItems = [];

            foreach ($cartItems as $item) {
                $productVariantId = $item['product_variant_id'];
                $quantity = $item['quantity'];

                // Tìm kho có đủ hàng (bao gồm cả stores và warehouses)
                $inventoryQuery = ProductInventory::where('product_variant_id', $productVariantId)
                    ->where('quantity', '>=', $quantity)
                    ->join('store_locations', 'product_inventories.store_location_id', '=', 'store_locations.id')
                    ->join('provinces_old', 'store_locations.province_code', '=', 'provinces_old.code')
                    ->where('store_locations.is_active', true)
                    ->whereIn('store_locations.type', ['store', 'warehouse']);

                // Ưu tiên cửa hàng nhận hàng nếu có sẵn hàng
                $inventoryQuery->orderByRaw("CASE WHEN store_locations.id = ? THEN 0 ELSE 1 END", [$pickupStoreId]);
                
                // Sau đó ưu tiên cùng vùng miền với cửa hàng nhận hàng
                $inventoryQuery->orderByRaw("CASE WHEN provinces_old.region = (SELECT p2.region FROM provinces_old p2 WHERE p2.code = ?) THEN 0 ELSE 1 END", [$pickupStore->province_code]);

                $inventory = $inventoryQuery->select(
                    'product_inventories.*',
                    'store_locations.name as store_name',
                    'store_locations.type as store_type',
                    'store_locations.province_code',
                    'provinces_old.name as province_name'
                )->first();

                if (!$inventory) {
                    $unavailableItems[] = $item;
                    continue;
                }

                $sourceLocationId = $inventory->store_location_id;
                
                // Nhóm sản phẩm theo kho nguồn
                if (!isset($shipments[$sourceLocationId])) {
                    // Tính thời gian chuyển hàng về cửa hàng nhận hàng
                    $transitTime = null;
                    if ($sourceLocationId != $pickupStoreId) {
                        $transitTime = ShippingTransitTime::getTransitTime(
                            'internal_transfer',
                            $inventory->province_code,
                            $pickupStore->province_code
                        );
                    }

                    $shipments[$sourceLocationId] = [
                        'source_location_id' => $sourceLocationId,
                        'source_name' => $inventory->store_name,
                        'source_type' => $inventory->store_type,
                        'province_name' => $inventory->province_name,
                        'pickup_store_id' => $pickupStoreId,
                        'pickup_store_name' => $pickupStore->name,
                        'items' => [],
                        'requires_transfer' => $sourceLocationId != $pickupStoreId,
                        'transit_days_min' => $transitTime ? $transitTime->transit_days_min : ($sourceLocationId != $pickupStoreId ? 1 : 0),
                        'transit_days_max' => $transitTime ? $transitTime->transit_days_max : ($sourceLocationId != $pickupStoreId ? 3 : 0),
                        'estimated_ready_date' => $transitTime ? 
                            now()->addDays($transitTime->transit_days_max)->format('Y-m-d') : 
                            ($sourceLocationId != $pickupStoreId ? now()->addDays(3)->format('Y-m-d') : now()->format('Y-m-d'))
                    ];
                }

                $shipments[$sourceLocationId]['items'][] = $item;
            }

            Log::info('Processing completed', [
                'shipments_count' => count($shipments),
                'unavailable_items_count' => count($unavailableItems)
            ]);
            
            if (!empty($unavailableItems)) {
                Log::warning('Some items unavailable', ['unavailable_items' => $unavailableItems]);
                return response()->json([
                    'success' => false,
                    'message' => 'Một số sản phẩm không có đủ hàng trong hệ thống',
                    'unavailable_items' => $unavailableItems
                ], 400);
            }

            $response = [
                'success' => true,
                'shipments' => array_values($shipments),
                'total_packages' => count($shipments),
                'pickup_store' => [
                    'id' => $pickupStore->id,
                    'name' => $pickupStore->name,
                    'address' => $pickupStore->address
                ]
            ];
            
            Log::info('Returning successful response', ['response' => $response]);
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error calculating pickup shipments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi tính toán shipments cho pickup'
            ], 500);
        }
    }
}