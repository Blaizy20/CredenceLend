# InfinityFree Post-Login Redirect Fix

## Problem Found

Login completed successfully, but the post-login redirect could still use a raw `return_url` value from session state.

The issue was in [includes/auth.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/auth.php):

```php
function resolve_post_login_redirect_url($role = null, $return_url = null, $tenant_id = null) {
    $role = $role ?? current_role();
    $tenant_id = normalize_tenant_id($tenant_id ?? current_active_tenant_id());
    $default_url = subscription_default_redirect_url($role, $tenant_id);

    if (can_redirect_to_return_url($return_url, $tenant_id)) {
        return $return_url;
    }

    return $default_url;
}
```

That allowed values like `staff/dashboard.php` to be returned unchanged. On InfinityFree, the browser could interpret that as a hostname-style redirect target instead of an application path, which matches the `DNS_PROBE_POSSIBLE` / "staffs DNS address could not be found" symptom.

## Change Made

Added redirect normalization in [includes/auth.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/auth.php) so post-login redirects are converted to safe local paths.

Current fixed function:

```php
function resolve_post_login_redirect_url($role = null, $return_url = null, $tenant_id = null) {
    $role = $role ?? current_role();
    $tenant_id = normalize_tenant_id($tenant_id ?? current_active_tenant_id());
    $default_url = normalize_app_redirect_url(
        subscription_default_redirect_url($role, $tenant_id),
        '/staff/dashboard.php'
    );
    $return_url = normalize_app_redirect_url($return_url);

    if (can_redirect_to_return_url($return_url, $tenant_id)) {
        return $return_url;
    }

    return $default_url;
}
```

Added helper:

```php
function normalize_app_redirect_url($url, $fallback = null) {
    $url = trim((string) $url);
    if ($url === '') {
        return $fallback;
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return $fallback;
    }

    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = strtolower((string) ($parts['host'] ?? ''));
    $path = (string) ($parts['path'] ?? '');

    if ($scheme !== '' || $host !== '') {
        $current_host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($scheme === '' || $host === '' || ($current_host !== '' && $host !== $current_host)) {
            return $fallback;
        }
    }

    if ($path === '') {
        $path = '/';
    }

    if (strpos($path, '//') === 0) {
        return $fallback;
    }

    $path = '/' . ltrim($path, '/');

    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';

    return $path . $query . $fragment;
}
```

## Result

The redirect now resolves to a root-relative path such as:

```php
/staff/dashboard.php
```

So the final header becomes effectively:

```php
header("Location: /staff/dashboard.php");
exit;
```

Or, when deployed from a subfolder in XAMPP:

```php
header("Location: /LOAN_MANAGEMENT_APP/staff/dashboard.php");
exit;
```

## APP_BASE Check

`APP_BASE` was already correct and was not hardcoded to `staff`.

Current definition in [includes/auth.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/auth.php):

```php
if (!defined('APP_BASE')) {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $dir = preg_replace('#/(staff|setup)$#', '', $dir);
    if ($dir === '') $dir = '/';
    define('APP_BASE', $dir);
}
```

This means:

- InfinityFree root deployment becomes `/`
- XAMPP subfolder deployment becomes `/LOAN_MANAGEMENT_APP`

## Findings Summary

- `resolve_post_login_redirect_url()` returned raw session `return_url` values without normalizing them.
- Relative values like `staff/dashboard.php` were the likely cause of the broken redirect.
- `APP_BASE` was not the problem in the current codebase.
- The fix forces post-login redirects to begin with `/` and rejects malformed or off-host absolute URLs.
