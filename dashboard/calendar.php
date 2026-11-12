<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();

$pageTitle = 'Calendar';
$activePage = 'calendar';
$taskModel = new Task();
$db = Database::getInstance();
new Invoice(); // Phase 3: trigger the overdue-status auto-check even on pages that query invoices directly.

$month = (int) ($_GET['month'] ?? date('n'));
$year = (int) ($_GET['year'] ?? date('Y'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstOfMonth = sprintf('%04d-%02d-01', $year, $month);
$daysInMonth = (int) date('t', strtotime($firstOfMonth));
$startWeekday = (int) date('w', strtotime($firstOfMonth)); // 0=Sun
$lastDay = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

// Pull tasks due this month
$tasksThisMonth = $taskModel->dueBetween($firstOfMonth, $lastDay);

// Pull project deadlines this month
$projectDeadlines = $db->select(
    "SELECT id, project_name, deadline, status FROM projects WHERE deadline BETWEEN ? AND ?",
    [$firstOfMonth, $lastDay]
);

// Pull invoice due dates this month
$invoiceDueDates = $db->select(
    "SELECT i.id, i.invoice_number, i.due_date, i.status, c.company_name
     FROM invoices i JOIN clients c ON c.id = i.client_id
     WHERE i.due_date BETWEEN ? AND ? AND i.status NOT IN ('paid','cancelled')",
    [$firstOfMonth, $lastDay]
);

// Group all events by day number
$eventsByDay = [];
foreach ($tasksThisMonth as $t) {
    $day = (int) date('j', strtotime($t['due_date']));
    $eventsByDay[$day][] = ['type' => 'task', 'label' => $t['title'], 'sub' => $t['project_name'], 'status' => $t['status'], 'link' => 'tasks.php'];
}
foreach ($projectDeadlines as $p) {
    $day = (int) date('j', strtotime($p['deadline']));
    $eventsByDay[$day][] = ['type' => 'deadline', 'label' => $p['project_name'], 'sub' => 'Project deadline', 'status' => $p['status'], 'link' => 'project_view.php?id=' . $p['id']];
}
foreach ($invoiceDueDates as $inv) {
    $day = (int) date('j', strtotime($inv['due_date']));
    $eventsByDay[$day][] = ['type' => 'invoice', 'label' => $inv['invoice_number'], 'sub' => $inv['company_name'] . ' — due', 'status' => $inv['status'], 'link' => 'invoice_view.php?id=' . $inv['id']];
}

$monthLabel = date('F Y', strtotime($firstOfMonth));
$today = date('Y-m-d');

$prevMonth = $month - 1; $prevYear = $year;
$nextMonth = $month + 1; $nextYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

require_once __DIR__ . '/../includes/dash_header.php';
?>

<div class="page-header">
  <div>
    <h2>Calendar</h2>
    <p class="text-muted mb-0">Task due dates, project deadlines and invoice due dates in one view.</p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <a href="calendar.php?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-chevron-left"></i></a>
    <span class="fw-semibold" style="min-width:150px;text-align:center;"><?= $monthLabel ?></span>
    <a href="calendar.php?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-chevron-right"></i></a>
    <a href="calendar.php" class="btn btn-sm btn-light">Today</a>
  </div>
</div>

<div class="d-flex flex-wrap gap-3 mb-3 small">
  <span><span class="legend-dot" style="background:#c9a15a;"></span> Task Due</span>
  <span><span class="legend-dot" style="background:#0f2a4a;"></span> Project Deadline</span>
  <span><span class="legend-dot" style="background:#c0392b;"></span> Invoice Due</span>
</div>

<div class="data-card">
  <div class="calendar-grid">
    <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
      <div class="calendar-head-cell"><?= $d ?></div>
    <?php endforeach; ?>

    <?php for ($i = 0; $i < $startWeekday; $i++): ?>
      <div class="calendar-cell empty"></div>
    <?php endfor; ?>

    <?php for ($day = 1; $day <= $daysInMonth; $day++):
      $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
      $isToday = $dateStr === $today;
      $events = $eventsByDay[$day] ?? [];
    ?>
      <div class="calendar-cell <?= $isToday ? 'is-today' : '' ?>">
        <div class="calendar-day-num"><?= $day ?></div>
        <?php foreach (array_slice($events, 0, 3) as $ev): ?>
          <a href="<?= htmlspecialchars($ev['link']) ?>" class="calendar-event calendar-event-<?= $ev['type'] ?>" title="<?= htmlspecialchars($ev['label'] . ' — ' . $ev['sub']) ?>">
            <?= htmlspecialchars($ev['label']) ?>
          </a>
        <?php endforeach; ?>
        <?php if (count($events) > 3): ?><div class="text-muted" style="font-size:0.68rem;">+<?= count($events) - 3 ?> more</div><?php endif; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-4">
    <div class="data-card">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Task Due Dates</h6></div>
      <div class="p-3">
        <?php if (!$tasksThisMonth): ?><p class="text-muted small mb-0">No tasks due this month.</p><?php endif; ?>
        <?php foreach ($tasksThisMonth as $t): ?>
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div><div class="small fw-semibold"><?= htmlspecialchars($t['title']) ?></div><div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($t['project_name']) ?></div></div>
            <span class="text-muted small"><?= date('M j', strtotime($t['due_date'])) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="data-card">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Project Deadlines</h6></div>
      <div class="p-3">
        <?php if (!$projectDeadlines): ?><p class="text-muted small mb-0">No project deadlines this month.</p><?php endif; ?>
        <?php foreach ($projectDeadlines as $p): ?>
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div class="small fw-semibold"><?= htmlspecialchars($p['project_name']) ?></div>
            <span class="text-muted small"><?= date('M j', strtotime($p['deadline'])) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="data-card">
      <div class="data-card-head"><h6 class="mb-0 fw-semibold">Invoices Due</h6></div>
      <div class="p-3">
        <?php if (!$invoiceDueDates): ?><p class="text-muted small mb-0">No invoices due this month.</p><?php endif; ?>
        <?php foreach ($invoiceDueDates as $inv): ?>
          <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div><div class="small fw-semibold"><?= htmlspecialchars($inv['invoice_number']) ?></div><div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($inv['company_name']) ?></div></div>
            <span class="text-muted small"><?= date('M j', strtotime($inv['due_date'])) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<style>
.legend-dot { display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:5px; }
.calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); }
.calendar-head-cell { padding:0.75rem; text-align:center; font-size:0.78rem; font-weight:600; color:#6c7889; background:#f8f9fb; border-bottom:1px solid #e7eaef; text-transform:uppercase; letter-spacing:0.5px; }
.calendar-cell { min-height:100px; border-right:1px solid #e7eaef; border-bottom:1px solid #e7eaef; padding:0.5rem; }
.calendar-cell.empty { background:#fbfbfc; }
.calendar-cell.is-today { background:#fbf7ee; }
.calendar-day-num { font-size:0.8rem; font-weight:600; color:#0f2a4a; margin-bottom:0.3rem; }
.calendar-event { display:block; font-size:0.68rem; padding:0.15rem 0.4rem; border-radius:5px; margin-bottom:0.2rem; text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#fff; }
.calendar-event-task { background:#c9a15a; }
.calendar-event-deadline { background:#0f2a4a; }
.calendar-event-invoice { background:#c0392b; }
@media (max-width:768px) { .calendar-cell { min-height:70px; } .calendar-head-cell { font-size:0.65rem; padding:0.4rem; } }
</style>

<?php require_once __DIR__ . '/../includes/dash_footer.php'; ?>
