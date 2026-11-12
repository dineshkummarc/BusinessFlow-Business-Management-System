<?php
/**
 * Notification
 * Phase 2 — in-app notification center. Other modules call
 * Notification::notify() when something happens that a user should
 * know about (task assigned, invoice overdue, new contact message,
 * new account registered).
 */
class Notification
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Send a notification to a specific user. */
    public function notify(int $userId, string $type, string $title, string $message = '', string $link = ''): void
    {
        $this->db->execute(
            'INSERT INTO notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)',
            [$userId, $type, $title, $message, $link]
        );
    }

    /** Send the same notification to every admin (e.g. new registration, new contact message). */
    public function notifyAdmins(string $type, string $title, string $message = '', string $link = ''): void
    {
        $admins = $this->db->select("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
        foreach ($admins as $admin) {
            $this->notify((int) $admin['id'], $type, $title, $message, $link);
        }
    }

    public function forUser(int $userId, int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));
        return $this->db->select(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit",
            [$userId]
        );
    }

    public function countUnread(int $userId): int
    {
        $row = $this->db->selectOne('SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
        return (int) ($row['c'] ?? 0);
    }

    public function markRead(int $id, int $userId): bool
    {
        return $this->db->execute('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?', [$id, $userId]) >= 0;
    }

    public function markAllRead(int $userId): bool
    {
        return $this->db->execute('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [$userId]) >= 0;
    }
}
