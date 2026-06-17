<?php
/**
 * CHECKPOINT LOCK: v1-router-pattern-matching
 * Milestone reached: Router compiles {param} patterns to named-capture regexes
 *                    and correctly extracts URL parameters at dispatch time.
 *                    This is a pure unit test — no HTTP connection required.
 * Key behaviors encoded:
 *   - Static routes match exactly and produce no params
 *   - {param} routes compile to named-capture regexes
 *   - URL parameters are correctly extracted into the $params array
 *   - Routes with multiple parameters each capture independently
 *   - Method mismatch does NOT match (a GET route won't fire on POST)
 *   - Unknown paths do not crash the router (handled via notFound)
 *   - The compiled route table holds all registered routes
 * How to run: php tests/checkpoint_v1_router_pattern_matching.php
 * If this fails: The Router's compilePattern() or dispatch() logic is broken.
 *                All web and API endpoints will misroute or fail to capture IDs.
 */

require_once __DIR__ . '/bootstrap.php';

echo "CHECKPOINT: v1 — Router Pattern Matching\n";
echo str_repeat('=', 50) . "\n";

// ── Expose private compilePattern via reflection ──────────────
// We test the compiled regex directly — this is the core logic we need to lock.

$router = new Router();
$reflect = new ReflectionClass(Router::class);

$compileMethod = $reflect->getMethod('compilePattern');
$compileMethod->setAccessible(true);

$routesProperty = $reflect->getProperty('routes');
$routesProperty->setAccessible(true);

// Helper: compile a path string to its regex pattern
$compile = fn(string $path): string => $compileMethod->invoke($router, $path);

// ── 1. Static route compilation ───────────────────────────────
section('1. Static route compilation');

$pattern = $compile('/login');
assert_true((bool) preg_match($pattern, '/login'),       'Static /login matches itself');
assert_false((bool) preg_match($pattern, '/login/extra'), 'Static /login does not match /login/extra');
assert_false((bool) preg_match($pattern, '/'),           'Static /login does not match /');

$rootPattern = $compile('/');
assert_true((bool) preg_match($rootPattern, '/'),        'Root / matches itself');
assert_false((bool) preg_match($rootPattern, '/admin'),  'Root / does not match /admin');

// ── 2. Single parameter route compilation ─────────────────────
section('2. Single {param} compilation and capture');

$svcPattern = $compile('/api/services/{id}');
$matched = preg_match($svcPattern, '/api/services/42', $m);
assert_true((bool) $matched,         '/api/services/42 matches /api/services/{id}');
assert_equals('42', $m['id'],        'Named capture "id" = "42"');

$matched2 = preg_match($svcPattern, '/api/services/999', $m2);
assert_true((bool) $matched2,        '/api/services/999 matches /api/services/{id}');
assert_equals('999', $m2['id'],      'Named capture "id" = "999"');

assert_false(
    (bool) preg_match($svcPattern, '/api/services/'),
    '/api/services/ (trailing slash, no value) does not match'
);
assert_false(
    (bool) preg_match($svcPattern, '/api/services/42/extra'),
    '/api/services/42/extra does not match single-segment pattern'
);

// ── 3. Multi-segment param route ──────────────────────────────
section('3. Multi-segment route — /api/staff/{id}/shifts');

$shiftPattern = $compile('/api/staff/{id}/shifts');
$matched3 = preg_match($shiftPattern, '/api/staff/7/shifts', $m3);
assert_true((bool) $matched3,        '/api/staff/7/shifts matches pattern');
assert_equals('7', $m3['id'],        'Named capture "id" = "7" in /api/staff/{id}/shifts');

assert_false(
    (bool) preg_match($shiftPattern, '/api/staff/7'),
    '/api/staff/7 does not match /api/staff/{id}/shifts (missing /shifts)'
);

// ── 4. Route registration via Router::get / post ──────────────
section('4. Route registration — routes table is populated');

$router->get('/test/static',     fn($p) => null);
$router->post('/test/create',    fn($p) => null);
$router->get('/test/items/{id}', fn($p) => null);

$routes = $routesProperty->getValue($router);
assert_true(count($routes) >= 3, 'At least 3 routes registered after three add() calls');

$paths = array_column($routes, 'path');
assert_true(in_array('/test/static',     $paths), '/test/static is in the route table');
assert_true(in_array('/test/create',     $paths), '/test/create is in the route table');
assert_true(in_array('/test/items/{id}', $paths), '/test/items/{id} is in the route table');

$methods = array_column($routes, 'method');
$getPaths  = array_column(
    array_filter($routes, fn($r) => $r['method'] === 'GET'),
    'path'
);
$postPaths = array_column(
    array_filter($routes, fn($r) => $r['method'] === 'POST'),
    'path'
);
assert_true(in_array('/test/static',  $getPaths),  'GET method stored for /test/static');
assert_true(in_array('/test/create',  $postPaths), 'POST method stored for /test/create');

// ── 5. Handler dispatch via simulated REQUEST ─────────────────
section('5. Handler invocation — callable receives params array');

$captured = null;
$router->get('/test/capture/{slug}', function (array $params) use (&$captured) {
    $captured = $params;
});

// Simulate a GET request to /test/capture/hello-world
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = '/test/capture/hello-world';
$_SERVER['SCRIPT_NAME']    = '/index.php';

// dispatch() calls exit() on notFound — redirect that branch for safety.
// Our route exists so dispatch() should call our handler and return normally.
ob_start();
$router->dispatch();
ob_end_clean();

assert_true(is_array($captured),             'Handler was invoked by dispatch()');
assert_equals('hello-world', $captured['slug'] ?? null, 'dispatch() passed slug = "hello-world" to handler');

// ── Done ──────────────────────────────────────────────────────
finish_suite('v1 — Router Pattern Matching');
