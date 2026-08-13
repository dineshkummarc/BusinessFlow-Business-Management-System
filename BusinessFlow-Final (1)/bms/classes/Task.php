<?php
/**
 * Task
 * Phase 2 — dedicated model for the global Tasks (Kanban) module.
 * The `tasks` table already existed in Phase 1 (used inline within a
 * single project's view); this class provides a proper OOP interface
 * so tasks can be managed system-wide, across every project, from
 * their own dashboard page.
 */
class Task
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = '', string $projectId = '', string $assignedTo = ''): array
    {
        $sql = 'SELECT t.*, p.project_name, e.full_name AS assignee_name
                FROM tasks t
                JOIN projects p ON p.id = t.project_id
                LEFT JOIN employees e ON e.id = t.assigned_to
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (t.title LIKE ? OR p.project_name LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($projectId !== '') {
            $sql .= ' AND t.project_id = ?';
            $params[] = $projectId;
        }
        if ($assignedTo !== '') {
            $sql .= ' AND t.assigned_to = ?';
            $params[] = $assignedTo;
        }
        $sql .= ' ORDER BY FIELD(t.priority,\'high\',\'medium\',\'low\'), t.due_date IS NULL, t.due_date ASC';

        return $this->db->select($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne(
            'SELECT t.*, p.project_name, e.full_name AS assignee_name
             FROM tasks t JOIN projects p ON p.id = t.project_id
             LEFT JOIN employees e ON e.id = t.assigned_to
             WHERE t.id = ?',
            [$id]
        );
    }

    public function create(array $d): int
    {
        $this->db->execute(
            'INSERT INTO tasks (project_id, assigned_to, title, description, due_date, status, priority)
             VALUES (?,?,?,?,?,?,?)',
            [
                $d['project_id'], $d['assigned_to'] ?: null, $d['title'], $d['description'] ?? null,
                $d['due_date'] ?: null, $d['status'] ?? 'todo', $d['priority'] ?? 'medium',
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        return $this->db->execute(
            'UPDATE tasks SET project_id=?, assigned_to=?, title=?, description=?, due_date=?, status=?, priority=? WHERE id=?',
            [
                $d['project_id'], $d['assigned_to'] ?: null, $d['title'], $d['description'] ?? null,
                $d['due_date'] ?: null, $d['status'] ?? 'todo', $d['priority'] ?? 'medium', $id,
            ]
        ) >= 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->db->execute('UPDATE tasks SET status = ? WHERE id = ?', [$status, $id]) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute('DELETE FROM tasks WHERE id = ?', [$id]) > 0;
    }

    public function countByStatus(string $status): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM tasks WHERE status = ?', [$status]);
        return (int) ($row['c'] ?? 0);
    }

    public function countOverdue(): int
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS c FROM tasks WHERE due_date < CURDATE() AND status != 'done'");
        return (int) ($row['c'] ?? 0);
    }

    /** Tasks + project deadlines within a date range, for the Calendar module. */
    public function dueBetween(string $start, string $end): array
    {
        return $this->db->select(
            "SELECT t.id, t.title, t.due_date, t.status, t.priority, p.project_name
             FROM tasks t JOIN projects p ON p.id = t.project_id
             WHERE t.due_date BETWEEN ? AND ?",
            [$start, $end]
        );
    }
}
