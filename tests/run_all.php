<?php
/**
 * Run all checkpoint tests sequentially and report a final pass/fail tally.
 *
 * Usage: php tests/run_all.php
 *
 * Exit codes:
 *   0 — all suites passed
 *   1 — one or more suites failed
 */

$suites = [
    'checkpoint_v1_db_connectivity.php',
    'checkpoint_v1_core_model_crud.php',
    'checkpoint_v1_router_pattern_matching.php',
    'checkpoint_v1_auth_flow.php',
    'checkpoint_v1_appointment_model.php',
    'checkpoint_v1_service_model.php',
];

$dir     = __DIR__;
$passed  = 0;
$failed  = 0;
$results = [];

echo "\n";
echo str_repeat('=', 60) . "\n";
echo "  Mbagathi Beauty Parlour — Checkpoint Test Suite v1\n";
echo "  " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 60) . "\n\n";

foreach ($suites as $suite) {
    $path = $dir . '/' . $suite;
    if (!file_exists($path)) {
        echo "\033[33m  MISSING\033[0m  {$suite}\n";
        $results[] = ['file' => $suite, 'status' => 'MISSING'];
        $failed++;
        continue;
    }

    // Run each suite in a subprocess so one failure does not abort the rest.
    $cmd    = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($path) . ' 2>&1';
    $output = [];
    $code   = 0;
    exec($cmd, $output, $code);

    $label  = str_pad($suite, 48);
    if ($code === 0) {
        echo "\033[32m  PASS\033[0m  {$label}\n";
        $passed++;
        $results[] = ['file' => $suite, 'status' => 'PASS'];
    } else {
        echo "\033[31m  FAIL\033[0m  {$label}\n";
        // Print the subprocess output indented so it is easy to scan
        foreach ($output as $line) {
            echo "         {$line}\n";
        }
        $failed++;
        $results[] = ['file' => $suite, 'status' => 'FAIL'];
    }
}

echo "\n" . str_repeat('-', 60) . "\n";
$total = $passed + $failed;
echo "  Results: {$passed}/{$total} suites passed";
if ($failed > 0) {
    echo "  (\033[31m{$failed} failed\033[0m)";
}
echo "\n" . str_repeat('=', 60) . "\n\n";

exit($failed > 0 ? 1 : 0);
