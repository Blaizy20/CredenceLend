<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_permission('view_dashboard');

$role = $_SESSION['role'] ?? '';
$global_dashboard_view = is_global_super_admin_view();
$dashboard_tenant_id = has_active_tenant_scope() ? require_current_tenant_id() : null;

$counts_sql = "SELECT
  SUM(status='PENDING') AS pending,
  SUM(status='DENIED') AS denied,
  SUM(status='PENDING') AS ci_queue,
  SUM(status='CI_REVIEWED') AS manager_queue,
  SUM(status='ACTIVE') AS approved,
  SUM(status='ACTIVE') AS active,
  SUM(status='OVERDUE') AS overdue,
  SUM(status='CLOSED') AS closed
FROM loans";
if (!$global_dashboard_view) {
  $counts_sql .= " WHERE tenant_id=?";
}
$counts = fetch_one(q($counts_sql, $global_dashboard_view ? "" : "i", $global_dashboard_view ? [] : [$dashboard_tenant_id]));

$total_tx = fetch_one(q(
  "SELECT IFNULL(SUM(amount),0) AS total FROM payments" . ($global_dashboard_view ? "" : " WHERE tenant_id=?"),
  $global_dashboard_view ? "" : "i",
  $global_dashboard_view ? [] : [$dashboard_tenant_id]
));

$total_customers = fetch_one(q(
  "SELECT COUNT(*) AS count FROM customers WHERE user_id IS NOT NULL" . ($global_dashboard_view ? "" : " AND tenant_id=?"),
  $global_dashboard_view ? "" : "i",
  $global_dashboard_view ? [] : [$dashboard_tenant_id]
));

$total_staff = fetch_one(q(
  "SELECT COUNT(*) AS count FROM users WHERE role <> 'CUSTOMER'" . ($global_dashboard_view ? "" : " AND tenant_id=?"),
  $global_dashboard_view ? "" : "i",
  $global_dashboard_view ? [] : [$dashboard_tenant_id]
));

$applicants = fetch_all(q(
  "SELECT l.reference_no, l.submitted_at, l.status, c.customer_no, CONCAT(c.first_name,' ',c.last_name) AS customer_name
   FROM loans l
   JOIN customers c ON c.customer_id=l.customer_id AND c.tenant_id=l.tenant_id
   WHERE " . tenant_condition('l.tenant_id') . "
   ORDER BY l.submitted_at DESC
   LIMIT 10",
  tenant_types(),
  tenant_params()
));

$staff = fetch_all(q(
  "SELECT full_name, role, created_at
   FROM users
   WHERE " . ($global_dashboard_view ? "role <> 'CUSTOMER'" : "role IN ('TENANT','MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER') AND tenant_id=?") . "
   ORDER BY
     CASE role
       WHEN 'SUPER_ADMIN' THEN 0
       WHEN 'ADMIN' THEN 1
       WHEN 'TENANT' THEN 1
       WHEN 'MANAGER' THEN 2
       WHEN 'CREDIT_INVESTIGATOR' THEN 3
       WHEN 'LOAN_OFFICER' THEN 4
       WHEN 'CASHIER' THEN 5
       ELSE 99
     END,
     created_at DESC",
  $global_dashboard_view ? "" : "i",
  $global_dashboard_view ? [] : [$dashboard_tenant_id]
));

$active_tenant_name = '';
if (current_tenant_id()) {
  $tenant_row = fetch_one(q(
    "SELECT COALESCE(display_name, tenant_name) AS tenant_name FROM tenants WHERE tenant_id=? LIMIT 1",
    "i",
    [current_tenant_id()]
  ));
  $active_tenant_name = $tenant_row['tenant_name'] ?? '';
}

$activity_overview = fetch_one(q(
  "SELECT
      COUNT(*) AS total_events,
      SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS events_today,
      COUNT(DISTINCT action) AS action_types
   FROM activity_logs
   WHERE " . ($global_dashboard_view ? "1=1" : "tenant_id=?"),
  $global_dashboard_view ? "" : "i",
  $global_dashboard_view ? [] : [$dashboard_tenant_id]
));

$recent_activity = fetch_all(q(
  "SELECT
      al.action,
      al.description,
      al.created_at,
      COALESCE(t.display_name, t.tenant_name) AS tenant_name
   FROM activity_logs al
   LEFT JOIN tenants t ON t.tenant_id = al.tenant_id
   WHERE " . ($global_dashboard_view ? "1=1" : "al.tenant_id=?") . "
   ORDER BY al.created_at DESC
   LIMIT 12",
  $global_dashboard_view ? "" : "i",
  $global_dashboard_view ? [] : [$dashboard_tenant_id]
));

$title = "Dashboard";
$active = "dash";
include __DIR__ . '/_layout_top.php';
$display_name = $_SESSION['full_name'] ?? 'User';
$role_label = str_replace('_', ' ', $_SESSION['role'] ?? '');
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

  .layout {
    background: transparent;
  }

  .sidebar {
    background: rgba(4, 10, 24, 0.84);
    border-right: 1px solid rgba(148, 163, 184, 0.12);
    backdrop-filter: blur(16px);
  }

  .sidebar h3 {
    color: #7f93b0;
  }

  .sidebar a {
    color: #d7e3f4;
  }

  .sidebar a.active,
  .sidebar a:hover {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.18), rgba(59, 130, 246, 0.2));
    color: #f8fbff;
  }

  .main {
    background: transparent;
  }

  .dashboard-shell { position: relative; display: grid; gap: 24px; color: #e5eefb; }
  .dashboard-shell::before, .dashboard-shell::after { content: ""; position: fixed; width: 420px; height: 420px; border-radius: 999px; filter: blur(90px); opacity: 0.18; pointer-events: none; z-index: 0; }
  .dashboard-shell::before { top: 92px; right: 4%; background: rgba(59, 130, 246, 0.55); }
  .dashboard-shell::after { bottom: 24px; left: 10%; background: rgba(14, 165, 233, 0.34); }
  .dashboard-shell > * { position: relative; z-index: 1; }
  .dashboard-hero { display: grid; gap: 18px; grid-template-columns: minmax(0, 1.8fr) minmax(280px, 1fr); padding: 30px; border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 28px; background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent 34%), radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.16), transparent 32%), linear-gradient(145deg, rgba(8, 15, 33, 0.96), rgba(15, 23, 42, 0.92)); box-shadow: 0 24px 60px rgba(2, 6, 23, 0.42); overflow: hidden; }
  .dashboard-hero-copy { display: grid; gap: 16px; align-content: start; }
  .dashboard-hero h1 { margin: 0; font-size: clamp(32px, 4vw, 52px); line-height: 1; letter-spacing: -0.04em; color: #f8fbff; }
  .dashboard-hero-sidebar { display: flex; align-items: stretch; }
  .dashboard-hero-stat-card { display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 12px; padding: 24px; border-radius: 20px; border: 1px solid rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.72); backdrop-filter: blur(12px); width: 100%; }
  .dashboard-hero-stat-label { color: #93a8c6; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; text-align: center; }
  .dashboard-hero-stat-value { color: #f8fbff; font-size: clamp(36px, 4vw, 48px); line-height: 1; font-weight: 800; letter-spacing: -0.02em; }
  .dashboard-hero p { margin: 0; max-width: 760px; color: #9fb0c9; font-size: 15px; line-height: 1.7; }
  .dashboard-kicker, .dashboard-pill { display: inline-flex; align-items: center; gap: 8px; width: fit-content; padding: 8px 12px; border-radius: 999px; border: 1px solid rgba(125, 211, 252, 0.24); background: rgba(14, 165, 233, 0.12); color: #d8f4ff; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
  .dashboard-hero-meta { display: flex; flex-wrap: wrap; gap: 10px; }
  .dashboard-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
  .dashboard-action-link { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 16px; border-radius: 999px; border: 1px solid rgba(148, 163, 184, 0.2); color: #e5eefb; background: rgba(15, 23, 42, 0.66); text-decoration: none; }
  .dashboard-action-link:hover { text-decoration: none; background: rgba(30, 41, 59, 0.9); }
  .dashboard-pill { padding: 10px 14px; font-size: 12px; letter-spacing: 0.04em; }
  .dashboard-highlight { display: grid; gap: 16px; align-content: space-between; padding: 22px; border-radius: 24px; border: 1px solid rgba(148, 163, 184, 0.14); background: rgba(15, 23, 42, 0.72); backdrop-filter: blur(12px); }
  .dashboard-highlight-label { color: #8ea3bf; font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase; }
  .dashboard-highlight-value { font-size: clamp(28px, 4vw, 44px); line-height: 1; font-weight: 800; color: #ffffff; }
  .dashboard-highlight-note { color: #9fb0c9; font-size: 13px; line-height: 1.6; }
  .dashboard-grid { display: grid; gap: 16px; grid-template-columns: repeat(12, minmax(0, 1fr)); }
  .dashboard-grid > * { grid-column: span 4; }
  .dashboard-grid.dashboard-grid-wide > * { grid-column: span 2; }
  .dashboard-grid.dashboard-grid-pipeline > * { grid-column: span 2; }
  .dashboard-card, .dashboard-panel, .dashboard-chart, .dashboard-table-card { border-radius: 22px; border: 1px solid rgba(148, 163, 184, 0.16); background: linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(8, 15, 30, 0.95)); box-shadow: 0 18px 44px rgba(2, 6, 23, 0.3); }
  .dashboard-card { padding: 20px; }
  .dashboard-card-label, .dashboard-section-label { margin: 0 0 8px; color: #93a8c6; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; }
  .dashboard-card-value { margin: 0; color: #f8fbff; font-size: clamp(28px, 3vw, 36px); line-height: 1; font-weight: 800; letter-spacing: -0.04em; }
  .dashboard-card-note { display: none; }
  .dashboard-overview-title { margin: 0 0 8px; color: #f8fbff; letter-spacing: -0.03em; }
  .dashboard-period-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
  .dashboard-period-toggle { display: flex; gap: 2px; background: rgba(15, 23, 42, 0.72); border-radius: 12px; padding: 4px; border: 1px solid rgba(148, 163, 184, 0.12); }
  .dashboard-period-btn { padding: 8px 16px; border: none; background: transparent; color: #8ea3bf; font-family: inherit; font-size: 12px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.04em; }
  .dashboard-period-btn:hover { color: #e5eefb; background: rgba(148, 163, 184, 0.08); }
  .dashboard-period-btn.active { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; }
  .dashboard-period-grid { display: grid; gap: 16px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .dashboard-period-card { padding: 20px; border-radius: 22px; border: 1px solid rgba(148, 163, 184, 0.16); background: linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(8, 15, 30, 0.95)); box-shadow: 0 18px 44px rgba(2, 6, 23, 0.3); }
  .dashboard-period-label { color: #8ea3bf; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 12px; }
  .dashboard-period-value { margin: 0; color: #f8fbff; font-size: clamp(28px, 3vw, 36px); line-height: 1; font-weight: 800; letter-spacing: -0.04em; }
  .dashboard-period-value.currency { font-size: clamp(22px, 2.5vw, 30px); color: #86efac; }
  .dashboard-revenue-progress { display: grid; gap: 16px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .dashboard-progress-card { padding: 20px; border-radius: 22px; border: 1px solid rgba(148, 163, 184, 0.16); background: linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(8, 15, 30, 0.95)); box-shadow: 0 18px 44px rgba(2, 6, 23, 0.3); }
  .dashboard-progress-label { color: #8ea3bf; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 8px; }
  .dashboard-progress-value { color: #86efac; font-size: clamp(18px, 2vw, 24px); font-weight: 700; margin-bottom: 12px; }
  .dashboard-progress-bar-container { background: rgba(15, 23, 42, 0.72); border-radius: 8px; height: 8px; overflow: hidden; margin-bottom: 10px; }
  .dashboard-progress-bar { height: 100%; border-radius: 8px; transition: width 0.5s ease; }
  .dashboard-progress-bar.success { background: linear-gradient(90deg, #22c55e, #86efac); }
  .dashboard-progress-bar.warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
  .dashboard-progress-bar.danger { background: linear-gradient(90deg, #ef4444, #fca5a5); }
  .dashboard-progress-bar.info { background: linear-gradient(90deg, #3b82f6, #7dd3fc); }
  .dashboard-progress-meta { color: #9fb0c9; font-size: 11px; }
  .dashboard-overview-grid { display: grid; gap: 16px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .dashboard-overview-card { padding: 20px; border-radius: 22px; border: 1px solid rgba(148, 163, 184, 0.16); background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.98)); box-shadow: 0 18px 44px rgba(2, 6, 23, 0.3); }
  .dashboard-overview-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
  .dashboard-overview-label { color: #93a8c6; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; }
  .dashboard-overview-icon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; font-size: 15px; font-weight: 700; }
  .dashboard-overview-icon.blue { color: #7dd3fc; background: rgba(14, 165, 233, 0.14); border: 1px solid rgba(125, 211, 252, 0.18); }
  .dashboard-overview-icon.green { color: #86efac; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(134, 239, 172, 0.18); }
  .dashboard-overview-icon.amber { color: #fbbf24; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(251, 191, 36, 0.18); }
  .dashboard-overview-icon.indigo { color: #a5b4fc; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(165, 180, 252, 0.18); }
  .dashboard-overview-value { margin: 0 0 14px; color: #f8fbff; font-size: clamp(28px, 3vw, 38px); line-height: 1; font-weight: 800; letter-spacing: -0.04em; }
  .dashboard-overview-value.currency { font-size: clamp(22px, 2.5vw, 30px); color: #86efac; }
  .dashboard-overview-subgrid { display: grid; gap: 8px; }
  .dashboard-overview-subgrid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .dashboard-overview-subgrid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .dashboard-overview-stat { padding: 10px 12px; border-radius: 14px; border: 1px solid rgba(148, 163, 184, 0.12); background: rgba(15, 23, 42, 0.72); min-width: 0; }
  .dashboard-overview-stat span { display: block; color: #8ea3bf; font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px; }
  .dashboard-overview-stat strong { display: block; color: #ffffff; font-size: 17px; line-height: 1.1; font-weight: 700; word-break: break-word; }
  .dashboard-overview-stat strong.success { color: #86efac; }
  .dashboard-overview-stat strong.warning { color: #fbbf24; }
  .dashboard-overview-stat strong.danger { color: #fca5a5; }
  .dashboard-section { display: grid; gap: 16px; }
  .dashboard-section-header { display: flex; justify-content: space-between; align-items: end; gap: 12px; }
  .dashboard-section-header h2, .dashboard-panel h3, .dashboard-chart h3, .dashboard-table-card h3 { margin: 0; color: #f8fbff; letter-spacing: -0.03em; }
  .dashboard-section-header p, .dashboard-panel-subtitle, .dashboard-table-card p { display: none; }
  .dashboard-split, .dashboard-table-grid { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .dashboard-panel { padding: 22px; }
  .dashboard-panel-grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-top: 16px; }
  .dashboard-panel-stat { padding: 14px 16px; border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.12); background: rgba(15, 23, 42, 0.72); }
  .dashboard-panel-stat span { display: block; color: #8ea3bf; font-size: 12px; margin-bottom: 8px; }
  .dashboard-panel-stat strong { display: block; color: #ffffff; font-size: 24px; line-height: 1.1; }
  .dashboard-chart-grid { display: grid; gap: 16px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .dashboard-chart-grid.dashboard-chart-grid-two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .dashboard-chart { padding: 22px; }
  .dashboard-chart.tall { min-height: 366px; }
  .dashboard-chart-canvas { position: relative; width: 100%; height: 300px; margin-top: 16px; }
  .dashboard-chart-canvas.short { height: 260px; }
  .dashboard-table-card { padding: 22px; overflow: hidden; }
  .dashboard-table-wrap { overflow-x: auto; margin-top: 16px; border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.12); }
  .dashboard-table-card .table { margin: 0; width: 100%; color: #e5eefb; background: transparent; }
  .dashboard-table-card .table thead th { background: rgba(15, 23, 42, 0.96); color: #93a8c6; text-transform: uppercase; letter-spacing: 0.08em; font-size: 11px; border-bottom: 1px solid rgba(148, 163, 184, 0.16); }
  .dashboard-table-card .table td, .dashboard-table-card .table th { border-color: rgba(148, 163, 184, 0.1); padding: 14px 16px; }
  .dashboard-table-card .table tbody tr:nth-child(odd) { background: rgba(15, 23, 42, 0.48); }
  .dashboard-table-card .small { color: #8ea3bf; }
  .dashboard-activity-list { display: grid; gap: 12px; margin-top: 16px; }
  .dashboard-activity-item { padding: 16px 18px; border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.12); background: rgba(15, 23, 42, 0.62); }
  .dashboard-activity-item strong { color: #f8fbff; display: block; }
  .dashboard-activity-meta { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; color: #8ea3bf; font-size: 12px; }
  @media (max-width: 1180px) {
    .dashboard-hero, .dashboard-split, .dashboard-table-grid, .dashboard-chart-grid, .dashboard-chart-grid.dashboard-chart-grid-two, .dashboard-overview-grid, .dashboard-period-grid { grid-template-columns: 1fr; }
    .dashboard-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .dashboard-grid > *, .dashboard-grid.dashboard-grid-wide > *, .dashboard-grid.dashboard-grid-pipeline > * { grid-column: span 3; }
    .dashboard-period-header { flex-direction: column; align-items: flex-start; gap: 12px; }
  }
  @media (max-width: 760px) {
    .dashboard-shell { gap: 18px; }
    .dashboard-hero, .dashboard-card, .dashboard-panel, .dashboard-chart, .dashboard-table-card { padding: 18px; border-radius: 18px; }
    .dashboard-grid, .dashboard-panel-grid { grid-template-columns: 1fr; }
    .dashboard-grid > *, .dashboard-grid.dashboard-grid-wide > *, .dashboard-grid.dashboard-grid-pipeline > * { grid-column: auto; }
    .dashboard-overview-card { padding: 18px; border-radius: 18px; }
    .dashboard-overview-subgrid.three, .dashboard-overview-subgrid.two { grid-template-columns: 1fr; }
    .dashboard-chart-canvas, .dashboard-chart-canvas.short { height: 240px; }
  }
</style>

<div class="dashboard-shell">
  <section class="dashboard-hero">
    <div class="dashboard-hero-copy">
      <h1>Dashboard</h1>
      <div class="dashboard-hero-actions">
        <a class="dashboard-action-link" href="<?php echo APP_BASE; ?>/staff/loans.php">Open Loans</a>
        <a class="dashboard-action-link" href="<?php echo APP_BASE; ?>/staff/history.php">View Activity</a>
        <?php if (can_access('view_role_permissions')): ?>
          <a class="dashboard-action-link" href="<?php echo APP_BASE; ?>/staff/role_permissions.php">Roles & Permissions</a>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!$global_dashboard_view): ?>
    <div class="dashboard-hero-sidebar">
      <div class="dashboard-hero-stat-card">
        <span class="dashboard-hero-stat-label">Total Staff</span>
        <span class="dashboard-hero-stat-value"><?= intval($total_staff['count'] ?? 0) ?></span>
      </div>
    </div>
    <?php endif; ?>
  </section>

<?php if ($global_dashboard_view): ?>
<?php
$admin_metrics = fetch_one(q("SELECT
  (SELECT COUNT(*) FROM tenants) AS total_tenants,
  (SELECT COUNT(*) FROM users) AS total_users,
  (SELECT COUNT(*) FROM users WHERE is_active=1) AS active_users,
  (SELECT COUNT(*) FROM users WHERE is_active=0) AS inactive_users,
  (SELECT COUNT(*) FROM customers WHERE is_active=1) AS total_customers,
  (SELECT COUNT(*) FROM loans) AS total_loans,
  (SELECT COUNT(*) FROM loans WHERE status='ACTIVE') AS active_loans,
  (SELECT COUNT(*) FROM loans WHERE status='OVERDUE') AS overdue_loans,
  (SELECT IFNULL(SUM(amount), 0) FROM payments) AS total_revenue,
  (SELECT IFNULL(SUM(principal_amount), 0) FROM loans WHERE status IN ('ACTIVE','OVERDUE')) AS portfolio_value,
  (SELECT COUNT(*) FROM loans WHERE status IN ('PENDING','CI_REVIEWED')) AS pending_approvals
"));

// Revenue targets for progress bars
$revenue_targets = [
  'daily' => 50000,
  'weekly' => 250000,
  'monthly' => 1000000
];

$activity_snapshot = fetch_one(q("SELECT
  (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) AS users_today,
  (SELECT COUNT(*) FROM loans WHERE DATE(submitted_at) = CURDATE()) AS loans_today,
  (SELECT IFNULL(SUM(amount), 0) FROM payments WHERE payment_date = CURDATE()) AS revenue_today,
  (SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()) AS events_today,
  (SELECT COUNT(*) FROM users WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)) AS users_week,
  (SELECT COUNT(*) FROM loans WHERE YEARWEEK(submitted_at, 1) = YEARWEEK(CURDATE(), 1)) AS loans_week,
  (SELECT IFNULL(SUM(amount), 0) FROM payments WHERE YEARWEEK(payment_date, 1) = YEARWEEK(CURDATE(), 1)) AS revenue_week,
  (SELECT COUNT(*) FROM activity_logs WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)) AS events_week,
  (SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS users_month,
  (SELECT COUNT(*) FROM loans WHERE DATE_FORMAT(submitted_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS loans_month,
  (SELECT IFNULL(SUM(amount), 0) FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS revenue_month,
  (SELECT COUNT(*) FROM activity_logs WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS events_month
"));
?>
  <section class="dashboard-section">
    <h3 class="dashboard-overview-title">Overview</h3>
    <div class="dashboard-overview-grid">
      <article class="dashboard-overview-card">
        <div class="dashboard-overview-top">
          <div class="dashboard-overview-label">Tenants &amp; Users</div>
          <div class="dashboard-overview-icon blue">T</div>
        </div>
        <p class="dashboard-overview-value"><?= intval($admin_metrics['total_tenants'] ?? 0) ?></p>
        <div class="dashboard-overview-subgrid three">
          <div class="dashboard-overview-stat"><span>Users</span><strong><?= intval($admin_metrics['total_users'] ?? 0) ?></strong></div>
          <div class="dashboard-overview-stat"><span>Active</span><strong class="success"><?= intval($admin_metrics['active_users'] ?? 0) ?></strong></div>
          <div class="dashboard-overview-stat"><span>Inactive</span><strong><?= intval($admin_metrics['inactive_users'] ?? 0) ?></strong></div>
        </div>
      </article>

      <article class="dashboard-overview-card">
        <div class="dashboard-overview-top">
          <div class="dashboard-overview-label">Loans</div>
          <div class="dashboard-overview-icon green">L</div>
        </div>
        <p class="dashboard-overview-value"><?= intval($admin_metrics['total_loans'] ?? 0) ?></p>
        <div class="dashboard-overview-subgrid three">
          <div class="dashboard-overview-stat"><span>Active</span><strong class="success"><?= intval($admin_metrics['active_loans'] ?? 0) ?></strong></div>
          <div class="dashboard-overview-stat"><span>Overdue</span><strong class="danger"><?= intval($admin_metrics['overdue_loans'] ?? 0) ?></strong></div>
          <div class="dashboard-overview-stat"><span>Customers</span><strong><?= intval($admin_metrics['total_customers'] ?? 0) ?></strong></div>
        </div>
      </article>

      <article class="dashboard-overview-card">
        <div class="dashboard-overview-top">
          <div class="dashboard-overview-label">Portfolio Value</div>
          <div class="dashboard-overview-icon amber">P</div>
        </div>
        <p class="dashboard-overview-value currency">PHP <?= number_format((float)($admin_metrics['total_revenue'] ?? 0), 2) ?></p>
        <div class="dashboard-overview-subgrid two">
          <div class="dashboard-overview-stat"><span>Revenue Today</span><strong>PHP <?= number_format((float)($activity_snapshot['revenue_today'] ?? 0), 2) ?></strong></div>
          <div class="dashboard-overview-stat"><span>Pending</span><strong class="warning"><?= intval($admin_metrics['pending_approvals'] ?? 0) ?></strong></div>
        </div>
      </article>

      <article class="dashboard-overview-card">
        <div class="dashboard-overview-top">
          <div class="dashboard-overview-label">System Activity</div>
          <div class="dashboard-overview-icon indigo">A</div>
        </div>
        <p class="dashboard-overview-value"><?= intval($activity_overview['events_today'] ?? 0) ?></p>
        <div class="dashboard-overview-subgrid two">
          <div class="dashboard-overview-stat"><span>Event Types</span><strong><?= intval($activity_overview['action_types'] ?? 0) ?></strong></div>
          <div class="dashboard-overview-stat"><span>Total Logged</span><strong><?= intval($activity_overview['total_events'] ?? 0) ?></strong></div>
        </div>
      </article>
    </div>
  </section>

  <div class="dashboard-period-header">
    <h3 class="dashboard-overview-title">Period Breakdown</h3>
    <div class="dashboard-period-toggle">
      <button class="dashboard-period-btn active" data-period="today">Today</button>
      <button class="dashboard-period-btn" data-period="week">This Week</button>
      <button class="dashboard-period-btn" data-period="month">This Month</button>
    </div>
  </div>
  
  <section class="dashboard-section">
    <div class="dashboard-period-grid">
      <article class="dashboard-period-card">
        <div class="dashboard-period-label">New Users</div>
        <div class="dashboard-period-value" id="period-users"><?= number_format(intval($activity_snapshot['users_today'] ?? 0)) ?></div>
      </article>
      
      <article class="dashboard-period-card">
        <div class="dashboard-period-label">Loan Applications</div>
        <div class="dashboard-period-value" id="period-loans"><?= number_format(intval($activity_snapshot['loans_today'] ?? 0)) ?></div>
      </article>
      
      <article class="dashboard-period-card">
        <div class="dashboard-period-label">Revenue</div>
        <div class="dashboard-period-value currency" id="period-revenue">₱<?= number_format((float)($activity_snapshot['revenue_today'] ?? 0), 0) ?></div>
      </article>
      
      <article class="dashboard-period-card">
        <div class="dashboard-period-label">Logged Events</div>
        <div class="dashboard-period-value" id="period-events"><?= number_format(intval($activity_snapshot['events_today'] ?? 0)) ?></div>
      </article>
    </div>
  </section>

  <section class="dashboard-section">
    <h3 class="dashboard-overview-title">Revenue Trend</h3>
    <div class="dashboard-chart-grid">
      <article class="dashboard-chart">
        <h3>Daily Revenue</h3>
        <div class="dashboard-chart-canvas short"><canvas id="chart-sales-daily" width="400" height="260"></canvas></div>
      </article>
      <article class="dashboard-chart">
        <h3>Weekly Revenue</h3>
        <div class="dashboard-chart-canvas short"><canvas id="chart-sales-weekly" width="400" height="260"></canvas></div>
      </article>
      <article class="dashboard-chart">
        <h3>Monthly Revenue</h3>
        <div class="dashboard-chart-canvas short"><canvas id="chart-sales-monthly" width="400" height="260"></canvas></div>
      </article>
    </div>
  </section>

  <section class="dashboard-chart-grid dashboard-chart-grid-two">
    <article class="dashboard-chart">
      <h3>User Growth</h3>
      <div class="dashboard-chart-canvas"><canvas id="chart-user-growth" width="400" height="300"></canvas></div>
    </article>
    <article class="dashboard-chart">
      <h3>Loan Status Distribution</h3>
      <div class="dashboard-chart-canvas"><canvas id="chart-loan-status" width="400" height="300"></canvas></div>
    </article>
  </section>

  <section class="dashboard-section">
    <article class="dashboard-chart tall">
      <h3>Tenant Activity</h3>
      <div class="dashboard-chart-canvas"><canvas id="chart-tenant-activity" width="400" height="320"></canvas></div>
    </article>
  </section>

  <section class="dashboard-chart-grid">
    <article class="dashboard-chart">
      <h3>Staff by Role</h3>
      <div class="dashboard-chart-canvas"><canvas id="chart-staff-role" width="400" height="300"></canvas></div>
    </article>
    <article class="dashboard-chart">
      <h3>Daily Activity</h3>
      <div class="dashboard-chart-canvas"><canvas id="chart-daily-activity" width="400" height="300"></canvas></div>
    </article>
    <article class="dashboard-chart">
      <h3>Loan Applications</h3>
      <div class="dashboard-chart-canvas"><canvas id="chart-applications" width="400" height="300"></canvas></div>
    </article>
  </section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
const analyticsUrl = <?= json_encode(url_for('/api/v1/analytics.php')) ?>;
const chartInstances = {};
const palette = ['#1d4ed8', '#0f766e', '#b45309', '#7c3aed', '#dc2626', '#0891b2', '#4f46e5'];

const currencyTick = (value) => 'PHP ' + Number(value).toLocaleString();

const chartConfigs = [
  {
    endpoint: 'sales_trends_daily',
    chartId: 'chart-sales-daily',
    type: 'line',
    label: 'Daily Revenue',
    borderColor: palette[0],
    backgroundColor: 'rgba(29, 78, 216, 0.12)',
    fill: true,
    tension: 0.24,
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: currencyTick
          }
        }
      }
    }
  },
  {
    endpoint: 'sales_trends_weekly',
    chartId: 'chart-sales-weekly',
    type: 'bar',
    label: 'Weekly Revenue',
    backgroundColor: 'rgba(15, 118, 110, 0.7)',
    borderColor: palette[1],
    borderWidth: 1,
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: currencyTick
          }
        }
      }
    }
  },
  {
    endpoint: 'sales_trends_monthly',
    chartId: 'chart-sales-monthly',
    type: 'line',
    label: 'Monthly Revenue',
    borderColor: palette[4],
    backgroundColor: 'rgba(220, 38, 38, 0.12)',
    fill: true,
    tension: 0.28,
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: currencyTick
          }
        }
      }
    }
  },
  {
    endpoint: 'user_growth',
    chartId: 'chart-user-growth',
    type: 'line',
    label: 'New Users',
    borderColor: palette[0],
    backgroundColor: 'rgba(29, 78, 216, 0.16)',
    fill: true,
    tension: 0.32
  },
  {
    endpoint: 'loan_status_distribution',
    chartId: 'chart-loan-status',
    type: 'doughnut'
  },
  {
    endpoint: 'staff_by_role',
    chartId: 'chart-staff-role',
    type: 'bar',
    label: 'Staff Members',
    backgroundColor: 'rgba(124, 58, 237, 0.72)',
    borderColor: palette[3],
    borderWidth: 1,
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  },
  {
    endpoint: 'tenant_activity',
    chartId: 'chart-tenant-activity',
    type: 'bar',
    useApiDatasets: true,
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  },
  {
    endpoint: 'daily_activity',
    chartId: 'chart-daily-activity',
    type: 'bar',
    label: 'Logged Activities',
    backgroundColor: 'rgba(180, 83, 9, 0.72)',
    borderColor: palette[2],
    borderWidth: 1,
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  },
  {
    endpoint: 'loan_applications_monthly',
    chartId: 'chart-applications',
    type: 'line',
    label: 'Applications',
    borderColor: palette[5],
    backgroundColor: 'rgba(8, 145, 178, 0.16)',
    fill: true,
    tension: 0.28
  }
];

function buildDatasets(config, payload) {
  if (config.useApiDatasets && Array.isArray(payload.datasets) && payload.datasets.length > 0) {
    return payload.datasets;
  }

  if (config.type === 'doughnut') {
    return [{
      data: Array.isArray(payload.data) ? payload.data : [],
      backgroundColor: palette,
      borderWidth: 1
    }];
  }

  return [{
    label: config.label,
    data: Array.isArray(payload.data) ? payload.data : [],
    borderColor: config.borderColor || palette[0],
    backgroundColor: config.backgroundColor || 'rgba(29, 78, 216, 0.2)',
    borderWidth: config.borderWidth || 2,
    fill: !!config.fill,
    tension: config.tension ?? 0.25
  }];
}

function buildOptions(config) {
  const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false
    },
    plugins: {
      legend: {
        display: true
      }
    }
  };

  if (config.type !== 'doughnut') {
    options.scales = {
      y: {
        beginAtZero: true
      }
    };
  }

  if (!config.options) {
    return options;
  }

  return {
    ...options,
    ...config.options,
    plugins: {
      ...options.plugins,
      ...(config.options.plugins || {})
    },
    scales: {
      ...(options.scales || {}),
      ...(config.options.scales || {})
    }
  };
}

async function loadChart(config) {
  const canvas = document.getElementById(config.chartId);
  if (!canvas) {
    return;
  }

  try {
    const response = await fetch(`${analyticsUrl}?endpoint=${encodeURIComponent(config.endpoint)}`, {
      credentials: 'same-origin'
    });
    const payload = await response.json();

    if (!response.ok) {
      throw new Error(payload.error || 'Request failed');
    }

    if (chartInstances[config.chartId]) {
      chartInstances[config.chartId].destroy();
    }

    chartInstances[config.chartId] = new Chart(canvas.getContext('2d'), {
      type: config.type,
      data: {
        labels: Array.isArray(payload.labels) ? payload.labels : [],
        datasets: buildDatasets(config, payload)
      },
      options: buildOptions(config)
    });
  } catch (error) {
    console.error(`Failed to load ${config.endpoint}`, error);
  }
}

chartConfigs.forEach(loadChart);

// Period Breakdown Toggle Functionality
const periodData = {
  today: {
    users: <?= intval($activity_snapshot['users_today'] ?? 0) ?>,
    loans: <?= intval($activity_snapshot['loans_today'] ?? 0) ?>,
    revenue: <?= (float)($activity_snapshot['revenue_today'] ?? 0) ?>,
    events: <?= intval($activity_snapshot['events_today'] ?? 0) ?>
  },
  week: {
    users: <?= intval($activity_snapshot['users_week'] ?? 0) ?>,
    loans: <?= intval($activity_snapshot['loans_week'] ?? 0) ?>,
    revenue: <?= (float)($activity_snapshot['revenue_week'] ?? 0) ?>,
    events: <?= intval($activity_snapshot['events_week'] ?? 0) ?>
  },
  month: {
    users: <?= intval($activity_snapshot['users_month'] ?? 0) ?>,
    loans: <?= intval($activity_snapshot['loans_month'] ?? 0) ?>,
    revenue: <?= (float)($activity_snapshot['revenue_month'] ?? 0) ?>,
    events: <?= intval($activity_snapshot['events_month'] ?? 0) ?>
  }
};

function formatNumber(num) {
  return new Intl.NumberFormat().format(num);
}

function formatCurrency(num) {
  return '₱' + new Intl.NumberFormat().format(Math.round(num));
}

function updatePeriodValues(period) {
  const data = periodData[period];
  if (!data) return;

  document.getElementById('period-users').textContent = formatNumber(data.users);
  document.getElementById('period-loans').textContent = formatNumber(data.loans);
  document.getElementById('period-revenue').textContent = formatCurrency(data.revenue);
  document.getElementById('period-events').textContent = formatNumber(data.events);
}

document.querySelectorAll('.dashboard-period-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    // Remove active class from all buttons
    document.querySelectorAll('.dashboard-period-btn').forEach(b => b.classList.remove('active'));
    
    // Add active class to clicked button
    btn.classList.add('active');
    
    // Update the period values
    const period = btn.dataset.period;
    updatePeriodValues(period);
  });
});

// Revenue Trend Toggle Functionality - REMOVED

</script>

<?php else: ?>
<?php
$role_metric_cards = [];
if (in_array($role, ['CASHIER', 'CREDIT_INVESTIGATOR', 'MANAGER', 'TENANT'], true)) {
  $role_metric_cards[] = [
    'label' => 'Total Transactions',
    'value' => 'PHP ' . number_format((float)($total_tx['total'] ?? 0), 2),
    'note' => 'Collections processed in your scope',
  ];
}
if (in_array($role, ['MANAGER', 'LOAN_OFFICER', 'CASHIER', 'CREDIT_INVESTIGATOR', 'TENANT'], true)) {
  $role_metric_cards[] = [
    'label' => 'Total Customers',
    'value' => intval($total_customers['count'] ?? 0),
    'note' => 'Customers linked to active accounts',
  ];
}

$pipeline_cards = [
  ['label' => 'Pending', 'value' => intval($counts['pending'] ?? 0), 'note' => 'Applications awaiting review'],
  ['label' => 'CI Review Queue', 'value' => intval($counts['ci_queue'] ?? 0), 'note' => 'Queued for investigation'],
  ['label' => 'Manager Approval', 'value' => intval($counts['manager_queue'] ?? 0), 'note' => 'Waiting for final approval'],
  ['label' => 'Approved', 'value' => intval($counts['approved'] ?? 0), 'note' => 'Approved and active loans'],
  ['label' => 'Overdue', 'value' => intval($counts['overdue'] ?? 0), 'note' => 'Require collection follow-up'],
  ['label' => 'Closed', 'value' => intval($counts['closed'] ?? 0), 'note' => 'Loans fully settled'],
];
?>
  <?php if (count($role_metric_cards) > 0): ?>
  <section class="dashboard-section">
    <div class="dashboard-grid">
      <?php foreach ($role_metric_cards as $metric): ?>
        <article class="dashboard-card">
          <div class="dashboard-card-label"><?= htmlspecialchars($metric['label']) ?></div>
          <p class="dashboard-card-value"><?= htmlspecialchars((string)$metric['value']) ?></p>
          <div class="dashboard-card-note"><?= htmlspecialchars($metric['note']) ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

<?php endif; ?>

<?php if ($global_dashboard_view): ?>
<?php else: ?>
  <section class="dashboard-section">
    <div class="dashboard-grid dashboard-grid-pipeline">
      <?php foreach ($pipeline_cards as $metric): ?>
        <article class="dashboard-card">
          <div class="dashboard-card-label"><?= htmlspecialchars($metric['label']) ?></div>
          <p class="dashboard-card-value"><?= htmlspecialchars((string)$metric['value']) ?></p>
          <div class="dashboard-card-note"><?= htmlspecialchars($metric['note']) ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="dashboard-chart-grid dashboard-chart-grid-two">
    <article class="dashboard-chart">
      <h3>Loan Status Distribution</h3>
      <div class="dashboard-chart-canvas"><canvas id="tenant-chart-loan-status" width="400" height="300"></canvas></div>
    </article>
    <article class="dashboard-chart">
      <h3>Application Trend</h3>
      <div class="dashboard-chart-canvas"><canvas id="tenant-chart-applications" width="400" height="300"></canvas></div>
    </article>
  </section>

  <section class="dashboard-chart-grid">
    <article class="dashboard-chart">
      <h3>Daily Activity</h3>
      <div class="dashboard-chart-canvas"><canvas id="tenant-chart-daily-activity" width="400" height="300"></canvas></div>
    </article>
    <article class="dashboard-chart">
      <h3>Staff Growth</h3>
      <div class="dashboard-chart-canvas"><canvas id="tenant-chart-user-growth" width="400" height="300"></canvas></div>
    </article>
    <article class="dashboard-chart">
      <h3>Staff by Role</h3>
      <div class="dashboard-chart-canvas"><canvas id="tenant-chart-staff-role" width="400" height="300"></canvas></div>
    </article>
  </section>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
  const tenantAnalyticsUrl = <?= json_encode(url_for('/api/v1/analytics.php')) ?>;
  const tenantPalette = ['#1d4ed8', '#0f766e', '#b45309', '#7c3aed', '#0891b2'];
  const tenantChartInstances = {};

  function tenantChartOptions(config) {
    const base = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: true } },
      interaction: { mode: 'index', intersect: false }
    };

    if (config.type !== 'doughnut') {
      base.scales = { y: { beginAtZero: true } };
    }

    if (!config.options) {
      return base;
    }

    return {
      ...base,
      ...config.options,
      plugins: { ...base.plugins, ...(config.options.plugins || {}) },
      scales: { ...(base.scales || {}), ...(config.options.scales || {}) }
    };
  }

  function tenantChartDatasets(config, payload) {
    if (config.type === 'doughnut') {
      return [{
        data: Array.isArray(payload.data) ? payload.data : [],
        backgroundColor: tenantPalette,
        borderWidth: 1
      }];
    }

    return [{
      label: config.label,
      data: Array.isArray(payload.data) ? payload.data : [],
      borderColor: config.borderColor || tenantPalette[0],
      backgroundColor: config.backgroundColor || 'rgba(29, 78, 216, 0.16)',
      borderWidth: config.borderWidth || 2,
      fill: !!config.fill,
      tension: config.tension ?? 0.28
    }];
  }

  async function loadTenantChart(config) {
    const canvas = document.getElementById(config.chartId);
    if (!canvas) {
      return;
    }

    try {
      const response = await fetch(`${tenantAnalyticsUrl}?endpoint=${encodeURIComponent(config.endpoint)}`, { credentials: 'same-origin' });
      const payload = await response.json();
      if (!response.ok) {
        throw new Error(payload.error || 'Request failed');
      }

      if (tenantChartInstances[config.chartId]) {
        tenantChartInstances[config.chartId].destroy();
      }

      tenantChartInstances[config.chartId] = new Chart(canvas.getContext('2d'), {
        type: config.type,
        data: {
          labels: Array.isArray(payload.labels) ? payload.labels : [],
          datasets: tenantChartDatasets(config, payload)
        },
        options: tenantChartOptions(config)
      });
    } catch (error) {
      console.error('Tenant chart load failed', config.endpoint, error);
    }
  }

  [
    { endpoint: 'loan_status_distribution', chartId: 'tenant-chart-loan-status', type: 'doughnut' },
    { endpoint: 'loan_applications_monthly', chartId: 'tenant-chart-applications', type: 'line', label: 'Applications', borderColor: tenantPalette[4], backgroundColor: 'rgba(8, 145, 178, 0.16)', fill: true },
    { endpoint: 'daily_activity', chartId: 'tenant-chart-daily-activity', type: 'bar', label: 'Activities', backgroundColor: 'rgba(180, 83, 9, 0.72)', borderColor: tenantPalette[2], borderWidth: 1, options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } },
    { endpoint: 'user_growth', chartId: 'tenant-chart-user-growth', type: 'line', label: 'Users', borderColor: tenantPalette[0], backgroundColor: 'rgba(29, 78, 216, 0.16)', fill: true },
    { endpoint: 'staff_by_role', chartId: 'tenant-chart-staff-role', type: 'bar', label: 'Staff Members', backgroundColor: 'rgba(124, 58, 237, 0.72)', borderColor: tenantPalette[3], borderWidth: 1, options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } }
  ].forEach(loadTenantChart);
  </script>

<?php endif; ?>

  <section class="dashboard-table-grid">
    <article class="dashboard-table-card">
      <h3>Recent client applications</h3>
      <div class="dashboard-table-wrap">
        <table class="table">
          <thead><tr><th>Reference</th><th>Customer</th><th>Status</th><th>Submitted</th></tr></thead>
          <tbody>
          <?php foreach ($applicants as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['reference_no']) ?></td>
              <td><?= htmlspecialchars($a['customer_name']) ?> <span class="small">(<?= htmlspecialchars($a['customer_no']) ?>)</span></td>
              <td><span class="badge <?= status_badge_class($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></span></td>
              <td><?= htmlspecialchars($a['submitted_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($applicants)): ?><tr><td colspan="4" class="small">No applications yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>

    <article class="dashboard-table-card">
      <h3><?= $global_dashboard_view ? 'Staff & Admin ranking' : 'Staff directory' ?></h3>
      <div class="dashboard-table-wrap">
        <table class="table">
          <thead><tr><th>Name</th><th>Role</th></tr></thead>
          <tbody>
          <?php foreach ($staff as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['full_name']) ?></td>
              <td><?= htmlspecialchars(str_replace('_', ' ', $s['role'])) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($staff)): ?><tr><td colspan="2" class="small">No staff.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>

  <section class="dashboard-section">
    <article class="dashboard-table-card">
      <h3>Recent Activity</h3>
      <div class="dashboard-activity-list">
        <?php foreach ($recent_activity as $activity): ?>
          <div class="dashboard-activity-item">
            <strong><?= htmlspecialchars(str_replace('_', ' ', $activity['action'])) ?></strong>
            <div class="small" style="margin-top:8px"><?= htmlspecialchars($activity['description']) ?></div>
            <div class="dashboard-activity-meta">
              <span><?= htmlspecialchars($activity['created_at']) ?></span>
              <?php if (!empty($activity['tenant_name']) && $global_dashboard_view): ?><span><?= htmlspecialchars($activity['tenant_name']) ?></span><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($recent_activity)): ?><div class="dashboard-activity-item"><div class="small">No activity recorded yet.</div></div><?php endif; ?>
      </div>
    </article>
  </section>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
