SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS backup_logs (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @backup_type_exists = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'backup_logs'
    AND column_name = 'backup_type'
);

SET @backup_type_sql = IF(
  @backup_type_exists = 0,
  'ALTER TABLE backup_logs ADD COLUMN backup_type ENUM(''MANUAL'',''RESTORE_POINT'') NOT NULL DEFAULT ''MANUAL'' AFTER filename',
  'SELECT 1'
);

PREPARE stmt FROM @backup_type_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @restore_label_exists = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'backup_logs'
    AND column_name = 'restore_label'
);

SET @restore_label_sql = IF(
  @restore_label_exists = 0,
  'ALTER TABLE backup_logs ADD COLUMN restore_label VARCHAR(255) NULL AFTER backup_type',
  'SELECT 1'
);

PREPARE stmt FROM @restore_label_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @restore_point_flag_exists = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'backup_logs'
    AND column_name = 'is_restore_point'
);

SET @restore_point_flag_sql = IF(
  @restore_point_flag_exists = 0,
  'ALTER TABLE backup_logs ADD COLUMN is_restore_point TINYINT(1) NOT NULL DEFAULT 0 AFTER restore_label',
  'SELECT 1'
);

PREPARE stmt FROM @restore_point_flag_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @backup_type_index_exists = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'backup_logs'
    AND index_name = 'idx_backup_logs_type'
);

SET @backup_type_index_sql = IF(
  @backup_type_index_exists = 0,
  'ALTER TABLE backup_logs ADD INDEX idx_backup_logs_type (backup_type, is_restore_point)',
  'SELECT 1'
);

PREPARE stmt FROM @backup_type_index_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS restore_logs (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
