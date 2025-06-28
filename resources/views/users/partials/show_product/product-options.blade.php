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

                    if (!$attributesGrouped[$attrName]->contains('value', $value)) {
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

        {{-- Hiển thị giá và trạng thái --}}
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

        {{-- Form thêm vào giỏ --}}
        <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
            @csrf
        
            {{-- Input ẩn khác --}}
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variant_key" id="variant_key_input">
        
            {{-- Số lượng + nút --}}
            <div class="d-flex flex-wrap flex-sm-nowrap flex-md-wrap flex-lg-nowrap gap-3 gap-lg-2 gap-xl-3 mb-4">
                <div class="count-input flex-shrink-0 order-sm-1">
                    <button type="button" class="btn btn-icon btn-lg" data-decrement aria-label="Giảm số lượng">
                        <i class="ci-minus"></i>
                    </button>
                    <input
                        type="number"
                        class="form-control form-control-lg"
                        name="quantity"
                        id="quantity_input"
                        value="1"
                        min="1"
                        max="5"
                        readonly
                    >
                    <button type="button" class="btn btn-icon btn-lg" data-increment aria-label="Tăng số lượng">
                        <i class="ci-plus"></i>
                    </button>
                </div>
        
                <button type="button" class="btn btn-icon btn-lg btn-secondary order-sm-3 order-md-2 order-lg-3"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add to Wishlist">
                    <i class="ci-heart fs-lg"></i>
                </button>
                <button type="button" class="btn btn-icon btn-lg btn-secondary order-sm-4 order-md-3 order-lg-4"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Compare">
                    <i class="ci-refresh-cw fs-lg"></i>
                </button>
                <button type="submit"
                    class="btn btn-lg btn-primary w-100 order-sm-2 order-md-4 order-lg-2">
                    <i class="ci-shopping-cart fs-lg ms-n1 me-2"></i>
                    Thêm vào giỏ hàng
                </button>
            </div>
        </form>
        

        {{-- JavaScript --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const variantData = @json($variantData);
                const availableCombinations = @json($variantCombinations);
                const attributes = @json($attributesGrouped->map(fn($values) => $values->pluck('value')));
                const initialVariantAttributes = @json($initialVariantAttributes);
        
                const variantKeyInput = document.getElementById('variant_key_input');
                const quantityInput = document.getElementById('quantity_input');
        
                const attributeOrder = Object.keys(initialVariantAttributes);
                let currentSelections = { ...initialVariantAttributes };
        
                // Cập nhật variant_key
                function updateVariantKey() {
                    const key = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
                    variantKeyInput.value = key;
                }
        
                // Lắng nghe thay đổi thuộc tính
                document.querySelectorAll('input[type="radio"][data-attr-name]').forEach(input => {
                    input.addEventListener('change', function () {
                        const attrName = this.dataset.attrName;
                        const attrValue = this.value;
                        currentSelections[attrName] = attrValue;
                        updateVariantKey();
                    });
                });
        
                // Tăng số lượng
                document.querySelector('[data-increment]').addEventListener('click', () => {
                    let val = parseInt(quantityInput.value);
                    if (val < 5) {
                        quantityInput.value = val + 1;
                    }
                });
        
                // Giảm số lượng
                document.querySelector('[data-decrement]').addEventListener('click', () => {
                    let val = parseInt(quantityInput.value);
                    if (val > 1) {
                        quantityInput.value = val - 1;
                    }
                });
        
                // Khởi tạo ban đầu
                updateVariantKey();
            });
        </script>
        
    </div>
</div>
