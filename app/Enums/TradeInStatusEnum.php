<?php

namespace App\Enums;

class TradeInStatusEnum {
    public static function getDescription(string $status): string
    {
        return match ($status) {
            'available' => 'Sẵn sàng bán',
            'pending_inspection' => 'Chờ kiểm tra',
            'sold' => 'Đã bán',
            default => 'Không xác định',
        };
    }
}