@extends('admin.layouts.app')
@include('admin.oderMannager.layouts.css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@section('content')
    <div class="p-4 md:p-8 bg-gray-100 min-h-screen">
        <div class="max-w-screen-2xl mx-auto space-y-6">
            {{-- Header --}}
            @include('admin.oderMannager.layouts.header')

            {{-- Danh sách nhân viên --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                {{-- Bộ lọc --}}
                @include('admin.oderMannager.layouts.filter')

                {{-- Bảng danh sách nhân viên --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-800">
                        <thead class="bg-gray-50">
                            <tr class="text-left font-semibold text-gray-600">
                                <th class="px-6 py-3">STT</th>
                                <th class="px-6 py-3">Họ tên</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">SĐT</th>
                                <th class="px-6 py-3">Trạng thái</th>
                                <th class="px-6 py-3">Ngày tạo</th>
                                <th class="px-6 py-3 text-right">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">{{ $loop->iteration }}</td> <!-- Số thứ tự -->
                                    <td class="px-6 py-4 font-medium flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-semibold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $user->email }}</td>
                                    <td class="px-6 py-4">{{ $user->phone_number ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        @if ($user->status === 'active')
                                            <span
                                                class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                                Đang hoạt động
                                            </span>
                                        @else
                                            <span
                                                class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                                Không hoạt động
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.order-manager.edit', $user->id) }}"
                                            class="text-indigo-600 hover:underline text-sm font-medium">
                                            <i class="fas fa-edit mr-1"></i>Sửa
                                        </a>

                                        <form action="" method="POST" class="inline"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này không?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:underline text-sm font-medium bg-transparent border-0 p-0">
                                                <i class="fas fa-trash-alt mr-1"></i>Xoá
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.order-manager.show', $user->id) }}"
                                            class="text-gray-600 hover:underline text-sm font-medium mr-3"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye mr-1"></i>Xem
                                        </a>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center px-6 py-6 text-gray-500">Không có nhân viên nào
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        {{-- Modal thêm/sửa nhân viên --}}
        <div id="staff-modal"
            class="modal hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
            <form id="staff-form" class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-2xl">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 id="modal-title" class="text-xl font-bold text-gray-800">Thêm nhân viên mới</h2>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>
                @include('admin.oderMannager.layouts.create')
            </form>
        </div>
    </div>
    {{ $users->links() }}
    {{-- Scripts --}}
    @include('admin.oderMannager.layouts.script')
@endsection
