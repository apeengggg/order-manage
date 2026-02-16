<?php
namespace App\Repositories;

use App\TenantContext;
use PDO;

class RoleRepository {
    private $db;
    private bool $globalView;
    private bool $showTenantName;

    public function __construct() {
        $this->db = getDB();
        $this->globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        $this->showTenantName = TenantContext::isSuperAdmin();
    }

    public function findAll(): array {
        $sql = "SELECT r.*";
        if ($this->showTenantName) {
            $sql .= ", t.name as tenant_name";
        }
        $sql .= " FROM roles r";
        if ($this->showTenantName) {
            $sql .= " LEFT JOIN tenants t ON r.tenant_id = t.id";
        }
        if ($this->globalView) {
            $sql .= " ORDER BY t.name, r.id";
            return $this->db->query($sql)->fetchAll();
        }
        $sql .= " WHERE r.tenant_id = ? ORDER BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([TenantContext::effectiveTenantId()]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE slug = ? AND tenant_id = ?");
        $stmt->execute([$slug, TenantContext::id()]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO roles (tenant_id, name, slug, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([TenantContext::id(), $data['name'], $data['slug'], $data['description'] ?? null]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE roles SET name=?, slug=?, description=? WHERE id=? AND tenant_id = ?");
        return $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? null, $id, TenantContext::id()]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, TenantContext::id()]);
    }

    public function countUsers(int $roleId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return (int)$stmt->fetchColumn();
    }

    public function findAllWithUserCount(): array {
        $sql = "SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) as user_count";
        if ($this->showTenantName) {
            $sql .= ", t.name as tenant_name";
        }
        $sql .= " FROM roles r";
        if ($this->showTenantName) {
            $sql .= " LEFT JOIN tenants t ON r.tenant_id = t.id";
        }
        if ($this->globalView) {
            $sql .= " ORDER BY t.name, r.id";
            return $this->db->query($sql)->fetchAll();
        }
        $sql .= " WHERE r.tenant_id = ? ORDER BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([TenantContext::effectiveTenantId()]);
        return $stmt->fetchAll();
    }
}
