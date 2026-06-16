-- =============================================================
-- Mbagathi Beauty Parlour Management System
-- Database Schema — MySQL 8.0
-- =============================================================
-- Encoding : utf8mb4 (full Unicode + emoji support)
-- Engine    : InnoDB (FK enforcement, transactions)
-- Naming    : snake_case, plural table names
-- =============================================================

CREATE DATABASE IF NOT EXISTS mbagathi_parlour
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mbagathi_parlour;

SET FOREIGN_KEY_CHECKS = 0;


-- =============================================================
-- MODULE: Role-Based Access Control (RBAC)
-- Tables: roles, users
-- =============================================================

CREATE TABLE roles (
  id        TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name      ENUM('client','staff','admin') NOT NULL,
  label     VARCHAR(50)  NOT NULL COMMENT 'Human-readable label',
  created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB COMMENT='Permission roles: client, staff, admin';

INSERT INTO roles (name, label) VALUES
  ('client', 'Client'),
  ('staff',  'Staff Member'),
  ('admin',  'Administrator');


CREATE TABLE users (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  role_id       TINYINT UNSIGNED NOT NULL,
  full_name     VARCHAR(120)     NOT NULL,
  email         VARCHAR(180)     NOT NULL,
  phone         VARCHAR(20)      NOT NULL COMMENT 'Kenyan format: 07XXXXXXXX or +2547XXXXXXXX',
  password_hash VARCHAR(255)     NOT NULL COMMENT 'bcrypt hash — never plain text',
  is_active     TINYINT(1)       NOT NULL DEFAULT 1,
  last_login_at DATETIME             NULL,
  created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_phone (phone),
  KEY idx_users_role (role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE=InnoDB COMMENT='All system users — clients, staff, admins';


-- =============================================================
-- MODULE: Client Profile
-- Tables: clients
-- =============================================================

CREATE TABLE clients (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id         INT UNSIGNED NOT NULL,
  date_of_birth   DATE             NULL,
  gender          ENUM('female','male','other','prefer_not_to_say') NULL,
  address         VARCHAR(255)     NULL,
  allergies       TEXT             NULL COMMENT 'Free-text allergy / sensitivity notes',
  hair_type       VARCHAR(80)      NULL COMMENT 'e.g. Natural 4C, Relaxed, Locs',
  skin_type       VARCHAR(80)      NULL COMMENT 'e.g. Oily, Dry, Combination',
  notes           TEXT             NULL COMMENT 'Additional stylist notes',
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_clients_user (user_id),
  CONSTRAINT fk_clients_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Extended profile data for client-role users';


-- =============================================================
-- MODULE: Staff Scheduling & Attendance
-- Tables: staff_profiles, shifts, attendance_records
-- =============================================================

CREATE TABLE staff_profiles (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id         INT UNSIGNED NOT NULL,
  job_title       VARCHAR(100) NOT NULL COMMENT 'e.g. Senior Stylist, Nail Technician',
  specialisations VARCHAR(255)     NULL COMMENT 'Comma-separated service areas',
  hire_date       DATE             NULL,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_staff_user (user_id),
  CONSTRAINT fk_staff_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Extended profile for staff-role users';


CREATE TABLE shifts (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  staff_id        INT UNSIGNED NOT NULL COMMENT 'References staff_profiles.id',
  shift_date      DATE         NOT NULL,
  start_time      TIME         NOT NULL,
  end_time        TIME         NOT NULL,
  notes           VARCHAR(255)     NULL,
  created_by      INT UNSIGNED NOT NULL COMMENT 'Admin user who created the shift',
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_shifts_staff_date (staff_id, shift_date),
  CONSTRAINT fk_shifts_staff   FOREIGN KEY (staff_id)   REFERENCES staff_profiles (id) ON DELETE CASCADE,
  CONSTRAINT fk_shifts_creator FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB COMMENT='Scheduled work shifts for each staff member';


CREATE TABLE attendance_records (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  shift_id        INT UNSIGNED NOT NULL,
  staff_id        INT UNSIGNED NOT NULL,
  clock_in_at     DATETIME         NULL,
  clock_out_at    DATETIME         NULL,
  status          ENUM('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  notes           VARCHAR(255)     NULL,
  recorded_by     INT UNSIGNED NOT NULL COMMENT 'Admin who recorded the entry',
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_attendance_shift (shift_id),
  KEY idx_attendance_staff (staff_id),
  CONSTRAINT fk_attendance_shift    FOREIGN KEY (shift_id)    REFERENCES shifts (id) ON DELETE CASCADE,
  CONSTRAINT fk_attendance_staff    FOREIGN KEY (staff_id)    REFERENCES staff_profiles (id),
  CONSTRAINT fk_attendance_recorder FOREIGN KEY (recorded_by) REFERENCES users (id)
) ENGINE=InnoDB COMMENT='Daily attendance log keyed to scheduled shifts';


-- =============================================================
-- MODULE: Services Catalogue
-- Tables: service_categories, services
-- =============================================================

CREATE TABLE service_categories (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL COMMENT 'e.g. Hairdressing, Nail Care, Skincare',
  description TEXT             NULL,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_svccat_name (name)
) ENGINE=InnoDB COMMENT='Top-level groupings for salon services';

INSERT INTO service_categories (name) VALUES
  ('Hairdressing'),
  ('Hair Colouring & Treatment'),
  ('Nail Care'),
  ('Facial & Skincare'),
  ('Makeup'),
  ('Eyebrow & Threading');


CREATE TABLE services (
  id            SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id   SMALLINT UNSIGNED NOT NULL,
  name          VARCHAR(150) NOT NULL,
  description   TEXT             NULL,
  price         DECIMAL(8,2) NOT NULL COMMENT 'Price in KES',
  duration_mins SMALLINT UNSIGNED NOT NULL COMMENT 'Estimated service duration in minutes',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_services_category (category_id),
  CONSTRAINT fk_services_category FOREIGN KEY (category_id) REFERENCES service_categories (id)
) ENGINE=InnoDB COMMENT='Individual bookable services with pricing in KES';


-- =============================================================
-- MODULE: Client Booking Portal & Appointments
-- Tables: appointments, appointment_services
-- =============================================================

CREATE TABLE appointments (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id       INT UNSIGNED NOT NULL COMMENT 'References clients.id',
  staff_id        INT UNSIGNED     NULL COMMENT 'Assigned stylist — nullable until confirmed',
  appointment_date DATE         NOT NULL,
  start_time      TIME         NOT NULL,
  end_time        TIME         NOT NULL,
  status          ENUM('pending','confirmed','in_progress','completed','cancelled','no_show')
                              NOT NULL DEFAULT 'pending',
  notes           TEXT             NULL COMMENT 'Client special requests',
  total_amount    DECIMAL(8,2) NOT NULL DEFAULT 0.00 COMMENT 'Sum of booked service prices in KES',
  cancelled_at    DATETIME         NULL,
  cancel_reason   VARCHAR(255)     NULL,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_appt_client      (client_id),
  KEY idx_appt_staff_date  (staff_id, appointment_date),
  KEY idx_appt_date_status (appointment_date, status),
  CONSTRAINT fk_appt_client FOREIGN KEY (client_id) REFERENCES clients (id),
  CONSTRAINT fk_appt_staff  FOREIGN KEY (staff_id)  REFERENCES staff_profiles (id)
) ENGINE=InnoDB COMMENT='Appointment bookings — one row per visit';


CREATE TABLE appointment_services (
  id             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  appointment_id INT UNSIGNED   NOT NULL,
  service_id     SMALLINT UNSIGNED NOT NULL,
  price_at_booking DECIMAL(8,2) NOT NULL COMMENT 'Snapshot of price at time of booking',
  PRIMARY KEY (id),
  KEY idx_apptsvcs_appointment (appointment_id),
  CONSTRAINT fk_apptsvcs_appt    FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE CASCADE,
  CONSTRAINT fk_apptsvcs_service FOREIGN KEY (service_id)     REFERENCES services (id)
) ENGINE=InnoDB COMMENT='Line items linking appointments to one or more services';


-- =============================================================
-- MODULE: Inventory Management
-- Tables: product_categories, products, stock_movements
-- =============================================================

CREATE TABLE product_categories (
  id         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(100) NOT NULL COMMENT 'e.g. Hair Products, Nail Supplies, Skincare',
  created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_prodcat_name (name)
) ENGINE=InnoDB;

INSERT INTO product_categories (name) VALUES
  ('Hair Products'),
  ('Nail Supplies'),
  ('Skincare & Facial'),
  ('Makeup & Cosmetics'),
  ('Tools & Equipment'),
  ('Consumables & Sundries');


CREATE TABLE products (
  id                 INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  category_id        SMALLINT UNSIGNED NOT NULL,
  name               VARCHAR(150)      NOT NULL,
  brand              VARCHAR(100)          NULL,
  sku                VARCHAR(80)           NULL COMMENT 'Stock Keeping Unit code',
  unit               VARCHAR(40)       NOT NULL DEFAULT 'piece' COMMENT 'e.g. piece, bottle, tube, ml',
  current_stock      INT              NOT NULL DEFAULT 0,
  low_stock_threshold INT             NOT NULL DEFAULT 5 COMMENT 'Alert fires when stock falls to this level',
  reorder_quantity   INT              NOT NULL DEFAULT 10 COMMENT 'Suggested quantity when reordering',
  unit_cost          DECIMAL(8,2)     NOT NULL DEFAULT 0.00 COMMENT 'Purchase cost in KES',
  is_active          TINYINT(1)       NOT NULL DEFAULT 1,
  created_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_sku (sku),
  KEY idx_products_category (category_id),
  KEY idx_products_lowstock (current_stock, low_stock_threshold),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories (id)
) ENGINE=InnoDB COMMENT='Salon product inventory with low-stock threshold per item';


CREATE TABLE stock_movements (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id     INT UNSIGNED NOT NULL,
  movement_type  ENUM('purchase','usage','adjustment','return','waste') NOT NULL,
  quantity_change INT         NOT NULL COMMENT 'Positive = stock in, negative = stock out',
  stock_after    INT         NOT NULL COMMENT 'Running balance after this movement',
  reference_id   INT UNSIGNED    NULL COMMENT 'Optional: links to appointment_id for usage movements',
  notes          VARCHAR(255)    NULL,
  recorded_by    INT UNSIGNED NOT NULL,
  created_at     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_stockmov_product (product_id),
  KEY idx_stockmov_type    (movement_type),
  CONSTRAINT fk_stockmov_product  FOREIGN KEY (product_id)  REFERENCES products (id),
  CONSTRAINT fk_stockmov_recorder FOREIGN KEY (recorded_by) REFERENCES users (id)
) ENGINE=InnoDB COMMENT='Audit log of every stock change with running balance';


-- =============================================================
-- MODULE: M-Pesa & Cash Payment Processing
-- Tables: payments, mpesa_transactions
-- =============================================================

CREATE TABLE payments (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  appointment_id INT UNSIGNED  NOT NULL,
  amount         DECIMAL(8,2)  NOT NULL COMMENT 'Amount paid in KES',
  method         ENUM('mpesa','cash') NOT NULL,
  status         ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  paid_at        DATETIME          NULL,
  notes          VARCHAR(255)      NULL,
  recorded_by    INT UNSIGNED  NOT NULL COMMENT 'Staff or admin who recorded the payment',
  created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_payments_appointment (appointment_id),
  KEY idx_payments_status      (status),
  CONSTRAINT fk_payments_appointment FOREIGN KEY (appointment_id) REFERENCES appointments (id),
  CONSTRAINT fk_payments_recorder    FOREIGN KEY (recorded_by)    REFERENCES users (id)
) ENGINE=InnoDB COMMENT='Master payment record per appointment';


CREATE TABLE mpesa_transactions (
  id                  INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  payment_id          INT UNSIGNED  NOT NULL,
  phone_number        VARCHAR(20)   NOT NULL COMMENT 'MSISDN in 2547XXXXXXXX format',
  merchant_request_id VARCHAR(100)  NOT NULL COMMENT 'Daraja STK Push MerchantRequestID',
  checkout_request_id VARCHAR(100)  NOT NULL COMMENT 'Daraja STK Push CheckoutRequestID',
  mpesa_receipt       VARCHAR(20)       NULL COMMENT 'M-Pesa confirmation code e.g. QHX5KZDT9A',
  result_code         TINYINT           NULL COMMENT '0 = success, other = failure code',
  result_description  VARCHAR(255)      NULL,
  amount              DECIMAL(8,2)  NOT NULL,
  transaction_date    DATETIME          NULL COMMENT 'Timestamp from Daraja callback',
  created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mpesa_checkout (checkout_request_id),
  KEY idx_mpesa_payment (payment_id),
  KEY idx_mpesa_receipt (mpesa_receipt),
  CONSTRAINT fk_mpesa_payment FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Daraja STK Push request and callback data per M-Pesa transaction';


-- =============================================================
-- MODULE: SMS & Email Notifications
-- Tables: notification_templates, notifications
-- =============================================================

CREATE TABLE notification_templates (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code        VARCHAR(60)  NOT NULL COMMENT 'e.g. BOOKING_CONFIRMATION, APPOINTMENT_REMINDER, LOW_STOCK',
  channel     ENUM('sms','email','both') NOT NULL DEFAULT 'sms',
  subject     VARCHAR(200)     NULL COMMENT 'Email subject line — null for SMS-only templates',
  body        TEXT         NOT NULL COMMENT 'Template body. Use {{variable}} placeholders.',
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_notif_tmpl_code (code)
) ENGINE=InnoDB COMMENT='Reusable notification templates with placeholder variables';

INSERT INTO notification_templates (code, channel, subject, body) VALUES
  ('BOOKING_CONFIRMATION', 'both',
   'Booking Confirmed – Mbagathi Beauty Parlour',
   'Dear {{client_name}}, your appointment at Mbagathi Beauty Parlour is confirmed for {{appointment_date}} at {{appointment_time}}. Services: {{services}}. Reply CANCEL to cancel. Call us: {{parlour_phone}}.'),
  ('APPOINTMENT_REMINDER', 'both',
   'Appointment Reminder – Tomorrow at Mbagathi Beauty Parlour',
   'Hi {{client_name}}, just a reminder about your appointment tomorrow ({{appointment_date}}) at {{appointment_time}}. We look forward to seeing you! – Mbagathi Beauty Parlour.'),
  ('APPOINTMENT_CANCELLED', 'sms',
   NULL,
   'Hi {{client_name}}, your appointment on {{appointment_date}} at {{appointment_time}} has been cancelled. To rebook call {{parlour_phone}}. – Mbagathi Beauty Parlour.'),
  ('LOW_STOCK_ALERT', 'email',
   'Low Stock Alert – {{product_name}}',
   'Stock alert: {{product_name}} has fallen to {{current_stock}} {{unit}}(s), below the threshold of {{threshold}} {{unit}}(s). Suggested reorder quantity: {{reorder_quantity}}. Please restock soon.');


CREATE TABLE notifications (
  id              INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  template_id     SMALLINT UNSIGNED NOT NULL,
  recipient_user_id INT UNSIGNED        NULL COMMENT 'NULL for admin-only alerts with no specific user',
  channel         ENUM('sms','email') NOT NULL,
  recipient_address VARCHAR(200)    NOT NULL COMMENT 'Phone number or email address',
  subject         VARCHAR(200)          NULL,
  body            TEXT              NOT NULL COMMENT 'Rendered message after placeholder substitution',
  status          ENUM('queued','sent','failed','delivered') NOT NULL DEFAULT 'queued',
  provider_message_id VARCHAR(100)      NULL COMMENT 'Africa\'s Talking messageId or SMTP message-id',
  sent_at         DATETIME              NULL,
  created_at      DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notif_recipient (recipient_user_id),
  KEY idx_notif_status    (status),
  KEY idx_notif_template  (template_id),
  CONSTRAINT fk_notif_template  FOREIGN KEY (template_id)      REFERENCES notification_templates (id),
  CONSTRAINT fk_notif_recipient FOREIGN KEY (recipient_user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Outbound notification log — one row per message dispatched';


-- =============================================================
-- REPORTING SUPPORT
-- Views for the admin analytics dashboard
-- =============================================================

-- Daily revenue summary
CREATE OR REPLACE VIEW v_daily_revenue AS
  SELECT
    DATE(p.paid_at)                          AS revenue_date,
    COUNT(DISTINCT p.appointment_id)         AS appointments_paid,
    SUM(CASE WHEN p.method = 'cash'  THEN p.amount ELSE 0 END) AS cash_revenue,
    SUM(CASE WHEN p.method = 'mpesa' THEN p.amount ELSE 0 END) AS mpesa_revenue,
    SUM(p.amount)                            AS total_revenue
  FROM payments p
  WHERE p.status = 'completed'
  GROUP BY DATE(p.paid_at);


-- Appointment summary by status
CREATE OR REPLACE VIEW v_appointment_summary AS
  SELECT
    a.appointment_date,
    a.status,
    COUNT(*)                                 AS count,
    SUM(a.total_amount)                      AS total_value,
    CONCAT(u.full_name)                      AS staff_name
  FROM appointments a
  LEFT JOIN staff_profiles sp ON a.staff_id = sp.id
  LEFT JOIN users u            ON sp.user_id = u.id
  GROUP BY a.appointment_date, a.status, a.staff_id;


-- Products currently below low-stock threshold
CREATE OR REPLACE VIEW v_low_stock_products AS
  SELECT
    p.id,
    p.name,
    p.sku,
    pc.name        AS category,
    p.current_stock,
    p.low_stock_threshold,
    p.reorder_quantity,
    p.unit
  FROM products p
  JOIN product_categories pc ON p.category_id = pc.id
  WHERE p.current_stock <= p.low_stock_threshold
    AND p.is_active = 1
  ORDER BY (p.current_stock - p.low_stock_threshold) ASC;


-- Staff attendance summary (current month)
CREATE OR REPLACE VIEW v_staff_attendance_summary AS
  SELECT
    u.full_name                              AS staff_name,
    sp.job_title,
    COUNT(ar.id)                             AS total_shifts_recorded,
    SUM(ar.status = 'present')               AS present,
    SUM(ar.status = 'absent')                AS absent,
    SUM(ar.status = 'late')                  AS late,
    SUM(ar.status = 'half_day')              AS half_day
  FROM staff_profiles sp
  JOIN users u               ON sp.user_id = u.id
  LEFT JOIN shifts s         ON s.staff_id  = sp.id
    AND MONTH(s.shift_date) = MONTH(CURRENT_DATE)
    AND YEAR(s.shift_date)  = YEAR(CURRENT_DATE)
  LEFT JOIN attendance_records ar ON ar.shift_id = s.id
  GROUP BY sp.id, u.full_name, sp.job_title;


SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- END OF SCHEMA
-- Tables  : roles, users, clients, staff_profiles, shifts,
--           attendance_records, service_categories, services,
--           appointments, appointment_services,
--           product_categories, products, stock_movements,
--           payments, mpesa_transactions,
--           notification_templates, notifications
-- Views   : v_daily_revenue, v_appointment_summary,
--           v_low_stock_products, v_staff_attendance_summary
-- =============================================================
