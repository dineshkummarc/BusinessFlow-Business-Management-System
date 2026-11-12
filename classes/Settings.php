<?php
/**
 * Settings
 * Phase 2 — simple key/value store for company-wide configuration
 * (company name, currency, default tax rate, etc). Cached per-request
 * so repeated calls don't re-hit the database.
 */
class Settings
{
    private Database $db;
    private static ?array $cache = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function loadAll(): array
    {
        if (self::$cache === null) {
            $rows = $this->db->select('SELECT setting_key, setting_value FROM settings');
            self::$cache = [];
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    public function get(string $key, string $default = ''): string
    {
        $all = $this->loadAll();
        return $all[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->loadAll();
    }

    public function set(string $key, string $value): void
    {
        $this->db->execute(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$cache = null; // invalidate cache
    }

    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->set($key, $value);
        }
    }
}
