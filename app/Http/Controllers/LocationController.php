<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Ward;

class LocationController extends Controller
{
    /**
     * Lấy danh sách tất cả tỉnh/thành phố
     */
    public function getProvinces()
    {
        try {
            $provinces = Province::select('code', 'name', 'name_with_type')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $provinces
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách tỉnh/thành phố'
            ], 500);
        }
    }

    /**
     * Lấy danh sách xã/phường theo mã tỉnh/thành phố
     */
    public function getWardsByProvince($provinceCode)
    {
        try {
            $wards = Ward::select('code', 'name', 'name_with_type', 'type')
                ->where('province_code', $provinceCode)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $wards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách xã/phường'
            ], 500);
        }
    }
} 