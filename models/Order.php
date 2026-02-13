<?php
class Order {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll($filters = []) {
        $sql = "SELECT o.*, e.name as expedition_name, e.code as expedition_code,
                       u.name as created_by_name, ue.name as exported_by_name
                FROM orders o
                LEFT JOIN expeditions e ON o.expedition_id = e.id
                LEFT JOIN users u ON o.created_by = u.id
                LEFT JOIN users ue ON o.exported_by = ue.id
                WHERE 1=1";
        $params = [];

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

    public function find($id) {
        $stmt = $this->db->prepare(
            "SELECT o.*, e.name as expedition_name
             FROM orders o
             LEFT JOIN expeditions e ON o.expedition_id = e.id
             WHERE o.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO orders (customer_name, customer_phone, customer_address, product_name, qty, price, total, expedition_id, resi, notes, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['customer_name'],
            $data['customer_phone'],
            $data['customer_address'],
            $data['product_name'],
            $data['qty'],
            $data['price'],
            $data['qty'] * $data['price'],
            $data['expedition_id'] ?: null,
            $data['resi'] ?? null,
            $data['notes'] ?? null,
            $data['created_by']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        // Check if exported - block edit
        $order = $this->find($id);
        if ($order && $order['is_exported']) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE orders SET customer_name=?, customer_phone=?, customer_address=?, product_name=?, qty=?, price=?, total=?, expedition_id=?, resi=?, notes=?
             WHERE id=? AND is_exported=0"
        );
        return $stmt->execute([
            $data['customer_name'],
            $data['customer_phone'],
            $data['customer_address'],
            $data['product_name'],
            $data['qty'],
            $data['price'],
            $data['qty'] * $data['price'],
            $data['expedition_id'] ?: null,
            $data['resi'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        // Check if exported - block delete
        $order = $this->find($id);
        if ($order && $order['is_exported']) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM orders WHERE id=? AND is_exported=0");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function markExported($ids, $userId) {
        if (empty($ids)) return false;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge($ids, [$userId]);
        $stmt = $this->db->prepare(
            "UPDATE orders SET is_exported=1, exported_at=NOW(), exported_by=?
             WHERE id IN ($placeholders) AND is_exported=0"
        );
        // Reorder: exported_by first then ids
        $stmt2 = $this->db->prepare(
            "UPDATE orders SET is_exported=1, exported_at=NOW(), exported_by=?
             WHERE id IN ($placeholders) AND is_exported=0"
        );
        $stmt2->execute(array_merge([$userId], $ids));
        return $stmt2->rowCount();
    }

    public function getByExpedition($expeditionId, $exportedOnly = false) {
        $sql = "SELECT o.*, e.name as expedition_name, e.code as expedition_code, u.name as created_by_name
                FROM orders o
                LEFT JOIN expeditions e ON o.expedition_id = e.id
                LEFT JOIN users u ON o.created_by = u.id
                WHERE o.expedition_id = ?";
        $params = [$expeditionId];

        if (!$exportedOnly) {
            $sql .= " AND o.is_exported = 0";
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll() {
        return $this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    }

    public function countExported() {
        return $this->db->query("SELECT COUNT(*) FROM orders WHERE is_exported=1")->fetchColumn();
    }

    public function countPending() {
        return $this->db->query("SELECT COUNT(*) FROM orders WHERE is_exported=0")->fetchColumn();
    }

    public function totalRevenue() {
        return $this->db->query("SELECT COALESCE(SUM(total),0) FROM orders")->fetchColumn();
    }
}
