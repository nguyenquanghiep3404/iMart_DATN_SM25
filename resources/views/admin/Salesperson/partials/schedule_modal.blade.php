@php
    use Illuminate\Support\Str;
@endphp
<!-- Lịch trình thay đổi chế độ -->
<div id="schedule-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md m-4 transform transition-all duration-200 ease-out scale-95 opacity-0"
        id="schedule-modal-content">
        <h2 id="schedule-modal-title" class="text-2xl font-bold text-gray-800 mb-6">Sắp xếp ca</h2>
        <form id="schedule-form">
            @csrf
            <input type="hidden" id="scheduling-staff-id" name="user_id">
            <input type="hidden" id="scheduling-date" name="date">
            <input type="hidden" name="store_location_id" value="{{ $store->id ?? '' }}">
            <div class="space-y-6">
                <div id="schedule-modal-info" class="text-center">
                    <!-- Thông tin được hiển thị -->
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Ca Làm Việc</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($workShifts as $shift)
                            @php
                                $shiftId = 'shift-' . Str::slug($shift->name);
                                $colorClass =
                                    'peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white';
                            @endphp
                            <div>
                                <input type="radio" id="{{ $shiftId }}" name="work_shift_name"
                                    value="{{ $shift->name }}" class="hidden peer">
                                <label for="{{ $shiftId }}"
                                    class="w-full text-center block px-4 py-3 rounded-lg border-2 border-gray-300 cursor-pointer {{ $colorClass }} transition-all duration-200">
                                    <div class="text-base font-bold">{{ $shift->name }}</div>
                                    <div class="text-sm text-gray-500 mt-1" style="color: inherit;">
                                        {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                    </div>
                                </label>
                            </div>
                        @endforeach

                        <!-- Option Nghỉ -->
                        <div>
                            <input type="radio" id="shift-off" name="work_shift_name" value="Nghỉ"
                                class="hidden peer">
                            <label for="shift-off"
                                class="w-full text-center block px-4 py-3 rounded-lg border-2 border-gray-300 cursor-pointer peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition-all duration-200">
                                <div class="text-base font-bold">Nghỉ</div>
                                <div class="text-sm text-gray-500 mt-1" style="color: inherit;">
                                    Không làm việc
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-4 mt-8">
                <button type="button" id="cancel-schedule-modal-btn"
                    class="px-6 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300 font-semibold">Hủy</button>
                <button type="submit"
                    class="px-6 py-2 rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 font-semibold">Lưu Ca</button>
            </div>
        </form>
    </div>
</div>
