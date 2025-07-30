<div class="card">
    <div class="p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Danh sách sản phẩm chuyển kho</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-3/5">Sản Phẩm</th>
                        <th scope="col" class="px-4 py-3 text-center">SKU</th>
                        <th scope="col" class="px-4 py-3 text-center">Số Lượng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockTransfer->items as $item)
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
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-mono">{{ $item->productVariant->sku }}</td>
                            <td class="px-4 py-3 text-center font-medium">{{ $item->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-10 text-gray-500">
                                Phiếu chuyển này không có sản phẩm nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                 <tfoot>
                    <tr class="font-semibold text-gray-900">
                        <th scope="row" colspan="2" class="px-4 py-3 text-base text-right">Tổng số lượng</th>
                        <td class="px-4 py-3 text-base text-center font-bold">{{ $stockTransfer->items->sum('quantity') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
