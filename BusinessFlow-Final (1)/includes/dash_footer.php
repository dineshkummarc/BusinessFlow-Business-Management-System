  </div><!-- /.dash-content -->

  <footer class="text-center text-muted small py-4" style="border-top:1px solid #e7eaef;">
    &copy; <?= date('Y') ?> BusinessFlow — Business Management System. Developed by <?= DEV_NAME ?>.
  </footer>
</div><!-- /.dash-main -->

<script>
  // Phase 3 — quick-navigation index for the topbar search box.
  // Mirrors the sidebar exactly, including role-based visibility,
  // so typing never surfaces a page the current user can't open.
  window.BMS_BASE_PATH = <?= json_encode(BASE_PATH) ?>;
  window.BMS_NAV_INDEX = [
    { label: 'Dashboard Overview', icon: 'bi-speedometer2', url: 'dashboard/index.php' },
    { label: 'Clients (CRM)', icon: 'bi-people-fill', url: 'dashboard/clients.php' },
    { label: 'Projects', icon: 'bi-kanban-fill', url: 'dashboard/projects.php' },
    { label: 'Tasks', icon: 'bi-check2-square', url: 'dashboard/tasks.php' },
    { label: 'Calendar', icon: 'bi-calendar3', url: 'dashboard/calendar.php' },
    { label: 'Invoices', icon: 'bi-receipt-cutoff', url: 'dashboard/invoices.php' },
    { label: 'Expenses', icon: 'bi-wallet2', url: 'dashboard/expenses.php' },
    { label: 'HR & Staff', icon: 'bi-person-workspace', url: 'dashboard/employees.php' },
    { label: 'Contact Messages', icon: 'bi-envelope-fill', url: 'dashboard/messages.php' },
    { label: 'Reports', icon: 'bi-bar-chart-fill', url: 'dashboard/reports.php' },
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
    { label: 'Manage Users', icon: 'bi-shield-lock-fill', url: 'dashboard/users.php' },
    { label: 'Settings', icon: 'bi-gear-fill', url: 'dashboard/settings.php' },
    <?php endif; ?>
    { label: 'My Profile', icon: 'bi-person-circle', url: 'dashboard/profile.php' },
  ];
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_PATH ?>/assets/js/dashboard.js"></script>
</body>
</html>
