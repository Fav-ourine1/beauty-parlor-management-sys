---
name: Local setup details
description: Exact commands, credentials, and quirks for setting up the project locally — avoids having to re-derive from codebase
type: project
---

## Install command (Ubuntu/WSL2)

```bash
sudo apt update && sudo apt install -y php8.3 php8.3-mysql php8.3-mbstring php8.3-curl mysql-server-8.0 libapache2-mod-php8.3
```

## Schema import

```bash
sudo mysql
SOURCE /path/to/mbagathi_schema.sql;
```

The schema creates the database `mbagathi_parlour`, all 16 tables, 4 views, and seeds:
- roles (client=1, staff=2, admin=3)
- service_categories (6 rows)
- product_categories (6 rows)
- notification_templates (4 rows: BOOKING_CONFIRMATION, APPOINTMENT_REMINDER, APPOINTMENT_CANCELLED, LOW_STOCK_ALERT)

## Database user

```sql
CREATE USER 'mbagathi'@'localhost' IDENTIFIED BY 'Mbagathi2024!';
GRANT ALL PRIVILEGES ON mbagathi_parlour.* TO 'mbagathi'@'localhost';
FLUSH PRIVILEGES;
```

## Default admin seed row

```sql
INSERT INTO mbagathi_parlour.users (role_id, full_name, email, phone, password_hash)
VALUES (3, 'Admin', 'admin@mbagathi.com', '0700000001',
        '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C');
```

Password for that hash is `password`. role_id 3 = admin.

## Start command

```bash
cd backend/public
php -S localhost:8000 router.php
```

URL: http://localhost:8000

## Log files

- `backend/storage/logs/app.log` — application log (Logger class writes here)
- `backend/storage/logs/php_errors.log` — PHP error log (configured in config/app.php)

## Known platform notes

- Windows: requires WSL2 (Ubuntu). Direct Windows PHP setup is not documented in the README.
- macOS: use Homebrew (`brew install php@8.3 mysql`).
- M-Pesa callback URL cannot be localhost — requires ngrok or a live server for STK Push to complete.
