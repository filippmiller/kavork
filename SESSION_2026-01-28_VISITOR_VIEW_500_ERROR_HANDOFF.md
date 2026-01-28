# Session Handoff: Visitor View 500 Error Fix
**Date:** 2026-01-28
**Status:** Deployment in progress, awaiting verification
**Session Duration:** Multiple hours across debugging, local dev setup, and production fixes

---

## CURRENT SITUATION

### Deployment Status
- **Latest Commit:** c3f8421 - "HOTFIX: Recursive Twig cache clearing with version bump to v6"
- **Railway Status:** Deployment in progress (pushed ~2 minutes ago)
- **Production URL:** https://kavork-app-production.up.railway.app/
- **Last Known State:** Site was showing 500 errors after previous fix attempt, now being restored

### Git History (Most Recent)
```
c3f8421 HOTFIX: Recursive Twig cache clearing with version bump to v6
22af8e2 Revert "HOTFIX: Add dummy use() Twig function for cached templates"
e21f587 HOTFIX: Add dummy use() Twig function for cached templates (REVERTED - broke site)
5ced28c CRITICAL: Clear Twig cache and opcache on first request after deployment
203ab7f CRITICAL: Force Twig cache clear with nixpacks configuration
34aaee4 TEST: Add deployment marker to verify cache clearing
```

---

## ORIGINAL ISSUE

### Problem Description
- **Error:** 500 Internal Server Error when clicking on visitor sessions to view/close them
- **Specific URL:** `/visits/view?id=208583` (and other visitor IDs)
- **User Flow:** Admin logs in → Selects cafe → Clicks on visitor name → Modal should open but 500 error occurs
- **Error Response:** Only 101 bytes (JSON error format), meaning exception occurs BEFORE template rendering

### Root Cause Identified
1. Template files had calls to undefined Twig function `use()`:
   - `site_demo/frontend/modules/visits/views/default/view.twig` (line 1 - FIXED)
   - `site_demo/frontend/modules/visits/views/default/_visit_info.twig` (line 1 - FIXED)
   - `site_demo/frontend/views/layouts/blank.twig` (lines 1-2 - NOT FIXED, but may not be used)
   - `site_demo/frontend/views/layouts/main.twig` (lines 1-2 - NOT FIXED, but may not be used)

2. **Persistent Cache Issue:** Railway persists the `runtime/` directory as a volume, so:
   - Source template fixes don't clear compiled cache
   - Twig cache contains references to undefined `use()` function
   - Cache clearing during build phase doesn't help (wrong timing)
   - Need runtime cache clearing on first request after deployment

---

## FIXES ATTEMPTED

### ✅ Successful Fixes (Applied)
1. **Removed undefined `use()` calls from templates** (commits 02d8306, 5228859, a7510de, 203ab7f, 5ced28c)
   - Removed from view.twig
   - Removed from _visit_info.twig
   - Added cache-bust comments with version markers

2. **Console spam reduction** (commit in early session)
   - Changed `setInterval(updateMain, 2000)` to `setInterval(updateMain, 10000)` in my.js
   - Reduced API polling from every 2 seconds to every 10 seconds

3. **Current Fix (PENDING VERIFICATION):** Recursive Twig cache clearing (commit c3f8421)
   - Added runtime cache clearing logic in `site_demo/frontend/web/index.php`
   - Uses RecursiveIteratorIterator to delete ALL cache files and subdirectories
   - Version marker bumped to v6 to trigger clearing on next request
   - Should eliminate all cached templates with undefined function references

### ❌ Failed Attempts
1. **nixpacks.toml build-time cache clearing** (commit 203ab7f)
   - Added cache clearing during build phase
   - **Why it failed:** Runtime directory is mounted as volume AFTER build, so build-time clearing doesn't affect runtime cache

2. **Dummy `use()` Twig function** (commit e21f587 - REVERTED in 22af8e2)
   - Added `'use' => function($class = null) { return ''; }` to twigFunctionList.php
   - **Why it failed:** Conflicts with Yii2's native `uses` configuration in main.php line 84
   - **Impact:** Caused site-wide 500 errors, completely broke production
   - **Resolution:** Immediately reverted

---

## TECHNICAL CONTEXT

### Key Files and Their Roles

**Templates (Twig):**
- `site_demo/frontend/modules/visits/views/default/view.twig` - Main visitor view modal
- `site_demo/frontend/modules/visits/views/default/_visit_info.twig` - Visitor details (included in view.twig)
- `site_demo/frontend/views/layouts/blank.twig` - Layout file (may still have `use()` calls)
- `site_demo/frontend/views/layouts/main.twig` - Layout file (may still have `use()` calls)

**Controllers:**
- `site_demo/frontend/modules/visits/controllers/DefaultController.php`
  - `beforeAction()` enforces AJAX-only access (except print_check)
  - Response format: JSON only
  - This is why error response is 101 bytes JSON

**Configuration:**
- `site_demo/common/config/main.php` (lines 62-91) - Twig renderer config
  - Line 84: `'uses' => ['yii\bootstrap']` - Native Yii2 use directive (conflicts with custom functions named 'use')
- `site_demo/common/components/twigFunctionList.php` - Custom Twig functions registry
- `site_demo/frontend/web/index.php` - Entry point with cache clearing logic

**Cache System:**
- Cache Path: `site_demo/frontend/runtime/Twig/cache/`
- Railway persists this as a volume across deployments
- Marker file: `.cache_cleared_v6` (current version)
- Cache contains compiled PHP from Twig templates

### Important Insights
1. **Visit Count Format:** The "1/1/30" display is NOT a date - it's visit counts (today/week/total)
   - User strongly corrected this misunderstanding earlier
   - File: site_demo/frontend/modules/visits/views/default/_visit_info.twig line 13

2. **AJAX-Only Controller:** The visits controller requires AJAX requests in production
   - Direct URL access will be blocked unless YII_DEBUG is true
   - Testing must use modal clicks, not direct navigation

3. **Response Format:** Controller sets `Response::FORMAT_JSON`
   - All responses are JSON, even error messages
   - 101-byte responses indicate early exception before template rendering

---

## LOCAL DEVELOPMENT ENVIRONMENT

### Current State
- **MySQL:** Running in Docker container `kavork_mysql_dev`
- **PHP Server:** Running on localhost:8080 (background process)
- **Database:** Imported from `timecafe_docowor.2026-01-28.sql.gz` (14MB)
- **Table Name:** `user` (not `users` - important!)
- **Login Issue:** Post-login 500 error (separate issue from visitor view bug)

### Setup Commands
```bash
# Start MySQL
docker compose -f docker-compose.dev.yml up -d

# Import database (if needed)
gunzip -c timecafe_docowor.2026-01-28.sql.gz | docker exec -i kavork_mysql_dev mysql -u timecafe_kvdemo -p11PhtKa4i3BY railway

# Start PHP server
cd site_demo/frontend/web
php -S localhost:8080 index.php

# Access site
http://localhost:8080
```

### Test Credentials
- **Production:** filipp1 / Mt12017555+
- **Local:** Filipp / Mt12017555+ (password hash was reset in DB)
- **Note:** Username is case-sensitive in database

### Database Schema Notes
- Table: `user` (singular, not plural)
- Columns: `id`, `name`, `pass`, `email`, `phone`, `color`, `lg`, `franchisee_id`, `state`, `last_sess`
- No `username` column - login uses `name` field

---

## NEXT STEPS (PRIORITY ORDER)

### 1. Verify Production Fix (IMMEDIATE)
Once Railway deployment completes (~1-2 minutes from last push):

```
1. Navigate to https://kavork-app-production.up.railway.app/
2. Login as filipp1 / Mt12017555+
3. Select "294 RUE SAINTE-CATHERINE O,H2X 2A1" cafe
4. Click ENTER
5. Look for a visitor named "Filipp" or any active visitor
6. Click on the visitor to open the modal
7. Verify: Modal opens successfully without 500 error
8. Check Railway logs for any errors
```

**Expected Outcome:**
- Cache should be cleared on first request (marker file .cache_cleared_v6 created)
- All compiled Twig templates regenerated from source (which no longer have `use()` calls)
- Visitor view modal opens successfully

### 2. If Production Still Fails
**Option A: Manual Cache Clear (Nuclear Option)**
- Access Railway console
- Delete entire `site_demo/frontend/runtime/Twig/cache/` directory
- Restart service

**Option B: Check Layouts**
- Investigate if blank.twig or main.twig are being used for the visits/view action
- If so, remove `{{ use('yii/bootstrap') }}` from those files
- The layouts shouldn't be needed for AJAX modal responses though

**Option C: Check Other Templates**
Search for remaining `use()` calls:
```bash
grep -r "{{ use(" site_demo/frontend/modules/visits/views/
grep -r "{{ use(" site_demo/frontend/views/layouts/
```

### 3. Local Development Testing
If production works, replicate the test locally:
- Fix post-login 500 error (likely missing franchisee/cafe relationship data)
- Navigate to visitor management
- Test modal opening
- This confirms the fix works in both environments

### 4. Documentation
- Update SESSION_2026-01-28_VISITOR_VIEW_FIX.md with final outcome
- Document the cache persistence issue for future reference
- Add note about Yii2's `uses` configuration and why custom `use()` functions don't work

---

## KNOWN ISSUES (NOT FIXED)

### 1. Post-Login 500 Error (Local Dev Only)
- Occurs after successful authentication
- Error in logs: `yii\base\InvalidConfigException` in ActiveField.php:178
- Likely cause: Missing franchisee_id or cafe relationship for test user
- Impact: Blocks local dev testing after login
- Not affecting production

### 2. Missing Static Assets (Local Dev Only)
- CSS/JS files return 404 on PHP dev server
- PHP built-in server doesn't handle static assets in subdirectories well
- Impact: Page loads but looks unstyled
- Not affecting production (served by proper web server)

### 3. Layout Files May Still Have use() Calls
- `site_demo/frontend/views/layouts/blank.twig` lines 1-2
- `site_demo/frontend/views/layouts/main.twig` lines 1-2
- These contain `{{ use('yii/bootstrap') }}` and `{{ use('yii\bootstrap\ActiveForm') }}`
- **Unknown if these layouts are used for AJAX modal responses**
- May need removal if cache clearing doesn't fully resolve the issue

---

## TESTING CHECKLIST

Use this checklist to verify the fix:

```
Production Testing:
[ ] Site loads without errors
[ ] Can login as filipp1
[ ] Can select cafe
[ ] Can navigate to visitor management area
[ ] Can click on a visitor name
[ ] Modal opens successfully (no 500 error)
[ ] Modal displays visitor information correctly
[ ] Can close visitor session without errors
[ ] Check Railway logs for any warnings/errors
[ ] Verify cache marker file was created (.cache_cleared_v6)

Local Development (if needed):
[ ] MySQL container running
[ ] PHP server running on localhost:8080
[ ] Can access login page
[ ] Database has correct user data
[ ] Post-login issue documented or resolved
```

---

## DEPLOYMENT LOGS TO CHECK

After deployment completes, check Railway logs for:
1. Cache clearing confirmation (should happen on first request)
2. Any Twig-related errors
3. Visitor view endpoint access attempts
4. Response sizes (should be >101 bytes for successful modal render)

---

## USEFUL COMMANDS

```bash
# Check deployment status
git log --oneline -n 5

# View recent Railway logs (if you have railway CLI)
railway logs --tail

# Find remaining use() calls in templates
grep -rn "{{ use(" site_demo/frontend/

# Check cache clearing marker
ls -la site_demo/frontend/runtime/Twig/cache/.cache_cleared_v6

# Restart PHP dev server
killall php
cd site_demo/frontend/web && php -S localhost:8080 index.php &

# Check MySQL container
docker ps | grep kavork
docker exec kavork_mysql_dev mysql -u timecafe_kvdemo -p11PhtKa4i3BY railway -e "SHOW TABLES;"
```

---

## CONTACT & CREDENTIALS

### Production Access
- URL: https://kavork-app-production.up.railway.app/
- User: filipp1
- Pass: Mt12017555+
- Cafe: 294 RUE SAINTE-CATHERINE O,H2X 2A1

### Local Dev Access
- URL: http://localhost:8080
- User: Filipp
- Pass: Mt12017555+
- MySQL: timecafe_kvdemo / 11PhtKa4i3BY

### GitHub
- Repo: https://github.com/filippmiller/kavork.git
- Branch: main
- Auto-deploys to Railway on push

---

## FINAL NOTES

1. **The user is eager to see this fixed** - it's blocking their visitor management workflow
2. **Be cautious with Twig function registration** - conflicts with Yii2's native `uses` configuration
3. **Railway cache persistence is tricky** - runtime volumes persist across deployments
4. **Test before committing** - previous fix broke production site completely
5. **Console spam was also fixed** - reduced polling from 2s to 10s
6. **Visit count display is NOT a date** - very important, user corrected this emphatically

**Most Likely Outcome:** The recursive cache clearing with v6 marker should resolve the issue. If not, check if layout files are being loaded for AJAX responses and remove their `use()` calls.

Good luck! The next deployment should restore the site and clear the problematic cache.
