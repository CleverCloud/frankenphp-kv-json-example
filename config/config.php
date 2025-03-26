<?php

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    (new \Symfony\Component\Dotenv\Dotenv())->load(__DIR__ . '/../.env');
}

// Application configuration
return [
    'app_name' => $_ENV['APP_NAME'] ?? 'Task Manager',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    // Redis configuration
    'redis_host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'redis_port' => 6378,
    'redis_password' => $_ENV['REDIS_PASSWORD'] ?? null,
    'redis_tls' => false,
    // Keep this for backward compatibility
    'data_file' => __DIR__ . '/../data/tasks.json',
];
