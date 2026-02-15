<?php
namespace App\Repositories;

use App\TenantContext;

class ExpeditionRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE is_active=1 AND tenant_id = ? ORDER BY name");
        $stmt->execute([TenantContext::id()]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE id=? AND tenant_id = ?");
        $stmt->execute([$id, TenantContext::id()]);
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
