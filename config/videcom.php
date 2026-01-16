<?php


return [
    'base_url' => env('VIDECOM_BASE_URL'),
    'token' => env('VIDECOM_API_TOKEN'),
    'proxy_secret' => env('VIDECOM_PROXY_SECRET'),
    'timeout' => (int)env('VIDECOM_TIMEOUT', 60),
];
