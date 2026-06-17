# Mbagathi Beauty Parlour Management System

A complete, self-contained management system for a beauty parlour. It handles everything the business needs day-to-day: client appointment booking, staff scheduling, inventory tracking, payments via M-Pesa or cash, and business reports — all through a single web application that runs in any browser.

Built with PHP 8.3 and MySQL 8.0. No external frameworks. No build tools. No Node.js required.

---

## Table of Contents

1. [What the system does](#1-what-the-system-does)
2. [Who uses it and what they can do](#2-who-uses-it-and-what-they-can-do)
3. [Features](#3-features)
4. [Screenshots](#4-screenshots)
5. [Prerequisites — what you need before you start](#5-prerequisites--what-you-need-before-you-start)
6. [Local setup — step by step](#6-local-setup--step-by-step)
7. [Configuration reference](#7-configuration-reference)
8. [Running the application](#8-running-the-application)
9. [Default login credentials](#9-default-login-credentials)
10. [Project structure](#10-project-structure)
11. [API reference](#11-api-reference)
12. [Running tests](#12-running-tests)
13. [Deploying to a live server](#13-deploying-to-a-live-server)
14. [Troubleshooting](#14-troubleshooting)
15. [FAQ](#15-faq)

---

## 1. What the system does

Mbagathi Beauty Parlour Management System is a web application that replaces paper-based or spreadsheet-based operations at a beauty salon. Once it is running on a computer:

- **Clients** visit the website, create an account, choose from the salon's full service menu, pick a date and time, and book an appointment. They see a live running total of the cost as they select services. They can pay by M-Pesa directly from the booking page, or pay in cash at the salon.
- **Staff members** log in to see their schedule for the day, view the appointments they are assigned to, and manage inventory stock levels.
- **Administrators** have full control: they manage all appointments, add or remove staff, set up the service menu and pricing, track inventory with a complete stock-movement history, run business reports, and configure the system.

All monetary amounts are in Kenyan Shillings (KES).

---

## 2. Who uses it and what they can do

The system has three user roles. Each role sees only what it needs to see.

### Administrator

An administrator has unrestricted access to every part of the system.

- View the dashboard: today's appointments at a glance, revenue totals (cash and M-Pesa), low-stock alerts
- Manage all appointments: confirm, start, complete, or cancel any booking
- Add and manage staff accounts, assign job titles, record shifts and attendance
- Manage the services catalogue: create service categories, add or edit individual services with pricing and duration
- Manage inventory: add products, record stock purchases, usages, adjustments, and returns
- Run reports: daily revenue breakdown, appointment statistics, staff attendance for any month, current low-stock list
- Receive low-stock email alerts automatically when any product falls below its threshold

### Staff Member

A staff member can see and act on what is relevant to their working day.

- View their personal schedule and assigned appointments
- Update appointment status (in progress, completed)
- View and record stock movements (e.g. log that a product was used in a service)
- View the low-stock report

### Client

A client is any person who books an appointment.

- Create an account with name, email, and phone number
- Browse the full service menu, grouped by category
- Book an appointment by selecting one or more services, choosing a date and time, and adding optional notes (allergies, preferences)
- See the estimated total cost update in real time as services are selected
- Pay by M-Pesa STK Push (a payment prompt is sent to their phone) or pay cash at the salon
- View their own past and upcoming appointments

---

## 3. Features

### Appointment booking
- Multi-service selection in a single booking — the total price updates live as each service is ticked
- Service catalogue grouped by category (Hairdressing, Nail Care, Skincare, Makeup, etc.)
- Price snapshots: the price paid is locked at the time of booking, so a price change later does not affect existing bookings

### Payments
- **M-Pesa STK Push**: the client enters their phone number and an M-Pesa PIN prompt arrives on their phone within seconds. The system listens for Safaricom's callback and marks the payment complete automatically
- **Cash**: staff or admin record a cash payment against any appointment
- Full payment history with status tracking (pending, completed, failed, refunded)

### Inventory management
- Track any product with a name, brand, SKU, unit type, unit cost, and reorder quantity
- Per-product low-stock threshold: when stock falls to or below the threshold, the product appears in the low-stock report and triggers an email alert
- Every stock change (purchase, usage, adjustment, return, waste) is recorded in a permanent audit log with a running balance
- Six built-in product categories: Hair Products, Nail Supplies, Skincare & Facial, Makeup & Cosmetics, Tools & Equipment, Consumables & Sundries

### Staff scheduling and attendance
- Admins create shifts for any staff member on any date with start and end times
- Attendance is recorded per shift: present, absent, late, or half-day
- Clock-in and clock-out times can be recorded
- Monthly attendance summary report per staff member

### Notifications
- **SMS** via Africa's Talking: booking confirmation and appointment reminder sent to the client's phone
- **Email** via SMTP (Gmail or any provider): booking confirmation, appointment reminder, cancellation notice, and low-stock alerts to the admin
- All notifications are logged in the database with delivery status

### Reports (admin only)
- Daily revenue table showing cash and M-Pesa totals side by side
- Appointment status summary for today
- Staff attendance summary for the current month
- Low-stock product list with suggested reorder quantities

---

## 4. Screenshots

> Screenshots will be added here once the UI is finalised. The admin dashboard, client booking page, and reports page are the primary views.

---

## 5. Prerequisites — what you need before you start

This section lists everything you must have installed on your computer before you can run the application. If you are on a fresh Ubuntu or Debian machine, all of it can be installed with a single command shown in Step 1 of the setup guide.

| Requirement | Minimum version | What it is |
|---|---|---|
| PHP | 8.3 | The programming language the application is written in |
| PHP extensions | `php8.3-mysql`, `php8.3-mbstring`, `php8.3-curl` | Add-on packages that give PHP database, text encoding, and HTTP capabilities |
| MySQL | 8.0 | The database that stores all application data |
| A web server | PHP's built-in server (dev) or Apache (production) | The program that receives browser requests and hands them to the application |

You do **not** need Node.js, npm, Composer, or any other tool. There is no build step.

### Supported operating systems

- **Linux** (Ubuntu 22.04 LTS or 24.04 LTS recommended)
- **macOS** 13 or later (using Homebrew)
- **Windows** 10 or 11 (using WSL2 — Windows Subsystem for Linux — running Ubuntu)

> **Windows users**: This guide assumes you are running commands inside WSL2. If you have not set up WSL2 yet, follow Microsoft's official guide at [https://learn.microsoft.com/en-us/windows/wsl/install](https://learn.microsoft.com/en-us/windows/wsl/install) first, then return here.

---

## 6. Local setup — step by step

Follow every step in order. Do not skip a step even if you think you already have something installed.

### Step 1 — Install PHP 8.3 and MySQL 8.0

**Ubuntu / Debian / WSL2 (Ubuntu)**

Open a terminal and run this command. It will ask for your password once.

```bash
sudo apt update && sudo apt install -y \
  php8.3 \
  php8.3-mysql \
  php8.3-mbstring \
  php8.3-curl \
  mysql-server-8.0 \
  libapache2-mod-php8.3
```

When it finishes, verify PHP is installed:

```bash
php --version
```

Expected output (your patch number may differ):

```
PHP 8.3.x (cli) ...
```

Verify MySQL is installed:

```bash
mysql --version
```

Expected output:

```
mysql  Ver 8.0.x ...
```

**macOS (Homebrew)**

If you do not have Homebrew, install it first from [https://brew.sh](https://brew.sh).

```bash
brew install php@8.3 mysql
brew services start mysql
```

---

### Step 2 — Start the MySQL service

**Ubuntu / WSL2:**

```bash
sudo service mysql start
```

**macOS:**

```bash
brew services start mysql
```

Verify MySQL is running:

```bash
sudo mysql -e "SELECT 1;"
```

Expected output: a table showing `1`.

---

### Step 3 — Download the project

If you have Git installed:

```bash
git clone https://github.com/your-username/beauty-parlor-management-sys.git
cd beauty-parlor-management-sys
```

If you downloaded a ZIP file instead, extract it and open a terminal inside the extracted folder.

> **What is a terminal?** On Ubuntu: press `Ctrl + Alt + T`. On macOS: open the Terminal app from Applications > Utilities. On Windows: open the Ubuntu app from the Start menu.

---

### Step 4 — Import the database schema

The schema file creates the database, all its tables, and some starter data (service categories, product categories, and notification templates).

**1.** Open a MySQL session as root:

```bash
sudo mysql
```

You will see a `mysql>` prompt. This means you are inside MySQL.

**2.** Import the schema. Replace `/path/to/` with the actual path to the project on your computer:

```sql
SOURCE /path/to/beauty-parlor-management-sys/mbagathi_schema.sql;
```

For example, if you cloned the project to your home folder:

```sql
SOURCE /home/yourusername/beauty-parlor-management-sys/mbagathi_schema.sql;
```

Expected output: a series of `Query OK` lines, one for each table created. No errors should appear.

**3.** Verify the database was created:

```sql
SHOW DATABASES;
```

You should see `mbagathi_parlour` in the list.

---

### Step 5 — Create the database user

Still inside the `mysql>` prompt, run these three commands one at a time:

```sql
CREATE USER 'mbagathi'@'localhost' IDENTIFIED BY 'Mbagathi2024!';
```

```sql
GRANT ALL PRIVILEGES ON mbagathi_parlour.* TO 'mbagathi'@'localhost';
```

```sql
FLUSH PRIVILEGES;
```

Expected output: `Query OK` after each one.

---

### Step 6 — Create the first admin account

Still inside the `mysql>` prompt, run this command. It inserts a default administrator user whose password is `password`:

```sql
INSERT INTO mbagathi_parlour.users
  (role_id, full_name, email, phone, password_hash)
VALUES
  (3, 'Admin', 'admin@mbagathi.com', '0700000001',
   '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C');
```

Expected output: `Query OK, 1 row affected`.

Now exit MySQL:

```sql
EXIT;
```

> **Important**: Change this password immediately after your first login. See [Default login credentials](#9-default-login-credentials) for instructions.

---

### Step 7 — Configure the application

Open the configuration file in a text editor:

```
beauty-parlor-management-sys/backend/config/app.php
```

The only values you **must** change for local development are:

| Setting | What to change | Why |
|---|---|---|
| `DB_PASS` | Set to `Mbagathi2024!` | Must match the password you used in Step 5 |
| `APP_URL` | Leave as `http://localhost:8000` | Correct for the built-in PHP server |

All other settings — M-Pesa, SMS, email — are pre-filled with sandbox/test values. You can leave them as-is to run the application locally. See [Configuration reference](#7-configuration-reference) for a full explanation of every setting.

> **Warning**: Never commit real API keys, passwords, or credentials to Git. The `config/app.php` file already contains placeholder values for the third-party services. Replace them only when you are ready to go live.

---

### Step 8 — Start the web server

Navigate to the application's public folder and start PHP's built-in web server:

```bash
cd /path/to/beauty-parlor-management-sys/backend/public
php -S localhost:8000 router.php
```

Expected output:

```
PHP 8.3.x Development Server (http://localhost:8000) started
```

The server is now running. **Do not close this terminal** — the server stops when you close it.

---

### Step 9 — Open the application

Open any web browser and go to:

```
http://localhost:8000
```

You should see the Mbagathi Beauty Parlour sign-in page.

---

## 7. Configuration reference

All configuration lives in one file: `backend/config/app.php`. Open it in any text editor to make changes. Restart the PHP server after saving changes.

### Application settings

| Constant | Default value | Description |
|---|---|---|
| `APP_ENV` | `development` | Set to `production` on a live server. Controls error display. |
| `APP_NAME` | `Mbagathi Beauty Parlour` | The name shown in the browser tab and emails |
| `APP_URL` | `http://localhost:8000` | The full URL where the app is running. Change this to your domain when going live. |
| `APP_DEBUG` | `true` (in development) | When true, PHP errors are shown in the browser. Always false in production. |

### Database settings

| Constant | Default value | Description |
|---|---|---|
| `DB_HOST` | `localhost` | The address of your MySQL server. Do not change this for local development. |
| `DB_PORT` | `3306` | MySQL's default port. Change only if your MySQL runs on a different port. |
| `DB_NAME` | `mbagathi_parlour` | The name of the database created by the schema file |
| `DB_USER` | `mbagathi` | The MySQL user you created in Step 5 |
| `DB_PASS` | `Mbagathi2024!` | The password you set in Step 5 |

### Session settings

| Constant | Default value | Description |
|---|---|---|
| `SESSION_LIFETIME` | `7200` | How long a login session lasts in seconds (7200 = 2 hours) |
| `SESSION_NAME` | `mbagathi_sess` | The name of the session cookie stored in the browser |

### Security

| Constant | Default value | What to do |
|---|---|---|
| `JWT_SECRET` | `CHANGE_THIS_TO_A_STRONG_RANDOM_STRING` | Replace with a long, random string before going live. You can generate one by running `openssl rand -hex 32` in a terminal. |
| `BCRYPT_COST` | `12` | The strength of password hashing. Do not lower this value. |

### M-Pesa (Safaricom Daraja) settings

You need a Safaricom Daraja account to use M-Pesa payments. Register at [https://developer.safaricom.co.ke](https://developer.safaricom.co.ke). You will receive credentials for a sandbox (testing) environment first.

| Constant | Where to get it | Description |
|---|---|---|
| `MPESA_ENV` | — | `sandbox` for testing, `production` for live payments |
| `MPESA_CONSUMER_KEY` | Daraja developer portal, under your app's credentials | Identifies your application to Safaricom |
| `MPESA_CONSUMER_SECRET` | Daraja developer portal | The secret paired with the consumer key |
| `MPESA_SHORTCODE` | Daraja portal — `174379` is the sandbox test shortcode | The till or paybill number clients pay to |
| `MPESA_PASSKEY` | Daraja portal, under Lipa Na M-Pesa Online | Used to generate the STK Push password |
| `MPESA_CALLBACK_URL` | Must be a publicly reachable HTTPS URL | The URL Safaricom calls when a payment succeeds or fails. In local development, use a tool like [ngrok](https://ngrok.com) to expose your local server. |

> **Note**: M-Pesa STK Push requires the callback URL to be a live, publicly reachable HTTPS address. It will not work with `localhost` unless you use a tunnelling tool.

### Africa's Talking (SMS) settings

Register at [https://africastalking.com](https://africastalking.com) to get API credentials.

| Constant | Where to get it | Description |
|---|---|---|
| `AT_USERNAME` | Africa's Talking dashboard | Your account username. Use `sandbox` for testing. |
| `AT_API_KEY` | Africa's Talking dashboard, under API Key | Your secret API key |
| `AT_SENDER_ID` | Africa's Talking dashboard — must be approved for production | The name that appears as the SMS sender |
| `AT_ENV` | — | `sandbox` for testing, `production` for live SMS |

### Email (SMTP) settings

The system sends emails via any SMTP server. Gmail works well for low volumes.

| Constant | Example value | Description |
|---|---|---|
| `MAIL_HOST` | `smtp.gmail.com` | Your email provider's SMTP server address |
| `MAIL_PORT` | `587` | SMTP port. Use `587` for TLS, `465` for SSL. |
| `MAIL_USERNAME` | `your@gmail.com` | The email address you are sending from |
| `MAIL_PASSWORD` | — | For Gmail: create an App Password at [https://myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords). Do not use your regular Gmail password. |
| `MAIL_ENCRYPTION` | `tls` | Use `tls` for port 587 or `ssl` for port 465 |

---

## 8. Running the application

### Starting the server (development)

Every time you want to use the application, open a terminal and run:

```bash
cd /path/to/beauty-parlor-management-sys/backend/public
php -S localhost:8000 router.php
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

### Stopping the server

In the terminal where the server is running, press `Ctrl + C`.

### Viewing application logs

Errors and events are written to:

```
backend/storage/logs/app.log
```

In a second terminal, you can watch new log entries appear in real time:

```bash
tail -f /path/to/beauty-parlor-management-sys/backend/storage/logs/app.log
```

---

## 9. Default login credentials

After completing the setup, log in with these credentials:

| Field | Value |
|---|---|
| Email | `admin@mbagathi.com` |
| Password | `password` |
| Role | Administrator |

> **Change the admin password immediately after your first login.** To do so, log in to MySQL and run:
>
> ```sql
> UPDATE mbagathi_parlour.users
> SET password_hash = '$2y$12$YOUR_NEW_HASH_HERE'
> WHERE email = 'admin@mbagathi.com';
> ```
>
> Generate a bcrypt hash for your new password with: `php -r "echo password_hash('yournewpassword', PASSWORD_BCRYPT, ['cost' => 12]);"`

### Creating additional users

- **Client accounts** are created through the registration page at `http://localhost:8000/register`.
- **Staff and additional admin accounts** must be created directly in the database by an administrator, or through the admin panel once the staff management UI is wired up.

---

## 10. Project structure

```
beauty-parlor-management-sys/
│
├── mbagathi_schema.sql          Database schema and seed data — run this once to set up MySQL
│
├── backend/
│   │
│   ├── config/
│   │   └── app.php              All configuration: database, M-Pesa, SMS, email, sessions
│   │
│   ├── core/                    The custom MVC framework (no external dependencies)
│   │   ├── Router.php           Matches incoming URLs to controller actions
│   │   ├── Middleware.php       Authentication and role-based access guards
│   │   ├── Controller.php       Base class for all controllers (JSON helpers, validation)
│   │   ├── Model.php            Base class for all models
│   │   ├── Database.php         PDO singleton — all database queries go through this
│   │   └── helpers.php          Utility functions (Logger, redirect, e(), kes())
│   │
│   ├── app/
│   │   ├── controllers/         One controller per feature area
│   │   │   ├── AuthController.php
│   │   │   ├── AppointmentController.php
│   │   │   ├── AdminController.php
│   │   │   ├── ClientController.php
│   │   │   ├── StaffController.php
│   │   │   ├── InventoryController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── ServiceController.php
│   │   │   └── ReportController.php
│   │   │
│   │   ├── models/              One model per database entity
│   │   │   ├── UserModel.php
│   │   │   ├── ClientModel.php
│   │   │   ├── AppointmentModel.php
│   │   │   ├── ServiceModel.php
│   │   │   ├── StaffModel.php
│   │   │   ├── ProductModel.php
│   │   │   ├── PaymentModel.php
│   │   │   └── NotificationModel.php
│   │   │
│   │   └── views/               PHP HTML templates, one folder per role
│   │       ├── layouts/         Shared page structure (head.php, nav.php, scripts.php)
│   │       ├── auth/            Login and registration pages
│   │       ├── admin/           Admin dashboard, appointments, staff, inventory, reports
│   │       ├── staff/           Staff dashboard and schedule
│   │       ├── client/          Client dashboard and booking form
│   │       └── errors/          403 and 404 error pages
│   │
│   ├── routes/
│   │   ├── web.php              URL routes that return HTML pages
│   │   └── api.php              URL routes that return JSON (used by the frontend JS)
│   │
│   ├── public/                  Web root — this is the only folder the web server needs to expose
│   │   ├── index.php            Front controller — every request starts here
│   │   ├── router.php           PHP built-in server router (dev only)
│   │   ├── .htaccess            Apache rewrite rules (production)
│   │   ├── css/app.css          All styles — rose and mauve design system
│   │   └── js/app.js            All frontend JavaScript
│   │
│   └── storage/
│       └── logs/
│           └── app.log          Application log file (errors, M-Pesa callbacks, etc.)
│
└── tests/                       Test suite
```

### How a request flows through the system

1. The browser sends a request, e.g. `GET /client/book`.
2. `public/index.php` (the front controller) loads all configuration, core classes, and starts the session.
3. The `Router` matches the URL against the registered routes in `routes/web.php` or `routes/api.php`.
4. Any `Middleware` attached to that route runs first (e.g. `Middleware::role('client')` checks that a client is logged in).
5. The matched controller action runs (e.g. `ClientController::bookingForm()`), which queries the database via a Model, then renders a view.
6. The browser receives the HTML page.

---

## 11. API reference

The application has a JSON API used by the frontend JavaScript. All API routes are prefixed with `/api`. Responses follow this structure:

**Success:**
```json
{
  "success": true,
  "message": "...",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "...",
  "errors": { ... }
}
```

### Authentication

| Method | Path | Auth required | Description |
|---|---|---|---|
| `POST` | `/api/auth/register` | No | Create a new client account |
| `POST` | `/api/auth/login` | No | Log in, start a session |
| `POST` | `/api/auth/logout` | Yes | End the current session |

### Services

| Method | Path | Auth required | Description |
|---|---|---|---|
| `GET` | `/api/service-categories` | No | List all service categories |
| `POST` | `/api/service-categories` | Admin | Create a new category |
| `GET` | `/api/services` | No | List all active services |
| `GET` | `/api/services/{id}` | No | Get a single service |
| `POST` | `/api/services` | Admin | Create a new service |
| `PUT` | `/api/services/{id}` | Admin | Update a service |
| `DELETE` | `/api/services/{id}` | Admin | Deactivate a service |

### Appointments

| Method | Path | Auth required | Description |
|---|---|---|---|
| `GET` | `/api/appointments` | Yes | List appointments (filtered by role) |
| `POST` | `/api/appointments` | Yes | Book a new appointment |
| `GET` | `/api/appointments/{id}` | Yes | Get a single appointment |
| `PUT` | `/api/appointments/{id}` | Staff/Admin | Update appointment status |
| `DELETE` | `/api/appointments/{id}` | Yes | Cancel an appointment |
| `GET` | `/api/appointments/today` | Staff/Admin | List today's appointments |

### Payments

| Method | Path | Auth required | Description |
|---|---|---|---|
| `POST` | `/api/payments/mpesa/initiate` | Yes | Send an M-Pesa STK Push to the client's phone |
| `POST` | `/api/payments/mpesa/callback` | No (Safaricom only) | Receive payment confirmation from Safaricom |
| `POST` | `/api/payments/cash` | Staff/Admin | Record a cash payment |

### Staff and Shifts

| Method | Path | Auth required | Description |
|---|---|---|---|
| `GET` | `/api/staff` | Admin | List all staff members |
| `POST` | `/api/staff` | Admin | Add a new staff member |
| `PUT` | `/api/staff/{id}` | Admin | Update staff details |
| `GET` | `/api/staff/{id}/shifts` | Staff/Admin | Get shifts for a staff member |
| `POST` | `/api/shifts` | Admin | Create a new shift |
| `POST` | `/api/attendance` | Admin | Record attendance for a shift |

### Inventory

| Method | Path | Auth required | Description |
|---|---|---|---|
| `GET` | `/api/products` | Staff/Admin | List all products |
| `POST` | `/api/products` | Admin | Add a new product |
| `GET` | `/api/products/{id}` | Staff/Admin | Get a single product |
| `PUT` | `/api/products/{id}` | Admin | Update a product |
| `GET` | `/api/products/{id}/movements` | Staff/Admin | Get stock movement history for a product |
| `POST` | `/api/stock-movements` | Staff/Admin | Record a stock movement (purchase, usage, etc.) |

### Reports

| Method | Path | Auth required | Description |
|---|---|---|---|
| `GET` | `/api/reports/revenue` | Admin | Daily revenue data |
| `GET` | `/api/reports/appointments` | Admin | Appointment statistics |
| `GET` | `/api/reports/low-stock` | Staff/Admin | Products below their low-stock threshold |
| `GET` | `/api/reports/attendance` | Admin | Staff attendance summary |

---

## 12. Running tests

The test suite lives in the `tests/` directory.

```bash
cd /path/to/beauty-parlor-management-sys
php tests/run.php
```

> **Note**: Detailed test instructions will be added here as the test suite is developed. If you encounter an error running the tests, please open an issue.

---

## 13. Deploying to a live server

The following steps outline what is different when running the application on a production server (e.g. a VPS with Apache) rather than your local machine.

### Step 1 — Point Apache at the public directory

The web server must be configured to serve files from `backend/public/`, not from `backend/` or the project root. The `backend/public/.htaccess` file handles URL rewriting for Apache.

A minimal Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/beauty-parlor-management-sys/backend/public

    <Directory /var/www/beauty-parlor-management-sys/backend/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable the rewrite module and restart Apache:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Step 2 — Update configuration

In `backend/config/app.php`:

- Set `APP_ENV` to `production`
- Set `APP_URL` to your actual domain, e.g. `https://yourdomain.com`
- Set `APP_DEBUG` will automatically become `false`
- Replace all placeholder values for M-Pesa, Africa's Talking, and email with your real credentials
- Replace `JWT_SECRET` with a strong random string (run `openssl rand -hex 32`)
- Change the `DB_PASS` to a strong password

### Step 3 — Set file permissions

The storage directory must be writable by the web server:

```bash
sudo chown -R www-data:www-data backend/storage
sudo chmod -R 755 backend/storage
```

### Step 4 — Set up HTTPS

M-Pesa requires HTTPS for the callback URL. Use Let's Encrypt (free):

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

---

## 14. Troubleshooting

### "Database unavailable" or blank page when visiting the site

**Cause**: PHP cannot connect to MySQL.

**Steps to fix:**
1. Confirm MySQL is running: `sudo service mysql status`
2. If it says "inactive", start it: `sudo service mysql start`
3. Open `backend/config/app.php` and confirm `DB_USER`, `DB_PASS`, and `DB_NAME` are correct
4. Test the connection manually:
   ```bash
   mysql -u mbagathi -p mbagathi_parlour
   ```
   Type the password when prompted. If this fails, the credentials in `config/app.php` are wrong.

---

### "Page not found" (404) for every URL

**Cause**: The PHP server was not started with `router.php`, or the `.htaccess` rewrite is not working.

**Steps to fix:**
1. Stop the server (`Ctrl + C`) and restart it from the correct directory:
   ```bash
   cd backend/public
   php -S localhost:8000 router.php
   ```
2. Make sure you are running the command from inside `backend/public/`, not from the project root.

---

### Login fails with "Invalid email or password" on the first login

**Cause**: The admin user was not inserted correctly, or was inserted into the wrong database.

**Steps to fix:**

Verify the user exists:

```bash
sudo mysql -e "SELECT id, email, role_id FROM mbagathi_parlour.users;"
```

If the row is missing, re-run the INSERT from [Step 6](#step-6--create-the-first-admin-account).

---

### M-Pesa STK Push does nothing (no prompt on phone)

**Likely causes:**
1. `MPESA_CONSUMER_KEY`, `MPESA_CONSUMER_SECRET`, or `MPESA_PASSKEY` contain placeholder values — replace them with your real Daraja sandbox credentials
2. `MPESA_CALLBACK_URL` is set to `http://localhost:8000/...` — Safaricom cannot reach a local server. Use [ngrok](https://ngrok.com) to create a public tunnel: `ngrok http 8000`, then update `MPESA_CALLBACK_URL` with the ngrok HTTPS URL
3. The phone number entered is not in Kenyan format — use `07XXXXXXXX` or `+2547XXXXXXXX`

---

### SMS notifications are not being sent

**Likely causes:**
1. `AT_API_KEY` is still set to `YOUR_AT_API_KEY` — replace it with your Africa's Talking sandbox API key
2. `AT_ENV` is set to `production` but your account is not yet live — change it to `sandbox` for testing

---

### PHP shows a white screen with no error

**Cause**: `APP_DEBUG` is `false` (production mode) so errors are hidden.

**Fix**: In `backend/config/app.php`, temporarily set `APP_ENV` to `development`. Check `backend/storage/logs/app.log` and `backend/storage/logs/php_errors.log` for the actual error message.

---

### "Permission denied" writing to the log file

**Cause**: The web server does not have permission to write to `backend/storage/logs/`.

**Fix**:
```bash
sudo chmod -R 777 backend/storage/logs
```

For production, use `755` and change the owner to the web server user (`www-data` on Ubuntu/Apache).

---

## 15. FAQ

**Q: Do I need to know how to code to use this system?**

No. Once it is set up, the system is operated entirely through a web browser. The setup process does require running a few commands in a terminal, but each step is explained in full detail above.

---

**Q: Can multiple people use the system at the same time?**

Yes. The system is a web application. Any number of staff, admins, and clients can be logged in and using it simultaneously from different computers or phones, as long as they can reach the server.

---

**Q: Do clients need to install anything?**

No. Clients use any web browser on their phone or computer. There is no app to install.

---

**Q: What happens if a client's M-Pesa payment fails?**

The payment is marked as "failed" in the system. The appointment remains in a pending state. The client can try again from the booking page, or pay in cash at the salon.

---

**Q: Can I use a different payment method, not M-Pesa?**

Cash payment is supported out of the box. Adding other payment providers would require a developer to write a new payment controller and integrate the provider's API.

---

**Q: How do I add a new service (e.g. a new type of massage)?**

Log in as an administrator and go to the Services section. You can add a new service under any existing category, or create a new category first.

---

**Q: Is the client's payment information stored in our database?**

No sensitive card or account details are stored. For M-Pesa, the system stores the M-Pesa confirmation receipt number and the transaction timestamp returned by Safaricom after a successful payment. No PINs or account credentials are ever transmitted to or stored by this system.

---

**Q: The system was working and now it shows "Database unavailable". What happened?**

MySQL may have stopped running. This can happen after a computer restart. Fix it with:

```bash
sudo service mysql start
```

---

**Q: Where do I go if I have a problem not listed here?**

Check the application log at `backend/storage/logs/app.log`. It records every error and event with a timestamp. If you cannot resolve the issue from the log, open an issue in the project's GitHub repository and paste the relevant log lines.

---

## License

See the `LICENSE` file in the project root.
