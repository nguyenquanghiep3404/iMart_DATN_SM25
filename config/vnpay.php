<?php

return [
    'url' => env('VNPAY_URL'),
    'return_url' => env('VNPAY_RETURN_URL'),
    'ipn_url' => env('VNPAY_IPN_URL'),
    'tmn_code' => env('VNPAY_TMN_CODE'),
    'hash_secret' => env('VNPAY_HASH_SECRET'),
];