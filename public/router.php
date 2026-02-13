<?php
/**
 * Router for PHP built-in server
 * Usage: php -S localhost:8000 -t public public/router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files directly if they exist
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route everything else to index.php
require __DIR__ . '/index.php';
