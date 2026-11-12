<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Projects';
$activePage = 'projects';
$projectModel = new Project();
$clientModel = new Client();
$log = new ActivityLog();
$canDelete = in_array($_SESSION['role'], ['admin', 'manager'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: projects.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to delete projects.');
            header('Location: projects.php'); exit;
        }
        $id = (int) $_POST['id'];
        $projectModel->delete($id);
        $log->record((int) $_SESSION['user_id'], 'projects', "Deleted project #$id");
        Flash::set('success', 'Project deleted successfully.');
        header('Location: projects.php'); exit;
    }

    $data = [
        'client_id'    => $_POST['client_id'] ?? null,
        'project_name' => Validator::clean($_POST['project_name'] ?? ''),
        'description'  => Validator::clean($_POST['description'] ?? ''),
        'budget'       => $_POST['budget'] ?? 0,
        'start_date'   => Validator::clean($_POST['start_date'] ?? ''),
        'deadline'     => Validator::clean($_POST['deadline'] ?? ''),
        'status'       => Validator::clean($_POST['status'] ?? 'planned'),
        'priority'     => Validator::clean($_POST['priority'] ?? 'medium'),
        'progress'     => $_POST['progress'] ?? 0,
    ];

    $v = new Validator();
    $v->required('project_name', $data['project_name'], 'Project name')
      ->numeric('budget', $data['budget'], 'Budget')
      ->numeric('progress', $data['progress'], 'Progress')
      ->range('progress', $data['progress'], 0, 100, 'Progress')
      ->dateOnOrAfter('deadline', $data['deadline'], $data['start_date'], 'Deadline', 'the start date');

    if ($v->fails()) {
        Flash::set('error', $v->firstError());
    } elseif ($formAction === 'create') {
        $projectModel->create($data, (int) $_SESSION['user_id']);
        $log->record((int) $_SESSION['user_id'], 'projects', "Created project: {$data['project_name']}");
        Flash::set('success', 'Project created successfully.');
    } elseif ($formAction === 'update') {
        $id = (int) $_POST['id'];
        $projectModel->update($id, $data);
        $log->record((int) $_SESSION['user_id'], 'projects', "Updated project: {$data['project_name']}");
        Flash::set('success', 'Project updated successfully.');
    }
    header('Location: projects.php'); exit;
}

$search = Validator::clean($_GET['q'] ?? '');
$statusFilter = Validator::clean($_GET['status'] ?? '');
$projects = $projectModel->all($search, $statusFilter);
$clientsForDropdown = $clientModel->forDropdown();
$autoOpenAdd = isset($_GET['action']) && $_GET['action'] === 'add';

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Projects</h2>
    <p class="text-muted mb-0">Plan, assign and track every project from kickoff to delivery.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#projModal" onclick="resetProjForm()">
    <i class="bi bi-plus-lg me-1"></i> New Project
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
      <input type="text" name="q" class="form-control form-control-bms form-control-sm" style="width:220px;" placeholder="Search projects..." value="<?= htmlspecialchars($search) ?>">
      <select name="status" class="form-select form-select-sm" style="width:160px;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach (['planned','in_progress','on_hold','completed','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
      <?php if ($search || $statusFilter): ?><a href="projects.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-bms">
      <thead><tr><th>Project</th><th>Client</th><th>Budget</th><th>Deadline</th><th>Progress</th><th>Priority</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (!$projects): ?>
        <tr><td colspan="8" class="text-center text-muted py-5">No projects found. Click "New Project" to create one.</td></tr>
      <?php endif; ?>
      <?php foreach ($projects as $pr): ?>
        <tr>
          <td class="fw-semibold"><a href="project_view.php?id=<?= $pr['id'] ?>" class="text-decoration-none" style="color:var(--navy);"><?= htmlspecialchars($pr['project_name']) ?></a></td>
          <td><?= htmlspecialchars($pr['company_name'] ?? '—') ?></td>
          <td>$<?= number_format($pr['budget'], 0) ?></td>
          <td><?= $pr['deadline'] ? date('M j, Y', strtotime($pr['deadline'])) : '—' ?></td>
          <td style="width:130px;">
            <div class="progress-bms"><div class="progress-bar" style="width:<?= (int)$pr['progress'] ?>%"></div></div>
            <small class="text-muted"><?= (int)$pr['progress'] ?>%</small>
          </td>
          <td class="text-capitalize"><?= htmlspecialchars($pr['priority']) ?></td>
          <td><?php $p = $pr; include __DIR__ . '/../includes/badge_status.php'; ?></td>
          <td class="text-end">
            <a href="project_view.php?id=<?= $pr['id'] ?>" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
            <button class="btn btn-sm btn-light" title="Edit"
              onclick='openEditProj(<?= json_encode($pr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              <i class="bi bi-pencil"></i>
            </button>
            <?php if ($canDelete): ?>
            <form method="POST" class="d-inline confirm-delete" data-label="this project">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <input type="hidden" name="form_action" value="delete">
              <input type="hidden" name="id" value="<?= $pr['id'] ?>">
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
<div class="modal fade" id="projModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="projForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" id="projFormAction" value="create">
        <input type="hidden" name="id" id="projId" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="projModalTitle">New Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Project Name *</label>
              <input type="text" name="project_name" id="pr_name" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Client</label>
              <select name="client_id" id="pr_client" class="form-select form-control-bms">
                <option value="">— No client —</option>
                <?php foreach ($clientsForDropdown as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" id="pr_description" rows="2" class="form-control form-control-bms"></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label">Budget ($)</label>
              <input type="number" step="0.01" min="0" name="budget" id="pr_budget" class="form-control form-control-bms">
            </div>
            <div class="col-md-4">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" id="pr_start" class="form-control form-control-bms">
            </div>
            <div class="col-md-4">
              <label class="form-label">Deadline</label>
              <input type="date" name="deadline" id="pr_deadline" class="form-control form-control-bms">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" id="pr_status" class="form-select form-control-bms">
                <option value="planned">Planned</option>
                <option value="in_progress">In Progress</option>
                <option value="on_hold">On Hold</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Priority</label>
              <select name="priority" id="pr_priority" class="form-select form-control-bms">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Progress (<span id="projectProgressLabel">0%</span>)</label>
              <input type="range" min="0" max="100" name="progress" id="projectProgress" class="form-range">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-gold">Save Project</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetProjForm() {
  document.getElementById('projForm').reset();
  document.getElementById('projFormAction').value = 'create';
  document.getElementById('projId').value = '';
  document.getElementById('projModalTitle').textContent = 'New Project';
  document.getElementById('projectProgress').value = 0;
  document.getElementById('projectProgressLabel').textContent = '0%';
}
function openEditProj(p) {
  document.getElementById('projFormAction').value = 'update';
  document.getElementById('projId').value = p.id;
  document.getElementById('pr_name').value = p.project_name || '';
  document.getElementById('pr_client').value = p.client_id || '';
  document.getElementById('pr_description').value = p.description || '';
  document.getElementById('pr_budget').value = p.budget || 0;
  document.getElementById('pr_start').value = p.start_date || '';
  document.getElementById('pr_deadline').value = p.deadline || '';
  document.getElementById('pr_status').value = p.status || 'planned';
  document.getElementById('pr_priority').value = p.priority || 'medium';
  document.getElementById('projectProgress').value = p.progress || 0;
  document.getElementById('projectProgressLabel').textContent = (p.progress || 0) + '%';
  document.getElementById('projModalTitle').textContent = 'Edit Project';
  new bootstrap.Modal(document.getElementById('projModal')).show();
}
<?php if ($autoOpenAdd): ?>
document.addEventListener('DOMContentLoaded', function () {
  resetProjForm();
  new bootstrap.Modal(document.getElementById('projModal')).show();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
