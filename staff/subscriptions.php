<?php
require_once __DIR__ . '/../includes/auth.php';

require_login();
require_permission('manage_subscriptions');

$title = "Subscription Management";
$active = "subscriptions";
$err = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

$schema_ready = subscription_schema_ready();
$plan_options = subscription_available_plan_options(false);
$status_options = subscription_manageable_status_options();
$status_labels = subscription_status_labels();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_subscription'])) {
  $tenant_id = intval($_POST['tenant_id'] ?? 0);
  $plan_code = $_POST['plan_code'] ?? '';
  $subscription_status = $_POST['subscription_status'] ?? '';

  try {
    $updated = update_tenant_subscription_settings($tenant_id, $plan_code, $subscription_status);
    $ok = 'Subscription updated for ' . ($updated['tenant_name'] ?? 'tenant') . '.';
  } catch (RuntimeException $e) {
    $err = $e->getMessage();
  } catch (Exception $e) {
    $err = 'Failed to update subscription: ' . $e->getMessage();
  }
}

$user_count_subquery = "
  SELECT scoped_users.tenant_id, COUNT(DISTINCT scoped_users.user_id) AS current_user_count
  FROM (
    SELECT ta.tenant_id, u.user_id
    FROM tenant_admins ta
    JOIN users u ON u.user_id = ta.user_id
    WHERE u.is_active = 1
      AND u.role = 'ADMIN'

    UNION

    SELECT u.tenant_id, u.user_id
    FROM users u
    WHERE u.tenant_id IS NOT NULL
      AND u.is_active = 1
      AND u.role IN ('ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER')
  ) AS scoped_users
  GROUP BY scoped_users.tenant_id
";

if ($schema_ready) {
  $tenants = fetch_all(q(
    "SELECT
       t.tenant_id,
       t.tenant_name,
       COALESCE(t.display_name, t.tenant_name) AS display_name,
       t.subdomain,
       t.tenant_status,
       t.plan_code,
       t.subscription_status,
       t.subscription_started_at,
       t.subscription_expires_at,
       t.created_at,
       COALESCE(p.name, '') AS plan_name,
       COALESCE(uc.current_user_count, 0) AS current_user_count
     FROM tenants t
     LEFT JOIN plans p ON p.plan_id = t.plan_id
     LEFT JOIN ({$user_count_subquery}) uc ON uc.tenant_id = t.tenant_id
     ORDER BY
       FIELD(t.tenant_status, 'PENDING', 'ACTIVE', 'INACTIVE', 'SUSPENDED', 'REJECTED'),
       COALESCE(t.display_name, t.tenant_name) ASC"
  ));
} else {
  $tenants = fetch_all(q(
    "SELECT
       t.tenant_id,
       t.tenant_name,
       COALESCE(t.display_name, t.tenant_name) AS display_name,
       t.subdomain,
       t.tenant_status,
       t.plan_code,
       NULL AS subscription_status,
       NULL AS subscription_started_at,
       NULL AS subscription_expires_at,
       t.created_at,
       '' AS plan_name,
       COALESCE(uc.current_user_count, 0) AS current_user_count
     FROM tenants t
     LEFT JOIN ({$user_count_subquery}) uc ON uc.tenant_id = t.tenant_id
     ORDER BY
       FIELD(t.tenant_status, 'PENDING', 'ACTIVE', 'INACTIVE', 'SUSPENDED', 'REJECTED'),
       COALESCE(t.display_name, t.tenant_name) ASC"
  ));
}

$format_datetime = static function ($value) {
  $value = trim((string) $value);
  if ($value === '' || $value === '0000-00-00 00:00:00') {
    return 'Not set';
  }

  $timestamp = strtotime($value);
  if (!$timestamp) {
    return 'Not set';
  }

  return date('M d, Y', $timestamp);
};

$stats = [
  'total_tenants' => count($tenants),
  'active_subscriptions' => 0,
  'attention_needed' => 0,
  'billable_users' => 0,
];

foreach ($tenants as &$tenant) {
  $tenant['plan_code'] = normalize_subscription_plan_code($tenant['plan_code'] ?? 'BASIC');
  $tenant['plan_label'] = trim((string) ($plan_options[$tenant['plan_code']]['name'] ?? $tenant['plan_name'] ?? $tenant['plan_code']));
  $tenant['stored_status'] = strtoupper(trim((string) ($tenant['subscription_status'] ?? '')));
  $tenant['tenant_status_label'] = ucwords(strtolower(str_replace('_', ' ', $tenant['tenant_status'] ?? '')));
  $tenant['current_user_count'] = intval($tenant['current_user_count'] ?? 0);
  $tenant['start_date_label'] = $format_datetime($tenant['subscription_started_at'] ?? null);
  $tenant['expiry_date_label'] = $format_datetime($tenant['subscription_expires_at'] ?? null);

  if ($schema_ready) {
    $resolved_status = get_tenant_subscription_status($tenant['tenant_id']);
    $tenant['status_key'] = $resolved_status['status'];
    $tenant['status_label'] = $resolved_status['label'];
  } else {
    $tenant['status_key'] = 'MISSING';
    $tenant['status_label'] = $status_labels['MISSING'];
  }

  if (in_array($tenant['status_key'], ['ACTIVE', 'TRIAL'], true)) {
    $stats['active_subscriptions']++;
  }
  if (in_array($tenant['status_key'], ['PAST_DUE', 'SUSPENDED', 'CANCELLED', 'EXPIRED', 'MISSING'], true)) {
    $stats['attention_needed']++;
  }
  $stats['billable_users'] += $tenant['current_user_count'];
}
unset($tenant);

$badge_class = static function ($status_key) {
  $status_key = strtoupper(trim((string) $status_key));
  if (in_array($status_key, ['ACTIVE', 'TRIAL'], true)) {
    return 'good';
  }
  if (in_array($status_key, ['PAST_DUE', 'PENDING'], true)) {
    return 'warn';
  }
  if (in_array($status_key, ['SUSPENDED', 'CANCELLED', 'EXPIRED', 'MISSING'], true)) {
    return 'bad';
  }

  return 'neutral';
};

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

  .subscription-admin-shell {
    display: grid;
    gap: 24px;
  }

  .subscription-admin-hero,
  .subscription-admin-card {
    border-radius: 26px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
      radial-gradient(circle at top left, rgba(56, 189, 248, 0.14), transparent 30%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.95));
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
  }

  .subscription-admin-hero {
    padding: 30px;
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(280px, 1fr);
    gap: 20px;
    align-items: start;
  }

  .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(125, 211, 252, 0.24);
    background: rgba(14, 165, 233, 0.12);
    color: #d8f4ff;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .subscription-admin-hero h1 {
    margin: 14px 0 8px;
    font-size: clamp(30px, 4vw, 48px);
    line-height: 0.98;
    letter-spacing: -0.05em;
    color: #f8fbff;
  }

  .subscription-admin-hero p,
  .tenant-meta,
  .schema-note,
  .table-note {
    margin: 0;
    color: #9fb0c9;
    font-size: 14px;
    line-height: 1.7;
  }

  .hero-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }

  .stat-card {
    padding: 18px;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: rgba(15, 23, 42, 0.78);
  }

  .stat-label {
    color: #7f93b0;
    font-size: 11px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .stat-value {
    margin-top: 10px;
    color: #f8fbff;
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
  }

  .subscription-admin-card {
    padding: 24px;
  }

  .flash-message {
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid transparent;
    font-size: 14px;
  }

  .flash-message.ok {
    background: rgba(16, 185, 129, 0.12);
    border-color: rgba(52, 211, 153, 0.24);
    color: #b7f7dd;
  }

  .flash-message.error {
    background: rgba(239, 68, 68, 0.12);
    border-color: rgba(248, 113, 113, 0.24);
    color: #fecaca;
  }

  .schema-warning {
    margin-top: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid rgba(250, 204, 21, 0.22);
    background: rgba(250, 204, 21, 0.1);
    color: #fde68a;
  }

  .table-wrap {
    overflow-x: auto;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th,
  td {
    padding: 16px 14px;
    vertical-align: top;
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    text-align: left;
  }

  th {
    color: #8ea5c4;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  td {
    color: #e5eefb;
    font-size: 14px;
  }

  .tenant-name {
    display: grid;
    gap: 4px;
  }

  .tenant-name strong {
    color: #f8fbff;
    font-size: 15px;
  }

  .tenant-meta {
    font-size: 12px;
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

  .count-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 48px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(59, 130, 246, 0.14);
    color: #dbeafe;
    font-weight: 700;
  }

  .inline-form {
    display: grid;
    gap: 10px;
    min-width: 220px;
  }

  .inline-form label {
    display: grid;
    gap: 6px;
    color: #9fb0c9;
    font-size: 12px;
    font-weight: 600;
  }

  .inline-form select {
    width: 100%;
    padding: 11px 12px;
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(2, 6, 23, 0.62);
    color: #f8fbff;
  }

  .inline-form button {
    justify-self: start;
    padding: 11px 14px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: #f8fbff;
    font-weight: 700;
    cursor: pointer;
  }

  .inline-form button:disabled,
  .inline-form select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .empty-state {
    padding: 32px 14px;
    text-align: center;
    color: #9fb0c9;
  }

  @media (max-width: 1100px) {
    .subscription-admin-hero {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 720px) {
    .hero-stats {
      grid-template-columns: 1fr;
    }

    .subscription-admin-card,
    .subscription-admin-hero {
      padding: 20px;
    }
  }
</style>

<div class="subscription-admin-shell">
  <section class="subscription-admin-hero">
    <div>
      <span class="eyebrow"><i class="bi bi-stars"></i> Super Admin Only</span>
      <h1>Tenant subscription control across the full portfolio.</h1>
      <p>Review every tenant’s plan, stored subscription state, effective status, lifecycle dates, and billable seat usage from one place.</p>
      <?php if (!$schema_ready): ?>
        <div class="schema-warning">
          <strong>Subscription schema not ready.</strong>
          <div class="schema-note">Run the subscription schema and seed SQL before using update actions on this page.</div>
        </div>
      <?php endif; ?>
    </div>
    <div class="hero-stats">
      <div class="stat-card">
        <div class="stat-label">Total Tenants</div>
        <div class="stat-value"><?= number_format($stats['total_tenants']) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Active Or Trial</div>
        <div class="stat-value"><?= number_format($stats['active_subscriptions']) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Needs Attention</div>
        <div class="stat-value"><?= number_format($stats['attention_needed']) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Billable Users</div>
        <div class="stat-value"><?= number_format($stats['billable_users']) ?></div>
      </div>
    </div>
  </section>

  <?php if ($ok !== ''): ?>
    <div class="flash-message ok"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>

  <?php if ($err !== ''): ?>
    <div class="flash-message error"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <section class="subscription-admin-card">
    <div style="display:grid;gap:6px;margin-bottom:18px">
      <h2 style="margin:0;color:#f8fbff;font-size:22px">Tenant subscriptions</h2>
      <p class="table-note">Displayed status is the effective subscription state. Stored status remains editable per tenant below.</p>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Tenant Name</th>
            <th>Current Plan</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>Expiry Date</th>
            <th>Current User Count</th>
            <th>Update</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tenants as $tenant): ?>
            <tr>
              <td>
                <div class="tenant-name">
                  <strong><?= htmlspecialchars($tenant['display_name']) ?></strong>
                  <div class="tenant-meta"><?= htmlspecialchars($tenant['subdomain']) ?> | Tenant workflow: <?= htmlspecialchars($tenant['tenant_status_label']) ?></div>
                </div>
              </td>
              <td><?= htmlspecialchars($tenant['plan_label']) ?></td>
              <td>
                <span class="status-badge <?= htmlspecialchars($badge_class($tenant['status_key'])) ?>">
                  <?= htmlspecialchars($tenant['status_label']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($tenant['start_date_label']) ?></td>
              <td><?= htmlspecialchars($tenant['expiry_date_label']) ?></td>
              <td><span class="count-pill"><?= number_format($tenant['current_user_count']) ?></span></td>
              <td>
                <form method="post" class="inline-form">
                  <?= csrf_field() ?>
                  <input type="hidden" name="tenant_id" value="<?= intval($tenant['tenant_id']) ?>">
                  <label>
                    Plan
                    <select name="plan_code" <?= !$schema_ready ? 'disabled' : '' ?>>
                      <?php foreach ($plan_options as $plan_code => $plan): ?>
                        <option value="<?= htmlspecialchars($plan_code) ?>" <?= $tenant['plan_code'] === $plan_code ? 'selected' : '' ?>>
                          <?= htmlspecialchars($plan['name'] ?? $plan_code) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                  <label>
                    Stored Status
                    <select name="subscription_status" <?= !$schema_ready ? 'disabled' : '' ?>>
                      <?php foreach ($status_options as $status_code => $status_label): ?>
                        <option value="<?= htmlspecialchars($status_code) ?>" <?= $tenant['stored_status'] === $status_code ? 'selected' : '' ?>>
                          <?= htmlspecialchars($status_label) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                  <button type="submit" name="update_subscription" value="1" <?= !$schema_ready ? 'disabled' : '' ?>>Save</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($tenants)): ?>
            <tr>
              <td colspan="7" class="empty-state">No tenants found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
