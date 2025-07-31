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
            'refundProcessor'
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

        // Validate thêm nếu là chuyển khoản
        if ($request->refund_method === 'bank') {
            $request->validate([
                'bank_name' => 'required|string|max:100',
                'bank_account_name' => 'required|string|max:100',
                'bank_account_number' => 'required|string|max:50',
            ]);
        }

        try {
            DB::beginTransaction();

            // Lấy thông tin order từ order_item
            $orderItem = OrderItem::with('order')->findOrFail($request->order_item_id);
            $order = $orderItem->order;

            // Tạo mã return_code
            $returnCode = 'RR' . strtoupper(Str::random(8));

            // Tính số tiền hoàn: đơn giản lấy theo giá x số lượng
            $refundAmount = $orderItem->price * $validated['quantity'];

            // Tạo return request
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
            // Upload media nếu có
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


            // Tạo return item
            ReturnItem::create([
                'return_request_id' => $returnRequest->id,
                'order_item_id' => $orderItem->id,
                'quantity' => $validated['quantity'],
                'condition' => null, // để admin điền sau
                'resolution' => null, // để admin điền sau
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Yêu cầu hoàn tiền đã được gửi.'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Refund error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,processing,refunded,rejected,completed',
            'refund_amount' => 'nullable|numeric',
            'rejection_reason' => 'nullable|string|max:1000'
        ]);

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

        // (Tùy chọn) Ghi log hoạt động
        if (method_exists($returnRequest, 'logs')) {
            $returnRequest->logs()->create([
                'action' => $request->status,
                'performed_by' => Auth::id(),
                'description' => 'Admin ' . Auth::user()->name . ' cập nhật trạng thái thành ' . $request->status
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công.'
        ]);
    }
}
