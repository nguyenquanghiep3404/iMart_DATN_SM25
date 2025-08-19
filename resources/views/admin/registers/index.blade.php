@php($disableMainCss = true)
@extends('admin.layouts.app')

@section('content')
    @include('admin.registers.layouts.css')

    <div x-data='posManager(@json($registers), @json($locations))'>
        <div class="px-4 sm:px-6 md:px-8 py-8">
            <div class="container mx-auto max-w-full">

                @include('admin.registers.layouts.header')

                <div class="card-custom">
                    <div class="card-custom-header">
                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                            <h3 class="card-custom-title">Danh sách máy POS (<span x-text="registers.length"></span>)</h3>
                            <button @click="openModal()" type="button" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Thêm máy POS
                            </button>
                        </div>
                    </div>

                    <div class="card-custom-body">
                        @include('admin.registers.layouts.filters')

                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">STT</th>
                                        <th>Tên máy POS</th>
                                        <th>Thuộc cửa hàng</th>
                                        <th>Device UID</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th style="width: 120px;" class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(register, index) in filteredRegisters" :key="register.id">
                                        <tr>
                                            <td x-text="index + 1"></td>
                                            <td class="font-semibold" x-text="register.name"></td>
                                            <td x-text="register.store_location_name"></td>
                                            <td x-text="register.device_uid || 'N/A'"></td>
                                            <td class="text-center">
                                                <span class="badge-custom"
                                                    :class="register.status === 'active' ? 'badge-success' : 'badge-secondary'"
                                                    x-text="register.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động'">
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="inline-flex space-x-1">
                                                    <button @click="editRegister(register)" type="button"
                                                        class="btn btn-primary btn-sm" title="Chỉnh sửa"><i
                                                            class="fas fa-edit"></i></button>
                                                    <button @click="deleteRegister(register)" type="button"
                                                        class="btn btn-danger btn-sm" title="Xóa"><i
                                                            class="fas fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="filteredRegisters.length === 0">
                                        <tr>
                                            <td colspan="6" class="text-center py-6 text-gray-500">
                                                Không tìm thấy máy POS nào.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-custom-footer">
                        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center w-full">
                            <p class="text-sm text-gray-700 leading-5">
                                Hiển thị từ <span class="font-medium">1</span> đến <span class="font-medium"
                                    x-text="filteredRegisters.length"></span> trên tổng số <span class="font-medium"
                                    x-text="registers.length"></span> kết quả
                            </p>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    aria-label="Pagination">
                                    <a href="#"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Previous</span>
                                        <i class="fas fa-chevron-left h-5 w-5"></i>
                                    </a>
                                    <a href="#" aria-current="page"
                                        class="z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                        1
                                    </a>
                                    <a href="#"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        <span class="sr-only">Next</span>
                                        <i class="fas fa-chevron-right h-5 w-5"></i>
                                    </a>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isModalOpen" @click="closeModal()"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="isModalOpen"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900"
                                    x-text="isEditMode ? 'Chỉnh sửa Máy POS' : 'Thêm Máy POS mới'">
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tên máy POS</label>
                                        <input type="text" x-model="formData.name" class="form-input mt-1"
                                            placeholder="Ví dụ: Máy POS 1 - Quận 1">
                                    </div>
                                    <div>
                                        <label for="store_location_id" class="block text-sm font-medium text-gray-700">Thuộc
                                            cửa hàng</label>
                                        <div class="relative mt-1" @click.away="isLocationDropdownOpen = false">
                                            <button @click="isLocationDropdownOpen = !isLocationDropdownOpen" type="button"
                                                class="form-input text-left flex justify-between items-center">
                                                <span x-text="getSelectedLocationName()"></span>
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </button>
                                            <div x-show="isLocationDropdownOpen" x-transition
                                                class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200">
                                                <div class="p-2 space-y-2 border-b">
                                                    <input type="text" x-model="locationSearch"
                                                        placeholder="Tìm kiếm tên cửa hàng..." class="form-input">
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <select x-model="provinceFilter" @change="districtFilter = 'all'"
                                                            class="form-select">
                                                            <option value="all">Tất cả tỉnh/thành</option>
                                                            <template x-for="province in uniqueProvinces"
                                                                :key="province">
                                                                <option :value="province" x-text="province"></option>
                                                            </template>
                                                        </select>
                                                        <select x-model="districtFilter" class="form-select"
                                                            :disabled="provinceFilter === 'all'">
                                                            <option value="all">Tất cả quận/huyện</option>
                                                            <template x-for="district in uniqueDistricts"
                                                                :key="district">
                                                                <option :value="district" x-text="district"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                                <ul class="max-h-60 overflow-y-auto p-1">
                                                    <template x-for="location in filteredLocations" :key="location.id">
                                                        <li>
                                                            <a href="#" @click.prevent="selectLocation(location)"
                                                                class="block px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-indigo-50"
                                                                x-text="location.name"></a>
                                                        </li>
                                                    </template>
                                                    <template x-if="filteredLocations.length === 0">
                                                        <li class="px-3 py-2 text-sm text-center text-gray-500">Không tìm
                                                            thấy cửa hàng.</li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Device UID (tùy
                                            chọn)</label>
                                        <input type="text" x-model="formData.device_uid" class="form-input mt-1"
                                            placeholder="Mã định danh duy nhất của thiết bị">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                                        <div class="mt-2">
                                            <label class="form-switch">
                                                <input type="checkbox" x-model="isActive">
                                                <span class="slider"></span>
                                            </label>
                                            <span class="ml-3 text-sm text-gray-600"
                                                x-text="isActive ? 'Đang hoạt động' : 'Không hoạt động'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="saveRegister()" type="button"
                            class="btn btn-primary w-full sm:ml-3 sm:w-auto">Lưu</button>
                        <button @click="closeModal()" type="button"
                            class="btn btn-secondary mt-3 w-full sm:mt-0 sm:w-auto">Hủy</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @include('admin.registers.layouts.script')
