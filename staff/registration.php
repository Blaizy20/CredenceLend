<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_once __DIR__ . '/../includes/mail_helper.php';

require_login();
require_permission('manage_staff');

$is_super_admin = is_super_admin();
$title = $is_super_admin ? "Register Staff and Tenants" : "Register Staff";
$active = "reg";

$tenant_staff_roles = [
  'MANAGER' => 'Manager',
  'CREDIT_INVESTIGATOR' => 'Credit Investigator',
  'LOAN_OFFICER' => 'Loan Officer',
  'CASHIER' => 'Cashier',
];
$staff_role_options = $is_super_admin
  ? ['ADMIN' => 'Admin Owner'] + $tenant_staff_roles
  : $tenant_staff_roles;

$registration_tenant_id = null;
$can_register_staff = true;
$err = $_SESSION['registration_flash_err'] ?? '';
$ok = $_SESSION['registration_flash_ok'] ?? '';
unset($_SESSION['registration_flash_err'], $_SESSION['registration_flash_ok']);
$reg_type = $_POST['registration_type'] ?? ($_GET['tab'] ?? 'staff');

$redirect_with_flash = function ($tab, $message, $type = 'ok') {
  $_SESSION[$type === 'err' ? 'registration_flash_err' : 'registration_flash_ok'] = $message;
  header("Location: " . APP_BASE . "/staff/registration.php?tab=" . urlencode($tab));
  exit;
};

$send_staff_welcome_email = function (array $account, $setup_token, $tenant_name = '') use ($is_super_admin) {
  $recipient_email = trim((string) ($account['email'] ?? ''));
  if ($recipient_email === '' || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
    return ['attempted' => false, 'ok' => false, 'error' => 'No valid recipient email address.'];
  }

  $role = trim((string) ($account['role'] ?? ''));
  $role_label = get_role_display_name($role);
  $full_name = trim((string) ($account['full_name'] ?? 'Staff User'));
  $username = trim((string) ($account['username'] ?? ''));
  $tenant_name = trim((string) $tenant_name);
  $setup_query = ['token' => $setup_token];
  if (intval($account['tenant_id'] ?? 0) > 0) {
    $setup_query['tenant_id'] = intval($account['tenant_id']);
  }
  $setup_url = app_public_url('/staff/set_password.php', $setup_query);

  $sender_name = 'CredenceLend';
  $sender_email = '';
  $inviter_note_html = '';
  $inviter_note_text = '';
  if (!$is_super_admin) {
    $sender_email = trim((string) ($_SESSION['email'] ?? ''));
    $sender_name = trim((string) ($_SESSION['full_name'] ?? 'CredenceLend'));
    $inviter_tenant_name = '';
    $inviter_tenant_id = intval(current_tenant_id() ?? 0);
    if ($inviter_tenant_id > 0) {
      $inviter_tenant = fetch_one(q(
        "SELECT COALESCE(display_name, tenant_name) AS tenant_name
         FROM tenants
         WHERE tenant_id=?
         LIMIT 1",
        "i",
        [$inviter_tenant_id]
      ));
      $inviter_tenant_name = trim((string) ($inviter_tenant['tenant_name'] ?? ''));
    }
    if ($inviter_tenant_name !== '') {
      $sender_name .= ' - ' . $inviter_tenant_name;
    }
    $inviter_note_html = '<p style="margin:0 0 16px 0;">This invitation was sent by <strong>' . htmlspecialchars($_SESSION['full_name'] ?? 'Administrator') . '</strong>' . ($inviter_tenant_name !== '' ? ' from <strong>' . htmlspecialchars($inviter_tenant_name) . '</strong>' : '') . '.</p>';
    $inviter_note_text = 'This invitation was sent by ' . trim((string) ($_SESSION['full_name'] ?? 'Administrator')) . ($inviter_tenant_name !== '' ? ' from ' . $inviter_tenant_name : '') . '.';
  }

  if ($tenant_name !== '') {
    $tenant_message_html = 'You have been assigned to the tenant <strong>' . htmlspecialchars($tenant_name) . '</strong>.';
    $tenant_message_text = 'Assigned tenant: ' . $tenant_name . '.';
  } elseif ($role === 'ADMIN') {
    $tenant_message_html = 'This owner admin account is ready. Tenant ownership can be assigned separately by the super admin.';
    $tenant_message_text = 'This owner admin account is ready. Tenant ownership can be assigned separately by the super admin.';
  } else {
    $tenant_message_html = 'Your tenant assignment will be finalized by your administrator.';
    $tenant_message_text = 'Your tenant assignment will be finalized by your administrator.';
  }

  $subject = 'Complete your CredenceLend account setup';
  $html_body = '
    <html>
      <body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
        <div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;">
          <div style="padding:24px 28px;background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);color:#ffffff;">
            <div style="font-size:22px;font-weight:700;letter-spacing:0.02em;">CredenceLend</div>
            <div style="margin-top:8px;font-size:14px;opacity:0.88;">Staff account notification</div>
          </div>
          <div style="padding:28px;">
            <p style="margin:0 0 16px 0;">Hello ' . htmlspecialchars($full_name) . ',</p>
            <p style="margin:0 0 16px 0;">An administrator has created your <strong>CredenceLend</strong> staff account.</p>
            ' . $inviter_note_html . '
            <div style="margin:0 0 18px 0;padding:16px 18px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;">
              <p style="margin:0 0 10px 0;"><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
              <p style="margin:0 0 10px 0;"><strong>Role:</strong> ' . htmlspecialchars($role_label) . '</p>
              <p style="margin:0;">' . $tenant_message_html . '</p>
            </div>
            <p style="margin:0 0 20px 0;">Click the button below to create your own password. You must finish this step before you can use your account.</p>
            <p style="margin:0 0 24px 0;">
              <a href="' . htmlspecialchars($setup_url, ENT_QUOTES) . '" style="display:inline-block;padding:12px 20px;background:#1d4ed8;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:700;">Set Your Password</a>
            </p>
            <p style="margin:0 0 10px 0;font-size:13px;color:#475569;">If the button does not open, use this link:</p>
            <p style="margin:0;font-size:13px;word-break:break-all;"><a href="' . htmlspecialchars($setup_url, ENT_QUOTES) . '">' . htmlspecialchars($setup_url) . '</a></p>
          </div>
        </div>
      </body>
    </html>';

  $text_body = implode("\n", [
    'CredenceLend',
    '',
    'Hello ' . $full_name . ',',
    '',
    'An administrator has created your CredenceLend staff account.',
    $inviter_note_text,
    'Username: ' . $username,
    'Role: ' . $role_label,
    $tenant_message_text,
    '',
    'Open the link below to create your own password before signing in.',
    'Set your password: ' . $setup_url,
  ]);

  $mail_options = [
    'from_name' => $sender_name,
    'text_body' => $text_body,
  ];
  if ($sender_email !== '' && filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
    $mail_options['reply_to'] = $sender_email;
  }

  $send_result = app_send_html_mail($recipient_email, $subject, $html_body, $mail_options);

  return [
    'attempted' => true,
    'ok' => !empty($send_result['ok']),
    'error' => $send_result['error'] ?? '',
  ];
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

if (!$is_super_admin) {
  try {
    $registration_tenant_id = require_current_tenant_id();
  } catch (RuntimeException $e) {
    $can_register_staff = false;
    $err = "Select a tenant first before registering staff.";
  }
}

$selected_staff_tenant_id = $is_super_admin
  ? intval($_POST['staff_tenant_id'] ?? 0)
  : intval($registration_tenant_id ?? 0);
$staff_list_tenant_id = $is_super_admin ? intval($_GET['staff_list_tenant_id'] ?? 0) : 0;
$selected_owner_user_id = intval($_POST['owner_user_id'] ?? 0);
$tenant_status_labels = tenant_status_options();
$tenant_plan_labels = tenant_plan_options();
$selected_plan_code = normalize_tenant_plan($_POST['plan_code'] ?? 'BASIC');

$editable_roles = array_keys($staff_role_options);

$find_staff = function ($user_id) use ($is_super_admin, $registration_tenant_id) {
  if ($is_super_admin) {
    return fetch_one(q(
      "SELECT user_id, tenant_id, username, full_name, role, email, is_active
       FROM users
       WHERE user_id=? AND role IN ('ADMIN','MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER')",
      "i",
      [$user_id]
    ));
  }

  return fetch_one(q(
    "SELECT user_id, tenant_id, username, full_name, role, email, is_active
     FROM users
     WHERE user_id=?
       AND tenant_id=?
       AND role IN ('MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER')",
    "ii",
    [$user_id, $registration_tenant_id]
  ));
};

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
  $user_id = intval($_POST['delete_user'] ?? 0);
  if ($user_id <= 0 || $user_id === intval($_SESSION['user_id'] ?? 0)) {
    $err = "Cannot delete this account.";
  } else {
    $staff_to_delete = $find_staff($user_id);
    if (!$staff_to_delete) {
      $err = "Staff account not found.";
    } elseif ($staff_to_delete['role'] === 'ADMIN' && fetch_one(q("SELECT 1 FROM tenant_admins WHERE user_id=? LIMIT 1", "i", [$user_id]))) {
      $err = "Remove this admin from owned tenants before deleting the account.";
    } else {
      q("DELETE FROM users WHERE user_id=?", "i", [$user_id]);
      log_activity('STAFF_DELETED', 'Staff account deleted: ' . $staff_to_delete['full_name']);
      $ok = "Staff account deleted successfully.";
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
  $user_id = intval($_POST['user_id'] ?? 0);
  $full_name = trim($_POST['full_name'] ?? '');
  $role = trim($_POST['role'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($user_id <= 0) {
    $err = "Invalid staff account.";
  } elseif ($full_name === '' || count(array_filter(explode(' ', $full_name))) < 2) {
    $err = "Full name must include first and last name.";
  } elseif (!in_array($role, $editable_roles, true)) {
    $err = "Invalid role.";
  } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = "Invalid email format.";
  } elseif ($password !== '' && !password_is_strong($password)) {
    $err = "Password must be 8+ chars with upper, lower, number, and special.";
  } else {
    $conn = db();
    try {
      $conn->begin_transaction();

      $staff_to_update = $find_staff($user_id);
      if (!$staff_to_update) {
        throw new RuntimeException('Staff account not found.');
      }
      if ($staff_to_update['role'] === 'ADMIN' && $role !== 'ADMIN') {
        throw new RuntimeException('Owner admins must remain ADMIN accounts. Create a new tenant-scoped staff account instead.');
      }
      if ($staff_to_update['role'] !== 'ADMIN' && $role === 'ADMIN') {
        throw new RuntimeException('Promote staff to ADMIN through a dedicated owner account creation flow.');
      }

      $target_tenant_id = intval($staff_to_update['tenant_id'] ?? 0);
      if (!empty($staff_to_update['is_active']) && $target_tenant_id > 0) {
        subscription_lock_tenant_scope($target_tenant_id);
        $role_assignment_check = can_assign_user_to_role(
          $target_tenant_id,
          $role,
          $staff_to_update['role'] ?? '',
          true
        );
        if (!$role_assignment_check['allowed']) {
          throw new RuntimeException($role_assignment_check['message']);
        }
      }

      if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        q("UPDATE users SET full_name=?, role=?, email=?, password_hash=? WHERE user_id=?", "ssssi", [$full_name, $role, $email, $hash, $user_id]);
      } else {
        q("UPDATE users SET full_name=?, role=?, email=? WHERE user_id=?", "sssi", [$full_name, $role, $email, $user_id]);
      }

      $conn->commit();
      log_activity('STAFF_UPDATED', 'Staff account updated: ' . $full_name . ' (' . $role . ')');
      $ok = "Staff account updated.";
    } catch (RuntimeException $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = "Failed to update staff account: " . $e->getMessage();
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_staff'])) {
  $full_name = trim($_POST['full_name'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $role = trim($_POST['role'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $target_tenant_id = $selected_staff_tenant_id;

  if (!$can_register_staff && !$is_super_admin) {
    $err = "Select a tenant first before registering staff.";
  } elseif ($full_name === '' || $username === '') {
    $err = "Please complete all required fields.";
  } elseif (count(array_filter(explode(' ', $full_name))) < 2) {
    $err = "Full name must include first and last name.";
  } elseif (!isset($staff_role_options[$role])) {
    $err = "Invalid role.";
  } elseif ($email === '') {
    $err = "Email address is required so the invitation can be sent.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = "Invalid email format.";
  } elseif ($role !== 'ADMIN' && intval($target_tenant_id) <= 0) {
    $err = "Select the tenant for this staff account.";
  } else {
    $conn = db();
    try {
      $conn->begin_transaction();

      if ($role !== 'ADMIN') {
        subscription_lock_tenant_scope($target_tenant_id);
        $role_assignment_check = can_add_user_to_role($target_tenant_id, $role);
        if (!$role_assignment_check['allowed']) {
          throw new RuntimeException($role_assignment_check['message']);
        }
      }

      $existing = fetch_one(q(
        "SELECT user_id FROM users WHERE LOWER(username)=LOWER(?) FOR UPDATE",
        "s",
        [$username]
      ));

      if ($existing) {
        $conn->rollback();
        $err = "Username already exists.";
      } else {
        $temporary_password = bin2hex(random_bytes(24)) . 'Aa1!';
        $hash = password_hash($temporary_password, PASSWORD_DEFAULT);
        $setup_token = bin2hex(random_bytes(32));
        $setup_ttl_seconds = 604800;

        if ($role === 'ADMIN') {
          q(
            "INSERT INTO users (tenant_id, username, password_hash, full_name, role, email) VALUES (NULL, ?, ?, ?, ?, ?)",
            "sssss",
            [$username, $hash, $full_name, $role, $email]
          );
        } else {
          q(
            "INSERT INTO users (tenant_id, username, password_hash, full_name, role, email) VALUES (?, ?, ?, ?, ?, ?)",
            "isssss",
            [$target_tenant_id, $username, $hash, $full_name, $role, $email]
          );
        }

        $created_user_id = intval($conn->insert_id);
        q(
          "UPDATE users
           SET reset_token=?,
               reset_token_expiry=DATE_ADD(NOW(), INTERVAL ? SECOND)
           WHERE user_id=?",
          "sii",
          [$setup_token, $setup_ttl_seconds, $created_user_id]
        );

        $tenant_name = '';
        if ($role !== 'ADMIN' && intval($target_tenant_id) > 0) {
          $tenant_row = fetch_one(q(
            "SELECT COALESCE(display_name, tenant_name) AS tenant_name
             FROM tenants
             WHERE tenant_id=?
             LIMIT 1",
            "i",
            [$target_tenant_id]
          ));
          $tenant_name = $tenant_row['tenant_name'] ?? '';
        }
        $mail_result = $send_staff_welcome_email([
          'email' => $email,
          'full_name' => $full_name,
          'role' => $role,
          'tenant_id' => $role === 'ADMIN' ? null : intval($target_tenant_id),
          'username' => $username,
        ], $setup_token, $tenant_name);

        $conn->commit();

          if ($role === 'ADMIN') {
              log_system_activity('STAFF_CREATED', 'Admin owner account created: ' . $full_name . ' (' . $role . ')');
          } else {
              log_activity('STAFF_CREATED', 'Staff account created: ' . $full_name . ' (' . $role . ')');
          }
          
          $flash_message = $role === 'ADMIN'
              ? "Admin owner account created. The CredenceLend setup email was sent successfully. This user must create their own password before signing in."
              : "Staff account created successfully. The CredenceLend setup email was sent successfully. This user must create their own password before signing in.";
          
          if (!$mail_result['ok']) {
              error_log('Setup email failed for ' . $email . ': ' . ($mail_result['error'] ?? ''));
              $flash_message = 'Staff account created, but the setup email could not be sent. Please resend manually.';
          }
          
          $redirect_with_flash(
              'staff',
              $flash_message
          );
      }
    } catch (mysqli_sql_exception $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = strpos(strtolower($e->getMessage()), 'duplicate') !== false
        ? "Username already exists."
        : "Registration failed: " . $e->getMessage();
    } catch (Exception $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = "Registration failed: " . $e->getMessage();
    }
  }
}

if ($is_super_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_tenant'])) {
  $tenant_name = trim($_POST['tenant_name'] ?? '');
  $subdomain = trim($_POST['subdomain'] ?? '');
  $plan_code = normalize_tenant_plan($_POST['plan_code'] ?? 'BASIC');

  if ($tenant_name === '') {
    $err = "Tenant name is required.";
  } elseif ($subdomain === '') {
    $err = "Subdomain is required.";
  } elseif (!preg_match('/^[a-z0-9-]+$/i', $subdomain)) {
    $err = "Subdomain may contain only letters, numbers, and hyphens.";
  } elseif (!isset($tenant_plan_labels[$plan_code])) {
    $err = "Invalid tenant plan.";
  } else {
    $conn = db();
    try {
      $conn->begin_transaction();

      $existing_name = fetch_one(q("SELECT tenant_id FROM tenants WHERE LOWER(tenant_name)=LOWER(?) FOR UPDATE", "s", [$tenant_name]));
      $existing_subdomain = fetch_one(q("SELECT tenant_id FROM tenants WHERE LOWER(subdomain)=LOWER(?) FOR UPDATE", "s", [$subdomain]));

      if ($existing_name || $existing_subdomain) {
        $conn->rollback();
        $err = $existing_name ? "Tenant name already exists." : "Subdomain already exists.";
      } else {
        $subscription_started_at = date('Y-m-d H:i:s');
        $subscription_expires_at = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($subscription_started_at)));
        $selected_plan = null;

        if ($selected_owner_user_id > 0) {
          $valid_owner = fetch_one(q(
            "SELECT user_id FROM users WHERE role='ADMIN' AND is_active=1 AND user_id=? LIMIT 1",
            "i",
            [$selected_owner_user_id]
          ));
          if (!$valid_owner) {
            throw new RuntimeException('Only active ADMIN users can be assigned as tenant owner.');
          }
        }

        if (subscription_schema_ready()) {
          $plan_options = subscription_available_plan_options(false);
          $selected_plan = $plan_options[$plan_code] ?? null;

          q(
            "INSERT INTO tenants (
               tenant_name,
               subdomain,
               display_name,
               tenant_status,
               plan_code,
               plan_id,
               subscription_status,
               subscription_started_at,
               subscription_expires_at,
               is_active
             ) VALUES (?, ?, ?, 'PENDING', ?, ?, 'ACTIVE', ?, ?, 0)",
            "ssssiss",
            [
              $tenant_name,
              $subdomain,
              $tenant_name,
              $plan_code,
              intval($selected_plan['plan_id'] ?? 0) ?: null,
              $subscription_started_at,
              $subscription_expires_at,
            ]
          );
        } else {
          q(
            "INSERT INTO tenants (tenant_name, subdomain, display_name, tenant_status, plan_code, is_active) VALUES (?, ?, ?, 'PENDING', ?, 0)",
            "ssss",
            [$tenant_name, $subdomain, $tenant_name, $plan_code]
          );
        }

        $tenant_id = intval($conn->insert_id);
        q(
          "INSERT INTO system_settings (tenant_id, system_name, primary_color) VALUES (?, ?, ?)",
          "iss",
          [$tenant_id, $tenant_name, '#2c3ec5']
        );

        if ($selected_owner_user_id > 0) {
          q(
            "INSERT INTO tenant_admins (tenant_id, user_id, is_primary_owner) VALUES (?, ?, ?)",
            "iii",
            [$tenant_id, $selected_owner_user_id, 1]
          );
        }

        $conn->commit();
        log_tenant_activity(
          $tenant_id,
          'TENANT_CREATED',
          'Tenant created: ' . $tenant_name . ' (' . $subdomain . '). Status: ' . ($tenant_status_labels['PENDING'] ?? 'PENDING') . '. Plan: ' . ($tenant_plan_labels[$plan_code] ?? $plan_code) . '. Subscription start: ' . $subscription_started_at . '. Subscription expiry: ' . $subscription_expires_at . '.'
        );
        $redirect_with_flash(
          'tenant',
          $selected_owner_user_id > 0
            ? "Tenant registered successfully, assigned to its owner admin, and queued for approval."
            : "Tenant registered successfully and queued for approval."
        );
      }
    } catch (RuntimeException $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = "Tenant registration failed: " . $e->getMessage();
    }
  }
}

if ($is_super_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_existing_tenant_owners'])) {
  $tenant_id = intval($_POST['tenant_id'] ?? 0);
  $owner_user_id = intval($_POST['owner_user_id'] ?? 0);

  if ($tenant_id <= 0) {
    $err = "Invalid tenant.";
  } else {
    $conn = db();
    try {
      $conn->begin_transaction();

      $tenant = fetch_one(q(
        "SELECT tenant_id, COALESCE(display_name, tenant_name) AS display_name, subdomain
         FROM tenants
         WHERE tenant_id=?
         LIMIT 1",
        "i",
        [$tenant_id]
      ));
      if (!$tenant) {
        throw new RuntimeException('Tenant not found.');
      }

      $previous_owner = fetch_one(q(
        "SELECT u.user_id, u.full_name
         FROM tenant_admins ta
         JOIN users u ON u.user_id = ta.user_id
         WHERE ta.tenant_id=?
         ORDER BY ta.is_primary_owner DESC, ta.id ASC
         LIMIT 1",
        "i",
        [$tenant_id]
      ));
      $valid_owner = null;

      if ($owner_user_id > 0) {
        $valid_owner = fetch_one(q(
          "SELECT user_id, full_name FROM users WHERE role='ADMIN' AND is_active=1 AND user_id=? LIMIT 1",
          "i",
          [$owner_user_id]
        ));
        if (!$valid_owner) {
          throw new RuntimeException('Only active ADMIN users can be assigned as tenant owner.');
        }

        $previous_owner_id = intval($previous_owner['user_id'] ?? 0);
        if ($previous_owner_id <= 0 || $previous_owner_id !== $owner_user_id) {
          subscription_lock_tenant_scope($tenant_id);
          $owner_assignment_check = $previous_owner_id > 0
            ? can_assign_user_to_role($tenant_id, 'ADMIN', 'ADMIN', true)
            : can_add_user_to_role($tenant_id, 'ADMIN');
          if (!$owner_assignment_check['allowed']) {
            throw new RuntimeException($owner_assignment_check['message']);
          }
        }
      }

      q("DELETE FROM tenant_admins WHERE tenant_id=?", "i", [$tenant_id]);
      if ($owner_user_id > 0) {
        q(
          "INSERT INTO tenant_admins (tenant_id, user_id, is_primary_owner) VALUES (?, ?, ?)",
          "iii",
          [$tenant_id, $owner_user_id, 1]
        );
      }

      $conn->commit();
      $updated_owner_name = $valid_owner['full_name'] ?? 'No owner assigned';
      $previous_owner_name = $previous_owner['full_name'] ?? 'No owner assigned';
      log_tenant_activity(
        $tenant_id,
        'TENANT_UPDATED',
        'Tenant owner updated for ' . $tenant['display_name'] . ' (' . $tenant['subdomain'] . '). Previous owner: ' . $previous_owner_name . '. New owner: ' . $updated_owner_name . '.'
      );
      $redirect_with_flash('tenant', 'Tenant owners updated successfully.');
    } catch (RuntimeException $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = "Failed to update tenant owners: " . $e->getMessage();
    }
  }
}

$available_tenants = fetch_all(q(
  "SELECT tenant_id, COALESCE(display_name, tenant_name) AS tenant_name
   FROM tenants
   WHERE is_active=1
   ORDER BY COALESCE(display_name, tenant_name) ASC"
));
$tenant_lookup = [];
foreach ($available_tenants as $tenant_row) {
  $tenant_lookup[intval($tenant_row['tenant_id'])] = $tenant_row['tenant_name'];
}

$admin_owner_candidates = $is_super_admin ? fetch_all(q(
  "SELECT user_id, username, full_name, email
   FROM users
   WHERE role='ADMIN' AND is_active=1
   ORDER BY full_name ASC"
)) : [];

$staff_query = "SELECT
                  u.user_id,
                  u.username,
                  u.full_name,
                  u.role,
                  u.email,
                  u.is_active,
                  u.tenant_id,
                  COALESCE(t.display_name, t.tenant_name) AS tenant_name
                FROM users u
                LEFT JOIN tenants t ON t.tenant_id = u.tenant_id
                WHERE u.role IN ('ADMIN','MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER')";
$staff_types = "";
$staff_params = [];

if (!$is_super_admin) {
  $staff_query .= " AND u.tenant_id=? AND u.role IN ('MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER')";
  $staff_types = "i";
  $staff_params[] = $registration_tenant_id;
} elseif ($staff_list_tenant_id > 0) {
  $staff_query .= " AND u.tenant_id=?";
  $staff_types = "i";
  $staff_params[] = $staff_list_tenant_id;
}

$staff_query .= " ORDER BY FIELD(u.role, 'ADMIN','MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER'), u.full_name ASC";
$staff = fetch_all(q($staff_query, $staff_types, $staff_params));

$tenants = $is_super_admin ? fetch_all(q(
  "SELECT
      t.tenant_id,
      COALESCE(t.display_name, t.tenant_name) AS tenant_name,
      t.subdomain,
      t.tenant_status,
      t.plan_code,
      t.is_active,
      t.created_at,
      MAX(CASE WHEN ta.is_primary_owner = 1 THEN u.full_name END) AS owners
   FROM tenants t
   LEFT JOIN tenant_admins ta ON ta.tenant_id = t.tenant_id
   LEFT JOIN users u ON u.user_id = ta.user_id
   GROUP BY t.tenant_id, t.tenant_name, t.display_name, t.subdomain, t.tenant_status, t.plan_code, t.is_active, t.created_at
   ORDER BY t.created_at DESC"
)) : [];

$tenant_owner_map = [];
if ($is_super_admin && !empty($tenants)) {
  $tenant_owner_rows = fetch_all(q(
    "SELECT tenant_id, user_id
     FROM tenant_admins
     ORDER BY tenant_id ASC, is_primary_owner DESC, id ASC"
  ));
  foreach ($tenant_owner_rows as $owner_row) {
    $tenant_owner_map[intval($owner_row['tenant_id'])] = intval($owner_row['user_id']);
  }
}

$subscription_role_labels = subscription_billable_role_labels();
$subscription_role_limit_map = subscription_role_limit_map();
$registration_usage_by_tenant = [];
$registration_usage_tenant_ids = [];

if (!$is_super_admin && intval($registration_tenant_id ?? 0) > 0) {
  $registration_usage_tenant_ids[] = intval($registration_tenant_id);
}

if ($is_super_admin && !empty($available_tenants)) {
  foreach ($available_tenants as $tenant_row) {
    $tenant_id = intval($tenant_row['tenant_id'] ?? 0);
    if ($tenant_id > 0) {
      $registration_usage_tenant_ids[] = $tenant_id;
    }
  }
}

$registration_usage_tenant_ids = array_values(array_unique(array_filter($registration_usage_tenant_ids)));

foreach ($registration_usage_tenant_ids as $usage_tenant_id) {
  $plan = get_tenant_plan($usage_tenant_id);
  $subscription = get_tenant_subscription_status($usage_tenant_id);
  $counts = get_tenant_user_counts($usage_tenant_id);

  if (!$plan) {
    continue;
  }

  $role_progress = [];
  foreach ($subscription_role_limit_map as $role_key => $limit_column) {
    $limit_value = intval($plan[$limit_column] ?? 0);
    $used_value = intval($counts['by_role'][$role_key] ?? 0);
    $progress_pct = $limit_value > 0 ? min(100, (int) round(($used_value / max(1, $limit_value)) * 100)) : 0;
    $role_progress[] = [
      'role_key' => $role_key,
      'label' => $subscription_role_labels[$role_key] ?? $role_key,
      'used' => $used_value,
      'limit' => subscription_plan_limit_label($plan, $limit_column),
      'limit_value' => $limit_value,
      'bar_width' => $progress_pct,
    ];
  }

  $total_limit_value = intval($plan['max_total_users'] ?? 0);
  $total_used_value = intval($counts['total_users'] ?? 0);
  $registration_usage_by_tenant[$usage_tenant_id] = [
    'tenant_id' => $usage_tenant_id,
    'tenant_name' => trim((string) ($plan['display_name'] ?? $plan['tenant_name'] ?? 'Current Tenant')),
    'plan_name' => subscription_plan_name($plan),
    'subscription_status' => trim((string) ($subscription['label'] ?? 'Unavailable')),
    'subscription_message' => trim((string) ($subscription['message'] ?? '')),
    'total' => [
      'used' => $total_used_value,
      'limit' => subscription_plan_limit_label($plan, 'max_total_users', 'Unlimited'),
      'limit_value' => $total_limit_value,
      'bar_width' => $total_limit_value > 0 ? min(100, (int) round(($total_used_value / max(1, $total_limit_value)) * 100)) : 0,
    ],
    'roles' => $role_progress,
  ];
}

$default_registration_usage_tenant_id = 0;
if ($is_super_admin) {
  if ($selected_staff_tenant_id > 0) {
    $default_registration_usage_tenant_id = $selected_staff_tenant_id;
  } elseif ($staff_list_tenant_id > 0) {
    $default_registration_usage_tenant_id = $staff_list_tenant_id;
  } elseif (!empty($registration_usage_tenant_ids)) {
    $default_registration_usage_tenant_id = intval($registration_usage_tenant_ids[0]);
  }
}

$current_registration_usage_tenant_id = $is_super_admin
  ? $default_registration_usage_tenant_id
  : intval($registration_tenant_id ?? 0);
$current_registration_usage = $registration_usage_by_tenant[$current_registration_usage_tenant_id] ?? null;

include __DIR__ . '/_layout_top.php';
?>
<style>
  body { background: radial-gradient(circle at top, rgba(14, 165, 233, 0.12), transparent 30%), linear-gradient(180deg, #020617 0%, #081121 42%, #0f172a 100%); color: #e5eefb; }
  .topbar { background: linear-gradient(135deg, #081121, #0f1b35) !important; border-bottom: 1px solid rgba(148, 163, 184, 0.14); box-shadow: 0 18px 40px rgba(2, 6, 23, 0.35); }
  .topbar .small, .topbar a.btn.btn-outline { color: #d8e4f5 !important; border-color: rgba(148, 163, 184, 0.24) !important; }
  .layout, .main { background: transparent; }
  .sidebar { background: rgba(4, 10, 24, 0.84); border-right: 1px solid rgba(148, 163, 184, 0.12); backdrop-filter: blur(16px); }
  .sidebar h3 { color: #7f93b0; }
  .sidebar a { color: #d7e3f4; }
  .sidebar a.active, .sidebar a:hover { background: linear-gradient(135deg, rgba(14, 165, 233, 0.18), rgba(59, 130, 246, 0.2)); color: #f8fbff; }
  .reg-shell { display: grid; gap: 20px; color: #e5eefb; }
  .reg-card, .reg-modal-card { border-radius: 26px; border: 1px solid rgba(148, 163, 184, 0.16); background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 26%), linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.97)); box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34); padding: 24px; }
  .reg-card h2, .reg-card h3, .reg-modal-card h3 { color: #f8fbff; }
  .reg-card .label, .reg-modal-card .label { color: #93a8c6; }
  .reg-card .small, .reg-modal-card .small { color: #8ea3bf; }
  .reg-card .input, .reg-modal-card .input { background: rgba(15, 23, 42, 0.86); color: #f8fbff; border: 1px solid rgba(148, 163, 184, 0.18); }
  .reg-card .input::placeholder, .reg-modal-card .input::placeholder { color: #6f86a6; }
  .reg-card .btn.btn-ghost, .reg-card .btn.btn-outline, .reg-modal-card .btn.btn-outline { border-color: rgba(148, 163, 184, 0.2); color: #d8e4f5; background: rgba(15, 23, 42, 0.55); }
  .reg-highlight-btn {
    border: 1px solid rgba(125, 211, 252, 0.36) !important;
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.24), rgba(37, 99, 235, 0.28)) !important;
    color: #f8fbff !important;
    box-shadow: 0 0 0 1px rgba(125, 211, 252, 0.12), 0 16px 30px rgba(14, 165, 233, 0.18);
    font-weight: 700;
  }
  .reg-highlight-btn:hover,
  .reg-highlight-btn:focus {
    border-color: rgba(125, 211, 252, 0.52) !important;
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.34), rgba(37, 99, 235, 0.4)) !important;
    color: #ffffff !important;
    text-decoration: none;
    transform: translateY(-1px);
  }
  .reg-management-grid { display: grid; gap: 20px; margin-top: 20px; }
  .reg-management-grid.has-two-columns { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: start; }
  .reg-pane { min-width: 0; }
  .reg-management-grid.has-two-columns .reg-pane { display: flex; }
  .reg-management-grid.has-two-columns .reg-pane-card { min-height: 480px; }
  .reg-pane-card { height: 100%; width: 100%; border-radius: 22px; border: 1px solid rgba(148, 163, 184, 0.14); background: rgba(15, 23, 42, 0.44); padding: 22px; }
  .reg-section-head { margin-top: 30px; border-bottom: 1px solid rgba(148, 163, 184, 0.14); padding-bottom: 12px; }
  .reg-subhead { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; border-bottom: 1px solid rgba(148, 163, 184, 0.14); padding-bottom: 12px; }
  .reg-subhead-copy { min-width: 0; }
  .reg-usage-panel { margin-top: 18px; padding: 18px; border-radius: 20px; border: 1px solid rgba(96, 165, 250, 0.18); background: linear-gradient(180deg, rgba(15, 23, 42, 0.82), rgba(8, 15, 30, 0.9)); }
  .reg-usage-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
  .reg-usage-head h4 { margin: 0; color: #f8fbff; font-size: 16px; }
  .reg-usage-kicker { color: #7dd3fc; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; }
  .reg-usage-meta { display: grid; gap: 4px; }
  .reg-usage-select { min-width: 220px; max-width: 320px; }
  .reg-usage-select .label { display: block; margin-bottom: 6px; color: #93a8c6; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; }
  .reg-usage-plan { color: #dbeafe; font-size: 13px; }
  .reg-usage-status { color: #93a8c6; font-size: 12px; }
  .reg-usage-note { margin-top: 6px; color: #8ea3bf; font-size: 12px; line-height: 1.55; }
  .reg-usage-stack { display: grid; gap: 12px; }
  .reg-usage-row { display: grid; gap: 8px; }
  .reg-usage-row-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
  .reg-usage-row-label { color: #dbe6f6; font-size: 13px; font-weight: 600; }
  .reg-usage-row-value { color: #f8fbff; font-size: 12px; font-weight: 700; }
  .reg-usage-bar { width: 100%; height: 10px; border-radius: 999px; overflow: hidden; background: rgba(51, 65, 85, 0.8); border: 1px solid rgba(148, 163, 184, 0.12); }
  .reg-usage-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, rgba(56, 189, 248, 0.92), rgba(37, 99, 235, 0.92)); }
  .reg-usage-empty { margin-top: 18px; padding: 16px 18px; border-radius: 18px; border: 1px dashed rgba(148, 163, 184, 0.24); color: #8ea3bf; background: rgba(15, 23, 42, 0.36); }
  .reg-table-wrap { overflow: auto; margin-top: 15px; border-radius: 20px; border: 1px solid rgba(148, 163, 184, 0.12); }
  .reg-card .table { margin: 0; width: 100%; color: #e5eefb; background: transparent; }
  .reg-card .table th { background: rgba(15, 23, 42, 0.96); color: #93a8c6; text-transform: uppercase; letter-spacing: 0.08em; font-size: 11px; border-bottom: 1px solid rgba(148, 163, 184, 0.16); }
  .reg-card .table td, .reg-card .table th { border-color: rgba(148, 163, 184, 0.1); padding: 14px 16px; vertical-align: middle; }
  .reg-card .table tbody tr:nth-child(odd) { background: rgba(15, 23, 42, 0.48); }
  .reg-inline-note { color: #8ea3bf; }
  .tenant-owner-row { background: rgba(15, 23, 42, 0.36); }
  .reg-password-wrap { position: relative; }
  .reg-password-wrap .input { padding-right: 48px; }
  .reg-password-toggle { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border: 0; background: transparent; color: #8ea3bf; cursor: pointer; }
  .reg-password-toggle:hover { color: #f8fbff; }
  .reg-modal { display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, 0.74); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
  .reg-modal-card { max-width: 500px; width: 90%; }
  .reg-modal-card.reg-modal-wide { max-width: min(1180px, 94vw); width: 100%; max-height: 90vh; overflow: auto; }
  @media (max-width: 980px) {
    .reg-management-grid.has-two-columns { grid-template-columns: 1fr; }
    .reg-management-grid.has-two-columns .reg-pane { display: block; }
    .reg-management-grid.has-two-columns .reg-pane-card { min-height: 0; }
  }
  @media (max-width: 760px) {
    .reg-card, .reg-modal-card, .reg-pane-card { padding: 18px; border-radius: 18px; }
    .reg-subhead { flex-direction: column; }
  }
</style>

<div class="reg-shell">
<div class="reg-card">
  <h2 style="margin:0 0 10px 0"><?= htmlspecialchars($title) ?></h2>

  <?php if ($err): ?><div class="alert red" style="margin-top:12px"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert green" style="margin-top:12px"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <div class="reg-management-grid<?= $is_super_admin ? ' has-two-columns' : '' ?>">
  <div id="staff-tab" class="reg-pane" style="display:block">
    <div class="reg-pane-card">
    <div class="reg-subhead">
      <div class="reg-subhead-copy">
      <h3 style="margin:0 0 10px 0">Register New Staff Account</h3>
      <div class="small">
        <?php if ($is_super_admin): ?>
          `ADMIN` accounts are owner accounts and are assigned to tenants through tenant ownership, not `users.tenant_id`.
        <?php else: ?>
          New staff are created inside your currently selected tenant only.
        <?php endif; ?>
      </div>
      </div>
      <button class="btn btn-outline reg-highlight-btn" type="button" onclick="openListModal('staffListModal')">View Staff Accounts</button>
    </div>

    <?php if (!$can_register_staff && !$is_super_admin): ?>
      <div class="alert red" style="margin-top:15px">Select a tenant first before registering staff.</div>
    <?php else: ?>
      <div class="reg-usage-panel" id="staff-usage-panel">
        <div class="reg-usage-head">
          <div class="reg-usage-meta">
            <div class="reg-usage-kicker">Subscription Usage</div>
            <h4 id="staff-usage-title"><?= htmlspecialchars($current_registration_usage['tenant_name'] ?? 'Select a tenant to view usage') ?></h4>
            <div class="reg-usage-plan" id="staff-usage-plan"><?= $current_registration_usage ? htmlspecialchars(($current_registration_usage['plan_name'] ?? 'Unknown') . ' plan') : '' ?></div>
            <div class="reg-usage-status" id="staff-usage-status"><?= $current_registration_usage ? htmlspecialchars('Subscription: ' . ($current_registration_usage['subscription_status'] ?? 'Unavailable')) : '' ?></div>
          </div>
          <?php if ($is_super_admin && !empty($available_tenants)): ?>
            <div class="reg-usage-select">
              <label class="label" for="staff-usage-tenant-select">Usage Preview Tenant</label>
              <select class="input" id="staff-usage-tenant-select">
                <option value="">Select tenant</option>
                <?php foreach ($available_tenants as $tenant_row): ?>
                  <option value="<?= intval($tenant_row['tenant_id']) ?>" <?= $current_registration_usage_tenant_id === intval($tenant_row['tenant_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tenant_row['tenant_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
        </div>
        <div class="reg-usage-note" id="staff-usage-note">
          <?= htmlspecialchars($current_registration_usage['subscription_message'] ?? ($is_super_admin ? 'Choose a tenant to preview seat usage for the selected subscription plan.' : 'Seat usage is based on active billable users for your current tenant.')) ?>
        </div>

        <div class="reg-usage-stack" id="staff-usage-stack"<?= $current_registration_usage ? '' : ' hidden' ?>>
          <div class="reg-usage-row">
            <div class="reg-usage-row-top">
              <div class="reg-usage-row-label">Total Billable Users</div>
              <div class="reg-usage-row-value" id="usage-total-value"><?= $current_registration_usage ? htmlspecialchars($current_registration_usage['total']['used'] . ' / ' . $current_registration_usage['total']['limit']) : '' ?></div>
            </div>
            <div class="reg-usage-bar">
              <div class="reg-usage-bar-fill" id="usage-total-fill" style="width:<?= intval($current_registration_usage['total']['bar_width'] ?? 0) ?>%"></div>
            </div>
          </div>

          <?php foreach ($subscription_role_limit_map as $role_key => $limit_column): ?>
            <?php
              $role_item = null;
              if ($current_registration_usage) {
                foreach (($current_registration_usage['roles'] ?? []) as $candidate_role_item) {
                  if (($candidate_role_item['role_key'] ?? '') === $role_key) {
                    $role_item = $candidate_role_item;
                    break;
                  }
                }
              }
            ?>
            <div class="reg-usage-row">
              <div class="reg-usage-row-top">
                <div class="reg-usage-row-label"><?= htmlspecialchars($subscription_role_labels[$role_key] ?? $role_key) ?></div>
                <div class="reg-usage-row-value" id="usage-role-value-<?= htmlspecialchars(strtolower($role_key)) ?>"><?= $role_item ? htmlspecialchars($role_item['used'] . ' / ' . $role_item['limit']) : '' ?></div>
              </div>
              <div class="reg-usage-bar">
                <div class="reg-usage-bar-fill" id="usage-role-fill-<?= htmlspecialchars(strtolower($role_key)) ?>" style="width:<?= intval($role_item['bar_width'] ?? 0) ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="reg-usage-empty" id="staff-usage-empty"<?= $current_registration_usage ? ' hidden' : '' ?>>
          <?= $is_super_admin ? 'Select a tenant for tenant-scoped staff to load the current seat usage and role limits.' : 'Seat usage is unavailable for the current tenant.' ?>
        </div>
      </div>

      <form method="post" style="margin-top:15px">
        <?= csrf_field() ?>
        <input type="hidden" name="registration_type" value="staff">

        <div class="grid2">
          <div>
            <label class="label">Full Name *</label>
            <input class="input" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
          </div>
          <div>
            <label class="label">Username *</label>
            <input class="input" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
        </div>

        <div class="grid2" style="margin-top:10px">
          <div>
            <label class="label">Role *</label>
            <select class="input" name="role" id="staff-role" required onchange="toggleStaffTenantSelect()">
              <?php foreach ($staff_role_options as $role_key => $role_label): ?>
                <option value="<?= htmlspecialchars($role_key) ?>" <?= ($role_key === ($_POST['role'] ?? '')) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($role_label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label">Email *</label>
            <input class="input" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>

        <?php if ($is_super_admin): ?>
          <div id="staff-tenant-select" style="margin-top:10px;display:<?= (($_POST['role'] ?? '') === 'ADMIN') ? 'none' : 'block' ?>">
            <label class="label">Tenant for Tenant-Scoped Staff *</label>
            <select class="input" name="staff_tenant_id">
              <option value="">Select tenant</option>
              <?php foreach ($available_tenants as $tenant_row): ?>
                <option value="<?= intval($tenant_row['tenant_id']) ?>" <?= intval($selected_staff_tenant_id) === intval($tenant_row['tenant_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($tenant_row['tenant_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="small" style="margin-top:6px">Leave tenant assignment to tenant ownership when creating an `ADMIN` owner account.</div>
          </div>
        <?php endif; ?>

        <div class="small" style="margin-top:12px">
          A setup email will be sent to this address. The user will create their own password from that email before they can access the system.
        </div>

        <div style="margin-top:14px;display:flex;gap:10px;align-items:center">
          <button class="btn btn-primary" type="submit" name="register_staff" value="1">Register Staff</button>
          <a class="btn btn-ghost" href="<?php echo APP_BASE; ?>/staff/registration.php">Reset</a>
        </div>
      </form>
    <?php endif; ?>
    </div>
  </div>

  <?php if ($is_super_admin): ?>
    <div id="tenant-tab" class="reg-pane" style="display:block">
      <div class="reg-pane-card">
      <div class="reg-subhead">
        <div class="reg-subhead-copy">
        <h3 style="margin:0 0 10px 0">Register New Tenant</h3>
        <div class="small">Only `SUPER_ADMIN` can create tenants. Assign exactly one owner admin during creation if needed.</div>
        </div>
        <button class="btn btn-outline reg-highlight-btn" type="button" onclick="openListModal('tenantListModal')">View Registered Tenants</button>
      </div>

      <form method="post" style="margin-top:15px;max-width:720px">
        <?= csrf_field() ?>
        <input type="hidden" name="registration_type" value="tenant">

        <div class="grid2">
          <div>
            <label class="label">Tenant Name *</label>
            <input class="input" type="text" name="tenant_name" value="<?= htmlspecialchars($_POST['tenant_name'] ?? '') ?>" required>
          </div>
          <div>
            <label class="label">Subdomain *</label>
            <input class="input" type="text" name="subdomain" value="<?= htmlspecialchars($_POST['subdomain'] ?? '') ?>" required>
          </div>
        </div>

        <div style="margin-top:14px">
          <label class="label">Plan *</label>
          <select class="input" name="plan_code" required>
            <?php foreach ($tenant_plan_labels as $plan_key => $plan_label): ?>
              <option value="<?= htmlspecialchars($plan_key) ?>" <?= $plan_key === $selected_plan_code ? 'selected' : '' ?>>
                <?= htmlspecialchars($plan_label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="small" style="margin-top:6px">New tenants are created as `PENDING` and must be approved from tenant management before they become active.</div>
        </div>

        <div style="margin-top:14px">
          <label class="label">Assign Owner Admin</label>
          <div class="small" style="margin-bottom:10px">Choose one `ADMIN` user to own this tenant, or leave it unassigned for now.</div>
          <select class="input" name="owner_user_id">
            <option value="">No owner yet</option>
            <?php foreach ($admin_owner_candidates as $owner): ?>
              <option value="<?= intval($owner['user_id']) ?>" <?= intval($owner['user_id']) === $selected_owner_user_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($owner['full_name']) ?><?= !empty($owner['username']) ? ' (' . htmlspecialchars($owner['username']) . ')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($admin_owner_candidates)): ?>
            <div class="small" style="margin-top:10px">No active `ADMIN` owner accounts exist yet. Create one from the staff tab first.</div>
          <?php endif; ?>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px">
          <button class="btn btn-primary" type="submit" name="register_tenant" value="1">Register Tenant</button>
          <a class="btn btn-ghost" href="<?php echo APP_BASE; ?>/staff/registration.php">Reset</a>
        </div>
      </form>
      </div>
    </div>
  <?php endif; ?>
  </div>
</div>
</div>

<div id="staffListModal" class="reg-modal">
  <div class="reg-modal-card reg-modal-wide">
    <div class="reg-subhead">
      <h3 style="margin:0 0 10px 0">Staff Accounts</h3>
      <div class="small">Review, edit, and delete staff accounts from this list.</div>
    </div>

    <?php if ($is_super_admin): ?>
      <form method="get" style="margin-top:14px;display:flex;gap:12px;align-items:end;flex-wrap:wrap">
        <input type="hidden" name="tab" value="staff">
        <div style="min-width:260px">
          <label class="label">Tenant Filter</label>
          <select class="input" name="staff_list_tenant_id" onchange="this.form.submit()">
            <option value="">All tenant staff accounts</option>
            <?php foreach ($available_tenants as $tenant_row): ?>
              <option value="<?= intval($tenant_row['tenant_id']) ?>" <?= $staff_list_tenant_id === intval($tenant_row['tenant_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($tenant_row['tenant_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <noscript><button class="btn btn-primary" type="submit">Apply</button></noscript>
      </form>
    <?php endif; ?>

    <div class="reg-table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Full Name</th>
            <?php if ($is_super_admin): ?><th>Tenant</th><?php endif; ?>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($staff as $staff_row): ?>
            <tr>
              <td><?= htmlspecialchars($staff_row['username']) ?></td>
              <td><?= htmlspecialchars($staff_row['full_name']) ?></td>
              <?php if ($is_super_admin): ?><td><?= htmlspecialchars($staff_row['tenant_name'] ?? 'Owner account') ?></td><?php endif; ?>
              <td><?= htmlspecialchars($staff_row['email'] ?? '-') ?></td>
              <td><?= htmlspecialchars(str_replace('_', ' ', $staff_row['role'])) ?></td>
              <td><span class="badge <?= $staff_row['is_active'] ? 'green' : 'red' ?>"><?= $staff_row['is_active'] ? 'Active' : 'Inactive' ?></span></td>
              <td>
                <a class="btn btn-primary" href="#" onclick="editStaff(<?= intval($staff_row['user_id']) ?>, '<?= htmlspecialchars($staff_row['full_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($staff_row['role'], ENT_QUOTES) ?>', '<?= htmlspecialchars($staff_row['email'] ?? '', ENT_QUOTES) ?>'); return false;">Edit</a>
                <?php if ($staff_row['user_id'] !== intval($_SESSION['user_id'] ?? 0)): ?>
                  <form style="display:inline" method="post" onsubmit="return confirm('Permanently delete this account?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="delete_user" value="<?= intval($staff_row['user_id']) ?>">
                    <button class="btn btn-primary" type="submit">Delete</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($staff)): ?>
            <tr><td colspan="<?= $is_super_admin ? '7' : '6' ?>" class="small">No staff accounts found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-outline" type="button" onclick="closeListModal('staffListModal')">Close</button>
    </div>
  </div>
</div>

<?php if ($is_super_admin): ?>
<div id="tenantListModal" class="reg-modal">
  <div class="reg-modal-card reg-modal-wide">
    <div class="reg-subhead">
      <h3 style="margin:0 0 10px 0">Registered Tenants</h3>
      <div class="small">Review tenants, open tenant management, and update owner assignments here.</div>
    </div>

    <div class="reg-table-wrap">
    <table class="table" style="margin-top:15px">
      <thead>
        <tr>
          <th>Tenant Name</th>
          <th>Subdomain</th>
          <th>Plan</th>
          <th>Owners</th>
          <th>Status</th>
          <th>Registered</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tenants as $tenant_row): ?>
          <?php $tenant_id = intval($tenant_row['tenant_id']); ?>
          <?php $current_owner_id = intval($tenant_owner_map[$tenant_id] ?? 0); ?>
          <?php $tenant_status = normalize_tenant_status($tenant_row['tenant_status'] ?? ''); ?>
          <?php $tenant_plan = normalize_tenant_plan($tenant_row['plan_code'] ?? ''); ?>
          <tr>
            <td><?= htmlspecialchars($tenant_row['tenant_name']) ?></td>
            <td><?= htmlspecialchars($tenant_row['subdomain']) ?></td>
            <td><?= htmlspecialchars($tenant_plan_labels[$tenant_plan] ?? $tenant_plan) ?></td>
            <td><?= htmlspecialchars($tenant_row['owners'] ?: 'No owner assigned') ?></td>
            <td><span class="badge <?= tenant_status_badge_class($tenant_status) ?>"><?= htmlspecialchars($tenant_status_labels[$tenant_status] ?? $tenant_status) ?></span></td>
            <td><?= htmlspecialchars($tenant_row['created_at']) ?></td>
            <td>
              <a class="btn btn-outline" href="<?php echo APP_BASE; ?>/staff/tenant_management.php?view=<?= intval($tenant_row['tenant_id']) ?>">
                Manage Tenant
              </a>
            </td>
          </tr>
          <tr>
            <td colspan="7" class="tenant-owner-row">
              <form method="post" style="margin:0;padding:10px 0">
                <?= csrf_field() ?>
                <input type="hidden" name="registration_type" value="tenant">
                <input type="hidden" name="tenant_id" value="<?= $tenant_id ?>">
                <input type="hidden" name="update_existing_tenant_owners" value="1">
                <div class="small" style="margin-bottom:8px">
                  Assign exactly one owner admin here, or clear the owner assignment.
                </div>
                <select class="input" name="owner_user_id">
                  <option value="">No owner assigned</option>
                  <?php foreach ($admin_owner_candidates as $owner): ?>
                    <?php $owner_user_id = intval($owner['user_id']); ?>
                    <option value="<?= $owner_user_id ?>" <?= $owner_user_id === $current_owner_id ? 'selected' : '' ?>>
                      <?= htmlspecialchars($owner['full_name']) ?><?= !empty($owner['username']) ? ' (' . htmlspecialchars($owner['username']) . ')' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (empty($admin_owner_candidates)): ?>
                  <div class="small" style="margin-top:10px">No active `ADMIN` owner accounts exist yet. Create one from the staff tab first.</div>
                <?php endif; ?>
                <div style="margin-top:12px">
                  <button class="btn btn-primary" type="submit">Save Owner Assignment</button>
                </div>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($tenants)): ?>
          <tr><td colspan="7" class="small" style="text-align:center;padding:20px">No tenants registered yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>

    <div style="margin-top:14px;display:flex;gap:10px;justify-content:flex-end">
      <button class="btn btn-outline" type="button" onclick="closeListModal('tenantListModal')">Close</button>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="editModal" class="reg-modal">
  <div class="reg-modal-card">
    <h3 style="margin-top:0">Edit Staff Account</h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" id="edit_user_id" name="user_id">
      <input type="hidden" name="registration_type" value="staff">
      <div>
        <label class="label">Full Name</label>
        <input class="input" id="edit_full_name" name="full_name" required>
      </div>
      <div style="margin-top:10px">
        <label class="label">Email</label>
        <input class="input" type="email" id="edit_email" name="email">
      </div>
      <div style="margin-top:10px">
        <label class="label">Role</label>
        <select class="input" id="edit_role" name="role" required>
          <?php foreach ($staff_role_options as $role_key => $role_label): ?>
            <option value="<?= htmlspecialchars($role_key) ?>"><?= htmlspecialchars($role_label) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="small" style="margin-top:6px">
          Changing between owner `ADMIN` and tenant-scoped staff is intentionally blocked here.
        </div>
      </div>
      <div style="margin-top:10px">
        <label class="label">New Password (leave blank to keep current)</label>
        <input class="input" type="password" name="password" id="edit_password">
      </div>
      <div style="margin-top:14px;display:flex;gap:10px">
        <button class="btn btn-primary" type="submit" name="update_user" value="1">Update</button>
        <button class="btn btn-outline" type="button" onclick="closeEdit()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
const registrationUsageByTenant = <?= json_encode($registration_usage_by_tenant, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

function syncUsageTenantSelect(tenantId) {
  const usageTenantSelect = document.getElementById('staff-usage-tenant-select');
  if (!usageTenantSelect) return;
  usageTenantSelect.value = tenantId ? String(tenantId) : '';
}

function openListModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.style.display = 'flex';
}

function closeListModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.style.display = 'none';
}

function togglePasswordField(id, button) {
  const input = document.getElementById(id);
  if (!input || !button) return;
  const icon = button.querySelector('i');
  const showing = input.type === 'text';
  input.type = showing ? 'password' : 'text';
  button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
  if (icon) {
    icon.className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
  }
}

function toggleStaffTenantSelect() {
  const role = document.getElementById('staff-role');
  const container = document.getElementById('staff-tenant-select');
  const tenantSelect = container ? container.querySelector('select[name="staff_tenant_id"]') : null;
  if (!role || !container) return;
  const tenantScoped = role.value !== 'ADMIN';
  container.style.display = tenantScoped ? 'block' : 'none';
  if (tenantScoped && tenantSelect && tenantSelect.value) {
    syncUsageTenantSelect(tenantSelect.value);
    updateStaffUsagePanel(tenantSelect.value);
    return;
  }

  const usageTenantSelect = document.getElementById('staff-usage-tenant-select');
  updateStaffUsagePanel(usageTenantSelect ? usageTenantSelect.value : '');
}

function updateStaffUsagePanel(tenantId) {
  const title = document.getElementById('staff-usage-title');
  const plan = document.getElementById('staff-usage-plan');
  const status = document.getElementById('staff-usage-status');
  const note = document.getElementById('staff-usage-note');
  const stack = document.getElementById('staff-usage-stack');
  const empty = document.getElementById('staff-usage-empty');
  const usage = registrationUsageByTenant[String(tenantId)] || registrationUsageByTenant[tenantId] || null;

  if (!usage) {
    if (title) title.textContent = 'Select a tenant to view usage';
    if (plan) plan.textContent = '';
    if (status) status.textContent = '';
    if (note) note.textContent = 'Choose a tenant for tenant-scoped staff to preview the real seat usage and role counts for that plan.';
    if (stack) stack.hidden = true;
    if (empty) empty.hidden = false;
    return;
  }

  if (title) title.textContent = usage.tenant_name || 'Current Tenant';
  if (plan) plan.textContent = `${usage.plan_name || 'Unknown'} plan`;
  if (status) status.textContent = `Subscription: ${usage.subscription_status || 'Unavailable'}`;
  if (note) note.textContent = usage.subscription_message || 'Seat usage is based on active billable users for this tenant.';
  if (stack) stack.hidden = false;
  if (empty) empty.hidden = true;

  const totalValue = document.getElementById('usage-total-value');
  const totalFill = document.getElementById('usage-total-fill');
  if (totalValue) totalValue.textContent = `${usage.total.used} / ${usage.total.limit}`;
  if (totalFill) totalFill.style.width = `${usage.total.bar_width || 0}%`;

  (usage.roles || []).forEach((roleItem) => {
    const roleKey = String(roleItem.role_key || '').toLowerCase();
    const roleValue = document.getElementById(`usage-role-value-${roleKey}`);
    const roleFill = document.getElementById(`usage-role-fill-${roleKey}`);
    if (roleValue) roleValue.textContent = `${roleItem.used} / ${roleItem.limit}`;
    if (roleFill) roleFill.style.width = `${roleItem.bar_width || 0}%`;
  });
}

function editStaff(userId, fullName, role, email) {
  document.getElementById('edit_user_id').value = userId;
  document.getElementById('edit_full_name').value = fullName;
  document.getElementById('edit_role').value = role;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_password').value = '';
  document.getElementById('editModal').style.display = 'flex';
}

function closeEdit() {
  document.getElementById('editModal').style.display = 'none';
}

document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) closeEdit();
});

document.querySelectorAll('.reg-modal').forEach(function(modal) {
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
});

toggleStaffTenantSelect();
const staffTenantSelect = document.querySelector('#staff-tenant-select select[name="staff_tenant_id"]');
if (staffTenantSelect) {
  staffTenantSelect.addEventListener('change', function() {
    syncUsageTenantSelect(this.value);
    updateStaffUsagePanel(this.value);
  });
}

const usageTenantSelect = document.getElementById('staff-usage-tenant-select');
if (usageTenantSelect) {
  usageTenantSelect.addEventListener('change', function() {
    const role = document.getElementById('staff-role');
    const tenantScoped = role ? role.value !== 'ADMIN' : false;
    if (tenantScoped && staffTenantSelect) {
      staffTenantSelect.value = this.value;
    }
    updateStaffUsagePanel(this.value);
  });
}

if (!staffTenantSelect && usageTenantSelect) {
  updateStaffUsagePanel(usageTenantSelect.value);
} else if (staffTenantSelect && !usageTenantSelect) {
  updateStaffUsagePanel(staffTenantSelect.value);
}
<?php if ($is_super_admin && $staff_list_tenant_id > 0): ?>
openListModal('staffListModal');
<?php endif; ?>
</script>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
