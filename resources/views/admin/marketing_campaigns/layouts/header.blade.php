<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Danh Sách Chiến Dịch</h1>
        <p class="mt-1 text-slate-500">Theo dõi và quản lý tất cả các chiến dịch marketing của bạn.</p>
    </div>
    <div class="flex items-center space-x-3 mt-4 sm:mt-0">
        <a href="{{ route('admin.marketing_campaigns.trash') }}" id="viewTrashBtn" title="Xem các mục đã xóa"
            class="inline-flex items-center justify-center p-3 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200 transition-all duration-200">
            <!-- SVG Icon for trash will be injected here -->
        </a>
        <a href="{{ route('admin.campaigns.create') }}" id="createCampaignBtn"
            class="inline-flex items-center justify-center px-5 py-3 text-sm font-medium text-white bg-indigo-500 rounded-lg shadow-md hover:bg-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition-all duration-200 transform hover:scale-105">
            <!-- SVG Icon will be injected here -->
            Tạo Chiến Dịch Mới
        </a>
    </div>
</div>
