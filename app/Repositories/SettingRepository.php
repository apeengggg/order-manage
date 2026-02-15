<?php
namespace App\Repositories;

class SettingRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(): array {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM app_settings");
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    public function get(string $key): ?string {
        $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : null;
    }

    public function set(string $key, ?string $value): void {
        $stmt = $this->db->prepare(
            "INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $stmt->execute([$key, $value]);
    }

    public function setMultiple(array $data): void {
        $stmt = $this->db->prepare(
            "INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        foreach ($data as $key => $value) {
            $stmt->execute([$key, $value]);
        }
    }
}
