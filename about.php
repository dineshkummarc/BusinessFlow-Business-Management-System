<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'About Us — BusinessFlow';
require_once __DIR__ . '/includes/header.php';
?>

<header class="hero py-5">
  <div class="container text-center hero-content">
    <span class="hero-badge"><i class="bi bi-building"></i> Our Story</span>
    <h1 style="font-size:2.4rem;">Built to Simplify How Businesses Operate</h1>
    <p class="lead mx-auto">A single, connected system replacing the spreadsheets, sticky notes and scattered tools most growing businesses rely on.</p>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6 reveal">
        <span class="section-label">Our Mission</span>
        <h2>Fewer Tools. More Clarity.</h2>
        <div class="divider-gold"></div>
        <p class="text-muted">Most businesses end up running on five different tools that don't talk to each other — a CRM here, a spreadsheet there, invoices in yet another app. BusinessFlow was built to bring client management, project delivery, billing and HR together under one roof, backed by a real relational database rather than disconnected files.</p>
        <p class="text-muted">Every module you see was designed around an actual business workflow: a lead becomes a client, a client gets a project, a project gets billed, and the whole thing is staffed by your team — all traceable from one dashboard.</p>
      </div>
      <div class="col-lg-6 reveal reveal-delay-1">
        <div class="row g-3">
          <div class="col-6">
            <div class="card-soft text-center">
              <div class="display-6 fw-bold" style="color:var(--navy);">04</div>
              <div class="text-muted small">Core Modules</div>
            </div>
          </div>
          <div class="col-6">
            <div class="card-soft text-center">
              <div class="display-6 fw-bold" style="color:var(--navy);">100%</div>
              <div class="text-muted small">Database Driven</div>
            </div>
          </div>
          <div class="col-6">
            <div class="card-soft text-center">
              <div class="display-6 fw-bold" style="color:var(--navy);">OOP</div>
              <div class="text-muted small">PHP Architecture</div>
            </div>
          </div>
          <div class="col-6">
            <div class="card-soft text-center">
              <div class="display-6 fw-bold" style="color:var(--navy);">MySQL</div>
              <div class="text-muted small">Relational Storage</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Our Principles</span>
      <h2 class="display-6">How We Build</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4 reveal reveal-delay-1">
        <div class="card-feature">
          <div class="icon-wrap"><i class="bi bi-puzzle"></i></div>
          <h5>Modular by Design</h5>
          <p class="text-muted mb-0">Every module is self-contained, so new functionality can be added later without breaking what already works.</p>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-2">
        <div class="card-feature">
          <div class="icon-wrap"><i class="bi bi-lock"></i></div>
          <h5>Security First</h5>
          <p class="text-muted mb-0">Hashed passwords, prepared SQL statements and session-based access control from the very first line of code.</p>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-3">
        <div class="card-feature">
          <div class="icon-wrap"><i class="bi bi-people"></i></div>
          <h5>Built for Teams</h5>
          <p class="text-muted mb-0">Role-based access means admins, managers and staff each see a dashboard suited to their responsibilities.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section pt-0">
  <div class="container">
    <div class="cta-band text-center reveal">
      <h2 class="text-white mb-3 position-relative">Curious How It Works?</h2>
      <p class="mx-auto position-relative" style="max-width:520px;color:#cdd8e4;">Create a free account and explore the live dashboard for yourself.</p>
      <a href="register.php" class="btn btn-gold mt-3 position-relative">Get Started <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
