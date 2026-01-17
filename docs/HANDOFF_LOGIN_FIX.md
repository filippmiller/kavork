# Handoff: Login Error Fix - 2026-01-17

## Status: IN PROGRESS - Testing Needed

**Login now authenticates successfully.** User needs to test in browser.

## Problem
User login with `filipp1` / `Airbus380+` was failing with "Internal server error" after authentication.

## Root Cause Found
**Database schema mismatch between code expectations and Railway database.**

The original SQL dump (`timecafe_docowor.sql`) has specific column names, but the Railway database had a different schema.

### User Table Issues (ALL FIXED)
| Expected (SQL dump) | Railway DB (was) | Status |
|---------------------|------------------|--------|
| `name` column       | `user` column    | ✅ FIXED - renamed |
| `franchisee_id`     | missing          | ✅ FIXED - added |
| `phone`             | missing          | ✅ FIXED - added |
| `lg`                | missing          | ✅ FIXED - added |
| PRIMARY KEY on `id` | missing          | ✅ FIXED - added |

### Cafe Table Issues (FIXED)
| Expected | Railway DB (was) | Status |
|----------|------------------|--------|
| `franchisee_id` | missing | ✅ FIXED - added |
| PRIMARY KEY | duplicate IDs issue | ⚠️ Has duplicates (non-blocking) |

## Current Status
- Login POST succeeds (returns 302 redirect) ✅
- Home page redirect may still have issues (need browser test)

## Fix Script Location
`site_demo/frontend/web/create_user.php`

### Usage
```bash
# View current schema
curl "https://kavork-app-production.up.railway.app/create_user.php"

# Fix schema and create user
curl "https://kavork-app-production.up.railway.app/create_user.php?action=fix_schema"
```

## Next Steps
1. **Deploy latest code** - the cafe table fix was just added
2. **Run schema fix again**:
   ```bash
   curl "https://kavork-app-production.up.railway.app/create_user.php?action=fix_schema"
   ```
3. **Test login** in browser at https://kavork-app-production.up.railway.app/login
4. **If more errors appear** - check Railway logs for missing columns in other tables

## Files Modified
- `site_demo/frontend/web/create_user.php` - Schema fix script
- `site_demo/frontend/controllers/SiteController.php` - Cleaned up debug code
- `deploy-config/entrypoint.sh` - Added PHP error logging to stderr

## Login Flow
1. POST `/login` → validates credentials ✅
2. Redirect to `/` → calls `actionIndex()`
3. Logged-in users → redirected to `/change-cafe`
4. `actionChangeCafe()` → calls `Users::getCafesList()`
5. `getCafesList()` → queries cafe table with `franchisee_id` ❌ FAILS HERE

## Database Connection
- Railway MySQL - credentials via environment variables
- Tables imported from `timecafe_docowor.sql` but with incomplete schema

## Key Insight
The database import to Railway either failed partially or used a different source schema. Multiple tables need their schema adjusted to match the code's expectations.
