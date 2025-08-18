<?php

namespace App\Http\Requests;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
            'value' => $this->getValueRules(),
            'max_discount_amount' => 'nullable|numeric|min:1000',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'start_date' => $this->getStartDateRules($couponId),
            'end_date' => $this->getEndDateRules($couponId),
            'status' => $this->getStatusRules(),
            'is_public' => 'boolean',
        ];
    }
    /**
     * Nhận các quy tắc xác thực cho giá trị giảm
     */
    protected function getValueRules(): string|array
    {
        $rules = ['required', 'numeric'];
        // Nếu type là percentage, thêm max:100 và min:0
        if ($this->input('type') === 'percentage') {
            $rules[] = 'min:0';
            $rules[] = 'max:100';
        }
        // Nếu type là fixed_amount, thêm min:1000
        elseif ($this->input('type') === 'fixed_amount') {
            $rules[] = 'min:1000';
        }
        else {
            // Mặc định min:0 nếu chưa chọn type
            $rules[] = 'min:0';
        }
        return $rules;
    }

    /**
     * Nhận các quy tắc xác thực cho ngày bắt đầu
     */
    protected function getStartDateRules($couponId = null): string|array
    {
        if ($couponId) {
            // Đối với hoạt động cập nhật - cho phép ngày quá khứ nhưng vẫn bắt buộc
            return 'required|date';
        }
        
        // Đối với hoạt động tạo mới - ngày bắt đầu bắt buộc và không được là quá khứ
        return 'required|date|after_or_equal:today';
    }

    /**
     * Nhận các quy tắc xác thực cho ngày kết thúc
     */
    protected function getEndDateRules($couponId = null): string|array
    {
        if ($couponId) {
            // Đối với hoạt động cập nhật - bắt buộc và phải sau ngày bắt đầu (không bằng)
            return 'required|date|after:start_date';
        }
        
        // Đối với hoạt động tạo mới - bắt buộc, phải sau thời điểm hiện tại và sau ngày bắt đầu
        return 'required|date|after:now|after_or_equal:start_date';
    }

    /**
     * Nhận các quy tắc xác thực mã dựa trên loại hoạt động
     */
    protected function getCodeRules($couponId = null): string|array
    {
        if ($couponId) {
            // Đối với hoạt động cập nhật
            return ['required', 'string', 'min:6', 'max:20', Rule::unique('coupons')->ignore($couponId)];
        }
        return 'required|string|min:6|max:20|unique:coupons,code';
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
        $couponId = $this->route('coupon')?->id;
        
        $messages = [
            'code.required' => 'Mã phiếu giảm giá là bắt buộc.',
            'code.unique' => 'Mã phiếu giảm giá này đã tồn tại.',
            'code.min' => 'Mã phiếu giảm giá phải có ít nhất 6 ký tự.',
            'code.max' => 'Mã phiếu giảm giá không được vượt quá 20 ký tự.',
            'type.required' => 'Loại phiếu giảm giá là bắt buộc.',
            'type.in' => 'Loại phiếu giảm giá không hợp lệ.',
            'value.required' => 'Giá trị phiếu giảm giá là bắt buộc.',
            'value.numeric' => 'Giá trị phiếu giảm giá phải là số.',
            'value.min' => $this->input('type') === 'fixed_amount' 
                ? 'Số tiền giảm phải ít nhất 1.000 VND.' 
                : 'Giá trị phiếu giảm giá phải lớn hơn hoặc bằng 0.',
            'value.max' => 'Phần trăm giảm giá không được vượt quá 100%.',
            'max_discount_amount.numeric' => 'Số tiền giảm tối đa phải là số.',
            'max_discount_amount.min' => 'Số tiền giảm tối đa phải ít nhất 1.000 VND.',
            'max_uses.integer' => 'Số lượt sử dụng tối đa phải là số nguyên.',
            'max_uses.min' => 'Số lượt sử dụng tối đa phải lớn hơn 0.',
            'max_uses_per_user.integer' => 'Số lượt sử dụng tối đa mỗi người phải là số nguyên.',
            'max_uses_per_user.min' => 'Số lượt sử dụng tối đa mỗi người phải lớn hơn 0.',
            'min_order_amount.numeric' => 'Số tiền đơn hàng tối thiểu phải là số.',
            'min_order_amount.min' => 'Số tiền đơn hàng tối thiểu phải lớn hơn hoặc bằng 0.',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng.',
            'start_date.after_or_equal' => 'Ngày bắt đầu không được là quá khứ.',
            'end_date.required' => 'Ngày kết thúc là bắt buộc.',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];

        // Messages khác nhau cho create và edit
        if ($couponId) {
            // Edit mode
            $messages['end_date.after'] = 'Ngày kết thúc phải sau ngày bắt đầu.';
            
            // Kiểm tra xem mã đã hết hạn chưa
            $existingCoupon = \App\Models\Coupon::find($couponId);
            if ($existingCoupon && $existingCoupon->end_date && $existingCoupon->end_date->isPast()) {
                $messages['end_date.custom'] = 'Mã giảm giá này đã hết hạn vào ngày ' . $existingCoupon->end_date->format('d/m/Y H:i') . '. Không thể chỉnh sửa ngày kết thúc.';
            }
        } else {
            // Create mode  
            $messages['end_date.after'] = 'Ngày kết thúc phải sau thời điểm hiện tại.';
            $messages['end_date.after_or_equal'] = 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.';
        }

        return $messages;
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

            // Kiểm tra max_discount_amount chỉ áp dụng cho mã giảm theo phần trăm
            if ($this->max_discount_amount && $this->type !== 'percentage') {
                $validator->errors()->add('max_discount_amount', 'Số tiền giảm tối đa chỉ áp dụng cho mã giảm theo phần trăm.');
            }

            // Validation bổ sung cho ngày
            $couponId = $this->route('coupon')?->id;
            
            if ($couponId) {
                // Đối với hoạt động cập nhật - kiểm tra mã đã hết hạn chưa
                $existingCoupon = \App\Models\Coupon::find($couponId);
                
                if ($existingCoupon && $existingCoupon->end_date && $existingCoupon->end_date->isPast()) {
                    // Mã đã hết hạn - không cho phép chỉnh sửa end_date
                    if ($this->end_date !== $existingCoupon->end_date->format('Y-m-d\TH:i')) {
                        $validator->errors()->add('end_date', 'Không thể chỉnh sửa ngày kết thúc của mã giảm giá đã hết hạn.');
                    }
                }
            } else {
                // Đối với hoạt động tạo mới - kiểm tra ngày kết thúc không được trùng với ngày bắt đầu
                if ($this->start_date && $this->end_date) {
                    $startDate = Carbon::parse($this->start_date);
                    $endDate = Carbon::parse($this->end_date);
                    
                    if ($endDate->equalTo($startDate)) {
                        $validator->errors()->add('end_date', 'Ngày kết thúc phải khác với ngày bắt đầu.');
                    }
                }
            }
        });
    }
}
