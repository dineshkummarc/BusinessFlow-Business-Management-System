<?php
/**
 * Public site header — included on every marketing page.
 * Expects (optionally) $pageTitle to be set before including.
 */
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="BusinessFlow — a complete business management system for clients, projects, invoicing and HR.">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
<!-- Site styles -->
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
<link rel="icon" href="<?= BASE_PATH ?>/assets/images/favicon-32.png" type="image/png">
<link rel="apple-touch-icon" href="<?= BASE_PATH ?>/assets/images/favicon-180.png">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-bms">
  <div class="container">
    <a class="navbar-brand logo-mark d-flex align-items-center gap-2" href="<?= BASE_PATH ?>/index.php">
      <img src="<?= BASE_PATH ?>/assets/images/logo-mark.png" alt="Rana Umar logo" style="height:46px;">
      <span class="d-none d-sm-inline fw-semibold" style="color:var(--navy);font-family:'Poppins',sans-serif;letter-spacing:0.5px;">RANA UMAR</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_PATH ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_PATH ?>/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_PATH ?>/services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_PATH ?>/pricing.php">Pricing</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_PATH ?>/contact.php">Contact</a></li>
        <?php if (Auth::isLoggedIn()): ?>
          <li class="nav-item ms-lg-3">
            <a class="btn btn-outline-navy btn-sm" href="<?= BASE_PATH ?>/dashboard/index.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
          </li>
        <?php else: ?>
          <li class="nav-item ms-lg-2"><a class="nav-link" href="<?= BASE_PATH ?>/login.php">Login</a></li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-gold btn-sm" href="<?= BASE_PATH ?>/register.php">Get Started</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
