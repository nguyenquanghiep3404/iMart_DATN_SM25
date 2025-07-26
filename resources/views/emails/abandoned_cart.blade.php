<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng bị bỏ lỡ</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">

    <h2>🛒 Xin chào {{ $cart->user->name ?? 'bạn' }},</h2>
    <p>Bạn còn một số sản phẩm trong giỏ hàng. Hãy quay lại và hoàn tất đơn hàng nhé!</p>
    <p>DEBUG link khôi phục: {{ $recoveryUrl ?? 'Chưa có link' }}</p>

    <table cellpadding="10" cellspacing="0" border="1"
        style="width: 100%; border-collapse: collapse; background: #fff;">
        <thead>
            <tr style="background-color: #eee;">
                <th>Ảnh</th>
                <th>Sản phẩm</th>
                <th>Biến thể</th>
                <th>Số lượng</th>
                <th>Giá</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cart->items as $item)
                @php
                    $product = $item->cartable;
                    $productName = $product->product->name ?? ($product->name ?? '[Không có tên]');
                    // $imageUrl = asset($product->image_url ?? 'images/no-image.png');
                    $imageUrl = config('app.url') . '/' . ($product->image_url ?? 'images/no-image.png');

                    $attributes =
                        $item->cartable_type === \App\Models\ProductVariant::class
                            ? optional($product->attributeValues)->mapWithKeys(
                                fn($attr) => [
                                    $attr->attribute->name => $attr->value,
                                ],
                            )
                            : null;
                @endphp

                <tr>
                    <td align="center">
                        <img src="{{ $imageUrl }}" alt="Ảnh sản phẩm" width="80" style="border-radius: 4px;">
                    </td>
                    <td>{{ $productName }}</td>
                    <td>
                        @if (!empty($attributes) && $attributes->count())
                            @foreach ($attributes as $name => $value)
                                <div><strong>{{ $name }}:</strong> {{ $value }}</div>
                            @endforeach
                        @else
                            Không có
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price) }} đ</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" align="center">Không có sản phẩm nào trong giỏ hàng.</td>
                </tr>
            @endforelse

            @if ($cart->items->count())
                <tr>
                    <td colspan="4" align="right"><strong>Tổng cộng:</strong></td>
                    <td><strong>{{ number_format($cart->items->sum(fn($i) => $i->price * $i->quantity)) }} đ</strong>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <p style="margin-top: 20px;">
        👉 <a href="{{ $recoveryUrl }}"
            style="color: #fff; background: #28a745; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Xem lại giỏ hàng
        </a>
    </p>

    <p style="margin-top: 30px;">Cảm ơn bạn đã mua sắm tại iMart</p>
</body>

</html>
