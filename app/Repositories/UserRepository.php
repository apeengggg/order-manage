<?php
namespace App\Repositories;

use App\TenantContext;
use PDO;

class UserRepository {
    private $db;
    private bool $globalView;
    private bool $showTenantName;

    public function __construct() {
        $this->db = getDB();
        $this->globalView = TenantContext::isSuperAdmin() && !TenantContext::isFiltering();
        $this->showTenantName = TenantContext::isSuperAdmin();
    }

    public function findAll(): array {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug";
        if ($this->showTenantName) {
            $sql .= ", t.name as tenant_name";
        }
        $sql .= " FROM users u LEFT JOIN roles r ON r.id = u.role_id";
        if ($this->showTenantName) {
            $sql .= " LEFT JOIN tenants t ON u.tenant_id = t.id";
        }
        if ($this->globalView) {
            $sql .= " ORDER BY t.name, u.id";
            return $this->db->query($sql)->fetchAll();
        }
        $sql .= " WHERE u.tenant_id = ? ORDER BY u.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([TenantContext::effectiveTenantId()]);
        return $stmt->fetchAll();
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.name as role_name, r.slug as role_slug
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.username = ?"
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.name as role_name, r.slug as role_slug
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (tenant_id, username, password, name, role_id) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            TenantContext::id(),
            $data['username'],
            $data['password'],
            $data['name'],
            $data['role_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET username=?, name=?, role_id=? WHERE id=? AND tenant_id = ?"
        );
        return $stmt->execute([$data['username'], $data['name'], $data['role_id'], $id, TenantContext::id()]);
    }

    public function updatePassword(int $id, string $hash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=? AND tenant_id = ?");
        return $stmt->execute([$hash, $id, TenantContext::id()]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id=? AND tenant_id = ?");
        return $stmt->execute([$id, TenantContext::id()]);
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username=? AND id!=?");
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username=?");
            $stmt->execute([$username]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }
}
