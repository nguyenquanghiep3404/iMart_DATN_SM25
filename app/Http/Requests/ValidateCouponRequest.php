<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
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
        return [
            'code' => 'required|string|exists:coupons,code',
            'user_id' => 'nullable|exists:users,id',
            'order_amount' => 'required|numeric|min:0',
        ];
    }

    /**
     * Nhận các thông điệp xác thực tùy chỉnh
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã phiếu giảm giá là bắt buộc.',
            'code.exists' => 'Mã phiếu giảm giá không tồn tại.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'order_amount.required' => 'Số tiền đơn hàng là bắt buộc.',
            'order_amount.numeric' => 'Số tiền đơn hàng phải là số.',
            'order_amount.min' => 'Số tiền đơn hàng phải lớn hơn hoặc bằng 0.',
        ];
    }
} 