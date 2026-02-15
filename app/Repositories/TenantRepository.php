<?php
namespace App\Repositories;

class TenantRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(): array {
        return $this->db->query(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM users u WHERE u.tenant_id = t.id) as user_count,
                    (SELECT COUNT(*) FROM orders o WHERE o.tenant_id = t.id) as order_count
             FROM tenants t ORDER BY t.id"
        )->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tenants WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO tenants (name, slug, domain, max_users) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['domain'] ?? null,
            $data['max_users'] ?? 10
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE tenants SET name=?, slug=?, domain=?, is_active=?, max_users=? WHERE id=?"
        );
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['domain'] ?? null,
            $data['is_active'] ?? 1,
            $data['max_users'] ?? 10,
            $id
        ]);
    }

    public function delete(int $id): bool {
        // Delete all tenant data in dependency order
        // role_permissions (via roles)
        $this->db->prepare(
            "DELETE rp FROM role_permissions rp INNER JOIN roles r ON r.id = rp.role_id WHERE r.tenant_id = ?"
        )->execute([$id]);
        // orders
        $this->db->prepare("DELETE FROM orders WHERE tenant_id = ?")->execute([$id]);
        // expedition_templates (via expeditions)
        $this->db->prepare(
            "DELETE et FROM expedition_templates et INNER JOIN expeditions e ON e.id = et.expedition_id WHERE e.tenant_id = ?"
        )->execute([$id]);
        // expeditions
        $this->db->prepare("DELETE FROM expeditions WHERE tenant_id = ?")->execute([$id]);
        // files
        $this->db->prepare("DELETE FROM files WHERE tenant_id = ?")->execute([$id]);
        // app_settings
        $this->db->prepare("DELETE FROM app_settings WHERE tenant_id = ?")->execute([$id]);
        // users
        $this->db->prepare("DELETE FROM users WHERE tenant_id = ?")->execute([$id]);
        // roles
        $this->db->prepare("DELETE FROM roles WHERE tenant_id = ?")->execute([$id]);
        // tenant
        $stmt = $this->db->prepare("DELETE FROM tenants WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM tenants WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM tenants WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function countUsers(int $tenantId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return (int)$stmt->fetchColumn();
    }
}
