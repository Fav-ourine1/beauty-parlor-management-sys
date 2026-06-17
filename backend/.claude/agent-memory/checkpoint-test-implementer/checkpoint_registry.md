---
name: Checkpoint Registry
description: Index of all checkpoint test suites created for this project, with milestone descriptions and run commands
type: project
---

## v1 Checkpoint Suite — 2026-06-16
Milestone: Initial stable state of the Mbagathi Beauty Parlour Management System.
All core systems (DB, models, router, auth, booking) verified working end-to-end.

Files (all in `/home/josh/Desktop/Projects/beauty-parlor-management-sys/tests/`):

| File | Focus | Critical failure meaning |
|------|-------|--------------------------|
| `checkpoint_v1_db_connectivity.php` | PDO singleton, all query methods, transaction integrity | DB server down or credentials changed |
| `checkpoint_v1_core_model_crud.php` | Base Model: findById, findAll, findWhere, create, update, delete, count | Base class broken — all models affected |
| `checkpoint_v1_router_pattern_matching.php` | Router compilePattern, param extraction, dispatch invocation | All URL routing broken |
| `checkpoint_v1_auth_flow.php` | password_verify on seeded hash, UserModel findByEmail/findWithRole/createUser | Login broken |
| `checkpoint_v1_appointment_model.php` | getTodaySummary shape, getByDate, updateStatus, cancel, createWithServices atomicity | Admin dashboard stats broken |
| `checkpoint_v1_service_model.php` | getServicesById (empty/single/multi/invalid), getAllWithCategories (33 services), getCategories (6 cats) | Client booking page broken |

Run command: `php tests/run_all.php`
Individual suite: `php tests/checkpoint_v1_<name>.php`

**Why:** Checkpoint tests act as save points. Run `php tests/run_all.php` any time to verify the system is still in its v1 stable state.
