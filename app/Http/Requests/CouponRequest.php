<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện yêu cầu này hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nhận các quy tắc xác thực áp dụng cho yêu cầu.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id;

        return [
            'code' => $this->getCodeRules($couponId),
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => $this->getStatusRules(),
            'is_public' => 'boolean',
        ];
    }



    /**
     * Nhận các quy tắc xác thực mã dựa trên loại hoạt động
     */
    protected function getCodeRules($couponId = null): string|array
    {
        if ($couponId) {
            // Đối với hoạt động cập nhật
            return ['required', 'string', 'max:20', Rule::unique('coupons')->ignore($couponId)];
        }
        return 'required|string|unique:coupons,code|max:20';
    }

    /**
     * Nhận các quy tắc xác thực trạng thái dựa trên loại hoạt động
     */
    protected function getStatusRules(): string
    {
        $couponId = $this->route('coupon')?->id;
        
        if ($couponId) {
            // Đối với hoạt động cập nhật, cho phép trạng thái đã hết hạn
            return 'required|in:active,inactive,expired';
        }

        return 'required|in:active,inactive';
    }

    /**
     * Nhận các thông điệp xác thực tùy chỉnh
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã phiếu giảm giá là bắt buộc.',
            'code.unique' => 'Mã phiếu giảm giá này đã tồn tại.',
            'code.max' => 'Mã phiếu giảm giá không được vượt quá 20 ký tự.',
            'type.required' => 'Loại phiếu giảm giá là bắt buộc.',
            'type.in' => 'Loại phiếu giảm giá không hợp lệ.',
            'value.required' => 'Giá trị phiếu giảm giá là bắt buộc.',
            'value.numeric' => 'Giá trị phiếu giảm giá phải là số.',
            'value.min' => 'Giá trị phiếu giảm giá phải lớn hơn hoặc bằng 0.',
            'max_uses.integer' => 'Số lượt sử dụng tối đa phải là số nguyên.',
            'max_uses.min' => 'Số lượt sử dụng tối đa phải lớn hơn 0.',
            'max_uses_per_user.integer' => 'Số lượt sử dụng tối đa mỗi người phải là số nguyên.',
            'max_uses_per_user.min' => 'Số lượt sử dụng tối đa mỗi người phải lớn hơn 0.',
            'min_order_amount.numeric' => 'Số tiền đơn hàng tối thiểu phải là số.',
            'min_order_amount.min' => 'Số tiền đơn hàng tối thiểu phải lớn hơn hoặc bằng 0.',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng.',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }

    /**
     * Cấu hình phiên bản trình xác thực để xác thực bổ sung
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Kiểm tra xem giá trị phần trăm có lớn hơn 100 hay không
            if ($this->type == 'percentage' && $this->value > 100) {
                $validator->errors()->add('value', 'Phần trăm chiết khấu không được vượt quá 100%');
            }
        });
    }
}
