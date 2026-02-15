<?php
namespace App\Repositories;

use App\TenantContext;

class SettingRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(?int $tenantId = null): array {
        $tid = $tenantId ?? TenantContext::id();
        $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM app_settings WHERE tenant_id = ?");
        $stmt->execute([$tid]);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    public function get(string $key, ?int $tenantId = null): ?string {
        $tid = $tenantId ?? TenantContext::id();
        $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE tenant_id = ? AND setting_key = ?");
        $stmt->execute([$tid, $key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : null;
    }

    public function set(string $key, ?string $value, ?int $tenantId = null): void {
        $tid = $tenantId ?? TenantContext::id();
        $stmt = $this->db->prepare(
            "INSERT INTO app_settings (tenant_id, setting_key, setting_value) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $stmt->execute([$tid, $key, $value]);
    }

    public function setMultiple(array $data, ?int $tenantId = null): void {
        $tid = $tenantId ?? TenantContext::id();
        $stmt = $this->db->prepare(
            "INSERT INTO app_settings (tenant_id, setting_key, setting_value) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        foreach ($data as $key => $value) {
            $stmt->execute([$tid, $key, $value]);
        }
    }
}
