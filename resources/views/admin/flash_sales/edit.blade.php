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
                <form action="{{ route('admin.flash-sales.update', $flashSale->id) }}" method="POST"
                    id="edit-flash-sale-form">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Tên --}}
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Tên chiến dịch</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $flashSale->name) }}"
                                class="w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                                required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Slug --}}
                        <div>
                            <label for="slug" class="block mb-2 text-sm font-medium text-gray-900">Slug</label>
                            <input type="text" id="slug" name="slug" value="{{ old('slug', $flashSale->slug) }}"
                                class="w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('slug') border-red-500 @enderror"
                                required>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Thời gian bắt đầu --}}
                        <div>
                            <label for="start_time" class="block mb-2 text-sm font-medium text-gray-900">Thời gian bắt đầu chiến dịch</label>
                            <input type="date" id="start_time" name="start_time"
                                value="{{ old('start_time', \Carbon\Carbon::parse($flashSale->start_time)->format('Y-m-d')) }}"
                                class="w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('start_time') border-red-500 @enderror"
                                required>
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Thời gian kết thúc --}}
                        <div>
                            <label for="end_time" class="block mb-2 text-sm font-medium text-gray-900">Thời gian kết thúc chiến dịch</label>
                            <input type="date" id="end_time" name="end_time"
                                value="{{ old('end_time', \Carbon\Carbon::parse($flashSale->end_time)->format('Y-m-d')) }}"
                                class="w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('end_time') border-red-500 @enderror"
                                required>
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Danh sách khung giờ --}}
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Khung giờ áp dụng ưu đãi</label>
                            @error('time_slots')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <div id="time-slots-wrapper" class="space-y-4">
                                @php
                                    $timeSlots = old('time_slots', $flashSale->flashSaleTimeSlots->isEmpty() ? [[]] : $flashSale->flashSaleTimeSlots->toArray());
                                    $slotIndex = 0;
                                @endphp
                                @foreach ($timeSlots as $index => $timeSlot)
                                    <div class="space-y-2 time-slot" data-index="{{ $slotIndex }}">
                                        @if (isset($timeSlot['id']))
                                            <input type="hidden" name="time_slots[{{ $slotIndex }}][id]" value="{{ $timeSlot['id'] }}">
                                        @endif
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block mb-1 text-xs font-medium text-gray-700">Giờ bắt đầu</label>
                                                <input type="time" name="time_slots[{{ $slotIndex }}][start_time]"
                                                    value="{{ old("time_slots.$slotIndex.start_time", isset($timeSlot['start_time']) ? \Carbon\Carbon::parse($timeSlot['start_time'])->format('H:i') : '') }}"
                                                    class="time-slot-start w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error("time_slots.$slotIndex.start_time") border-red-500 @enderror"
                                                    step="60" required>
                                                @error("time_slots.$slotIndex.start_time")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="block mb-1 text-xs font-medium text-gray-700">Giờ kết thúc</label>
                                                <input type="time" name="time_slots[{{ $slotIndex }}][end_time]"
                                                    value="{{ old("time_slots.$slotIndex.end_time", isset($timeSlot['end_time']) ? \Carbon\Carbon::parse($timeSlot['end_time'])->format('H:i') : '') }}"
                                                    class="time-slot-end w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error("time_slots.$slotIndex.end_time") border-red-500 @enderror"
                                                    step="60" required>
                                                @error("time_slots.$slotIndex.end_time")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Display overlap or other time slot-specific errors --}}
                                        @error("time_slots.$slotIndex")
                                            <p class="mt-1 text-sm text-red-600">{{ str_replace(':index', $slotIndex + 1, $message) }}</p>
                                        @enderror
                                        <div class="text-left mt-2">
                                            <button type="button" class="remove-time-slot btn btn-danger text-xs px-3 py-1">Xoá khung giờ</button>
                                        </div>
                                    </div>
                                    @php $slotIndex++; @endphp
                                @endforeach
                            </div>
                            <button type="button" id="add-time-slot" class="mt-3 btn btn-secondary">+ Thêm khung giờ</button>
                        </div>
                        {{-- Trạng thái --}}
                        <div class="md:col-span-2">
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900">Trạng thái</label>
                            @if ($flashSale->status === 'scheduled')
                                <input type="text" value="Đã lên lịch"
                                    class="w-full p-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                    disabled>
                                <input type="hidden" name="status" value="scheduled">
                            @elseif ($flashSale->status === 'finished')
                                <input type="text" value="Đã kết thúc"
                                    class="w-full p-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                    disabled>
                                <input type="hidden" name="status" value="finished">
                            @else
                                <select id="status" name="status"
                                    class="w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror"
                                    required>
                                    <option value="active" {{ old('status', $flashSale->status) == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                                    <option value="inactive" {{ old('status', $flashSale->status) == 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
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
            background-color: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background-color: #dc2626;
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
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/đ/g, 'd')
                    .replace(/ /g, '-')
                    .replace(/[^\w-]+/g, '')
                    .replace(/--+/g, '-')
                    .trim();
                slugInput.value = slug;
            });

            // Thêm/xóa khung giờ động
            let slotIndex = {{ $slotIndex }};
            const wrapper = document.getElementById('time-slots-wrapper');

            document.getElementById('add-time-slot').addEventListener('click', function() {
                const newSlot = document.createElement('div');
                newSlot.className = 'space-y-2 time-slot';
                newSlot.setAttribute('data-index', slotIndex);
                newSlot.innerHTML = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-700">Giờ bắt đầu</label>
                            <input type="time" name="time_slots[${slotIndex}][start_time]"
                                class="time-slot-start w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                step="60" required>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-700">Giờ kết thúc</label>
                            <input type="time" name="time_slots[${slotIndex}][end_time]"
                                class="time-slot-end w-full p-2.5 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                step="60" required>
                        </div>
                    </div>
                    <div class="text-left mt-2">
                        <button type="button" class="remove-time-slot btn btn-danger text-xs px-3 py-1">Xoá khung giờ</button>
                    </div>
                `;
                wrapper.appendChild(newSlot);
                slotIndex++;
            });

            // Xóa khung giờ
            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-time-slot')) {
                    const slot = e.target.closest('.time-slot');
                    if (wrapper.querySelectorAll('.time-slot').length > 1) {
                        slot.remove();
                    } else {
                        alert('Bạn phải có ít nhất một khung giờ.');
                    }
                }
            });
        });
    </script>
@endsection