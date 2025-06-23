@extends('users.layouts.app')

@section('title', $product->name . ' - iMart')

@section('meta')
    <meta name="description" content="{{ $product->meta_description }}">
    <meta name="keywords" content="{{ $product->meta_keywords }}">
@endsection


@section('content')
    <main class="content-wrapper">
        {{-- Breadcrumb --}}
        <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        {{-- Tiêu đề sản phẩm --}}
        <h1 class="h3 container mb-4">{{ $product->name }}</h1>

        {{-- Tabs --}}
        <section class="container pb-2 pb-lg-4">
            <div class="d-flex align-items-center border-bottom">
                <ul class="nav nav-underline flex-nowrap gap-4">
                    <li class="nav-item me-sm-2">
                        <a class="nav-link pe-none active" href="#!">Thông tin chung</a>
                    </li>
                    <li class="nav-item me-sm-2">
                        <a class="nav-link" href="#details">Chi tiết sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#reviews">Đánh giá ({{ $product->reviews_count }})</a>
                    </li>
                </ul>
                <a class="d-none d-md-flex align-items-center gap-2 text-decoration-none ms-auto mb-1" href="#reviews">
                    <div class="d-flex gap-1 fs-sm">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="ci-star{{ $i <= $product->average_rating ? '-filled' : '' }} text-warning"></i>
                        @endfor
                    </div>
                    <span class="text-body-tertiary fs-xs">{{ $product->reviews_count }} reviews</span>
                </a>
            </div>
        </section>

        {{-- Gallery + Options --}}
        <section class="container pb-5 mb-1 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
            <div class="row align-items-start">
                @include('users.partials.show_product.product-gallery')
                @include('users.partials.show_product.product-options')
            </div>
        </section>

        {{-- Chi tiết + Đánh giá --}}
        @include('users.partials.show_product.product-details')
        @include('users.partials.show_product.reviews-section')
        @include('users.review')


        {{-- Sản phẩm liên quan --}}
        @include('users.partials.show_product.related-products')

        <section style="margin: 80px 0;"></section>
    </main>
@endsection
@push('styles')
<style>
    .option-container {
        display: inline-block;
    }

    .btn-check:checked + .btn,
    .btn-check:checked + .color-swatch-option {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .color-swatch-option {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        border: 2px solid #e0e0e0;
        cursor: pointer;
        transition: 0.3s ease-in-out;
        display: inline-block;
    }

    .color-swatch-option:hover {
        transform: scale(1.05);
        border-color: #adb5bd;
    }

    .product-price-box {
        font-size: 1.5rem;
        font-weight: bold;
        color: #dc3545;
    }

    .product-original-price {
        text-decoration: line-through;
        color: #6c757d;
        margin-left: 10px;
    }

    .product-status {
        font-size: 0.9rem;
        color: #198754;
    }

    .count-input {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .count-input input {
        text-align: center;
        width: 60px;
        height: 45px;
    }
</style>
@endpush


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const priceElement = document.querySelector('#product-price');
        const originalPriceElement = document.querySelector('#original-price');
        const selectedColorName = document.getElementById('selected-color-name');

        const availableCombinations = @json($availableCombinations);
        const variantData = @json($variantData);
        const attributes = @json($attributes);
        const attributeOrder = @json($attributeOrder);

        let currentSelections = @php
            $initialSelections = $defaultVariant ? $defaultVariant->attributeValues->pluck('value', 'attribute.name')->all() : [];
            echo json_encode($initialSelections);
        @endphp;

        function updateAvailableOptions() {
            let newlyAvailableOptions = {};

            attributeOrder.forEach((attrName, attrIndex) => {
                newlyAvailableOptions[attrName] = new Set();

                availableCombinations.forEach(combination => {
                    let isMatch = true;
                    for (let i = 0; i < attrIndex; i++) {
                        const prevAttr = attributeOrder[i];
                        if (currentSelections[prevAttr] !== combination[prevAttr]) {
                            isMatch = false;
                            break;
                        }
                    }
                    if (isMatch && combination[attrName]) {
                        newlyAvailableOptions[attrName].add(combination[attrName]);
                    }
                });

                document.querySelectorAll(`.option-container[data-attr-name="${attrName}"]`).forEach(container => {
                    const value = container.getAttribute('data-attr-value');
                    const input = container.querySelector('input[type="radio"]');
                    if (newlyAvailableOptions[attrName].has(value)) {
                        container.style.display = 'inline-block';
                    } else {
                        container.style.display = 'none';
                        if (input && input.checked) input.checked = false;
                    }
                });

                const valuesSet = newlyAvailableOptions[attrName];
                const currentSelectedValue = currentSelections[attrName];
                if (!valuesSet.has(currentSelectedValue)) {
                    const firstValue = Array.from(valuesSet)[0];
                    currentSelections[attrName] = firstValue || null;
                }

                if (currentSelections[attrName]) {
                    const radio = document.querySelector(`input[data-attr-name="${attrName}"][value="${currentSelections[attrName]}"]`);
                    if (radio) radio.checked = true;
                }
            });

            if (selectedColorName && currentSelections['Màu sắc']) {
                selectedColorName.textContent = currentSelections['Màu sắc'];
            } else if (selectedColorName) {
                selectedColorName.textContent = 'N/A';
            }

            updateSelectedColorClass();
        }

        function updateSelectedColorClass() {
            document.querySelectorAll('.color-swatch-option').forEach(label => {
                label.classList.remove('selected');
            });
            const checkedColorInput = document.querySelector('input[data-attr-name="Màu sắc"]:checked');
            if (checkedColorInput) {
                const label = document.querySelector(`label[for="${checkedColorInput.id}"]`);
                if (label) label.classList.add('selected');
            }
        }

        function updateVariantInfo() {
            const variantKey = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
            const variant = variantData[variantKey];

            if (variant) {
                if (priceElement) {
                    priceElement.textContent = new Intl.NumberFormat('vi-VN').format(variant.price) + 'đ';
                }
                if (originalPriceElement) {
                    if (variant.original_price) {
                        originalPriceElement.textContent = new Intl.NumberFormat('vi-VN').format(variant.original_price) + 'đ';
                        originalPriceElement.style.display = '';
                    } else {
                        originalPriceElement.style.display = 'none';
                    }
                }

                const statusElement = document.getElementById('variant-status');
                if (statusElement) {
                    statusElement.textContent = variant.status;
                }

                updateMainImage(variant.image);
            }
        }

        function updateMainImage(src) {
            const variantImage = document.getElementById('variant-image');
            if (variantImage && src) {
                variantImage.src = src;
            }

            const mainSwiperEl = document.querySelector('.swiper');
            if (mainSwiperEl && mainSwiperEl.swiper) {
                const mainSwiper = mainSwiperEl.swiper;
                const slideIndex = Array.from(mainSwiper.slides).findIndex(slide => {
                    const img = slide.querySelector('img');
                    return img && img.src.includes(src);
                });
                if (slideIndex !== -1) {
                    mainSwiper.slideTo(slideIndex);
                }
            }
        }

        document.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', function() {
                const attrName = this.dataset.attrName;
                const attrValue = this.value;
                currentSelections[attrName] = attrValue;

                updateAvailableOptions();
                updateVariantInfo();
                updateSelectedColorClass(); // ✅ Thêm dòng này để xử lý viền hover lại
            });
        });

        updateAvailableOptions();
        updateVariantInfo();
    });
</script>
@endpush

