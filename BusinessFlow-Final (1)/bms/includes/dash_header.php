<?php
/**
 * Dashboard layout header.
 * Requires: Auth::requireLogin() already called, $pageTitle, $activePage set.
 */
$contactModel = $contactModel ?? new ContactMessage();
$unreadMessages = $contactModel->countUnread();
$notificationModel = new Notification();
$myNotifications = $notificationModel->forUser((int) $_SESSION['user_id'], 8);
$unreadNotifications = $notificationModel->countUnread((int) $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — BusinessFlow</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/dashboard.css">
<link rel="icon" href="<?= BASE_PATH ?>/assets/images/favicon-32.png" type="image/png">
<link rel="apple-touch-icon" href="<?= BASE_PATH ?>/assets/images/favicon-180.png">
</head>
<body class="dash-body">

<?php require_once __DIR__ . '/sidebar.php'; ?>

<div class="dash-main">
  <div class="dash-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-light sidebar-toggle-btn"><i class="bi bi-list fs-5"></i></button>
      <div class="search-box d-none d-sm-flex position-relative">
        <i class="bi bi-search text-muted"></i>
        <input type="text" id="quickNavInput" placeholder="Jump to a module..." autocomplete="off">
        <div id="quickNavResults" class="quick-nav-results"></div>
      </div>
    </div>
    <div class="d-flex align-items-center gap-3">
      <div class="dropdown">
        <a href="#" class="position-relative text-dark" title="Notifications" data-bs-toggle="dropdown">
          <i class="bi bi-bell fs-5"></i>
          <?php if ($unreadNotifications > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;"><?= $unreadNotifications ?></span>
          <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width:340px;">
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <span class="fw-semibold small">Notifications</span>
            <?php if ($unreadNotifications > 0): ?>
              <form method="POST" action="<?= BASE_PATH ?>/dashboard/notifications_read.php">
                <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <button class="btn btn-link btn-sm p-0 text-gold" style="font-size:0.78rem;">Mark all read</button>
              </form>
            <?php endif; ?>
          </div>
          <div style="max-height:340px;overflow-y:auto;">
            <?php if (!$myNotifications): ?>
              <p class="text-muted small text-center py-4 mb-0">You're all caught up.</p>
            <?php endif; ?>
            <?php foreach ($myNotifications as $n): ?>
              <a href="<?= BASE_PATH . '/' . ltrim($n['link'] ?: 'dashboard/index.php', '/') ?>" class="dropdown-item py-2 border-bottom <?= $n['is_read'] ? '' : 'bg-light' ?>" style="white-space:normal;">
                <div class="small fw-semibold"><?= htmlspecialchars($n['title']) ?></div>
                <?php if ($n['message']): ?><div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($n['message']) ?></div><?php endif; ?>
                <div class="text-muted" style="font-size:0.7rem;"><?= date('M j, g:ia', strtotime($n['created_at'])) ?></div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="dropdown">
        <a class="d-flex align-items-center gap-2 text-decoration-none text-dark dropdown-toggle" href="#" data-bs-toggle="dropdown">
          <div class="avatar-sm"><?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?></div>
          <span class="d-none d-md-inline fw-semibold small"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
          <li><span class="dropdown-item-text small text-muted text-capitalize"><?= htmlspecialchars($_SESSION['role'] ?? '') ?> account</span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= BASE_PATH ?>/dashboard/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
          <li><a class="dropdown-item" href="<?= BASE_PATH ?>/index.php"><i class="bi bi-globe me-2"></i>Visit Website</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= BASE_PATH ?>/logout.php"><i class="bi bi-power me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="dash-content">
