<?php
/**
 * Expense
 * Phase 2 — Financial module: tracks business expenses, separate
 * from Invoice (which tracks incoming revenue). Together they feed
 * the profit/loss view in Reports.
 */
class Expense
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = '', string $category = '', string $status = ''): array
    {
        $sql = 'SELECT e.*, p.project_name
                FROM expenses e
                LEFT JOIN projects p ON p.id = e.project_id
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (e.description LIKE ? OR e.category LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($category !== '') {
            $sql .= ' AND e.category = ?';
            $params[] = $category;
        }
        if ($status !== '') {
            $sql .= ' AND e.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY e.expense_date DESC';

        return $this->db->select($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM expenses WHERE id = ?', [$id]);
    }

    public function create(array $d, int $createdBy): int
    {
        $this->db->execute(
            'INSERT INTO expenses (category, description, amount, expense_date, project_id, payment_method, status, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [
                $d['category'], $d['description'], $d['amount'] ?? 0, $d['expense_date'],
                $d['project_id'] ?: null, $d['payment_method'] ?? 'other', $d['status'] ?? 'pending',
                $d['notes'] ?? null, $createdBy,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        return $this->db->execute(
            'UPDATE expenses SET category=?, description=?, amount=?, expense_date=?, project_id=?, payment_method=?, status=?, notes=? WHERE id=?',
            [
                $d['category'], $d['description'], $d['amount'] ?? 0, $d['expense_date'],
                $d['project_id'] ?: null, $d['payment_method'] ?? 'other', $d['status'] ?? 'pending',
                $d['notes'] ?? null, $id,
            ]
        ) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute('DELETE FROM expenses WHERE id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM expenses');
        return (int) ($row['c'] ?? 0);
    }

    public function totalApproved(): float
    {
        $row = $this->db->selectOne("SELECT SUM(amount) AS s FROM expenses WHERE status = 'approved'");
        return (float) ($row['s'] ?? 0);
    }

    public function totalPending(): float
    {
        $row = $this->db->selectOne("SELECT SUM(amount) AS s FROM expenses WHERE status = 'pending'");
        return (float) ($row['s'] ?? 0);
    }

    public function categories(): array
    {
        $rows = $this->db->select('SELECT DISTINCT category FROM expenses ORDER BY category ASC');
        return array_column($rows, 'category');
    }
}
