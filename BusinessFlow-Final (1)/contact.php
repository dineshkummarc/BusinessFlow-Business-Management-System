<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Contact Us — BusinessFlow';

$success = '';
$errors  = [];
$old     = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please try submitting the form again.';
    } else {
        $old['name']    = Validator::clean($_POST['name'] ?? '');
        $old['email']   = Validator::clean($_POST['email'] ?? '');
        $old['subject'] = Validator::clean($_POST['subject'] ?? '');
        $old['message'] = Validator::clean($_POST['message'] ?? '');

        $v = new Validator();
        $v->required('name', $old['name'], 'Name')
          ->required('email', $old['email'], 'Email')
          ->email('email', $old['email'], 'Email')
          ->required('message', $old['message'], 'Message')
          ->minLength('message', $old['message'], 10, 'Message');

        if ($v->fails()) {
            $errors[] = $v->firstError();
        } else {
            $contactModel = new ContactMessage();
            $newMsgId = $contactModel->create($old);
            $notificationModel = new Notification();
            $notificationModel->notifyAdmins(
                'contact_message',
                'New contact message',
                "From {$old['name']}: " . mb_strimwidth($old['message'], 0, 80, '...'),
                'dashboard/messages.php'
            );
            $success = "Thanks, {$old['name']}! Your message has been received — we'll get back to you soon.";
            $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<header class="hero py-5">
  <div class="container text-center hero-content">
    <span class="hero-badge"><i class="bi bi-chat-dots"></i> Let's Talk</span>
    <h1 style="font-size:2.4rem;">Get in Touch</h1>
    <p class="lead mx-auto">Questions about the platform or interested in a custom module? Send a message below.</p>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5 reveal">
        <span class="section-label">Contact Details</span>
        <h3 class="mb-4">We'd Love to Hear From You</h3>
        <div class="d-flex gap-3 mb-4">
          <div class="icon-wrap" style="width:48px;height:48px;font-size:1.1rem;flex-shrink:0;"><i class="bi bi-envelope"></i></div>
          <div>
            <h6 class="mb-1">Email</h6>
            <p class="text-muted mb-0"><?= DEV_EMAIL ?></p>
          </div>
        </div>
        <div class="d-flex gap-3 mb-4">
          <div class="icon-wrap" style="width:48px;height:48px;font-size:1.1rem;flex-shrink:0;"><i class="bi bi-linkedin"></i></div>
          <div>
            <h6 class="mb-1">LinkedIn</h6>
            <p class="text-muted mb-0"><a href="<?= DEV_LINKEDIN ?>" class="text-decoration-none" target="_blank" rel="noopener">Connect with the developer</a></p>
          </div>
        </div>
        <div class="d-flex gap-3">
          <div class="icon-wrap" style="width:48px;height:48px;font-size:1.1rem;flex-shrink:0;"><i class="bi bi-github"></i></div>
          <div>
            <h6 class="mb-1">GitHub</h6>
            <p class="text-muted mb-0"><a href="<?= DEV_GITHUB ?>" class="text-decoration-none" target="_blank" rel="noopener">View source &amp; project history</a></p>
          </div>
        </div>
      </div>

      <div class="col-lg-7 reveal reveal-delay-1">
        <div class="card-soft">
          <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
              <i class="bi bi-check-circle-fill"></i><div><?= $success ?></div>
            </div>
          <?php endif; ?>
          <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
              <i class="bi bi-exclamation-triangle-fill"></i><div><?= htmlspecialchars($e) ?></div>
            </div>
          <?php endforeach; ?>

          <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control form-control-bms" value="<?= $old['name'] ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control form-control-bms" value="<?= $old['email'] ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control form-control-bms" value="<?= $old['subject'] ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Message</label>
                <textarea name="message" rows="5" class="form-control form-control-bms" required minlength="10"><?= $old['message'] ?></textarea>
              </div>
              <div class="col-12">
                <button type="submit" name="contact_submit" class="btn btn-gold">Send Message <i class="bi bi-send ms-1"></i></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
