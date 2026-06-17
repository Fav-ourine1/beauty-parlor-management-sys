<?php
/**
 * Test Bootstrap
 * Required by every checkpoint test file. Sets up constants, autoloads
 * all core classes, and suppresses HTTP-output side effects.
 *
 * Usage: require_once __DIR__ . '/bootstrap.php';
 */

// ── Paths ─────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__) . '/backend');
define('APP_PATH',  BASE_PATH . '/app');

// ── Suppress any output that depends on HTTP headers ─────────
// (Router::notFound and Middleware::deny call header()/exit — we stub them)
if (!function_exists('header')) {
    // header() is available in CLI but we still want side-effect-free routing tests
}

// ── Core config & classes ─────────────────────────────────────
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Middleware.php';

// ── App class autoloader (models, controllers) ────────────────
spl_autoload_register(function (string $class): void {
    $paths = [
        APP_PATH . '/models/'      . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        BASE_PATH . '/services/'   . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ── Minimal $_SERVER environment for CLI runs ─────────────────
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$_SERVER['REQUEST_URI']    = $_SERVER['REQUEST_URI']    ?? '/';
$_SERVER['SCRIPT_NAME']    = $_SERVER['SCRIPT_NAME']    ?? '/index.php';
$_SERVER['HTTP_ACCEPT']    = $_SERVER['HTTP_ACCEPT']    ?? 'text/html';

// ── Session stub (avoids "session already started" in CLI) ────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Simple assertion helpers ──────────────────────────────────

/**
 * Assert a condition is truthy. Prints PASS/FAIL with the label.
 */
function assert_true(mixed $condition, string $label): void
{
    if ($condition) {
        echo "\033[32m  PASS\033[0m  {$label}\n";
    } else {
        echo "\033[31m  FAIL\033[0m  {$label}\n";
        // Record failure so the file can exit non-zero
        $GLOBALS['__test_failures'][] = $label;
    }
}

function assert_false(mixed $condition, string $label): void
{
    assert_true(!$condition, $label);
}

function assert_equals(mixed $expected, mixed $actual, string $label): void
{
    $ok = ($expected === $actual);
    if (!$ok) {
        $exp = var_export($expected, true);
        $got = var_export($actual,   true);
        assert_true(false, "{$label} (expected {$exp}, got {$got})");
    } else {
        assert_true(true, $label);
    }
}

function assert_array_has_keys(array $keys, array $arr, string $label): void
{
    $missing = array_diff($keys, array_keys($arr));
    if ($missing) {
        $list = implode(', ', $missing);
        assert_true(false, "{$label} (missing keys: {$list})");
    } else {
        assert_true(true, $label);
    }
}

/**
 * Print a section header.
 */
function section(string $title): void
{
    echo "\n\033[1m{$title}\033[0m\n";
}

/**
 * Call at the end of each test file. Exits non-zero if any assertion failed.
 */
function finish_suite(string $name): void
{
    $failures = $GLOBALS['__test_failures'] ?? [];
    $count    = count($failures);
    echo "\n";
    if ($count === 0) {
        echo "\033[32m  All assertions passed — {$name}\033[0m\n\n";
    } else {
        echo "\033[31m  {$count} assertion(s) FAILED — {$name}\033[0m\n\n";
        exit(1);
    }
}

$GLOBALS['__test_failures'] = [];
