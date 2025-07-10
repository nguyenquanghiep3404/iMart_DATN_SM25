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
            class="tab-button w-1/2 py-2.5 px-4 rounded-lg text-sm font-semibold text-gray-600">Bài viết đánh giá</button>
        <button id="tab-specs-btn"
            class="tab-button w-1/2 py-2.5 px-4 rounded-lg text-sm font-semibold tab-active">Thông số kỹ thuật</button>
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
                <!-- Nội dung sẽ được render bởi JavaScript -->
            </div>
        </div>
    </div>
</section>

    <!-- PHẦN 4: ĐÁNH GIÁ & NHẬN XÉT -->
    <section class="bg-white p-6 md:p-8 rounded-xl shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Đánh giá & Nhận xét từ khách hàng</h2>
            <button id="write-review-btn"
                class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Viết
                đánh giá</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="flex flex-col items-center justify-center md:border-r md:border-gray-200 md:pr-8">
                <p class="text-4xl font-bold text-gray-800">4.9 / 5</p>
                <div class="flex text-yellow-400 my-2">★★★★☆</div>
                <p class="text-sm text-gray-600">(1,258 đánh giá)</p>
            </div>
            <div class="col-span-2">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-sm"><span class="text-yellow-400">5 ★</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 85%"></div>
                        </div><span class="text-gray-600 w-12 text-right">1000</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm"><span class="text-yellow-400">4 ★</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 12%"></div>
                        </div><span class="text-gray-600 w-12 text-right">250</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm"><span class="text-yellow-400">3 ★</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 2%"></div>
                        </div><span class="text-gray-600 w-12 text-right">8</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm"><span class="text-yellow-400">2 ★</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 0%"></div>
                        </div><span class="text-gray-600 w-12 text-right">0</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm"><span class="text-yellow-400">1 ★</span>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 1%"></div>
                        </div><span class="text-gray-600 w-12 text-right">2</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 border-t border-gray-200 pt-6">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-y-4">
                <h3 class="text-lg font-bold text-gray-800">1,258 Bình luận</h3>
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
            <div class="my-6">
                <div class="flex items-center gap-2 relative">
                    <textarea id="comment-textarea" maxlength="3000" placeholder="Nhập nội dung bình luận..."
                        class="w-full px-4 py-3 pr-24 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>
                    <span id="char-counter" class="absolute right-32 bottom-3 text-sm text-gray-400">0/3000</span>
                    <button id="comment-submit-btn"
                        class="absolute right-2 bottom-1.5 bg-gray-800 text-white font-semibold py-2 px-5 rounded-lg hover:bg-gray-900 transition-colors">Gửi
                        bình luận</button>
                </div>
            </div>

            <div class="border-b border-gray-200 py-4">
                <div class="flex items-start gap-3">
                    <img src="https://placehold.co/40x40/7e22ce/ffffff?text=N" alt="Avatar"
                        class="w-10 h-10 rounded-full">
                    <div>
                        <p class="font-semibold text-gray-800">Nguyễn V. An</p>
                        <div class="flex text-yellow-400 text-sm my-1">★★★★★</div>
                        <p class="text-sm text-gray-600">Sản phẩm tuyệt vời, đúng hàng chính hãng. Giao hàng nhanh,
                            đóng gói cẩn thận. Máy mượt, pin trâu, chụp ảnh siêu nét. Rất đáng tiền!</p>
                        <div class="flex gap-2 mt-2"><img src="https://placehold.co/80x80/d0d0d0/333?text=Ảnh+thật"
                                alt="Review Image" class="w-20 h-20 rounded-md object-cover"><img
                                src="https://placehold.co/80x80/c0c0c0/333?text=Ảnh+thật" alt="Review Image"
                                class="w-20 h-20 rounded-md object-cover"></div>
                        <div class="text-xs text-gray-500 mt-2 flex items-center gap-4"><span>2 ngày
                                trước</span><button class="flex items-center gap-1 text-blue-600 hover:underline"><svg
                                    xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z">
                                    </path>
                                </svg>Hữu ích</button></div>
                    </div>
                </div>
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
