@extends('admin.layouts.app')

@section('title', 'Thêm bài viết')

@push('styles')
    <style>
        .ck-editor__editable_inline {
            min-height: 200px;
            max-height: 800px;
        }

        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .btn {
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
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

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .form-input {
            border-radius: 0.375rem;
            border-color: #d1d5db;
            transition: all 0.2s ease-in-out;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .form-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            outline: none;
        }

        .form-textarea {
            min-height: 6rem;
        }

        .select2-container .select2-selection--multiple {
            border-radius: 0.375rem;
            border-color: #d1d5db;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .select2-container--focus .select2-selection--multiple {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            outline: none;
        }

        @media (max-width: 768px) {
            .btn {
                width: 100%;
            }

            .flex.space-x-3 {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="body-content px-6 md:px-8 py-8">
        <div class="container mx-auto max-w-screen-2xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Thêm bài viết</h1>
                <nav aria-label="breadcrumb" class="mt-2">
                    <ol class="flex text-sm text-gray-500">
                        <li><a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800">Bảng điều
                                khiển</a></li>
                        <li class="mx-1">/</li>
                        <li><a href="{{ route('admin.posts.index') }}" class="text-indigo-600 hover:text-indigo-800">Bài
                                viết</a></li>
                        <li class="mx-1">/</li>
                        <li class="text-gray-700 font-medium">Thêm bài viết</li>
                    </ol>
                </nav>
            </div>

            <div class="card bg-white">
                <div class="p-6">
                    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data"
                        class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @csrf

                        {{-- Cột trái --}}
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tiêu đề <span
                                        class="text-red-600">*</span></label>
                                <input type="text" name="title" id="title" class="form-input w-full"
                                    value="{{ old('title') }}">
                                @error('title')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Slug <span
                                        class="text-red-600">*</span></label>
                                <input type="text" name="slug" id="slug" class="form-input w-full"
                                    value="{{ old('slug') }}">
                                @error('slug')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Meta title</label>
                                <input type="text" name="meta_title" class="form-input w-full"
                                    value="{{ old('meta_title') }}">
                                @error('meta_title')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Meta description</label>
                                <textarea name="meta_description" class="form-input form-textarea w-full">{{ old('meta_description') }}</textarea>
                                @error('meta_description')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Meta keywords</label>
                                <input type="text" name="meta_keywords" class="form-input w-full"
                                    value="{{ old('meta_keywords') }}">
                                @error('meta_keywords')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Cột phải --}}
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Danh mục</label>
                                <select name="post_category_id" class="form-input w-full">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('post_category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('post_category_id')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Thẻ (Tags)</label>
                                <select name="tags[]" class="form-input w-full" multiple>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}"
                                            {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                            {{ $tag->name }}</option>
                                    @endforeach
                                </select>
                                @error('tags')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Hình ảnh bài viết</label>
                                <input type="file" name="post_cover_image" class="form-input w-full"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                @error('post_cover_image')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tóm tắt</label>
                                <textarea name="excerpt" class="form-input form-textarea w-full">{{ old('excerpt') }}</textarea>
                                @error('excerpt')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nội dung <span
                                        class="text-red-600">*</span></label>
                                <textarea name="content" id="editor" class="form-input form-textarea w-full h-48">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror

                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Trạng thái <span
                                            class="text-red-600">*</span></label>
                                    <select name="status" class="form-input w-full">
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Nháp
                                        </option>
                                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Xuất
                                            bản</option>
                                        <option value="pending_review"
                                            {{ old('status') == 'pending_review' ? 'selected' : '' }}>Chờ duyệt</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-end">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="is_featured" value="1" class="form-input mr-2"
                                            {{ old('is_featured') ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">Bài viết nổi bật</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Nút lưu --}}
                        <div class="md:col-span-2 flex items-center justify-center space-x-3 pt-6 border-t">
                            <button type="submit" class="btn btn-success">Lưu</button>
                            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        function slugify(str) {
    return str.toLowerCase()
        .replace(/đ/g, 'd') // ✅ xử lý ngoại lệ duy nhất
        .replace(/Đ/g, 'd') // ✅ nếu hỗ trợ chữ hoa
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // ✅ bỏ toàn bộ dấu
        .replace(/[^a-z0-9\s-]/g, '')    // ❌ bỏ ký tự đặc biệt
        .trim()
        .replace(/\s+/g, '-')            // khoảng trắng → -
        .replace(/-+/g, '-');            // bỏ dấu gạch lặp
}


        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');
            titleInput.addEventListener('input', function() {
                slugInput.value = slugify(titleInput.value);
            });

            // Khởi tạo Select2 cho trường tags
            $('select[name="tags[]"]').select2({
                placeholder: 'Chọn thẻ',
                allowClear: true
            });
        });

        ClassicEditor
            .create(document.querySelector('#editor'), {
                ckfinder: {
                    uploadUrl: '{{ route('admin.posts.uploadImage') . '?_token=' . csrf_token() }}'
                }
            })
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush
