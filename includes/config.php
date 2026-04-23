<?php
/**
 * Application configuration — edit DB credentials for your environment (XAMPP/WAMP).
 */
declare(strict_types=1);

// Database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'hospital_management');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Paths (no trailing slash)
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'reports');

// Session cookie name
define('SESSION_NAME', 'HMS_SESSION');

// Max upload size for reports (bytes) — 5 MB
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
