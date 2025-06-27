@extends('users.layouts.app')

@section('title', 'Trợ giúp - iMart')

@section('content')
    <!-- Page content -->
    <main class="content-wrapper">

      <!-- Hero -->
      <section class="container pt-3 pt-sm-4">
        <div class="position-relative">
          <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none-dark rtl-flip" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
          <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none d-block-dark rtl-flip" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
          <div class="row align-items-center position-relative z-1">
            <div class="col-lg-7 col-xl-5 offset-xl-1 py-5">
              <div class="px-4 px-sm-5 px-xl-0 pe-lg-4">
                <h1 class="text-center text-sm-start mb-4">Chúng tôi có thể giúp gì cho bạn?</h1>
                <form class="d-flex flex-column flex-sm-row gap-2">
                  <input type="search" class="form-control form-control-lg" placeholder="Bạn cần hỗ trợ gì?" aria-label="Tìm kiếm">
                  <button type="submit" class="btn btn-lg btn-primary px-3">
                    <i class="ci-search fs-lg ms-n2 ms-sm-0"></i>
                    <span class="ms-2 d-sm-none">Tìm kiếm</span>
                  </button>
                </form>
                <div class="nav gap-2 pt-3 pt-sm-4 mt-1 mt-sm-0">
                  <span class="nav-link text-body-secondary pe-none p-0 me-1">Chủ đề phổ biến:</span>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="#payment">thanh toán</a>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="#returns">hoàn tiền</a>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="#delivery">giao hàng</a>
                  <a class="nav-link text-body-emphasis text-decoration-underline p-0 me-1" href="#order">đơn hàng</a>
                </div>
              </div>
            </div>
            <div class="col-lg-5 offset-xl-1 d-none d-lg-block">
              <div class="ratio rtl-flip" style="--cz-aspect-ratio: calc(356 / 526 * 100%)">
                <img src="assets/img/help/hero-light.png" class="d-none-dark" alt="Hình ảnh hỗ trợ">
                <img src="assets/img/help/hero-dark.png" class="d-none d-block-dark" alt="Hình ảnh hỗ trợ">
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Help topics -->
      <section class="container py-5">
        <div class="row g-0 pt-md-2 pt-xl-4">
          <div class="col-md-4 col-lg-3 pb-2 pb-sm-3 pb-md-0 mb-4 mb-md-0">
            <h2 class="h5 border-bottom pb-3 pb-sm-4 mb-0">Chủ đề trợ giúp</h2>

            <!-- Nav tabs động từ database -->
            <div class="list-group list-group-borderless pt-4 pe-md-4" role="tablist">
              @if(isset($helpData) && count($helpData) > 0)
                @foreach($helpData as $index => $data)
                  <a class="list-group-item list-group-item-action d-flex align-items-center {{ $index === 0 ? 'active' : '' }}" 
                     href="#category-{{ $data['category']->id }}" 
                     data-bs-toggle="list" 
                     role="tab" 
                     aria-controls="category-{{ $data['category']->id }}" 
                     id="category-{{ $data['category']->id }}-tab" 
                     aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                     {{ $index > 0 ? 'tabindex="-1"' : '' }}>
                    @switch($data['category']->slug)
                      @case('giao-hang')
                        <i class="ci-delivery fs-base opacity-75 me-2"></i>
                        @break
                      @case('tra-hang-hoan-tien')
                        <i class="ci-refresh-cw fs-base opacity-75 me-2"></i>
                        @break
                      @case('tuy-chon-thanh-toan')
                        <i class="ci-credit-card fs-base opacity-75 me-2"></i>
                        @break
                      @case('van-de-ve-don-hang')
                        <i class="ci-shopping-bag fs-base opacity-75 me-2"></i>
                        @break
                      @default
                        <i class="ci-help-circle fs-base opacity-75 me-2"></i>
                    @endswitch
                    {{ $data['category']->name }}
                  </a>
                @endforeach
              @else
                <div class="text-muted">Chưa có dữ liệu trợ giúp</div>
              @endif
            </div>
          </div>
          
          <div class="col-md-8 col-lg-9">
            <!-- Tabs với nội dung động -->
            <div class="tab-content">
              @if(isset($helpData) && count($helpData) > 0)
                @foreach($helpData as $index => $data)
                  <div class="tab-pane {{ $index === 0 ? 'show active' : '' }}" 
                       id="category-{{ $data['category']->id }}" 
                       role="tabpanel" 
                       aria-labelledby="category-{{ $data['category']->id }}-tab">
                    
                    <!-- Tiêu đề danh mục -->
                    <div class="d-flex align-items-start border-bottom ps-md-4 pb-3 pb-sm-4">
                      <h2 class="h5 d-flex min-w-0 mb-0">
                        <span class="text-truncate">{{ $data['category']->name }}</span>
                      </h2>
                    </div>
                    
                    <!-- Danh sách câu hỏi -->
                    <div class="position-relative">
                      <div class="position-absolute top-0 start-0 h-100 border-start d-none d-md-block"></div>
                      <div class="pt-4 ps-md-4">
                        @foreach($data['posts'] as $postIndex => $post)
                          <div class="mb-3">
                            <!-- Câu hỏi - có thể click để mở/đóng -->
                            <button class="btn btn-link text-start text-decoration-none fw-normal p-0 w-100 d-flex align-items-center question-toggle" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#answer-{{ $post->id }}" 
                                    aria-expanded="false" 
                                    aria-controls="answer-{{ $post->id }}">
                              <span class="me-2">{{ $post->title }}</span>
                              <i class="ci-chevron-down ms-auto fs-sm"></i>
                            </button>
                            
                            <!-- Câu trả lời - ẩn/hiện -->
                            <div class="collapse mt-3" id="answer-{{ $post->id }}">
                              <div class="card card-body border-0 bg-light">
                                @if($post->excerpt)
                                  <p class="text-muted fs-sm mb-3">{{ $post->excerpt }}</p>
                                @endif
                                
                                <div class="post-content">
                                  {!! Str::limit(strip_tags($post->content), 500) !!}
                                  @if(strlen(strip_tags($post->content)) > 500)
                                    <a href="{{ route('users.help.answer', $post->slug) }}" class="text-primary ms-2">
                                      Đọc thêm
                                    </a>
                                  @endif
                                </div>
                                
                                <!-- Was this helpful -->
                                <div class="mt-4 pt-3 border-top">
                                  <h6 class="fs-sm mb-3">Thông tin này có hữu ích không?</h6>
                                  <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-success">
                                      <i class="ci-thumbs-up fs-sm me-1"></i>
                                      Có
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger">
                                      <i class="ci-thumbs-down fs-sm me-1"></i>
                                      Không
                                    </button>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="tab-pane show active">
                  <div class="d-flex border-bottom ps-md-4 pb-3 pb-sm-4">
                    <h2 class="h5 mb-0">Chưa có dữ liệu</h2>
                  </div>
                  <div class="ps-md-4 pt-4">
                    <p class="text-muted">Hiện tại chưa có câu hỏi nào trong hệ thống trợ giúp.</p>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Contact CTA -->
        <div class="pt-4 pb-1 pb-sm-3 pb-md-4 pb-xl-5 mt-2 mt-sm-3">
          <h3 class="fs-sm pb-sm-1">Không tìm thấy câu trả lời cho câu hỏi của bạn?</h3>
          <a class="btn btn-lg btn-primary" href="mailto:support@imart.vn">Liên hệ với chúng tôi</a>
        </div>
      </section>

    </main>

    <style>
    .question-toggle {
      color: #333 !important;
      border: none !important;
    }
    
    .question-toggle:hover {
      color: #0d6efd !important;
    }
    
    .question-toggle[aria-expanded="true"] .ci-chevron-down {
      transform: rotate(180deg);
      transition: transform 0.2s ease;
    }
    
    .question-toggle .ci-chevron-down {
      transition: transform 0.2s ease;
    }
    
    .post-content {
      line-height: 1.6;
    }
    </style>
@endsection