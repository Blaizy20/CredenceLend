<?php
require_once __DIR__ . '/../includes/auth.php';
$user = current_user();
$settings = get_system_settings();
$active_tenant_name = '';
$brand_title = $settings['system_name'] ?? 'CredenceLend';
$active_scope_label = 'Active Tenant';
$owned_tenant_count = is_admin_owner() ? count(user_owned_tenants($_SESSION['user_id'] ?? 0, true)) : 0;
$can_switch_tenant = is_super_admin() || $owned_tenant_count > 1;
$can_view_settings = can_access('view_settings');
$profile_name = $user['full_name'] ?? ($_SESSION['full_name'] ?? '');
$profile_role = get_role_display_name($_SESSION['role'] ?? '');
$profile_initial = strtoupper(substr(trim($profile_name), 0, 1));
$primary_color = $settings['primary_color'] ?? app_default_primary_color();
$can_view_loans = can_access('view_loans');
$can_view_customers = can_access('view_customers');
$can_view_payments = can_access('view_payments');
$can_manage_vouchers = can_access('manage_vouchers');
$can_review_applications = can_access('review_applications');
$can_approve_applications = can_access('approve_applications');
$can_view_reports = can_access('view_reports');
$can_use_reports_feature = current_tenant_has_feature('reports');
$can_view_staff = can_access('view_staff');
$can_manage_staff = can_access('manage_staff');
$can_manage_tenants = can_access('manage_tenants');
$can_view_subscription = can_access('view_subscription');
$can_manage_subscriptions = can_access('manage_subscriptions');
$can_view_role_permissions = can_access('view_role_permissions');
$can_manage_backups = can_access('manage_backups');
$can_view_sales = can_access('view_sales');
$can_view_history = can_access('view_history');
$can_use_audit_logs_feature = current_tenant_has_feature('audit_logs');
$show_reports_menu = $can_view_reports && $can_use_reports_feature;
$show_sales_menu = $can_view_sales && $can_use_reports_feature;
$show_history_menu = $can_view_history && $can_use_audit_logs_feature;

$show_main_section = true;
$show_operations_section = $can_review_applications || $can_approve_applications || $show_reports_menu;
$show_admin_section = $can_view_staff || $can_manage_staff || $can_manage_tenants || $can_view_subscription || $can_manage_subscriptions || $can_view_role_permissions || $can_manage_backups;
$show_reports_section = $show_sales_menu || $show_history_menu || $can_view_settings;

if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $primary_color)) {
  $primary_color = app_default_primary_color();
}

function theme_hex_to_rgb($hex) {
  $hex = ltrim($hex, '#');
  return [
    hexdec(substr($hex, 0, 2)),
    hexdec(substr($hex, 2, 2)),
    hexdec(substr($hex, 4, 2)),
  ];
}

function theme_adjust_hex($hex, $factor) {
  [$red, $green, $blue] = theme_hex_to_rgb($hex);
  $channels = [$red, $green, $blue];

  foreach ($channels as &$channel) {
    if ($factor >= 0) {
      $channel = (int) round($channel + ((255 - $channel) * $factor));
    } else {
      $channel = (int) round($channel * (1 + $factor));
    }
    $channel = max(0, min(255, $channel));
  }
  unset($channel);

  return sprintf('#%02x%02x%02x', $channels[0], $channels[1], $channels[2]);
}

[$primary_red, $primary_green, $primary_blue] = theme_hex_to_rgb($primary_color);
$primary_hover = theme_adjust_hex($primary_color, -0.18);
$primary_deep = theme_adjust_hex($primary_color, -0.5);
$primary_mid = theme_adjust_hex($primary_color, -0.28);
$primary_soft = "rgba({$primary_red}, {$primary_green}, {$primary_blue}, 0.08)";
$primary_soft_strong = "rgba({$primary_red}, {$primary_green}, {$primary_blue}, 0.14)";

if (is_global_super_admin_view()) {
  $active_tenant_name = $brand_title;
  $active_scope_label = 'System Workspace';
} elseif (current_tenant_id()) {
  $active_tenant = fetch_one(q(
    "SELECT COALESCE(display_name, tenant_name) AS tenant_name FROM tenants WHERE tenant_id=? LIMIT 1",
    "i",
    [current_tenant_id()]
  ));
  $active_tenant_name = $active_tenant['tenant_name'] ?? '';
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars($title ?? 'Loan Management') ?></title>
  <link rel="stylesheet" href="<?php echo APP_BASE; ?>/assets/css/theme.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <style>
    :root {
      --brand-primary: <?= htmlspecialchars($primary_color, ENT_QUOTES) ?>;
      --brand-primary-hover: <?= htmlspecialchars($primary_hover, ENT_QUOTES) ?>;
      --brand-primary-deep: <?= htmlspecialchars($primary_deep, ENT_QUOTES) ?>;
      --brand-primary-mid: <?= htmlspecialchars($primary_mid, ENT_QUOTES) ?>;
      --brand-topbar-start: <?= htmlspecialchars($primary_deep, ENT_QUOTES) ?>;
      --brand-topbar-end: <?= htmlspecialchars($primary_mid, ENT_QUOTES) ?>;
      --brand-primary-rgb: <?= intval($primary_red) ?>, <?= intval($primary_green) ?>, <?= intval($primary_blue) ?>;
      --brand-primary-soft: <?= htmlspecialchars($primary_soft, ENT_QUOTES) ?>;
      --brand-primary-soft-strong: <?= htmlspecialchars($primary_soft_strong, ENT_QUOTES) ?>;
      --brand-red: var(--brand-primary);
      --brand-red-hover: var(--brand-primary-hover);
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sidebar a .bi {
      width: 18px;
      text-align: center;
      font-size: 15px;
      flex: 0 0 18px;
    }
  </style>
</head>
<body>
<div class="topbar">
  <div class="brand">
    <img src="<?php echo htmlspecialchars($settings['logo_path'] ?? APP_BASE . '/assets/img/new-logo.jfif'); ?>" alt="Logo"/>
    <div>
      <div style="font-weight:800;line-height:1"><?= htmlspecialchars($brand_title) ?></div>
      <div class="small" style="color:#fde8ec"><?php 
        $role = $_SESSION['role'] ?? '';
        $roleNames = [
          'SUPER_ADMIN' => 'Super Admin Portal',
          'ADMIN' => 'Admin Portal',
          'MANAGER' => 'Manager Portal',
          'CREDIT_INVESTIGATOR' => 'Credit Investigator Portal',
          'LOAN_OFFICER' => 'Loan Officer Portal',
          'CASHIER' => 'Cashier Portal'
        ];
        echo htmlspecialchars($roleNames[$role] ?? 'Staff Portal');
      ?></div>
    </div>
  </div>
  <div class="topbar-actions">
    <?php if ($active_tenant_name): ?>
      <span class="small topbar-tenant-label" style="color:#fde8ec"><?= htmlspecialchars($active_tenant_name) ?></span>
    <?php endif; ?>
    <button
      type="button"
      class="profile-trigger"
      id="profileMenuTrigger"
      aria-haspopup="dialog"
      aria-expanded="false"
      aria-controls="profileMenuModal"
    >
      <i class="bi bi-person-circle"></i>
      <span class="profile-trigger-label">Profile</span>
    </button>
  </div>
</div>
<div class="profile-modal-backdrop" id="profileMenuBackdrop" hidden></div>
<div
  class="profile-modal"
  id="profileMenuModal"
  role="dialog"
  aria-modal="true"
  aria-labelledby="profileMenuTitle"
  hidden
>
  <div class="profile-modal-header">
    <div class="profile-modal-identity">
      <div class="profile-modal-avatar"><?= htmlspecialchars($profile_initial ?: 'U') ?></div>
      <div class="profile-modal-copy">
        <div class="profile-modal-kicker">Signed In As</div>
        <h3 id="profileMenuTitle"><?= htmlspecialchars($profile_name) ?></h3>
        <div class="profile-modal-role"><?= htmlspecialchars($profile_role) ?></div>
      </div>
    </div>
    <button type="button" class="profile-modal-close" id="profileMenuClose" aria-label="Close profile menu">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  <div class="profile-modal-panel">
    <?php if ($active_tenant_name): ?>
      <div class="profile-modal-tenant-block">
        <div class="profile-modal-label"><?= htmlspecialchars($active_scope_label) ?></div>
        <div class="profile-modal-tenant"><?= htmlspecialchars($active_tenant_name) ?></div>
      </div>
    <?php endif; ?>
    <div class="profile-modal-status">
      <span class="profile-status-dot"></span>
      <span>Session active</span>
    </div>
  </div>
  <div class="profile-modal-section-title">Quick Actions</div>
  <div class="profile-modal-actions">
    <a class="profile-modal-link" href="<?php echo APP_BASE; ?>/staff/account_settings.php">
      <span class="profile-modal-link-icon"><i class="bi bi-person-gear"></i></span>
      <span class="profile-modal-link-copy">
        <span class="profile-modal-link-title">My Account</span>
        <span class="profile-modal-link-subtitle">Edit username, contact info, and password</span>
      </span>
    </a>
    <?php if ($can_switch_tenant): ?>
      <a
        class="profile-modal-link"
        href="<?php echo APP_BASE; ?>/staff/select_tenant.php"
        data-profile-nav="<?php echo APP_BASE; ?>/staff/select_tenant.php"
      >
        <span class="profile-modal-link-icon"><i class="bi bi-arrow-repeat"></i></span>
        <span class="profile-modal-link-copy">
          <span class="profile-modal-link-title">Switch Tenant</span>
          <span class="profile-modal-link-subtitle">Change your active workspace</span>
        </span>
      </a>
    <?php endif; ?>
    <?php if ($can_view_settings): ?>
      <a class="profile-modal-link" href="<?php echo APP_BASE; ?>/staff/settings.php">
        <span class="profile-modal-link-icon"><i class="bi bi-gear"></i></span>
        <span class="profile-modal-link-copy">
          <span class="profile-modal-link-title">Settings</span>
          <span class="profile-modal-link-subtitle">Manage portal configuration</span>
        </span>
      </a>
    <?php endif; ?>
    <a class="profile-modal-link logout" href="<?php echo APP_BASE; ?>/staff/logout.php">
      <span class="profile-modal-link-icon"><i class="bi bi-box-arrow-right"></i></span>
      <span class="profile-modal-link-copy">
        <span class="profile-modal-link-title">Logout</span>
        <span class="profile-modal-link-subtitle">End this session securely</span>
      </span>
    </a>
  </div>
</div>
<div class="layout">
  <div class="sidebar">
    <?php if ($show_main_section): ?>
      <div class="nav-section">
        <div class="nav-section-label">Main</div>
        <a href="<?php echo APP_BASE; ?>/staff/dashboard.php" class="<?= ($active??'')==='dash'?'active':''?>"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
        
        <?php if ($can_view_loans): ?>
          <a href="<?php echo APP_BASE; ?>/staff/loans.php" class="<?= ($active??'')==='loans'?'active':''?>"><i class="bi bi-cash-stack"></i><span>Loans</span></a>
        <?php endif; ?>
        <?php if ($can_view_customers): ?>
          <a href="<?php echo APP_BASE; ?>/staff/customers.php" class="<?= ($active??'')==='cust'?'active':''?>"><i class="bi bi-people-fill"></i><span>Customers</span></a>
        <?php endif; ?>
        
        <?php if ($can_view_payments): ?>
          <a href="<?php echo APP_BASE; ?>/staff/payments.php" class="<?= ($active??'')==='pay'?'active':''?>"><i class="bi bi-credit-card-2-front-fill"></i><span>Payments</span></a>
        <?php endif; ?>
        <?php if ($can_manage_vouchers): ?>
          <a href="<?php echo APP_BASE; ?>/staff/release_queue.php" class="<?= ($active??'')==='release_queue'?'active':''?>"><i class="bi bi-wallet2"></i><span>Money Release</span></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($show_operations_section): ?>
      <div class="nav-section">
        <div class="nav-section-label">Operations</div>
        <?php if ($can_review_applications): ?>
          <a href="<?php echo APP_BASE; ?>/staff/ci_queue.php" class="<?= ($active??'')==='ci'?'active':''?>"><i class="bi bi-search"></i><span>CI Review</span></a>
        <?php endif; ?>
        <?php if ($can_approve_applications): ?>
          <a href="<?php echo APP_BASE; ?>/staff/manager_queue.php" class="<?= ($active??'')==='mgr'?'active':''?>"><i class="bi bi-check2-square"></i><span>Manager Approval</span></a>
        <?php endif; ?>
        <?php if ($show_reports_menu): ?>
          <a href="<?php echo APP_BASE; ?>/staff/reports.php" class="<?= ($active??'')==='rep'?'active':''?>"><i class="bi bi-bar-chart-line-fill"></i><span>Reports</span></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($show_admin_section): ?>
      <div class="nav-section">
        <div class="nav-section-label">Admin</div>
        <?php if ($can_view_staff): ?>
          <a href="<?php echo APP_BASE; ?>/staff/staff.php" class="<?= ($active??'')==='staff'?'active':''?>"><i class="bi bi-person-badge-fill"></i><span>Staff</span></a>
        <?php endif; ?>
        <?php if ($can_manage_staff): ?>
          <a href="<?php echo APP_BASE; ?>/staff/registration.php" class="<?= ($active??'')==='reg'?'active':''?>"><i class="bi bi-person-plus-fill"></i><span>Register Staff</span></a>
          <a href="<?php echo APP_BASE; ?>/staff/staff_settings.php" class="<?= ($active??'')==='staff_settings'?'active':''?>"><i class="bi bi-person-gear"></i><span>Staff Settings</span></a>
        <?php endif; ?>
        <?php if ($can_manage_tenants): ?>
          <a href="<?php echo APP_BASE; ?>/staff/tenant_management.php" class="<?= ($active??'')==='tenants'?'active':''?>"><i class="bi bi-buildings-fill"></i><span>Tenant Management</span></a>
        <?php endif; ?>
        <?php if ($can_manage_subscriptions): ?>
          <a href="<?php echo APP_BASE; ?>/staff/subscriptions.php" class="<?= ($active??'')==='subscriptions'?'active':''?>"><i class="bi bi-stars"></i><span>Subscription Management</span></a>
        <?php endif; ?>
        <?php if ($can_view_subscription): ?>
          <?php if (is_admin_owner()): ?>
            <a href="<?php echo APP_BASE; ?>/staff/my_subscription.php" class="<?= in_array(($active??''), ['my_subscription', 'subscription'], true) ? 'active' : '' ?>"><i class="bi bi-stars"></i><span>My Subscription</span></a>
          <?php else: ?>
            <a href="<?php echo APP_BASE; ?>/staff/subscription.php" class="<?= ($active??'')==='subscription'?'active':''?>"><i class="bi bi-stars"></i><span>Subscription</span></a>
          <?php endif; ?>
        <?php endif; ?>
        <?php if ($can_view_role_permissions): ?>
          <a href="<?php echo APP_BASE; ?>/staff/role_permissions.php" class="<?= ($active??'')==='roles'?'active':''?>"><i class="bi bi-shield-lock-fill"></i><span>Roles & Permissions</span></a>
        <?php endif; ?>
        <?php if ($can_manage_backups): ?>
          <a href="<?php echo APP_BASE; ?>/staff/backup_settings.php" class="<?= ($active??'')==='backups'?'active':''?>"><i class="bi bi-database-fill-gear"></i><span>Backup Settings</span></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($show_reports_section): ?>
      <div class="nav-section">
        <div class="nav-section-label">Reports</div>
        <?php if ($show_sales_menu): ?>
          <a href="<?php echo APP_BASE; ?>/staff/sales_report.php" class="<?= ($active??'')==='sales'?'active':''?>"><i class="bi bi-graph-up-arrow"></i><span>Sales Report</span></a>
        <?php endif; ?>
        <?php if ($show_history_menu): ?>
          <a href="<?php echo APP_BASE; ?>/staff/history.php" class="<?= ($active??'')==='history'?'active':''?>"><i class="bi bi-clock-history"></i><span>History</span></a>
        <?php endif; ?>
        <?php if ($can_view_settings): ?>
          <a href="<?php echo APP_BASE; ?>/staff/settings.php" class="<?= ($active??'')==='settings'?'active':''?>"><i class="bi bi-gear-fill"></i><span>Settings</span></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <div class="main">
