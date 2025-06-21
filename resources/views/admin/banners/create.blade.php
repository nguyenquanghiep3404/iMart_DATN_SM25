@extends('admin.layouts.app')

@section('title', 'Thêm Banner')

@section('content')
<div class="max-w-3xl mx-auto py-6">
    <h2 class="text-2xl font-semibold mb-6">Thêm Banner mới</h2>

    @if ($errors->any())
    <div class="mb-4 text-red-600">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
        @csrf

        <div class="mb-4">
            <label class="block font-medium mb-1">Tiêu đề <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Liên kết (URL)</label>
            <input type="url" name="link_url" value="{{ old('link_url') }}" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Ảnh desktop</label>
            <input type="file" name="image_desktop" class="form-input w-full">
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Ảnh mobile</label>
            <input type="file" name="image_mobile" class="form-input w-full">
        </div>
        <div class="mb-4">
            <label for="status" class="block font-medium">Trạng thái</label>
            <select name="status" id="status" class="form-select mt-1 block w-full">
                <option value="active" {{ old('status', $banner->status ?? '') == 'active' ? 'selected' : '' }}>Hiển thị</option>
                <option value="inactive" {{ old('status', $banner->status ?? '') == 'inactive' ? 'selected' : '' }}>Ẩn</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Tạo banner</button>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary ml-2">Hủy</a>
    </form>
</div>
@endsection