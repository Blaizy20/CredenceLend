<?php
require_once __DIR__ . '/../includes/auth.php';

require_login();

if (is_super_admin()) {
  header('Location: ' . APP_BASE . '/staff/dashboard.php');
  exit;
}

$tenant_id = current_active_tenant_id();
if (!$tenant_id) {
  header('Location: ' . APP_BASE . '/staff/select_tenant.php');
  exit;
}

$access = current_tenant_subscription_access($tenant_id);
if (!empty($access['allowed'])) {
  header('Location: ' . APP_BASE . '/staff/dashboard.php');
  exit;
}

http_response_code(403);

$tenant = fetch_one(q(
  "SELECT tenant_id, tenant_name, COALESCE(display_name, tenant_name) AS display_name, subdomain
   FROM tenants
   WHERE tenant_id = ?
   LIMIT 1",
  "i",
  [$tenant_id]
));
$plan = get_tenant_plan_human_info($tenant_id);
$settings = get_system_settings($tenant_id);
$is_admin = is_admin_owner();
$owned_tenant_count = $is_admin ? count(user_owned_tenants($_SESSION['user_id'] ?? 0, true)) : 0;
$can_switch_tenant = is_super_admin() || $owned_tenant_count > 1;

$status_class = 'warn';
if (in_array($access['status'], ['SUSPENDED', 'CANCELLED', 'EXPIRED', 'MISSING'], true)) {
  $status_class = 'bad';
}

$headline = $access['title'] ?: 'Subscription Required';
$support_copy = $is_admin
  ? 'Review the active tenant subscription, switch to another tenant you own, or contact the billing owner to reactivate access.'
  : 'Core modules are unavailable until this tenant subscription is active again. Contact your tenant administrator for the next billing step.';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars($headline) ?></title>
  <link rel="stylesheet" href="<?php echo APP_BASE; ?>/assets/css/theme.css">
  <style>
    :root {
      --block-primary: <?= htmlspecialchars(app_primary_color($settings['primary_color'] ?? null), ENT_QUOTES) ?>;
      --block-bg: #081121;
      --block-panel: rgba(8, 15, 30, 0.94);
      --block-line: rgba(148, 163, 184, 0.16);
      --block-copy: #dbe6f6;
      --block-muted: #93a4c3;
    }

    body {
      min-height: 100vh;
      margin: 0;
      background:
        radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 24%),
        radial-gradient(circle at bottom right, rgba(239, 68, 68, 0.12), transparent 28%),
        linear-gradient(180deg, #020617 0%, #081121 44%, #0f172a 100%);
      color: var(--block-copy);
      font-family: inherit;
    }

    .block-shell {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px;
    }

    .block-card {
      width: min(980px, 100%);
      border-radius: 30px;
      border: 1px solid var(--block-line);
      background:
        radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 30%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.98), var(--block-panel));
      box-shadow: 0 30px 80px rgba(2, 6, 23, 0.42);
      overflow: hidden;
    }

    .block-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
    }

    .block-main,
    .block-side {
      padding: 32px;
    }

    .block-side {
      border-left: 1px solid var(--block-line);
      background: rgba(15, 23, 42, 0.62);
    }

    .block-kicker,
    .status-badge,
    .plan-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      width: fit-content;
      padding: 8px 12px;
      border-radius: 999px;
      font-size: 12px;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .block-kicker {
      border: 1px solid rgba(125, 211, 252, 0.24);
      background: rgba(14, 165, 233, 0.12);
      color: #d8f4ff;
    }

    .status-badge {
      margin-top: 16px;
      border: 1px solid rgba(245, 158, 11, 0.18);
      background: rgba(245, 158, 11, 0.14);
      color: #fde68a;
    }

    .status-badge.bad {
      border-color: rgba(248, 113, 113, 0.2);
      background: rgba(239, 68, 68, 0.14);
      color: #fecaca;
    }

    .plan-pill {
      margin-top: 12px;
      border: 1px solid rgba(148, 163, 184, 0.18);
      background: rgba(15, 23, 42, 0.72);
      color: var(--block-copy);
    }

    h1 {
      margin: 18px 0 12px;
      color: #f8fbff;
      font-size: clamp(34px, 5vw, 56px);
      line-height: 0.98;
      letter-spacing: -0.05em;
    }

    p {
      margin: 0;
      color: var(--block-muted);
      font-size: 15px;
      line-height: 1.75;
    }

    .block-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 28px;
    }

    .block-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 46px;
      padding: 0 18px;
      border-radius: 999px;
      border: 1px solid var(--block-line);
      text-decoration: none;
      color: #f8fbff;
      background: rgba(15, 23, 42, 0.7);
      font-weight: 700;
    }

    .block-action.primary {
      border-color: transparent;
      background: linear-gradient(135deg, var(--block-primary), #1d4ed8);
      box-shadow: 0 16px 28px rgba(29, 78, 216, 0.28);
    }

    .tenant-meta,
    .side-list {
      display: grid;
      gap: 14px;
    }

    .meta-card,
    .side-card {
      padding: 18px;
      border-radius: 20px;
      border: 1px solid var(--block-line);
      background: rgba(15, 23, 42, 0.72);
    }

    .meta-label,
    .side-label {
      color: #8ea3bf;
      font-size: 11px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }

    .meta-value,
    .side-value {
      margin-top: 8px;
      color: #f8fbff;
      font-size: 22px;
      font-weight: 800;
      line-height: 1.15;
    }

    .side-copy {
      margin-top: 8px;
      font-size: 14px;
    }

    @media (max-width: 860px) {
      .block-grid {
        grid-template-columns: 1fr;
      }

      .block-side {
        border-left: 0;
        border-top: 1px solid var(--block-line);
      }
    }

    @media (max-width: 640px) {
      .block-main,
      .block-side {
        padding: 22px;
      }

      .block-actions {
        flex-direction: column;
      }

      .block-action {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="block-shell">
    <section class="block-card">
      <div class="block-grid">
        <div class="block-main">
          <span class="block-kicker">Tenant Subscription Access</span>
          <h1><?= htmlspecialchars($headline) ?></h1>
          <p><?= htmlspecialchars($access['message']) ?></p>
          <span class="status-badge <?= htmlspecialchars($status_class) ?>"><?= htmlspecialchars($access['label']) ?></span>
          <?php if (!empty($plan['plan_label'])): ?>
            <div class="plan-pill"><?= htmlspecialchars($plan['plan_label']) ?></div>
          <?php endif; ?>

          <div class="block-actions">
            <?php if ($is_admin): ?>
              <a class="block-action primary" href="<?php echo APP_BASE; ?>/staff/my_subscription.php">Review My Subscription</a>
            <?php endif; ?>
            <?php if ($can_switch_tenant): ?>
              <a class="block-action" href="<?php echo APP_BASE; ?>/staff/select_tenant.php">Switch Tenant</a>
            <?php endif; ?>
            <a class="block-action" href="<?php echo APP_BASE; ?>/staff/logout.php">Logout</a>
          </div>

          <p style="margin-top:18px"><?= htmlspecialchars($support_copy) ?></p>
        </div>

        <aside class="block-side">
          <div class="tenant-meta">
            <div class="meta-card">
              <div class="meta-label">Tenant</div>
              <div class="meta-value"><?= htmlspecialchars($tenant['display_name'] ?? 'Unknown Tenant') ?></div>
              <?php if (!empty($tenant['subdomain'])): ?>
                <p class="side-copy"><?= htmlspecialchars($tenant['subdomain']) ?></p>
              <?php endif; ?>
            </div>
            <div class="meta-card">
              <div class="meta-label">Current plan</div>
              <div class="meta-value"><?= htmlspecialchars($plan['plan_name'] ?? 'Unknown') ?></div>
              <p class="side-copy"><?= htmlspecialchars($plan['price_label'] ?? 'Plan unavailable') ?></p>
            </div>
          </div>

          <div class="side-list" style="margin-top:16px">
            <div class="side-card">
              <div class="side-label">Access rule</div>
              <div class="side-value">Core modules blocked</div>
              <p class="side-copy">Dashboard, loans, customers, payments, reports, and other tenant operations stay blocked until the subscription returns to `ACTIVE` or `TRIAL`.</p>
            </div>
            <div class="side-card">
              <div class="side-label">Allowed while blocked</div>
              <div class="side-value">Subscription review</div>
              <p class="side-copy">Tenant selection, logout, account access, and subscription review remain available so the session can recover cleanly.</p>
            </div>
          </div>
        </aside>
      </div>
    </section>
  </div>
</body>
</html>
