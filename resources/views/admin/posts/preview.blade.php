```blade
@extends('admin.layouts.app')

@section('title', 'Xem trước bài viết: ' . $post->title)

@push('styles')
    
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Swiper('.swiper-container', {
                effect: 'fade',
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    prevEl: '.swiper-button-prev',
                    nextEl: '.swiper-button-next',
                },
            });
        });
    </script>
@endpush

@section('content')
    <!-- Page content -->
<main class="content-wrapper">

  <!-- Breadcrumb -->
  <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="">Trang chủ</a></li>
      <li class="breadcrumb-item"><a href="">Blog</a></li>
      <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
    </ol>
  </nav>

  <!-- Post content + Sidebar -->
  <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
    <div class="row">

      <!-- Posts content -->
      <div class="col-lg-8 position-relative z-2">

        <!-- Post title -->
        <h1 class="h3 mb-4">{{ $post->title }}</h1>

        <!-- Post meta -->
        <div class="nav align-items-center gap-2 border-bottom pb-4 mt-n1 mb-4">
          @if ($post->category)
            <a class="nav-link text-body fs-xs text-uppercase p-0" href="">{{ $post->category->name }}</a>
          @endif
          <hr class="vr my-1 mx-1">
          <span class="text-body-tertiary fs-xs">{{ $post->published_at ? $post->published_at->format('F d, Y') : 'Chưa xuất bản' }}</span>
        </div>

        <!-- Post content -->
        {!! Purify::clean($post->content) !!}

        <!-- Cover image -->
        @if ($post->coverImage)
          <figure class="figure w-100 py-3 py-md-4 mb-3">
            <div class="ratio" style="--cz-aspect-ratio: calc(599 / 856 * 100%)">
              <img src="{{ asset('storage/' . $post->coverImage->path) }}" class="rounded-4" alt="{{ $post->title }}">
            </div>
            <figcaption class="figure-caption fs-sm pt-2">Hình ảnh bìa bài viết</figcaption>
          </figure>
        @endif

        <!-- Tags + Sharing -->
        <div class="d-sm-flex align-items-center justify-content-between py-4 py-md-5 mt-n2 mt-md-n3 mb-2 mb-sm-3 mb-md-0">
          <div class="d-flex flex-wrap gap-2 mb-4 mb-sm-0 me-sm-4">
            @foreach ($post->tags as $tag)
              <a class="btn btn-outline-secondary px-3 mt-1 me-1" href="">{{ $tag->name }}</a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-2">
            <div class="text-body-emphasis fs-sm fw-medium">Chia sẻ:</div>
            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="" data-bs-toggle="tooltip" data-bs-template="<div class='tooltip fs-xs mb-n2' role='tooltip'><div class='tooltip-inner bg-transparent text-body p-0'></div></div>" title="X (Twitter)" aria-label="Chia sẻ trên X">
              <i class="ci-x"></i>
            </a>
            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="" data-bs-toggle="tooltip" data-bs-template="<div class='tooltip fs-xs mb-n2' role='tooltip'><div class='tooltip-inner bg-transparent text-body p-0'></div></div>" title="Facebook" aria-label="Chia sẻ trên Facebook">
              <i class="ci-facebook"></i>
            </a>
            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="" data-bs-toggle="tooltip" data-bs-template="<div class='tooltip fs-xs mb-n2' role='tooltip'><div class='tooltip-inner bg-transparent text-body p-0'></div></div>" title="Telegram" aria-label="Chia sẻ trên Telegram">
              <i class="ci-telegram"></i>
            </a>
          </div>
        </div>

        <!-- Subscription CTA -->
        <div class="d-sm-flex align-items-center justify-content-between bg-body-tertiary rounded-4 py-5 px-4 px-md-5">
          <div class="mb-4 mb-sm-0 me-sm-4">
            <h3 class="h5 mb-2">Đăng ký nhận bản tin</h3>
            <p class="fs-sm mb-0">Nhận thông tin cập nhật mới nhất về sản phẩm và khuyến mãi của chúng tôi</p>
          </div>
          <button type="button" class="btn btn-dark">
            <i class="ci-mail fs-base ms-n1 me-2"></i>
            Đăng ký
          </button>
        </div>

        <!-- Related articles -->
        @if ($relatedPosts->isNotEmpty())
          <div class="pt-5 mt-2 mt-md-3 mt-lg-4 mt-xl-5">
            <h2 class="h3 pb-2 pb-sm-3">Bài viết liên quan</h2>
            <div class="d-flex flex-column gap-4 mt-n3">
              @foreach ($relatedPosts as $relatedPost)
                <article class="row align-items-start align-items-md-center gx-0 gy-4 pt-3">
                  <div class="col-sm-5 pe-sm-4">
                    <a class="ratio d-flex hover-effect-scale rounded overflow-hidden flex-md-shrink-0" href="{{ route('blog.show', $relatedPost->slug) }}" style="--cz-aspect-ratio: calc(226 / 306 * 100%)">
                      @if ($relatedPost->coverImage)
                        <img src="{{ asset('storage/' . $relatedPost->coverImage->path) }}" class="hover-effect-target rounded-4" alt="{{ $relatedPost->title }}">
                      @else
                        <img src="{{ asset('assets/img/blog/list/placeholder.jpg') }}" class="hover-effect-target rounded-4" alt="Hình ảnh mặc định">
                      @endif
                    </a>
                  </div>
                  <div class="col-sm-7">
                    <div class="nav align-items-center gap-2 pb-2 mt-n1 mb-1">
                      @if ($relatedPost->category)
                        <a class="nav-link text-body fs-xs text-uppercase p-0" href="{{ route('blog.category', $relatedPost->category->slug) }}">{{ $relatedPost->category->name }}</a>
                      @endif
                      <hr class="vr my-1 mx-1">
                      <span class="text-body-tertiary fs-xs">{{ $relatedPost->published_at ? $relatedPost->published_at->format('F d, Y') : 'Chưa xuất bản' }}</span>
                    </div>
                    <h3 class="h5 mb-2 mb-md-3">
                      <a class="hover-effect-underline" href="">{{ $relatedPost->title }}</a>
                    </h3>
                    <p class="mb-0">{{ Str::limit(strip_tags($relatedPost->excerpt ?: $relatedPost->content), 100) }}</p>
                  </div>
                </article>
              @endforeach
              <div class="nav">
                <a class="nav-link animate-underline px-0 py-2" href="">
                  <span class="animate-target">Xem tất cả</span>
                  <i class="ci-chevron-right fs-base ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        @endif
      </div>

      <!-- Sticky sidebar -->
      <aside class="col-lg-4 col-xl-3 offset-xl-1" style="margin-top: -115px">
        <div class="offcanvas-lg offcanvas-end sticky-lg-top ps-lg-4 ps-xl-0" id="blogSidebar">
          <div class="d-none d-lg-block" style="height: 115px"></div>
          <div class="offcanvas-header py-3">
            <h5 class="offcanvas-title">Thanh bên</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#blogSidebar" aria-label="Đóng"></button>
          </div>
          <div class="offcanvas-body d-block pt-2 py-lg-0">
            <h4 class="h6 mb-4">Danh mục blog</h4>
            <div class="d-flex flex-wrap gap-3">
              @foreach ($categories as $category)
                <a class="btn btn-outline-secondary px-3" href="">{{ $category->name }}</a>
              @endforeach
            </div>
            <h4 class="h6 pt-5 mb-0">Bài viết nổi bật</h4>
            @foreach ($trendingPosts as $trendingPost)
              <article class="hover-effect-scale position-relative d-flex align-items-center border-bottom py-4">
                <div class="w-100 pe-3">
                  <h3 class="h6 lh-base fs-sm mb-0">
                    <a class="hover-effect-underline stretched-link" href="">{{ $trendingPost->title }}</a>
                  </h3>
                </div>
                <div class="ratio w-100" style="max-width: 86px; --cz-aspect-ratio: calc(64 / 86 * 100%)">
                  @if ($trendingPost->coverImage)
                    <img src="{{ asset('storage/' . $trendingPost->coverImage->path) }}" class="rounded-2" alt="{{ $trendingPost->title }}">
                  @else
                    <img src="{{ asset('assets/img/blog/grid/v1/placeholder.jpg') }}" class="rounded-2" alt="Hình ảnh mặc định">
                  @endif
                </div>
              </article>
            @endforeach
            <h4 class="h6 pt-4">Theo dõi chúng tôi</h4>
            <div class="d-flex gap-2 pb-2">
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template="<div class='tooltip fs-xs mb-n2' role='tooltip'><div class='tooltip-inner bg-transparent text-body p-0'></div></div>" title="Instagram" aria-label="Theo dõi trên Instagram">
                <i class="ci-instagram"></i>
              </a>
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template="<div class='tooltip fs-xs mb-n2' role='tooltip'><div class='tooltip-inner bg-transparent text-body p-0'></div></div>" title="X (Twitter)" aria-label="Theo dõi trên X">
                <i class="ci-x"></i>
              </a>
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template="<div class='tooltip fs-xs mb-n2' role='tooltip'><div class='tooltip-inner bg-transparent text-body p-0'></div></div>" title="Facebook" aria-label="Theo dõi trên Facebook">
                <i class="ci-facebook"></i>
              </a>
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template="<div class='tooltip fs-xs mb-n2' role='tooltip'><div class='tooltip-inner bg-transparent text-body p-0'></div></div>" title="Telegram" aria-label="Theo dõi trên Telegram">
                <i class="ci-telegram"></i>
              </a>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </section>
</main>
@endsection
```