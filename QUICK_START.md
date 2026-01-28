# Quick Start Guide - Current Session State

**Last Updated:** 2026-01-28 19:50 UTC+3
**Status:** ⏳ Awaiting deployment verification

---

## 🚨 IMMEDIATE ACTION REQUIRED

Railway deployment in progress. Once complete (~2 min), verify production fix:

1. Go to: https://kavork-app-production.up.railway.app/
2. Login: filipp1 / Mt12017555+
3. Select cafe: "294 RUE SAINTE-CATHERINE O,H2X 2A1"
4. Click on any visitor name
5. ✅ Modal should open (was getting 500 error)

---

## 📋 What Was Fixed

**Original Issue:** 500 error when clicking visitor sessions to view/close them

**Root Cause:** Twig templates had `{{ use() }}` calls but function not registered

**Current Fix (Deployed):**
- Removed all `use()` calls from affected templates
- Implemented recursive Twig cache clearing in index.php
- Version marker bumped to v6 to trigger cache clear

**Commits:**
```
c3f8421 ← Current: Recursive cache clearing
22af8e2 ← Reverted broken dummy function
e21f587 ← BROKEN (site-wide 500s)
5ced28c ← Cache clearing attempt #1
```

---

## 🔧 If It Still Fails

### Check These Files
May still have `use()` calls:
- `site_demo/frontend/views/layouts/blank.twig`
- `site_demo/frontend/views/layouts/main.twig`

### Nuclear Option
Access Railway console:
```bash
rm -rf site_demo/frontend/runtime/Twig/cache/*
```

---

## 💻 Local Dev Environment

**Status:** MySQL running, PHP server running, login works but post-login 500 error

**Quick Start:**
```bash
# MySQL
docker compose -f docker-compose.dev.yml up -d

# PHP Server
cd site_demo/frontend/web
php -S localhost:8080 index.php

# Access
http://localhost:8080
Login: Filipp / Mt12017555+
```

---

## 📁 Key Files

**Templates (Fixed):**
- `site_demo/frontend/modules/visits/views/default/view.twig`
- `site_demo/frontend/modules/visits/views/default/_visit_info.twig`

**Cache Logic:**
- `site_demo/frontend/web/index.php` (lines 5-24)

**Config:**
- `site_demo/common/config/main.php` (line 84: `uses` config)

---

## ⚠️ Important Context

1. **Visit count "1/1/30" is NOT a date** - it's today/week/total visits
2. **AJAX-only controller** - direct URL access blocked in production
3. **Response format: JSON** - 101-byte responses = early exception
4. **Railway persists cache** - build-time clearing doesn't work
5. **Yii2 `uses` config** - can't add custom `use()` function (conflicts)

---

## 📊 Test Credentials

**Production:**
- URL: https://kavork-app-production.up.railway.app/
- User: filipp1 / Mt12017555+

**Local:**
- URL: http://localhost:8080
- User: Filipp / Mt12017555+

---

## 📄 Full Details

See: `SESSION_2026-01-28_VISITOR_VIEW_500_ERROR_HANDOFF.md`

---

## 🎯 Success Criteria

- ✅ Production site accessible
- ✅ Can login
- ✅ Can select cafe
- ✅ Can click visitor names
- ✅ Modal opens without 500 error
- ✅ Can view visitor details
- ✅ Can close visitor sessions
