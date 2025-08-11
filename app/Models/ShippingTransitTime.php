<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingTransitTime extends Model
{
    protected $fillable = [
        'carrier_name',
        'from_province_code',
        'to_province_code',
        'transit_days_min',
        'transit_days_max'
    ];

    /**
     * Lấy thời gian vận chuyển giữa hai tỉnh
     */
    public static function getTransitTime($carrierName, $fromProvinceCode, $toProvinceCode)
    {
        return self::where('carrier_name', $carrierName)
            ->where('from_province_code', $fromProvinceCode)
            ->where('to_province_code', $toProvinceCode)
            ->first();
    }

    /**
     * Tính ngày giao hàng dự kiến
     */
    public static function calculateDeliveryDate($carrierName, $fromProvinceCode, $toProvinceCode, $orderDate = null)
    {
        $transitTime = self::getTransitTime($carrierName, $fromProvinceCode, $toProvinceCode);
        
        if (!$transitTime) {
            return null;
        }

        $orderDate = $orderDate ?: now();
        
        return [
            'min_delivery_date' => $orderDate->addDays($transitTime->transit_days_min),
            'max_delivery_date' => $orderDate->addDays($transitTime->transit_days_max),
            'transit_days_min' => $transitTime->transit_days_min,
            'transit_days_max' => $transitTime->transit_days_max
        ];
    }
}