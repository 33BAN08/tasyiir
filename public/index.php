<?php

/**
 * PlanZeen — front controller.
 *
 * Phase 1 note: this project runs on a tiny zero-dependency PHP router +
 * Blade-lite view engine instead of a real Laravel installation, because
 * Composer/Packagist could not be reached from the build sandbox. See
 * /NOTES.md for the full explanation and the Phase-2 migration path.
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require BASE_PATH . '/app/helpers.php';

App\View\BladeLite::boot(
    BASE_PATH . '/resources/views',
    BASE_PATH . '/storage/framework/views'
);

require BASE_PATH . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

try {
    echo App\Routing\Route::dispatch($method, $uri);
} catch (\Throwable $e) {
    http_response_code(500);
    if (getenv('APP_DEBUG') === 'false') {
        echo '<h1>500 - Server Error</h1>';
    } else {
        echo '<pre style="direction:ltr;text-align:left;padding:20px;background:#1e1e2e;color:#f38ba8;font-family:monospace;white-space:pre-wrap;">';
        echo htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    }
}
