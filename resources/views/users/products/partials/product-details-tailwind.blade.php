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
                        <div class="space-y-3" id="specs-accordion">
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Đánh giá & Nhận xét từ khách hàng</h2>

            {{-- Logic hiển thị nút "Viết đánh giá" hoặc thông báo --}}
            @auth
                @if (isset($orderItemId) && $orderItemId)
                    @if (!isset($hasReviewed) || !$hasReviewed)
                        {{-- Chưa đánh giá, được phép viết --}}
                        <button id="write-review-btn"
                            class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Viết đánh giá
                        </button>
                    @else
                        {{-- Đã đánh giá --}}
                        <p class="text-sm text-green-600">Bạn đã đánh giá sản phẩm này.</p>
                    @endif
                @else
                    {{-- Đã đăng nhập nhưng chưa mua hoặc đơn chưa giao --}}
                    <p class="text-sm text-gray-600">Bạn cần mua sản phẩm này và đơn hàng phải được giao để viết đánh giá.
                    </p>
                @endif
            @else
                {{-- Chưa đăng nhập --}}
                <p class="text-sm text-gray-600">Đăng nhập để viết đánh giá.</p>
            @endauth

        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="flex flex-col items-center justify-center md:border-r md:border-gray-200 md:pr-8">
                {{-- Giả sử $averageRating và $totalReviews được truyền từ controller --}}
                <p class="text-4xl font-bold text-gray-800">{{ number_format($averageRating, 1) }} / 5</p>
                <div class="flex text-yellow-400 my-2">
                    {{-- Logic hiển thị sao dựa trên $averageRating --}}
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= round($averageRating))
                            ★
                        @else
                            <span class="text-gray-300">★</span>
                        @endif
                    @endfor
                </div>
                <p class="text-sm text-gray-600">({{ number_format($totalReviews) }} đánh giá)</p>
            </div>
            <div class="col-span-2">
                <div class="space-y-1">
                    @php
                        // Giả sử $starRatingsCount là một mảng như: ['5' => 1000, '4' => 250, ...]
                        // Và $totalReviews là tổng số đánh giá
                        $starLevels = [5, 4, 3, 2, 1];
                    @endphp

                    @foreach ($starLevels as $star)
                        @php
                            $count = $starRatingsCount[$star] ?? 0;
                            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-yellow-400">{{ $star }} ★</span>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-gray-600 w-12 text-right">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-6 border-t border-gray-200 pt-6">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-y-4">
                <h3 class="text-lg font-bold text-gray-800" id="comments-count">
                    {{ $commentsCount ?? $comments->count() }} Bình luận</h3>
                <div class="flex gap-2 flex-wrap">
                    <button
                        class="px-3 py-1 bg-gray-200 text-gray-800 text-sm font-medium rounded-full border-2 border-transparent hover:border-gray-400">Tất
                        cả</button>
                    <button
                        class="px-3 py-1 bg-gray-200 text-gray-800 text-sm font-medium rounded-full flex items-center gap-1 border-2 border-transparent hover:border-gray-400">5
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg></button>
                    <button
                        class="px-3 py-1 bg-gray-200 text-gray-800 text-sm font-medium rounded-full flex items-center gap-1 border-2 border-transparent hover:border-gray-400">4
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg></button>
                    <button
                        class="px-3 py-1 bg-gray-200 text-gray-800 text-sm font-medium rounded-full flex items-center gap-1 border-2 border-transparent hover:border-gray-400">3
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg></button>
                    <button
                        class="px-3 py-1 bg-gray-200 text-gray-800 text-sm font-medium rounded-full flex items-center gap-1 border-2 border-transparent hover:border-gray-400">2
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg></button>
                    <button
                        class="px-3 py-1 bg-gray-200 text-gray-800 text-sm font-medium rounded-full flex items-center gap-1 border-2 border-transparent hover:border-gray-400">1
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg></button>
                    <button
                        class="px-3 py-1 bg-gray-200 text-gray-800 text-sm font-medium rounded-full border-2 border-transparent hover:border-gray-400">Có
                        hình ảnh/video</button>
                </div>
            </div>
            <!-- New Comment Form -->
            @include('users.products.partials.product-comments')
            <!-- New Comment Form -->
            {{-- @include('users.products.partials.product-comments') --}}
            <div id="review-modal"
                class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4 transition-opacity duration-300 ">
                <div
                    class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-transform duration-300 scale-95">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900">Viết đánh giá</h3><button
                            id="close-review-modal-btn"
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
                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48" aria-hidden="true">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600"><label for="file-upload"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"><span>Tải
                                                lên một file</span><input id="file-upload" name="file-upload"
                                                type="file" class="sr-only" multiple></label>
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

            <div class="reviews-section mt-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Đánh giá sản phẩm</h3>

                @forelse ($reviews as $review)
                    <div class="border-b border-gray-200 py-4">
                        <div class="flex items-start gap-4">
                            {{-- Avatar người dùng --}}
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
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-gray-800">{{ $review->user->name }}</p>
                                </div>

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

                                {{-- Tiêu đề --}}
                                @if ($review->title)
                                    <p class="text-sm text-gray-600 review-text">{{ $review->title }}</p>
                                @endif

                                {{-- Nội dung bình luận --}}
                                <p class="text-sm text-gray-600 review-text">{{ $review->comment }}</p>

                                {{-- Ảnh hoặc video --}}
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
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 mt-4">Chưa có đánh giá nào cho sản phẩm này.</p>
                @endforelse
            </div>
        </div>

    </section>

    <!-- PHẦN 5: HỎI & ĐÁP VỚI TRỢ LÝ AI -->
    <section class="bg-white p-6 md:p-8 rounded-xl shadow-sm">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Hỏi & Đáp với Trợ lý AI</h2>
        <div class="mb-6">
            <textarea id="qna-textarea" placeholder="Nhập câu hỏi của bạn về iPhone 15 Pro Max..."
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
            <!-- Q&A items will be dynamically inserted here -->
        </div>
        <!-- Pagination -->
        <nav id="qna-pagination" class="flex items-center justify-center mt-6"></nav>
    </section>

    <!-- PHẦN 6: SẢN PHẨM TƯƠNG TỰ -->
    <section>
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Sản phẩm tương tự</h2>
        <div class="carousel flex gap-4 overflow-x-auto pb-4">
            <div
                class="product-card flex-shrink-0 w-52 bg-white rounded-lg shadow-sm overflow-hidden transform hover:-translate-y-1 transition-transform">
                <img src="https://placehold.co/200x200/e0e0e0/333?text=iPhone+15+Plus"
                    class="w-full h-40 object-cover">
                <div class="p-3">
                    <h4 class="font-semibold text-sm text-gray-800 truncate">iPhone 15 Plus 128GB</h4>
                    <p class="font-bold text-red-600 mt-1">24.990.000₫</p>
                    <div class="flex items-center gap-1 text-xs text-yellow-500 mt-1">4.8 ★</div>
                </div>
            </div>
            <div
                class="product-card flex-shrink-0 w-52 bg-white rounded-lg shadow-sm overflow-hidden transform hover:-translate-y-1 transition-transform">
                <img src="https://placehold.co/200x200/e0e0e0/333?text=Galaxy+S24+Ultra"
                    class="w-full h-40 object-cover">
                <div class="p-3">
                    <h4 class="font-semibold text-sm text-gray-800 truncate">Samsung Galaxy S24 Ultra</h4>
                    <p class="font-bold text-red-600 mt-1">28.490.000₫</p>
                    <div class="flex items-center gap-1 text-xs text-yellow-500 mt-1">4.9 ★</div>
                </div>
            </div>
            <div
                class="product-card flex-shrink-0 w-52 bg-white rounded-lg shadow-sm overflow-hidden transform hover:-translate-y-1 transition-transform">
                <img src="https://placehold.co/200x200/e0e0e0/333?text=iPhone+15+Pro"
                    class="w-full h-40 object-cover">
                <div class="p-3">
                    <h4 class="font-semibold text-sm text-gray-800 truncate">iPhone 15 Pro 128GB</h4>
                    <p class="font-bold text-red-600 mt-1">27.990.000₫</p>
                    <div class="flex items-center gap-1 text-xs text-yellow-500 mt-1">4.9 ★</div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initCommentForm();
        initReplyForm();
        initReviewModal();
        initTermsCheckboxToggle();
        initUserInfoModal();
    });
    const ORDER_ITEM_ID = {{ isset($orderItemId) ? json_encode($orderItemId) : 'null' }};
    const PRODUCT_VARIANT_ID = {{ json_encode($product->defaultVariant->id ?? null) }};
    const reviewPostUrl = "{{ route('reviews.store') }}";

    // ------------------------
    // Gửi bình luận chính
    function initCommentForm() {
        const mainForm = document.getElementById('comment-form');
        if (!mainForm) return;

        const submitBtn = document.getElementById('comment-submit-btn');
        mainForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            submitBtn.disabled = true;
            submitBtn.innerText = 'Đang gửi...';

            fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async res => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Gửi bình luận';

                    if (!res.ok) {
                        const contentType = res.headers.get("content-type");
                        if (contentType && contentType.includes("application/json")) {
                            const data = await res.json();
                            throw new Error(data.message || 'Lỗi không xác định');
                        } else {
                            throw new Error('Server trả về HTML thay vì JSON');
                        }
                    }

                    return res.json();
                })
                .then(data => {
                    toastr.success(data.message || 'Bình luận đã được gửi thành công!');
                    this.reset();
                })
                .catch(err => {
                    toastr.error(err.message || 'Đã xảy ra lỗi khi gửi bình luận.');
                });
        });

        // Toastr config
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3000,
            showMethod: 'slideDown',
            hideMethod: 'slideUp'
        };
    }

    // ------------------------
    // Gửi phản hồi (trả lời bình luận)
    function initReplyForm() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form.classList.contains('reply-form')) return;

            e.preventDefault();
            const formData = new FormData(form);
            const wrapper = form.closest('.border-b');

            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const div = document.createElement('div');
                        div.classList.add('flex', 'items-start', 'gap-3', 'mb-3');
                        div.innerHTML = `
                    <img src="${data.comment.avatar}" class="w-8 h-8 rounded-full object-cover">
                    <div>
                        <p class="font-semibold text-sm">${data.comment.name}</p>
                        <p class="text-sm text-gray-700">${data.comment.content}</p>
                        <div class="text-xs text-gray-500 mt-1">${data.comment.time}</div>
                    </div>
                `;
                        const replyList = wrapper.querySelector('.reply-list');
                        if (replyList) replyList.appendChild(div);

                        form.reset();
                        form.classList.add('hidden');
                    } else {
                        alert(data.message || 'Đã có lỗi xảy ra khi gửi phản hồi');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Lỗi khi gửi phản hồi');
                });
        });
    }

    // ------------------------
    // Toggle form phản hồi theo ID
    function toggleReplyForm(commentId) {
        const form = document.getElementById('reply-form-' + commentId);
        if (form) form.classList.toggle('hidden');
    }

    // ------------------------
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

        // Render sao đánh giá
        starsContainer.innerHTML = ''; // Xoá nếu có cũ
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

        // Bắt sự kiện click vào nút "Viết đánh giá"
        // Chỉ mở modal nếu nút tồn tại (được hiển thị do người dùng đã mua hàng)
        if (writeBtn) {
            writeBtn.addEventListener('click', () => {
                // Kiểm tra lại orderItemId trong JS để đảm bảo
                if (ORDER_ITEM_ID !== null) {
                    showModal(modal);
                } else {
                    // Mặc dù nút sẽ không hiển thị nếu chưa đủ điều kiện,
                    // đây là lớp bảo vệ bổ sung hoặc nếu bạn có logic JS phức tạp hơn
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


            for (let i = 0; i < files.length && i < 3; i++) {
                formData.append('media[]', files[i]);
            }

            fetch(reviewPostUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                // A more robust way to handle the response
                .then(async res => {
                    const contentType = res.headers.get("content-type");
                    if (res.ok && contentType?.includes("application/json")) {
                        return res.json();
                    }
                    const text = await res.text();
                    throw new Error('Phản hồi không hợp lệ: ' + text);
                })
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message || 'Đánh giá thành công!');
                        hideModal(modal);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(data.message || 'Đánh giá thất bại');
                    }
                })
                .catch(err => {
                    toastr.error(err.message || 'Lỗi kết nối server');
                });

        });
    }


    // ------------------------
    // Checkbox điều kiện hoàn thành QnA
    function initTermsCheckboxToggle() {
        const checkbox = document.getElementById('terms-checkbox');
        const button = document.getElementById('qna-complete-btn');
        if (!checkbox || !button) return;

        checkbox.addEventListener('change', () => {
            const isChecked = checkbox.checked;
            button.disabled = !isChecked;
            button.classList.toggle('bg-gray-300', !isChecked);
            button.classList.toggle('text-gray-500', !isChecked);
            button.classList.toggle('cursor-not-allowed', !isChecked);

            button.classList.toggle('bg-blue-600', isChecked);
            button.classList.toggle('text-white', isChecked);
            button.classList.toggle('hover:bg-blue-700', isChecked);
        });
    }

    // ------------------------
    // Modal hiển thị thông tin người dùng
    function initUserInfoModal() {
        const modal = document.getElementById('user-info-modal');
        const closeBtn = document.getElementById('close-user-info-modal-btn');
        if (!modal || !closeBtn) return;

        closeBtn.addEventListener('click', () => hideModal(modal));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) hideModal(modal);
        });
    }

    // ------------------------
    // Hàm chung mở/đóng modal
    function showModal(modal) {
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('opacity-100', 'scale-100'), 10);
    }

    function hideModal(modal) {
        modal.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>
