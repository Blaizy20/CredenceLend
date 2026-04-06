<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mail_helper.php';

$selected_tenant_id = requested_tenant_id();
$tenants = fetch_all(q(
  "SELECT tenant_id, COALESCE(display_name, tenant_name) AS display_name
   FROM tenants
   WHERE is_active=1
   ORDER BY tenant_name ASC"
));
$settings = get_system_settings($selected_tenant_id);
$msg = ''; $err='';
$step = $_GET['step'] ?? '1'; // Step 1: Request reset, Step 2: Reset password
$auth_service = new AuthService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

if ($step == '1' && $_SERVER['REQUEST_METHOD']==='POST') {
  // Step 1: User requests password reset via email
  $email = trim($_POST['email'] ?? '');
  $selected_tenant_id = intval($_POST['tenant_id'] ?? 0);
  $settings = get_system_settings($selected_tenant_id);
  
  if (empty($email)) {
    $err = 'Please enter your email address.';
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Enter a valid email address.';
  } else {
    if ($selected_tenant_id > 0) {
      $u = fetch_one(q(
        "SELECT user_id, email, full_name, role FROM users WHERE tenant_id=? AND email=? AND role != 'CUSTOMER' AND is_active=1",
        "is",
        [$selected_tenant_id, $email]
      ));
    } else {
      $u = fetch_one(q(
        "SELECT user_id, email, full_name, role FROM users WHERE email=? AND role IN ('SUPER_ADMIN','ADMIN') AND is_active=1",
        "s",
        [$email]
      ));
    }
    
    if (!$u) {
      $err = 'No staff account found with this email.';
    } else {
      $token = $auth_service->generateResetToken($u['user_id'], 3600);
      if (!$token) {
        $err = 'Could not create a password reset token right now. Please try again.';
      } else {
        $resetLink = app_public_url('/staff/forgot_password.php', array_filter([
          'step' => 2,
          'tenant_id' => $selected_tenant_id > 0 ? $selected_tenant_id : null,
          'token' => $token,
        ], static function ($value) {
          return $value !== null && $value !== '';
        }));
      
        $to = $email;
        $subject = 'Password Reset Request - CredenceLend';
      
        $body = "
          <html><body style=\"font-family:Arial,sans-serif;color:#0f172a;line-height:1.6\">
          <h2>Password Reset Request</h2>
          <p>Hello " . htmlspecialchars($u['full_name']) . ",</p>
          <p>You requested a password reset for your staff account.</p>
          <p><a href=\"" . htmlspecialchars($resetLink, ENT_QUOTES) . "\" style=\"display:inline-block;background-color:#2563eb;color:#ffffff;padding:12px 18px;text-decoration:none;border-radius:8px;font-weight:700;\">Reset Password</a></p>
          <p>If the button does not open, use this link:</p>
          <p><a href=\"" . htmlspecialchars($resetLink, ENT_QUOTES) . "\">" . htmlspecialchars($resetLink) . "</a></p>
          <p><strong>This reset link expires in 1 hour.</strong></p>
          <p>If you did not request this reset, you can ignore this email.</p>
          <p>Best regards,<br>CredenceLend</p>
          </body></html>
        ";

        $textBody = implode("\n", [
          'Password Reset Request',
          '',
          'Hello ' . ($u['full_name'] ?? 'User') . ',',
          '',
          'You requested a password reset for your staff account.',
          'Open this link to continue:',
          $resetLink,
          '',
          'This reset link expires in 1 hour.',
          'If you did not request this reset, you can ignore this email.',
        ]);
      
        $send_result = app_send_html_mail($to, $subject, $body, [
          'from_name' => 'CredenceLend',
          'text_body' => $textBody,
        ]);

        if ($send_result['ok']) {
          $msg = 'A password reset email has been sent to <strong>' . htmlspecialchars($email) . '</strong>. Please check your inbox.';
        } else {
          $err = 'Error sending email. Configure mail delivery and try again.';
        }
      }
    }
  }
} else if ($step == '2' && $_SERVER['REQUEST_METHOD']==='POST') {
  // Step 2: Staff resets password using token
  $token = trim($_POST['token'] ?? $_GET['token'] ?? '');
  $newpw = $_POST['new_password'] ?? '';
  $conf = $_POST['confirm_password'] ?? '';
  
  if (empty($token)) {
    $err = 'Invalid reset token.';
  } else if ($newpw !== $conf) {
    $err = 'Passwords do not match.';
  } else if (!password_is_strong($newpw)) {
    $err = 'Password must be 8+ chars with upper, lower, number, special.';
  } else {
    // Verify token exists and is not expired
    if ($selected_tenant_id > 0) {
      $u = fetch_one(q(
        "SELECT user_id, tenant_id, full_name, role FROM users WHERE tenant_id=? AND reset_token=? AND reset_token_expiry > NOW()",
        "is",
        [$selected_tenant_id, $token]
      ));
    } else {
      $u = fetch_one(q("SELECT user_id, tenant_id, full_name, role FROM users WHERE reset_token=? AND reset_token_expiry > NOW()", "s", [$token]));
    }

    if (!$u) {
      $err = 'Invalid or expired reset token.';
    } else {
      // Update password and clear token
      $hash = password_hash($newpw, PASSWORD_DEFAULT);
      q("UPDATE users SET password_hash=?, reset_token=NULL, reset_token_expiry=NULL WHERE user_id=?", "si", [$hash, $u['user_id']]);
      log_password_history_entry(
        $u['user_id'],
        $u['tenant_id'] ?? $selected_tenant_id,
        $u['role'] ?? 'UNKNOWN',
        'PASSWORD_RESET',
        'Password reset completed for ' . ($u['full_name'] ?? 'Staff user') . '.'
      );
      $login_query = ['reset' => 'success'];
      if ($selected_tenant_id > 0) {
        $login_query['tenant_id'] = $selected_tenant_id;
      }
      header('Location: ' . APP_BASE . '/staff/login.php?' . http_build_query($login_query));
      exit;
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Staff Password Reset</title>
  <link rel="stylesheet" href="<?php echo APP_BASE; ?>/assets/css/theme.css">
  <style>
    :root{
      --reset-primary: <?= htmlspecialchars(app_primary_color($settings['primary_color'] ?? null), ENT_QUOTES) ?>;
      --reset-primary-dark: #091121;
      --reset-ink: #e5eefc;
      --reset-muted: #93a4c3;
      --reset-line: rgba(148, 163, 184, 0.18);
      --reset-panel: rgba(9, 16, 34, 0.84);
      --reset-surface: rgba(15, 23, 42, 0.9);
      --reset-shadow: 0 28px 80px rgba(2, 6, 23, 0.46);
    }
    body.reset-body{
      min-height:100vh;
      background:
        radial-gradient(circle at top left, rgba(37,99,235,.22), transparent 26%),
        radial-gradient(circle at bottom right, rgba(14,165,233,.16), transparent 30%),
        linear-gradient(135deg, #030712 0%, #081224 40%, #0b1733 100%);
      color:var(--reset-ink);
    }
    .reset-shell{
      min-height:100vh;
      padding:24px;
      display:flex;
      flex-direction:column;
    }
    .reset-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
      margin-bottom:24px;
    }
    .reset-brand{
      display:flex;
      align-items:center;
      gap:14px;
    }
    .reset-brand-mark{
      width:54px;
      height:54px;
      border-radius:18px;
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.08);
      box-shadow:0 14px 36px rgba(2,6,23,.28);
      display:flex;
      align-items:center;
      justify-content:center;
      overflow:hidden;
    }
    .reset-brand-mark img{
      width:40px;
      height:40px;
      object-fit:contain;
    }
    .reset-brand-text strong{
      display:block;
      font-size:17px;
      line-height:1.1;
      letter-spacing:-.03em;
    }
    .reset-brand-text span{
      display:block;
      margin-top:4px;
      color:var(--reset-muted);
      font-size:12px;
      text-transform:uppercase;
      letter-spacing:.12em;
    }
    .reset-status{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:10px 14px;
      border-radius:999px;
      background:rgba(15,23,42,.58);
      border:1px solid var(--reset-line);
      color:var(--reset-muted);
      font-size:12px;
    }
    .reset-status::before{
      content:"";
      width:8px;
      height:8px;
      border-radius:999px;
      background:var(--reset-primary);
      box-shadow:0 0 0 4px rgba(44,62,197,.12);
    }
    .reset-main{
      flex:1;
      display:grid;
      grid-template-columns:minmax(0, 1.05fr) minmax(380px, 460px);
      gap:26px;
      align-items:stretch;
    }
    .reset-hero{
      position:relative;
      overflow:hidden;
      border-radius:32px;
      padding:38px;
      color:#fff;
      background:
        radial-gradient(circle at top left, rgba(96,165,250,.24), transparent 28%),
        radial-gradient(circle at bottom right, rgba(14,165,233,.18), transparent 24%),
        linear-gradient(135deg, #091121 0%, var(--reset-primary-dark) 38%, var(--reset-primary) 100%);
      box-shadow:var(--reset-shadow);
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }
    .reset-kicker{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:10px 14px;
      border-radius:999px;
      background:rgba(255,255,255,.12);
      border:1px solid rgba(255,255,255,.16);
      font-size:11px;
      text-transform:uppercase;
      letter-spacing:.14em;
      color:rgba(255,255,255,.8);
    }
    .reset-kicker::before{
      content:"";
      width:8px;
      height:8px;
      border-radius:999px;
      background:#7dd3fc;
    }
    .reset-hero h1{
      margin:22px 0 14px;
      font-size:clamp(38px, 6vw, 68px);
      line-height:.98;
      letter-spacing:-.06em;
      max-width:10ch;
    }
    .reset-hero p{
      margin:0;
      max-width:60ch;
      color:rgba(255,255,255,.78);
      line-height:1.7;
      font-size:15px;
    }
    .reset-grid{
      margin-top:28px;
      display:grid;
      grid-template-columns:repeat(2, minmax(0, 1fr));
      gap:14px;
    }
    .reset-panel-note{
      padding:16px 18px;
      border-radius:20px;
      background:rgba(255,255,255,.12);
      border:1px solid rgba(255,255,255,.14);
      backdrop-filter:blur(10px);
    }
    .reset-panel-note strong{
      display:block;
      font-size:14px;
      letter-spacing:.02em;
    }
    .reset-panel-note span{
      display:block;
      margin-top:6px;
      color:rgba(255,255,255,.68);
      font-size:12px;
      line-height:1.5;
    }
    .reset-footer-stats{
      margin-top:24px;
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:12px;
    }
    .reset-stat{
      padding:16px 18px;
      border-radius:20px;
      background:rgba(15,23,42,.18);
      border:1px solid rgba(255,255,255,.08);
    }
    .reset-stat strong{
      display:block;
      font-size:24px;
      letter-spacing:-.04em;
    }
    .reset-stat span{
      display:block;
      margin-top:4px;
      font-size:12px;
      color:rgba(255,255,255,.72);
    }
    .reset-card{
      border-radius:32px;
      padding:28px;
      background:var(--reset-panel);
      border:1px solid rgba(148,163,184,.12);
      box-shadow:var(--reset-shadow);
      backdrop-filter:blur(18px);
      display:flex;
      flex-direction:column;
      justify-content:center;
    }
    .reset-card-top h2{
      margin:0;
      font-size:34px;
      line-height:1;
      letter-spacing:-.05em;
    }
    .reset-card-top p{
      margin:10px 0 0;
      color:var(--reset-muted);
      line-height:1.65;
      font-size:14px;
    }
    .reset-field{
      margin-top:16px;
    }
    .reset-card .label{
      margin:0 0 8px;
      font-size:12px;
      text-transform:uppercase;
      letter-spacing:.12em;
      color:var(--reset-muted);
    }
    .reset-card .input{
      min-height:48px;
      border-radius:16px;
      border:1px solid rgba(148,163,184,.16);
      background:var(--reset-surface);
      box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
      color:var(--reset-ink);
    }
    .reset-card .input::placeholder{
      color:#6b7b97;
    }
    .reset-card .input:focus{
      outline:none;
      border-color:rgba(96,165,250,.45);
      box-shadow:0 0 0 3px rgba(59,130,246,.16);
    }
    .reset-card select.input option{
      color:#e5eefc;
      background:#0f172a;
    }
    .reset-hint{
      margin-top:8px;
      color:var(--reset-muted);
      font-size:12px;
      line-height:1.55;
    }
    .reset-toggle{
      display:flex;
      align-items:center;
      gap:8px;
      margin-top:12px;
      color:var(--reset-muted);
      font-size:12px;
    }
    .reset-submit{
      width:100%;
      min-height:50px;
      margin-top:20px;
      border-radius:16px;
      font-size:15px;
      box-shadow:0 16px 34px rgba(44,62,197,.22);
    }
    .reset-link-row{
      margin-top:14px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      flex-wrap:wrap;
    }
    .reset-link{
      color:var(--reset-primary);
      font-weight:600;
    }
    .reset-note{
      color:var(--reset-muted);
      font-size:12px;
    }
    .reset-card .alert.err{
      background:rgba(127, 29, 29, 0.28);
      color:#fecaca;
      border:1px solid rgba(248,113,113,.22);
    }
    .reset-card .alert.ok{
      background:rgba(22, 101, 52, 0.24);
      color:#bbf7d0;
      border:1px solid rgba(74, 222, 128, .2);
    }
    @media (max-width: 1080px){
      .reset-main{
        grid-template-columns:1fr;
      }
    }
    @media (max-width: 720px){
      .reset-shell{
        padding:16px;
      }
      .reset-header{
        flex-direction:column;
        align-items:flex-start;
      }
      .reset-hero,
      .reset-card{
        padding:22px;
        border-radius:28px;
      }
      .reset-grid,
      .reset-footer-stats{
        grid-template-columns:1fr;
      }
      .reset-link-row{
        flex-direction:column;
        align-items:flex-start;
      }
      .reset-submit{
        width:100%;
      }
    }
  </style>
</head>
<body class="reset-body">
  <div class="reset-shell">
    <header class="reset-header">
      <div class="reset-brand">
        <div class="reset-brand-mark">
          <img src="<?php echo htmlspecialchars($settings['logo_path'] ?? APP_BASE . '/assets/img/new-logo.jfif'); ?>" alt="Logo"/>
        </div>
        <div class="reset-brand-text">
          <strong><?= htmlspecialchars($settings['system_name'] ?? 'CredenceLend') ?></strong>
          <span>Staff Recovery</span>
        </div>
      </div>
      <div class="reset-status"><?= ($step == '1') ? 'Request reset link' : 'Create new password' ?></div>
    </header>

    <main class="reset-main">
      <section class="reset-hero">
        <div>
          <div class="reset-kicker">Account Recovery</div>
          <h1><?= ($step == '1') ? 'Recover staff access without losing tenant context.' : 'Choose a new password and return to work.' ?></h1>
          <p><?= ($step == '1')
            ? 'Request a reset link for your staff account. Tenant-scoped roles should select the correct tenant before submitting their email address.'
            : 'Use the reset token from your email to create a fresh password. The existing token rules and validation remain unchanged.' ?></p>

          <div class="reset-grid">
            <div class="reset-panel-note">
              <strong>Tenant-aware recovery</strong>
              <span>Tenant-scoped staff can recover access with the correct tenant selected first.</span>
            </div>
            <div class="reset-panel-note">
              <strong>Admin owner access</strong>
              <span>`ADMIN` and `SUPER_ADMIN` accounts can request reset links without picking a tenant.</span>
            </div>
            <div class="reset-panel-note">
              <strong>Token lifetime</strong>
              <span>Reset links remain valid for one hour after generation.</span>
            </div>
            <div class="reset-panel-note">
              <strong>Strong password rule</strong>
              <span>New passwords still require upper, lower, numeric, and special characters.</span>
            </div>
          </div>
        </div>

        <div class="reset-footer-stats">
          <div class="reset-stat">
            <strong><?= count($tenants) ?></strong>
            <span>Active tenants</span>
          </div>
          <div class="reset-stat">
            <strong>1 hr</strong>
            <span>Token expiry window</span>
          </div>
          <div class="reset-stat">
            <strong>2</strong>
            <span>Recovery steps</span>
          </div>
        </div>
      </section>

      <section class="reset-card">
        <div class="reset-card-top">
          <h2>Staff Password Reset</h2>
          <p><?= ($step == '1') ? 'Enter your recovery email and we will send a reset link.' : 'Create your new password to finish the reset flow.' ?></p>
        </div>

        <?php if ($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
        <?php if ($msg): ?><div class="alert ok"><?= $msg ?></div><?php endif; ?>

        <form method="post" action="<?php
          $reset_form_query = ['step' => $step];
          if ($step == '2' && !empty($_GET['token'])) {
            $reset_form_query['token'] = $_GET['token'];
          }
          if ($selected_tenant_id) {
            $reset_form_query['tenant_id'] = intval($selected_tenant_id);
          }
          echo htmlspecialchars(APP_BASE . '/staff/forgot_password.php?' . http_build_query($reset_form_query));
        ?>">
          <?= csrf_field() ?>
          <?php if ($step == '1'): ?>
            <div class="reset-field">
              <label class="label">Tenant</label>
              <select class="input" name="tenant_id">
                <option value="">Select tenant if you are tenant-scoped staff</option>
                <?php foreach ($tenants as $tenant): ?>
                  <option value="<?= intval($tenant['tenant_id']) ?>" <?= ($selected_tenant_id === intval($tenant['tenant_id'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tenant['display_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="reset-hint">Owner `ADMIN` and `SUPER_ADMIN` accounts can request a reset without choosing a tenant.</div>
            </div>

            <div class="reset-field">
              <label class="label">Email Address</label>
              <input class="input" type="email" name="email" required placeholder="your-email@example.com">
            </div>

            <button class="btn btn-primary reset-submit" type="submit">Send Reset Link</button>
          <?php else: ?>
            <?php if (!isset($_GET['token']) || empty($_GET['token'])): ?>
              <div class="alert err">Invalid or missing reset token.</div>
              <a class="btn btn-primary reset-submit" href="<?php echo APP_BASE; ?>/staff/forgot_password.php?step=1" style="display:flex;align-items:center;justify-content:center;text-align:center">Back to Email Request</a>
            <?php else: ?>
              <div class="reset-field">
                <label class="label">New Password</label>
                <input class="input" type="password" id="pw" name="new_password" required>
              </div>

              <div class="reset-field">
                <label class="label">Confirm Password</label>
                <input class="input" type="password" id="confirm_pw" name="confirm_password" required>
              </div>

              <label class="reset-toggle">
                <input type="checkbox" onclick="['pw','confirm_pw'].forEach(function(id){var field=document.getElementById(id);if(field){field.type=this.checked?'text':'password';}}, this)">
                <span>Show password</span>
              </label>

              <button class="btn btn-primary reset-submit" type="submit">Reset Password</button>

              <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
              <?php if ($selected_tenant_id): ?>
                <input type="hidden" name="tenant_id" value="<?php echo intval($selected_tenant_id); ?>">
              <?php endif; ?>
            <?php endif; ?>
          <?php endif; ?>

          <div class="reset-link-row">
            <a class="reset-link" href="<?php echo APP_BASE; ?>/staff/login.php">Back to login</a>
            <div class="reset-note"><?= ($step == '1') ? 'Step 1 of 2' : 'Step 2 of 2' ?></div>
          </div>
        </form>
      </section>
    </main>
  </div>
</body>
</html>
