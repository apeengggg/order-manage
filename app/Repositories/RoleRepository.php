<?php
namespace App\Repositories;

use PDO;

class RoleRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(): array {
        return $this->db->query("SELECT * FROM roles ORDER BY id")->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO roles (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? null]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE roles SET name=?, slug=?, description=? WHERE id=?");
        return $stmt->execute([$data['name'], $data['slug'], $data['description'] ?? null, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countUsers(int $roleId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return (int)$stmt->fetchColumn();
    }

    public function findAllWithUserCount(): array {
        return $this->db->query(
            "SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) as user_count
             FROM roles r ORDER BY r.id"
        )->fetchAll();
    }
}
