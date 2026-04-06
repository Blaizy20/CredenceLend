<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_login();
require_permission('edit_payments');

$id = intval($_GET['id'] ?? 0);
error_log("DEBUG: payment_edit.php accessed for payment_id=$id by user=" . ($_SESSION['user_id'] ?? 'unknown'));
enforce_tenant_resource_access('payments', 'payment_id', $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

$p = fetch_one(q(
  "SELECT p.*, l.reference_no
   FROM payments p
   JOIN loans l ON l.loan_id=p.loan_id AND l.tenant_id=p.tenant_id
   WHERE " . tenant_condition('p.tenant_id') . " AND p.payment_id=?",
  tenant_types("i"),
  tenant_params([$id])
));
error_log("DEBUG: Payment fetch result: " . ($p ? "Found" : "Not found"));
if (!$p) { http_response_code(404); echo "Payment not found"; exit; }

$current_user = current_user();
error_log("DEBUG: current_user={$current_user['full_name']} (role={$current_user['role']})");
$is_cashier = $current_user['role'] === 'CASHIER';
$otp_required = $is_cashier;

error_log("DEBUG: is_cashier=$is_cashier, otp_required=$otp_required");

ensure_payment_otp_table();
error_log("DEBUG: OTP table ensured");

$otp_verified = false;
if (!$is_cashier) {
  $otp_verified = true;
  error_log("DEBUG: Not a cashier - OTP not required");
} else {
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['verified_payment_edits'][$id])) {
      unset($_SESSION['verified_payment_edits'][$id]);
      error_log("DEBUG: Cleared previous OTP verification for payment $id - requiring fresh OTP");
    }
    $otp_verified = false;
  } else {
    $otp_verified = is_payment_edit_verified($id);
  }
  error_log("DEBUG: Cashier check - otp_verified=$otp_verified, method={$_SERVER['REQUEST_METHOD']}");
}

$err = '';
$ok = '';

if ($otp_required && !$otp_verified && $_SERVER['REQUEST_METHOD'] !== 'POST') {
  error_log("DEBUG: OTP generation condition met - generating OTP");
  try {
    $otp_code = generate_otp_for_payment_edit($id, $current_user['user_id']);
    error_log("DEBUG: OTP generated: $otp_code");

    $sent = send_otp_notification($id, $otp_code, $p['or_no'], $current_user['full_name']);
    error_log("DEBUG: OTP notification sent result: $sent");

    if ($sent) {
      error_log("OTP sent to managers/admins for payment edit: Payment ID=$id, Cashier={$current_user['full_name']}, OR={$p['or_no']}, OTP=$otp_code");
      $ok = 'OTP has been generated and sent to all managers and admins. Please wait for their verification.';
    } else {
      error_log("Failed to send OTP notification for payment edit: Payment ID=$id");
      $ok = 'OTP generated: ' . $otp_code . ' - Note: Failed to send email notification to managers. Please contact your manager directly.';
    }
  } catch (Exception $e) {
    error_log("OTP Generation Error: " . $e->getMessage());
    $err = "OTP Generation Error: " . $e->getMessage();
  }
} else {
  error_log("DEBUG: OTP generation condition NOT met - otp_required=$otp_required, otp_verified=$otp_verified, method={$_SERVER['REQUEST_METHOD']}");
}

if ($otp_required && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
  $otp_code = trim($_POST['otp_code'] ?? '');
  if (empty($otp_code)) {
    $err = 'Please enter the OTP code.';
  } else {
    $result = verify_otp_for_payment($id, $current_user['user_id'], $otp_code);
    if (!$result['success']) {
      $err = $result['message'];
    } else {
      $otp_verified = true;
      $_SESSION['verified_payment_edits'][$id] = true;
      $ok = 'OTP verified! You can now edit the payment.';
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment']) && $otp_verified) {
  $amount = floatval($_POST['amount'] ?? 0);
  $date = $_POST['payment_date'] ?? $p['payment_date'];
  $method = trim($_POST['method'] ?? $p['method']);
  $notes = trim($_POST['notes'] ?? '');
  $cheque_number = trim($_POST['cheque_number'] ?? '');
  $cheque_date = $_POST['cheque_date'] ?? null;
  $bank_name = trim($_POST['bank_name'] ?? '');
  $account_holder = trim($_POST['account_holder'] ?? '');
  $bank_reference_no = trim($_POST['bank_reference_no'] ?? '');
  $gcash_reference_no = trim($_POST['gcash_reference_no'] ?? '');

  if ($amount <= 0) $err = 'Invalid amount.';
  else if ($method === 'CHEQUE' && $cheque_number === '') $err = 'Cheque number is required.';
  else if ($method === 'CHEQUE' && $cheque_date === '') $err = 'Cheque date is required.';
  else if ($method === 'CHEQUE' && $bank_name === '') $err = 'Bank name is required.';
  else if ($method === 'CHEQUE' && $account_holder === '') $err = 'Account holder name is required.';
  else if ($method === 'BANK' && $bank_reference_no === '') $err = 'Bank reference number is required.';
  else if ($method === 'GCASH' && $gcash_reference_no === '') $err = 'GCash reference number is required.';
  else {
    if (is_system_admin()) {
      q(
        "UPDATE payments
         SET amount=?, payment_date=?, method=?, cheque_number=?, cheque_date=?, bank_name=?, account_holder=?, bank_reference_no=?, gcash_reference_no=?, notes=?
         WHERE payment_id=?",
        "dsssssssssi",
        [$amount, $date, $method, $cheque_number ?: null, $cheque_date ?: null, $bank_name ?: null, $account_holder ?: null, $bank_reference_no ?: null, $gcash_reference_no ?: null, $notes, $id]
      );
    } else {
      q(
        "UPDATE payments
         SET amount=?, payment_date=?, method=?, cheque_number=?, cheque_date=?, bank_name=?, account_holder=?, bank_reference_no=?, gcash_reference_no=?, notes=?
         WHERE tenant_id=? AND payment_id=?",
        "dsssssssssii",
        [$amount, $date, $method, $cheque_number ?: null, $cheque_date ?: null, $bank_name ?: null, $account_holder ?: null, $bank_reference_no ?: null, $gcash_reference_no ?: null, $notes, require_current_tenant_id(), $id]
      );
    }
    $loan = fetch_one(q(
      "SELECT customer_id, reference_no
       FROM loans
       WHERE " . tenant_condition('tenant_id') . " AND loan_id=?",
      tenant_types("i"),
      tenant_params([$p['loan_id']])
    ));
    if ($loan) {
      log_activity('Payment Updated', 'Payment of PHP ' . number_format($amount, 2) . ' updated via ' . $method . ' - OR#' . $p['or_no'] . ($is_cashier ? ' (OTP Verified)' : ''), $p['loan_id'], $loan['customer_id'], $loan['reference_no']);
    }
    recalc_loan($p['loan_id']);
    header("Location: " . APP_BASE . "/staff/loan_view.php?id=" . intval($p['loan_id']));
    exit;
  }
}

$title = "Edit Payment";
$active = "pay";
include __DIR__ . '/_layout_top.php';
?>
<style>
body{background:radial-gradient(circle at top,rgba(14,165,233,.12),transparent 30%),linear-gradient(180deg,#020617 0%,#081121 42%,#0f172a 100%);color:#e5eefb}
.topbar{background:linear-gradient(135deg,#081121,#0f1b35)!important;border-bottom:1px solid rgba(148,163,184,.14);box-shadow:0 18px 40px rgba(2,6,23,.35)}
.topbar .small,.topbar a.btn.btn-outline{color:#d8e4f5!important;border-color:rgba(148,163,184,.24)!important}.layout,.main{background:transparent}
.sidebar{background:rgba(4,10,24,.84);border-right:1px solid rgba(148,163,184,.12);backdrop-filter:blur(16px)}.sidebar h3{color:#7f93b0}.sidebar a{color:#d7e3f4}.sidebar a.active,.sidebar a:hover{background:linear-gradient(135deg,rgba(14,165,233,.18),rgba(59,130,246,.2));color:#f8fbff}
.payment-shell{display:grid;gap:20px}
.payment-card{border-radius:24px;border:1px solid rgba(148,163,184,.16);background:radial-gradient(circle at top left,rgba(56,189,248,.12),transparent 26%),linear-gradient(180deg,rgba(15,23,42,.96),rgba(8,15,30,.97));box-shadow:0 24px 60px rgba(2,6,23,.34);padding:24px}
.payment-hero{display:grid;gap:18px;grid-template-columns:minmax(0,1.6fr) minmax(220px,.8fr)}
.payment-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid rgba(125,211,252,.24);background:rgba(14,165,233,.12);color:#d8f4ff;font-size:12px;letter-spacing:.08em;text-transform:uppercase}
.payment-card h2,.payment-card h3,.payment-card h4{margin:0;color:#f8fbff}
.payment-card h2{margin-top:10px;font-size:clamp(30px,4vw,42px);line-height:1;letter-spacing:-.04em}
.payment-card .small,.payment-card .label,.payment-card p{color:#8ea3bf}
.payment-side{border-radius:20px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.72);padding:18px;display:grid;gap:12px;align-content:center}
.payment-side strong{display:block;color:#f8fbff;font-size:18px}
.payment-grid{display:grid;gap:14px;grid-template-columns:repeat(2,minmax(0,1fr))}
.payment-card .input,.payment-card select{background:rgba(15,23,42,.86);color:#f8fbff;border:1px solid rgba(148,163,184,.18)}
.payment-card .input::placeholder{color:#6f86a6}
.payment-card .btn.btn-outline{border-color:rgba(148,163,184,.2);color:#d8e4f5;background:rgba(15,23,42,.55)}
.payment-panel{margin-top:16px;padding:18px;border-radius:18px;border:1px solid rgba(148,163,184,.14);background:rgba(15,23,42,.62)}
.payment-panel h4{margin-bottom:12px}
.payment-otp{border-color:rgba(250,204,21,.24);background:linear-gradient(180deg,rgba(250,204,21,.12),rgba(15,23,42,.82));box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}
.payment-otp h3{color:#fef3c7}
.payment-otp strong,.payment-hero strong{color:#f8fbff}
.payment-actions{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}
@media (max-width:980px){.payment-hero,.payment-grid{grid-template-columns:1fr}}
@media (max-width:760px){.payment-card{padding:18px;border-radius:18px}}
</style>

<div class="payment-shell">
  <section class="payment-hero">
    <div class="payment-card">
      <span class="payment-kicker">Payments</span>
      <h2>Edit Payment</h2>
      <p style="margin:10px 0 0;line-height:1.7;">Update the recorded payment details for loan <strong><?= htmlspecialchars($p['reference_no']) ?></strong>.</p>
    </div>
    <div class="payment-side">
      <div><div class="small">Loan Reference</div><strong><?= htmlspecialchars($p['reference_no']) ?></strong></div>
      <div><div class="small">Official Receipt</div><strong><?= htmlspecialchars($p['or_no']) ?></strong></div>
      <div><div class="small">Current Method</div><strong><?= htmlspecialchars($p['method']) ?></strong></div>
    </div>
  </section>

  <?php if ($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <?php if ($otp_required && !$otp_verified): ?>
    <section class="payment-card payment-otp">
      <h3>OTP Verification Required</h3>
      <p style="margin:10px 0 0;line-height:1.7;"><strong>Cashier edits require manager or admin approval before changes can be saved.</strong></p>
      <p style="margin:10px 0 0;line-height:1.7;">An OTP has been generated and sent to managers and admins. Enter the 6-digit code they provide to continue.</p>

      <form method="post" style="margin-top:16px">
        <?= csrf_field() ?>
        <div class="payment-grid">
          <div>
            <label class="label">Enter OTP Code</label>
            <input class="input" type="text" name="otp_code" placeholder="Enter 6-digit code" maxlength="6" inputmode="numeric" required style="font-size:18px;letter-spacing:3px;text-align:center;">
          </div>
          <div style="display:flex;align-items:flex-end;">
            <button class="btn btn-primary" name="verify_otp" style="width:100%;">Verify OTP</button>
          </div>
        </div>
      </form>

      <p class="small" style="margin:14px 0 0;line-height:1.7;">OTP is valid for 15 minutes. Reload the page to generate a new code if it expires.</p>
    </section>
  <?php endif; ?>

  <?php if (!$otp_required || $otp_verified): ?>
  <section class="payment-card">
    <div style="margin-bottom:16px;">
      <h3>Edit Details</h3>
      <div class="small" style="margin-top:8px;">Adjust the amount, date, payment method, and reference information.</div>
    </div>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="save_payment" value="1">

      <div class="payment-grid">
        <div>
          <label class="label">Amount</label>
          <input class="input" type="number" step="0.01" name="amount" value="<?= htmlspecialchars($p['amount']) ?>" required>
        </div>
        <div>
          <label class="label">Payment Date</label>
          <input class="input" type="date" name="payment_date" value="<?= htmlspecialchars($p['payment_date']) ?>" required>
        </div>
      </div>

      <div class="payment-grid" style="margin-top:14px;">
        <div>
          <label class="label">Method</label>
          <select class="input" name="method" id="method" onchange="toggleChequeFields()">
            <?php foreach (['CASH' => 'Cash', 'GCASH' => 'GCash', 'BANK' => 'Bank Transfer', 'CHEQUE' => 'Cheque'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= $p['method'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="label">Notes</label>
          <input class="input" name="notes" value="<?= htmlspecialchars($p['notes'] ?? '') ?>">
        </div>
      </div>

      <div id="cheque-fields" class="payment-panel" style="<?= $p['method'] === 'CHEQUE' ? 'display:block' : 'display:none' ?>">
        <h4>Cheque Details</h4>
        <div class="payment-grid">
          <div>
            <label class="label">Cheque Number</label>
            <input class="input" type="text" name="cheque_number" id="cheque_number" value="<?= htmlspecialchars($p['cheque_number'] ?? '') ?>" placeholder="e.g., ABC123456">
          </div>
          <div>
            <label class="label">Cheque Date</label>
            <input class="input" type="date" name="cheque_date" id="cheque_date" value="<?= htmlspecialchars($p['cheque_date'] ?? '') ?>">
          </div>
        </div>
        <div class="payment-grid" style="margin-top:14px;">
          <div>
            <label class="label">Bank Name</label>
            <input class="input" type="text" name="bank_name" id="bank_name" value="<?= htmlspecialchars($p['bank_name'] ?? '') ?>" placeholder="e.g., BDO, BPI, Metrobank">
          </div>
          <div>
            <label class="label">Account Holder Name</label>
            <input class="input" type="text" name="account_holder" id="account_holder" value="<?= htmlspecialchars($p['account_holder'] ?? '') ?>" placeholder="Name on the cheque">
          </div>
        </div>
      </div>

      <div id="bank-fields" class="payment-panel" style="<?= $p['method'] === 'BANK' ? 'display:block' : 'display:none' ?>">
        <h4>Bank Transfer Details</h4>
        <div>
          <label class="label">Bank Reference Number</label>
          <input class="input" type="text" name="bank_reference_no" id="bank_reference_no" value="<?= htmlspecialchars($p['bank_reference_no'] ?? '') ?>" placeholder="Transaction/Reference number">
        </div>
      </div>

      <div id="gcash-fields" class="payment-panel" style="<?= $p['method'] === 'GCASH' ? 'display:block' : 'display:none' ?>">
        <h4>GCash Details</h4>
        <div>
          <label class="label">GCash Reference Number</label>
          <input class="input" type="text" name="gcash_reference_no" id="gcash_reference_no" value="<?= htmlspecialchars($p['gcash_reference_no'] ?? '') ?>" placeholder="Transaction/Reference number">
        </div>
      </div>

      <div class="payment-actions">
        <button class="btn btn-primary" name="save_payment">Save Changes</button>
        <a class="btn btn-outline" href="<?php echo APP_BASE; ?>/staff/loan_view.php?id=<?= intval($p['loan_id']) ?>">Cancel</a>
      </div>
    </form>
  </section>
  <?php endif; ?>
</div>

<script>
function toggleChequeFields() {
  const method = document.getElementById('method').value;
  const chequeFields = document.getElementById('cheque-fields');
  const bankFields = document.getElementById('bank-fields');
  const gcashFields = document.getElementById('gcash-fields');
  const chequeInputs = ['cheque_number', 'cheque_date', 'bank_name', 'account_holder'];

  if (method === 'CHEQUE') {
    chequeFields.style.display = 'block';
    bankFields.style.display = 'none';
    gcashFields.style.display = 'none';
    chequeInputs.forEach(id => document.getElementById(id).required = true);
    document.getElementById('bank_reference_no').required = false;
    document.getElementById('gcash_reference_no').required = false;
  } else if (method === 'BANK') {
    chequeFields.style.display = 'none';
    bankFields.style.display = 'block';
    gcashFields.style.display = 'none';
    chequeInputs.forEach(id => document.getElementById(id).required = false);
    document.getElementById('bank_reference_no').required = true;
    document.getElementById('gcash_reference_no').required = false;
  } else if (method === 'GCASH') {
    chequeFields.style.display = 'none';
    bankFields.style.display = 'none';
    gcashFields.style.display = 'block';
    chequeInputs.forEach(id => document.getElementById(id).required = false);
    document.getElementById('bank_reference_no').required = false;
    document.getElementById('gcash_reference_no').required = true;
  } else {
    chequeFields.style.display = 'none';
    bankFields.style.display = 'none';
    gcashFields.style.display = 'none';
    chequeInputs.forEach(id => document.getElementById(id).required = false);
    document.getElementById('bank_reference_no').required = false;
    document.getElementById('gcash_reference_no').required = false;
  }
}
</script>
<?php include __DIR__ . '/_layout_bottom.php'; ?>
