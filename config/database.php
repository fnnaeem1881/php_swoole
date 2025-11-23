<?php
return [
    'host' => App\Core\Helpers::env('DB_HOST', '127.0.0.1'),
    'port' => App\Core\Helpers::env('DB_PORT', 3306),
    'database' => App\Core\Helpers::env('DB_DATABASE', 'testdb'),
    'username' => App\Core\Helpers::env('DB_USERNAME', 'root'),
    'password' => App\Core\Helpers::env('DB_PASSWORD', ''),
];