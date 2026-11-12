<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Clients';
$activePage = 'clients';
$clientModel = new Client();
$log = new ActivityLog();
$canDelete = in_array($_SESSION['role'], ['admin', 'manager'], true);

// -------- Handle Create / Update / Delete --------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: clients.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to delete clients.');
            header('Location: clients.php'); exit;
        }
        $id = (int) $_POST['id'];
        $related = $clientModel->relatedRecordCounts($id);
        if ($related['invoices'] > 0) {
            Flash::set('error', "This client has {$related['invoices']} invoice(s) on record. Delete or reassign those invoices before removing the client, to avoid losing financial history.");
            header('Location: clients.php'); exit;
        }
        $clientModel->delete($id);
        $log->record((int) $_SESSION['user_id'], 'clients', "Deleted client #$id");
        $msg = 'Client deleted successfully.';
        if ($related['projects'] > 0) {
            $msg .= " {$related['projects']} related project(s) are now unlinked from this client, not deleted.";
        }
        Flash::set('success', $msg);
        header('Location: clients.php'); exit;
    }

    $data = [
        'company_name'   => Validator::clean($_POST['company_name'] ?? ''),
        'contact_person' => Validator::clean($_POST['contact_person'] ?? ''),
        'email'          => Validator::clean($_POST['email'] ?? ''),
        'phone'          => Validator::clean($_POST['phone'] ?? ''),
        'address'        => Validator::clean($_POST['address'] ?? ''),
        'city'           => Validator::clean($_POST['city'] ?? ''),
        'country'        => Validator::clean($_POST['country'] ?? ''),
        'industry'       => Validator::clean($_POST['industry'] ?? ''),
        'status'         => Validator::clean($_POST['status'] ?? 'lead'),
        'notes'          => Validator::clean($_POST['notes'] ?? ''),
    ];

    $v = new Validator();
    $v->required('company_name', $data['company_name'], 'Company name')
      ->required('contact_person', $data['contact_person'], 'Contact person')
      ->required('email', $data['email'], 'Email')
      ->email('email', $data['email'], 'Email');

    if ($v->fails()) {
        Flash::set('error', $v->firstError());
    } elseif ($formAction === 'create') {
        $newId = $clientModel->create($data, (int) $_SESSION['user_id']);
        $log->record((int) $_SESSION['user_id'], 'clients', "Added new client: {$data['company_name']}");
        Flash::set('success', 'Client added successfully.');
    } elseif ($formAction === 'update') {
        $id = (int) $_POST['id'];
        $clientModel->update($id, $data);
        $log->record((int) $_SESSION['user_id'], 'clients', "Updated client: {$data['company_name']}");
        Flash::set('success', 'Client updated successfully.');
    }
    header('Location: clients.php'); exit;
}

// -------- List with filters --------
$search = Validator::clean($_GET['q'] ?? '');
$statusFilter = Validator::clean($_GET['status'] ?? '');
$clients = $clientModel->all($search, $statusFilter);
$editClient = null;
if (isset($_GET['edit'])) {
    $editClient = $clientModel->find((int) $_GET['edit']);
}
$autoOpenAdd = isset($_GET['action']) && $_GET['action'] === 'add';

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Clients</h2>
    <p class="text-muted mb-0">Manage your leads and active client accounts.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#clientModal" onclick="resetClientForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Client
  </button>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $clientModel->count() ?></div><div class="stat-label">Total Clients</div></div></div>
  <div class="col-md-3"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $clientModel->countByStatus('lead') ?></div><div class="stat-label">Leads</div></div></div>
  <div class="col-md-3"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $clientModel->countByStatus('active') ?></div><div class="stat-label">Active</div></div></div>
  <div class="col-md-3"><div class="stat-card py-3"><div class="stat-value fs-4"><?= $clientModel->countByStatus('inactive') ?></div><div class="stat-label">Inactive</div></div></div>
</div>

<div class="data-card">
  <div class="data-card-head">
    <form class="d-flex gap-2 flex-wrap" method="GET">
      <input type="text" name="q" class="form-control form-control-bms form-control-sm" style="width:220px;" placeholder="Search clients..." value="<?= htmlspecialchars($search) ?>">
      <select name="status" class="form-select form-select-sm" style="width:150px;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="lead" <?= $statusFilter === 'lead' ? 'selected' : '' ?>>Lead</option>
        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
      </select>
      <button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
      <?php if ($search || $statusFilter): ?><a href="clients.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-bms">
      <thead><tr><th>Company</th><th>Contact</th><th>Email</th><th>Industry</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (!$clients): ?>
        <tr><td colspan="6" class="text-center text-muted py-5">No clients found. Click "Add Client" to create one.</td></tr>
      <?php endif; ?>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($c['company_name']) ?></td>
          <td><?= htmlspecialchars($c['contact_person']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['industry'] ?: '—') ?></td>
          <td><?php $p = $c; include __DIR__ . '/../includes/badge_status.php'; ?></td>
          <td class="text-end">
            <button class="btn btn-sm btn-light" title="Edit"
              onclick='openEditClient(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              <i class="bi bi-pencil"></i>
            </button>
            <?php if ($canDelete): ?>
            <form method="POST" class="d-inline confirm-delete" data-label="this client">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <input type="hidden" name="form_action" value="delete">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
<div class="modal fade" id="clientModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="clientForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" id="clientFormAction" value="create">
        <input type="hidden" name="id" id="clientId" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="clientModalTitle">Add New Client</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Company Name *</label>
              <input type="text" name="company_name" id="f_company_name" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Person *</label>
              <input type="text" name="contact_person" id="f_contact_person" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input type="email" name="email" id="f_email" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" id="f_phone" class="form-control form-control-bms">
            </div>
            <div class="col-md-4">
              <label class="form-label">City</label>
              <input type="text" name="city" id="f_city" class="form-control form-control-bms">
            </div>
            <div class="col-md-4">
              <label class="form-label">Country</label>
              <input type="text" name="country" id="f_country" class="form-control form-control-bms">
            </div>
            <div class="col-md-4">
              <label class="form-label">Industry</label>
              <input type="text" name="industry" id="f_industry" class="form-control form-control-bms">
            </div>
            <div class="col-md-6">
              <label class="form-label">Address</label>
              <input type="text" name="address" id="f_address" class="form-control form-control-bms">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" id="f_status" class="form-select form-control-bms">
                <option value="lead">Lead</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" id="f_notes" rows="3" class="form-control form-control-bms"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-gold">Save Client</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetClientForm() {
  document.getElementById('clientForm').reset();
  document.getElementById('clientFormAction').value = 'create';
  document.getElementById('clientId').value = '';
  document.getElementById('clientModalTitle').textContent = 'Add New Client';
}
function openEditClient(c) {
  document.getElementById('clientFormAction').value = 'update';
  document.getElementById('clientId').value = c.id;
  document.getElementById('f_company_name').value = c.company_name || '';
  document.getElementById('f_contact_person').value = c.contact_person || '';
  document.getElementById('f_email').value = c.email || '';
  document.getElementById('f_phone').value = c.phone || '';
  document.getElementById('f_city').value = c.city || '';
  document.getElementById('f_country').value = c.country || '';
  document.getElementById('f_industry').value = c.industry || '';
  document.getElementById('f_address').value = c.address || '';
  document.getElementById('f_status').value = c.status || 'lead';
  document.getElementById('f_notes').value = c.notes || '';
  document.getElementById('clientModalTitle').textContent = 'Edit Client';
  new bootstrap.Modal(document.getElementById('clientModal')).show();
}
<?php if ($autoOpenAdd): ?>
document.addEventListener('DOMContentLoaded', function () {
  resetClientForm();
  new bootstrap.Modal(document.getElementById('clientModal')).show();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
