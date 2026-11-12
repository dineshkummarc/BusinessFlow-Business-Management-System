<?php
/**
 * Invoice
 * Billing module — manages invoices and their line items for clients.
 */
class Invoice
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->markOverdueInvoices();
    }

    /**
     * Phase 3 — keep invoice status honest without needing a cron job.
     * Any invoice still marked "sent" whose due date has passed is
     * automatically flagged "overdue" the next time the Invoice model is
     * used anywhere in the app (dashboard, invoices list, reports,
     * calendar) — so the status shown always reflects reality instead of
     * waiting on someone to notice and click a dropdown.
     */
    private function markOverdueInvoices(): void
    {
        $justOverdue = $this->db->select(
            "SELECT i.id, i.invoice_number, c.company_name
             FROM invoices i JOIN clients c ON c.id = i.client_id
             WHERE i.status = 'sent' AND i.due_date < CURDATE()"
        );
        if (!$justOverdue) {
            return;
        }
        $this->db->execute(
            "UPDATE invoices SET status = 'overdue' WHERE status = 'sent' AND due_date < CURDATE()"
        );
        $notification = new Notification();
        foreach ($justOverdue as $inv) {
            $notification->notifyAdmins(
                'invoice_overdue',
                'Invoice now overdue',
                "{$inv['invoice_number']} ({$inv['company_name']}) passed its due date.",
                'dashboard/invoice_view.php?id=' . $inv['id']
            );
        }
    }

    public function all(string $search = '', string $status = ''): array
    {
        $sql = 'SELECT i.*, c.company_name
                FROM invoices i
                JOIN clients c ON c.id = i.client_id
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (i.invoice_number LIKE ? OR c.company_name LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($status !== '') {
            $sql .= ' AND i.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY i.issue_date DESC';

        return $this->db->select($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne(
            'SELECT i.*, c.company_name, c.contact_person, c.email
             FROM invoices i JOIN clients c ON c.id = i.client_id WHERE i.id = ?',
            [$id]
        );
    }

    public function items(int $invoiceId): array
    {
        return $this->db->select('SELECT * FROM invoice_items WHERE invoice_id = ?', [$invoiceId]);
    }

    public function nextInvoiceNumber(): string
    {
        $year = date('Y');
        $row = $this->db->selectOne(
            "SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1",
            ["INV-$year-%"]
        );
        $next = 1;
        if ($row && preg_match('/(\d+)$/', $row['invoice_number'], $m)) {
            $next = (int) $m[1] + 1;
        }
        return "INV-$year-" . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $d, array $items, int $createdBy): int
    {
        // Defensive sanitization: never trust quantity/price to be
        // non-negative just because the form's HTML said min="0".
        $amount = 0;
        foreach ($items as $item) {
            $qty = max(0, (float) ($item['quantity'] ?? 0));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $amount += $qty * $price;
        }
        $tax = max(0, min(100, (float) ($d['tax_percent'] ?? 0)));
        $total = $amount + ($amount * $tax / 100);

        $this->db->execute(
            'INSERT INTO invoices (invoice_number, client_id, project_id, amount, tax_percent, total_amount, issue_date, due_date, status, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [
                $this->nextInvoiceNumber(), $d['client_id'], $d['project_id'] ?: null,
                $amount, $tax, $total, $d['issue_date'], $d['due_date'],
                $d['status'] ?? 'draft', $d['notes'] ?? null, $createdBy,
            ]
        );
        $invoiceId = (int) $this->db->lastInsertId();

        foreach ($items as $item) {
            if (trim($item['description'] ?? '') === '') {
                continue;
            }
            $qty = max(0, (float) ($item['quantity'] ?? 0));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $lineTotal = $qty * $price;
            $this->db->execute(
                'INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, line_total) VALUES (?,?,?,?,?)',
                [$invoiceId, $item['description'], $qty, $price, $lineTotal]
            );
        }

        return $invoiceId;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->db->execute('UPDATE invoices SET status = ? WHERE id = ?', [$status, $id]) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute('DELETE FROM invoices WHERE id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM invoices');
        return (int) ($row['c'] ?? 0);
    }

    public function totalRevenue(): float
    {
        $row = $this->db->selectOne("SELECT SUM(total_amount) AS s FROM invoices WHERE status = 'paid'");
        return (float) ($row['s'] ?? 0);
    }

    public function totalOutstanding(): float
    {
        $row = $this->db->selectOne("SELECT SUM(total_amount) AS s FROM invoices WHERE status IN ('sent','overdue')");
        return (float) ($row['s'] ?? 0);
    }
}
