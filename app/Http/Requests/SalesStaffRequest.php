<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesStaffRequest extends FormRequest
{
    /**
     * Xác định quyền gửi request (cho phép luôn).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validate cho thêm/sửa nhân viên.
     */
    public function rules(): array
    {
        $userId = $this->route('userId');
        $isDetail = $this->has('store_location_id') && $this->has('province') && $this->has('district');
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($userId ? ',' . $userId : ''),
            'phone' => [
                'required',
                'string',
                'max:10',
                'regex:/^(0[1-9][0-9]{8,9})$/',
                'unique:users,phone_number' . ($userId ? ',' . $userId : ''),
            ],
            'status' => 'required|in:active,inactive,banned',
            'province' => $isDetail ? 'nullable' : 'required',
            'district' => $isDetail ? 'nullable' : 'required',
            'store_location_id' => $isDetail ? 'nullable' : 'required|exists:store_locations,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại trong hệ thống.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.max' => 'Vui lòng nhập đúng định dạng số điện thoại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng (bắt đầu bằng 0, đủ 10-11 số).',
            'phone.unique' => 'Số điện thoại đã tồn tại trong hệ thống.',
            'position.required' => 'Vui lòng nhập chức vụ.',
            'position.max' => 'Chức vụ không được vượt quá 255 ký tự.',
            'province.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'district.required' => 'Vui lòng chọn quận/huyện.',
            'store_location_id.required' => 'Vui lòng chọn cửa hàng.',
            'store_location_id.exists' => 'Cửa hàng không hợp lệ.',
        ];
    }
}
