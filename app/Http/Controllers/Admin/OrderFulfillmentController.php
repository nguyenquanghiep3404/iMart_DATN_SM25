<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFulfillmentCheckService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderFulfillmentController extends Controller
{
    protected $fulfillmentCheckService;
    
    public function __construct()
    {
        $this->fulfillmentCheckService = new OrderFulfillmentCheckService();
    }
    
    /**
     * Kiểm tra trạng thái fulfillment của đơn hàng
     * 
     * @param Order $order
     * @return JsonResponse
     */
    public function checkFulfillmentStatus(Order $order): JsonResponse
    {
        try {
            $fulfillmentCheck = $this->fulfillmentCheckService->canAssignShipper($order);
            
            return response()->json([
                'success' => true,
                'data' => $fulfillmentCheck
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Lỗi khi kiểm tra trạng thái fulfillment:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể kiểm tra trạng thái fulfillment',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Tạo phiếu chuyển kho tự động cho đơn hàng
     * 
     * @param Order $order
     * @return JsonResponse
     */
    public function createAutoTransfer(Order $order): JsonResponse
    {
        try {
            $result = $this->fulfillmentCheckService->createAutoTransferIfNeeded($order);
            
            if ($result['created']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã tạo phiếu chuyển kho tự động thành công',
                    'data' => [
                        'transfers' => $result['transfers'],
                        'reason' => $result['reason']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['reason']
                ], 422);
            }
            
        } catch (\Exception $e) {
            \Log::error('Lỗi khi tạo phiếu chuyển kho tự động:', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo phiếu chuyển kho tự động',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Lấy danh sách đơn hàng đang chờ hàng về kho
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrdersAwaitingStock(Request $request): JsonResponse
    {
        try {
            $query = Order::with(['items.productVariant', 'customer'])
                ->where('status', Order::STATUS_AWAITING_SHIPMENT);
                
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('order_code', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%");
                });
            }
            
            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));
                
            $ordersWithStatus = $orders->getCollection()->map(function($order) {
                $fulfillmentCheck = $this->fulfillmentCheckService->canAssignShipper($order);
                
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at,
                    'fulfillment_status' => $fulfillmentCheck,
                    'items_count' => $order->items->count()
                ];
            });
            
            $orders->setCollection($ordersWithStatus);
            
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Lỗi khi lấy danh sách đơn hàng chờ hàng về kho:', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách đơn hàng',
                'error' => app()->isLocal() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}