# Dependency Matrix

**Project:** Kavork
**Last Updated:** 2026-01-27
**Total Packages:** 276+

---

## PHP Version Requirements

| Component | Minimum | Maximum | Current |
|-----------|---------|---------|---------|
| **PHP Runtime** | 8.1 | 8.5 | **8.2.30** |

---

## Critical Dependencies

### Core Framework

| Package | Version | PHP Requirement | Purpose | Notes |
|---------|---------|-----------------|---------|-------|
| **yiisoft/yii2** | ~2.0.6 | >=5.4.0 | Core MVC framework | Main application framework |
| yiisoft/yii2-bootstrap | ~2.0.0 | >=5.4.0 | Bootstrap integration | UI framework integration |
| yiisoft/yii2-swiftmailer | ~2.0.0 \|\| ~2.1.0 | >=5.4.0 | Email sending | SMTP email support |

### Template Engine

| Package | Version | PHP Requirement | Purpose | Dependencies |
|---------|---------|-----------------|---------|--------------|
| **twig/twig** | ^3.0 | >=7.2.5 | Template engine | - |
| **yiisoft/yii2-twig** | ^2.5 | >=5.4.0 | Twig integration for Yii2 | **Requires twig/twig ^3.0** |

**⚠️ Important Version Compatibility:**
- `yii2-twig ~2.0.x` → Twig v1 only
- `yii2-twig ~2.1.x` → Twig v1/v2 only
- `yii2-twig ^2.5` → **Twig v3** ✅

### Queue Processing

| Package | Version | PHP Requirement | Purpose | Notes |
|---------|---------|-----------------|---------|-------|
| **yiisoft/yii2-queue** | ^2.1 | >=5.5.0 | Background job processing | DB-backed queue |
| yiisoft/yii2-faker | ~2.0.0 | >=5.4.0 | Test data generation | Development only |

---

## UI & Frontend Components

### Widget Libraries

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| kartik-v/yii2-widgets | ^3.4 | Enhanced form widgets | Date pickers, file inputs |
| kartik-v/yii2-field-range | * | Range field widget | Numeric ranges |
| kartik-v/yii2-slider | dev-master | Slider widget | Value sliders |
| kartik-v/yii2-date-range | dev-master | Date range picker | Date filtering |
| kartik-v/yii2-widget-typeahead | * | Autocomplete widget | Search suggestions |
| kartik-v/yii2-mpdf | dev-master | PDF generation | Reports, receipts |

### Content Management

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| mihaildev/yii2-ckeditor | * | WYSIWYG editor | Rich text editing |
| mihaildev/yii2-elfinder | * | File manager | Media library |
| unclead/yii2-multiple-input | ~2.0 | Dynamic form inputs | Multiple input rows |

### Charts & Visualization

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| 2amigos/yii2-highcharts-widget | ~1.0 | Highcharts integration | Legacy charts |
| miloschuman/yii2-highcharts-widget | ^6.0 | Highcharts integration | Modern charts |

---

## Utilities

### AJAX & CRUD

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| johnitvn/yii2-ajaxcrud | * | AJAX CRUD generator | Modal-based CRUD |

### Access Control

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| developeruz/yii2-db-rbac | * | Database RBAC | Role/permission management |

### Code Generation

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| yiimaker/yii2-gii-migration | ^1.1 | Migration generator | DB schema versioning |

### QR Code

| Package | Version | Purpose | Dependencies |
|---------|---------|---------|--------------|
| 2amigos/qrcode-library | ~1.1 | QR code generation | bacon/bacon-qr-code ^1.0 |

### Payment Processing

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| paypal/rest-api-sdk-php | ^1.11 | PayPal integration | Payment processing |

### Calendar

| Package | Version | Purpose | Notes |
|---------|---------|---------|-------|
| edofre/yii2-fullcalendar | V1.0.11 | Calendar widget | Event scheduling |

---

## Development Dependencies

Only installed in development (`require-dev`):

| Package | Version | Purpose | PHP Requirement |
|---------|---------|---------|-----------------|
| yiisoft/yii2-debug | ~2.1.0 | Debug toolbar | >=5.4.0 |
| yiisoft/yii2-gii | ~2.1.0 | Code generator | >=5.4.0 |
| codeception/codeception | ^5.0 | Testing framework | >=8.1 |
| codeception/verify | ^3.0 | Test assertions | >=8.1 |

**Note:** Not installed in production (`--no-dev` flag)

---

## PHP Extensions Required

These must be installed in the PHP runtime:

| Extension | Purpose | Installation |
|-----------|---------|--------------|
| **pdo** | Database abstraction | `docker-php-ext-install pdo` |
| **pdo_mysql** | MySQL driver | `docker-php-ext-install pdo_mysql` |
| **mysqli** | MySQL improved | `docker-php-ext-install mysqli` |
| **gd** | Image processing | `docker-php-ext-install gd` |
| **zip** | Archive handling | `docker-php-ext-install zip` |
| **intl** | Internationalization | `docker-php-ext-install intl` |
| opcache | Bytecode caching | `docker-php-ext-install opcache` |

### Extension Configuration

**GD Extension:**
```bash
docker-php-ext-configure gd --with-freetype --with-jpeg
```

**System Libraries for Extensions:**
- libpng-dev (for GD)
- libjpeg-dev (for GD)
- libfreetype6-dev (for GD)
- libzip-dev (for zip)
- libicu-dev (for intl)

---

## Version Conflicts & Resolutions

### Past Issues

#### 1. Twig Version Conflict (2026-01-27) ❌ → ✅

**Problem:**
```
yiisoft/yii2-twig ~2.1.0 requires twig/twig ~1.0
Conflicts with root composer.json require (^3.0)
```

**Resolution:**
```json
"yiisoft/yii2-twig": "^2.5",  // Changed from ~2.1.0
"twig/twig": "^3.0"            // Required for PHP 8.2
```

#### 2. Faker Abandonment (2026-01-17) ❌ → ✅

**Problem:**
```
fzaninotto/faker is abandoned
```

**Resolution:**
- Removed `fzaninotto/faker`
- Added `yiisoft/yii2-faker` (maintained fork)

---

## Dependency Update Strategy

### When to Update

**Security Updates:** Immediately
- Check: `composer audit`
- Apply: `composer update --with-dependencies package/name`

**Minor Updates:** Monthly maintenance window
- Patch versions (1.2.3 → 1.2.4)
- Low risk, bug fixes only

**Major Updates:** Quarterly + testing
- Breaking changes (1.x → 2.x)
- Requires testing before production

### Update Process

1. **Check for updates:**
   ```bash
   composer outdated
   ```

2. **Test update (dry-run):**
   ```bash
   composer update --dry-run
   ```

3. **Update specific package:**
   ```bash
   composer update vendor/package --with-dependencies
   ```

4. **Update all (rare):**
   ```bash
   composer update
   ```

5. **Commit composer.lock:**
   ```bash
   git add composer.json composer.lock
   git commit -m "Update dependencies"
   ```

---

## Composer Configuration

### composer.json Settings

```json
{
  "minimum-stability": "stable",
  "config": {
    "process-timeout": 1800,
    "fxp-asset": {
      "enabled": false
    },
    "audit": {
      "abandoned": "ignore",
      "block-insecure": false
    },
    "allow-plugins": {
      "yiisoft/yii2-composer": true
    }
  }
}
```

### Asset Management

**Asset Packagist:**
- URL: https://asset-packagist.org
- Purpose: Bower and NPM packages for PHP projects
- Used for: jQuery, Bootstrap, etc.

---

## Production vs Development Differences

| Aspect | Development | Production |
|--------|-------------|------------|
| **Dependencies** | All packages | `--no-dev` (no debug/test tools) |
| **Autoloader** | Standard | `--optimize-autoloader` |
| **Composer Platform** | System PHP | `--ignore-platform-reqs` (Docker) |
| **Updates** | `composer update` | `composer install` |

---

## Known Abandoned Packages

| Package | Status | Replacement | Action |
|---------|--------|-------------|--------|
| fzaninotto/faker | Abandoned | yiisoft/yii2-faker | ✅ Replaced |

**Check for abandoned packages:**
```bash
composer outdated --direct --minor-only
```

---

## Dependency Tree (Key Packages)

```
yiisoft/yii2
├── yiisoft/yii2-composer
├── bower-asset/jquery
├── bower-asset/bootstrap
└── cebe/markdown

yiisoft/yii2-twig ^2.5
├── twig/twig ^3.0
└── yiisoft/yii2 ~2.0

yiisoft/yii2-queue
├── yiisoft/yii2 ~2.0
└── yiisoft/yii2-db ~2.0

kartik-v/yii2-widgets
├── yiisoft/yii2 ~2.0
├── kartik-v/yii2-krajee-base
└── bower-asset/bootstrap

2amigos/qrcode-library
├── bacon/bacon-qr-code ^1.0
├── khanamiryan/qrcode-detector-decoder ^1.0
└── ext-gd *
```

---

## Vulnerability Scanning

### Manual Check
```bash
composer audit
```

### Automated (CI/CD)
- Add to build pipeline
- Fail build on high-severity issues

### Update Process
1. Review CVE details
2. Check if upgrade is available
3. Test in staging
4. Deploy to production

---

## License Compliance

Most dependencies use permissive licenses:
- **BSD-3-Clause** (Yii2)
- **MIT** (Twig, most Kartik widgets)
- **Apache 2.0** (PayPal SDK)

**Action Required:**
- Review license file if redistributing
- No issues for internal/SaaS use

---

## Dependency Health Metrics

### Current State (2026-01-27)

| Metric | Count | Status |
|--------|-------|--------|
| **Total Packages** | 276+ | ⚠️ High |
| **Abandoned** | 0 | ✅ Good |
| **Security Issues** | 0 | ✅ Good |
| **Outdated (major)** | Unknown | ⚠️ Need audit |
| **PHP 8.2 Compatible** | All | ✅ Good |

### Recommendations

1. **Reduce package count**
   - Audit unused dependencies
   - Remove redundant packages

2. **Regular maintenance**
   - Monthly: Check for updates
   - Quarterly: Major version upgrades

3. **Monitoring**
   - Set up automated vulnerability scanning
   - Subscribe to security advisories

---

## References

- [Packagist](https://packagist.org/) - PHP package repository
- [Asset Packagist](https://asset-packagist.org/) - Frontend assets for Composer
- [Composer Docs](https://getcomposer.org/doc/) - Dependency manager documentation
- [PHP Version Support](https://www.php.net/supported-versions.php) - Official PHP support timeline

---

## Change Log

| Date | Change | Reason |
|------|--------|--------|
| 2026-01-27 | Twig ^3.0, yii2-twig ^2.5 | PHP 8.2 compatibility |
| 2026-01-27 | Removed fzaninotto/faker | Package abandoned |
| 2026-01-27 | PHP 8.2 upgrade | Security & performance |
