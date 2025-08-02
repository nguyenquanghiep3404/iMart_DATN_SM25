<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GhnService
{
    protected $token;
    protected $shopId;
    protected $baseUrl;

    public function __construct()
    {
        $apiUrl = rtrim(config('services.ghn.api_url'), '/');
        // Nếu chưa có /shiip/public-api ở cuối thì tự động nối vào
        if (!str_contains($apiUrl, '/shiip/public-api')) {
            $apiUrl .= '/shiip/public-api';
        }
        $this->token = config('services.ghn.token');
        $this->shopId = config('services.ghn.shop_id');
        $this->baseUrl = $apiUrl;
    }

    /**
     * Tính phí vận chuyển GHN
     * @param int $toDistrictId
     * @param string $toWardCode
     * @param int $weight (gram)
     * @param string $serviceType
     * @return int|false
     */
    public function calculateShippingFee($toDistrictId, $toWardCode, $weight, $length = 20, $width = 10, $height = 10, $insuranceValue = 0)
    {
        // \Log::info('GHN: Đã vào hàm calculateShippingFee', [
        //     'toDistrictId' => $toDistrictId,
        //     'toWardCode' => $toWardCode,
        //     'weight' => $weight,
        //     'length' => $length,
        //     'width' => $width,
        //     'height' => $height
        // ]);
        $serviceId = $this->getServiceId($toDistrictId, $weight);
        if (!$serviceId) {
            // \Log::error('GHN: Không tìm thấy service_id phù hợp', [
            //     'toDistrictId' => $toDistrictId,
            //     'weight' => $weight
            // ]);
            return false;
        }
        
        $fromDistrictId = (int)config('services.ghn.from_district_id', 1485);
        
        $body = [
            'from_district_id' => $fromDistrictId,
            'service_id' => (int)$serviceId,
            'to_district_id' => (int)$toDistrictId,
            'to_ward_code' => $toWardCode,
            'weight' => (int)$weight,
            'height' => (int)$height,
            'length' => (int)$length,
            'width' => (int)$width,
            'insurance_value' => (int)$insuranceValue,
        ];
        // \Log::info('GHN /fee body types', [
        //     'from_district_id' => [gettype($body['from_district_id']), $body['from_district_id']],
        //     'service_id' => [gettype($body['service_id']), $body['service_id']],
        //     'to_district_id' => [gettype($body['to_district_id']), $body['to_district_id']],
        //     'weight' => [gettype($body['weight']), $body['weight']],
        // ]);
        $response = Http::withHeaders([
            'Token' => $this->token,
            'ShopId' => $this->shopId,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v2/shipping-order/fee', $body);
        
        if (!$response->successful()) {
            // \Log::error('GHN: API lỗi', ['body' => $response->body(), 'status' => $response->status()]);
            return false;
        }
        
        $data = $response->json();
        
        // Debug: Log full response (đã comment)
        // \Log::info('GHN API - Full response', [
        //     'request' => $body,
        //     'response' => $data,
        //     'status' => $response->status()
        // ]);
        
        if (isset($data['data']['total']) && is_numeric($data['data']['total'])) {
            $fee = (int)$data['data']['total'];
            
            // Debug: Log phí ship trả về (đã comment)
            // \Log::info('GHN API - Phí ship trả về', [
            //     'fee' => $fee,
            //     'from_district' => $fromDistrictId,
            //     'to_district' => $toDistrictId,
            //     'to_ward' => $toWardCode
            // ]);
            
            return $fee;
        }
        
        \Log::error('GHN: Response không có data.total', ['response' => $data]);
        return false;
    }

    /**
     * Lấy service_id phù hợp cho quận/huyện đích
     */
    public function getServiceId($toDistrictId, $serviceType = 'standard', $fromDistrictId = null, $weight = 1000)
    {
        $fromDistrictId = $fromDistrictId ?: config('services.ghn.from_district_id', 1485); // Cho phép cấu hình from_district_id
        $body = [
            'shop_id' => (int)$this->shopId,
            'from_district' => (int)$fromDistrictId,
            'to_district' => (int)$toDistrictId,
            'weight' => (int)$weight
        ];
        $headers = [
            'Token' => $this->token,
            'ShopId' => $this->shopId,
            'Content-Type' => 'application/json',
        ];
        // \Log::info('GHN getServiceId request', [
        //     'headers' => $headers,
        //     'body' => $body
        // ]);
        // \Log::info('GHN getServiceId body types', [
        //     'shop_id' => [gettype($body['shop_id']), $body['shop_id']],
        //     'from_district' => [gettype($body['from_district']), $body['from_district']],
        //     'to_district' => [gettype($body['to_district']), $body['to_district']],
        //     'weight' => [gettype($body['weight']), $body['weight']]
        // ]);
        $response = Http::withHeaders($headers)
            ->post($this->baseUrl . '/v2/shipping-order/available-services', $body);
        // \Log::info('GHN getServiceId response', [
        //     'toDistrictId' => $toDistrictId,
        //     'serviceType' => $serviceType,
        //     'response' => $response->json()
        // ]);
        if ($response->successful() && isset($response['data'])) {
            // Lấy service_id đầu tiên hoặc theo loại dịch vụ
            $service = collect($response['data'])->first();
            $serviceId = $service['service_id'] ?? null;
            
            // Debug: Log getServiceId result (đã comment)
            // \Log::info('GHN getServiceId result', [
            //     'toDistrictId' => $toDistrictId,
            //     'serviceId' => $serviceId,
            //     'availableServices' => count($response['data'])
            // ]);
            
            return $serviceId;
        }
        
        \Log::error('GHN getServiceId failed', [
            'toDistrictId' => $toDistrictId,
            'response' => $response->json()
        ]);
        return null;
    }
} 