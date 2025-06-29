@extends('users.layouts.app')

@section('title', 'Về chúng tôi - iMart')

@section('content')
    <!-- Page content -->
    <main class="content-wrapper">

      <!-- Breadcrumb -->
      <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Trang chủ</a></li>
          <li class="breadcrumb-item active" aria-current="page">Về chúng tôi</li>
        </ol>
      </nav>


      <!-- Hero -->
      <section class="container">
        <div class="row">

          <!-- Cover image -->
          <div class="col-md-7 order-md-2 mb-4 mb-md-0">
            <div class="position-relative h-100">
              <div class="ratio ratio-16x9"></div>
              <img src="assets/img/about/v1/hero.jpg" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover rounded-5" alt="Hình ảnh">
            </div>
          </div>

          <!-- Text + button -->
          <div class="col-md-5 order-md-1">
            <div class="position-relative py-5 px-4 px-sm-5">
              <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none-dark rtl-flip" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
              <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none d-block-dark rtl-flip" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
              <div class="position-relative z-1 py-md-2 py-lg-4 py-xl-5 px-xl-2 px-xxl-4 my-xxl-3">
                <h1 class="pb-1 pb-md-2 pb-lg-3">iMart - Nhiều hơn một cửa hàng</h1>
                <p class="text-dark-emphasis pb-sm-2 pb-lg-0 mb-4 mb-lg-5">Từ năm 2024, chúng tôi đã và đang thực hiện những ước mơ nhỏ và kế hoạch lớn của hàng triệu người. Bạn có thể tìm thấy mọi thứ bạn cần tại đây.</p>
                <a class="btn btn-lg btn-outline-dark animate-slide-down" href="#mission">
                  Tìm hiểu thêm
                  <i class="ci-arrow-down fs-lg animate-target ms-2 me-n1"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- Stats -->
      <section class="container py-5 mt-md-2 mt-lg-4">
        <div class="row row-cols-2 row-cols-md-4 g-4">
          <div class="col text-center">
            <div class="display-4 text-dark-emphasis mb-2">5k</div>
            <p class="fs-sm mb-0">sản phẩm có sẵn để mua</p>
          </div>
          <div class="col text-center">
            <div class="display-4 text-dark-emphasis mb-2">50k</div>
            <p class="fs-sm mb-0">khách hàng đã ghé thăm từ 2024</p>
          </div>
          <div class="col text-center">
            <div class="display-4 text-dark-emphasis mb-2">100+</div>
            <p class="fs-sm mb-0">nhân viên trên toàn quốc</p>
          </div>
          <div class="col text-center">
            <div class="display-4 text-dark-emphasis mb-2">95%</div>
            <p class="fs-sm mb-0">khách hàng quay trở lại</p>
          </div>
        </div>
      </section>


      <!-- CEO quotation (Mission) -->
      <section class="container pt-3 pt-sm-4 pt-lg-5 mt-lg-2 mt-xl-4 mt-xxl-5" id="mission" style="scroll-margin-top: 60px">
        <div class="text-center mx-auto" style="max-width: 690px">
          <h2 class="text-body fs-sm fw-normal">Sứ mệnh</h2>
          <h3 class="h1 pb-2 pb-md-3 mx-auto" style="max-width: 500px">Sản phẩm tốt nhất với giá cả hợp lý</h3>
          <p class="fs-xl pb-2 pb-md-3 pb-lg-4">"Chúng tôi tin rằng mọi thứ tồn tại để làm cho cuộc sống dễ dàng hơn, dễ chịu hơn và tử tế hơn. Đó là lý do tại sao việc tìm kiếm sản phẩm phù hợp phải nhanh chóng, tiện lợi và thú vị. Chúng tôi không chỉ bán thiết bị gia dụng và điện tử, mà còn mang đến sự thoải mái và tiện lợi."</p>
          <img src="assets/img/about/v1/avatar.jpg" width="64" class="d-block rounded-circle mx-auto mb-3" alt="Avatar">
          <h6 class="mb-0">Nguyễn Văn A, CEO iMart</h6>
        </div>
      </section>


      <!-- Principles -->
      <section class="container pt-5">
        <div class="row pt-2 pt-sm-3 pt-md-4 pt-lg-5">
          <div class="col-md-5 col-lg-6 pb-1 pb-sm-2 pb-md-0 mb-4 mb-md-0">
            <div class="ratio ratio-1x1">
              <img src="assets/img/about/v1/delivery.jpg" class="rounded-5" alt="Hình ảnh giao hàng">
            </div>
          </div>
          <div class="col-md-7 col-lg-6 pt-md-3 pt-xl-4 pt-xxl-5">
            <div class="ps-md-3 ps-lg-4 ps-xl-5 ms-xxl-4">
              <h2 class="text-body fs-sm fw-normal">Nguyên tắc</h2>
              <h3 class="h1 pb-1 pb-sm-2 pb-lg-3">Những nguyên tắc chính giúp chúng tôi phát triển</h3>
              <p class="pb-xl-3">iMart là một giải pháp toàn diện để đáp ứng mọi nhu cầu của khách hàng, vừa là điểm khởi đầu vừa là điểm đến cuối cùng trong hành trình tìm kiếm của họ. Chúng tôi hoạt động như một trợ lý đáng tin cậy, tận tâm loại bỏ mọi sự thỏa hiệp khó chịu, biến ước mơ thành hiện thực và trao quyền cho họ suy nghĩ lớn.</p>

              <!-- Accordion -->
              <div class="accordion accordion-alt-icon" id="principles">

                <!-- Item (expanded) -->
                <div class="accordion-item">
                  <h3 class="accordion-header" id="headingFocus">
                    <button type="button" class="accordion-button animate-underline collapsed" data-bs-toggle="collapse" data-bs-target="#focus" aria-expanded="false" aria-controls="focus">
                      <span class="animate-target me-2">Tập trung vào khách hàng</span>
                    </button>
                  </h3>
                  <div class="accordion-collapse collapse" id="focus" aria-labelledby="headingFocus" data-bs-parent="#principles">
                    <div class="accordion-body">Chúng tôi ưu tiên hiểu và dự đoán nhu cầu của khách hàng, mang đến trải nghiệm đặc biệt và cá nhân hóa từ đầu đến cuối.</div>
                  </div>
                </div>

                <!-- Item -->
                <div class="accordion-item">
                  <h3 class="accordion-header" id="headingReputation">
                    <button type="button" class="accordion-button animate-underline collapsed" data-bs-toggle="collapse" data-bs-target="#reputation" aria-expanded="false" aria-controls="reputation">
                      <span class="animate-target me-2">Xây dựng uy tín</span>
                    </button>
                  </h3>
                  <div class="accordion-collapse collapse" id="reputation" aria-labelledby="headingReputation" data-bs-parent="#principles">
                    <div class="accordion-body">Chúng tôi đánh giá cao uy tín vững chắc được xây dựng trên sự chính trực, minh bạch và chất lượng - đảm bảo khách hàng tin tưởng và dựa vào thương hiệu của chúng tôi.</div>
                  </div>
                </div>

                <!-- Item -->
                <div class="accordion-item">
                  <h3 class="accordion-header" id="headingFast">
                    <button type="button" class="accordion-button animate-underline collapsed" data-bs-toggle="collapse" data-bs-target="#fast" aria-expanded="false" aria-controls="fast">
                      <span class="animate-target me-2">Nhanh chóng, tiện lợi và thú vị</span>
                    </button>
                  </h3>
                  <div class="accordion-collapse collapse" id="fast" aria-labelledby="headingFast" data-bs-parent="#principles">
                    <div class="accordion-body">Chúng tôi đã tối ưu hóa quy trình để mang đến tốc độ, sự tiện lợi và trải nghiệm mua sắm thú vị, tái định nghĩa tiêu chuẩn trực tuyến cho khách hàng hài lòng.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- Values (Carousel of icon boxes) -->
      <section class="container-start pt-5">
        <div class="row align-items-center g-0 pt-2 pt-sm-3 pt-md-4 pt-lg-5">
          <div class="col-md-4 col-lg-3 pb-1 pb-md-0 pe-3 ps-md-0 mb-4 mb-md-0">
            <div class="d-flex flex-md-column align-items-end align-items-md-start">
              <div class="mb-md-5 me-3 me-md-0">
                <h2 class="text-body fs-sm fw-normal">Giá trị</h2>
                <h3 class="h1 mb-0">Giá trị đơn giản để đạt được mục tiêu</h3>
              </div>

              <!-- External slider prev/next buttons -->
              <div class="d-flex gap-2">
                <button type="button" id="prev-values" class="btn btn-icon btn-outline-secondary rounded-circle animate-slide-start me-1" aria-label="Trước">
                  <i class="ci-chevron-left fs-xl animate-target"></i>
                </button>
                <button type="button" id="next-values" class="btn btn-icon btn-outline-secondary rounded-circle animate-slide-end" aria-label="Sau">
                  <i class="ci-chevron-right fs-xl animate-target"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="col-md-8 col-lg-9">
            <div class="ps-md-4 ps-lg-5">
              <div class="swiper" data-swiper="{
                &quot;slidesPerView&quot;: &quot;auto&quot;,
                &quot;spaceBetween&quot;: 24,
                &quot;loop&quot;: true,
                &quot;navigation&quot;: {
                  &quot;prevEl&quot;: &quot;#prev-values&quot;,
                  &quot;nextEl&quot;: &quot;#next-values&quot;
                }
              }">
                <div class="swiper-wrapper">

                  <!-- Item -->
                  <div class="swiper-slide w-auto h-auto">
                    <div class="card h-100 rounded-4 px-3" style="max-width: 306px">
                      <div class="card-body py-5 px-3">
                        <div class="h4 h5 d-flex align-items-center">
                          <i class="ci-user-plus fs-4 me-3"></i>
                          Con người
                        </div>
                        <p class="mb-0">Giá trị quan trọng nhất của Công ty là con người (nhân viên, đối tác, khách hàng). Đằng sau mọi thành công trước hết là một con người cụ thể. Chính họ tạo ra sản phẩm, công nghệ và sự đổi mới.</p>
                      </div>
                    </div>
                  </div>

                  <!-- Item -->
                  <div class="swiper-slide w-auto h-auto">
                    <div class="card h-100 rounded-4 px-3" style="max-width: 306px">
                      <div class="card-body py-5 px-3">
                        <div class="h4 h5 d-flex align-items-center">
                          <i class="ci-shopping-bag fs-4 me-3"></i>
                          Dịch vụ
                        </div>
                        <p class="mb-0">Quan tâm, chú ý, mong muốn và khả năng hữu ích (với đồng nghiệp trong bộ phận, các bộ phận khác, khách hàng và tất cả những người xung quanh chúng ta).</p>
                      </div>
                    </div>
                  </div>

                  <!-- Item -->
                  <div class="swiper-slide w-auto h-auto">
                    <div class="card h-100 rounded-4 px-3" style="max-width: 306px">
                      <div class="card-body py-5 px-3">
                        <div class="h4 h5 d-flex align-items-center">
                          <i class="ci-trending-up fs-4 me-3"></i>
                          Trách nhiệm
                        </div>
                        <p class="mb-0">Trách nhiệm là phẩm chất quan trọng của chúng tôi. Chúng tôi không đổ lỗi cho hoàn cảnh bên ngoài hay người khác. Nếu thấy điều gì có thể cải thiện, chúng tôi không chỉ phê bình mà còn đưa ra lựa chọn của riêng mình.</p>
                      </div>
                    </div>
                  </div>

                  <!-- Item -->
                  <div class="swiper-slide w-auto h-auto">
                    <div class="card h-100 rounded-4 px-3" style="max-width: 306px">
                      <div class="card-body py-5 px-3">
                        <div class="h4 h5 d-flex align-items-center">
                          <i class="ci-rocket fs-4 me-3"></i>
                          Đổi mới
                        </div>
                        <p class="mb-0">Chúng tôi nuôi dưỡng văn hóa cải tiến và đổi mới liên tục. Chấp nhận thay đổi và luôn dẫn đầu là điều cần thiết cho thành công. Chúng tôi khuyến khích tư duy sáng tạo, thử nghiệm và theo đuổi ý tưởng mới.</p>
                      </div>
                    </div>
                  </div>

                  <!-- Item -->
                  <div class="swiper-slide w-auto h-auto">
                    <div class="card h-100 rounded-4 px-3" style="max-width: 306px">
                      <div class="card-body py-5 px-3">
                        <div class="h4 h5 d-flex align-items-center">
                          <i class="ci-star fs-4 me-3"></i>
                          Lãnh đạo
                        </div>
                        <p class="mb-0">Người iMart là những cá nhân trẻ trung, đầy tham vọng và năng động. Với những phẩm chất lãnh đạo được xác định, với mong muốn trở thành người giỏi nhất trong những gì họ làm.</p>
                      </div>
                    </div>
                  </div>

                  <!-- Item -->
                  <div class="swiper-slide w-auto h-auto">
                    <div class="card h-100 rounded-4 px-3" style="max-width: 306px">
                      <div class="card-body py-5 px-3">
                        <div class="h4 h5 d-flex align-items-center">
                          <i class="ci-leaf fs-4 me-3"></i>
                          Bền vững
                        </div>
                        <p class="mb-0">Chúng tôi cam kết giảm thiểu tác động môi trường và thúc đẩy các hoạt động bền vững. Từ việc tìm nguồn cung ứng có trách nhiệm đến bao bì thân thiện với môi trường, chúng tôi hướng đến đóng góp tích cực cho sức khỏe hành tinh.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- Video + Blog post -->
      <section class="container pt-5 mt-2 mt-sm-3 mt-md-4 mt-lg-5">
        <div class="row row-cols-1 row-cols-md-2 g-4">
          <div class="col">
            <div class="position-relative h-100">
              <div class="ratio ratio-16x9"></div>
              <img src="assets/img/about/v1/video-cover.jpg" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover rounded-5" alt="Video">
              <div class="position-absolute start-0 bottom-0 d-flex align-items-end w-100 h-100 z-2 p-4">
                <a class="btn btn-lg btn-light rounded-pill m-md-2" href="https://www.youtube.com/watch?v=Sqqj_14wBxU" data-glightbox="">
                  <i class="ci-play fs-lg ms-n1 me-2"></i>
                  Phát
                </a>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="bg-body-tertiary rounded-5 py-5 px-4 px-sm-5">
              <div class="py-md-3 py-lg-4 py-xl-5 px-lg-4 px-xl-5 my-lg-2 my-xl-4 my-xxl-5">
                <h2 class="h3 pb-sm-1 pb-lg-2">Vai trò của từ thiện trong việc xây dựng thế giới tốt đẹp hơn</h2>
                <p class="pb-sm-2 pb-lg-0 mb-4 mb-lg-5">Đóng góp từ thiện là một khía cạnh quan trọng trong việc xây dựng thế giới tốt đẹp hơn. Những đóng góp này có nhiều hình thức khác nhau, bao gồm quyên góp tiền...</p>
                <a class="btn btn-lg btn-outline-dark" href="#!">Tìm hiểu thêm</a>
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- Open positions (Carousel of cards) -->
      <section class="container py-5 mt-2 mb-1 my-sm-3 my-md-4 my-lg-5">
        <div class="d-flex align-items-end justify-content-between pb-3 mb-1 mb-md-3">
          <div class="me-4">
            <h2 class="text-body fs-sm fw-normal">Sự nghiệp</h2>
            <h3 class="h1 mb-0">Vị trí tuyển dụng</h3>
          </div>

          <!-- External slider prev/next buttons -->
          <div class="d-flex justify-content-center justify-content-md-start gap-2">
            <button type="button" id="prev-positions" class="btn btn-icon btn-outline-secondary rounded-circle animate-slide-start me-1" aria-label="Trước">
              <i class="ci-chevron-left fs-xl animate-target"></i>
            </button>
            <button type="button" id="next-positions" class="btn btn-icon btn-outline-secondary rounded-circle animate-slide-end" aria-label="Sau">
              <i class="ci-chevron-right fs-xl animate-target"></i>
            </button>
          </div>
        </div>

        <!-- Slider -->
        <div class="swiper" data-swiper="{
          &quot;slidesPerView&quot;: 1,
          &quot;spaceBetween&quot;: 24,
          &quot;loop&quot;: true,
          &quot;navigation&quot;: {
            &quot;prevEl&quot;: &quot;#prev-positions&quot;,
            &quot;nextEl&quot;: &quot;#next-positions&quot;
          },
          &quot;breakpoints&quot;: {
            &quot;500&quot;: {
              &quot;slidesPerView&quot;: 2
            },
            &quot;800&quot;: {
              &quot;slidesPerView&quot;: 3
            },
            &quot;1080&quot;: {
              &quot;slidesPerView&quot;: 4
            }
          }
        }">
          <div class="swiper-wrapper py-2">

            <!-- Item -->
            <div class="swiper-slide h-auto">
              <a class="card btn btn-outline-secondary w-100 h-100 align-items-start text-wrap text-start rounded-4 px-0 px-xl-2 py-4 py-xl-5" href="#!">
                <div class="card-body pb-0 pt-3 pt-xl-0">
                  <span class="badge bg-info fs-xs rounded-pill">Toàn thời gian</span>
                  <h4 class="h5 py-3 my-2 my-xl-3">Điều phối viên Chuỗi cung ứng và Logistics</h4>
                </div>
                <div class="card-footer w-100 bg-transparent border-0 text-body fs-sm fw-normal pt-0 pb-3 pb-xl-0">Hà Nội</div>
              </a>
            </div>

            <!-- Item -->
            <div class="swiper-slide h-auto">
              <a class="card btn btn-outline-secondary w-100 h-100 align-items-start text-wrap text-start rounded-4 px-0 px-xl-2 py-4 py-xl-5" href="#!">
                <div class="card-body pb-0 pt-3 pt-xl-0">
                  <span class="badge bg-success fs-xs rounded-pill">Bán thời gian</span>
                  <h4 class="h5 py-3 my-2 my-xl-3">Quản lý Nội dung Mạng xã hội</h4>
                </div>
                <div class="card-footer w-100 bg-transparent border-0 text-body fs-sm fw-normal pt-0 pb-3 pb-xl-0">Từ xa</div>
              </a>
            </div>

            <!-- Item -->
            <div class="swiper-slide h-auto">
              <a class="card btn btn-outline-secondary w-100 h-100 align-items-start text-wrap text-start rounded-4 px-0 px-xl-2 py-4 py-xl-5" href="#!">
                <div class="card-body pb-0 pt-3 pt-xl-0">
                  <span class="badge bg-info fs-xs rounded-pill">Toàn thời gian</span>
                  <h4 class="h5 py-3 my-2 my-xl-3">Đại diện Dịch vụ Khách hàng</h4>
                </div>
                <div class="card-footer w-100 bg-transparent border-0 text-body fs-sm fw-normal pt-0 pb-3 pb-xl-0">TP.HCM</div>
              </a>
            </div>

            <!-- Item -->
            <div class="swiper-slide h-auto">
              <a class="card btn btn-outline-secondary w-100 h-100 align-items-start text-wrap text-start rounded-4 px-0 px-xl-2 py-4 py-xl-5" href="#!">
                <div class="card-body pb-0 pt-3 pt-xl-0">
                  <span class="badge bg-warning fs-xs rounded-pill">Freelance</span>
                  <h4 class="h5 py-3 my-2 my-xl-3">Chuyên gia Phân tích Dữ liệu</h4>
                </div>
                <div class="card-footer w-100 bg-transparent border-0 text-body fs-sm fw-normal pt-0 pb-3 pb-xl-0">Từ xa</div>
              </a>
            </div>

            <!-- Item -->
            <div class="swiper-slide h-auto">
              <a class="card btn btn-outline-secondary w-100 h-100 align-items-start text-wrap text-start rounded-4 px-0 px-xl-2 py-4 py-xl-5" href="#!">
                <div class="card-body pb-0 pt-3 pt-xl-0">
                  <span class="badge bg-info fs-xs rounded-pill">Toàn thời gian</span>
                  <h4 class="h5 py-3 my-2 my-xl-3">Giám đốc Thương mại điện tử</h4>
                </div>
                <div class="card-footer w-100 bg-transparent border-0 text-body fs-sm fw-normal pt-0 pb-3 pb-xl-0">Tại văn phòng</div>
              </a>
            </div>
          </div>
        </div>
      </section>


      <!-- Subscription form + Vlog -->
      <section class="bg-body-tertiary py-5">
        <div class="container pt-sm-2 pt-md-3 pt-lg-4 pt-xl-5">
          <div class="row">
            <div class="col-md-6 col-lg-5 mb-5 mb-md-0">
              <h2 class="h4 mb-2">Đăng ký nhận bản tin</h2>
              <p class="text-body pb-2 pb-ms-3">Nhận những cập nhật mới nhất về sản phẩm &amp; khuyến mãi từ chúng tôi</p>
              <form class="d-flex needs-validation pb-1 pb-sm-2 pb-md-3 pb-lg-0 mb-4 mb-lg-5" novalidate="">
                <div class="position-relative w-100 me-2">
                  <input type="email" class="form-control form-control-lg" placeholder="Email của bạn" required="">
                </div>
                <button type="submit" class="btn btn-lg btn-primary">Đăng ký</button>
              </form>
              <div class="d-flex gap-3">
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Instagram">
                  <i class="ci-instagram fs-base"></i>
                </a>
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Facebook">
                  <i class="ci-facebook fs-base"></i>
                </a>
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="YouTube">
                  <i class="ci-youtube fs-base"></i>
                </a>
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Telegram">
                  <i class="ci-telegram fs-base"></i>
                </a>
              </div>
            </div>
            <div class="col-md-6 col-lg-5 col-xl-4 offset-lg-1 offset-xl-2">
              <ul class="list-unstyled d-flex flex-column gap-4 ps-md-4 ps-lg-0 mb-3">
                <li class="nav flex-nowrap align-items-center position-relative">
                  <img src="assets/img/home/electronics/vlog/01.jpg" class="rounded" width="140" alt="Video cover">
                  <div class="ps-3">
                    <div class="fs-xs text-body-secondary lh-sm mb-2">6:16</div>
                    <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">5 Tiện ích mới siêu ngầu bạn phải xem trên iMart - Giá rẻ</a>
                  </div>
                </li>
                <li class="nav flex-nowrap align-items-center position-relative">
                  <img src="assets/img/home/electronics/vlog/02.jpg" class="rounded" width="140" alt="Video cover">
                  <div class="ps-3">
                    <div class="fs-xs text-body-secondary lh-sm mb-2">10:20</div>
                    <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">5 Tiện ích siêu hữu ích trên iMart bạn phải có năm 2024</a>
                  </div>
                </li>
                <li class="nav flex-nowrap align-items-center position-relative">
                  <img src="assets/img/home/electronics/vlog/03.jpg" class="rounded" width="140" alt="Video cover">
                  <div class="ps-3">
                    <div class="fs-xs text-body-secondary lh-sm mb-2">8:40</div>
                    <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">Top 5 Tiện ích tuyệt vời mới trên iMart bạn phải xem</a>
                  </div>
                </li>
              </ul>
              <div class="nav ps-md-4 ps-lg-0">
                <a class="btn nav-link animate-underline text-decoration-none px-0" href="#!">
                  <span class="animate-target">Xem tất cả</span>
                  <i class="ci-chevron-right fs-base ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
@endsection