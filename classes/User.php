<?php
/**
 * User
 * Handles user-account records (login credentials, roles, profile).
 */
class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByLogin(string $loginId): ?array
    {
        return $this->db->selectOne(
            'SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1',
            [$loginId, $loginId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function emailExists(string $email): bool
    {
        return $this->db->selectOne('SELECT id FROM users WHERE email = ?', [$email]) !== null;
    }

    public function usernameExists(string $username): bool
    {
        return $this->db->selectOne('SELECT id FROM users WHERE username = ?', [$username]) !== null;
    }

    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO users (full_name, email, username, password_hash, role, phone, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $data['full_name'],
                $data['email'],
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'] ?? 'staff',
                $data['phone'] ?? null,
                'active',
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->execute('UPDATE users SET last_login = NOW() WHERE id = ?', [$id]);
    }

    public function updateProfile(int $id, array $data): bool
    {
        return $this->db->execute(
            'UPDATE users SET full_name = ?, phone = ? WHERE id = ?',
            [$data['full_name'], $data['phone'] ?? null, $id]
        ) >= 0;
    }

    public function countAll(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM users');
        return (int) ($row['c'] ?? 0);
    }

    // ---------------------------------------------------------------
    // Phase 2 — User & Role Management (admin only)
    // ---------------------------------------------------------------

    public function all(string $search = '', string $role = ''): array
    {
        $sql = 'SELECT * FROM users WHERE 1=1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (full_name LIKE ? OR email LIKE ? OR username LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($role !== '') {
            $sql .= ' AND role = ?';
            $params[] = $role;
        }
        $sql .= ' ORDER BY created_at DESC';
        return $this->db->select($sql, $params);
    }

    public function createByAdmin(array $d): int
    {
        $this->db->execute(
            'INSERT INTO users (full_name, email, username, password_hash, role, phone, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $d['full_name'], $d['email'], $d['username'],
                password_hash($d['password'], PASSWORD_DEFAULT),
                $d['role'] ?? 'staff', $d['phone'] ?? null, $d['status'] ?? 'active',
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateByAdmin(int $id, array $d): bool
    {
        return $this->db->execute(
            'UPDATE users SET full_name=?, email=?, phone=?, role=?, status=? WHERE id=?',
            [$d['full_name'], $d['email'], $d['phone'] ?? null, $d['role'], $d['status'], $id]
        ) >= 0;
    }

    public function resetPassword(int $id, string $newPassword): bool
    {
        return $this->db->execute(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [password_hash($newPassword, PASSWORD_DEFAULT), $id]
        ) >= 0;
    }

    public function countAdmins(): int
    {
        $row = $this->db->selectOne("SELECT COUNT(*) AS c FROM users WHERE role = 'admin' AND status = 'active'");
        return (int) ($row['c'] ?? 0);
    }

    public function delete(int $id): bool
    {
        return $this->db->execute('DELETE FROM users WHERE id = ?', [$id]) > 0;
    }
}
