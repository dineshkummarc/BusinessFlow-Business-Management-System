<?php
/**
 * Renders a colored status badge.
 * Expects $p['status'] to be set by the including page.
 */
$status = $p['status'] ?? 'unknown';
$map = [
    // Clients
    'lead'        => ['gray', 'Lead'],
    'active'      => ['green', 'Active'],
    'inactive'    => ['gray', 'Inactive'],
    // Projects
    'planned'     => ['blue', 'Planned'],
    'in_progress' => ['gold', 'In Progress'],
    'on_hold'     => ['red', 'On Hold'],
    'completed'   => ['green', 'Completed'],
    'cancelled'   => ['red', 'Cancelled'],
    // Invoices
    'draft'       => ['gray', 'Draft'],
    'sent'        => ['blue', 'Sent'],
    'paid'        => ['green', 'Paid'],
    'overdue'     => ['red', 'Overdue'],
    // Employees
    'on_leave'    => ['gold', 'On Leave'],
    'terminated'  => ['red', 'Terminated'],
    // Tasks
    'todo'        => ['gray', 'To Do'],
    'review'      => ['blue', 'In Review'],
    'done'        => ['green', 'Done'],
    // Expenses
    'pending'     => ['gold', 'Pending'],
    'approved'    => ['green', 'Approved'],
    'rejected'    => ['red', 'Rejected'],
    // User accounts
    'suspended'   => ['red', 'Suspended'],
];
[$color, $label] = $map[$status] ?? ['gray', ucfirst($status)];
?>
<span class="badge-soft badge-soft-<?= $color ?>"><?= $label ?></span>
