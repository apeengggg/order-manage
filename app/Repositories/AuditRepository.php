<?php
namespace App\Repositories;

use App\TenantContext;

class AuditRepository {
    private $db;
    private bool $globalView;
    private bool $showTenantName;

    public function __construct() {
        $this->db = getDB();
        $this->globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        $this->showTenantName = TenantContext::isSuperAdmin();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO audit_logs (tenant_id, user_id, user_name, action, entity_type, entity_id, entity_label, old_values, new_values, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['tenant_id'],
            $data['user_id'],
            $data['user_name'],
            $data['action'],
            $data['entity_type'],
            $data['entity_id'],
            $data['entity_label'] ?? null,
            $data['old_values'] ? json_encode($data['old_values'], JSON_UNESCAPED_UNICODE) : null,
            $data['new_values'] ? json_encode($data['new_values'], JSON_UNESCAPED_UNICODE) : null,
            $data['ip_address'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findAll(array $filters = [], int $limit = 50, int $offset = 0): array {
        $sql = "SELECT a.*";
        if ($this->showTenantName) {
            $sql .= ", t.name as tenant_name";
        }
        $sql .= " FROM audit_logs a";
        if ($this->showTenantName) {
            $sql .= " LEFT JOIN tenants t ON a.tenant_id = t.id";
        }

        $conditions = [];
        $params = [];

        if (!$this->globalView) {
            $conditions[] = "a.tenant_id = ?";
            $params[] = TenantContext::effectiveTenantId();
        }

        if (!empty($filters['entity_type'])) {
            $conditions[] = "a.entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        if (!empty($filters['action'])) {
            $conditions[] = "a.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "a.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "a.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "a.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $conditions[] = "(a.entity_label LIKE ? OR a.user_name LIKE ? OR a.entity_id LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM audit_logs a";

        $conditions = [];
        $params = [];

        if (!$this->globalView) {
            $conditions[] = "a.tenant_id = ?";
            $params[] = TenantContext::effectiveTenantId();
        }

        if (!empty($filters['entity_type'])) {
            $conditions[] = "a.entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        if (!empty($filters['action'])) {
            $conditions[] = "a.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['user_id'])) {
            $conditions[] = "a.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = "a.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "a.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $conditions[] = "(a.entity_label LIKE ? OR a.user_name LIKE ? OR a.entity_id LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findByEntity(string $type, string $id): array {
        $sql = "SELECT * FROM audit_logs WHERE entity_type = ? AND entity_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type, $id]);
        return $stmt->fetchAll();
    }
}
