<?php
/**
 * ContactMessage
 * Handles messages submitted through the public "Contact Us" form.
 */
class ContactMessage
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $d): int
    {
        $this->db->execute(
            'INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)',
            [$d['name'], $d['email'], $d['subject'] ?? null, $d['message']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->select('SELECT * FROM contact_messages ORDER BY created_at DESC');
    }

    public function countUnread(): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM contact_messages WHERE is_read = 0');
        return (int) ($row['c'] ?? 0);
    }

    public function markRead(int $id): bool
    {
        return $this->db->execute('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]) >= 0;
    }
}
