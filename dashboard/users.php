<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireRole(['admin']);

$pageTitle = 'Manage Users';
$activePage = 'users';
$userModel = new User();
$log = new ActivityLog();
$notificationModel = new Notification();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: users.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'delete') {
        $id = (int) $_POST['id'];
        if ($id === (int) $_SESSION['user_id']) {
            Flash::set('error', 'You cannot delete your own account while logged in.');
        } else {
            $target = $userModel->findById($id);
            if ($target && $target['role'] === 'admin' && $userModel->countAdmins() <= 1) {
                Flash::set('error', 'You cannot delete the last remaining admin account.');
            } else {
                $userModel->delete($id);
                $log->record((int) $_SESSION['user_id'], 'users', "Deleted user account #$id");
                Flash::set('success', 'User account deleted.');
            }
        }
        header('Location: users.php'); exit;
    }

    if ($formAction === 'reset_password') {
        $id = (int) $_POST['id'];
        $newPass = $_POST['new_password'] ?? '';
        $v = new Validator();
        $v->minLength('new_password', $newPass, 8, 'New password');
        if ($v->fails()) {
            Flash::set('error', $v->firstError());
        } else {
            $userModel->resetPassword($id, $newPass);
            $log->record((int) $_SESSION['user_id'], 'users', "Reset password for user #$id");
            Flash::set('success', 'Password reset successfully.');
        }
        header('Location: users.php'); exit;
    }

    $data = [
        'full_name' => Validator::clean($_POST['full_name'] ?? ''),
        'email'     => Validator::clean($_POST['email'] ?? ''),
        'username'  => Validator::clean($_POST['username'] ?? ''),
        'phone'     => Validator::clean($_POST['phone'] ?? ''),
        'role'      => Validator::clean($_POST['role'] ?? 'staff'),
        'status'    => Validator::clean($_POST['status'] ?? 'active'),
        'password'  => $_POST['password'] ?? '',
    ];

    $v = new Validator();
    $v->required('full_name', $data['full_name'], 'Full name')
      ->required('email', $data['email'], 'Email')
      ->email('email', $data['email'], 'Email');

    if ($formAction === 'create') {
        $v->required('username', $data['username'], 'Username')
          ->minLength('username', $data['username'], 4, 'Username')
          ->required('password', $data['password'], 'Password')
          ->minLength('password', $data['password'], 8, 'Password');

        if (!$v->fails() && $userModel->emailExists($data['email'])) {
            Flash::set('error', 'An account with this email already exists.');
        } elseif (!$v->fails() && $userModel->usernameExists($data['username'])) {
            Flash::set('error', 'This username is already taken.');
        } elseif ($v->fails()) {
            Flash::set('error', $v->firstError());
        } else {
            $newId = $userModel->createByAdmin($data);
            $log->record((int) $_SESSION['user_id'], 'users', "Created user account: {$data['full_name']} ({$data['role']})");
            $notificationModel->notify($newId, 'account_created', 'Your account was created', 'An administrator created an account for you on BusinessFlow.', 'dashboard/profile.php');
            Flash::set('success', 'User account created successfully.');
        }
    } elseif ($formAction === 'update') {
        $id = (int) $_POST['id'];
        if ($v->fails()) {
            Flash::set('error', $v->firstError());
        } elseif ($id === (int) $_SESSION['user_id'] && $data['role'] !== 'admin') {
            Flash::set('error', 'You cannot remove your own admin role while logged in.');
        } elseif ($id === (int) $_SESSION['user_id'] && $data['status'] !== 'active') {
            Flash::set('error', 'You cannot deactivate your own account while logged in.');
        } else {
            $target = $userModel->findById($id);
            if ($target && $target['role'] === 'admin' && $data['role'] !== 'admin' && $userModel->countAdmins() <= 1) {
                Flash::set('error', 'You cannot demote the last remaining admin account.');
            } else {
                $userModel->updateByAdmin($id, $data);
                $log->record((int) $_SESSION['user_id'], 'users', "Updated user account: {$data['full_name']}");
                Flash::set('success', 'User account updated successfully.');
            }
        }
    }
    header('Location: users.php'); exit;
}

$search = Validator::clean($_GET['q'] ?? '');
$roleFilter = Validator::clean($_GET['role'] ?? '');
$users = $userModel->all($search, $roleFilter);
$autoOpenAdd = isset($_GET['action']) && $_GET['action'] === 'add';

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Manage Users</h2>
    <p class="text-muted mb-0">Create system login accounts and control who can access the dashboard, and with what role.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetUserForm()">
    <i class="bi bi-plus-lg me-1"></i> Add User
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
      <input type="text" name="q" class="form-control form-control-bms form-control-sm" style="width:220px;" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
      <select name="role" class="form-select form-select-sm" style="width:150px;" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <option value="admin" <?= $roleFilter==='admin'?'selected':'' ?>>Admin</option>
        <option value="manager" <?= $roleFilter==='manager'?'selected':'' ?>>Manager</option>
        <option value="staff" <?= $roleFilter==='staff'?'selected':'' ?>>Staff</option>
      </select>
      <button class="btn btn-sm btn-outline-navy"><i class="bi bi-search"></i></button>
      <?php if ($search || $roleFilter): ?><a href="users.php" class="btn btn-sm btn-light">Clear</a><?php endif; ?>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-bms">
      <thead><tr><th>User</th><th>Username</th><th>Role</th><th>Status</th><th>Last Login</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td class="fw-semibold">
            <div class="d-flex align-items-center gap-2">
              <div class="avatar-sm" style="width:32px;height:32px;font-size:0.75rem;"><?= strtoupper(substr($u['full_name'],0,1)) ?></div>
              <div><?= htmlspecialchars($u['full_name']) ?><?php if ($u['id']==$_SESSION['user_id']): ?> <span class="badge-soft badge-soft-blue" style="font-size:0.65rem;">You</span><?php endif; ?><div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div></div>
            </div>
          </td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td class="text-capitalize"><span class="badge-soft badge-soft-<?= $u['role']==='admin'?'gold':($u['role']==='manager'?'blue':'gray') ?>"><?= $u['role'] ?></span></td>
          <td><?php $p = $u; include __DIR__ . '/../includes/badge_status.php'; ?></td>
          <td class="text-muted small"><?= $u['last_login'] ? date('M j, Y g:ia', strtotime($u['last_login'])) : 'Never' ?></td>
          <td class="text-end">
            <button class="btn btn-sm btn-light" title="Edit" onclick='openEditUser(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-light" title="Reset Password" onclick='openResetPassword(<?= (int)$u['id'] ?>, <?= json_encode($u['full_name'], JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="bi bi-key"></i></button>
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
              <form method="POST" class="d-inline confirm-delete" data-label="this user account">
                <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                <input type="hidden" name="form_action" value="delete">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
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

<!-- Add / Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="userForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" id="userFormAction" value="create">
        <input type="hidden" name="id" id="userId" value="">
        <div class="modal-header"><h5 class="modal-title" id="userModalTitle">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="full_name" id="u_full_name" class="form-control form-control-bms" required></div>
            <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" id="u_email" class="form-control form-control-bms" required></div>
            <div class="col-md-6" id="usernameWrap">
              <label class="form-label">Username *</label>
              <input type="text" name="username" id="u_username" class="form-control form-control-bms" minlength="4">
            </div>
            <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" id="u_phone" class="form-control form-control-bms"></div>
            <div class="col-md-6" id="passwordWrap">
              <label class="form-label">Password *</label>
              <input type="password" name="password" id="u_password" class="form-control form-control-bms" minlength="8">
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <select name="role" id="u_role" class="form-select form-control-bms">
                <option value="staff">Staff</option><option value="manager">Manager</option><option value="admin">Admin</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select name="status" id="u_status" class="form-select form-control-bms">
                <option value="active">Active</option><option value="inactive">Inactive</option><option value="suspended">Suspended</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-gold">Save User</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPwModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="form_action" value="reset_password">
        <input type="hidden" name="id" id="rp_id">
        <div class="modal-header"><h5 class="modal-title">Reset Password for <span id="rp_name"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label">New Password *</label>
          <input type="password" name="new_password" class="form-control form-control-bms" minlength="8" required>
          <p class="text-muted small mt-2 mb-0">The user will need to use this new password on their next login.</p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-gold">Reset Password</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function resetUserForm() {
  document.getElementById('userForm').reset();
  document.getElementById('userFormAction').value = 'create';
  document.getElementById('userId').value = '';
  document.getElementById('u_username').required = true;
  document.getElementById('u_password').required = true;
  document.getElementById('usernameWrap').style.display = '';
  document.getElementById('passwordWrap').style.display = '';
  document.getElementById('userModalTitle').textContent = 'Add User';
}
function openEditUser(u) {
  document.getElementById('userFormAction').value = 'update';
  document.getElementById('userId').value = u.id;
  document.getElementById('u_full_name').value = u.full_name || '';
  document.getElementById('u_email').value = u.email || '';
  document.getElementById('u_username').value = u.username || '';
  document.getElementById('u_phone').value = u.phone || '';
  document.getElementById('u_role').value = u.role || 'staff';
  document.getElementById('u_status').value = u.status || 'active';
  // Username/password aren't editable here (username is fixed; use Reset Password for credentials)
  document.getElementById('u_username').required = false;
  document.getElementById('u_password').required = false;
  document.getElementById('usernameWrap').style.display = 'none';
  document.getElementById('passwordWrap').style.display = 'none';
  document.getElementById('userModalTitle').textContent = 'Edit User';
  new bootstrap.Modal(document.getElementById('userModal')).show();
}
function openResetPassword(id, name) {
  document.getElementById('rp_id').value = id;
  document.getElementById('rp_name').textContent = name;
  new bootstrap.Modal(document.getElementById('resetPwModal')).show();
}
<?php if ($autoOpenAdd): ?>
document.addEventListener('DOMContentLoaded', function () { resetUserForm(); new bootstrap.Modal(document.getElementById('userModal')).show(); });
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
