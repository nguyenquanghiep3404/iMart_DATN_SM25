@extends('admin.layouts.app')

@section('title', 'Thùng rác Người dùng')

@push('styles')
{{--
    Toàn bộ CSS cần thiết để trang hiển thị đúng và nhất quán.
    Bạn có thể chuyển các style này ra file CSS chung nếu muốn.
--}}
<style>
    body {
        background-color: #f8f9fa;
    }
    .btn {
        border-radius: 0.375rem;
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-primary { background-color: #4f46e5; color: white; }
    .btn-primary:hover { background-color: #4338ca; }
    .btn-secondary { background-color: #6c757d; color: white; border: 1px solid #6c757d; }
    .btn-secondary:hover { background-color: #5a6268; border-color: #545b62;}
    .btn-danger { background-color: #dc3545; color: white; }
    .btn-danger:hover { background-color: #c82333; }
    .btn-success { background-color: #28a745; color: white; }
    .btn-success:hover { background-color: #218838; }
    .btn-icon {
        padding: 0.5rem;
        font-size: 0.875rem;
        line-height: 1;
    }
    .admin-main-card {
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .table th {
        background-color: #f9fafb;
        color: #374151;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.75rem 1.5rem;
        text-align: left;
        font-size: 0.75rem;
    }
    .table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
    }
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    .badge-role {
        display: inline-block; padding: 0.25em 0.6em; font-size: 75%; font-weight: 700;
        line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline;
        border-radius: 0.375rem; margin: 0.1rem; color: #fff; background-color: #6c757d;
    }
    .badge-role-admin { background-color: #dc3545; }
    .badge-role-customer { background-color: #6c757d; }
    .badge-role-content_manager { background-color: #ffc107; color: #212529; }
    .badge-role-order_manager { background-color: #17a2b8; }
    .badge-role-shipper { background-color: #28a745; }
</style>
@endpush

@section('content')
<div class="body-content px-4 md:px-6 py-8">
    <div class="container mx-auto max-w-full">

        {{-- Tiêu đề trang và các nút điều hướng --}}
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">Thùng rác Người dùng ({{ $users->total() }})</h1>
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="flex text-xs text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800">Người dùng</a></li>
                        <li class="text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700" aria-current="page">Thùng rác</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary inline-flex items-center mt-4 sm:mt-0">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại Danh sách
            </a>
        </div>

        {{-- Card chứa nội dung chính --}}
        <div class="admin-main-card">
            <div class="overflow-x-auto">
                <table class="table w-full min-w-full">
                    {{-- Phần đầu của bảng --}}
                    <thead>
                        <tr>
                            <th class="w-16">ID</th>
                            <th>Thông tin người dùng</th>
                            <th>Vai trò</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Ngày xóa</th>
                            <th class="w-40 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    {{-- Phần thân của bảng, hiển thị dữ liệu --}}
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="flex items-center">
                                        {{-- Hiển thị avatar bị làm mờ và tên bị gạch ngang để biểu thị đã xóa --}}
                                        <img class="h-10 w-10 rounded-full object-cover mr-3 opacity-50" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                        <div>
                                            <span class="font-semibold text-gray-500 line-through">{{ $user->name }}</span>
                                            <div class="text-xs text-gray-500 line-through">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{-- Hiển thị các vai trò (nếu có) --}}
                                    @forelse($user->roles as $role)
                                        <span class="badge-role badge-role-{{ str_replace(' ', '_', $role->name) }} opacity-75">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-xs text-gray-500">Không có</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-red-600 bg-red-200">
                                        Đã xóa
                                    </span>
                                </td>
                                <td class="text-xs">
                                    {{-- Hiển thị ngày và giờ xóa --}}
                                    {{ $user->deleted_at->format('d/m/Y') }}
                                    <div class="text-gray-400">{{ $user->deleted_at->format('H:i') }}</div>
                                </td>
                                <td class="text-center">
                                    {{-- Các nút hành động: Khôi phục và Xóa vĩnh viễn --}}
                                    <div class="flex items-center justify-center space-x-2">

                                        {{-- Nút Khôi phục --}}
                                        <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH') {{-- Phương thức PATCH cho việc khôi phục --}}
                                            <button type="submit" class="btn btn-icon btn-success" title="Khôi phục người dùng">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>

                                        {{-- Nút Xóa vĩnh viễn --}}
                                        <form action="{{ route('admin.users.forceDelete', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('HÀNH ĐỘNG NÀY SẼ XÓA VĨNH VIỄN NGƯỜI DÙNG VÀ KHÔNG THỂ HOÀN TÁC! Bạn có chắc chắn?');">
                                            @csrf
                                            @method('DELETE') {{-- Phương thức DELETE cho việc xóa vĩnh viễn --}}
                                            <button type="submit" class="btn btn-icon btn-danger" title="Xóa vĩnh viễn">
                                                <i class="fas fa-fire"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- Thông báo khi không có user nào trong thùng rác --}}
                            <tr>
                                <td colspan="6" class="text-center py-16 text-gray-500">
                                    <i class="fas fa-trash fa-3x mb-2"></i>
                                    <p>Thùng rác trống.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

             {{-- PAGINATION --}}
             @if ($users->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200">
                     {!! $users->links() !!}
                </div>
             @endif
        </div>
    </div>
</div>
@endsection
