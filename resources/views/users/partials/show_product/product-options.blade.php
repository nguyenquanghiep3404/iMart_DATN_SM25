<div class="col-md-6 col-xl-5 offset-xl-1">
    <div class="ps-md-4 ps-xl-0 pt-md-0">

        {{-- Thuộc tính sản phẩm --}}
        @foreach ($attributes as $attrName => $attrValues)
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
                            $isChecked =
                                isset($initialVariantAttributes[$attrName]) &&
                                $initialVariantAttributes[$attrName] === $attrValue->value;
                        @endphp

                        <div class="option-container" data-attr-name="{{ $attrName }}"
                            data-attr-value="{{ $attrValue->value }}">
                            <input type="radio" class="btn-check" name="{{ $inputName }}" id="{{ $inputId }}"
                                value="{{ $attrValue->value }}" data-attr-name="{{ $attrName }}"
                                {{ $isChecked ? 'checked' : '' }}>

                            @if ($isColor)
                                <label for="{{ $inputId }}" class="color-swatch-option rounded"
                                    title="{{ $attrValue->value }}"
                                    style="width: 36px; height: 36px; display: inline-block;
                                           background-color: {{ $attrValue->meta }};
                                           cursor: pointer; transition: transform 0.2s;">
                                </label>
                            @else
                                <label for="{{ $inputId }}" class="btn btn-outline-secondary btn-sm text-nowrap">
                                    {{ $attrValue->value }}
                                </label>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @php
            $variant = $defaultVariant ?? $product->variants->first();
            $now = now();
            $salePrice = (int) $variant->sale_price;
            $originalPrice = (int) $variant->price;
            $isOnSale =
                $variant->sale_price !== null &&
                $variant->sale_price_starts_at <= $now &&
                $variant->sale_price_ends_at >= $now;
            $displayPrice = $isOnSale ? $salePrice : $originalPrice;
            $displayVariant = $variant;
            $imageToShow = $displayVariant?->primaryImage ?? $product->coverImage;
            $imageUrl = $imageToShow
                ? Storage::url($imageToShow->path)
                : asset('assets/admin/img/placeholder-image.png');
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

        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <div class="count-input d-flex align-items-center border rounded px-2">
                <button type="button" class="btn btn-icon btn-sm px-2" data-decrement>
                    <i class="ci-minus"></i>
                </button>
                <input type="number" class="form-control border-0 text-center" value="1" min="1"
                    max="5" readonly style="width: 60px;">
                <button type="button" class="btn btn-icon btn-sm px-2" data-increment>
                    <i class="ci-plus"></i>
                </button>
            </div>

            <button type="button" class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center">
                <i class="ci-shopping-cart fs-lg me-2"></i> Thêm vào giỏ hàng
            </button>

            <button type="button" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip"
                data-bs-title="Thêm vào yêu thích">
                <i class="ci-heart fs-lg"></i>
            </button>

            <button class="btn btn-icon btn-outline-warning btn-compare"
                data-variant-id="{{ $variant->id }}"
                data-product-id="{{ $product->id }}"
                data-product-name="{{ $product->name }}"
                data-product-image="{{ $imageUrl }}"
                data-variant-label="{{ $variant->attributeValues->pluck('value')->join(' / ') }}"
                data-bs-toggle="tooltip" title="Thêm vào danh sách so sánh">
                <i class="ci-bar-chart fs-lg"></i>
            </button>
        </div>

        {{-- ✅ Nút xem danh sách so sánh (dropdown) --}}
        <div class="dropdown mb-3 w-100">
            <button class="btn btn-outline-info w-100 dropdown-toggle d-flex justify-content-between align-items-center"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span>Xem danh sách so sánh (<span id="compare-count">0</span>)</span>
            </button>

            <ul class="dropdown-menu w-100 shadow" id="compare-dropdown">
                <li class="text-center text-muted small px-3 py-2">Chưa có sản phẩm nào.</li>
            </ul>
        </div>
    </div>
</div>

<script>
    const maxCompare = 3;

    function removeFromCompare(variantId) {
        let list = JSON.parse(localStorage.getItem('compare_products') || '[]');
        list = list.filter(item => item.id != variantId);
        localStorage.setItem('compare_products', JSON.stringify(list));
        updateCompareDropdown();
    }

    function updateCompareDropdown() {
        const dropdown = document.getElementById('compare-dropdown');
        const countSpan = document.getElementById('compare-count');
        const compareList = JSON.parse(localStorage.getItem('compare_products') || '[]');

        if (countSpan) countSpan.textContent = compareList.length;

        dropdown.innerHTML = '';
        if (compareList.length === 0) {
            dropdown.innerHTML = '<li class="text-center text-muted small px-3 py-2">Chưa có sản phẩm nào.</li>';
            return;
        }

        compareList.forEach(item => {
            dropdown.innerHTML += `
                <li class="d-flex align-items-center gap-3 px-3 py-3 border-bottom compare-dropdown-item">
                    <img src="${item.image}" alt="${item.name}" style="width:54px;height:54px;object-fit:cover;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,0.08);">
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-truncate">${item.name}</div>
                        <div class="small text-muted">${item.variant_label || ''}</div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger ms-2 px-3 py-1" onclick="removeFromCompare(${item.id});event.stopPropagation();">Xóa</button>
                </li>
            `;
        });

        dropdown.innerHTML += `
            <li class="text-center py-2 border-top">
                <a href="/compare?ids=${encodeURIComponent(JSON.stringify(compareList))}" class="btn btn-sm btn-primary w-100">So sánh sản phẩm</a>
            </li>
        `;
    }

    function showCompareMessage(msg, type = 'info') {
        let toast = document.getElementById('compare-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'compare-toast';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = 9999;
            toast.style.minWidth = '220px';
            toast.style.padding = '12px 20px';
            toast.style.borderRadius = '8px';
            toast.style.fontWeight = 'bold';
            toast.style.color = '#fff';
            toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
            document.body.appendChild(toast);
        }
        let bg = '#17a2b8';
        if (type === 'success') bg = '#28a745';
        if (type === 'danger') bg = '#dc3545';
        if (type === 'warning') {
            bg = '#ffc107';
            toast.style.color = '#333';
        } else {
            toast.style.color = '#fff';
        }
        toast.style.background = bg;
        toast.textContent = msg;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 1800);
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateCompareDropdown();

        // Cập nhật lại data-variant-id, data-variant-label, data-product-image cho nút so sánh khi chọn thuộc tính
        const attributeOrder = window.attributeOrder || @json($attributeOrder);
        const variantData = window.variantData || @json($variantData);
        document.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', function () {
                // Lấy key variant hiện tại
                const key = attributeOrder.map(attr => {
                    const checked = document.querySelector(`input[data-attr-name='${attr}']:checked`);
                    return checked ? checked.value : '';
                }).join('_');
                const variant = variantData[key];
                const btn = document.querySelector('.btn-compare');
                if (btn && variant) {
                    btn.setAttribute('data-variant-id', variant.variant_id);
                    btn.setAttribute('data-product-image', variant.image || '');

                    // Build label từ radio đang checked
                    let labels = [];
                    attributeOrder.forEach(attr => {
                        const checked = document.querySelector(`input[data-attr-name='${attr}']:checked`);
                        if (checked) {
                            const labelEl = checked.closest('.option-container').querySelector('label');
                            if (labelEl) {
                                labels.push(labelEl.title || labelEl.innerText.trim());
                            }
                        }
                    });
                    btn.setAttribute('data-variant-label', labels.join(' / '));
                }
            });
        });
    });

    // Thêm log debug vào sự kiện click nút so sánh
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-compare');
        if (!btn) return;
        // Lấy thông tin biến thể
        const variantId = btn.getAttribute('data-variant-id');
        const productName = btn.getAttribute('data-product-name');
        const productImage = btn.getAttribute('data-product-image');
        const variantLabel = btn.getAttribute('data-variant-label');

        // Log debug
        console.log('Clicked compare button', {
            variantId,
            productName,
            productImage,
            variantLabel
        });

        // Thêm vào localStorage
        let compareList = JSON.parse(localStorage.getItem('compare_products') || '[]');
        // Kiểm tra trùng
        if (compareList.some(item => item.id == variantId)) {
            showCompareMessage('Biến thể này đã có trong danh sách so sánh!', 'warning');
            return;
        }
        // Giới hạn số lượng
        if (compareList.length >= maxCompare) {
            showCompareMessage('Bạn chỉ có thể so sánh tối đa 3 sản phẩm!', 'danger');
            return;
        }
        // Thêm mới
        compareList.push({
            id: variantId,
            name: productName,
            image: productImage,
            variant_label: variantLabel
        });
        localStorage.setItem('compare_products', JSON.stringify(compareList));
        updateCompareDropdown();
        showCompareMessage('Đã thêm vào danh sách so sánh!', 'success');
    });
</script>


<style>
#compare-dropdown li.compare-dropdown-item {
    min-width: 260px;
    font-size: 1.08rem;
}
#compare-dropdown img {
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
#compare-dropdown .btn-outline-danger {
    font-size: 1rem;
    padding: 2px 16px;
    border-radius: 6px;
}
</style>
