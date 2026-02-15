<?php
namespace App\Repositories;

use App\TenantContext;

class ExpeditionRepository {
    private $db;
    private bool $globalView;

    public function __construct() {
        $this->db = getDB();
        $this->globalView = TenantContext::isSuperAdmin();
    }

    public function findAll(): array {
        if ($this->globalView) {
            return $this->db->query(
                "SELECT e.*, t.name as tenant_name FROM expeditions e
                 LEFT JOIN tenants t ON e.tenant_id = t.id
                 WHERE e.is_active=1 ORDER BY t.name, e.name"
            )->fetchAll();
        }
        $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE is_active=1 AND tenant_id = ? ORDER BY name");
        $stmt->execute([TenantContext::id()]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        if ($this->globalView) {
            $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE id=?");
            $stmt->execute([$id]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE id=? AND tenant_id = ?");
            $stmt->execute([$id, TenantContext::id()]);
        }
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO expeditions (tenant_id, name, code) VALUES (?, ?, ?)");
        $stmt->execute([TenantContext::id(), $data['name'], strtoupper($data['code'])]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE expeditions SET name=?, code=? WHERE id=? AND tenant_id = ?");
        return $stmt->execute([$data['name'], strtoupper($data['code']), $id, TenantContext::id()]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE expeditions SET is_active=0 WHERE id=? AND tenant_id = ?");
        return $stmt->execute([$id, TenantContext::id()]);
    }
}
