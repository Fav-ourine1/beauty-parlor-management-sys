---
name: Mbagathi Beauty Parlour — Project Architecture
description: Core architecture, file layout, and test infrastructure for the Mbagathi Beauty Parlour Management System
type: project
---

PHP 8.3 MVC app, MySQL 8.0, no framework. All tests live in `/home/josh/Desktop/Projects/beauty-parlor-management-sys/tests/`.

**Backend root:** `/home/josh/Desktop/Projects/beauty-parlor-management-sys/backend/`

Key directories:
- `core/` — Database (PDO singleton), Model (base CRUD), Controller (base JSON), Router, Middleware, helpers (Logger, dd, redirect, e, data_get, kes, fmt_datetime)
- `app/models/` — UserModel, ClientModel, StaffModel, ServiceModel, AppointmentModel, ProductModel, PaymentModel, NotificationModel
- `app/controllers/` — AuthController, AdminController, ClientController, StaffController, ServiceController, AppointmentController, InventoryController, PaymentController, ReportController
- `routes/api.php` + `routes/web.php` — all routes wired
- `config/app.php` — all constants: DB_HOST, DB_NAME (mbagathi_parlour), DB_USER (mbagathi), DB_PASS (Mbagathi2024!), BCRYPT_COST (12), SESSION_NAME (mbagathi_sess)

**DB seed facts (locked at v1 checkpoint):**
- 3 roles: client, staff, admin
- 6 active service_categories: Hairdressing, Hair Colouring & Treatment, Nail Care, Facial & Skincare, Makeup, Eyebrow & Threading
- 33 active services total
- 1 seeded admin user: admin@mbagathi.com / password (bcrypt hash stored)
- 4 notification templates seeded

**Why:** These facts are load-bearing for the checkpoint tests. If numbers change, update the tests.
**How to apply:** When writing future checkpoint tests, verify seed counts against live DB before hardcoding them.
