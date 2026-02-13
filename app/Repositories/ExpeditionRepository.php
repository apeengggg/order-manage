<?php
namespace App\Repositories;

class ExpeditionRepository {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function findAll(): array {
        return $this->db->query("SELECT * FROM expeditions WHERE is_active=1 ORDER BY name")->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO expeditions (name, code) VALUES (?, ?)");
        return $stmt->execute([$data['name'], strtoupper($data['code'])]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE expeditions SET name=?, code=? WHERE id=?");
        return $stmt->execute([$data['name'], strtoupper($data['code']), $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE expeditions SET is_active=0 WHERE id=?");
        return $stmt->execute([$id]);
    }
}
