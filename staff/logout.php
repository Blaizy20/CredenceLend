<?php
require_once __DIR__ . '/../includes/auth.php';
if (is_password_setup_required()) {
  header("Location: " . password_setup_url());
  exit;
}
logout_user();
header("Location: " . APP_BASE . "/staff/login.php");
exit;
?>
