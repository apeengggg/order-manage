<?php
namespace App\Repositories;

use App\TenantContext;

class FileRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO files (tenant_id, module, module_id, file_name, file_path, file_type, file_size, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            TenantContext::id(),
            $data['module'],
            $data['module_id'],
            $data['file_name'],
            $data['file_path'],
            $data['file_type'],
            $data['file_size'],
            $data['uploaded_by']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findByModule(string $module, int $moduleId): array {
        $globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        if ($globalView) {
            $stmt = $this->db->prepare(
                "SELECT f.*, u.name AS uploader_name
                 FROM files f LEFT JOIN users u ON u.id = f.uploaded_by
                 WHERE f.module = ? AND f.module_id = ?
                 ORDER BY f.created_at DESC"
            );
            $stmt->execute([$module, $moduleId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT f.*, u.name AS uploader_name
                 FROM files f LEFT JOIN users u ON u.id = f.uploaded_by
                 WHERE f.module = ? AND f.module_id = ? AND f.tenant_id = ?
                 ORDER BY f.created_at DESC"
            );
            $stmt->execute([$module, $moduleId, TenantContext::effectiveTenantId()]);
        }
        return $stmt->fetchAll();
    }

    public function findLatestByModuleIds(string $module, array $moduleIds): array {
        if (empty($moduleIds)) return [];
        $placeholders = implode(',', array_fill(0, count($moduleIds), '?'));
        $globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        if ($globalView) {
            $params = array_merge([$module], $moduleIds);
            $stmt = $this->db->prepare(
                "SELECT f.* FROM files f
                 INNER JOIN (
                     SELECT module_id, MAX(id) as max_id
                     FROM files WHERE module = ? AND module_id IN ($placeholders)
                     GROUP BY module_id
                 ) latest ON f.id = latest.max_id"
            );
        } else {
            $params = array_merge([$module, TenantContext::effectiveTenantId()], $moduleIds);
            $stmt = $this->db->prepare(
                "SELECT f.* FROM files f
                 INNER JOIN (
                     SELECT module_id, MAX(id) as max_id
                     FROM files WHERE module = ? AND tenant_id = ? AND module_id IN ($placeholders)
                     GROUP BY module_id
                 ) latest ON f.id = latest.max_id"
            );
        }
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['module_id']] = $row;
        }
        return $map;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM files WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteByModule(string $module, int $moduleId): array {
        $files = $this->findByModule($module, $moduleId);
        $stmt = $this->db->prepare("DELETE FROM files WHERE module = ? AND module_id = ? AND tenant_id = ?");
        $stmt->execute([$module, $moduleId, TenantContext::id()]);
        return $files;
    }
}
