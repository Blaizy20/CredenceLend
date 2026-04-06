<?php
require_once __DIR__ . '/../includes/auth.php';

require_login();
require_roles(['ADMIN']);
require_permission('view_subscription');

$tenant_id = require_current_tenant_id();
$tenant = fetch_one(q(
  "SELECT
     tenant_id,
     tenant_name,
     COALESCE(display_name, tenant_name) AS display_name,
     subdomain,
     tenant_status,
     created_at
   FROM tenants
   WHERE tenant_id = ?
   LIMIT 1",
  "i",
  [$tenant_id]
));

if (!$tenant) {
  http_response_code(404);
  echo "Tenant not found.";
  exit;
}

$title = "My Subscription";
$active = "my_subscription";

$plan = get_tenant_plan($tenant_id);
$subscription = get_tenant_subscription_status($tenant_id);
$human_info = get_tenant_plan_human_info($tenant_id);
$counts = get_tenant_user_counts($tenant_id);
$feature_labels = subscription_feature_labels();
$role_labels = subscription_billable_role_labels();
$role_limit_map = subscription_role_limit_map();

$format_datetime = static function ($value, $fallback = 'Not set') {
  $value = trim((string) $value);
  if ($value === '' || $value === '0000-00-00 00:00:00') {
    return $fallback;
  }

  $timestamp = strtotime($value);
  if (!$timestamp) {
    return $fallback;
  }

  return date('M d, Y', $timestamp);
};

$plan_name = trim((string) ($human_info['plan_name'] ?? 'Unknown'));
$monthly_price = 'PHP ' . number_format(floatval($plan['monthly_price'] ?? 0), 2) . '/month';
$start_date = $format_datetime($plan['subscription_started_at'] ?? ($tenant['created_at'] ?? null));
$expiry_date = $format_datetime($plan['subscription_expires_at'] ?? null, 'No expiry set');
$usage_summary = $human_info['usage_summary'] ?? 'Unavailable';

$included_features = [];
$excluded_features = [];
foreach ($feature_labels as $column => $label) {
  if (!empty($plan[$column])) {
    $included_features[] = $label;
  } else {
    $excluded_features[] = $label;
  }
}

$role_limit_rows = [];
foreach ($role_limit_map as $role_key => $limit_column) {
  $role_limit_rows[] = [
    'label' => $role_labels[$role_key] ?? $role_key,
    'limit' => subscription_plan_limit_label($plan, $limit_column),
    'current' => intval($counts['by_role'][$role_key] ?? 0),
  ];
}

$status_badge_class = 'neutral';
if (in_array($subscription['status'], ['ACTIVE', 'TRIAL'], true)) {
  $status_badge_class = 'good';
} elseif (in_array($subscription['status'], ['PAST_DUE', 'PENDING'], true)) {
  $status_badge_class = 'warn';
} elseif (in_array($subscription['status'], ['SUSPENDED', 'CANCELLED', 'EXPIRED', 'MISSING'], true)) {
  $status_badge_class = 'bad';
}

include __DIR__ . '/_layout_top.php';
?>
<style>
  body {
    background:
      radial-gradient(circle at top, rgba(14, 165, 233, 0.12), transparent 30%),
      linear-gradient(180deg, #020617 0%, #081121 42%, #0f172a 100%);
    color: #e5eefb;
  }

  .topbar {
    background: linear-gradient(135deg, #081121, #0f1b35) !important;
    border-bottom: 1px solid rgba(148, 163, 184, 0.14);
    box-shadow: 0 18px 40px rgba(2, 6, 23, 0.35);
  }

  .topbar .small,
  .topbar a.btn.btn-outline {
    color: #d8e4f5 !important;
    border-color: rgba(148, 163, 184, 0.24) !important;
  }

  .layout,
  .main {
    background: transparent;
  }

  .my-subscription-shell {
    display: grid;
    gap: 24px;
  }

  .hero-card,
  .panel {
    border-radius: 28px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
      radial-gradient(circle at top left, rgba(56, 189, 248, 0.14), transparent 30%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.95));
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
  }

  .hero-card {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(280px, 1fr);
    gap: 24px;
    padding: 32px;
  }

  .eyebrow,
  .usage-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: fit-content;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(125, 211, 252, 0.24);
    background: rgba(14, 165, 233, 0.12);
    color: #d8f4ff;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .hero-card h1 {
    margin: 14px 0 10px;
    font-size: clamp(32px, 4vw, 50px);
    line-height: 0.98;
    letter-spacing: -0.05em;
    color: #f8fbff;
  }

  .hero-card p,
  .meta-copy,
  .muted-copy {
    margin: 0;
    color: #9fb0c9;
    font-size: 14px;
    line-height: 1.7;
  }

  .tenant-summary {
    display: grid;
    gap: 16px;
    align-content: start;
  }

  .tenant-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }

  .summary-stat {
    padding: 18px;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: rgba(15, 23, 42, 0.78);
  }

  .summary-stat-label {
    color: #7f93b0;
    font-size: 11px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .summary-stat-value {
    margin-top: 10px;
    color: #f8fbff;
    font-size: 23px;
    font-weight: 800;
    line-height: 1.15;
  }

  .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .status-badge.good {
    background: rgba(16, 185, 129, 0.16);
    color: #9ef0cd;
  }

  .status-badge.warn {
    background: rgba(245, 158, 11, 0.16);
    color: #fcd69a;
  }

  .status-badge.bad {
    background: rgba(239, 68, 68, 0.16);
    color: #fecaca;
  }

  .status-badge.neutral {
    background: rgba(148, 163, 184, 0.16);
    color: #dbe6f6;
  }

  .content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
    gap: 24px;
  }

  .panel {
    padding: 24px;
  }

  .panel h2 {
    margin: 0 0 6px;
    color: #f8fbff;
    font-size: 22px;
  }

  .panel-copy {
    margin: 0 0 18px;
    color: #9fb0c9;
    font-size: 14px;
  }

  .feature-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
  }

  .feature-group {
    display: grid;
    gap: 12px;
  }

  .feature-group h3 {
    margin: 0;
    color: #dbe6f6;
    font-size: 15px;
  }

  .feature-list {
    margin: 0;
    padding-left: 20px;
    color: #e5eefb;
  }

  .feature-list li {
    margin: 0 0 8px;
    font-size: 14px;
    line-height: 1.6;
  }

  .feature-list.excluded {
    color: #fecaca;
  }

  .usage-stack {
    display: grid;
    gap: 14px;
  }

  .usage-card {
    padding: 18px;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: rgba(15, 23, 42, 0.78);
  }

  .role-limit-table {
    width: 100%;
    border-collapse: collapse;
  }

  .role-limit-table th,
  .role-limit-table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    text-align: left;
  }

  .role-limit-table th {
    color: #8ea5c4;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .role-limit-table td {
    color: #e5eefb;
    font-size: 14px;
  }

  .restriction-note {
    margin-top: 16px;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid rgba(248, 113, 113, 0.18);
    background: rgba(239, 68, 68, 0.08);
    color: #fecaca;
    font-size: 14px;
  }

  @media (max-width: 1100px) {
    .hero-card,
    .content-grid,
    .feature-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 720px) {
    .hero-card,
    .panel {
      padding: 20px;
    }

    .tenant-summary-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="my-subscription-shell">
  <section class="hero-card">
    <div>
      <span class="eyebrow"><i class="bi bi-stars"></i> My Subscription</span>
      <h1><?= htmlspecialchars($plan_name) ?> plan for <?= htmlspecialchars($tenant['display_name']) ?>.</h1>
      <p>This page is scoped to your active tenant only. It shows the current plan, subscription lifecycle, enabled capabilities, and seat usage without exposing any other tenant data.</p>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">
        <span class="status-badge <?= htmlspecialchars($status_badge_class) ?>"><?= htmlspecialchars($subscription['label']) ?></span>
        <span class="usage-chip"><i class="bi bi-people-fill"></i> <?= htmlspecialchars($usage_summary) ?></span>
      </div>
    </div>
    <div class="tenant-summary">
      <div class="tenant-summary-grid">
        <div class="summary-stat">
          <div class="summary-stat-label">Monthly Price</div>
          <div class="summary-stat-value"><?= htmlspecialchars($monthly_price) ?></div>
        </div>
        <div class="summary-stat">
          <div class="summary-stat-label">Current Plan</div>
          <div class="summary-stat-value"><?= htmlspecialchars($plan_name) ?></div>
        </div>
        <div class="summary-stat">
          <div class="summary-stat-label">Start Date</div>
          <div class="summary-stat-value"><?= htmlspecialchars($start_date) ?></div>
        </div>
        <div class="summary-stat">
          <div class="summary-stat-label">Expiry Date</div>
          <div class="summary-stat-value"><?= htmlspecialchars($expiry_date) ?></div>
        </div>
      </div>
      <p class="meta-copy">Tenant: <?= htmlspecialchars($tenant['display_name']) ?> | Workspace: <?= htmlspecialchars($tenant['subdomain']) ?></p>
    </div>
  </section>

  <div class="content-grid">
    <section class="panel">
      <h2>Features</h2>
      <p class="panel-copy">Included and excluded capabilities are derived from your tenant’s resolved plan definition only.</p>

      <div class="feature-grid">
        <div class="feature-group">
          <h3>Included Features</h3>
          <ul class="feature-list">
            <?php foreach ($included_features as $feature): ?>
              <li><?= htmlspecialchars($feature) ?></li>
            <?php endforeach; ?>
            <?php if (empty($included_features)): ?>
              <span class="muted-copy">No included features found.</span>
            <?php endif; ?>
          </ul>
        </div>

        <div class="feature-group">
          <h3>Excluded Features</h3>
          <ul class="feature-list excluded">
            <?php foreach ($excluded_features as $feature): ?>
              <li><?= htmlspecialchars($feature) ?></li>
            <?php endforeach; ?>
            <?php if (empty($excluded_features)): ?>
              <span class="muted-copy">No excluded features found.</span>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <div class="restriction-note">
        This page is read-only. `ADMIN` can review only the active tenant subscription and cannot assign plans, edit plan definitions, or view subscriptions for other tenants.
      </div>
    </section>

    <section class="panel">
      <h2>Usage and Role Limits</h2>
      <p class="panel-copy">Seat usage is calculated from active billable roles for your current tenant.</p>

      <div class="usage-stack">
        <div class="usage-card">
          <div class="summary-stat-label">Current User Usage</div>
          <div class="summary-stat-value"><?= number_format(intval($counts['total_users'] ?? 0)) ?></div>
          <p class="muted-copy"><?= htmlspecialchars($usage_summary) ?></p>
        </div>

        <div class="usage-card">
          <table class="role-limit-table">
            <thead>
              <tr>
                <th>Role</th>
                <th>Current</th>
                <th>Limit</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($role_limit_rows as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['label']) ?></td>
                  <td><?= number_format($row['current']) ?></td>
                  <td><?= htmlspecialchars($row['limit']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
