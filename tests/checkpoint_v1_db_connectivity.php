<?php
/**
 * CHECKPOINT LOCK: v1-db-connectivity
 * Milestone reached: Initial stable state — Database singleton connects to
 *                    mbagathi_parlour via PDO, all core query methods work.
 * Key behaviors encoded:
 *   - Database::getInstance() returns a singleton (same object on repeat calls)
 *   - fetchOne() returns false for a no-match query, not null or an exception
 *   - fetchAll() returns a plain array (may be empty)
 *   - fetchScalar() returns a numeric string/int for COUNT(*)
 *   - insert() returns the new row's ID as a string
 *   - execute() returns an int row-count
 *   - Transactions: beginTransaction / commit / rollBack round-trip without error
 * How to run: php tests/checkpoint_v1_db_connectivity.php
 * If this fails: The database server is down, credentials have changed, or the
 *                PDO singleton is broken. Nothing else in the system will work.
 */

require_once __DIR__ . '/bootstrap.php';

echo "CHECKPOINT: v1 — Database Connectivity\n";
echo str_repeat('=', 50) . "\n";

// ── 1. Singleton behaviour ────────────────────────────────────
section('1. Singleton behaviour');

$db1 = Database::getInstance();
$db2 = Database::getInstance();

assert_true($db1 instanceof Database, 'getInstance() returns a Database object');
assert_true($db1 === $db2,            'getInstance() always returns the same instance');

// ── 2. fetchOne ───────────────────────────────────────────────
section('2. fetchOne — hit and miss');

$roleRow = $db1->fetchOne('SELECT id, name FROM roles WHERE name = ?', ['admin']);
assert_true($roleRow !== false,               'fetchOne() finds the admin role row');
assert_true(is_array($roleRow),               'fetchOne() returns an array on hit');
assert_equals('admin', $roleRow['name'],      'admin role has name = "admin"');

$noRow = $db1->fetchOne('SELECT id FROM roles WHERE name = ?', ['nonexistent_role']);
assert_true($noRow === false,                 'fetchOne() returns false when no row matches');

// ── 3. fetchAll ───────────────────────────────────────────────
section('3. fetchAll — roles catalogue');

$roles = $db1->fetchAll('SELECT name FROM roles ORDER BY id');
assert_true(is_array($roles),                 'fetchAll() returns an array');
assert_true(count($roles) === 3,              'There are exactly 3 roles (client, staff, admin)');

$roleNames = array_column($roles, 'name');
assert_true(in_array('client', $roleNames),   '"client" role exists');
assert_true(in_array('staff',  $roleNames),   '"staff" role exists');
assert_true(in_array('admin',  $roleNames),   '"admin" role exists');

// ── 4. fetchScalar ────────────────────────────────────────────
section('4. fetchScalar — COUNT query');

$categoryCount = $db1->fetchScalar('SELECT COUNT(*) FROM service_categories WHERE is_active = 1');
assert_true((int) $categoryCount === 6,       'There are exactly 6 active service categories');

$serviceCount  = $db1->fetchScalar('SELECT COUNT(*) FROM services WHERE is_active = 1');
assert_true((int) $serviceCount === 33,       'There are exactly 33 active services');

// ── 5. Transactions — insert + rollback leaves no trace ───────
section('5. Transaction integrity — rollback discards insert');

$db1->beginTransaction();
$tempId = $db1->insert(
    "INSERT INTO service_categories (name) VALUES (?)",
    ['__checkpoint_test_category__']
);
assert_true((int) $tempId > 0, 'Temporary row inserted during transaction (ID > 0)');

$db1->rollBack();

$ghost = $db1->fetchOne(
    'SELECT id FROM service_categories WHERE name = ?',
    ['__checkpoint_test_category__']
);
assert_true($ghost === false, 'Rolled-back row is not visible after rollBack()');

// ── 6. execute() returns affected row count ───────────────────
section('6. execute() row-count return');

// We use a no-op UPDATE on a known-stable row (roles.id = 1) with unchanged data.
// Affected count may be 0 (MySQL skips unchanged rows) — the point is no exception.
$affected = $db1->execute('UPDATE roles SET label = label WHERE id = ?', [1]);
assert_true(is_int($affected), 'execute() returns an integer');

// ── Done ──────────────────────────────────────────────────────
finish_suite('v1 — Database Connectivity');
