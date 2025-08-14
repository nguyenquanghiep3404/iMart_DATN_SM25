<table>
    <thead>
        <tr>
            <th>Ngày giờ</th>
            <th>Sản phẩm</th>
            <th>SKU</th>
            <th>Địa điểm</th>
            <th>Giao dịch</th>
            <th>Tham chiếu</th>
            <th>Thay đổi</th>
            <th>Tồn sau thay đổi</th>
            <th>Người thực hiện</th>
        </tr>
    </thead>
    <tbody>
        @foreach($movements as $movement)
            <tr>
                <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    {{ $movement->productVariant->product->name ?? 'N/A' }}
                    @php
                        $attrs = $movement->productVariant->attributeValues->pluck('value')->toArray();
                    @endphp
                    @if ($attrs)
                        ({{ implode(' ', $attrs) }})
                    @endif
                </td>
                <td>{{ $movement->productVariant->sku ?? 'N/A' }}</td>
                <td>{{ $movement->storeLocation->name ?? 'N/A' }}</td>
                <td>{{ $movement->reason_label }}</td>
                <td>{{ $movement->reference_code ?? '' }}</td>
                <td>{{ $movement->quantity_change }}</td>
                <td>{{ $movement->quantity_after_change }}</td>
                <td>{{ $movement->user->name ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
