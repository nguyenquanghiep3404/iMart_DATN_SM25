@extends('users.layouts.app')

@section('title', $post->title . ' - Trợ giúp - iMart')

@section('content')
    <!-- Page content -->
    <main class="content-wrapper">

      <!-- Breadcrumb -->
      <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('users.home') }}" class="text-muted text-decoration-none">Trang chủ</a></li>
          <li class="breadcrumb-item"><a href="{{ route('users.help') }}" class="text-muted text-decoration-none">Trợ giúp</a></li>
          <li class="breadcrumb-item"><a href="{{ route('users.help') }}#category-{{ $post->postCategory->id }}" class="text-muted text-decoration-none">{{ $post->postCategory->name }}</a></li>
          <li class="breadcrumb-item active text-muted" aria-current="page">{{ Str::limit($post->title, 50) }}</li>
        </ol>
      </nav>

      <!-- Article Content -->
      <section class="container pb-5">
        <div class="row justify-content-center">
          <div class="col-lg-8 col-xl-7">
            
            <!-- Article Header -->
            <div class="mb-4">
              <!-- Category Badge and Date -->
              <div class="d-flex align-items-center gap-3 mb-3">
                {{-- <span class="badge bg-danger text-white px-3 py-2 rounded-pill fs-sm">{{ $post->postCategory->name }}</span> --}}
                <small class="text-muted d-flex align-items-center">
                  <i class="ci-calendar me-1"></i>
                  {{ $post->created_at->format('d/m/Y') }}
                </small>
              </div>
              
              <!-- Title -->
              <h1 class="h3 fw-bold text-dark mb-4">{{ $post->title }}</h1>
            </div>

            <!-- Article Content -->
            <div class="post-content mb-5">
              <div class="fs-base text-dark" style="line-height: 1.8;">
                {!! $post->content !!}
              </div>
            </div>

            <!-- Divider -->
            <hr class="my-5" style="border-top: 2px solid #dee2e6;">

            <!-- Was this helpful section -->
            <div class="text-left py-4 mb-5">
              <h4 class="h5 fw-bold text-dark mb-4">Thông tin này có hữu ích không?</h4>
              <div class="d-flex gap-3 justify-content-left mb-3">
                <button type="button" class="btn btn-outline-success px-4 py-2 rounded-pill">
                  <i class="ci-thumbs-up me-2"></i>
                  Có
                </button>
                <button type="button" class="btn btn-outline-danger px-4 py-2 rounded-pill">
                  <i class="ci-thumbs-down me-2"></i>
                  Không
                </button>
                <a href="mailto:support@imart.vn" class="btn btn-danger px-4 py-2 rounded-pill">
                  <i class="ci-mail me-2"></i>
                  Liên hệ hỗ trợ
                </a>
              </div>
              <p class="text-muted fs-sm mb-0">
                Cảm ơn bạn đã đóng góp ý kiến để chúng tôi cải thiện dịch vụ!
              </p>
            </div>
            <!-- Back Button -->
            <div class="text-left mb-5">
              <a href="{{ route('users.help') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                <i class="ci-arrow-left me-2"></i>
                Quay lại trang trợ giúp
              </a>
            </div>

          </div>
        </div>
      </section>
    </main>
@endsection