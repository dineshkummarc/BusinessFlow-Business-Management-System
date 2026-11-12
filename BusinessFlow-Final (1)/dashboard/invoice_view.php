<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$invoiceModel = new Invoice();
$settings = new Settings();
$currency = $settings->get('currency_symbol', '$');
$id = (int) ($_GET['id'] ?? 0);
$invoice = $invoiceModel->find($id);
if (!$invoice) {
    header('Location: invoices.php');
    exit;
}
$items = $invoiceModel->items($id);

$pageTitle = $invoice['invoice_number'];
$activePage = 'invoices';
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <a href="invoices.php" class="text-muted"><i class="bi bi-arrow-left"></i></a>
      <h2 class="mb-0"><?= htmlspecialchars($invoice['invoice_number']) ?></h2>
      <?php $p = $invoice; include __DIR__ . '/../includes/badge_status.php'; ?>
    </div>
  </div>
  <button onclick="window.print()" class="btn btn-outline-navy btn-sm"><i class="bi bi-printer me-1"></i> Print</button>
</div>

<div class="data-card p-4">
  <div class="row mb-4">
    <div class="col-md-6">
      <img src="<?= BASE_PATH ?>/assets/images/logo-mark.png" style="height:52px;" alt="Rana Umar logo">
      <p class="text-muted small mt-2 mb-0">
        <strong><?= htmlspecialchars($settings->get('company_name', 'BusinessFlow')) ?></strong><br>
        <?php if ($settings->get('company_address')): ?><?= htmlspecialchars($settings->get('company_address')) ?><br><?php endif; ?>
        <?php if ($settings->get('company_email')): ?><?= htmlspecialchars($settings->get('company_email')) ?><?php endif; ?>
        <?php if ($settings->get('company_phone')): ?> · <?= htmlspecialchars($settings->get('company_phone')) ?><?php endif; ?>
      </p>
    </div>
    <div class="col-md-6 text-md-end">
      <h4 class="mb-1">Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></h4>
      <p class="text-muted mb-0">Issue Date: <?= date('F j, Y', strtotime($invoice['issue_date'])) ?></p>
      <p class="text-muted mb-0">Due Date: <?= date('F j, Y', strtotime($invoice['due_date'])) ?></p>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <h6 class="text-muted text-uppercase small" style="letter-spacing:1px;">Billed To</h6>
      <p class="mb-0 fw-semibold"><?= htmlspecialchars($invoice['company_name']) ?></p>
      <p class="mb-0"><?= htmlspecialchars($invoice['contact_person']) ?></p>
      <p class="mb-0 text-muted"><?= htmlspecialchars($invoice['email']) ?></p>
    </div>
  </div>

  <div class="table-responsive mb-4">
    <table class="table table-bms">
      <thead><tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['description']) ?></td>
            <td class="text-end"><?= rtrim(rtrim(number_format($it['quantity'],2),'0'),'.') ?></td>
            <td class="text-end"><?= $currency ?><?= number_format($it['unit_price'],2) ?></td>
            <td class="text-end"><?= $currency ?><?= number_format($it['line_total'],2) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$items): ?><tr><td colspan="4" class="text-center text-muted py-3">No line items.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="row justify-content-end">
    <div class="col-md-4">
      <div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span><?= $currency ?><?= number_format($invoice['amount'],2) ?></span></div>
      <div class="d-flex justify-content-between small mb-1"><span>Tax (<?= number_format($invoice['tax_percent'],1) ?>%)</span><span><?= $currency ?><?= number_format($invoice['total_amount'] - $invoice['amount'],2) ?></span></div>
      <hr>
      <div class="d-flex justify-content-between fw-bold fs-5"><span>Total Due</span><span><?= $currency ?><?= number_format($invoice['total_amount'],2) ?></span></div>
    </div>
  </div>

  <?php if (!empty($invoice['notes'])): ?>
    <div class="mt-4 pt-3 border-top">
      <h6 class="text-muted text-uppercase small" style="letter-spacing:1px;">Notes</h6>
      <p class="mb-0"><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
    </div>
  <?php endif; ?>
</div>

<style>@media print { .dash-sidebar, .dash-topbar, .page-header, footer, .sidebar-overlay { display:none !important; } .dash-main { margin-left:0 !important; } }</style>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
