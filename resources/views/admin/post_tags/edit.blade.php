@extends('admin.layouts.app')

@section('title', 'Sửa thẻ bài viết')

@push('styles')
    <style>
        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn {
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
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
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .form-input {
            border-radius: 0.5rem;
            border-color: #d1d5db;
            transition: all 0.2s ease-in-out;
        }

        .form-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            outline: none;
        }
    </style>
@endpush

@section('content')
    <div class="body-content px-6 md:px-8 py-8">
        @include('admin.partials.flash_message')

        <div class="container mx-auto max-w-screen-2xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Sửa thẻ bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.post-tags.index') }}" class="text-indigo-600 hover:text-indigo-800">Thẻ bài viết</a></li>
                        <li class="breadcrumb-item text-gray-400 mx-2">/</li>
                        <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Sửa thẻ</li>
                    </ol>
                </nav>
            </div>

            <div class="card bg-white">
                <div class="p-5">
                    <form action="{{ route('admin.post-tags.update', $postTag) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tên thẻ <span class="text-red-600">*</span></label>
                            <input type="text" name="name" class="form-input w-full" value="{{ old('name', $postTag->name) }}">
                            @error('name')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Slug <span class="text-red-600">*</span></label>
                            <input type="text" name="slug" class="form-input w-full" value="{{ old('slug', $postTag->slug) }}">
                            @error('slug')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="submit" class="btn btn-success py-2 px-4 text-sm">Cập nhật</button>
                            <a href="{{ route('admin.post-tags.index') }}" class="btn btn-secondary py-2 px-4 text-sm">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('name').addEventListener('input', function () {
        const name = this.value;
        const slug = name.toLowerCase()
                         .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // bỏ dấu
                         .replace(/[^a-z0-9\s-]/g, '')                      // loại ký tự đặc biệt
                         .trim().replace(/\s+/g, '-')                       // thay space bằng "-"
        document.getElementById('slug').value = slug;
    });
</script>
@endpush