@extends('users.layouts.app')

@section('title', $product->name . ' - iMart')

@section('meta')
    <meta name="description" content="{{ $product->meta_description }}">
    <meta name="keywords" content="{{ $product->meta_keywords }}">
@endsection

@section('content')
    <main class="content-wrapper">
        <!-- Breadcrumb -->
        <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <!-- Page title -->
        <h1 class="h3 container mb-4">{{ $product->name }}</h1>

        <!-- Nav links + Reviews -->
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

        <!-- Gallery + Product options -->
        <section class="container pb-5 mb-1 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
            <div class="row">
                <!-- Product gallery -->
                <div class="col-md-6">
                    <!-- Preview (Large image) -->
                    <div class="swiper"
                        data-swiper="{
          &quot;loop&quot;: true,
          &quot;navigation&quot;: {
            &quot;prevEl&quot;: &quot;.btn-prev&quot;,
            &quot;nextEl&quot;: &quot;.btn-next&quot;
          },
          &quot;thumbs&quot;: {
            &quot;swiper&quot;: &quot;#thumbs&quot;
          }
        }">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="ratio ratio-1x1">
                                    <img src="{{ asset('assets/img/shop/single/gallery/01.jpg') }}"
                                        data-zoom="{{ asset('assets/img/shop/single/gallery/01.jpg') }}"
                                        data-zoom-options="{
                  &quot;paneSelector&quot;: &quot;#zoomPane&quot;,
                  &quot;inlinePane&quot;: 768,
                  &quot;hoverDelay&quot;: 500,
                  &quot;touchDisable&quot;: true
                }"
                                        alt="{{ $product->name }}">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="ratio ratio-1x1">
                                    <img src="{{ asset('assets/img/shop/single/gallery/02.jpg') }}"
                                        data-zoom="{{ asset('assets/img/shop/single/gallery/02.jpg') }}"
                                        data-zoom-options="{
                  &quot;paneSelector&quot;: &quot;#zoomPane&quot;,
                  &quot;inlinePane&quot;: 768,
                  &quot;hoverDelay&quot;: 500,
                  &quot;touchDisable&quot;: true
                }"
                                        alt="{{ $product->name }}">
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="ratio ratio-1x1">
                                    <img src="{{ asset('assets/img/shop/single/gallery/03.jpg') }}"
                                        data-zoom="{{ asset('assets/img/shop/single/gallery/03.jpg') }}"
                                        data-zoom-options="{
                  &quot;paneSelector&quot;: &quot;#zoomPane&quot;,
                  &quot;inlinePane&quot;: 768,
                  &quot;hoverDelay&quot;: 500,
                  &quot;touchDisable&quot;: true
                }"
                                        alt="{{ $product->name }}">
                                </div>
                            </div>
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
                        data-swiper="{
          &quot;loop&quot;: true,
          &quot;spaceBetween&quot;: 12,
          &quot;slidesPerView&quot;: 3,
          &quot;watchSlidesProgress&quot;: true,
          &quot;breakpoints&quot;: {
            &quot;340&quot;: {
              &quot;slidesPerView&quot;: 4
            },
            &quot;500&quot;: {
              &quot;slidesPerView&quot;: 5
            },
            &quot;600&quot;: {
              &quot;slidesPerView&quot;: 6
            },
            &quot;768&quot;: {
              &quot;slidesPerView&quot;: 4
            },
            &quot;992&quot;: {
              &quot;slidesPerView&quot;: 5
            },
            &quot;1200&quot;: {
              &quot;slidesPerView&quot;: 6
            }
          }
        }">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide swiper-thumb">
                                <div class="ratio ratio-1x1" style="max-width: 94px">
                                    <img src="{{ asset('assets/img/shop/single/gallery/th01.jpg') }}"
                                        class="swiper-thumb-img" alt="{{ $product->name }}">
                                </div>
                            </div>
                            <div class="swiper-slide swiper-thumb">
                                <div class="ratio ratio-1x1" style="max-width: 94px">
                                    <img src="{{ asset('assets/img/shop/single/gallery/th02.jpg') }}"
                                        class="swiper-thumb-img" alt="{{ $product->name }}">
                                </div>
                            </div>
                            <div class="swiper-slide swiper-thumb">
                                <div class="ratio ratio-1x1" style="max-width: 94px">
                                    <img src="{{ asset('assets/img/shop/single/gallery/th03.jpg') }}"
                                        class="swiper-thumb-img" alt="{{ $product->name }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product options -->
                <div class="col-md-6 col-xl-5 offset-xl-1 pt-4">
                    <div class="ps-md-4 ps-xl-0">
                        <div class="position-relative" id="zoomPane">
                            @php
                                // Gom nhóm các attributeValue của tất cả variants theo attribute name
                                $attributesGrouped = collect();

                                foreach ($product->variants as $variant) {
                                    foreach ($variant->attributeValues as $attrValue) {
                                        $attrName = $attrValue->attribute->name;

                                        if (!$attributesGrouped->has($attrName)) {
                                            $attributesGrouped[$attrName] = collect();
                                        }

                                        // Thêm vào nếu chưa có giá trị trùng (unique theo 'value')
                                        if (!$attributesGrouped[$attrName]->contains('value', $attrValue->value)) {
                                            // Nếu muốn lọc bỏ giá trị không mong muốn, có thể thêm điều kiện ở đây
                                            // Ví dụ: if ($attrValue->value !== 'Giá trị không mong muốn')
                                            $attributesGrouped[$attrName]->push($attrValue);
                                        }
                                    }
                                }
                            @endphp

                            @foreach ($attributesGrouped as $attrName => $attrValues)
                                <div class="pb-3 mb-2 mb-lg-3">
                                    <label class="form-label fw-semibold pb-1 mb-2">{{ $attrName }}</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($attrValues as $index => $attrValue)
                                            @php
                                                $inputName = strtolower(str_replace(' ', '-', $attrName)) . '-options';
                                                $inputId = $inputName . '-' . $attrValue->id;
                                            @endphp

                                            <input type="radio" class="btn-check" name="{{ $inputName }}"
                                                id="{{ $inputId }}" value="{{ $attrValue->id }}"
                                                {{ $index === 0 ? 'checked' : '' }}>

                                            @if ($attrValue->attribute->display_type === 'color_swatch' && $attrValue->meta)
                                                <label for="{{ $inputId }}"
                                                    class="btn btn-sm btn-outline-secondary p-1"
                                                    style="width: 32px; height: 32px; background-color: {{ $attrValue->meta }}; border: 1px solid #ccc; border-radius: 4px;"
                                                    title="{{ $attrValue->value }}"></label>
                                            @else
                                                <label for="{{ $inputId }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    {{ $attrValue->value }}
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach


                            <!-- Price -->
                            <div class="d-flex flex-wrap align-items-center mb-3">
                                <div class="h4 mb-0 me-3" id="variant-price">
                                    {{ number_format($product->variants->first()->price) }}đ</div>
                                <div class="d-flex align-items-center text-success fs-sm ms-auto">
                                    <i class="ci-check-circle fs-base me-2"></i>
                                    <span id="variant-status">{{ $product->variants->first()->status }}</span>
                                </div>
                            </div>

                            <!-- Count + Buttons -->
                            <div
                                class="d-flex flex-wrap flex-sm-nowrap flex-md-wrap flex-lg-nowrap gap-3 gap-lg-2 gap-xl-3 mb-4">
                                <div class="count-input flex-shrink-0 order-sm-1">
                                    <button type="button" class="btn btn-icon btn-lg" data-decrement=""
                                        aria-label="Decrement quantity">
                                        <i class="ci-minus"></i>
                                    </button>
                                    <input type="number" class="form-control form-control-lg" value="1"
                                        min="1" max="5" readonly>
                                    <button type="button" class="btn btn-icon btn-lg" data-increment=""
                                        aria-label="Increment quantity">
                                        <i class="ci-plus"></i>
                                    </button>
                                </div>
                                <button type="button"
                                    class="btn btn-icon btn-lg btn-secondary animate-pulse order-sm-3 order-md-2 order-lg-3"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-sm"
                                    data-bs-title="Add to Wishlist" aria-label="Add to Wishlist">
                                    <i class="ci-heart fs-lg animate-target"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-icon btn-lg btn-secondary animate-rotate order-sm-4 order-md-3 order-lg-4"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-sm"
                                    data-bs-title="Compare" aria-label="Compare">
                                    <i class="ci-refresh-cw fs-lg animate-target"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-lg btn-primary w-100 animate-slide-end order-sm-2 order-md-4 order-lg-2">
                                    <i class="ci-shopping-cart fs-lg animate-target ms-n1 me-2"></i>
                                    Thêm vào giỏ hàng
                                </button>
                            </div>


                        </div>

                        <script>
                            // JS để cập nhật giá và trạng thái khi chọn biến thể khác
                            document.querySelectorAll('input[name="model-options"]').forEach(radio => {
                                radio.addEventListener('change', function() {
                                    const variantId = this.value;
                                    // Dữ liệu biến thể (id => price, status) bạn có thể gửi kèm qua data attribute hoặc JSON
                                    const variants = @json(
                                        $product->variants->mapWithKeys(function ($item) {
                                            return [$item->id => ['price' => number_format($item->price), 'status' => $item->status]];
                                        }));

                                    if (variants[variantId]) {
                                        document.getElementById('variant-price').textContent = variants[variantId].price + 'đ';
                                        document.getElementById('variant-status').textContent = variants[variantId].status;
                                    }
                                });
                            });
                        </script>


                        <!-- Shipping options -->
                        {{-- <div class="d-flex align-items-center pb-2">
            <h3 class="h6 mb-0">Shipping options</h3>
            <a class="btn btn-sm btn-secondary ms-auto" href="#!">
              <i class="ci-map-pin fs-sm ms-n1 me-1"></i>
              Find local store
            </a>
          </div>
          <table class="table table-borderless fs-sm mb-2">
            <tbody>
              <tr>
                <td class="py-2 ps-0">Pickup from the store</td>
                <td class="py-2">Today</td>
                <td class="text-body-emphasis fw-semibold text-end py-2 pe-0">Free</td>
              </tr>
              <tr>
                <td class="py-2 ps-0">Pickup from postal offices</td>
                <td class="py-2">Tomorrow</td>
                <td class="text-body-emphasis fw-semibold text-end py-2 pe-0">$25.00</td>
              </tr>
              <tr>
                <td class="py-2 ps-0">Delivery by courier</td>
                <td class="py-2">2-3 days</td>
                <td class="text-body-emphasis fw-semibold text-end py-2 pe-0">$35.00</td>
              </tr>
            </tbody>
          </table> --}}

                        <!-- Warranty + Payment info accordion -->
                        <div class="accordion" id="infoAccordion">
                            <!-- Accordion item: Thông tin bảo hành -->
                            <div class="accordion-item border-top">
                                <h3 class="accordion-header" id="headingWarranty">
                                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                                        data-bs-target="#warranty" aria-expanded="false" aria-controls="warranty">
                                        Thông tin bảo hành
                                    </button>
                                </h3>
                                <div id="warranty" class="accordion-collapse collapse" aria-labelledby="headingWarranty"
                                    data-bs-parent="#infoAccordion">
                                    <div class="accordion-body">
                                        <div class="alert d-flex alert-info mb-3" role="alert">
                                            <i class="ci-check-shield fs-xl mt-1 me-2"></i>
                                            <div class="fs-sm">
                                                <span class="fw-semibold">Bảo hành:</span> 12 tháng bảo hành chính hãng.
                                                Đổi/trả sản phẩm trong vòng 14 ngày.
                                            </div>
                                        </div>
                                        <p class="mb-0">
                                            Khám phá chi tiết về <a class="fw-medium" href="#!">chính sách bảo hành
                                                sản phẩm</a>, bao gồm thời hạn, phạm vi bảo hành và các gói bảo vệ bổ sung
                                            có sẵn. Chúng tôi ưu tiên sự hài lòng của bạn, và thông tin bảo hành được thiết
                                            kế để giúp bạn nắm rõ và tự tin khi mua hàng.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Accordion item: Thanh toán và thẻ tín dụng -->
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingPayment">
                                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                                        data-bs-target="#payment" aria-expanded="false" aria-controls="payment">
                                        Thanh toán và thẻ tín dụng
                                    </button>
                                </h3>
                                <div id="payment" class="accordion-collapse collapse" aria-labelledby="headingPayment"
                                    data-bs-parent="#infoAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-0">
                                            Trải nghiệm giao dịch dễ dàng với <a class="fw-medium" href="#!">các
                                                phương thức thanh toán linh hoạt</a> và dịch vụ tín dụng. Tìm hiểu thêm về
                                            các phương thức thanh toán được chấp nhận, kế hoạch trả góp và các ưu đãi tín
                                            dụng độc quyền có sẵn để làm cho trải nghiệm mua sắm của bạn trở nên suôn sẻ.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Product details and Reviews shared container -->
        <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
            <div class="row">
                <div class="col-md-7">
                    <!-- Product details -->
                    <h2 class="h3 pb-2 pb-md-3" id="details">Chi tiết sản phẩm</h2>
                    <h3 class="h6">Thông số kĩ thuật chung</h3>
                    <ul class="list-unstyled d-flex flex-column gap-3 fs-sm pb-3 m-0 mb-2 mb-sm-3">
                        <li class="d-flex align-items-center position-relative pe-4">
                            <span>Danh mục:</span>
                            <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                            <span class="text-dark-emphasis fw-medium text-end">
                                {{ $product->category->name ?? 'Chưa xác định' }}
                            </span>
                        </li>
                        <li class="d-flex align-items-center position-relative pe-4">
                            <span>Trạng thái:</span>
                            <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                            <span class="text-dark-emphasis fw-medium text-end">
                                {{ $product->status ?? 'Chưa xác định' }}
                            </span>
                        </li>

                        {{-- Hiển thị các thuộc tính kỹ thuật: Màu sắc, Dung lượng, RAM, Kích thước màn hình, Chất liệu vỏ --}}
                        @php
                            // Danh sách các thuộc tính cần lấy hiển thị
                            $specAttributes = ['Màu sắc', 'Dung lượng', 'RAM', 'Kích thước màn hình', 'Chất liệu vỏ'];
                        @endphp

                        @foreach ($specAttributes as $attrName)
                            <li class="d-flex align-items-center position-relative pe-4">
                                <span>{{ $attrName }}:</span>
                                <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                                <span class="text-dark-emphasis fw-medium text-end">
                                    @if (isset($attributes[$attrName]) && $attributes[$attrName]->isNotEmpty())
                                        {{ $attributes[$attrName]->pluck('value')->join(', ') }}
                                    @else
                                        Chưa xác định
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>


                    <!-- Description -->
                    <div class="pb-3">
                        <h3 class="h6">Mô tả sản phẩm</h3>
                        <div class="fs-sm">
                            {!! $product->description !!}
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div class="d-flex align-items-center pt-5 mb-4 mt-2 mt-md-3 mt-lg-4" id="reviews"
                        style="scroll-margin-top: 80px">
                        <h2 class="h3 mb-0">Đánh giá</h2>
                        <button type="button" class="btn btn-secondary ms-auto" data-bs-toggle="modal"
                            data-bs-target="#reviewForm">
                            <i class="ci-edit-3 fs-base ms-n1 me-2"></i>
                            Để lại đánh giá
                        </button>
                    </div>

                    <!-- Reviews stats -->
                    <div class="row g-4 pb-3">
                        <div class="col-sm-4">
                            <!-- Overall rating card -->
                            <div
                                class="d-flex flex-column align-items-center justify-content-center h-100 bg-body-tertiary rounded p-4">
                                <div class="h1 pb-2 mb-1">{{ $product->average_rating }}</div>
                                <div class="hstack justify-content-center gap-1 fs-sm mb-2">
                                    @php
                                        $fullStars = floor($product->average_rating);
                                        $halfStar = $product->average_rating - $fullStars >= 0.5;
                                    @endphp

                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $fullStars)
                                            <i class="ci-star-filled text-warning"></i>
                                        @elseif ($i == $fullStars + 1 && $halfStar)
                                            <i class="ci-star-half-filled text-warning"></i>
                                        @else
                                            <i class="ci-star text-body-tertiary"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="fs-sm">{{ $totalReviews }} reviews</div>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <!-- Rating breakdown by quantity -->
                            <div class="vstack gap-3">
                                @foreach (range(5, 1) as $star)
                                    <div class="hstack gap-2">
                                        <div class="hstack fs-sm gap-1">
                                            {{ $star }}<i class="ci-star-filled text-warning"></i>
                                        </div>
                                        <div class="progress w-100" role="progressbar"
                                            aria-label="{{ $star }} stars" style="height: 4px">
                                            <div class="progress-bar bg-warning rounded-pill"
                                                style="width: {{ $ratingPercentages[$star] ?? 0 }}%"></div>
                                        </div>
                                        <div class="fs-sm text-nowrap text-end" style="width: 40px;">
                                            {{ $ratingCounts[$star] ?? 0 }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>


                    <!-- Reviews list -->
                    @foreach ($product->reviews as $review)
                        <div class="border-bottom py-3 mb-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-nowrap me-3">
                                    <span class="h6 mb-0">{{ $review->user->name ?? 'Người dùng' }}</span>
                                    <i class="ci-check-circle text-success align-middle ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="top" data-bs-custom-class="tooltip-sm"
                                        data-bs-title="Verified customer"></i>
                                </div>
                                <span
                                    class="text-body-secondary fs-sm ms-auto">{{ $review->created_at->format('F d, Y') }}</span>
                            </div>
                            <div class="d-flex gap-1 fs-sm pb-2 mb-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="ci-star{{ $i <= $review->rating ? '-filled' : '' }} text-warning"></i>
                                @endfor
                            </div>
                            <p class="fs-sm">{{ $review->comment }}</p>
                            <div class="nav align-items-center">
                                <button type="button" class="nav-link animate-underline px-0">
                                    <i class="ci-corner-down-right fs-base ms-1 me-1"></i>
                                    <span class="animate-target">Trả lời</span>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    @if ($product->reviews->isEmpty())
                        <p class="text-center text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
                    @endif

                </div>

                <!-- Sticky product preview visible on screens > 991px wide (lg breakpoint) -->
                {{-- <aside class="col-md-5 col-xl-4 offset-xl-1 d-none d-md-block" style="margin-top: -100px">
        <div class="position-sticky top-0 ps-3 ps-lg-4 ps-xl-0" style="padding-top: 100px">
          <div class="border rounded p-3 p-lg-4">
            <div class="d-flex align-items-center mb-3">
              <div class="ratio ratio-1x1 flex-shrink-0" style="width: 110px">
                <img src="{{ asset('assets/img/shop/single/gallery/01.jpg') }}" width="110" alt="{{ $product->name }}">
              </div>
              <div class="w-100 min-w-0 ps-2 ps-sm-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="d-flex gap-1 fs-xs">
                    @for ($i = 1; $i <= 5; $i++)
                      <i class="ci-star{{ $i <= $product->rating ? '-filled' : '' }} text-warning"></i>
                    @endfor
                  </div>
                  <span class="text-body-tertiary fs-xs">{{ $product->reviews->count() }}</span>
                </div>
                <h4 class="fs-sm fw-medium mb-2">{{ $product->name }}</h4>
                <div class="h5 mb-0">
                  @if ($product->variants && $product->variants->isNotEmpty())
                    {{ number_format($product->variants->first()->price) }}đ
                  @endif
                </div>
              </div>
            </div>
            <div class="d-flex gap-2 gap-lg-3">
              <button type="button" class="btn btn-primary w-100 animate-slide-end">
                <i class="ci-shopping-cart fs-base animate-target ms-n1 me-2"></i>
                Add to cart
              </button>
              <button type="button" class="btn btn-icon btn-secondary animate-pulse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-sm" data-bs-title="Add to Wishlist" aria-label="Add to Wishlist">
                <i class="ci-heart fs-base animate-target"></i>
              </button>
              <button type="button" class="btn btn-icon btn-secondary animate-rotate" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-sm" data-bs-title="Compare" aria-label="Compare">
                <i class="ci-refresh-cw fs-base animate-target"></i>
              </button>
            </div>
          </div>
        </div>
      </aside> --}}
            </div>
        </section>
    </main>
@endsection

@push('styles')
    <style>
        .product-gallery {
            position: relative;
        }

        .product-gallery-preview {
            position: relative;
            margin-bottom: 1rem;
        }

        .product-gallery-preview-item {
            display: none;
        }

        .product-gallery-preview-item.active {
            display: block;
        }

        .product-gallery-thumblist {
            display: flex;
            gap: 0.5rem;
        }

        .product-gallery-thumblist-item {
            width: 80px;
            height: 80px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .product-gallery-thumblist-item.active {
            border-color: #0d6efd;
        }

        .product-gallery-thumblist-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Product gallery
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.product-gallery-thumblist-item');
            const previews = document.querySelectorAll('.product-gallery-preview-item');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);

                    // Update active states
                    thumbnails.forEach(t => t.classList.remove('active'));
                    previews.forEach(p => p.classList.remove('active'));

                    this.classList.add('active');
                    document.getElementById(targetId).classList.add('active');
                });
            });
        });
    </script>
@endpush
