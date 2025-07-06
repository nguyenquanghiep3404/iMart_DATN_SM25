@extends('admin.layouts.app')

@include('admin.oderMannager.layouts.css')

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
                                        <button onclick="openModal()"
                                            class="text-indigo-600 hover:underline text-sm font-medium">
                                            <i class="fas fa-edit mr-1"></i>Sửa
                                        </button>
                                        <button class="text-red-600 hover:underline text-sm font-medium">
                                            <i class="fas fa-trash-alt mr-1"></i>Xoá
                                        </button>
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

                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và Tên <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="name" required
                                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Nguyễn Văn A">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span
                                    class="text-red-500">*</span></label>
                            <input type="email" id="email" required
                                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="example@email.com">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                            <input type="password" id="password"
                                class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Để trống nếu không đổi">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="status"
                                class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-2xl">
                    <button type="button" onclick="closeModal()"
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Hủy</button>
                    <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">Lưu thông
                        tin</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
        function openModal() {
            document.getElementById('staff-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('staff-modal').classList.add('hidden');
        }
    </script>
@endsection
