<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Invoices';
$activePage = 'invoices';
$invoiceModel = new Invoice();
$clientModel = new Client();
$projectModel = new Project();
$settings = new Settings();
$currency = $settings->get('currency_symbol', '$');
$defaultTax = $settings->get('default_tax_percent', '0');
$log = new ActivityLog();
$canDelete = in_array($_SESSION['role'], ['admin', 'manager'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: invoices.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to delete invoices.');
            header('Location: invoices.php'); exit;
        }
        $id = (int) $_POST['id'];
        $invoiceModel->delete($id);
        $log->record((int) $_SESSION['user_id'], 'invoices', "Deleted invoice #$id");
        Flash::set('success', 'Invoice deleted successfully.');
        header('Location: invoices.php'); exit;
    }

    if ($formAction === 'update_status') {
        $id = (int) $_POST['id'];
        $invoiceModel->updateStatus($id, Validator::clean($_POST['status'] ?? 'draft'));
        $log->record((int) $_SESSION['user_id'], 'invoices', "Updated invoice #$id status");
        Flash::set('success', 'Invoice status updated.');
        header('Location: invoices.php'); exit;
    }

    if ($formAction === 'create') {
        $data = [
            'client_id'   => $_POST['client_id'] ?? '',
            'project_id'  => $_POST['project_id'] ?? '',
            'issue_date'  => Validator::clean($_POST['issue_date'] ?? ''),
            'due_date'    => Validator::clean($_POST['due_date'] ?? ''),
            'tax_percent' => $_POST['tax_percent'] ?? 0,
            'status'      => Validator::clean($_POST['status'] ?? 'draft'),
            'notes'       => Validator::clean($_POST['notes'] ?? ''),
        ];
        $items = [];
        foreach (($_POST['item_desc'] ?? []) as $i => $desc) {
            $items[] = [
                'description' => Validator::clean($desc),
                'quantity'    => $_POST['item_qty'][$i] ?? 1,
                'unit_price'  => $_POST['item_price'][$i] ?? 0,
            ];
        }

        $v = new Validator();
        $v->required('client_id', $data['client_id'], 'Client')
          ->required('issue_date', $data['issue_date'], 'Issue date')
          ->required('due_date', $data['due_date'], 'Due date')
          ->dateOnOrAfter('due_date', $data['due_date'], $data['issue_date'], 'Due date', 'the issue date')
          ->numeric('tax_percent', $data['tax_percent'], 'Tax percent')
          ->range('tax_percent', $data['tax_percent'], 0, 100, 'Tax percent');

        if ($v->fails()) {
            Flash::set('error', $v->firstError());
        } else {
            $invoiceModel->create($data, $items, (int) $_SESSION['user_id']);
            $log->record((int) $_SESSION['user_id'], 'invoices', 'Created new invoice');
            Flash::set('success', 'Invoice created successfully.');
        }
    }
    header('Location: invoices.php'); exit;
}

$search = Validator::clean($_GET['q'] ?? '');
$statusFilter = Validator::clean($_GET['status'] ?? '');
$invoices = $invoiceModel->all($search, $statusFilter);
$clientsForDropdown = $clientModel->forDropdown();
$projectsForDropdown = $projectModel->forDropdown();
$autoOpenAdd = isset($_GET['action']) && $_GET['action'] === 'add';

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Invoices</h2>
    <p class="text-muted mb-0">Create and track invoices for your clients.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#invModal">
    <i class="bi bi-plus-lg me-1"></i> New Invoice
  </button>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $invoiceModel->count() ?></div><div class="stat-label">Total Invoices</div></div></div>
  <div class="col-md-4"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $currency ?><?= number_format($invoiceModel->totalRevenue(),0) ?></div><div class="stat-label">Revenue Collected</div></div></div>
  <div class="col-md-4"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $currency ?><?= number_format($invoiceModel->totalOutstanding(),0) ?></div><div class="stat-label">Outstanding</div></div></div>
</div>

<div class="data-card">
  <div class="data-card-head">
    <form class="d-flex gap-2 flex-wrap" method="GET">
      <input type="text" name="q" class="form-control form-control-bms form-control-sm" style="width:220px;" placeholder="Search invoices..." value="<?= htmlspecialchars($search) ?>">
      <select name="status" class="form-select form-select-sm" style="width:150px;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach (['draft','sent','paid','overdue','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
      <?php if ($search || $statusFilter): ?><a href="invoices.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-bms">
      <thead><tr><th>Invoice #</th><th>Client</th><th>Issue Date</th><th>Due Date</th><th>Amount</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (!$invoices): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">No invoices found. Click "New Invoice" to create one.</td></tr>
      <?php endif; ?>
      <?php foreach ($invoices as $inv): ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($inv['invoice_number']) ?></td>
          <td><?= htmlspecialchars($inv['company_name']) ?></td>
          <td><?= date('M j, Y', strtotime($inv['issue_date'])) ?></td>
          <td><?= date('M j, Y', strtotime($inv['due_date'])) ?></td>
          <td class="fw-semibold"><?= $currency ?><?= number_format($inv['total_amount'], 2) ?></td>
          <td style="width:150px;">
            <form method="POST">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <input type="hidden" name="form_action" value="update_status">
              <input type="hidden" name="id" value="<?= $inv['id'] ?>">
              <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach (['draft','sent','paid','overdue','cancelled'] as $s): ?>
                  <option value="<?= $s ?>" <?= $inv['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td class="text-end">
            <a href="invoice_view.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
            <?php if ($canDelete): ?>
            <form method="POST" class="d-inline confirm-delete" data-label="this invoice">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <input type="hidden" name="form_action" value="delete">
              <input type="hidden" name="id" value="<?= $inv['id'] ?>">
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

<!-- New Invoice Modal -->
<div class="modal fade" id="invModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" value="create">
        <div class="modal-header"><h5 class="modal-title">New Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Client *</label>
              <select name="client_id" class="form-select form-control-bms" required>
                <option value="">Select client...</option>
                <?php foreach ($clientsForDropdown as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Related Project</label>
              <select name="project_id" class="form-select form-control-bms">
                <option value="">— None —</option>
                <?php foreach ($projectsForDropdown as $pj): ?><option value="<?= $pj['id'] ?>"><?= htmlspecialchars($pj['project_name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Issue Date *</label>
              <input type="date" name="issue_date" class="form-control form-control-bms" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Due Date *</label>
              <input type="date" name="due_date" class="form-control form-control-bms" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select form-control-bms">
                <option value="draft">Draft</option><option value="sent">Sent</option><option value="paid">Paid</option>
              </select>
            </div>
          </div>

          <label class="form-label">Line Items</label>
          <div id="invoiceItems">
            <div class="row g-2 mb-2 invoice-item-row align-items-center">
              <div class="col-5"><input type="text" name="item_desc[]" class="form-control form-control-sm item-desc" placeholder="Description" required></div>
              <div class="col-2"><input type="number" name="item_qty[]" class="form-control form-control-sm item-qty" value="1" min="0.01" step="0.01"></div>
              <div class="col-2"><input type="number" name="item_price[]" class="form-control form-control-sm item-price" value="0" min="0" step="0.01"></div>
              <div class="col-2 text-end small text-muted"><?= $currency ?><span class="item-line-total">0.00</span></div>
              <div class="col-1"><button type="button" class="btn btn-sm btn-light text-danger remove-item-row"><i class="bi bi-x"></i></button></div>
            </div>
            <button type="button" id="addInvoiceItem" class="btn btn-sm btn-outline-navy mt-1"><i class="bi bi-plus"></i> Add Line</button>
          </div>

          <div class="row justify-content-end mt-3">
            <div class="col-md-5">
              <div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span><?= $currency ?><span id="invoiceSubtotal">0.00</span></span></div>
              <div class="d-flex justify-content-between small mb-1 align-items-center">
                <span>Tax (%)</span>
                <input type="number" id="taxPercent" name="tax_percent" value="<?= htmlspecialchars($defaultTax) ?>" min="0" max="100" step="0.01" class="form-control form-control-sm" style="width:80px;">
              </div>
              <div class="d-flex justify-content-between small mb-1"><span>Tax Amount</span><span><?= $currency ?><span id="invoiceTax">0.00</span></span></div>
              <hr>
              <div class="d-flex justify-content-between fw-bold"><span>Total</span><span><?= $currency ?><span id="invoiceTotal">0.00</span></span></div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-control form-control-bms"></textarea>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-gold">Create Invoice</button></div>
      </form>
    </div>
  </div>
</div>

<?php if ($autoOpenAdd): ?>
<script>document.addEventListener('DOMContentLoaded', function(){ new bootstrap.Modal(document.getElementById('invModal')).show(); });</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
