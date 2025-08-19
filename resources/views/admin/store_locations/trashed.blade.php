@extends('admin.layouts.app')

@section('title', 'Quản lý Cửa hàng đã xóa mềm')

@section('content')
<div class="px-4 sm:px-6 md:px-8 py-8" x-data="trashedStoreLocationManager()" x-init="init()">
    <div class="container mx-auto max-w-7xl">
        <header class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-800">Cửa hàng đã xóa mềm</h1>
            <a href="{{ route('admin.store-locations.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại quản lý cửa hàng
            </a>
        </header>

        <div x-show="message" x-cloak
             :class="{'bg-green-100 border-green-400 text-green-700': messageType === 'success', 'bg-red-100 border-red-400 text-red-700': messageType === 'error'}"
             class="border px-4 py-3 rounded relative mb-4" role="alert">
            <span x-html="message"></span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="message = ''">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center w-full">
                    <h3 class="text-xl font-semibold text-gray-900">Danh sách cửa hàng đã xóa (<span x-text="filteredLocations.length"></span>)</h3>
                </div>
            </div>

            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-4 mb-6 md:items-end">
                    <div class="flex-grow grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Tìm kiếm</label>
                            <input type="text" id="search" x-model.debounce.500ms="searchQuery" @input="currentPage = 1" placeholder="Tìm theo tên, địa chỉ, SĐT..."
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
                    </div>
                    <div class="flex-shrink-0 flex items-center gap-2">
                           <button @click="clearFilters()" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times mr-2"></i>Xoá bộ lọc
                            </button>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">STT</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Tên Địa điểm</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-3/12">Địa chỉ</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Số điện thoại</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Phân loại</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Ngày xóa</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-2/12">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-if="paginatedLocations.length === 0">
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        Không tìm thấy địa điểm đã xóa nào.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(location, index) in paginatedLocations" :key="location.id">
                                <tr>
                                    <td x-text="(currentPage - 1) * itemsPerPage + index + 1" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"></td>
                                    <td x-text="location.name" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"></td>
                                    <td class="px-6 py-4 whitespace-normal text-sm text-gray-600">
                                        <span x-text="location.address"></span>
                                        <template x-if="location.ward">, <span x-text="location.ward.name"></span></template>
                                        <template x-if="location.district">, <span x-text="location.district.name"></span></template>
                                        <template x-if="location.province">, <span x-text="location.province.name"></span></template>
                                    </td>
                                    <td x-text="location.phone || 'N/A'" class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"></td>
                                    <td x-text="getTypeName(location.type)" class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"></td>
                                    <td x-text="formatDate(location.deleted_at)" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="inline-flex space-x-2">
                                            <button @click="openRestoreModal(location.id)" type="button" class="text-green-600 hover:text-green-900" title="Khôi phục">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                            <button @click="openForceDeleteModal(location.id)" type="button" class="text-red-600 hover:text-red-900" title="Xóa vĩnh viễn">
                                                <i class="fas fa-times-circle"></i>
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
                        Hiển thị <span x-text="startItem"></span> đến <span x-text="endItem"></span> trong tổng số <span x-text="totalItems"></span> mục
                    </div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button @click="prevPage()" :disabled="currentPage === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
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
                        <button @click="nextPage()" :disabled="currentPage === totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                            <span class="sr-only">Tiếp</span>
                            <i class="fas fa-chevron-right h-5 w-5"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Khôi phục --}}
    <div x-show="isRestoreModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="restore-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isRestoreModalOpen" @click="closeRestoreModal()" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="isRestoreModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-undo-alt text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="restore-modal-title">
                                Khôi phục địa điểm
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Bạn có chắc chắn muốn khôi phục địa điểm này không?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="confirmRestore()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Xác nhận Khôi phục
                    </button>
                    <button @click="closeRestoreModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Xóa vĩnh viễn --}}
    <div x-show="isForceDeleteModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="force-delete-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isForceDeleteModalOpen" @click="closeForceDeleteModal()" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="isForceDeleteModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-circle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="force-delete-modal-title">
                                Xóa vĩnh viễn địa điểm
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Bạn có chắc chắn muốn xóa **vĩnh viễn** địa điểm này không? Hành động này không thể được hoàn tác.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="confirmForceDelete()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Xác nhận Xóa Vĩnh viễn
                    </button>
                    <button @click="closeForceDeleteModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('trashedStoreLocationManager', () => ({
                isRestoreModalOpen: false,
                isForceDeleteModalOpen: false,
                locationToRestoreId: null,
                locationToForceDeleteId: null,
                message: '',
                messageType: 'success',

                searchQuery: '',
                filterType: '',

                currentPage: 1,
                itemsPerPage: 5,

                allLocations: @json($trashedLocations),

                districts: [],
                wards: [],

                get filteredLocations() {
                    const searchTerm = this.searchQuery.toLowerCase().trim();
                    return this.allLocations
                        .filter(location => {
                            const fullAddress = this.getFullAddress(location).toLowerCase();
                            const name = location.name.toLowerCase();
                            const phone = location.phone ? location.phone.toLowerCase() : '';
                            return name.includes(searchTerm) || fullAddress.includes(searchTerm) || phone.includes(searchTerm);
                        })
                        .filter(location => this.filterType === '' || location.type === this.filterType);
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

                // *** ĐIỀU CHỈNH CHÍNH Ở ĐÂY ***
                init() {
                    this.currentPage = 1;
                    // Flash messages từ Laravel Session vẫn có thể được hiển thị ở đây
                    @if(Session::has('success'))
                        this.showMessage('{{ Session::get('success') }}', 'success');
                    @endif
                    @if(Session::has('error'))
                        this.showMessage('{{ Session::get('error') }}', 'error');
                    @endif
                },

                showMessage(msg, type = 'success') {
                    this.message = msg;
                    this.messageType = type;
                    setTimeout(() => {
                        this.message = '';
                    }, 3000);
                },

                clearFilters() {
                    this.searchQuery = '';
                    this.filterType = '';
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

                getTypeName(type) {
                    switch (type) {
                        case 'store': return 'Cửa hàng';
                        case 'warehouse': return 'Kho';
                        case 'service_center': return 'Trung tâm bảo hành';
                        default: return 'Không xác định';
                    }
                },

                getFullAddress(location) {
                    const provinceName = location.province ? location.province.name : '';
                    const districtName = location.district ? location.district.name : '';
                    const wardName = location.ward ? location.ward.name : '';
                    return [location.address, wardName, districtName, provinceName].filter(Boolean).join(', ');
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                    return new Date(dateString).toLocaleString('vi-VN', options);
                },

                openRestoreModal(id) {
                    this.locationToRestoreId = id;
                    this.isRestoreModalOpen = true;
                },
                closeRestoreModal() {
                    this.isRestoreModalOpen = false;
                    this.locationToRestoreId = null;
                },
                async confirmRestore() {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const response = await fetch(`/admin/store-locations/${this.locationToRestoreId}/restore`, {
                            method: 'POST', // Phương thức POST cho restore
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || `Không thể khôi phục địa điểm. Trạng thái: ${response.status}`);
                        }

                        this.showMessage(data.message || 'Khôi phục địa điểm thành công!', 'success');
                        this.closeRestoreModal();
                        // Loại bỏ mục đã khôi phục khỏi danh sách hiển thị NGAY LẬP TỨC
                        const updatedLocations = this.allLocations.filter(loc => loc.id !== this.locationToRestoreId);
                        this.allLocations = [...updatedLocations]; // Kích hoạt reactivity

                        if (this.paginatedLocations.length === 0 && this.currentPage > 1) {
                            this.currentPage--;
                        }
                    } catch (error) {
                        console.error('Lỗi khi khôi phục địa điểm:', error);
                        this.showMessage(error.message || 'Lỗi kết nối hoặc xử lý server khi khôi phục.', 'error');
                    }
                },

                openForceDeleteModal(id) {
                    this.locationToForceDeleteId = id;
                    this.isForceDeleteModalOpen = true;
                },
                closeForceDeleteModal() {
                    this.isForceDeleteModalOpen = false;
                    this.locationToForceDeleteId = null;
                },
                async confirmForceDelete() {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const response = await fetch(`/admin/store-locations/${this.locationToForceDeleteId}/force-delete`, {
                            method: 'DELETE', // Phương thức DELETE cho force-delete
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                             throw new Error(data.message || `Không thể xóa vĩnh viễn địa điểm. Trạng thái: ${response.status}`);
                        }

                        this.showMessage(data.message || 'Xóa vĩnh viễn địa điểm thành công!', 'success');
                        this.closeForceDeleteModal();
                        // Loại bỏ mục đã xóa vĩnh viễn khỏi danh sách NGAY LẬP TỨC
                        const updatedLocations = this.allLocations.filter(loc => loc.id !== this.locationToForceDeleteId);
                        this.allLocations = [...updatedLocations]; // Kích hoạt reactivity

                        if (this.paginatedLocations.length === 0 && this.currentPage > 1) {
                            this.currentPage--;
                        }
                    } catch (error) {
                        console.error('Lỗi khi xóa vĩnh viễn địa điểm:', error);
                        this.showMessage(error.message || 'Lỗi kết nối hoặc xử lý server khi xóa vĩnh viễn.', 'error');
                    }
                },
            }));
        });
    </script>
</div>
@endsection
