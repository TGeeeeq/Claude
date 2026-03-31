<?php
/**
 * Database configuration
 * Credentials loaded from environment variables
 */

require_once __DIR__ . '/env.php';

return [
    'smtp' => [
        'Host'       => env('SMTP_HOST', 'smtp.forpsi.com'),
        'SMTPAuth'   => true,
        'Username'   => env('SMTP_USERNAME', 'info@nechmerust.org'),
        'Password'   => env('SMTP_PASSWORD', ''),
        'SMTPSecure' => 'tls',
        'Port'       => (int)env('SMTP_PORT', 587),
        'FromEmail'  => env('SMTP_FROM_EMAIL', 'info@nechmerust.org'),
        'FromName'   => env('SMTP_FROM_NAME', 'Nech mě růst'),
    ],
    'database' => [
        'host' => env('DB_HOST', 'localhost'),
        'name' => env('DB_NAME', ''),
        'user' => env('DB_USER', ''),
        'pass' => env('DB_PASS', ''),
    ]
];
?>