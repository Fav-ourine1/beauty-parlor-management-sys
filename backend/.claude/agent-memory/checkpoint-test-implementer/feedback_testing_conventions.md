---
name: Testing Conventions for This Project
description: Conventions established at v1 checkpoint: plain PHP assertions, no PHPUnit, transaction-isolated mutations
type: feedback
---

Tests must be plain PHP scripts runnable with `php tests/SomeTest.php`. No PHPUnit or external test framework.

**Why:** The user explicitly requested no framework. All assertion helpers are defined in `tests/bootstrap.php`.

**How to apply:**
- Every test file starts with `require_once __DIR__ . '/bootstrap.php';`
- Use assert_true(), assert_false(), assert_equals(), assert_array_has_keys(), section(), finish_suite() from bootstrap
- All DB-mutating tests (create/update/delete) must wrap operations in a transaction and call rollBack() in teardown
- Tests that need a real DB record but the table might be empty should use a SKIP echo rather than a hard failure
- finish_suite() exits with code 1 if any assertion failed — the run_all.php runner uses subprocess exit codes
- Color codes: green = PASS, red = FAIL, reset after label
