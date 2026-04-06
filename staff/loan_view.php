<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_login();
require_permission('view_loan_details');
$custom_loan_config_feature_access = [
  'allowed' => false,
  'message' => subscription_feature_label('custom_loan_config') . ' is unavailable.',
];
$can_manage_custom_loan_config = false;

$id = intval($_GET['id'] ?? 0);
enforce_tenant_resource_access('loans', 'loan_id', $id);

$fetch_loan = function () use ($id) {
  return fetch_one(q(
    "SELECT l.*, c.customer_no, CONCAT(c.first_name,' ',c.last_name) AS customer_name, c.contact_no, u.full_name AS officer_name
     FROM loans l
     JOIN customers c ON c.customer_id=l.customer_id AND c.tenant_id=l.tenant_id
     LEFT JOIN users u ON u.user_id=l.loan_officer_id AND u.tenant_id=l.tenant_id
     WHERE " . tenant_condition('l.tenant_id') . " AND l.loan_id=?",
    tenant_types("i"),
    tenant_params([$id])
  ));
};

$loan = $fetch_loan();
if (!$loan) { http_response_code(404); echo "Loan not found"; exit; }
$custom_loan_config_feature_access = current_tenant_feature_access('custom_loan_config', intval($loan['tenant_id'] ?? 0));
$can_manage_custom_loan_config = can_access('update_loan_terms') && !empty($custom_loan_config_feature_access['allowed']);

if (in_array($loan['status'], ['APPROVED','ACTIVE','OVERDUE'], true)) {
  recalc_loan($loan['loan_id']);
  $loan = $fetch_loan();
}

$reqs = fetch_all(q(
  "SELECT * FROM requirements WHERE " . tenant_condition('tenant_id') . " AND loan_id=? ORDER BY uploaded_at DESC",
  tenant_types("i"),
  tenant_params([$id])
));

$payment_filter_from = trim($_GET['from'] ?? '');
$payment_filter_to = trim($_GET['to'] ?? '');
$payment_filter_range = trim($_GET['range'] ?? '');
$payment_params = tenant_params([$id]);
$payment_types = tenant_types('i');
$payment_where = '';

if ($payment_filter_range === 'week') {
  $payment_where = " AND DATE(p.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($payment_filter_range === 'month') {
  $payment_where = " AND DATE(p.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
} elseif ($payment_filter_from && $payment_filter_to) {
  $payment_where = " AND DATE(p.payment_date) BETWEEN ? AND ?";
  $payment_types .= 'ss';
  $payment_params[] = $payment_filter_from;
  $payment_params[] = $payment_filter_to;
}

$payments = fetch_all(q(
  "SELECT p.*, u.full_name AS cashier_name
   FROM payments p
   LEFT JOIN users u ON u.user_id=p.received_by
   WHERE " . tenant_condition('p.tenant_id') . " AND p.loan_id=?$payment_where
   ORDER BY p.payment_date DESC, p.payment_id DESC",
  $payment_types,
  $payment_params
));

$err = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['ci_review'])) {
  require_permission('review_applications');
  if (is_system_admin()) {
    q("UPDATE loans SET status='CI_REVIEWED', ci_by=?, ci_at=NOW(), notes=? WHERE loan_id=? AND status='PENDING'", "isi", [$_SESSION['user_id'], trim($_POST['ci_notes'] ?? ''), $id]);
  } else {
    q("UPDATE loans SET status='CI_REVIEWED', ci_by=?, ci_at=NOW(), notes=? WHERE tenant_id=? AND loan_id=? AND status='PENDING'", "isii", [$_SESSION['user_id'], trim($_POST['ci_notes'] ?? ''), require_current_tenant_id(), $id]);
  }
  log_activity('CI Review', 'Loan marked as CI reviewed - ' . trim($_POST['ci_notes'] ?? ''), $id, $loan['customer_id'], $loan['reference_no']);
  header("Location: " . APP_BASE . "/staff/loan_view.php?id=$id");
  exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['manager_decision'])) {
  require_permission('approve_applications');
  $decision = $_POST['decision'] ?? '';
  if ($decision === 'APPROVE') {
    $due = date('Y-m-d', strtotime("+" . intval($loan['term_months']) . " months"));
    if (is_system_admin()) {
      q("UPDATE loans SET status='ACTIVE', manager_by=?, manager_at=NOW(), activated_at=NOW(), due_date=?, notes=? WHERE loan_id=? AND status IN ('CI_REVIEWED','PENDING')", "issi", [$_SESSION['user_id'], $due, trim($_POST['manager_notes'] ?? ''), $id]);
    } else {
      q("UPDATE loans SET status='ACTIVE', manager_by=?, manager_at=NOW(), activated_at=NOW(), due_date=?, notes=? WHERE tenant_id=? AND loan_id=? AND status IN ('CI_REVIEWED','PENDING')", "issii", [$_SESSION['user_id'], $due, trim($_POST['manager_notes'] ?? ''), require_current_tenant_id(), $id]);
    }
    log_activity('Loan Approved', 'Loan approved and activated - ' . trim($_POST['manager_notes'] ?? ''), $id, $loan['customer_id'], $loan['reference_no']);
    recalc_loan($id);
    header("Location: " . APP_BASE . "/staff/loan_view.php?id=$id");
    exit;
  } elseif ($decision === 'DENY') {
    if (is_system_admin()) {
      q("UPDATE loans SET status='DENIED', manager_by=?, manager_at=NOW(), notes=? WHERE loan_id=? AND status IN ('CI_REVIEWED','PENDING')", "isi", [$_SESSION['user_id'], trim($_POST['manager_notes'] ?? ''), $id]);
    } else {
      q("UPDATE loans SET status='DENIED', manager_by=?, manager_at=NOW(), notes=? WHERE tenant_id=? AND loan_id=? AND status IN ('CI_REVIEWED','PENDING')", "isii", [$_SESSION['user_id'], trim($_POST['manager_notes'] ?? ''), require_current_tenant_id(), $id]);
    }
    log_activity('Loan Denied', 'Loan denied - ' . trim($_POST['manager_notes'] ?? ''), $id, $loan['customer_id'], $loan['reference_no']);
    header("Location: " . APP_BASE . "/staff/loan_view.php?id=$id");
    exit;
  } else {
    $err = 'Invalid decision.';
  }
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_terms'])) {
  require_permission('update_loan_terms');
  if (!$can_manage_custom_loan_config) {
    $err = $custom_loan_config_feature_access['message'];
  } else {
    $update_type = $_POST['update_type'] ?? '';
    $rate = floatval($_POST['interest_rate_update'] ?? 0);
    $payment_term = trim($_POST['payment_term_update'] ?? '');
    if ($update_type === 'interest_only' && $rate > 0) {
      if (is_system_admin()) {
        q("UPDATE loans SET interest_rate=? WHERE loan_id=?", "di", [$rate, $id]);
      } else {
        q("UPDATE loans SET interest_rate=? WHERE tenant_id=? AND loan_id=?", "dii", [$rate, require_current_tenant_id(), $id]);
      }
      log_activity('Interest Rate Updated', 'Interest rate changed to ' . number_format($rate, 2) . '%', $id, $loan['customer_id'], $loan['reference_no']);
      recalc_loan($id);
      $loan = $fetch_loan();
      $ok = 'Interest rate updated.';
    } elseif ($update_type === 'term_only' && in_array($payment_term, ['daily','weekly','semi_monthly','monthly'], true)) {
      if (is_system_admin()) {
        q("UPDATE loans SET payment_term=? WHERE loan_id=?", "si", [$payment_term, $id]);
      } else {
        q("UPDATE loans SET payment_term=? WHERE tenant_id=? AND loan_id=?", "sii", [$payment_term, require_current_tenant_id(), $id]);
      }
      $term_names = ['daily' => 'Daily', 'weekly' => 'Weekly', 'semi_monthly' => 'Semi-Monthly', 'monthly' => 'Monthly'];
      log_activity('Payment Term Updated', 'Payment term changed to ' . $term_names[$payment_term], $id, $loan['customer_id'], $loan['reference_no']);
      recalc_loan($id);
      $loan = $fetch_loan();
      $ok = 'Payment term updated.';
    } else {
      $err = 'Invalid loan term update.';
    }
  }
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['assign_officer'])) {
  require_permission('assign_loan_officer');
  $officer_id = intval($_POST['loan_officer_id'] ?? 0);
  if ($officer_id <= 0) {
    $err = 'Please select a loan officer.';
  } else {
    $officer = fetch_one(q("SELECT user_id, full_name FROM users WHERE role='LOAN_OFFICER' AND is_active=1 AND " . tenant_condition('tenant_id') . " AND user_id=?", tenant_types("i"), tenant_params([$officer_id])));
    if (!$officer) {
      $err = 'Loan officer not found.';
    } else {
      if (is_system_admin()) {
        q("UPDATE loans SET loan_officer_id=? WHERE loan_id=?", "ii", [$officer_id, $id]);
      } else {
        q("UPDATE loans SET loan_officer_id=? WHERE tenant_id=? AND loan_id=?", "iii", [$officer_id, require_current_tenant_id(), $id]);
      }
      log_activity('Loan Officer Assigned', 'Loan officer assigned to ' . htmlspecialchars($officer['full_name']), $id, $loan['customer_id'], $loan['reference_no']);
      header("Location: " . APP_BASE . "/staff/loan_view.php?id=$id");
      exit;
    }
  }
}

$loan_officers = can_access('assign_loan_officer')
  ? fetch_all(q("SELECT user_id, full_name FROM users WHERE role='LOAN_OFFICER' AND is_active=1 AND " . tenant_condition('tenant_id') . " ORDER BY full_name", tenant_types(), tenant_params()))
  : [];

$comaker_full_name = '';
$comaker_id_type = '';
$comaker_contact = '';
$comaker_email = '';
$comaker_address = '';
if (!empty($loan['notes']) && strpos($loan['notes'], 'Co-maker:') !== false) {
  preg_match('/Co-maker:\s*([^|]+)/', $loan['notes'], $m); if ($m) $comaker_full_name = trim($m[1]);
  preg_match('/ID Type:\s*([^|]+)/', $loan['notes'], $m); if ($m) $comaker_id_type = trim($m[1]);
  preg_match('/Contact:\s*([^|]+)/', $loan['notes'], $m); if ($m) $comaker_contact = trim($m[1]);
  preg_match('/Email:\s*([^|]+)/', $loan['notes'], $m); if ($m) $comaker_email = trim($m[1]);
  preg_match('/Address:\s*(.+)$/', $loan['notes'], $m); if ($m) $comaker_address = trim($m[1]);
}

$comaker_reqs = array_filter($reqs, function($r) {
  return strpos($r['requirement_code'], 'COMAKER') === 0;
});

$pt = trim($loan['payment_term'] ?? '');
$payment_term_label = $pt === '' ? 'Not Set' : (['daily' => 'Daily', 'weekly' => 'Weekly', 'semi_monthly' => 'Semi Monthly', 'monthly' => 'Monthly'][$pt] ?? ucfirst(str_replace('_', ' ', $pt)));
$interest_rate_label = floatval($loan['interest_rate']) > 0 ? number_format((float)$loan['interest_rate'], 2) . '%' : '—';
$display_late_fee = $loan['status'] === 'OVERDUE' ? calculate_late_fee($loan['loan_id'], $loan['payment_term'], null) : 0;

$title="Loan Details"; $active="loans";
include __DIR__ . '/_layout_top.php';
?>
<style>
body{background:radial-gradient(circle at top,rgba(14,165,233,.12),transparent 30%),linear-gradient(180deg,#020617 0%,#081121 42%,#0f172a 100%);color:#e5eefb}
.topbar{background:linear-gradient(135deg,#081121,#0f1b35)!important;border-bottom:1px solid rgba(148,163,184,.14);box-shadow:0 18px 40px rgba(2,6,23,.35)}
.topbar .small,.topbar a.btn.btn-outline{color:#d8e4f5!important;border-color:rgba(148,163,184,.24)!important}.layout,.main{background:transparent}
.sidebar{background:rgba(4,10,24,.84);border-right:1px solid rgba(148,163,184,.12);backdrop-filter:blur(16px)}.sidebar h3{color:#7f93b0}.sidebar a{color:#d7e3f4}.sidebar a.active,.sidebar a:hover{background:linear-gradient(135deg,rgba(14,165,233,.18),rgba(59,130,246,.2));color:#f8fbff}
.loan-shell{display:grid;gap:20px}.lv-card{border-radius:24px;border:1px solid rgba(148,163,184,.16);background:radial-gradient(circle at top left,rgba(56,189,248,.12),transparent 26%),linear-gradient(180deg,rgba(15,23,42,.96),rgba(8,15,30,.97));box-shadow:0 24px 60px rgba(2,6,23,.34);padding:24px}
.lv-hero{display:grid;gap:18px;grid-template-columns:minmax(0,1.6fr) minmax(220px,.8fr)}.lv-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid rgba(125,211,252,.24);background:rgba(14,165,233,.12);color:#d8f4ff;font-size:12px;letter-spacing:.08em;text-transform:uppercase}
.lv-hero h2{margin:10px 0 0;font-size:clamp(30px,4vw,44px);line-height:1;letter-spacing:-.04em;color:#f8fbff}.lv-card .small,.lv-card .label,.lv-hero p{color:#8ea3bf}
.lv-side{border-radius:20px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.72);padding:18px;display:grid;gap:12px;align-content:center}.lv-side strong{display:block;color:#f8fbff;font-size:18px}
.lv-metrics{display:grid;gap:14px;grid-template-columns:repeat(4,minmax(0,1fr))}.lv-metric{border-radius:18px;border:1px solid rgba(148,163,184,.14);background:rgba(15,23,42,.68);padding:18px}.lv-metric span{display:block;color:#8ea3bf;font-size:12px;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}.lv-metric strong{display:block;color:#f8fbff;font-size:24px;line-height:1.15;font-weight:800}
.lv-head{display:flex;justify-content:space-between;gap:14px;align-items:end;margin-bottom:16px}.lv-card h3{margin:0;color:#f8fbff}
.lv-card .input,.lv-card textarea,.lv-card select{background:rgba(15,23,42,.86);color:#f8fbff;border:1px solid rgba(148,163,184,.18)}.lv-card .input::placeholder,.lv-card textarea::placeholder{color:#6f86a6}
.lv-card .btn.btn-outline,.lv-card .btn.btn-ghost{border-color:rgba(148,163,184,.2);color:#d8e4f5;background:rgba(15,23,42,.55)}
.lv-table{overflow-x:auto;border-radius:18px;border:1px solid rgba(148,163,184,.12)}.lv-card .table{margin:0;width:100%;color:#e5eefb;background:transparent}.lv-card .table th{background:rgba(15,23,42,.96);color:#93a8c6;text-transform:uppercase;letter-spacing:.08em;font-size:11px;border-bottom:1px solid rgba(148,163,184,.16)}.lv-card .table td,.lv-card .table th{border-color:rgba(148,163,184,.1);padding:14px 16px;vertical-align:middle}.lv-card .table tbody tr:nth-child(odd){background:rgba(15,23,42,.48)}
.lv-grid2{display:grid;gap:14px;grid-template-columns:repeat(2,minmax(0,1fr))}.lv-box{padding:14px 16px;border-radius:16px;border:1px solid rgba(148,163,184,.14);background:rgba(15,23,42,.62)}.lv-box strong{display:block;color:#f8fbff;margin-bottom:6px}.lv-box span{color:#d8e4f5}.lv-detail{background:rgba(15,23,42,.62);border:1px solid rgba(148,163,184,.14);border-radius:14px;padding:10px 12px;color:#d8e4f5;font-size:12px;line-height:1.7}
@media (max-width:1080px){.lv-hero,.lv-metrics,.lv-grid2{grid-template-columns:1fr}}@media (max-width:760px){.lv-card{padding:18px;border-radius:18px}}
</style>

<div class="loan-shell">
  <section class="lv-hero">
    <div class="lv-card">
      <span class="lv-kicker">Loan Profile</span>
      <h2><?= htmlspecialchars($loan['reference_no']) ?></h2>
      <p style="margin:10px 0 0;line-height:1.7;">Customer: <strong style="color:#f8fbff;"><?= htmlspecialchars($loan['customer_name']) ?></strong> (<?= htmlspecialchars($loan['customer_no']) ?>) · <?= htmlspecialchars($loan['contact_no']) ?></p>
      <div style="margin-top:14px"><span class="badge <?= status_badge_class($loan['status']) ?>"><?= htmlspecialchars($loan['status']) ?></span></div>
    </div>
    <div class="lv-side">
      <div><div class="small">Submitted</div><strong><?= htmlspecialchars($loan['submitted_at']) ?></strong></div>
      <div><div class="small">Due Date</div><strong><?= $loan['due_date'] ? htmlspecialchars($loan['due_date']) : '—' ?></strong></div>
      <div><div class="small">Assigned Officer</div><strong><?= $loan['officer_name'] ? htmlspecialchars($loan['officer_name']) : '—' ?></strong></div>
    </div>
  </section>

  <?php if($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <section class="lv-metrics">
    <article class="lv-metric"><span>Principal</span><strong>₱<?= number_format($loan['principal_amount'], 2) ?></strong></article>
    <article class="lv-metric"><span>Payment Term</span><strong><?= htmlspecialchars($payment_term_label) ?></strong></article>
    <article class="lv-metric"><span>Interest Rate</span><strong><?= htmlspecialchars($interest_rate_label) ?></strong></article>
    <article class="lv-metric"><span>Total Payable</span><strong><?= $loan['total_payable']===null ? '—' : '₱' . number_format($loan['total_payable'], 2) ?></strong></article>
    <article class="lv-metric"><span>Remaining</span><strong><?= $loan['remaining_balance']===null ? '—' : '₱' . number_format($loan['remaining_balance'], 2) ?></strong></article>
    <article class="lv-metric"><span>Late Fee Est.</span><strong><?= $display_late_fee > 0 ? '₱' . number_format($display_late_fee, 2) : '—' ?></strong></article>
    <article class="lv-metric"><span>Customer No</span><strong><?= htmlspecialchars($loan['customer_no']) ?></strong></article>
    <article class="lv-metric"><span>Status</span><strong><?= htmlspecialchars($loan['status']) ?></strong></article>
  </section>

  <div class="lv-card">
    <div class="lv-head"><div><h3>Submitted Requirements</h3><div class="small" style="margin-top:8px;">Review borrower documents attached to this loan.</div></div></div>
    <div class="lv-table"><table class="table"><thead><tr><th>Requirement</th><th>Uploaded</th><th>Notes</th><th>File</th></tr></thead><tbody>
      <?php foreach($reqs as $r): ?><tr><td><?= htmlspecialchars($r['requirement_name']) ?></td><td><?= htmlspecialchars($r['uploaded_at']) ?></td><td><?= htmlspecialchars($r['notes'] ?? '') ?></td><td><a class="btn btn-primary" href="<?php echo APP_BASE; ?>/staff/download_requirement.php?id=<?= intval($r['requirement_id']) ?>">View/Download</a></td></tr><?php endforeach; ?>
      <?php if(empty($reqs)): ?><tr><td colspan="4" class="small">No requirements uploaded.</td></tr><?php endif; ?>
    </tbody></table></div>
  </div>

  <?php if (!empty($comaker_full_name) || !empty($comaker_reqs)): ?>
  <div class="lv-card">
    <div class="lv-head"><div><h3>Co-Maker Information</h3><div class="small" style="margin-top:8px;">Support profile and co-maker documents.</div></div></div>
    <?php if (!empty($comaker_full_name)): ?>
    <div class="lv-grid2" style="margin-bottom:16px">
      <div class="lv-box"><strong>Full Name</strong><span><?= htmlspecialchars($comaker_full_name) ?></span></div>
      <div class="lv-box"><strong>Valid ID Type</strong><span><?= htmlspecialchars($comaker_id_type) ?></span></div>
      <div class="lv-box"><strong>Contact Number</strong><span><?= htmlspecialchars($comaker_contact) ?></span></div>
      <div class="lv-box"><strong>Email Address</strong><span><?= htmlspecialchars($comaker_email) ?></span></div>
      <div class="lv-box" style="grid-column:1/-1"><strong>Address</strong><span><?= htmlspecialchars($comaker_address) ?></span></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($comaker_reqs)): ?>
    <div class="lv-table"><table class="table"><thead><tr><th>Document</th><th>Uploaded</th><th>File</th></tr></thead><tbody>
      <?php foreach($comaker_reqs as $r): ?><tr><td><?= htmlspecialchars($r['requirement_name']) ?></td><td><?= htmlspecialchars($r['uploaded_at']) ?></td><td><a class="btn btn-primary" href="<?php echo APP_BASE; ?>/staff/download_requirement.php?id=<?= intval($r['requirement_id']) ?>">View/Download</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="lv-card">
    <div class="lv-head">
      <div><h3>Payments</h3><div class="small" style="margin-top:8px;">Receipts, cashier activity, and payment method details.</div></div>
      <form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;justify-content:flex-end">
        <input type="hidden" name="id" value="<?= intval($id) ?>">
        <div><label class="label" style="margin-bottom:4px">Filter</label><select class="input" name="range" onchange="this.form.submit()"><option value="">All</option><option value="week" <?= ($payment_filter_range === 'week') ? 'selected' : '' ?>>Last Week</option><option value="month" <?= ($payment_filter_range === 'month') ? 'selected' : '' ?>>Last Month</option><option value="custom" <?= ($payment_filter_from && $payment_filter_to) ? 'selected' : '' ?>>Custom Range</option></select></div>
        <?php if ($payment_filter_range === 'custom' || ($payment_filter_from && $payment_filter_to)): ?>
          <div><input class="input" type="date" name="from" value="<?= htmlspecialchars($payment_filter_from) ?>" style="min-width:130px"></div>
          <div><input class="input" type="date" name="to" value="<?= htmlspecialchars($payment_filter_to) ?>" style="min-width:130px"></div>
          <button class="btn btn-primary" type="submit" style="padding:8px 12px">Filter</button>
          <a class="btn btn-primary" href="<?php echo APP_BASE; ?>/staff/loan_view.php?id=<?= intval($id) ?>" style="padding:8px 12px">Reset</a>
        <?php endif; ?>
      </form>
    </div>
    <div class="lv-table"><table class="table"><thead><tr><th>OR No</th><th>Date</th><th>Amount</th><th>Method</th><th>Details</th><th>Notes</th><th>Received By</th><th>Action</th></tr></thead><tbody>
      <?php foreach($payments as $p): ?><tr>
        <td><?= htmlspecialchars($p['or_no']) ?></td><td><?= htmlspecialchars($p['payment_date']) ?></td><td>₱<?= number_format($p['amount'], 2) ?></td><td><?= htmlspecialchars($p['method'] ?? '') ?></td>
        <td><?php if ($p['method'] === 'CHEQUE'): ?><div class="lv-detail"><strong>Cheque #:</strong> <?= htmlspecialchars($p['cheque_number'] ?? '—') ?><br><strong>Date:</strong> <?= htmlspecialchars($p['cheque_date'] ?? '—') ?><br><strong>Bank:</strong> <?= htmlspecialchars($p['bank_name'] ?? '—') ?><br><strong>Holder:</strong> <?= htmlspecialchars($p['account_holder'] ?? '—') ?></div><?php elseif ($p['method'] === 'BANK'): ?><div class="lv-detail"><strong>Reference #:</strong> <?= htmlspecialchars($p['bank_reference_no'] ?? '—') ?></div><?php elseif ($p['method'] === 'GCASH'): ?><div class="lv-detail"><strong>Reference #:</strong> <?= htmlspecialchars($p['gcash_reference_no'] ?? '—') ?></div><?php else: ?>—<?php endif; ?></td>
        <td><?= htmlspecialchars($p['notes'] ?? '') ?></td><td><?= htmlspecialchars($p['cashier_name'] ?? '') ?></td>
        <td style="display:flex;gap:6px;flex-wrap:wrap"><?php if (can_access('print_receipts')): ?><a class="btn btn-primary" href="<?php echo APP_BASE; ?>/staff/payment_receipt.php?id=<?= intval($p['payment_id']) ?>" style="font-size:12px">Receipt</a><?php endif; ?><?php if (can_access('edit_payments')): ?><a class="btn btn-primary" href="<?php echo APP_BASE; ?>/staff/payment_edit.php?id=<?= intval($p['payment_id']) ?>" style="font-size:12px">Edit</a><?php endif; ?><?php if (!can_access('print_receipts') && !can_access('edit_payments')): ?>—<?php endif; ?></td>
      </tr><?php endforeach; ?>
      <?php if(empty($payments)): ?><tr><td colspan="8" class="small">No payments yet.</td></tr><?php endif; ?>
    </tbody></table></div>
    <div style="margin-top:12px;display:flex;justify-content:flex-end"><?php if (can_access('record_payments') && in_array($loan['status'], ['ACTIVE','OVERDUE'], true)): ?><a class="btn btn-primary" href="<?php echo APP_BASE; ?>/staff/payment_add.php?loan_id=<?= intval($loan['loan_id']) ?>">Record Payment</a><?php endif; ?></div>
  </div>

  <?php if (in_array($loan['status'], ['PENDING'], true) && can_access('review_applications')): ?>
  <div class="lv-card"><h3>CI Review</h3><form method="post"><?= csrf_field() ?><label class="label">Remarks</label><input class="input" name="ci_notes" placeholder="Verification notes (optional)"><div style="margin-top:10px"><button class="btn btn-primary" name="ci_review" value="1">Mark as CI Reviewed</button></div></form></div>
  <?php endif; ?>

  <?php if (in_array($loan['status'], ['PENDING','CI_REVIEWED'], true) && can_access('approve_applications')): ?>
  <div class="lv-card"><h3>Manager Decision</h3><form method="post"><?= csrf_field() ?><label class="label">Remarks</label><input class="input" name="manager_notes" placeholder="Approval/denial notes (optional)"><div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap"><button class="btn btn-primary" name="manager_decision" value="1" type="submit" onclick="document.getElementById('decision').value='APPROVE'">Approve</button><button class="btn btn-primary" name="manager_decision" value="1" type="submit" onclick="document.getElementById('decision').value='DENY'">Deny</button></div><input type="hidden" id="decision" name="decision" value="APPROVE"></form></div>
  <?php endif; ?>

  <?php if (in_array($loan['status'], ['ACTIVE','OVERDUE','APPROVED'], true) && $can_manage_custom_loan_config): ?>
  <div class="lv-card"><h3>Update Loan Terms</h3><form method="post"><?= csrf_field() ?><div class="lv-grid2"><div><label class="label">Interest Rate (%)</label><input class="input" type="number" step="0.01" name="interest_rate_update" value="<?= htmlspecialchars($loan['interest_rate']) ?>"><button class="btn btn-primary" name="update_terms" value="1" type="submit" onclick="document.getElementById('update_type').value='interest_only'" style="margin-top:8px">Update Interest Rate Only</button></div><div><label class="label">Payment Term</label><select class="input" name="payment_term_update"><option value="">-- Select Payment Term --</option><option value="daily" <?= ($loan['payment_term'] === 'daily') ? 'selected' : '' ?>>Daily</option><option value="weekly" <?= ($loan['payment_term'] === 'weekly') ? 'selected' : '' ?>>Weekly</option><option value="semi_monthly" <?= ($loan['payment_term'] === 'semi_monthly') ? 'selected' : '' ?>>Semi-Monthly</option><option value="monthly" <?= ($loan['payment_term'] === 'monthly') ? 'selected' : '' ?>>Monthly</option></select><button class="btn btn-primary" name="update_terms" value="1" type="submit" onclick="document.getElementById('update_type').value='term_only'" style="margin-top:8px">Update Payment Term Only</button></div></div><input type="hidden" id="update_type" name="update_type" value=""></form></div>
  <?php elseif (in_array($loan['status'], ['ACTIVE','OVERDUE','APPROVED'], true) && can_access('update_loan_terms')): ?>
  <div class="lv-card"><h3>Update Loan Terms</h3><div class="alert red" style="margin-top:14px"><?= htmlspecialchars($custom_loan_config_feature_access['message']) ?></div></div>
  <?php endif; ?>

  <?php if (can_access('assign_loan_officer')): ?>
  <div class="lv-card"><h3>Loan Officer Assignment</h3><form method="post"><?= csrf_field() ?><label class="label">Loan Officer</label><select class="input" name="loan_officer_id"><option value="">-- Select Officer --</option><?php foreach ($loan_officers as $officer): ?><option value="<?= intval($officer['user_id']) ?>" <?= ($loan['loan_officer_id'] == $officer['user_id']) ? 'selected' : '' ?>><?= htmlspecialchars($officer['full_name']) ?></option><?php endforeach; ?></select><div style="margin-top:10px"><button class="btn btn-primary" name="assign_officer" value="1">Assign Officer</button></div></form></div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/_layout_bottom.php'; ?>
