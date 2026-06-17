<?php
/**
 * CHECKPOINT LOCK: v1-auth-flow
 * Milestone reached: Authentication system is live. Session-based login works
 *                    end-to-end: bcrypt hash stored at seed time is verifiable
 *                    at runtime, and role-aware redirect logic is correct.
 * Key behaviors encoded:
 *   - The seeded admin@mbagathi.com record exists and is active
 *   - password_verify('password', <stored hash>) returns true
 *   - UserModel::verifyPassword() delegates to password_verify correctly
 *   - Wrong password returns false — not an exception or truthy value
 *   - UserModel::findByEmail() locates a user by email
 *   - UserModel::findWithRole() returns the role name joined from roles table
 *   - Admin role name in the joined row is exactly 'admin'
 *   - UserModel::createUser() creates a user under a valid role and rejects an invalid role
 *   - password_hash cost constant BCRYPT_COST is defined and is a sensible value
 * How to run: php tests/checkpoint_v1_auth_flow.php
 * If this fails: Login is broken. Users cannot authenticate. Check the users
 *                table seed, the password_hash column, or the bcrypt cost constant.
 */

require_once __DIR__ . '/bootstrap.php';

echo "CHECKPOINT: v1 — Auth Flow\n";
echo str_repeat('=', 50) . "\n";

$userModel = new UserModel();
$db        = Database::getInstance();

// ── 1. Seeded admin record ────────────────────────────────────
section('1. Seeded admin user record');

$admin = $userModel->findByEmail('admin@mbagathi.com');
assert_true($admin !== false,                   'admin@mbagathi.com exists in the users table');
assert_true(is_array($admin),                   'findByEmail() returns an array');
assert_array_has_keys(
    ['id', 'email', 'password_hash', 'is_active', 'role_id'],
    $admin,
    'Admin row has the required columns'
);
assert_equals('1', (string) $admin['is_active'], 'Admin account is_active = 1');

// ── 2. Password verification ──────────────────────────────────
section('2. Password verification against stored hash');

$hash = $admin['password_hash'];

// The password defined in the project description is "password"
$correctResult = password_verify('password', $hash);
assert_true($correctResult,                     'password_verify("password", stored_hash) is true');

$wrongResult = password_verify('WrongPassword!', $hash);
assert_false($wrongResult,                      'password_verify(wrong_pass, stored_hash) is false');

// ── 3. UserModel::verifyPassword() ───────────────────────────
section('3. UserModel::verifyPassword() delegation');

assert_true(
    $userModel->verifyPassword('password', $hash),
    'UserModel::verifyPassword() returns true for the correct password'
);
assert_false(
    $userModel->verifyPassword('', $hash),
    'UserModel::verifyPassword() returns false for an empty string'
);
assert_false(
    $userModel->verifyPassword('password ', $hash),  // trailing space
    'UserModel::verifyPassword() returns false for password with trailing space'
);

// ── 4. Role join via findWithRole() ───────────────────────────
section('4. findWithRole() — role name joined from roles table');

$adminWithRole = $userModel->findWithRole((int) $admin['id']);
assert_true($adminWithRole !== false,           'findWithRole() returns a row for the admin user');
assert_array_has_keys(['role'], $adminWithRole, 'findWithRole() result includes a "role" key');
assert_equals('admin', $adminWithRole['role'],  'Joined role name is exactly "admin"');

// ── 5. getAllWithRoles() ──────────────────────────────────────
section('5. getAllWithRoles() — every row has a role key');

$allUsers = $userModel->getAllWithRoles();
assert_true(is_array($allUsers) && count($allUsers) >= 1, 'getAllWithRoles() returns at least one user');
foreach ($allUsers as $u) {
    assert_true(
        isset($u['role']) && in_array($u['role'], ['client', 'staff', 'admin']),
        "User id={$u['id']} has a valid role value"
    );
}

// ── 6. BCRYPT_COST constant ───────────────────────────────────
section('6. BCRYPT_COST constant is defined and valid');

assert_true(defined('BCRYPT_COST'),             'BCRYPT_COST constant is defined');
assert_true(
    is_int(BCRYPT_COST) && BCRYPT_COST >= 10 && BCRYPT_COST <= 14,
    'BCRYPT_COST is in the secure range 10–14'
);

// ── 7. createUser() role validation ──────────────────────────
section('7. createUser() rejects an unknown role name');

$db->beginTransaction();

$exceptionThrown = false;
try {
    $userModel->createUser(
        [
            'full_name' => 'Ghost User',
            'email'     => 'ghost_checkpoint@example.com',
            'phone'     => '0799999999',
            'password'  => 'irrelevant',
        ],
        'nonexistent_role'
    );
} catch (InvalidArgumentException $e) {
    $exceptionThrown = true;
}

assert_true($exceptionThrown, 'createUser() throws InvalidArgumentException for an unknown role');

$db->rollBack();

// ── 8. createUser() success path ─────────────────────────────
section('8. createUser() success — creates user with hashed password');

$db->beginTransaction();

$newId = $userModel->createUser(
    [
        'full_name' => 'Auth Checkpoint Client',
        'email'     => 'auth_checkpoint_client@example.com',
        'phone'     => '0711111110',
        'password'  => 'TestPass2024!',
    ],
    'client'
);

assert_true((int) $newId > 0, 'createUser() returns a positive ID on success');

$created = $userModel->findByEmail('auth_checkpoint_client@example.com');
assert_true($created !== false,                    'Newly created user is findable by email');
// The stored hash should verify against the plain-text password we provided
assert_true(
    password_verify('TestPass2024!', $created['password_hash']),
    'createUser() stored a valid bcrypt hash that verifies against the original password'
);
// Ensure plain text was NOT stored
assert_false(
    $created['password_hash'] === 'TestPass2024!',
    'Plain-text password is NOT stored in password_hash'
);

$db->rollBack();

// ── Done ──────────────────────────────────────────────────────
finish_suite('v1 — Auth Flow');
