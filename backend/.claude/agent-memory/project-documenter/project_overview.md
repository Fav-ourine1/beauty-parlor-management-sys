---
name: Project overview
description: Tech stack, architecture, documentation file locations, and key design decisions for the Mbagathi Beauty Parlour Management System
type: project
---

## Tech stack

- PHP 8.3, no framework — custom MVC with Router, Middleware, PDO singleton (Database.php)
- MySQL 8.0, schema at `mbagathi_schema.sql` in the project root
- Vanilla HTML/CSS/JS — no build step, no Node.js required
- Payments: Safaricom M-Pesa Daraja STK Push
- SMS: Africa's Talking
- Email: SMTP via PHPMailer (Gmail-compatible)

## Three user roles

- `admin` (role_id 3) — full access
- `staff` (role_id 2) — schedule, inventory, appointments
- `client` (role_id 1) — booking and own appointment view

## Documentation files

- `/home/josh/Desktop/Projects/beauty-parlor-management-sys/README.md` — main README, created 2026-06-16, covers quickstart, config reference, API reference, troubleshooting, FAQ
- `/home/josh/Desktop/Projects/beauty-parlor-management-sys/backend/README.md` — minimal stub ("backend logic and database interactions"), not yet expanded

## Webroot

The web server must point at `backend/public/`, not the project root. `backend/public/router.php` is the PHP built-in server entry point. `backend/public/.htaccess` handles Apache rewrites.

## Request flow

Browser → `backend/public/index.php` → `Router` (matches routes/web.php or routes/api.php) → `Middleware` guards → Controller → Model → View

## All monetary values are KES (Kenyan Shillings)

The `kes()` helper formats floats as "KES 1,250.00".

**Why:** Business is based in Kenya; payments are M-Pesa.
**How to apply:** Always specify KES explicitly in docs; never say "currency" without qualifying.
