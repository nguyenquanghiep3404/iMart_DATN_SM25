<section class="container pb-5 mb-2 mb-md-3 mb-lg-4 mb-xl-5">
    <div class="row">
        <div class="col-md-7">
            <div class="d-flex align-items-center pt-5 mb-4 mt-2 mt-md-3 mt-lg-4" id="reviews"
                style="scroll-margin-top: 80px">
                <h2 class="h3 mb-0">Đánh giá</h2>
                <button type="button" class="btn btn-secondary ms-auto" data-bs-toggle="modal"
                    data-bs-target="#reviewForm">
                    <i class="ci-edit-3 fs-base ms-n1 me-2"></i>
                    Để lại đánh giá
                </button>
            </div>

            <div class="row g-4 pb-3">
                <div class="col-sm-4">
                    <div
                        class="d-flex flex-column align-items-center justify-content-center h-100 bg-body-tertiary rounded p-4">
                        <div class="h1 pb-2 mb-1">{{ $product->average_rating }}</div>
                        <div class="hstack justify-content-center gap-1 fs-sm mb-2">
                            @php
                                $fullStars = floor($product->average_rating);
                                $halfStar = $product->average_rating - $fullStars >= 0.5;
                            @endphp

                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $fullStars)
                                    <i class="ci-star-filled text-warning"></i>
                                @elseif ($i == $fullStars + 1 && $halfStar)
                                    <i class="ci-star-half-filled text-warning"></i>
                                @else
                                    <i class="ci-star text-body-tertiary"></i>
                                @endif
                            @endfor
                        </div>
                        <div class="fs-sm">{{ $totalReviews }} reviews</div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="vstack gap-3">
                        @foreach (range(5, 1) as $star)
                            <div class="hstack gap-2">
                                <div class="hstack fs-sm gap-1">
                                    {{ $star }}<i class="ci-star-filled text-warning"></i>
                                </div>
                                <div class="progress w-100" role="progressbar"
                                    aria-label="{{ $star }} stars" style="height: 4px">
                                    <div class="progress-bar bg-warning rounded-pill"
                                        style="width: {{ $ratingPercentages[$star] ?? 0 }}%"></div>
                                </div>
                                <div class="fs-sm text-nowrap text-end" style="width: 40px;">
                                    {{ $ratingCounts[$star] ?? 0 }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @foreach ($product->reviews as $review)
                <div class="border-bottom py-3 mb-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-nowrap me-3">
                            <span class="h6 mb-0">{{ $review->user->name ?? 'Người dùng' }}</span>
                            <i class="ci-check-circle text-success align-middle ms-1" data-bs-toggle="tooltip"
                                data-bs-placement="top" data-bs-custom-class="tooltip-sm"
                                data-bs-title="Verified customer"></i>
                        </div>
                        <span
                            class="text-body-secondary fs-sm ms-auto">{{ $review->created_at->format('F d, Y') }}</span>
                    </div>
                    <div class="d-flex gap-1 fs-sm pb-2 mb-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="ci-star{{ $i <= $review->rating ? '-filled' : '' }} text-warning"></i>
                        @endfor
                    </div>
                    <p class="fs-sm">{{ $review->comment }}</p>
                    <div class="nav align-items-center">
                        <button type="button" class="nav-link animate-underline px-0">
                            <i class="ci-corner-down-right fs-base ms-1 me-1"></i>
                            <span class="animate-target">Trả lời</span>
                        </button>
                    </div>
                </div>
            @endforeach

            @if ($product->reviews->isEmpty())
                <p class="text-center text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
            @endif

            @include('users.review')
        </div>
    </div>
</section>