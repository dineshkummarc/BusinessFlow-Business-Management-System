<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Dashboard Overview';
$activePage = 'index';

$clientModel   = new Client();
$projectModel  = new Project();
$invoiceModel  = new Invoice();
$employeeModel = new Employee();
$taskModel     = new Task();
$expenseModel  = new Expense();
$settings      = new Settings();
$currency      = $settings->get('currency_symbol', '$');
$logModel      = new ActivityLog();
$contactModel  = new ContactMessage();
$unreadMessages = $contactModel->countUnread();

$stats = [
    'clients'      => $clientModel->count(),
    'active_clients' => $clientModel->countByStatus('active'),
    'projects'     => $projectModel->count(),
    'active_projects' => $projectModel->countByStatus('in_progress'),
    'invoices'     => $invoiceModel->count(),
    'revenue'      => $invoiceModel->totalRevenue(),
    'outstanding'  => $invoiceModel->totalOutstanding(),
    'employees'    => $employeeModel->count(),
    'tasks_open'   => $taskModel->countByStatus('todo') + $taskModel->countByStatus('in_progress') + $taskModel->countByStatus('review'),
    'tasks_overdue' => $taskModel->countOverdue(),
    'expenses_pending' => $expenseModel->totalPending(),
];

$recentProjects = array_slice($projectModel->all(), 0, 5);
$recentInvoices = array_slice($invoiceModel->all(), 0, 5);
$recentActivity = $logModel->recent(6);

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?> 👋</h2>
    <p class="text-muted mb-0">Here's what's happening across your business today.</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="clients.php?action=add" class="btn btn-navy btn-sm"><i class="bi bi-plus-lg me-1"></i>New Client</a>
    <a href="tasks.php" class="btn btn-outline-navy btn-sm"><i class="bi bi-plus-lg me-1"></i>New Task</a>
    <a href="invoices.php?action=add" class="btn btn-gold btn-sm"><i class="bi bi-plus-lg me-1"></i>New Invoice</a>
  </div>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e6f0fb;color:#1d5fb8;"><i class="bi bi-people-fill"></i></div>
      <div class="stat-value"><?= $stats['clients'] ?></div>
      <div class="stat-label">Total Clients</div>
      <div class="stat-trend text-success mt-2"><?= $stats['active_clients'] ?> active</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fbf1de;color:#a97a1f;"><i class="bi bi-kanban-fill"></i></div>
      <div class="stat-value"><?= $stats['projects'] ?></div>
      <div class="stat-label">Total Projects</div>
      <div class="stat-trend text-success mt-2"><?= $stats['active_projects'] ?> in progress</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e5f7ea;color:#1a8a44;"><i class="bi bi-cash-stack"></i></div>
      <div class="stat-value"><?= $currency ?><?= number_format($stats['revenue'], 0) ?></div>
      <div class="stat-label">Revenue Collected</div>
      <div class="stat-trend text-danger mt-2"><?= $currency ?><?= number_format($stats['outstanding'], 0) ?> outstanding</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fde8e8;color:#c0392b;"><i class="bi bi-person-workspace"></i></div>
      <div class="stat-value"><?= $stats['employees'] ?></div>
      <div class="stat-label">Team Members</div>
      <div class="stat-trend text-muted mt-2"><?= $stats['invoices'] ?> invoices issued</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#f1e8fb;color:#6f42c1;"><i class="bi bi-check2-square"></i></div>
      <div class="stat-value"><?= $stats['tasks_open'] ?></div>
      <div class="stat-label">Open Tasks</div>
      <div class="stat-trend <?= $stats['tasks_overdue'] > 0 ? 'text-danger' : 'text-success' ?> mt-2"><?= $stats['tasks_overdue'] ?> overdue</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fbf1de;color:#a97a1f;"><i class="bi bi-wallet2"></i></div>
      <div class="stat-value"><?= $currency ?><?= number_format($stats['expenses_pending'], 0) ?></div>
      <div class="stat-label">Expenses Pending Approval</div>
      <div class="stat-trend text-muted mt-2"><a href="expenses.php" class="text-decoration-none text-muted">Review expenses <i class="bi bi-arrow-right"></i></a></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Recent Projects -->
  <div class="col-lg-7">
    <div class="data-card mb-3">
      <div class="data-card-head">
        <h6 class="mb-0 fw-semibold">Recent Projects</h6>
        <a href="projects.php" class="small text-gold fw-semibold">View all <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="table-responsive">
        <table class="table table-bms">
          <thead><tr><th>Project</th><th>Client</th><th>Progress</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$recentProjects): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No projects yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentProjects as $p): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($p['project_name']) ?></td>
              <td><?= htmlspecialchars($p['company_name'] ?? '—') ?></td>
              <td style="width:140px;">
                <div class="progress-bms"><div class="progress-bar" style="width:<?= (int)$p['progress'] ?>%"></div></div>
                <small class="text-muted"><?= (int)$p['progress'] ?>%</small>
              </td>
              <td><?php include __DIR__ . '/../includes/badge_status.php'; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="data-card">
      <div class="data-card-head">
        <h6 class="mb-0 fw-semibold">Recent Invoices</h6>
        <a href="invoices.php" class="small text-gold fw-semibold">View all <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="table-responsive">
        <table class="table table-bms">
          <thead><tr><th>Invoice #</th><th>Client</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$recentInvoices): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No invoices yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentInvoices as $inv): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($inv['invoice_number']) ?></td>
              <td><?= htmlspecialchars($inv['company_name']) ?></td>
              <td><?= $currency ?><?= number_format($inv['total_amount'], 2) ?></td>
              <td><?php $p = $inv; include __DIR__ . '/../includes/badge_status.php'; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Activity feed -->
  <div class="col-lg-5">
    <div class="data-card h-100">
      <div class="data-card-head">
        <h6 class="mb-0 fw-semibold">Recent Activity</h6>
      </div>
      <div class="p-3">
        <?php if (!$recentActivity): ?>
          <p class="text-muted text-center py-4 mb-0">No activity recorded yet.</p>
        <?php endif; ?>
        <?php foreach ($recentActivity as $a): ?>
          <div class="d-flex gap-3 mb-3">
            <div class="avatar-sm" style="width:34px;height:34px;font-size:0.75rem;"><?= strtoupper(substr($a['full_name'] ?? 'S', 0, 1)) ?></div>
            <div>
              <div class="small"><strong><?= htmlspecialchars($a['full_name'] ?? 'System') ?></strong> <?= htmlspecialchars($a['action']) ?></div>
              <div class="text-muted" style="font-size:0.75rem;"><?= date('M j, g:ia', strtotime($a['created_at'])) ?> · <span class="text-capitalize"><?= htmlspecialchars($a['module']) ?></span></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
