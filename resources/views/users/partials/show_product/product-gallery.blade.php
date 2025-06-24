<div class="col-md-6 mb-4 mb-md-0 position-relative">
    <!-- Preview (Large image) -->
    <div class="swiper"
        data-swiper='{
            "loop": true,
            "navigation": {
                "prevEl": ".btn-prev",
                "nextEl": ".btn-next"
            },
            "thumbs": {
                "swiper": "#thumbs"
            }
        }'>
        <div class="swiper-wrapper" id="main-image-slides">
            @php
                // Lấy ảnh mặc định từ defaultVariant (ưu tiên primary_image_id) hoặc product
                $defaultImages = [];
                if ($defaultVariant) {
                    if ($defaultVariant->primary_image_id) {
                        $primaryImage = $defaultVariant->primaryImage;
                        if ($primaryImage) {
                            $defaultImages[] = Storage::url($primaryImage->path);
                        }
                    }
                    $defaultImages = array_merge($defaultImages, $defaultVariant->images->map(fn($image) => Storage::url($image->path))->toArray());
                } elseif ($product->coverImage) {
                    $defaultImages[] = Storage::url($product->coverImage->path);
                }
                foreach ($product->galleryImages as $galleryImage) {
                    $defaultImages[] = Storage::url($galleryImage->path);
                }
                $defaultImages = array_unique($defaultImages);
            @endphp

            @foreach ($defaultImages as $image)
                <div class="swiper-slide">
                    <div class="ratio ratio-4x3">
                        <img id="variant-image" src="{{ $image }}"
                            alt="{{ $product->name }}"
                            style="max-height: 400px; object-fit: contain; margin: 40px 0 20px 0;">
                    </div>
                </div>
            @endforeach

            @if (empty($defaultImages))
                <div class="swiper-slide">
                    <div class="ratio ratio-4x3">
                        <img src="{{ asset('images/placeholder.jpg') }}" alt="No image available"
                            style="max-height: 400px; object-fit: contain; margin: 40px 0 20px 0;">
                    </div>
                </div>
            @endif
        </div>

        <!-- Prev button -->
        <div class="position-absolute top-50 start-0 z-2 translate-middle-y ms-sm-2 ms-lg-3">
            <button type="button"
                class="btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start"
                aria-label="Prev">
                <i class="ci-chevron-left fs-lg animate-target"></i>
            </button>
        </div>

        <!-- Next button -->
        <div class="position-absolute top-50 end-0 z-2 translate-middle-y me-sm-2 me-lg-3">
            <button type="button"
                class="btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end"
                aria-label="Next">
                <i class="ci-chevron-right fs-lg animate-target"></i>
            </button>
        </div>
    </div>

    <!-- Thumbnails -->
    <div class="swiper swiper-load swiper-thumbs pt-2 mt-1" id="thumbs"
        data-swiper='{
            "loop": true,
            "spaceBetween": 10,
            "slidesPerView": 4,
            "watchSlidesProgress": true,
            "breakpoints": {
                "340": {"slidesPerView": 4},
                "500": {"slidesPerView": 5},
                "600": {"slidesPerView": 5},
                "768": {"slidesPerView": 4},
                "992": {"slidesPerView": 5},
                "1200": {"slidesPerView": 5}
            }
        }'>
        <div class="swiper-wrapper" id="variant-gallery">
            @foreach ($defaultImages as $image)
                <div class="swiper-slide swiper-thumb">
                    <div class="ratio ratio-4x3" style="max-width: 80px;">
                        <img src="{{ $image }}" class="swiper-thumb-img"
                            alt="{{ $product->name }}" style="object-fit: contain;">
                    </div>
                </div>
            @endforeach

            @if (empty($defaultImages))
                <div class="swiper-slide swiper-thumb">
                    <div class="ratio ratio-4x3" style="max-width: 80px;">
                        <img src="{{ asset('images/placeholder.jpg') }}" class="swiper-thumb-img"
                            alt="No image available" style="object-fit: contain;">
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Hàm cập nhật ảnh chính (ưu tiên primary_image_id)
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

    // Hàm cập nhật thumbnails
    function updateThumbnails(images) {
        const gallery = document.getElementById('variant-gallery');
        if (gallery) {
            gallery.innerHTML = '';
            images.forEach(image => {
                const slide = document.createElement('div');
                slide.className = 'swiper-slide swiper-thumb';
                slide.innerHTML = `
                    <div class="ratio ratio-4x3" style="max-width: 80px;">
                        <img src="${image}" class="swiper-thumb-img" alt="{{ $product->name }}" style="object-fit: contain;">
                    </div>
                `;
                gallery.appendChild(slide);
            });

            // Reinitialize Swiper for thumbnails
            const thumbSwiper = new Swiper('#thumbs', {
                loop: true,
                spaceBetween: 10,
                slidesPerView: 4,
                watchSlidesProgress: true,
                breakpoints: {
                    "340": { slidesPerView: 4 },
                    "500": { slidesPerView: 5 },
                    "600": { slidesPerView: 5 },
                    "768": { slidesPerView: 4 },
                    "992": { slidesPerView: 5 },
                    "1200": { slidesPerView: 5 }
                }
            });
        }
    }

    // Cập nhật gallery dựa trên variant được chọn
    window.updateGalleryFromSelection = function(variantKey) {
        const variantData = @json($variantData);
        const variant = variantData[variantKey] || null;

        if (variant) {
            // Ưu tiên primary_image_id nếu có
            let mainImage = null;
            if (variant.primary_image_id) {
                const primaryImage = @php
                    $variants = $product->variants;
                    $primaryImagePath = $variants->firstWhere('id', $variant->variant_id ?? null)?->primaryImage?->path ?? null;
                    echo $primaryImagePath ? Storage::url($primaryImagePath) : null;
                @endphp;
                mainImage = primaryImage || variant.image;
            } else {
                mainImage = variant.image;
            }

            if (mainImage) {
                updateMainImage(mainImage);
            } else {
                @php
                    $fallbackImage = $product->coverImage ? Storage::url($product->coverImage->path) : asset('images/placeholder.jpg');
                @endphp
                updateMainImage('{{ $fallbackImage }}');
            }

            // Cập nhật thumbnails
            const thumbnailImages = variant.images || [];
            if (thumbnailImages.length > 0) {
                updateThumbnails(thumbnailImages);
            } else {
                @php
                    $fallbackImages = [];
                    if ($product->coverImage) {
                        $fallbackImages[] = Storage::url($product->coverImage->path);
                    }
                    foreach ($product->galleryImages as $galleryImage) {
                        $fallbackImages[] = Storage::url($galleryImage->path);
                    }
                    $fallbackImages = array_unique($fallbackImages);
                @endphp
                updateThumbnails(@json($fallbackImages) || ['{{ asset('images/placeholder.jpg') }}']);
            }
        } else {
            // Fallback nếu không có variant
            @php
                $fallbackImage = $product->coverImage ? Storage::url($product->coverImage->path) : asset('images/placeholder.jpg');
                $fallbackImages = [];
                if ($product->coverImage) {
                    $fallbackImages[] = Storage::url($product->coverImage->path);
                }
                foreach ($product->galleryImages as $galleryImage) {
                    $fallbackImages[] = Storage::url($galleryImage->path);
                }
                $fallbackImages = array_unique($fallbackImages);
            @endphp
            updateMainImage('{{ $fallbackImage }}');
            updateThumbnails(@json($fallbackImages) || ['{{ asset('images/placeholder.jpg') }}']);
        }
    };

    // Khởi tạo gallery với variant mặc định
    document.addEventListener('DOMContentLoaded', function() {
        const defaultVariantKey = @php
            $defaultAttrs = $defaultVariant ? $defaultVariant->attributeValues->pluck('value', 'attribute.name')->all() : [];
            $keys = array_keys($attributes);
            echo json_encode(implode('_', array_map(fn($key) => $defaultAttrs[$key] ?? '', $keys)));
        @endphp;
        if (defaultVariantKey) {
            window.updateGalleryFromSelection(defaultVariantKey);
        }
    });
</script>