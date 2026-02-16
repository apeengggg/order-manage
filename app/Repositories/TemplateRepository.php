<?php
namespace App\Repositories;

use App\TenantContext;
use PDO;

class TemplateRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findByExpeditionId(int $expeditionId): ?array {
        $globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        if ($globalView) {
            $stmt = $this->db->prepare("SELECT * FROM expedition_templates WHERE expedition_id = ?");
            $stmt->execute([$expeditionId]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM expedition_templates WHERE expedition_id = ? AND tenant_id = ?");
            $stmt->execute([$expeditionId, TenantContext::effectiveTenantId()]);
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByExpeditionIds(array $ids): array {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        if ($globalView) {
            $stmt = $this->db->prepare(
                "SELECT expedition_id, id, sheet_name, columns, file_id
                 FROM expedition_templates WHERE expedition_id IN ($placeholders)"
            );
            $stmt->execute($ids);
        } else {
            $params = array_merge($ids, [TenantContext::effectiveTenantId()]);
            $stmt = $this->db->prepare(
                "SELECT expedition_id, id, sheet_name, columns, file_id
                 FROM expedition_templates WHERE expedition_id IN ($placeholders) AND tenant_id = ?"
            );
            $stmt->execute($params);
        }
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['expedition_id']] = $row;
        }
        return $map;
    }

    public function upsert(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO expedition_templates (tenant_id, expedition_id, file_id, sheet_name, columns, uploaded_by)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                file_id = VALUES(file_id),
                sheet_name = VALUES(sheet_name),
                `columns` = VALUES(`columns`),
                uploaded_by = VALUES(uploaded_by)"
        );
        $stmt->execute([
            TenantContext::id(),
            $data['expedition_id'],
            $data['file_id'] ?? null,
            $data['sheet_name'],
            $data['columns'],
            $data['uploaded_by'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateColumns(int $expeditionId, string $columnsJson): bool {
        $stmt = $this->db->prepare(
            "UPDATE expedition_templates SET `columns` = ? WHERE expedition_id = ? AND tenant_id = ?"
        );
        return $stmt->execute([$columnsJson, $expeditionId, TenantContext::id()]);
    }

    public function delete(int $expeditionId): bool {
        $stmt = $this->db->prepare("DELETE FROM expedition_templates WHERE expedition_id = ? AND tenant_id = ?");
        return $stmt->execute([$expeditionId, TenantContext::id()]);
    }
}
