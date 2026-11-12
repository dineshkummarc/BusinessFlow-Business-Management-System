<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'BusinessFlow — Business Management System';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ================= HERO ================= -->
<header class="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7 hero-content">
        <span class="hero-badge"><i class="bi bi-stars"></i> All-in-one business platform</span>
        <h1>Run Your Entire Business From One Powerful Dashboard</h1>
        <p class="lead my-4">BusinessFlow brings clients, projects, invoicing and your team together in one clean, professional system — so you spend less time juggling tools and more time growing.</p>
        <div class="d-flex flex-wrap gap-3">
          <a href="register.php" class="btn btn-gold">Get Started Free <i class="bi bi-arrow-right ms-1"></i></a>
          <a href="#features" class="btn btn-outline-navy" style="border-color:#ffffff55;color:#fff;">Explore Features</a>
        </div>
        <div class="hero-stats">
          <div>
            <div class="stat-num" data-count="500" data-suffix="+">0</div>
            <div class="stat-label">Businesses Onboarded</div>
          </div>
          <div>
            <div class="stat-num" data-count="12000" data-suffix="+">0</div>
            <div class="stat-label">Invoices Processed</div>
          </div>
          <div>
            <div class="stat-num" data-count="99" data-suffix="%">0</div>
            <div class="stat-label">Uptime Reliability</div>
          </div>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block">
        <div class="reveal">
          <div class="card-soft" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.14);backdrop-filter:blur(6px);">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="text-white fw-semibold"><i class="bi bi-graph-up-arrow text-gold me-2"></i>Revenue Overview</span>
              <span class="badge bg-success bg-opacity-25 text-success">+18.4%</span>
            </div>
            <div class="d-flex align-items-end gap-2" style="height:120px;">
              <div style="width:14%;height:45%;background:#c9a15a55;border-radius:6px 6px 0 0;"></div>
              <div style="width:14%;height:65%;background:#c9a15a77;border-radius:6px 6px 0 0;"></div>
              <div style="width:14%;height:40%;background:#c9a15a55;border-radius:6px 6px 0 0;"></div>
              <div style="width:14%;height:85%;background:#c9a15aaa;border-radius:6px 6px 0 0;"></div>
              <div style="width:14%;height:60%;background:#c9a15a77;border-radius:6px 6px 0 0;"></div>
              <div style="width:14%;height:100%;background:var(--gold);border-radius:6px 6px 0 0;"></div>
            </div>
            <hr style="border-color:rgba(255,255,255,0.15);">
            <div class="row text-white text-center g-2">
              <div class="col-4">
                <div class="fw-bold">128</div>
                <small style="color:#9fb0c3;">Clients</small>
              </div>
              <div class="col-4">
                <div class="fw-bold">47</div>
                <small style="color:#9fb0c3;">Projects</small>
              </div>
              <div class="col-4">
                <div class="fw-bold">$84K</div>
                <small style="color:#9fb0c3;">This Month</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- ================= TRUSTED BY ================= -->
<section class="py-5 border-bottom">
  <div class="container">
    <p class="text-center text-uppercase small fw-semibold" style="letter-spacing:2px;color:var(--gray-500);">Trusted by growing teams across industries</p>
    <div class="row text-center g-4 mt-2 align-items-center opacity-75">
      <div class="col-6 col-md-2 fw-bold" style="color:var(--navy);">RETAIL<span style="color:var(--gold);">CO</span></div>
      <div class="col-6 col-md-2 fw-bold" style="color:var(--navy);">VERTEX<span style="color:var(--gold);">MFG</span></div>
      <div class="col-6 col-md-2 fw-bold" style="color:var(--navy);">BRIGHT<span style="color:var(--gold);">PATH</span></div>
      <div class="col-6 col-md-2 fw-bold" style="color:var(--navy);">NORTH<span style="color:var(--gold);">GATE</span></div>
      <div class="col-6 col-md-2 fw-bold" style="color:var(--navy);">APEX<span style="color:var(--gold);">GRP</span></div>
      <div class="col-6 col-md-2 fw-bold" style="color:var(--navy);">STUDIO<span style="color:var(--gold);">99</span></div>
    </div>
  </div>
</section>

<!-- ================= FEATURES ================= -->
<section class="section" id="features">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Core Modules</span>
      <h2 class="display-6">Everything Your Business Needs</h2>
      <p class="text-muted mx-auto" style="max-width:600px;">Four purpose-built modules working together — each with its own dedicated workspace, not a single generic screen pretending to do everything.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3 reveal reveal-delay-1">
        <div class="card-feature">
          <div class="icon-wrap"><i class="bi bi-people-fill"></i></div>
          <h5>Client CRM</h5>
          <p class="text-muted mb-0">Track leads, active accounts and full contact history in one organized pipeline.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal reveal-delay-2">
        <div class="card-feature">
          <div class="icon-wrap"><i class="bi bi-kanban-fill"></i></div>
          <h5>Project Management</h5>
          <p class="text-muted mb-0">Plan timelines, assign teams and monitor progress from kickoff to delivery.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal reveal-delay-3">
        <div class="card-feature">
          <div class="icon-wrap"><i class="bi bi-receipt-cutoff"></i></div>
          <h5>Invoicing &amp; Billing</h5>
          <p class="text-muted mb-0">Generate professional invoices, track payments and stay on top of revenue.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal reveal-delay-4">
        <div class="card-feature">
          <div class="icon-wrap"><i class="bi bi-person-workspace"></i></div>
          <h5>HR &amp; Staff</h5>
          <p class="text-muted mb-0">Manage employee records, departments, roles and team assignments.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= ABOUT / WHY US ================= -->
<section class="section section-alt">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 reveal">
        <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow" style="background:linear-gradient(135deg,var(--navy),var(--navy-light));">
          <div class="d-flex flex-column justify-content-center align-items-start p-5 text-white">
            <i class="bi bi-bar-chart-line display-3 text-gold mb-3"></i>
            <h4 class="text-white">Built for Real Business Operations</h4>
            <p style="color:#cdd8e4;">Not a template — a functioning system with real database-backed workflows.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-6 reveal reveal-delay-1">
        <span class="section-label">Why BusinessFlow</span>
        <h2 class="mb-3">Small Footprint. Serious Capability.</h2>
        <div class="divider-gold"></div>
        <p class="text-muted mb-4">BusinessFlow was designed for teams who need dependable, no-nonsense tools — not bloated software. Every module is connected to a real relational database, secured with proper authentication, and built to scale as your business grows.</p>
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-check-circle-fill text-gold fs-5"></i>
              <span>Secure role-based access</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-check-circle-fill text-gold fs-5"></i>
              <span>Real-time dashboard insights</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-check-circle-fill text-gold fs-5"></i>
              <span>Relational MySQL database</span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-check-circle-fill text-gold fs-5"></i>
              <span>Clean, modular OOP codebase</span>
            </div>
          </div>
        </div>
        <a href="about.php" class="btn btn-navy mt-4">More About Us</a>
      </div>
    </div>
  </div>
</section>

<!-- ================= SERVICES PREVIEW ================= -->
<section class="section">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">What We Offer</span>
      <h2 class="display-6">Services Built Around Your Workflow</h2>
    </div>
    <div class="row g-4">
      <div class="col-lg-4 reveal reveal-delay-1">
        <div class="card-soft h-100">
          <i class="bi bi-diagram-3 fs-2 text-gold mb-3"></i>
          <h5>Business Process Setup</h5>
          <p class="text-muted mb-0">We configure the platform around your existing operational structure so adoption is fast and painless.</p>
        </div>
      </div>
      <div class="col-lg-4 reveal reveal-delay-2">
        <div class="card-soft h-100">
          <i class="bi bi-shield-check fs-2 text-gold mb-3"></i>
          <h5>Secure Data Management</h5>
          <p class="text-muted mb-0">Passwords are hashed, inputs sanitized and every module protected by session-based authentication.</p>
        </div>
      </div>
      <div class="col-lg-4 reveal reveal-delay-3">
        <div class="card-soft h-100">
          <i class="bi bi-graph-up fs-2 text-gold mb-3"></i>
          <h5>Growth-Ready Architecture</h5>
          <p class="text-muted mb-0">A modular structure means new modules can be added in later phases without disrupting existing ones.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= TESTIMONIALS ================= -->
<section class="section section-alt">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Client Feedback</span>
      <h2 class="display-6">What Teams Are Saying</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4 reveal reveal-delay-1">
        <div class="testimonial-card">
          <div class="quote-icon mb-2">&ldquo;</div>
          <p class="text-muted">Since switching to this system, our project handoffs are finally organized. Everything lives in one place.</p>
          <div class="d-flex align-items-center gap-3 mt-4">
            <div class="avatar-circle">SC</div>
            <div>
              <div class="fw-semibold">Sarah Collins</div>
              <small class="text-muted">Northgate Retail Group</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-2">
        <div class="testimonial-card">
          <div class="quote-icon mb-2">&ldquo;</div>
          <p class="text-muted">Invoicing used to take us hours every month. Now it's a matter of minutes, with a clear record for every client.</p>
          <div class="d-flex align-items-center gap-3 mt-4">
            <div class="avatar-circle">DO</div>
            <div>
              <div class="fw-semibold">David Okafor</div>
              <small class="text-muted">Vertex Manufacturing</small>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal reveal-delay-3">
        <div class="testimonial-card">
          <div class="quote-icon mb-2">&ldquo;</div>
          <p class="text-muted">The HR and project modules being separate but connected made onboarding our new managers incredibly simple.</p>
          <div class="d-flex align-items-center gap-3 mt-4">
            <div class="avatar-circle">MG</div>
            <div>
              <div class="fw-semibold">Maria Gomez</div>
              <small class="text-muted">BrightPath Logistics</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= CTA ================= -->
<section class="section pt-0">
  <div class="container">
    <div class="cta-band text-center reveal">
      <h2 class="text-white mb-3 position-relative">Ready to Bring Order to Your Business?</h2>
      <p class="mx-auto position-relative" style="max-width:520px;color:#cdd8e4;">Create your account and get access to the full dashboard — clients, projects, invoices and your team, all in one place.</p>
      <a href="register.php" class="btn btn-gold mt-3 position-relative">Create Free Account <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
