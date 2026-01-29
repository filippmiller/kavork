# Local Dev Startup and Recovery

This document lives in the repo and is the single source for local dev setup, recovery, and verification.

## Start Local Dev (Clean)
```bash
# MySQL
docker compose -f docker-compose.dev.yml up -d

# PHP dev server (router required)
cd site_demo/frontend/web
php -S localhost:8080 router.php
```

Open: `http://localhost:8080/index.php`

## Required Local Config
`site_demo/frontend/config/main-local.php` must include:
- `assetManager.linkAssets = false`
- `assetManager.forceCopy = true`
- `session.savePath = @runtime/sessions`
- `user.class = frontend\components\WebUser`

`site_demo/frontend/web/index.php` must include a static-file fallback for CSS/JS/images.

## Default Local Login
The local setup uses a dev‑only auto‑login for `testuser` on localhost.

Manual credentials (if auto‑login is disabled):
- Username: `testuser`
- Password: `Test1234!`

## DB Requirements
`testuser` must exist and be linked to a cafe.
```sql
INSERT INTO user (name, pass, state, lg, franchisee_id)
VALUES ('testuser', '<bcrypt_hash>', 0, 'en-EN', 1);

INSERT INTO user_cafe (user_id, cafe_id) VALUES (<testuser_id>, 1);
```

## Common Failures and Fixes
### Assets 500 / `yiiActiveForm` errors
- Ensure static file fallback exists in `frontend/web/index.php`
- Ensure `assetManager.forceCopy = true` in `frontend/config/main-local.php`
- Restart PHP server

### Login loops / stuck on login page
- Ensure `session.savePath = @runtime/sessions`
- Ensure `frontend\components\WebUser` is used
- Ensure only one PHP server runs on port 8080

### Multiple PHP servers on 8080
- Stop all PHP processes on port 8080
- Start only one server with `router.php`

## Verification Checklist
- `http://localhost:8080/index.php` opens the app shell
- UI shows `Administrator: testuser`
- No JS errors in console
