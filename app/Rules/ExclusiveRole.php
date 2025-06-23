<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ExclusiveRole implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute Tên của trường input (ví dụ: 'roles')
     * @param  mixed  $value Giá trị của trường input (mảng các role_id)
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Nếu chỉ có 1 vai trò được chọn, hoặc không có vai trò nào, thì luôn hợp lệ.
        if (count($value) <= 1) {
            return true;
        }

        // Nếu có nhiều hơn 1 vai trò được chọn, kiểm tra xem 'admin' hoặc 'customer' có nằm trong số đó không.
        // Lấy tên của các vai trò từ các ID được chọn.
        $roleNames = DB::table('roles')->whereIn('id', $value)->pluck('name');

        // Nếu trong danh sách tên có 'admin' hoặc 'customer' thì không hợp lệ.
        if ($roleNames->contains('admin') || $roleNames->contains('customer')) {
            return false;
        }

        // Nếu không, các vai trò khác có thể được kết hợp (ví dụ: content_manager và order_manager)
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Vai trò "Admin" và "Customer" không thể được kết hợp với các vai trò khác.';
    }
}
