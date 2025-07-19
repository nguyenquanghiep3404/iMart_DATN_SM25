<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="mt-10 md:mt-12 space-y-10 md:space-y-12">
    <!-- Mua Kèm Deal Sốc / Cheaper Together -->
    <section class="bg-white p-6 md:p-8 rounded-xl shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Mua Kèm Deal Sốc</h2>
        <div class="flex flex-col lg:flex-row items-center justify-center gap-4" id="bundle-deal-container">
            <!-- Wrapper for scrollable items -->
            <div
                class="w-full flex items-center gap-4 overflow-x-auto lg:overflow-visible pb-4 lg:pb-0 lg:w-auto carousel">
                <!-- Main Product -->
                <div
                    class="flex flex-col items-center text-center p-4 border border-gray-200 rounded-lg flex-shrink-0 w-44 sm:w-48">
                    <img src="https://placehold.co/150x150/f0f0f0/333?text=iPhone+15"
                        class="w-32 h-32 object-contain mb-2">
                    <p class="font-semibold text-sm">iPhone 15 Pro Max</p>
                    <p class="font-bold text-red-600">30.490.000₫</p>
                </div>
                <div class="text-3xl font-light text-gray-400">+</div>
                <!-- Bundle Item 1 -->
                <div
                    class="bundle-item flex flex-col items-center text-center p-4 border border-gray-200 rounded-lg relative flex-shrink-0 w-44 sm:w-48">
                    <input type="checkbox" data-price="4490000"
                        class="bundle-checkbox absolute top-2 right-2 h-5 w-5 rounded text-blue-600 focus:ring-blue-500"
                        checked>
                    <img src="https://placehold.co/150x150/e0e0e0/333?text=AirPods"
                        class="w-32 h-32 object-contain mb-2">
                    <p class="font-semibold text-sm">AirPods Pro 2</p>
                    <p class="font-bold text-red-600">4.490.000₫ <span
                            class="text-gray-500 line-through text-xs">5.990.000₫</span></p>
                </div>
                <div class="text-3xl font-light text-gray-400">+</div>
                <!-- Bundle Item 2 -->
                <div
                    class="bundle-item flex flex-col items-center text-center p-4 border border-gray-200 rounded-lg relative flex-shrink-0 w-44 sm:w-48">
                    <input type="checkbox" data-price="890000"
                        class="bundle-checkbox absolute top-2 right-2 h-5 w-5 rounded text-blue-600 focus:ring-blue-500">
                    <img src="https://placehold.co/150x150/d0d0d0/333?text=Sạc" class="w-32 h-32 object-contain mb-2">
                    <p class="font-semibold text-sm">Sạc nhanh 20W</p>
                    <p class="font-bold text-red-600">890.000₫ <span
                            class="text-gray-500 line-through text-xs">1.200.000₫</span></p>
                </div>
            </div>

            <div class="text-3xl font-light text-gray-400 hidden lg:block">=</div>

            <!-- Total Price -->
            <div
                class="w-full max-w-xs sm:w-auto lg:w-auto lg:max-w-none mt-4 lg:mt-0 lg:ml-4 p-4 border-2 border-red-500 rounded-lg text-center">
                <p class="font-semibold">Tổng giá trị:</p>
                <p id="bundle-total-price" class="text-2xl font-bold text-red-600 my-2">35.870.000₫</p>
                <button class="w-full bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700">Thêm
                    tất cả vào giỏ</button>
            </div>
        </div>
    </section>

    <!-- PHẦN 2 & 3: TABS - BÀI VIẾT & THÔNG SỐ -->
    <section class="bg-white p-6 md:p-8 rounded-xl shadow-sm">
        <!-- Tab Buttons -->
        <div class="flex justify-center border-2 border-gray-200 rounded-xl p-1 mb-6 max-w-md mx-auto">
            <button id="tab-desc-btn"
                class="tab-button w-1/2 py-2.5 px-4 rounded-lg text-sm font-semibold text-gray-600">Bài viết đánh
                giá</button>
            <button id="tab-specs-btn"
                class="tab-button w-1/2 py-2.5 px-4 rounded-lg text-sm font-semibold tab-active">Thông số kỹ
                thuật</button>
        </div>

        <!-- Tab Content -->
        <div>
            <!-- Content for Description Tab -->
            <div id="tab-desc-content" class="tab-content hidden">
                <div id="description-wrapper" class="description-content collapsed">
                    {!! $product->description !!}
                </div>
                <div class="text-center mt-4">
                    <button id="read-more-btn" class="font-semibold text-blue-600 hover:text-blue-800">
                        Xem thêm
                    </button>
                </div>
            </div>

            <!-- Content for Specs Tab -->
            <div id="tab-specs-content" class="tab-content">
                <div class="space-y-3" id="specs-accordion">
                    @if (!empty($specGroupsData))
                        <div class="space-y-3">
                            @foreach ($specGroupsData as $groupName => $specs)
                                <div>
                                    <button
                                        class="accordion-button w-full flex justify-between items-center p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                        onclick="this.nextElementSibling.classList.toggle('hidden')">
                                        <span class="font-semibold text-gray-800">{{ $groupName }}</span>
                                        <svg class="accordion-icon w-5 h-5 text-gray-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div class="accordion-content hidden">
                                        <div class="p-4 border border-t-0 border-gray-200 rounded-b-lg">
                                            <dl class="divide-y divide-gray-100">
                                                @foreach ($specs as $specName => $value)
                                                    <div class="px-1 py-2 grid grid-cols-3 gap-4">
                                                        <dt class="text-sm font-medium text-gray-600">
                                                            {{ $specName }}
                                                        </dt>
                                                        <dd class="text-sm text-gray-800 col-span-2">{{ $value }}
                                                        </dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- PHẦN 4: ĐÁNH GIÁ & NHẬN XÉT -->
    <section class="bg-white p-6 md:p-8 rounded-xl shadow-sm">
        {{-- Header và thống kê đánh giá --}}
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Đánh giá & Nhận xét từ khách hàng</h2>
            @auth
                @if (isset($orderItemId) && $orderItemId)
                    @if (!isset($hasReviewed) || !$hasReviewed)
                        <button id="write-review-btn"
                            class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Viết đánh giá
                        </button>
                    @else
                        <p class="text-sm text-green-600">Bạn đã đánh giá sản phẩm này.</p>
                    @endif
                @else
                    <p class="text-sm text-gray-600">Bạn cần mua và nhận hàng để viết đánh giá.</p>
                @endif
                <p class="text-sm text-gray-600">Đăng nhập để viết đánh giá.</p>
            @endauth
        </div>

        {{-- Thống kê sao --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Cột trái: Trung bình đánh giá -->
            <div class="flex flex-col items-center justify-center md:border-r md:border-gray-200 md:pr-8">
                <p class="text-4xl font-bold text-gray-800">
                    {{ number_format($averageRating, 1) }} / 5
                </p>

                <!-- Hiển thị sao trung bình -->
                <div class="flex my-2">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($averageRating >= $i)
                            <!-- Sao đầy -->
                            <svg class="w-5 h-5 fill-current text-yellow-400" viewBox="0 0 20 20">
                                <path d="M10 15l-5.878 3.09L5.82 12.18 1.64 8.09l6.084-.878L10 2l2.276
                        5.212 6.084.878-4.18 4.09 1.698 5.91z" />
                            </svg>
                        @elseif ($averageRating >= $i - 0.5)
                            <!-- Sao nửa -->
                            <svg class="w-5 h-5 fill-current text-yellow-400" viewBox="0 0 20 20">
                                <defs>
                                    <linearGradient id="half-grad-{{ $i }}" x1="0" x2="1"
                                        y1="0" y2="0">
                                        <stop offset="50%" stop-color="currentColor" />
                                        <stop offset="50%" stop-color="#e5e7eb" />
                                    </linearGradient>
                                </defs>
                                <path fill="url(#half-grad-{{ $i }})" d="M10 15l-5.878 3.09L5.82 12.18 1.64 8.09l6.084-.878L10 2l2.276
                            5.212 6.084.878-4.18 4.09 1.698 5.91z" />
                            </svg>
                        @else
                            <!-- Sao rỗng -->
                            <svg class="w-5 h-5 fill-current text-gray-200" viewBox="0 0 20 20">
                                <path d="M10 15l-5.878 3.09L5.82 12.18 1.64 8.09l6.084-.878L10 2l2.276
                        5.212 6.084.878-4.18 4.09 1.698 5.91z" />
                            </svg>
                        @endif
                    @endfor
                </div>

                <p class="text-sm text-gray-600">
                    ({{ number_format($totalReviews) }} đánh giá)
                </p>
            </div>

            <!-- Cột phải: Thống kê theo từng sao -->
            <div class="col-span-2">
                <div class="space-y-2">
                    @foreach ([5, 4, 3, 2, 1] as $star)
                        @php
                            $count = $starRatingsCount[$star] ?? 0;
                            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-yellow-400 w-10">{{ $star }} ★</span>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-yellow-400 h-2.5 rounded-full transition-all duration-300"
                                    style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-gray-600 w-12 text-right">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        {{-- Khu vực bình luận và danh sách tổng hợp --}}
        <div class="mt-6 border-t border-gray-200 pt-6">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-y-4">
                <h3 class="text-lg font-bold text-gray-800">
                    Tất cả nhận xét ({{ $totalReviewsCount + $totalCommentsCount }})
                </h3>

                {{-- Các nút filter --}}
                <div class="flex gap-2 flex-wrap">
                    {{-- Nút "Tất cả" --}}
                    <a href="{{ request()->url() }}"
                        class="px-3 py-1 text-sm font-medium rounded-full border
           {{ request()->has('rating') ? 'bg-gray-200 text-gray-800 hover:border-gray-400' : 'bg-gray-800 text-white border-gray-700' }}">
                        Tất cả
                    </a>

                    {{-- Các nút filter theo sao --}}
                    @for ($i = 5; $i >= 1; $i--)
                        <a href="{{ request()->fullUrlWithQuery(['rating' => $i, 'page' => 1]) }}"
                            class="px-3 py-1 bg-gray-100 text-gray-800 text-sm font-medium rounded-full flex items-center gap-1 border-2 border-transparent hover:bg-gray-200
               {{ request('rating') == $i ? 'bg-gray-800 text-white border-gray-700' : 'bg-gray-200 text-gray-800 hover:border-gray-400' }}">
                            {{ $i }} <svg class="w-4 h-4 text-yellow-400" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                        </a>
                    @endfor
                </div>
            </div>


            <!-- Form để lại bình luận (nếu có) -->
            @include('users.products.partials.product-comments')

            <!-- DANH SÁCH GỘP (BÌNH LUẬN & ĐÁNH GIÁ) -->
            <div id="combined-list" class="mt-6">
                @forelse ($paginatedItems as $item)
                    {{-- MỤC NÀY LÀ MỘT ĐÁNH GIÁ (REVIEW) --}}
                    @if ($item->type === 'review')
                        @php $review = $item->data; @endphp
                        <div class="border-b border-gray-200 py-4">
                            <div class="flex items-start gap-4">
                                {{-- Avatar --}}
                                @if ($review->user->avatar_url)
                                    <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}"
                                        class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center font-semibold text-sm uppercase">
                                        {{ strtoupper(mb_substr($review->user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800">{{ $review->user->name }}</p>

                                    {{-- Sao đánh giá --}}
                                    <div class="flex text-yellow-400 text-sm my-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $review->rating)
                                                ★
                                            @else
                                                <span class="text-gray-300">★</span>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-sm text-gray-600 review-text">{{ $review->comment }}</p>
                                    {{-- Ảnh review --}}
                                    @if ($review->images->count())
                                        <div class="flex gap-2 mt-2 flex-wrap">
                                            @foreach ($review->images as $image)
                                                <a href="{{ Storage::url($image->path) }}" target="_blank"
                                                    class="block">
                                                    <img src="{{ Storage::url($image->path) }}" alt="Ảnh đánh giá"
                                                        class="w-20 h-20 rounded-md object-cover border border-gray-200">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <span
                                        class="text-xs text-gray-500 mt-2 flex items-center gap-4">{{ $review->created_at->diffForHumans() }}</span>

                                    {{-- THÔNG BÁO TRẠNG THÁI --}}
                                    @if (
                                        $review->status !== 'approved' &&
                                            Auth::check() &&
                                            ($review->user_id === Auth::id() || Auth::user()->hasRole('admin')))
                                        <p class="text-yellow-600 text-sm italic mt-1">Bình luận của bạn đang chờ duyệt
                                        </p>
                                    @endif
                                </div>
                                {{-- MỤC NÀY LÀ MỘT BÌNH LUẬN (COMMENT) --}}
                            @elseif ($item->type === 'comment')
                                @include('users.products.partials.recursive-comment', [
                                    'comment' => $item->data,
                                ])
                    @endif

                @empty
                    <p class="text-sm text-gray-500 mt-4">Chưa có đánh giá hoặc bình luận nào.</p>
                @endforelse

                <!-- NÚT PHÂN TRANG -->
                <div class="mt-8">
                    {{ $paginatedItems->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </section>

    <!-- PHẦN 5: HỎI & ĐÁP VỚI TRỢ LÝ AI -->
    <section class="bg-white p-6 md:p-8 rounded-xl shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Hỏi & Đáp với Trợ lý AI</h2>
        <div class="mb-6">
            <textarea id="qna-textarea" placeholder="Nhập câu hỏi của bạn về {{ $product->name }}..."
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                rows="3"></textarea>
            <button id="ask-ai-btn"
                class="mt-2 bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 3a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 3zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM4.134 5.866a.75.75 0 011.06 0l1.061 1.06a.75.75 0 01-1.06 1.06l-1.06-1.06a.75.75 0 010-1.06zm9.193 9.193a.75.75 0 011.06 0l1.06 1.06a.75.75 0 11-1.06 1.06l-1.06-1.06a.75.75 0 010-1.06zM15 10a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0115 10zM10 4a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 4zM5.866 14.134a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 01-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zm9.193-9.193a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 11-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM10 16a6 6 0 100-12 6 6 0 000 12z"
                        clip-rule="evenodd" />
                </svg>
                ✨ Hỏi AI ngay
            </button>
        </div>
        <div id="ai-answer-container" class="hidden mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <!-- AI Answer will be injected here -->
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-t pt-6">Hoặc xem các câu hỏi thường gặp</h3>
        <div id="qna-list" class="space-y-4">
            <!-- Example Q&A Item 1 -->
            <div class="border-b border-gray-200 pb-4">
                <button class="w-full flex justify-between items-center text-left"
                    onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                    <span class="font-semibold text-gray-800">Sản phẩm này có hỗ trợ trả góp không?</span>
                    <svg class="w-5 h-5 text-gray-600 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>
                <div class="text-gray-600 mt-2 hidden">
                    <p>Chào bạn, hiện tại chúng tôi có hỗ trợ trả góp 0% qua thẻ tín dụng của các ngân hàng lớn. Bạn có
                        thể xem chi tiết chính sách trả góp tại trang thanh toán hoặc liên hệ hotline để được tư vấn
                        thêm.</p>
                </div>
            </div>
            <!-- Example Q&A Item 2 -->
            <div class="border-b border-gray-200 pb-4">
                <button class="w-full flex justify-between items-center text-left"
                    onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                    <span class="font-semibold text-gray-800">Thời gian bảo hành của sản phẩm là bao lâu?</span>
                    <svg class="w-5 h-5 text-gray-600 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>
                <div class="text-gray-600 mt-2 hidden">
                    <p>Sản phẩm được bảo hành chính hãng 12 tháng tại các trung tâm bảo hành ủy quyền trên toàn quốc.
                        Mọi lỗi từ nhà sản xuất sẽ được đổi mới trong 30 ngày đầu tiên.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PHẦN 6: SẢN PHẨM TƯƠNG TỰ -->
    <section>
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Sản phẩm tương tự</h2>
        @if ($relatedProducts->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($relatedProducts as $relatedProduct)
                    <div
                        class="product-card bg-white rounded-lg shadow-sm overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                        <a href="{{ route('products.show', $relatedProduct->slug) }}" class="block">
                            <img src="{{ $relatedProduct->coverImage ? Storage::url($relatedProduct->coverImage->path) : 'https://placehold.co/300x300/e2e8f0/e2e8f0' }}"
                                alt="{{ $relatedProduct->name }}" class="w-full h-40 object-cover">
                            <div class="p-3">
                                <h4 class="font-semibold text-sm text-gray-800 truncate">{{ $relatedProduct->name }}
                                </h4>
                                @if ($relatedProduct->defaultVariant)
                                    <p class="font-bold text-red-600 mt-1">
                                        {{ number_format($relatedProduct->defaultVariant->display_price) }}₫</p>
                                @endif
                                <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <span>{{ round($relatedProduct->average_rating, 1) }}</span>
                                    <span class="ml-1">({{ $relatedProduct->reviews_count }} đánh giá)</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    </a>
            </div>
        @endforeach
</div>
@else
<p class="text-center text-gray-500">Không có sản phẩm tương tự.</p>
@endif
</section>
</div>

<!-- MODAL ĐÁNH GIÁ -->
<div id="review-modal"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-transform duration-300 scale-95">
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Viết đánh giá</h3><button id="close-review-modal-btn"
                class="text-gray-500 hover:text-gray-700 text-3xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-4 ">
            <div>
                <label class="font-semibold text-gray-700">Đánh giá của bạn</label>
                <div id="review-stars-container" class="flex items-center gap-1 text-4xl mt-1">
                    <!-- Stars will be generated by JS -->
                </div>
            </div>
            <div>
                <label for="review-text" class="font-semibold text-gray-700">Bình luận</label>
                <textarea id="review-text" placeholder="Hãy chia sẻ cảm nhận của bạn về sản phẩm..."
                    class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    rows="4"></textarea>
            </div>
            <div>
                <label class="font-semibold text-gray-700">Thêm hình ảnh/video</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                            viewBox="0 0 48 48" aria-hidden="true">
                            <path
                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600"><label for="file-upload"
                                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"><span>Tải
                                    lên một file</span><input id="file-upload" name="file-upload" type="file"
                                    class="sr-only" multiple></label>
                            <p class="pl-1">hoặc kéo và thả</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <button id="submit-review-btn"
                    class="bg-blue-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-blue-700 transition-colors">Gửi
                    đánh giá</button>
            </div>
        </div>
    </div>
</div>

{{-- TOÀN BỘ SCRIPT CỦA BẠN --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initReplyForm();
        initReviewModal();
        initTermsCheckboxToggle();
        initUserInfoModal();
    });

    const ORDER_ITEM_ID = {{ isset($orderItemId) ? json_encode($orderItemId) : 'null' }};
    const PRODUCT_VARIANT_ID = {{ json_encode($defaultVariant->id ?? null) }};
    const reviewPostUrl = "{{ route('reviews.store') }}";


    // Gửi phản hồi (trả lời bình luận)
    function initReplyForm() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form.classList.contains('reply-form')) return;

            e.preventDefault();
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');

            const commentContainer = form.closest('.comment-container');
            const repliesContainer = commentContainer ? commentContainer.querySelector('.replies-list') : null;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Đang gửi...';
            }

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Có lỗi xảy ra khi gửi phản hồi.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success && data.comment) {
                        const newReplyHtml = `
                        <div class="flex items-start gap-3 mt-4 ml-10">
                            <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center font-semibold text-sm uppercase">
                               ${data.comment.initial ?? 'A'}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-sm text-gray-800">${data.comment.user_name}</p>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">${data.comment.content}</p>
                                <div class="text-xs text-gray-500 mt-1">${data.comment.created_at_human}</div>
                            </div>
                        </div>
                    `;

                        if (repliesContainer) {
                            repliesContainer.insertAdjacentHTML('beforeend', newReplyHtml);
                        }

                        form.reset();
                        form.classList.add('hidden');
                        toastr.success(data.message || 'Phản hồi của bạn đã được gửi!');
                    } else {
                        throw new Error(data.message || 'Không thể gửi phản hồi.');
                    }
                })
                .catch(err => {
                    toastr.error(err.message);
                })
                .finally(() => {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = 'Gửi';
                    }
                });
        });
    }

    // Toggle form phản hồi theo ID
    function toggleReplyForm(commentId) {
        const form = document.getElementById(`reply-form-${commentId}`);
        if (form) {
            form.classList.toggle('hidden');
            if (!form.classList.contains('hidden')) {
                form.querySelector('textarea').focus();
            }
        }
    }

    // Đánh giá sản phẩm (sao + comment + modal)
    function initReviewModal() {
        const writeBtn = document.getElementById('write-review-btn');
        const modal = document.getElementById('review-modal');
        const closeBtn = document.getElementById('close-review-modal-btn');
        const starsContainer = document.getElementById('review-stars-container');
        const submitBtn = document.getElementById('submit-review-btn');
        const reviewText = document.getElementById('review-text');
        const fileInput = document.getElementById('file-upload');
        let selectedRating = 0;
        if (!writeBtn || !modal || !closeBtn || !starsContainer) return;

        starsContainer.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const star = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            star.setAttribute('class', 'review-star w-8 h-8 text-gray-300 cursor-pointer transition-colors');
            star.setAttribute('fill', 'currentColor');
            star.setAttribute('viewBox', '0 0 20 20');
            star.dataset.rating = i;
            star.innerHTML =
                `<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>`;
            starsContainer.appendChild(star);
        }

        function showModal(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(() => {
                modal.classList.add('opacity-100');
                modal.querySelector('div[class*="transform"]').classList.remove('scale-95');
            }, 10);
        }

        function hideModal(modal) {
            document.body.classList.remove('overflow-hidden');
            modal.classList.remove('opacity-100');
            modal.querySelector('div[class*="transform"]').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        const stars = starsContainer.querySelectorAll('.review-star');
        stars.forEach(star => {
            star.addEventListener('mouseover', () => {
                stars.forEach(s => s.classList.toggle('text-yellow-400', s.dataset.rating <= star
                    .dataset.rating));
            });
            star.addEventListener('mouseout', () => {
                stars.forEach(s => {
                    s.classList.remove('text-yellow-400');
                    s.classList.add(s.dataset.rating <= selectedRating ? 'text-yellow-400' :
                        'text-gray-300');
                });
            });
            star.addEventListener('click', () => {
                selectedRating = parseInt(star.dataset.rating);
                stars.forEach(s => {
                    s.classList.remove('text-yellow-400', 'text-gray-300');
                    s.classList.add(s.dataset.rating <= selectedRating ? 'text-yellow-400' :
                        'text-gray-300');
                });
            });
        });

        if (writeBtn) {
            writeBtn.addEventListener('click', () => {
                if (ORDER_ITEM_ID !== null) {
                    showModal(modal);
                } else {
                    toastr.warning('Bạn cần mua sản phẩm này và đơn hàng phải được giao để viết đánh giá.');
                }
            });
        }
        closeBtn.addEventListener('click', () => hideModal(modal));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) hideModal(modal);
        });

        submitBtn.addEventListener('click', () => {
            if (!selectedRating) return toastr.warning('Vui lòng chọn số sao');
            const comment = reviewText?.value.trim();
            const files = fileInput?.files;

            const formData = new FormData();
            formData.append('rating', selectedRating);
            formData.append('comment', comment);
            formData.append('product_variant_id', PRODUCT_VARIANT_ID);
            if (ORDER_ITEM_ID !== null) {
                formData.append('order_item_id', ORDER_ITEM_ID);
            }

            if (files) {
                for (let i = 0; i < files.length && i < 5; i++) {
                    formData.append('images[]', files[i]);
                }
            }

            fetch(reviewPostUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Có lỗi xảy ra.');
                    }
                    return data;
                })
                .then(data => {
                    toastr.success(data.message || 'Đánh giá thành công!');
                    hideModal(modal);
                    setTimeout(() => location.reload(), 1500);
                })
                .catch(err => {
                    toastr.error(err.message || 'Lỗi kết nối server');
                });
        });
    }

    // Các hàm helper khác
    function initTermsCheckboxToggle() {
        /* ... */
    }

    function initUserInfoModal() {
        /* ... */
    }

    function showModal(modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modal.querySelector('div[class*="transform"]').classList.remove('scale-95');
        }, 10);
    }

    function hideModal(modal) {
        document.body.classList.remove('overflow-hidden');
        modal.classList.remove('opacity-100');
        modal.querySelector('div[class*="transform"]').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
