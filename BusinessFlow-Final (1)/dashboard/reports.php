<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Reports';
$activePage = 'reports';
$db = Database::getInstance();
$settings = new Settings();
$expenseModel = new Expense();
$currency = $settings->get('currency_symbol', '$');
new Invoice(); // Phase 3: trigger the overdue-status auto-check before running raw invoice queries below.

// Profit & Loss — revenue (paid invoices) vs approved expenses
$totalRevenue = (float) ($db->selectOne("SELECT SUM(total_amount) AS s FROM invoices WHERE status = 'paid'")['s'] ?? 0);
$totalExpenses = $expenseModel->totalApproved();
$netProfit = $totalRevenue - $totalExpenses;

// Expenses by category
$expensesByCategory = $db->select(
    "SELECT category, SUM(amount) AS total FROM expenses WHERE status = 'approved' GROUP BY category ORDER BY total DESC"
);

// Revenue by month (last 6 months) from paid invoices
$revenueByMonth = $db->select(
    "SELECT DATE_FORMAT(issue_date, '%Y-%m') AS ym, SUM(total_amount) AS total
     FROM invoices WHERE status = 'paid' AND issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY ym ORDER BY ym ASC"
);

// Projects by status
$projectsByStatus = $db->select(
    "SELECT status, COUNT(*) AS c FROM projects GROUP BY status"
);

// Clients by industry
$clientsByIndustry = $db->select(
    "SELECT COALESCE(NULLIF(industry,''),'Unspecified') AS industry, COUNT(*) AS c FROM clients GROUP BY industry ORDER BY c DESC LIMIT 6"
);

// Invoice status breakdown
$invoicesByStatus = $db->select(
    "SELECT status, COUNT(*) AS c, SUM(total_amount) AS total FROM invoices GROUP BY status"
);

// Top clients by revenue
$topClients = $db->select(
    "SELECT c.company_name, SUM(i.total_amount) AS total
     FROM invoices i JOIN clients c ON c.id = i.client_id
     WHERE i.status = 'paid'
     GROUP BY c.id ORDER BY total DESC LIMIT 5"
);

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Reports</h2>
    <p class="text-muted mb-0">Business performance at a glance, generated live from your database.</p>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e5f7ea;color:#1a8a44;"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="stat-value"><?= $currency ?><?= number_format($totalRevenue,0) ?></div>
      <div class="stat-label">Revenue (Paid Invoices)</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fde8e8;color:#c0392b;"><i class="bi bi-graph-down-arrow"></i></div>
      <div class="stat-value"><?= $currency ?><?= number_format($totalExpenses,0) ?></div>
      <div class="stat-label">Approved Expenses</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background:<?= $netProfit>=0?'#e6f0fb;color:#1d5fb8':'#fde8e8;color:#c0392b' ?>;"><i class="bi bi-cash-coin"></i></div>
      <div class="stat-value"><?= $currency ?><?= number_format($netProfit,0) ?></div>
      <div class="stat-label">Net Profit / Loss</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="data-card mb-3">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Projects by Status</h6></div>
      <div class="p-3">
        <?php if (!$projectsByStatus): ?><p class="text-muted small mb-0">No project data yet.</p><?php endif; ?>
        <?php
          $maxC = max(array_map(fn($r) => (int)$r['c'], $projectsByStatus ?: [['c'=>1]]));
          foreach ($projectsByStatus as $row):
            $pct = $maxC ? round(($row['c'] / $maxC) * 100) : 0;
        ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between small mb-1">
              <span class="text-capitalize"><?= str_replace('_',' ',$row['status']) ?></span>
              <span class="fw-semibold"><?= $row['c'] ?></span>
            </div>
            <div class="progress-bms"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="data-card">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Clients by Industry</h6></div>
      <div class="p-3">
        <?php if (!$clientsByIndustry): ?><p class="text-muted small mb-0">No client data yet.</p><?php endif; ?>
        <?php foreach ($clientsByIndustry as $row): ?>
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <span><?= htmlspecialchars($row['industry']) ?></span>
            <span class="badge-soft badge-soft-blue"><?= $row['c'] ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="data-card mt-3">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Expenses by Category</h6></div>
      <div class="p-3">
        <?php if (!$expensesByCategory): ?><p class="text-muted small mb-0">No approved expenses yet.</p><?php endif; ?>
        <?php
          $maxExp = $expensesByCategory ? max(array_map(fn($r) => (float)$r['total'], $expensesByCategory)) : 1;
          foreach ($expensesByCategory as $row):
            $pct = $maxExp ? round(($row['total'] / $maxExp) * 100) : 0;
        ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between small mb-1">
              <span><?= htmlspecialchars($row['category']) ?></span>
              <span class="fw-semibold"><?= $currency ?><?= number_format($row['total'],0) ?></span>
            </div>
            <div class="progress-bms"><div class="progress-bar" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#c0392b,#0f2a4a);"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="data-card mb-3">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Invoice Status Breakdown</h6></div>
      <div class="table-responsive">
        <table class="table table-bms">
          <thead><tr><th>Status</th><th>Count</th><th class="text-end">Total Value</th></tr></thead>
          <tbody>
          <?php if (!$invoicesByStatus): ?><tr><td colspan="3" class="text-center text-muted py-4">No invoice data yet.</td></tr><?php endif; ?>
          <?php foreach ($invoicesByStatus as $row): ?>
            <tr>
              <td><?php $p = ['status' => $row['status']]; include __DIR__ . '/../includes/badge_status.php'; ?></td>
              <td><?= $row['c'] ?></td>
              <td class="text-end fw-semibold"><?= $currency ?><?= number_format($row['total'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="data-card">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Top Clients by Revenue</h6></div>
      <div class="table-responsive">
        <table class="table table-bms">
          <thead><tr><th>Client</th><th class="text-end">Revenue Paid</th></tr></thead>
          <tbody>
          <?php if (!$topClients): ?><tr><td colspan="2" class="text-center text-muted py-4">No paid invoices yet.</td></tr><?php endif; ?>
          <?php foreach ($topClients as $row): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($row['company_name']) ?></td>
              <td class="text-end"><?= $currency ?><?= number_format($row['total'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="data-card mt-3">
  <div class="data-card-head"><h6 class="mb-0 fw-semibold">Revenue Trend (Last 6 Months, Paid Invoices)</h6></div>
  <div class="p-3">
    <?php if (!$revenueByMonth): ?>
      <p class="text-muted small mb-0">No revenue recorded in the last 6 months yet.</p>
    <?php else: ?>
      <div class="d-flex align-items-end gap-3" style="height:160px;">
        <?php
          $maxRev = max(array_map(fn($r) => (float)$r['total'], $revenueByMonth));
          foreach ($revenueByMonth as $row):
            $h = $maxRev ? max(6, round(($row['total'] / $maxRev) * 140)) : 6;
        ?>
          <div class="text-center flex-fill">
            <div style="height:<?= $h ?>px;background:linear-gradient(180deg,#c9a15a,#0f2a4a);border-radius:6px 6px 0 0;" title="<?= $currency ?><?= number_format($row['total'],2) ?>"></div>
            <small class="text-muted d-block mt-1"><?= date('M Y', strtotime($row['ym'] . '-01')) ?></small>
            <small class="fw-semibold"><?= $currency ?><?= number_format($row['total'],0) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
