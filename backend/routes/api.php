<?php
/**
 * API Routes  — prefix: /api
 * All handlers return JSON.
 */

// ── Auth ──────────────────────────────────────────────────────
$router->post('/api/auth/register', ['AuthController', 'register']);
$router->post('/api/auth/login',    ['AuthController', 'login']);
$router->post('/api/auth/logout',   ['AuthController', 'logout'], [fn() => Middleware::auth()]);

// ── Service categories ────────────────────────────────────────
$router->get('/api/service-categories',       ['ServiceController', 'categories']);
$router->post('/api/service-categories',      ['ServiceController', 'storeCategory'], [fn() => Middleware::admin()]);

// ── Services ─────────────────────────────────────────────────
$router->get('/api/services',                 ['ServiceController', 'index']);
$router->get('/api/services/{id}',            ['ServiceController', 'show']);
$router->post('/api/services',                ['ServiceController', 'store'],   [fn() => Middleware::admin()]);
$router->put('/api/services/{id}',            ['ServiceController', 'update'],  [fn() => Middleware::admin()]);
$router->delete('/api/services/{id}',         ['ServiceController', 'destroy'], [fn() => Middleware::admin()]);

// ── Appointments ──────────────────────────────────────────────
$router->get('/api/appointments/today',       ['AppointmentController', 'today'],  [fn() => Middleware::staff()]);
$router->get('/api/appointments',             ['AppointmentController', 'index'],  [fn() => Middleware::auth()]);
$router->post('/api/appointments',            ['AppointmentController', 'store'],  [fn() => Middleware::auth()]);
$router->get('/api/appointments/{id}',        ['AppointmentController', 'show'],   [fn() => Middleware::auth()]);
$router->put('/api/appointments/{id}',        ['AppointmentController', 'update'], [fn() => Middleware::staff()]);
$router->delete('/api/appointments/{id}',     ['AppointmentController', 'cancel'], [fn() => Middleware::auth()]);

// ── Staff ─────────────────────────────────────────────────────
$router->get('/api/staff',                    ['StaffController', 'index'],            [fn() => Middleware::admin()]);
$router->post('/api/staff',                   ['StaffController', 'store'],            [fn() => Middleware::admin()]);
$router->put('/api/staff/{id}',               ['StaffController', 'update'],           [fn() => Middleware::admin()]);
$router->get('/api/staff/{id}/shifts',        ['StaffController', 'shifts'],           [fn() => Middleware::staff()]);
$router->post('/api/shifts',                  ['StaffController', 'createShift'],      [fn() => Middleware::admin()]);
$router->post('/api/attendance',              ['StaffController', 'recordAttendance'], [fn() => Middleware::admin()]);

// ── Inventory ─────────────────────────────────────────────────
$router->get('/api/products',                 ['InventoryController', 'index'],          [fn() => Middleware::staff()]);
$router->post('/api/products',                ['InventoryController', 'store'],          [fn() => Middleware::admin()]);
$router->get('/api/products/{id}',            ['InventoryController', 'show'],           [fn() => Middleware::staff()]);
$router->put('/api/products/{id}',            ['InventoryController', 'update'],         [fn() => Middleware::admin()]);
$router->get('/api/products/{id}/movements',  ['InventoryController', 'movements'],      [fn() => Middleware::staff()]);
$router->post('/api/stock-movements',         ['InventoryController', 'recordMovement'], [fn() => Middleware::staff()]);

// ── Payments ──────────────────────────────────────────────────
$router->post('/api/payments/mpesa/initiate', ['PaymentController', 'mpesaInitiate'], [fn() => Middleware::auth()]);
$router->post('/api/payments/mpesa/callback', ['PaymentController', 'mpesaCallback']);
$router->post('/api/payments/cash',           ['PaymentController', 'recordCash'],    [fn() => Middleware::staff()]);

// ── Reports ───────────────────────────────────────────────────
$router->get('/api/reports/revenue',          ['ReportController', 'revenue'],      [fn() => Middleware::admin()]);
$router->get('/api/reports/appointments',     ['ReportController', 'appointments'], [fn() => Middleware::admin()]);
$router->get('/api/reports/low-stock',        ['ReportController', 'lowStock'],     [fn() => Middleware::staff()]);
$router->get('/api/reports/attendance',       ['ReportController', 'attendance'],   [fn() => Middleware::admin()]);
