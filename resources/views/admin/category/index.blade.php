@extends('admin.layouts.app')

@section('content')
<div class="body-content px-4 md:px-8 py-8 bg-slate-100 min-h-screen">
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="flex flex-col md:flex-row justify-between mb-6 md:mb-10 items-start md:items-end gap-4">
                <div class="page-title">
                    <h3 class="mb-2 md:mb-0 text-2xl md:text-[28px] font-semibold text-gray-800">Categories</h3>
                    <ul class="text-sm font-medium flex items-center space-x-3 text-gray-500">
                        <li class="breadcrumb-item">
                            <a href="" class="text-blue-500 hover:text-blue-600 transition">Home</a>
                        </li>
                        <li class="breadcrumb-item flex items-center">
                            <span class="inline-block bg-gray-400 w-1 h-1 rounded-full mx-2"></span>
                        </li>
                        <li class="breadcrumb-item text-gray-500">Category List</li>
                    </ul>
                </div>
                <a href="{{ route('admin.categories.create') }}" class="tp-btn px-7 py-2">
                    Add New Category
                </a>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
            <div class="relative bg-white rounded-lg shadow-md w-full max-w-full p-4 md:p-6">
                @if (session('success'))
                    <div id="alert" class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 relative">
                        {{ session('success') }}
                        <div class="progress-bar opacity-50"></div>
                    </div>
                @endif

                @if (session('error'))
                    <div id="alert" class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 relative">
                        {{ session('error') }}
                        <div class="progress-bar opacity-50"></div>
                    </div>
                @endif

                <div class="w-full overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase font-semibold text-gray-700">
                                <th scope="col" class="py-3 px-4 w-[3%]">
                                    <div class="tp-checkbox">
                                        <input id="selectAllProduct" type="checkbox" class="cursor-pointer">
                                        <label for="selectAllProduct"></label>
                                    </div>
                                </th>
                                <th scope="col" class="py-3 px-4">ID</th>
                                <th scope="col" class="py-3 px-4">Name</th>
                                <th scope="col" class="py-3 px-4 text-right">Slug</th>
                                <th scope="col" class="py-3 px-4 text-right">Parent</th>
                                <th scope="col" class="py-3 px-4 text-right">Description</th>
                                <th scope="col" class="py-3 px-4 text-right">Order</th>
                                <th scope="col" class="py-3 px-4 text-right">Status</th>
                                <th scope="col" class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr class="bg-white border-b border-gray-200 last:border-0 hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="tp-checkbox">
                                            <input id="product-{{ $category->id }}" type="checkbox" class="cursor-pointer">
                                            <label for="product-{{ $category->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-gray-700">{{ $category->id }}</td>
                                    <td class="py-3 px-4">
                                        <a href="" class="flex items-center space-x-3 group">
                                            <img class="w-8 h-8 rounded-full object-cover" src="{{ $category->getImageUrlAttribute() }}" alt="{{ $category->name }}">
                                            <span class="font-medium text-gray-800 group-hover:text-blue-600 transition">{{ $category->name }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-600">{{ $category->slug }}</td>
                                    <td class="py-3 px-4 text-right text-gray-600">{{ $category->parent?->name ?? 'None' }}</td>
                                    <td class="py-3 px-4 text-right text-gray-600">{{ Str::limit($category->description ?? '', 30, '...') }}</td>
                                    <td class="py-3 px-4 text-right text-gray-600">{{ $category->order ?? 0 }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $category->status === 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                            {{ ucfirst($category->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('admin.categories.show', $category->id) }}"
                                                class="inline-flex items-center justify-center w-10 h-10 text-blue-500 bg-blue-50 rounded-lg hover:bg-blue-100 transition-all"
                                                title="Show">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                class="inline-flex items-center justify-center w-10 h-10 text-green-500 bg-green-50 rounded-lg hover:bg-green-100 transition-all"
                                                title="Edit">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" 
                                                method="POST" 
                                                class="inline-block"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này ?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="current_page" value="{{ $categories->currentPage() }}">
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center w-10 h-10 text-red-500 bg-red-50 rounded-lg hover:bg-red-100 transition-all"
                                                    title="Delete">
                                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-6">
                                        <div class="flex flex-col items-center justify-center">
                                            <img src="{{ asset('assets/img/empty.svg') }}" alt="Empty" class="w-32 h-32 mb-4">
                                            <h5 class="text-lg font-medium text-gray-500 mb-2">No Categories Found</h5>
                                            <p class="text-sm text-gray-400">Create your first category by clicking the Add Category button.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-6 px-4">
                    <div class="flex items-center gap-2">
                        @if($categories->hasPages())
                            @if($categories->onFirstPage())
                                <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded cursor-not-allowed">Previous</span>
                            @else
                                <a href="{{ $categories->previousPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">Previous</a>
                            @endif

                            @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                                @if ($page == $categories->currentPage())
                                    <span class="px-3 py-1 text-white bg-blue-600 rounded">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($categories->hasMorePages())
                                <a href="{{ $categories->nextPageUrl() }}" class="px-3 py-1 bg-white border rounded hover:bg-gray-50">Next</a>
                            @else
                                <span class="px-3 py-1 text-gray-400 bg-gray-100 rounded cursor-not-allowed">Next</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection