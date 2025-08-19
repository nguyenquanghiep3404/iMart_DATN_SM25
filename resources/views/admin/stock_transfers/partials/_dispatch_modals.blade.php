<!-- Manual/Scanning Modal -->
<div x-show="isScanningModalOpen" x-cloak x-transition class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div @click.outside="closeScanningModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <header class="p-5 border-b flex justify-between items-center">
            <div>
                 <h3 class="text-xl font-bold text-gray-800">Quét & Nhập IMEI/Serial</h3>
                 <p x-show="currentItemForScanning" x-text="currentItemForScanning.name" class="text-sm text-gray-600"></p>
            </div>
            <button @click="closeScanningModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
        </header>
        <main class="flex-1 p-5 grid grid-cols-1 md:grid-cols-2 gap-5 overflow-hidden">
            <!-- Left: Input Area -->
            <div class="flex flex-col space-y-3">
                 <form @submit.prevent="addSerial()">
                     <label for="serial-input" class="font-semibold text-gray-700">
                         Quét mã cho sản phẩm (<span x-text="scannedData[currentItemForScanning?.id]?.length || 0"></span>/<span x-text="currentItemForScanning?.quantity || 0"></span>)
                     </label>
                     <input type="text" id="serial-input" x-model="currentSerialInput"
                            class="w-full p-3 mt-1 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Nhập tay hoặc quét barcode...">
                     <p x-show="scanError" x-text="scanError" class="text-sm text-red-600 h-5 mt-1"></p>
                 </form>
                 <div class="flex-1 flex flex-col space-y-2">
                     <button @click="addSerial()" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                         <i class="fas fa-keyboard mr-2"></i> Thêm
                     </button>
                     <button @click="openBarcodeScanner()" class="w-full bg-gray-700 text-white font-bold py-3 rounded-lg hover:bg-gray-800 transition-colors">
                         <i class="fas fa-camera mr-2"></i> Mở Camera
                     </button>
                 </div>
            </div>
            <!-- Right: Scanned List -->
            <div class="bg-gray-50 rounded-lg flex flex-col overflow-hidden">
                <p class="p-3 font-semibold text-gray-700 border-b">Danh sách đã quét</p>
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
                    <template x-if="!scannedData[currentItemForScanning?.id] || scannedData[currentItemForScanning?.id].length === 0">
                        <p class="text-center text-gray-500 pt-10">Chưa có mã nào được quét.</p>
                    </template>
                    <template x-for="serial in scannedData[currentItemForScanning?.id]" :key="serial">
                         <div class="flex justify-between items-center bg-white p-2 rounded-md border text-sm">
                             <span class="font-mono text-gray-700" x-text="serial"></span>
                             <button @click="removeSerial(currentItemForScanning.id, serial)" class="text-red-500 hover:text-red-700 text-xs">
                                 <i class="fas fa-trash-alt"></i>
                             </button>
                         </div>
                    </template>
                </div>
            </div>
        </main>
        <footer class="p-4 bg-gray-50 border-t text-right">
            <button @click="closeScanningModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold">Đóng</button>
        </footer>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div x-show="isScannerOpen" x-cloak x-transition class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div @click.outside="closeBarcodeScanner()" class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col">
        <header class="p-4 bg-indigo-600 text-white rounded-t-xl">
            <h3 class="text-xl font-bold text-center">Quét Barcode / QR Code</h3>
        </header>
        <main class="p-4 flex-1">
            <div id="reader-container" class="relative">
                <div id="reader" class="w-full border-4 border-gray-300 rounded-lg overflow-hidden transition-all duration-300"></div>
                <div id="loading-message" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-70 text-white hidden">
                    <i class="fas fa-spinner fa-spin text-4xl mb-3"></i>
                    <p>Đang khởi tạo camera...</p>
                </div>
            </div>
            <div id="scan-error-message" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg hidden"></div>
        </main>
        <footer class="p-4 bg-gray-50 border-t rounded-b-xl flex justify-end">
            <button @click="closeBarcodeScanner()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                Đóng
            </button>
        </footer>
    </div>
</div>

<!-- Success Modal -->
<div x-show="isSuccessModalOpen" x-cloak x-transition class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div @click.outside="closeSuccessModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-md text-center p-8">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
             <i class="fas fa-check text-green-600 text-3xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mt-5">Thành công!</h3>
        <p x-text="successMessage" class="text-gray-600 mt-2"></p>
        <button @click="closeSuccessModal()" class="mt-8 w-full px-5 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold transition-colors">OK</button>
    </div>
</div>
