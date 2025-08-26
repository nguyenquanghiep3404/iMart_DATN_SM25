@extends('admin.layouts.app')

@section('title', 'Quản lý Điểm thưởng')

@section('content')
<div class="container mx-auto px-4 sm:px-8 py-8">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800 mb-2 sm:mb-0">Lịch sử Giao dịch Điểm thưởng</h1>
        <button x-data="{}" x-on:click.prevent="window.dispatchEvent(new Event('open-adjust-modal'))" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H5a1 1 0 110-2h4V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Điều chỉnh điểm thủ công
        </button>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.loyalty.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1">
                <input type="text" name="search" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Tìm theo tên hoặc email người dùng..." value="{{ request('search') }}">
            </div>
            <div class="md:col-span-1">
                <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Tất cả loại giao dịch</option>
                    <option value="earn" @selected(request('type') == 'earn')>Tích điểm</option>
                    <option value="spend" @selected(request('type') == 'spend')>Sử dụng</option>
                    <option value="refund" @selected(request('type') == 'refund')>Hoàn điểm</option>
                    <option value="manual_adjustment" @selected(request('type') == 'manual_adjustment')>Điều chỉnh thủ công</option>
                    <option value="expire" @selected(request('type') == 'expire')>Hết hạn</option>
                </select>
            </div>
            <div class="md:col-span-1">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Lọc</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="border-b-2 border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="px-5 py-3">Ngày</th>
                        <th class="px-5 py-3">Người dùng</th>
                        <th class="px-5 py-3">Loại Giao dịch</th>
                        <th class="px-5 py-3 text-right">Điểm thay đổi</th>
                        <th class="px-5 py-3">Mô tả</th>
                        <th class="px-5 py-3 text-right">Số dư hiện tại</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr class="border-b border-slate-200 hover:bg-slate-50">
                        <td class="px-5 py-4 text-sm">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4 text-sm">
                            @if($log->user)
                                <p class="text-slate-900 whitespace-no-wrap">{{ $log->user->name }}</p>
                                <p class="text-slate-600 whitespace-no-wrap">{{ $log->user->email }}</p>
                            @else
                                <span class="text-red-500">Người dùng không tồn tại</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm">
                            <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                @if($log->type == 'earn') bg-green-100 text-green-700
                                @elseif($log->type == 'spend') bg-red-100 text-red-700
                                @elseif($log->type == 'refund') bg-blue-100 text-blue-700
                                @else bg-slate-200 text-slate-700 @endif">
                               {{ $log->vietnamese_type }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm text-right font-bold {{ $log->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $log->points > 0 ? '+' : '' }}{{ number_format($log->points) }}
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-700">{{ $log->description }}</td>
                        <td class="px-5 py-4 text-sm text-right font-semibold text-slate-800">{{ number_format($log->user->loyalty_points_balance ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-500">Không có giao dịch nào được tìm thấy.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<div x-data="{ open: false }" @open-adjust-modal.window="open = true" @keydown.escape.window="open = false" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.loyalty.adjust') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-gift text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Điều chỉnh điểm thưởng
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="user_id" class="block text-sm font-medium text-gray-700">Chọn người dùng</label>
                                    <select id="user_id" name="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" required>
                                        <option value="" disabled selected>-- Chọn một người dùng --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="points" class="block text-sm font-medium text-gray-700">Số điểm thay đổi</label>
                                    <input type="number" name="points" id="points" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Nhập 500 để cộng, -100 để trừ" required>
                                </div>
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">Lý do điều chỉnh</label>
                                    <textarea id="reason" name="reason" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Ví dụ: Thưởng khách hàng thân thiết..." required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Lưu thay đổi
                    </button>
                    <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
