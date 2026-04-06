<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/backup_helpers.php';

require_login();
require_permission('manage_backups');

$title = "Backup Settings";
$active = "backups";
$error = $_SESSION['backup_settings_error'] ?? '';
$message = $_SESSION['backup_settings_message'] ?? '';
unset($_SESSION['backup_settings_error'], $_SESSION['backup_settings_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();

  if (isset($_POST['trigger_backup'])) {
    $result = create_manual_backup($_SESSION['user_id'] ?? 0);
    if (!empty($result['success'])) {
      $message = 'Backup created: ' . $result['filename'];
    } else {
      $error = $result['message'] ?? 'Backup failed.';
    }
  } elseif (isset($_POST['create_restore_point'])) {
    $restore_label = trim((string) ($_POST['restore_label'] ?? ''));
    $result = create_restore_point($_SESSION['user_id'] ?? 0, $restore_label);
    if (!empty($result['success'])) {
      $message = 'Restore point created: ' . ($result['restore_label'] ?? $result['filename']);
    } else {
      $error = $result['message'] ?? 'Restore point creation failed.';
    }
  }
}

$backup_logs = fetch_backup_logs(20);
$restore_points = fetch_restore_points(20);
$restore_logs = fetch_restore_logs(20);
$backup_files = list_backup_files();
$total_backup_bytes = array_sum(array_map(static function ($file) {
  return intval($file['size'] ?? 0);
}, $backup_files));

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
  .backup-shell { display: grid; gap: 20px; color: #e5eefb; }
  .backup-card { border-radius: 26px; border: 1px solid rgba(148, 163, 184, 0.16); background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 26%), linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.97)); box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34); padding: 24px; }
  .backup-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; border: 1px solid rgba(125, 211, 252, 0.24); background: rgba(14, 165, 233, 0.12); color: #d8f4ff; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
  .backup-card h2, .backup-card h3 { color: #f8fbff; margin-top: 0; }
  .backup-section-head h3 { margin-bottom: 0; }
  .backup-card .small, .backup-meta span { color: #8ea3bf; }
  .backup-grid { display: grid; gap: 16px; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-top: 20px; }
  .backup-metric { border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.14); background: rgba(15, 23, 42, 0.62); padding: 18px; }
  .backup-metric span { display: block; color: #8ea3bf; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
  .backup-metric strong { font-size: 30px; color: #f8fbff; line-height: 1; }
  .backup-section-head { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
  .backup-inline-form { display: flex; gap: 12px; flex-wrap: nowrap; align-items: center; min-width: 0; }
  .backup-inline-form .input { width: 320px; min-width: 0; background: rgba(15, 23, 42, 0.86); color: #f8fbff; border: 1px solid rgba(148, 163, 184, 0.18); }
  .backup-inline-form .btn { flex: 0 0 auto; white-space: nowrap; }
  .backup-card .btn.btn-outline {
    border-color: rgba(148, 163, 184, 0.24);
    color: #d8e4f5;
    background: rgba(15, 23, 42, 0.55);
  }
  .backup-card .btn.btn-outline:hover,
  .backup-card .btn.btn-outline:focus {
    border-color: rgba(125, 211, 252, 0.42);
    background: rgba(30, 41, 59, 0.88);
    color: #f8fbff;
    text-decoration: none;
  }
  .backup-table-wrap { overflow: auto; margin-top: 14px; border-radius: 20px; border: 1px solid rgba(148, 163, 184, 0.12); }
  .backup-card .table { margin: 0; width: 100%; color: #e5eefb; background: transparent; }
  .backup-card .table th { background: rgba(15, 23, 42, 0.96); color: #93a8c6; text-transform: uppercase; letter-spacing: 0.08em; font-size: 11px; border-bottom: 1px solid rgba(148, 163, 184, 0.16); }
  .backup-card .table td, .backup-card .table th { border-color: rgba(148, 163, 184, 0.1); padding: 14px 16px; vertical-align: middle; }
  .backup-card .table tbody tr:nth-child(odd) { background: rgba(15, 23, 42, 0.48); }
  .backup-warning { margin-top: 14px; padding: 14px 16px; border-radius: 16px; border: 1px solid rgba(245, 158, 11, 0.18); background: rgba(245, 158, 11, 0.08); color: #fcd69a; font-size: 13px; line-height: 1.6; }
  .backup-stack { display: grid; gap: 20px; grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); }
  @media (max-width: 980px) { .backup-grid, .backup-stack { grid-template-columns: 1fr; } }
  @media (max-width: 760px) {
    .backup-card { padding: 18px; border-radius: 18px; }
    .backup-inline-form { width: 100%; flex-wrap: wrap; }
    .backup-inline-form .input { width: 100%; }
  }
</style>

<div class="backup-shell">
  <section class="backup-card">
    <span class="backup-kicker">System Backups</span>
    <h2>Backup Settings</h2>

    <?php if ($message): ?><div class="alert green" style="margin-top:16px"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert red" style="margin-top:16px"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="backup-grid">
      <div class="backup-metric">
        <span>Backup Files</span>
        <strong><?= count($backup_files) ?></strong>
      </div>
      <div class="backup-metric">
        <span>Recent Runs</span>
        <strong><?= count($backup_logs) ?></strong>
      </div>
      <div class="backup-metric">
        <span>Stored Size</span>
        <strong><?= number_format($total_backup_bytes / 1048576, 2) ?> MB</strong>
      </div>
      <div class="backup-metric">
        <span>Restore Points</span>
        <strong><?= count($restore_points) ?></strong>
      </div>
    </div>

  </section>

  <section class="backup-stack">
    <article class="backup-card">
      <div class="backup-section-head">
        <h3>Backup History</h3>
        <form method="post">
          <?= csrf_field() ?>
          <button class="btn btn-primary" type="submit" name="trigger_backup" value="1">Trigger Manual Backup</button>
        </form>
      </div>
      <div class="backup-table-wrap">
        <table class="table">
          <thead><tr><th>File</th><th>Type</th><th>Status</th><th>Requested By</th><th>Created</th><th>Completed</th></tr></thead>
          <tbody>
            <?php foreach ($backup_logs as $log): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($log['filename']) ?></strong>
                  <?php if (!empty($log['restore_label'])): ?><div class="small" style="margin-top:6px"><?= htmlspecialchars($log['restore_label']) ?></div><?php endif; ?>
                  <?php if (!empty($log['details'])): ?><div class="small" style="margin-top:6px"><?= htmlspecialchars($log['details']) ?></div><?php endif; ?>
                </td>
                <td><?= !empty($log['is_restore_point']) ? 'Restore Point' : 'Manual Backup' ?></td>
                <td><span class="badge <?= $log['status'] === 'SUCCESS' ? 'green' : ($log['status'] === 'FAILED' ? 'red' : 'gray') ?>"><?= htmlspecialchars($log['status']) ?></span></td>
                <td><?= htmlspecialchars($log['requested_by_name'] ?? 'System') ?></td>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
                <td><?= htmlspecialchars($log['completed_at'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($backup_logs)): ?><tr><td colspan="6" class="small">No backup runs yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>

    <article class="backup-card">
      <div class="backup-section-head">
        <h3>Restore Points</h3>
        <form method="post" class="backup-inline-form">
          <?= csrf_field() ?>
          <input class="input" type="text" name="restore_label" maxlength="255" placeholder="Restore point label, e.g. Before monthly closing">
          <button class="btn btn-outline" type="submit" name="create_restore_point" value="1">Create Restore Point</button>
        </form>
      </div>
      <div class="backup-table-wrap">
        <table class="table">
          <thead><tr><th>Label</th><th>File</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($restore_points as $point): ?>
              <?php $point_file = backup_file_metadata($point['filename'] ?? ''); ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($point['restore_label'] ?: 'Restore Point') ?></strong>
                  <div class="small" style="margin-top:6px"><?= htmlspecialchars($point['requested_by_name'] ?? 'System') ?></div>
                </td>
                <td><?= htmlspecialchars($point['filename']) ?></td>
                <td><span class="badge <?= $point['status'] === 'SUCCESS' ? 'green' : ($point['status'] === 'FAILED' ? 'red' : 'gray') ?>"><?= htmlspecialchars($point['status']) ?></span></td>
                <td><?= htmlspecialchars($point['created_at']) ?></td>
                <td style="white-space:nowrap">
                  <a class="btn btn-outline" href="<?php echo APP_BASE; ?>/staff/backup_download.php?file=<?= urlencode($point['filename']) ?>">Download</a>
                  <?php if ($point['status'] === 'SUCCESS' && $point_file): ?>
                    <a class="btn btn-primary" href="<?php echo APP_BASE; ?>/staff/backup_restore.php?id=<?= intval($point['id']) ?>">Restore</a>
                  <?php else: ?>
                    <span class="small">Unavailable</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($restore_points)): ?><tr><td colspan="5" class="small">No restore points available yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>

  <section class="backup-stack">
    <article class="backup-card">
      <h3>Backup Files</h3>
      <div class="backup-table-wrap">
        <table class="table">
          <thead><tr><th>Filename</th><th>Size</th><th>Updated</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($backup_files as $file): ?>
              <tr>
                <td><?= htmlspecialchars($file['filename']) ?></td>
                <td><?= number_format((float)($file['size'] ?? 0) / 1048576, 2) ?> MB</td>
                <td><?= htmlspecialchars($file['modified_at'] ?? '-') ?></td>
                <td><a class="btn btn-outline" href="<?php echo APP_BASE; ?>/staff/backup_download.php?file=<?= urlencode($file['filename']) ?>">Download</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($backup_files)): ?><tr><td colspan="4" class="small">No backup files available.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>

    <article class="backup-card">
      <h3>Restore History</h3>
      <div class="backup-table-wrap">
        <table class="table">
          <thead><tr><th>Restore Point</th><th>Status</th><th>Restored By</th><th>Created</th><th>Completed</th></tr></thead>
          <tbody>
            <?php foreach ($restore_logs as $log): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($log['restore_label'] ?: $log['filename']) ?></strong>
                  <?php if (!empty($log['details'])): ?><div class="small" style="margin-top:6px"><?= htmlspecialchars($log['details']) ?></div><?php endif; ?>
                </td>
                <td><span class="badge <?= $log['status'] === 'SUCCESS' ? 'green' : ($log['status'] === 'FAILED' ? 'red' : 'gray') ?>"><?= htmlspecialchars($log['status']) ?></span></td>
                <td><?= htmlspecialchars($log['restored_by_name'] ?? 'System') ?></td>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
                <td><?= htmlspecialchars($log['completed_at'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($restore_logs)): ?><tr><td colspan="5" class="small">No restore activity recorded yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </article>
  </section>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
