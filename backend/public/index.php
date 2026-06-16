
<?php
/**
 * Mbagathi Beauty Parlour Management System
 * Front Controller — all HTTP requests route through here.
 */
 
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
 
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Middleware.php';
 
// Auto-load app classes (models, controllers, services, middleware)
spl_autoload_register(function (string $class): void {
    $paths = [
        APP_PATH . '/models/'      . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/services/'   . $class . '.php',
        BASE_PATH . '/middleware/' . $class . '.php',
        BASE_PATH . '/helpers/'    . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
 
session_start();
 
$router = new Router();
require_once BASE_PATH . '/routes/api.php';
require_once BASE_PATH . '/routes/web.php';
$router->dispatch();
 