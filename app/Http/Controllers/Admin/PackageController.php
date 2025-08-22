<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\OrderFulfillment;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    protected $packageService;

    public function __construct()
    {
        $this->packageService = new PackageService();
    }

    /**
     * Display a listing of packages for a fulfillment
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $fulfillmentId = $request->get('fulfillment_id');
            $status = $request->get('status');

            if ($fulfillmentId) {
                $packages = $this->packageService->getPackagesByFulfillment($fulfillmentId);
            } elseif ($status) {
                $packages = $this->packageService->getPackagesByStatus($status);
            } else {
                $packages = Package::with(['orderFulfillment.order', 'fulfillmentItems.orderItem'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
            }

            return response()->json([
                'success' => true,
                'data' => $packages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách packages',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created package
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order_fulfillment_id' => 'required|exists:order_fulfillments,id',
                'description' => 'nullable|string|max:255',
                'shipping_carrier' => 'nullable|string|max:100',
                'items' => 'nullable|array',
                'items.*.order_item_id' => 'required|exists:order_items,id',
                'items.*.quantity' => 'required|integer|min:1'
            ]);

            $fulfillment = OrderFulfillment::findOrFail($request->order_fulfillment_id);

            if ($request->has('items')) {
                $package = $this->packageService->createPackageForFulfillment(
                    $fulfillment,
                    $request->items,
                    $request->description,
                    $request->shipping_carrier
                );
            } else {
                $package = $this->packageService->createDefaultPackageForFulfillment($fulfillment);
            }

            return response()->json([
                'success' => true,
                'data' => $package->load(['orderFulfillment', 'fulfillmentItems.orderItem']),
                'message' => 'Package đã được tạo thành công'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo package',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified package
     */
    public function show(Package $package): JsonResponse
    {
        try {
            $packageDetails = $this->packageService->getPackageDetails($package->id);

            return response()->json([
                'success' => true,
                'data' => $packageDetails
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin package',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified package
     */
    public function update(Request $request, Package $package): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'sometimes|in:' . implode(',', Package::getStatuses()),
                'description' => 'sometimes|nullable|string|max:255',
                'shipping_carrier' => 'sometimes|nullable|string|max:100',
                'tracking_code' => 'sometimes|nullable|string|max:100'
            ]);

            if ($request->has('status')) {
                $this->packageService->updatePackageStatus(
                    $package->id,
                    $request->status,
                    $request->get('notes', 'Cập nhật trạng thái từ admin'),
                    auth()->id()
                );
            }

            // Cập nhật các thông tin khác
            $updateData = $request->only(['description', 'shipping_carrier', 'tracking_code']);
            if (!empty($updateData)) {
                $package->update($updateData);
            }

            return response()->json([
                'success' => true,
                'data' => $package->fresh()->load(['orderFulfillment', 'fulfillmentItems.orderItem']),
                'message' => 'Package đã được cập nhật thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật package',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified package
     */
    public function destroy(Package $package): JsonResponse
    {
        try {
            $this->packageService->deletePackage($package->id);

            return response()->json([
                'success' => true,
                'message' => 'Package đã được xóa thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa package',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Assign items to a package
     */
    public function assignItems(Request $request, Package $package): JsonResponse
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.fulfillment_item_id' => 'required|exists:order_fulfillment_items,id',
                'items.*.quantity' => 'required|integer|min:1'
            ]);

            $this->packageService->assignItemsToPackage($package->id, $request->items);

            return response()->json([
                'success' => true,
                'data' => $package->fresh()->load(['fulfillmentItems.orderItem']),
                'message' => 'Items đã được gán vào package thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gán items vào package',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Split a package
     */
    public function split(Request $request, Package $package): JsonResponse
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.fulfillment_item_id' => 'required|exists:order_fulfillment_items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'new_package_description' => 'nullable|string|max:255'
            ]);

            $newPackage = $this->packageService->splitPackage(
                $package->id,
                $request->items,
                $request->get('new_package_description', 'Package tách từ ' . $package->package_code)
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'original_package' => $package->fresh()->load(['fulfillmentItems.orderItem']),
                    'new_package' => $newPackage->load(['fulfillmentItems.orderItem'])
                ],
                'message' => 'Package đã được tách thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tách package',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
