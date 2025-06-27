@extends('users.layouts.app')

@section('title', 'Điều khoản và điều kiện - iMart')

@section('head')
<style>
  .post-content .content-wrapper {
    line-height: 1.8;
  }
  
  .post-content h1, 
  .post-content h2, 
  .post-content h3, 
  .post-content h4, 
  .post-content h5, 
  .post-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
    color: var(--bs-dark-emphasis);
  }
  
  .post-content h1 { font-size: 2rem; }
  .post-content h2 { font-size: 1.75rem; }
  .post-content h3 { font-size: 1.5rem; }
  .post-content h4 { font-size: 1.25rem; }
  
  .post-content p {
    margin-bottom: 1.25rem;
    text-align: justify;
  }
  
  .post-content ul, 
  .post-content ol {
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
  }
  
  .post-content li {
    margin-bottom: 0.5rem;
  }
  
  .post-content blockquote {
    border-left: 4px solid var(--bs-primary);
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    background-color: var(--bs-light);
    padding: 1rem;
    border-radius: 0.375rem;
  }
  
  .post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
  }
  
  .post-content table {
    width: 100%;
    margin-bottom: 1.5rem;
    border-collapse: collapse;
  }
  
  .post-content table th,
  .post-content table td {
    padding: 0.75rem;
    border: 1px solid var(--bs-border-color);
  }
  
  .post-content table th {
    background-color: var(--bs-light);
    font-weight: 600;
  }
</style>
@endsection

@section('content')
    <!-- Page content -->
    <main class="content-wrapper">

      <!-- Breadcrumb -->
      <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Trang chủ</a></li>
          <li class="breadcrumb-item active" aria-current="page">Điều khoản và điều kiện</li>
        </ol>
      </nav>

      @if(isset($termsPost))
      <!-- Content from Post -->
      <section class="container">
        <div class="row justify-content-center">
          <div class="col-lg-10 col-xl-9">
            
            <!-- Post Header -->
            <div class="text-center mb-4">
              <h1 class="h2 pb-2 pb-sm-3">{{ $termsPost->title }}</h1>
              
              @if($termsPost->excerpt)
                <p class="fs-lg text-muted mb-4">{{ $termsPost->excerpt }}</p>
              @endif
              
              @if($termsPost->coverImage)
                <div class="ratio ratio-21x9 mb-4">
                  <img src="{{ Storage::url($termsPost->coverImage->file_path) }}" 
                       class="rounded-4 object-fit-cover" 
                       alt="{{ $termsPost->title }}">
                </div>
              @endif
            </div>
            <!-- Post Content -->
            <div class="post-content fs-base lh-lg">
              <div class="content-wrapper">
                {!! $termsPost->content !!}
              </div>
            </div>

            <!-- Feedback Section -->
            <hr class="my-4 my-lg-5">
            
            <div class="text-left py-4">
              <h3 class="h5 mb-4">Thông tin này có hữu ích không?</h3>
              <div class="d-flex gap-3 justify-content-left">
                <button type="button" class="btn btn-outline-success px-4">
                  <i class="ci-thumbs-up fs-base me-2"></i>
                  Có
                </button>
                <button type="button" class="btn btn-outline-danger px-4">
                  <i class="ci-thumbs-down fs-base me-2"></i>
                  Không
                </button>
              </div>
              <p class="text-muted mt-3 mb-0 fs-sm">
                Cảm ơn bạn đã đóng góp ý kiến để chúng tôi cải thiện dịch vụ!
              </p>
            </div>
            {{-- info user đăng tin --}}
            {{-- <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
              @if($termsPost->user->avatar)
                <img src="{{ Storage::url($termsPost->user->avatar) }}" 
                     width="48" height="48" 
                     class="rounded-circle me-3" 
                     alt="{{ $termsPost->user->name }}">
              @else
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                     style="width: 48px; height: 48px;">
                  <span class="text-white fw-medium fs-lg">{{ substr($termsPost->user->name, 0, 1) }}</span>
                </div>
              @endif
              <div>
                <div class="fw-semibold text-dark-emphasis">{{ $termsPost->user->name }}</div>
                <small class="text-muted">
                  <i class="ci-calendar me-1"></i>
                  Cập nhật: {{ $termsPost->updated_at->format('d/m/Y') }}
                </small>
              </div>
            </div> --}}
*
          </div>
        </div>
      </section>
      @else
      <!-- Fallback content -->
      <div class="container py-5 mb-2 mt-n2 mt-sm-1 my-md-3 my-lg-4 mb-xl-5">
        <div class="row justify-content-center">
          <div class="col-lg-11 col-xl-10 col-xxl-9">
            <h1 class="h2 pb-2 pb-sm-3 pb-lg-4">Điều khoản và điều kiện</h1>
            <hr class="mt-0">
            <p class="text-muted">Nội dung đang được cập nhật...</p>
          </div>
        </div>
      </div>
      @endif

    </main>
@endsection