@extends('admin.layouts.app')

@section('content')
<div class="body-content px-8 py-8 bg-gray-100">
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="flex justify-between mb-10 items-end">
                <div class="page-title">
                    <h3 class="text-3xl font-extrabold text-gray-900">Add Category</h3>
                    <ul class="text-sm font-medium flex items-center space-x-3 text-gray-500 mt-2">
                        <li>
                            <a href="" class="text-blue-600 hover:text-blue-500 font-semibold">Home</a>
                        </li>
                        <li><span class="inline-block bg-gray-400 w-[4px] h-[4px] rounded-full"></span></li>
                        <li class="text-gray-700 font-semibold">Add Category</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
            <div class="bg-white rounded-lg shadow p-8">
                <form action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- Upload Image --}}
                        {{-- <div class="col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Upload Image</label>
                            <div class="flex flex-col items-center justify-center border border-dashed border-gray-400 rounded-lg p-6 bg-gray-50">
                                <img src="{{ asset('assets/img/icons/upload.png') }}" class="w-[100px] mb-2" alt="">
                                <span class="text-xs text-gray-500 mb-3">Image size must be less than 5MB</span>
                                <input type="file" name="image" id="productImage" class="hidden">
                                <label for="productImage" class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded text-sm">Upload Image</label>
                            </div>
                        </div> --}}

                        {{-- Form Fields --}}
                        <div class="col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Name</label>
                                <input type="text" name="name" id="name" placeholder="Name" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-bold text-gray-700 mb-1">Slug</label>
                                <input type="text" name="slug" id="slug" placeholder="Slug" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div>
                                <label for="parent_id" class="block text-sm font-bold text-gray-700 mb-1">Parent Category</label>
                                <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="">-- None (Parent) --</option>
                                    {{-- @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach --}}
                                </select>
                            </div>

                            <div>
                                <label for="order" class="block text-sm font-bold text-gray-700 mb-1">Order</label>
                                <input type="number" name="order" id="order" value="0" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label for="description" class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="description" rows="3" placeholder="Description here" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
                            </div>

                            <div class="col-span-2">
                                <label for="meta_title" class="block text-sm font-bold text-gray-700 mb-1">Meta Title</label>
                                <input type="text" name="meta_title" id="meta_title" placeholder="Enter Meta Title" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>

                            <div class="col-span-2">
                                <label for="meta_description" class="block text-sm font-bold text-gray-700 mb-1">Meta Description</label>
                                <textarea name="meta_description" id="meta_description" rows="2" placeholder="Enter Meta Description" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
                            </div>

                            <div class="col-span-2">
                                <label for="meta_keywords" class="block text-sm font-bold text-gray-700 mb-1">Meta Keywords</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" placeholder="Enter Meta Keywords" class="w-full px-3 py-2 border border-gray-400 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-8 text-right">
                        <button type="submit" class="tp-btn px-7 py-2">Add Category</button>
                        <a href="" 
                        class="ml-4 inline-block px-7 py-2 border border-red-500 text-red-500 rounded hover:bg-red-50 font-semibold shadow">
                        Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
