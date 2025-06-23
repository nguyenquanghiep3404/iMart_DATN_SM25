<div class="col-md-6 col-xl-5 offset-xl-1">
    <div class="ps-md-4 ps-xl-0 pt-md-0">
        @php
            $attributesGrouped = collect();
            $variantCombinations = [];
            $variantData = [];
            $initialVariantAttributes = [];

            if ($defaultVariant) {
                foreach ($defaultVariant->attributeValues as $attrValue) {
                    $initialVariantAttributes[$attrValue->attribute->name] = $attrValue->value;
                }
            }

            foreach ($product->variants as $variant) {
                $combination = [];
                foreach ($variant->attributeValues as $attrValue) {
                    $attrName = $attrValue->attribute->name;
                    $value = $attrValue->value;
                    $combination[$attrName] = $value;

                    if (!$attributesGrouped->has($attrName)) {
                        $attributesGrouped[$attrName] = collect();
                    }

                    if (!$attributesGrouped[$attrName]->contains('value', $attrValue->value)) {
                        $attributesGrouped[$attrName]->push($attrValue);
                    }
                }

                $variantCombinations[] = $combination;

                $key = collect($combination)->implode('_');
                $now = now();
                $salePrice = $variant->sale_price;
                $isOnSale = $salePrice !== null && $variant->sale_price_starts_at <= $now && $variant->sale_price_ends_at >= $now;

                $variantData[$key] = [
                    'price' => (int) ($isOnSale ? $salePrice : $variant->price),
                    'original_price' => (int) $variant->price,
                    'status' => $variant->status,
                    'image' => $variant->image_url ?? null,
                ];
            }
        @endphp

        {{-- Thuộc tính sản phẩm --}}
        @foreach ($attributesGrouped as $attrName => $attrValues)
            <div class="mb-4">
                <label class="form-label fw-semibold d-block mb-2">
                    {{ $attrName }}
                    @if (strtolower($attrName) === 'màu sắc')
                        : <span id="selected-color-name" class="fw-normal"></span>
                    @endif
                </label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($attrValues as $attrValue)
                        @php
                            $inputName = strtolower(str_replace(' ', '-', $attrName)) . '-options';
                            $inputId = $inputName . '-' . $attrValue->id;
                            $isColor = $attrValue->attribute->display_type === 'color_swatch' && $attrValue->meta;
                            $isChecked = isset($initialVariantAttributes[$attrName]) && $initialVariantAttributes[$attrName] === $attrValue->value;
                        @endphp

                        <div class="option-container" data-attr-name="{{ $attrName }}" data-attr-value="{{ $attrValue->value }}">
                            <input type="radio"
                                class="btn-check"
                                name="{{ $inputName }}"
                                id="{{ $inputId }}"
                                value="{{ $attrValue->value }}"
                                data-attr-name="{{ $attrName }}"
                                {{ $isChecked ? 'checked' : '' }}>

                            @if ($isColor)
                                <label for="{{ $inputId }}"
                                    class="color-swatch-option rounded"
                                    title="{{ $attrValue->value }}"
                                    style="width: 36px; height: 36px; display: inline-block;
                                           background-color: {{ $attrValue->meta }};
                                           cursor: pointer; transition: transform 0.2s;">
                                </label>
                            @else
                                <label for="{{ $inputId }}"
                                    class="btn btn-outline-secondary btn-sm text-nowrap">
                                    {{ $attrValue->value }}
                                </label>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Giá và trạng thái --}}
        @php
            $variant = $defaultVariant ?? $product->variants->first();
            $now = now();
            $salePrice = (int) $variant->sale_price;
            $originalPrice = (int) $variant->price;
            $isOnSale = $variant->sale_price !== null && $variant->sale_price_starts_at <= $now && $variant->sale_price_ends_at >= $now;
            $displayPrice = $isOnSale ? $salePrice : $originalPrice;
        @endphp

        <div class="d-flex flex-wrap align-items-center mb-3">
            <div class="d-flex align-items-baseline me-3">
                <div class="h4 mb-0 text-danger" id="product-price">
                    {{ number_format($displayPrice) }}đ
                </div>
                <div class="ms-2 text-muted text-decoration-line-through" id="original-price"
                    style="{{ $isOnSale && $originalPrice > $salePrice ? '' : 'display: none;' }}">
                    {{ $isOnSale && $originalPrice > $salePrice ? number_format($originalPrice) . 'đ' : '' }}
                </div>
            </div>
            <div class="d-flex align-items-center text-success fs-sm ms-auto">
                <i class="ci-check-circle fs-base me-2"></i>
                <span id="variant-status">{{ $variant->status }}</span>
            </div>
        </div>

        {{-- Số lượng và nút --}}
        <div class="d-flex flex-wrap flex-sm-nowrap flex-md-wrap flex-lg-nowrap gap-3 gap-lg-2 gap-xl-3 mb-4">
            <div class="count-input flex-shrink-0 order-sm-1">
                <button type="button" class="btn btn-icon btn-lg" data-decrement aria-label="Giảm số lượng">
                    <i class="ci-minus"></i>
                </button>
                <input type="number" class="form-control form-control-lg" value="1" min="1" max="5" readonly>
                <button type="button" class="btn btn-icon btn-lg" data-increment aria-label="Tăng số lượng">
                    <i class="ci-plus"></i>
                </button>
            </div>
            <button type="button"
                class="btn btn-icon btn-lg btn-secondary animate-pulse order-sm-3 order-md-2 order-lg-3"
                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add to Wishlist">
                <i class="ci-heart fs-lg animate-target"></i>
            </button>
            <button type="button"
                class="btn btn-icon btn-lg btn-secondary animate-rotate order-sm-4 order-md-3 order-lg-4"
                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Compare">
                <i class="ci-refresh-cw fs-lg animate-target"></i>
            </button>
            <button type="button"
                class="btn btn-lg btn-primary w-100 animate-slide-end order-sm-2 order-md-4 order-lg-2">
                <i class="ci-shopping-cart fs-lg animate-target ms-n1 me-2"></i>
                Thêm vào giỏ hàng
            </button>
        </div>

        {{-- Truyền dữ liệu xuống JS --}}
        <script>
            const variantData = @json($variantData);
            const availableCombinations = @json($variantCombinations);
            const attributes = @json($attributesGrouped->map(fn($values) => $values->pluck('value')));
            const initialVariantAttributes = @json($initialVariantAttributes);
        </script>
    </div>
</div>
