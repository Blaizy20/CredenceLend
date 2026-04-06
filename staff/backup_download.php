<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/backup_helpers.php';

require_login();
require_permission('manage_backups');

$requested_file = basename((string)($_GET['file'] ?? ''));
if ($requested_file === '' || !preg_match('/^[A-Za-z0-9._-]+\.sql$/', $requested_file)) {
  http_response_code(404);
  echo "Backup file not found.";
  exit;
}

$file_path = backup_storage_dir() . DIRECTORY_SEPARATOR . $requested_file;
if (!is_file($file_path)) {
  http_response_code(404);
  echo "Backup file not found.";
  exit;
}

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $requested_file . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
