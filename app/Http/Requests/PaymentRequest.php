<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PaymentRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lấy các quy tắc validation áp dụng cho yêu cầu.
     */
    public function rules(): array
    {
        $rules = [
            // Phương thức giao hàng
            'delivery_method' => 'required|string|in:delivery,pickup',
            'payment_method' => 'required|string|in:cod,bank_transfer,vnpay,bank_transfer_qr,momo,qrcode',
            'notes' => 'nullable|string|max:1000',

            // Vận chuyển
            'shipping_method' => 'nullable|string',
            'shipping_fee' => 'nullable|integer|min:0',
            'shipping_time' => 'nullable|string',
        ];

        // VALIDATION PHƯƠNG THỨC GIAO HÀNG
        if ($this->input('delivery_method') === 'delivery') {
            // Kiểm tra xem có sử dụng địa chỉ đã lưu hay địa chỉ mới
            if ($this->input('address_id')) {
                // Sử dụng địa chỉ đã lưu - validation tối thiểu
                $rules['address_id'] = 'required|integer|exists:addresses,id|address_ownership';
                $rules['shipping_method'] = 'required|string';

                // Validation khung giờ giao hàng cho giao hàng tại cửa hàng
                $rules['delivery_date'] = 'nullable|date|after_or_equal:today';
                $rules['delivery_time_slot'] = 'nullable|string|in:8-11,11-14,14-17,17-20,20-22';
            } else {
                // Sử dụng form địa chỉ mới - validation đầy đủ
                $rules = array_merge($rules, [
                    'full_name' => [
                        'required',
                        'string',
                        'max:100',
                        'regex:/^[a-zA-ZÀ-ỹ\s]+$/' // Chỉ cho phép chữ cái và khoảng trắng
                    ],
                    'phone_number' => [
                        'required',
                        'string',
                        'regex:/^0[0-9]{9,10}$/' // Định dạng số điện thoại Việt Nam
                    ],
                    'phone' => [ // Cũng chấp nhận 'phone' để tương thích
                        'nullable',
                        'string',
                        'regex:/^0[0-9]{9,10}$/'
                    ],
                    'email' => 'required|email|max:255',
                    'province_code' => 'required|string',
                    'district_code' => 'nullable|string',
                    'ward_code' => 'required|string',
                    'address_line1' => 'required|string|min:5|max:500',
                    'address' => 'nullable|string|min:5|max:500', // Cũng chấp nhận 'address' để tương thích
                    'postcode' => 'nullable|string|max:10',
                    'save_address' => 'nullable|boolean',
                    'shipping_method' => 'required|string',

                    // Validation khung giờ giao hàng cho giao hàng tại cửa hàng
                    'delivery_date' => 'nullable|date|after_or_equal:today',
                    'delivery_time_slot' => 'nullable|string|in:8-11,11-14,14-17,17-20,20-22',
                ]);

                // Validation hệ thống địa chỉ (cũ vs mới)
                if ($this->input('address_system') === 'new') {
                    $rules['address_system'] = 'required|string|in:new,old';
                    $rules['province_code'] = 'required|string|exists:provinces_new,code';
                    $rules['ward_code'] = 'required|string|exists:wards_new,code';
                } else {
                    $rules['address_system'] = 'required|string|in:new,old';
                    $rules['province_code'] = 'required|string|exists:provinces_old,code';
                    $rules['district_code'] = 'required|string|exists:districts_old,code';
                    $rules['ward_code'] = 'required|string|exists:wards_old,code';
                }
            }
        }

        // VALIDATION PHƯƠNG THỨC NHẬN HÀNG TẠI CỬA HÀNG
        if ($this->input('delivery_method') === 'pickup') {
            $rules = array_merge($rules, [
                'pickup_full_name' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-zA-ZÀ-ỹ\s]+$/' // Chỉ cho phép chữ cái và khoảng trắng
                ],
                'pickup_phone_number' => [
                    'required',
                    'string',
                    'regex:/^0[0-9]{9,10}$/' // Định dạng số điện thoại Việt Nam
                ],
                'pickup_email' => 'required|email|max:255',
                'store_location_id' => 'required|integer|exists:store_locations,id',
                'pickup_date' => 'nullable|date|after_or_equal:today',
                'pickup_time_slot' => 'nullable|string|in:8-11,11-14,14-17,17-20,20-22',
                'shipping_method' => 'nullable|string', // Cho phép null khi pickup

                // Loại trừ các trường liên quan đến giao hàng khỏi validation khi sử dụng phương thức pickup
                'address_id' => 'nullable|integer',
                'full_name' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'phone' => 'nullable|string',
                'email' => 'nullable|string',
                'province_code' => 'nullable|string',
                'district_code' => 'nullable|string',
                'ward_code' => 'nullable|string',
                'address_line1' => 'nullable|string',
                'address' => 'nullable|string',
                'postcode' => 'nullable|string',
                'address_system' => 'nullable|string',
                'save_address' => 'nullable|boolean',
            ]);
        } else {
            // Nếu là phương thức giao hàng, loại trừ rõ ràng các trường liên quan đến pickup khỏi validation
            $rules['store_location_id'] = 'nullable|integer';
            $rules['pickup_full_name'] = 'nullable|string';
            $rules['pickup_phone_number'] = 'nullable|string';
            $rules['pickup_email'] = 'nullable|string';
            $rules['pickup_date'] = 'nullable|string';
            $rules['pickup_time_slot'] = 'nullable|string';
        }

        return $rules;
    }

    /**
     * Lấy các thông báo tùy chỉnh cho lỗi validator.
     */
    public function messages(): array
    {
        return [
            // Chung
            'delivery_method.required' => 'Vui lòng chọn phương thức nhận hàng',
            'delivery_method.in' => 'Phương thức nhận hàng không hợp lệ',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ',

            // Giao hàng - Địa chỉ mới
            'full_name.required' => 'Vui lòng nhập tên',
            'full_name.regex' => 'Vui lòng chỉ nhập chữ cái',
            'full_name.max' => 'Tên không được quá 100 ký tự',

            'phone_number.required' => 'Vui lòng nhập số điện thoại',
            'phone_number.regex' => 'Vui lòng nhập đúng định dạng số điện thoại',

            'phone.regex' => 'Vui lòng nhập đúng định dạng số điện thoại',

            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Vui lòng nhập email hợp lệ',

            'province_code.required' => 'Vui lòng chọn Tỉnh/Thành phố',
            'province_code.exists' => 'Tỉnh/Thành phố không tồn tại',

            'district_code.required' => 'Vui lòng chọn Quận/Huyện',
            'district_code.exists' => 'Quận/Huyện không tồn tại',

            'ward_code.required' => 'Vui lòng chọn Phường/Xã',
            'ward_code.exists' => 'Phường/Xã không tồn tại',

            'address_line1.required' => 'Vui lòng nhập số nhà, tên đường',
            'address_line1.min' => 'Địa chỉ quá ngắn, vui lòng nhập đầy đủ',
            'address_line1.max' => 'Địa chỉ không được quá 500 ký tự',

            'address.min' => 'Địa chỉ quá ngắn, vui lòng nhập đầy đủ',
            'address.max' => 'Địa chỉ không được quá 500 ký tự',

            'shipping_method.required' => 'Vui lòng chọn phương thức vận chuyển',

            // Khung giờ giao hàng
            'delivery_date.after_or_equal' => 'Ngày nhận hàng không được là ngày trong quá khứ',
            'delivery_time_slot.in' => 'Khung giờ giao hàng không hợp lệ',

            // Giao hàng - Địa chỉ đã lưu
            'address_id.required' => 'Vui lòng chọn địa chỉ giao hàng',
            'address_id.exists' => 'Địa chỉ không tồn tại',
            'address_id.address_ownership' => 'Bạn không có quyền sử dụng địa chỉ này',

            // Nhận hàng tại cửa hàng
            'pickup_full_name.required' => 'Vui lòng nhập tên người nhận',
            'pickup_full_name.regex' => 'Vui lòng chỉ nhập chữ cái',
            'pickup_full_name.max' => 'Tên không được quá 100 ký tự',

            'pickup_phone_number.required' => 'Vui lòng nhập số điện thoại người nhận',
            'pickup_phone_number.regex' => 'Vui lòng nhập đúng định dạng số điện thoại',

            'pickup_email.required' => 'Vui lòng nhập email người nhận',
            'pickup_email.email' => 'Vui lòng nhập email hợp lệ',

            'store_location_id.required' => 'Vui lòng chọn cửa hàng nhận hàng',
            'store_location_id.exists' => 'Cửa hàng không tồn tại',

            'pickup_date.after_or_equal' => 'Ngày nhận hàng không được là ngày trong quá khứ',
            'pickup_time_slot.in' => 'Khung giờ nhận hàng không hợp lệ',

            // Khác
            'notes.max' => 'Ghi chú không được quá 1000 ký tự',
            'shipping_fee.integer' => 'Phí vận chuyển phải là số nguyên',
            'shipping_fee.min' => 'Phí vận chuyển không được âm',
        ];
    }

    /**
     * Lấy các thuộc tính tùy chỉnh cho lỗi validator.
     */
    public function attributes(): array
    {
        return [
            'delivery_method' => 'phương thức nhận hàng',
            'payment_method' => 'phương thức thanh toán',
            'full_name' => 'họ tên',
            'phone_number' => 'số điện thoại',
            'phone' => 'số điện thoại',
            'email' => 'email',
            'province_code' => 'tỉnh/thành phố',
            'district_code' => 'quận/huyện',
            'ward_code' => 'phường/xã',
            'address_line1' => 'địa chỉ',
            'address' => 'địa chỉ',
            'postcode' => 'mã bưu điện',
            'shipping_method' => 'phương thức vận chuyển',
            'delivery_date' => 'ngày nhận hàng',
            'delivery_time_slot' => 'khung giờ giao hàng',
            'pickup_full_name' => 'tên người nhận',
            'pickup_phone_number' => 'số điện thoại người nhận',
            'pickup_email' => 'email người nhận',
            'store_location' => 'cửa hàng',
            'pickup_date' => 'ngày nhận hàng',
            'pickup_time_slot' => 'khung giờ nhận hàng',
            'notes' => 'ghi chú',
        ];
    }

    /**
     * Xử lý khi validation thất bại.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);

            throw new ValidationException($validator, $response);
        }

        parent::failedValidation($validator);
    }
}
