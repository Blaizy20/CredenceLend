<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_login();
require_permission('manage_staff');

$title = "Staff Settings";
$active = "staff_settings";

$err = '';
$ok = '';
$global_staff_settings_view = is_global_super_admin_view();
$staff_scope_sql = $global_staff_settings_view
  ? "SELECT user_id, full_name, role, is_active, tenant_id FROM users WHERE role IN ('SUPER_ADMIN','ADMIN','MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER') AND user_id=?"
  : "SELECT user_id, full_name, role, is_active, tenant_id FROM users WHERE role IN ('MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER') AND user_id=? AND tenant_id=?";
$staff_owner_tenant_ids = function ($user_id) {
  $user_id = intval($user_id ?? 0);
  if ($user_id <= 0) {
    return [];
  }

  $rows = fetch_all(q(
    "SELECT tenant_id
     FROM tenant_admins
     WHERE user_id=?
     ORDER BY tenant_id ASC",
    "i",
    [$user_id]
  ));

  $tenant_ids = [];
  foreach ($rows as $row) {
    $tenant_id = intval($row['tenant_id'] ?? 0);
    if ($tenant_id > 0) {
      $tenant_ids[$tenant_id] = $tenant_id;
    }
  }

  return array_values($tenant_ids);
};

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
  $user_id = intval($_POST['user_id'] ?? 0);
  $new_role = $_POST['role'] ?? '';
  
  $allowed_roles = $global_staff_settings_view
    ? ['ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER']
    : ['MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER'];
  
  if ($user_id <= 0) {
    $err = "Invalid user.";
  } else if (!in_array($new_role, $allowed_roles, true)) {
    $err = "Invalid role.";
  } else if ($user_id === $_SESSION['user_id']) {
    $err = "Cannot change your own role.";
  } else {
    $conn = db();
    try {
      $conn->begin_transaction();

      $staff = fetch_one(q(
        $staff_scope_sql,
        $global_staff_settings_view ? "i" : "ii",
        $global_staff_settings_view ? [$user_id] : [$user_id, require_current_tenant_id()]
      ));
      if (!$staff) {
        throw new RuntimeException('Staff not found.');
      }
      if ($staff['role'] === 'ADMIN' && $new_role !== 'ADMIN') {
        throw new RuntimeException('Owner admins must remain ADMIN accounts.');
      }
      if ($staff['role'] !== 'ADMIN' && $new_role === 'ADMIN') {
        throw new RuntimeException('Use the owner admin creation flow instead of changing a tenant staff role to ADMIN.');
      }
      if (!empty($staff['is_active']) && intval($staff['tenant_id'] ?? 0) > 0) {
        subscription_lock_tenant_scope(intval($staff['tenant_id']));
        $role_assignment_check = can_assign_user_to_role(
          intval($staff['tenant_id']),
          $new_role,
          $staff['role'] ?? '',
          true
        );
        if (!$role_assignment_check['allowed']) {
          throw new RuntimeException($role_assignment_check['message']);
        }
      }

      $old_role = $staff['role'];
      q("UPDATE users SET role=? WHERE user_id=?", "si", [$new_role, $user_id]);
      $conn->commit();
      log_activity('Staff Role Changed', 'Staff ' . htmlspecialchars($staff['full_name']) . ' role changed from ' . htmlspecialchars($old_role) . ' to ' . htmlspecialchars($new_role), null, null, null);
      $ok = "Staff role updated successfully.";
    } catch (RuntimeException $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = "Failed to update staff role: " . $e->getMessage();
    }
  }
}

// Handle activate/deactivate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
  $user_id = intval($_POST['user_id'] ?? 0);
  
  if ($user_id <= 0) {
    $err = "Invalid user.";
  } else if ($user_id === $_SESSION['user_id']) {
    $err = "Cannot deactivate your own account.";
  } else {
    $conn = db();
    try {
      $conn->begin_transaction();

      $staff = fetch_one(q(
        $staff_scope_sql,
        $global_staff_settings_view ? "i" : "ii",
        $global_staff_settings_view ? [$user_id] : [$user_id, require_current_tenant_id()]
      ));
      if (!$staff) {
        throw new RuntimeException('Staff not found.');
      }

      $new_status = $staff['is_active'] ? 0 : 1;
      $status_text = $new_status ? 'Activated' : 'Deactivated';

      if ($new_status) {
        $tenant_id = intval($staff['tenant_id'] ?? 0);
        if ($tenant_id > 0) {
          subscription_lock_tenant_scope($tenant_id);
          $role_assignment_check = can_assign_user_to_role(
            $tenant_id,
            $staff['role'] ?? '',
            null,
            false
          );
          if (!$role_assignment_check['allowed']) {
            throw new RuntimeException($role_assignment_check['message']);
          }
        } elseif (($staff['role'] ?? '') === 'ADMIN') {
          foreach ($staff_owner_tenant_ids($user_id) as $owner_tenant_id) {
            subscription_lock_tenant_scope($owner_tenant_id);
            $role_assignment_check = can_add_user_to_role($owner_tenant_id, 'ADMIN');
            if (!$role_assignment_check['allowed']) {
              throw new RuntimeException($role_assignment_check['message']);
            }
          }
        }
      }

      q("UPDATE users SET is_active=? WHERE user_id=?", "ii", [$new_status, $user_id]);
      $conn->commit();
      log_activity('Staff ' . $status_text, 'Staff ' . htmlspecialchars($staff['full_name']) . ' ' . strtolower($status_text), null, null, null);
      $ok = "Staff " . strtolower($status_text) . " successfully.";
    } catch (RuntimeException $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
      try { $conn->rollback(); } catch (Exception $rollback_exception) {}
      $err = "Failed to update staff status: " . $e->getMessage();
    }
  }
}

// Fetch all staff
$tenant_id = $_SESSION['tenant_id'] ?? current_tenant_id();
$staff = $global_staff_settings_view
  ? fetch_all(q("SELECT user_id, username, full_name, role, email, is_active FROM users WHERE role IN ('SUPER_ADMIN','ADMIN','MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER') ORDER BY role DESC, full_name"))
  : fetch_all(q("SELECT user_id, username, full_name, role, email, is_active FROM users WHERE tenant_id=? AND role IN ('MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER') ORDER BY role DESC, full_name", "i", [$tenant_id]));

$staff_metrics = [
  'total' => count($staff),
  'active' => 0,
  'inactive' => 0,
  'manageable_roles' => 0,
];

foreach ($staff as $staff_member) {
  if (!empty($staff_member['is_active'])) {
    $staff_metrics['active']++;
  } else {
    $staff_metrics['inactive']++;
  }
}
$staff_metrics['manageable_roles'] = $global_staff_settings_view ? 5 : 4;

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

  .sidebar {
    background: rgba(4, 10, 24, 0.84);
    border-right: 1px solid rgba(148, 163, 184, 0.12);
    backdrop-filter: blur(16px);
  }

  .sidebar h3 { color: #7f93b0; }
  .sidebar a { color: #d7e3f4; }
  .sidebar a.active,
  .sidebar a:hover {
    background: linear-gradient(135deg, rgba(14, 165, 233, 0.18), rgba(59, 130, 246, 0.2));
    color: #f8fbff;
  }

  .staff-settings-shell {
    display: grid;
    gap: 20px;
    color: #e5eefb;
  }

  .staff-settings-card,
  .staff-role-modal-card {
    border-radius: 26px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
      radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 26%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.97));
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
    padding: 24px;
  }

  .staff-settings-hero {
    display: grid;
    gap: 18px;
    grid-template-columns: 1fr;
    align-items: stretch;
  }

  .staff-settings-kicker {
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

  .staff-settings-hero h2 {
    margin: 10px 0 0;
    font-size: clamp(30px, 4vw, 44px);
    line-height: 1;
    letter-spacing: -0.04em;
    color: #f8fbff;
  }

  .staff-settings-hero p,
  .staff-settings-card .small,
  .staff-role-modal-card .small {
    color: #8ea3bf;
  }

  .staff-settings-summary-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .staff-settings-summary-card {
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.68);
    padding: 18px;
  }

  .staff-settings-summary-card span {
    display: block;
    color: #8ea3bf;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 8px;
  }

  .staff-settings-summary-card strong {
    display: block;
    color: #f8fbff;
    font-size: clamp(24px, 3vw, 34px);
    line-height: 1;
  }

  .staff-settings-layout {
    display: block;
  }

  .staff-settings-table-card {
    min-width: 0;
    width: 100%;
  }

  .staff-settings-header {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: end;
    margin-bottom: 18px;
  }

  .staff-settings-header h3,
  .staff-role-modal-card h3 {
    margin: 0;
    color: #f8fbff;
  }

  .staff-settings-card .label,
  .staff-role-modal-card .label {
    color: #93a8c6;
  }

  .staff-settings-card .input,
  .staff-role-modal-card .input {
    background: rgba(15, 23, 42, 0.86);
    color: #f8fbff;
    border: 1px solid rgba(148, 163, 184, 0.18);
  }

  .staff-settings-card .input::placeholder,
  .staff-role-modal-card .input::placeholder {
    color: #6f86a6;
  }

  .staff-settings-card .btn.btn-outline,
  .staff-role-modal-card .btn.btn-outline {
    border-color: rgba(148, 163, 184, 0.2);
    color: #d8e4f5;
    background: rgba(15, 23, 42, 0.55);
  }

  .staff-settings-table-wrap {
    overflow-x: auto;
    margin-top: 14px;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    overflow-y: auto;
  }

  .staff-settings-card .table {
    margin: 0;
    width: 100%;
    color: #e5eefb;
    background: transparent;
  }

  .staff-settings-card .table th {
    background: rgba(15, 23, 42, 0.96);
    color: #93a8c6;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 11px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.16);
  }

  .staff-settings-card .table td,
  .staff-settings-card .table th {
    border-color: rgba(148, 163, 184, 0.1);
    padding: 14px 16px;
    vertical-align: middle;
  }

  .staff-settings-card .table tbody tr:nth-child(odd) {
    background: rgba(15, 23, 42, 0.48);
  }

  .staff-user-name strong {
    display: block;
    color: #f8fbff;
  }

  .staff-user-name span {
    display: block;
    color: #8ea3bf;
    font-size: 12px;
    margin-top: 4px;
  }

  .staff-settings-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .staff-settings-role-card {
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.68);
    padding: 18px;
  }

  .staff-settings-role-card h4 {
    margin: 0 0 10px 0;
    color: #7dd3fc;
    letter-spacing: 0.02em;
  }

  .staff-settings-role-card ul {
    margin: 0;
    padding-left: 18px;
    color: #d8e4f5;
    line-height: 1.7;
  }

  .staff-settings-empty {
    text-align: center;
    padding: 34px 20px;
    color: #8ea3bf;
  }

  .staff-role-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, 0.74);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }

  .staff-role-modal-card {
    max-width: 460px;
    width: 95%;
  }

  @media (max-width: 1080px) {
    .staff-settings-summary-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 760px) {
    .staff-settings-card,
    .staff-role-modal-card {
      padding: 18px;
      border-radius: 18px;
    }

    .staff-settings-summary-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="staff-settings-shell">
  <section class="staff-settings-hero">
    <div class="staff-settings-card">
      <span class="staff-settings-kicker">Staff Control</span>
      <h2>Staff Settings & Permissions</h2>
      <p>Manage staff roles, account status, and access expectations using the same dark dashboard UI as the rest of the portal.</p>
    </div>
  </section>

  <?php if ($err): ?><div class="alert red" style="margin-top:0"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert green" style="margin-top:0"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <section class="staff-settings-summary-grid">
    <div class="staff-settings-summary-card">
      <span>Total Staff</span>
      <strong><?= intval($staff_metrics['total']) ?></strong>
    </div>
    <div class="staff-settings-summary-card">
      <span>Active</span>
      <strong><?= intval($staff_metrics['active']) ?></strong>
    </div>
    <div class="staff-settings-summary-card">
      <span>Inactive</span>
      <strong><?= intval($staff_metrics['inactive']) ?></strong>
    </div>
    <div class="staff-settings-summary-card">
      <span>Editable Roles</span>
      <strong><?= intval($staff_metrics['manageable_roles']) ?></strong>
    </div>
  </section>

  <section class="staff-settings-layout">
    <div class="staff-settings-card staff-settings-table-card">
      <div class="staff-settings-header" style="margin-bottom:0">
        <div>
          <h3>Staff Members</h3>
          <p style="margin:8px 0 0; color:#8ea3bf; line-height:1.7;">Review each account, update role assignments, and activate or deactivate staff without leaving this workspace.</p>
        </div>
      </div>

      <div class="staff-settings-table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Staff</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($staff)): ?>
              <?php foreach ($staff as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s['username']) ?></td>
                  <td>
                    <div class="staff-user-name">
                      <strong><?= htmlspecialchars($s['full_name']) ?></strong>
                      <span><?= $s['user_id'] === $_SESSION['user_id'] ? 'Current session account' : 'Managed staff account' ?></span>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                  <td><span class="badge blue"><?= htmlspecialchars(str_replace('_', ' ', $s['role'])) ?></span></td>
                  <td><span class="badge <?= $s['is_active'] ? 'green' : 'gray' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                  <td>
                    <div class="staff-settings-actions">
                      <?php if ($s['user_id'] !== $_SESSION['user_id']): ?>
                        <a class="btn btn-primary" href="#" onclick="return editRole(<?= intval($s['user_id']) ?>, '<?= htmlspecialchars($s['full_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['role'], ENT_QUOTES) ?>');">Change Role</a>
                        <form style="display:inline" method="post" onsubmit="return confirm('<?= $s['is_active'] ? 'Deactivate' : 'Activate' ?> this staff account?');">
                          <input type="hidden" name="user_id" value="<?= intval($s['user_id']) ?>">
                          <button class="btn btn-outline" type="submit" name="toggle_active" value="1">
                            <?= $s['is_active'] ? 'Deactivate' : 'Activate' ?>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="small">(Current User)</span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="staff-settings-empty">No staff members found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<div id="editRoleModal" class="staff-role-modal">
  <div class="staff-role-modal-card">
    <h3>Change Staff Role</h3>
    <div id="roleStaffName" class="small" style="margin:10px 0 18px;font-weight:600;color:#d8e4f5;"></div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" id="edit_user_id" name="user_id">
      <div>
        <label class="label">Select New Role</label>
        <select class="input" id="edit_role" name="role" required>
          <option value="">-- Select Role --</option>
          <?php if (is_system_admin()): ?>
            <option value="ADMIN">Admin</option>
          <?php endif; ?>
          <option value="MANAGER">Manager</option>
          <option value="CREDIT_INVESTIGATOR">Credit Investigator</option>
          <option value="LOAN_OFFICER">Loan Officer</option>
          <option value="CASHIER">Cashier</option>
        </select>
      </div>
      <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-primary" type="submit" name="update_role" value="1">Update Role</button>
        <button class="btn btn-outline" type="button" onclick="closeEditRole()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function editRole(userId, fullName, currentRole) {
  document.getElementById('edit_user_id').value = userId;
  document.getElementById('roleStaffName').textContent = 'Staff: ' + fullName;
  document.getElementById('edit_role').value = currentRole;
  document.getElementById('editRoleModal').style.display = 'flex';
  return false;
}

function closeEditRole() {
  document.getElementById('editRoleModal').style.display = 'none';
}

document.getElementById('editRoleModal').addEventListener('click', function(e) {
  if (e.target === this) closeEditRole();
});
</script>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
