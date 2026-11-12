<?php
/**
 * Client
 * CRM module — manages client/customer company records.
 */
class Client
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = '', string $status = ''): array
    {
        $sql = 'SELECT * FROM clients WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (company_name LIKE ? OR contact_person LIKE ? OR email LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($status !== '') {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY created_at DESC';

        return $this->db->select($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM clients WHERE id = ?', [$id]);
    }

    public function create(array $d, int $createdBy): int
    {
        $this->db->execute(
            'INSERT INTO clients (company_name, contact_person, email, phone, address, city, country, industry, status, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [
                $d['company_name'], $d['contact_person'], $d['email'], $d['phone'] ?? null,
                $d['address'] ?? null, $d['city'] ?? null, $d['country'] ?? null,
                $d['industry'] ?? null, $d['status'] ?? 'lead', $d['notes'] ?? null, $createdBy,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        return $this->db->execute(
            'UPDATE clients SET company_name=?, contact_person=?, email=?, phone=?, address=?, city=?, country=?, industry=?, status=?, notes=? WHERE id=?',
            [
                $d['company_name'], $d['contact_person'], $d['email'], $d['phone'] ?? null,
                $d['address'] ?? null, $d['city'] ?? null, $d['country'] ?? null,
                $d['industry'] ?? null, $d['status'] ?? 'lead', $d['notes'] ?? null, $id,
            ]
        ) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->execute('DELETE FROM clients WHERE id = ?', [$id]) > 0;
    }

    /**
     * Phase 3 — data-integrity guard. Deleting a client would cascade and
     * silently destroy their invoice history (see schema FK). Before that
     * happens, tell the caller what's actually attached so they can make
     * an informed choice instead of losing financial records by accident.
     */
    public function relatedRecordCounts(int $id): array
    {
        $invoices = $this->db->selectOne('SELECT COUNT(*) AS c FROM invoices WHERE client_id = ?', [$id]);
        $projects = $this->db->selectOne('SELECT COUNT(*) AS c FROM projects WHERE client_id = ?', [$id]);
        return [
            'invoices' => (int) ($invoices['c'] ?? 0),
            'projects' => (int) ($projects['c'] ?? 0),
        ];
    }

    public function count(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM clients');
        return (int) ($row['c'] ?? 0);
    }

    public function countByStatus(string $status): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM clients WHERE status = ?', [$status]);
        return (int) ($row['c'] ?? 0);
    }

    /** Simple list for populating <select> dropdowns elsewhere (projects, invoices). */
    public function forDropdown(): array
    {
        return $this->db->select('SELECT id, company_name FROM clients ORDER BY company_name ASC');
    }
}
