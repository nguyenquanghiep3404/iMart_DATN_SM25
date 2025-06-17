@extends('admin.layouts.app')
@section('title', 'Quản lý Vai trò')
@push('styles')
    {{-- Copy style từ trang users.index nếu cần --}}
@endpush

@section('content')
<div class="body-content px-4 md:px-6 py-8">
    <div class="container mx-auto max-w-full">
        {{-- PAGE HEADER --}}
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">Quản lý Vai trò</h1>
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="flex text-xs text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700" aria-current="page">Vai trò</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary inline-flex items-center mt-4 sm:mt-0">
                <i class="fas fa-plus-circle mr-2"></i> Thêm vai trò mới
            </a>
        </div>

        {{-- MAIN CONTENT CARD --}}
        <div class="admin-main-card">
            <div class="overflow-x-auto">
                <table class="table w-full min-w-full">
                    <thead>
                        <tr>
                            <th class="w-16">ID</th>
                            <th>Tên Vai trò</th>
                            <th>Mô tả</th>
                            <th class="text-center">Số người dùng</th>
                            <th class="w-32 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td class="font-semibold">{{ $role->name }}</td>
                                <td>{{ $role->description }}</td>
                                <td class="text-center">{{ $role->users_count }}</td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-icon btn-primary" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vai trò này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-500">Chưa có vai trò nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
