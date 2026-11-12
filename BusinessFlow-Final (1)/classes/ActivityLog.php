<?php
/**
 * ActivityLog
 * Records key actions across the system for auditing purposes.
 */
class ActivityLog
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function record(?int $userId, string $module, string $action): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->db->execute(
            'INSERT INTO activity_log (user_id, module, action, ip_address) VALUES (?, ?, ?, ?)',
            [$userId, $module, $action, $ip]
        );
    }

    public function recent(int $limit = 10): array
    {
        $limit = max(1, min(100, $limit));
        return $this->db->select(
            "SELECT al.*, u.full_name
             FROM activity_log al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT $limit"
        );
    }
}
