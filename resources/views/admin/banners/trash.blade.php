@extends('admin.layouts.app')

@section('title')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">🗑 Thùng rác Banner</h1>
            <a href="{{ route('admin.banners.index') }}" class="text-indigo-600 hover:underline">← Quay lại danh sách</a>
        </div>

        @if(session('success'))
        <div class="mb-4 text-green-600 font-semibold">
            {{ session('success') }}
        </div>
        @endif

        <div class="overflow-x-auto bg-white rounded-xl shadow-sm">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-4">Tiêu đề</th>
                        <th class="px-6 py-4">Đã xoá bởi</th>
                        <th class="px-6 py-4">Thời điểm xoá</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold">{{ $banner->title }}</td>
                        <td class="px-6 py-4">{{ $banner->deletedBy?->name ?? 'Không xác định' }}</td>
                        <td class="px-6 py-4">{{ $banner->deleted_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <form method="POST" action="{{ route('admin.banners.restore', $banner->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:underline">Khôi phục</button>
                            </form>
                            <form method="POST" action="{{ route('admin.banners.forceDelete', $banner->id) }}" class="inline" onsubmit="return confirm('Xoá vĩnh viễn banner này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xoá vĩnh viễn</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center p-6 text-gray-500">Không có banner nào trong thùng rác.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6 px-4 pb-6">
                {{ $banners->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
