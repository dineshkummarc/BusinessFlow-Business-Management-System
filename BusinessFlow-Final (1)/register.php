<?php
require_once __DIR__ . '/config/config.php';

if (Auth::isLoggedIn()) {
    header('Location: dashboard/index.php');
    exit;
}

$pageTitle = 'Create Account — BusinessFlow';
$errors = [];
$old = ['full_name' => '', 'email' => '', 'username' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $old['full_name'] = Validator::clean($_POST['full_name'] ?? '');
        $old['email']     = Validator::clean($_POST['email'] ?? '');
        $old['username']  = Validator::clean($_POST['username'] ?? '');
        $old['phone']     = Validator::clean($_POST['phone'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirmPassword  = $_POST['confirm_password'] ?? '';

        $v = new Validator();
        $v->required('full_name', $old['full_name'], 'Full name')
          ->required('email', $old['email'], 'Email')
          ->email('email', $old['email'], 'Email')
          ->required('username', $old['username'], 'Username')
          ->minLength('username', $old['username'], 4, 'Username')
          ->required('password', $password, 'Password')
          ->minLength('password', $password, 8, 'Password')
          ->matches('confirm_password', $confirmPassword, $password, 'Password confirmation');

        if ($v->fails()) {
            $errors[] = $v->firstError();
        } else {
            $auth = new Auth();
            $result = $auth->register([
                'full_name' => $old['full_name'],
                'email'     => $old['email'],
                'username'  => $old['username'],
                'phone'     => $old['phone'],
                'password'  => $password,
                'role'      => 'staff',
            ]);
            if ($result['success']) {
                $notificationModel = new Notification();
                $notificationModel->notifyAdmins(
                    'new_registration',
                    'New account registered',
                    "{$old['full_name']} ({$old['email']}) just created an account.",
                    'dashboard/users.php'
                );
                header('Location: dashboard/index.php');
                exit;
            }
            $errors[] = $result['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-wrapper">
  <div class="row g-0 w-100">
    <div class="col-lg-6 auth-side d-flex flex-column justify-content-center">
      <div class="position-relative" style="z-index:2;">
        <img src="<?= BASE_PATH ?>/assets/images/logo-full.png" height="130" class="mb-4" alt="Rana Umar logo">
        <h2 class="text-white">Start Managing Your Business Today</h2>
        <p style="color:#cdd8e4;" class="mb-4">Create your account and get instant access to clients, projects, invoicing and HR tools.</p>
        <ul class="list-unstyled" style="color:#cdd8e4;">
          <li class="mb-3"><i class="bi bi-check-circle-fill text-gold me-2"></i>No credit card required</li>
          <li class="mb-3"><i class="bi bi-check-circle-fill text-gold me-2"></i>Set up in under two minutes</li>
          <li class="mb-3"><i class="bi bi-check-circle-fill text-gold me-2"></i>Your data stays in your own database</li>
        </ul>
      </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center justify-content-center py-5">
      <div class="auth-card w-100" style="max-width:460px;">
        <h3 class="mb-1">Create Your Account</h3>
        <p class="text-muted mb-4">Fill in your details to get started.</p>

        <?php foreach ($errors as $e): ?>
          <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><div><?= htmlspecialchars($e) ?></div></div>
        <?php endforeach; ?>

        <form method="POST" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control form-control-bms" value="<?= htmlspecialchars($old['full_name']) ?>" required autofocus>
          </div>
          <div class="row g-3">
            <div class="col-md-6 mb-1">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control form-control-bms" value="<?= htmlspecialchars($old['email']) ?>" required>
            </div>
            <div class="col-md-6 mb-1">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control form-control-bms" value="<?= htmlspecialchars($old['username']) ?>" required minlength="4">
            </div>
          </div>
          <div class="mb-3 mt-3">
            <label class="form-label">Phone <span class="text-muted small">(optional)</span></label>
            <input type="text" name="phone" class="form-control form-control-bms" value="<?= htmlspecialchars($old['phone']) ?>">
          </div>
          <div class="row g-3">
            <div class="col-md-6 mb-1">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control form-control-bms" required minlength="8">
            </div>
            <div class="col-md-6 mb-1">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control form-control-bms" required minlength="8">
            </div>
          </div>
          <div class="form-check my-3">
            <input class="form-check-input" type="checkbox" id="terms" required>
            <label class="form-check-label small" for="terms">I agree to the Terms of Service and Privacy Policy</label>
          </div>
          <button type="submit" class="btn btn-gold w-100">Create Account <i class="bi bi-person-plus ms-1"></i></button>
        </form>

        <p class="text-center text-muted mt-4 mb-0">Already have an account? <a href="login.php" class="text-gold fw-semibold">Sign in</a></p>
        <p class="text-center mt-2"><a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to homepage</a></p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
