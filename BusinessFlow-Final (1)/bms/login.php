<?php
require_once __DIR__ . '/config/config.php';

if (Auth::isLoggedIn()) {
    header('Location: dashboard/index.php');
    exit;
}

$pageTitle = 'Login — BusinessFlow';
$errors = [];
$oldLogin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $oldLogin = Validator::clean($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        $v = new Validator();
        $v->required('login', $oldLogin, 'Username or Email')
          ->required('password', $password, 'Password');

        if ($v->fails()) {
            $errors[] = $v->firstError();
        } else {
            $auth = new Auth();
            $result = $auth->attemptLogin($oldLogin, $password, !empty($_POST['remember']));
            if ($result['success']) {
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
        <h2 class="text-white">Welcome Back to Your Business Dashboard</h2>
        <p style="color:#cdd8e4;" class="mb-4">Sign in to manage your clients, projects, invoices and team — all in one place.</p>
        <ul class="list-unstyled" style="color:#cdd8e4;">
          <li class="mb-3"><i class="bi bi-check-circle-fill text-gold me-2"></i>Real-time overview of your business</li>
          <li class="mb-3"><i class="bi bi-check-circle-fill text-gold me-2"></i>Secure, role-based access</li>
          <li class="mb-3"><i class="bi bi-check-circle-fill text-gold me-2"></i>All your modules in one dashboard</li>
        </ul>
      </div>
    </div>
    <div class="col-lg-6 d-flex align-items-center justify-content-center py-5">
      <div class="auth-card w-100" style="max-width:420px;">
        <h3 class="mb-1">Sign In</h3>
        <p class="text-muted mb-4">Enter your credentials to access your dashboard.</p>

        <?php foreach ($errors as $e): ?>
          <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><div><?= htmlspecialchars($e) ?></div></div>
        <?php endforeach; ?>

        <div class="alert alert-light border small">
          <strong>Demo account:</strong> username <code>admin</code>. Set the password by following the README (Phase 1 seed data uses a placeholder hash).
        </div>

        <form method="POST" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
          <div class="mb-3">
            <label class="form-label">Username or Email</label>
            <input type="text" name="login" class="form-control form-control-bms" value="<?= htmlspecialchars($oldLogin) ?>" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="password" id="loginPassword" class="form-control form-control-bms" required>
              <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#loginPassword"><i class="bi bi-eye"></i></button>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" id="remember">
              <label class="form-check-label small" for="remember">Remember me</label>
            </div>
          </div>
          <button type="submit" class="btn btn-gold w-100">Sign In <i class="bi bi-box-arrow-in-right ms-1"></i></button>
        </form>

        <p class="text-center text-muted mt-4 mb-0">Don't have an account? <a href="register.php" class="text-gold fw-semibold">Create one</a></p>
        <p class="text-center mt-2"><a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to homepage</a></p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
