<div class="card">
    <div class="p-6">
        <label for="product-search" class="block text-lg font-semibold text-gray-800 mb-2">Tìm & Thêm Sản Phẩm</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            {{-- Thêm padding bên phải (pr-10) để có không gian cho icon --}}
            <input type="text" id="product-search" placeholder="Gõ tên sản phẩm hoặc SKU..."
                   class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            
            {{-- Icon quét barcode --}}
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" title="Quét mã vạch">
                <i class="fas fa-barcode text-gray-400 hover:text-gray-600 transition-colors"></i>
            </div>

            <div id="search-suggestions"
                 class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-2 shadow-lg max-h-80 overflow-y-auto hidden custom-scrollbar">
                <!-- Kết quả tìm kiếm sẽ được hiển thị ở đây bởi JS -->
            </div>
        </div>
    </div>
</div>
