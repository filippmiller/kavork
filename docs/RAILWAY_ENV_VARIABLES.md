# Railway Environment Variables - Kavork

**Updated:** 2026-01-26
**Status:** Ready to Deploy

---

## Required SMTP Variables

Add these to your Railway project environment variables:

```bash
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=anticafe294@gmail.com
SMTP_PASSWORD=qhrqcflmjhqnqlep
SMTP_PORT=587
```

---

## How to Add in Railway Dashboard

1. Go to: https://railway.app
2. Select your Kavork project
3. Click on your service (PHP/Yii app)
4. Go to **Variables** tab
5. Click **+ New Variable**
6. Add each variable above (4 total)
7. Click **Deploy** or wait for auto-deploy

---

## Verification

After deployment with these variables:

1. **Check Logs:**
   ```
   Railway Dashboard → Service → Deployments → View Logs
   ```

2. **Test Email:**
   - Process a checkout in production
   - Check if receipt email arrives at customer email
   - Check anticafe294@gmail.com for any bounces

3. **Monitor Queue:**
   - Ensure queue worker is running: `php yii queue/listen`
   - Check for failed jobs in database table: `queue`

---

## Security Notes

- Never commit `.env` files with real credentials
- Railway variables are encrypted at rest
- App Password can be revoked/regenerated anytime in Gmail
- Consider using separate Gmail account for production later

---

## Alternative: Local .env for Development

If testing locally (requires OpenSSL in PHP):

Create `site_demo/.env`:
```bash
SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=anticafe294@gmail.com
SMTP_PASSWORD=qhrqcflmjhqnqlep
SMTP_PORT=587
```

Load in PHP:
```php
// In index.php or bootstrap
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            putenv(trim($line));
        }
    }
}
```

**DO NOT** commit `.env` to git (already in .gitignore).

---

## Queue Worker Configuration

Railway must run the queue worker as a background process.

**Procfile** (if using):
```
web: php -S 0.0.0.0:$PORT -t site_demo/frontend/web
worker: php site_demo/yii queue/listen
```

Or configure in Railway settings:
- **Start Command:** `php yii queue/listen`
- Run as separate service or background process

Without the queue worker, emails will queue but never send.
