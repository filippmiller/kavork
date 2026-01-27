# Technology Stack Documentation

**Project:** Kavork
**Type:** Cafe Management System
**Last Updated:** 2026-01-27

---

## Stack Overview

```
┌─────────────────────────────────────┐
│  Frontend (Browser)                 │
│  - jQuery, Bootstrap                │
│  - CKEditor, ElFinder               │
└─────────────┬───────────────────────┘
              │ AJAX/HTTP
┌─────────────▼───────────────────────┐
│  Web Server (Apache 2.4)            │
│  - PHP 8.2 + mod_php                │
│  - Yii2 Framework                   │
└─────────────┬───────────────────────┘
              │
    ┌─────────┴─────────┐
    │                   │
┌───▼──────────┐  ┌────▼──────────┐
│  MySQL DB    │  │  Queue Worker │
│  (Railway)   │  │  (Background) │
└──────────────┘  └───────────────┘
```

---

## Core Technologies

### Backend

#### PHP 8.2
- **Version:** 8.2.30
- **Why:** Latest stable PHP with improved performance and type safety
- **Key Features Used:**
  - Named arguments
  - Union types
  - Match expressions
  - Null-safe operator

#### Yii2 Framework
- **Version:** ~2.0.6
- **Type:** Full-stack MVC framework
- **Why:** Mature, secure, well-documented PHP framework
- **Key Components:**
  - Active Record ORM
  - URL routing and pretty URLs
  - RBAC (Role-Based Access Control)
  - Queue component for background jobs
  - Gii code generator

### Frontend

#### JavaScript Libraries

**jQuery** (via Yii2 assets)
- DOM manipulation
- AJAX requests
- Event handling

**Bootstrap 3.x**
- Responsive grid system
- UI components
- Form styling

**Kartik Widgets**
- Enhanced form inputs
- Date pickers
- Grid views
- File uploads

**CKEditor**
- WYSIWYG text editor
- Used for content management

**ElFinder**
- File manager
- Media library

#### Template Engine

**Twig v3**
- **Version:** ^3.0
- **Extension:** yii2-twig ^2.5
- **Why:** Secure template syntax, better than plain PHP templates
- **Usage:** Email templates, dynamic content rendering

### Database

#### MySQL
- **Version:** 8.x (Railway managed)
- **Why:** Reliable, well-supported relational database
- **Key Features:**
  - InnoDB storage engine
  - Transactions
  - Foreign key constraints
  - Full-text search

### Web Server

#### Apache 2.4
- **Modules Used:**
  - `mod_rewrite` - URL rewriting for Yii2 pretty URLs
  - `mod_php` - PHP integration
  - `mpm_prefork` - Process model
- **Configuration:**
  - Document root: `/var/www/html/frontend/web`
  - Port: 8080 (Railway sets via `$PORT`)
  - `.htaccess` support enabled

### Infrastructure

#### Railway
- **Platform:** Cloud hosting with automatic deployments
- **Features Used:**
  - Docker-based deployments
  - Automatic SSL
  - Environment variables
  - MySQL database (managed)
  - Health checks
  - Log aggregation

#### Docker
- **Base Image:** `php:8.2-apache` (Debian-based)
- **Why:** Consistent environment across dev/prod
- **Custom Setup:**
  - PHP extensions compiled in
  - Composer pre-installed
  - Queue worker auto-start

---

## Application Architecture

### Directory Structure

```
site_demo/
├── backend/           # Admin panel application
│   ├── config/        # Backend-specific config
│   ├── controllers/   # Backend controllers
│   ├── views/         # Backend views
│   └── web/          # Backend web root
│
├── common/            # Shared code
│   ├── components/    # Shared components (TwigString, Helper)
│   ├── config/        # Common configuration
│   ├── mail/          # Email templates
│   └── models/        # Shared models
│
├── console/           # CLI application
│   ├── config/        # Console config
│   ├── controllers/   # Console commands
│   └── migrations/    # Database migrations
│
├── frontend/          # User-facing application
│   ├── components/    # Frontend components
│   ├── config/        # Frontend config
│   ├── controllers/   # Frontend controllers
│   ├── modules/       # Feature modules (cafe, users, visits, etc.)
│   ├── views/         # Frontend views
│   └── web/          # Frontend web root (public)
│       ├── css/
│       ├── js/
│       └── index.php  # Application entry point
│
└── vendor/            # Composer dependencies (276+ packages)
```

### Module System

The application uses Yii2 modules for feature organization:

- **cafe** - Cafe management
- **users** - User authentication and management
- **visits** - Visitor tracking and time management
- **tasks** - Task management
- **franchisee** - Franchise operations
- **report** - Reporting and analytics
- **shop** - E-commerce features

---

## Background Processing

### Queue System

**Technology:** `yiisoft/yii2-queue` with DB backend

**Configuration:**
```php
'queue' => [
    'class' => \yii\queue\db\Queue::class,
    'db' => 'db',
    'tableName' => '{{%queue}}',
    'channel' => 'default',
    'mutex' => \yii\mutex\MysqlMutex::class,
]
```

**Worker Process:**
```bash
php yii queue/listen --verbose=1
```
- Runs in background via `nohup`
- Auto-starts in Docker entrypoint
- Logs to `/var/log/queue-worker.log`

**Use Cases:**
- Email sending (checkout receipts, notifications)
- Report generation
- Background data processing

---

## Security Features

### Application Security

1. **CSRF Protection**
   - Enabled by default in Yii2
   - Token validation on forms

2. **SQL Injection Protection**
   - Parameterized queries via Active Record
   - PDO prepared statements

3. **XSS Protection**
   - HTML encoding via `Html::encode()`
   - Twig auto-escaping

4. **RBAC (Role-Based Access Control)**
   - `developeruz/yii2-db-rbac`
   - Role and permission management

5. **Password Hashing**
   - `password_hash()` with bcrypt
   - Secure password verification

### Infrastructure Security

1. **HTTPS/SSL**
   - Automatic SSL via Railway
   - Forced HTTPS redirect

2. **Environment Variables**
   - Secrets stored in Railway environment
   - Not committed to Git

3. **File Permissions**
   - Runtime directories: `755`
   - Web-accessible: `/frontend/web` only

---

## Development Tools

### Code Generation

**Gii** - Yii2's code generator
- Model generation from DB tables
- CRUD generation
- Module scaffolding

### Testing (Development Only)

**Codeception** ^5.0
- Acceptance testing
- Functional testing
- Unit testing

**Note:** Test suite not currently in use in production

### Debugging (Development Only)

**Yii2 Debug Toolbar**
- Request profiling
- Database query analysis
- Log inspection

**Yii2 Gii**
- Code generation
- Schema inspection

---

## Key Dependencies

See [DEPENDENCIES.md](./DEPENDENCIES.md) for full dependency matrix.

### Critical Dependencies

1. **yiisoft/yii2** (~2.0.6)
   - Core framework

2. **twig/twig** (^3.0)
   - Template engine
   - **Requires:** yiisoft/yii2-twig ^2.5

3. **yiisoft/yii2-queue** (^2.1)
   - Background job processing

4. **yiisoft/yii2-swiftmailer** (~2.0.0 || ~2.1.0)
   - Email sending

5. **kartik-v/*** (various)
   - UI widgets and components

---

## Performance Considerations

### Optimization Techniques

1. **OPcache**
   - Currently DISABLED (was causing segfaults)
   - Should re-enable after stability confirmed

2. **Composer Autoloader**
   - Optimized with `--optimize-autoloader`
   - Class map generation

3. **Asset Compression**
   - Minified CSS/JS in production
   - Asset versioning for cache busting

4. **Database Indexing**
   - Indexed columns for common queries
   - Composite indexes where appropriate

### Known Performance Issues

1. **Long Build Times**
   - `composer update` takes 5-10 minutes
   - **Fix:** Switch to `composer install` (staged)

2. **Cold Start**
   - First request after deploy is slow
   - Apache/PHP initialization

---

## Monitoring & Logging

### Application Logs

**Location:**
- `/var/www/html/frontend/runtime/logs/app.log`
- `/var/www/html/console/runtime/logs/console.log`

**Configuration:**
```php
'log' => [
    'traceLevel' => 0,
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
        ],
    ],
],
```

### Server Logs

**Apache Access Log:** `stdout` (Railway logs)
**Apache Error Log:** `stderr` (Railway logs)
**PHP Error Log:** `stderr` (Railway logs)
**Queue Worker Log:** `/var/log/queue-worker.log`

### Health Checks

**Railway Health Check:**
- Endpoint: `GET /healthcheck.php`
- Interval: 30 seconds
- Timeout: 10 seconds

**Custom Health Check:**
- Endpoint: `GET /queue-status` (staged, not deployed)
- Provides queue and worker status

---

## Configuration Management

### Environment-Based Configuration

**Development:**
- `YII_DEBUG = true`
- `YII_ENV = 'dev'`
- Gii and Debug toolbar enabled

**Production (Railway):**
- `YII_DEBUG = false`
- `YII_ENV = 'prod'`
- No debug tools
- Production optimizations

### Configuration Files

**Common Config:**
- `common/config/main.php` - Shared config
- `common/config/main-local.php` - Environment-specific
- `common/config/params.php` - Application parameters

**Application Config:**
- `frontend/config/main.php` - Frontend app config
- `backend/config/main.php` - Backend app config
- `console/config/main.php` - Console app config

**Production Overrides (Docker):**
- `deploy-config/*.php` - Railway-specific configs
- Copied during Docker build
- Override local configs

---

## Known Issues & Limitations

### Current Issues

1. **OPcache Disabled**
   - Disabled due to segfaults during initial PHP 8.2 migration
   - Need to re-test and re-enable for performance

2. **Missing Logo**
   - 404 on `/img/logos/1570439749.png`
   - Non-critical, but appears in logs

3. **Build Performance**
   - Slow composer update (5-10 minutes)
   - Fix staged but not deployed

### Technical Debt

1. **Test Coverage**
   - Minimal automated tests
   - Should add integration tests

2. **Code Quality**
   - Some mixed PHP/HTML in views
   - Consider stricter linting

3. **Documentation**
   - API endpoints not documented
   - Need developer onboarding guide

---

## Upgrade Path

### Planned Upgrades

1. **Short Term:**
   - ✅ PHP 8.2 (completed)
   - ✅ Twig v3 (completed)
   - ⏳ Optimize build process
   - ⏳ Re-enable OPcache

2. **Medium Term:**
   - Yii2 to latest stable
   - Modernize frontend (Vue.js or React?)
   - Add automated testing

3. **Long Term:**
   - Evaluate Yii3 migration
   - Microservices architecture for queue processing
   - Containerize MySQL for local development

---

## References

- [Yii2 Documentation](https://www.yiiframework.com/doc/guide/2.0/en)
- [PHP 8.2 Release Notes](https://www.php.net/releases/8.2/en.php)
- [Twig Documentation](https://twig.symfony.com/doc/3.x/)
- [Railway Documentation](https://docs.railway.app/)
- [Apache Configuration](https://httpd.apache.org/docs/2.4/)
