<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/loan_helpers.php';
require_login();
require_permission('manage_vouchers');

// FORCE MANILA TIME
date_default_timezone_set('Asia/Manila');
$today_manila = date('Y-m-d'); 

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { exit("Invalid Loan ID"); }
enforce_tenant_resource_access('loans', 'loan_id', $id);

// 1. Fetch Loan Details
$loan = fetch_one(q("SELECT l.*, c.first_name, c.last_name, c.street, c.barangay, c.city 
                      FROM loans l JOIN customers c ON c.customer_id = l.customer_id AND c.tenant_id=l.tenant_id
                      WHERE " . tenant_condition('l.tenant_id') . " AND l.loan_id=?", tenant_types("i"), tenant_params([$id])));
if (!$loan) { exit("Loan not found"); }

$real_client_name = strtoupper($loan['first_name'] . ' ' . $loan['last_name']);

// 2. Fetch existing voucher - Be strict about the ID
$existing = fetch_one(q("SELECT * FROM money_release_vouchers WHERE " . tenant_condition('tenant_id') . " AND loan_id=? AND status!='CANCELLED' LIMIT 1", tenant_types("i"), tenant_params([$id])));
$editMode = (isset($_GET['edit']) && $_GET['edit'] === '1') || !$existing;

/**
 * 3. CALCULATION LOGIC
 */
$principal = floatval($loan['principal_amount']); 
$term = strtolower($loan['payment_term']);
$rates = ['daily'=>0.0275, 'weekly'=>0.03, 'semi_monthly'=>0.035, 'monthly'=>0.04];
$active_rate = $rates[$term] ?? 0.03;

$service_fee    = $principal * $active_rate;
$notarial_alloc = ($principal > 5000) ? 150.00 : 50.00;
$risk_alloc     = $principal * 0.01;
$doc_stamps     = $principal * 0.005;

$current_allocations_default = [
    'CASH IN BANK-SB' => $principal, 
    'UNEARNED SERVICE FEE' => $service_fee,
    'UNEARNED NOTARIAL ALLOCATION' => $notarial_alloc,
    'RISK MANAGEMENT ALLOCATION' => $risk_alloc,
    'PAF COLLECTED' => 0.00,
    'DOCUMENTARY STAMPS ALLOC' => $doc_stamps
];

/**
 * 4. PERSISTENCE SYNC
 */
$current_allocations = [];
if ($existing && !empty($existing['voucher_data'])) {
    $decoded = json_decode($existing['voucher_data'], true);
    $saved_accounts = $decoded['accounts'] ?? [];
    if (!isset($saved_accounts['CASH IN BANK-SB']) || floatval($saved_accounts['CASH IN BANK-SB']) != $principal) {
        $current_allocations = $current_allocations_default;
    } else {
        $current_allocations = $saved_accounts;
    }
} else {
    $current_allocations = $current_allocations_default;
}

/**
 * 5. THE ULTIMATE DATE FIX
 * If it's a NEW application (no existing voucher) OR the voucher is just a DRAFT:
 * We IGNORE the database and force PHP's current date.
 */
if (!$existing || (isset($existing['status']) && $existing['status'] === 'DRAFT')) {
    $display_date = $today_manila; 
} else {
    // Only use the DB date if the voucher is actually completed/released
    $display_date = $existing['release_date'];
}

/**
 * 6. POST HANDLING
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_voucher'])) {
    $allocations = [];
    $final_release_amount = 0;
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'alloc_') === 0) {
            $account_name = urldecode(substr($k, 6));
            $val = floatval($v);
            $allocations[$account_name] = $val;
            if ($account_name === 'CASH IN BANK-SB') { $final_release_amount = $val; }
        }
    }
    
    $v_data = json_encode(['accounts' => $allocations]);
    $voucher_no_auto = str_replace('APP-', 'VCH-', $loan['reference_no']);
    $post_date = $_POST['release_date']; // This takes the date from the form
    $post_received = $_POST['received_by_name'];
    
    if ($existing) {
        q("UPDATE money_release_vouchers SET release_date=?, check_amount=?, received_by_name=?, voucher_data=?, status='DRAFT' WHERE tenant_id=? AND loan_id=?",
          "sdssii", [$post_date, $final_release_amount, $post_received, $v_data, $loan['tenant_id'], $id]);
    } else {
        q("INSERT INTO money_release_vouchers (tenant_id, loan_id, voucher_no, release_date, check_no, check_amount, prepared_by, received_by_name, voucher_data) VALUES (?,?,?,?,?,?,?,?,?)",
          "iisssdsss", [$loan['tenant_id'], $id, $voucher_no_auto, $post_date, 'MANUAL', $final_release_amount, $_SESSION['user_id'], $post_received, $v_data]);
    }
    header("Location: release_voucher.php?id=$id&ok=1"); exit;
}

include __DIR__ . '/_layout_top.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    body { background: radial-gradient(circle at top, rgba(14, 165, 233, 0.12), transparent 30%), linear-gradient(180deg, #020617 0%, #081121 42%, #0f172a 100%); color: #e5eefb; }
    .topbar { background: linear-gradient(135deg, #081121, #0f1b35) !important; border-bottom: 1px solid rgba(148, 163, 184, 0.14); box-shadow: 0 18px 40px rgba(2, 6, 23, 0.35); }
    .topbar .small, .topbar a.btn.btn-outline { color: #d8e4f5 !important; border-color: rgba(148, 163, 184, 0.24) !important; }
    .layout, .main { background: transparent; }
    .sidebar { background: rgba(4, 10, 24, 0.84); border-right: 1px solid rgba(148, 163, 184, 0.12); backdrop-filter: blur(16px); }
    .sidebar h3 { color: #7f93b0; }
    .sidebar a { color: #d7e3f4; }
    .sidebar a.active, .sidebar a:hover { background: linear-gradient(135deg, rgba(14, 165, 233, 0.18), rgba(59, 130, 246, 0.2)); color: #f8fbff; }
    .voucher-shell { display: grid; gap: 20px; color: #e5eefb; }
    .voucher-card { border-radius: 26px; border: 1px solid rgba(148, 163, 184, 0.16); background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent 26%), linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.97)); box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34); padding: 24px; }
    .voucher-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .voucher-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; border: 1px solid rgba(125, 211, 252, 0.24); background: rgba(14, 165, 233, 0.12); color: #d8f4ff; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; }
    .voucher-header h2 { margin: 10px 0 0; font-size: clamp(30px, 4vw, 44px); line-height: 1; letter-spacing: -0.04em; color: #f8fbff; }
    .voucher-header p { margin: 10px 0 0; color: #8ea3bf; line-height: 1.7; }
    .voucher-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
    .btn-brand-red { background: linear-gradient(135deg, #0ea5e9, #2563eb) !important; color: #fff !important; border: 1px solid rgba(125, 211, 252, 0.28) !important; padding: 10px 20px; border-radius: 14px; cursor: pointer; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 16px 30px rgba(14, 165, 233, 0.22); }
    .btn-brand-red:hover { opacity: 0.96; transform: translateY(-1px); }
    .btn-outline-custom { border: 1px solid rgba(148, 163, 184, 0.22); padding: 10px 20px; border-radius: 14px; text-decoration: none; color: #d8e4f5; font-weight: 700; background: rgba(15, 23, 42, 0.55); display: inline-flex; align-items: center; justify-content: center; }
    .input { width: 100%; padding: 11px 12px; border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 12px; background: rgba(15, 23, 42, 0.86); color: #f8fbff; }
    .input::placeholder { color: #6f86a6; }
    .field-label { display: block; margin-bottom: 6px; color: #8ea3bf; font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase; }
    .voucher-meta { display: grid; gap: 14px; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 22px; }
    .voucher-meta-card { border-radius: 18px; border: 1px solid rgba(148, 163, 184, 0.14); background: rgba(15, 23, 42, 0.68); padding: 18px; }
    .voucher-meta-card span { display: block; color: #8ea3bf; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
    .voucher-meta-card strong { display: block; color: #f8fbff; font-size: 20px; line-height: 1.2; }
    .voucher-form-card { margin-top: 24px; border-top: 1px solid rgba(148, 163, 184, 0.12); padding-top: 24px; }
    .voucher-form-card h3 { margin: 0 0 8px; color: #f8fbff; }
    .voucher-form-card p { margin: 0 0 18px; color: #8ea3bf; }
    .voucher-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .voucher-account-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
    .voucher-print-wrap { border-radius: 22px; padding: 18px; background: linear-gradient(180deg, rgba(248, 250, 252, 0.08), rgba(148, 163, 184, 0.04)); border: 1px solid rgba(148, 163, 184, 0.12); }

    @media print {
        body * { visibility: hidden; }
        #voucher-print-area, #voucher-print-area * { visibility: visible; }
        #voucher-print-area { position: absolute; left: 0; top: 0; width: 100%; border: none !important; padding: 0 !important; }
        .voucher-card, .voucher-print-wrap { border: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; }
        .btn-brand-red, .btn-outline-custom, form, .voucher-header, .voucher-meta { display: none !important; }
    }
    @media (max-width: 900px) {
        .voucher-meta, .voucher-form-grid, .voucher-account-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 760px) {
        .voucher-card { padding: 18px; border-radius: 18px; }
        .voucher-actions { width: 100%; justify-content: stretch; }
        .voucher-actions > * { flex: 1 1 180px; }
    }
</style>

<div class="voucher-shell">
<div class="voucher-card">
    <div class="voucher-header no-print">
        <div>
            <span class="voucher-kicker">Voucher Release</span>
            <h2>Money Release Voucher</h2>
            <p>Review the generated voucher, adjust release details if needed, and export a clean final copy for release processing.</p>
        </div>
        <div class="voucher-actions">
            <a class="btn-brand-red" href="release_queue.php">Back to Queue</a>
            <?php if(!$editMode): ?>
                <a class="btn-brand-red" href="?id=<?= $id ?>&edit=1">Edit Voucher</a>
                <button class="btn-brand-red" onclick="saveToPDF()">Save to PDF</button>
                <button class="btn-brand-red" onclick="window.print()">Print Voucher</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="voucher-meta no-print">
        <div class="voucher-meta-card">
            <span>Reference No</span>
            <strong><?= htmlspecialchars($loan['reference_no']) ?></strong>
        </div>
        <div class="voucher-meta-card">
            <span>Customer</span>
            <strong><?= htmlspecialchars($real_client_name) ?></strong>
        </div>
        <div class="voucher-meta-card">
            <span>Release Amount</span>
            <strong>PHP <?= number_format($current_allocations['CASH IN BANK-SB'] ?? 0, 2) ?></strong>
        </div>
    </div>

    <div class="voucher-print-wrap">
    <div id="voucher-print-area" style="background:#fff; border:1px solid #ddd; padding:40px; font-family:Arial, sans-serif; color:#000;">
        <div style="text-align:center; font-weight:bold; font-size:18px; text-transform:uppercase; margin-bottom:20px;">MONEY RELEASE VOUCHER</div>
        
        <div style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:2px solid #000; padding-bottom:10px;">
            <div><strong>Voucher No:</strong> <?= htmlspecialchars($existing['voucher_no'] ?? str_replace('APP-', 'VCH-', $loan['reference_no'])) ?></div>
            <div><strong>Date:</strong> <span style="font-weight: bold;"><?= date('F d, Y', strtotime($display_date)) ?></span></div>
        </div>

        <div style="margin-bottom:20px;"><strong>PAY TO:</strong> <?= $real_client_name ?></div>
        
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <thead>
                <tr style="background:#f4f4f4;">
                    <th style="padding:10px; border:1px solid #ddd; text-align:left;">ACCOUNT TITLE / PARTICULARS</th>
                    <th style="padding:10px; border:1px solid #ddd; text-align:right;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($current_allocations as $acc => $amt): ?>
                    <tr>
                        <td style="padding:10px; border:1px solid #ddd;"><?= htmlspecialchars($acc) ?></td>
                        <td style="padding:10px; border:1px solid #ddd; text-align:right;">₱<?= number_format($amt, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#eee; font-weight:bold;">
                    <td style="padding:12px; border:1px solid #ddd; text-align:right;">TOTAL</td>
                    <td style="padding:12px; border:1px solid #ddd; text-align:right;">₱<?= number_format(array_sum($current_allocations), 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top:60px; display:flex; justify-content:space-between;">
            <div style="width:40%; border-top:1px solid #000; text-align:center; padding-top:5px; font-size:12px; font-weight: bold;">
                Prepared By: <?= htmlspecialchars($existing['prepared_by_name'] ?? $_SESSION['full_name'] ?? 'Admin User') ?>
            </div>
            <div style="width:40%; border-top:1px solid #000; text-align:center; padding-top:5px; font-size:12px; font-weight: bold;">
                Received By: <?= $real_client_name ?>
            </div>
        </div>
    </div>
    </div>

    <?php if($editMode): ?>
    <div class="voucher-form-card no-print">
        <form method="post">
            <h3>Edit Voucher Details</h3>
            <p>Update release metadata and account allocations before saving the draft voucher.</p>
            <div class="voucher-form-grid">
                <div>
                    <label class="field-label">Release Date</label>
                    <input type="date" name="release_date" class="input" value="<?= $display_date ?>">
                </div>
                <div>
                    <label class="field-label">Received By Name</label>
                    <input type="text" name="received_by_name" class="input" value="<?= $real_client_name ?>">
                </div>
            </div>
            
            <h4 style="margin:0 0 14px; color:#f8fbff;">Account Breakdown</h4>
            <div class="voucher-account-grid">
                <?php foreach($current_allocations as $name => $amt): ?>
                    <div>
                        <label class="field-label"><?= $name ?></label>
                        <input type="number" step="0.01" name="alloc_<?= urlencode($name) ?>" class="input" value="<?= $amt ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:20px; display:flex; gap:10px;">
                <button class="btn-brand-red" name="save_voucher" type="submit">Save Voucher</button>
                <a href="?id=<?= $id ?>" class="btn-outline-custom">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
</div>

<script>
function saveToPDF() {
    const element = document.getElementById('voucher-print-area');
    const voucherNo = "<?= $existing['voucher_no'] ?? str_replace('APP-', 'VCH-', $loan['reference_no']) ?>";
    const opt = {
        margin: 0.5,
        filename: 'Voucher_' + voucherNo + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
