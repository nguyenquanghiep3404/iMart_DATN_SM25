@extends('users.layouts.app')

@section('title', 'So sánh sản phẩm')

@section('content')
    <div class="container py-5">
        <h2 class="text-center mb-4 fw-bold text-primary">SO SÁNH SẢN PHẨM</h2>

        @if ($variants->isEmpty())
            <div class="alert alert-warning text-center">Chưa có sản phẩm nào để so sánh.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center bg-white shadow-sm" style="min-width: 900px;">
                    <thead class="table-dark align-middle">
                        <tr>
                            <th style="width: 180px;"></th>
                            @foreach ($variants as $variant)
                                @php
                                    $product = $variant->product;
                                    $imageToShow = $variant->primaryImage ?? $product->coverImage;
                                    $imageUrl = $imageToShow
                                        ? Storage::url($imageToShow->path)
                                        : asset('assets/admin/img/placeholder-image.png');
                                    $altText = $imageToShow?->alt_text ?? $product->name;
                                @endphp
                                <th class="text-center" style="min-width: 220px; max-width: 260px;" data-variant-id="{{ $variant->id }}">
                                    <div class="card border-0 shadow-sm mb-2 p-2 bg-light h-100">
                                        <div class="compare-img-bg mb-2 d-flex align-items-center justify-content-center"
                                            style="height: 100px;">
                                            <img src="{{ $imageUrl }}" alt="{{ $altText }}"
                                                class="img-thumbnail border-0 shadow-sm"
                                                style="width: 90px; height: 90px; object-fit: contain; background: transparent;"
                                                onerror="this.onerror=null;this.src='{{ asset('assets/admin/img/placeholder-image.png') }}';">
                                        </div>
                                        <div class="fw-semibold small text-dark mb-1" style="min-height: 38px;">
                                            {{ $product->name }}
                                            <div class="small text-muted mt-1">{{ $variant->attributeValues->pluck('value')->join(' / ') }}</div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                            onclick="removeFromCompare({{ $variant->id }})">
                                            <i class="ci-trash me-1"></i> Xoá
                                        </button>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-light-subtle">
                            <th class="bg-light">Giá</th>
                            @foreach ($variants as $variant)
                                @php
                                    $now = now();
                                    $isSale = $variant->sale_price && $variant->sale_price_starts_at <= $now && $variant->sale_price_ends_at >= $now;
                                    $price = $isSale ? $variant->sale_price : $variant->price;
                                @endphp
                                <td data-variant-id="{{ $variant->id }}">
                                    @if ($isSale)
                                        <del class="text-muted small">{{ number_format($variant->price) }}đ</del><br>
                                        <span class="text-danger fw-bold fs-5">{{ number_format($variant->sale_price) }}đ</span>
                                    @else
                                        <span class="fw-semibold">{{ number_format($price) }}đ</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-light-subtle">
                            <th class="bg-light">Tình trạng</th>
                            @foreach ($variants as $variant)
                                @php
                                    $inStock = $variant->stock_quantity > 0 ? "Còn {$variant->stock_quantity} hàng" : 'Hết hàng';
                                @endphp
                                <td data-variant-id="{{ $variant->id }}" class="{{ $variant->stock_quantity > 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                    {{ $inStock }}
                                </td>
                            @endforeach
                        </tr>
                        {{-- Thuộc tính động --}}
                        @foreach ($allAttributes as $i => $attrName)
                            <tr class="{{ $i % 2 == 0 ? 'bg-light' : '' }}">
                                <th class="bg-light">{{ $attrName }}</th>
                                @foreach ($variants as $variant)
                                    @php
                                        $value = optional($variant->attributeValues->firstWhere('attribute.name', $attrName))->value;
                                    @endphp
                                    <td data-variant-id="{{ $variant->id }}">{{ $value ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <style>
    .compare-img-bg {
        background: #e0e0e0 !important; /* Thay đổi màu nền của khung ảnh */
        border-radius: 12px;
        min-width: 100px;
        min-height: 100px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    }
    .card.bg-light {
        background: #e0e0e0 !important; /* Thay đổi màu nền của thẻ card */
    }
    .img-thumbnail {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    .table th, .table td {
        vertical-align: middle !important;
        text-align: center;
        padding: 18px 8px;
    }
    .table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .table thead th {
        background: #212529;
        color: #fff;
        font-size: 1.1rem;
    }
    .table-responsive {
        overflow-x: auto;
    }
    @media (max-width: 768px) {
        .table {
            font-size: 0.95rem;
        }
        .table th, .table td {
            padding: 10px 4px;
        }
    }
</style>

    <script>
        function removeFromCompare(variantId) {
            // Xóa khỏi localStorage
            let list = JSON.parse(localStorage.getItem('compare_products') || '[]');
            list = list.filter(item => item.id != variantId);
            localStorage.setItem('compare_products', JSON.stringify(list));

            // Xóa cột khỏi bảng
            const ths = document.querySelectorAll('th[data-variant-id]');
            const tds = document.querySelectorAll('td[data-variant-id]');
            ths.forEach(th => {
                if (th.getAttribute('data-variant-id') == variantId) th.remove();
            });
            tds.forEach(td => {
                if (td.getAttribute('data-variant-id') == variantId) td.remove();
            });

            // Nếu không còn sản phẩm nào thì ẩn bảng và hiển thị thông báo
            if (document.querySelectorAll('th[data-variant-id]').length === 0) {
                const tableWrap = document.querySelector('.table-responsive');
                if (tableWrap) tableWrap.style.display = 'none';
                const alert = document.createElement('div');
                alert.className = 'alert alert-warning text-center mt-4';
                alert.textContent = 'Chưa có sản phẩm nào để so sánh.';
                document.querySelector('.container').appendChild(alert);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const stored = JSON.parse(localStorage.getItem('compare_products') || '[]');
            const hasParam = new URLSearchParams(window.location.search).has('ids');
            if (stored.length && !hasParam) {
                const params = new URLSearchParams();
                params.set('ids', JSON.stringify(stored));
                window.location.href = window.location.pathname + '?' + params.toString();
            }
        });
    </script>
@endsection
