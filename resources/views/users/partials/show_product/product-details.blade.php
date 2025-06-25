<section class="container pb-5 mb-4">
    <div class="row">
        <div class="col-md-7">
            <h2 class="h3 pb-2 border-bottom" id="details">Chi tiết sản phẩm</h2>

            <div class="pt-3">
                <h5 class="fw-semibold mb-3 text-primary">Thông số kỹ thuật</h5>
                <ul class="list-unstyled d-flex flex-column gap-3 fs-sm">
                    <li class="d-flex justify-content-between border-bottom py-2">
                        <span class="fw-medium">Danh mục</span>
                        <span class="text-muted">{{ $product->category->name ?? 'Chưa xác định' }}</span>
                    </li>
                    <li class="d-flex justify-content-between border-bottom py-2">
                        <span class="fw-medium">Trạng thái</span>
                        <span class="text-muted">{{ $product->status ?? 'Chưa xác định' }}</span>
                    </li>

                    @php
                        $specAttributes = ['Màu sắc', 'Dung lượng', 'RAM', 'Kích thước màn hình', 'Chất liệu vỏ', 'Bộ nhớ'];
                    @endphp

                    @foreach ($specAttributes as $attrName)
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span class="fw-medium">{{ $attrName }}</span>
                            <span class="text-muted">
                                @if (isset($attributes[$attrName]) && $attributes[$attrName]->isNotEmpty())
                                    {{ $attributes[$attrName]->pluck('value')->join(', ') }}
                                @else
                                    Chưa xác định
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="pt-4">
                <h5 class="fw-semibold text-primary mb-3">Mô tả sản phẩm</h5>
                <div class="fs-sm lh-base text-body">
                    {!! $product->description !!}
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    #details h2,
    #details h5 {
        font-weight: 600;
        color: #0d6efd;
    }

    ul.spec-list li {
        transition: background 0.2s;
    }

    ul.spec-list li:hover {
        background: #f8f9fa;
    }

    .fs-sm {
        font-size: 0.925rem;
    }
</style>
@endpush
