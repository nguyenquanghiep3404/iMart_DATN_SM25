@extends('users.layouts.app')

@section('content')
    <!-- Page content -->
    <main class="content-wrapper">
        <!-- Breadcrumb -->
        <nav class="container pt-3 my-3 my-md-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('users.home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Blog</li>
            </ol>
        </nav>


        <!-- Featured posts -->
        <section class="container pb-5">
            <h1 class="h3 fw-bold mb-4 text-primary">Tin Tức Mới Nhất</h1>
            <div class="row gy-5 pb-5">
                {{-- Bài viết mới nhất --}}
                @if ($latestPost)
                    <article class="col-md-6 col-lg-7">
                        <a class="ratio d-flex hover-effect-scale rounded-4 overflow-hidden"
                            href="{{ route('users.blogs.show', $latestPost->slug) }}"
                            style="--cz-aspect-ratio: calc(484 / 746 * 100%)">
                            <img src="{{ asset('storage/' . optional($latestPost->coverImage)->path) }}"
                                class="hover-effect-target rounded-4 shadow-sm" alt="{{ $latestPost->title }}">
                        </a>
                        <div class="pt-4">
                            <div class="nav align-items-center gap-2 pb-2 mt-n1 mb-1">
                                @if ($latestPost->category)
                                    <a class="nav-link text-body fs-xs text-uppercase p-0"
                                        href="{{ route('users.blogs.home', ['category' => $latestPost->category->slug]) }}">
                                        {{ $latestPost->category->name }}
                                    </a>
                                    <hr class="vr my-1 mx-1">
                                @endif
                                <span class="text-body fs-xs">
                                    Tác giả: {{ $latestPost->user->name ?? 'Không rõ' }}
                                </span>
                                <hr class="vr my-1 mx-1">
                                <span class="text-body-tertiary fs-xs">
                                    {{ $latestPost->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <h3 class="h4 fw-bold mb-0">
                                <a class="hover-effect-underline" href="{{ route('users.blogs.show', $latestPost->slug) }}">
                                    {{ $latestPost->title }}
                                </a>
                            </h3>
                        </div>
                    </article>
                @endif

                {{-- 3 bài viết tiếp theo --}}
                <div class="col-md-6 col-lg-5 d-flex flex-column align-content-between gap-4">
                    @foreach ($nextPosts as $post)
                        <article class="hover-effect-scale position-relative d-flex align-items-center ps-xl-4 mb-xl-1">
                            <div class="w-100 pe-3 pe-sm-4 pe-lg-3 pe-xl-4">
                                <div class="nav align-items-center gap-2 pb-2 mb-1">
                                    @if ($post->category)
                                        <a class="nav-link text-body fs-xs text-uppercase p-0"
                                            href="{{ route('users.blogs.home', ['category' => $post->category->slug]) }}">
                                            {{ $post->category->name }}
                                        </a>
                                        <hr class="vr my-1 mx-1">
                                    @endif

                                    <span class="text-body-tertiary fs-xs">
                                        {{ $post->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <h3 class="h5 fw-bold mb-2">
                                    <a class="hover-effect-underline stretched-link"
                                        href="{{ route('users.blogs.show', $post->slug) }}">
                                        {{ $post->title }}
                                    </a>
                                </h3>
                            </div>
                            <div class="ratio w-100 rounded overflow-hidden"
                                style="max-width: 216px; --cz-aspect-ratio: calc(140 / 216 * 100%)">
                                <img src="{{ asset('storage/' . optional($post->coverImage)->path) }}"
                                    class="hover-effect-target rounded-4 shadow-sm" alt="{{ $post->title }}">
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('users.blogs.index') }}" class="btn btn-outline-primary">
                Xem tất cả bài viết
            </a>



            <hr class="my-0 my-md-2 my-lg-4">
        </section>

        <!-- Popular posts grid + Sidebar -->
        <section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
            <div class="row">
                <!-- Popular posts grid -->
                <div class="col-lg-8">
                    <!-- Danh sách bài viết phổ biến -->
                    <h2 class="h3 fw-bold mb-4 text-primary">Bài viết có lượt xem nhiều</h2>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 gy-5 gx-5">
                        @foreach ($popularPosts as $post)
                            <article class="col">
                                <a class="ratio d-flex hover-effect-scale rounded overflow-hidden"
                                    href="{{ route('users.blogs.show', $post->slug) }}"
                                    style="--cz-aspect-ratio: calc(250 / 416 * 100%)">
                                    <img src="{{ asset('storage/' . optional($post->coverImage)->path) }}"
                                        class="hover-effect-target rounded-4 shadow-sm" alt="{{ $post->title }}">
                                </a>
                                <div class="pt-4">
                                    <div class="nav align-items-center gap-2 pb-2 mt-n1 mb-1">
                                        @if ($post->category)
                                            <a class="nav-link text-body fs-xs text-uppercase p-0"
                                                href="{{ route('users.blogs.home', ['category' => $post->category->slug]) }}">
                                                {{ $post->category->name }}
                                            </a>
                                            <hr class="vr my-1 mx-1">
                                        @endif
                                        <span class="text-body-tertiary fs-xs">
                                            {{ $post->created_at->format('d/m/Y H:i') }}
                                        </span>
                                        <hr class="vr my-1 mx-1">
                                        <span class="text-body-tertiary fs-xs">
                                            <i class="fas fa-eye"></i> Lượt xem: {{ $post->view_count ?? 0 }}
                                        </span>
                                    </div>
                                    <h3 class="h5 fw-bold mb-0">
                                        <a class="hover-effect-underline"
                                            href="{{ route('users.blogs.show', $post->slug) }}">
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                </div>
                            </article>
                        @endforeach
                    </div>
                     <!-- Contributors' posts slider -->
            <div class="py-5 my-1 my-sm-2 my-md-3 my-lg-4 my-xl-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <h2 class="h3 mb-0">Charitable contributions</h2>
                  <div class="d-flex gap-2">
                    <button type="button" id="prev-post" class="btn btn-prev btn-icon btn-outline-secondary rounded-circle animate-slide-start me-1" aria-label="Prev">
                      <i class="ci-chevron-left fs-lg animate-target"></i>
                    </button>
                    <button type="button" id="next-post" class="btn btn-next btn-icon btn-outline-secondary rounded-circle animate-slide-end" aria-label="Next">
                      <i class="ci-chevron-right fs-lg animate-target"></i>
                    </button>
                  </div>
                </div>
                <div class="row row-cols-1 row-cols-md-2 g-0 overflow-hidden rounded-5">
  
                  <!-- Binded images (controlled slider) -->
                  <div class="col order-md-2 user-select-none">
                    <div class="swiper h-100" id="images" data-swiper="{
                      &quot;allowTouchMove&quot;: false,
                      &quot;loop&quot;: true,
                      &quot;effect&quot;: &quot;fade&quot;
                    }">
                      <div class="swiper-wrapper">
                        <div class="swiper-slide">
                          <div class="ratio ratio-16x9"></div>
                          <img src="{{ asset('assets/users/img/blog/grid/v1/slider01.jpg') }}" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Image">
                        </div>
                        <div class="swiper-slide">
                          <div class="ratio ratio-16x9"></div>
                          <img src="{{ asset('assets/users/img/blog/grid/v1/slider02.jpg') }}" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" alt="Image">
                        </div>
                      </div>
                    </div>
                  </div>
  
                  <!-- Text slider -->
                  <div class="col bg-dark order-md-1 py-5 px-4 px-sm-5" data-bs-theme="dark">
                    <div class="swiper py-sm-2 py-md-3 my-xl-2 my-xxl-3" data-swiper="{
                      &quot;spaceBetween&quot;: 40,
                      &quot;loop&quot;: true,
                      &quot;speed&quot;: 400,
                      &quot;controlSlider&quot;: &quot;#images&quot;,
                      &quot;navigation&quot;: {
                        &quot;prevEl&quot;: &quot;#prev-post&quot;,
                        &quot;nextEl&quot;: &quot;#next-post&quot;
                      }
                    }">
                      <div class="swiper-wrapper">
                        <div class="swiper-slide">
                          <h3 class="h5">The role of philanthropy in building a better world</h3>
                          <p class="text-body fs-sm pb-4">Charitable contributions are a vital aspect of building a better world. These contributions come in various forms, including monetary donations, volunteering time, providing expertise...</p>
                          <a class="btn btn-outline-light" href="blog-single-v1.html">Read more</a>
                        </div>
                        <div class="swiper-slide">
                          <h3 class="h5">Supporting communities through charitable giving</h3>
                          <p class="text-body fs-sm pb-4">Join us on a journey of generosity as we spotlight the transformative power of charitable contributions. In this section, we celebrate the stories of impact made possible by your...</p>
                          <a class="btn btn-outline-light" href="blog-single-v1.html">Read more</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
                </div>

               



                <!-- Sticky sidebar that turns into offcanvas on screens < 992px wide (lg breakpoint) -->
                <aside class="col-lg-4 col-xl-3 offset-xl-1" style="margin-top: -115px">
                    <div class="offcanvas-lg offcanvas-end sticky-lg-top ps-lg-5 ps-xl-0" id="blogSidebar">
                        <div class="d-none d-lg-block" style="height: 115px;"></div>

                        <!-- Header for mobile -->
                        <div class="offcanvas-header py-3">
                            <h5 class="offcanvas-title">Sidebar</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                data-bs-target="#blogSidebar" aria-label="Close"></button>
                        </div>

                        <!-- Sidebar content -->
                        <div class="offcanvas-body d-block pt-2 py-lg-0">
                            {{-- Danh mục --}}
                            <h4 class="h6 mb-4">Danh mục bài viết</h4>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($parentCategories as $category)
                                    <a class="btn blog-category-btn px-3"
                                        href="{{ route('users.blogs.home', ['category' => $category->slug]) }}">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>

                            {{-- Bài viết nổi bật --}}
                            <h4 class="h4 pt-5 mb-0">Bài viết nổi bật</h4>
                            @foreach ($featuredPosts as $featured)
                                <article
                                    class="hover-effect-scale position-relative d-flex align-items-center border-bottom py-4">
                                    <div class="flex-grow-1 pe-3">
                                        <h3 class="h6 lh-base fs-sm mb-0">
                                            <a class="hover-effect-underline stretched-link"
                                                href="{{ route('users.blogs.show', $featured->slug) }}">
                                                {{ $featured->title }}
                                            </a>
                                        </h3>
                                    </div>
                                    <div class="ratio flex-shrink-0 d-flex align-items-center justify-content-center"
                                        style="max-width: 100px; --cz-aspect-ratio: calc(60 / 86 * 100%)">
                                        <img src="{{ asset('storage/' . optional($featured->coverImage)->path) }}"
                                            class="rounded-2 shadow-sm" alt="{{ $featured->title }}">
                                    </div>
                                </article>
                            @endforeach

                            {{-- Mạng xã hội --}}
                            <h4 class="h6 pt-4">Follow us</h4>
                            <div class="d-flex gap-2 pb-2">
                                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i
                                        class="ci-instagram"></i></a>
                                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i
                                        class="ci-x"></i></a>
                                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i
                                        class="ci-facebook"></i></a>
                                <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#"><i
                                        class="ci-telegram"></i></a>
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
        .content-wrapper {
            font-size: 1.18rem;
            line-height: 1.85;
            color: #23272f;
        }

        .content-wrapper h1,
        .content-wrapper h2,
        .content-wrapper h3 {
            color: #0d6efd;
            font-weight: 700;
            margin: 0rem 0 1.2rem 0;
            line-height: 1.25;
        }

        .content-wrapper h1 {
            font-size: 2.1rem;
        }

        .content-wrapper h2 {
            font-size: 1.6rem;
        }

        .content-wrapper h3 {
            font-size: 1.3rem;
        }

        .content-wrapper figure img {
            display: block;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            border-radius: 0.7rem;
            box-shadow: 0 2px 12px 0 rgba(60, 72, 88, 0.10);
        }

        /* Bài viết mới nhất và tiếp theo */
        .hover-effect-scale img {
            border-radius: 0.7rem;
            box-shadow: 0 2px 12px 0 rgba(60, 72, 88, 0.10);
        }

        .hover-effect-underline {
            text-decoration: none;
            color: #23272f;
            transition: all 0.2s ease;
        }

        .hover-effect-underline:hover {
            color: #0d6efd;
            text-decoration: underline;
        }

        /* Popular posts grid
                        .row-cols-1 .col {
                            background: #fff;
                            border-radius: 1rem;
                            box-shadow: 0 2px 16px 0 rgba(60, 72, 88, 0.07);
                            padding: 1rem;
                        } */

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

        /* Responsive */
        @media (max-width: 991.98px) {
            .content-wrapper {
                font-size: 1.05rem;
            }

            .row-cols-1 .col {
                padding: 0.8rem;
            }
        }

        text-center {
            text-align: center !important;
        }

        #blogSidebar article.d-flex,
        #blogSidebar article.position-relative.d-flex {
            align-items: center;
            min-height: 80px;
        }

        #blogSidebar .ratio {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            min-height: 60px;
        }

        #blogSidebar h3,
        #blogSidebar .h6 {
            margin-bottom: 0;
            line-height: 1.3;
            word-break: break-word;
        }

        #blogSidebar .ratio img {
            object-fit: cover;
            width: 100%;
            height: 60px;
        }
    </style>
@endpush
