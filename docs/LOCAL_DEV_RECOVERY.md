# Local Dev Recovery Guide (DEPRECATED)

Deprecated: use `docs/LOCAL_DEV_STARTUP_AND_RECOVERY.md`.

This is the minimum set of steps and fixes required to get Kavork running locally with the PHP built‑in server.

## Preconditions
- Docker Desktop running
- PHP 8.2+ available on PATH
- Repo workspace: `C:\dev\kavork`

## Start Local Services
```bash
# MySQL
docker compose -f docker-compose.dev.yml up -d

# PHP dev server (use the router)
cd site_demo/frontend/web
php -S localhost:8080 router.php
```

## Expected Local URL
- `http://localhost:8080/index.php`

## Required Local Config (must be present)
- `site_demo/frontend/config/main-local.php`
  - `assetManager`:
    - `linkAssets: false`
    - `forceCopy: true`
  - `session.savePath: @runtime/sessions`
  - `user.class: frontend\components\WebUser`
- `site_demo/frontend/web/index.php`
  - Local static file fallback (serves CSS/JS/images correctly when PHP server routes via index)
- `site_demo/frontend/web/router.php`
  - Router must return static files directly when they exist

## Known Good Test User
If login is unavailable or flaky, the local environment uses a dev‑only auto‑login for `testuser`.

Local credentials:
- Username: `testuser`
- Password: `Test1234!`

User must be linked to a cafe:
```sql
INSERT INTO user_cafe (user_id, cafe_id) VALUES (167, 1);
```

## Common Failure Modes and Fixes
### 1) Assets return 500 / JS errors (yiiActiveForm missing)
Symptoms:
- Console error: `yiiActiveForm is not a function`
- CSS/JS requests 500

Fix:
- Ensure `frontend/web/index.php` serves static files
- Ensure `frontend/config/main-local.php` has `assetManager.forceCopy = true`
- Restart PHP server

### 2) Login loops / always shows login form
Cause:
- Session not persisted correctly

Fix:
- Ensure `session.savePath = @runtime/sessions`
- Ensure `frontend\components\WebUser` is used
- Restart PHP server

### 3) Multiple PHP servers on port 8080
Symptoms:
- Random behavior, stale routes, redirection loops

Fix:
- Stop all PHP processes listening on port 8080
- Start only one server: `php -S localhost:8080 router.php`

## Verification Checklist
- `http://localhost:8080/index.php` loads the main UI
- Page shows `Administrator: testuser`
- No JS errors in console

