<?php
namespace App\Repositories;

use App\TenantContext;

class OrderRepository {
    private $db;
    private bool $globalView;
    private bool $showTenantName;

    public function __construct() {
        $this->db = getDB();
        $this->globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        $this->showTenantName = TenantContext::isSuperAdmin();
    }

    private function tenantWhere(string $alias = ''): string {
        $col = $alias ? "$alias.tenant_id" : "tenant_id";
        return $this->globalView ? "1=1" : "$col = ?";
    }

    private function tenantParams(): array {
        return $this->globalView ? [] : [TenantContext::effectiveTenantId()];
    }

    public function findAll(array $filters = []): array {
        $sql = "SELECT o.*, e.name as expedition_name, e.code as expedition_code,
                       u.name as created_by_name, ue.name as exported_by_name";
        if ($this->showTenantName) {
            $sql .= ", t.name as tenant_name";
        }
        $sql .= " FROM orders o
                LEFT JOIN expeditions e ON o.expedition_id = e.id
                LEFT JOIN users u ON o.created_by = u.id
                LEFT JOIN users ue ON o.exported_by = ue.id";
        if ($this->showTenantName) {
            $sql .= " LEFT JOIN tenants t ON o.tenant_id = t.id";
        }
        $sql .= " WHERE " . $this->tenantWhere('o');
        $params = $this->tenantParams();

        if (!empty($filters['search'])) {
            $sql .= " AND (o.customer_name LIKE ? OR o.customer_phone LIKE ? OR o.product_name LIKE ? OR o.resi LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }
        if (!empty($filters['expedition_id'])) {
            $sql .= " AND o.expedition_id = ?";
            $params[] = $filters['expedition_id'];
        }
        if (isset($filters['is_exported']) && $filters['is_exported'] !== '') {
            $sql .= " AND o.is_exported = ?";
            $params[] = $filters['is_exported'];
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $sql = "SELECT o.*, e.name as expedition_name
                FROM orders o LEFT JOIN expeditions e ON o.expedition_id = e.id
                WHERE o.id = ? AND " . $this->tenantWhere('o');
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$id], $this->tenantParams()));
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO orders (tenant_id, customer_name, customer_phone, customer_address, product_name, qty, price, total, expedition_id, resi, notes, extra_fields, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            TenantContext::id(),
            $data['customer_name'], $data['customer_phone'], $data['customer_address'],
            $data['product_name'], $data['qty'], $data['price'], $data['total'],
            $data['expedition_id'], $data['resi'], $data['notes'],
            $data['extra_fields'] ?? null, $data['created_by']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE orders SET customer_name=?, customer_phone=?, customer_address=?, product_name=?, qty=?, price=?, total=?, expedition_id=?, resi=?, notes=?, extra_fields=?
             WHERE id=? AND tenant_id=? AND is_exported=0"
        );
        $stmt->execute([
            $data['customer_name'], $data['customer_phone'], $data['customer_address'],
            $data['product_name'], $data['qty'], $data['price'], $data['total'],
            $data['expedition_id'], $data['resi'], $data['notes'],
            $data['extra_fields'] ?? null, $id, TenantContext::id()
        ]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM orders WHERE id=? AND tenant_id=? AND is_exported=0");
        $stmt->execute([$id, TenantContext::id()]);
        return $stmt->rowCount() > 0;
    }

    public function markExported(array $ids, int $userId): int {
        if (empty($ids)) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "UPDATE orders SET is_exported=1, exported_at=NOW(), exported_by=?
             WHERE id IN ($placeholders) AND tenant_id=? AND is_exported=0"
        );
        $stmt->execute(array_merge([$userId], $ids, [TenantContext::id()]));
        return $stmt->rowCount();
    }

    public function findByExpedition(int $expeditionId, bool $exportedOnly = false): array {
        $sql = "SELECT o.*, e.name as expedition_name, e.code as expedition_code, u.name as created_by_name
                FROM orders o
                LEFT JOIN expeditions e ON o.expedition_id = e.id
                LEFT JOIN users u ON o.created_by = u.id
                WHERE o.expedition_id = ? AND " . $this->tenantWhere('o');
        $params = array_merge([$expeditionId], $this->tenantParams());
        if (!$exportedOnly) $sql .= " AND o.is_exported = 0";
        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(): int {
        $sql = "SELECT COUNT(*) FROM orders WHERE " . $this->tenantWhere();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->tenantParams());
        return (int)$stmt->fetchColumn();
    }

    public function countExported(): int {
        $sql = "SELECT COUNT(*) FROM orders WHERE " . $this->tenantWhere() . " AND is_exported=1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->tenantParams());
        return (int)$stmt->fetchColumn();
    }

    public function countPending(): int {
        $sql = "SELECT COUNT(*) FROM orders WHERE " . $this->tenantWhere() . " AND is_exported=0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->tenantParams());
        return (int)$stmt->fetchColumn();
    }

    public function totalRevenue(): float {
        $sql = "SELECT COALESCE(SUM(total),0) FROM orders WHERE " . $this->tenantWhere();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->tenantParams());
        return (float)$stmt->fetchColumn();
    }
}
