{{-- Hiển thị thông báo (flash message) dưới dạng TOAST ở góc trên bên phải --}}
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
        @if (session('success'))
            <div id="toast-success" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg dark:text-gray-400 dark:bg-gray-800" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                    <i class="fas fa-check"></i>
                </div>
                <div class="ml-3 text-sm font-normal">{{ session('success') }}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" onclick="closeToast('toast-success')" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div id="toast-error" class="toast flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg dark:text-gray-400 dark:bg-gray-800" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                     <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="ml-3 text-sm font-normal">{{ session('error') }}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" onclick="closeToast('toast-error')" aria-label="Close">
                    <span class="sr-only">Close</span>
                     <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tự động đóng toast sau 5 giây
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(toast) {
            setTimeout(function() {
                if (toast && toast.parentNode) {
                    toast.style.transition = 'opacity 0.5s ease-out';
                    toast.style.opacity = '0';
                    setTimeout(function() {
                        if (toast && toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 500);
                }
            }, 5000);
        });
    });

    // Hàm đóng toast khi click nút X
    function closeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.style.transition = 'opacity 0.5s ease-out';
            toast.style.opacity = '0';
            setTimeout(function() {
                if (toast && toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 500);
        }
    }
    </script>