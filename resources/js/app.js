import './bootstrap';
// import './chat';
import './admin_chat';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js'; // Nếu bạn không tải qua CDN
// window.Pusher = Pusher; // Nếu bạn muốn Pusher có sẵn global
// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     // ... các cấu hình khác
// });
