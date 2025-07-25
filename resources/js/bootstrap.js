// resources/js/bootstrap.js

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Pusher.js (PusherJS là thư viện JS client cho Reverb)
import Pusher from 'pusher-js';
window.Pusher = Pusher; // Gán Pusher vào window để Laravel Echo có thể tìm thấy nó

// Import Laravel Echo
import Echo from 'laravel-echo';

// Khởi tạo Echo
window.Echo = new Echo({
    broadcaster: 'reverb', // Sử dụng broadcaster là Reverb
    key: import.meta.env.VITE_REVERB_APP_KEY, // Lấy key từ biến môi trường Vite
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname, // Host của Reverb server
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080, // Port WebSocket
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080, // Port WebSocket an toàn (SSL)
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https', // Buộc sử dụng TLS nếu scheme là https
    disableStats: true, // Tắt gửi thống kê
    enabledTransports: ['ws', 'wss'], // Chỉ định các transport được bật
    authEndpoint: '/broadcasting/auth', // Endpoint xác thực kênh riêng tư
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    },
});

console.log('bootstrap.js executed: window.Pusher and window.Echo should be defined.');
console.log('Echo instance:', window.Echo); // Kiểm tra đối tượng Echo đã được tạo
