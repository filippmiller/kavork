# Kavork Project - Deployment Handoff Document

**Date:** January 16, 2026
**Author:** Claude Opus 4.5
**Status:** Deployed and Running

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Repository Structure](#repository-structure)
3. [Deployment Summary](#deployment-summary)
4. [Configuration Files](#configuration-files)
5. [Environment Variables](#environment-variables)
6. [Troubleshooting History](#troubleshooting-history)
7. [Current State](#current-state)
8. [Next Steps](#next-steps)
9. [Useful Commands](#useful-commands)

---

## Project Overview

**Kavork** is a hybrid project containing two main components:

### 1. NEW Backend - FastAPI (Python)
- **Location:** `site_demo/app/`
- **Purpose:** eBay auction sniper SaaS backend ("BIDNAPPER")
- **Stack:** FastAPI, SQLAlchemy, PostgreSQL, JWT auth, OAuth 2.0
- **Features:**
  - User authentication with Argon2id password hashing
  - JWT-based sessions (short-lived access tokens)
  - eBay OAuth integration (tokens encrypted with AES-GCM)
  - Alembic database migrations

### 2. Legacy Frontend - Yii2 (PHP)
- **Location:** `site_demo/frontend/` and `site_demo/backend/`
- **Purpose:** Original "Timecafe" restaurant booking system
- **Stack:** Yii2 Framework, MySQL, Bootstrap, Twig templates
- **Status:** Legacy code, not currently deployed on Railway

---

## Repository Structure

```
C:\dev\kavork\
├── .gitignore                 # Root gitignore (created today)
├── Dockerfile                 # Docker build for Railway
├── railway.json               # Railway deployment config
├── nixpacks.toml              # Nixpacks config (not used, Dockerfile preferred)
├── docs/
│   └── DEPLOYMENT_HANDOFF.md  # This document
└── site_demo/
    ├── app/                   # FastAPI application
    │   ├── main.py            # App entry point
    │   ├── config.py          # Pydantic settings
    │   ├── db.py              # SQLAlchemy setup
    │   ├── routes/
    │   │   └── auth.py        # Auth endpoints
    │   └── schemas/
    │       └── auth.py        # Pydantic models
    ├── alembic/               # Database migrations
    ├── alembic.ini            # Alembic config
    ├── requirements.txt       # Python dependencies
    ├── frontend/              # Yii2 PHP frontend (legacy)
    ├── backend/               # Yii2 PHP backend (legacy)
    ├── common/                # Shared Yii2 code
    ├── console/               # Yii2 console commands
    └── vendor/                # PHP dependencies (gitignored)
```

---

## Deployment Summary

### GitHub Repository
- **URL:** https://github.com/filippmiller/kavork
- **Branch:** `main`
- **Auto-deploy:** Enabled (pushes to main trigger Railway deployment)

### Railway Project
- **Project URL:** https://railway.com/project/10b715e3-aa20-41e9-8975-126be47556a6
- **Project ID:** `10b715e3-aa20-41e9-8975-126be47556a6`
- **Environment:** `production`

### Services Deployed

| Service | Type | Status | URL/Connection |
|---------|------|--------|----------------|
| kavork-app | FastAPI | Running | https://kavork-app-production.up.railway.app |
| Postgres | Database | Running | `postgres.railway.internal:5432` |
| MySQL | Database | Running | Available for legacy PHP app |

### Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/healthz` | GET | Health check (returns `{"status":"ok"}`) |
| `/docs` | GET | Swagger UI documentation |
| `/redoc` | GET | ReDoc documentation |
| `/auth/*` | Various | Authentication routes |

---

## Configuration Files

### Dockerfile
```dockerfile
FROM python:3.11-slim

WORKDIR /app

RUN apt-get update && apt-get install -y \
    libpq-dev \
    gcc \
    && rm -rf /var/lib/apt/lists/*

COPY site_demo/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY site_demo/ .

EXPOSE 8000

CMD uvicorn app.main:app --host 0.0.0.0 --port ${PORT:-8000}
```

### railway.json
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "DOCKERFILE",
    "dockerfilePath": "Dockerfile"
  },
  "deploy": {
    "healthcheckPath": "/healthz",
    "restartPolicyType": "ON_FAILURE"
  }
}
```

### .gitignore (root)
Key exclusions:
- `site_demo/vendor/` - PHP dependencies (291MB)
- `*.zip` - Archive files
- `*.sql` - Database dumps
- `.env` - Environment files
- `node_modules/` - Node dependencies
- `.venv/`, `__pycache__/` - Python artifacts

---

## Environment Variables

### kavork-app Service

| Variable | Value | Description |
|----------|-------|-------------|
| `DATABASE_URL` | `postgresql://postgres:***@postgres.railway.internal:5432/railway` | PostgreSQL connection |
| `JWT_SECRET` | `VJoO8vsVp67eJhSzQRe6EyMjKyur_gJHTe8AecKPO4E` | JWT signing key |
| `ENCRYPTION_KEY` | `dcfe4c60e35fdfda546d1f33a7741e789cd220f24c22650e7356a41f5211accb` | AES-256 encryption key |

### PostgreSQL Service

| Variable | Value |
|----------|-------|
| `PGHOST` | `postgres.railway.internal` |
| `PGPORT` | `5432` |
| `PGUSER` | `postgres` |
| `PGPASSWORD` | `kLMAWyWcjUlFdLKOFqKxxOPToFqlGxwC` |
| `PGDATABASE` | `railway` |
| `DATABASE_PUBLIC_URL` | `postgresql://postgres:***@centerbeam.proxy.rlwy.net:58280/railway` |

---

## Troubleshooting History

### Issues Encountered and Resolved

#### 1. Embedded Git Repository
- **Problem:** `site_demo/` had its own `.git` folder
- **Solution:** Removed `.git` from `site_demo/` to allow proper staging

#### 2. UTF-8 Encoding Errors
- **Problem:** Nixpacks failed reading ISO-8859 encoded JS files
- **Files Fixed:**
  - `site_demo/frontend/views/site/assets/js/contactFormThanks.js`
  - `site_demo/frontend/views/site/assets/js/landing.js`
- **Solution:** Converted to UTF-8 using `iconv`

#### 3. Nixpacks pip Not Found
- **Problem:** Nixpacks Python setup didn't include pip in PATH
- **Solution:** Switched from Nixpacks to Dockerfile for better control

#### 4. Missing email-validator
- **Problem:** Pydantic EmailStr requires email-validator
- **Solution:** Changed `pydantic>=2.6.0` to `pydantic[email]>=2.6.0`

#### 5. Missing Environment Variables
- **Problem:** App crashed due to missing `DATABASE_URL`, `JWT_SECRET`, `ENCRYPTION_KEY`
- **Solution:** Set variables via `railway variables --set`

#### 6. Healthcheck Path Mismatch
- **Problem:** Railway healthcheck was hitting `/` (404) instead of `/healthz`
- **Solution:** Updated `railway.json` healthcheckPath to `/healthz`

#### 7. X-Railway-Fallback Responses
- **Problem:** Requests returned Railway fallback page instead of app
- **Solution:** Fixed healthcheck, redeployed - domain became active after healthcheck passed

---

## Current State

### What's Working
- FastAPI backend deployed and running
- Health endpoint responding correctly
- API documentation accessible at `/docs`
- PostgreSQL database provisioned and connected
- MySQL database provisioned (unused)
- Auto-deploy from GitHub enabled
- All environment variables configured

### What's NOT Deployed
- Legacy PHP/Yii2 application (would require separate service with PHP runtime)
- Database migrations not run yet (need to run `alembic upgrade head`)

### Known Limitations
- The `/` root path returns 404 (no root route defined in FastAPI)
- eBay OAuth credentials not configured (need `EBAY_CLIENT_ID`, `EBAY_CLIENT_SECRET`, etc.)

---

## Next Steps

### Immediate Tasks
1. **Run Database Migrations**
   ```bash
   railway run alembic upgrade head
   ```

2. **Configure eBay OAuth** (if using eBay integration)
   ```bash
   railway variables --service kavork-app \
     --set "EBAY_CLIENT_ID=your_client_id" \
     --set "EBAY_CLIENT_SECRET=your_client_secret" \
     --set "EBAY_REDIRECT_URI=https://kavork-app-production.up.railway.app/auth/ebay/callback"
   ```

3. **Add Root Route** (optional, for better UX)
   Add to `site_demo/app/main.py`:
   ```python
   @app.get("/")
   def root():
       return {"message": "BIDNAPPER API", "docs": "/docs"}
   ```

### Future Considerations
- Set up custom domain
- Configure CORS for frontend integration
- Add rate limiting
- Set up logging/monitoring
- Deploy PHP frontend (requires Dockerfile with PHP-FPM + Nginx)

---

## Useful Commands

### Railway CLI

```bash
# Check project status
railway status

# View logs
railway logs --deployment --lines 50
railway logs --build --lines 50

# Set environment variables
railway variables --set "KEY=value"

# View variables
railway variables --service kavork-app --json

# Trigger deployment
git push origin main  # Auto-deploys
# OR
railway up --detach   # Manual deploy

# Run commands in Railway environment
railway run <command>

# Open dashboard
railway open  # (interactive mode only)
```

### Git Commands

```bash
# Standard workflow
git add .
git commit -m "Description"
git push origin main  # Triggers auto-deploy
```

### Local Development

```bash
cd site_demo

# Create virtual environment
python -m venv .venv
source .venv/bin/activate  # or .venv\Scripts\activate on Windows

# Install dependencies
pip install -r requirements.txt

# Set environment variables
export DATABASE_URL="postgresql://..."
export JWT_SECRET="..."
export ENCRYPTION_KEY="..."

# Run locally
uvicorn app.main:app --reload --port 8000

# Run migrations
alembic upgrade head
```

---

## Commit History (Today)

| Commit | Description |
|--------|-------------|
| `bfcaded` | Add kavork site_demo application |
| `102b17e` | Add Railway deployment configuration |
| `a39494b` | Fix UTF-8 encoding for contactFormThanks.js |
| `9aaa904` | Fix UTF-8 encoding for landing.js |
| `c081df4` | Fix pip path in nixpacks config |
| `e5de547` | Switch to Dockerfile for Railway deployment |
| `fb0d7cf` | Add email-validator dependency for Pydantic EmailStr |
| `13cbd45` | Fix healthcheck path to /healthz |

---

## Contact & Resources

- **Railway Dashboard:** https://railway.com/project/10b715e3-aa20-41e9-8975-126be47556a6
- **GitHub Repo:** https://github.com/filippmiller/kavork
- **Live API:** https://kavork-app-production.up.railway.app
- **API Docs:** https://kavork-app-production.up.railway.app/docs

---

*Document generated by Claude Opus 4.5 on January 16, 2026*
