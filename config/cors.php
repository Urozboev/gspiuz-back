<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the settings for handling Cross-Origin Resource Sharing (CORS).
    | CORS allows your web application to communicate with resources on different domains.
    | These settings let you control which origins, methods, and headers are permitted.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Paths that should allow CORS. You can specify API routes or specific endpoints.
    'paths' => [env('API_PREFIX', 'api') . '/*', 'sanctum/csrf-cookie'],

    // HTTP methods that are allowed for CORS requests.
    // Example: ['GET', 'POST', 'PUT', 'DELETE']
    // Use '*' to allow all HTTP methods.
    'allowed_methods' => ['*'],

    // Origins that are allowed to access your resources.
    //
    // Prodda faqat saytning o'z domeni. Aslida brauzerdan to'g'ridan-to'g'ri
    // so'rov ketmaydi — Next.js Laravelga localhostdan boradi — lekin '*'
    // qoldirilsa, prefiksni bilgan har qanday sayt API'ni o'qiy olardi.
    //
    // Mahalliy ishlab chiqish uchun CORS_ALLOWED_ORIGINS ga vergul bilan
    // ajratib qo'shimcha manzil berish mumkin.
    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'https://gspi.uz,https://www.gspi.uz'))
    )),

    // Patterns for allowed origins using regular expressions.
    // Example: ['*.example.com'] to allow all subdomains of example.com.
    'allowed_origins_patterns' => [],

    // Headers that are allowed in the incoming CORS request.
    // Example: ['Content-Type', 'Authorization']
    // Use '*' to allow all headers.
    'allowed_headers' => ['*'],

    // Headers that should be exposed in the response for CORS requests.
    // Example: ['Authorization', 'X-Custom-Header']
    'exposed_headers' => [],

    // Maximum age (in seconds) the CORS request will be cached by the browser.
    // Set to 0 to disable caching.
    'max_age' => 0,

    // Whether to support credentials like cookies or HTTP authentication.
    // Set to true if your application needs to allow credentials in CORS requests.
    'supports_credentials' => false,

];

