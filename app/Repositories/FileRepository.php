<?php
namespace App\Repositories;

class FileRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO files (module, module_id, file_name, file_path, file_type, file_size, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
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
        $stmt = $this->db->prepare(
            "SELECT f.*, u.name AS uploader_name
             FROM files f
             LEFT JOIN users u ON u.id = f.uploaded_by
             WHERE f.module = ? AND f.module_id = ?
             ORDER BY f.created_at DESC"
        );
        $stmt->execute([$module, $moduleId]);
        return $stmt->fetchAll();
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
        $stmt = $this->db->prepare("DELETE FROM files WHERE module = ? AND module_id = ?");
        $stmt->execute([$module, $moduleId]);
        return $files;
    }
}
