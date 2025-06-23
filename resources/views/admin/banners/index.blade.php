@extends('admin.layouts.app')

@section('title', 'Danh sách Banner')

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Danh sách Banner</h2>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">+ Thêm Banner</a>
        </div>

        @if(session('success'))
        <div class="mb-4 text-green-600 font-semibold">
            {{ session('success') }}
        </div>
        @endif

        <div class="overflow-x-auto bg-white rounded shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Tiêu đề</th>
                        <th class="px-4 py-2 text-sm font-semibold text-center">Ảnh desktop</th>
                        <th class="px-4 py-2 text-sm font-semibold text-center">Ảnh mobile</th>
                        <th class="px-4 py-2 text-sm font-semibold text-center">Vị trí</th>
                        <th class="px-4 py-2 text-sm font-semibold text-center">Thứ tự</th>
                        <th class="px-4 py-2 text-sm font-semibold text-center">Trạng thái</th>
                        <th class="px-4 py-2 text-sm font-semibold text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($banners as $banner)
                    <tr>
                        <td class="px-4 py-2">{{ $banner->title }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($banner->desktopImage)
                            <img src="{{ Storage::url($banner->desktopImage->path) }}" class="w-16 h-auto rounded border">
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($banner->mobileImage)
                            <img src="{{ Storage::url($banner->mobileImage->path) }}" class="w-16 h-auto rounded border">
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center">{{ $banner->position }}</td>
                        <td class="px-4 py-2 text-center">{{ $banner->order }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded text-white {{ $banner->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}">
                                {{ $banner->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-secondary">Sửa</a>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bạn có chắc muốn xóa?')">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $banners->links() }}
        </div>
    </div>
</div>
@endsection