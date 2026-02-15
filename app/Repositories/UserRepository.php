<?php
namespace App\Repositories;

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
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
}
