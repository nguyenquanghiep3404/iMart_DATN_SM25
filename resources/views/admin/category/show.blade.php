@extends('admin.layouts.app')

@section('title', 'Chi tiết danh mục ' . $category->name)

@section('content')
<div class="min-h-screen bg-gray-100 pb-8">
    <!-- Header -->
    <div class="bg-white border-b">
        <div class="px-8 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <h1 class="text-base font-medium text-gray-800">Chi tiết danh mục : {{ $category->name }}</h1>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="text-base tp-btn">Quay lại</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-8 mt-6">
        <div class="bg-white rounded-lg shadow-sm">
            <!-- Basic Information -->
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-6">Thông tin</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 font-medium">ID :</span>
                        <span class="flex-1 text-gray-900">{{ $category->id }}</span>
                        <span class="w-32 text-gray-600 mr-3 font-medium">Tên danh mục :</span>
                        <span class="flex-1 text-gray-900">{{ $category->name }}</span>
                    </div>

                    <div class="flex items-center py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 mr-3 font-medium">Slug :</span>
                        <span class="flex-1 text-gray-900">{{ $category->slug }}</span>
                        <span class="w-32 text-gray-600 mr-3 font-medium">Danh mục cha :</span>
                        <span class="flex-1 text-gray-900">
                            @if($category->parent)
                                <a href="{{ route('admin.categories.show', $category->parent) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $category->parent->name }}
                                </a>
                            @else
                                <span class="text-gray-500">Không có</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 mr-3 font-medium">Trạng thái :</span>
                        <span class="flex-1">
                            <span class="px-2 py-1 text-xs {{ $category->status ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' }} rounded">
                                {{ $category->status ? 'Hoạt động' : 'Không hoạt động' }}
                            </span>
                        </span>
                        <span class="w-32 text-gray-600 mr-3 font-medium">Danh mục con :</span>
                        <span class="flex-1 text-gray-900">
                            @if($category->children->count() > 0)
                                @foreach($category->children as $child)
                                    <a href="{{ route('admin.categories.show', $child) }}" 
                                       class="inline-block text-blue-600 hover:text-blue-800 mr-3">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            @else
                                <span class="text-gray-500">Không có danh mục con</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 mr-3 font-medium">Số sản phẩm :</span>
                        <span class="flex-1 text-gray-900">{{ $category->products->count() }}</span>
                        <span class="w-32 text-gray-600 mr-3 font-medium">Thứ tự hiển thị :</span>
                        <span class="flex-1 text-gray-900">{{ $category->order }}</span>
                    </div>

                    <div class="flex py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 mr-3 font-medium">Mô tả :</span>
                        <span class="flex-1 text-gray-900">
                            {{ $category->description ?: 'Chưa có mô tả' }}
                        </span>
                    </div>

                    <div class="flex py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 mr-3 font-medium">Meta Title :</span>
                        <span class="flex-1 text-gray-900">
                            {{ $category->meta_title ?: 'Chưa có meta title' }}
                        </span>
                    </div>

                    <div class="flex py-3 border-b border-gray-100">
                        <span class="w-35 text-gray-600 mr-3 font-medium">Meta Keywords :</span>
                        <span class="flex-1 text-gray-900">
                            {{ $category->meta_keywords ?: 'Chưa có meta keywords' }}
                        </span>
                    </div>

                    <div class="flex py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 mr-3 font-medium">Meta Description :</span>
                        <span class="flex-1 text-gray-900">
                            {{ $category->meta_description ?: 'Chưa có meta description' }}
                        </span>
                    </div>

                    <div class="flex py-3 border-b border-gray-100">
                        <span class="w-32 text-gray-600 mr-3 font-medium">Ngày tạo :</span>
                        <span class="flex-1 text-gray-900">{{ $category->created_at->format('d/m/Y H:i:s') }}</span>
                        <span class="w-32 text-gray-600 mr-3 font-medium">Cập nhật cuối :</span>
                        <span class="flex-1 text-gray-900">{{ $category->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <!-- Image Section -->
            {{-- @if($category->images->count() > 0)
            <div class="border-t border-gray-200">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-6">Hình ảnh danh mục</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($category->images as $image)
                        <div class="relative aspect-square">
                            <img src="{{ asset('storage/' . $image->path) }}" 
                                 alt="{{ $category->name }}" 
                                 class="w-full h-full object-cover rounded-lg">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif --}}

            <!-- Action Buttons -->
            <div class="border-t border-gray-200">
                <div class="p-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.categories.edit', $category) }}" 
                       class="text-baseinline-flex items-center px-4 py-2 border border-transparent rounded text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Chỉnh sửa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
