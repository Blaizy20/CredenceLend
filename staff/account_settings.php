<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';

require_login();

$title = 'My Account';
$active = '';
$err = '';
$ok = '';
$user_id = intval($_SESSION['user_id'] ?? 0);

$fetch_account = function () use ($user_id) {
  return fetch_one(q(
    "SELECT user_id, tenant_id, username, password_hash, full_name, role, email, contact_no, is_active
     FROM users
     WHERE user_id=?
     LIMIT 1",
    "i",
    [$user_id]
  ));
};

$sync_identity_session = function (array $account) {
  $_SESSION['username'] = $account['username'];
  $_SESSION['full_name'] = $account['full_name'];
  $_SESSION['email'] = $account['email'] ?? '';
  $_SESSION['contact_no'] = $account['contact_no'] ?? '';
};

$account = $fetch_account();
if (!$account) {
  logout_user();
  header('Location: ' . APP_BASE . '/staff/login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
  $username = trim($_POST['username'] ?? '');
  $full_name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $contact_no = trim($_POST['contact_no'] ?? '');

  if ($username === '') {
    $err = 'Username is required.';
  } elseif ($full_name === '' || count(array_filter(explode(' ', $full_name))) < 2) {
    $err = 'Full name must include first and last name.';
  } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Enter a valid email address.';
  } elseif (strlen($contact_no) > 30) {
    $err = 'Contact number must be 30 characters or less.';
  } else {
    $username_exists = fetch_one(q(
      "SELECT user_id
       FROM users
       WHERE LOWER(username)=LOWER(?)
         AND user_id != ?
       LIMIT 1",
      "si",
      [$username, $user_id]
    ));

    if ($username_exists) {
      $err = 'Username already exists.';
    } else {
      q(
        "UPDATE users
         SET username=?,
             full_name=?,
             email=?,
             contact_no=?
         WHERE user_id=?",
        "ssssi",
        [$username, $full_name, $email, $contact_no, $user_id]
      );

      $account = $fetch_account();
      $sync_identity_session($account);
      log_activity('ACCOUNT_PROFILE_UPDATED', 'Staff account profile updated by ' . $account['full_name'], null, null, null);
      $ok = 'Account details updated successfully.';
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if ($current_password === '' || $new_password === '' || $confirm_password === '') {
    $err = 'Complete all password fields.';
  } elseif (!password_verify($current_password, $account['password_hash'])) {
    $err = 'Current password is incorrect.';
  } elseif ($new_password !== $confirm_password) {
    $err = 'New password and confirmation do not match.';
  } elseif (!password_is_strong($new_password)) {
    $err = 'Password must be 8+ chars with upper, lower, number, and special.';
  } elseif (password_verify($new_password, $account['password_hash'])) {
    $err = 'Choose a different password from your current one.';
  } else {
    q(
      "UPDATE users
       SET password_hash=?
       WHERE user_id=?",
      "si",
      [password_hash($new_password, PASSWORD_DEFAULT), $user_id]
    );

    $account = $fetch_account();
    $sync_identity_session($account);
    log_activity('ACCOUNT_PASSWORD_UPDATED', 'Staff password changed for ' . $account['full_name'], null, null, null);
    $ok = 'Password changed successfully.';
  }
}

$account_scope = 'Owner Account';
if (current_tenant_id()) {
  $tenant = fetch_one(q(
    "SELECT COALESCE(display_name, tenant_name) AS tenant_name
     FROM tenants
     WHERE tenant_id=?
     LIMIT 1",
    "i",
    [require_current_tenant_id()]
  ));
  $account_scope = $tenant['tenant_name'] ?? $account_scope;
} elseif (is_global_super_admin_view()) {
  $account_scope = 'All Tenants';
} elseif (!empty($account['tenant_id'])) {
  $tenant = fetch_one(q(
    "SELECT COALESCE(display_name, tenant_name) AS tenant_name
     FROM tenants
     WHERE tenant_id=?
     LIMIT 1",
    "i",
    [intval($account['tenant_id'])]
  ));
  $account_scope = $tenant['tenant_name'] ?? $account_scope;
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
    background: linear-gradient(135deg, var(--brand-primary-deep), var(--brand-primary-mid)) !important;
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

  .sidebar {
    background: rgba(4, 10, 24, 0.84);
    border-right: 1px solid rgba(148, 163, 184, 0.12);
    backdrop-filter: blur(16px);
  }

  .sidebar h3 { color: #7f93b0; }
  .sidebar a { color: #d7e3f4; }
  .sidebar a.active,
  .sidebar a:hover {
    background: linear-gradient(135deg, var(--brand-primary-soft-strong), var(--brand-primary-soft));
    color: #f8fbff;
  }

  .account-shell {
    display: grid;
    gap: 20px;
    color: #e5eefb;
  }

  .account-card {
    border-radius: 26px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
      radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 26%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.97));
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
    padding: 24px;
  }

  .account-hero {
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  .account-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
  }

  .account-title-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .account-badges {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .account-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.68);
    color: #f8fbff;
    font-size: 12px;
    letter-spacing: 0.04em;
  }

  .account-badge-label {
    color: #8ea3bf;
    text-transform: uppercase;
    font-size: 10px;
    margin-right: 4px;
  }

  .account-kicker {
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

  .account-hero h2 {
    margin: 10px 0 0;
    font-size: clamp(30px, 4vw, 44px);
    line-height: 1;
    letter-spacing: -0.04em;
    color: #f8fbff;
  }

  .account-hero p,
  .account-card .small {
    color: #8ea3bf;
  }

  .account-overview {
    display: grid;
    gap: 12px;
    align-content: center;
  }

  .account-overview-block {
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.68);
    padding: 16px 18px;
  }

  .account-overview-block span {
    display: block;
    color: #8ea3bf;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 8px;
  }

  .account-overview-block strong {
    display: block;
    color: #f8fbff;
    font-size: 18px;
    line-height: 1.25;
  }

  .account-grid {
    display: block;
  }

  .account-card h3 {
    margin: 0;
    color: #f8fbff;
  }

  .account-copy {
    margin: 8px 0 0;
    color: #8ea3bf;
    line-height: 1.7;
  }

  .account-form-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 18px;
  }

  .account-card .label {
    color: #93a8c6;
    margin-bottom: 6px;
    display: block;
  }

  .account-card .input {
    background: rgba(15, 23, 42, 0.86);
    color: #f8fbff;
    border: 1px solid rgba(148, 163, 184, 0.18);
  }

  .account-card .input::placeholder {
    color: #6f86a6;
  }

  .account-card .btn.btn-outline {
    border-color: rgba(148, 163, 184, 0.2);
    color: #d8e4f5;
    background: rgba(15, 23, 42, 0.55);
  }

  .form-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid rgba(148, 163, 184, 0.12);
  }

  .form-section:first-child {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
  }

  .account-password-grid {
    display: grid;
    gap: 14px;
    margin-top: 18px;
  }

  .account-note strong {
    display: block;
    color: #7dd3fc;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 11px;
  }

  .account-actions {
    margin-top: 18px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  @media (max-width: 1080px) {
    .account-header {
      flex-direction: column;
      align-items: flex-start;
    }
    
    .account-badges {
      order: -1;
    }
  }

  @media (max-width: 760px) {
    .account-card {
      padding: 18px;
      border-radius: 18px;
    }

    .account-form-grid {
      grid-template-columns: 1fr;
    }

    .account-badges {
      width: 100%;
      justify-content: flex-start;
    }
  }
</style>

<div class="account-shell">
  <section class="account-hero">
    <div class="account-card">
      <div class="account-header">
        <div class="account-title-section">
          <h2>My Account</h2>
        </div>
        <div class="account-badges">
          <div class="account-badge">
            <span class="account-badge-label">Role:</span>
            <strong><?= htmlspecialchars(get_role_display_name($account['role'])) ?></strong>
          </div>
          <div class="account-badge">
            <span class="account-badge-label">Workspace:</span>
            <strong><?= htmlspecialchars($account_scope) ?></strong>
          </div>
          <div class="account-badge">
            <span class="account-badge-label">Status:</span>
            <strong><?= !empty($account['is_active']) ? 'Active' : 'Inactive' ?></strong>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if ($err): ?><div class="alert red" style="margin-top:0"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert green" style="margin-top:0"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <section class="account-grid">
    <div class="account-card">
      <!-- Profile Details Section -->
      <div class="form-section">
        <h3>Profile Details</h3>

        <form method="post">
          <?= csrf_field() ?>
          <div class="account-form-grid">
            <div>
              <label class="label">Username</label>
              <input class="input" type="text" name="username" value="<?= htmlspecialchars($account['username']) ?>" required>
            </div>
            <div>
              <label class="label">Full Name</label>
              <input class="input" type="text" name="full_name" value="<?= htmlspecialchars($account['full_name']) ?>" required>
            </div>
            <div>
              <label class="label">Email Address</label>
              <input class="input" type="email" name="email" value="<?= htmlspecialchars($account['email'] ?? '') ?>" placeholder="your-email@example.com">
            </div>
            <div>
              <label class="label">Contact Number</label>
              <input class="input" type="text" name="contact_no" value="<?= htmlspecialchars($account['contact_no'] ?? '') ?>" placeholder="09XXXXXXXXX">
            </div>
          </div>

          <div class="account-actions">
            <button class="btn btn-primary" type="submit" name="save_profile" value="1">Save Profile</button>
            <a class="btn btn-outline" href="<?php echo APP_BASE; ?>/staff/dashboard.php">Back to Dashboard</a>
          </div>
        </form>
      </div>

      <!-- Password & Recovery Section -->
      <div class="form-section">
        <h3>Password & Recovery</h3>

        <form method="post">
          <?= csrf_field() ?>
          <div class="account-password-grid">
            <div>
              <label class="label">Current Password</label>
              <input class="input" type="password" name="current_password" required>
            </div>
            <div>
              <label class="label">New Password</label>
              <input class="input" type="password" name="new_password" required>
            </div>
            <div>
              <label class="label">Confirm New Password</label>
              <input class="input" type="password" name="confirm_password" required>
            </div>
          </div>

          <div class="account-actions">
            <button class="btn btn-primary" type="submit" name="change_password" value="1">Change Password</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
