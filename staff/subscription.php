<?php
require_once __DIR__ . '/../includes/auth.php';

require_login();
require_permission('view_subscription');

if (is_admin_owner()) {
  header("Location: " . APP_BASE . "/staff/my_subscription.php");
  exit;
}

$title = "Subscription";
$active = "subscription";

// Legacy placeholder plan content kept only for layout history.
$unused_subscription_mock_plans = [
  [
    'name' => 'Starter',
    'price' => '₱1,499',
    'billing' => 'per month',
    'tagline' => 'For small lending teams launching one branch.',
    'accent' => 'starter',
    'featured' => false,
    'features' => [
      'Up to 5 staff accounts',
      '1 active tenant workspace',
      'Loan application and payment tracking',
      'Basic borrower history and receipt records',
      'Email support during business hours',
    ],
  ],
  [
    'name' => 'Growth',
    'price' => '₱3,999',
    'billing' => 'per month',
    'tagline' => 'For growing operations that need tighter control and reporting.',
    'accent' => 'growth',
    'featured' => true,
    'features' => [
      'Up to 20 staff accounts',
      '3 active tenant workspaces',
      'Manager review and CI workflow tools',
      'Collections, voucher, and audit activity tracking',
      'Priority onboarding and support',
    ],
  ],
  [
    'name' => 'Enterprise',
    'price' => 'Custom',
    'billing' => 'annual plan',
    'tagline' => 'For multi-branch lenders that need operational depth and scale.',
    'accent' => 'enterprise',
    'featured' => false,
    'features' => [
      'Unlimited staff and tenant workspaces',
      'Central oversight for owners and admins',
      'Advanced reporting and tenant management',
      'Dedicated account support and migration help',
      'Custom onboarding, security review, and rollout planning',
    ],
  ],
];

$unused_subscription_mock_comparison_rows = [
  ['label' => 'Tenant workspaces', 'starter' => '1', 'growth' => '3', 'enterprise' => 'Unlimited'],
  ['label' => 'Staff accounts', 'starter' => '5', 'growth' => '20', 'enterprise' => 'Unlimited'],
  ['label' => 'Manager approval flow', 'starter' => 'Included', 'growth' => 'Included', 'enterprise' => 'Included'],
  ['label' => 'Custom branding', 'starter' => 'Basic', 'growth' => 'Advanced', 'enterprise' => 'Full service'],
  ['label' => 'Support coverage', 'starter' => 'Business hours', 'growth' => 'Priority', 'enterprise' => 'Dedicated'],
];

$unused_subscription_mock_metrics = [
  ['label' => 'Active tenants supported', 'value' => '50+'],
  ['label' => 'Average onboarding window', 'value' => '3 days'],
  ['label' => 'Audit visibility included', 'value' => 'Enterprise'],
];

$plan_order = ['BASIC', 'PROFESSIONAL', 'ENTERPRISE'];
$plan_taglines = [
  'BASIC' => 'For small lending teams that need a simple owner and staff seat structure.',
  'PROFESSIONAL' => 'For growing operations that need broader staffing capacity and branded workflows.',
  'ENTERPRISE' => 'For larger lending teams that need advanced controls and expanded staffing capacity.',
];
$plan_accent_map = [
  'BASIC' => 'starter',
  'PROFESSIONAL' => 'growth',
  'ENTERPRISE' => 'enterprise',
];
$plan_definitions = subscription_plan_definitions();
$role_labels = subscription_billable_role_labels();
$plans = [];

foreach ($plan_order as $plan_code) {
  $plan = $plan_definitions[$plan_code];
  $plans[] = [
    'name' => $plan['name'],
    'price' => 'PHP ' . number_format(floatval($plan['monthly_price'] ?? 0), 2),
    'billing' => 'per month',
    'tagline' => $plan_taglines[$plan_code] ?? '',
    'accent' => $plan_accent_map[$plan_code] ?? strtolower($plan_code),
    'featured' => ($plan_code === 'PROFESSIONAL'),
    'features' => [
      $role_labels['ADMIN'] . ': ' . subscription_plan_limit_label($plan, 'max_admins'),
      $role_labels['MANAGER'] . ': ' . subscription_plan_limit_label($plan, 'max_managers'),
      $role_labels['CREDIT_INVESTIGATOR'] . ': ' . subscription_plan_limit_label($plan, 'max_credit_investigators'),
      $role_labels['LOAN_OFFICER'] . ': ' . subscription_plan_limit_label($plan, 'max_loan_officers'),
      $role_labels['CASHIER'] . ': ' . subscription_plan_limit_label($plan, 'max_cashiers'),
      'Total billable seats: ' . subscription_plan_limit_label($plan, 'max_total_users', '20+'),
    ],
  ];
}

$comparison_rows = [
  ['label' => $role_labels['ADMIN'], 'basic' => subscription_plan_limit_label($plan_definitions['BASIC'], 'max_admins'), 'professional' => subscription_plan_limit_label($plan_definitions['PROFESSIONAL'], 'max_admins'), 'enterprise' => subscription_plan_limit_label($plan_definitions['ENTERPRISE'], 'max_admins')],
  ['label' => $role_labels['MANAGER'], 'basic' => subscription_plan_limit_label($plan_definitions['BASIC'], 'max_managers'), 'professional' => subscription_plan_limit_label($plan_definitions['PROFESSIONAL'], 'max_managers'), 'enterprise' => subscription_plan_limit_label($plan_definitions['ENTERPRISE'], 'max_managers')],
  ['label' => $role_labels['CREDIT_INVESTIGATOR'], 'basic' => subscription_plan_limit_label($plan_definitions['BASIC'], 'max_credit_investigators'), 'professional' => subscription_plan_limit_label($plan_definitions['PROFESSIONAL'], 'max_credit_investigators'), 'enterprise' => subscription_plan_limit_label($plan_definitions['ENTERPRISE'], 'max_credit_investigators')],
  ['label' => $role_labels['LOAN_OFFICER'], 'basic' => subscription_plan_limit_label($plan_definitions['BASIC'], 'max_loan_officers'), 'professional' => subscription_plan_limit_label($plan_definitions['PROFESSIONAL'], 'max_loan_officers'), 'enterprise' => subscription_plan_limit_label($plan_definitions['ENTERPRISE'], 'max_loan_officers')],
  ['label' => $role_labels['CASHIER'], 'basic' => subscription_plan_limit_label($plan_definitions['BASIC'], 'max_cashiers'), 'professional' => subscription_plan_limit_label($plan_definitions['PROFESSIONAL'], 'max_cashiers'), 'enterprise' => subscription_plan_limit_label($plan_definitions['ENTERPRISE'], 'max_cashiers')],
  ['label' => 'Total billable seats', 'basic' => subscription_plan_limit_label($plan_definitions['BASIC'], 'max_total_users', '20+'), 'professional' => subscription_plan_limit_label($plan_definitions['PROFESSIONAL'], 'max_total_users', '20+'), 'enterprise' => subscription_plan_limit_label($plan_definitions['ENTERPRISE'], 'max_total_users', '20+')],
];

$current_subscription_tenant_id = intval(current_tenant_id() ?? 0);
if ($current_subscription_tenant_id > 0) {
  $current_plan_info = get_tenant_plan_human_info($current_subscription_tenant_id);
  $subscription_metrics = [
    ['label' => 'Current plan', 'value' => $current_plan_info['plan_name'] ?? 'Unknown'],
    ['label' => 'Subscription status', 'value' => $current_plan_info['subscription_status_label'] ?? 'Unavailable'],
    ['label' => 'Billable seat usage', 'value' => $current_plan_info['usage_summary'] ?? 'Unavailable'],
  ];
} else {
  $subscription_metrics = [
    ['label' => 'Counted seat roles', 'value' => '5'],
    ['label' => 'Excluded from billing', 'value' => 'TENANT, CUSTOMER, SUPER_ADMIN'],
    ['label' => 'Billing basis', 'value' => 'Owner and staff seats only'],
  ];
}

include __DIR__ . '/_layout_top.php';
?>
<style>
  body {
    background:
      radial-gradient(circle at top, rgba(14, 165, 233, 0.12), transparent 32%),
      linear-gradient(180deg, #020617 0%, #081121 44%, #0f172a 100%);
    color: #e5eefb;
  }

  .topbar {
    background: linear-gradient(135deg, #081121, #0f1b35) !important;
    border-bottom: 1px solid rgba(148, 163, 184, 0.14);
    box-shadow: 0 18px 40px rgba(2, 6, 23, 0.35);
  }

  .topbar .small,
  .topbar a.btn.btn-outline {
    color: #d8e4f5 !important;
    border-color: rgba(148, 163, 184, 0.24) !important;
  }

  .layout,
  .main {
    background: transparent;
  }

  .subscription-shell {
    position: relative;
    display: grid;
    gap: 24px;
    color: #e5eefb;
  }

  .subscription-shell::before,
  .subscription-shell::after {
    content: "";
    position: fixed;
    width: 420px;
    height: 420px;
    border-radius: 999px;
    filter: blur(90px);
    opacity: 0.18;
    pointer-events: none;
    z-index: 0;
  }

  .subscription-shell::before {
    top: 96px;
    right: 6%;
    background: rgba(59, 130, 246, 0.52);
  }

  .subscription-shell::after {
    bottom: 20px;
    left: 8%;
    background: rgba(14, 165, 233, 0.3);
  }

  .subscription-shell > * {
    position: relative;
    z-index: 1;
  }

  .subscription-hero,
  .subscription-story,
  .subscription-faq,
  .subscription-comparison {
    border-radius: 28px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
      radial-gradient(circle at top left, rgba(56, 189, 248, 0.14), transparent 30%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(8, 15, 30, 0.95));
    box-shadow: 0 24px 60px rgba(2, 6, 23, 0.34);
  }

  .subscription-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
    gap: 22px;
    padding: 32px;
    overflow: hidden;
  }

  .subscription-kicker,
  .subscription-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: fit-content;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(125, 211, 252, 0.24);
    background: rgba(14, 165, 233, 0.12);
    color: #d8f4ff;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .subscription-hero h1 {
    margin: 0;
    font-size: clamp(32px, 4vw, 54px);
    line-height: 0.98;
    letter-spacing: -0.05em;
    color: #f8fbff;
  }

  .subscription-hero p,
  .subscription-story p,
  .subscription-faq p,
  .subscription-plan-copy,
  .subscription-compare-note,
  .subscription-cta-copy {
    margin: 0;
    color: #9fb0c9;
    font-size: 15px;
    line-height: 1.7;
  }

  .subscription-hero-copy {
    display: grid;
    gap: 16px;
    align-content: start;
  }

  .subscription-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }

  .subscription-button,
  .subscription-button-alt {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 18px;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 700;
    transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
  }

  .subscription-button {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.94), rgba(37, 99, 235, 0.92));
    color: #f8fbff;
    box-shadow: 0 16px 30px rgba(37, 99, 235, 0.28);
  }

  .subscription-button-alt {
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(15, 23, 42, 0.7);
    color: #e5eefb;
  }

  .subscription-button:hover,
  .subscription-button-alt:hover {
    text-decoration: none;
    transform: translateY(-1px);
  }

  .subscription-metrics {
    display: grid;
    gap: 14px;
  }

  .subscription-metric-card {
    padding: 18px 20px;
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(12px);
  }

  .subscription-metric-label {
    color: #8ea3bf;
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-bottom: 10px;
  }

  .subscription-metric-value {
    font-size: clamp(28px, 4vw, 42px);
    line-height: 1;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.04em;
  }

  .subscription-plan-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .subscription-plan {
    display: grid;
    gap: 18px;
    padding: 26px;
    border-radius: 26px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
      linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(8, 15, 30, 0.95));
    box-shadow: 0 18px 44px rgba(2, 6, 23, 0.3);
  }

  .subscription-plan.featured {
    transform: translateY(-6px);
    border-color: rgba(96, 165, 250, 0.42);
    background:
      radial-gradient(circle at top, rgba(56, 189, 248, 0.18), transparent 34%),
      linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(8, 15, 30, 0.98));
    box-shadow: 0 24px 56px rgba(8, 47, 73, 0.36);
  }

  .subscription-plan-top {
    display: grid;
    gap: 10px;
  }

  .subscription-plan-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .subscription-plan h2 {
    margin: 0;
    font-size: 26px;
    letter-spacing: -0.04em;
    color: #f8fbff;
  }

  .subscription-plan-price {
    display: flex;
    align-items: baseline;
    gap: 8px;
  }

  .subscription-plan-price strong {
    font-size: clamp(36px, 4vw, 48px);
    line-height: 1;
    letter-spacing: -0.05em;
    color: #ffffff;
  }

  .subscription-plan-price span {
    color: #8ea3bf;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .subscription-plan ul {
    display: grid;
    gap: 12px;
    margin: 0;
    padding: 0;
    list-style: none;
  }

  .subscription-plan li {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    color: #d9e7f7;
    line-height: 1.6;
  }

  .subscription-plan li i {
    color: #7dd3fc;
    margin-top: 3px;
  }

  .subscription-story {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
    gap: 22px;
    padding: 28px;
  }

  .subscription-story h3,
  .subscription-comparison h3,
  .subscription-faq h3 {
    margin: 0 0 12px;
    font-size: 24px;
    letter-spacing: -0.03em;
    color: #f8fbff;
  }

  .subscription-story-points {
    display: grid;
    gap: 16px;
  }

  .subscription-story-point {
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
  }

  .subscription-story-point:last-child {
    padding-bottom: 0;
    border-bottom: none;
  }

  .subscription-story-point strong {
    display: block;
    margin-bottom: 6px;
    color: #f8fbff;
    font-size: 16px;
  }

  .subscription-comparison {
    padding: 28px;
  }

  .subscription-compare-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
  }

  .subscription-compare-table th,
  .subscription-compare-table td {
    padding: 16px 14px;
    text-align: left;
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
  }

  .subscription-compare-table th {
    color: #93a8c6;
    font-size: 11px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }

  .subscription-compare-table td {
    color: #e5eefb;
    font-size: 14px;
  }

  .subscription-compare-table tr:last-child td {
    border-bottom: none;
  }

  .subscription-faq {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(280px, 0.95fr);
    gap: 22px;
    padding: 28px;
  }

  .subscription-faq-list {
    display: grid;
    gap: 14px;
  }

  .subscription-faq-item {
    padding: 18px 20px;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.72);
  }

  .subscription-faq-item strong {
    display: block;
    margin-bottom: 6px;
    color: #f8fbff;
  }

  .subscription-cta {
    display: grid;
    gap: 14px;
    padding: 22px;
    border-radius: 24px;
    border: 1px solid rgba(96, 165, 250, 0.24);
    background:
      radial-gradient(circle at top right, rgba(56, 189, 248, 0.18), transparent 35%),
      rgba(15, 23, 42, 0.8);
  }

  .subscription-cta h4 {
    margin: 0;
    font-size: 22px;
    color: #f8fbff;
    letter-spacing: -0.03em;
  }

  @media (max-width: 1120px) {
    .subscription-hero,
    .subscription-story,
    .subscription-faq,
    .subscription-plan-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 720px) {
    .subscription-hero,
    .subscription-story,
    .subscription-faq,
    .subscription-comparison,
    .subscription-plan {
      padding: 20px;
      border-radius: 20px;
    }

    .subscription-compare-table,
    .subscription-compare-table thead,
    .subscription-compare-table tbody,
    .subscription-compare-table th,
    .subscription-compare-table td,
    .subscription-compare-table tr {
      display: block;
      width: 100%;
    }

    .subscription-compare-table thead {
      display: none;
    }

    .subscription-compare-table tr {
      padding: 12px 0;
      border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    }

    .subscription-compare-table tr:last-child {
      border-bottom: none;
    }

    .subscription-compare-table td {
      padding: 8px 0;
      border-bottom: none;
    }
  }
</style>

<div class="subscription-shell">
  <section class="subscription-hero">
    <div class="subscription-hero-copy">
      <span class="subscription-kicker"><i class="bi bi-stars"></i> Subscription Plans</span>
      <h1>Choose the billing tier that matches your team&rsquo;s billable seat needs.</h1>
      <p>
        Subscription limits now count only owner and staff seats. Tenants are organization containers, not billed user roles.
      </p>
      <div class="subscription-hero-actions">
        <a class="subscription-button" href="#plans">View plans</a>
        <a class="subscription-button-alt" href="#compare">Compare details</a>
      </div>
    </div>

    <div class="subscription-metrics">
      <?php foreach ($subscription_metrics as $metric): ?>
        <div class="subscription-metric-card">
          <div class="subscription-metric-label"><?= htmlspecialchars($metric['label']) ?></div>
          <div class="subscription-metric-value"><?= htmlspecialchars($metric['value']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="subscription-plan-grid" id="plans">
    <?php foreach ($plans as $plan): ?>
      <article class="subscription-plan <?= $plan['featured'] ? 'featured' : '' ?>">
        <div class="subscription-plan-top">
          <div class="subscription-plan-title-row">
            <h2><?= htmlspecialchars($plan['name']) ?></h2>
            <?php if ($plan['featured']): ?>
              <span class="subscription-chip"><i class="bi bi-lightning-charge-fill"></i> Popular</span>
            <?php endif; ?>
          </div>
          <div class="subscription-plan-price">
            <strong><?= htmlspecialchars($plan['price']) ?></strong>
            <span><?= htmlspecialchars($plan['billing']) ?></span>
          </div>
          <p class="subscription-plan-copy"><?= htmlspecialchars($plan['tagline']) ?></p>
        </div>

        <ul>
          <?php foreach ($plan['features'] as $feature): ?>
            <li><i class="bi bi-check2-circle"></i><span><?= htmlspecialchars($feature) ?></span></li>
          <?php endforeach; ?>
        </ul>

        <a class="subscription-button-alt" href="#">Select <?= htmlspecialchars($plan['name']) ?></a>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="subscription-story">
    <div>
      <span class="subscription-kicker"><i class="bi bi-diagram-3-fill"></i> Sample rollout</span>
      <h3>Built for lenders that need clear seat counting and predictable plan limits.</h3>
      <p>
        Plan usage is based on billable roles only: Admin (Owner), Manager, Credit Investigator, Loan Officer, and Cashier.
        Legacy TENANT users are not counted as seats and do not consume admin capacity.
      </p>
    </div>

    <div class="subscription-story-points">
      <div class="subscription-story-point">
        <strong>Count only billable seats</strong>
        <p>Tenant containers are not users. Only owner and staff roles count toward plan enforcement.</p>
      </div>
      <div class="subscription-story-point">
        <strong>Role limits stay explicit</strong>
        <p>Each plan separates owner, manager, credit investigator, loan officer, and cashier capacity.</p>
      </div>
      <div class="subscription-story-point">
        <strong>Aligned with backend enforcement</strong>
        <p>The plan helper layer now uses the same counted-role rules reflected on this page.</p>
      </div>
    </div>
  </section>

  <section class="subscription-comparison" id="compare">
    <span class="subscription-kicker"><i class="bi bi-table"></i> Comparison</span>
    <h3>What each billing tier counts</h3>
    <p class="subscription-compare-note">The comparison below reflects billable seat limits only.</p>

    <table class="subscription-compare-table">
      <thead>
        <tr>
          <th>Feature</th>
          <th>Basic</th>
          <th>Professional</th>
          <th>Enterprise</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($comparison_rows as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['label']) ?></td>
            <td><?= htmlspecialchars($row['basic']) ?></td>
            <td><?= htmlspecialchars($row['professional']) ?></td>
            <td><?= htmlspecialchars($row['enterprise']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="subscription-faq">
    <div>
      <span class="subscription-kicker"><i class="bi bi-chat-square-text"></i> Questions</span>
      <h3>Common upgrade and billing questions</h3>
      <div class="subscription-faq-list">
        <div class="subscription-faq-item">
          <strong>Is a tenant counted as a billed user?</strong>
          <p>No. A tenant is an organization container. It is not a subscription seat and is not counted as an admin.</p>
        </div>
        <div class="subscription-faq-item">
          <strong>Which roles count as seats?</strong>
          <p>Only Admin (Owner), Manager, Credit Investigator, Loan Officer, and Cashier are counted for billing.</p>
        </div>
        <div class="subscription-faq-item">
          <strong>What happens to legacy TENANT users?</strong>
          <p>They remain legacy-compatible in the codebase, but they are excluded from plan seat counting and admin-seat usage.</p>
        </div>
      </div>
    </div>

    <aside class="subscription-cta">
      <span class="subscription-kicker"><i class="bi bi-rocket-takeoff-fill"></i> Next step</span>
      <h4>Use this page to explain billable seat limits to owners.</h4>
      <p class="subscription-cta-copy">
        This page now reflects billable-seat rules, but payment collection and self-service plan changes are still separate work.
      </p>
      <a class="subscription-button" href="#">Request enterprise demo</a>
    </aside>
  </section>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
