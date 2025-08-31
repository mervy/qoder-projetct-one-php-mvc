<?php

/**
 * View Configuration
 */

return [
    'paths' => [
        BASE_PATH . '/src/Resources/Views',
    ],
    
    'cache' => env('VIEW_CACHE', false),
    'compiled_path' => env('VIEW_COMPILED_PATH', BASE_PATH . '/storage/views'),
    
    'debug' => env('APP_DEBUG', false),
];