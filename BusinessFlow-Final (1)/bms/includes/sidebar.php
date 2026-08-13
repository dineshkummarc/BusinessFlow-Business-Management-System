<?php
/**
 * Dashboard sidebar navigation.
 * Expects $activePage to be set (e.g. 'index', 'clients', 'projects'...).
 */
$activePage = $activePage ?? '';
$isActive = function (string $page) use ($activePage) {
    return $activePage === $page ? 'active' : '';
};
?>
<div class="sidebar-overlay"></div>
<aside class="dash-sidebar">
  <div class="brand">
    <img src="<?= BASE_PATH ?>/assets/images/logo-mark.png" alt="Rana Umar logo" style="height:42px;">
    <div class="small mt-2" style="color:#9fb0c3;letter-spacing:1.5px;">RANA UMAR</div>
  </div>

  <div class="nav-section-label">Overview</div>
  <a href="<?= BASE_PATH ?>/dashboard/index.php" class="nav-link <?= $isActive('index') ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
  </a>

  <div class="nav-section-label">Modules</div>
  <a href="<?= BASE_PATH ?>/dashboard/clients.php" class="nav-link <?= $isActive('clients') ?>">
    <i class="bi bi-people-fill"></i> Clients
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/projects.php" class="nav-link <?= $isActive('projects') ?>">
    <i class="bi bi-kanban-fill"></i> Projects
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/tasks.php" class="nav-link <?= $isActive('tasks') ?>">
    <i class="bi bi-check2-square"></i> Tasks
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/calendar.php" class="nav-link <?= $isActive('calendar') ?>">
    <i class="bi bi-calendar3"></i> Calendar
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/invoices.php" class="nav-link <?= $isActive('invoices') ?>">
    <i class="bi bi-receipt-cutoff"></i> Invoices
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/expenses.php" class="nav-link <?= $isActive('expenses') ?>">
    <i class="bi bi-wallet2"></i> Expenses
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/employees.php" class="nav-link <?= $isActive('employees') ?>">
    <i class="bi bi-person-workspace"></i> HR &amp; Staff
  </a>

  <div class="nav-section-label">Communication</div>
  <a href="<?= BASE_PATH ?>/dashboard/messages.php" class="nav-link <?= $isActive('messages') ?>">
    <i class="bi bi-envelope-fill"></i> Contact Messages
    <?php if (!empty($unreadMessages)): ?><span class="nav-badge"><?= (int) $unreadMessages ?></span><?php endif; ?>
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/reports.php" class="nav-link <?= $isActive('reports') ?>">
    <i class="bi bi-bar-chart-fill"></i> Reports
  </a>

  <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
  <div class="nav-section-label">Administration</div>
  <a href="<?= BASE_PATH ?>/dashboard/users.php" class="nav-link <?= $isActive('users') ?>">
    <i class="bi bi-shield-lock-fill"></i> Manage Users
  </a>
  <a href="<?= BASE_PATH ?>/dashboard/settings.php" class="nav-link <?= $isActive('settings') ?>">
    <i class="bi bi-gear-fill"></i> Settings
  </a>
  <?php endif; ?>

  <div class="nav-section-label">Account</div>
  <a href="<?= BASE_PATH ?>/dashboard/profile.php" class="nav-link <?= $isActive('profile') ?>">
    <i class="bi bi-person-circle"></i> My Profile
  </a>
  <a href="<?= BASE_PATH ?>/index.php" class="nav-link">
    <i class="bi bi-box-arrow-left"></i> Visit Website
  </a>
  <a href="<?= BASE_PATH ?>/logout.php" class="nav-link text-danger" style="color:#f1a3a3 !important;">
    <i class="bi bi-power"></i> Logout
  </a>
  <div style="height:2rem;"></div>
</aside>
