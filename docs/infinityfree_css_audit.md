# InfinityFree CSS Audit

## Scope

Checked the staff-facing entry pages and their shared asset references for Linux hosting issues after deployment to InfinityFree.

## Live Hosting Findings

- `https://credencelend.free.nf/assets/css/theme.css` is being served successfully by InfinityFree.
- Live CSS response returned `200 OK` with `Content-Type: text/css`.
- `https://credencelend.free.nf/staff/login.php` is fronted by an InfinityFree JavaScript anti-bot challenge, so the final rendered HTML could not be fetched directly from the shell without the browser cookie flow.

## Codebase Findings

### 1. `APP_BASE` was wrong for root deployment

In [includes/auth.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/auth.php), the auto-detection logic set:

```php
if ($dir === '') $dir = '/';
```

That is unsafe for this codebase because most templates build paths like:

```php
APP_BASE . '/assets/css/theme.css'
APP_BASE . '/staff/dashboard.php'
```

When `APP_BASE` becomes `/`, those render as:

```php
//assets/css/theme.css
//staff/dashboard.php
```

On production, that breaks shared stylesheet and navigation URLs. This matches the symptom where sidebar and topbar appear unstyled, because they depend heavily on `theme.css`.

Fix applied:

```php
if ($dir === '' || $dir === '.') $dir = '';
```

This makes root deployment generate:

```php
/assets/css/theme.css
/staff/dashboard.php
```

### 1. Auth page CSS variables were output as JSON strings

These pages injected the tenant primary color into CSS like this:

```php
--login-primary: <?= json_encode($settings['primary_color'] ?? app_default_primary_color()) ?>;
```

That produces a quoted JSON string such as:

```css
--login-primary: "#0f1b35";
```

This is incorrect for CSS color tokens and can break any rule that uses the variable as a color in gradients, badges, buttons, or highlights.

Affected files:

- [staff/login.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/login.php)
- [staff/forgot_password.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/forgot_password.php)
- [staff/set_password.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/set_password.php)
- [staff/subscription_required.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/subscription_required.php)

### 2. Auth page CSS variables were output as JSON strings

The shared stylesheet include is consistent across the auth pages:

```php
<link rel="stylesheet" href="<?php echo APP_BASE; ?>/assets/css/theme.css">
```

The local file exists at:

- [assets/css/theme.css](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/assets/css/theme.css)

This means the more likely deployment issue for the login page was not a missing stylesheet file, but broken CSS values inside the page.

### 3. Shared stylesheet file exists, but path generation was broken

The shared stylesheet include pattern itself is fine:

```php
<link rel="stylesheet" href="<?php echo APP_BASE; ?>/assets/css/theme.css">
```

The failure came from `APP_BASE` being `/` on root deployment, which turned that into `//assets/css/theme.css`.

## Fix Applied

Added a shared helper in [includes/auth.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/auth.php):

```php
function app_primary_color($value = null) {
    $color = trim((string) ($value ?? ''));
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        return app_default_primary_color();
    }

    return strtolower($color);
}
```

Updated the affected pages to output a sanitized raw hex color instead of JSON:

```php
--login-primary: <?= htmlspecialchars(app_primary_color($settings['primary_color'] ?? null), ENT_QUOTES) ?>;
```

## Result

- Root deployment now generates `/assets/...` and `/staff/...` instead of protocol-relative `//assets/...` and `//staff/...`.
- Tenant color variables now render as proper CSS color values like `#0f1b35`.
- Login, forgot-password, set-password, and subscription-required pages should render consistently on InfinityFree after redeploy.
- Sidebar and topbar pages that include [staff/_layout_top.php](C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/_layout_top.php) should load their shared layout styling correctly after redeploy.

## Remaining Deployment Notes

- Because the live login page is behind InfinityFree’s anti-bot challenge, browser-only issues can still exist outside shell inspection.
- Internal app pages still rely on some external CDNs such as Bootstrap Icons and Chart.js. If a page looks incomplete while `theme.css` loads, check whether those CDN requests are being blocked in the browser.
