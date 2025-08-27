@extends('admin.layouts.app')

@section('title', 'Danh sách Flash Sale')

@push('styles')
    <style>
        .card-custom {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
        }

        .card-custom-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }

        .card-custom-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .card-custom-body {
            padding: 1.5rem;
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
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn-info {
            background-color: #3b82f6;
            color: white;
        }

        .btn-info:hover {
            background-color: #2563eb;
        }



        .table-custom {
            width: 100%;
            color: #374151;
        }

        .table-custom th,
        .table-custom td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
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
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .03);
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
    <div class="p-4 sm:p-6 lg:p-8">
        @include('admin.partials.flash_message')
        <div id="campaign-list-view">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Quản lý Flash Sale</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Flash Sale</li>
                    </ol>
                </nav>
            </div>

            <div class="card-custom">
                <div class="card-custom-header flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <h3 class="card-custom-title mb-2 sm:mb-0">Danh sách chiến dịch</h3>
                    <a href="{{ route('admin.flash-sales.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Tạo chiến dịch mới
                    </a>
                </div>
                <div class="card-custom-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table-custom table-striped">
                            <thead>
                                <tr>
                                    <th class="text-left text-base font-medium" style="width: 20%;">Tên chiến dịch</th>
                                    <th class="text-left text-base font-medium" style="width: 20%;">Thời gian bắt đầu & kết
                                        thúc</th>
                                    <th class="text-left text-base font-medium" style="width: 20%;">Khung giờ ưu đãi</th>
                                    <th class="text-left text-base font-medium" style="width: 10%;">Số sản phẩm</th>
                                    <th class="text-left text-base font-medium" style="width: 15%;">Trạng thái</th>
                                    <th class="text-left text-base font-medium" style="width: 15%;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($flashSales as $flashSale)
                                    <tr>
                                        <td class="text-left text-base font-medium">{{ $flashSale->name }}</td>
                                        <td class="text-left text-base font-medium">
                                            <div class="text-green-600">
                                                {{ \Carbon\Carbon::parse($flashSale->start_time)->format('d/m/Y H:i:s') }}
                                            </div>
                                            <div class="text-red-600">
                                                {{ \Carbon\Carbon::parse($flashSale->end_time)->format('d/m/Y H:i:s') }}
                                            </div>
                                        </td>
                                        <td class="text-left text-base font-medium">
                                            @if ($flashSale->flashSaleTimeSlots->isNotEmpty())
                                                @foreach ($flashSale->flashSaleTimeSlots as $slot)
                                                    <div>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                                        {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</div>
                                                @endforeach
                                            @else
                                                Toàn thời gian chiến dịch
                                            @endif
                                        </td>
                                        <td class="text-left text-base font-medium">
                                            {{ $flashSale->flashSaleProducts->count() }}</td>
                                        <td class="text-left text-base font-medium">
                                            @php
                                                $now = now();
                                                if ($flashSale->status === 'inactive') {
                                                    $status = 'Tạm dừng';
                                                    $bgClass = 'bg-gray-500';
                                                } elseif ($flashSale->start_time > $now) {
                                                    $status = 'Đã lên lịch';
                                                    $bgClass = 'bg-blue-500';
                                                } elseif ($flashSale->end_time < $now) {
                                                    $status = 'Đã kết thúc';
                                                    $bgClass = 'bg-gray-500';
                                                } else {
                                                    $status = 'Đang diễn ra';
                                                    $bgClass = 'bg-green-500';
                                                }
                                            @endphp
                                            <span
                                                class="px-3 py-1 rounded text-white {{ $bgClass }}">{{ $status }}</span>
                                        </td>
                                        <td class="text-left text-base font-medium">
                                            <div class="inline-flex space-x-1">
                                                {{-- Nút "Thống kê" chỉ hiển thị khi flash sale đã kết thúc --}}
                                                {{-- Nút "Thống kê" chỉ hiển thị khi flash sale đã kết thúc --}}
                                                @if ($flashSale->status === 'finished')
                                                    <a href="{{ route('admin.flash-sales.statistics', $flashSale->id) }}"
                                                        class="btn btn-sm btn-info" title="Thống kê">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                @endif
                                                {{-- Nút "Chi tiết" --}}
                                                @unless ($flashSale->status === 'finished')
                                                    <a href="{{ route('admin.flash-sales.show', $flashSale->id) }}"
                                                        class="btn btn-sm btn-primary" title="Chi tiết"><i
                                                            class="fas fa-list"></i></a>
                                                @endunless

                                                {{-- Ẩn nút "Sửa" nếu trạng thái là "Đã kết thúc" --}}
                                                @unless ($flashSale->status === 'finished')
                                                    <a href="{{ route('admin.flash-sales.edit', $flashSale->id) }}"
                                                        class="btn btn-sm btn-warning" title="Sửa"><i
                                                            class="fas fa-edit"></i></a>
                                                @endunless
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Không có chiến dịch nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



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
@endsection
