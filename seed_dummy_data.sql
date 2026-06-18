-- ================================================================
-- Mbagathi Beauty Parlour — Dummy Data Seed
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Staff users ───────────────────────────────────────────────
INSERT INTO users (role_id, full_name, email, phone, password_hash) VALUES
(2, 'Jane Wanjiku',  'jane@mbagathi.com',  '0712345678', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C'),
(2, 'Mary Akinyi',   'mary@mbagathi.com',  '0723456789', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C'),
(2, 'Grace Muthoni', 'grace@mbagathi.com', '0734567890', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C');

SET @jane_uid  = (SELECT id FROM users WHERE email = 'jane@mbagathi.com');
SET @mary_uid  = (SELECT id FROM users WHERE email = 'mary@mbagathi.com');
SET @grace_uid = (SELECT id FROM users WHERE email = 'grace@mbagathi.com');

INSERT INTO staff_profiles (user_id, job_title, specialisations, hire_date) VALUES
(@jane_uid,  'Senior Stylist',      'Hairdressing, Hair Colouring & Treatment', '2024-01-15'),
(@mary_uid,  'Nail Technician',     'Nail Care',                                '2024-03-01'),
(@grace_uid, 'Skincare Specialist', 'Facial & Skincare, Makeup',                '2024-06-01');

SET @jane_sid  = (SELECT id FROM staff_profiles WHERE user_id = @jane_uid);
SET @mary_sid  = (SELECT id FROM staff_profiles WHERE user_id = @mary_uid);
SET @grace_sid = (SELECT id FROM staff_profiles WHERE user_id = @grace_uid);

-- ── Client users ──────────────────────────────────────────────
INSERT INTO users (role_id, full_name, email, phone, password_hash) VALUES
(1, 'Alice Kamau',   'alice@email.com',  '0745678901', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C'),
(1, 'Betty Otieno',  'betty@email.com',  '0756789012', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C'),
(1, 'Carol Njeri',   'carol@email.com',  '0767890123', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C'),
(1, 'Diana Wangari', 'diana@email.com',  '0778901234', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C'),
(1, 'Eve Mutua',     'eve@email.com',    '0789012345', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C'),
(1, 'Faith Waweru',  'faith@email.com',  '0791234567', '$2y$12$BSlU6SLw8s27HU1JAVMCCe1FI1DYBDT335JUA85388jkihayFBh3C');

INSERT INTO clients (user_id) SELECT id FROM users WHERE role_id = 1;

SET @alice = (SELECT c.id FROM clients c JOIN users u ON c.user_id=u.id WHERE u.email='alice@email.com');
SET @betty = (SELECT c.id FROM clients c JOIN users u ON c.user_id=u.id WHERE u.email='betty@email.com');
SET @carol = (SELECT c.id FROM clients c JOIN users u ON c.user_id=u.id WHERE u.email='carol@email.com');
SET @diana = (SELECT c.id FROM clients c JOIN users u ON c.user_id=u.id WHERE u.email='diana@email.com');
SET @eve   = (SELECT c.id FROM clients c JOIN users u ON c.user_id=u.id WHERE u.email='eve@email.com');
SET @faith = (SELECT c.id FROM clients c JOIN users u ON c.user_id=u.id WHERE u.email='faith@email.com');
SET @adm   = (SELECT id FROM users WHERE email='admin@mbagathi.com');

-- ── Service shorthand ─────────────────────────────────────────
SET @wash   = (SELECT id FROM services WHERE name = 'Wash & Blow Dry');
SET @relax  = (SELECT id FROM services WHERE name = 'Relaxer Application');
SET @braids = (SELECT id FROM services WHERE name = 'Box Braids');
SET @weave  = (SELECT id FROM services WHERE name = 'Weave Installation');
SET @corn   = (SELECT id FROM services WHERE name = 'Cornrows');
SET @dcon   = (SELECT id FROM services WHERE name = 'Deep Conditioning');
SET @mani_b = (SELECT id FROM services WHERE name = 'Basic Manicure');
SET @mani_g = (SELECT id FROM services WHERE name = 'Gel Manicure');
SET @pedi_b = (SELECT id FROM services WHERE name = 'Basic Pedicure');
SET @pedi_s = (SELECT id FROM services WHERE name = 'Spa Pedicure');
SET @facial = (SELECT id FROM services WHERE name = 'Basic Facial');
SET @eyebr  = (SELECT id FROM services WHERE name = 'Eyebrow Threading');
SET @lip    = (SELECT id FROM services WHERE name = 'Upper Lip Threading');
SET @glam   = (SELECT id FROM services WHERE name = 'Full Glam Makeup');
SET @hi     = (SELECT id FROM services WHERE name = 'Highlights');

-- ── Appointments ──────────────────────────────────────────────
INSERT INTO appointments (client_id, staff_id, appointment_date, start_time, end_time, status, total_amount, created_at) VALUES
-- Jun 3
(@alice, @jane_sid,  '2026-06-03', '09:00', '10:00', 'completed',  600.00, '2026-06-02 14:00:00'),
(@betty, @mary_sid,  '2026-06-03', '10:00', '11:30', 'completed', 1700.00, '2026-06-02 15:00:00'),
-- Jun 4
(@carol, @jane_sid,  '2026-06-04', '09:00', '11:00', 'completed', 1800.00, '2026-06-03 10:00:00'),
(@diana, @grace_sid, '2026-06-04', '14:00', '15:00', 'completed', 1500.00, '2026-06-03 11:00:00'),
-- Jun 5
(@eve,   @mary_sid,  '2026-06-05', '10:00', '11:00', 'completed', 1000.00, '2026-06-04 09:00:00'),
(@alice, @grace_sid, '2026-06-05', '11:00', '12:00', 'completed', 1500.00, '2026-06-04 10:00:00'),
-- Jun 6
(@faith, @jane_sid,  '2026-06-06', '09:00', '11:00', 'completed', 2500.00, '2026-06-05 09:00:00'),
(@betty, @grace_sid, '2026-06-06', '13:00', '14:00', 'completed', 1500.00, '2026-06-05 10:00:00'),
-- Jun 7
(@carol, @mary_sid,  '2026-06-07', '10:00', '11:00', 'completed',  500.00, '2026-06-06 09:00:00'),
(@diana, @jane_sid,  '2026-06-07', '11:00', '13:00', 'completed', 3500.00, '2026-06-06 10:00:00'),
(@eve,   @jane_sid,  '2026-06-07', '14:00', '15:00', 'cancelled',  600.00, '2026-06-06 11:00:00'),
-- Jun 10
(@alice, @jane_sid,  '2026-06-10', '09:00', '10:00', 'completed',  600.00, '2026-06-09 09:00:00'),
(@faith, @mary_sid,  '2026-06-10', '10:00', '11:30', 'completed', 1500.00, '2026-06-09 10:00:00'),
-- Jun 11
(@betty, @jane_sid,  '2026-06-11', '09:00', '13:00', 'completed', 3500.00, '2026-06-10 09:00:00'),
(@carol, @grace_sid, '2026-06-11', '14:00', '15:30', 'completed', 2500.00, '2026-06-10 10:00:00'),
-- Jun 12
(@diana, @mary_sid,  '2026-06-12', '10:00', '11:00', 'completed', 1000.00, '2026-06-11 09:00:00'),
(@eve,   @jane_sid,  '2026-06-12', '11:00', '13:00', 'completed', 2600.00, '2026-06-11 10:00:00'),
-- Jun 13
(@alice, @grace_sid, '2026-06-13', '09:00', '10:30', 'completed', 1500.00, '2026-06-12 09:00:00'),
(@faith, @jane_sid,  '2026-06-13', '11:00', '13:00', 'completed', 1800.00, '2026-06-12 10:00:00'),
-- Jun 14
(@betty, @mary_sid,  '2026-06-14', '10:00', '11:30', 'completed', 1200.00, '2026-06-13 09:00:00'),
(@carol, @jane_sid,  '2026-06-14', '13:00', '14:00', 'completed',  600.00, '2026-06-13 10:00:00'),
(@diana, @grace_sid, '2026-06-14', '14:00', '15:00', 'completed', 1500.00, '2026-06-13 11:00:00'),
-- Today Jun 17
(@alice,  @jane_sid,  '2026-06-17', '09:00', '10:00', 'completed',   600.00, '2026-06-16 12:00:00'),
(@betty,  @mary_sid,  '2026-06-17', '10:00', '11:30', 'in_progress', 1700.00, '2026-06-16 13:00:00'),
(@carol,  @grace_sid, '2026-06-17', '11:00', '12:00', 'confirmed',   1500.00, '2026-06-16 14:00:00'),
(@diana,  @jane_sid,  '2026-06-17', '13:00', '15:00', 'confirmed',   3500.00, '2026-06-16 15:00:00'),
(@eve,    @mary_sid,  '2026-06-17', '14:00', '15:00', 'pending',     1000.00, '2026-06-17 08:00:00'),
-- Future
(@faith,  @jane_sid,  '2026-06-18', '09:00', '11:00', 'confirmed',  2400.00, '2026-06-17 07:00:00'),
(@alice,  @grace_sid, '2026-06-19', '10:00', '11:00', 'pending',    1500.00, '2026-06-17 08:30:00');

-- ── Appointment services ──────────────────────────────────────
SET @a1  = (SELECT id FROM appointments WHERE client_id=@alice AND appointment_date='2026-06-03');
SET @a2  = (SELECT id FROM appointments WHERE client_id=@betty AND appointment_date='2026-06-03');
SET @a3  = (SELECT id FROM appointments WHERE client_id=@carol AND appointment_date='2026-06-04');
SET @a4  = (SELECT id FROM appointments WHERE client_id=@diana AND appointment_date='2026-06-04');
SET @a5  = (SELECT id FROM appointments WHERE client_id=@eve   AND appointment_date='2026-06-05');
SET @a6  = (SELECT id FROM appointments WHERE client_id=@alice AND appointment_date='2026-06-05');
SET @a7  = (SELECT id FROM appointments WHERE client_id=@faith AND appointment_date='2026-06-06');
SET @a8  = (SELECT id FROM appointments WHERE client_id=@betty AND appointment_date='2026-06-06');
SET @a9  = (SELECT id FROM appointments WHERE client_id=@carol AND appointment_date='2026-06-07');
SET @a10 = (SELECT id FROM appointments WHERE client_id=@diana AND appointment_date='2026-06-07');
SET @a11 = (SELECT id FROM appointments WHERE client_id=@alice AND appointment_date='2026-06-10');
SET @a12 = (SELECT id FROM appointments WHERE client_id=@faith AND appointment_date='2026-06-10');
SET @a13 = (SELECT id FROM appointments WHERE client_id=@betty AND appointment_date='2026-06-11');
SET @a14 = (SELECT id FROM appointments WHERE client_id=@carol AND appointment_date='2026-06-11');
SET @a15 = (SELECT id FROM appointments WHERE client_id=@diana AND appointment_date='2026-06-12');
SET @a16 = (SELECT id FROM appointments WHERE client_id=@eve   AND appointment_date='2026-06-12');
SET @a17 = (SELECT id FROM appointments WHERE client_id=@alice AND appointment_date='2026-06-13');
SET @a18 = (SELECT id FROM appointments WHERE client_id=@faith AND appointment_date='2026-06-13');
SET @a19 = (SELECT id FROM appointments WHERE client_id=@betty AND appointment_date='2026-06-14');
SET @a20 = (SELECT id FROM appointments WHERE client_id=@carol AND appointment_date='2026-06-14');
SET @a21 = (SELECT id FROM appointments WHERE client_id=@diana AND appointment_date='2026-06-14');
SET @t1  = (SELECT id FROM appointments WHERE client_id=@alice  AND appointment_date='2026-06-17' AND status='completed');
SET @t2  = (SELECT id FROM appointments WHERE client_id=@betty  AND appointment_date='2026-06-17');
SET @t3  = (SELECT id FROM appointments WHERE client_id=@carol  AND appointment_date='2026-06-17');
SET @t4  = (SELECT id FROM appointments WHERE client_id=@diana  AND appointment_date='2026-06-17');
SET @t5  = (SELECT id FROM appointments WHERE client_id=@eve    AND appointment_date='2026-06-17');

INSERT INTO appointment_services (appointment_id, service_id, price_at_booking) VALUES
(@a1,  @wash,    600.00),
(@a2,  @mani_g, 1000.00), (@a2, @pedi_b, 500.00), (@a2, @eyebr, 200.00),
(@a3,  @relax,  1800.00),
(@a4,  @facial, 1500.00),
(@a5,  @mani_g, 1000.00),
(@a6,  @facial, 1500.00),
(@a7,  @weave,  2500.00),
(@a8,  @facial, 1500.00),
(@a9,  @mani_b,  500.00),
(@a10, @braids, 3500.00),
(@a11, @wash,    600.00),
(@a12, @pedi_s, 1500.00),
(@a13, @braids, 3500.00),
(@a14, @weave,  2500.00),
(@a15, @mani_g, 1000.00),
(@a16, @wash,    600.00), (@a16, @dcon, 800.00), (@a16, @hi, 1200.00),
(@a17, @facial, 1500.00),
(@a18, @relax,  1800.00),
(@a19, @mani_b,  500.00), (@a19, @pedi_b, 700.00),
(@a20, @wash,    600.00),
(@a21, @facial, 1500.00),
(@t1,  @wash,    600.00),
(@t2,  @mani_g, 1000.00), (@t2, @pedi_b, 700.00),
(@t3,  @facial, 1500.00),
(@t4,  @braids, 3500.00),
(@t5,  @mani_g, 1000.00);

-- Fix a16 total to match actual services (2600)
UPDATE appointments SET total_amount = 2600.00 WHERE id = @a16;

-- ── Payments ──────────────────────────────────────────────────
INSERT INTO payments (appointment_id, amount, method, status, paid_at, recorded_by) VALUES
(@a1,  600.00,  'cash',  'completed', '2026-06-03 10:05:00', @adm),
(@a2,  1700.00, 'mpesa', 'completed', '2026-06-03 11:40:00', @adm),
(@a3,  1800.00, 'cash',  'completed', '2026-06-04 11:10:00', @adm),
(@a4,  1500.00, 'mpesa', 'completed', '2026-06-04 15:10:00', @adm),
(@a5,  1000.00, 'cash',  'completed', '2026-06-05 11:05:00', @adm),
(@a6,  1500.00, 'mpesa', 'completed', '2026-06-05 12:10:00', @adm),
(@a7,  2500.00, 'mpesa', 'completed', '2026-06-06 11:15:00', @adm),
(@a8,  1500.00, 'cash',  'completed', '2026-06-06 14:10:00', @adm),
(@a9,   500.00, 'cash',  'completed', '2026-06-07 11:05:00', @adm),
(@a10, 3500.00, 'mpesa', 'completed', '2026-06-07 13:10:00', @adm),
(@a11,  600.00, 'cash',  'completed', '2026-06-10 10:05:00', @adm),
(@a12, 1500.00, 'mpesa', 'completed', '2026-06-10 11:40:00', @adm),
(@a13, 3500.00, 'mpesa', 'completed', '2026-06-11 13:10:00', @adm),
(@a14, 2500.00, 'cash',  'completed', '2026-06-11 15:40:00', @adm),
(@a15, 1000.00, 'cash',  'completed', '2026-06-12 11:05:00', @adm),
(@a16, 2600.00, 'mpesa', 'completed', '2026-06-12 13:15:00', @adm),
(@a17, 1500.00, 'cash',  'completed', '2026-06-13 10:40:00', @adm),
(@a18, 1800.00, 'mpesa', 'completed', '2026-06-13 13:10:00', @adm),
(@a19, 1200.00, 'cash',  'completed', '2026-06-14 11:05:00', @adm),
(@a20,  600.00, 'cash',  'completed', '2026-06-14 14:05:00', @adm),
(@a21, 1500.00, 'mpesa', 'completed', '2026-06-14 15:10:00', @adm),
(@t1,   600.00, 'cash',  'completed', '2026-06-17 10:05:00', @adm);

-- ── Shifts ────────────────────────────────────────────────────
INSERT INTO shifts (staff_id, shift_date, start_time, end_time, created_by) VALUES
(@jane_sid,'2026-06-03','08:00','17:00',@adm),(@jane_sid,'2026-06-04','08:00','17:00',@adm),
(@jane_sid,'2026-06-05','08:00','17:00',@adm),(@jane_sid,'2026-06-06','08:00','17:00',@adm),
(@jane_sid,'2026-06-07','08:00','14:00',@adm),(@jane_sid,'2026-06-10','08:00','17:00',@adm),
(@jane_sid,'2026-06-11','08:00','17:00',@adm),(@jane_sid,'2026-06-12','08:00','17:00',@adm),
(@jane_sid,'2026-06-13','08:00','17:00',@adm),(@jane_sid,'2026-06-14','08:00','14:00',@adm),
(@jane_sid,'2026-06-17','08:00','17:00',@adm),(@jane_sid,'2026-06-18','08:00','17:00',@adm),
(@mary_sid,'2026-06-03','08:00','17:00',@adm),(@mary_sid,'2026-06-04','08:00','17:00',@adm),
(@mary_sid,'2026-06-05','08:00','17:00',@adm),(@mary_sid,'2026-06-06','08:00','17:00',@adm),
(@mary_sid,'2026-06-07','08:00','14:00',@adm),(@mary_sid,'2026-06-10','08:00','17:00',@adm),
(@mary_sid,'2026-06-11','08:00','17:00',@adm),(@mary_sid,'2026-06-12','08:00','17:00',@adm),
(@mary_sid,'2026-06-13','08:00','17:00',@adm),(@mary_sid,'2026-06-14','08:00','14:00',@adm),
(@mary_sid,'2026-06-17','08:00','17:00',@adm),(@mary_sid,'2026-06-18','08:00','17:00',@adm),
(@grace_sid,'2026-06-04','09:00','17:00',@adm),(@grace_sid,'2026-06-05','09:00','17:00',@adm),
(@grace_sid,'2026-06-06','09:00','17:00',@adm),(@grace_sid,'2026-06-11','09:00','17:00',@adm),
(@grace_sid,'2026-06-12','09:00','17:00',@adm),(@grace_sid,'2026-06-13','09:00','17:00',@adm),
(@grace_sid,'2026-06-14','09:00','17:00',@adm),(@grace_sid,'2026-06-17','09:00','17:00',@adm);

-- ── Attendance (all past shifts) ──────────────────────────────
INSERT INTO attendance_records (shift_id, staff_id, clock_in_at, clock_out_at, status, recorded_by)
SELECT s.id, s.staff_id,
  CONCAT(s.shift_date,' ',s.start_time),
  CONCAT(s.shift_date,' ',s.end_time),
  CASE
    WHEN s.shift_date='2026-06-05' AND s.staff_id=@mary_sid  THEN 'absent'
    WHEN s.shift_date='2026-06-12' AND s.staff_id=@jane_sid  THEN 'late'
    WHEN s.shift_date='2026-06-13' AND s.staff_id=@grace_sid THEN 'late'
    ELSE 'present'
  END,
  @adm
FROM shifts s WHERE s.shift_date < CURDATE();

-- ── Products ──────────────────────────────────────────────────
SET @hair = (SELECT id FROM product_categories WHERE name='Hair Products');
SET @nail = (SELECT id FROM product_categories WHERE name='Nail Supplies');
SET @skin = (SELECT id FROM product_categories WHERE name='Skincare & Facial');
SET @tool = (SELECT id FROM product_categories WHERE name='Tools & Equipment');
SET @cons = (SELECT id FROM product_categories WHERE name='Consumables & Sundries');

INSERT INTO products (category_id, name, brand, sku, unit, current_stock, low_stock_threshold, reorder_quantity, unit_cost) VALUES
(@hair, 'Relaxer Kit Regular',  'Dark and Lovely', 'DL-RLX-01', 'kit',     12, 10, 20,  850.00),
(@hair, 'Relaxer Kit Super',    'Dark and Lovely', 'DL-RLX-02', 'kit',      3, 10, 20,  850.00),
(@hair, 'Shampoo Moisture',     'ORS',             'ORS-SH-01', 'bottle',  18,  8, 15,  450.00),
(@hair, 'Deep Conditioner',     'SheaMoisture',    'SM-DC-01',  'jar',      4,  8, 12,  700.00),
(@hair, 'Hair Cream',           'Sofnfree',        'SF-HC-01',  'jar',     20,  6, 10,  320.00),
(@hair, 'Edge Control',         'Eco Styler',      'ES-EC-01',  'jar',      2,  5, 10,  280.00),
(@nail, 'Gel Polish Set Nudes', 'OPI',             'OPI-GP-ND', 'set',      8,  5, 10, 2200.00),
(@nail, 'Acrylic Powder Clear', 'Young Nails',     'YN-AP-CL',  'bottle',  6,  5, 10, 1800.00),
(@nail, 'Nail Tips Natural',    NULL,              NULL,         'pack',   15,  8, 20,  150.00),
(@skin, 'Facial Cleanser',      'Cetaphil',        'CT-FC-01',  'bottle',  3,  5, 10,  800.00),
(@skin, 'Toner',                'Simple',          'SM-TN-01',  'bottle',  7,  4,  8,  650.00),
(@skin, 'Face Mask Charcoal',   'Garnier',         'GN-FM-CH',  'sachet', 30, 10, 30,  120.00),
(@tool, 'Hair Dryer Pro',       'Remington',       'RM-HD-PR',  'piece',   3,  1,  2, 8500.00),
(@tool, 'Flat Iron',            'GHD',             'GHD-FI-01', 'piece',   4,  2,  2,12000.00),
(@cons, 'Disposable Gloves M',  NULL,              NULL,         'box',    5,  5, 10,  350.00),
(@cons, 'Foil Sheets',          NULL,              NULL,         'roll',  12,  5, 10,  200.00),
(@cons, 'Cotton Wool',          NULL,              NULL,         'bag',    8,  5, 10,  150.00);

INSERT INTO stock_movements (product_id, movement_type, quantity_change, stock_after, notes, recorded_by)
SELECT id, 'purchase', current_stock, current_stock, 'Opening stock', @adm FROM products;

SET FOREIGN_KEY_CHECKS = 1;

SELECT CONCAT(
  (SELECT COUNT(*) FROM users WHERE role_id=2), ' staff, ',
  (SELECT COUNT(*) FROM users WHERE role_id=1), ' clients, ',
  (SELECT COUNT(*) FROM appointments), ' appointments, ',
  (SELECT COUNT(*) FROM payments WHERE status="completed"), ' paid, ',
  (SELECT COUNT(*) FROM shifts), ' shifts, ',
  (SELECT COUNT(*) FROM products), ' products'
) AS seeded;
