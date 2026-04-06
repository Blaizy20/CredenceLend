<?php
require_once __DIR__ . '/db.php';

function backup_storage_dir() {
  return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'backups';
}

function ensure_backup_storage_dir() {
  $directory = backup_storage_dir();
  if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
  }
  return $directory;
}

function backup_log_column_exists($column_name) {
  $row = fetch_one(q(
    "SELECT COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'backup_logs'
       AND COLUMN_NAME = ?
     LIMIT 1",
    "s",
    [$column_name]
  ));

  return !empty($row['COLUMN_NAME']);
}

function ensure_backup_logs_table() {
  q(
    "CREATE TABLE IF NOT EXISTS backup_logs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      filename VARCHAR(255) NOT NULL,
      backup_type ENUM('MANUAL','RESTORE_POINT') NOT NULL DEFAULT 'MANUAL',
      restore_label VARCHAR(255) NULL,
      is_restore_point TINYINT(1) NOT NULL DEFAULT 0,
      status ENUM('RUNNING','SUCCESS','FAILED') NOT NULL DEFAULT 'RUNNING',
      details TEXT NULL,
      file_size BIGINT NULL,
      requested_by INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      completed_at DATETIME NULL,
      KEY idx_backup_logs_status (status),
      KEY idx_backup_logs_type (backup_type, is_restore_point),
      KEY idx_backup_logs_created_at (created_at),
      KEY idx_backup_logs_requested_by (requested_by),
      CONSTRAINT fk_backup_logs_user FOREIGN KEY (requested_by) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB"
  );

  if (!backup_log_column_exists('backup_type')) {
    q("ALTER TABLE backup_logs ADD COLUMN backup_type ENUM('MANUAL','RESTORE_POINT') NOT NULL DEFAULT 'MANUAL' AFTER filename");
  }

  if (!backup_log_column_exists('restore_label')) {
    q("ALTER TABLE backup_logs ADD COLUMN restore_label VARCHAR(255) NULL AFTER backup_type");
  }

  if (!backup_log_column_exists('is_restore_point')) {
    q("ALTER TABLE backup_logs ADD COLUMN is_restore_point TINYINT(1) NOT NULL DEFAULT 0 AFTER restore_label");
  }

  $type_index = fetch_one(q(
    "SELECT INDEX_NAME
     FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'backup_logs'
       AND INDEX_NAME = 'idx_backup_logs_type'
     LIMIT 1"
  ));

  if (!$type_index) {
    q("ALTER TABLE backup_logs ADD INDEX idx_backup_logs_type (backup_type, is_restore_point)");
  }
}

function ensure_restore_logs_table() {
  q(
    "CREATE TABLE IF NOT EXISTS restore_logs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      backup_log_id INT NOT NULL,
      filename VARCHAR(255) NOT NULL,
      restored_by INT NULL,
      status ENUM('RUNNING','SUCCESS','FAILED') NOT NULL DEFAULT 'RUNNING',
      details TEXT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      completed_at DATETIME NULL,
      KEY idx_restore_logs_status (status),
      KEY idx_restore_logs_backup (backup_log_id),
      KEY idx_restore_logs_created_at (created_at),
      CONSTRAINT fk_restore_logs_backup FOREIGN KEY (backup_log_id) REFERENCES backup_logs(id) ON DELETE CASCADE,
      CONSTRAINT fk_restore_logs_user FOREIGN KEY (restored_by) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB"
  );
}

function fetch_backup_logs($limit = 25) {
  ensure_backup_logs_table();
  return fetch_all(q(
    "SELECT
        bl.*,
        u.full_name AS requested_by_name
     FROM backup_logs bl
     LEFT JOIN users u ON u.user_id = bl.requested_by
     ORDER BY bl.created_at DESC
     LIMIT ?",
    "i",
    [max(1, intval($limit))]
  )) ?: [];
}

function fetch_restore_points($limit = 25) {
  ensure_backup_logs_table();
  return fetch_all(q(
    "SELECT
        bl.*,
        u.full_name AS requested_by_name
     FROM backup_logs bl
     LEFT JOIN users u ON u.user_id = bl.requested_by
     WHERE bl.is_restore_point = 1
     ORDER BY bl.created_at DESC
     LIMIT ?",
    "i",
    [max(1, intval($limit))]
  )) ?: [];
}

function fetch_restore_logs($limit = 25) {
  ensure_restore_logs_table();
  return fetch_all(q(
    "SELECT
        rl.*,
        bl.restore_label,
        u.full_name AS restored_by_name
     FROM restore_logs rl
     LEFT JOIN backup_logs bl ON bl.id = rl.backup_log_id
     LEFT JOIN users u ON u.user_id = rl.restored_by
     ORDER BY rl.created_at DESC
     LIMIT ?",
    "i",
    [max(1, intval($limit))]
  )) ?: [];
}

function list_backup_files() {
  $directory = ensure_backup_storage_dir();
  $files = glob($directory . DIRECTORY_SEPARATOR . '*.sql') ?: [];
  rsort($files, SORT_NATURAL);

  return array_map(static function ($file_path) {
    return [
      'filename' => basename($file_path),
      'path' => $file_path,
      'size' => is_file($file_path) ? filesize($file_path) : 0,
      'modified_at' => is_file($file_path) ? date('Y-m-d H:i:s', filemtime($file_path)) : null,
    ];
  }, $files);
}

function backup_file_metadata($filename) {
  $filename = basename((string) $filename);
  if ($filename === '') {
    return null;
  }

  $file_path = backup_storage_dir() . DIRECTORY_SEPARATOR . $filename;
  if (!is_file($file_path)) {
    return null;
  }

  return [
    'filename' => $filename,
    'path' => $file_path,
    'size' => filesize($file_path),
    'modified_at' => date('Y-m-d H:i:s', filemtime($file_path)),
  ];
}

function backup_log_create($filename, $requested_by, array $options = []) {
  ensure_backup_logs_table();
  $backup_type = strtoupper(trim((string) ($options['backup_type'] ?? 'MANUAL')));
  if (!in_array($backup_type, ['MANUAL', 'RESTORE_POINT'], true)) {
    $backup_type = 'MANUAL';
  }

  $restore_label = trim((string) ($options['restore_label'] ?? ''));
  $restore_label = $restore_label !== '' ? substr($restore_label, 0, 255) : null;
  $is_restore_point = !empty($options['is_restore_point']) ? 1 : 0;

  q(
    "INSERT INTO backup_logs (filename, backup_type, restore_label, is_restore_point, status, requested_by)
     VALUES (?, ?, ?, ?, 'RUNNING', ?)",
    "sssii",
    [$filename, $backup_type, $restore_label, $is_restore_point, intval($requested_by)]
  );
  return intval(db()->insert_id);
}

function backup_log_complete($log_id, $status, $details = null, $file_size = null) {
  ensure_backup_logs_table();
  $normalized_file_size = $file_size !== null ? intval($file_size) : 0;
  q(
    "UPDATE backup_logs
     SET status = ?, details = ?, file_size = ?, completed_at = NOW()
     WHERE id = ?",
    "ssii",
    [$status, $details, $normalized_file_size, intval($log_id)]
  );
}

function get_backup_log($backup_log_id) {
  ensure_backup_logs_table();
  return fetch_one(q(
    "SELECT
        bl.*,
        u.full_name AS requested_by_name
     FROM backup_logs bl
     LEFT JOIN users u ON u.user_id = bl.requested_by
     WHERE bl.id = ?
     LIMIT 1",
    "i",
    [intval($backup_log_id)]
  ));
}

function ensure_backup_log_reference(array $backup_log) {
  ensure_backup_logs_table();

  $backup_log_id = intval($backup_log['id'] ?? 0);
  if ($backup_log_id <= 0) {
    return 0;
  }

  $existing = fetch_one(q(
    "SELECT id
     FROM backup_logs
     WHERE id = ?
     LIMIT 1",
    "i",
    [$backup_log_id]
  ));

  if ($existing) {
    q(
      "UPDATE backup_logs
       SET filename = ?,
           backup_type = ?,
           restore_label = ?,
           is_restore_point = ?,
           status = ?,
           details = ?,
           file_size = ?,
           requested_by = ?,
           completed_at = ?
       WHERE id = ?",
      "sssissiisi",
      [
        $backup_log['filename'] ?? '',
        $backup_log['backup_type'] ?? 'RESTORE_POINT',
        $backup_log['restore_label'] ?? null,
        intval($backup_log['is_restore_point'] ?? 1),
        $backup_log['status'] ?? 'SUCCESS',
        $backup_log['details'] ?? 'Restore point metadata refreshed after database restore.',
        intval($backup_log['file_size'] ?? 0),
        intval($backup_log['requested_by'] ?? 0) ?: null,
        $backup_log['completed_at'] ?? date('Y-m-d H:i:s'),
        $backup_log_id,
      ]
    );
    return $backup_log_id;
  }

  q(
    "INSERT INTO backup_logs (
       id,
       filename,
       backup_type,
       restore_label,
       is_restore_point,
       status,
       details,
       file_size,
       requested_by,
       created_at,
       completed_at
     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    "isssissiiss",
    [
      $backup_log_id,
      $backup_log['filename'] ?? '',
      $backup_log['backup_type'] ?? 'RESTORE_POINT',
      $backup_log['restore_label'] ?? null,
      intval($backup_log['is_restore_point'] ?? 1),
      $backup_log['status'] ?? 'SUCCESS',
      $backup_log['details'] ?? 'Restore point reference re-created after database restore.',
      intval($backup_log['file_size'] ?? 0),
      intval($backup_log['requested_by'] ?? 0) ?: null,
      $backup_log['created_at'] ?? date('Y-m-d H:i:s'),
      $backup_log['completed_at'] ?? date('Y-m-d H:i:s'),
    ]
  );

  return $backup_log_id;
}

function restore_log_create($backup_log_id, $filename, $restored_by, $details = null) {
  ensure_restore_logs_table();
  q(
    "INSERT INTO restore_logs (backup_log_id, filename, restored_by, status, details)
     VALUES (?, ?, ?, 'RUNNING', ?)",
    "isis",
    [intval($backup_log_id), basename((string) $filename), intval($restored_by), $details]
  );

  return intval(db()->insert_id);
}

function restore_log_complete($restore_log_id, $status, $details = null) {
  ensure_restore_logs_table();
  q(
    "UPDATE restore_logs
     SET status = ?, details = ?, completed_at = NOW()
     WHERE id = ?",
    "ssi",
    [$status, $details, intval($restore_log_id)]
  );
}

function ensure_restore_log_result($restore_log_id, $backup_log_id, $filename, $restored_by, $status, $details) {
  ensure_restore_logs_table();

  $existing = fetch_one(q(
    "SELECT id
     FROM restore_logs
     WHERE id = ?
     LIMIT 1",
    "i",
    [intval($restore_log_id)]
  ));

  if ($existing) {
    restore_log_complete($restore_log_id, $status, $details);
    return intval($existing['id']);
  }

  q(
    "INSERT INTO restore_logs (backup_log_id, filename, restored_by, status, details, completed_at)
     VALUES (?, ?, ?, ?, ?, NOW())",
    "isiss",
    [intval($backup_log_id), basename((string) $filename), intval($restored_by), $status, $details]
  );

  return intval(db()->insert_id);
}

function backup_sql_value($conn, $value) {
  if ($value === null) {
    return 'NULL';
  }

  if (is_bool($value)) {
    return $value ? '1' : '0';
  }

  if (is_int($value) || is_float($value)) {
    return (string) $value;
  }

  return "'" . $conn->real_escape_string((string) $value) . "'";
}

function backup_filename_prefix($is_restore_point = false) {
  return $is_restore_point ? 'restore_point_' : 'backup_';
}

function create_database_backup($requested_by, array $options = []) {
  $conn = db();
  $directory = ensure_backup_storage_dir();
  $is_restore_point = !empty($options['is_restore_point']);
  $prefix = backup_filename_prefix($is_restore_point);
  $filename = $prefix . date('Ymd_His') . '.sql';
  $file_path = $directory . DIRECTORY_SEPARATOR . $filename;
  $backup_type = $is_restore_point ? 'RESTORE_POINT' : 'MANUAL';
  $restore_label = trim((string) ($options['restore_label'] ?? ''));
  $header_title = $is_restore_point ? 'CredenceLend restore point backup' : 'CredenceLend manual backup';
  $completion_message = trim((string) ($options['completion_message'] ?? ''));
  if ($completion_message === '') {
    $completion_message = $is_restore_point
      ? 'Restore point created successfully.'
      : 'Backup completed successfully.';
  }

  $log_id = backup_log_create($filename, $requested_by, [
    'backup_type' => $backup_type,
    'restore_label' => $restore_label,
    'is_restore_point' => $is_restore_point,
  ]);

  try {
    $handle = fopen($file_path, 'wb');
    if ($handle === false) {
      throw new RuntimeException('Unable to create backup file.');
    }

    fwrite($handle, "-- {$header_title}\n");
    fwrite($handle, "-- Generated at " . date('Y-m-d H:i:s') . "\n");
    if ($restore_label !== '') {
      fwrite($handle, "-- Label: {$restore_label}\n");
    }
    fwrite($handle, "\nSET NAMES utf8mb4;\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    $tables_result = $conn->query("SHOW TABLES");
    if (!$tables_result) {
      throw new RuntimeException('Unable to enumerate database tables.');
    }

    while ($table_row = $tables_result->fetch_array(MYSQLI_NUM)) {
      $table_name = $table_row[0];
      $table_name_sql = '`' . str_replace('`', '``', $table_name) . '`';

      $create_row = fetch_one(q("SHOW CREATE TABLE {$table_name_sql}"));
      $create_sql = $create_row['Create Table'] ?? null;
      if ($create_sql === null) {
        throw new RuntimeException('Unable to read schema for table ' . $table_name . '.');
      }

      fwrite($handle, "--\n-- Table structure for {$table_name}\n--\n");
      fwrite($handle, "DROP TABLE IF EXISTS {$table_name_sql};\n");
      fwrite($handle, $create_sql . ";\n\n");

      $data_result = $conn->query("SELECT * FROM {$table_name_sql}");
      if (!$data_result) {
        throw new RuntimeException('Unable to export data for table ' . $table_name . '.');
      }

      if ($data_result->num_rows > 0) {
        fwrite($handle, "--\n-- Data for {$table_name}\n--\n");
        while ($row = $data_result->fetch_assoc()) {
          $columns = array_map(static function ($column) {
            return '`' . str_replace('`', '``', $column) . '`';
          }, array_keys($row));
          $values = array_map(static function ($value) use ($conn) {
            return backup_sql_value($conn, $value);
          }, array_values($row));

          fwrite(
            $handle,
            "INSERT INTO {$table_name_sql} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n"
          );
        }
        fwrite($handle, "\n");
      }
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);

    $file_size = is_file($file_path) ? filesize($file_path) : 0;
    backup_log_complete($log_id, 'SUCCESS', $completion_message, $file_size);

    return [
      'success' => true,
      'filename' => $filename,
      'path' => $file_path,
      'file_size' => $file_size,
      'log_id' => $log_id,
      'backup_type' => $backup_type,
      'restore_label' => $restore_label,
    ];
  } catch (Throwable $e) {
    if (isset($handle) && is_resource($handle)) {
      fclose($handle);
    }

    if (is_file($file_path)) {
      @unlink($file_path);
    }

    backup_log_complete($log_id, 'FAILED', $e->getMessage(), null);

    return [
      'success' => false,
      'message' => $e->getMessage(),
      'log_id' => $log_id,
      'backup_type' => $backup_type,
      'restore_label' => $restore_label,
    ];
  }
}

function create_manual_backup($requested_by) {
  return create_database_backup($requested_by, [
    'backup_type' => 'MANUAL',
    'completion_message' => 'Backup completed successfully.',
  ]);
}

function create_restore_point($requested_by, $restore_label = '') {
  $restore_label = trim((string) $restore_label);
  if ($restore_label === '') {
    $restore_label = 'Restore point ' . date('Y-m-d H:i:s');
  }

  return create_database_backup($requested_by, [
    'backup_type' => 'RESTORE_POINT',
    'restore_label' => $restore_label,
    'is_restore_point' => true,
    'completion_message' => 'Restore point created successfully.',
  ]);
}

function backup_mysql_client_candidates() {
  return [
    'C:\\xampp\\mysql\\bin\\mysql.exe',
    'C:\\xampp\\mariadb\\bin\\mysql.exe',
    'mysql',
  ];
}

function find_mysql_client_binary() {
  foreach (backup_mysql_client_candidates() as $candidate) {
    if (preg_match('/[\\\\\\/]/', $candidate)) {
      if (is_file($candidate)) {
        return $candidate;
      }
      continue;
    }

    return $candidate;
  }

  return null;
}

function run_mysql_restore_import($file_path) {
  global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

  if (!function_exists('proc_open')) {
    throw new RuntimeException('Restore is unavailable because PHP process control is disabled.');
  }

  $mysql_binary = find_mysql_client_binary();
  if ($mysql_binary === null) {
    throw new RuntimeException('MySQL client not found. Install or expose mysql.exe before using restore.');
  }

  $file_path = (string) $file_path;
  if (!is_file($file_path)) {
    throw new RuntimeException('Restore file not found.');
  }

  $command = escapeshellarg($mysql_binary)
    . ' --protocol=TCP'
    . ' --host=' . escapeshellarg((string) $DB_HOST)
    . ' --user=' . escapeshellarg((string) $DB_USER)
    . ($DB_PASS !== '' ? ' --password=' . escapeshellarg((string) $DB_PASS) : ' --skip-password')
    . ' --default-character-set=utf8mb4 '
    . escapeshellarg((string) $DB_NAME);

  $descriptor_spec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
  ];

  $pipes = [];
  $process = proc_open($command, $descriptor_spec, $pipes, dirname($file_path));
  if (!is_resource($process)) {
    throw new RuntimeException('Unable to start the restore process.');
  }

  $handle = fopen($file_path, 'rb');
  if ($handle === false) {
    foreach ($pipes as $pipe) {
      if (is_resource($pipe)) {
        fclose($pipe);
      }
    }
    proc_close($process);
    throw new RuntimeException('Unable to read the restore file.');
  }

  stream_copy_to_stream($handle, $pipes[0]);
  fclose($handle);
  fclose($pipes[0]);

  $stdout = stream_get_contents($pipes[1]);
  $stderr = stream_get_contents($pipes[2]);
  fclose($pipes[1]);
  fclose($pipes[2]);

  $exit_code = proc_close($process);
  if ($exit_code !== 0) {
    $error_message = trim((string) ($stderr !== '' ? $stderr : $stdout));
    throw new RuntimeException($error_message !== '' ? $error_message : 'Restore process failed.');
  }

  return [
    'stdout' => trim((string) $stdout),
    'stderr' => trim((string) $stderr),
  ];
}

function restore_from_backup_log($backup_log_id, $restored_by) {
  $backup_log = get_backup_log($backup_log_id);
  if (!$backup_log) {
    throw new RuntimeException('Restore point not found.');
  }

  if (intval($backup_log['is_restore_point'] ?? 0) !== 1) {
    throw new RuntimeException('Only restore points can be restored from this screen.');
  }

  if (($backup_log['status'] ?? '') !== 'SUCCESS') {
    throw new RuntimeException('Only successful restore points can be restored.');
  }

  $file = backup_file_metadata($backup_log['filename'] ?? '');
  if (!$file) {
    throw new RuntimeException('The selected restore point file is missing from backups storage.');
  }

  $restore_label = trim((string) ($backup_log['restore_label'] ?? $backup_log['filename']));
  $restore_log_id = restore_log_create(
    intval($backup_log['id']),
    $file['filename'],
    $restored_by,
    'Restore started for ' . $restore_label . '.'
  );

  $safety_backup = create_database_backup($restored_by, [
    'backup_type' => 'MANUAL',
    'restore_label' => 'Pre-restore safety backup before ' . $restore_label,
    'completion_message' => 'Automatic safety backup created before restore.',
  ]);

  if (empty($safety_backup['success'])) {
    $details = 'Restore stopped because the pre-restore safety backup failed. ' . trim((string) ($safety_backup['message'] ?? ''));
    ensure_restore_log_result($restore_log_id, intval($backup_log['id']), $file['filename'], $restored_by, 'FAILED', $details);
    throw new RuntimeException($details);
  }

  try {
    run_mysql_restore_import($file['path']);

    ensure_backup_logs_table();
    ensure_restore_logs_table();
    ensure_backup_log_reference($backup_log);
    require_once __DIR__ . '/loan_helpers.php';
    log_system_activity(
      'RESTORE_POINT_RESTORED',
      'Database restored from restore point ' . $restore_label . ' using file ' . $file['filename'] . '.'
    );

    $details = 'Restore completed successfully from ' . $file['filename'] . '. Safety backup: ' . ($safety_backup['filename'] ?? 'N/A') . '.';
    ensure_restore_log_result($restore_log_id, intval($backup_log['id']), $file['filename'], $restored_by, 'SUCCESS', $details);

    return [
      'success' => true,
      'message' => $details,
      'restore_log_id' => $restore_log_id,
      'safety_backup' => $safety_backup,
      'backup_log' => $backup_log,
    ];
  } catch (Throwable $e) {
    ensure_backup_logs_table();
    ensure_restore_logs_table();
    ensure_backup_log_reference($backup_log);
    require_once __DIR__ . '/loan_helpers.php';
    log_system_activity(
      'RESTORE_POINT_FAILED',
      'Restore attempt failed for restore point ' . $restore_label . ' using file ' . $file['filename'] . '. Reason: ' . $e->getMessage()
    );

    ensure_restore_log_result(
      $restore_log_id,
      intval($backup_log['id']),
      $file['filename'],
      $restored_by,
      'FAILED',
      'Restore failed. Safety backup: ' . ($safety_backup['filename'] ?? 'N/A') . '. Reason: ' . $e->getMessage()
    );
    throw $e;
  }
}
