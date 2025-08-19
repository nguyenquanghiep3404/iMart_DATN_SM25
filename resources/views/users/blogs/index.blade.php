@extends('users.layouts.app')

@section('content')
<main class="content-wrapper">
    <!-- Breadcrumb -->
    <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
        <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.blogs.index') }}">Blog</a></li>
    @if (isset($currentCategory))
        <li class="breadcrumb-item active" aria-current="page">{{ $currentCategory->name }}</li>
    @else
        <li class="breadcrumb-item active" aria-current="page">Tất cả tin tức</li>
    @endif
</ol>

    </nav>

    <!-- Blog list + Sidebar -->
    <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
        <div class="row">
            <!-- Blog list -->
            <div class="col-lg-8 position-relative z-2">

                <!-- Tất cả tin tức mới nhất -->
                <div class="pt-2 mt-2">
                    <h2 class="h3 pb-2 pb-sm-3 text-primary">Tất cả tin tức mới nhất</h2>

                    <div class="d-flex flex-column gap-4 mt-n3">
                        @foreach ($posts as $post)
                            <article class="row align-items-start align-items-md-center gx-0 gy-4 pt-3">
                                <div class="col-sm-5 pe-sm-4">
                                    <a class="ratio d-flex hover-effect-scale rounded overflow-hidden flex-md-shrink-0"
                                       href="{{ route('users.blogs.show', $post->slug) }}"
                                       style="--cz-aspect-ratio: calc(180 / 306 * 100%)">
                                        <img src="{{ asset('storage/' . optional($post->coverImage)->path) }}"
                                             class="hover-effect-target" alt="{{ $post->title }}">
                                    </a>
                                </div>
                                <div class="col-sm-7 d-flex flex-column justify-content-start h-100">
                                    <div class="nav align-items-center gap-2 pb-2 mt-n1 mb-2">
                                        @if ($post->category)
                                            <a class="nav-link text-body fs-xs text-uppercase p-0"
                                               href="{{ route('users.blogs.index', ['category' => $post->category->slug]) }}">
                                                {{ $post->category->name }}
                                            </a>
                                            <hr class="vr my-1 mx-1">
                                        @endif

                                        @if ($post->user)
                                            <span class="text-body fs-xs">Tác giả: {{ $post->user->name }}</span>
                                            <hr class="vr my-1 mx-1">
                                        @endif

                                        <span class="text-body-tertiary fs-xs">
                                            {{ $post->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>

                                    <h3 class="h5 mb-2 mb-md-3">
                                        <a class="hover-effect-underline"
                                           href="{{ route('users.blogs.show', $post->slug) }}">
                                            {{ $post->title }}
                                        </a>
                                    </h3>

                                    <p class="mb-0">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($posts->hasPages())
    <div class="pt-4">
        {{ $posts->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endif

                </div>
            </div>

            <!-- Sidebar -->
            <aside class="col-lg-4 col-xl-3 offset-xl-1" style="margin-top: -100px">
                <div class="offcanvas-lg offcanvas-end sticky-lg-top ps-lg-5 ps-xl-0" id="blogSidebar">
                    <div id="header-spacer" class="d-none d-lg-block" style="height: 60px;"></div>
                    <div class="offcanvas-header py-3">
                        <h5 class="offcanvas-title">Sidebar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                data-bs-target="#blogSidebar" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body d-block pt-2 py-lg-0">
                        <h4 class="h6 mb-4">Danh mục bài viết</h4>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($parentCategories as $category)
                                <a class="btn blog-category-btn px-3"
                                   href="{{ route('users.blogs.index', ['category' => $category->slug]) }}">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>

                        <h4 class="h4 pt-5 mb-0">Bài viết nổi bật</h4>
                        @foreach ($featuredPosts as $featured)
                            <article
                                class="hover-effect-scale position-relative d-flex align-items-center border-bottom py-4">
                                <div class="w-100 pe-3">
                                    <h3 class="h4 lh-base fs-sm mb-0">
                                        <a class="hover-effect-underline stretched-link"
                                           href="{{ route('users.blogs.show', $featured->slug) }}">
                                            {{ $featured->title }}
                                        </a>
                                    </h3>
                                </div>
                                <div class="ratio w-100"
                                     style="max-width: 100px; --cz-aspect-ratio: calc(60 / 86 * 100%)">
                                    <img src="{{ asset('storage/' . optional($featured->coverImage)->path) }}"
                                         class="rounded-2" alt="{{ $featured->title }}">
                                </div>
                            </article>
                        @endforeach

                        <h4 class="h6 pt-4">Follow us</h4>
                        <div class="d-flex gap-2 pb-2">
                            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i
                                    class="ci-instagram"></i></a>
                            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i
                                    class="ci-x"></i></a>
                            <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i
                                    class="ci-facebook"></i></a>

                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</main>
@endsection
@push('styles')
<style>
    /* Bài viết trong danh sách */
    .hover-effect-underline {
        text-decoration: none;
        color: #23272f;
        transition: all 0.2s ease;
    }

    .hover-effect-underline:hover {
        color: #0d6efd;
        text-decoration: underline;
    }

    .ratio img {
        border-radius: 0.7rem;
        box-shadow: 0 2px 12px rgba(60, 72, 88, 0.10);
    }

    /* Sidebar */
    #blogSidebar,
    #blogSidebar * {
        font-size: 1.05rem !important;
    }

    #blogSidebar h4,
    #blogSidebar .h6 {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #0d6efd;
    }

    #blogSidebar .btn,
    #blogSidebar a {
        font-size: 1.05rem !important;
    }

    #blogSidebar .lh-base {
        line-height: 1.6 !important;
    }

    .blog-category-btn {
        border: 1.5px solid #6c757d;
        border-radius: 0.45rem;
        color: #373737;
        font-weight: 500;
        background-color: #fff;
        transition: all 0.2s ease;
    }

    .blog-category-btn:hover {
        background-color: #f4f4ff;
        border-color: #3730A3;
        color: #3730A3;
    }

    #blogSidebar .blog-category-btn:hover {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
</style>
@endpush
