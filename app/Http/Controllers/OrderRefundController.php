<?php

namespace App\Http\Controllers;

use App\Models\ReturnRequest;
use App\Models\ReturnItem;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\ActivityLog;

class OrderRefundController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order.user']);

        if ($request->filled('search')) {
            $query->where('return_code', 'like', "%{$request->search}%")
                ->orWhereHas('order', fn($q) =>
                $q->where('order_code', 'like', "%{$request->search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returnRequests = $query->latest()->paginate(10)->withQueryString();

        return view('admin.refunds.index', compact('returnRequests'));
    }
    public function indexuser(Request $request)
    {
        $user = Auth::user();

        $refunds = ReturnRequest::with([
            'order',
            'returnItems.orderItem.variant.product.coverImage'
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('users.refunds.index', compact('refunds'));
    }
    public function show($id)
    {
        $returnRequest = ReturnRequest::with([
            'returnItems.orderItem.variant.product',
            'files',
            'order.user',
            'refundProcessor'
        ])->findOrFail($id);

        return view('admin.refunds.show', compact('returnRequest'));
    }
    public function showuser($code)
    {
        // Tìm yêu cầu trả hàng theo return_code, đảm bảo là của user hiện tại
        $returnRequest = ReturnRequest::with([
            'returnItems.orderItem.variant.product',
            'files',
            'order.user',
            'refundProcessor',
            'logs'
        ])->where('id', $code)
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->firstOrFail();

        return view('users.refunds.show', compact('returnRequest'));
    }

    public function updateNote(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000'
        ]);

        $returnRequest = ReturnRequest::findOrFail($id);
        $returnRequest->admin_note = $request->admin_note;
        $returnRequest->save();

        return back()->with('success', 'Đã lưu ghi chú nội bộ.');
    }
    public function confirmRefund($id)
    {
        $returnRequest = ReturnRequest::findOrFail($id);

        // Xử lý logic hoàn tiền, ví dụ: chuyển khoản, tặng điểm, mã giảm giá...

        $returnRequest->update([
            'refund_processed_by' => Auth::id(),
            'refunded_at' => now(),
            'status' => 'refunded',
        ]);

        // (Tuỳ chọn) Ghi vào log hoạt động
        $returnRequest->logs()->create([
            'action' => 'refunded',
            'performed_by' => Auth::id(),
            'description' => 'Admin ' . Auth::user()->name . ' đã hoàn tiền cho khách',
        ]);

        return redirect()->back()->with('success', 'Đã xác nhận hoàn tiền');
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'refund_method' => 'required|in:points,bank,coupon',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'reason_details' => 'nullable|string|max:1000',
            'media.*' => 'nullable|file|max:10240',
            'order_item_id' => 'required|exists:order_items,id',
        ]);

        if ($request->refund_method === 'bank') {
            $request->validate([
                'bank_name' => 'required|string|max:100',
                'bank_account_name' => 'required|string|max:100',
                'bank_account_number' => 'required|string|max:50',
            ]);
        }

        try {
            DB::beginTransaction();

            $orderItem = OrderItem::with('order')->findOrFail($request->order_item_id);
            $order = $orderItem->order;

            $returnCode = 'RR' . strtoupper(Str::random(8));
            $refundAmount = $orderItem->price * $validated['quantity'];

            $returnRequest = ReturnRequest::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'return_code' => $returnCode,
                'reason' => $validated['reason'],
                'reason_details' => $validated['reason_details'] ?? null,
                'refund_method' => $validated['refund_method'],
                'status' => 'pending',
                'refund_amount' => $refundAmount,
                'refunded_points' => $validated['refund_method'] === 'points' ? floor($refundAmount / 1000) : null,
                'bank_name' => $request->bank_name,
                'bank_account_name' => $request->bank_account_name,
                'bank_account_number' => $request->bank_account_number,
            ]);

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('refunds', 'public');

                    $returnRequest->files()->create([
                        'path' => $path,
                        'filename' => $file->hashName(),
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'disk' => 'public',
                        'type' => 'return_media',
                    ]);
                }
            }

            ReturnItem::create([
                'return_request_id' => $returnRequest->id,
                'order_item_id' => $orderItem->id,
                'quantity' => $validated['quantity'],
            ]);

            DB::commit();
            ActivityLog::create([
                'log_name'     => 'return_request',
                'description'  => 'Bạn đã gửi yêu cầu trả hàng #' . $returnCode . ' thành công',
                'subject_type' => ReturnRequest::class,
                'subject_id'   => $returnRequest->id,
                'causer_type'  => get_class(Auth::user()),
                'causer_id'    => Auth::id(),
            ]);


            return redirect()->route('orders.returns')
                ->with('success', 'Yêu cầu hoàn tiền đã được gửi.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Refund error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
    public function create(OrderItem $orderItem)
    {
        // Lấy đơn hàng liên kết
        $order = $orderItem->order;

        // Kiểm tra quyền của người dùng (chỉ chủ đơn mới được tạo yêu cầu hoàn)
        if (auth()->id() !== $order->user_id) {
            abort(403);
        }

        // Lấy toàn bộ sản phẩm trong đơn
        $orderItems = $order->items()->with(['variant.product.coverImage'])->get();

        // Truyền toàn bộ orderItems thay vì chỉ 1 item
        return view('users.refunds.create', [
            'orderItems' => $orderItems,
            'order' => $order
        ]);
    }



    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,processing,refunded,rejected,completed',
            'refund_amount' => 'nullable|numeric',
            'rejection_reason' => 'nullable|string|max:1000'
        ]);

        $statusMap = [
            'pending'    => 'Chờ xử lý',
            'approved'   => 'Đã duyệt',
            'processing' => 'Đang xử lý',
            'refunded'   => 'Đã hoàn tiền',
            'rejected'   => 'Bị từ chối',
            'completed'  => 'Hoàn tất',
        ];


        $returnRequest = ReturnRequest::findOrFail($id);
        $returnRequest->status = $request->status;

        // Nếu từ chối → ghi chú lý do
        if ($request->status === 'rejected' && $request->filled('rejection_reason')) {
            $returnRequest->rejection_reason = $request->rejection_reason;
            $returnRequest->admin_note = 'Lý do từ chối: ' . $request->rejection_reason;
        }

        if ($request->status === 'completed') {
            $returnRequest->refund_processed_by = Auth::id();
            $returnRequest->refunded_at = now();

            if ($request->filled('refund_amount')) {
                $returnRequest->refund_amount = $request->refund_amount;
            }

            $user = $returnRequest->user;

            // Hoàn điểm thưởng
            if ($returnRequest->refund_method === 'points' && $returnRequest->refunded_points > 0) {
                $user->increment('loyalty_points_balance', $returnRequest->refunded_points);
            }

            // Tạo mã giảm giá riêng cho user
            if ($returnRequest->refund_method === 'coupon') {
                $code = 'REFUND-' . strtoupper(Str::random(6));

                $coupon = \App\Models\Coupon::create([
                    'code' => $code,
                    'description' => 'Mã hoàn tiền cho đơn trả hàng #' . $returnRequest->return_code,
                    'type' => 'fixed_amount',
                    'value' => $returnRequest->refund_amount,
                    'max_discount_amount' => $returnRequest->refund_amount,
                    'max_uses' => 1,
                    'max_uses_per_user' => 1,
                    'min_order_amount' => 0,
                    'start_date' => now(),
                    'status' => 'active',
                    'is_public' => 0,
                    'user_id' => $user->id,
                    'created_by' => Auth::id(),
                ]);
            }
            \Mail::to($user->email)->send(new \App\Mail\RefundCompletedNotification($returnRequest));
        }

        $returnRequest->save();


        ActivityLog::create([
            'log_name'     => 'return_request',
            'description'  => Auth::user()->name . ' đã cập nhật trạng thái: ' . ($statusMap[$request->status] ?? $request->status),
            'subject_type' => ReturnRequest::class,
            'subject_id'   => $returnRequest->id,
            'causer_type'  => get_class(Auth::user()),
            'causer_id'    => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công.'
        ]);
    }
}
