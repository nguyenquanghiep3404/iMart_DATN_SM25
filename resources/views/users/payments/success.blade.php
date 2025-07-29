@extends('users.layouts.app')

@section('content')
    <!-- Page content -->
    <main class="content-wrapper">
      <div class="row row-cols-1 row-cols-lg-2 g-0 mx-auto" style="max-width: 1920px">

        <!-- Tank you content column -->
        <div class="col d-flex flex-column justify-content-center py-5 px-xl-4 px-xxl-5">
          <div class="w-100 pt-sm-2 pt-md-3 pt-lg-4 pb-lg-4 pb-xl-5 px-3 px-sm-4 pe-lg-0 ps-lg-5 mx-auto ms-lg-auto me-lg-4" style="max-width: 740px">
            <div class="d-flex align-items-sm-center border-bottom pb-4 pb-md-5">
              <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0" style="width: 3rem; height: 3rem; margin-top: -.125rem">
                <i class="ci-check fs-4"></i>
              </div>
              <div class="w-100 ps-3">
                <div class="fs-sm mb-1">Đơn hàng {{ $order ? '#' . $order->order_code : '#N/A' }}</div>
                <div class="d-sm-flex align-items-center">
                  <h1 class="h4 mb-0 me-3">Cảm ơn bạn đã đặt hàng!</h1>
                  @if($order)
                  <div class="nav mt-2 mt-sm-0 ms-auto">
                
                    <a class="nav-link text-decoration-underline p-0" href="#!">Theo dõi đơn hàng</a>
                  </div>
                  @endif
                </div>
              </div>
            </div>
            @if($order)
            <div class="d-flex flex-column gap-4 pt-3 pb-5 mt-3">
              <div>
                <h3 class="h6 mb-2">Địa chỉ giao hàng</h3>
                <p class="fs-sm mb-1">Họ tên : <strong>{{ $order->customer_name }}</strong></p>
                <p class="fs-sm mb-1">Số điện thoại : <strong>{{ $order->customer_phone }}</strong></p>
                <p class="fs-sm mb-0">Địa chỉ : <strong>{{ $order->shipping_full_address_with_type }}</strong></p>
              </div>
              
              <div>
                <h3 class="h6 mb-2">Phương thức vận chuyển</h3>
                <p class="fs-sm mb-1">
                  @if(str_contains(strtolower($order->shipping_method), 'giao hàng nhanh'))
                    <span class="fw-medium">🚀 Giao hàng nhanh</span>
                    <span class="text-body-secondary">
                      @if($order->shipping_fee > 0)
                         {{ number_format($order->shipping_fee, 0, ',', '.') }} VNĐ
                      @else
                         Miễn phí
                      @endif
                    </span>
                  @elseif(str_contains(strtolower($order->shipping_method), 'giao hàng của cửa hàng'))
                    <span class="fw-medium">🏪 Giao hàng của cửa hàng</span>
                    <span class="text-body-secondary">
                      @if($order->shipping_fee > 0)
                         {{ number_format($order->shipping_fee, 0, ',', '.') }} VNĐ
                      @else
                         Miễn phí
                      @endif
                    </span>
                  @else
                    <span class="fw-medium">{{ $order->shipping_method }}</span>
                    <span class="text-body-secondary">
                      @if($order->shipping_fee > 0)
                         {{ number_format($order->shipping_fee, 0, ',', '.') }} VNĐ
                      @else
                         Miễn phí
                      @endif
                    </span>
                  @endif
                </p>
                @if($order->formatted_delivery_date)
                <p class="fs-sm mb-0">Ngày : {{ $order->formatted_delivery_date }}</p>
                @endif
                @if($order->desired_delivery_time_slot)
                <p class="fs-sm mb-0 text-muted">Khung giờ vào lúc : {{ $order->desired_delivery_time_slot }}</p>
                @endif
              </div>
              <div>
                <h3 class="h6 mb-2">Phương thức thanh toán</h3>
                <p class="fs-sm mb-1">
                  @if($order->payment_method === 'cod')
                    Thanh toán khi nhận hàng (COD)
                  @elseif($order->payment_method === 'bank_transfer')
                    Chuyển khoản ngân hàng
                  @elseif($order->payment_method === 'vnpay')
                    VNPay
                  @else
                    {{ $order->payment_method }}
                  @endif
                </p>
                <p class="fs-sm mb-0 text-muted">
                  Trạng thái: 
                  @if($order->payment_status === 'pending')
                    <span class="badge bg-warning">Chờ thanh toán</span>
                  @elseif($order->payment_status === 'paid')
                    <span class="badge bg-success">Đã thanh toán</span>
                  @else
                    <span class="badge bg-secondary">{{ $order->payment_status }}</span>
                  @endif
                </p>
              </div>
              <div>
                <h3 class="h6 mb-2">Tổng đơn hàng</h3>
                <div class="fs-sm">
                  <div class="d-flex justify-content-between mb-1">
                    <span>Tạm tính:</span>
                    <span>{{ number_format($order->sub_total, 0, ',', '.') }} VNĐ</span>
                  </div>
                  @if($order->discount_amount > 0)
                  <div class="d-flex justify-content-between mb-1">
                    <span>Giảm giá:</span>
                    <span class="text-danger">-{{ number_format($order->discount_amount, 0, ',', '.') }} VNĐ</span>
                  </div>
                  @endif
                  <div class="d-flex justify-content-between mb-1">
                    <span>Phí vận chuyển:</span>
                    <span>{{ $order->shipping_fee > 0 ? number_format($order->shipping_fee, 0, ',', '.') . ' VNĐ' : 'Miễn phí' }}</span>
                  </div>
                  <div class="d-flex justify-content-between border-top pt-2 fw-bold">
                    <span>Tổng cộng:</span>
                    <span>{{ number_format($order->grand_total, 0, ',', '.') }} VNĐ</span>
                  </div>
                </div>
              </div>
            </div>
            @endif
            @if($order)
              @if(str_contains(strtolower($order->shipping_method), 'nhận tại cửa hàng'))
                <!-- Thông báo cho "Nhận tại cửa hàng" -->
                <div class="bg-info rounded px-4 py-4" style="--cz-bg-opacity: .2">
                  <div class="py-3">
                    <h2 class="h5 text-center pb-2 mb-1">🏪 Thông báo nhận hàng</h2>
                    <p class="fs-sm text-center mb-2">Bạn đã chọn nhận hàng tại cửa hàng</p>
                    @if($order->payment_method === 'cod')
                      <p class="fs-sm text-center mb-0">Vui lòng chuẩn bị đủ tiền mặt <strong>{{ number_format($order->grand_total, 0, ',', '.') }} VNĐ</strong> khi đến nhận hàng.</p>
                    @else
                      <p class="fs-sm text-center mb-0">Chúng tôi sẽ thông báo khi hàng sẵn sàng để bạn đến nhận tại cửa hàng.</p>
                    @endif
                  </div>
                </div>
              @elseif($order->payment_method === 'cod')
                <!-- Thông báo cho "Giao hàng tận nơi + COD" -->
                <div class="bg-warning rounded px-4 py-4" style="--cz-bg-opacity: .2">
                  <div class="py-3">
                    <h2 class="h5 text-center pb-2 mb-1">📦 Lưu ý quan trọng</h2>
                    <p class="fs-sm text-center mb-2">Bạn đã chọn thanh toán khi nhận hàng (COD)</p>
                    <p class="fs-sm text-center mb-0">Vui lòng chuẩn bị đủ tiền mặt <strong>{{ number_format($order->grand_total, 0, ',', '.') }} VNĐ</strong> khi nhận hàng.</p>
                  </div>
                </div>
              @else
                <!-- Thông báo cho "Giao hàng tận nơi + Thanh toán online" -->
                <div class="bg-success rounded px-4 py-4" style="--cz-bg-opacity: .2">
                  <div class="py-3">
                    <h2 class="h5 text-center pb-2 mb-1">🎉 Cảm ơn bạn đã tin tưởng iMart!</h2>
                    <p class="fs-sm text-center mb-0">Đơn hàng của bạn đang được xử lý và sẽ sớm được giao đến tận nơi.</p>
                  </div>
                </div>
              @endif
            @endif
            <p class="fs-sm pt-4 pt-md-5 mt-2 mt-sm-3 mt-md-0 mb-0">Cần hỗ trợ?<a class="fw-medium ms-2" href="#!">Liên hệ chúng tôi</a></p>
          </div>
        </div>


        <!-- Related products -->
        <div class="col pt-sm-3 p-md-5 ps-lg-5 py-lg-4 pe-lg-4 p-xxl-5">
          <div class="position-relative d-flex align-items-center h-100 py-5 px-3 px-sm-4 px-xl-5">
            <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-tertiary rounded-5 d-none d-md-block"></span>
            <span class="position-absolute top-0 start-0 w-100 h-100 bg-body-tertiary d-md-none"></span>
            <div class="position-relative w-100 z-2 mx-auto pb-2 pb-sm-3 pb-md-0" style="max-width: 636px">
              <h2 class="h4 text-center pb-3">You may also like</h2>
              <div class="row row-cols-2 g-3 g-sm-4 mb-4">

                <!-- Item -->
                <div class="col">
                  <div class="product-card animate-underline hover-effect-opacity bg-body rounded shadow-none">
                    <div class="position-relative">
                      <div class="position-absolute top-0 end-0 z-2 hover-effect-target opacity-0 mt-3 me-3">
                        <div class="d-flex flex-column gap-2">
                          <button type="button" class="btn btn-icon btn-secondary animate-pulse d-none d-lg-inline-flex" aria-label="Add to Wishlist">
                            <i class="ci-heart fs-base animate-target"></i>
                          </button>
                          <button type="button" class="btn btn-icon btn-secondary animate-rotate d-none d-lg-inline-flex" aria-label="Compare">
                            <i class="ci-refresh-cw fs-base animate-target"></i>
                          </button>
                        </div>
                      </div>
                      <div class="dropdown d-lg-none position-absolute top-0 end-0 z-2 mt-2 me-2">
                        <button type="button" class="btn btn-icon btn-sm btn-secondary bg-body" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                          <i class="ci-more-vertical fs-lg"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end fs-xs p-2" style="min-width: auto">
                          <li>
                            <a class="dropdown-item" href="#!">
                              <i class="ci-heart fs-sm ms-n1 me-2"></i>
                              Add to Wishlist
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="#!">
                              <i class="ci-refresh-cw fs-sm ms-n1 me-2"></i>
                              Compare
                            </a>
                          </li>
                        </ul>
                      </div>
                      <a class="d-block rounded-top overflow-hidden p-3 p-sm-4" href="shop-product-general-electronics.html">
                        <span class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2 mt-lg-3 ms-lg-3">-21%</span>
                        <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                          <img src="assets/img/shop/electronics/01.png" alt="VR Glasses">
                        </div>
                      </a>
                    </div>
                    <div class="w-100 min-w-0 px-2 pb-2 px-sm-3 pb-sm-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="d-flex gap-1 fs-xs">
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star text-body-tertiary opacity-75"></i>
                        </div>
                        <span class="text-body-tertiary fs-xs">(123)</span>
                      </div>
                      <h3 class="pb-1 mb-2">
                        <a class="d-block fs-sm fw-medium text-truncate" href="shop-product-general-electronics.html">
                          <span class="animate-target">VRB01 Virtual Reality Glasses</span>
                        </a>
                      </h3>
                      <div class="d-flex align-items-center justify-content-between">
                        <div class="h5 lh-1 mb-0">$340.99 <del class="text-body-tertiary fs-sm fw-normal">$430.00</del></div>
                        <button type="button" class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2" aria-label="Add to Cart">
                          <i class="ci-shopping-cart fs-base animate-target"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Item -->
                <div class="col">
                  <div class="product-card animate-underline hover-effect-opacity bg-body rounded shadow-none">
                    <div class="position-relative">
                      <div class="position-absolute top-0 end-0 z-2 hover-effect-target opacity-0 mt-3 me-3">
                        <div class="d-flex flex-column gap-2">
                          <button type="button" class="btn btn-icon btn-secondary animate-pulse d-none d-lg-inline-flex" aria-label="Add to Wishlist">
                            <i class="ci-heart fs-base animate-target"></i>
                          </button>
                          <button type="button" class="btn btn-icon btn-secondary animate-rotate d-none d-lg-inline-flex" aria-label="Compare">
                            <i class="ci-refresh-cw fs-base animate-target"></i>
                          </button>
                        </div>
                      </div>
                      <div class="dropdown d-lg-none position-absolute top-0 end-0 z-2 mt-2 me-2">
                        <button type="button" class="btn btn-icon btn-sm btn-secondary bg-body" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                          <i class="ci-more-vertical fs-lg"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end fs-xs p-2" style="min-width: auto">
                          <li>
                            <a class="dropdown-item" href="#!">
                              <i class="ci-heart fs-sm ms-n1 me-2"></i>
                              Add to Wishlist
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="#!">
                              <i class="ci-refresh-cw fs-sm ms-n1 me-2"></i>
                              Compare
                            </a>
                          </li>
                        </ul>
                      </div>
                      <a class="d-block rounded-top overflow-hidden p-3 p-sm-4" href="shop-product-general-electronics.html">
                        <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                          <img src="assets/img/shop/electronics/14.png" alt="iPhone 14">
                        </div>
                      </a>
                    </div>
                    <div class="w-100 min-w-0 px-2 pb-2 px-sm-3 pb-sm-3">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="d-flex gap-1 fs-xs">
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star-filled text-warning"></i>
                          <i class="ci-star-half text-warning"></i>
                        </div>
                        <span class="text-body-tertiary fs-xs">(142)</span>
                      </div>
                      <h3 class="pb-1 mb-2">
                        <a class="d-block fs-sm fw-medium text-truncate" href="shop-product-general-electronics.html">
                          <span class="animate-target">Apple iPhone 14 128GB Blue</span>
                        </a>
                      </h3>
                      <div class="d-flex align-items-center justify-content-between">
                        <div class="h5 lh-1 mb-0">$899.00</div>
                        <button type="button" class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2" aria-label="Add to Cart">
                          <i class="ci-shopping-cart fs-base animate-target"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <a class="btn btn-lg btn-primary w-100" href="{{ route('users.home') }}">
                Tiếp tục mua sắm
                <i class="ci-chevron-right fs-lg ms-1 me-n1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </main>

@endsection 