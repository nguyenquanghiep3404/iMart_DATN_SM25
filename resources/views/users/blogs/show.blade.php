@extends('users.layouts.app')

@section('content')
    <main class="content-wrapper">
        <!-- Breadcrumb -->
        <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.blogs.home') }}">Blog</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
            </ol>
        </nav>

        <!-- Post content + Sidebar -->
        <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
            <div class="row">
                <!-- Post content -->
                <div class="col-lg-8 position-relative z-2">
                    <h1 class="display-6 fw-bold mb-4 text-black">{{ $post->title }}</h1>

                    <div class="nav align-items-center gap-2 border-bottom pb-4 mt-n1 mb-4">
                        @if ($post->category)
                            <a class="nav-link text-body fs-xs text-uppercase p-0" href="#">
                                {{ $post->category->name }}
                            </a>
                            <hr class="vr my-1 mx-1">
                        @endif

                        @if ($post->user)
                            <span class="text-body fs-xs">Tác giả: {{ $post->user->name }}</span>
                        @endif
                        <hr class="vr my-1 mx-1">
                        <span class="text-body-tertiary fs-xs">
                            {{ $post->created_at->format('d/m/Y H:i') }}
                        </span>
                        <hr class="vr my-1 mx-1">
                        <span class="text-body-tertiary fs-xs">
                            <i class="fas fa-eye"></i> Lượt xem: {{ $post->view_count ?? 0 }}
                        </span>
                    </div>

                    <!-- Excerpt -->
                    @if ($post->excerpt)
                        <div class="post-excerpt">
                            <p class="mb-0">{!! nl2br(e($post->excerpt)) !!}</p>
                        </div>
                    @endif

                    <!-- Cover image -->
                    @if ($post->coverImage)
                        <figure class="figure w-100 py-3 py-md-4 mb-3">
                            <div class="ratio hover-effect-scale" style="--cz-aspect-ratio: calc(599 / 856 * 100%)">
                                <img src="{{ asset('storage/' . $post->coverImage->path) }}" class="rounded-4 shadow-sm"
                                    alt="{{ $post->title }}">
                            </div>
                            <figcaption class="figure-caption fs-sm pt-2">{{ $post->coverImage->alt ?? '' }}</figcaption>
                        </figure>
                    @endif

                    <!-- Post content -->
                    <div class="post-content px-2 px-md-4">
                        {!! $post->content !!}
                    </div>

                    @if ($post->tags->count())
                        <div class="post-tags-share border-top pt-4 mt-5">
                            <!-- Tag Title -->
                            <h5 class="fw-semibold mb-3 text-primary">Từ khóa bài viết</h5>

                            <!-- Tag List -->
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                @foreach ($post->tags as $tag)
                                    <a href="{{ route('users.blogs.home', ['tag' => $tag->slug]) }}"
                                        class="badge bg-light text-primary border border-primary px-3 py-2 fw-medium"
                                        style="border-radius: 1.5rem;">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>

                            <!-- Share -->
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-secondary fw-medium">Chia sẻ:</span>
                                <a class="btn btn-icon btn-sm btn-outline-secondary border-0" href="#"><i
                                        class="ci-facebook"></i></a>

                                <a class="btn btn-icon btn-sm btn-outline-secondary border-0" href="#"><i
                                        class="ci-x"></i></a>
                            </div>
                        </div>
                    @endif


                    <!-- Related articles -->
                    <div class="pt-5 mt-2 mt-md-3 mt-lg-4 mt-xl-5">
                        <h2 class="h3 pb-2 pb-sm-3">Bài viết liên quan</h2>
                        <div class="d-flex flex-column gap-4 mt-n3">
                            @foreach ($relatedPosts as $related)
                                <article class="row align-items-start align-items-md-center gx-0 gy-4 pt-3">
                                    <div class="col-sm-5 pe-sm-4">
                                        <a class="ratio d-flex hover-effect-scale rounded overflow-hidden flex-md-shrink-0"
                                            href="{{ route('users.blogs.show', $related->slug) }}"
                                            style="--cz-aspect-ratio: calc(180 / 306 * 100%)">
                                            <img src="{{ asset('storage/' . optional($related->coverImage)->path) }}"
                                                class="hover-effect-target" alt="{{ $related->title }}">
                                        </a>
                                    </div>
                                    <div class="col-sm-7 d-flex flex-column justify-content-start h-100">
                                        <div class="nav align-items-center gap-2 pb-2 mt-n1 mb-2">
                                            @if ($related->category)
                                                <a class="nav-link text-body fs-xs text-uppercase p-0" href="#">
                                                    {{ $related->category->name }}
                                                </a>
                                                <hr class="vr my-1 mx-1">
                                            @endif
                                            @if ($related->user)
                                                <span class="text-body fs-xs">Tác giả: {{ $related->user->name }}</span>
                                                <hr class="vr my-1 mx-1">
                                            @endif
                                            <span class="text-body-tertiary fs-xs">
                                                {{ $related->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>

                                        <h3 class="h5 mb-2 mb-md-3">
                                            <a class="hover-effect-underline"
                                                href="{{ route('users.blogs.show', $related->slug) }}">
                                                {{ $related->title }}
                                            </a>
                                        </h3>

                                        <p class="mb-0">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($related->content), 100) }}
                                        </p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <aside class="col-lg-4 col-xl-3 offset-xl-1" style="margin-top: -115px">
                    <div class="offcanvas-lg offcanvas-end sticky-lg-top ps-lg-5 ps-xl-0" id="blogSidebar">
                        <div id="header-spacer" class="d-none d-lg-block" style="height: 115px;"></div>
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
                                        href="{{ route('users.blogs.home', ['category' => $category->slug]) }}">
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
        /* Tổng thể bài viết */
        .post-content {
            font-size: 1.18rem;
            line-height: 1.85;
            color: #23272f;
            padding: 0.8rem 1.5rem 2.5rem 1.5rem;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 16px 0 rgba(60, 72, 88, 0.07);
            margin-bottom: 2.5rem;
        }

        .post-content p {
            margin: 1.5rem 0 !important;
            letter-spacing: 0.01em;
        }

        .post-content h1,
        .post-content h2,
        .post-content h3 {
            color: #0d6efd;
            font-weight: 700;
            margin: 2.2rem 0 1.2rem 0;
            line-height: 1.25;
        }

        .post-content h1 {
            font-size: 2.1rem;
        }

        .post-content h2 {
            font-size: 1.6rem;
        }

        .post-content h3 {
            font-size: 1.3rem;
        }

        .post-content ul,
        .post-content ol {
            padding-left: 2rem;
            margin-bottom: 1.5rem;
        }

        .post-content li {
            margin-bottom: 0.7rem;
            font-size: 1.08rem;
        }

        .post-content blockquote {
            border-left: 4px solid #0d6efd;
            padding-left: 1.2rem;
            color: #555;
            font-style: italic;
            background-color: #f8f9fa;
            margin: 2rem 0;
            border-radius: 0.5rem;
        }

        .post-content figure {
            margin: 2.5rem auto !important;
            text-align: center;
        }

        .post-content figure img {
            display: block;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            border-radius: 0.7rem;
            box-shadow: 0 2px 12px 0 rgba(60, 72, 88, 0.10);
        }

        .post-content p+figure,
        .post-content figure+p {
            margin-top: 2.2rem !important;
            margin-bottom: 2.2rem !important;
        }

        /* Excerpt nổi bật */
        .post-excerpt {
            font-size: 1.22rem;
            color: #495057;
            background-color: #f4f8fb;
            border-left: 4px solid #0d6efd;
            padding: 1.2rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.7rem;
            font-style: italic;
            box-shadow: 0 1px 8px 0 rgba(60, 72, 88, 0.06);
        }

        /* Tag & share */
        .btn.btn-outline-secondary {
            border-radius: 2rem;
            padding: 0.45rem 1.2rem;
            font-size: 1.02rem;
            margin-bottom: 0.2rem;
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

        /* Responsive */
        @media (max-width: 991.98px) {
            .post-content {
                padding: 1.2rem 0.5rem 1.5rem 0.5rem;
                font-size: 1.05rem;
            }

            .post-excerpt {
                font-size: 1.08rem;
                padding: 1rem 0.7rem;
            }
        }

        .blog-category-btn {
            border: 1.5px solid #6c757d;
            border-radius: 0.45rem;
            /* Bo nhẹ */
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

        /* #blogSidebar .blog-category-btn {
                color: #0d6efd;
                border-color: #0d6efd;
            } */

        #blogSidebar .blog-category-btn:hover {
            background-color: #f4f4ff;
            color: #fff;
            background: #0d6efd;
            border-color: #0d6efd;
        }

        #blogSidebar .hover-effect-underline {
            text-decoration: none;
            color: #23272f;
            transition: all 0.2s ease;
        }

        #blogSidebar .hover-effect-underline:hover {
            color: #0d6efd;
            text-decoration: underline;
        }

        #blogSidebar {
            z-index: 0;
        }
    </style>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            fetch('{{ route('users.blogs.increaseViews', $post->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data.message);
                });
        }, 60000); // 60 giây
    });
</script>
