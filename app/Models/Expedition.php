<?php
namespace App\Models;

use PDO;

class Expedition {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM expeditions WHERE is_active=1 ORDER BY name")->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM expeditions WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO expeditions (name, code) VALUES (?, ?)");
        return $stmt->execute([$data['name'], strtoupper($data['code'])]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE expeditions SET name=?, code=? WHERE id=?");
        return $stmt->execute([$data['name'], strtoupper($data['code']), $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE expeditions SET is_active=0 WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function getOrderCount($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE expedition_id=? AND is_exported=0");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }
}
