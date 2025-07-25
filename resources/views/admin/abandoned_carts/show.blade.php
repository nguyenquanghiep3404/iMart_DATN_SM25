@extends('admin.layouts.app')

@section('content')
    @include('admin.abandoned_carts.css.style')

    <div class="px-4 sm:px-6 md:px-8 py-8">
        <div class="container mx-auto max-w-full">
            @include('admin.abandoned_carts.layouts.headerShow')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Cart Items -->
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h3 class="card-custom-title">Chi tiết giỏ hàng</h3>
                        </div>
                        <div class="card-custom-body p-0">
                            <div class="overflow-x-auto">
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th colspan="2">Sản phẩm</th>
                                            <th class="text-right">Đơn giá</th>
                                            <th class="text-center">Số lượng</th>
                                            <th class="text-right">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($cart->items as $item)
                                            @php
                                                $isVariant = $item->cartable_type === 'App\Models\ProductVariant';
                                                $productName = $item->cartable->product->name ?? 'Không rõ';
                                                $variantName = $isVariant ? $item->cartable->name ?? '' : null;
                                                $image =
                                                    $item->cartable->image_url ??
                                                    'https://placehold.co/100x100/e2e8f0/e2e8f0';
                                                $attributes = null;
                                                if ($isVariant && $item->cartable) {
                                                    $attributes = $item->cartable->attributeValues
                                                        ? $item->cartable->attributeValues->mapWithKeys(
                                                            fn($attrVal) => [
                                                                $attrVal->attribute->name => $attrVal->value ?? 'N/A',
                                                            ],
                                                        )
                                                        : null;
                                                }
                                            @endphp
                                            <tr>
                                                <td style="width: 80px;">
                                                    <img src="{{ $image }}" alt="Product Image"
                                                        class="w-16 h-16 object-cover rounded-md">
                                                </td>
                                                @php
                                                    $isNewProduct =
                                                        $item->cartable_type === \App\Models\ProductVariant::class;
                                                    $productName = $isNewProduct
                                                        ? $item->cartable->product->name ?? '[Chưa rõ sản phẩm]'
                                                        : $item->cartable->name ?? '[Chưa rõ sản phẩm]';

                                                    $variantName = $isNewProduct ? $item->cartable->name ?? null : null;

                                                    $attributes = null;
                                                    if ($isNewProduct && $item->cartable) {
                                                        $attributes = $item->cartable->attributeValues->mapWithKeys(
                                                            fn($attrVal) => [
                                                                $attrVal->attribute->name => $attrVal->value,
                                                            ],
                                                        );
                                                    }
                                                @endphp

                                                <td>
                                                    <div class="font-semibold text-gray-800 flex items-center gap-2">
                                                        {{ $productName }}

                                                        @if ($isNewProduct)
                                                            <span
                                                                class="inline-block bg-green-100 text-green-700 text-xs font-medium px-2 py-0.5 rounded">
                                                                Sản phẩm mới
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-block bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-0.5 rounded">
                                                                Hàng cũ
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($variantName)
                                                        <div class="text-xs text-gray-500">Biến thể: {{ $variantName }}
                                                        </div>
                                                    @endif

                                                    @if ($attributes && $attributes->isNotEmpty())
                                                        <div class="text-xs text-gray-500 mt-1 space-y-1">
                                                            @foreach ($attributes as $attr => $value)
                                                                <div>{{ $attr }}: {{ $value }}</div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="text-xs text-gray-400">Không có thuộc tính</div>
                                                    @endif
                                                </td>

                                                <td class="text-right">{{ number_format($item->price) }} ₫</td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-right font-semibold">
                                                    {{ number_format($item->price * $item->quantity) }} ₫
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-gray-500 py-4">
                                                    Không có sản phẩm nào trong giỏ hàng.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-custom-footer text-right">
                            <div class="text-lg">
                                <span class="text-gray-600">Tổng giá trị:</span>
                                <span class="font-bold text-xl text-indigo-600 ml-2">{{ number_format($total) }}đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Restore History -->
                    @include('admin.abandoned_carts.layouts.restore_history')
                </div>

                <!-- Right Column -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Customer Info -->
                    @include('admin.abandoned_carts.layouts.infomationUser')
                    <!-- Restore Actions -->
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h3 class="card-custom-title">Thao tác khôi phục</h3>
                        </div>
                        <div class="card-custom-body space-y-3">
                            @if ($cart->user && $cart->user->email)
                                <button class="btn btn-primary btn-sm btn-send-email primary w-full"
                                    data-id="{{ $cart->id }}" title="Gửi mail khôi phục"
                                    @if ($cart->email_status === 'sent') disabled @endif>
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Gửi lại Email khôi phục
                                </button>
                            @endif
                            <button class="btn btn-info w-full btn-send-inapp" data-id="{{ $cart->id }}"
                                title="Gửi thông báo in-app" @if ($cart->in_app_notification_status === 'sent') disabled @endif>
                                <i class="fas fa-bell mr-2"></i>
                                Gửi lại thông báo In-App
                            </button>

                        </div>
                    </div>

                    <!-- Internal Notes -->
                    <div class="card-custom">
                        <div class="card-custom-header">
                            <h3 class="card-custom-title">Ghi chú nội bộ</h3>
                        </div>
                        <div class="card-custom-body">
                            <form action="#" method="POST">
                                <textarea name="note" rows="4" class="form-textarea"
                                    placeholder="Thêm ghi chú về các lần liên hệ với khách hàng..."></textarea>
                                <div class="mt-3 text-right">
                                    <button type="submit" class="btn btn-secondary">Lưu ghi chú</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@include('admin.abandoned_carts.script.script')
