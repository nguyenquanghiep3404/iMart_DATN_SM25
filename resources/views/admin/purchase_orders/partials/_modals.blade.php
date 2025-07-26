<div x-show="isModalOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div @click.outside="isModalOpen = false" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <header class="p-4 border-b flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800" x-text="modalTitle"></h3>
            <button @click="isModalOpen = false" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
        </header>
        <div class="p-5 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Lọc theo Tỉnh/Thành</label>
                    <select x-model="modalSelectedProvince" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tất cả tỉnh thành</option>
                        <template x-for="province in provinces" :key="province.code">
                            <option :value="province.code" x-text="province.name"></option>
                        </template>
                    </select>
                </div>
                 <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tìm kiếm</label>
                    <input type="text" x-model="modalSearchTerm" placeholder="Tên, địa chỉ, SĐT..." class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        <main class="flex-1 p-5 overflow-y-auto custom-scrollbar">
            <div class="space-y-3">
                <template x-if="filteredModalItems.length === 0">
                    <div class="text-center py-10 text-gray-500">Không có kết quả nào phù hợp.</div>
                </template>
                <template x-for="item in filteredModalItems" :key="item.id">
                     <div @click="selectModalItem(item)" class="p-4 border rounded-lg hover:bg-blue-50 hover:border-blue-400 cursor-pointer transition-colors">
                        <p class="font-bold text-gray-800" x-text="item.name"></p>
                        <p class="text-sm text-gray-600" x-text="item.fullAddress"></p>
                        <p x-show="item.phone" class="text-sm text-gray-500 mt-1"><i class="fas fa-phone-alt mr-2"></i><span x-text="item.phone"></span></p>
                    </div>
                </template>
            </div>
        </main>
    </div>
</div>
