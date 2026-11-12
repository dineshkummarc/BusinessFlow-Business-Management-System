<?php
/**
 * Employee
 * HR module — manages staff records, distinct from login `users`.
 */
class Employee
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = '', string $department = ''): array
    {
        $sql = "SELECT e.*, u.full_name AS linked_user_name, u.email AS linked_user_email
                FROM employees e
                LEFT JOIN users u ON u.id = e.user_id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (e.full_name LIKE ? OR e.email LIKE ? OR e.employee_code LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($department !== '') {
            $sql .= ' AND e.department = ?';
            $params[] = $department;
        }
        $sql .= ' ORDER BY e.created_at DESC';

        return $this->db->select($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM employees WHERE id = ?', [$id]);
    }

    public function nextEmployeeCode(): string
    {
        $row = $this->db->selectOne('SELECT employee_code FROM employees ORDER BY id DESC LIMIT 1');
        $next = 1;
        if ($row && preg_match('/(\d+)$/', $row['employee_code'], $m)) {
            $next = (int) $m[1] + 1;
        }
        return 'EMP-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $d): int
    {
        $this->db->execute(
            'INSERT INTO employees (employee_code, full_name, email, phone, department, designation, salary, hire_date, status, user_id)
             VALUES (?,?,?,?,?,?,?,?,?,?)',
            [
                $this->nextEmployeeCode(), $d['full_name'], $d['email'], $d['phone'] ?? null,
                $d['department'], $d['designation'], $d['salary'] ?? 0,
                $d['hire_date'], $d['status'] ?? 'active', $d['user_id'] ?: null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        return $this->db->execute(
            'UPDATE employees SET full_name=?, email=?, phone=?, department=?, designation=?, salary=?, hire_date=?, status=?, user_id=? WHERE id=?',
            [
                $d['full_name'], $d['email'], $d['phone'] ?? null, $d['department'],
                $d['designation'], $d['salary'] ?? 0, $d['hire_date'], $d['status'] ?? 'active',
                $d['user_id'] ?: null, $id,
            ]
        ) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute('DELETE FROM employees WHERE id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM employees');
        return (int) ($row['c'] ?? 0);
    }

    public function departments(): array
    {
        $rows = $this->db->select('SELECT DISTINCT department FROM employees ORDER BY department ASC');
        return array_column($rows, 'department');
    }

    public function forDropdown(): array
    {
        return $this->db->select("SELECT id, full_name, employee_code FROM employees WHERE status = 'active' ORDER BY full_name ASC");
    }

    /**
     * Phase 3 — users who don't yet have an employee record linked to them
     * (plus the currently-linked user, if editing), for the "System Login
     * Account" dropdown on the employee form. This is what makes task
     * assignment notifications actually reach a real inbox.
     */
    public function unlinkedUsers(?int $currentUserId = null): array
    {
        $sql = "SELECT u.id, u.full_name, u.email FROM users u
                WHERE u.id NOT IN (SELECT user_id FROM employees WHERE user_id IS NOT NULL)";
        $params = [];
        if ($currentUserId) {
            $sql .= ' OR u.id = ?';
            $params[] = $currentUserId;
        }
        $sql .= ' ORDER BY u.full_name ASC';
        return $this->db->select($sql, $params);
    }
}
