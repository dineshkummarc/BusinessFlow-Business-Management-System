<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$projectModel = new Project();
$employeeModel = new Employee();
$log = new ActivityLog();
$canDelete = in_array($_SESSION['role'], ['admin', 'manager'], true);

$id = (int) ($_GET['id'] ?? 0);
$project = $projectModel->find($id);
if (!$project) {
    header('Location: projects.php');
    exit;
}

// -------- Handle team assignment / task creation on this page --------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: project_view.php?id=' . $id); exit;
    }

    $formAction = $_POST['form_action'] ?? '';
    $db = Database::getInstance();

    if ($formAction === 'assign_team' && !empty($_POST['employee_id'])) {
        $db->execute(
            'INSERT IGNORE INTO project_team (project_id, employee_id, role_in_project) VALUES (?,?,?)',
            [$id, (int) $_POST['employee_id'], Validator::clean($_POST['role_in_project'] ?? '')]
        );
        $log->record((int) $_SESSION['user_id'], 'projects', "Assigned team member to project #$id");
        Flash::set('success', 'Team member assigned.');
    } elseif ($formAction === 'remove_team') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to remove team members.');
            header('Location: project_view.php?id=' . $id); exit;
        }
        $db->execute('DELETE FROM project_team WHERE project_id = ? AND employee_id = ?', [$id, (int) $_POST['employee_id']]);
        Flash::set('success', 'Team member removed from project.');
    } elseif ($formAction === 'add_task') {
        $title = Validator::clean($_POST['title'] ?? '');
        if ($title !== '') {
            $db->execute(
                'INSERT INTO tasks (project_id, assigned_to, title, description, due_date, status, priority) VALUES (?,?,?,?,?,?,?)',
                [
                    $id,
                    $_POST['assigned_to'] ?: null,
                    $title,
                    Validator::clean($_POST['description'] ?? ''),
                    $_POST['due_date'] ?: null,
                    Validator::clean($_POST['status'] ?? 'todo'),
                    Validator::clean($_POST['priority'] ?? 'medium'),
                ]
            );
            Flash::set('success', 'Task added to project.');
        }
    } elseif ($formAction === 'update_task_status') {
        $db->execute('UPDATE tasks SET status = ? WHERE id = ? AND project_id = ?', [Validator::clean($_POST['status'] ?? 'todo'), (int) $_POST['task_id'], $id]);
        Flash::set('success', 'Task status updated.');
    } elseif ($formAction === 'delete_task') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to delete tasks.');
            header('Location: project_view.php?id=' . $id); exit;
        }
        $db->execute('DELETE FROM tasks WHERE id = ? AND project_id = ?', [(int) $_POST['task_id'], $id]);
        Flash::set('success', 'Task deleted.');
    }
    header('Location: project_view.php?id=' . $id); exit;
}

$team = $projectModel->team($id);
$tasks = $projectModel->tasks($id);
$availableEmployees = $employeeModel->forDropdown();
$assignedIds = array_column($team, 'id');

$pageTitle = $project['project_name'];
$activePage = 'projects';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <a href="projects.php" class="text-muted"><i class="bi bi-arrow-left"></i></a>
      <h2 class="mb-0"><?= htmlspecialchars($project['project_name']) ?></h2>
      <?php $p = $project; include __DIR__ . '/../includes/badge_status.php'; ?>
    </div>
    <p class="text-muted mb-0">Client: <strong><?= htmlspecialchars($project['company_name'] ?? 'No client assigned') ?></strong></p>
  </div>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="stat-card py-3"><div class="stat-value fs-5">$<?= number_format($project['budget'],0) ?></div><div class="stat-label">Budget</div></div></div>
  <div class="col-md-3"><div class="stat-card py-3"><div class="stat-value fs-5"><?= $project['deadline'] ? date('M j, Y', strtotime($project['deadline'])) : '—' ?></div><div class="stat-label">Deadline</div></div></div>
  <div class="col-md-3"><div class="stat-card py-3"><div class="stat-value fs-5 text-capitalize"><?= htmlspecialchars($project['priority']) ?></div><div class="stat-label">Priority</div></div></div>
  <div class="col-md-3">
    <div class="stat-card py-3">
      <div class="progress-bms mb-1"><div class="progress-bar" style="width:<?= (int)$project['progress'] ?>%"></div></div>
      <div class="stat-label"><?= (int)$project['progress'] ?>% Complete</div>
    </div>
  </div>
</div>

<?php if (!empty($project['description'])): ?>
  <div class="data-card mb-3"><div class="p-3"><h6 class="fw-semibold">Description</h6><p class="text-muted mb-0"><?= nl2br(htmlspecialchars($project['description'])) ?></p></div></div>
<?php endif; ?>

<div class="row g-3">
  <!-- Team -->
  <div class="col-lg-5">
    <div class="data-card mb-3">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Project Team</h6></div>
      <div class="p-3">
        <?php if (!$team): ?><p class="text-muted small">No team members assigned yet.</p><?php endif; ?>
        <?php foreach ($team as $t): ?>
          <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar-sm" style="width:32px;height:32px;font-size:0.75rem;"><?= strtoupper(substr($t['full_name'],0,1)) ?></div>
              <div>
                <div class="small fw-semibold"><?= htmlspecialchars($t['full_name']) ?></div>
                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($t['role_in_project'] ?: $t['designation']) ?></div>
              </div>
            </div>
            <?php if ($canDelete): ?>
            <form method="POST" class="confirm-delete" data-label="this team member from the project">
              <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
              <input type="hidden" name="form_action" value="remove_team">
              <input type="hidden" name="employee_id" value="<?= $t['id'] ?>">
              <button class="btn btn-sm btn-light text-danger"><i class="bi bi-x"></i></button>
            </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <form method="POST" class="mt-3 d-flex gap-2">
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <input type="hidden" name="form_action" value="assign_team">
          <select name="employee_id" class="form-select form-select-sm" required>
            <option value="">Select employee...</option>
            <?php foreach ($availableEmployees as $e): if (in_array($e['id'], $assignedIds)) continue; ?>
              <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['full_name']) ?> (<?= htmlspecialchars($e['employee_code']) ?>)</option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="role_in_project" class="form-control form-control-sm" placeholder="Role" style="max-width:120px;">
          <button class="btn btn-sm btn-navy"><i class="bi bi-plus"></i></button>
        </form>
      </div>
    </div>
  </div>

  <!-- Tasks -->
  <div class="col-lg-7">
    <div class="data-card">
      <div class="data-card-head">
        <h6 class="mb-0 fw-semibold">Tasks</h6>
        <button class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#taskModal"><i class="bi bi-plus-lg me-1"></i>Add Task</button>
      </div>
      <div class="table-responsive">
        <table class="table table-bms">
          <thead><tr><th>Task</th><th>Assignee</th><th>Due</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php if (!$tasks): ?><tr><td colspan="5" class="text-center text-muted py-4">No tasks yet.</td></tr><?php endif; ?>
          <?php foreach ($tasks as $t): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($t['title']) ?></td>
              <td><?= htmlspecialchars($t['assignee_name'] ?? '—') ?></td>
              <td><?= $t['due_date'] ? date('M j', strtotime($t['due_date'])) : '—' ?></td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                  <input type="hidden" name="form_action" value="update_task_status">
                  <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                  <select name="status" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
                    <?php foreach (['todo'=>'To Do','in_progress'=>'In Progress','review'=>'In Review','done'=>'Done'] as $val=>$label): ?>
                      <option value="<?= $val ?>" <?= $t['status']===$val?'selected':'' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td class="text-end">
                <?php if ($canDelete): ?>
                <form method="POST" class="d-inline confirm-delete" data-label="this task">
                  <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                  <input type="hidden" name="form_action" value="delete_task">
                  <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                  <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" value="add_task">
        <div class="modal-header"><h5 class="modal-title">Add Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Task Title *</label><input type="text" name="title" class="form-control form-control-bms" required></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="2" class="form-control form-control-bms"></textarea></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Assign To</label>
              <select name="assigned_to" class="form-select form-control-bms">
                <option value="">Unassigned</option>
                <?php foreach ($team as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control form-control-bms"></div>
            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select name="priority" class="form-select form-control-bms">
                <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" class="form-select form-control-bms">
                <option value="todo" selected>To Do</option><option value="in_progress">In Progress</option><option value="review">In Review</option><option value="done">Done</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-gold">Add Task</button></div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
