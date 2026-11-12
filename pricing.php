<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Pricing — BusinessFlow';
require_once __DIR__ . '/includes/header.php';
?>

<header class="hero py-5">
  <div class="container text-center hero-content">
    <span class="hero-badge"><i class="bi bi-tag"></i> Simple Pricing</span>
    <h1 style="font-size:2.4rem;">Plans That Grow With Your Team</h1>
    <p class="lead mx-auto">Start free while you're building out the system, upgrade when you need more room.</p>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="row g-4 justify-content-center">
      <div class="col-md-4 reveal reveal-delay-1">
        <div class="card-plan h-100">
          <h5 class="text-uppercase" style="letter-spacing:1px;color:var(--gray-500);">Starter</h5>
          <div class="my-3"><span class="display-5 fw-bold" style="color:var(--navy);">$0</span><span class="text-muted">/month</span></div>
          <p class="text-muted small">For solo operators getting the system set up.</p>
          <ul class="list-unstyled text-start mt-4 mb-4">
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Up to 10 clients</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>3 active projects</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Basic invoicing</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>1 team member</li>
          </ul>
          <a href="register.php" class="btn btn-outline-navy w-100">Start Free</a>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-2">
        <div class="card-plan featured h-100">
          <span class="badge-pop">MOST POPULAR</span>
          <h5 class="text-uppercase" style="letter-spacing:1px;color:var(--gray-500);">Professional</h5>
          <div class="my-3"><span class="display-5 fw-bold" style="color:var(--navy);">$49</span><span class="text-muted">/month</span></div>
          <p class="text-muted small">For growing teams managing multiple clients and projects.</p>
          <ul class="list-unstyled text-start mt-4 mb-4">
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Unlimited clients</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Unlimited projects</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Full invoicing suite</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Up to 15 team members</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>HR &amp; staff module</li>
          </ul>
          <a href="register.php" class="btn btn-gold w-100">Choose Professional</a>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-3">
        <div class="card-plan h-100">
          <h5 class="text-uppercase" style="letter-spacing:1px;color:var(--gray-500);">Enterprise</h5>
          <div class="my-3"><span class="display-5 fw-bold" style="color:var(--navy);">Custom</span></div>
          <p class="text-muted small">For established businesses needing tailored workflows.</p>
          <ul class="list-unstyled text-start mt-4 mb-4">
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Everything in Professional</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Unlimited team members</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Custom module development</li>
            <li class="mb-2"><i class="bi bi-check2 text-gold me-2"></i>Dedicated onboarding</li>
          </ul>
          <a href="contact.php" class="btn btn-outline-navy w-100">Contact Sales</a>
        </div>
      </div>
    </div>

    <div class="text-center mt-5 reveal">
      <p class="text-muted">Billing and subscription management is planned for a later phase — Phase 1 focuses on the core operational modules.</p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
