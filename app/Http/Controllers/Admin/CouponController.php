<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code|max:20',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->type == 'percentage' && $request->value > 100) {
            return redirect()->back()
                ->withErrors(['value' => 'Phần trăm chiết khấu không được vượt quá 100%'])
                ->withInput();
        }

        $coupon = new Coupon($request->all());
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
    public function update(Request $request, Coupon $coupon)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:20', Rule::unique('coupons')->ignore($coupon->id)],
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive,expired',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->type == 'percentage' && $request->value > 100) {
            return redirect()->back()
                ->withErrors(['value' => 'Phần trăm chiết khấu không được vượt quá 100%'])
                ->withInput();
        }

        $coupon->update($request->all());

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
}
