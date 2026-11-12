<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Contact Messages';
$activePage = 'messages';
$contactModel = new ContactMessage();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    if (Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        $contactModel->markRead((int) $_POST['id']);
    }
    header('Location: messages.php'); exit;
}

$messages = $contactModel->all();
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Contact Messages</h2>
    <p class="text-muted mb-0">Messages submitted through the public "Contact Us" form.</p>
  </div>
</div>

<div class="data-card">
  <div class="table-responsive">
    <table class="table table-bms">
      <thead><tr><th></th><th>Name</th><th>Email</th><th>Subject</th><th>Received</th><th></th></tr></thead>
      <tbody>
      <?php if (!$messages): ?>
        <tr><td colspan="6" class="text-center text-muted py-5">No messages yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($messages as $m): ?>
        <tr style="<?= $m['is_read'] ? '' : 'background:#fbf7ee;' ?>">
          <td><?php if (!$m['is_read']): ?><span class="badge-soft badge-soft-gold">New</span><?php endif; ?></td>
          <td class="fw-semibold"><?= htmlspecialchars($m['name']) ?></td>
          <td><?= htmlspecialchars($m['email']) ?></td>
          <td><?= htmlspecialchars($m['subject'] ?: '(no subject)') ?></td>
          <td class="text-muted small"><?= date('M j, Y g:ia', strtotime($m['created_at'])) ?></td>
          <td class="text-end">
            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#msgModal<?= $m['id'] ?>"><i class="bi bi-eye"></i></button>
          </td>
        </tr>

        <div class="modal fade" id="msgModal<?= $m['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><?= htmlspecialchars($m['subject'] ?: 'Message from ' . $m['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p class="small text-muted mb-1">From: <strong><?= htmlspecialchars($m['name']) ?></strong> &lt;<?= htmlspecialchars($m['email']) ?>&gt;</p>
                <p class="small text-muted">Received: <?= date('F j, Y g:ia', strtotime($m['created_at'])) ?></p>
                <hr>
                <p><?= nl2br(htmlspecialchars($m['message'])) ?></p>
              </div>
              <div class="modal-footer">
                <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="btn btn-navy btn-sm"><i class="bi bi-reply me-1"></i>Reply by Email</a>
                <?php if (!$m['is_read']): ?>
                <form method="POST">
                  <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                  <input type="hidden" name="mark_read" value="1">
                  <input type="hidden" name="id" value="<?= $m['id'] ?>">
                  <button class="btn btn-outline-navy btn-sm">Mark as Read</button>
                </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
