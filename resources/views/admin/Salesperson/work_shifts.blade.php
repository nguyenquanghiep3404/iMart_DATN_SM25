@extends('admin.layouts.app')

@section('title', 'Quản Lý Ca Làm Việc')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div>
            <a href="{{ route('admin.sales-staff.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-indigo-600 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Quay lại Quản lý Nhân viên Bán hàng
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Quản Lý Ca Làm Việc</h1>
            <p class="text-gray-500">Thêm, sửa, xóa các ca làm việc</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="create-default-shifts-btn" class="flex items-center gap-2 bg-green-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"></path></svg>
                Tạo Ca Mặc Định
            </button>
            <button id="add-work-shift-btn" class="flex items-center gap-2 bg-indigo-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                Thêm Ca Mới
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Tên Ca</th>
                        <th scope="col" class="px-6 py-3">Giờ Bắt Đầu</th>
                        <th scope="col" class="px-6 py-3">Giờ Kết Thúc</th>
                        <th scope="col" class="px-6 py-3">Thời Gian Làm Việc</th>
                        <th scope="col" class="px-6 py-3">Màu Sắc</th>
                        <th scope="col" class="px-6 py-3 text-center">Hành Động</th>
                    </tr>
                </thead>
                <tbody id="work-shifts-table-body">
                    @forelse($workShifts ?? [] as $shift)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $shift->name }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</td>
                            <td class="px-6 py-4">{{ $shift->thoi_gian_lam_viec_tinh_bang_gio }} giờ</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $shift->color_code }}"></div>
                                    <span class="text-xs">{{ $shift->color_code }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <button class="edit-shift-btn bg-gray-200 text-gray-800 p-2 rounded-lg hover:bg-gray-300 transition-colors" title="Chỉnh sửa" data-id="{{ $shift->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </button>
                                    <button class="delete-shift-btn bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition-colors" title="Xóa" data-id="{{ $shift->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12,6 12,12 16,14"></polyline></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có ca làm việc</h3>
                                    <p class="mt-1 text-sm text-gray-500">Hãy thêm ca làm việc đầu tiên hoặc tạo ca mặc định.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Work Shift Modal -->
<div id="work-shift-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md m-4 transform transition-all scale-95 opacity-0" id="work-shift-modal-content">
        <h2 id="work-shift-modal-title" class="text-2xl font-bold text-gray-800 mb-6">Thêm Ca Làm Việc Mới</h2>
        <form id="work-shift-form">
            @csrf
            <input type="hidden" id="editing-shift-id">
            <div class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="shift-name">Tên Ca</label>
                    <input id="shift-name" name="name" type="text" class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="VD: Ca Sáng, Ca Chiều" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2" for="shift-start-time">Giờ Bắt Đầu</label>
                        <input id="shift-start-time" name="start_time" type="time" class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-semibold mb-2" for="shift-end-time">Giờ Kết Thúc</label>
                        <input id="shift-end-time" name="end_time" type="time" class="w-full px-4 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="shift-color">Màu Sắc</label>
                    <input id="shift-color" name="color_code" type="color" class="w-full h-12 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" value="#4299E1">
                </div>
            </div>
            <div class="flex justify-end gap-4 mt-8">
                <button type="button" id="cancel-work-shift-modal-btn" class="px-6 py-2 rounded-lg text-gray-700 bg-gray-200 hover:bg-gray-300 font-semibold">Hủy</button>
                <button type="submit" id="work-shift-modal-submit-btn" class="px-6 py-2 rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 font-semibold">Thêm Mới</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addWorkShiftBtn = document.getElementById('add-work-shift-btn');
    const createDefaultShiftsBtn = document.getElementById('create-default-shifts-btn');
    const workShiftModal = document.getElementById('work-shift-modal');
    const cancelWorkShiftModalBtn = document.getElementById('cancel-work-shift-modal-btn');
    const workShiftForm = document.getElementById('work-shift-form');
    const workShiftsTableBody = document.getElementById('work-shifts-table-body');
    
    // Open modal for adding new shift
    addWorkShiftBtn.addEventListener('click', function() {
        openAddModal();
    });
    
    // Create default shifts
    createDefaultShiftsBtn.addEventListener('click', function() {
        if (confirm('Bạn có chắc muốn tạo các ca làm việc mặc định?')) {
            fetch('{{ route("admin.sales-staff.api.work-shifts.create-default") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    location.reload();
                }
            });
        }
    });
    
    // Edit shift
    workShiftsTableBody.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-shift-btn');
        if (editBtn) {
            const shiftId = editBtn.dataset.id;
            fetch(`/admin/sales-staff/api/work-shifts/${shiftId}`)
                .then(response => response.json())
                .then(data => {
                    openEditModal(data.work_shift);
                });
        }
        
        const deleteBtn = e.target.closest('.delete-shift-btn');
        if (deleteBtn) {
            const shiftId = deleteBtn.dataset.id;
            if (confirm('Bạn có chắc muốn xóa ca làm việc này?')) {
                fetch(`/admin/sales-staff/api/work-shifts/${shiftId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    }
                });
            }
        }
    });
    
    // Modal functionality
    cancelWorkShiftModalBtn.addEventListener('click', closeModal);
    workShiftModal.addEventListener('click', function(e) {
        if (e.target === workShiftModal) {
            closeModal();
        }
    });
    
    function openAddModal() {
        document.getElementById('work-shift-modal-title').textContent = 'Thêm Ca Làm Việc Mới';
        document.getElementById('work-shift-modal-submit-btn').textContent = 'Thêm Mới';
        document.getElementById('editing-shift-id').value = '';
        document.getElementById('work-shift-form').reset();
        workShiftModal.classList.remove('hidden');
    }
    
    function openEditModal(shift) {
        document.getElementById('work-shift-modal-title').textContent = 'Chỉnh Sửa Ca Làm Việc';
        document.getElementById('work-shift-modal-submit-btn').textContent = 'Lưu Thay Đổi';
        document.getElementById('editing-shift-id').value = shift.id;
        document.getElementById('shift-name').value = shift.name;
        document.getElementById('shift-start-time').value = shift.start_time;
        document.getElementById('shift-end-time').value = shift.end_time;
        document.getElementById('shift-color').value = shift.color_code;
        workShiftModal.classList.remove('hidden');
    }
    
    function closeModal() {
        workShiftModal.classList.add('hidden');
    }
    
    // Form submission
    workShiftForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const shiftId = document.getElementById('editing-shift-id').value;
        const url = shiftId ? 
            `/admin/sales-staff/api/work-shifts/${shiftId}` :
            '{{ route("admin.sales-staff.api.work-shifts.store") }}';
        const method = shiftId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                closeModal();
                location.reload();
            } else if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    console.error(`${field}: ${data.errors[field][0]}`);
                });
            }
        });
    });
});
</script>
@endpush 