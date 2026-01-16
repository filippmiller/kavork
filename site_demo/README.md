# NEW BIDNAPPER — Secure Backend Foundation

Step 1 of the NEW BIDNAPPER project focuses on shipping a hardened FastAPI + PostgreSQL backend that covers:

- user accounts & role-based auth,
- our own email/password authentication with JWT access tokens and hashed refresh tokens,
- secure eBay OAuth onboarding and token storage (AES-GCM at rest),
- token refresh worker skeleton,
- comprehensive security/audit logging.

## Tech Stack

- Python 3.11+
- FastAPI, SQLAlchemy 2.x, Alembic
- PostgreSQL (Supabase) via `DATABASE_URL`
- Argon2id/Bcrypt via `argon2-cffi` / `passlib`
- JWT via `python-jose[cryptography]`
- Encryption via `cryptography` (AES-GCM)
- Background worker entrypoint for token refresh jobs

## Project Layout

```
app/
  main.py               FastAPI app + router wiring
  config.py             Pydantic settings (env driven)
  db.py                 SQLAlchemy engine/session helpers
  models/               ORM models (users, sessions, ebay, etc.)
  schemas/              Pydantic response/request models
  routes/               FastAPI routers (/auth, /oauth/ebay, /account)
  security/             Password hashing, JWT, AES-GCM helpers
  services/             Auth, eBay OAuth, token refresh logic
  workers/              Token refresh worker entrypoints
alembic/                Migration environment + versions
```

## Running Locally

1. Create a virtual environment (`python -m venv .venv && .\\.venv\\Scripts\\activate` on Windows).
2. Install dependencies: `pip install -r requirements.txt`.
3. Set required env vars (`DATABASE_URL`, `JWT_SECRET`, `ENCRYPTION_KEY`, eBay creds).
4. Initialize DB: `alembic upgrade head`.
5. Run API: `uvicorn app.main:app --reload`.
6. (Optional) Run token refresh worker: `python -m app.workers.token_refresh`.

## Security Considerations

- Passwords are hashed using Argon2id (fallback to bcrypt if needed).
- Access JWTs live 15–30 minutes, signed with `JWT_SECRET`.
- Refresh tokens are 256-bit random strings stored as bcrypt hashes in `auth_sessions`.
- eBay OAuth tokens are encrypted via AES-GCM using `ENCRYPTION_KEY`.
- `security_events` table captures logins, OAuth events, token refresh outcomes.

This document will grow as Step 1 progresses; for now it anchors the project goals and architecture.
# Установка проекта

- исходная база данных timecafe.sql
- в папках `common/config`,`console/config` и `frontend/config` все файлы конфигурации example скопировать в рабочие версии (точно такое же имя только без example)
- запускаем `composer install` 
- запускаем `php yii migrate` 
- настраиваем запуск сайта из папки `frontend/web`. Так же можно воспользоваться командой `php -S 0.0.0.0:8080 -t frontend/web`

# авторизация

Что б авторизироваться на сайте можно использовать

Artur

1234567890


# требования
- PHP7 и выше
- composer