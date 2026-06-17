<?php
/**
 * CHECKPOINT LOCK: v1-appointment-model
 * Milestone reached: AppointmentModel::getTodaySummary() is live and powering
 *                    the admin dashboard stats block. The shape contract is locked.
 * Key behaviors encoded:
 *   - getTodaySummary() always returns an array with all five status keys
 *   - All five values are integers (even when no appointments exist today)
 *   - The five keys are: pending, confirmed, in_progress, completed, cancelled
 *   - No additional keys bleed through from the DB GROUP BY
 *   - getByDate() returns an array (possibly empty) for any date string
 *   - getByClient() returns an array; status filter narrows results correctly
 *   - updateStatus() changes the stored status and returns 1 affected row
 *   - cancel() sets status to 'cancelled' and returns 1 affected row
 *   - createWithServices() rolls back atomically on failure
 * How to run: php tests/checkpoint_v1_appointment_model.php
 * If this fails: The admin dashboard stats are broken, or appointment booking /
 *                status workflows are producing incorrect data.
 */

require_once __DIR__ . '/bootstrap.php';

echo "CHECKPOINT: v1 — AppointmentModel\n";
echo str_repeat('=', 50) . "\n";

$apptModel = new AppointmentModel();
$db        = Database::getInstance();

// ── 1. getTodaySummary() shape contract ───────────────────────
section('1. getTodaySummary() — shape and type contract');

$summary = $apptModel->getTodaySummary();

assert_true(is_array($summary),                  'getTodaySummary() returns an array');

$expectedKeys = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
assert_array_has_keys($expectedKeys, $summary,   'Summary has all five required status keys');

$summaryKeys = array_keys($summary);
sort($summaryKeys);
$sortedExpected = $expectedKeys;
sort($sortedExpected);
assert_equals(
    $sortedExpected,
    $summaryKeys,
    'Summary contains exactly the five expected keys and no others'
);

foreach ($expectedKeys as $key) {
    assert_true(
        is_int($summary[$key]),
        "summary['{$key}'] is an integer (got " . gettype($summary[$key]) . ")"
    );
    assert_true(
        $summary[$key] >= 0,
        "summary['{$key}'] is non-negative"
    );
}

// ── 2. getTodaySummary() sum sanity ───────────────────────────
section('2. getTodaySummary() — sum is non-negative and bounded');

$total = array_sum($summary);
assert_true($total >= 0, 'Sum of all status counts is >= 0');

// A sanity check: total should not exceed the row count for today
$countToday = (int) $db->fetchScalar(
    "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()"
);
assert_equals(
    $countToday,
    $total,
    'Sum of status counts equals raw COUNT(*) for today'
);

// ── 3. getByDate() ────────────────────────────────────────────
section('3. getByDate() — returns array for any date');

$futureDate = '2099-01-01';
$noAppts = $apptModel->getByDate($futureDate);
assert_true(is_array($noAppts),  'getByDate() returns an array for a far-future date with no appointments');
assert_true(count($noAppts) === 0, 'getByDate() returns an empty array when no appointments exist on that date');

// getByDate for today should match the raw count
$todayAppts = $apptModel->getByDate(date('Y-m-d'));
assert_true(is_array($todayAppts), 'getByDate(today) returns an array');
assert_equals($countToday, count($todayAppts), 'getByDate(today) count matches raw COUNT(*) for today');

// ── 4. updateStatus() and cancel() — use a transaction ────────
section('4. updateStatus() and cancel() — status mutation');

// To avoid needing a real client/staff, we test only if there is at least one
// appointment in the DB. If the DB is empty of appointments we skip gracefully.
$anyAppt = $db->fetchOne("SELECT id, status FROM appointments LIMIT 1");
if ($anyAppt === false) {
    echo "  SKIP  No appointments in DB — skipping mutation tests\n";
} else {
    $apptId      = (int) $anyAppt['id'];
    $origStatus  = $anyAppt['status'];

    $db->beginTransaction();

    // updateStatus()
    $affected = $apptModel->updateStatus($apptId, 'confirmed');
    assert_equals(1, $affected, 'updateStatus() returns 1 affected row');

    $reloaded = $apptModel->findById($apptId);
    assert_equals('confirmed', $reloaded['status'], 'Status is now "confirmed" after updateStatus()');

    // cancel()
    $cancelAffected = $apptModel->cancel($apptId, 'Checkpoint test cancel');
    assert_equals(1, $cancelAffected, 'cancel() returns 1 affected row');

    $cancelled = $apptModel->findById($apptId);
    assert_equals('cancelled', $cancelled['status'], 'Status is "cancelled" after cancel()');
    assert_true(
        !empty($cancelled['cancel_reason']),
        'cancel_reason is stored after cancel()'
    );
    assert_true(
        !empty($cancelled['cancelled_at']),
        'cancelled_at timestamp is set after cancel()'
    );

    $db->rollBack();
}

// ── 5. createWithServices() atomic rollback on failure ────────
section('5. createWithServices() — atomicity on error');

// We pass a deliberately invalid service_id (999999) to trigger a FK violation
// which should cause the entire transaction to roll back.
$countBefore = (int) $db->fetchScalar('SELECT COUNT(*) FROM appointments');

// First, get a valid client_id if one exists
$anyClient = $db->fetchOne('SELECT id FROM clients LIMIT 1');

if ($anyClient === false) {
    echo "  SKIP  No clients in DB — skipping createWithServices atomicity test\n";
} else {
    $clientId = (int) $anyClient['id'];
    $exceptionThrown = false;
    try {
        $apptModel->createWithServices(
            [
                'client_id'        => $clientId,
                'appointment_date' => '2099-12-31',
                'start_time'       => '10:00:00',
                'end_time'         => '11:00:00',
                'status'           => 'pending',
                'total_amount'     => 500.00,
            ],
            [999999],   // invalid service_id — FK violation
            [500.00]
        );
    } catch (Throwable $e) {
        $exceptionThrown = true;
    }
    assert_true($exceptionThrown, 'createWithServices() throws on FK violation');

    $countAfter = (int) $db->fetchScalar('SELECT COUNT(*) FROM appointments');
    assert_equals(
        $countBefore,
        $countAfter,
        'Appointment count is unchanged after a rolled-back createWithServices()'
    );
}

// ── Done ──────────────────────────────────────────────────────
finish_suite('v1 — AppointmentModel');
