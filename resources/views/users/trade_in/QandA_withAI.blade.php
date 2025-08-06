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
    <nav id="qna-pagination" class="flex items-center justify-center mt-6"></nav>
</section>
