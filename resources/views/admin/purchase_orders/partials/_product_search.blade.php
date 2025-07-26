<div class="card">
    <div class="p-6">
        <label for="product-search" class="block text-lg font-semibold text-gray-800 mb-2">Tìm & Thêm Sản Phẩm</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="product-search" placeholder="Gõ tên sản phẩm hoặc SKU..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <div id="search-results" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-1 shadow-lg max-h-60 overflow-y-auto hidden custom-scrollbar">
                <!-- Search results will be populated by JS -->
            </div>
        </div>
    </div>
</div>
