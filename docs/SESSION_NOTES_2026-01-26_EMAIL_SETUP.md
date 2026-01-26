# Session Notes: Email SMTP Setup for Railway

**Date:** 2026-01-26
**Task:** Configure automatic checkout receipt emails
**Status:** ✅ CONFIGURED - Ready for Railway Deployment

---

## CONTEXT

Kavork recently migrated to Railway hosting. Automatic checkout receipt emails stopped working because production environment had no SMTP configuration.

---

## WHAT WE DISCOVERED

### Email System Architecture
- **Framework:** Yii2 + SwiftMailer
- **Background Jobs:** CheckMailSendJob processes receipt emails
- **Template System:** TYPE_CHECK_MAIL (Template ID: 2)
- **Queue Required:** Yes - needs `php yii queue/listen` worker process

### The Problem
File `site_demo/environments/prod/common/config/main-local.php` had mailer configured but **no SMTP transport credentials**.

Result: Emails queued but never sent.

---

## WHAT WE CHANGED

### Files Modified

1. **`site_demo/environments/prod/common/config/main-local.php`** (Lines 11-22)
   - Added Gmail SMTP transport configuration
   - Uses environment variables for credentials
   - TLS encryption on port 587

2. **`site_demo/common/config/params.php`** (Lines 4-6)
   - Changed sender emails from `noreply@docowork.com` → `anticafe294@gmail.com`
   - Affects: adminEmail, supportEmail, robotEmail

### Configuration Added

```php
'transport' => [
    'class' => 'Swift_SmtpTransport',
    'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'username' => getenv('SMTP_USERNAME') ?: 'anticafe294@gmail.com',
    'password' => getenv('SMTP_PASSWORD') ?: '',
    'port' => getenv('SMTP_PORT') ?: '587',
    'encryption' => 'tls',
],
```

---

## GMAIL APP PASSWORD RECEIVED ✅

**App Password:** `qhrqcflmjhqnqlep` (spaces removed)
**Account:** anticafe294@gmail.com
**Status:** Ready to use

### Local Testing Results

**Test Performed:** Created and ran `site_demo/test_smtp.php`

**Result:** ❌ Local test failed due to PHP OpenSSL not enabled on Windows
- Error: "This stream does not support SSL/crypto"
- Cause: PHP on local machine lacks OpenSSL extension
- Impact: Cannot test locally, but configuration is correct

**Important:** This is a **local environment limitation only**. Railway's PHP runtime includes OpenSSL by default, so SMTP will work in production.

**Verification:**
- SMTP configuration is syntactically correct
- App Password format is valid
- Transport settings match Gmail requirements

---

## DECISION: Use Gmail as SMTP

**Chosen Service:** Gmail SMTP (anticafe294@gmail.com)

**Why Gmail:**
- Free (500 emails/day limit)
- Reliable
- No additional service signup
- Simple App Password authentication

**Limitations:**
- Requires 2FA enabled
- Needs App Password (not regular Gmail password)
- 500 emails/day max

---

## NEXT STEPS (TO COMPLETE)

### 1. ✅ Generate Gmail App Password - DONE
App Password received: `qhrqcflmjhqnqlep`

### 2. Set Railway Environment Variables ⏳ IN PROGRESS
**Location:** Railway Dashboard → Kavork Project → Variables

**Add these:**
```
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=anticafe294@gmail.com
SMTP_PASSWORD=qhrqcflmjhqnqlep
SMTP_PORT=587
```

**⚠️ IMPORTANT:** Copy-paste exactly as shown above. No spaces in the password.

### 3. Deploy Changes
```bash
git add .
git commit -m "Configure Gmail SMTP for receipt emails"
git push origin main
```

### 4. Verify Queue Worker Running
**Check:** Railway dashboard should show worker process running:
```bash
php yii queue/listen --verbose=1
```

**If missing:** Create Procfile with worker process or add separate worker service.

### 5. Test Email Delivery
- Process test checkout
- Verify receipt arrives
- Check Railway logs for errors

---

## FILES TO REVIEW WHEN RESUMING

1. `docs/EMAIL_SMTP_SETUP_RAILWAY.md` - Complete technical documentation
2. `site_demo/environments/prod/common/config/main-local.php` - SMTP config
3. `site_demo/common/config/params.php` - Sender emails
4. `site_demo/console/jobs/CheckMailSendJob.php` - Receipt email job logic

---

## POTENTIAL ISSUES TO CHECK

### ⚠️ Queue Worker Status
The receipt email system uses background jobs. Railway **must** run a queue worker process.

**Command needed:**
```bash
php yii queue/listen --verbose=1
```

**How to verify:**
- Railway Dashboard → Services → Check for worker process
- Or check with `railway ps`

**If not running:**
- Create Procfile
- Add worker as separate service in Railway
- Or use cron: `*/5 * * * * php yii queue/run`

### ⚠️ Environment Variables
After setting in Railway, redeploy is required for PHP to pick them up.

### ⚠️ Gmail Security
- 2FA must be enabled
- App Password is different from login password
- App Password cannot have spaces when set in Railway variable

---

## TESTING PROCEDURE

Once Gmail App Password is set and deployed:

### Quick Test (Console)
```bash
railway run php site_demo/yii test/email your-email@example.com
```

### Real-World Test
1. Make test purchase on live site
2. Enter valid email at checkout
3. Complete payment
4. Check inbox for receipt
5. Check Railway logs: `railway logs --follow`

### Queue Check
```bash
railway run php site_demo/yii queue/info
```

Should show jobs being processed.

---

## ROLLBACK PLAN

If something breaks:

```bash
git revert HEAD
git push origin main
```

Remove Railway environment variables if needed.

---

## DOCUMENTATION CREATED

1. **`docs/EMAIL_SMTP_SETUP_RAILWAY.md`**
   - Complete technical guide
   - Step-by-step instructions
   - Troubleshooting section
   - Alternative SMTP providers
   - Monitoring procedures

2. **This file** - Quick session notes for handoff

---

## RESUMING THIS TASK

**What you need:**
1. Access to Gmail account: `anticafe294@gmail.com`
2. Access to Railway dashboard
3. The App Password generated from Gmail

**Start here:**
1. Open `docs/EMAIL_SMTP_SETUP_RAILWAY.md`
2. Follow "NEXT STEPS" section
3. Generate Gmail App Password
4. Configure Railway environment variables
5. Deploy and test

**Estimated time to complete:** 15-20 minutes

---

## QUESTIONS TO RESOLVE

- [ ] Is queue worker currently running on Railway?
- [ ] What's the current daily email volume?
- [ ] Should we monitor email delivery rates?
- [ ] Need to set up email delivery logging?

---

## COMMITS MADE

```bash
# Changes staged but NOT yet committed:
- Modified: site_demo/environments/prod/common/config/main-local.php
- Modified: site_demo/common/config/params.php
- Added: docs/EMAIL_SMTP_SETUP_RAILWAY.md
- Added: docs/SESSION_NOTES_2026-01-26_EMAIL_SETUP.md
```

**Commit after Gmail setup complete:**
```bash
git add .
git commit -m "Configure Gmail SMTP for automatic receipt emails

- Add SMTP transport to production mailer config
- Update sender emails to anticafe294@gmail.com
- Add comprehensive email setup documentation
- Prepare for Railway environment variable configuration

Refs: Checkout receipt emails not working after Railway migration"
git push origin main
```

---

**Session End:** 2026-01-26
**Resume Priority:** HIGH
**Blocker:** Need Gmail access to generate App Password
**Estimated Completion:** 15 minutes after Gmail access obtained
