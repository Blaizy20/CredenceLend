<?php
/**
 * Refactored Authentication & Authorization for Staff-Only Web Portal
 * 
 * This file handles staff authentication. Customer login is NO LONGER available
 * on the web portal. All customer-related functionality has been moved to mobile app.
 * 
 * The database still contains customer/user data for future mobile API integration.
 * This system is modular and ready for API gateway integration.
 */

require_once __DIR__ . '/pdo_db.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/db.php'; // Keep for backward compatibility with existing code
require_once __DIR__ . '/subscription_helpers.php';

// Only staff/web portal sessions are managed here
// Session name for web staff portal
if (!isset($_SESSION)) {
    session_name('LOAN_STAFF_SESSION');
    session_start();
}

// Auto-detect app base URL
if (!defined('APP_BASE')) {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    // Strip if running in subfolder
    $dir = preg_replace('#/(staff|setup)$#', '', $dir);
    if ($dir === '' || $dir === '.') $dir = '';
    define('APP_BASE', $dir);
}

function app_base() { 
    return APP_BASE; 
}

function app_default_primary_color() {
    return '#0f1b35';
}

function app_primary_color($value = null) {
    $color = trim((string) ($value ?? ''));
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        return app_default_primary_color();
    }

    return strtolower($color);
}

function url_for($path = '') {
    if ($path === '' || $path === null) return APP_BASE;
    if ($path[0] !== '/') $path = '/' . $path;
    return rtrim(APP_BASE, '/') . $path;
}

function auth_role_permissions() {
    static $permissions = null;
    if ($permissions !== null) {
        return $permissions;
    }

    $permissions = [
        'view_dashboard' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER'],
        'view_loans' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER'],
        'view_loan_details' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER'],
        'view_customers' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER'],
        'manage_customers' => ['SUPER_ADMIN', 'ADMIN'],
        'view_payments' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CASHIER'],
        'record_payments' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CASHIER'],
        'edit_payments' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CASHIER'],
        'print_receipts' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CASHIER'],
        'manage_vouchers' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'LOAN_OFFICER', 'CASHIER'],
        'review_applications' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR'],
        'approve_applications' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER'],
        'update_loan_terms' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER'],
        'assign_loan_officer' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER'],
        'view_reports' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER'],
        'view_advanced_reports' => ['SUPER_ADMIN', 'ADMIN'],
        'view_staff' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER'],
        'manage_staff' => ['SUPER_ADMIN', 'ADMIN'],
        'view_history' => ['SUPER_ADMIN', 'ADMIN'],
        'manage_tenants' => ['SUPER_ADMIN'],
        'view_subscription' => ['SUPER_ADMIN', 'ADMIN'],
        'manage_subscriptions' => ['SUPER_ADMIN'],
        'view_role_permissions' => ['SUPER_ADMIN'],
        'manage_backups' => ['SUPER_ADMIN'],
        'view_sales' => ['SUPER_ADMIN', 'ADMIN'],
        'view_settings' => ['SUPER_ADMIN', 'ADMIN', 'MANAGER'],
    ];

    return $permissions;
}

function current_role() {
    return $_SESSION['role'] ?? '';
}

function normalize_tenant_id($tenant_id) {
    $tenant_id = intval($tenant_id ?? 0);
    return $tenant_id > 0 ? $tenant_id : null;
}

function is_super_admin() {
    return current_role() === 'SUPER_ADMIN';
}

function is_admin_owner() {
    return current_role() === 'ADMIN';
}

function has_role($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return in_array(current_role(), $roles, true);
}

function can_access($permission) {
    $permissions = auth_role_permissions();
    if (!isset($permissions[$permission])) {
        return false;
    }
    return has_role($permissions[$permission]);
}

function require_permission($permission) {
    require_login();

    if (!can_access($permission)) {
        http_response_code(403);
        echo "<h2>403 Forbidden</h2>";
        echo "<p>You do not have permission to access this resource.</p>";
        echo "<p><a href='" . APP_BASE . "/staff/dashboard.php'>Return to Dashboard</a></p>";
        exit;
    }
}

/**
 * Check if user is logged in
 */
function is_logged_in() { 
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']); 
}

function is_password_setup_required() {
    return is_logged_in() && !empty($_SESSION['password_setup_required']);
}

function current_password_setup_token() {
    return trim((string) ($_SESSION['password_setup_token'] ?? ''));
}

function password_setup_url($token = null) {
    $query = [];
    $token = trim((string) ($token ?? current_password_setup_token()));
    if ($token !== '') {
        $query['token'] = $token;
    }

    $url = APP_BASE . '/staff/set_password.php';
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    return $url;
}

function begin_password_setup_session($user, $token) {
    login_user($user);
    $_SESSION['password_setup_required'] = 1;
    $_SESSION['password_setup_token'] = trim((string) $token);
}

function clear_password_setup_state() {
    unset($_SESSION['password_setup_required'], $_SESSION['password_setup_token']);
}

/**
 * Get current logged-in user
 */
function current_user() {
    if (!is_logged_in()) return null;
    
    try {
        $authService = new AuthService();
        $user = $authService->getUserById($_SESSION['user_id']);
        return $user;
    } catch (Exception $e) {
        error_log("Current user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Require staff login (redirects to staff login if not logged in)
 */
function require_login() {
    if (!is_logged_in()) {
        // Store return URL for redirect after login
        $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . APP_BASE . "/staff/login.php");
        exit;
    }

    if (is_password_setup_required() && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'set_password.php') {
        header("Location: " . password_setup_url());
        exit;
    }

    try {
        ensure_active_tenant_session();
    } catch (RuntimeException $e) {
        error_log("Tenant session enforcement error: " . $e->getMessage());
        logout_user();
        header("Location: " . APP_BASE . "/staff/login.php?error=invalid_tenant");
        exit;
    }

    enforce_current_subscription_access();
}

/**
 * Require customer login (alias for require_login for customer pages)
 * Note: Customer login functionality is maintained for backward compatibility
 */
function require_login_customer() {
    require_login();
}

/**
 * Require specific roles
 * @param array $roles Array of allowed roles (e.g., ['ADMIN', 'MANAGER'])
 */
function require_roles($roles) {
    require_login();
    
    $userRole = $_SESSION['role'] ?? '';
    if (!in_array($userRole, $roles, true)) {
        http_response_code(403);
        echo "<h2>403 Forbidden</h2>";
        echo "<p>You do not have permission to access this resource.</p>";
        echo "<p><a href='" . APP_BASE . "/staff/dashboard.php'>Return to Dashboard</a></p>";
        exit;
    }
}

/**
 * Require admin role specifically
 */
function require_admin() {
    require_roles(['SUPER_ADMIN', 'ADMIN']);
}

/**
 * Require manager or admin
 */
function require_manager() {
    require_roles(['SUPER_ADMIN', 'ADMIN', 'MANAGER']);
}

/**
 * Require credit investigator or higher
 */
function require_credit_investigator() {
    require_roles(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR']);
}

/**
 * Login a staff user (sets session variables)
 */
function login_user($user) {
    $base_tenant_id = normalize_tenant_id($user['tenant_id'] ?? null);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['contact_no'] = $user['contact_no'] ?? '';
    $_SESSION['base_tenant_id'] = $base_tenant_id;
    $_SESSION['tenant_id'] = $base_tenant_id;
    $_SESSION['current_active_tenant_id'] = $base_tenant_id;
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time();

    $_SESSION['is_system_admin'] = ($user['role'] === 'SUPER_ADMIN');

    foreach (array_keys($_SESSION) as $session_key) {
        if (strpos($session_key, 'system_settings') === 0) {
            unset($_SESSION[$session_key]);
        }
    }
}

/**
 * Check if current user is a system admin (can access all tenants)
 */
function is_system_admin() {
    return isset($_SESSION['is_system_admin']) && $_SESSION['is_system_admin'];
}

function current_active_tenant_id() {
    return normalize_tenant_id($_SESSION['current_active_tenant_id'] ?? $_SESSION['tenant_id'] ?? null);
}

function current_tenant_id() {
    return current_active_tenant_id();
}

function has_active_tenant_scope() {
    return normalize_tenant_id(current_active_tenant_id()) !== null;
}

function is_global_super_admin_view() {
    return is_super_admin() && !has_active_tenant_scope();
}

function user_owned_tenants($user_id = null, $active_only = true) {
    $user_id = intval($user_id ?? ($_SESSION['user_id'] ?? 0));
    if ($user_id <= 0) {
        return [];
    }

    $sql = "SELECT
              t.tenant_id,
              t.tenant_name,
              COALESCE(t.display_name, t.tenant_name) AS display_name,
              t.subdomain,
              t.is_active,
              ta.is_primary_owner
            FROM tenant_admins ta
            JOIN tenants t ON t.tenant_id = ta.tenant_id
            JOIN users u ON u.user_id = ta.user_id
            WHERE ta.user_id = ? AND u.role = 'ADMIN'";

    if ($active_only) {
        $sql .= " AND t.is_active = 1";
    }

    $sql .= " ORDER BY ta.is_primary_owner DESC, COALESCE(t.display_name, t.tenant_name) ASC";

    try {
        return fetch_all(q($sql, "i", [$user_id])) ?: [];
    } catch (Exception $e) {
        error_log("Owned tenants lookup error: " . $e->getMessage());
        return [];
    }
}

function user_owns_tenant($user_id, $tenant_id, $active_only = true) {
    $user_id = intval($user_id ?? 0);
    $tenant_id = intval($tenant_id ?? 0);
    if ($user_id <= 0 || $tenant_id <= 0) {
        return false;
    }

    $sql = "SELECT 1
            FROM tenant_admins ta
            JOIN tenants t ON t.tenant_id = ta.tenant_id
            JOIN users u ON u.user_id = ta.user_id
            WHERE ta.user_id = ? AND ta.tenant_id = ? AND u.role = 'ADMIN'";

    if ($active_only) {
        $sql .= " AND t.is_active = 1";
    }

    return (bool)fetch_one(q($sql, "ii", [$user_id, $tenant_id]));
}

function set_current_active_tenant_id($tenant_id) {
    $tenant_id = intval($tenant_id ?? 0);
    if ($tenant_id <= 0) {
        unset($_SESSION['tenant_id'], $_SESSION['current_active_tenant_id']);
        clear_system_settings_cache();
        return;
    }

    $tenant = fetch_one(q(
        "SELECT tenant_id FROM tenants WHERE tenant_id=? AND is_active=1",
        "i",
        [$tenant_id]
    ));
    if (!$tenant) {
        throw new RuntimeException('The selected tenant is unavailable.');
    }

    if (is_admin_owner() && !user_owns_tenant($_SESSION['user_id'] ?? 0, $tenant_id, true)) {
        throw new RuntimeException('You do not own the selected tenant.');
    }

    if (!is_super_admin() && !is_admin_owner()) {
        $base_tenant_id = normalize_tenant_id($_SESSION['base_tenant_id'] ?? null);
        if ($base_tenant_id === null || $base_tenant_id !== $tenant_id) {
            throw new RuntimeException('Invalid tenant selection.');
        }
    }

    $_SESSION['tenant_id'] = $tenant_id;
    $_SESSION['current_active_tenant_id'] = $tenant_id;
    clear_system_settings_cache();
}

function clear_current_active_tenant_id() {
    unset($_SESSION['tenant_id'], $_SESSION['current_active_tenant_id']);
    clear_system_settings_cache();
}

function apply_super_admin_scope_from_request($param = 'tenant_id') {
    if (!is_super_admin() || !array_key_exists($param, $_GET)) {
        return false;
    }

    $requested_tenant_id = intval($_GET[$param] ?? 0);
    $current_tenant_id = intval(current_tenant_id() ?? 0);
    if ($requested_tenant_id === $current_tenant_id) {
        return false;
    }

    set_current_active_tenant_id($requested_tenant_id);
    return true;
}

function requires_tenant_selection() {
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === 'select_tenant.php';
}

function ensure_active_tenant_session() {
    if (!is_logged_in()) {
        return false;
    }

    $user = current_user();
    if (!$user || !intval($user['is_active'] ?? 0)) {
        throw new RuntimeException('Account is unavailable.');
    }

    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['contact_no'] = $user['contact_no'] ?? '';
    $_SESSION['base_tenant_id'] = normalize_tenant_id($user['tenant_id'] ?? null);
    $_SESSION['is_system_admin'] = ($user['role'] === 'SUPER_ADMIN');

    if (is_super_admin()) {
        return true;
    }

    if (is_admin_owner()) {
        $owned_tenants = user_owned_tenants($user['user_id'], true);
        if (empty($owned_tenants)) {
            throw new RuntimeException('No active owned tenants are assigned to this admin.');
        }

        $active_tenant_id = current_active_tenant_id();
        if (count($owned_tenants) === 1) {
            $only_tenant_id = intval($owned_tenants[0]['tenant_id']);
            if ($active_tenant_id !== $only_tenant_id) {
                set_current_active_tenant_id($only_tenant_id);
            }
            return true;
        }

        if ($active_tenant_id && user_owns_tenant($user['user_id'], $active_tenant_id, true)) {
            return true;
        }

        clear_current_active_tenant_id();
        if (!requires_tenant_selection()) {
            header("Location: " . APP_BASE . "/staff/select_tenant.php");
            exit;
        }
        return false;
    }

    $tenant_id = normalize_tenant_id($user['tenant_id'] ?? null);
    if ($tenant_id === null) {
        throw new RuntimeException('Missing tenant context.');
    }

    $tenant = fetch_one(q(
        "SELECT tenant_id FROM tenants WHERE tenant_id=? AND is_active=1",
        "i",
        [$tenant_id]
    ));

    if (!$tenant) {
        throw new RuntimeException('Assigned tenant is inactive or missing.');
    }

    if (current_active_tenant_id() !== $tenant_id) {
        $_SESSION['base_tenant_id'] = $tenant_id;
        $_SESSION['tenant_id'] = $tenant_id;
        $_SESSION['current_active_tenant_id'] = $tenant_id;
    }

    return true;
}

/**
 * Check if current user can access a specific tenant
 */
function can_access_tenant($tenant_id) {
    if (!is_logged_in()) return false;

    $tenant_id = intval($tenant_id ?? 0);
    if ($tenant_id <= 0) {
        return false;
    }

    if (is_super_admin()) {
        return true;
    }

    if (is_admin_owner()) {
        return user_owns_tenant($_SESSION['user_id'] ?? 0, $tenant_id, true);
    }

    return current_active_tenant_id() === $tenant_id;
}

function requested_tenant_id() {
    $candidates = [
        $_POST['tenant_id'] ?? null,
        $_GET['tenant_id'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        $tenant_id = intval($candidate);
        if ($tenant_id > 0) {
            return $tenant_id;
        }
    }

    return null;
}

function require_current_tenant_id() {
    $tenant_id = intval(current_active_tenant_id() ?? 0);
    if ($tenant_id <= 0) {
        throw new RuntimeException('Missing tenant context.');
    }
    return $tenant_id;
}

function subscription_access_exempt_paths() {
    return [
        'account_settings.php',
        'login.php',
        'logout.php',
        'my_subscription.php',
        'select_tenant.php',
        'set_password.php',
        'subscription_required.php',
    ];
}

function is_subscription_access_exempt_path($path = null) {
    if ($path === null || $path === '') {
        $path = $_SERVER['SCRIPT_NAME'] ?? '';
    }

    $script_name = basename((string) $path);
    if ($script_name === '') {
        return false;
    }

    return in_array($script_name, subscription_access_exempt_paths(), true);
}

function current_tenant_subscription_access($tenant_id = null) {
    $tenant_id = normalize_tenant_id($tenant_id ?? current_active_tenant_id());
    if (is_super_admin() || $tenant_id === null) {
        return [
            'allowed' => true,
            'tenant_id' => $tenant_id,
            'status' => 'ACTIVE',
            'label' => 'Active',
            'title' => 'Subscription Active',
            'message' => 'Subscription access is available.',
        ];
    }

    $subscription = get_tenant_subscription_status($tenant_id);
    $status = strtoupper(trim((string) ($subscription['status'] ?? 'MISSING')));
    $label = trim((string) ($subscription['label'] ?? 'Unavailable'));
    $allowed = in_array($status, ['ACTIVE', 'TRIAL'], true);
    $title_map = [
        'PENDING' => 'Subscription Required',
        'PAST_DUE' => 'Subscription Inactive',
        'SUSPENDED' => 'Subscription Inactive',
        'CANCELLED' => 'Subscription Inactive',
        'EXPIRED' => 'Subscription Inactive',
        'MISSING' => 'Subscription Required',
    ];

    return [
        'allowed' => $allowed,
        'tenant_id' => $tenant_id,
        'status' => $status,
        'label' => $label,
        'title' => $allowed ? 'Subscription Active' : ($title_map[$status] ?? 'Subscription Inactive'),
        'message' => trim((string) ($subscription['message'] ?? 'Subscription information is unavailable.')),
        'expires_at' => $subscription['expires_at'] ?? null,
        'is_trial' => !empty($subscription['is_trial']),
        'is_pending' => !empty($subscription['is_pending']),
        'is_suspended' => !empty($subscription['is_suspended']),
        'is_expired' => !empty($subscription['is_expired']),
    ];
}

function subscription_required_url($tenant_id = null, $status = null) {
    $query = [];
    $tenant_id = normalize_tenant_id($tenant_id ?? current_active_tenant_id());
    if ($tenant_id !== null) {
        $query['tenant_id'] = $tenant_id;
    }

    $status = strtoupper(trim((string) ($status ?? '')));
    if ($status !== '') {
        $query['status'] = strtolower($status);
    }

    $url = APP_BASE . '/staff/subscription_required.php';
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    return $url;
}

function subscription_default_redirect_url($role = null, $tenant_id = null) {
    $role = $role ?? current_role();
    $tenant_id = normalize_tenant_id($tenant_id ?? current_active_tenant_id());

    if ($role === 'ADMIN' || ($tenant_id !== null && !is_super_admin())) {
        $access = current_tenant_subscription_access($tenant_id);
        if (empty($access['allowed'])) {
            return subscription_required_url($access['tenant_id'], $access['status']);
        }
    }

    return APP_BASE . '/staff/dashboard.php';
}

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

function can_redirect_to_return_url($return_url, $tenant_id = null) {
    $return_url = normalize_app_redirect_url($return_url);
    if ($return_url === null || $return_url === '') {
        return false;
    }

    $tenant_id = normalize_tenant_id($tenant_id ?? current_active_tenant_id());
    $access = current_tenant_subscription_access($tenant_id);
    if (!empty($access['allowed'])) {
        return true;
    }

    $return_path = parse_url($return_url, PHP_URL_PATH);
    if ($return_path === false || $return_path === null || $return_path === '') {
        $return_path = $return_url;
    }

    return is_subscription_access_exempt_path($return_path);
}

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

function post_login_default_url($role = null) {
    return subscription_default_redirect_url($role);
}

function enforce_current_subscription_access() {
    if (!is_logged_in() || is_super_admin() || !has_active_tenant_scope()) {
        return true;
    }

    $access = current_tenant_subscription_access();
    if (!empty($access['allowed']) || is_subscription_access_exempt_path()) {
        return true;
    }

    header("Location: " . subscription_required_url($access['tenant_id'], $access['status']));
    exit;
}

function has_valid_tenant_session() {
    try {
        return ensure_active_tenant_session();
    } catch (Exception $e) {
        error_log("Tenant session validation error: " . $e->getMessage());
        return false;
    }
}

function is_tenant_restricted() {
    return is_logged_in() && has_active_tenant_scope();
}

function tenant_condition($column = 'tenant_id') {
    return is_tenant_restricted() ? "{$column} = ?" : "1=1";
}

function tenant_types($types = '') {
    return is_tenant_restricted() ? 'i' . $types : $types;
}

function tenant_params(array $params = []) {
    return is_tenant_restricted() ? array_merge([require_current_tenant_id()], $params) : $params;
}

function enforce_tenant_resource_access($table, $id_column, $resource_id, $tenant_column = 'tenant_id') {
    if (!has_active_tenant_scope() && is_super_admin()) {
        return;
    }

    $resource_id = intval($resource_id);
    if ($resource_id <= 0) {
        http_response_code(404);
        echo "Not found";
        exit;
    }

    $sql = "SELECT 1 FROM {$table} WHERE {$id_column}=? AND {$tenant_column}=? LIMIT 1";
    $resource = fetch_one(q($sql, "ii", [$resource_id, require_current_tenant_id()]));

    if (!$resource) {
        http_response_code(404);
        echo "Not found";
        exit;
    }
}

/**
 * Logout current user
 */
function logout_user() {
    if (is_logged_in() && !is_password_setup_required()) {
        require_once __DIR__ . '/loan_helpers.php';
        $logout_tenant_id = intval(current_tenant_id() ?? $_SESSION['tenant_id'] ?? 0);
        $full_name = $_SESSION['full_name'] ?? 'User';
        $role = $_SESSION['role'] ?? 'UNKNOWN';
        $description = 'Staff user logged out: ' . $full_name . ' (' . $role . ').';
        if ($logout_tenant_id > 0) {
            log_activity_for_tenant($logout_tenant_id, 'USER_LOGOUT', $description);
        } else {
            log_system_activity('USER_LOGOUT', $description);
        }
    }

    clear_password_setup_state();
    session_unset();
    session_destroy();
}

function log_password_history_entry($user_id, $tenant_id, $user_role, $action, $description) {
    require_once __DIR__ . '/loan_helpers.php';

    $user_id = intval($user_id ?? 0);
    $tenant_id = intval($tenant_id ?? 0);
    $user_role = trim((string) $user_role);
    $action = trim((string) $action);
    $description = trim((string) $description);

    if ($user_id <= 0 || $user_role === '' || $action === '' || $description === '') {
        return;
    }

    ensure_activity_logs_table();

    try {
        q(
            "INSERT INTO activity_logs (tenant_id, user_id, user_role, action, description, loan_id, customer_id, reference_no)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            "iisssiis",
            [$tenant_id > 0 ? $tenant_id : null, $user_id, $user_role, $action, $description, null, null, null]
        );
    } catch (Exception $e) {
        error_log("Password history insertion error: " . $e->getMessage());
    }
}

function current_tenant_feature_access($feature_key, $tenant_id = null) {
    $resolved_tenant_id = intval($tenant_id ?? 0);
    if ($resolved_tenant_id <= 0) {
        $resolved_tenant_id = intval(current_active_tenant_id() ?? 0);
    }

    if ($resolved_tenant_id <= 0 && is_super_admin()) {
        return [
            'allowed' => true,
            'tenant_id' => 0,
            'feature_key' => normalize_subscription_feature_key($feature_key),
            'feature_label' => subscription_feature_label($feature_key),
            'plan_code' => '',
            'plan_name' => 'Global',
            'message' => subscription_feature_label($feature_key) . ' is available in global super admin view.',
        ];
    }

    return get_tenant_feature_access($resolved_tenant_id, $feature_key);
}

function current_tenant_has_feature($feature_key, $tenant_id = null) {
    $access = current_tenant_feature_access($feature_key, $tenant_id);
    return !empty($access['allowed']);
}

function require_tenant_feature($feature_key, $tenant_id = null, $options = []) {
    require_login();

    $access = current_tenant_feature_access($feature_key, $tenant_id);
    if (!empty($access['allowed'])) {
        return $access;
    }

    $status = intval($options['status'] ?? 403);
    $title = trim((string) ($options['title'] ?? 'Plan Restricted'));
    $back_href = trim((string) ($options['back_href'] ?? (APP_BASE . '/staff/dashboard.php')));
    $back_label = trim((string) ($options['back_label'] ?? 'Return to Dashboard'));

    http_response_code($status);
    echo "<h2>" . htmlspecialchars($title) . "</h2>";
    echo "<p>" . htmlspecialchars($access['message'] ?? 'This feature is not available for the current tenant plan.') . "</p>";
    echo "<p><a href='" . htmlspecialchars($back_href, ENT_QUOTES) . "'>" . htmlspecialchars($back_label) . "</a></p>";
    exit;
}

/**
 * Get role display name
 */
function get_role_display_name($role) {
    $roleNames = [
        'SUPER_ADMIN' => 'Super Administrator',
        'ADMIN' => 'Administrator',
        'MANAGER' => 'Manager',
        'CREDIT_INVESTIGATOR' => 'Credit Investigator',
        'LOAN_OFFICER' => 'Loan Officer',
        'CASHIER' => 'Cashier',
        'CUSTOMER' => 'Customer (Mobile Only)' // Informational only
    ];
    return $roleNames[$role] ?? $role;
}

/**
 * Get role rank for hierarchical comparisons
 * Lower number = higher authority
 */
function get_role_rank($role) {
    $ranks = [
        'SUPER_ADMIN' => 0,
        'ADMIN' => 1,
        'MANAGER' => 2,
        'CREDIT_INVESTIGATOR' => 3,
        'LOAN_OFFICER' => 4,
        'CASHIER' => 5,
        'CUSTOMER' => 99 // Not applicable for web
    ];
    return $ranks[$role] ?? 99;
}

/**
 * Check if current user has greater authority than specified role
 */
function user_outranks($role) {
    $userRole = $_SESSION['role'] ?? '';
    return get_role_rank($userRole) < get_role_rank($role);
}

/**
 * Backward compatibility: use get_role_rank
 */
function role_rank($role) {
    return get_role_rank($role);
}

/**
 * Get system settings (cached in session when possible)
 */
function default_system_settings() {
    return [
        'system_name' => 'CredenceLend',
        'primary_color' => app_default_primary_color(),
        'logo_path' => APP_BASE . '/assets/img/new-logo.jfif'
    ];
}

function system_settings_cache_key($tenant_id = null) {
    $tenant_id = intval($tenant_id ?? 0);
    return $tenant_id > 0 ? 'system_settings_' . $tenant_id : 'system_settings_default';
}

function clear_system_settings_cache($tenant_id = null) {
    if ($tenant_id !== null && intval($tenant_id) > 0) {
        unset($_SESSION[system_settings_cache_key($tenant_id)]);
        return;
    }

    foreach (array_keys($_SESSION) as $session_key) {
        if (strpos($session_key, 'system_settings') === 0) {
            unset($_SESSION[$session_key]);
        }
    }
}

function resolve_settings_tenant_id($tenant_id = null) {
    $tenant_id = intval($tenant_id ?? 0);
    if ($tenant_id > 0) {
        return $tenant_id;
    }

    if (is_logged_in()) {
        if (is_global_super_admin_view()) {
            return 0;
        }

        $session_tenant_id = intval(current_tenant_id() ?? 0);
        if ($session_tenant_id > 0) {
            return $session_tenant_id;
        }
    }

    return requested_tenant_id();
}

function get_system_settings($tenant_id = null) {
    $tenant_id = resolve_settings_tenant_id($tenant_id);
    $cache_key = system_settings_cache_key($tenant_id);

    if (isset($_SESSION[$cache_key])) {
        return $_SESSION[$cache_key];
    }

    $result = default_system_settings();

    try {
        $conn = db();
        $check_table = $conn->query("SHOW TABLES LIKE 'system_settings'");
        if (!$check_table || $check_table->num_rows === 0) {
            return $result;
        }

        $has_tenant_column = $conn->query("SHOW COLUMNS FROM system_settings LIKE 'tenant_id'");
        $supports_tenant_settings = $has_tenant_column && $has_tenant_column->num_rows > 0;

        if ($supports_tenant_settings && $tenant_id) {
            $stmt = $conn->prepare("SELECT system_name, logo_path, primary_color FROM system_settings WHERE tenant_id=? LIMIT 1");
            $stmt->bind_param("i", $tenant_id);
            $stmt->execute();
            $settings = $stmt->get_result()->fetch_assoc();

            if (!$settings) {
                $tenant = fetch_one(q(
                    "SELECT COALESCE(display_name, tenant_name) AS system_name, logo_path, primary_color FROM tenants WHERE tenant_id=? LIMIT 1",
                    "i",
                    [$tenant_id]
                ));
                if ($tenant) {
                    $settings = $tenant;
                }
            }
        } else {
            $stmt = $conn->prepare("SELECT system_name, logo_path, primary_color FROM system_settings LIMIT 1");
            $stmt->execute();
            $settings = $stmt->get_result()->fetch_assoc();
        }

        if ($settings) {
            $result = array_merge($result, array_filter($settings, static function ($value) {
                return $value !== null && $value !== '';
            }));
        }

        if ($tenant_id > 0) {
            if (!tenant_has_feature($tenant_id, 'logo_upload')) {
                $result['logo_path'] = default_system_settings()['logo_path'];
            }

            if (!tenant_has_feature($tenant_id, 'theme_customization')) {
                $result['primary_color'] = app_default_primary_color();
            }
        }

        $_SESSION[$cache_key] = $result;
        return $result;
    } catch (Exception $e) {
        error_log("System settings error: " . $e->getMessage());
        return $result;
    }
}

/**
 * Update system setting
 */
function update_system_setting($key, $value) {
    try {
        $tenant_id = require_current_tenant_id();
        $allowed_keys = ['system_name', 'logo_path', 'primary_color'];
        if (!in_array($key, $allowed_keys, true)) {
            return false;
        }

        $settings = get_system_settings($tenant_id);
        $settings[$key] = $value;

        $existing = fetch_one(q("SELECT setting_id FROM system_settings WHERE tenant_id=? LIMIT 1", "i", [$tenant_id]));
        if ($existing) {
            q(
                "UPDATE system_settings SET system_name=?, logo_path=?, primary_color=? WHERE tenant_id=?",
                "sssi",
                [$settings['system_name'], $settings['logo_path'], $settings['primary_color'], $tenant_id]
            );
        } else {
            q(
                "INSERT INTO system_settings (tenant_id, system_name, logo_path, primary_color) VALUES (?, ?, ?, ?)",
                "isss",
                [$tenant_id, $settings['system_name'], $settings['logo_path'], $settings['primary_color']]
            );
        }

        clear_system_settings_cache($tenant_id);
        return true;
    } catch (Exception $e) {
        error_log("Update setting error: " . $e->getMessage());
        return false;
    }
}

/**
 * Security: Check session timeout (30 minutes of inactivity)
 * Call this in critical pages
 */
function check_session_timeout($timeout = 1800) {
    if (!is_logged_in()) return;
    
    $currentTime = time();
    $lastActivity = $_SESSION['last_activity'] ?? $currentTime;
    
    if (($currentTime - $lastActivity) > $timeout) {
        logout_user();
        $_SESSION['timeout_message'] = "Your session has expired. Please login again.";
        header("Location: " . APP_BASE . "/staff/login.php");
        exit;
    }
    
    $_SESSION['last_activity'] = $currentTime;
}

/**
 * Generate CSRF token for forms
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_post_csrf() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        http_response_code(400);
        echo "<h2>400 Bad Request</h2>";
        echo "<p>Invalid or missing CSRF token.</p>";
        exit;
    }
}

/**
 * Get CSRF input field HTML
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

/**
 * Password strength validation
 */
function password_is_strong($pw) {
    return AuthService::isPasswordStrong($pw);
}
?>
