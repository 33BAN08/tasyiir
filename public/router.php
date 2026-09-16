<?php
// Router script for PHP's built-in server: `php -S 0.0.0.0:8000 -t public public/router.php`
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false; // serve the requested static file as-is
}

require __DIR__ . '/index.php';
