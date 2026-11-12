<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireRole(['admin']);

$pageTitle = 'Settings';
$activePage = 'settings';
$settings = new Settings();
$log = new ActivityLog();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
        Flash::set('error', 'Your session expired. Please try again.');
        header('Location: settings.php'); exit;
    }

    $data = [
        'company_name'        => Validator::clean($_POST['company_name'] ?? ''),
        'company_email'       => Validator::clean($_POST['company_email'] ?? ''),
        'company_phone'       => Validator::clean($_POST['company_phone'] ?? ''),
        'company_address'     => Validator::clean($_POST['company_address'] ?? ''),
        'currency_symbol'     => Validator::clean($_POST['currency_symbol'] ?? '$'),
        'default_tax_percent' => Validator::clean($_POST['default_tax_percent'] ?? '0'),
        'timezone'            => Validator::clean($_POST['timezone'] ?? 'UTC'),
        'invoice_prefix'      => Validator::clean($_POST['invoice_prefix'] ?? 'INV'),
    ];

    $v = new Validator();
    $v->required('company_name', $data['company_name'], 'Company name')
      ->numeric('default_tax_percent', $data['default_tax_percent'], 'Default tax percent')
      ->range('default_tax_percent', $data['default_tax_percent'], 0, 100, 'Default tax percent');

    if ($v->fails()) {
        Flash::set('error', $v->firstError());
    } else {
        $settings->setMany($data);
        $log->record((int) $_SESSION['user_id'], 'settings', 'Updated company settings');
        Flash::set('success', 'Settings saved successfully.');
    }
    header('Location: settings.php'); exit;
}

$s = $settings->all();
require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Settings</h2>
    <p class="text-muted mb-0">Company-wide configuration used throughout the system (invoices, currency display, defaults).</p>
  </div>
</div>

<?php if ($msg = Flash::get('success')): ?>
  <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Flash::get('error')): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="data-card">
  <div class="data-card-head"><h6 class="mb-0 fw-semibold">Company Profile</h6></div>
  <div class="p-4">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Company Name *</label>
          <input type="text" name="company_name" class="form-control form-control-bms" value="<?= htmlspecialchars($s['company_name'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Company Email</label>
          <input type="email" name="company_email" class="form-control form-control-bms" value="<?= htmlspecialchars($s['company_email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Company Phone</label>
          <input type="text" name="company_phone" class="form-control form-control-bms" value="<?= htmlspecialchars($s['company_phone'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Timezone</label>
          <select name="timezone" class="form-select form-control-bms">
            <?php foreach (['UTC','America/New_York','America/Chicago','America/Los_Angeles','Europe/London','Asia/Karachi','Asia/Dubai','Asia/Kolkata'] as $tz): ?>
              <option value="<?= $tz ?>" <?= ($s['timezone'] ?? 'UTC')===$tz?'selected':'' ?>><?= $tz ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Company Address</label>
          <textarea name="company_address" rows="2" class="form-control form-control-bms"><?= htmlspecialchars($s['company_address'] ?? '') ?></textarea>
        </div>
      </div>

      <hr class="my-4">
      <h6 class="fw-semibold mb-3">Billing Defaults</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Currency Symbol</label>
          <input type="text" name="currency_symbol" class="form-control form-control-bms" value="<?= htmlspecialchars($s['currency_symbol'] ?? '$') ?>" maxlength="5">
        </div>
        <div class="col-md-4">
          <label class="form-label">Default Tax Rate (%)</label>
          <input type="number" step="0.01" min="0" max="100" name="default_tax_percent" class="form-control form-control-bms" value="<?= htmlspecialchars($s['default_tax_percent'] ?? '0') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Invoice Number Prefix</label>
          <input type="text" name="invoice_prefix" class="form-control form-control-bms" value="<?= htmlspecialchars($s['invoice_prefix'] ?? 'INV') ?>" maxlength="10">
        </div>
      </div>

      <button class="btn btn-gold mt-4">Save Settings</button>
    </form>
  </div>
</div>

<div class="data-card mt-3">
  <div class="data-card-head"><h6 class="mb-0 fw-semibold">System Information</h6></div>
  <div class="p-4 row g-3 small text-muted">
    <div class="col-md-4"><strong class="text-dark d-block">Stack</strong>PHP (OOP) + MySQL + Bootstrap 5</div>
    <div class="col-md-4"><strong class="text-dark d-block">Current Phase</strong>Phase 2</div>
    <div class="col-md-4"><strong class="text-dark d-block">Developer</strong><?= DEV_NAME ?></div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
