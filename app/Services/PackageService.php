<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageStatusHistory;
use App\Models\OrderFulfillment;
use App\Models\OrderFulfillmentItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service để xử lý logic nghiệp vụ của packages
 */
class PackageService
{
    /**
     * Tạo packages cho một fulfillment
     * 
     * @param OrderFulfillment $fulfillment
     * @param array $packageData - Mảng chứa thông tin packages cần tạo
     * @return array
     */
    public function createPackagesForFulfillment(OrderFulfillment $fulfillment, array $packageData = [])
    {
        return DB::transaction(function () use ($fulfillment, $packageData) {
            $packages = [];
            
            // Nếu không có packageData, tạo một package mặc định cho tất cả items
            if (empty($packageData)) {
                $package = $this->createDefaultPackage($fulfillment);
                $packages[] = $package;
            } else {
                // Tạo packages theo dữ liệu được cung cấp
                foreach ($packageData as $data) {
                    $package = $this->createPackage($fulfillment, $data);
                    $packages[] = $package;
                }
            }
            
            Log::info('Created packages for fulfillment', [
                'fulfillment_id' => $fulfillment->id,
                'packages_count' => count($packages),
                'package_ids' => collect($packages)->pluck('id')->toArray()
            ]);
            
            return $packages;
        });
    }

    /**
     * Tạo package mặc định cho fulfillment (method public để OrderController gọi)
     * 
     * @param OrderFulfillment $fulfillment
     * @return Package
     */
    public function createDefaultPackageForFulfillment(OrderFulfillment $fulfillment)
    {
        return DB::transaction(function () use ($fulfillment) {
            return $this->createDefaultPackage($fulfillment);
        });
    }

    /**
     * Tạo package mặc định cho fulfillment (tất cả items trong một package)
     */
    private function createDefaultPackage(OrderFulfillment $fulfillment)
    {
        $packageCode = Package::generatePackageCode($fulfillment->id);
        
        $package = Package::create([
            'order_fulfillment_id' => $fulfillment->id,
            'package_code' => $packageCode,
            'description' => 'Gói hàng mặc định cho đơn hàng ' . $fulfillment->order->order_code,
            'status' => Package::STATUS_PENDING_CONFIRMATION,
        ]);

        // Gán tất cả fulfillment items vào package này
        $fulfillment->items()->update(['package_id' => $package->id]);

        // Tạo lịch sử trạng thái ban đầu
        $package->statusHistory()->create([
            'status' => Package::STATUS_PENDING_CONFIRMATION,
            'timestamp' => now(),
            'notes' => 'Gói hàng được tạo tự động',
        ]);

        return $package;
    }

    /**
     * Tạo package với dữ liệu cụ thể
     */
    private function createPackage(OrderFulfillment $fulfillment, array $data)
    {
        $packageCode = $data['package_code'] ?? Package::generatePackageCode($fulfillment->id);
        
        $package = Package::create([
            'order_fulfillment_id' => $fulfillment->id,
            'package_code' => $packageCode,
            'description' => $data['description'] ?? null,
            'shipping_carrier' => $data['shipping_carrier'] ?? null,
            'tracking_code' => $data['tracking_code'] ?? null,
            'status' => $data['status'] ?? Package::STATUS_PENDING_CONFIRMATION,
        ]);

        // Gán items vào package nếu có
        if (isset($data['item_ids']) && is_array($data['item_ids'])) {
            OrderFulfillmentItem::whereIn('id', $data['item_ids'])
                ->where('order_fulfillment_id', $fulfillment->id)
                ->update(['package_id' => $package->id]);
        }

        // Tạo lịch sử trạng thái ban đầu
        $package->statusHistory()->create([
            'status' => $package->status,
            'timestamp' => now(),
            'notes' => $data['initial_notes'] ?? 'Gói hàng được tạo',
            'created_by' => $data['created_by'] ?? null,
        ]);

        return $package;
    }

    /**
     * Cập nhật trạng thái package
     */
    public function updatePackageStatus($packageId, $newStatus, $notes = null, $userId = null)
    {
        return DB::transaction(function () use ($packageId, $newStatus, $notes, $userId) {
            $package = Package::findOrFail($packageId);
            
            if (!$package->canUpdateStatus($newStatus)) {
                throw new Exception("Không thể chuyển từ trạng thái '{$package->status}' sang '{$newStatus}'");
            }

            $package->updateStatus($newStatus, $notes, $userId);

            Log::info('Package status updated', [
                'package_id' => $packageId,
                'package_code' => $package->package_code,
                'old_status' => $package->getOriginal('status'),
                'new_status' => $newStatus,
                'notes' => $notes,
                'updated_by' => $userId
            ]);

            return $package;
        });
    }

    /**
     * Lấy thông tin chi tiết package
     */
    public function getPackageDetails($packageId)
    {
        return Package::with([
            'orderFulfillment.order',
            'fulfillmentItems.orderItem.productVariant.product',
            'statusHistory.createdBy'
        ])->findOrFail($packageId);
    }

    /**
     * Lấy danh sách packages theo fulfillment
     */
    public function getPackagesByFulfillment($fulfillmentId)
    {
        return Package::where('order_fulfillment_id', $fulfillmentId)
            ->with(['fulfillmentItems.orderItem.productVariant.product'])
            ->get();
    }

    /**
     * Lấy danh sách packages theo trạng thái
     */
    public function getPackagesByStatus($status)
    {
        return Package::where('status', $status)
            ->with([
                'orderFulfillment.order',
                'fulfillmentItems.orderItem.productVariant.product'
            ])
            ->get();
    }

    /**
     * Gán items vào package
     */
    public function assignItemsToPackage($packageId, array $itemIds)
    {
        return DB::transaction(function () use ($packageId, $itemIds) {
            $package = Package::findOrFail($packageId);
            
            // Kiểm tra tất cả items thuộc cùng fulfillment
            $items = OrderFulfillmentItem::whereIn('id', $itemIds)
                ->where('order_fulfillment_id', $package->order_fulfillment_id)
                ->get();

            if ($items->count() !== count($itemIds)) {
                throw new Exception('Một số items không thuộc fulfillment này');
            }

            // Gán items vào package
            OrderFulfillmentItem::whereIn('id', $itemIds)
                ->update(['package_id' => $packageId]);

            Log::info('Items assigned to package', [
                'package_id' => $packageId,
                'package_code' => $package->package_code,
                'item_ids' => $itemIds,
                'items_count' => count($itemIds)
            ]);

            return $package->fresh(['fulfillmentItems']);
        });
    }

    /**
     * Tách items ra khỏi package (tạo package mới)
     */
    public function splitPackage($packageId, array $itemIds, array $newPackageData = [])
    {
        return DB::transaction(function () use ($packageId, $itemIds, $newPackageData) {
            $originalPackage = Package::findOrFail($packageId);
            
            // Tạo package mới
            $newPackageCode = $newPackageData['package_code'] ?? 
                Package::generatePackageCode($originalPackage->order_fulfillment_id);
            
            $newPackage = Package::create([
                'order_fulfillment_id' => $originalPackage->order_fulfillment_id,
                'package_code' => $newPackageCode,
                'description' => $newPackageData['description'] ?? 'Gói hàng được tách từ ' . $originalPackage->package_code,
                'status' => Package::STATUS_PENDING_CONFIRMATION,
            ]);

            // Chuyển items sang package mới
            OrderFulfillmentItem::whereIn('id', $itemIds)
                ->where('package_id', $packageId)
                ->update(['package_id' => $newPackage->id]);

            // Tạo lịch sử cho package mới
            $newPackage->statusHistory()->create([
                'status' => Package::STATUS_PENDING_CONFIRMATION,
                'timestamp' => now(),
                'notes' => 'Gói hàng được tách từ ' . $originalPackage->package_code,
                'created_by' => $newPackageData['created_by'] ?? null,
            ]);

            Log::info('Package split', [
                'original_package_id' => $packageId,
                'original_package_code' => $originalPackage->package_code,
                'new_package_id' => $newPackage->id,
                'new_package_code' => $newPackage->package_code,
                'moved_item_ids' => $itemIds
            ]);

            return $newPackage;
        });
    }

    /**
     * Xóa package (chỉ khi chưa có trạng thái shipped)
     */
    public function deletePackage($packageId)
    {
        return DB::transaction(function () use ($packageId) {
            $package = Package::findOrFail($packageId);
            
            if (in_array($package->status, [Package::STATUS_IN_TRANSIT, Package::STATUS_DELIVERED])) {
                throw new Exception('Không thể xóa gói hàng đã được vận chuyển');
            }

            // Gỡ bỏ liên kết với items
            $package->fulfillmentItems()->update(['package_id' => null]);
            
            // Xóa lịch sử
            $package->statusHistory()->delete();
            
            // Xóa package
            $package->delete();

            Log::info('Package deleted', [
                'package_id' => $packageId,
                'package_code' => $package->package_code
            ]);

            return true;
        });
    }
}