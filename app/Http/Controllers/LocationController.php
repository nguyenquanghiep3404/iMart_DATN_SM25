<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Ward;
use App\Models\ProvinceOld;
use App\Models\DistrictOld;
use App\Models\WardOld;

class LocationController extends Controller
{
    /**
     * Lấy danh sách tất cả tỉnh/thành phố (hệ thống mới)
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
     * Lấy danh sách xã/phường theo mã tỉnh/thành phố (hệ thống mới)
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

    /**
     * Lấy danh sách tất cả tỉnh/thành phố (hệ thống cũ)
     */
    public function getOldProvinces()
    {
        try {
            $provinces = ProvinceOld::select('code', 'name', 'name_with_type')
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
     * Lấy danh sách quận/huyện theo mã tỉnh/thành phố (hệ thống cũ)
     */
    public function getOldDistrictsByProvince($provinceCode)
    {
        try {
            $districts = DistrictOld::select('code', 'name', 'name_with_type', 'type')
                ->where('parent_code', $provinceCode)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $districts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách quận/huyện'
            ], 500);
        }
    }

    /**
     * Lấy danh sách xã/phường theo mã quận/huyện (hệ thống cũ)
     */
    public function getOldWardsByDistrict($districtCode)
    {
        try {
            $wards = WardOld::select('code', 'name', 'name_with_type', 'type')
                ->where('parent_code', $districtCode)
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

    /**
     * Kiểm tra xem tỉnh/thành phố có hỗ trợ hệ thống địa chỉ mới không
     * (Hiện tại chỉ hỗ trợ Hà Nội - code: 01)
     */
    public function checkNewSystemSupport($provinceCode)
    {
        $supportedProvinces = ['01']; // Hà Nội
        
        return response()->json([
            'success' => true,
            'supported' => in_array($provinceCode, $supportedProvinces),
            'message' => in_array($provinceCode, $supportedProvinces) 
                ? 'Hỗ trợ hệ thống địa chỉ mới' 
                : 'Chỉ hỗ trợ hệ thống địa chỉ cũ'
        ]);
    }
} 