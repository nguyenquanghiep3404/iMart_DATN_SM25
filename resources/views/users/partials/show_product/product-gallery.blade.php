@php
    // Lấy ảnh cho biến thể mặc định (nếu có), nếu không thì fallback về ảnh sản phẩm cha
    $initialImages = [];
    if ($defaultVariant && $defaultVariant->images->count()) {
        $initialImages = $defaultVariant->images->map(fn($img) => Storage::url($img->path))->toArray();
    } elseif ($product->coverImage) {
        $initialImages[] = Storage::url($product->coverImage->path);
    }
    foreach ($product->galleryImages as $galleryImage) {
        $initialImages[] = Storage::url($galleryImage->path);
    }
    $initialImages = array_unique($initialImages);
@endphp

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
        <div class="swiper-wrapper" id="main-image-slides"></div>

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
        <div class="swiper-wrapper" id="variant-gallery"></div>
    </div>
</div>

<script>
    const variantData = @json($variantData);
    const attributeOrder = @json($attributeOrder);

    let mainSwiper = null;
    let thumbSwiper = null;

    document.addEventListener('DOMContentLoaded', function () {
        function updateGallery(images) {
            const mainWrapper = document.querySelector('#main-image-slides');
            const thumbWrapper = document.querySelector('#variant-gallery');

            // Destroy Swiper instances if exist
            if (mainSwiper && typeof mainSwiper.destroy === 'function') {
                mainSwiper.destroy(true, true);
                mainSwiper = null;
            }
            if (thumbSwiper && typeof thumbSwiper.destroy === 'function') {
                thumbSwiper.destroy(true, true);
                thumbSwiper = null;
            }

            mainWrapper.innerHTML = '';
            thumbWrapper.innerHTML = '';

            images.forEach((img) => {
                // Main slide
                const mainSlide = document.createElement('div');
                mainSlide.className = 'swiper-slide';
                mainSlide.innerHTML = `
                    <div class="ratio ratio-4x3">
                        <img src="${img}" alt="Product Image"
                             style="max-height: 400px; object-fit: contain; margin: 40px 0 20px 0;">
                    </div>`;
                mainWrapper.appendChild(mainSlide);

                // Thumbnail slide
                const thumbSlide = document.createElement('div');
                thumbSlide.className = 'swiper-slide swiper-thumb';
                thumbSlide.innerHTML = `
                    <div class="ratio ratio-4x3" style="max-width: 80px;">
                        <img src="${img}" alt="Thumb" style="object-fit: contain;">
                    </div>`;
                thumbWrapper.appendChild(thumbSlide);
            });

            // Re-init Swiper
            thumbSwiper = new Swiper(document.getElementById('thumbs'), {
                loop: false,
                spaceBetween: 10,
                slidesPerView: 4,
                watchSlidesProgress: true,
                breakpoints: {
                    340: { slidesPerView: 3 },
                    500: { slidesPerView: 4 },
                    768: { slidesPerView: 5 },
                    992: { slidesPerView: 6 },
                }
            });

            mainSwiper = new Swiper(document.querySelector('.swiper'), {
                loop: false,
                navigation: {
                    nextEl: '.btn-next',
                    prevEl: '.btn-prev',
                },
                thumbs: {
                    swiper: thumbSwiper,
                }
            });

            mainSwiper.slideTo(0);
        }

        function getSelectedVariantKey() {
            return attributeOrder.map(attr => {
                const input = document.querySelector(`input[data-attr-name="${attr}"]:checked`);
                return input ? input.value : '';
            }).join('_');
        }

        function ensureAllAttributesChecked() {
            attributeOrder.forEach(attr => {
                const checked = document.querySelector(`input[data-attr-name="${attr}"]:checked`);
                if (!checked) {
                    const first = document.querySelector(`input[data-attr-name="${attr}"]`);
                    if (first) first.checked = true;
                }
            });
        }

        window.updateGalleryFromSelection = function (variantKey) {
            const variant = variantData[variantKey];
            console.log('Selected variantKey:', variantKey, 'variant:', variant);
            if (!variant) return;

            let images = [];
            if (variant.images && variant.images.length > 0) {
                if (variant.primary_image_id && variant.image) {
                    // Đưa ảnh chính lên đầu nếu chưa có
                    if (variant.images[0] !== variant.image) {
                        images = [variant.image, ...variant.images.filter(img => img !== variant.image)];
                    } else {
                        images = variant.images;
                    }
                } else {
                    images = variant.images;
                }
            } else {
                images = ['{{ asset("images/placeholder.jpg") }}'];
            }

            updateGallery(images);
        };

        // Khởi tạo ban đầu sau khi toàn bộ trang đã load (đảm bảo radio đã checked)
        window.addEventListener('load', function() {
            ensureAllAttributesChecked();
            const defaultKey = getSelectedVariantKey();
            if (defaultKey) {
                window.updateGalleryFromSelection(defaultKey);
            }
        });
    });
</script>

