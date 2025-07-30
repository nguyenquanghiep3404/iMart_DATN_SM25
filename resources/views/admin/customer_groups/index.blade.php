@extends('admin.layouts.app')
@section('content')
    @include('admin.customer_groups.layouts.css')

    <body class="antialiased text-slate-700">
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Phân Nhóm Khách Hàng</h1>
                    <p class="mt-1 text-slate-500">Quản lý và tạo các chiến dịch marketing mục tiêu.</p>
                </div>
                <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                    <a href="{{ route('admin.trashed') }}" id="viewTrashBtn" title="Xem các mục đã xóa"
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
                                <th scope="col" class="px-6 py-4 text-center">Độ ưu tiên</th>
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

        <!-- Modal -->
        <div id="groupModal"
            class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop transition-opacity duration-300 hidden">
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg m-4 transform transition-all duration-300 scale-95 opacity-0 animate-fade-in-scale">
                <form id="groupForm">
                    <input type="hidden" id="groupId" name="id">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-5 border-b border-slate-200">
                        <h3 id="modalTitle" class="text-xl font-semibold text-slate-800"></h3>
                        <button type="button" id="closeModalBtn"
                            class="p-1 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                            <!-- SVG Icon will be injected here -->
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium text-slate-600">Tên nhóm</label>
                            <input type="text" id="name" name="name"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                placeholder="Ví dụ: Khách hàng VIP" required />
                        </div>
                        <div class="space-y-2">
                            <label for="description" class="text-sm font-medium text-slate-600">Mô tả</label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                placeholder="Mô tả ngắn gọn về nhóm khách hàng này..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="condition_orders" class="text-sm font-medium text-slate-600">Số đơn hàng tối
                                    thiểu</label>
                                <input type="number" id="condition_orders" name="condition_orders"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    min="0" />
                            </div>
                            <div class="space-y-2">
                                <label for="condition_spend" class="text-sm font-medium text-slate-600">Tổng chi tiêu tối
                                    thiểu</label>
                                <input type="number" id="condition_spend" name="condition_spend"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    min="0" step="1000" />
                            </div>
                            <div class="space-y-2">
                                <label for="priority" class="text-sm font-medium text-slate-600">Độ ưu tiên</label>
                                <input type="number" id="priority" name="priority" min="0"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end p-5 border-t border-slate-200 space-x-3">
                        <button type="button" id="cancelModalBtn"
                            class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 focus:ring-4 focus:outline-none focus:ring-slate-200 transition">
                            Hủy bỏ
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-500 rounded-lg hover:bg-indigo-600 focus:ring-4 focus:outline-none focus:ring-indigo-300 transition-all duration-200">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
    @include('admin.customer_groups.layouts.script')
    <script>
        const groups = @json($groups);
        console.log(groups); // kiểm tra xem dữ liệu có đúng không
    </script>
@endsection
