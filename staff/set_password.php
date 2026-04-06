<?php
require_once __DIR__ . '/../includes/auth.php';

$clear_setup_auth_session = function () {
  clear_password_setup_state();
  clear_system_settings_cache();
  foreach ([
    'user_id',
    'role',
    'full_name',
    'email',
    'contact_no',
    'base_tenant_id',
    'tenant_id',
    'current_active_tenant_id',
    'username',
    'login_time',
    'is_system_admin',
    'pending_login_audit',
    'return_url',
  ] as $session_key) {
    unset($_SESSION[$session_key]);
  }
  if (session_status() === PHP_SESSION_ACTIVE) {
    @session_regenerate_id(true);
  }
};

$find_account_by_token = function ($token) {
  if ($token === '') {
    return null;
  }

  return fetch_one(q(
    "SELECT
        u.user_id,
        u.tenant_id,
        u.username,
        u.full_name,
        u.role,
        u.email,
        u.contact_no,
        COALESCE(t.display_name, t.tenant_name) AS tenant_name
     FROM users u
     LEFT JOIN tenants t ON t.tenant_id = u.tenant_id
     WHERE u.reset_token=?
       AND u.reset_token_expiry > NOW()
       AND u.role != 'CUSTOMER'
       AND u.is_active=1
     LIMIT 1",
    "s",
    [$token]
  ));
};

$token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? current_password_setup_token()));
$account = $find_account_by_token($token);
$err = '';
$selected_tenant_id = intval($_GET['tenant_id'] ?? $_POST['tenant_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

if (!$account && is_password_setup_required()) {
  $clear_setup_auth_session();
}

if ($account) {
  begin_password_setup_session($account, $token);
  $selected_tenant_id = intval($account['tenant_id'] ?? 0);
}

$settings = get_system_settings($selected_tenant_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if ($token === '') {
    $err = 'Invalid setup token.';
  } elseif (!$account) {
    $err = 'This setup link is invalid or has expired. Ask your administrator to send a new invitation.';
  } elseif ($new_password !== $confirm_password) {
    $err = 'Passwords do not match.';
  } elseif (!password_is_strong($new_password)) {
    $err = 'Password must be 8+ chars with upper, lower, number, special.';
  } else {
    q(
      "UPDATE users
       SET password_hash=?,
           reset_token=NULL,
           reset_token_expiry=NULL
       WHERE user_id=?
         AND reset_token=?",
      "sis",
      [password_hash($new_password, PASSWORD_DEFAULT), $account['user_id'], $token]
    );
    log_password_history_entry(
      $account['user_id'],
      $account['tenant_id'] ?? $selected_tenant_id,
      $account['role'] ?? 'UNKNOWN',
      'PASSWORD_SETUP_COMPLETED',
      'Initial password setup completed for ' . ($account['full_name'] ?? 'Staff user') . '.'
    );

    $clear_setup_auth_session();
    $login_query = ['setup' => 'success'];
    if ($selected_tenant_id > 0) {
      $login_query['tenant_id'] = $selected_tenant_id;
    }
    header('Location: ' . APP_BASE . '/staff/login.php?' . http_build_query($login_query));
    exit;
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Create Your Password</title>
  <link rel="stylesheet" href="<?php echo APP_BASE; ?>/assets/css/theme.css">
  <style>
    :root{
      --setup-primary: <?= htmlspecialchars(app_primary_color($settings['primary_color'] ?? null), ENT_QUOTES) ?>;
      --setup-primary-dark:#091121;
      --setup-ink:#e5eefc;
      --setup-muted:#93a4c3;
      --setup-line:rgba(148,163,184,.18);
      --setup-panel:rgba(9,16,34,.84);
      --setup-surface:rgba(15,23,42,.9);
      --setup-shadow:0 28px 80px rgba(2,6,23,.46);
    }
    body.setup-body{
      min-height:100vh;
      background:
        radial-gradient(circle at top left, rgba(37,99,235,.22), transparent 26%),
        radial-gradient(circle at bottom right, rgba(14,165,233,.16), transparent 30%),
        linear-gradient(135deg, #030712 0%, #081224 40%, #0b1733 100%);
      color:var(--setup-ink);
    }
    .setup-shell{min-height:100vh;padding:24px;display:flex;align-items:center;justify-content:center}
    .setup-card{
      width:min(100%, 560px);
      border-radius:28px;
      background:var(--setup-panel);
      border:1px solid var(--setup-line);
      box-shadow:var(--setup-shadow);
      padding:30px;
    }
    .setup-card h1{margin:0 0 12px 0;font-size:30px;line-height:1.05}
    .setup-card p{color:var(--setup-muted)}
    .setup-summary{
      margin:22px 0;
      padding:16px 18px;
      border-radius:18px;
      background:rgba(15,23,42,.62);
      border:1px solid var(--setup-line);
    }
    .setup-summary strong{display:block;color:#fff;margin-bottom:6px}
    .setup-field{margin-top:14px}
    .setup-card .input{
      width:100%;
      border-radius:16px;
      border:1px solid var(--setup-line);
      background:var(--setup-surface);
      color:var(--setup-ink);
    }
    .setup-toggle{
      margin-top:10px;
      display:inline-flex;
      align-items:center;
      gap:10px;
      color:var(--setup-muted);
      font-size:14px;
    }
    .setup-submit{
      width:100%;
      min-height:50px;
      margin-top:20px;
      border-radius:16px;
      font-size:15px;
    }
    .setup-note{
      margin-top:16px;
      font-size:13px;
      color:var(--setup-muted);
    }
  </style>
</head>
<body class="setup-body">
  <div class="setup-shell">
    <section class="setup-card">
      <h1>Create Your Password</h1>
      <?php if ($account): ?>
        <p>Finish this step to activate your staff account. Until you create a password, this account cannot access the system.</p>
      <?php else: ?>
        <p>This setup link is invalid or has expired.</p>
      <?php endif; ?>

      <?php if ($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

      <?php if ($account): ?>
        <div class="setup-summary">
          <strong><?= htmlspecialchars($account['full_name']) ?></strong>
          <div>Username: <?= htmlspecialchars($account['username']) ?></div>
          <div>Role: <?= htmlspecialchars(get_role_display_name($account['role'])) ?></div>
          <div>Tenant: <?= htmlspecialchars($account['tenant_name'] ?: 'Owner account') ?></div>
        </div>

        <form method="post" id="setup-password-form">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
          <?php if ($selected_tenant_id > 0): ?>
            <input type="hidden" name="tenant_id" value="<?= intval($selected_tenant_id) ?>">
          <?php endif; ?>

          <div class="setup-field">
            <label class="label">New Password</label>
            <input class="input" type="password" id="setup_pw" name="new_password" required>
          </div>

          <div class="setup-field">
            <label class="label">Confirm Password</label>
            <input class="input" type="password" id="setup_confirm_pw" name="confirm_password" required>
          </div>

          <label class="setup-toggle">
            <input type="checkbox" onclick="['setup_pw','setup_confirm_pw'].forEach(function(id){var field=document.getElementById(id);if(field){field.type=this.checked?'text':'password';}}, this)">
            <span>Show password</span>
          </label>

          <button class="btn btn-primary setup-submit" type="submit">Save Password</button>
          <div class="setup-note">You must create your own password here before you can continue to the login page.</div>
        </form>
      <?php else: ?>
        <a class="btn btn-primary setup-submit" href="<?php echo APP_BASE; ?>/staff/login.php<?= $selected_tenant_id > 0 ? '?tenant_id=' . intval($selected_tenant_id) : '' ?>" style="display:flex;align-items:center;justify-content:center">Back to login</a>
      <?php endif; ?>
    </section>
  </div>

  <?php if ($account): ?>
  <script>
    (function () {
      var form = document.getElementById('setup-password-form');
      var submitted = false;
      if (form) {
        form.addEventListener('submit', function () {
          submitted = true;
        });
      }
      window.addEventListener('beforeunload', function (event) {
        if (submitted) {
          return;
        }
        event.preventDefault();
        event.returnValue = '';
      });
    }());
  </script>
  <?php endif; ?>
</body>
</html>
