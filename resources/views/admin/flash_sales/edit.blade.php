@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa Flash Sale')

@section('content')
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Chỉnh sửa Flash Sale</h1>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="flex text-sm text-gray-500">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                            class="text-indigo-600 hover:text-indigo-800">Bảng điều khiển</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.flash-sales.index') }}"
                            class="text-indigo-600 hover:text-indigo-800">Flash Sale</a></li>
                    <li class="text-gray-400 mx-2">/</li>
                    <li class="breadcrumb-item active text-gray-700 font-medium" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>
        </div>

        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title">Cập nhật chiến dịch Flash Sale</h3>
            </div>
            <div class="card-custom-body">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.flash-sales.update', $flashSale->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Tên chiến
                                dịch</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $flashSale->name) }}"
                                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>

                        <div>
                            <label for="slug" class="block mb-2 text-sm font-medium text-gray-900">Slug</label>
                            <input type="text" id="slug" name="slug" value="{{ old('slug', $flashSale->slug) }}"
                                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>

                        <div>
                            <label for="start_time" class="block mb-2 text-sm font-medium text-gray-900">Thời gian bắt
                                đầu</label>
                            <input type="datetime-local" id="start_time" name="start_time"
                                value="{{ old('start_time', \Carbon\Carbon::parse($flashSale->start_time)->format('Y-m-d\TH:i')) }}"
                                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                step="60" required>
                        </div>

                        <div>
                            <label for="end_time" class="block mb-2 text-sm font-medium text-gray-900">Thời gian kết
                                thúc</label>
                            <input type="datetime-local" id="end_time" name="end_time"
                                value="{{ old('end_time', \Carbon\Carbon::parse($flashSale->end_time)->format('Y-m-d\TH:i')) }}"
                                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                step="60" required>
                        </div>
                        <div class="mt-6">
                            <h3 class="text-base font-semibold mb-3">Các khung giờ trong chiến dịch</h3>
                           
                            @foreach ($flashSale->flashSaleTimeSlots ?? [] as $index => $timeSlot)

                                <div class="grid grid-cols-[1fr_1fr_auto] gap-3 items-center mb-2 time-slot-row">
                                    <input type="time" name="time_slots[{{ $index }}][start_time]"
                                        value="{{ \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i') }}"
                                        class="p-2 border rounded w-full" required>

                                    <input type="time" name="time_slots[{{ $index }}][end_time]"
                                        value="{{ \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i') }}"
                                        class="p-2 border rounded w-full" required>

                                    {{-- <form action="{{ route('admin.flash-sales.time-slots.destroy', $timeSlot->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xoá khung giờ này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger text-red-500 text-sm">Xoá</button>
                                    </form> --}}
                                </div>
                            @endforeach

                            <div id="time-slots-wrapper"></div>

                            <button type="button" id="add-time-slot"
                                class="btn btn-secondary mt-3 inline-block text-blue-600 hover:underline text-sm">+ Thêm
                                khung giờ</button>
                        </div>




                        <div class="md:col-span-2">
                            <label for="banner_image_url" class="block mb-2 text-sm font-medium text-gray-900">URL ảnh
                                banner</label>
                            <input type="text" id="banner_image_url" name="banner_image_url"
                                value="{{ old('banner_image_url', $flashSale->banner_image_url) }}"
                                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900">Trạng thái</label>
                            <select id="status" name="status"
                                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                                <option value="scheduled"
                                    {{ old('status', $flashSale->status) == 'scheduled' ? 'selected' : '' }}>Đã lên lịch
                                </option>
                                <option value="active"
                                    {{ old('status', $flashSale->status) == 'active' ? 'selected' : '' }}>Đang hoạt động
                                </option>
                                <option value="finished"
                                    {{ old('status', $flashSale->status) == 'finished' ? 'selected' : '' }}>Đã kết thúc
                                </option>
                                <option value="inactive"
                                    {{ old('status', $flashSale->status) == 'inactive' ? 'selected' : '' }}>Không hoạt động
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-2">
                        <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-secondary">Hủy</a>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .card-custom {
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
        }

        .card-custom-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }

        .card-custom-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .card-custom-body {
            padding: 1.5rem;
        }

        .btn {
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-danger {
            background-color: #f87171;
            color: white;
        }

        .btn-danger:hover {
            background-color: #ef4444;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tự động tạo slug từ tên chiến dịch
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');
            nameInput.addEventListener('input', function() {
                const slug = nameInput.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                slugInput.value = slug;
            });

            // Thêm khung giờ động
            let slotIndex = 1;
            const wrapper = document.getElementById('time-slots-wrapper');
            document.getElementById('add-time-slot').addEventListener('click', function() {
                const newSlot = document.createElement('div');
                newSlot.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4 time-slot';
                newSlot.innerHTML = `
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-700">Giờ bắt đầu</label>
                    <input type="time" name="time_slots[${slotIndex}][start_time]" class="time-slot-start w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" step="60" required>
                </div>
                <div>
                    <label class="block mb-1 text-xs font-medium text-gray-700">Giờ kết thúc</label>
                    <input type="time" name="time_slots[${slotIndex}][end_time]" class="time-slot-end w-full p-2.5 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" step="60" required>
                </div>
            `;
                wrapper.appendChild(newSlot);
                slotIndex++;
            });
        });
    </script>

@endsection
