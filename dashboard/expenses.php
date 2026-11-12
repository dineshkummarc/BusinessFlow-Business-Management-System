<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Expenses';
$activePage = 'expenses';
$expenseModel = new Expense();
$projectModel = new Project();
$settings = new Settings();
$currency = $settings->get('currency_symbol', '$');
$log = new ActivityLog();
$canDelete = in_array($_SESSION['role'], ['admin', 'manager'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: expenses.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to delete expenses.');
            header('Location: expenses.php'); exit;
        }
        $id = (int) $_POST['id'];
        $expenseModel->delete($id);
        $log->record((int) $_SESSION['user_id'], 'expenses', "Deleted expense #$id");
        Flash::set('success', 'Expense deleted successfully.');
        header('Location: expenses.php'); exit;
    }

    $data = [
        'category'       => Validator::clean($_POST['category'] ?? ''),
        'description'    => Validator::clean($_POST['description'] ?? ''),
        'amount'         => $_POST['amount'] ?? 0,
        'expense_date'   => Validator::clean($_POST['expense_date'] ?? ''),
        'project_id'     => $_POST['project_id'] ?? '',
        'payment_method' => Validator::clean($_POST['payment_method'] ?? 'other'),
        'status'         => Validator::clean($_POST['status'] ?? 'pending'),
        'notes'          => Validator::clean($_POST['notes'] ?? ''),
    ];

    $v = new Validator();
    $v->required('category', $data['category'], 'Category')
      ->required('description', $data['description'], 'Description')
      ->required('expense_date', $data['expense_date'], 'Expense date')
      ->numeric('amount', $data['amount'], 'Amount')
      ->range('amount', $data['amount'], 0, 100000000, 'Amount');

    if ($v->fails()) {
        Flash::set('error', $v->firstError());
    } elseif ($formAction === 'create') {
        $expenseModel->create($data, (int) $_SESSION['user_id']);
        $log->record((int) $_SESSION['user_id'], 'expenses', "Logged expense: {$data['description']}");
        Flash::set('success', 'Expense recorded successfully.');
    } elseif ($formAction === 'update') {
        $id = (int) $_POST['id'];
        $expenseModel->update($id, $data);
        $log->record((int) $_SESSION['user_id'], 'expenses', "Updated expense: {$data['description']}");
        Flash::set('success', 'Expense updated successfully.');
    }
    header('Location: expenses.php'); exit;
}

$search = Validator::clean($_GET['q'] ?? '');
$categoryFilter = Validator::clean($_GET['category'] ?? '');
$statusFilter = Validator::clean($_GET['status'] ?? '');
$expenses = $expenseModel->all($search, $categoryFilter, $statusFilter);
$categories = $expenseModel->categories();
$projectsForDropdown = $projectModel->forDropdown();
$autoOpenAdd = isset($_GET['action']) && $_GET['action'] === 'add';

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Expenses</h2>
    <p class="text-muted mb-0">Track business spending — separate from client invoices, feeding directly into your profit &amp; loss report.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#expModal" onclick="resetExpForm()">
    <i class="bi bi-plus-lg me-1"></i> Log Expense
  </button>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $expenseModel->count() ?></div><div class="stat-label">Total Expenses</div></div></div>
  <div class="col-md-4"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $currency ?><?= number_format($expenseModel->totalApproved(),0) ?></div><div class="stat-label">Approved Spend</div></div></div>
  <div class="col-md-4"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $currency ?><?= number_format($expenseModel->totalPending(),0) ?></div><div class="stat-label">Pending Approval</div></div></div>
</div>

<div class="data-card">
  <div class="data-card-head">
    <form class="d-flex gap-2 flex-wrap" method="GET">
      <input type="text" name="q" class="form-control form-control-bms form-control-sm" style="width:200px;" placeholder="Search expenses..." value="<?= htmlspecialchars($search) ?>">
      <select name="category" class="form-select form-select-sm" style="width:160px;" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>" <?= $categoryFilter===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
      </select>
      <select name="status" class="form-select form-select-sm" style="width:150px;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Pending</option>
        <option value="approved" <?= $statusFilter==='approved'?'selected':'' ?>>Approved</option>
        <option value="rejected" <?= $statusFilter==='rejected'?'selected':'' ?>>Rejected</option>
      </select>
      <button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
      <?php if ($search || $categoryFilter || $statusFilter): ?><a href="expenses.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-bms">
      <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Project</th><th>Amount</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (!$expenses): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">No expenses recorded yet. Click "Log Expense" to add one.</td></tr>
      <?php endif; ?>
      <?php foreach ($expenses as $ex): ?>
        <tr>
          <td><?= date('M j, Y', strtotime($ex['expense_date'])) ?></td>
          <td><span class="badge-soft badge-soft-blue"><?= htmlspecialchars($ex['category']) ?></span></td>
          <td><?= htmlspecialchars($ex['description']) ?></td>
          <td><?= htmlspecialchars($ex['project_name'] ?? '—') ?></td>
          <td class="fw-semibold"><?= $currency ?><?= number_format($ex['amount'], 2) ?></td>
          <td><?php $p = $ex; include __DIR__ . '/../includes/badge_status.php'; ?></td>
          <td class="text-end">
            <button class="btn btn-sm btn-light" title="Edit" onclick='openEditExp(<?= json_encode($ex, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="bi bi-pencil"></i></button>
            <?php if ($canDelete): ?>
              <form method="POST" class="d-inline confirm-delete" data-label="this expense">
                <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                <input type="hidden" name="form_action" value="delete">
                <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                <button class="btn btn-sm btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="expModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="expForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" id="expFormAction" value="create">
        <input type="hidden" name="id" id="expId" value="">
        <div class="modal-header"><h5 class="modal-title" id="expModalTitle">Log Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Category *</label>
              <input type="text" name="category" id="ex_category" class="form-control form-control-bms" list="catList" required>
              <datalist id="catList"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist>
            </div>
            <div class="col-md-6">
              <label class="form-label">Amount (<?= $currency ?>) *</label>
              <input type="number" step="0.01" min="0" name="amount" id="ex_amount" class="form-control form-control-bms" required>
            </div>
            <div class="col-12">
              <label class="form-label">Description *</label>
              <input type="text" name="description" id="ex_description" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Expense Date *</label>
              <input type="date" name="expense_date" id="ex_date" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Related Project</label>
              <select name="project_id" id="ex_project" class="form-select form-control-bms">
                <option value="">— None —</option>
                <?php foreach ($projectsForDropdown as $pj): ?><option value="<?= $pj['id'] ?>"><?= htmlspecialchars($pj['project_name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Payment Method</label>
              <select name="payment_method" id="ex_method" class="form-select form-control-bms">
                <option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="card">Card</option><option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" id="ex_status" class="form-select form-control-bms">
                <option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" id="ex_notes" rows="2" class="form-control form-control-bms"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-gold">Save Expense</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function resetExpForm() {
  document.getElementById('expForm').reset();
  document.getElementById('expFormAction').value = 'create';
  document.getElementById('expId').value = '';
  document.getElementById('ex_date').value = new Date().toISOString().slice(0,10);
  document.getElementById('expModalTitle').textContent = 'Log Expense';
}
function openEditExp(ex) {
  document.getElementById('expFormAction').value = 'update';
  document.getElementById('expId').value = ex.id;
  document.getElementById('ex_category').value = ex.category || '';
  document.getElementById('ex_amount').value = ex.amount || 0;
  document.getElementById('ex_description').value = ex.description || '';
  document.getElementById('ex_date').value = ex.expense_date || '';
  document.getElementById('ex_project').value = ex.project_id || '';
  document.getElementById('ex_method').value = ex.payment_method || 'other';
  document.getElementById('ex_status').value = ex.status || 'pending';
  document.getElementById('ex_notes').value = ex.notes || '';
  document.getElementById('expModalTitle').textContent = 'Edit Expense';
  new bootstrap.Modal(document.getElementById('expModal')).show();
}
<?php if ($autoOpenAdd): ?>
document.addEventListener('DOMContentLoaded', function () { resetExpForm(); new bootstrap.Modal(document.getElementById('expModal')).show(); });
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
