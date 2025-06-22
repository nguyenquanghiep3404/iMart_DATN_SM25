@extends('admin.layouts.app')
@section('title', 'Thêm Vai trò mới')
@push('styles')
    {{-- Copy style từ trang users.create nếu cần --}}
@endpush

@section('content')
<div class="body-content px-4 md:px-8 py-8">
    <div class="container mx-auto max-w-4xl">
        {{-- Breadcrumbs --}}
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Thêm Vai trò mới</h1>
            {{-- Thêm breadcrumbs nếu cần --}}
        </div>

        <div class="card-form">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="p-6 space-y-6">
                    <div>
                        <label for="role_name" class="form-label">Tên Vai trò (viết liền, không dấu) <span class="text-red-500">*</span></label>
                        <input type="text" id="role_name" name="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="role_description" class="form-label">Mô tả</label>
                        <input type="text" id="role_description" name="description" class="form-input @error('description') is-invalid @enderror" value="{{ old('description') }}">
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <hr class="my-6 border-gray-300">
                        <div>
                            <label class="form-label font-semibold text-lg">Gán Quyền cho Vai trò</label>
                            <p class="text-sm text-gray-600 mb-4">Chọn các quyền mà vai trò này được phép thực hiện.</p>
                            <div class="space-y-4">
                                {{-- Lặp qua từng nhóm quyền (users, roles, products...) --}}
                                @foreach ($permissions as $groupName => $groupPermissions)
                                    <div class="p-4 border border-gray-200 rounded-lg">
                                        <h4 class="font-semibold capitalize text-gray-800 mb-3">{{ str_replace('_', ' ', $groupName) }}</h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                            {{-- Lặp qua từng quyền trong nhóm --}}
                                            @foreach ($groupPermissions as $permission)
                                                <div class="flex items-center">
                                                    <input type="checkbox"
                                                        id="permission_{{ $permission->id }}"
                                                        name="permissions[]"
                                                        value="{{ $permission->id }}"
                                                        class="form-checkbox h-5 w-5 text-indigo-600">
                                                    <label for="permission_{{ $permission->id }}" class="ml-2 text-sm text-gray-700">{{ $permission->description }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <div class="text-red-500 text-sm mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary">Lưu Vai trò</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
