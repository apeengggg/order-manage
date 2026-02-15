<?php
namespace App\Repositories;

use App\TenantContext;
use PDO;

class RoleRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE tenant_id = ? ORDER BY id");
        $stmt->execute([TenantContext::id()]);
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
        $stmt = $this->db->prepare(
            "SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) as user_count
             FROM roles r WHERE r.tenant_id = ? ORDER BY r.id"
        );
        $stmt->execute([TenantContext::id()]);
        return $stmt->fetchAll();
    }
}
