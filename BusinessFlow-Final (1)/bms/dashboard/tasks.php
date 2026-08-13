<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Tasks';
$activePage = 'tasks';
$taskModel = new Task();
$projectModel = new Project();
$employeeModel = new Employee();
$notificationModel = new Notification();
$log = new ActivityLog();
$canDelete = in_array($_SESSION['role'], ['admin', 'manager'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: tasks.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        if (!$canDelete) {
            Flash::set('error', 'You do not have permission to delete tasks.');
            header('Location: tasks.php'); exit;
        }
        $id = (int) $_POST['id'];
        $taskModel->delete($id);
        $log->record((int) $_SESSION['user_id'], 'tasks', "Deleted task #$id");
        Flash::set('success', 'Task deleted.');
        header('Location: tasks.php'); exit;
    }

    if ($formAction === 'update_status') {
        $id = (int) $_POST['id'];
        $taskModel->updateStatus($id, Validator::clean($_POST['status'] ?? 'todo'));
        header('Location: tasks.php'); exit;
    }

    if ($formAction === 'create') {
        $data = [
            'project_id'  => $_POST['project_id'] ?? '',
            'assigned_to' => $_POST['assigned_to'] ?? '',
            'title'       => Validator::clean($_POST['title'] ?? ''),
            'description' => Validator::clean($_POST['description'] ?? ''),
            'due_date'    => Validator::clean($_POST['due_date'] ?? ''),
            'status'      => Validator::clean($_POST['status'] ?? 'todo'),
            'priority'    => Validator::clean($_POST['priority'] ?? 'medium'),
        ];
        $v = new Validator();
        $v->required('project_id', $data['project_id'], 'Project')
          ->required('title', $data['title'], 'Task title');

        if ($v->fails()) {
            Flash::set('error', $v->firstError());
        } else {
            $taskId = $taskModel->create($data);
            $log->record((int) $_SESSION['user_id'], 'tasks', "Created task: {$data['title']}");

            // Notify the assigned employee's linked user account, if any.
            if (!empty($data['assigned_to'])) {
                $emp = $employeeModel->find((int) $data['assigned_to']);
                if ($emp && !empty($emp['user_id'])) {
                    $notificationModel->notify(
                        (int) $emp['user_id'],
                        'task_assigned',
                        'New task assigned to you',
                        $data['title'],
                        'dashboard/tasks.php'
                    );
                }
            }
            Flash::set('success', 'Task created successfully.');
        }
    } elseif ($formAction === 'update') {
        $id = (int) $_POST['id'];
        $data = [
            'project_id'  => $_POST['project_id'] ?? '',
            'assigned_to' => $_POST['assigned_to'] ?? '',
            'title'       => Validator::clean($_POST['title'] ?? ''),
            'description' => Validator::clean($_POST['description'] ?? ''),
            'due_date'    => Validator::clean($_POST['due_date'] ?? ''),
            'status'      => Validator::clean($_POST['status'] ?? 'todo'),
            'priority'    => Validator::clean($_POST['priority'] ?? 'medium'),
        ];
        $taskModel->update($id, $data);
        $log->record((int) $_SESSION['user_id'], 'tasks', "Updated task: {$data['title']}");
        Flash::set('success', 'Task updated successfully.');
    }
    header('Location: tasks.php'); exit;
}

$search = Validator::clean($_GET['q'] ?? '');
$projectFilter = Validator::clean($_GET['project'] ?? '');
$assigneeFilter = Validator::clean($_GET['assignee'] ?? '');
$tasks = $taskModel->all($search, $projectFilter, $assigneeFilter);
$projectsForDropdown = $projectModel->forDropdown();
$employeesForDropdown = $employeeModel->forDropdown();

$columns = [
    'todo'        => ['label' => 'To Do',       'icon' => 'bi-circle'],
    'in_progress' => ['label' => 'In Progress',  'icon' => 'bi-arrow-repeat'],
    'review'      => ['label' => 'In Review',    'icon' => 'bi-eye'],
    'done'        => ['label' => 'Done',         'icon' => 'bi-check-circle-fill'],
];
$grouped = array_fill_keys(array_keys($columns), []);
foreach ($tasks as $t) {
    $grouped[$t['status']][] = $t;
}

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Tasks</h2>
    <p class="text-muted mb-0">A system-wide board of every task across every project. Drag a card to change its status.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#taskModal" onclick="resetTaskForm()">
    <i class="bi bi-plus-lg me-1"></i> New Task
  </button>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="data-card mb-3">
  <div class="data-card-head">
    <form class="d-flex gap-2 flex-wrap" method="GET">
      <input type="text" name="q" class="form-control form-control-bms form-control-sm" style="width:200px;" placeholder="Search tasks..." value="<?= htmlspecialchars($search) ?>">
      <select name="project" class="form-select form-select-sm" style="width:180px;" onchange="this.form.submit()">
        <option value="">All Projects</option>
        <?php foreach ($projectsForDropdown as $pj): ?>
          <option value="<?= $pj['id'] ?>" <?= $projectFilter == $pj['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pj['project_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="assignee" class="form-select form-select-sm" style="width:170px;" onchange="this.form.submit()">
        <option value="">All Assignees</option>
        <?php foreach ($employeesForDropdown as $e): ?>
          <option value="<?= $e['id'] ?>" <?= $assigneeFilter == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
      <?php if ($search || $projectFilter || $assigneeFilter): ?><a href="tasks.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
  </div>
</div>

<div class="row g-3 kanban-board">
  <?php foreach ($columns as $key => $col): ?>
    <div class="col-md-6 col-lg-3">
      <div class="kanban-column" data-status="<?= $key ?>">
        <div class="kanban-column-head">
          <span><i class="bi <?= $col['icon'] ?> me-2"></i><?= $col['label'] ?></span>
          <span class="badge-soft badge-soft-gray"><?= count($grouped[$key]) ?></span>
        </div>
        <div class="kanban-column-body" data-status="<?= $key ?>">
          <?php if (!$grouped[$key]): ?>
            <p class="text-muted small text-center py-4 mb-0">No tasks here.</p>
          <?php endif; ?>
          <?php foreach ($grouped[$key] as $t): ?>
            <div class="kanban-card" draggable="true" data-task-id="<?= $t['id'] ?>">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="badge-soft badge-soft-<?= $t['priority']==='high'?'red':($t['priority']==='medium'?'gold':'gray') ?> text-capitalize" style="font-size:0.68rem;"><?= $t['priority'] ?></span>
                <div class="dropdown">
                  <button class="btn btn-sm p-0 text-muted" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                  <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item small" href="#" onclick='openEditTask(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>);return false;'><i class="bi bi-pencil me-2"></i>Edit</a></li>
                    <?php if ($canDelete): ?>
                    <li>
                      <form method="POST" class="confirm-delete" data-label="this task">
                        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button class="dropdown-item small text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                      </form>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
              <div class="fw-semibold small mb-1"><?= htmlspecialchars($t['title']) ?></div>
              <div class="text-muted mb-2" style="font-size:0.76rem;"><i class="bi bi-kanban me-1"></i><?= htmlspecialchars($t['project_name']) ?></div>
              <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted" style="font-size:0.74rem;">
                  <?php if ($t['due_date']): ?><i class="bi bi-calendar-event me-1"></i><?= date('M j', strtotime($t['due_date'])) ?><?php endif; ?>
                </span>
                <?php if ($t['assignee_name']): ?>
                  <span class="avatar-sm" style="width:26px;height:26px;font-size:0.68rem;" title="<?= htmlspecialchars($t['assignee_name']) ?>"><?= strtoupper(substr($t['assignee_name'],0,1)) ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Hidden form used by drag-and-drop to persist status changes -->
<form method="POST" id="dragStatusForm" class="d-none">
  <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
  <input type="hidden" name="form_action" value="update_status">
  <input type="hidden" name="id" id="dragTaskId">
  <input type="hidden" name="status" id="dragTaskStatus">
</form>

<!-- Add / Edit Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="taskForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" id="taskFormAction" value="create">
        <input type="hidden" name="id" id="taskId" value="">
        <div class="modal-header"><h5 class="modal-title" id="taskModalTitle">New Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Project *</label>
            <select name="project_id" id="tk_project" class="form-select form-control-bms" required>
              <option value="">Select project...</option>
              <?php foreach ($projectsForDropdown as $pj): ?><option value="<?= $pj['id'] ?>"><?= htmlspecialchars($pj['project_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Task Title *</label><input type="text" name="title" id="tk_title" class="form-control form-control-bms" required></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="tk_description" rows="2" class="form-control form-control-bms"></textarea></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Assign To</label>
              <select name="assigned_to" id="tk_assigned" class="form-select form-control-bms">
                <option value="">Unassigned</option>
                <?php foreach ($employeesForDropdown as $e): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['full_name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Due Date</label><input type="date" name="due_date" id="tk_due" class="form-control form-control-bms"></div>
            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select name="priority" id="tk_priority" class="form-select form-control-bms">
                <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" id="tk_status" class="form-select form-control-bms">
                <?php foreach ($columns as $key => $col): ?><option value="<?= $key ?>"><?= $col['label'] ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-gold">Save Task</button></div>
      </form>
    </div>
  </div>
</div>

<style>
.kanban-column { background:#f8f9fb; border-radius:14px; border:1px solid #e7eaef; display:flex; flex-direction:column; height:100%; min-height:420px; }
.kanban-column-head { display:flex; justify-content:space-between; align-items:center; padding:0.9rem 1rem; font-weight:600; font-size:0.9rem; color:#0f2a4a; border-bottom:1px solid #e7eaef; }
.kanban-column-body { padding:0.75rem; flex:1; overflow-y:auto; min-height:200px; }
.kanban-column-body.drag-over { background:#fbf1de; }
.kanban-card { background:#fff; border:1px solid #e7eaef; border-radius:10px; padding:0.8rem; margin-bottom:0.7rem; cursor:grab; transition:all 0.2s ease; }
.kanban-card:hover { box-shadow:0 6px 16px rgba(15,42,74,0.10); transform:translateY(-2px); }
.kanban-card.dragging { opacity:0.4; }
</style>

<script>
function resetTaskForm() {
  document.getElementById('taskForm').reset();
  document.getElementById('taskFormAction').value = 'create';
  document.getElementById('taskId').value = '';
  document.getElementById('taskModalTitle').textContent = 'New Task';
}
function openEditTask(t) {
  document.getElementById('taskFormAction').value = 'update';
  document.getElementById('taskId').value = t.id;
  document.getElementById('tk_project').value = t.project_id || '';
  document.getElementById('tk_title').value = t.title || '';
  document.getElementById('tk_description').value = t.description || '';
  document.getElementById('tk_assigned').value = t.assigned_to || '';
  document.getElementById('tk_due').value = t.due_date || '';
  document.getElementById('tk_priority').value = t.priority || 'medium';
  document.getElementById('tk_status').value = t.status || 'todo';
  document.getElementById('taskModalTitle').textContent = 'Edit Task';
  new bootstrap.Modal(document.getElementById('taskModal')).show();
}

// ---- Drag and drop between Kanban columns ----
document.addEventListener('DOMContentLoaded', function () {
  const cards = document.querySelectorAll('.kanban-card');
  const columns = document.querySelectorAll('.kanban-column-body');

  cards.forEach((card) => {
    card.addEventListener('dragstart', function () {
      card.classList.add('dragging');
    });
    card.addEventListener('dragend', function () {
      card.classList.remove('dragging');
    });
  });

  columns.forEach((col) => {
    col.addEventListener('dragover', function (e) {
      e.preventDefault();
      col.classList.add('drag-over');
    });
    col.addEventListener('dragleave', function () {
      col.classList.remove('drag-over');
    });
    col.addEventListener('drop', function (e) {
      e.preventDefault();
      col.classList.remove('drag-over');
      const dragging = document.querySelector('.kanban-card.dragging');
      if (!dragging) return;
      const taskId = dragging.getAttribute('data-task-id');
      const newStatus = col.getAttribute('data-status');
      const oldStatus = dragging.closest('.kanban-column-body').getAttribute('data-status');
      if (newStatus === oldStatus) return;

      document.getElementById('dragTaskId').value = taskId;
      document.getElementById('dragTaskStatus').value = newStatus;
      document.getElementById('dragStatusForm').submit();
    });
  });
});
</script>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
