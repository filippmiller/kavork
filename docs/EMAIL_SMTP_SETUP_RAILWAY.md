# Email & SMTP Configuration for Kavork on Railway

**Date:** 2026-01-26
**Status:** IN PROGRESS - Awaiting Gmail App Password
**Priority:** HIGH - Checkout receipts not sending

---

## PROBLEM STATEMENT

After migrating to Railway hosting, automatic checkout receipt emails stopped working.

**Root Cause:** Production email configuration missing SMTP credentials.

---

## EMAIL SYSTEM ARCHITECTURE

### Framework & Components

- **Framework:** Yii2 with SwiftMailer
- **Email Handler:** `console\jobs\CheckMailSendJob.php`
- **Sender Address:** `anticafe294@gmail.com` (updated 2026-01-26)
- **Template Type:** `TYPE_CHECK_MAIL` (Template ID: 2)

### Email Flow for Checkout Receipts

1. Customer completes checkout
2. System triggers `StorefrontController::actionPrint_check()`
3. Job queued: `CheckMailSendJob` (background process)
4. Job retrieves:
   - Sale/visit record
   - Cafe details
   - Customer email address
   - Email template (Type: CHECK_MAIL)
5. Renders HTML receipt from template
6. Sends via SwiftMailer SMTP
7. Optional: Attaches PDF if `cafe->pdf_to_mail` enabled

### Key Files

| File | Purpose | Line Reference |
|------|---------|----------------|
| `site_demo/environments/prod/common/config/main-local.php` | Production SMTP config | 11-22 |
| `site_demo/common/config/params.php` | Email sender addresses | 4-6 |
| `site_demo/console/jobs/CheckMailSendJob.php` | Receipt email job | 56-82 |
| `site_demo/frontend/modules/shop/controllers/StorefrontController.php` | Checkout trigger | 379-425 |
| `site_demo/frontend/modules/visits/models/VisitorLog.php` | Email validation | 838-869 |

---

## CONFIGURATION CHANGES MADE (2026-01-26)

### 1. Production SMTP Configuration

**File:** `site_demo/environments/prod/common/config/main-local.php`

**Before:**
```php
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'viewPath' => '@common/mail',
],
```

**After:**
```php
'mailer' => [
    'class' => 'yii\swiftmailer\Mailer',
    'viewPath' => '@common/mail',
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'username' => getenv('SMTP_USERNAME') ?: 'anticafe294@gmail.com',
        'password' => getenv('SMTP_PASSWORD') ?: '',
        'port' => getenv('SMTP_PORT') ?: '587',
        'encryption' => 'tls',
    ],
],
```

### 2. Email Sender Addresses

**File:** `site_demo/common/config/params.php`

**Changed from:** `noreply@docowork.com`
**Changed to:** `anticafe294@gmail.com`

**Affected parameters:**
- `adminEmail`
- `supportEmail`
- `robotEmail` (used for receipt emails)

---

## GMAIL SMTP SETUP

### Why Gmail?

- **Free:** No cost for up to 500 emails/day
- **Reliable:** High deliverability rate
- **Simple:** No additional service signup needed

### Limitations

- **Daily limit:** 500 emails/day (Gmail) or 2,000/day (Google Workspace)
- **Requires:** 2-Factor Authentication + App Password

### Gmail Account

**Email:** `anticafe294@gmail.com`
**Purpose:** Send all system emails (receipts, notifications, etc.)

---

## NEXT STEPS (TO COMPLETE SETUP)

### Step 1: Generate Gmail App Password

**Account Required:** `anticafe294@gmail.com`

#### A. Enable 2-Factor Authentication (if not already)
1. Visit: https://myaccount.google.com/security
2. Find "2-Step Verification"
3. Click "Get Started" and complete setup

#### B. Create App Password
1. Visit: https://myaccount.google.com/apppasswords
2. Re-enter Gmail password if prompted
3. Select app: **"Mail"**
4. Select device: **"Other (Custom name)"**
5. Name it: **"Railway Kavork"**
6. Click **"Generate"**
7. **Copy the 16-character password** (format: `xxxx xxxx xxxx xxxx`)

**⚠️ IMPORTANT:** This is NOT your regular Gmail password. It's a special app-specific password.

---

### Step 2: Configure Railway Environment Variables

Set these in Railway Dashboard → Project Settings → Variables:

```
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=anticafe294@gmail.com
SMTP_PASSWORD=[paste-16-char-app-password-here]
SMTP_PORT=587
```

**How to add in Railway:**
1. Open Railway dashboard: https://railway.app
2. Navigate to your Kavork project
3. Click on the service (web app)
4. Go to "Variables" tab
5. Click "+ New Variable"
6. Add each variable above
7. Save/deploy

---

### Step 3: Deploy Changes to Railway

#### Option A: Automatic Deploy (if connected to Git)
```powershell
git add .
git commit -m "Configure Gmail SMTP for receipt emails"
git push origin main
```

Railway will auto-deploy if GitHub integration is enabled.

#### Option B: Manual Deploy via Railway CLI
```powershell
railway up
```

---

### Step 4: Verify Queue Worker is Running

The receipt email system uses background jobs via Yii2 Queue.

**Check Railway processes:**
1. Railway Dashboard → Your Project → Service
2. Look for a process running: `php yii queue/listen`
3. If NOT running, add to Procfile or start command

**Required worker command:**
```bash
php yii queue/listen --verbose=1
```

**If you don't have a Procfile, create one:**
```
web: php -S 0.0.0.0:$PORT -t site_demo/frontend/web
worker: php site_demo/yii queue/listen --verbose=1
```

Then in Railway, add a new service for the worker process.

---

## TESTING THE EMAIL SYSTEM

### Test 1: Console Test Email

SSH into Railway container or run locally:

```bash
cd site_demo
php yii test/email anticafe294@gmail.com
```

Create this test command if it doesn't exist:
```php
// In console/controllers/TestController.php
public function actionEmail($to) {
    $sent = \Yii::$app->mailer->compose()
        ->setFrom(\Yii::$app->params['robotEmail'])
        ->setTo($to)
        ->setSubject('Test Email from Kavork')
        ->setHtmlBody('<h1>Success!</h1><p>SMTP is working.</p>')
        ->send();

    echo $sent ? "Email sent successfully\n" : "Failed to send\n";
}
```

### Test 2: Process a Real Checkout

1. Make a test purchase on the live site
2. Complete checkout with a valid email address
3. Check the customer's inbox for receipt
4. Check Railway logs for errors:
   ```bash
   railway logs
   ```

### Test 3: Check Queue Status

```bash
php yii queue/info
```

Should show:
- Jobs waiting
- Jobs in progress
- Failed jobs (if any)

---

## TROUBLESHOOTING

### Issue: Emails not sending

**Check 1: Environment variables set in Railway?**
```bash
railway variables
```

**Check 2: Queue worker running?**
```bash
railway ps
```
Should show a worker process.

**Check 3: Gmail App Password correct?**
- 16 characters, no spaces in Railway variable
- Generated from correct Gmail account

**Check 4: Check Railway logs**
```bash
railway logs --follow
```
Look for SwiftMailer or SMTP errors.

### Issue: "Authentication failed" error

**Cause:** Wrong App Password or 2FA not enabled

**Fix:**
1. Verify 2FA is enabled on Gmail
2. Regenerate App Password
3. Update Railway `SMTP_PASSWORD` variable
4. Redeploy

### Issue: "Connection timeout"

**Cause:** Port 587 blocked or wrong host

**Fix:**
- Verify Railway allows outbound SMTP connections
- Try alternative port: 465 with SSL encryption
  ```php
  'port' => '465',
  'encryption' => 'ssl',
  ```

### Issue: Queue jobs not processing

**Cause:** Queue worker not running

**Fix:**
- Add worker process to Railway
- Or run via cron/scheduler:
  ```bash
  */5 * * * * cd /path/to/site_demo && php yii queue/run
  ```

---

## SECURITY CONSIDERATIONS

### Never Commit Credentials

✅ **GOOD:** Environment variables in Railway
❌ **BAD:** Hardcoded passwords in config files

The current config uses `getenv()` which is secure.

### App Password vs Regular Password

- **App Passwords** can be revoked without changing Gmail password
- If compromised, revoke and generate new one
- Each app/service should have its own App Password

### Rate Limiting

Gmail enforces:
- 500 emails/day (free Gmail)
- 2,000 emails/day (Google Workspace)

If you exceed this, consider:
- SendGrid (5,000/month free)
- Mailgun (5,000/month free)
- Amazon SES ($0.10 per 1,000)

---

## ALTERNATIVE SMTP PROVIDERS

If Gmail doesn't work or you need higher volume:

### SendGrid
- **Free tier:** 100 emails/day
- **Setup:** https://sendgrid.com
- **Config:**
  ```
  SMTP_HOST=smtp.sendgrid.net
  SMTP_USERNAME=apikey
  SMTP_PASSWORD=[sendgrid-api-key]
  SMTP_PORT=587
  ```

### Mailgun
- **Free tier:** 5,000 emails/month
- **Setup:** https://mailgun.com
- **Config:**
  ```
  SMTP_HOST=smtp.mailgun.org
  SMTP_USERNAME=[mailgun-smtp-username]
  SMTP_PASSWORD=[mailgun-smtp-password]
  SMTP_PORT=587
  ```

### Amazon SES
- **Cost:** $0.10 per 1,000 emails
- **Setup:** AWS Console → SES
- **Config:**
  ```
  SMTP_HOST=email-smtp.[region].amazonaws.com
  SMTP_USERNAME=[ses-smtp-username]
  SMTP_PASSWORD=[ses-smtp-password]
  SMTP_PORT=587
  ```

---

## MONITORING & MAINTENANCE

### Check Email Delivery Logs

**In Railway:**
```bash
railway logs --filter "mailer"
```

**In application:**
- Check `console/jobs/CheckMailSendJob.php` error logs
- Look for `Yii::error()` messages

### Monitor Queue Health

**Command:**
```bash
php yii queue/info
```

**Expected output:**
```
Jobs: 0 waiting, 0 delayed, 0 reserved, 0 done, 0 failed
```

**If jobs stuck:**
```bash
php yii queue/clear     # Clear all
php yii queue/remove 123 # Remove specific job
```

### Regular Checks

- Weekly: Verify receipt emails arriving
- Monthly: Check Gmail sending limits not exceeded
- Quarterly: Rotate App Password for security

---

## ROLLBACK PROCEDURE

If email system breaks after deployment:

### 1. Revert Code Changes
```bash
git revert HEAD
git push origin main
```

### 2. Restore Previous Config
Copy backup of:
- `environments/prod/common/config/main-local.php`
- `common/config/params.php`

### 3. Clear Environment Variables
In Railway dashboard, remove SMTP variables if causing issues.

---

## CONTACTS & REFERENCES

### Gmail Account
- **Email:** anticafe294@gmail.com
- **App Passwords:** https://myaccount.google.com/apppasswords

### Railway
- **Dashboard:** https://railway.app
- **Docs:** https://docs.railway.app

### Yii2 SwiftMailer
- **Docs:** https://www.yiiframework.com/extension/yiisoft/yii2-swiftmailer/doc/guide/2.1/en
- **Configuration:** https://www.yiiframework.com/doc/guide/2.0/en/tutorial-mailing

---

## STATUS CHECKLIST

- [x] Identified email system architecture
- [x] Updated production SMTP config
- [x] Changed sender email to anticafe294@gmail.com
- [ ] **PENDING:** Generate Gmail App Password
- [ ] **PENDING:** Set Railway environment variables
- [ ] **PENDING:** Deploy to Railway
- [ ] **PENDING:** Verify queue worker running
- [ ] **PENDING:** Test email sending
- [ ] **PENDING:** Confirm receipt delivery to customer

---

**Last Updated:** 2026-01-26
**Next Action:** Generate Gmail App Password from anticafe294@gmail.com
**Assigned To:** Team (access to Gmail account required)
