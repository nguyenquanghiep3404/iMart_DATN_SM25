@extends('admin.layouts.app')

@section('title', 'Danh sách sản phẩm')

@push('styles')
{{-- Custom Styles inspired by TailwindCSS for a modern look --}}
<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
    }

    .card {
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25em 0.6em;
        font-size: 0.8em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 9999px;
    }

    .badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }

    /* Status Colors */
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-pending .badge-dot {
        background-color: #f59e0b;
    }

    .status-approved {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .status-approved .badge-dot {
        background-color: #3b82f6;
    }

    .status-processing {
        background-color: #e9d5ff;
        color: #5b21b6;
    }

    .status-processing .badge-dot {
        background-color: #8b5cf6;
    }

    .status-completed {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-completed .badge-dot {
        background-color: #10b981;
    }

    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-rejected .badge-dot {
        background-color: #ef4444;
    }

    .status-cancelled {
        background-color: #e5e7eb;
        color: #4b5563;
    }

    .status-cancelled .badge-dot {
        background-color: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Page Header -->
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Quản lý Yêu cầu Trả hàng</h1>
                <p class="mt-1 text-sm text-gray-600">Theo dõi và xử lý các yêu cầu trả hàng từ khách hàng.</p>
            </div>
            <a href="#" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại
            </a>
        </div>
    </header>

    <div class="card">
        <div class="p-6">
            <!-- Filter and Search Section -->
            <form action="#" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="md:col-span-2 lg:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" name="search" id="search" class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Mã yêu cầu, mã đơn hàng...">
                        </div>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Trạng thái yêu cầu</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending">Chờ duyệt</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="rejected">Từ chối</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-filter mr-2"></i> Lọc
                        </button>
                        <a href="#" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Xóa lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã Yêu Cầu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn Hàng Gốc</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách Hàng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý Do Trả Hàng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày Tạo</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Thao tác</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($returnRequests as $index => $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration + ($returnRequests->currentPage() - 1) * $returnRequests->perPage() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $request->return_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 hover:text-indigo-900">
                            <a href="{{ route('admin.orders.show', $request->order_id) }}">{{ $request->order->order_code ?? 'N/A' }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $request->order->user->name ?? 'Ẩn danh' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->reason_text}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="badge status-{{ $request->status }}">
                                <span class="badge-dot"></span>{{ $request->status_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.refunds.show', $request->id) }}" class="text-indigo-600 hover:text-indigo-900">Xem chi tiết</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Không có yêu cầu nào.</td>
                    </tr>
                    @endforelse
                </tbody>
                <div class="mt-6 px-6">
                    {{ $returnRequests->links('pagination::tailwind') }}
                </div>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg">
            <div class="flex-1 flex justify-between sm:hidden">
                <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Previous </a>
                <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Next </a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Hiển thị từ
                        <span class="font-medium">1</span>
                        đến
                        <span class="font-medium">5</span>
                        trên
                        <span class="font-medium">25</span>
                        kết quả
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left h-5 w-5"></i>
                        </a>
                        <a href="#" aria-current="page" class="z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium"> 1 </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"> 2 </a>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 hidden md:inline-flex relative items-center px-4 py-2 border text-sm font-medium"> 3 </a>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"> ... </span>
                        <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 hidden md:inline-flex relative items-center px-4 py-2 border text-sm font-medium"> 5 </a>
                        <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right h-5 w-5"></i>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@endpush