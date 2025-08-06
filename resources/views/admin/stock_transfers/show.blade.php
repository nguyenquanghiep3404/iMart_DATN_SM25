@extends('admin.layouts.app')

@section('title', 'Chi tiết Phiếu Chuyển Kho - ' . $stockTransfer->transfer_code)

@push('styles')
    {{-- Thêm các thư viện cần thiết --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        .card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.5rem 1rem;
        }
        .info-grid dt { color: #6b7280; }
        .info-grid dd { color: #111827; font-weight: 500; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25em 0.6em;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            border-radius: 0.375rem;
        }
        /* Status Styles */
        .status-pending { background-color: #FEF9C3; color: #713F12; } /* yellow-200, amber-800 */
        .status-shipped { background-color: #DBEAFE; color: #1E40AF; } /* blue-200, blue-800 */
        .status-received { background-color: #D1FAE5; color: #065F46; } /* green-200, green-800 */
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; } /* red-200, red-800 */

    </style>
@endpush

@section('content')
<div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    <header class="mb-8">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <a href="{{ route('admin.stock-transfers.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Quay lại danh sách
            </a>
        </div>
        <!-- Header Title & Actions -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    Chi tiết Phiếu Chuyển
                    <span class="ml-3 font-mono text-2xl text-indigo-600 bg-indigo-100 px-3 py-1 rounded-lg">{{ $stockTransfer->transfer_code }}</span>
                </h1>
            </div>
            <!-- Action Buttons -->
            <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
                <button onclick="window.print()" class="flex items-center bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-sm border border-gray-300 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-print mr-2 text-gray-500"></i>
                    In Phiếu
                </button>

                @if($stockTransfer->status == 'pending')
                    <a href="{{ route('admin.stock-transfers.edit', $stockTransfer->id) }}" class="flex items-center bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-sm border border-gray-300 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-pencil-alt mr-2 text-gray-500"></i>
                        Sửa Phiếu
                    </a>
                    <a href="{{ route('admin.stock-transfers.dispatch.show', $stockTransfer->id) }}" class="flex items-center bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-box-open mr-2"></i>
                        Xuất Kho
                    </a>
                @endif
                
                @if($stockTransfer->status == 'shipped')
    <a href="{{ route('admin.stock-transfers.receive.show', $stockTransfer->id) }}" class="flex items-center bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-green-700 transition-colors">
        <i class="fas fa-dolly-flatbed mr-2"></i>
        Nhận Kho
    </a>
@endif
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Items List -->
        <div class="lg:col-span-2">
            @include('admin.stock_transfers.partials._show_items', ['stockTransfer' => $stockTransfer])
        </div>

        <!-- Right Column: Details -->
        <div class="lg:col-span-1 space-y-8">
            @include('admin.stock_transfers.partials._show_details', ['stockTransfer' => $stockTransfer])
        </div>
    </div>
</div>
@endsection
