<!-- Lịch trình thay đổi chế độ -->
<div id="schedule-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md m-4 transform transition-all scale-95 opacity-0"
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
                        <div>
                            <input type="radio" id="shift-morning" name="work_shift_name" value="Ca Sáng"
                                class="hidden peer">
                            <label for="shift-morning"
                                class="w-full text-center block px-4 py-2 rounded-lg border-2 border-gray-300 cursor-pointer peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600">Ca
                                Sáng</label>
                        </div>
                        <div>
                            <input type="radio" id="shift-afternoon" name="work_shift_name" value="Ca Chiều"
                                class="hidden peer">
                            <label for="shift-afternoon"
                                class="w-full text-center block px-4 py-2 rounded-lg border-2 border-gray-300 cursor-pointer peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500">Ca
                                Chiều</label>
                        </div>
                        <div>
                            <input type="radio" id="shift-evening" name="work_shift_name" value="Ca Tối"
                                class="hidden peer">
                            <label for="shift-evening"
                                class="w-full text-center block px-4 py-2 rounded-lg border-2 border-gray-300 cursor-pointer peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">Ca
                                Tối</label>
                        </div>
                        <div>
                            <input type="radio" id="shift-off" name="work_shift_name" value="Nghỉ"
                                class="hidden peer">
                            <label for="shift-off"
                                class="w-full text-center block px-4 py-2 rounded-lg border-2 border-gray-300 cursor-pointer peer-checked:bg-gray-600 peer-checked:text-white peer-checked:border-gray-600">Nghỉ</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-4 mt-8">
                <button type="button" id="cancel-schedule-modal-btn"
                    class="px-6 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300 font-semibold">Hủy</button>
                <button type="submit"
                    class="px-6 py-2 rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 font-semibold">Lưu
                    Ca</button>
            </div>
        </form>
    </div>
</div>
