<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_login();
require_permission('record_payments');

$loan_id = intval($_GET['loan_id'] ?? 0);
enforce_tenant_resource_access('loans', 'loan_id', $loan_id);
$loan = fetch_one(q(
  "SELECT l.*, c.customer_no, CONCAT(c.first_name,' ',c.last_name) AS customer_name, u.full_name AS officer_name
   FROM loans l
   JOIN customers c ON c.customer_id=l.customer_id AND c.tenant_id=l.tenant_id
   LEFT JOIN users u ON u.user_id=l.loan_officer_id AND u.tenant_id=l.tenant_id
   WHERE " . tenant_condition('l.tenant_id') . " AND l.loan_id=?",
  tenant_types("i"),
  tenant_params([$loan_id])
));
if (!$loan) { http_response_code(404); echo "Loan not found"; exit; }
$can_manage_officer = can_access('assign_loan_officer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_post_csrf();
}

recalc_loan($loan_id);
$loan = fetch_one(q(
  "SELECT l.*, c.customer_no, CONCAT(c.first_name,' ',c.last_name) AS customer_name, u.full_name AS officer_name
   FROM loans l
   JOIN customers c ON c.customer_id=l.customer_id AND c.tenant_id=l.tenant_id
   LEFT JOIN users u ON u.user_id=l.loan_officer_id AND u.tenant_id=l.tenant_id
   WHERE " . tenant_condition('l.tenant_id') . " AND l.loan_id=?",
  tenant_types("i"),
  tenant_params([$loan_id])
));

$err = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = floatval($_POST['amount'] ?? 0);
  $date = $_POST['payment_date'] ?? date('Y-m-d');
  $method = trim($_POST['method'] ?? 'CASH');
  $notes = trim($_POST['notes'] ?? '');
  $officer_id = intval($_POST['loan_officer_id'] ?? 0);
  $cheque_number = trim($_POST['cheque_number'] ?? '');
  $cheque_date = $_POST['cheque_date'] ?? null;
  $bank_name = trim($_POST['bank_name'] ?? '');
  $account_holder = trim($_POST['account_holder'] ?? '');
  $bank_reference_no = trim($_POST['bank_reference_no'] ?? '');
  $gcash_reference_no = trim($_POST['gcash_reference_no'] ?? '');

  if ($amount <= 0) $err = 'Invalid amount.';
  else if ($loan['remaining_balance'] !== null && $amount > floatval($loan['remaining_balance'])) $err = 'Amount exceeds remaining balance.';
  else if ($method === 'CHEQUE' && $cheque_number === '') $err = 'Cheque number is required.';
  else if ($method === 'CHEQUE' && $cheque_date === '') $err = 'Cheque date is required.';
  else if ($method === 'CHEQUE' && $bank_name === '') $err = 'Bank name is required.';
  else if ($method === 'CHEQUE' && $account_holder === '') $err = 'Account holder name is required.';
  else if ($method === 'BANK' && $bank_reference_no === '') $err = 'Bank reference number is required.';
  else if ($method === 'GCASH' && $gcash_reference_no === '') $err = 'GCash reference number is required.';
  else if (!$can_manage_officer && empty($loan['loan_officer_id'])) $err = 'A manager or admin must assign a loan officer before this payment can be recorded.';
  else {
    if ($officer_id > 0 && $can_manage_officer) {
      $officer = fetch_one(q(
        "SELECT user_id FROM users WHERE tenant_id=? AND role='LOAN_OFFICER' AND is_active=1 AND user_id=?",
        "ii",
        [$loan['tenant_id'], $officer_id]
      ));
      if (!$officer) {
        $err = 'Loan officer not found for this tenant.';
      } else {
        q("UPDATE loans SET loan_officer_id=? WHERE tenant_id=? AND loan_id=?", "iii", [$officer_id, $loan['tenant_id'], $loan_id]);
      }
    }

    if (!$err) {
      $or = generate_or_no();

      try {
        q(
          "INSERT INTO payments (tenant_id, loan_id, amount, payment_date, method, cheque_number, cheque_date, bank_name, account_holder, bank_reference_no, gcash_reference_no, or_no, loan_officer_id, received_by, notes)
           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
          "iidssssssssssis",
          [$loan['tenant_id'], $loan_id, $amount, $date, $method, $cheque_number ?: null, $cheque_date ?: null, $bank_name ?: null, $account_holder ?: null, $bank_reference_no ?: null, $gcash_reference_no ?: null, $or, $officer_id ?: null, $_SESSION['user_id'], $notes]
        );
        log_activity('Payment Recorded', 'Payment of PHP ' . number_format($amount, 2) . ' recorded via ' . $method . ' - OR#' . $or, $loan_id, $loan['customer_id'], $loan['reference_no']);

        recalc_loan($loan_id);

        header("Location: " . APP_BASE . "/staff/loan_view.php?id=" . intval($loan_id));
        exit;
      } catch (Exception $e) {
        $err = 'Error saving payment: ' . $e->getMessage();
      }
    }
  }
}

$loan_officers = fetch_all(q("SELECT user_id, full_name FROM users WHERE tenant_id=? AND role='LOAN_OFFICER' AND is_active=1 ORDER BY full_name", "i", [$loan['tenant_id']]));

$terms = ['daily' => 'Daily', 'weekly' => 'Weekly', 'semi_monthly' => 'Semi Monthly', 'monthly' => 'Monthly'];
$payment_term_label = isset($terms[$loan['payment_term']]) ? $terms[$loan['payment_term']] : (trim((string) $loan['payment_term']) !== '' ? $loan['payment_term'] : 'Not Set');
$rate = null;
if ($loan['payment_term']) {
  $term_rates = ['daily' => 2.75, 'weekly' => 3.0, 'semi_monthly' => 3.50, 'monthly' => 4.0];
  if (isset($term_rates[$loan['payment_term']])) {
    $rate = $term_rates[$loan['payment_term']];
  }
}
if (!$rate && $loan['interest_rate']) $rate = $loan['interest_rate'];
if (!$rate && $loan['status'] == 'PENDING') $rate = 5.0;

$title = "Record Payment";
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
.payment-metrics{display:grid;gap:14px;grid-template-columns:repeat(5,minmax(0,1fr))}
.payment-metric{border-radius:18px;border:1px solid rgba(148,163,184,.14);background:rgba(15,23,42,.68);padding:18px}
.payment-metric span{display:block;color:#8ea3bf;font-size:12px;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}
.payment-metric strong{display:block;color:#f8fbff;font-size:24px;line-height:1.15;font-weight:800}
.payment-grid{display:grid;gap:14px;grid-template-columns:repeat(2,minmax(0,1fr))}
.payment-card .input,.payment-card select{background:rgba(15,23,42,.86);color:#f8fbff;border:1px solid rgba(148,163,184,.18)}
.payment-card .input::placeholder{color:#6f86a6}
.payment-card .btn.btn-outline{border-color:rgba(148,163,184,.2);color:#d8e4f5;background:rgba(15,23,42,.55)}
.payment-panel{margin-top:16px;padding:18px;border-radius:18px;border:1px solid rgba(148,163,184,.14);background:rgba(15,23,42,.62)}
.payment-panel h4{margin-bottom:12px}
.payment-actions{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}
.payment-hint{margin-top:6px;color:#d8e4f5;font-weight:500}
@media (max-width:1080px){.payment-hero,.payment-metrics,.payment-grid{grid-template-columns:1fr}}
@media (max-width:760px){.payment-card{padding:18px;border-radius:18px}}
</style>

<div class="payment-shell">
  <section class="payment-hero">
    <div class="payment-card">
      <span class="payment-kicker">Payments</span>
      <h2>Record Payment</h2>
      <p style="margin:10px 0 0;line-height:1.7;">Capture a new payment for <strong><?= htmlspecialchars($loan['customer_name']) ?></strong> on loan <strong><?= htmlspecialchars($loan['reference_no']) ?></strong>.</p>
    </div>
    <div class="payment-side">
      <div><div class="small">Customer No</div><strong><?= htmlspecialchars($loan['customer_no']) ?></strong></div>
      <div><div class="small">Loan Reference</div><strong><?= htmlspecialchars($loan['reference_no']) ?></strong></div>
      <div><div class="small">Assigned Officer</div><strong><?= htmlspecialchars($loan['officer_name'] ?: 'Not assigned') ?></strong></div>
    </div>
  </section>

  <section class="payment-metrics">
    <article class="payment-metric"><span>Principal</span><strong><?= $loan['principal_amount'] === null ? 'N/A' : 'PHP ' . number_format($loan['principal_amount'], 2) ?></strong></article>
    <article class="payment-metric"><span>Total Payable</span><strong><?= $loan['total_payable'] === null ? 'N/A' : 'PHP ' . number_format($loan['total_payable'], 2) ?></strong></article>
    <article class="payment-metric"><span>Remaining</span><strong id="remaining"><?= $loan['remaining_balance'] === null ? 'N/A' : 'PHP ' . number_format($loan['remaining_balance'], 2) ?></strong></article>
    <article class="payment-metric"><span>Payment Term</span><strong><?= htmlspecialchars($payment_term_label) ?></strong></article>
    <article class="payment-metric"><span>Interest Rate</span><strong><?= $rate ? htmlspecialchars($rate) . '%' : 'N/A' ?></strong></article>
  </section>

  <?php if ($err): ?><div class="alert err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <section class="payment-card">
    <div style="margin-bottom:16px;">
      <h3>Payment Details</h3>
      <div class="small" style="margin-top:8px;">Enter the amount, payment method, and any supporting reference details.</div>
    </div>

    <form method="post">
      <?= csrf_field() ?>
      <div class="payment-grid">
        <div>
          <label class="label">Payment Amount</label>
          <input class="input" type="number" step="0.01" name="amount" id="amount" required>
          <div class="small payment-hint" id="after"></div>
        </div>
        <div>
          <label class="label">Payment Date</label>
          <input class="input" type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="payment-grid" style="margin-top:14px;">
        <div>
          <label class="label">Payment Method</label>
          <select class="input" name="method" id="method" onchange="toggleChequeFields()">
            <option value="CASH">Cash</option>
            <option value="GCASH">GCash</option>
            <option value="BANK">Bank Transfer</option>
            <option value="CHEQUE">Cheque</option>
          </select>
        </div>
        <div>
          <label class="label">Notes</label>
          <input class="input" name="notes" placeholder="e.g., partial payment">
        </div>
      </div>

      <div id="cheque-fields" class="payment-panel" style="display:none">
        <h4>Cheque Details</h4>
        <div class="payment-grid">
          <div>
            <label class="label">Cheque Number</label>
            <input class="input" type="text" name="cheque_number" id="cheque_number" placeholder="e.g., ABC123456">
          </div>
          <div>
            <label class="label">Cheque Date</label>
            <input class="input" type="date" name="cheque_date" id="cheque_date">
          </div>
        </div>
        <div class="payment-grid" style="margin-top:14px;">
          <div>
            <label class="label">Bank Name</label>
            <input class="input" type="text" name="bank_name" id="bank_name" placeholder="e.g., BDO, BPI, Metrobank">
          </div>
          <div>
            <label class="label">Account Holder Name</label>
            <input class="input" type="text" name="account_holder" id="account_holder" placeholder="Name on the cheque">
          </div>
        </div>
      </div>

      <div id="bank-fields" class="payment-panel" style="display:none">
        <h4>Bank Transfer Details</h4>
        <div>
          <label class="label">Bank Reference Number / Transaction ID</label>
          <input class="input" type="text" name="bank_reference_no" id="bank_reference_no" placeholder="e.g., REF123456789">
        </div>
      </div>

      <div id="gcash-fields" class="payment-panel" style="display:none">
        <h4>GCash Details</h4>
        <div>
          <label class="label">GCash Reference Number</label>
          <input class="input" type="text" name="gcash_reference_no" id="gcash_reference_no" placeholder="e.g., REF123456789">
        </div>
      </div>

      <div class="payment-panel">
        <h4>Loan Officer</h4>
        <?php if ($can_manage_officer): ?>
          <label class="label">Assign Loan Officer</label>
          <select class="input" name="loan_officer_id" required>
            <option value="">Select Loan Officer</option>
            <?php foreach ($loan_officers as $officer): ?>
              <option value="<?= $officer['user_id'] ?>" <?= $loan['loan_officer_id'] == $officer['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($officer['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <label class="label">Assigned Loan Officer</label>
          <input class="input" value="<?= htmlspecialchars($loan['officer_name'] ?: 'Not assigned') ?>" readonly>
          <input type="hidden" name="loan_officer_id" value="<?= intval($loan['loan_officer_id'] ?? 0) ?>">
          <div class="small" style="margin-top:8px;">Loan officer assignment can only be changed by Manager or Admin.</div>
        <?php endif; ?>
      </div>

      <div class="payment-actions">
        <button class="btn btn-primary">Save Payment</button>
        <a class="btn btn-outline" href="<?php echo APP_BASE; ?>/staff/loan_view.php?id=<?= intval($loan_id) ?>">Cancel</a>
      </div>
    </form>
  </section>
</div>

<script>
const remaining = <?= $loan['remaining_balance'] === null ? "null" : floatval($loan['remaining_balance']) ?>;
document.getElementById('amount').addEventListener('input', (e) => {
  if (remaining === null) return;
  const amt = parseFloat(e.target.value || '0');
  const after = Math.max(0, (remaining - amt)).toFixed(2);
  document.getElementById('after').textContent = "Remaining after: PHP " + parseFloat(after).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

function toggleChequeFields() {
  const method = document.getElementById('method').value;
  const chequeFields = document.getElementById('cheque-fields');
  const bankFields = document.getElementById('bank-fields');
  const gcashFields = document.getElementById('gcash-fields');
  const chequeInputs = ['cheque_number', 'cheque_date', 'bank_name', 'account_holder'];
  const bankInputs = ['bank_reference_no'];
  const gcashInputs = ['gcash_reference_no'];

  chequeFields.style.display = 'none';
  bankFields.style.display = 'none';
  gcashFields.style.display = 'none';

  chequeInputs.forEach(id => document.getElementById(id).required = false);
  bankInputs.forEach(id => document.getElementById(id).required = false);
  gcashInputs.forEach(id => document.getElementById(id).required = false);

  if (method === 'CHEQUE') {
    chequeFields.style.display = 'block';
    chequeInputs.forEach(id => document.getElementById(id).required = true);
  } else if (method === 'BANK') {
    bankFields.style.display = 'block';
    bankInputs.forEach(id => document.getElementById(id).required = true);
  } else if (method === 'GCASH') {
    gcashFields.style.display = 'block';
    gcashInputs.forEach(id => document.getElementById(id).required = true);
  }
}
</script>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
