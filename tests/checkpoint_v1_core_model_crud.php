<?php
/**
 * CHECKPOINT LOCK: v1-core-model-crud
 * Milestone reached: Base Model class provides working generic CRUD that all
 *                    concrete models inherit without reimplementing.
 * Key behaviors encoded:
 *   - findById() returns the correct row or false
 *   - findAll() returns all rows, honoring the orderBy argument
 *   - findWhere() filters by one or more equality conditions
 *   - findOneWhere() returns false when no row matches
 *   - create() inserts a new row and returns its string ID
 *   - update() modifies the row and returns 1 (affected)
 *   - delete() removes the row and returns 1 (affected)
 *   - count() without conditions counts the whole table
 *   - count() with conditions counts the filtered subset
 * How to run: php tests/checkpoint_v1_core_model_crud.php
 * If this fails: The base Model is broken. Every model in the system is affected.
 */

require_once __DIR__ . '/bootstrap.php';

echo "CHECKPOINT: v1 — Core Model CRUD\n";
echo str_repeat('=', 50) . "\n";

// We exercise CRUD through UserModel because it has a simple, well-defined table.
// All operations are wrapped in a transaction and rolled back so no live data changes.

$db = Database::getInstance();

section('Setup — open transaction so test rows are isolated');
$db->beginTransaction();

// ── UserModel is a concrete Model subclass ────────────────────
$userModel = new UserModel();

// ── 1. count() on full table ──────────────────────────────────
section('1. count() — full table');

$total = $userModel->count();
assert_true(is_int($total) && $total >= 1, 'count() returns an integer >= 1 (at least the admin user exists)');

// ── 2. findById() ─────────────────────────────────────────────
section('2. findById()');

$adminRow = $userModel->findById(1);
assert_true($adminRow !== false,              'findById(1) finds the seeded admin user');
assert_true(is_array($adminRow),              'findById() returns an array on hit');
assert_array_has_keys(
    ['id', 'role_id', 'full_name', 'email', 'password_hash', 'is_active'],
    $adminRow,
    'Admin row has all expected column keys'
);
assert_equals('admin@mbagathi.com', $adminRow['email'], 'Admin email matches expected seed value');

$missing = $userModel->findById(999999);
assert_true($missing === false, 'findById() returns false for a non-existent ID');

// ── 3. findAll() ──────────────────────────────────────────────
section('3. findAll()');

$allUsers = $userModel->findAll('id', 'ASC');
assert_true(is_array($allUsers),              'findAll() returns an array');
assert_true(count($allUsers) >= 1,            'findAll() returns at least 1 row');
assert_equals(1, (int) $allUsers[0]['id'],    'First row in ASC order has id = 1');

// ── 4. create(), findOneWhere(), count(conditions) ────────────
section('4. create() and conditional queries');

// Grab the 'client' role id — needed for FK
$db2    = Database::getInstance();
$cRole  = $db2->fetchOne('SELECT id FROM roles WHERE name = ?', ['client']);
$roleId = (int) $cRole['id'];

$newId = $userModel->create([
    'role_id'       => $roleId,
    'full_name'     => 'Test Client Checkpoint',
    'email'         => 'checkpoint_test_user@example.com',
    'phone'         => '0799999999',
    'password_hash' => password_hash('test_pass', PASSWORD_BCRYPT, ['cost' => 4]),
]);
assert_true((int) $newId > 0, 'create() returns a positive integer string as the new ID');

$found = $userModel->findOneWhere(['email' => 'checkpoint_test_user@example.com']);
assert_true($found !== false,                  'findOneWhere() locates the newly created row');
assert_equals((int) $newId, (int) $found['id'], 'Returned row ID matches insert ID');

$countFiltered = $userModel->count(['email' => 'checkpoint_test_user@example.com']);
assert_equals(1, $countFiltered, 'count() with condition returns 1 for the unique test user');

// ── 5. findWhere() ────────────────────────────────────────────
section('5. findWhere()');

$rows = $userModel->findWhere(['role_id' => $roleId]);
assert_true(is_array($rows), 'findWhere() returns an array');
$foundInRows = array_filter($rows, fn($r) => $r['email'] === 'checkpoint_test_user@example.com');
assert_true(count($foundInRows) === 1, 'findWhere() includes the newly created test row');

// ── 6. update() ───────────────────────────────────────────────
section('6. update()');

$affected = $userModel->update((int) $newId, ['full_name' => 'Updated Checkpoint Name']);
assert_equals(1, $affected, 'update() returns 1 affected row');

$reloaded = $userModel->findById((int) $newId);
assert_equals('Updated Checkpoint Name', $reloaded['full_name'], 'Updated full_name is persisted within transaction');

// ── 7. delete() ───────────────────────────────────────────────
section('7. delete()');

$deleted = $userModel->delete((int) $newId);
assert_equals(1, $deleted, 'delete() returns 1 affected row');

$gone = $userModel->findById((int) $newId);
assert_true($gone === false, 'Row is not findable after delete()');

// ── Teardown ──────────────────────────────────────────────────
section('Teardown — roll back transaction');
$db->rollBack();
// The rollback would undo the create if delete had not already happened.
// Regardless, the transaction closes cleanly.
assert_true(true, 'Transaction closed without error');

// ── Done ──────────────────────────────────────────────────────
finish_suite('v1 — Core Model CRUD');
