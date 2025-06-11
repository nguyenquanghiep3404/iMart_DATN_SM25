<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'content_manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(array_keys(Order::getStatusOptions()))
            ],
            'admin_note' => 'nullable|string|max:500',
            'cancellation_reason' => 'required_if:status,' . Order::STATUS_CANCELLED . '|nullable|string|max:500',
            'failed_delivery_reason' => 'required_if:status,' . Order::STATUS_FAILED_DELIVERY . '|nullable|string|max:500',
            'shipped_by' => 'nullable|exists:users,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái đơn hàng là bắt buộc.',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'admin_note.max' => 'Ghi chú admin không được vượt quá 500 ký tự.',
            'cancellation_reason.required_if' => 'Lý do hủy là bắt buộc khi hủy đơn hàng.',
            'cancellation_reason.max' => 'Lý do hủy không được vượt quá 500 ký tự.',
            'failed_delivery_reason.required_if' => 'Lý do giao hàng thất bại là bắt buộc.',
            'failed_delivery_reason.max' => 'Lý do giao hàng thất bại không được vượt quá 500 ký tự.',
            'shipped_by.exists' => 'Người giao hàng không tồn tại.'
        ];
    }

    /**
     * Validate status transition logic
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $currentOrder = $this->route('order');
            $newStatus = $this->input('status');
            
            if ($currentOrder && !$this->isValidStatusTransition($currentOrder->status, $newStatus)) {
                $currentStatusText = Order::getStatusOptions()[$currentOrder->status] ?? $currentOrder->status;
                $newStatusText = Order::getStatusOptions()[$newStatus] ?? $newStatus;
                
                $validator->errors()->add('status', "Không thể chuyển từ trạng thái '{$currentStatusText}' sang '{$newStatusText}'. Vui lòng chọn trạng thái hợp lệ.");
            }
        });
    }

    /**
     * Check if status transition is valid
     */
    private function isValidStatusTransition($currentStatus, $newStatus): bool
    {
        $validTransitions = [
            Order::STATUS_PENDING_CONFIRMATION => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_AWAITING_SHIPMENT, Order::STATUS_CANCELLED],
            Order::STATUS_AWAITING_SHIPMENT => [Order::STATUS_SHIPPED, Order::STATUS_CANCELLED],
            Order::STATUS_SHIPPED => [Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_DELIVERED, Order::STATUS_FAILED_DELIVERY, Order::STATUS_CANCELLED],
            Order::STATUS_OUT_FOR_DELIVERY => [Order::STATUS_DELIVERED, Order::STATUS_FAILED_DELIVERY, Order::STATUS_CANCELLED],
            Order::STATUS_DELIVERED => [Order::STATUS_RETURNED], 
            Order::STATUS_CANCELLED => [], 
            Order::STATUS_RETURNED => [], 
            Order::STATUS_FAILED_DELIVERY => [Order::STATUS_SHIPPED, Order::STATUS_CANCELLED] 
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []) || $currentStatus === $newStatus;
    }
}
