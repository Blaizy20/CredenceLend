<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_login();
require_permission('view_settings');

$settings_tenant_id = require_current_tenant_id();
$message = '';
$error = '';
$logo_feature_access = current_tenant_feature_access('logo_upload', $settings_tenant_id);
$theme_feature_access = current_tenant_feature_access('theme_customization', $settings_tenant_id);
$can_upload_logo = !empty($logo_feature_access['allowed']);
$can_customize_theme = !empty($theme_feature_access['allowed']);
$branding_feature_notices = [];
if (!$can_upload_logo) {
  $branding_feature_notices[] = $logo_feature_access['message'];
}
if (!$can_customize_theme) {
  $branding_feature_notices[] = $theme_feature_access['message'];
}
$current_settings_record = fetch_one(q(
  "SELECT setting_id, system_name, logo_path, primary_color
   FROM system_settings
   WHERE tenant_id=?
   LIMIT 1",
  "i",
  [$settings_tenant_id]
));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $system_name = trim($_POST['system_name'] ?? '');
  $reset_primary = isset($_POST['reset_primary']);
  $primary_color = trim($_POST['primary_color'] ?? app_default_primary_color());
  if ($reset_primary) {
    $primary_color = app_default_primary_color();
  }
  $logo_path = null;

  if (empty($system_name) || strlen($system_name) > 255) {
    $error = 'System name is required and must be less than 255 characters.';
  } elseif (!preg_match('/^#[0-9A-Fa-f]{6}$/', $primary_color)) {
    $error = 'Invalid color format. Please use hex color (e.g., ' . app_default_primary_color() . ').';
  } elseif (!$can_customize_theme && isset($_POST['primary_color']) && $primary_color !== app_default_primary_color()) {
    $error = $theme_feature_access['message'];
  } else {
    if (!$can_upload_logo && isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
      $error = $logo_feature_access['message'];
    } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Logo upload failed. Error code: ' . $_FILES['logo']['error'];
      } else {
        $file_name = $_FILES['logo']['name'];
        $file_size = $_FILES['logo']['size'];
        $file_tmp = $_FILES['logo']['tmp_name'];

        $allowed_types = ['image/png', 'image/jpeg'];
        $file_type = mime_content_type($file_tmp);

        if (!in_array($file_type, $allowed_types, true)) {
          $error = 'Only PNG and JPG files are allowed.';
        } elseif ($file_size > 5 * 1024 * 1024) {
          $error = 'Logo file size must not exceed 5MB.';
        } else {
          $upload_dir = __DIR__ . '/../uploads/logo/';
          if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
          }

          $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
          $new_file_name = 'logo_' . time() . '.' . strtolower($file_extension);
          $upload_path = $upload_dir . $new_file_name;

          if (move_uploaded_file($file_tmp, $upload_path)) {
            $logo_path = APP_BASE . '/uploads/logo/' . $new_file_name;
          } else {
            $error = 'Failed to save logo file.';
          }
        }
      }
    }

    if ($error === '') {
      try {
        if ($logo_path === null && $current_settings_record) {
          $logo_path = $current_settings_record['logo_path'];
        }

        $resolved_logo_path = $can_upload_logo
          ? $logo_path
          : ($current_settings_record['logo_path'] ?? null);
        $resolved_primary_color = $can_customize_theme
          ? $primary_color
          : ($current_settings_record['primary_color'] ?? app_default_primary_color());

        if ($current_settings_record && isset($current_settings_record['setting_id'])) {
          q(
            "UPDATE system_settings SET system_name=?, logo_path=?, primary_color=? WHERE tenant_id=?",
            "sssi",
            [$system_name, $resolved_logo_path, $resolved_primary_color, $settings_tenant_id]
          );
        } else {
          q(
            "INSERT INTO system_settings (tenant_id, system_name, logo_path, primary_color) VALUES (?, ?, ?, ?)",
            "isss",
            [$settings_tenant_id, $system_name, $resolved_logo_path, $resolved_primary_color]
          );
        }

        clear_system_settings_cache($settings_tenant_id);
        $message = 'Settings updated successfully.';
      } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
        error_log("Settings update error: " . $e->getMessage());
      }
    }
  }
}

$settings = get_system_settings($settings_tenant_id);
$can_view_role_permissions = current_role() === 'SUPER_ADMIN';
$role_permissions_map = [];
$role_permission_labels = [
  'view_dashboard' => 'View dashboard',
  'view_loans' => 'View loans',
  'view_loan_details' => 'View loan details',
  'view_customers' => 'View customers',
  'manage_customers' => 'Manage customers',
  'view_payments' => 'View payments',
  'record_payments' => 'Record payments',
  'edit_payments' => 'Edit payments',
  'print_receipts' => 'Print receipts',
  'manage_vouchers' => 'Manage vouchers',
  'review_applications' => 'Review applications',
  'approve_applications' => 'Approve applications',
  'update_loan_terms' => 'Update loan terms',
  'assign_loan_officer' => 'Assign loan officer',
  'view_reports' => 'View reports',
  'view_advanced_reports' => 'View advanced reports',
  'view_staff' => 'View staff',
  'manage_staff' => 'Manage staff',
  'view_history' => 'View history',
  'manage_tenants' => 'Manage tenants',
  'view_role_permissions' => 'View roles and permissions',
  'manage_backups' => 'Manage backups',
  'view_sales' => 'View sales',
  'view_settings' => 'View settings',
];
$role_display_order = ['SUPER_ADMIN', 'ADMIN', 'TENANT', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER', 'CUSTOMER'];

if ($can_view_role_permissions) {
  foreach ($role_display_order as $role_key) {
    $role_permissions_map[$role_key] = [];
  }

  foreach (auth_role_permissions() as $permission_key => $allowed_roles) {
    foreach ($allowed_roles as $role_key) {
      if (!isset($role_permissions_map[$role_key])) {
        $role_permissions_map[$role_key] = [];
      }
      $role_permissions_map[$role_key][] = $role_permission_labels[$permission_key] ?? ucwords(str_replace('_', ' ', $permission_key));
    }
  }
}

$title = "Manager Settings";
$active = "settings";
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

  .settings-shell {
    display: grid;
    gap: 20px;
    color: #e5eefb;
  }

  .settings-card {
    border-radius: 26px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
      radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 26%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.97));
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
    padding: 24px;
  }

  .settings-card.settings-card-full {
    grid-column: 1 / -1;
  }

  .settings-hero {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.9fr);
    align-items: stretch;
  }

  .settings-kicker {
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

  .settings-hero h2 {
    margin: 10px 0 0;
    font-size: clamp(30px, 4vw, 44px);
    line-height: 1;
    letter-spacing: -0.04em;
    color: #f8fbff;
  }

  .settings-hero p,
  .settings-card .small {
    color: #8ea3bf;
  }

  .settings-grid {
    display: grid;
    gap: 20px;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.9fr);
    align-items: start;
  }

  .settings-side {
    display: grid;
    gap: 18px;
  }

  .settings-guidelines {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }

  .settings-guideline {
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.62);
    padding: 16px;
    font-size: 12px;
    line-height: 1.7;
    color: #cbd5e1;
  }

  .settings-guideline strong {
    display: block;
    color: #7dd3fc;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 11px;
  }

  .settings-card .label {
    color: #93a8c6;
  }

  .settings-card .input {
    background: rgba(15, 23, 42, 0.86);
    color: #f8fbff;
    border: 1px solid rgba(148, 163, 184, 0.18);
  }

  .settings-card .input::placeholder {
    color: #6f86a6;
  }

  .settings-card .btn.btn-outline {
    border-color: rgba(148, 163, 184, 0.2);
    color: #d8e4f5;
    background: rgba(15, 23, 42, 0.55);
  }

  .settings-color-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }

  .settings-color-picker {
    width: 84px;
    height: 44px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 10px;
    cursor: pointer;
    background: rgba(15, 23, 42, 0.86);
  }

  .settings-logo-preview {
    display: inline-flex;
    flex-direction: column;
    gap: 8px;
  }

  .settings-logo-preview img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background: rgba(15, 23, 42, 0.62);
    padding: 10px;
  }

  .settings-file-input {
    padding: 10px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 12px;
    width: 100%;
    background: rgba(15, 23, 42, 0.86);
    color: #f8fbff;
  }

  .role-access-list {
    display: grid;
    gap: 12px;
  }

  .role-access-button {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    width: 100%;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background: rgba(15, 23, 42, 0.62);
    color: #f8fbff;
    cursor: pointer;
    text-align: left;
    transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
  }

  .role-access-button:hover {
    transform: translateY(-1px);
    border-color: rgba(125, 211, 252, 0.32);
    background: rgba(14, 165, 233, 0.12);
  }

  .role-access-button strong {
    display: block;
    font-size: 15px;
    color: #f8fbff;
  }

  .role-access-button span {
    font-size: 12px;
    color: #8ea3bf;
  }

  .role-access-arrow {
    font-size: 20px;
    line-height: 1;
    color: #7dd3fc;
  }

  .role-permission-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1200;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(2, 6, 23, 0.7);
    backdrop-filter: blur(6px);
  }

  .role-permission-dialog {
    width: min(680px, 100%);
    max-height: min(80vh, 760px);
    overflow: auto;
  }

  .role-permission-list {
    margin: 18px 0 0;
    padding-left: 20px;
    color: #d8e4f5;
    line-height: 1.7;
  }

  .role-permission-empty {
    margin-top: 18px;
    padding: 14px 16px;
    border-radius: 14px;
    border: 1px dashed rgba(148, 163, 184, 0.18);
    background: rgba(15, 23, 42, 0.48);
    color: #8ea3bf;
  }

  @media (max-width: 1080px) {
    .settings-hero,
    .settings-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 760px) {
    .settings-card {
      padding: 18px;
      border-radius: 18px;
    }

    .settings-color-row {
      flex-direction: column;
      align-items: stretch;
    }

    .settings-color-picker {
      width: 100%;
    }
  }
</style>

<div class="settings-shell">
  <section class="settings-hero">
    <div class="settings-card settings-card-full">
      <span class="settings-kicker">Tenant Branding</span>
      <h2>System Settings</h2>
      <p>Manage the tenant-facing system name, primary color, and logo using the same dark interface treatment used across the rest of the staff workspace.</p>
    </div>
  </section>

  <?php if ($message): ?>
    <div class="alert green" style="margin-top:0"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert red" style="margin-top:0"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php foreach ($branding_feature_notices as $feature_notice): ?>
    <div class="alert red" style="margin-top:0"><?= htmlspecialchars($feature_notice) ?></div>
  <?php endforeach; ?>

  <section class="settings-grid">
    <div class="settings-card">
      <h3 style="margin-top:0;color:#f8fbff">Update Branding</h3>
      <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div style="margin-bottom:20px">
          <label class="label">System Name</label>
          <input type="text" name="system_name" class="input" value="<?= htmlspecialchars($settings['system_name'] ?? '') ?>" required>
        </div>

        <?php if ($can_customize_theme): ?>
          <div style="margin-bottom:20px">
            <label class="label">Primary Color</label>
            <div class="settings-color-row">
              <input type="color" id="color_picker" class="settings-color-picker" value="<?= htmlspecialchars($settings['primary_color'] ?? app_default_primary_color()) ?>">
              <input type="text" id="color_hex" name="primary_color" class="input" value="<?= htmlspecialchars($settings['primary_color'] ?? app_default_primary_color()) ?>" placeholder="<?= htmlspecialchars(app_default_primary_color()) ?>" style="max-width:180px">
              <button type="submit" id="reset_color" name="reset_primary" value="1" class="btn btn-outline">Reset Default</button>
            </div>
            <div class="small" style="margin-top:6px">Use the picker or enter a hex value such as `<?= htmlspecialchars(app_default_primary_color()) ?>`.</div>
          </div>
        <?php endif; ?>

        <?php if ($can_upload_logo): ?>
          <div style="margin-bottom:20px">
            <label class="label">System Logo</label>
            <div style="margin-bottom:12px">
              <?php if (!empty($settings['logo_path'])): ?>
                <div class="settings-logo-preview">
                  <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="Current Logo">
                  <div class="small">Current logo</div>
                </div>
              <?php else: ?>
                <div class="small">No logo uploaded yet.</div>
              <?php endif; ?>
            </div>
            <input type="file" name="logo" accept=".png,.jpg,.jpeg" class="settings-file-input">
            <div class="small" style="margin-top:6px">PNG or JPG only, maximum file size 5MB.</div>
          </div>
        <?php endif; ?>

        <div style="margin-top:30px;display:flex;gap:10px;flex-wrap:wrap">
          <button type="submit" class="btn btn-primary">Save Settings</button>
          <a href="<?php echo APP_BASE; ?>/staff/dashboard.php" class="btn btn-outline">Cancel</a>
        </div>
      </form>
    </div>

    <?php if ($can_view_role_permissions): ?>
      <div class="settings-card">
        <h3 style="margin-top:0;color:#f8fbff">Role Permissions</h3>
        <div class="small" style="margin-bottom:16px">Inspect the permissions assigned to each role. This panel is restricted to developer-level access and super admins.</div>

        <div class="role-access-list">
          <?php foreach ($role_display_order as $role_key): ?>
            <?php
              $permission_count = count($role_permissions_map[$role_key] ?? []);
              $role_name = get_role_display_name($role_key);
            ?>
            <button
              type="button"
              class="role-access-button"
              data-role-key="<?= htmlspecialchars($role_key) ?>"
              data-role-name="<?= htmlspecialchars($role_name) ?>"
            >
              <span>
                <strong><?= htmlspecialchars($role_name) ?></strong>
                <span><?= $permission_count ?> permission<?= $permission_count === 1 ? '' : 's' ?></span>
              </span>
              <span class="role-access-arrow" aria-hidden="true">&rsaquo;</span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php if ($can_view_role_permissions): ?>
  <div id="rolePermissionModal" class="role-permission-modal" role="dialog" aria-modal="true" aria-labelledby="rolePermissionModalTitle">
    <div class="settings-card role-permission-dialog">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
        <div>
          <div class="settings-kicker">Role Access</div>
          <h3 id="rolePermissionModalTitle" style="margin:10px 0 0;color:#f8fbff">Role Permissions</h3>
          <div id="rolePermissionModalSubtitle" class="small" style="margin-top:8px">Current permissions for this role.</div>
        </div>
        <button type="button" class="btn btn-outline" id="closeRolePermissionModal">Close</button>
      </div>

      <ul id="rolePermissionList" class="role-permission-list"></ul>
      <div id="rolePermissionEmpty" class="role-permission-empty" style="display:none">This role does not currently have any mapped staff portal permissions.</div>
    </div>
  </div>
<?php endif; ?>

<script>
const rootStyle = document.documentElement.style;
const defaultPrimary = <?= json_encode(app_default_primary_color()) ?>;
const rolePermissions = <?= json_encode($role_permissions_map, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

function hexToRgb(value) {
  const normalized = value.replace('#', '');
  return {
    r: parseInt(normalized.slice(0, 2), 16),
    g: parseInt(normalized.slice(2, 4), 16),
    b: parseInt(normalized.slice(4, 6), 16)
  };
}

function adjustHex(value, factor) {
  const { r, g, b } = hexToRgb(value);
  const adjust = (channel) => {
    if (factor >= 0) {
      return Math.round(channel + ((255 - channel) * factor));
    }
    return Math.round(channel * (1 + factor));
  };

  const next = [adjust(r), adjust(g), adjust(b)]
    .map((channel) => Math.max(0, Math.min(255, channel)))
    .map((channel) => channel.toString(16).padStart(2, '0'))
    .join('');

  return `#${next}`;
}

function applyThemePreview(value) {
  const color = /^#[0-9A-Fa-f]{6}$/.test(value) ? value : defaultPrimary;
  const { r, g, b } = hexToRgb(color);

  rootStyle.setProperty('--brand-primary', color);
  rootStyle.setProperty('--brand-primary-hover', adjustHex(color, -0.18));
  rootStyle.setProperty('--brand-primary-deep', adjustHex(color, -0.5));
  rootStyle.setProperty('--brand-primary-mid', adjustHex(color, -0.28));
  rootStyle.setProperty('--brand-topbar-start', adjustHex(color, -0.5));
  rootStyle.setProperty('--brand-topbar-end', adjustHex(color, -0.28));
  rootStyle.setProperty('--brand-primary-rgb', `${r}, ${g}, ${b}`);
  rootStyle.setProperty('--brand-primary-soft', `rgba(${r}, ${g}, ${b}, 0.08)`);
  rootStyle.setProperty('--brand-primary-soft-strong', `rgba(${r}, ${g}, ${b}, 0.14)`);
  rootStyle.setProperty('--brand-red', color);
  rootStyle.setProperty('--brand-red-hover', adjustHex(color, -0.18));
}

const colorPicker = document.getElementById('color_picker');
const colorHexInput = document.getElementById('color_hex');
const resetColorButton = document.getElementById('reset_color');

if (colorPicker && colorHexInput) {
  colorPicker.addEventListener('change', function (e) {
    colorHexInput.value = e.target.value;
    applyThemePreview(e.target.value);
  });

  colorPicker.addEventListener('input', function (e) {
    colorHexInput.value = e.target.value;
    applyThemePreview(e.target.value);
  });

  colorHexInput.addEventListener('input', function () {
    let value = this.value.trim();

    if (value && !value.startsWith('#')) {
      value = '#' + value;
      this.value = value;
    }

    if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
      colorPicker.value = value;
      applyThemePreview(value);
    }
  });

  if (resetColorButton) {
    resetColorButton.addEventListener('click', function () {
      colorPicker.value = defaultPrimary;
      colorHexInput.value = defaultPrimary;
      applyThemePreview(defaultPrimary);
    });
  }

  applyThemePreview(colorHexInput.value.trim());
}

const rolePermissionModal = document.getElementById('rolePermissionModal');
const rolePermissionList = document.getElementById('rolePermissionList');
const rolePermissionEmpty = document.getElementById('rolePermissionEmpty');
const rolePermissionModalTitle = document.getElementById('rolePermissionModalTitle');
const rolePermissionModalSubtitle = document.getElementById('rolePermissionModalSubtitle');
const closeRolePermissionModalButton = document.getElementById('closeRolePermissionModal');

function openRolePermissionModal(roleKey, roleName) {
  if (!rolePermissionModal || !rolePermissionList || !rolePermissionEmpty) {
    return;
  }

  const permissions = Array.isArray(rolePermissions[roleKey]) ? rolePermissions[roleKey] : [];

  rolePermissionModalTitle.textContent = roleName + ' Permissions';
  rolePermissionModalSubtitle.textContent = permissions.length
    ? 'This role currently has ' + permissions.length + ' mapped permission' + (permissions.length === 1 ? '.' : 's.')
    : 'This role currently has no mapped staff portal permissions.';

  rolePermissionList.innerHTML = '';

  if (permissions.length === 0) {
    rolePermissionList.style.display = 'none';
    rolePermissionEmpty.style.display = 'block';
  } else {
    permissions.forEach(function (permissionLabel) {
      const item = document.createElement('li');
      item.textContent = permissionLabel;
      rolePermissionList.appendChild(item);
    });

    rolePermissionList.style.display = 'block';
    rolePermissionEmpty.style.display = 'none';
  }

  rolePermissionModal.style.display = 'flex';
}

function closeRolePermissionModal() {
  if (rolePermissionModal) {
    rolePermissionModal.style.display = 'none';
  }
}

document.querySelectorAll('.role-access-button').forEach(function (button) {
  button.addEventListener('click', function () {
    openRolePermissionModal(button.dataset.roleKey || '', button.dataset.roleName || 'Role');
  });
});

if (closeRolePermissionModalButton) {
  closeRolePermissionModalButton.addEventListener('click', closeRolePermissionModal);
}

if (rolePermissionModal) {
  rolePermissionModal.addEventListener('click', function (event) {
    if (event.target === rolePermissionModal) {
      closeRolePermissionModal();
    }
  });
}
</script>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
