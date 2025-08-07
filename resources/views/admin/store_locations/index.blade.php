@extends('admin.layouts.app')
@php
    session()->pull('success');
    session()->pull('error');
@endphp
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@section('title', 'Quản lý Cửa hàng')

@section('content')
<div class="px-4 sm:px-6 md:px-8 py-8" x-data="storeLocationManager()" x-init="init()">
    <div class="container mx-auto max-w-7xl">
        <header class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-gray-800">Quản lý Địa điểm Cửa Hàng</h1>
                <div class="flex items-center space-x-3">
                    <button @click="openModal()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Thêm địa điểm mới
                    </button>
                    <a href="{{ route('admin.store-locations.trashed') }}" class="inline-flex items-center px-4 py-2 border border-gray-500 shadow-sm text-sm font-medium rounded-lg text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>Thùng rác
                    </a>
                </div>
            </div>
        </header>
            <div x-show="message" x-cloak
                :class="{ 'bg-green-100 border-green-400 text-green-700': messageType === 'success', 'bg-red-100 border-red-400 text-red-700': messageType === 'error' }"
                class="border px-4 py-3 rounded relative mb-4" role="alert">
                <span x-html="message"></span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="message = ''">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                        <h3 class="text-xl font-semibold text-gray-900">Danh sách địa điểm (<span
                                x-text="filteredLocations.length"></span>)</h3>
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-4 mb-6 md:items-end">
                        <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                                <input type="text" id="search" x-model.debounce.500ms="searchQuery"
                                    @input="currentPage = 1" placeholder="Tìm theo tên, địa chỉ, SĐT..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1">
                            </div>
                            <div>
                                <label for="filterType" class="block text-sm font-medium text-gray-700">Phân loại</label>
                                <select id="filterType" x-model="filterType" @change="currentPage = 1"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1">
                                    <option value="">Tất cả</option>
                                    <option value="store">Cửa hàng</option>
                                    <option value="warehouse">Kho</option>
                                    <option value="service_center">Trung tâm bảo hành</option>
                                </select>
                            </div>
                            <div>
                                <label for="filterStatus" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                                <select id="filterStatus" x-model="filterStatus" @change="currentPage = 1"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1">
                                    <option value="">Tất cả</option>
                                    <option value="true">Đang hoạt động</option>
                                    <option value="false">Ngưng hoạt động</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-2">
                            <button @click="clearFilters()"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times mr-2"></i>Xoá bộ lọc
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                        STT</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                        Tên Địa điểm</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-3/12">
                                        Địa chỉ</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                        Số điện thoại</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                        Phân loại</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">
                                        Trạng thái</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">
                                        Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-if="paginatedLocations.length === 0">
                                    <tr>
                                        <td colspan="7"
                                            class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Không tìm thấy địa điểm nào.
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="(location, index) in paginatedLocations" :key="location.id">
                                    <tr>
                                        <td x-text="(currentPage - 1) * itemsPerPage + index + 1"
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"></td>
                                        <td x-text="location.name"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"></td>
                                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-600">
                                            <span x-text="location.address"></span>
                                            <template x-if="location.ward">, <span
                                                    x-text="location.ward.name"></span></template>
                                            <template x-if="location.district">, <span
                                                    x-text="location.district.name"></span></template>
                                            <template x-if="location.province">, <span
                                                    x-text="location.province.name"></span></template>
                                        </td>
                                        <td x-text="location.phone || 'N/A'"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"></td>
                                        <td x-text="getTypeName(location.type)"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            <span
                                                :class="location.is_active ? 'bg-green-100 text-green-800' :
                                                    'bg-gray-100 text-gray-800'"
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                x-text="location.is_active ? 'Hoạt động' : 'Ngưng hoạt động'">
                                            </span>
                                            {{-- Công tắc bật/tắt mới --}}
                                            <label class="relative inline-flex items-center cursor-pointer ml-2">
                                                <input type="checkbox" :checked="location.is_active"
                                                    @change="toggleActive(location.id, location.is_active)"
                                                    class="sr-only peer">
                                                <div
                                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                                                </div>
                                                <span class="ml-3 text-sm font-medium text-gray-900 hidden sm:inline"
                                                    x-text="location.is_active ? 'Bật' : 'Tắt'"></span>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="inline-flex space-x-2">
                                                <button @click="editLocation(location)" type="button"
                                                    class="text-indigo-600 hover:text-indigo-900" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="openDeleteModal(location.id)" type="button"
                                                    class="text-red-600 hover:text-red-900" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex flex-col md:flex-row justify-between items-center" x-show="totalPages > 1">
                        <div class="text-sm text-gray-700 mb-2 md:mb-0">
                            Hiển thị <span x-text="startItem"></span> đến <span x-text="endItem"></span> trong tổng số
                            <span x-text="totalItems"></span> mục
                        </div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button @click="prevPage()" :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                <span class="sr-only">Trước</span>
                                <i class="fas fa-chevron-left h-5 w-5"></i>
                            </button>
                            <template x-for="page in pages" :key="page">
                                <button @click="changePage(page)"
                                    :class="{
                                        'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': currentPage === page,
                                        'bg-white border-gray-300 text-gray-700 hover:bg-gray-50': currentPage !== page
                                    }"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    x-text="page">
                                </button>
                            </template>
                            <button @click="nextPage()" :disabled="currentPage === totalPages"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                <span class="sr-only">Tiếp</span>
                                <i class="fas fa-chevron-right h-5 w-5"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Thêm/Sửa --}}
        <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isModalOpen" @click="closeModal()" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="isModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title"
                            x-text="isEditMode ? 'Chỉnh sửa Địa điểm' : 'Thêm Địa điểm mới'"></h3>

                        <form @submit.prevent="saveLocation()">
                            <input type="hidden" name="id" x-model="formData.id">
                            <input type="hidden" name="province_code" x-model="formData.province_code">
                            <input type="hidden" name="district_code" x-model="formData.district_code">
                            <input type="hidden" name="ward_code" x-model="formData.ward_code">
                            <input type="hidden" name="type" x-model="formData.type">
                            <input type="hidden" name="is_active" :value="formData.is_active ? 1 : 0">

                            <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Tên địa
                                        điểm*</label>
                                    <input type="text" id="name" name="name" x-model="formData.name"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1"
                                        required>
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Số điện
                                        thoại*</label>
                                    <input type="text" id="phone" name="phone" x-model="formData.phone"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1"
                                        required>
                                </div>
                                <div>
                                    <label for="province_code_display"
                                        class="block text-sm font-medium text-gray-700">Tỉnh/Thành phố*</label>
                                    <select id="province_code_display" x-model="formData.province_code"
                                        @change="updateDistricts()"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1"
                                        required>
                                        <option value="">Chọn Tỉnh/Thành phố</option>
                                        <template x-for="province in provinces" :key="province.code">
                                            <option :value="province.code" x-text="province.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label for="district_code_display"
                                        class="block text-sm font-medium text-gray-700">Quận/Huyện*</label>
                                    <select id="district_code_display" x-model="formData.district_code"
                                        @change="updateWards()"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1"
                                        :disabled="!formData.province_code" required>
                                        <option value="">Chọn Quận/Huyện</option>
                                        <template x-for="district in districts" :key="district.code">
                                            <option :value="district.code" x-text="district.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label for="type_display" class="block text-sm font-medium text-gray-700">Phân loại
                                        địa điểm*</label>
                                    <select id="type_display" x-model="formData.type"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1"
                                        required>
                                        <option value="store">Cửa hàng</option>
                                        <option value="warehouse">Kho</option>
                                        <option value="service_center">Trung tâm bảo hành</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="ward_code_display"
                                        class="block text-sm font-medium text-gray-700">Phường/Xã*</label>
                                    <select id="ward_code_display" x-model="formData.ward_code"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1"
                                        :disabled="!formData.district_code" required>
                                        <option value="">Chọn Phường/Xã</option>
                                        <template x-for="ward in wards" :key="ward.code">
                                            <option :value="ward.code" x-text="ward.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700">Địa chỉ chi tiết
                                        (số nhà, đường)*</label>
                                    <input type="text" id="address" name="address" x-model="formData.address"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 mt-1"
                                        placeholder="Ví dụ: 123 Lê Lợi" required>
                                </div>
                                <div class="sm:col-span-2 flex items-center">
                                    <label for="is_active_display" class="relative inline-block w-11 h-6 cursor-pointer">
                                        <input type="checkbox" id="is_active_display" x-model="formData.is_active"
                                            class="opacity-0 w-0 h-0">
                                        <span class="absolute inset-0 bg-gray-200 rounded-full transition-all duration-300"
                                            :class="{ 'bg-indigo-600': formData.is_active }"></span>
                                        <span
                                            class="absolute content-[''] h-4 w-4 left-1 bottom-1 bg-white rounded-full transition-all duration-300"
                                            :class="{ 'translate-x-5': formData.is_active }"></span>
                                    </label>
                                    <span class="ml-3 text-sm text-gray-600"
                                        x-text="formData.is_active ? 'Đang hoạt động' : 'Ngưng hoạt động'"></span>
                                </div>
                            </div>

                            <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                                <button type="submit"
                                    class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Lưu
                                </button>
                                <button @click.prevent="closeModal()" type="button"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                    Hủy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Xóa --}}
        <div x-show="isDeleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="isDeleteModalOpen" @click="closeDeleteModal()" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="isDeleteModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="delete-modal-title">
                                    Xóa địa điểm
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Bạn có chắc chắn muốn xóa địa điểm này không? Hành động này không thể được hoàn tác.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="confirmDelete()" type="button"
                            class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Xác nhận Xóa
                        </button>
                        <button @click="closeDeleteModal()" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Hủy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Script Alpine.js --}}
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('storeLocationManager', () => ({
                    isModalOpen: false,
                    isDeleteModalOpen: false,
                    isEditMode: false,
                    locationToDeleteId: null,
                    message: '',
                    messageType: 'success',

                    searchQuery: '',
                    filterType: '',
                    filterStatus: '',

                    currentPage: 1,
                    itemsPerPage: 10,

                    allLocations: @json($storeLocations),
                    provinces: @json($provinces),

                    districts: [],
                    wards: [],

                    formData: {
                        id: null,
                        name: '',
                        phone: '',
                        type: 'store',
                        is_active: true,
                        province_code: '',
                        district_code: '',
                        ward_code: '',
                        address: '',
                    },

                    get filteredLocations() {
                        const searchTerm = this.searchQuery.toLowerCase().trim();
                        return this.allLocations
                            .filter(location => {
                                const fullAddress = this.getFullAddress(location).toLowerCase();
                                const name = location.name.toLowerCase();
                                const phone = location.phone ? location.phone.toLowerCase() : '';
                                return name.includes(searchTerm) || fullAddress.includes(searchTerm) || phone.includes(searchTerm);
                            })
                            .filter(location => this.filterType === '' || location.type === this.filterType)
                            .filter(location => this.filterStatus === '' || String(location.is_active) === this.filterStatus);
                    },

                    get totalItems() {
                        return this.filteredLocations.length;
                    },

                    get totalPages() {
                        return Math.ceil(this.totalItems / this.itemsPerPage);
                    },

                    get paginatedLocations() {
                        const start = (this.currentPage - 1) * this.itemsPerPage;
                        const end = start + this.itemsPerPage;
                        return this.filteredLocations.slice(start, end);
                    },

                    get startItem() {
                        return this.totalItems > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
                    },

                    get endItem() {
                        return Math.min(this.currentPage * this.itemsPerPage, this.totalItems);
                    },

                    get pages() {
                        const pagesArray = [];
                        for (let i = 1; i <= this.totalPages; i++) {
                            pagesArray.push(i);
                        }
                        return pagesArray;
                    },

                    init() {
                        this.resetForm();
                        this.currentPage = 1;
                        console.log('Initialized with locations:', this.allLocations.length);
                        console.log('Sample location:', this.allLocations[0]);
                        console.log('Filtered locations:', this.filteredLocations.length);
                        console.log('Paginated locations:', this.paginatedLocations.length);
                    },

                    showMessage(msg, type = 'success') {
                        this.message = msg;
                        this.messageType = type;
                        setTimeout(() => {
                            this.message = '';
                        }, 3000);
                    },

                    resetForm() {
                        this.formData = {
                            id: null,
                            name: '',
                            phone: '',
                            type: 'store',
                            is_active: true,
                            province_code: '',
                            district_code: '',
                            ward_code: '',
                            address: '',
                        };
                        this.districts = [];
                        this.wards = [];
                    },

                    clearFilters() {
                        this.searchQuery = '';
                        this.filterType = '';
                        this.filterStatus = '';
                        this.currentPage = 1;
                    },

                    changePage(page) {
                        if (page < 1 || page > this.totalPages) return;
                        this.currentPage = page;
                    },

                    prevPage() {
                        if (this.currentPage > 1) this.currentPage--;
                    },

                    nextPage() {
                        if (this.currentPage < this.totalPages) this.currentPage++;
                    },

                    openModal() {
                        this.isEditMode = false;
                        this.resetForm();
                        this.isModalOpen = true;
                        this.$nextTick(() => {
                            this.formData = { ...this.formData };
                        });
                    },

                    closeModal() {
                        this.isModalOpen = false;
                        this.resetForm();
                    },

                    async editLocation(location) {
                        this.isEditMode = true;
                        this.resetForm();
                        try {
                            const response = await fetch(`/admin/store-locations/${location.id}/edit`);
                            if (!response.ok) {
                                const errorText = await response.text();
                                throw new Error(`Không thể lấy dữ liệu địa điểm. Trạng thái: ${response.status}. Phản hồi: ${errorText}`);
                            }
                            const fullLocation = await response.json();

                            this.formData = {
                                id: fullLocation.id,
                                name: fullLocation.name,
                                phone: fullLocation.phone || '',
                                type: fullLocation.type,
                                is_active: fullLocation.is_active,
                                province_code: fullLocation.province_code || '',
                                district_code: fullLocation.district_code || '',
                                ward_code: fullLocation.ward_code || '',
                                address: fullLocation.address || '',
                            };

                            // Tải lại các dropdown địa chỉ theo thứ tự
                            if (this.formData.province_code) {
                                await this.updateDistricts(true);
                                if (this.formData.district_code) {
                                    await new Promise(resolve => setTimeout(resolve, 100));
                                    await this.updateWards(true);
                                }
                            }

                            this.isModalOpen = true;
                        } catch (error) {
                            console.error('Lỗi khi lấy địa điểm để chỉnh sửa:', error);
                            this.showMessage(error.message || 'Lỗi khi tải thông tin cửa hàng để sửa.', 'error');
                        }
                    },

                    async updateDistricts(isInitialLoad = false) {
                        const provinceCode = this.formData.province_code;
                        this.districts = [];
                        this.wards = [];

                        if (!isInitialLoad) {
                            this.formData.district_code = '';
                            this.formData.ward_code = '';
                        }

                        if (!provinceCode) return;

                        try {
                            const response = await fetch(`/admin/api/districts?province_code=${provinceCode}`);
                            if (!response.ok) {
                                const errorText = await response.text();
                                throw new Error(`Không thể lấy danh sách quận/huyện. Trạng thái: ${response.status}. Phản hồi: ${errorText}`);
                            }
                            this.districts = await response.json();

                            if (isInitialLoad && this.formData.district_code) {
                                if (!this.districts.some(d => d.code == this.formData.district_code)) {
                                    this.formData.district_code = '';
                                    this.formData.ward_code = '';
                                }
                            }
                        } catch (error) {
                            console.error('Lỗi khi lấy danh sách quận/huyện:', error);
                            this.showMessage(error.message || 'Lỗi khi tải danh sách quận/huyện.', 'error');
                        }
                    },

                    async updateWards(isInitialLoad = false) {
                        const districtCode = this.formData.district_code;
                        this.wards = [];

                        if (!isInitialLoad) {
                            this.formData.ward_code = '';
                        }

                        if (!districtCode) return;

                        try {
                            const response = await fetch(`/admin/api/wards?district_code=${districtCode}`);
                            if (!response.ok) {
                                const errorText = await response.text();
                                throw new Error(`Không thể lấy danh sách phường/xã. Trạng thái: ${response.status}. Phản hồi: ${errorText}`);
                            }
                            this.wards = await response.json();

                            if (isInitialLoad && this.formData.ward_code) {
                                if (!this.wards.some(w => w.code == this.formData.ward_code)) {
                                    this.formData.ward_code = '';
                                }
                            }
                        } catch (error) {
                            console.error('Lỗi khi lấy danh sách phường/xã:', error);
                            this.showMessage(error.message || 'Lỗi khi tải danh sách phường/xã.', 'error');
                        }
                    },

                    getTypeName(type) {
                        switch (type) {
                            case 'store':
                                return 'Cửa hàng';
                            case 'warehouse':
                                return 'Kho';
                            case 'service_center':
                                return 'Trung tâm bảo hành';
                            default:
                                return 'Không xác định';
                        }
                    },

                    getFullAddress(location) {
                        const provinceName = location.province ? location.province.name : '';
                        const districtName = location.district ? location.district.name : '';
                        const wardName = location.ward ? location.ward.name : '';
                        return [location.address, wardName, districtName, provinceName].filter(Boolean).join(', ');
                    },

                    async saveLocation() {
                        if (!this.formData.name || !this.formData.address || !this.formData.phone ||
                            !this.formData.province_code || !this.formData.district_code || !this.formData.ward_code) {
                            this.showMessage('Vui lòng điền đầy đủ các trường bắt buộc có dấu *', 'error');
                            return;
                        }

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const method = this.isEditMode ? 'PUT' : 'POST';
                        const url = this.isEditMode ? `/admin/store-locations/${this.formData.id}` : '/admin/store-locations';

                        try {
                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(this.formData)
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                if (response.status === 422 && data.errors) {
                                    let errorMessages = Object.values(data.errors).map(e => e.join('<br>')).join('<br>');
                                    this.showMessage(`Lỗi nhập liệu:<br>${errorMessages}`, 'error');
                                } else {
                                    throw new Error(data.message || `Lỗi server: ${response.status}`);
                                }
                                return;
                            }

                            this.showMessage(data.message || (this.isEditMode ? 'Cập nhật thành công!' : 'Thêm mới thành công!'), 'success');
                            this.closeModal();
                            await this.fetchLocations();
                        } catch (error) {
                            console.error('Lỗi khi lưu địa điểm:', error);
                            this.showMessage(error.message || 'Lỗi kết nối hoặc xử lý server.', 'error');
                        }
                    },

                    openDeleteModal(id) {
                        this.locationToDeleteId = id;
                        this.isDeleteModalOpen = true;
                    },

                    closeDeleteModal() {
                        this.isDeleteModalOpen = false;
                        this.locationToDeleteId = null;
                    },

                    async confirmDelete() {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        try {
                            const response = await fetch(`/admin/store-locations/${this.locationToDeleteId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            });
                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({}));
                                throw new Error(errorData.message || `Không thể xóa địa điểm. Trạng thái: ${response.status}`);
                            }
                            const data = await response.json();

                            this.showMessage(data.message || 'Xóa địa điểm thành công!', 'success');
                            this.closeDeleteModal();
                            await this.fetchLocations();

                            if (this.paginatedLocations.length === 0 && this.currentPage > 1) {
                                this.currentPage--;
                            }
                        } catch (error) {
                            console.error('Lỗi khi xóa địa điểm:', error);
                            this.showMessage(error.message || 'Lỗi kết nối hoặc xử lý server khi xóa.', 'error');
                        }
                    },

                    async toggleActive(locationId, currentStatus) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        try {
                            const response = await fetch(`/admin/store-locations/${locationId}/toggle-active`, {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    is_active: !currentStatus
                                })
                            });
                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({}));
                                throw new Error(errorData.message || `Không thể thay đổi trạng thái. Trạng thái: ${response.status}`);
                            }
                            const data = await response.json();
                            this.showMessage(data.message, 'success');
                            await this.fetchLocations();
                        } catch (error) {
                            console.error('Lỗi khi thay đổi trạng thái hoạt động:', error);
                            this.showMessage(error.message || 'Lỗi kết nối hoặc xử lý server.', 'error');
                        }
                    },

                    async fetchLocations() {
                        try {
                            const response = await fetch('/admin/api/store-locations');
                            if (!response.ok) {
                                const errorText = await response.text();
                                throw new Error(`Không thể tải danh sách cửa hàng. Trạng thái: ${response.status}. Phản hồi: ${errorText}`);
                            }
                            this.allLocations = await response.json();
                        } catch (error) {
                            console.error('Lỗi khi tải danh sách cửa hàng:', error);
                            this.showMessage('Lỗi khi tải danh sách cửa hàng.', 'error');
                        }
                    }
                }));
            });
        </script>
    </div>
@endsection
