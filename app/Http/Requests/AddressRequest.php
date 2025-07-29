<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'full_name' => ['required','string','max:255',],
            'phone_number' => [ 'required', 'regex:/^(0[0-9]{9})$/',],
            'old_province_code' => 'required|string',
            'old_district_code' => 'required|string',
            'old_ward_code'     => 'required|string',
            'address_line1'     => 'required|string|max:255',
            'is_default_shipping' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'         => 'Vui lòng nhập họ tên.',
            'phone_number.required'      => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex'         => 'Số điện thoại không đúng định dạng.',
            'old_province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'old_district_code.required' => 'Vui lòng chọn quận/huyện.',
            'old_ward_code.required'     => 'Vui lòng chọn xã/phường.',
            'address_line1.required'     => 'Vui lòng nhập địa chỉ cụ thể.',
        ];
    }
}
