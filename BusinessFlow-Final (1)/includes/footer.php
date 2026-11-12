<footer class="footer-bms">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <img src="<?= BASE_PATH ?>/assets/images/logo-full.png" alt="Rana Umar logo" style="height:100px;" class="mb-3">
        <p class="pe-lg-4">A complete business management platform to run clients, projects, invoicing and HR from one professional dashboard.</p>
        <div class="mt-3">
          <a href="<?= DEV_LINKEDIN ?>" class="social-icon" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="<?= DEV_INSTAGRAM ?>" class="social-icon" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="<?= DEV_GITHUB ?>" class="social-icon" target="_blank" rel="noopener" aria-label="GitHub"><i class="bi bi-github"></i></a>
          <a href="mailto:<?= DEV_EMAIL ?>" class="social-icon" aria-label="Email"><i class="bi bi-envelope-fill"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-6">
        <h5>Company</h5>
        <a href="<?= BASE_PATH ?>/about.php" class="footer-link mb-2">About Us</a><br>
        <a href="<?= BASE_PATH ?>/services.php" class="footer-link mb-2">Services</a><br>
        <a href="<?= BASE_PATH ?>/pricing.php" class="footer-link mb-2">Pricing</a><br>
        <a href="<?= BASE_PATH ?>/contact.php" class="footer-link mb-2">Contact</a>
      </div>
      <div class="col-lg-2 col-6">
        <h5>Modules</h5>
        <a href="<?= BASE_PATH ?>/login.php" class="footer-link mb-2">Client CRM</a><br>
        <a href="<?= BASE_PATH ?>/login.php" class="footer-link mb-2">Projects</a><br>
        <a href="<?= BASE_PATH ?>/login.php" class="footer-link mb-2">Invoicing</a><br>
        <a href="<?= BASE_PATH ?>/login.php" class="footer-link mb-2">HR &amp; Staff</a>
      </div>
      <div class="col-lg-4">
        <h5>Get in Touch</h5>
        <p class="mb-2"><i class="bi bi-envelope me-2 text-gold"></i><?= DEV_EMAIL ?></p>
        <p class="mb-2"><i class="bi bi-geo-alt me-2 text-gold"></i>Available for remote &amp; on-site engagements</p>
        <p class="mb-0"><i class="bi bi-person-badge me-2 text-gold"></i>Built by <?= DEV_NAME ?></p>
      </div>
    </div>
    <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center">
      <span>&copy; <?= date('Y') ?> BusinessFlow. All rights reserved.</span>
      <span>Developed by <?= DEV_NAME ?></span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_PATH ?>/assets/js/main.js"></script>
</body>
</html>
