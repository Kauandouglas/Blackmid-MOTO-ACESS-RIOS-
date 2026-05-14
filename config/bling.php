<?php

return [
    'base_url' => env('BLING_BASE_URL', 'https://api.bling.com.br/Api/v3'),
    'access_token' => env('BLING_ACCESS_TOKEN'),
    'refresh_token' => env('BLING_REFRESH_TOKEN'),
    'client_id' => env('BLING_CLIENT_ID'),
    'client_secret' => env('BLING_CLIENT_SECRET'),
    'timeout' => (int) env('BLING_TIMEOUT', 20),
];
