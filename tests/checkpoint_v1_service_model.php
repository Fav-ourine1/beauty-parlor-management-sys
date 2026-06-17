<?php
/**
 * CHECKPOINT LOCK: v1-service-model
 * Milestone reached: ServiceModel is fully operational. The client booking page
 *                    (/client/book) loads services grouped by category with live
 *                    prices. 33 services across 6 categories are seeded and active.
 * Key behaviors encoded:
 *   - getAllWithCategories() returns all 33 active services with category names joined
 *   - getAllWithCategories(false) includes inactive services too (>= 33)
 *   - Every row from getAllWithCategories() has category_name and category_description keys
 *   - getServicesById([]) returns an empty array — no DB query, no exception
 *   - getServicesById([id]) returns a keyed array with the service at that key
 *   - getServicesById([id1, id2]) returns both rows, keyed by their IDs
 *   - Returned rows from getServicesById are keyed by the service's id field
 *   - getByCategory() returns only services for the requested category
 *   - getCategories() returns all 6 active categories
 *   - getCategoryById() returns a row or false, never throws
 * How to run: php tests/checkpoint_v1_service_model.php
 * If this fails: The client booking page is broken, or the admin services table
 *                will not load correctly. Check the services/service_categories tables.
 */

require_once __DIR__ . '/bootstrap.php';

echo "CHECKPOINT: v1 — ServiceModel\n";
echo str_repeat('=', 50) . "\n";

$serviceModel = new ServiceModel();
$db           = Database::getInstance();

// ── 1. getServicesById([]) — empty-array guard ────────────────
section('1. getServicesById([]) — safe empty-input handling');

$result = $serviceModel->getServicesById([]);
assert_true(is_array($result),    'getServicesById([]) returns an array');
assert_true(count($result) === 0, 'getServicesById([]) returns an empty array (no query issued)');

// ── 2. getServicesById([id]) — single ID ─────────────────────
section('2. getServicesById() — single valid ID');

// Grab the first active service to use as a known ID
$firstService = $db->fetchOne('SELECT id FROM services WHERE is_active = 1 ORDER BY id LIMIT 1');
assert_true($firstService !== false, 'At least one active service exists in the DB');

$serviceId = (int) $firstService['id'];
$keyed     = $serviceModel->getServicesById([$serviceId]);

assert_true(is_array($keyed),                     'getServicesById([id]) returns an array');
assert_true(count($keyed) === 1,                  'getServicesById([id]) returns exactly 1 entry');
assert_true(isset($keyed[$serviceId]),             'Result is keyed by the service id');
assert_array_has_keys(
    ['id', 'name', 'price', 'duration_mins', 'category_id'],
    $keyed[$serviceId],
    'Service row has all required columns'
);
assert_equals(
    $serviceId,
    (int) $keyed[$serviceId]['id'],
    'id field inside the row matches the key'
);

// ── 3. getServicesById([id1, id2]) — multi ID ─────────────────
section('3. getServicesById() — multiple IDs returned and keyed correctly');

$twoServices = $db->fetchAll('SELECT id FROM services WHERE is_active = 1 ORDER BY id LIMIT 2');

if (count($twoServices) < 2) {
    echo "  SKIP  Fewer than 2 active services — skipping multi-ID test\n";
} else {
    $id1 = (int) $twoServices[0]['id'];
    $id2 = (int) $twoServices[1]['id'];

    $multi = $serviceModel->getServicesById([$id1, $id2]);

    assert_equals(2, count($multi),       'getServicesById([id1,id2]) returns 2 entries');
    assert_true(isset($multi[$id1]),       "Entry keyed by {$id1} exists");
    assert_true(isset($multi[$id2]),       "Entry keyed by {$id2} exists");
    assert_equals($id1, (int) $multi[$id1]['id'], "Row at key {$id1} has id = {$id1}");
    assert_equals($id2, (int) $multi[$id2]['id'], "Row at key {$id2} has id = {$id2}");
}

// ── 4. getServicesById() with non-existent IDs ────────────────
section('4. getServicesById() — non-existent IDs produce no rows');

$ghost = $serviceModel->getServicesById([999998, 999999]);
assert_true(is_array($ghost),       'getServicesById([invalid_ids]) returns an array');
assert_true(count($ghost) === 0,    'getServicesById([invalid_ids]) returns an empty array');

// ── 5. getAllWithCategories() — shape and count ────────────────
section('5. getAllWithCategories() — 33 active services with joined category fields');

$all = $serviceModel->getAllWithCategories(true);

assert_true(is_array($all),         'getAllWithCategories() returns an array');
assert_equals(33, count($all),      'getAllWithCategories() returns exactly 33 active services');

foreach ($all as $svc) {
    assert_array_has_keys(
        ['id', 'name', 'price', 'duration_mins', 'category_id', 'category_name'],
        $svc,
        "Service id={$svc['id']} has all required joined columns"
    );
    assert_true(
        !empty($svc['category_name']),
        "Service id={$svc['id']} has a non-empty category_name"
    );
    assert_true(
        (float) $svc['price'] > 0,
        "Service id={$svc['id']} has a positive price"
    );
    assert_true(
        (int) $svc['duration_mins'] > 0,
        "Service id={$svc['id']} has a positive duration_mins"
    );
}

// ── 6. getAllWithCategories(false) includes at least as many rows ─
section('6. getAllWithCategories(false) — includes active + inactive services');

$allIncInactive = $serviceModel->getAllWithCategories(false);
assert_true(
    count($allIncInactive) >= 33,
    'getAllWithCategories(false) returns >= 33 rows (active + any inactive)'
);

// ── 7. getCategories() ────────────────────────────────────────
section('7. getCategories() — 6 active service categories');

$categories = $serviceModel->getCategories(true);
assert_true(is_array($categories),    'getCategories() returns an array');
assert_equals(6, count($categories),  'There are exactly 6 active service categories');

$catNames = array_column($categories, 'name');
$expected = [
    'Hairdressing',
    'Hair Colouring & Treatment',
    'Nail Care',
    'Facial & Skincare',
    'Makeup',
    'Eyebrow & Threading',
];
foreach ($expected as $name) {
    assert_true(in_array($name, $catNames), "Category \"{$name}\" exists and is active");
}

// ── 8. getByCategory() ───────────────────────────────────────
section('8. getByCategory() — filters correctly by category_id');

// Use the first real category
$firstCat = $db->fetchOne('SELECT id FROM service_categories WHERE is_active = 1 ORDER BY id LIMIT 1');
$catId    = (int) $firstCat['id'];

$catServices = $serviceModel->getByCategory($catId, true);
assert_true(is_array($catServices), 'getByCategory() returns an array');
foreach ($catServices as $svc) {
    assert_equals(
        $catId,
        (int) $svc['category_id'],
        "Service id={$svc['id']} belongs to category_id={$catId}"
    );
}

// ── 9. getCategoryById() ─────────────────────────────────────
section('9. getCategoryById() — hit and miss');

$catRow = $serviceModel->getCategoryById($catId);
assert_true($catRow !== false,  'getCategoryById() returns a row for a valid ID');
assert_equals($catId, (int) $catRow['id'], 'Returned row has the correct id');

$noCat = $serviceModel->getCategoryById(99999);
assert_true($noCat === false,   'getCategoryById() returns false for a non-existent ID');

// ── Done ──────────────────────────────────────────────────────
finish_suite('v1 — ServiceModel');
