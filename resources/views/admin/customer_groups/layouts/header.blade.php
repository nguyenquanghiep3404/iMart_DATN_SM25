<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Phân Nhóm Khách Hàng</h1>
            <p class="mt-1 text-slate-500">Quản lý và tạo các chiến dịch marketing mục tiêu.</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            <a href="?view=trash" id="viewTrashBtn" title="Xem các mục đã xóa"
                class="inline-flex items-center justify-center p-3 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-100 focus:outline-none focus:ring-4 focus:ring-slate-200 transition-all duration-200">
                <!-- SVG Icon for trash will be injected here -->
            </a>
            <button id="addGroupBtn"
                class="inline-flex items-center justify-center px-5 py-3 text-sm font-medium text-white bg-indigo-500 rounded-lg shadow-md hover:bg-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition-all duration-200 transform hover:scale-105">
                <!-- SVG Icon will be injected here -->
                Thêm Nhóm Mới
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                    <tr>
                        <th scope="col" class="px-6 py-4">Tên Nhóm</th>
                        <th scope="col" class="px-6 py-4">Điều kiện</th>
                        <th scope="col" class="px-6 py-4 text-center">Số lượng</th>
                        <th scope="col" class="px-6 py-4 text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody id="groups-table-body">
                    <!-- Group rows will be inserted here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
