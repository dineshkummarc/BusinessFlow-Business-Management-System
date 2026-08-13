<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Services — BusinessFlow';
require_once __DIR__ . '/includes/header.php';
?>

<header class="hero py-5">
  <div class="container text-center hero-content">
    <span class="hero-badge"><i class="bi bi-grid-3x3-gap"></i> What We Offer</span>
    <h1 style="font-size:2.4rem;">One Platform, Four Dedicated Modules</h1>
    <p class="lead mx-auto">Each service below is a fully separate module in the system — its own database tables, its own workspace, its own workflow.</p>
  </div>
</header>

<section class="section">
  <div class="container">

    <div class="row g-5 align-items-center mb-5 pb-4 border-bottom reveal">
      <div class="col-lg-1 text-center">
        <div class="icon-wrap mx-auto" style="width:70px;height:70px;font-size:1.8rem;"><i class="bi bi-people-fill"></i></div>
      </div>
      <div class="col-lg-11">
        <h3>Client Relationship Management</h3>
        <p class="text-muted">Capture every lead and account in a structured pipeline — company details, contact person, industry and status (lead, active, inactive). Search and filter your client base instantly, and keep a running log of notes for every relationship.</p>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge rounded-pill text-bg-light border">Lead tracking</span>
          <span class="badge rounded-pill text-bg-light border">Status pipeline</span>
          <span class="badge rounded-pill text-bg-light border">Searchable directory</span>
        </div>
      </div>
    </div>

    <div class="row g-5 align-items-center mb-5 pb-4 border-bottom reveal reveal-delay-1">
      <div class="col-lg-1 text-center">
        <div class="icon-wrap mx-auto" style="width:70px;height:70px;font-size:1.8rem;"><i class="bi bi-kanban-fill"></i></div>
      </div>
      <div class="col-lg-11">
        <h3>Project Management</h3>
        <p class="text-muted">Turn a client relationship into a tracked project with a budget, timeline, priority and live progress percentage. Assign employees to a project team and break work down into individual tasks with due dates and statuses.</p>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge rounded-pill text-bg-light border">Budgets &amp; deadlines</span>
          <span class="badge rounded-pill text-bg-light border">Team assignment</span>
          <span class="badge rounded-pill text-bg-light border">Task tracking</span>
        </div>
      </div>
    </div>

    <div class="row g-5 align-items-center mb-5 pb-4 border-bottom reveal reveal-delay-2">
      <div class="col-lg-1 text-center">
        <div class="icon-wrap mx-auto" style="width:70px;height:70px;font-size:1.8rem;"><i class="bi bi-receipt-cutoff"></i></div>
      </div>
      <div class="col-lg-11">
        <h3>Invoicing &amp; Billing</h3>
        <p class="text-muted">Generate invoices tied to a client and, optionally, a specific project. Add line items, apply tax automatically, and track each invoice through draft, sent, paid, overdue or cancelled status — with the totals rolling up into your revenue reports.</p>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge rounded-pill text-bg-light border">Line-item billing</span>
          <span class="badge rounded-pill text-bg-light border">Tax calculation</span>
          <span class="badge rounded-pill text-bg-light border">Payment status</span>
        </div>
      </div>
    </div>

    <div class="row g-5 align-items-center reveal reveal-delay-3">
      <div class="col-lg-1 text-center">
        <div class="icon-wrap mx-auto" style="width:70px;height:70px;font-size:1.8rem;"><i class="bi bi-person-workspace"></i></div>
      </div>
      <div class="col-lg-11">
        <h3>HR &amp; Staff Management</h3>
        <p class="text-muted">Maintain a full employee directory with department, designation and salary records, independent of login credentials. Employees can be assigned to project teams and individual tasks, connecting your workforce directly to the work they're doing.</p>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge rounded-pill text-bg-light border">Staff directory</span>
          <span class="badge rounded-pill text-bg-light border">Department tracking</span>
          <span class="badge rounded-pill text-bg-light border">Task assignment</span>
        </div>
      </div>
    </div>

  </div>
</section>

<section class="section section-alt pt-0">
  <div class="container">
    <div class="cta-band text-center reveal">
      <h2 class="text-white mb-3 position-relative">See Every Module in Action</h2>
      <p class="mx-auto position-relative" style="max-width:520px;color:#cdd8e4;">Sign in to the dashboard and explore each workspace for yourself.</p>
      <a href="register.php" class="btn btn-gold mt-3 position-relative">Create Free Account <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
