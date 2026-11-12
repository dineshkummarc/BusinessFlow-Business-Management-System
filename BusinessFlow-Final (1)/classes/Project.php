<?php
/**
 * Project
 * Project management module — linked to clients, tracks progress & deadlines.
 */
class Project
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = '', string $status = ''): array
    {
        $sql = 'SELECT p.*, c.company_name
                FROM projects p
                LEFT JOIN clients c ON c.id = p.client_id
                WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (p.project_name LIKE ? OR c.company_name LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($status !== '') {
            $sql .= ' AND p.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY p.created_at DESC';

        return $this->db->select($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne(
            'SELECT p.*, c.company_name FROM projects p LEFT JOIN clients c ON c.id = p.client_id WHERE p.id = ?',
            [$id]
        );
    }

    public function team(int $projectId): array
    {
        return $this->db->select(
            'SELECT pt.role_in_project, e.id, e.full_name, e.designation
             FROM project_team pt
             JOIN employees e ON e.id = pt.employee_id
             WHERE pt.project_id = ?',
            [$projectId]
        );
    }

    public function tasks(int $projectId): array
    {
        return $this->db->select(
            'SELECT t.*, e.full_name AS assignee_name
             FROM tasks t
             LEFT JOIN employees e ON e.id = t.assigned_to
             WHERE t.project_id = ?
             ORDER BY t.due_date ASC',
            [$projectId]
        );
    }

    public function create(array $d, int $createdBy): int
    {
        $this->db->execute(
            'INSERT INTO projects (client_id, project_name, description, budget, start_date, deadline, status, priority, progress, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)',
            [
                $d['client_id'] ?: null, $d['project_name'], $d['description'] ?? null,
                $d['budget'] ?? 0, $d['start_date'] ?: null, $d['deadline'] ?: null,
                $d['status'] ?? 'planned', $d['priority'] ?? 'medium', $d['progress'] ?? 0, $createdBy,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        return $this->db->execute(
            'UPDATE projects SET client_id=?, project_name=?, description=?, budget=?, start_date=?, deadline=?, status=?, priority=?, progress=? WHERE id=?',
            [
                $d['client_id'] ?: null, $d['project_name'], $d['description'] ?? null,
                $d['budget'] ?? 0, $d['start_date'] ?: null, $d['deadline'] ?: null,
                $d['status'] ?? 'planned', $d['priority'] ?? 'medium', $d['progress'] ?? 0, $id,
            ]
        ) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute('DELETE FROM projects WHERE id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM projects');
        return (int) ($row['c'] ?? 0);
    }

    public function countByStatus(string $status): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM projects WHERE status = ?', [$status]);
        return (int) ($row['c'] ?? 0);
    }

    public function forDropdown(): array
    {
        return $this->db->select('SELECT id, project_name FROM projects ORDER BY project_name ASC');
    }
}
