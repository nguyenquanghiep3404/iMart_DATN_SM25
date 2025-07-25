<?php

return [

    'default' => env('BROADCAST_CONNECTION', 'null'), // Hoặc 'reverb' nếu bạn muốn nó là mặc định cứng

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST'),
                'port' => env('REVERB_PORT'), // Bỏ giá trị mặc định '443' để dùng giá trị từ .env
                'scheme' => env('REVERB_SCHEME'), // Bỏ giá trị mặc định 'https' để dùng giá trị từ .env
                'useTLS' => env('REVERB_SCHEME') === 'https', // Điều kiện này sẽ tự động đúng nếu scheme là 'https'
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        // ... các kết nối khác ...

    ],

];
