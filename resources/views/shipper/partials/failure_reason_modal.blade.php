
<div x-show="isFailureModalOpen"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="modal-overlay">

    <div @click.outside="isFailureModalOpen = false"
         x-show="isFailureModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-full"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-full"
         class="modal-content">
        
        <h2 class="text-xl font-bold text-gray-800 p-6 border-b">Lý do giao không thành công</h2>

        {{-- Form ẩn để JS điền thông tin và submit --}}
        <form id="fail-delivery-form" action="{{ route('shipper.orders.updateStatus', $order) }}" method="POST" class="hidden">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="failed_delivery">
            <input type="hidden" id="fail-reason-input" name="reason">
            <input type="hidden" id="fail-notes-input" name="notes">
        </form>

        <div class="modal-body p-6 space-y-4">
            <div>
                <label for="failure-reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do chính</label>
                <select id="failure-reason" class="w-full py-2 px-3 border border-gray-300 bg-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Không liên lạc được khách hàng">Không liên lạc được khách</option>
                    <option value="Khách hẹn giao lại">Khách hẹn giao lại</option>
                    <option value="Sai địa chỉ">Sai địa chỉ</option>
                    <option value="other">Lý do khác</option>
                </select>
            </div>
            <div>
                <label for="failure-notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú thêm</label>
                <textarea id="failure-notes" rows="3" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="VD: Khách hẹn giao sau 17h"></textarea>
            </div>
        </div>
        
        <div class="p-6 grid grid-cols-2 gap-3 border-t">
            {{-- Nút Hủy giờ sẽ đổi trạng thái isFailureModalOpen --}}
            <button type="button" @click="isFailureModalOpen = false" class="w-full bg-gray-200 text-gray-700 font-bold py-3 rounded-lg">Hủy</button>
            
            {{-- Nút Xác nhận sẽ gọi hàm submitFailureForm trong AlpineJS component --}}
            <button type="button" @click="submitFailureForm()" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg">Xác nhận</button>
        </div>
    </div>
</div>