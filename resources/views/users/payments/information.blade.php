@extends('users.layouts.app')

@section('content')
    <style>
      .checkout-step {
        transition: all 0.3s ease;
      }
      
      .checkout-step.hidden {
        display: none !important;
      }
      
      .step-summary {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
      }
      
      .step-indicator {
        transition: all 0.3s ease;
      }
      
      .step-indicator.active {
        background: #dc3545 !important;
        color: white !important;
      }
      
      .step-indicator.completed {
        background: #28a745 !important;
        color: white !important;
      }
      
      .step-indicator.inactive {
        background: #6c757d !important;
        color: white !important;
      }
      
      .form-control:invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
      }
      
      .form-control:valid:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
      }
    </style>
    
    <!-- Page content -->
    <main class="content-wrapper">
      <div class="container py-5">
        <div class="row pt-1 pt-sm-3 pt-lg-4 pb-2 pb-md-3 pb-lg-4 pb-xl-5">

          <!--Thông tin giao hàng (Bước 1) -->
          <div class="col-lg-8 col-xl-7 mb-5 mb-lg-0">
            <div class="d-flex flex-column gap-5 pe-lg-4 pe-xl-0">

              <!-- Thông tin giao hàng (Bước 1) -->
              <div class="d-flex align-items-start" id="step-1">
                <div class="d-flex align-items-center justify-content-center bg-danger text-white rounded-circle fs-sm fw-semibold lh-1 flex-shrink-0" style="width: 2rem; height: 2rem; margin-top: -.125rem">1</div>
                <div class="flex-grow-0 flex-shrink-0 ps-3 ps-md-4" style="width: calc(100% - 2rem)">
                  <h1 class="h5 mb-md-4">Thông tin vận chuyển</h1>
                  <div class="ms-n5 ms-sm-0">
                    <p class="fs-sm mb-4">Vui lòng chọn địa chỉ của bạn để xem các tùy chọn giao hàng và chi phí.</p>
                    
                    <!-- Biểu mẫu lựa chọn địa chỉ -->
                    <div class="row g-3 mb-4">
                      <div class="col-12">
                        <label for="province" class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                        <select class="form-select" id="province" name="province" required>
                          <option value="">Chọn tỉnh/thành phố</option>
                          <!-- Dữ liệu sẽ được load từ API -->
                        </select>
                      </div>
                      
                      <div class="col-md-6">
                        <label for="ward" class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                        <select class="form-select" id="ward" name="ward" required disabled>
                          <option value="">Chọn tỉnh/thành phố trước</option>
                          <!-- Dữ liệu sẽ được load từ API khi chọn tỉnh -->
                        </select>
                      </div>
                    </div>

                    <h3 class="h6 border-bottom pb-4 mb-0">Chọn phương thức vận chuyển</h3>
                    <div class="mb-lg-4" id="shippingMethod" role="list">

                      <!-- Tùy chọn giao hàng chuyển phát nhanh -->
                      <div class="border-bottom">
                        <div class="form-check mb-0" role="listitem" data-bs-toggle="collapse" data-bs-target="#courier" aria-expanded="false" aria-controls="courier">
                          <label class="form-check-label d-flex align-items-center text-dark-emphasis fw-semibold py-4">
                            <input type="radio" class="form-check-input fs-base me-2 me-sm-3" name="shipping-method">
                            Giao hàng nhanh
                            <span class="fw-normal ms-auto">35.000 VNĐ</span>
                          </label>
                        </div>
                        <div class="collapse" id="courier" data-bs-parent="#shippingMethod">
                          <div class="pb-4 ps-3 ms-2 ms-sm-3">
                            <p class="fs-sm">Chọn thời gian giao hàng phù hợp:</p>
                            <div class="d-flex justify-content-start">
                              <button type="button" class="btn btn-icon btn-sm btn-outline-secondary ms-n2" id="courierTimePrev" aria-label="Prev">
                                <i class="ci-chevron-left fs-lg"></i>
                              </button>
                              <div class="swiper swiper-load pt-2" data-swiper="{
                                &quot;slidesPerView&quot;: 2,
                                &quot;spaceBetween&quot;: 12,
                                &quot;navigation&quot;: {
                                  &quot;prevEl&quot;: &quot;#courierTimePrev&quot;,
                                  &quot;nextEl&quot;: &quot;#courierTimeNext&quot;
                                },
                                &quot;breakpoints&quot;: {
                                  &quot;600&quot;: {
                                    &quot;slidesPerView&quot;: 3,
                                    &quot;spaceBetween&quot;: 16
                                  },
                                  &quot;768&quot;: {
                                    &quot;slidesPerView&quot;: 4,
                                    &quot;spaceBetween&quot;: 16
                                  },
                                  &quot;991&quot;: {
                                    &quot;slidesPerView&quot;: 3,
                                    &quot;spaceBetween&quot;: 16
                                  },
                                  &quot;1100&quot;: {
                                    &quot;slidesPerView&quot;: 4,
                                    &quot;spaceBetween&quot;: 12
                                  },
                                  &quot;1250&quot;: {
                                    &quot;slidesPerView&quot;: 4,
                                    &quot;spaceBetween&quot;: 24
                                  }
                                }
                              }">
                                <div class="swiper-wrapper">
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 2</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-mon-1">
                                      <label for="c-mon-1" class="btn btn-outline-secondary w-100 rounded-pill">12:00 - 15:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-mon-2">
                                      <label for="c-mon-2" class="btn btn-outline-secondary w-100 rounded-pill">17:00 - 20:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 3</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-tue-1">
                                      <label for="c-tue-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 12:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-tue-2">
                                      <label for="c-tue-2" class="btn btn-outline-secondary w-100 rounded-pill">14:00 - 19:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 4</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-wed-1">
                                      <label for="c-wed-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 12:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-wed-2">
                                      <label for="c-wed-2" class="btn btn-outline-secondary w-100 rounded-pill">14:00 - 19:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 5</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-thu-1">
                                      <label for="c-thu-1" class="btn btn-outline-secondary w-100 rounded-pill">12:00 - 15:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-thu-2">
                                      <label for="c-thu-2" class="btn btn-outline-secondary w-100 rounded-pill">17:00 - 20:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 6</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-fri-1">
                                      <label for="c-fri-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 12:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-fri-2">
                                      <label for="c-fri-2" class="btn btn-outline-secondary w-100 rounded-pill">14:00 - 19:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 7</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-sat-1">
                                      <label for="c-sat-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 11:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-sat-2">
                                      <label for="c-sat-2" class="btn btn-outline-secondary w-100 rounded-pill">13:00 - 15:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Chủ nhật</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-sun-1">
                                      <label for="c-sun-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 11:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="courier-time" id="c-sun-2">
                                      <label for="c-sun-2" class="btn btn-outline-secondary w-100 rounded-pill">13:00 - 15:00</label>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <button type="button" class="btn btn-icon btn-sm btn-outline-secondary me-n2" id="courierTimeNext" aria-label="Next">
                                <i class="ci-chevron-right fs-lg"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Tùy chọn nhận hàng tại cửa hàng -->
                      <div class="border-bottom">
                        <div class="form-check mb-0" role="listitem" data-bs-toggle="collapse" data-bs-target="#pickup" aria-expanded="false" aria-controls="pickup">
                          <label class="form-check-label d-flex align-items-center text-dark-emphasis fw-semibold py-4">
                            <input type="radio" class="form-check-input fs-base me-2 me-sm-3" name="shipping-method">
                            Nhận tại cửa hàng
                            <span class="fw-normal ms-auto">Miễn phí</span>
                          </label>
                        </div>
                        <div class="collapse" id="pickup" data-bs-parent="#shippingMethod">
                          <div class="pb-4 ps-3 ms-2 ms-sm-3">
                            <p class="fs-sm mb-2">Chọn cửa hàng gần nhất:</p>
                            <div class="w-100 mb-4" style="max-width: 300px">
                              <select class="form-select" data-select="{
                                &quot;removeItemButton&quot;: false,
                                &quot;choices&quot;: [
                                  {
                                    &quot;value&quot;: &quot;iMart Ba Đình&quot;,
                                    &quot;label&quot;: &quot;<span class=\&quot;text-dark-emphasis fw-medium\&quot;>iMart Ba Đình</span>&quot;,
                                    &quot;customProperties&quot;: {
                                      &quot;address&quot;: &quot;<span class=\&quot;d-block text-body-secondary fs-xs fw-normal\&quot;>123 Đội Cấn, Ba Đình, Hà Nội</span>&quot;,
                                      &quot;selected&quot;: &quot;<span class=\&quot;text-dark-emphasis fw-medium\&quot;>iMart Ba Đình</span>&quot;
                                    }
                                  },
                                  {
                                    &quot;value&quot;: &quot;iMart Hoàn Kiếm&quot;,
                                    &quot;label&quot;: &quot;<span class=\&quot;text-dark-emphasis fw-medium\&quot;>iMart Hoàn Kiếm</span>&quot;,
                                    &quot;customProperties&quot;: {
                                      &quot;address&quot;: &quot;<span class=\&quot;d-block text-body-secondary fs-xs fw-normal\&quot;>456 Hàng Bài, Hoàn Kiếm, Hà Nội</span>&quot;,
                                      &quot;selected&quot;: &quot;<span class=\&quot;text-dark-emphasis fw-medium\&quot;>iMart Hoàn Kiếm</span>&quot;
                                    }
                                  },
                                  {
                                    &quot;value&quot;: &quot;iMart Tây Hồ&quot;,
                                    &quot;label&quot;: &quot;<span class=\&quot;text-dark-emphasis fw-medium\&quot;>iMart Tây Hồ</span>&quot;,
                                    &quot;customProperties&quot;: {
                                      &quot;address&quot;: &quot;<span class=\&quot;d-block text-body-secondary fs-xs fw-normal\&quot;>789 Lạc Long Quân, Tây Hồ, Hà Nội</span>&quot;,
                                      &quot;selected&quot;: &quot;<span class=\&quot;text-dark-emphasis fw-medium\&quot;>iMart Tây Hồ</span>&quot;
                                    }
                                  }
                                ]
                              }" data-select-template="true"></select>
                            </div>
                            <p class="fs-sm">Chọn thời gian nhận hàng phù hợp:</p>
                            <div class="d-flex justify-content-start">
                              <button type="button" class="btn btn-icon btn-sm btn-outline-secodary ms-n2" id="pickupTimePrev" aria-label="Prev">
                                <i class="ci-chevron-left fs-lg"></i>
                              </button>
                              <div class="swiper swiper-load pt-2" data-swiper="{
                                &quot;slidesPerView&quot;: 2,
                                &quot;spaceBetween&quot;: 12,
                                &quot;navigation&quot;: {
                                  &quot;prevEl&quot;: &quot;#pickupTimePrev&quot;,
                                  &quot;nextEl&quot;: &quot;#pickupTimeNext&quot;
                                },
                                &quot;breakpoints&quot;: {
                                  &quot;600&quot;: {
                                    &quot;slidesPerView&quot;: 3,
                                    &quot;spaceBetween&quot;: 16
                                  },
                                  &quot;768&quot;: {
                                    &quot;slidesPerView&quot;: 4,
                                    &quot;spaceBetween&quot;: 16
                                  },
                                  &quot;991&quot;: {
                                    &quot;slidesPerView&quot;: 3,
                                    &quot;spaceBetween&quot;: 16
                                  },
                                  &quot;1100&quot;: {
                                    &quot;slidesPerView&quot;: 4,
                                    &quot;spaceBetween&quot;: 12
                                  },
                                  &quot;1250&quot;: {
                                    &quot;slidesPerView&quot;: 4,
                                    &quot;spaceBetween&quot;: 24
                                  }
                                }
                              }">
                                <div class="swiper-wrapper">
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 2</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-mon-1">
                                      <label for="p-mon-1" class="btn btn-outline-secondary w-100 rounded-pill">12:00 - 15:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-mon-2">
                                      <label for="p-mon-2" class="btn btn-outline-secondary w-100 rounded-pill">17:00 - 20:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 3</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-tue-1">
                                      <label for="p-tue-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 12:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-tue-2">
                                      <label for="p-tue-2" class="btn btn-outline-secondary w-100 rounded-pill">14:00 - 19:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 4</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-wed-1">
                                      <label for="p-wed-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 12:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-wed-2">
                                      <label for="p-wed-2" class="btn btn-outline-secondary w-100 rounded-pill">14:00 - 19:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 5</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-thu-1">
                                      <label for="p-thu-1" class="btn btn-outline-secondary w-100 rounded-pill">12:00 - 15:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-thu-2">
                                      <label for="p-thu-2" class="btn btn-outline-secondary w-100 rounded-pill">17:00 - 20:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 6</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-fri-1">
                                      <label for="p-fri-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 12:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-fri-2">
                                      <label for="p-fri-2" class="btn btn-outline-secondary w-100 rounded-pill">14:00 - 19:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Thứ 7</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-sat-1">
                                      <label for="p-sat-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 11:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-sat-2">
                                      <label for="p-sat-2" class="btn btn-outline-secondary w-100 rounded-pill">13:00 - 15:00</label>
                                    </div>
                                  </div>
                                  <div class="swiper-slide text-center">
                                    <div class="h6 fs-sm pb-2 mb-0">Chủ nhật</div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-sun-1">
                                      <label for="p-sun-1" class="btn btn-outline-secondary w-100 rounded-pill">09:00 - 11:00</label>
                                    </div>
                                    <div class="py-1 my-1">
                                      <input type="radio" class="btn-check" name="pickup-time" id="p-sun-2">
                                      <label for="p-sun-2" class="btn btn-outline-secondary w-100 rounded-pill">13:00 - 15:00</label>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <button type="button" class="btn btn-icon btn-sm btn-outline-secondary me-n2" id="pickupTimeNext" aria-label="Next">
                                <i class="ci-chevron-right fs-lg"></i>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Tùy chọn giao hàng địa phương -->
                      <div class="border-bottom">
                        <div class="form-check mb-0" role="listitem" data-bs-toggle="collapse" data-bs-target="#shipping" aria-expanded="false" aria-controls="shipping">
                          <label class="form-check-label d-flex align-items-center text-dark-emphasis fw-semibold py-4">
                            <input type="radio" class="form-check-input fs-base me-2 me-sm-3" name="shipping-method">
                            Giao hàng tiêu chuẩn
                            <span class="fw-normal ms-auto">25.000 VNĐ</span>
                          </label>
                        </div>
                        <div class="collapse" id="shipping" data-bs-parent="#shippingMethod">
                          <div class="pb-4 ps-3 ms-2 ms-sm-3">
                            <div class="alert d-flex align-items-center alert-info mb-3" role="alert">
                              <i class="ci-info fs-lg me-2"></i>
                              <div class="fs-sm">Giao hàng tiêu chuẩn có thể mất tới <span class="text-info-emphasis fw-semibold">3-5</span> ngày làm việc.</div>
                            </div>
                            <p class="fs-sm mb-0">Dự kiến giao hàng - <span class="text-body-emphasis fw-medium">15 tháng 3, 2024</span></p>
                          </div>
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-lg btn-primary w-100" id="continue-to-address">
                      Tiếp tục
                      <i class="ci-chevron-right fs-lg ms-1 me-n1"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Tóm tắt Bước 1 (khi hoàn thành) -->
              <div class="d-flex align-items-start" id="step-1-summary" style="display: none !important;">
                <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0" style="width: 2rem; height: 2rem; margin-top: -.125rem">
                  <i class="ci-check fs-base"></i>
                </div>
                <div class="w-100 ps-3 ps-md-4">
                  <div class="d-flex align-items-center">
                    <h2 class="h5 mb-0 me-3">Thông tin vận chuyển</h2>
                    <div class="nav ms-auto">
                      <button type="button" class="btn btn-link text-decoration-underline p-0" id="edit-delivery-info">Sửa</button>
                    </div>
                  </div>
                  <div class="step-summary mt-3">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <h3 class="fs-sm mb-2 text-muted">Địa chỉ giao hàng</h3>
                        <p class="fs-sm mb-0" id="delivery-location">Chưa chọn địa chỉ</p>
                      </div>
                      <div class="col-md-6">
                        <h3 class="fs-sm mb-2 text-muted">Phương thức vận chuyển</h3>
                        <p class="fs-sm mb-0" id="selected-shipping-method">Chưa chọn phương thức</p>
                        <p class="fs-xs text-muted mb-0" id="selected-shipping-time">Chưa chọn thời gian</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 2: Shipping Address -->
              <div class="d-flex align-items-start" id="step-2" style="display: none !important;">
                <div class="d-flex align-items-center justify-content-center bg-danger text-white rounded-circle fs-sm fw-semibold lh-1 flex-shrink-0" style="width: 2rem; height: 2rem; margin-top: -.125rem">2</div>
                <div class="w-100 ps-3 ps-md-4">
                  <h1 class="h5 mb-md-4">Địa chỉ giao hàng</h1>
                  <form class="needs-validation" novalidate="">
                    <div class="row row-cols-1 row-cols-sm-2 g-3 g-sm-4 mb-4">
                      <div class="col">
                        <label for="shipping-fn" class="form-label">Họ & tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="shipping-fn" required="" 
                               pattern="[a-zA-ZÀ-ỹ\s]+" title="Chỉ được nhập chữ và khoảng trắng"
                               value="{{ Auth::check() ? Auth::user()->name : '' }}">
                      </div>
                      <div class="col">
                        <label for="shipping-address" class="form-label">Số nhà / Tên đường <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="shipping-address" required="" minlength="5" title="Vui lòng nhập địa chỉ chi tiết (tối thiểu 5 ký tự)">
                      </div>
                      <div class="col">
                        <label for="shipping-email" class="form-label">Địa chỉ email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-lg" id="shipping-email" required="" 
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" 
                               title="Vui lòng nhập email đúng định dạng (vd: example@gmail.com)"
                               value="{{ Auth::check() ? Auth::user()->email : '' }}">
                      </div>
                      <div class="col">
                        <label for="shipping-mobile" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control form-control-lg" id="shipping-mobile" required="" 
                               pattern="[0-9]{10,11}" title="Vui lòng nhập số điện thoại 10-11 số"
                               value="{{ Auth::check() && Auth::user()->phone_number ? Auth::user()->phone_number : '' }}">
                      </div>
                      <div class="col">
                        <label class="form-label">Địa chỉ từ bước trước</label>
                        <input type="text" class="form-control form-control-lg" id="auto-filled-address" readonly style="background-color: #f8f9fa;">
                      </div>
                      <div class="col">
                        <label for="shipping-postcode" class="form-label">Mã bưu điện</label>
                        <input type="text" class="form-control form-control-lg" id="shipping-postcode" pattern="[0-9]{5,6}" title="Vui lòng nhập mã bưu điện 5-6 số">
                      </div>
                    </div>

                    <div class="nav mb-4">
                      <a class="nav-link px-0" href="#!">
                        Thêm địa chỉ
                        <i class="ci-plus fs-base ms-1"></i>
                      </a>
                    </div>
                    <h3 class="h6">
                      Địa chỉ thanh toán
                      <i class="ci-info text-body-secondary align-middle ms-2" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-custom-class="popover-sm" data-bs-content="Bỏ chọn ô bên dưới nếu địa chỉ thanh toán khác với địa chỉ giao hàng."></i>
                    </h3>
                    <div class="form-check mb-lg-4">
                      <input type="checkbox" class="form-check-input" id="same-address" checked="">
                      <label for="same-address" class="form-check-label">Giống với địa chỉ giao hàng</label>
                    </div>
                    <button type="button" class="btn btn-lg btn-primary w-100" id="continue-to-payment">
                      Tiếp tục
                      <i class="ci-chevron-right fs-lg ms-1 me-n1"></i>
                    </button>
                  </form>
                </div>
              </div>

              <!-- Tóm tắt Bước 2 (khi hoàn thành) -->
              <div class="d-flex align-items-start" id="step-2-summary" style="display: none !important;">
                <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0" style="width: 2rem; height: 2rem; margin-top: -.125rem">
                  <i class="ci-check fs-base"></i>
                </div>
                <div class="w-100 ps-3 ps-md-4">
                  <div class="d-flex align-items-center">
                    <h2 class="h5 mb-0 me-3">Địa chỉ giao hàng</h2>
                    <div class="nav ms-auto">
                      <button type="button" class="btn btn-link text-decoration-underline p-0" id="edit-shipping-address">Sửa</button>
                    </div>
                  </div>
                  <div class="step-summary mt-3">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <h3 class="fs-sm mb-2 text-muted">Thông tin người nhận</h3>
                        <p class="fs-sm mb-0" id="receiver-info">Chưa có thông tin</p>
                      </div>
                      <div class="col-md-6">
                        <h3 class="fs-sm mb-2 text-muted">Địa chỉ giao hàng</h3>
                        <p class="fs-sm mb-0" id="shipping-address-info">Chưa có địa chỉ</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 3: Thanh toán  -->
              <div class="d-flex align-items-start" id="step-3" style="display: none !important;">
                <div class="d-flex align-items-center justify-content-center bg-danger text-white rounded-circle fs-sm fw-semibold lh-1 flex-shrink-0" style="width: 2rem; height: 2rem; margin-top: -.125rem">3</div>
                <div class="w-100 ps-3 ps-md-4">
                  <h1 class="h5 mb-md-4">Thanh toán</h1>
                  <p class="fs-sm text-body-secondary">Chọn phương thức thanh toán phù hợp với bạn.</p>
                  
                  <div class="mb-4">
                    <div class="form-check mb-3">
                      <input type="radio" class="form-check-input" name="payment-method" id="cod">
                      <label class="form-check-label" for="cod">
                        <span class="fw-medium">Thanh toán khi nhận hàng (COD)</span>
                        <div class="fs-sm text-body-secondary mt-1">Thanh toán bằng tiền mặt khi nhận hàng</div>
                      </label>
                    </div>
                    <div class="form-check mb-3">
                      <input type="radio" class="form-check-input" name="payment-method" id="bank-transfer">
                      <label class="form-check-label" for="bank-transfer">
                        <span class="fw-medium">Chuyển khoản ngân hàng</span>
                        <div class="fs-sm text-body-secondary mt-1">Thanh toán qua chuyển khoản ngân hàng</div>
                      </label>
                    </div>
                    <div class="form-check mb-3">
                      <input type="radio" class="form-check-input" name="payment-method" id="vnpay">
                      <label class="form-check-label" for="vnpay">
                        <span class="fw-medium">VNPay</span>
                        <div class="fs-sm text-body-secondary mt-1">Thanh toán qua ví điện tử VNPay</div>
                      </label>
                    </div>
                  </div>
                  
                  <button type="button" class="btn btn-lg btn-success w-100" id="place-order">
                    Đặt hàng
                    <i class="ci-check fs-lg ms-1 me-n1"></i>
                  </button>
                </div>
              </div>

              <!-- Inactive Steps (default state) -->
              {{-- <div class="d-flex align-items-start" id="step-2-inactive">
                <div class="d-flex align-items-center justify-content-center bg-body-secondary text-body-secondary rounded-circle fs-sm fw-semibold lh-1 flex-shrink-0" style="width: 2rem; height: 2rem; margin-top: -.125rem">2</div>
                <h2 class="h5 text-body-secondary ps-3 ps-md-4 mb-0">Địa chỉ giao hàng</h2>
              </div>

              <div class="d-flex align-items-start" id="step-3-inactive">
                <div class="d-flex align-items-center justify-content-center bg-body-secondary text-body-secondary rounded-circle fs-sm fw-semibold lh-1 flex-shrink-0" style="width: 2rem; height: 2rem; margin-top: -.125rem">3</div>
                <h2 class="h5 text-body-secondary ps-3 ps-md-4 mb-0">Thanh toán</h2>
              </div> --}}
            </div>
          </div>

          <!-- Tóm tắt đơn hàng (thanh bên cố định) -->
          <aside class="col-lg-4 offset-xl-1" style="margin-top: -100px">
            <div class="position-sticky top-0" style="padding-top: 100px">
              <div class="bg-body-tertiary rounded-5 p-4 mb-3">
                <div class="p-sm-2 p-lg-0 p-xl-2">
                  <div class="border-bottom pb-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                      <h5 class="mb-0">Tổng đơn hàng</h5>
                      <div class="nav">
                        <a class="nav-link text-decoration-underline p-0" href="{{ route('cart.index') }}">Sửa</a>
                      </div>
                    </div>
                    <a class="d-flex align-items-center gap-2 text-decoration-none" href="#orderPreview" data-bs-toggle="offcanvas">
                      @foreach($items->take(3) as $item)
                        <div class="ratio ratio-1x1" style="max-width: 64px">
                          <img src="{{ $item->productVariant->primaryImage->file_url ?? asset('assets/users/img/no-image.png') }}" 
                               class="d-block p-1 rounded" alt="{{ $item->productVariant->product->name }}">
                        </div>
                      @endforeach
                      @if($items->count() > 3)
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 64px; height: 64px;">
                          <span class="fs-sm text-muted">+{{ $items->count() - 3 }}</span>
                        </div>
                      @endif
                      <i class="ci-chevron-right text-body fs-xl p-0 ms-auto"></i>
                    </a>
                  </div>
                  <ul class="list-unstyled fs-sm gap-3 mb-0">
                    <li class="d-flex justify-content-between">
                      Tạm tính ({{ $total_quantity }} sản phẩm):
                      <span class="text-dark-emphasis fw-medium">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</span>
                    </li>
                    @if($discount > 0)
                    <li class="d-flex justify-content-between">
                      Giảm giá @if($voucher)({{ $voucher['code'] }})@endif:
                      <span class="text-danger fw-medium">-{{ number_format($discount, 0, ',', '.') }} VNĐ</span>
                    </li>
                    @endif
                    <li class="d-flex justify-content-between">
                      Phí vận chuyển:
                      <span class="text-dark-emphasis fw-medium" id="shipping-fee-display">Chọn phương thức vận chuyển</span>
                    </li>
                  </ul>
                  <div class="border-top pt-4 mt-4">
                    <div class="d-flex justify-content-between mb-3">
                      <span class="fs-sm">Tổng cộng:</span>
                      <span class="h5 mb-0" id="grand-total-display">{{ number_format($total, 0, ',', '.') }} VNĐ</span>
                    </div>
                  </div>
                </div>
              </div>
              {{-- div ở giỏ hàng --}}
              {{-- <div class="accordion bg-body-tertiary rounded-5 p-4">
                <div class="accordion-item border-0">
                    <h3 class="accordion-header" id="promoCodeHeading">
                        <button type="button"
                            class="accordion-button animate-underline collapsed py-0 ps-sm-2 ps-lg-0 ps-xl-2"
                            data-bs-toggle="collapse" data-bs-target="#promoCode" aria-expanded="false"
                            aria-controls="promoCode">
                            <i class="ci-percent fs-xl me-2"></i>
                            <span class="animate-target me-2">Nhập mã khuyến mãi(nếu có)</span>
                        </button>
                    </h3>
                    <div class="accordion-collapse collapse" id="promoCode" aria-labelledby="promoCodeHeading">
                        <div class="accordion-body pt-3 pb-2 ps-sm-2 px-lg-0 px-xl-2">
                            <form id="voucher-form" class="needs-validation d-flex gap-2" novalidate>
                                @csrf
                                <div class="position-relative w-100">
                                    <input type="text" name="voucher_code" class="form-control"
                                        placeholder="Enter promo code" required>
                                    <div class="invalid-tooltip bg-transparent py-0">Enter a valid promo code!</div>
                                </div>
                                <button type="submit" class="btn btn-dark">Áp dụng</button>
                            </form>
                        </div>
                    </div>
                </div>
              </div> --}}
              <div class="bg-body-tertiary rounded-5 p-4">
                <div class="d-flex align-items-center px-sm-2 px-lg-0 px-xl-2">
                  <svg class="text-warning flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"><path d="M1.333 9.667H7.5V16h-5c-.64 0-1.167-.527-1.167-1.167V9.667zm13.334 0v5.167c0 .64-.527 1.167-1.167 1.167h-5V9.667h6.167zM0 5.833V7.5c0 .64.527 1.167 1.167 1.167h.167H7.5v-1-3H1.167C.527 4.667 0 5.193 0 5.833zm14.833-1.166H8.5v3 1h6.167.167C15.473 8.667 16 8.14 16 7.5V5.833c0-.64-.527-1.167-1.167-1.167z"></path><path d="M8 5.363a.5.5 0 0 1-.495-.573C7.752 3.123 9.054-.03 12.219-.03c1.807.001 2.447.977 2.447 1.813 0 1.486-2.069 3.58-6.667 3.58zM12.219.971c-2.388 0-3.295 2.27-3.595 3.377 1.884-.088 3.072-.565 3.756-.971.949-.563 1.287-1.193 1.287-1.595 0-.599-.747-.811-1.447-.811z"></path><path d="M8.001 5.363c-4.598 0-6.667-2.094-6.667-3.58 0-.836.641-1.812 2.448-1.812 3.165 0 4.467 3.153 4.713 4.819a.5.5 0 0 1-.495.573zM3.782.971c-.7 0-1.448.213-1.448.812 0 .851 1.489 2.403 5.042 2.566C7.076 3.241 6.169.971 3.782.971z"></path></svg>
                  <div class="text-dark-emphasis fs-sm ps-2 ms-1">Chúc mừng! Bạn đã tích được <span class="fw-semibold">240 điểm thưởng</span></div>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </main>

    <!-- Đặt hàng Xem trước Offcanvas -->
    <div class="offcanvas offcanvas-end" id="orderPreview" tabindex="-1" aria-labelledby="orderPreviewLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="orderPreviewLabel">Chi tiết đơn hàng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        @foreach($items as $item)
          <div class="d-flex gap-3 pb-4 border-bottom mb-4">
            <div class="flex-shrink-0">
              <img src="{{ $item->productVariant->primaryImage->file_url ?? asset('assets/users/img/no-image.png') }}" 
                   alt="{{ $item->productVariant->product->name }}" 
                   class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
            </div>
            <div class="flex-grow-1">
              <h6 class="mb-1">{{ $item->productVariant->product->name }}</h6>
              @if($item->productVariant->attributeValues->count() > 0)
                <p class="fs-sm text-muted mb-1">
                  @foreach($item->productVariant->attributeValues as $attrValue)
                    {{ $attrValue->attribute->name }}: {{ $attrValue->value }}
                    @if(!$loop->last), @endif
                  @endforeach
                </p>
              @endif
              <div class="d-flex justify-content-between align-items-center">
                <span class="fs-sm text-muted">{{ number_format($item->price, 0, ',', '.') }} VNĐ × {{ $item->quantity }}</span>
                <span class="fw-medium">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} VNĐ</span>
              </div>
            </div>
          </div>
        @endforeach
        
        <div class="border-top pt-4">
          <div class="d-flex justify-content-between mb-2">
            <span>Tạm tính:</span>
            <span>{{ number_format($subtotal, 0, ',', '.') }} VNĐ</span>
          </div>
          @if($discount > 0)
          <div class="d-flex justify-content-between mb-2">
            <span>Giảm giá @if($voucher)({{ $voucher['code'] }})@endif:</span>
            <span class="text-danger">-{{ number_format($discount, 0, ',', '.') }} VNĐ</span>
          </div>
          @endif
          <div class="d-flex justify-content-between mb-2">
            <span>Phí vận chuyển:</span>
            <span id="offcanvas-shipping-fee">Chọn phương thức</span>
          </div>
          <div class="d-flex justify-content-between border-top pt-2 fw-bold">
            <span>Tổng cộng:</span>
            <span id="offcanvas-grand-total">{{ number_format($total, 0, ',', '.') }} VNĐ</span>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Lấy tất cả các phần tử
        const step1 = document.getElementById('step-1');
        const step1Summary = document.getElementById('step-1-summary');
        const step2 = document.getElementById('step-2');
        const step2Summary = document.getElementById('step-2-summary');
        const step2Inactive = document.getElementById('step-2-inactive');
        const step3 = document.getElementById('step-3');
        const step3Inactive = document.getElementById('step-3-inactive');
        
        const continueToAddressBtn = document.getElementById('continue-to-address');
        const continueToPaymentBtn = document.getElementById('continue-to-payment');
        const editDeliveryBtn = document.getElementById('edit-delivery-info');
        const editShippingBtn = document.getElementById('edit-shipping-address');
        const placeOrderBtn = document.getElementById('place-order');

        // Các yếu tố vị trí
        const provinceSelect = document.getElementById('province');
        const wardSelect = document.getElementById('ward');

        // Tải các tỉnh khi tải trang
        loadProvinces();

        // Xử lý thay đổi tỉnh
        provinceSelect.addEventListener('change', function() {
          const provinceCode = this.value;
          if (provinceCode) {
            loadWards(provinceCode);
            wardSelect.disabled = false;
          } else {
            wardSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố trước</option>';
            wardSelect.disabled = true;
          }
          updateDeliverySummary();
        });

        // Xử lý thay đổi xã
        wardSelect.addEventListener('change', function() {
          updateDeliverySummary();
        });

        // Tải các tỉnh từ API
        async function loadProvinces() {
          try {
            const response = await fetch('/api/locations/provinces');
            const data = await response.json();
            
            if (data.success) {
              provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
              data.data.forEach(province => {
                const option = document.createElement('option');
                option.value = province.code;
                option.textContent = province.name_with_type;
                option.dataset.name = province.name;
                provinceSelect.appendChild(option);
              });
            } else {
              console.error('Error loading provinces:', data.message);
            }
          } catch (error) {
            console.error('Error fetching provinces:', error);
          }
        }

        // Tải phường từ API
        async function loadWards(provinceCode) {
          try {
            wardSelect.innerHTML = '<option value="">Đang tải...</option>';
            wardSelect.disabled = true;
            
            const response = await fetch(`/api/locations/wards/${provinceCode}`);
            const data = await response.json();
            
            if (data.success) {
              wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
              data.data.forEach(ward => {
                const option = document.createElement('option');
                option.value = ward.code;
                option.textContent = ward.name_with_type;
                option.dataset.name = ward.name;
                wardSelect.appendChild(option);
              });
              wardSelect.disabled = false;
            } else {
              console.error('Error loading wards:', data.message);
              wardSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
            }
          } catch (error) {
            console.error('Error fetching wards:', error);
            wardSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
          }
        }

        // Chức năng trợ giúp để cập nhật tóm tắt giao hàng
        function updateDeliverySummary() {
          const selectedMethodEl = document.querySelector('input[name="shipping-method"]:checked');
          const selectedTimeEl = document.querySelector('input[name="courier-time"]:checked') || document.querySelector('input[name="pickup-time"]:checked');

          // Cập nhật phương thức và thời gian giao hàng
          const methodElement = document.getElementById('selected-shipping-method');
          const timeElement = document.getElementById('selected-shipping-time');
          
          if (selectedMethodEl) {
            const labelEl = selectedMethodEl.closest('label');
            const spanEl = labelEl.querySelector('span');
            const priceText = spanEl ? spanEl.textContent.trim() : '';
            let methodLabel = labelEl.textContent.trim();
            if (priceText) {
              methodLabel = methodLabel.replace(priceText, '').trim();
            }
            methodElement.textContent = methodLabel + (priceText ? ' - ' + priceText : '');

            // Kiểm tra và hiển thị thời gian dựa vào phương thức
            if (methodLabel.toLowerCase().includes('giao hàng nhanh') || methodLabel.toLowerCase().includes('nhận tại cửa hàng')) {
              if (selectedTimeEl) {
                const dayText = selectedTimeEl.closest('.swiper-slide').querySelector('.h6').textContent;
                const timeText = selectedTimeEl.nextElementSibling.textContent;
                timeElement.textContent = `${dayText} ${timeText}`;
              } else {
                timeElement.textContent = 'Chưa chọn thời gian';
              }
            } else if (methodLabel.toLowerCase().includes('giao hàng tiêu chuẩn')) {
              timeElement.textContent = 'Dự kiến 3-5 ngày làm việc';
            }
          } else {
            methodElement.textContent = 'Chưa chọn phương thức';
            timeElement.textContent = 'Chưa chọn thời gian';
          }

          // Cập nhật thông tin địa điểm
          const province = document.getElementById('province');
          const ward = document.getElementById('ward');
          const deliveryLocationEl = document.getElementById('delivery-location');
          
          if (province && ward && deliveryLocationEl) {
            if (province.selectedIndex > 0 && ward.selectedIndex > 0) {
              const provinceText = province.options[province.selectedIndex].textContent;
              const wardText = ward.options[ward.selectedIndex].textContent;
              const locationText = `${wardText}, ${provinceText}`;
              deliveryLocationEl.textContent = locationText;
            } else {
              deliveryLocationEl.textContent = 'Chưa chọn địa chỉ';
            }
          }
        }

        // Chức năng trợ giúp để cập nhật tóm tắt vận chuyển
        function updateShippingSummary() {
          const fullName = document.getElementById('shipping-fn').value || '';
          const mobile = document.getElementById('shipping-mobile').value || '';
          const address = document.getElementById('shipping-address').value || '';
          const autoFilledAddress = document.getElementById('auto-filled-address').value || '';
          
          const receiverInfoEl = document.getElementById('receiver-info');
          const shippingAddressInfoEl = document.getElementById('shipping-address-info');
          
          // Chỉ cập nhật thông tin người nhận nếu chúng tôi có dữ liệu thực tế
          if (receiverInfoEl) {
            if (fullName.trim() || mobile.trim()) {
              const receiverInfo = fullName.trim() + (mobile ? ' - ' + mobile : '');
              receiverInfoEl.textContent = receiverInfo;
            } else {
              receiverInfoEl.textContent = 'Chưa có thông tin';
            }
          }

          // Cập nhật thông tin địa chỉ sử dụng địa chỉ tự động điền + địa chỉ đường
          if (shippingAddressInfoEl) {
            if (address.trim() || autoFilledAddress.trim()) {
              let fullAddress = address.trim();
              if (autoFilledAddress.trim()) {
                fullAddress += (fullAddress ? ', ' : '') + autoFilledAddress;
              }
              shippingAddressInfoEl.textContent = fullAddress || 'Chưa có địa chỉ';
            } else {
              shippingAddressInfoEl.textContent = 'Chưa có địa chỉ';
            }
          }
        }

        // Chức năng để hiển thị chỉ các bước liên quan
        function showStep(activeStep) {
          // Ẩn tất cả các bước trước
          [step1, step1Summary, step2, step2Summary, step3, step2Inactive, step3Inactive].forEach(el => {
            if (el) el.style.display = 'none';
          });

          // Hiển thị các bước phù hợp dựa trên bước hoạt động
          if (activeStep === 1) {
            // Bước 1: Hiển thị bước hiện tại + các bước không hoạt động cho 2 & 3
            step1.style.display = 'flex';
            step2Inactive.style.display = 'flex';
            step3Inactive.style.display = 'flex';
          } else if (activeStep === 2) {
            // Bước 2: Hiển thị tóm tắt bước 1 + bước hiện tại + bước không hoạt động 3
            step1Summary.style.display = 'flex';
            step2.style.display = 'flex';
            step3Inactive.style.display = 'flex';
          } else if (activeStep === 3) {
            // Bước 3: Hiển thị chỉ các tóm tắt đã hoàn thành + bước hiện tại (không có bước không hoạt động)
            step1Summary.style.display = 'flex';
            step2Summary.style.display = 'flex';
            step3.style.display = 'flex';
            // KHÔNG có bước không hoạt động nào được hiển thị ở bước 3
          }
        }

        // Bước 1 đến Bước 2
        continueToAddressBtn.addEventListener('click', function() {
          // Kiểm tra chi tiết cho bước 1
          const selectedMethod = document.querySelector('input[name="shipping-method"]:checked');
          const selectedTime = document.querySelector('input[name="courier-time"]:checked') || document.querySelector('input[name="pickup-time"]:checked');
          const province = document.getElementById('province');
          const ward = document.getElementById('ward');
          
          let errors = [];
          
          if (!province || province.selectedIndex === 0) {
            errors.push('Tỉnh/Thành phố');
          }
          
          if (!ward || ward.selectedIndex === 0) {
            errors.push('Phường/Xã');
          }
          
          if (!selectedMethod) {
            errors.push('Phương thức vận chuyển');
          } else {
            // Chỉ kiểm tra thời gian nếu chọn giao hàng nhanh hoặc nhận tại cửa hàng
            const methodLabel = selectedMethod.nextElementSibling.textContent.trim().toLowerCase();
            if ((methodLabel.includes('giao hàng nhanh') || methodLabel.includes('nhận tại cửa hàng')) && !selectedTime) {
              errors.push('Thời gian ' + (methodLabel.includes('giao hàng nhanh') ? 'giao hàng' : 'nhận hàng'));
            }
          }

          if (errors.length > 0) {
            alert('Vui lòng chọn ' + errors.join(', '));
            return;
          }
          
          // Tự động điền địa chỉ ở bước 2
          const provinceText = province.options[province.selectedIndex].textContent;
          const wardText = ward.options[ward.selectedIndex].textContent;
          const fullAddress = `${wardText}, ${provinceText}`;
          document.getElementById('auto-filled-address').value = fullAddress;
          
          updateDeliverySummary();
          showStep(2);
        });

        // Bước 2 đến Bước 3
        continueToPaymentBtn.addEventListener('click', function() {
          // Kiểm tra chi tiết cho bước 2 - chỉ các trường bắt buộc
          const fullName = document.getElementById('shipping-fn').value.trim();
          const mobile = document.getElementById('shipping-mobile').value.trim();
          const address = document.getElementById('shipping-address').value.trim();
          const email = document.getElementById('shipping-email').value.trim();
          
          let errors = [];
          
          if (!fullName) {
            errors.push('Họ & tên');
          } else if (!/^[a-zA-ZÀ-ỹ\s]+$/.test(fullName)) {
            errors.push('Họ & tên (chỉ được chứa chữ cái và khoảng trắng)');
          }
          
          if (!mobile) {
            errors.push('Số điện thoại');
          } else if (!/^[0-9]{10,11}$/.test(mobile)) {
            errors.push('Số điện thoại (phải có 10-11 chữ số)');
          }
          
          if (!address) {
            errors.push('Số nhà / Tên đường');
          } else if (address.length < 5) {
            errors.push('Số nhà / Tên đường (tối thiểu 5 ký tự)');
          }

          if (!email || !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
            errors.push('Email (bắt buộc và đúng định dạng)');
          }

          if (errors.length > 0) {
            alert('Vui lòng điền ' + errors.join(', '));
            return;
          }
          
          updateShippingSummary();
          showStep(3);
        });

        // Chỉnh sửa thông tin giao hàng
        editDeliveryBtn.addEventListener('click', function() {
          showStep(1);
        });

        // Chỉnh sửa địa chỉ giao hàng
        editShippingBtn.addEventListener('click', function() {
          showStep(2);
        });

        // Bảng phí vận chuyển
        const shippingFees = {
          'Giao hàng nhanh': 35000,
          'Nhận tại cửa hàng': 0,
          'Giao hàng tiêu chuẩn': 25000
        };

        // Giá trị cơ sở từ máy chủ
        const baseSubtotal = {{ $subtotal }};
        const baseDiscount = {{ $discount }};

        // Cập nhật tóm tắt giao hàng khi phương thức giao hàng thay đổi
        document.querySelectorAll('input[name="shipping-method"]').forEach(function(radio) {
          radio.addEventListener('change', function() {
            updateDeliverySummary();
            updateShippingFee();
            
            // Đặt lại lựa chọn thời gian khi thay đổi phương thức vận chuyển
            document.querySelectorAll('input[name="courier-time"], input[name="pickup-time"]').forEach(input => {
              input.checked = false;
            });
            updateDeliverySummary();
          });
        });

        // Chức năng cập nhật phí vận chuyển và tổng số
        function updateShippingFee() {
          const selectedMethod = document.querySelector('input[name="shipping-method"]:checked');
          if (selectedMethod) {
            // Lấy văn bản nhãn từ khoảng đầu tiên (tên phương thức)
            const labelElement = selectedMethod.closest('label');
            if (!labelElement) {
              console.error('Label element not found');
              return;
            }

            // Làm sạch văn bản bằng cách xóa khoảng trắng và dòng mới thừa
            const labelText = labelElement.textContent
              .replace(/\s+/g, ' ') // Thay thế nhiều khoảng trắng/dòng mới bằng một khoảng trắng
              .trim();
            
            console.log('Cleaned label text:', labelText);
            
            let shippingFee = 0;
            if (labelText.includes('Giao hàng nhanh')) {
              shippingFee = 35000;
            } else if (labelText.includes('Giao hàng tiêu chuẩn')) {
              shippingFee = 25000;
            } // Nhận tại hàng vẫn là 0
            
            console.log('Calculated shipping fee:', shippingFee);
            
            const grandTotal = baseSubtotal + shippingFee - baseDiscount;
            const shippingFeeFormatted = shippingFee === 0 ? 'Miễn phí' : number_format(shippingFee) + ' VNĐ';
            
            // Cập nhật tất cả các màn hình hiển thị phí vận chuyển
            document.getElementById('shipping-fee-display').textContent = shippingFeeFormatted;
            document.getElementById('grand-total-display').textContent = number_format(grandTotal) + ' VNĐ';
            document.getElementById('offcanvas-shipping-fee').textContent = shippingFeeFormatted;
            document.getElementById('offcanvas-grand-total').textContent = number_format(grandTotal) + ' VNĐ';

            // Cũng cập nhật phương thức vận chuyển hiển thị trong phần tóm tắt
            const methodElement = document.getElementById('selected-shipping-method');
            if (methodElement) {
              methodElement.textContent = labelText;
            }
          } else {
            // Đặt lại màn hình khi không có phương pháp nào được chọn
            const displays = [
              'shipping-fee-display',
              'grand-total-display',
              'offcanvas-shipping-fee',
              'offcanvas-grand-total'
            ];
            displays.forEach(id => {
              const element = document.getElementById(id);
              if (element) {
                element.textContent = id.includes('shipping') ? 'Chọn phương thức vận chuyển' : number_format(baseSubtotal - baseDiscount) + ' VNĐ';
              }
            });
          }
        }

        // Chức năng định dạng số
        function number_format(number) {
          return new Intl.NumberFormat('vi-VN').format(number);
        }

        // Cập nhật tóm tắt giao hàng khi lựa chọn thời gian thay đổi
        document.querySelectorAll('input[name="courier-time"], input[name="pickup-time"]').forEach(function(radio) {
          radio.addEventListener('change', function() {
            // Bỏ chọn các lựa chọn thời gian khác
            const name = this.getAttribute('name');
            document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
              if (input !== this) input.checked = false;
            });
            updateDeliverySummary();
          });
        });

        // Cập nhật tóm tắt giao hàng khi địa chỉ thay đổi 
        // Lưu ý: người nghe thay đổi tỉnh và phường đã được thêm vào ở trên

        // Cập nhật ban đầu
        updateDeliverySummary();

        // Đặt hàng
        placeOrderBtn.addEventListener('click', function() {
          let errors = [];

          // Validate địa chỉ
          const province = document.getElementById('province');
          const ward = document.getElementById('ward');
          const selectedShippingMethod = document.querySelector('input[name="shipping-method"]:checked');
          const selectedTime = document.querySelector('input[name="courier-time"]:checked') || document.querySelector('input[name="pickup-time"]:checked');

          if (!province || province.selectedIndex === 0) {
            errors.push('Tỉnh/Thành phố');
          }
          
          if (!ward || ward.selectedIndex === 0) {
            errors.push('Phường/Xã');
          }

          if (!selectedShippingMethod) {
            errors.push('Phương thức vận chuyển');
          } else {
            const methodLabel = selectedShippingMethod.nextElementSibling.textContent.trim().toLowerCase();
            if ((methodLabel.includes('giao hàng nhanh') || methodLabel.includes('nhận tại cửa hàng')) && !selectedTime) {
              errors.push('Thời gian ' + (methodLabel.includes('giao hàng nhanh') ? 'giao hàng' : 'nhận hàng'));
            }
          }

          // Validate thông tin người nhận
          const fullName = document.getElementById('shipping-fn').value.trim();
          const mobile = document.getElementById('shipping-mobile').value.trim();
          const address = document.getElementById('shipping-address').value.trim();
          const email = document.getElementById('shipping-email').value.trim();

          if (!fullName || !/^[a-zA-ZÀ-ỹ\s]+$/.test(fullName)) {
            errors.push('Họ & tên');
          }
          
          if (!mobile || !/^[0-9]{10,11}$/.test(mobile)) {
            errors.push('Số điện thoại');
          }
          
          if (!address || address.length < 5) {
            errors.push('Địa chỉ');
          }

          if (!email || !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
            errors.push('Email');
          }

          // Bước 3 xác thực
          const selectedPaymentMethod = document.querySelector('input[name="payment-method"]:checked');
          if (!selectedPaymentMethod) {
            errors.push('Phương thức thanh toán');
          }

          if (errors.length > 0) {
            alert('Vui lòng điền đầy đủ thông tin: ' + errors.join(', '));
            return;
          }

          // Tắt nút và hiển thị tải
          placeOrderBtn.disabled = true;
          placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang xử lý...';

          // Chuẩn bị dữ liệu đơn hàng
          const orderData = {
            province_code: province.value,
            ward_code: ward.value,
            shipping_method: selectedShippingMethod.closest('label').querySelector('.form-check-input').nextSibling.textContent.trim(),
            shipping_time: selectedTime ? selectedTime.closest('.swiper-slide').querySelector('.h6').textContent + ' ' + selectedTime.nextElementSibling.textContent : null,
            full_name: fullName,
            phone: mobile,
            email: email,
            address: address,
            postcode: document.getElementById('shipping-postcode').value.trim(),
            payment_method: selectedPaymentMethod.id,
            notes: null
          };

          // Gửi đơn hàng qua AJAX
          fetch('{{ route("payments.process") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            },
            body: JSON.stringify(orderData)
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Chuyển hướng đến trang thành công với ID đơn hàng
              window.location.href = '{{ route("payments.success") }}?order_id=' + data.order.id;
            } else {
              throw new Error(data.message || 'Có lỗi xảy ra khi đặt hàng');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra: ' + error.message);
          })
          .finally(() => {
            // Kích hoạt lại nút
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = 'Đặt hàng <i class="ci-check fs-lg ms-1 me-n1"></i>';
          });
        });
      });
    </script>
@endsection
