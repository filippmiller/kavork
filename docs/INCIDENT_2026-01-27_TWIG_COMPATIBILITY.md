# Incident Report: Twig v3 Compatibility Issue

**Date:** 2026-01-27
**Severity:** Critical (Site Down)
**Duration:** ~15 minutes
**Status:** Resolved

---

## Summary

Site crashed in production due to incompatible Twig library versions after PHP 8.2 upgrade. Custom component `TwigString` was using deprecated Twig v1/v2 class names that don't exist in Twig v3.

---

## Timeline

### 15:00 UTC - Initial Deployment
- Deployed Twig v3 compatibility fix for PHP 8.2
- Updated `yii2-twig` from `~2.0.0` to `~2.1.0`
- Updated `TwigString.php` component with Twig v3 classes

### 15:02 UTC - Build Failure
**Error:**
```
Problem 1
- yiisoft/yii2-twig[2.1.0, 2.1.1] require twig/twig ~1.0
- Conflicts with root composer.json require (^3.0)
```

**Root Cause:** `yii2-twig ~2.1.0` only supports Twig v1, not Twig v3

### 15:05 UTC - Site Down
- Build failed, previous deployment still running
- Users seeing 500 errors with "Class Twig_Environment not found"

### 15:10 UTC - Hotfix Deployed
**Fix:** Changed `yii2-twig` version from `~2.1.0` to `^2.5`
- `yii2-twig 2.5.0+` supports Twig v3.9
- Commit: `59d4587`

### 15:15 UTC - Site Restored
- Build completed successfully
- All tests passing
- Site operational

---

## Root Cause Analysis

### What Went Wrong

1. **Incorrect version constraint research**
   - Assumed `yii2-twig ~2.1.0` would support Twig v3
   - Did not verify version compatibility before deployment

2. **Two-part dependency issue**
   - Custom `TwigString` component used old Twig v1/v2 class names
   - Package version constraint was incompatible with Twig v3

### Why It Happened

- PHP 8.2 upgrade required Twig v3 (Twig v2 not compatible)
- `yii2-twig` extension has complex version history:
  - v2.0.x → Twig v1
  - v2.1.x → Twig v1/v2
  - v2.5.0+ → Twig v3

---

## Technical Details

### Changes Made

#### File: `site_demo/composer.json`
```diff
- "yiisoft/yii2-twig": "~2.0.0",
+ "yiisoft/yii2-twig": "^2.5",
  "twig/twig": "^3.0",
```

#### File: `site_demo/common/components/TwigString.php`
```diff
- use Twig_Loader_String;
- use Twig_Environment;
+ use Twig\Loader\ArrayLoader;
+ use Twig\Environment;

- $loader = new Twig_Loader_String();
+ $loader = new ArrayLoader([]);

- $this->twig = new \Twig_Environment($loader, $params);
+ $this->twig = new Environment($loader, $params);

- $classFunction = 'Twig_Simple' . $classType;
+ $classFunction = '\Twig\Twig' . $classType;

- case $func instanceof \Twig_SimpleFunction || $func instanceof \Twig_SimpleFilter:
+ case $func instanceof \Twig\TwigFunction || $func instanceof \Twig\TwigFilter:
```

### Twig v3 Migration Summary

| Twig v1/v2 Class | Twig v3 Replacement |
|------------------|---------------------|
| `Twig_Loader_String` | `Twig\Loader\ArrayLoader` |
| `Twig_Environment` | `Twig\Environment` |
| `Twig_SimpleFunction` | `Twig\TwigFunction` |
| `Twig_SimpleFilter` | `Twig\TwigFilter` |

**Note:** Twig v3 removed the "string loader" - templates must be added to ArrayLoader first.

---

## Resolution

### Immediate Actions Taken

1. ✅ Identified correct `yii2-twig` version (`^2.5`)
2. ✅ Updated `composer.json` with compatible version
3. ✅ Deployed hotfix (commit `59d4587`)
4. ✅ Verified site operational

### Verification Steps

- [x] Site loads without errors
- [x] Twig templates render correctly
- [x] Queue worker started successfully
- [x] No PHP errors in logs
- [x] Email functionality working

---

## Lessons Learned

### What Went Well

- Quick identification of root cause (dependency conflict)
- Fast hotfix deployment (~5 minutes)
- Git history preserved issue for future reference

### What Could Be Improved

1. **Pre-deployment testing**
   - Test composer dependency resolution before committing
   - Use `composer update --dry-run` to preview changes

2. **Research verification**
   - Verify version compatibility via official docs/Packagist
   - Check CHANGELOG for breaking changes

3. **Deployment strategy**
   - Deploy during low-traffic hours
   - Have rollback plan ready

4. **Monitoring**
   - Add health check endpoint (queue status)
   - Set up error alerting for critical failures

---

## Action Items

- [x] Document incident and resolution
- [x] Update dependencies to stable versions
- [ ] Create queue monitoring endpoint (staged, not deployed)
- [ ] Add pre-deployment checklist to docs
- [ ] Consider staging environment for testing

---

## References

- [yii2-twig Release Notes](https://www.yiiframework.com/news/634/twig-extension-2-5-0-released)
- [Twig v3 Migration Guide](https://twig.symfony.com/doc/3.x/deprecated.html)
- Commit history: `cca45c2` (broken), `59d4587` (fix)

---

## Contact

If similar issues occur, refer to this document and verify:
1. Twig version compatibility with `yii2-twig`
2. Custom components using Twig classes
3. Composer lock file regeneration after PHP version changes
