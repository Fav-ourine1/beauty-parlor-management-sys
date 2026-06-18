<?php
/**
 * Web Routes — serve HTML views
 */

// ── Public pages ──────────────────────────────────────────────
$router->get('/about',   ['PagesController', 'about']);
$router->get('/contact', ['PagesController', 'contact']);

// ── Guest pages ───────────────────────────────────────────────
$router->get('/',          ['AuthController', 'showLogin']);
$router->get('/login',     ['AuthController', 'showLogin'],    [fn() => Middleware::guest()]);
$router->post('/login',    ['AuthController', 'login'],        [fn() => Middleware::guest()]);
$router->get('/register',  ['AuthController', 'showRegister'], [fn() => Middleware::guest()]);
$router->post('/register', ['AuthController', 'register'],     [fn() => Middleware::guest()]);
$router->post('/logout',   ['AuthController', 'logout'],       [fn() => Middleware::auth()]);

// ── Client portal ─────────────────────────────────────────────
$router->get('/client/dashboard', ['ClientController', 'dashboard'],   [fn() => Middleware::role('client')]);
$router->get('/client/book',      ['ClientController', 'bookingForm'], [fn() => Middleware::role('client')]);

// ── Staff portal ──────────────────────────────────────────────
$router->get('/staff/dashboard', ['StaffController', 'dashboard'], [fn() => Middleware::staff()]);
$router->get('/staff/schedule',  ['StaffController', 'schedule'],  [fn() => Middleware::staff()]);

// ── Admin portal ──────────────────────────────────────────────
$router->get('/admin/dashboard',    ['ReportController', 'adminDashboard'], [fn() => Middleware::admin()]);
$router->get('/admin/appointments', ['AdminController',  'appointments'],   [fn() => Middleware::admin()]);
$router->get('/admin/staff',        ['AdminController',  'staff'],          [fn() => Middleware::admin()]);
$router->get('/admin/services',     ['AdminController',  'services'],       [fn() => Middleware::admin()]);
$router->get('/admin/inventory',    ['AdminController',  'inventory'],      [fn() => Middleware::admin()]);
$router->get('/admin/reports',      ['AdminController',  'reports'],        [fn() => Middleware::admin()]);
