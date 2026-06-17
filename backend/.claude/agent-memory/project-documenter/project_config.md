---
name: Configuration reference
description: Every constant in backend/config/app.php, what needs changing before go-live, and where to obtain third-party credentials
type: project
---

## File location

`backend/config/app.php` — loaded by `backend/public/index.php` before anything else.

## Constants defined

### Core
- APP_ENV: 'development' | 'production'
- APP_NAME: 'Mbagathi Beauty Parlour'
- APP_URL: 'http://localhost:8000' (change to real domain in production)
- APP_DEBUG: auto-derived from APP_ENV

### Database
- DB_HOST, DB_PORT (3306), DB_NAME (mbagathi_parlour), DB_USER (mbagathi), DB_PASS (Mbagathi2024!), DB_CHARSET (utf8mb4)

### Session
- SESSION_LIFETIME: 7200 seconds (2 hours)
- SESSION_NAME: mbagathi_sess

### Security
- JWT_SECRET: placeholder — MUST be replaced before production (use `openssl rand -hex 32`)
- BCRYPT_COST: 12

### M-Pesa (Daraja)
- MPESA_ENV: 'sandbox' | 'production'
- MPESA_CONSUMER_KEY, MPESA_CONSUMER_SECRET, MPESA_PASSKEY: from developer.safaricom.co.ke
- MPESA_SHORTCODE: '174379' (sandbox test shortcode)
- MPESA_CALLBACK_URL: APP_URL + '/api/payments/mpesa/callback' — must be public HTTPS in production
- MPESA_BASE_URL: auto-derived from MPESA_ENV

### Africa's Talking (SMS)
- AT_USERNAME: 'sandbox' for testing
- AT_API_KEY: from africastalking.com dashboard
- AT_SENDER_ID: 'Mbagathi' — must be approved by AT for production
- AT_ENV: 'sandbox' | 'production'

### Email (SMTP)
- MAIL_HOST: 'smtp.gmail.com'
- MAIL_PORT: 587
- MAIL_USERNAME, MAIL_PASSWORD: Gmail app password (not regular password)
- MAIL_FROM_NAME: APP_NAME
- MAIL_ENCRYPTION: 'tls'

### Paths
- STORAGE_PATH, LOG_PATH: derived from BASE_PATH (defined in public/index.php as dirname(__DIR__))

## What MUST change before going live

1. APP_ENV → 'production'
2. APP_URL → real domain
3. JWT_SECRET → strong random string
4. DB_PASS → strong password (update MySQL too)
5. All M-Pesa constants → real Daraja production credentials
6. MPESA_ENV → 'production'
7. AT_API_KEY → real Africa's Talking production key
8. AT_ENV → 'production'
9. MAIL_USERNAME / MAIL_PASSWORD → real SMTP credentials
