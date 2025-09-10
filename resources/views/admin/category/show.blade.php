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
                <h2 class="text-base font-medium text-gray-800 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Thông tin
                </h2>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">ID :</span>
                            <span class="text-gray-900 ml-2">{{ $category->id }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Tên danh mục :</span>
                            <span class="text-gray-900 ml-2">{{ $category->name }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Slug :</span>
                            <span class="text-gray-900 ml-2">{{ $category->slug }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Danh mục cha :</span>
                            <span class="text-gray-900 ml-2">
                                @if($category->parent)
                                    <a href="{{ route('admin.categories.show', $category->parent) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $category->parent->name }}
                                    </a>
                                @else
                                    <span class="text-gray-500">Không có</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Trạng thái :</span>
                            <span class="ml-2">
                                <span class="px-2 py-1 text-xs {{ $category->status === 'active' ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' }} rounded">
                                    {{ $category->status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                                </span>
                            </span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Danh mục con :</span>
                            <span class="text-gray-900 ml-2">
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
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Số sản phẩm :</span>
                            <span class="text-gray-900 ml-2">{{ $category->products->count() }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Thứ tự hiển thị :</span>
                            <span class="text-gray-900 ml-2">{{ $category->order }}</span>
                        </div>
                    </div>

                    <div class="py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Mô tả :</span>
                            <span class="text-gray-900 ml-2">
                                {{ $category->description ?: 'Chưa có mô tả' }}
                            </span>
                        </div>
                    </div>
                    <h3 class="text-base font-bold text-red-500 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        SEO
                    </h3>

                    <div class="py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Meta Title :</span>
                            <span class="text-gray-900 ml-2">
                                {{ $category->meta_title ?: 'Chưa có meta title' }}
                            </span>
                        </div>
                    </div>

                    <div class="py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Meta Keywords :</span>
                            <span class="text-gray-900 ml-2">
                                {{ $category->meta_keywords ?: 'Chưa có meta keywords' }}
                            </span>
                        </div>
                    </div>

                    <div class="py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Meta Description :</span>
                            <span class="text-gray-900 ml-2">
                                {{ $category->meta_description ?: 'Chưa có meta description' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Ngày tạo :</span>
                            <span class="text-gray-900 ml-2">{{ $category->created_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-gray-600 font-medium whitespace-nowrap">Cập nhật cuối :</span>
                            <span class="text-gray-900 ml-2">{{ $category->updated_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Section -->
            {{-- @if($category->images->count() > 0)
            <div class="border-t border-gray-200">
                <div class="p-6">
                    <h2 class="text-base font-medium text-gray-800 mb-6">Hình ảnh danh mục</h2>
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
                       class="text-base inline-flex items-center px-4 py-2 border border-transparent rounded text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
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
