@extends('users.layouts.app')

@section('content')
<main class="content-wrapper">
  <!-- Breadcrumb -->
  <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('users.blogs.index') }}">Blog</a></li>
      <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
    </ol>
  </nav>

  <!-- Post content + Sidebar -->
  <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
    <div class="row">
      <!-- Posts content -->
      <div class="col-lg-8 position-relative z-2">
        <h1 class="h3 mb-4">{{ $post->title }}</h1>

        <div class="nav align-items-center gap-2 border-bottom pb-4 mt-n1 mb-4">
          @if ($post->category)
          <a class="nav-link text-body fs-xs text-uppercase p-0" href="#">
            {{ $post->category->name }}
          </a>
          <hr class="vr my-1 mx-1">
          @endif
          <span class="text-body-tertiary fs-xs">{{ $post->created_at->format('F d, Y') }}</span>
        </div>

        @if ($post->coverImage)
        <figure class="figure w-100 py-3 py-md-4 mb-3">
          <div class="ratio" style="--cz-aspect-ratio: calc(599 / 856 * 100%)">
            <img src="{{ asset('storage/' . $post->coverImage->path) }}" class="rounded-4" alt="{{ $post->title }}">
          </div>
          <figcaption class="figure-caption fs-sm pt-2">{{ $post->coverImage->alt ?? '' }}</figcaption>
        </figure>
        @endif

        <div class="post-content">
          {!! $post->content !!}
        </div>

        <!-- Tags -->
        @if ($post->tags->count())
        <div class="d-sm-flex align-items-center justify-content-between py-4 py-md-5 mt-n2 mt-md-n3 mb-2 mb-sm-3 mb-md-0">
          <div class="d-flex flex-wrap gap-2 mb-4 mb-sm-0 me-sm-4">
            @foreach ($post->tags as $tag)
            <a class="btn btn-outline-secondary px-3 mt-1 me-1" href="{{ route('users.blogs.index', ['tag' => $tag->slug]) }}">
              {{ $tag->name }}
            </a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-2">
            <div class="text-body-emphasis fs-sm fw-medium">Share:</div>
            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i class="ci-x"></i></a>
            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i class="ci-facebook"></i></a>
            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i class="ci-telegram"></i></a>
          </div>
        </div>
        @endif

        <!-- Related articles -->
        <div class="pt-5 mt-2 mt-md-3 mt-lg-4 mt-xl-5">
          <h2 class="h3 pb-2 pb-sm-3">Related articles</h2>
          <div class="d-flex flex-column gap-4 mt-n3">
            @foreach ($relatedPosts as $related)
            <article class="row align-items-start align-items-md-center gx-0 gy-4 pt-3">
              <div class="col-sm-5 pe-sm-4">
                <a class="ratio d-flex hover-effect-scale rounded overflow-hidden flex-md-shrink-0"
                   href="{{ route('users.blogs.show', $related->slug) }}"
                   style="--cz-aspect-ratio: calc(226 / 306 * 100%)">
                  <img src="{{ asset('storage/' . optional($related->coverImage)->path) }}"
                       class="hover-effect-target" alt="{{ $related->title }}">
                </a>
              </div>
              <div class="col-sm-7">
                <div class="nav align-items-center gap-2 pb-2 mt-n1 mb-1">
                  @if ($related->category)
                  <a class="nav-link text-body fs-xs text-uppercase p-0" href="#">
                    {{ $related->category->name }}
                  </a>
                  <hr class="vr my-1 mx-1">
                  @endif
                  <span class="text-body-tertiary fs-xs">{{ $related->created_at->format('F d, Y') }}</span>
                </div>
                <h3 class="h5 mb-2 mb-md-3">
                  <a class="hover-effect-underline" href="{{ route('users.blogs.show', $related->slug) }}">
                    {{ $related->title }}
                  </a>
                </h3>
                <p class="mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($related->content), 100) }}</p>
              </div>
            </article>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <aside class="col-lg-4 col-xl-3 offset-xl-1" style="margin-top: -115px">
        <div class="offcanvas-lg offcanvas-end sticky-lg-top ps-lg-4 ps-xl-0" id="blogSidebar">
          <div class="d-none d-lg-block" style="height: 115px"></div>
          <div class="offcanvas-header py-3">
            <h5 class="offcanvas-title">Sidebar</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#blogSidebar" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body d-block pt-2 py-lg-0">
            <h4 class="h6 mb-4">Blog categories</h4>
            <div class="d-flex flex-wrap gap-3">
              @foreach ($allTags as $tag)
              <a class="btn btn-outline-secondary px-3" href="{{ route('users.blogs.index', ['tag' => $tag->slug]) }}">
                {{ $tag->name }}
              </a>
              @endforeach
            </div>

            <h4 class="h6 pt-5 mb-0">Bài viết nổi bật</h4>
            @foreach ($featuredPosts as $featured)
            <article class="hover-effect-scale position-relative d-flex align-items-center border-bottom py-4">
              <div class="w-100 pe-3">
                <h3 class="h6 lh-base fs-sm mb-0">
                  <a class="hover-effect-underline stretched-link" href="{{ route('users.blogs.show', $featured->slug) }}">
                    {{ $featured->title }}
                  </a>
                </h3>
              </div>
              <div class="ratio w-100" style="max-width: 86px; --cz-aspect-ratio: calc(64 / 86 * 100%)">
                <img src="{{ asset('storage/' . optional($featured->coverImage)->path) }}" class="rounded-2" alt="{{ $featured->title }}">
              </div>
            </article>
            @endforeach

            <h4 class="h6 pt-4">Follow us</h4>
            <div class="d-flex gap-2 pb-2">
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i class="ci-instagram"></i></a>
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i class="ci-x"></i></a>
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i class="ci-facebook"></i></a>
              <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i class="ci-telegram"></i></a>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </section>
</main>
@endsection
