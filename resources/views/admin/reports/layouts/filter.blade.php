<div class="p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3 flex-wrap flex-grow">
        <!-- Province Filter -->
        <select id="province-filter"
            class="w-full sm:w-48 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <!-- Options rendered by JS -->
        </select>
        <!-- District Filter -->
        <select id="district-filter"
            class="w-full sm:w-48 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            disabled>
            <!-- Options rendered by JS -->
        </select>
        <!-- Location Type Filter -->
        <select id="location-type-filter"
            class="w-full sm:w-48 py-2 px-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="all">Tất cả loại địa điểm</option>
            <option value="warehouse">Kho tổng</option>
            <option value="store">Cửa hàng</option>
        </select>
        <!-- Search Input -->
        <div class="relative flex-grow min-w-[200px]">
            <input id="search-input" type="text" placeholder="Tìm SKU / Tên sản phẩm..."
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
        </div>
    </div>
</div>
