# Session Notes - January 17, 2026

## Overview

This session focused on two main areas:
1. **Security hardening** - Implementing rate limiting, security headers, and event logging
2. **Email investigation** - Diagnosing why receipt emails are not being sent

---

## Part 1: Security Implementation (COMPLETED)

### What Was Done

#### 1. Malware Removal
- **Deleted:** `site_demo/frontend/web/wp-blog-header.php` (PHP webshell)
- **Deleted:** `site_demo/frontend/web/wp-cron.php` (PHP webshell)
- **Deleted:** `site_demo/frontend/web/test.php` (exposed phpinfo)

#### 2. Cookie/Session Security
- **File:** `site_demo/frontend/config/main.php`
  - Added secure session configuration with `httpOnly`, `secure`, `sameSite` flags
  - Set 30-minute inactivity timeout

- **File:** `site_demo/frontend/controllers/SiteController.php`
  - Updated all cookie creation with secure flags

- **File:** `site_demo/frontend/modules/selfservice/controllers/DefaultController.php`
  - Updated cookie creation in `setMode()` method

#### 3. Login Rate Limiting with Exponential Backoff
- **File:** `site_demo/common/components/LoginRateLimiter.php` (NEW)
  - 10 max attempts per 15-minute window
  - Exponential backoff: 30s (4 attempts) → 2min (6) → 5min (8) → 30min lockout (10)
  - IP-based and username-based tracking

#### 4. API Rate Limiting
- **File:** `site_demo/common/components/ApiRateLimiter.php` (NEW)
  - Login: 5 requests/minute
  - Authenticated API: 100 requests/minute
  - Guest API: 30 requests/minute
  - Password reset: 3 per hour
  - Sets X-RateLimit-* response headers

#### 5. Security HTTP Headers
- **File:** `site_demo/common/components/SecurityHeaders.php` (NEW)
  - X-Content-Type-Options: nosniff
  - X-Frame-Options: SAMEORIGIN
  - X-XSS-Protection: 1; mode=block
  - Referrer-Policy: strict-origin-when-cross-origin
  - Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()
  - Strict-Transport-Security (HTTPS only)
  - Cross-Origin-Opener-Policy: same-origin
  - X-Download-Options: noopen

#### 6. Security Event Logging
- **File:** `site_demo/common/models/SecurityLog.php` (NEW)
  - Logs: login_success, login_failed, login_blocked, logout, password_change, rate_limit_exceeded
  - Captures: user_id, username, IP address, user agent, details, timestamp

#### 7. Database Migration
- **File:** `site_demo/console/migrations/m260117_200000_security_tables.php` (NEW)
  - Creates `login_attempts` table
  - Creates `security_log` table
  - Creates `rate_limit` table
  - Adds columns to `user` table: `failed_login_attempts`, `locked_until`, `last_failed_login`

#### 8. Configuration Updates
- **File:** `site_demo/common/config/main.php`
  - Registered `securityHeaders` bootstrap component
  - Registered `loginRateLimiter` component
  - Registered `apiRateLimiter` component

### Deployment Status
- All security features committed and pushed
- Deployed to Railway
- Database tables created via setup script
- Setup script deleted after use
- **Verified working:** Security headers present, rate limiting active

### Test Results
Login rate limiting tested successfully:
- First attempt allowed
- Subsequent attempts show "wait X seconds before trying again"
- IP-based tracking working correctly

---

## Part 2: Email Receipt Investigation (PENDING FIX)

### The Problem
When a session is marked as paid, users are supposed to receive an email receipt. This is not happening.

### Root Causes Identified

#### Issue 1: File Transport Enabled (CRITICAL)
**File:** `deploy-config/main-local.php` (line 16-20)
```php
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'viewPath' => '@common/mail',
    'useFileTransport' => true,  // ← SAVES TO FILES, DOESN'T SEND
],
```

**Impact:** All emails are saved to `runtime/mail/` directory instead of being sent via SMTP.

**Fix Required:** Set `useFileTransport` to `false` and add SMTP configuration.

#### Issue 2: No Queue Worker Running (CRITICAL)
**File:** `deploy-config/entrypoint.sh`

The system uses a database queue to process email jobs:
```php
Yii::$app->queue->push(new CheckMailSendJob(['visit_id' => $model->id]));
```

But the entrypoint only starts Apache - no queue worker is running.

**Impact:** Email jobs are pushed to the `queue` database table but never processed.

**Fix Required:** Add queue worker to entrypoint:
```sh
# Start queue worker in background
php /var/www/html/yii queue/listen &

# Then start Apache
exec apache2-foreground
```

#### Issue 3: No SMTP Configuration
**File:** `deploy-config/main-local.php`

No SMTP transport is configured. Required format:
```php
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'viewPath' => '@common/mail',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.example.com',
        'username' => 'your-email@example.com',
        'password' => 'your-password',
        'port' => '465',
        'encryption' => 'ssl',
    ],
],
```

**Fix Required:** Add SMTP credentials for email delivery service.

### Email Flow (How It Should Work)

1. User pays → `actionPay()` in `DefaultController.php`
2. Payment recorded → `makePay($method)` in `VisitorLog.php`
3. Print check called → `actionPrint_check()` in `DefaultController.php`
4. Check if can send email → `canSendMail()` verifies visitor has email
5. Queue email job → `CheckMailSendJob` pushed to queue
6. Queue worker processes → `CheckMailSendJob::execute()` runs
7. Load template → `Template::TYPE_CHECK_MAIL` (type 2)
8. Render content → `getCheckData()` + `renderTemplate()`
9. Send email → `Yii::$app->mailer->send()`

### Key Files for Email

| File | Purpose |
|------|---------|
| `console/jobs/CheckMailSendJob.php` | Queue job that sends the receipt email |
| `frontend/modules/visits/models/VisitorLog.php` | `canSendMail()`, `getVisitorEmail()`, `getCheckData()` |
| `frontend/modules/visits/controllers/DefaultController.php` | `actionPay()`, `actionPrint_check()` - triggers email |
| `frontend/modules/templates/models/Template.php` | Email template rendering |
| `common/config/params.php` | `robotEmail`, `checkMailSendDelay` settings |

### Pending Questions for Tomorrow
1. What SMTP service should be used? (SendGrid, Mailgun, Gmail, etc.)
2. Do you have SMTP credentials available?
3. Should we use environment variables for SMTP config on Railway?

---

## Git Status

### Recent Commits
```
24535cc - Remove setup_security_tables.php after successful execution
0494854 - Add security features: rate limiting, security headers, event logging
01c532c - (previous security/cleanup commits)
```

### Current Branch
`main` - up to date with `origin/main`

---

## Production URLs

- **Site:** https://kavork-app-production.up.railway.app/
- **Login:** https://kavork-app-production.up.railway.app/login
- **Healthcheck:** https://kavork-app-production.up.railway.app/healthcheck.php

---

## Next Steps for Tomorrow

### Priority 1: Fix Email Sending
1. Get SMTP credentials from user
2. Update `deploy-config/main-local.php`:
   - Set `useFileTransport` to `false`
   - Add SMTP transport configuration
3. Update `deploy-config/entrypoint.sh`:
   - Add queue worker: `php /var/www/html/yii queue/listen &`
4. Deploy and test email sending

### Priority 2: Verify Email Templates
1. Check if `Template::TYPE_CHECK_MAIL` (type 2) templates exist in database
2. Verify template content is valid

### Priority 3: Test End-to-End
1. Create a test session
2. Mark as paid
3. Verify email is queued
4. Verify email is sent
5. Check email content/formatting

---

## Security Audit Summary (From Earlier)

### Completed Fixes
- [x] Malware removed
- [x] Debug endpoint removed
- [x] Cookie security implemented
- [x] Login rate limiting implemented
- [x] API rate limiting implemented
- [x] Security headers implemented
- [x] Security event logging implemented

### Still Pending (Lower Priority)
- [ ] SQL injection fixes (9+ locations identified)
- [ ] XSS fixes (15+ locations identified)
- [ ] File upload validation improvements
- [ ] Enable CSP header (currently disabled)
- [ ] Remove hardcoded credentials from config files
- [ ] Add CAPTCHA for additional protection
- [ ] Implement 2FA for admin accounts

Full security audit documented in: `docs/SECURITY_AUDIT_2026-01-17.md`

---

## Environment Notes

- **PHP Version:** 8.0.30 (8.1 causes segfaults)
- **Database:** MySQL on Railway
- **Queue:** Database queue (`yii\queue\db\Queue`)
- **Mailer:** SwiftMailer (currently misconfigured)

---

*Session ended: January 17, 2026*
*Next session: Continue with email fix implementation*
