<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'HR & Staff';
$activePage = 'employees';
$employeeModel = new Employee();
$log = new ActivityLog();
$canDelete = in_array($_SESSION['role'], ['admin', 'manager'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: employees.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to delete employee records.');
            header('Location: employees.php'); exit;
        }
        $id = (int) $_POST['id'];
        $employeeModel->delete($id);
        $log->record((int) $_SESSION['user_id'], 'employees', "Deleted employee #$id");
        Flash::set('success', 'Employee removed successfully.');
        header('Location: employees.php'); exit;
    }

    $data = [
        'full_name'   => Validator::clean($_POST['full_name'] ?? ''),
        'email'       => Validator::clean($_POST['email'] ?? ''),
        'phone'       => Validator::clean($_POST['phone'] ?? ''),
        'department'  => Validator::clean($_POST['department'] ?? ''),
        'designation' => Validator::clean($_POST['designation'] ?? ''),
        'salary'      => $_POST['salary'] ?? 0,
        'hire_date'   => Validator::clean($_POST['hire_date'] ?? ''),
        'status'      => Validator::clean($_POST['status'] ?? 'active'),
        'user_id'     => $_POST['user_id'] ?? '',
    ];

    $v = new Validator();
    $v->required('full_name', $data['full_name'], 'Full name')
      ->required('email', $data['email'], 'Email')
      ->email('email', $data['email'], 'Email')
      ->required('department', $data['department'], 'Department')
      ->required('designation', $data['designation'], 'Designation')
      ->required('hire_date', $data['hire_date'], 'Hire date')
      ->numeric('salary', $data['salary'], 'Salary');

    if ($v->fails()) {
        Flash::set('error', $v->firstError());
    } elseif ($formAction === 'create') {
        $employeeModel->create($data);
        $log->record((int) $_SESSION['user_id'], 'employees', "Added employee: {$data['full_name']}");
        Flash::set('success', 'Employee added successfully.');
    } elseif ($formAction === 'update') {
        $id = (int) $_POST['id'];
        $employeeModel->update($id, $data);
        $log->record((int) $_SESSION['user_id'], 'employees', "Updated employee: {$data['full_name']}");
        Flash::set('success', 'Employee updated successfully.');
    }
    header('Location: employees.php'); exit;
}

$search = Validator::clean($_GET['q'] ?? '');
$deptFilter = Validator::clean($_GET['department'] ?? '');
$employees = $employeeModel->all($search, $deptFilter);
$departments = $employeeModel->departments();
$unlinkedUsers = $employeeModel->unlinkedUsers();
$autoOpenAdd = isset($_GET['action']) && $_GET['action'] === 'add';

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>HR &amp; Staff</h2>
    <p class="text-muted mb-0">Manage your company's employee directory.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#empModal" onclick="resetEmpForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Employee
  </button>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="data-card">
  <div class="data-card-head">
    <form class="d-flex gap-2 flex-wrap" method="GET">
      <input type="text" name="q" class="form-control form-control-bms form-control-sm" style="width:220px;" placeholder="Search employees..." value="<?= htmlspecialchars($search) ?>">
      <select name="department" class="form-select form-select-sm" style="width:170px;" onchange="this.form.submit()">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= htmlspecialchars($d) ?>" <?= $deptFilter === $d ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
      <?php if ($search || $deptFilter): ?><a href="employees.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-bms">
      <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Designation</th><th>Hire Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (!$employees): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">No employees found. Click "Add Employee" to create one.</td></tr>
      <?php endif; ?>
      <?php foreach ($employees as $e): ?>
        <tr>
          <td class="text-muted"><?= htmlspecialchars($e['employee_code']) ?></td>
          <td class="fw-semibold">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar-sm" style="width:32px;height:32px;font-size:0.75rem;"><?= strtoupper(substr($e['full_name'],0,1)) ?></div>
              <div>
                <?= htmlspecialchars($e['full_name']) ?>
                <?php if ($e['user_id']): ?>
                  <i class="bi bi-shield-check text-gold ms-1" title="Has dashboard login access"></i>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($e['department']) ?></td>
          <td><?= htmlspecialchars($e['designation']) ?></td>
          <td><?= date('M j, Y', strtotime($e['hire_date'])) ?></td>
          <td><?php $p = $e; include __DIR__ . '/../includes/badge_status.php'; ?></td>
          <td class="text-end">
            <button class="btn btn-sm btn-light" title="Edit"
              onclick='openEditEmp(<?= json_encode($e, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              <i class="bi bi-pencil"></i>
            </button>
            <?php if ($canDelete): ?>
            <form method="POST" class="d-inline confirm-delete" data-label="this employee record">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <input type="hidden" name="form_action" value="delete">
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
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
<div class="modal fade" id="empModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="empForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" id="empFormAction" value="create">
        <input type="hidden" name="id" id="empId" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="empModalTitle">Add New Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name *</label>
              <input type="text" name="full_name" id="e_full_name" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input type="email" name="email" id="e_email" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" id="e_phone" class="form-control form-control-bms">
            </div>
            <div class="col-md-6">
              <label class="form-label">Department *</label>
              <input type="text" name="department" id="e_department" class="form-control form-control-bms" list="deptList" required>
              <datalist id="deptList">
                <?php foreach ($departments as $d): ?><option value="<?= htmlspecialchars($d) ?>"><?php endforeach; ?>
              </datalist>
            </div>
            <div class="col-md-6">
              <label class="form-label">Designation *</label>
              <input type="text" name="designation" id="e_designation" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Monthly Salary ($)</label>
              <input type="number" step="0.01" min="0" name="salary" id="e_salary" class="form-control form-control-bms">
            </div>
            <div class="col-md-6">
              <label class="form-label">Hire Date *</label>
              <input type="date" name="hire_date" id="e_hire_date" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" id="e_status" class="form-select form-control-bms">
                <option value="active">Active</option>
                <option value="on_leave">On Leave</option>
                <option value="terminated">Terminated</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">System Login Account <span class="text-muted small">(optional — links this employee to a dashboard user so they receive task notifications)</span></label>
              <select name="user_id" id="e_user_id" class="form-select form-control-bms">
                <option value="">— No login access —</option>
                <?php foreach ($unlinkedUsers as $u): ?>
                  <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-gold">Save Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetEmpForm() {
  document.getElementById('empForm').reset();
  document.getElementById('empFormAction').value = 'create';
  document.getElementById('empId').value = '';
  document.getElementById('e_user_id').value = '';
  document.getElementById('empModalTitle').textContent = 'Add New Employee';
}
function openEditEmp(e) {
  document.getElementById('empFormAction').value = 'update';
  document.getElementById('empId').value = e.id;
  document.getElementById('e_full_name').value = e.full_name || '';
  document.getElementById('e_email').value = e.email || '';
  document.getElementById('e_phone').value = e.phone || '';
  document.getElementById('e_department').value = e.department || '';
  document.getElementById('e_designation').value = e.designation || '';
  document.getElementById('e_salary').value = e.salary || 0;
  document.getElementById('e_hire_date').value = e.hire_date || '';
  document.getElementById('e_status').value = e.status || 'active';

  const userSelect = document.getElementById('e_user_id');
  if (e.user_id) {
    // The currently-linked account may not be in the "unlinked users" list
    // this select was built from (since it's already linked to this row).
    // Add it dynamically so it shows correctly, instead of dropping silently.
    let opt = userSelect.querySelector('option[value="' + e.user_id + '"]');
    if (!opt) {
      opt = document.createElement('option');
      opt.value = e.user_id;
      opt.textContent = (e.linked_user_name || 'Linked user') + (e.linked_user_email ? ' (' + e.linked_user_email + ')' : '');
      userSelect.appendChild(opt);
    }
    userSelect.value = e.user_id;
  } else {
    userSelect.value = '';
  }

  document.getElementById('empModalTitle').textContent = 'Edit Employee';
  new bootstrap.Modal(document.getElementById('empModal')).show();
}
<?php if ($autoOpenAdd): ?>
document.addEventListener('DOMContentLoaded', function () {
  resetEmpForm();
  new bootstrap.Modal(document.getElementById('empModal')).show();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
