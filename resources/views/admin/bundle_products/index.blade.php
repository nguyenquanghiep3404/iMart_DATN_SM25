@extends('admin.layouts.app')

@section('title', 'Quản lý Gói Sản Phẩm')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }

        .card-custom {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
        }

        .card-custom-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .card-custom-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .card-custom-body {
            padding: 1.5rem;
        }

        .card-custom-footer {
            background-color: #f9fafb;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }

        .btn {
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.25rem;
            border: 1px solid transparent;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border-color: #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
            background-color: white;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: #4f46e5;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .table-custom {
            width: 100%;
            min-width: 800px;
            color: #374151;
        }

        .table-custom th,
        .table-custom td {
            padding: 0.75rem 1rem;
            vertical-align: middle !important;
            border-bottom-width: 1px;
            border-color: #e5e7eb;
            white-space: nowrap;
        }

        .table-custom thead th {
            font-weight: 600;
            color: #4b5563;
            background-color: #f9fafb;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-align: left;
            border-bottom-width: 2px;
        }

        .badge-custom {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: .75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
        }

        .badge-success-custom {
            background-color: #10b981;
        }

        .badge-secondary-custom {
            background-color: #6b7280;
        }
         .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .toast {
            opacity: 1;
            transform: translateX(0);
            transition: all 0.3s ease-in-out;
        }

        .toast.hide {
            opacity: 0;
            transform: translateX(100%);
        }
    </style>
@endpush

@section('content')
    <div class="px-4 sm:px-6 md:px-8 py-8">
        @include('admin.partials.flash_message')
        <div class="container mx-auto max-w-full">
            <!-- PAGE HEADER -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Deal Bán Kèm</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="#" class="text-indigo-600 hover:text-indigo-800">Bảng
                                điều khiển</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Deal bán kèm</li>
                    </ol>
                </nav>
            </header>

            <div class="card-custom">
                <div class="card-custom-header">
                    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                        <h3 class="card-custom-title">Danh sách Deal ({{ $bundles->count() }})</h3>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.bundle-products.trashed') }}" class="btn btn-danger">
                                <i class="fas fa-trash mr-2"></i>Thùng rác
                            </a>

                            <a href="{{ route('admin.bundle-products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Tạo Deal Mới
                            </a>
                        </div>
                    </div>
                </div>


                <div class="card-custom-body">
                    <!-- FILTERS -->
                    <form action="{{ route('admin.bundle-products.index') }}" method="GET" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                            <div class="md:col-span-2">
                                <label for="search_deal" class="sr-only">Tìm kiếm</label>
                                <input type="text" id="search_deal" name="search"
                                    class="h-10 w-full border border-gray-300 rounded px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Tìm theo tên deal..." value="{{ request('search') }}">
                            </div>

                            <div class="md:col-span-2">
                                <label for="filter_status" class="sr-only">Trạng thái</label>
                                <select id="filter_status" name="status"
                                    class="h-10 w-full border border-gray-300 rounded px-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 text-center">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang kích
                                        hoạt</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Đã tắt
                                    </option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button type="submit"
                                    class="btn bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 h-10 w-full text-sm">
                                    Lọc
                                </button>

                                <a href="{{ route('admin.bundle-products.index') }}"
                                    class="btn bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 h-10 w-full text-sm text-center flex items-center justify-center">
                                    Xóa lọc
                                </a>
                            </div>
                        </div>
                    </form>



                    <!-- DEALS TABLE -->
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Tên Deal</th>
                                    <th class="text-center">Số SP chính</th>
                                    <th class="text-center">Số SP bán kèm</th>
                                    <th>Trạng thái</th>
                                    <th style="width: 120px;" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bundles as $index => $bundle)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="#"
                                                class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $bundle->name }}</a>
                                            <p class="text-xs text-gray-500">{{ $bundle->display_title }}</p>
                                        </td>
                                        <td class="text-center">{{ $bundle->mainProducts->count() }}</td>
                                        <td class="text-center">{{ $bundle->suggestedProducts->count() }}</td>
                                        <td>
                                            @if ($bundle->status === 'active')
                                                <span class="badge-custom badge-success-custom">Đang kích hoạt</span>
                                            @else
                                                <span class="badge-custom badge-secondary-custom">Đã tắt</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="inline-flex space-x-1">
                                                {{-- Nút xem chi tiết --}}
                                                <a href="{{ route('admin.bundle-products.show', $bundle->id) }}"
                                                    class="btn btn-info btn-sm" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.bundle-products.edit', $bundle->id) }}"
                                                    class="btn btn-primary btn-sm" title="Chỉnh sửa"><i
                                                        class="fas fa-edit"></i></a>
                                                <form action="{{ route('admin.bundle-products.destroy', $bundle->id) }}"
                                                    method="POST" onsubmit="return confirm('Xóa deal này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa"><i
                                                            class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-gray-500 py-4">Không có deal nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PAGINATION -->
                <div class="card-custom-footer">
                    {{ $bundles->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');

            const hideToast = (toastElement) => {
                if (toastElement) {
                    toastElement.classList.add('hide');
                    setTimeout(() => {
                        toastElement.remove();
                    }, 350);
                }
            };

            toasts.forEach(toast => {
                const autoHideTimeout = setTimeout(() => {
                    hideToast(toast);
                }, 5000);

                const closeButton = toast.querySelector('[data-dismiss-target]');
                if (closeButton) {
                    closeButton.addEventListener('click', function() {
                        clearTimeout(autoHideTimeout);
                        const targetId = this.getAttribute('data-dismiss-target');
                        const toastToHide = document.querySelector(targetId);
                        hideToast(toastToHide);
                    });
                }
            });

            window.openModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            }

            window.closeModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            }

            window.addEventListener('click', function(event) {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    if (event.target.closest('.modal-content') === null && event.target.classList
                        .contains('modal')) {
                        closeModal(modal.id);
                    }
                });
            });

            window.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.modal.show').forEach(modal => closeModal(modal.id));
                }
            });
        });
    </script>
@endpush
