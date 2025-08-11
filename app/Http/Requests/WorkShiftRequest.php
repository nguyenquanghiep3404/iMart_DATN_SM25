<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class WorkShiftRequest extends FormRequest
{
    /**
     * Xác định quyền gửi request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validate cho thêm/sửa ca làm việc.
     */
    public function rules(): array
    {
        $workShiftId = $this->route('workShiftId');
        
        $rules = [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'color_code' => 'nullable|string|max:7',
        ];
        
        // Unique validation cho tên ca làm việc
        $rules['name'] .= $workShiftId ? '|unique:work_shifts,name,' . $workShiftId : '|unique:work_shifts,name';
        
        return $rules;
    }
    /**
     * Thông báo lỗi tùy chỉnh cho validation.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên ca làm việc.',
            'name.string' => 'Tên ca làm việc phải là chuỗi ký tự.',
            'name.max' => 'Tên ca làm việc không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên ca làm việc đã tồn tại trong hệ thống.',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'start_time.date_format' => 'Giờ bắt đầu không đúng định dạng (HH:mm).',
            'end_time.required' => 'Vui lòng chọn giờ kết thúc.',
            'end_time.date_format' => 'Giờ kết thúc không đúng định dạng (HH:mm).',
            'color_code.string' => 'Mã màu phải là chuỗi ký tự.',
            'color_code.max' => 'Mã màu không được vượt quá 7 ký tự.',
        ];
    }

    /**
     * Các thuộc tính mô tả cho các trường trong request.
     */
    public function attributes(): array
    {
        return [
            'name' => 'tên ca làm việc',
            'start_time' => 'giờ bắt đầu',
            'end_time' => 'giờ kết thúc',
            'color_code' => 'mã màu',
        ];
    }

    /**
     * Chuẩn bị dữ liệu trước khi validation.
     */
    protected function prepareForValidation(): void
    {
        // Đảm bảo color_code có giá trị mặc định nếu null
        if ($this->color_code === null || $this->color_code === '') {
            $this->merge(['color_code' => '#4299E1']);
        }
    }

    /**
     * Xử lý khi validation fails
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
