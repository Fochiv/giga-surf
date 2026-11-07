<?php
/**
 * Copy this file to config/local.php only for shared-hosting installations.
 * Prefer keeping this file outside the public web root when your host allows it.
 */
return [
    'app_env' => 'production',
    'base_url' => 'https://example.com',
    'db' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'name' => 'your_database',
        'user' => 'your_database_user',
        'password' => 'change_this',
        'charset' => 'utf8mb4',
    ],
    'fapshi' => [
        'apiuser' => '',
        'apikey' => '',
        'webhook_secret' => '',
        'base_url' => 'https://live.fapshi.com',
    ],
];
