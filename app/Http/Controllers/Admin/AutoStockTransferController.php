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
            $query = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
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
     * Xem chi tiết phiếu chuyển kho tự động
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transfer = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
                ->with([
                    'fromLocation',
                    'toLocation',
                    'items.productVariant.product',
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
            $transfer = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
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
                    'message' => 'Phiếu chuyển kho này không thể tự động xử lý (khác tỉnh thành)'
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
            $stats = [
                'total' => StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')->count(),
                'pending' => StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
                    ->where('status', 'pending')->count(),
                'shipped' => StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
                    ->where('status', 'shipped')->count(),
                'received' => StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
                    ->where('status', 'received')->count(),
                'today' => StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
                    ->whereDate('created_at', today())->count(),
                'this_week' => StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'this_month' => StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
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
            $transfer = StockTransfer::where('transfer_code', 'LIKE', 'AUTO-%')
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
}