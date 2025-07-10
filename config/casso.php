<?php
return [
    'api_key' => env('CASSO_API_KEY'),
    'secure_token' => env('CASSO_API_KEY'), // Giữ lại để tương thích code cũ nếu cần
    'webhook_secret' => env('CASSO_WEBHOOK_SECRET'), // Thêm dòng này
];
