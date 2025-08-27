@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->order_code)

@section('content')
<style>
    body {
        font-family: 'Be Vietnam Pro', sans-serif;
        background-color: #f8f9fa;
    }

    .modal {
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .modal:not(.is-open) {
        opacity: 0;
        visibility: hidden;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .status-pending_confirmation {
        background-color: #e0e7ff;
        color: #4338ca;
    }



    .status-processing {
        background-color: #cffafe;
        color: #0891b2;
    }

    .status-shipped {
        background-color: #d1fae5;
        color: #059669;
    }

    .status-delivered {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .status-cancelled {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .status-packed {
        background-color: #fef3c7;
        color: #d97706;
    }

    .status-out_for_delivery {
        background-color: #f3e8ff;
        color: #7c3aed;
    }

    .status-failed_delivery {
        background-color: #fecaca;
        color: #b91c1c;
    }

    .status-returned {
        background-color: #f3f4f6;
        color: #6b7280;
    }

    .status-cancelled {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .payment-pending {
        background-color: #fef3c7;
        color: #d97706;
    }

    .payment-paid {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .payment-failed {
        background-color: #fee2e2;
        color: #dc2626;
    }

    /* Custom scrollbar for modal */
    .modal-content::-webkit-scrollbar {
        width: 8px;
    }

    .modal-content::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-content::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .modal-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Pagination styles */
    #pagination-controls button {
        transition: all 0.2s ease;
    }

    #pagination-controls button:hover:not(:disabled) {
        transform: translateY(-1px);
        shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Toast notification styles */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    }

    .toast {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 16px 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        transform: translateX(400px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid;
    }

    .toast.show {
        transform: translateX(0);
        opacity: 1;
    }

    .toast.success {
        border-left-color: #10b981;
    }

    .toast.error {
        border-left-color: #ef4444;
    }

    .toast.warning {
        border-left-color: #f59e0b;
    }

    .toast-icon {
        width: 24px;
        height: 24px;
        margin-right: 12px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
    }

    .toast.success .toast-icon {
        background: #10b981;
    }

    .toast.error .toast-icon {
        background: #ef4444;
    }

    .toast.warning .toast-icon {
        background: #f59e0b;
    }

    .toast-content {
        flex: 1;
    }

    .toast-title {
        font-weight: 600;
        font-size: 14px;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .toast-message {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.4;
    }

    .toast-close {
        margin-left: 12px;
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .toast-close:hover {
        color: #6b7280;
        background: #f3f4f6;
    }

    /* Product item styles */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .product-image-placeholder {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #9ca3af;
    }

    /* Enhanced modal table styling */
    #modal-order-items tr:hover {
        background-color: #f9fafb;
    }

    #modal-order-items img {
        transition: transform 0.2s ease;
    }

    #modal-order-items img:hover {
        transform: scale(1.05);
    }
    .status-cancellation-requested {
    background-color: #fef9c3; /* Màu vàng nhạt */
    color: #854d0e;
    }
    /* Responsive table for modal */
    @media (max-width: 768px) {
        .modal-content table {
            font-size: 14px;
        }

        .modal-content .w-16.h-16 {
            width: 48px;
            height: 48px;
        }
    }
</style>
<div class="p-6 mx-auto bg-white rounded-2xl shadow-lg" id="order-details-content">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Chi tiết đơn hàng</h1>
            <p class="text-lg text-gray-600 font-semibold mt-1">#{{ $order->order_code }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">THÔNG TIN GỠ LỖI:</strong>
                <ul class="mt-2 list-disc list-inside text-sm">
                    <li>
                        <strong>Giá trị của <code>$order->status</code>:</strong>
                        <code class="bg-red-200 p-1 rounded">{{ $order->status }}</code>
                    </li>
                    <li>
                        <strong>Kết quả của <code>$order->cancellationRequest</code>:</strong>
                        <pre class="bg-red-200 p-2 rounded mt-1"><code>@php(var_dump($order->cancellationRequest))</code></pre>
                    </li>
                </ul>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
            @if($order->status == 'cancellation_requested' && $order->cancellationRequest)
                {{-- Thay thế `href` bên trong nó --}}
            <a href="{{ route('admin.orders.cancellation.show', $order->cancellationRequest->id) }}" class="px-5 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-semibold flex items-center space-x-2">
                <i class="fas fa-exclamation-triangle"></i><span>Xử lý Yêu cầu Hủy</span>
            </a>
            @endif
            <button onclick="window.print()" class="px-5 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold flex items-center space-x-2">
                <i class="fas fa-print"></i><span>In hóa đơn</span>
            </button>
        </div>
    </div>

    <div class="py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-8">
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Thông tin khách hàng</h3>
                    <p><strong>Tên:</strong> <span class="text-gray-700">{{ $order->customer_name ?? 'N/A' }}</span></p>
                    <p><strong>Email:</strong> <span class="text-gray-700">{{ $order->customer_email ?? 'N/A' }}</span></p>
                    <p><strong>SĐT:</strong> <span class="text-gray-700">{{ $order->customer_phone ?? 'N/A' }}</span></p>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Địa chỉ giao hàng</h3>
                    <address class="not-italic text-gray-700">
                        {{ $order->shipping_address_line1 ?? 'N/A' }}<br>
                        {{ $order->shipping_ward ?? 'N/A' }}, {{ $order->shipping_district ?? 'N/A' }},<br>
                        {{ $order->shipping_city ?? 'N/A' }}
                    </address>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-800 mb-3 border-b pb-2">Ghi chú</h3>
                    <p class="text-gray-600 italic">{{ $order->notes_from_customer ?? 'Không có ghi chú.' }}</p>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-gray-50 p-6 rounded-lg mb-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-6">Tổng quan đơn hàng</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Ngày đặt</p>
                                <p class="font-semibold text-gray-800">{{ $order->created_at ? $order->created_at->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Trạng thái đơn hàng</p>
                                <span id="modal-order-status" class="status-badge status-{{ $order->status ?? 'na' }}">{{ ($order->status) }}</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Mã giảm giá đã sử dụng</p>
                                @if($order->couponUsages && $order->couponUsages->count() > 0)
                                    @foreach($order->couponUsages as $usage)
                                        <div class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-medium inline-block mr-2 mb-1">
                                            {{ $usage->coupon->code ?? 'N/A' }}
                                            @if($usage->coupon->type == 'percentage')
                                                ({{ $usage->coupon->value }}%)
                                            @else
                                                ({{ number_format($usage->coupon->value, 0, ',', '.') }}₫)
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-600 text-sm">Không sử dụng mã giảm giá</p>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Trạng thái thanh toán</p>
                                <span class="status-badge payment-{{ $order->payment_status ?? 'na' }}">{{ ($order->payment_status) }}</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Phương thức thanh toán</p>
                                <p class="font-semibold text-gray-800">{{ $order->payment_method ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Điểm thưởng đã sử dụng</p>
                                @if($order->loyaltyPointLogs && $order->loyaltyPointLogs->count() > 0)
                                    @foreach($order->loyaltyPointLogs as $log)
                                        <div class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-medium inline-block">
                                            {{-- Điểm trong CSDL là số âm, nên ta dùng abs() để lấy giá trị tuyệt đối --}}
                                            {{ number_format(abs($log->points)) }} điểm
                                            ({{ number_format(abs($log->points) * 1000, 0, ',', '.') }}₫)
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-600 text-sm">Không sử dụng điểm thưởng</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="font-bold text-lg text-gray-800 mb-3">Sản phẩm trong đơn</h3>
                <div class="border rounded-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm text-gray-600">
                            <tr>
                                <th class="p-3">Sản phẩm</th>
                                <th class="p-3 text-center">Số lượng</th>
                                <th class="p-3 text-right">Đơn giá</th>
                                <th class="p-3 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                            <tr class="border-b last:border-none hover:bg-gray-50">
                                <td class="p-3">
                                    {{ $item->productVariant->product->name ?? 'Sản phẩm không tồn tại' }}
                                </td>
                                <td class="p-3 text-center font-medium">{{ $item->quantity ?? 0 }}</td>
                                <td class="p-3 text-right font-medium">{{ number_format($item->price ?? 0, 0, ',', '.') }} ₫</td>
                                <td class="p-3 text-right font-semibold text-gray-600">{{ number_format($item->total_price ?? 0, 0, ',', '.') }} ₫</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-3 text-center text-gray-500">Không có sản phẩm trong đơn hàng.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex justify-end">
                    <div class="w-full md:w-1/2">
                        <dl class="space-y-2 text-gray-700">
                            <div class="flex justify-between">
                                <dt>Tổng tiền hàng:</dt>
                                <dd class="font-medium">{{ number_format($order->sub_total ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Phí vận chuyển:</dt>
                                <dd class="font-medium">{{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Giảm giá:</dt>
                                <dd class="font-medium text-red-500">- {{ number_format($order->discount_amount ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                            <div class="flex justify-between text-xl font-bold text-gray-900 border-t pt-2 mt-2">
                                <dt>Tổng cộng:</dt>
                                <dd>{{ number_format($order->grand_total ?? 0, 0, ',', '.') }} ₫</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Thông tin fulfillments -->
                @if($order->fulfillments && $order->fulfillments->count() > 0)
                <div class="mt-8">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">Thông tin gói hàng</h3>
                    @foreach($order->fulfillments as $fulfillment)

                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-semibold text-md text-gray-700">Kho: {{ $fulfillment->storeLocation->name ?? 'N/A' }}</h4>
                                    @if($fulfillment->estimated_delivery_date)
                                    <div class="text-sm text-blue-600">
                                        <span class="font-medium">Dự kiến giao: {{ \Carbon\Carbon::parse($fulfillment->estimated_delivery_date)->format('d/m/Y') }}</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="bg-white border rounded-lg p-4 mb-3">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="font-semibold text-gray-800">Fulfillment #{{ $fulfillment->id }}</p>
                                            <p class="text-sm text-gray-600">Trạng thái:
                                                <span class="status-badge" style="
                                                    @if($fulfillment->status == 'cancelled')
                                                        background-color: #fee2e2 !important; color: #dc2626 !important;
                                                    @elseif($fulfillment->status == 'pending')
                                                        background-color: #e0e7ff !important; color: #4338ca !important;
                                                    @elseif($fulfillment->status == 'processing')
                                                        background-color: #cffafe !important; color: #0891b2 !important;
                                                    @elseif($fulfillment->status == 'awaiting_shipment')
                                                        background-color: #cffafe !important; color: #0891b2 !important;
                                                    @elseif($fulfillment->status == 'shipped')
                                                        background-color: #f3e8ff !important; color: #7c3aed !important;
                                                    @elseif($fulfillment->status == 'delivered')
                                                        background-color: #dcfce7 !important; color: #16a34a !important;
                                                    @else
                                                        background-color: #f3f4f6 !important; color: #6b7280 !important;
                                                    @endif
                                                    ">
                                                    @if($fulfillment->status == 'cancelled')
                                                        Đã hủy
                                                    @elseif($fulfillment->status == 'pending')
                                                        Chờ xử lý
                                                    @elseif($fulfillment->status == 'processing')
                                                        Đang xử lý
                                                    @elseif($fulfillment->status == 'awaiting_shipment')
                                                        Chờ giao hàng
                                                    @elseif($fulfillment->status == 'shipped')
                                                        đang giao hàng
                                                    @elseif($fulfillment->status == 'delivered')
                                                        Giao hàng thành công
                                                    @else
                                                        {{ $fulfillment->status }}
                                                    @endif
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    @if($fulfillment->shipping_carrier || $fulfillment->tracking_code)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                        @if($fulfillment->shipping_carrier)
                                        <div>
                                            <p class="text-sm text-gray-500">Đơn vị vận chuyển</p>
                                            <p class="font-medium">{{ $fulfillment->shipping_carrier }}</p>
                                        </div>
                                        @endif
                                        @if($fulfillment->tracking_code)
                                        <div>
                                            <p class="text-sm text-gray-500">Mã vận đơn</p>
                                            <p class="font-medium">{{ $fulfillment->tracking_code }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    @endif

                                    @if($fulfillment->shipped_at || $fulfillment->delivered_at || $fulfillment->estimated_delivery_date || $fulfillment->shipping_fee)
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                        @if($fulfillment->estimated_delivery_date)
                                        <div>
                                            <p class="text-sm text-gray-500">Ngày dự kiến giao</p>
                                            <p class="font-medium">{{ \Carbon\Carbon::parse($fulfillment->estimated_delivery_date)->format('d/m/Y') }}</p>
                                        </div>
                                        @endif
                                        @if($fulfillment->shipping_fee)
                                        <div>
                                            <p class="text-sm text-gray-500">Phí vận chuyển</p>
                                            <p class="font-medium">{{ number_format($fulfillment->shipping_fee, 0, ',', '.') }} ₫</p>
                                        </div>
                                        @endif
                                        @if($fulfillment->shipped_at)
                                        <div>
                                            <p class="text-sm text-gray-500">Ngày xuất kho</p>
                                            <p class="font-medium">{{ $fulfillment->shipped_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        @endif
                                        @if($fulfillment->delivered_at)
                                        <div>
                                            <p class="text-sm text-gray-500">Ngày giao hàng</p>
                                            <p class="font-medium">{{ $fulfillment->delivered_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    @endif

                                    <!-- Sản phẩm trong fulfillment -->
                                    @if($fulfillment->fulfillmentItems && $fulfillment->fulfillmentItems->count() > 0)
                                    <div class="border-t pt-3">
                                        <p class="text-sm font-medium text-gray-700 mb-2">Sản phẩm trong gói hàng:</p>
                                        <div class="space-y-3">
                                            @foreach($fulfillment->fulfillmentItems as $item)
                                                @php
                                                    // Tính giá đã áp dụng giảm giá cho từng sản phẩm
                                                    $originalPrice = $item->orderItem->price ?? 0;
                                                    $quantity = $item->quantity;
                                                    $originalTotal = $originalPrice * $quantity;
                                                    
                                                    // Tính tỷ lệ giảm giá của toàn đơn hàng
                                                    $discountRatio = $order->sub_total > 0 ? $order->discount_amount / $order->sub_total : 0;
                                                    
                                                    // Áp dụng tỷ lệ giảm giá cho sản phẩm này
                                                    $itemDiscount = $originalTotal * $discountRatio;
                                                    $finalPrice = $originalPrice - ($itemDiscount / $quantity);
                                                    $finalTotal = $originalTotal - $itemDiscount;
                                                @endphp
                                                <div class="bg-white border rounded p-3">
                                                    <div class="flex justify-between items-start">
                                                        <div class="flex-1">
                                                            <p class="font-medium text-gray-800">{{ $item->orderItem->product_name ?? 'N/A' }}</p>
                                                            <div class="text-sm text-gray-600 mt-1">
                                                                <span>Số lượng: {{ $quantity }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            @if($discountRatio > 0)
                                                                <div class="text-sm">
                                                                    <span class="text-gray-400 line-through">{{ number_format($originalPrice, 0, ',', '.') }}₫</span>
                                                                    <span class="text-green-600 font-medium ml-1">{{ number_format($finalPrice, 0, ',', '.') }}₫</span>
                                                                </div>
                                                                <div class="text-sm font-semibold text-gray-800">
                                                                    Tổng: <span class="text-green-600">{{ number_format($finalTotal, 0, ',', '.') }}₫</span>
                                                                </div>
                                                            @else
                                                                <div class="text-sm font-medium text-gray-800">
                                                                    {{ number_format($originalPrice, 0, ',', '.') }}₫
                                                                </div>
                                                                <div class="text-sm font-semibold text-gray-800">
                                                                    Tổng: {{ number_format($originalTotal, 0, ',', '.') }}₫
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<script>
    // --- CONFIGURATION ---
    const CONFIG = {
        routes: {
            index: '{{ route("admin.orders.index") }}',
            show: '{{ route("admin.orders.show", ":id") }}',
            updateStatus: '{{ route("admin.orders.updateStatus", ":id") }}',
            getShippers: '{{ route("admin.orders.shippers") }}',
            assignShipper: '{{ route("admin.orders.assignShipper", ":id") }}',
        },
        csrfToken: '{{ csrf_token() }}'
    };

    // --- GLOBAL VARIABLES REMOVED FOR DETAIL PAGE ---

    // --- UTILITY FUNCTIONS ---
    const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const statusMap = {
        pending_confirmation: {
            text: "Chờ xác nhận",
            class: "status-pending_confirmation"
        },
        pending: {
            text: "Chờ xác nhận",
            class: "status-pending_confirmation"
        },
        processing: {
            text: "Đang xử lý",
            class: "status-processing"
        },
        awaiting_shipment: {
            text: "Chờ giao hàng",
            class: "status-processing"
        },
        packed: {
            text: "Đóng gói thành công",
            class: "status-packed"
        },
        shipped: {
            text: "đang giao hàng",
            class: "status-shipped"
        },
        out_for_delivery: {
            text: "Đang giao hàng",
            class: "status-out_for_delivery"
        },
        delivered: {
            text: "Giao hàng thành công",
            class: "status-delivered"
        },
        cancelled: {
            text: "Đã hủy",
            class: "status-cancelled"
        },
        cancellation_requested: {
            text: "Yêu cầu hủy",
            class: "status-cancellation-requested"
        },
        failed_delivery: {
            text: "Giao thất bại",
            class: "status-failed_delivery"
        },
        returned: {
            text: "Trả hàng",
            class: "status-returned"
        }
    };

    const paymentStatusMap = {
        pending: {
            text: "Chờ thanh toán",
            class: "payment-pending"
        },
        paid: {
            text: "Đã thanh toán",
            class: "payment-paid"
        },
        failed: {
            text: "Thất bại",
            class: "payment-failed"
        },
        refunded: {
            text: "Đã hoàn tiền",
            class: "payment-failed"
        },
        partially_refunded: {
            text: "Hoàn tiền một phần",
            class: "payment-pending"
        }
    };

    // --- RENDER FUNCTIONS REMOVED FOR DETAIL PAGE ---

    // --- TABLE AND PAGINATION FUNCTIONS REMOVED FOR DETAIL PAGE ---

    // --- MODAL LOGIC ---
    const modal = document.getElementById('order-detail-modal');

    async function viewOrder(orderId) {
        try {
            const response = await fetch(CONFIG.routes.show.replace(':id', orderId));
            const result = await response.json();

            if (result.success) {
                const order = result.data;
                populateModal(order);
                console.log("Đang kiểm tra Order Status:", order.status);
    console.log("Đang kiểm tra Cancellation Request:", order.cancellationRequest);
                modal.classList.add('is-open');
                modal.querySelector('div').classList.remove('scale-95');
            }
        } catch (error) {
            console.error('Error fetching order details:', error);
            if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                showToast('Không thể kết nối đến server. Kiểm tra mạng và thử lại.', 'error', 'Lỗi kết nối');
            } else {
                showToast('Không thể tải chi tiết đơn hàng. Đơn hàng có thể đã bị xóa hoặc bạn không có quyền truy cập.', 'error', 'Tải dữ liệu thất bại');
            }
        }
    }

    function populateModal(order) {
        document.getElementById('modal-order-code').textContent = order.order_code || 'N/A';
        document.getElementById('modal-customer-name').textContent = order.customer_name || 'N/A';
        document.getElementById('modal-customer-email').textContent = order.customer_email || 'N/A';
        document.getElementById('modal-customer-phone').textContent = order.customer_phone || 'N/A';

        document.getElementById('modal-shipping-address').innerHTML = `
        ${order.shipping_address_line1 || 'N/A'}<br>
        ${order.shipping_ward || 'N/A'}, ${order.shipping_district || 'N/A'},<br>
        ${order.shipping_city || 'N/A'}
    `;

        document.getElementById('modal-customer-notes').textContent = order.notes_from_customer || "Không có ghi chú.";
        document.getElementById('modal-order-date').textContent = order.created_at ? formatDate(order.created_at) : 'N/A';

        const orderStatus = statusMap[order.status] || {
            text: 'N/A',
            class: ''
        };
        const modalOrderStatusEl = document.getElementById('modal-order-status');
        modalOrderStatusEl.textContent = orderStatus.text;
        modalOrderStatusEl.className = `status-badge ${orderStatus.class}`;

        const paymentStatus = paymentStatusMap[order.payment_status] || {
            text: 'N/A',
            class: ''
        };
        const modalPaymentStatusEl = document.getElementById('modal-payment-status');
        modalPaymentStatusEl.textContent = paymentStatus.text;
        modalPaymentStatusEl.className = `status-badge ${paymentStatus.class}`;

        document.getElementById('modal-payment-method').textContent = order.payment_method || 'N/A';

        // Render items
        const itemsTbody = document.getElementById('modal-order-items');
        if (order.items && Array.isArray(order.items)) {
            itemsTbody.innerHTML = order.items.map(item => {
                // Prepare product image
                let productImage = null;
                if (item.product_variant?.primary_image?.path) {
                    productImage = `/storage/${item.product_variant.primary_image.path}`;
                } else if (item.product_variant?.product?.cover_image?.path) {
                    productImage = `/storage/${item.product_variant.product.cover_image.path}`;
                } else if (item.image_url) {
                    productImage = item.image_url;
                } else if (item.product_image) {
                    productImage = item.product_image;
                }

                // Prepare product link
                let productLink = '#';
                if (item.product_variant?.product?.id) {
                    productLink = '/admin/products/' + item.product_variant.product.id + '/edit';
                } else if (item.product_id) {
                    productLink = '/admin/products/' + item.product_id + '/edit';
                }

                return `
            <tr class="border-b last:border-none hover:bg-gray-50">
                <td class="p-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            ${productImage ?
                                `<img src="${productImage}" alt="${item.product_name || 'Sản phẩm'}"
                                    class="w-full h-full object-cover"
                                    onerror="this.parentElement.innerHTML='<div class=\\'product-image-placeholder\\' style=\\'width:100%;height:100%\\'><i class=\\'fas fa-image text-2xl\\'></i></div>'">`
                                : `<div class="product-image-placeholder" style="width:100%;height:100%">
                                    <i class="fas fa-box text-2xl"></i>
                                  </div>`}
                        </div>
                        <div class="flex-1 min-w-0">
                            ${productLink !== '#' ?
                                `<a href="${productLink}"
                                    target="_blank"
                                    class="font-medium text-gray-600 hover:text-gray-900 hover:underline line-clamp-2"
                                    title="Chỉnh sửa sản phẩm (mở tab mới)"
                                    onclick="event.stopPropagation();">
                                    ${item.product_name || 'N/A'}
                                </a>`
                                : `<span class="font-medium text-gray-800 line-clamp-2">${item.product_name || 'N/A'}</span>`}
                        </div>
                    </div>
                </td>
                <td class="p-3 text-center font-medium">${item.quantity || 0}</td>
                <td class="p-3 text-right font-medium">${formatCurrency(item.price || 0)}</td>
                <td class="p-3 text-right font-semibold text-gray-600">${formatCurrency(item.total_price || 0)}</td>
            </tr>
            `;
            }).join('');
        } else {
            itemsTbody.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-gray-500">Không có sản phẩm</td></tr>';
        }

        // Render totals
        document.getElementById('modal-sub-total').textContent = formatCurrency(order.sub_total || 0);
        document.getElementById('modal-shipping-fee').textContent = formatCurrency(order.shipping_fee || 0);
        document.getElementById('modal-discount').textContent = `- ${formatCurrency(order.discount_amount || 0)}`;
        document.getElementById('modal-grand-total').textContent = formatCurrency(order.grand_total || 0);
    }


    function closeModal() {
        modal.classList.remove('is-open');
        modal.querySelector('div').classList.add('scale-95');
    }

    // Close modal on escape key press
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // --- DETAIL PAGE SPECIFIC LOGIC ---

    // --- TOAST NOTIFICATION SYSTEM ---
    function showToast(message, type = 'success', title = null) {
        const toastContainer = document.getElementById('toast-container');

        // Determine title and icon based on type
        let toastTitle = title;
        let icon = '';

        if (!toastTitle) {
            switch (type) {
                case 'success':
                    toastTitle = 'Thành công';
                    icon = '✓';
                    break;
                case 'error':
                    toastTitle = 'Lỗi';
                    icon = '✕';
                    break;
                case 'warning':
                    toastTitle = 'Cảnh báo';
                    icon = '⚠';
                    break;
                default:
                    toastTitle = 'Thông báo';
                    icon = 'ℹ';
            }
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-title">${toastTitle}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="removeToast(this.parentElement)">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M13 1L1 13M1 1l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        `;

        // Add to container
        toastContainer.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            removeToast(toast);
        }, 5000);
    }

    function removeToast(toast) {
        if (toast && toast.parentElement) {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }
    }

    // --- UPDATE STATUS MODAL LOGIC ---
    const updateStatusModal = document.getElementById('update-status-modal');
    let currentOrderId = null;

    function showUpdateStatusModal(orderId, currentStatus) {
        currentOrderId = orderId;

        // Find order data to get order code
        const orderRows = document.querySelectorAll('#orders-tbody tr');
        let orderCode = '';
        orderRows.forEach(row => {
            const button = row.querySelector(`button[onclick*="${orderId}"]`);
            if (button) {
                orderCode = row.querySelector('td').textContent.trim();
            }
        });

        document.getElementById('update-order-code').textContent = orderCode;
        document.getElementById('new-status').value = currentStatus;
        document.getElementById('new-status').setAttribute('data-current-status', currentStatus);
        document.getElementById('admin-note').value = '';
        document.getElementById('cancellation-reason').value = '';

        // Show/hide cancellation reason field
        toggleCancellationField(currentStatus);

        updateStatusModal.classList.add('is-open');
        updateStatusModal.querySelector('div').classList.remove('scale-95');
    }

    function closeUpdateStatusModal() {
        updateStatusModal.classList.remove('is-open');
        updateStatusModal.querySelector('div').classList.add('scale-95');
        currentOrderId = null;
    }

    function toggleCancellationField(status) {
        const cancellationField = document.getElementById('cancellation-reason-field');
        const cancellationTextarea = document.getElementById('cancellation-reason');

        if (status === 'cancelled') {
            cancellationField.style.display = 'block';
            cancellationTextarea.setAttribute('required', 'required');
        } else {
            cancellationField.style.display = 'none';
            cancellationTextarea.removeAttribute('required');
        }
    }

    // Validate form before submit
    function validateStatusForm() {
        const newStatus = document.getElementById('new-status').value;
        const currentStatus = document.getElementById('new-status').getAttribute('data-current-status');

        if (!newStatus) {
            showToast('Vui lòng chọn trạng thái mới cho đơn hàng.', 'warning', 'Thiếu thông tin');
            return false;
        }

        // Ngăn chuyển từ 'processing' sang trạng thái khác mà không qua trạm đóng gói
        if (currentStatus === 'processing' && newStatus !== 'processing' && newStatus !== 'cancelled') {
            showToast('Đơn hàng đang xử lý phải được xác nhận tại Trạm Đóng Gói trước khi chuyển sang trạng thái khác', 'error');
            return false;
        }

        if (newStatus === 'cancelled') {
            const cancellationReason = document.getElementById('cancellation-reason').value;
            if (!cancellationReason.trim()) {
                showToast('Vui lòng nhập lý do hủy đơn hàng.', 'warning', 'Thiếu thông tin');
                return false;
            }
        }

        return true;
    }

    // Listen for status change to show/hide cancellation field
    document.getElementById('new-status').addEventListener('change', function() {
        toggleCancellationField(this.value);
    });

    // Handle update status form submission
    document.getElementById('update-status-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!currentOrderId) {
            showToast('Không xác định được đơn hàng cần cập nhật.', 'error', 'Lỗi hệ thống');
            return;
        }

        // Validate form
        if (!validateStatusForm()) {
            return;
        }

        const formData = new FormData(e.target);

        try {
            const response = await fetch(CONFIG.routes.updateStatus.replace(':id', currentOrderId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    status: formData.get('status'),
                    admin_note: formData.get('admin_note'),
                    cancellation_reason: formData.get('cancellation_reason')
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Show enhanced success message
                const statusText = result.data?.status_text || 'trạng thái mới';
                showToast(`Đơn hàng đã được cập nhật thành "${statusText}" thành công!`, 'success', 'Cập nhật thành công');

                // Close modal
                closeUpdateStatusModal();

                // Reload the page to show updated order status
                window.location.reload();
            } else {
                // Handle different types of errors
                if (response.status === 422) {
                    // Validation errors
                    if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat();
                        showToast(errorMessages.join('. '), 'error', 'Dữ liệu không hợp lệ');
                    } else {
                        showToast('Dữ liệu gửi lên không hợp lệ. Vui lòng kiểm tra lại.', 'error', 'Validation Error');
                    }
                } else if (response.status === 403) {
                    showToast('Bạn không có quyền thực hiện hành động này.', 'error', 'Không có quyền');
                } else if (response.status === 404) {
                    showToast('Không tìm thấy đơn hàng. Đơn hàng có thể đã bị xóa.', 'error', 'Không tìm thấy');
                } else if (response.status >= 500) {
                    showToast('Lỗi server. Vui lòng thử lại sau hoặc liên hệ IT Support.', 'error', 'Lỗi server');
                } else {
                    showToast(result.message || 'Không thể cập nhật trạng thái. Vui lòng thử lại.', 'error', 'Cập nhật thất bại');
                }
            }
        } catch (error) {
            console.error('Error updating status:', error);
            if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                showToast('Mất kết nối mạng. Vui lòng kiểm tra internet và thử lại.', 'error', 'Lỗi kết nối');
            } else {
                showToast('Lỗi hệ thống không xác định. Vui lòng liên hệ IT Support hoặc thử lại sau.', 'error', 'Lỗi hệ thống');
            }
        }
    });

    // Close modal on escape key
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && updateStatusModal.classList.contains('is-open')) {
            closeUpdateStatusModal();
        }
        if (event.key === 'Escape' && assignShipperModal.classList.contains('is-open')) {
            closeAssignShipperModal();
        }
    });

    // --- ASSIGN SHIPPER MODAL LOGIC ---
    const assignShipperModal = document.getElementById('assign-shipper-modal');
    let currentAssignOrderId = null;
    let shippersCache = null; // Cache for shippers list

    async function showAssignShipperModal(orderId, orderCode) {
        currentAssignOrderId = orderId;

        // Set order code
        document.getElementById('assign-shipper-order-code').textContent = orderCode;

        // Reset form
        document.getElementById('shipper-select').value = '';

        // Show modal
        assignShipperModal.classList.add('is-open');
        assignShipperModal.querySelector('div').classList.remove('scale-95');

        // Load shippers theo warehouse của đơn hàng
        await loadShippers(orderId);
    }

    function closeAssignShipperModal() {
        assignShipperModal.classList.remove('is-open');
        assignShipperModal.querySelector('div').classList.add('scale-95');
        currentAssignOrderId = null;
    }

    async function loadShippers(orderId = null) {
        const shipperSelect = document.getElementById('shipper-select');
        const loadingDiv = document.getElementById('shipper-loading');

        // Show loading
        loadingDiv.style.display = 'block';
        shipperSelect.disabled = true;

        try {
            // Tạo URL với order_id nếu có
            let url = CONFIG.routes.getShippers;
            if (orderId) {
                url += `?order_id=${orderId}`;
                // Reset cache khi có order_id để lấy shipper theo warehouse
                shippersCache = null;
            }

            // Use cache if available và không có order_id
            if (shippersCache && !orderId) {
                populateShipperSelect(shippersCache);
                return;
            }

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CONFIG.csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                if (!orderId) {
                    shippersCache = result.data; // Chỉ cache khi không có order_id
                }
                populateShipperSelect(result.data);
            } else {
                showToast('Không thể tải danh sách shipper.', 'error', 'Lỗi tải dữ liệu');
            }
        } catch (error) {
            console.error('Error loading shippers:', error);
            showToast('Lỗi kết nối khi tải danh sách shipper.', 'error', 'Lỗi kết nối');
        } finally {
            loadingDiv.style.display = 'none';
            shipperSelect.disabled = false;
        }
    }

    function populateShipperSelect(shippers) {
        const shipperSelect = document.getElementById('shipper-select');

        // Clear existing options except the first one
        shipperSelect.innerHTML = '<option value="">-- Chọn Shipper --</option>';

        // Add shipper options
        shippers.forEach(shipper => {
            const option = document.createElement('option');
            option.value = shipper.id;
            option.textContent = `${shipper.name} - ${shipper.email}`;
            shipperSelect.appendChild(option);
        });
    }

    // Handle assign shipper form submission
    document.getElementById('assign-shipper-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!currentAssignOrderId) {
            showToast('Không xác định được đơn hàng cần gán shipper.', 'error', 'Lỗi hệ thống');
            return;
        }

        const formData = new FormData(e.target);
        const shipperId = formData.get('shipper_id');

        if (!shipperId) {
            showToast('Vui lòng chọn shipper để gán.', 'warning', 'Thiếu thông tin');
            return;
        }

        try {
            const response = await fetch(CONFIG.routes.assignShipper.replace(':id', currentAssignOrderId), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    shipper_id: shipperId
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showToast(`Đã gán shipper "${result.data.shipper.name}" cho đơn hàng thành công!`, 'success', 'Gán shipper thành công');

                // Close modal
                closeAssignShipperModal();

                // Reload the page to show updated statuses
                window.location.reload();
            } else {
                if (response.status === 422) {
                    showToast(result.message || 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', 'error', 'Dữ liệu không hợp lệ');
                } else if (response.status === 403) {
                    showToast('Bạn không có quyền thực hiện hành động này.', 'error', 'Không có quyền');
                } else if (response.status === 404) {
                    showToast('Không tìm thấy đơn hàng hoặc shipper.', 'error', 'Không tìm thấy');
                } else {
                    showToast(result.message || 'Không thể gán shipper. Vui lòng thử lại.', 'error', 'Gán shipper thất bại');
                }
            }
        } catch (error) {
            console.error('Error assigning shipper:', error);
            if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                showToast('Mất kết nối mạng. Vui lòng kiểm tra internet và thử lại.', 'error', 'Lỗi kết nối');
            } else {
                showToast('Lỗi hệ thống không xác định. Vui lòng thử lại sau.', 'error', 'Lỗi hệ thống');
            }
        }
    });


    document.addEventListener('DOMContentLoaded', () => {
        // Xử lý hiển thị trạng thái gói hàng
        document.querySelectorAll('[data-status]').forEach(element => {
            const status = element.getAttribute('data-status');
            // Chỉ áp dụng cho status-badge trong gói hàng, không áp dụng cho lịch sử trạng thái
            if (status && statusMap[status] && element.classList.contains('status-badge') && !element.closest('.space-y-1')) {
                element.textContent = statusMap[status].text;
                element.className = `status-badge ${statusMap[status].class}`;
            }
        });

        // Detail page initialization complete
        console.log('Detail page loaded successfully');
    });
</script>
@endsection
