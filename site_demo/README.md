# Kavork Cafe Management System

A cafe/anticafe management system with a FastAPI backend and legacy Yii2 PHP frontend.

## Tech Stack

### FastAPI Backend (New)
- Python 3.11+
- FastAPI, SQLAlchemy 2.x, Alembic
- PostgreSQL via `DATABASE_URL`
- JWT authentication via `python-jose[cryptography]`

### Yii2 PHP Frontend (Legacy)
- PHP 7+
- Yii2 Framework
- MySQL

## Project Layout

```
app/
  main.py               FastAPI app + router wiring
  config.py             Pydantic settings (env driven)
  db.py                 SQLAlchemy engine/session helpers
  models/               ORM models (franchisee, cafe, user, visitor)
  schemas/              Pydantic response/request models
  routes/               FastAPI routers (/auth)
alembic/                Migration environment + versions
frontend/               Yii2 PHP frontend (legacy)
backend/                Yii2 PHP backend (legacy)
common/                 Shared Yii2 code
```

## Running FastAPI Backend

1. Create a virtual environment:
   ```bash
   python -m venv .venv
   # Windows: .venv\Scripts\activate
   # Linux/Mac: source .venv/bin/activate
   ```
2. Install dependencies: `pip install -r requirements.txt`
3. Set required env vars: `DATABASE_URL`, `JWT_SECRET`, `ENCRYPTION_KEY`
4. Initialize DB: `alembic upgrade head`
5. Run API: `uvicorn app.main:app --reload`

## Running Legacy PHP Frontend

1. Copy config files from `common/config`, `console/config`, `frontend/config` (remove `example` suffix)
2. Run `composer install`
3. Run `php yii migrate`
4. Start server: `php -S 0.0.0.0:8080 -t frontend/web`

### Default Login (Legacy)
- Username: `Artur`
- Password: `1234567890`

## Requirements

- Python 3.11+ (for FastAPI backend)
- PHP 7+ (for legacy frontend)
- PostgreSQL (FastAPI) / MySQL (legacy PHP)
