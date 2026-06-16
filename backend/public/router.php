<?php
// Routes all non-file requests through index.php (mimics .htaccess rewrite)
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && file_exists(__DIR__ . $path)) {
        return false; // serve css/js/images directly
    }
}
require __DIR__ . '/index.php';
