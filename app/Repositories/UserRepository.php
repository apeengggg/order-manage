<?php
namespace App\Repositories;

use PDO;

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(): array {
        return $this->db->query(
            "SELECT u.*, r.name as role_name, r.slug as role_slug
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             ORDER BY u.id"
        )->fetchAll();
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
            "INSERT INTO users (username, password, name, role_id) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['username'],
            $data['password'],
            $data['name'],
            $data['role_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET username=?, name=?, role_id=? WHERE id=?"
        );
        return $stmt->execute([$data['username'], $data['name'], $data['role_id'], $id]);
    }

    public function updatePassword(int $id, string $hash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=?");
        return $stmt->execute([$hash, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
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
