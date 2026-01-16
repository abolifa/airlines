<?php


return [
    'base_url' => env('VIDECOM_BASE_URL'),
    'token' => env('VIDECOM_API_TOKEN'),
    'proxy_secret' => env('VIDECOM_PROXY_SECRET'),
    'timeout' => (int)env('VIDECOM_TIMEOUT', 60),
    
    'session_cookie_minutes' => env('VIDECOM_SESSION_COOKIE_MINUTES', 30),
    'session_history_minutes' => env('VIDECOM_SESSION_HISTORY_MINUTES', 120),
];
