<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/backup_helpers.php';

require_login();
require_permission('manage_backups');

$backup_log_id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
$restore_point = $backup_log_id > 0 ? get_backup_log($backup_log_id) : null;
$restore_file = $restore_point ? backup_file_metadata($restore_point['filename'] ?? '') : null;
$error = '';

if (!$restore_point || intval($restore_point['is_restore_point'] ?? 0) !== 1) {
  http_response_code(404);
  echo "Restore point not found.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();

  if (empty($_POST['confirm_restore'])) {
    $error = 'Confirm the restore before continuing.';
  } elseif (!$restore_file) {
    $error = 'The restore point file is missing from the backups folder.';
  } elseif (($restore_point['status'] ?? '') !== 'SUCCESS') {
    $error = 'Only successful restore points can be restored.';
  } else {
    try {
      $result = restore_from_backup_log($backup_log_id, $_SESSION['user_id'] ?? 0);
      $_SESSION['backup_settings_message'] = $result['message'] ?? 'Restore completed successfully.';
      header('Location: ' . APP_BASE . '/staff/backup_settings.php');
      exit;
    } catch (Throwable $e) {
      $error = $e->getMessage();
    }
  }
}

$title = 'Restore Point';
$active = 'backups';
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
  .restore-shell { display: grid; gap: 20px; color: #e5eefb; }
  .restore-card { border-radius: 26px; border: 1px solid rgba(148, 163, 184, 0.16); background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 26%), linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.97)); box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34); padding: 24px; }
  .restore-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; border: 1px solid rgba(125, 211, 252, 0.24); background: rgba(14, 165, 233, 0.12); color: #d8f4ff; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
  .restore-grid { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 18px; }
  .restore-stat { border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.14); background: rgba(15, 23, 42, 0.62); padding: 18px; }
  .restore-stat span { display: block; color: #8ea3bf; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
  .restore-stat strong { color: #f8fbff; font-size: 18px; line-height: 1.3; }
  .restore-warning { margin-top: 18px; padding: 18px; border-radius: 18px; border: 1px solid rgba(248, 113, 113, 0.18); background: rgba(239, 68, 68, 0.08); color: #fecaca; line-height: 1.65; }
  .restore-actions { margin-top: 18px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
  .restore-checkbox { display: flex; gap: 10px; align-items: flex-start; margin-top: 18px; color: #dbe6f6; }
  @media (max-width: 760px) { .restore-grid { grid-template-columns: 1fr; } .restore-card { padding: 18px; border-radius: 18px; } }
</style>

<div class="restore-shell">
  <section class="restore-card">
    <span class="restore-kicker">Restore Confirmation</span>
    <h2 style="margin:14px 0 8px 0;color:#f8fbff">Restore from <?= htmlspecialchars($restore_point['restore_label'] ?: 'Restore Point') ?></h2>
    <div class="small" style="color:#8ea3bf">This action restores the entire database, not just one tenant. A safety backup will be created first.</div>

    <?php if ($error): ?><div class="alert red" style="margin-top:16px"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="restore-grid">
      <div class="restore-stat">
        <span>Label</span>
        <strong><?= htmlspecialchars($restore_point['restore_label'] ?: 'Restore Point') ?></strong>
      </div>
      <div class="restore-stat">
        <span>Filename</span>
        <strong><?= htmlspecialchars($restore_point['filename']) ?></strong>
      </div>
      <div class="restore-stat">
        <span>Created</span>
        <strong><?= htmlspecialchars($restore_point['created_at']) ?></strong>
      </div>
      <div class="restore-stat">
        <span>Status</span>
        <strong><?= htmlspecialchars($restore_point['status']) ?></strong>
      </div>
    </div>

    <div class="restore-warning">
      This will overwrite the current database state for all tenants, users, customers, loans, payments, and logs.
      Use restore only when you intentionally want to return the whole system to this snapshot.
    </div>

    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= intval($backup_log_id) ?>">

      <label class="restore-checkbox">
        <input type="checkbox" name="confirm_restore" value="1" required>
        <span>I understand this will overwrite the current database with the selected restore point.</span>
      </label>

      <div class="restore-actions">
        <button class="btn btn-primary" type="submit">Restore Now</button>
        <a class="btn btn-outline" href="<?php echo APP_BASE; ?>/staff/backup_settings.php">Cancel</a>
      </div>
    </form>
  </section>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
