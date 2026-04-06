<?php
require_once __DIR__ . '/../includes/auth.php';

require_login();
require_permission('view_settings');

if (!current_tenant_id() && !is_global_super_admin_view()) {
    $_SESSION['return_url'] = APP_BASE . '/staff/settings.php';
    header('Location: ' . APP_BASE . '/staff/select_tenant.php');
    exit;
}

require __DIR__ . '/manager_settings.php';
