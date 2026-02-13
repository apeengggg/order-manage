<?php
namespace App\Controllers;

use App\Models\Expedition;

class ExpeditionController {
    private $expedition;

    public function __construct() {
        $this->expedition = new Expedition();
    }

    public function index() {
        checkPermission('expeditions', 'can_view');
        $expeditions = $this->expedition->getAll();
        $pageTitle = 'Kelola Ekspedisi';
        require __DIR__ . '/../../views/expeditions/index.php';
    }

    public function create() {
        checkPermission('expeditions', 'can_add');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'code' => trim($_POST['code'] ?? '')
            ];
            if (empty($data['name']) || empty($data['code'])) {
                flash('error', 'Nama dan kode ekspedisi harus diisi.');
                redirect('expeditions');
            }
            $this->expedition->create($data);
            flash('success', 'Ekspedisi berhasil ditambahkan.');
        }
        redirect('expeditions');
    }

    public function update($id) {
        checkPermission('expeditions', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'code' => trim($_POST['code'] ?? '')
            ];
            $this->expedition->update($id, $data);
            flash('success', 'Ekspedisi berhasil diupdate.');
        }
        redirect('expeditions');
    }

    public function delete($id) {
        checkPermission('expeditions', 'can_delete');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->expedition->delete($id);
            flash('success', 'Ekspedisi berhasil dihapus.');
        }
        redirect('expeditions');
    }
}
