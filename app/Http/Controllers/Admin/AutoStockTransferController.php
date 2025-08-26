<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\Order;
use App\Services\AutoStockTransferService;
use App\Services\StockTransferWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các phiếu chuyển kho tự động
 */
class AutoStockTransferController extends Controller
{
    protected $autoTransferService;

    public function __construct(AutoStockTransferService $autoTransferService)
    {
        $this->autoTransferService = $autoTransferService;
    }

    /**
     * Hiển thị giao diện quản lý phiếu chuyển kho tự động
     */
    public function manage()
    {
        return view('admin.auto-stock-transfers.index');
    }

    /**
     * Lấy danh sách các phiếu chuyển kho tự động (API)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                })
                ->with(['fromLocation', 'toLocation', 'items.productVariant.product'])
                ->orderBy('created_at', 'desc');

            // Lọc theo trạng thái
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Lọc theo ngày tạo
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $transfers = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $transfers,
                'message' => 'Lấy danh sách phiếu chuyển kho tự động thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy danh sách phiếu chuyển kho tự động: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách phiếu chuyển kho tự động'
            ], 500);
        }
    }

    /**
     * Hiển thị trang chi tiết phiếu chuyển kho tự động
     */
    public function detail(string $id)
    {
        try {
            $transfer = StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                })
                ->with([
                    'fromLocation',
                    'toLocation',
                    'items.productVariant.product',
                    'items.serials.inventorySerial',
                    'createdBy'
                ])
                ->findOrFail($id);

            return view('admin.auto-stock-transfers.detail', compact('transfer'));

        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết phiếu chuyển kho: ' . $e->getMessage());
            return redirect()->route('admin.auto-stock-transfers.manage')
                ->with('error', 'Không tìm thấy phiếu chuyển kho');
        }
    }

    /**
     * Xem chi tiết phiếu chuyển kho tự động (API)
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transfer = StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                })
                ->with([
                    'fromLocation',
                    'toLocation',
                    'items.productVariant.product',
                    'items.serials.inventorySerial',
                    'createdBy'
                ])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $transfer,
                'message' => 'Lấy chi tiết phiếu chuyển kho thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy chi tiết phiếu chuyển kho: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu chuyển kho'
            ], 404);
        }
    }

    /**
     * Tự động xử lý phiếu chuyển kho (xuất và nhận)
     */
    public function autoProcess(string $id): JsonResponse
    {
        try {
            $transfer = StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                })
                ->findOrFail($id);

            if ($transfer->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Phiếu chuyển kho không ở trạng thái chờ xử lý'
                ], 400);
            }

            // Sử dụng StockTransferWorkflowService để xử lý workflow hoàn chỉnh
            $workflowService = new StockTransferWorkflowService();
            
            // Kiểm tra xem có thể tự động xử lý không
            if (!$workflowService->canAutoProcess($transfer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phiếu chuyển kho này không thể tự động xử lý (thiếu thông tin kho)'
                ], 400);
            }
            
            $result = $workflowService->processTransferWorkflow($transfer);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tự động xử lý phiếu chuyển kho: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tự động xử lý phiếu chuyển kho'
            ], 500);
        }
    }

    /**
     * Kiểm tra và tạo phiếu chuyển kho tự động cho đơn hàng
     */
    public function checkAndCreateForOrder(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id'
            ]);

            $order = Order::with('items')->findOrFail($request->order_id);

            $result = $this->autoTransferService->checkAndCreateAutoTransfer($order);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra và tạo phiếu chuyển kho: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra và tạo phiếu chuyển kho'
            ], 500);
        }
    }

    /**
     * Lấy thống kê phiếu chuyển kho tự động
     */
    public function statistics(): JsonResponse
    {
        try {
            $baseQuery = function() {
                return StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                });
            };
            
            $stats = [
                'total' => $baseQuery()->count(),
                'pending' => $baseQuery()->where('status', 'pending')->count(),
                'shipped' => $baseQuery()->where('status', 'shipped')->count(),
                'received' => $baseQuery()->where('status', 'received')->count(),
                'today' => $baseQuery()->whereDate('created_at', today())->count(),
                'this_week' => $baseQuery()
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'this_month' => $baseQuery()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Lấy thống kê thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thống kê phiếu chuyển kho: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thống kê'
            ], 500);
        }
    }

    /**
     * Hủy phiếu chuyển kho tự động (chỉ khi ở trạng thái pending)
     */
    public function cancel(string $id): JsonResponse
    {
        try {
            $transfer = StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                })
                ->findOrFail($id);

            if (!in_array($transfer->status, ['pending', 'dispatched', 'received'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy phiếu chuyển kho ở trạng thái này'
                ], 400);
            }

            // Sử dụng StockTransferWorkflowService để hủy và hoàn tồn kho
            $workflowService = new StockTransferWorkflowService();
            $result = $workflowService->cancelTransfer($transfer);

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            Log::info("Đã hủy phiếu chuyển kho tự động: {$transfer->transfer_code}");

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy phiếu chuyển kho thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi hủy phiếu chuyển kho: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi hủy phiếu chuyển kho'
            ], 500);
        }
    }

    /**
     * Nhận hàng ngay lập tức (chỉ khi ở trạng thái phù hợp)
     */
    public function receive(string $id): JsonResponse
    {
        try {
            $transfer = StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                })
                ->findOrFail($id);

            // Kiểm tra trạng thái phù hợp để nhận hàng
            $allowedStatuses = ['in_transit', 'dispatched', 'shipped'];
            if (!in_array($transfer->status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => "Chỉ có thể nhận hàng khi phiếu ở trạng thái: " . implode(', ', $allowedStatuses) . ". Trạng thái hiện tại: {$transfer->status}"
                ], 400);
            }

            // Sử dụng StockTransferWorkflowService để nhận hàng
            $workflowService = new StockTransferWorkflowService();
            $result = $workflowService->receiveTransfer($transfer);

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            Log::info("Đã nhận hàng ngay lập tức cho phiếu chuyển kho: {$transfer->transfer_code}");

            return response()->json([
                'success' => true,
                'message' => 'Đã xác nhận nhận hàng thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi nhận hàng: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xác nhận nhận hàng'
            ], 500);
        }
    }

    /**
     * Lưu IMEI/Serial cho sản phẩm trong phiếu chuyển kho
     */
    public function saveImei(Request $request, $id): JsonResponse
    {
        try {
            $transfer = StockTransfer::where(function($q) {
                    $q->where('transfer_code', 'LIKE', 'AUTO-%')
                      ->orWhere('transfer_code', 'LIKE', 'FULFILL-%');
                })
                ->with('items')
                ->findOrFail($id);

            $request->validate([
                'product_variant_id' => 'required|integer',
                'imei_serials' => 'required|array',
                'imei_serials.*' => 'required|string'
            ]);

            // Tìm item trong phiếu chuyển kho
            $transferItem = $transfer->items()->where('product_variant_id', $request->product_variant_id)->first();
            
            if (!$transferItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy sản phẩm trong phiếu chuyển kho'
                ], 404);
            }

            // Kiểm tra sản phẩm có yêu cầu quản lý IMEI/Serial không
            $productVariant = $transferItem->productVariant;
            if (!$productVariant->has_serial_tracking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này không yêu cầu quản lý IMEI/Serial'
                ], 400);
            }

            // Kiểm tra số lượng IMEI/Serial có khớp với số lượng yêu cầu
            if (count($request->imei_serials) !== $transferItem->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Số lượng IMEI/Serial (" . count($request->imei_serials) . ") không khớp với số lượng yêu cầu ({$transferItem->quantity})"
                ], 400);
            }

            // Kiểm tra trùng lặp IMEI/Serial trong request
            $uniqueImeis = array_unique($request->imei_serials);
            if (count($uniqueImeis) !== count($request->imei_serials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có IMEI/Serial trùng lặp trong danh sách'
                ], 400);
            }

            // Validate từng IMEI/Serial
            foreach ($request->imei_serials as $imei) {
                $serial = \App\Models\InventorySerial::where('serial_number', $imei)
                    ->where('product_variant_id', $request->product_variant_id)
                    ->first();

                if (!$serial) {
                    return response()->json([
                        'success' => false,
                        'message' => "IMEI/Serial '{$imei}' không tồn tại cho sản phẩm này"
                    ], 400);
                }

                if ($serial->status !== 'available') {
                    return response()->json([
                        'success' => false,
                        'message' => "IMEI/Serial '{$imei}' đã được sử dụng hoặc không khả dụng (trạng thái: {$serial->status})"
                    ], 400);
                }

                // Kiểm tra IMEI có đang ở kho nguồn không (nếu cần)
                if ($serial->store_location_id !== $transfer->from_location_id) {
                    return response()->json([
                        'success' => false,
                        'message' => "IMEI/Serial '{$imei}' không có trong kho nguồn"
                    ], 400);
                }
            }

            // Lưu IMEI/Serial vào cột imei_serials dưới dạng JSON
            $transferItem->imei_serials = json_encode($request->imei_serials);
            $transferItem->save();

            Log::info("Đã lưu IMEI/Serial cho sản phẩm {$request->product_variant_id} trong phiếu chuyển kho {$transfer->transfer_code}");

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu IMEI/Serial thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi lưu IMEI/Serial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu IMEI/Serial'
            ], 500);
        }
    }
}