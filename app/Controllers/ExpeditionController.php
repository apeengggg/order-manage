<?php
namespace App\Controllers;

use App\Services\ExpeditionService;

class ExpeditionController {
    private $expeditionService;

    public function __construct() {
        $this->expeditionService = new ExpeditionService();
    }

    public function index() {
        checkPermission('expeditions', 'can_view');
        $expeditions = $this->expeditionService->getAll();
        $pageTitle = 'Kelola Ekspedisi';
        require ROOT_PATH . '/views/expeditions/index.php';
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
            $this->expeditionService->create($data);
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
            $this->expeditionService->update($id, $data);
            flash('success', 'Ekspedisi berhasil diupdate.');
        }
        redirect('expeditions');
    }

    public function delete($id) {
        checkPermission('expeditions', 'can_delete');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->expeditionService->delete($id);
            flash('success', 'Ekspedisi berhasil dihapus.');
        }
        redirect('expeditions');
    }
}
