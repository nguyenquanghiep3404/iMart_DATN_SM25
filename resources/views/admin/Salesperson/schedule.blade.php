@extends('admin.layouts.app')

@section('title', 'Lịch Làm Việc - ' . $store->name)

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.sales-staff.stores.employees', $store->id) }}" class="p-2 rounded-md hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
<div>
                <h1 class="text-3xl font-bold text-gray-800">Lịch Làm Việc</h1>
                <p class="text-gray-500">{{ $store->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button id="prev-week-btn" class="p-2 rounded-md hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
            <span id="current-week-display" class="font-semibold text-gray-700">
                {{ \Carbon\Carbon::parse($weekStartDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($weekStartDate)->addDays(6)->format('d/m/Y') }}
            </span>
            <button id="next-week-btn" class="p-2 rounded-md hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <div id="schedule-grid-container" class="min-w-[1000px]">
            @if($employees->count() > 0)
                <div class="grid schedule-grid text-sm">
                    <!-- Header Row -->
                    <div class="font-semibold p-2 border-b border-r bg-gray-50 text-gray-600">Nhân viên</div>
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $date = \Carbon\Carbon::parse($weekStartDate)->addDays($i);
                            $dayNames = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
                        @endphp
                        <div class="font-semibold p-2 border-b text-center bg-gray-50 text-gray-600">
                            <div>{{ $dayNames[$i] }}</div>
                            <div class="text-xs font-normal">{{ $date->format('d/m') }}</div>
                        </div>
                    @endfor

                    <!-- Employee Rows -->
                    @foreach($employees as $employee)
                        <div class="font-semibold p-3 border-b border-r text-gray-800 bg-gray-50 flex items-center">{{ $employee->name }}</div>
                        @for($i = 0; $i < 7; $i++)
                            @php
                                $date = \Carbon\Carbon::parse($weekStartDate)->addDays($i);
                                $dateString = $date->format('Y-m-d');
                                $schedule = $schedules->where('user_id', $employee->id)->where('date', $dateString)->first();
                            @endphp
                            <div class="schedule-cell border-b p-2 cursor-pointer hover:bg-indigo-50 min-h-[60px]" 
                                 data-staff-id="{{ $employee->id }}" 
                                 data-date="{{ $dateString }}">
                                @if($schedule)
                                    @php
                                        $shiftColors = [
                                            'Ca Sáng' => 'bg-blue-100 text-blue-800',
                                            'Ca Chiều' => 'bg-amber-100 text-amber-800',
                                            'Ca Tối' => 'bg-indigo-100 text-indigo-800',
                                        ];
                                        $colorClass = $shiftColors[$schedule->workShift->name ?? ''] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <div class="font-semibold rounded-md p-2 text-xs {{ $colorClass }}">
                                        {{ $schedule->workShift->name ?? 'N/A' }}
                                    </div>
                                @endif
                            </div>
                        @endfor
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Cửa hàng này chưa có nhân viên nào.</h3>
                    <p class="mt-1 text-sm text-gray-500">Hãy thêm nhân viên trước khi quản lý lịch làm việc.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Schedule Shift Modal -->
@include('admin.Salesperson.partials.schedule_modal')

@endsection

@push('styles')
<style>
.schedule-grid {
    grid-template-columns: 150px repeat(7, minmax(0, 1fr));
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const prevWeekBtn = document.getElementById('prev-week-btn');
    const nextWeekBtn = document.getElementById('next-week-btn');
    const currentWeekDisplay = document.getElementById('current-week-display');
    const scheduleGridContainer = document.getElementById('schedule-grid-container');
    const scheduleModal = document.getElementById('schedule-modal');
    const cancelScheduleModalBtn = document.getElementById('cancel-schedule-modal-btn');
    const scheduleForm = document.getElementById('schedule-form');
    
    let currentWeekStartDate = new Date('{{ $weekStartDate }}');
    
    // Navigation buttons
    prevWeekBtn.addEventListener('click', function() {
        currentWeekStartDate.setDate(currentWeekStartDate.getDate() - 7);
        loadSchedule();
    });
    
    nextWeekBtn.addEventListener('click', function() {
        currentWeekStartDate.setDate(currentWeekStartDate.getDate() + 7);
        loadSchedule();
    });
    
    function loadSchedule() {
        const weekStart = currentWeekStartDate.toISOString().split('T')[0];
        const weekEnd = new Date(currentWeekStartDate);
        weekEnd.setDate(currentWeekStartDate.getDate() + 6);
        const weekEndStr = weekEnd.toISOString().split('T')[0];
        
        // Update display
        currentWeekDisplay.textContent = `${currentWeekStartDate.toLocaleDateString('vi-VN')} - ${weekEnd.toLocaleDateString('vi-VN')}`;
        
        // Show loading
        scheduleGridContainer.innerHTML = '<div class="p-8 text-center">Đang tải...</div>';
        
        // Fetch schedule data
        fetch(`{{ route('admin.sales-staff.api.schedule.weekly', $store->id) }}?week_start=${weekStart}`)
            .then(response => response.json())
            .then(data => {
                renderScheduleGrid(data.schedules);
            });
    }
    
    function renderScheduleGrid(schedules) {
        const employees = @json($employees);
        const dayNames = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];
        
        if (employees.length === 0) {
            scheduleGridContainer.innerHTML = `
                <div class="p-8 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Cửa hàng này chưa có nhân viên nào.</h3>
                    <p class="mt-1 text-sm text-gray-500">Hãy thêm nhân viên trước khi quản lý lịch làm việc.</p>
                </div>
            `;
            return;
        }
        
        let gridHTML = '<div class="grid schedule-grid text-sm">';
        
        // Header Row
        gridHTML += '<div class="font-semibold p-2 border-b border-r bg-gray-50 text-gray-600">Nhân viên</div>';
        for (let i = 0; i < 7; i++) {
            const date = new Date(currentWeekStartDate);
            date.setDate(currentWeekStartDate.getDate() + i);
            gridHTML += `
                <div class="font-semibold p-2 border-b text-center bg-gray-50 text-gray-600">
                    <div>${dayNames[i]}</div>
                    <div class="text-xs font-normal">${date.getDate()}/${date.getMonth() + 1}</div>
                </div>
            `;
        }
        
        // Employee Rows
        employees.forEach(employee => {
            gridHTML += `<div class="font-semibold p-3 border-b border-r text-gray-800 bg-gray-50 flex items-center">${employee.name}</div>`;
            
            for (let i = 0; i < 7; i++) {
                const date = new Date(currentWeekStartDate);
                date.setDate(currentWeekStartDate.getDate() + i);
                const dateString = date.toISOString().split('T')[0];
                
                const schedule = schedules.find(s => s.user_id === employee.id && s.date === dateString);
                let shiftTag = '';
                
                if (schedule) {
                    const shiftColors = {
                        "Ca Sáng": "bg-blue-100 text-blue-800",
                        "Ca Chiều": "bg-amber-100 text-amber-800",
                        "Ca Tối": "bg-indigo-100 text-indigo-800",
                    };
                    const colorClass = shiftColors[schedule.work_shift_name] || 'bg-gray-100 text-gray-800';
                    shiftTag = `<div class="font-semibold rounded-md p-2 text-xs ${colorClass}">${schedule.work_shift_name}</div>`;
                }
                
                gridHTML += `
                    <div class="schedule-cell border-b p-2 cursor-pointer hover:bg-indigo-50 min-h-[60px]" 
                         data-staff-id="${employee.id}" 
                         data-date="${dateString}">
                        ${shiftTag}
                    </div>
                `;
            }
        });
        
        gridHTML += '</div>';
        scheduleGridContainer.innerHTML = gridHTML;
    }
    
    // Schedule cell click
    scheduleGridContainer.addEventListener('click', function(e) {
        const cell = e.target.closest('.schedule-cell');
        if (cell) {
            const staffId = cell.dataset.staffId;
            const date = cell.dataset.date;
            openScheduleModal(staffId, date);
        }
    });
    
    function openScheduleModal(staffId, dateString) {
        const employee = @json($employees).find(e => e.id == staffId);
        if (!employee) return;
        
        document.getElementById('scheduling-staff-id').value = staffId;
        document.getElementById('scheduling-date').value = dateString;
        
        const dateObj = new Date(dateString);
        document.getElementById('schedule-modal-info').innerHTML = `
            <p class="font-semibold text-gray-800">${employee.name}</p>
            <p class="text-sm text-gray-500">${dateObj.toLocaleDateString('vi-VN')}</p>
        `;
        
        // Check existing schedule
        fetch(`{{ route('admin.sales-staff.api.schedule.weekly', $store->id) }}?week_start=${currentWeekStartDate.toISOString().split('T')[0]}`)
            .then(response => response.json())
            .then(data => {
                const existingSchedule = data.schedules.find(s => s.user_id == staffId && s.date === dateString);
                
                // Reset all radio buttons
                document.querySelectorAll('input[name="work-shift"]').forEach(radio => {
                    radio.checked = false;
                });
                
                if (existingSchedule) {
                    const radio = document.querySelector(`input[name="work-shift"][value="${existingSchedule.work_shift_name}"]`);
                    if (radio) radio.checked = true;
                } else {
                    document.getElementById('shift-off').checked = true;
                }
                
                scheduleModal.classList.remove('hidden');
            });
    }
    
    function closeScheduleModal() {
        scheduleModal.classList.add('hidden');
    }
    
    cancelScheduleModalBtn.addEventListener('click', closeScheduleModal);
    scheduleModal.addEventListener('click', function(e) {
        if (e.target === scheduleModal) {
            closeScheduleModal();
        }
    });
    
    // Form submission
    scheduleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route('admin.sales-staff.api.schedule.assign-shift') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                closeScheduleModal();
                loadSchedule(); // Reload the schedule
            } else if (data.errors) {
                // Handle validation errors
                Object.keys(data.errors).forEach(field => {
                    console.error(`${field}: ${data.errors[field][0]}`);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi cập nhật lịch làm việc');
        });
    });
});
</script>
@endpush
