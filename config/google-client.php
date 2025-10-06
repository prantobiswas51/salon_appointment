<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google API client with SSL handling options
    |
    */

    'http_client_options' => [
        // For development environments with SSL certificate issues
        'curl' => [
            CURLOPT_SSL_VERIFYPEER => env('GOOGLE_SSL_VERIFY_PEER', true),
            CURLOPT_SSL_VERIFYHOST => env('GOOGLE_SSL_VERIFY_HOST', 2),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]
    ],
];