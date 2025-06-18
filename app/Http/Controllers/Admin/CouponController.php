<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Http\Requests\ValidateCouponRequest;

class CouponController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Coupon::with('createdBy');

        // Áp dụng các bộ lọc
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $query->orderByDesc('id');
        $coupons = $query->paginate(10);
        return view('admin.coupons.index', compact('coupons'));
    }
    /**
     * .
     */
    public function create()
    {
        return view('admin.coupons.create');
    }
    /**
     * .
     */
    public function store(CouponRequest $request)
    {
        $coupon = new Coupon($request->validated());
        $coupon->created_by = auth()->id();
        $coupon->save();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Phiếu giảm giá đã được tạo thành công.');
    }
    /**
     * .
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * .
     */
    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Phiếu giảm giá đã được cập nhật thành công.');
    }
    public function destroy(Coupon $coupon)
    {
        try {
            DB::beginTransaction();

            // xóa các bản ghi sử dụng liên quan
            $coupon->usages()->delete();

            // xóa phiếu giảm giá
            $coupon->delete();

            DB::commit();

            return redirect()->route('admin.coupons.index')
                ->with('success', 'Đã xóa phiếu giảm giá thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.coupons.index')
                ->with('error', 'Error deleting coupon: ' . $e->getMessage());
        }
    }
    public function show(Coupon $coupon)
    {
        //  thông tin sử dụng phiếu giảm giá với người dùng và đơn hàng
        $coupon->load(['usages.user', 'usages.order', 'createdBy']);

        // Nhận số liệu thống kê sử dụng
        $totalUsages = $coupon->usages->count();
        $usagesByUser = $coupon->usages->groupBy('user_id')->map->count();

        return view('admin.coupons.show', compact('coupon', 'totalUsages', 'usagesByUser'));
    }
    public function changeStatus(Coupon $coupon, $status)
    {
        if (!in_array($status, ['active', 'inactive', 'expired'])) {
            return redirect()->back()->with('error', 'Trạng thái không hợp lệ.');
        }

        $coupon->status = $status;
        $coupon->save();

        return redirect()->back()->with('success', "Trạng thái phiếu giảm giá đã được thay đổi thành {$status}.");
    }

    /**
     * Hiển thị lịch sử sử dụng mã giảm giá cụ thể.
     */
    public function usageHistory(Coupon $coupon)
    {
        $query = CouponUsage::with(['user', 'order'])
            ->where('coupon_id', $coupon->id);

        // Áp dụng sắp xếp nếu được yêu cầu
        if (request('sort') == 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif (request('sort') == 'highest_amount') {
            $query->join('orders', 'coupon_usages.order_id', '=', 'orders.id')
                ->orderBy('orders.discount_amount', 'desc')
                ->select('coupon_usages.*');
        } elseif (request('sort') == 'lowest_amount') {
            $query->join('orders', 'coupon_usages.order_id', '=', 'orders.id')
                ->orderBy('orders.discount_amount', 'asc')
                ->select('coupon_usages.*');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $usages = $query->paginate(15);

        // Tính tổng số tiền tiết kiệm tổng số tiền giảm giá từ các đơn hàng
        $totalSavings = $coupon->usages()
            ->join('orders', 'coupon_usages.order_id', '=', 'orders.id')
            ->sum('orders.discount_amount');

        // Tính tổng giá trị đơn hàng
        $totalOrderValue = $coupon->usages()
            ->join('orders', 'coupon_usages.order_id', '=', 'orders.id')
            ->sum('orders.grand_total');

        return view('admin.coupons.usage-history', compact('coupon', 'usages', 'totalSavings', 'totalOrderValue'));
    }

    /**
     * Xác thực mã phiếu giảm giá
     */
    public function validateCoupon(ValidateCouponRequest $request)
    {
        $code = $request->code;
        $userId = $request->user_id;
        $orderAmount = $request->order_amount;

        $coupon = Coupon::where('code', $code)->first();

        // Kiểm tra xem phiếu giảm giá có tồn tại không
        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code.'
            ]);
        }

        // Kiểm tra trạng thái phiếu giảm giá
        if ($coupon->status !== 'active') {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon is ' . $coupon->status . '.'
            ]);
        }

        // Kiểm tra ngày
        $now = now();
        if ($coupon->start_date && $now < $coupon->start_date) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon is not valid yet.'
            ]);
        }

        if ($coupon->end_date && $now > $coupon->end_date) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon has expired.'
            ]);
        }

        // Kiểm tra số tiền đơn hàng tối thiểu
        if ($coupon->min_order_amount && $orderAmount < $coupon->min_order_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Số tiền đơn hàng không đạt yêu cầu tối thiểu là ' . number_format($coupon->min_order_amount) . ' VND.'
            ]);
        }

        // Kiểm tra số lượt sử dụng tối đa
        if ($coupon->max_uses && $coupon->usages()->count() >= $coupon->max_uses) {
            return response()->json([
                'valid' => false,
                'message' => 'Phiếu giảm giá này đã đạt số lượt sử dụng tối đa.'
            ]);
        }

        // Kiểm tra số lượt sử dụng tối đa theo người dùng
        if ($userId && $coupon->max_uses_per_user) {
            $userUsages = $coupon->usages()->where('user_id', $userId)->count();
            if ($userUsages >= $coupon->max_uses_per_user) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Bạn đã sử dụng phiếu giảm giá này đủ số lần tối đa.'
                ]);
            }
        }

        // Tính toán số tiền giảm giá
        $discountAmount = 0;
        if ($coupon->type === 'percentage') {
            $discountAmount = ($orderAmount * $coupon->value) / 100;
        } else {
            $discountAmount = $coupon->value;
        }

        return response()->json([
            'valid' => true,
            'message' => 'Phiếu giảm giá hợp lệ.',
            'discount_amount' => $discountAmount,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ]
        ]);
    }
}
