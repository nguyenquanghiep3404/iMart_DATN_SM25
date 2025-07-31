@php
    $subtotal = $purchaseOrder->items->sum(function($item) {
        return $item->quantity * $item->cost_price;
    });
    $tax = $subtotal * 0.10; // Giả sử VAT là 10%
    $grandTotal = $subtotal + $tax;
@endphp

<div class="card">
    <div class="p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Danh sách sản phẩm</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-2/5">Sản Phẩm</th>
                        <th scope="col" class="px-4 py-3 text-center">Số Lượng</th>
                        <th scope="col" class="px-4 py-3 text-right">Giá Vốn (VNĐ)</th>
                        <th scope="col" class="px-4 py-3 text-right">Thành Tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseOrder->items as $item)
                        <tr class="bg-white border-b">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <img src="{{ optional($item->productVariant->primaryImage)->path ? Storage::url($item->productVariant->primaryImage->path) : asset('assets/admin/img/placeholder-image.png') }}" 
                                         alt="{{ $item->productVariant->product->name }}" 
                                         class="w-10 h-10 object-cover rounded-md mr-4">
                                    <div>
                                        <div class="font-semibold text-gray-800">
                                            {{ $item->productVariant->product->name }} - 
                                            {{ $item->productVariant->attributeValues->pluck('value')->implode(' - ') }}
                                        </div>
                                        <div class="text-xs text-gray-500">SKU: {{ $item->productVariant->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-medium">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($item->cost_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($item->quantity * $item->cost_price, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-10 text-gray-500">
                                Phiếu nhập này không có sản phẩm nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="bg-gray-50 px-6 py-4 rounded-b-lg space-y-3 mt-[-1px]">
        <div class="flex justify-between items-center text-gray-600">
            <span>Tổng tiền hàng:</span>
            <span class="font-medium text-gray-800">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</span>
        </div>
         <div class="flex justify-between items-center text-gray-600">
            <span>Thuế (VAT 10%):</span>
            <span class="font-medium text-gray-800">{{ number_format($tax, 0, ',', '.') }} VNĐ</span>
        </div>
        <div class="flex justify-between items-center text-xl font-bold text-gray-900 border-t border-gray-200 pt-3">
            <span>Tổng Cộng:</span>
            <span>{{ number_format($grandTotal, 0, ',', '.') }} VNĐ</span>
        </div>
    </div>
</div>