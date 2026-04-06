# Restore Point Implementation Guide

This file explains, in simple words, how restore points can be added to the current system.

## What A Restore Point Is

A restore point is a saved backup version of the system that you can return to later.

Example:
- you create a restore point today at `10:00 AM`
- later, wrong data is entered or the database is damaged
- you restore that point
- the system goes back to the state it had at `10:00 AM`

## What The Current System Already Has

The current backup feature already does these things:

- creates a full `.sql` database backup
- stores backup files in the `backups/` folder
- logs backup runs in the `backup_logs` table
- tracks backup status:
  - `RUNNING`
  - `SUCCESS`
  - `FAILED`

So the system already has the main base needed for restore points.

## What Is Missing Right Now

The system does **not** yet have:

- a real restore point label or type
- a restore button in the UI
- a restore history log
- protection before restoring
- a database import process inside the app

## Best Simple Way To Implement Restore Points

The easiest and safest way is:

1. keep using the current SQL backup files
2. allow some backup files to be marked as `restore points`
3. add a restore action that imports the selected SQL file
4. log every restore action

This is better than building a totally new backup format.

## Suggested Database Changes

### 1. Update `backup_logs`

Add fields like:

```sql
ALTER TABLE backup_logs
ADD COLUMN backup_type ENUM('MANUAL','RESTORE_POINT') NOT NULL DEFAULT 'MANUAL',
ADD COLUMN restore_label VARCHAR(255) NULL,
ADD COLUMN is_restore_point TINYINT(1) NOT NULL DEFAULT 0;
```

Meaning:
- `backup_type`: says if this is a normal backup or a restore point
- `restore_label`: custom name like `Before loan status update`
- `is_restore_point`: quick flag to identify restore-point backups

### 2. Add restore history table

```sql
CREATE TABLE restore_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  backup_log_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  restored_by INT NULL,
  status ENUM('RUNNING','SUCCESS','FAILED') NOT NULL DEFAULT 'RUNNING',
  details TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  CONSTRAINT fk_restore_logs_backup FOREIGN KEY (backup_log_id) REFERENCES backup_logs(id) ON DELETE CASCADE,
  CONSTRAINT fk_restore_logs_user FOREIGN KEY (restored_by) REFERENCES users(user_id) ON DELETE SET NULL
);
```

Meaning:
- logs who restored
- logs which file was used
- logs if restore succeeded or failed

## Suggested UI Changes

In `staff/backup_settings.php`, add:

### 1. Create Restore Point button

Example buttons:
- `Trigger Manual Backup`
- `Create Restore Point`

When clicking `Create Restore Point`:
- generate a backup like normal
- mark it as `is_restore_point = 1`
- optionally ask for a short label

Example labels:
- `Before monthly closing`
- `Before tenant update`
- `Before import`

### 2. Restore Point list

Add a new table section:

- Label
- Filename
- Created at
- Created by
- Status
- Action

Action buttons:
- `Download`
- `Restore`

### 3. Restore confirmation dialog

Before restore, force the user to confirm:

- selected file name
- warning that current data will be replaced
- checkbox like:
  - `I understand this will overwrite the current database`

## Suggested Backend Logic

## A. Create Restore Point

This is almost the same as the current backup creation.

Process:

1. user clicks `Create Restore Point`
2. system runs the same SQL export
3. backup is saved into `backups/`
4. `backup_logs` is saved with:
   - `backup_type = 'RESTORE_POINT'`
   - `is_restore_point = 1`
   - optional label

So restore points are just special backups with a clear label.

## B. Restore From Restore Point

### Important

This is dangerous because it can overwrite current data.

So before restoring:

1. create an automatic safety backup first
2. log the restore action
3. then run the restore

Recommended restore flow:

1. user selects a restore point
2. system creates an automatic pre-restore backup
3. system writes a `restore_logs` row with `RUNNING`
4. system imports the SQL file
5. if success:
   - mark restore log as `SUCCESS`
6. if failed:
   - mark restore log as `FAILED`

## C. How Restore Can Be Done Technically

There are 2 common ways:

### Option 1. Use MySQL command line

Example idea:

```bash
mysql -u USERNAME -p DATABASE_NAME < backup_file.sql
```

Pros:
- fast
- reliable for big SQL files

Cons:
- needs shell access
- must securely handle DB credentials

### Option 2. Import SQL through PHP

Read the `.sql` file and execute statements from PHP.

Pros:
- fully inside app code

Cons:
- harder for large files
- more memory/time issues
- less reliable than MySQL command line

## Recommended Choice

For this project, use:

- current PHP backup export for creating backups
- MySQL command line import for restoring

That is the simpler and safer real-world approach.

## Security Rules For Restore Feature

Restore should be restricted to:

- `SUPER_ADMIN` only

Reason:
- restoring affects the whole database
- it is not a tenant-only action
- one bad restore can affect all tenants

Also add:

- CSRF protection
- confirmation step
- action logging
- pre-restore automatic backup

## Tenant Warning

The current backup system creates a **full database backup**.

That means restore will also restore:

- all tenants
- all users
- all customers
- all loans
- all payments
- all logs

So this is not a tenant-only restore.

If you want tenant-only restore later, that is a bigger feature and should be designed separately.

## Recommended Minimal Version

If you want a practical first version, implement only:

1. `Create Restore Point` button
2. label restore point in `backup_logs`
3. `Restore` button for `SUPER_ADMIN`
4. confirmation prompt
5. automatic pre-restore backup
6. `restore_logs` history table

This is enough to make restore points useful without overcomplicating the module.

## Recommended File Changes

Likely files to update:

- `staff/backup_settings.php`
- `includes/backup_helpers.php`
- `schema.sql`
- `setup/loan_management_complete.sql`

Possible new file:

- `staff/backup_restore.php`

## Final Recommendation

The best design for your current system is:

- treat restore points as **named SQL backups**
- keep them in the same `backups/` folder
- mark them clearly in the database
- allow only `SUPER_ADMIN` to restore
- always create a safety backup before restore

That gives you restore point support with minimal changes and matches the current backup architecture.
