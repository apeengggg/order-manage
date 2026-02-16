<?php
namespace App\Repositories;

use App\TenantContext;

class ExpeditionRepository {
    private $db;
    private bool $globalView;
    private bool $showTenantName;

    public function __construct() {
        $this->db = getDB();
        $this->globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        $this->showTenantName = TenantContext::isSuperAdmin();
    }

    public function findAll(): array {
        $sql = "SELECT e.*";
        if ($this->showTenantName) {
            $sql .= ", t.name as tenant_name";
        }
        $sql .= " FROM expeditions e";
        if ($this->showTenantName) {
            $sql .= " LEFT JOIN tenants t ON e.tenant_id = t.id";
        }
        if ($this->globalView) {
            $sql .= " WHERE e.is_active=1 ORDER BY t.name, e.name";
            return $this->db->query($sql)->fetchAll();
        }
        $sql .= " WHERE e.is_active=1 AND e.tenant_id = ? ORDER BY e.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([TenantContext::effectiveTenantId()]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        if ($this->globalView) {
            $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE id=?");
            $stmt->execute([$id]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE id=? AND tenant_id = ?");
            $stmt->execute([$id, TenantContext::effectiveTenantId()]);
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
