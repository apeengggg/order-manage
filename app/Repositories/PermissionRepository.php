<?php
namespace App\Repositories;

use PDO;

class PermissionRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAllModules(): array {
        return $this->db->query("SELECT * FROM modules WHERE is_active=1 ORDER BY sort_order")->fetchAll();
    }

    public function findAllPermissions(): array {
        $stmt = $this->db->query(
            "SELECT rp.*, m.name as module_name, m.slug as module_slug, m.icon as module_icon
             FROM role_permissions rp
             INNER JOIN modules m ON m.id = rp.module_id
             WHERE m.is_active = 1
             ORDER BY rp.role, m.sort_order"
        );
        $rows = $stmt->fetchAll();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['role']][] = $row;
        }
        return $grouped;
    }

    public function loadPermissionsForRole(string $role): array {
        $stmt = $this->db->prepare(
            "SELECT m.slug, m.name, m.icon, m.url, m.parent_id, m.sort_order,
                    rp.can_view, rp.can_add, rp.can_edit, rp.can_delete,
                    rp.can_view_detail, rp.can_upload, rp.can_download
             FROM role_permissions rp
             INNER JOIN modules m ON m.id = rp.module_id
             WHERE rp.role = ? AND m.is_active = 1
             ORDER BY m.sort_order"
        );
        $stmt->execute([$role]);
        $rows = $stmt->fetchAll();

        $permissions = [];
        foreach ($rows as $row) {
            $permissions[$row['slug']] = [
                'name' => $row['name'],
                'icon' => $row['icon'],
                'url' => $row['url'],
                'parent_id' => $row['parent_id'],
                'sort_order' => $row['sort_order'],
                'can_view' => (int)$row['can_view'],
                'can_add' => (int)$row['can_add'],
                'can_edit' => (int)$row['can_edit'],
                'can_delete' => (int)$row['can_delete'],
                'can_view_detail' => (int)$row['can_view_detail'],
                'can_upload' => (int)$row['can_upload'],
                'can_download' => (int)$row['can_download'],
            ];
        }
        return $permissions;
    }

    public function upsertPermission(string $role, int $moduleId, array $perms): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO role_permissions (role, module_id, can_view, can_add, can_edit, can_delete, can_view_detail, can_upload, can_download)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                can_view=VALUES(can_view), can_add=VALUES(can_add), can_edit=VALUES(can_edit),
                can_delete=VALUES(can_delete), can_view_detail=VALUES(can_view_detail),
                can_upload=VALUES(can_upload), can_download=VALUES(can_download)"
        );
        return $stmt->execute([
            $role, $moduleId,
            $perms['can_view'] ?? 0, $perms['can_add'] ?? 0, $perms['can_edit'] ?? 0,
            $perms['can_delete'] ?? 0, $perms['can_view_detail'] ?? 0,
            $perms['can_upload'] ?? 0, $perms['can_download'] ?? 0
        ]);
    }

    public function getRoles(): array {
        return $this->db->query("SELECT DISTINCT role FROM users ORDER BY role")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createModule(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO modules (name, slug, icon, url, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['slug'], $data['icon'], $data['url'], $data['sort_order']]);
        return (int)$this->db->lastInsertId();
    }

    public function updateModule(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE modules SET name=?, slug=?, icon=?, url=?, sort_order=? WHERE id=?");
        return $stmt->execute([$data['name'], $data['slug'], $data['icon'], $data['url'], $data['sort_order'], $id]);
    }

    public function deleteModule(int $id): bool {
        $stmt = $this->db->prepare("UPDATE modules SET is_active=0 WHERE id=?");
        return $stmt->execute([$id]);
    }
}
