<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'My Profile';
$activePage = 'profile';
$userModel = new User();
$log = new ActivityLog();
$user = $userModel->findById((int) $_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: profile.php'); exit;
    }

    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'update_profile') {
        $fullName = Validator::clean($_POST['full_name'] ?? '');
        $phone = Validator::clean($_POST['phone'] ?? '');

        $v = new Validator();
        $v->required('full_name', $fullName, 'Full name');

        if ($v->fails()) {
            Flash::set('error', $v->firstError());
        } else {
            $userModel->updateProfile((int) $user['id'], ['full_name' => $fullName, 'phone' => $phone]);
            $_SESSION['full_name'] = $fullName;
            $log->record((int) $user['id'], 'profile', 'Updated profile information');
            Flash::set('success', 'Profile updated successfully.');
        }
    } elseif ($formAction === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            Flash::set('error', 'Your current password is incorrect.');
        } else {
            $v = new Validator();
            $v->minLength('new_password', $newPass, 8, 'New password')
              ->matches('confirm_password', $confirm, $newPass, 'Password confirmation');

            if ($v->fails()) {
                Flash::set('error', $v->firstError());
            } else {
                Database::getInstance()->execute(
                    'UPDATE users SET password_hash = ? WHERE id = ?',
                    [password_hash($newPass, PASSWORD_DEFAULT), $user['id']]
                );
                $log->record((int) $user['id'], 'profile', 'Changed account password');
                Flash::set('success', 'Password changed successfully.');
            }
        }
    }
    header('Location: profile.php'); exit;
}

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>My Profile</h2>
    <p class="text-muted mb-0">Manage your account information and security settings.</p>
  </div>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="data-card p-4 text-center">
      <div class="avatar-sm mx-auto mb-3" style="width:80px;height:80px;font-size:2rem;"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
      <h5 class="mb-0"><?= htmlspecialchars($user['full_name']) ?></h5>
      <p class="text-muted small text-capitalize"><?= htmlspecialchars($user['role']) ?> account</p>
      <hr>
      <div class="text-start small">
        <p class="mb-1"><i class="bi bi-envelope text-gold me-2"></i><?= htmlspecialchars($user['email']) ?></p>
        <p class="mb-1"><i class="bi bi-person text-gold me-2"></i><?= htmlspecialchars($user['username']) ?></p>
        <p class="mb-1"><i class="bi bi-calendar text-gold me-2"></i>Joined <?= date('M j, Y', strtotime($user['created_at'])) ?></p>
        <?php if ($user['last_login']): ?>
          <p class="mb-0"><i class="bi bi-clock-history text-gold me-2"></i>Last login <?= date('M j, Y g:ia', strtotime($user['last_login'])) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="data-card mb-3">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Profile Information</h6></div>
      <div class="p-3">
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <input type="hidden" name="form_action" value="update_profile">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control form-control-bms" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control form-control-bms" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email <span class="text-muted small">(read-only)</span></label>
              <input type="email" class="form-control form-control-bms" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Username <span class="text-muted small">(read-only)</span></label>
              <input type="text" class="form-control form-control-bms" value="<?= htmlspecialchars($user['username']) ?>" disabled>
            </div>
          </div>
          <button class="btn btn-navy mt-3">Save Changes</button>
        </form>
      </div>
    </div>

    <div class="data-card">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Change Password</h6></div>
      <div class="p-3">
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <input type="hidden" name="form_action" value="change_password">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control form-control-bms" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control form-control-bms" minlength="8" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control form-control-bms" minlength="8" required>
            </div>
          </div>
          <button class="btn btn-gold mt-3">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
