<?php
require_once __DIR__ . '/../includes/auth.php';

require_login();
require_permission('view_role_permissions');

$title = "Roles & Permissions";
$active = "roles";

// Role configuration with display order (descending access level) and colors
$roles = [
  'SUPER_ADMIN' => ['label' => 'Super Admin', 'color' => '#ef4444'],
  'ADMIN' => ['label' => 'Administrator', 'color' => '#f97316'], 
  'MANAGER' => ['label' => 'Manager', 'color' => '#eab308'],
  'CREDIT_INVESTIGATOR' => ['label' => 'Credit Investigator', 'color' => '#22c55e'],
  'LOAN_OFFICER' => ['label' => 'Loan Officer', 'color' => '#06b6d4'],
  'TENANT' => ['label' => 'Tenant', 'color' => '#8b5cf6'],
];

// Permission categories and their permissions
$permission_categories = [
  'Dashboard & Loans' => [
    'view_dashboard' => 'View Dashboard',
    'view_loans' => 'View Loans',
    'view_loan_details' => 'View Loan Details', 
    'update_loan_terms' => 'Update Loan Terms',
    'assign_loan_officer' => 'Assign Loan Officer',
  ],
  'Customers & Payments' => [
    'view_customers' => 'View Customers',
    'manage_customers' => 'Manage Customers',
    'view_payments' => 'View Payments',
    'record_payments' => 'Record Payments',
    'edit_payments' => 'Edit Payments',
    'print_receipts' => 'Print Receipts',
    'manage_vouchers' => 'Manage Vouchers',
  ],
  'Applications' => [
    'review_applications' => 'Review Applications',
    'approve_applications' => 'Approve Applications',
  ],
  'Reports' => [
    'view_reports' => 'View Reports',
    'view_advanced_reports' => 'View Advanced Reports',
    'view_sales' => 'View Sales',
  ],
  'Admin & Staff' => [
    'view_staff' => 'View Staff',
    'manage_staff' => 'Manage Staff', 
    'view_history' => 'View History',
    'manage_tenants' => 'Manage Tenants',
    'manage_subscriptions' => 'Manage Subscriptions',
    'view_role_permissions' => 'View Role Permissions',
    'manage_backups' => 'Manage Backups',
    'view_settings' => 'View Settings',
  ],
];

// Get all permission data
$all_permissions = auth_role_permissions();
$total_permissions = count($all_permissions);

// Calculate role statistics
$role_stats = [];
foreach ($roles as $role_key => $role_info) {
  $count = 0;
  foreach ($all_permissions as $permission => $allowed_roles) {
    if (in_array($role_key, $allowed_roles)) {
      $count++;
    }
  }
  $role_stats[$role_key] = [
    'count' => $count,
    'percentage' => ($count / $total_permissions) * 100
  ];
}

include __DIR__ . '/_layout_top.php';
?>
<style>
/* Override body and existing layout for this page */
body { 
  background: radial-gradient(circle at top, rgba(14, 165, 233, 0.12), transparent 30%), 
              linear-gradient(180deg, #020617 0%, #081121 42%, #0f172a 100%); 
  color: #e5eefb; 
  line-height: 1.5;
}

.topbar { 
  background: linear-gradient(135deg, #081121, #0f1b35) !important; 
  border-bottom: 1px solid rgba(148, 163, 184, 0.14); 
  box-shadow: 0 18px 40px rgba(2, 6, 23, 0.35); 
}

.topbar .small, .topbar a.btn.btn-outline { 
  color: #d8e4f5 !important; 
  border-color: rgba(148, 163, 184, 0.24) !important; 
}

.layout, .main { background: transparent; }
.sidebar { 
  background: rgba(4, 10, 24, 0.84); 
  border-right: 1px solid rgba(148, 163, 184, 0.12); 
  backdrop-filter: blur(16px);
}
.sidebar h3 { color: #7f93b0; }
.sidebar a { color: #d7e3f4; }
.sidebar a.active, .sidebar a:hover { 
  background: linear-gradient(135deg, rgba(14, 165, 233, 0.18), rgba(59, 130, 246, 0.2)); 
  color: #f8fbff; 
}

/* Content shell - work within existing .main container */
.content-shell {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

/* Page hero - non-scrolling banner */
.page-hero {
  background: rgba(15, 23, 42, 0.7);
  border-bottom: 1px solid rgba(148, 163, 184, 0.16);
  padding: 32px 20px;
  flex-shrink: 0;
}

.hero-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  max-width: none;
  margin: 0;
}

.hero-left {
  flex: 1;
}

.hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid rgba(125, 211, 252, 0.24);
  background: rgba(14, 165, 233, 0.12);
  color: #7dd3fc;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  margin-bottom: 12px;
}

.hero-title {
  font-size: 28px;
  font-weight: 700;
  color: #f8fbff;
  margin: 0 0 8px 0;
  line-height: 1.2;
}

.hero-subtitle {
  color: #94a3b8;
  font-size: 15px;
  margin: 0 0 24px 0;
  max-width: 500px;
}

.hero-stats {
  display: flex;
  gap: 16px;
}

.stat-chip {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.14);
  border-radius: 12px;
  padding: 12px 16px;
  min-width: 90px;
  text-align: center;
}

.stat-label {
  display: block;
  color: #64748b;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 6px;
}

.stat-value {
  display: block;
  color: #f8fbff;
  font-size: 20px;
  font-weight: 700;
  line-height: 1;
}

/* Scrollable content area */
.scrollable-content {
  flex: 1;
  overflow-y: auto;
  padding: 24px 20px;
}

.content-wrapper {
  max-width: none;
  margin: 0;
}

/* Filter bar */
.filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 24px;
}

.filter-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.filter-label {
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
}

.filter-pills {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-pill {
  background: transparent;
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #94a3b8;
  padding: 8px 16px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-pill:hover {
  border-color: rgba(148, 163, 184, 0.4);
  color: #cbd5e1;
}

.filter-pill.active {
  background: rgba(14, 165, 233, 0.15);
  border-color: rgba(14, 165, 233, 0.4);
  color: #7dd3fc;
}

.search-input {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #e2e8f0;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  width: 280px;
  outline: none;
  transition: all 0.2s ease;
}

.search-input:focus {
  border-color: rgba(14, 165, 233, 0.4);
  background: rgba(15, 23, 42, 0.8);
}

.search-input::placeholder {
  color: #64748b;
}

/* Legend row */
.legend-row {
  display: flex;
  gap: 24px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #94a3b8;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Matrix table */
.matrix-container {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.16);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(2, 6, 23, 0.3);
}

.matrix-table {
  width: 100%;
  border-collapse: collapse;
}

/* Column headers */
.matrix-table thead th {
  background: rgba(8, 17, 33, 0.9);
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
  padding: 20px 16px;
  text-align: center;
  position: sticky;
  top: 0;
  z-index: 10;
}

.matrix-table thead th:first-child {
  text-align: left;
  width: 220px;
  padding-left: 24px;
}

.permission-header {
  color: #94a3b8;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.role-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  min-width: 140px;
}

.role-name {
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 4px;
}

.role-progress {
  width: 100%;
  max-width: 80px;
  height: 3px;
  background: rgba(148, 163, 184, 0.2);
  border-radius: 2px;
  overflow: hidden;
}

.role-progress-fill {
  height: 100%;
  border-radius: 2px;
  transition: width 0.3s ease;
}

.role-count {
  color: #64748b;
  font-size: 11px;
  font-weight: 500;
}

/* Category divider rows */
.category-divider td {
  background: rgba(8, 17, 33, 0.6);
  border-top: 1px solid rgba(148, 163, 184, 0.1);
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  padding: 12px 24px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #7c3aed;
}

/* Permission rows */
.permission-row {
  transition: background-color 0.15s ease;
}

.permission-row:hover {
  background: rgba(15, 23, 42, 0.6);
}

.permission-row td {
  padding: 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.08);
}

.permission-row.category-last td {
  border-bottom: none;
}

.permission-name {
  color: #e2e8f0;
  font-size: 14px;
  font-weight: 500;
  padding-left: 24px;
}

.permission-cell {
  text-align: center;
  vertical-align: middle;
}

.permission-indicator {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
}

.permission-granted {
  background: #059669;
  border-radius: 50%;
  color: white;
  font-size: 12px;
  font-weight: 600;
}

.permission-denied {
  color: #64748b;
  font-weight: 400;
}

/* Hidden state for filtering */
.hidden {
  display: none;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
  .scrollable-content {
    padding: 20px 16px;
  }
  
  .page-hero {
    padding: 24px 16px;
  }
  
  .hero-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
  }
  
  .filter-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }
  
  .search-input {
    width: 100%;
  }
  
  .matrix-table {
    font-size: 13px;
  }
  
  .matrix-table thead th:first-child {
    width: 180px;
  }
}

@media (max-width: 768px) {
  .hero-stats {
    flex-direction: column;
    gap: 12px;
  }
  
  .stat-chip {
    min-width: auto;
  }
  
  .filter-pills {
    justify-content: center;
  }
  
  .legend-row {
    justify-content: center;
  }
  
  .matrix-table thead th:first-child {
    width: 160px;
  }
  
  .role-header {
    min-width: 100px;
  }
}
</style>

<div class="content-shell">
  <!-- Page Hero -->
  <div class="page-hero">
    <div class="hero-content">
      <div class="hero-left">
        <span class="hero-eyebrow">System Access Control</span>
        <h1 class="hero-title">Role Permissions Matrix</h1>
           </div>
      <div class="hero-stats">
        <div class="stat-chip">
          <span class="stat-label">Roles</span>
          <strong class="stat-value"><?= count($roles) ?></strong>
        </div>
        <div class="stat-chip">
          <span class="stat-label">Permissions</span>
          <strong class="stat-value"><?= $total_permissions ?></strong>
        </div>
        <div class="stat-chip">
          <span class="stat-label">Tenant Context</span>
          <strong class="stat-value">Global</strong>
        </div>
      </div>
    </div>
  </div>

  <!-- Scrollable Content -->
  <div class="scrollable-content">
    <div class="content-wrapper">
      
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-left">
          <span class="filter-label">Category:</span>
          <div class="filter-pills">
            <button class="filter-pill active" data-category="all">All</button>
            <?php foreach ($permission_categories as $category => $permissions): ?>
              <button class="filter-pill" data-category="<?= strtolower(str_replace([' ', '&'], ['-', 'and'], $category)) ?>">
                <?= htmlspecialchars($category) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <input type="text" class="search-input" placeholder="Search permissions..." id="permission-search">
      </div>

      <!-- Legend Row -->
      <div class="legend-row">
        <?php foreach ($roles as $role_key => $role_info): ?>
          <div class="legend-item">
            <div class="legend-dot" style="background-color: <?= $role_info['color'] ?>;"></div>
            <span><?= htmlspecialchars($role_info['label']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Matrix Table -->
      <div class="matrix-container">
        <table class="matrix-table">
          <thead>
            <tr>
              <th>
                <div class="permission-header">Permission</div>
              </th>
              <?php foreach ($roles as $role_key => $role_info): ?>
                <th>
                  <div class="role-header">
                    <div class="role-name" style="color: <?= $role_info['color'] ?>;">
                      <?= htmlspecialchars($role_info['label']) ?>
                    </div>
                    <div class="role-progress">
                      <div class="role-progress-fill" 
                           style="background-color: <?= $role_info['color'] ?>; width: <?= $role_stats[$role_key]['percentage'] ?>%;"></div>
                    </div>
                    <div class="role-count">
                      <?= $role_stats[$role_key]['count'] ?> / <?= $total_permissions ?>
                    </div>
                  </div>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($permission_categories as $category => $permissions): ?>
              <!-- Category Divider -->
              <tr class="category-divider" data-category="<?= strtolower(str_replace([' ', '&'], ['-', 'and'], $category)) ?>">
                <td colspan="<?= count($roles) + 1 ?>">
                  <?= strtoupper($category) ?>
                </td>
              </tr>
              
              <!-- Permission Rows -->
              <?php 
              $category_permissions = array_keys($permissions);
              $last_index = count($category_permissions) - 1;
              ?>
              <?php foreach ($permissions as $permission_key => $permission_label): ?>
                <?php $is_last = array_search($permission_key, $category_permissions) === $last_index; ?>
                <tr class="permission-row <?= $is_last ? 'category-last' : '' ?>" 
                    data-category="<?= strtolower(str_replace([' ', '&'], ['-', 'and'], $category)) ?>"
                    data-permission="<?= strtolower($permission_label) ?>">
                  <td class="permission-name">
                    <?= htmlspecialchars($permission_label) ?>
                  </td>
                  <?php foreach ($roles as $role_key => $role_info): ?>
                    <td class="permission-cell">
                      <?php if (isset($all_permissions[$permission_key]) && in_array($role_key, $all_permissions[$permission_key])): ?>
                        <div class="permission-indicator permission-granted">✓</div>
                      <?php else: ?>
                        <div class="permission-indicator permission-denied">—</div>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const filterPills = document.querySelectorAll('.filter-pill');
  const searchInput = document.getElementById('permission-search');
  const permissionRows = document.querySelectorAll('.permission-row');
  const categoryDividers = document.querySelectorAll('.category-divider');
  
  let activeCategory = 'all';
  let searchTerm = '';
  
  // Filter by category
  filterPills.forEach(pill => {
    pill.addEventListener('click', function() {
      // Update active pill
      filterPills.forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      
      activeCategory = this.dataset.category;
      applyFilters();
    });
  });
  
  // Search functionality
  searchInput.addEventListener('input', function() {
    searchTerm = this.value.toLowerCase();
    applyFilters();
  });
  
  function applyFilters() {
    const visibleCategories = new Set();
    
    // Filter permission rows
    permissionRows.forEach(row => {
      const category = row.dataset.category;
      const permission = row.dataset.permission;
      
      const categoryMatch = activeCategory === 'all' || category === activeCategory;
      const searchMatch = searchTerm === '' || permission.includes(searchTerm);
      
      if (categoryMatch && searchMatch) {
        row.classList.remove('hidden');
        visibleCategories.add(category);
      } else {
        row.classList.add('hidden');
      }
    });
    
    // Show/hide category dividers
    categoryDividers.forEach(divider => {
      const category = divider.dataset.category;
      if (visibleCategories.has(category)) {
        divider.classList.remove('hidden');
      } else {
        divider.classList.add('hidden');
      }
    });
  }
});
</script>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
