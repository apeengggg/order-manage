<?php
namespace App\Repositories;

use App\TenantContext;
use PDO;

class PermissionRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAllModules(): array {
        $isSuperAdmin = TenantContext::isSuperAdmin() && !TenantContext::isImpersonating();
        if ($isSuperAdmin) {
            return $this->db->query("SELECT * FROM modules WHERE is_active=1 ORDER BY sort_order")->fetchAll();
        }
        return $this->db->query("SELECT * FROM modules WHERE is_active=1 AND is_superadmin_only=0 ORDER BY sort_order")->fetchAll();
    }

    public function findAllPermissions(): array {
        $globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        if ($globalView) {
            $rows = $this->db->query(
                "SELECT rp.*, m.name as module_name, m.slug as module_slug, m.icon as module_icon
                 FROM role_permissions rp
                 INNER JOIN modules m ON m.id = rp.module_id
                 WHERE m.is_active = 1
                 ORDER BY rp.role_id, m.sort_order"
            )->fetchAll();
        } else {
            $stmt = $this->db->prepare(
                "SELECT rp.*, m.name as module_name, m.slug as module_slug, m.icon as module_icon
                 FROM role_permissions rp
                 INNER JOIN modules m ON m.id = rp.module_id
                 INNER JOIN roles r ON r.id = rp.role_id
                 WHERE m.is_active = 1 AND (r.tenant_id = ? OR r.tenant_id IS NULL)
                 ORDER BY rp.role_id, m.sort_order"
            );
            $stmt->execute([TenantContext::effectiveTenantId()]);
            $rows = $stmt->fetchAll();
        }
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['role_id']][] = $row;
        }
        return $grouped;
    }

    public function loadPermissionsForRole(int $roleId): array {
        $isSuperAdmin = false;
        $role = $this->db->prepare("SELECT tenant_id, slug FROM roles WHERE id = ?");
        $role->execute([$roleId]);
        $roleData = $role->fetch();
        if ($roleData && $roleData['slug'] === 'superadmin' && $roleData['tenant_id'] === null) {
            $isSuperAdmin = true;
        }

        $sql = "SELECT m.slug, m.name, m.icon, m.url, m.parent_id, m.sort_order, m.is_superadmin_only,
                    rp.can_view, rp.can_add, rp.can_edit, rp.can_delete,
                    rp.can_view_detail, rp.can_upload, rp.can_download
             FROM role_permissions rp
             INNER JOIN modules m ON m.id = rp.module_id
             WHERE rp.role_id = ? AND m.is_active = 1
             ORDER BY m.sort_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        $rows = $stmt->fetchAll();

        $permissions = [];
        foreach ($rows as $row) {
            // Skip superadmin-only modules for non-superadmin roles
            if ($row['is_superadmin_only'] && !$isSuperAdmin) continue;

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

    public function upsertPermission(int $roleId, int $moduleId, array $perms): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO role_permissions (role_id, module_id, can_view, can_add, can_edit, can_delete, can_view_detail, can_upload, can_download)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                can_view=VALUES(can_view), can_add=VALUES(can_add), can_edit=VALUES(can_edit),
                can_delete=VALUES(can_delete), can_view_detail=VALUES(can_view_detail),
                can_upload=VALUES(can_upload), can_download=VALUES(can_download)"
        );
        return $stmt->execute([
            $roleId, $moduleId,
            $perms['can_view'] ?? 0, $perms['can_add'] ?? 0, $perms['can_edit'] ?? 0,
            $perms['can_delete'] ?? 0, $perms['can_view_detail'] ?? 0,
            $perms['can_upload'] ?? 0, $perms['can_download'] ?? 0
        ]);
    }

    public function getRoles(): array {
        $globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        if ($globalView) {
            return $this->db->query(
                "SELECT r.*, t.name as tenant_name FROM roles r
                 LEFT JOIN tenants t ON r.tenant_id = t.id
                 ORDER BY t.name, r.id"
            )->fetchAll();
        }
        $showTenantName = TenantContext::isSuperAdmin();
        $sql = "SELECT r.*";
        if ($showTenantName) {
            $sql .= ", t.name as tenant_name";
        }
        $sql .= " FROM roles r";
        if ($showTenantName) {
            $sql .= " LEFT JOIN tenants t ON r.tenant_id = t.id";
        }
        $sql .= " WHERE r.tenant_id = ? ORDER BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([TenantContext::effectiveTenantId()]);
        return $stmt->fetchAll();
    }

    public function getAllRolesAcrossTenants(): array {
        return $this->db->query("SELECT * FROM roles ORDER BY tenant_id, id")->fetchAll();
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
