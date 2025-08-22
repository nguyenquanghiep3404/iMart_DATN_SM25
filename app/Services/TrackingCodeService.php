<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TrackingCodeService
{
    /**
     * Generate unique tracking code for order
     *
     * @param Order $order
     * @return string
     */
    public function generateTrackingCode(Order $order): string
    {
        $prefix = 'IM'; // iMart prefix
        $date = Carbon::now()->format('ymd'); // YYMMDD format
        $orderNumber = str_pad($order->id, 6, '0', STR_PAD_LEFT); // 6-digit order ID
        $random = strtoupper(Str::random(4)); // 4 random characters for better uniqueness
        
        $trackingCode = $prefix . $date . $orderNumber . $random;
        
        // Ensure uniqueness by checking OrderFulfillment table
        while (\App\Models\OrderFulfillment::where('tracking_code', $trackingCode)->exists()) {
            $random = strtoupper(Str::random(4));
            $trackingCode = $prefix . $date . $orderNumber . $random;
        }
        
        return $trackingCode;
    }

    /**
     * Generate and assign tracking code to order via fulfillment
     *
     * @param Order $order
     * @param int|null $storeLocationId
     * @return string
     */
    public function assignTrackingCodeToOrder(Order $order, $storeLocationId = null): string
    {
        // Check if order already has fulfillments with tracking codes
        $fulfillmentWithTrackingCode = $order->fulfillments()->whereNotNull('tracking_code')->first();
        
        if ($fulfillmentWithTrackingCode) {
            return $fulfillmentWithTrackingCode->tracking_code;
        }
        
        // Get all fulfillments without tracking codes
        $fulfillmentsWithoutTrackingCode = $order->fulfillments()->whereNull('tracking_code')->get();
        
        if ($fulfillmentsWithoutTrackingCode->isEmpty()) {
            // No fulfillments exist, create one with tracking code
            $trackingCode = $this->generateTrackingCode($order);
            
            // Get store_location_id from parameter, order, or current user's first assigned store
            if (!$storeLocationId) {
                // Try to get from order first
                if ($order->store_location_id) {
                    $storeLocationId = $order->store_location_id;
                } else {
                    // Get from current user's first assigned store location
                    $userStoreLocation = auth()->user()->storeLocations()->first();
                    $storeLocationId = $userStoreLocation ? $userStoreLocation->id : 1; // Default to store location 1 if not found
                }
            }
            
            $fulfillment = $order->fulfillments()->create([
                'tracking_code' => $trackingCode,
                'store_location_id' => $storeLocationId,
                'status' => 'pending'
            ]);
            
            \Log::info('Tracking code generated for new fulfillment', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'tracking_code' => $trackingCode,
                'fulfillment_id' => $fulfillment->id,
                'store_location_id' => $storeLocationId
            ]);
            
            return $trackingCode;
        }
        
        // Generate tracking codes for all existing fulfillments without tracking codes
        $firstTrackingCode = null;
        foreach ($fulfillmentsWithoutTrackingCode as $fulfillment) {
            $trackingCode = $this->generateTrackingCode($order);
            
            $fulfillment->update([
                'tracking_code' => $trackingCode,
                'status' => 'pending'
            ]);
            
            if ($firstTrackingCode === null) {
                $firstTrackingCode = $trackingCode;
            }
            
            \Log::info('Tracking code generated for existing fulfillment', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'tracking_code' => $trackingCode,
                'fulfillment_id' => $fulfillment->id,
                'store_location_id' => $fulfillment->store_location_id
            ]);
        }
        
        return $firstTrackingCode;
    }
    
    /**
     * Generate tracking codes for all fulfillments of an order
     *
     * @param Order $order
     * @return array Array of tracking codes
     */
    public function generateTrackingCodesForAllFulfillments(Order $order): array
    {
        $trackingCodes = [];
        
        // Get all fulfillments without tracking codes
        $fulfillments = $order->fulfillments()->whereNull('tracking_code')->get();
        
        foreach ($fulfillments as $fulfillment) {
            $trackingCode = $this->generateTrackingCode($order);
            
            $fulfillment->update([
                'tracking_code' => $trackingCode,
                'status' => 'pending'
            ]);
            
            $trackingCodes[] = $trackingCode;
            
            \Log::info('Tracking code generated for fulfillment', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'tracking_code' => $trackingCode,
                'fulfillment_id' => $fulfillment->id,
                'store_location_id' => $fulfillment->store_location_id
            ]);
        }
        
        return $trackingCodes;
    }
}