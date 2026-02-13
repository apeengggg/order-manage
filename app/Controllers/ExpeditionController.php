<?php
namespace App\Controllers;

use App\Services\ExpeditionService;
use App\Services\FileService;

class ExpeditionController {
    private $expeditionService;
    private $fileService;

    public function __construct() {
        $this->expeditionService = new ExpeditionService();
        $this->fileService = new FileService();
    }

    public function index() {
        checkPermission('expeditions', 'can_view');
        $expeditions = $this->expeditionService->getAll();

        // Load photos for all expeditions
        $expIds = array_column($expeditions, 'id');
        $photosMap = $this->fileService->getFilesMap('expeditions', $expIds);

        $pageTitle = 'Kelola Ekspedisi';
        require ROOT_PATH . '/views/expeditions/index.php';
    }

    public function create() {
        checkPermission('expeditions', 'can_add');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $v = validate($_POST, [
                'name' => 'required|string|min:2|max:100',
                'code' => 'required|string|min:2|max:20|alpha_dash',
            ], [], [
                'name' => 'nama ekspedisi',
                'code' => 'kode ekspedisi',
            ]);

            if ($v->fails()) {
                flash('error', $v->firstError());
                redirect('expeditions');
            }

            $data = $v->validated();
            $newId = $this->expeditionService->create($data);

            // Upload photo if provided
            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $result = $this->fileService->upload($_FILES['photo'], 'expeditions', $newId, auth('user_id'));
                if (!$result['success']) {
                    flash('warning', 'Ekspedisi ditambahkan, tapi foto gagal: ' . $result['message']);
                    redirect('expeditions');
                }
            }

            flash('success', 'Ekspedisi berhasil ditambahkan.');
        }
        redirect('expeditions');
    }

    public function update($id) {
        checkPermission('expeditions', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $v = validate($_POST, [
                'name' => 'required|string|min:2|max:100',
                'code' => 'required|string|min:2|max:20|alpha_dash',
            ], [], [
                'name' => 'nama ekspedisi',
                'code' => 'kode ekspedisi',
            ]);

            if ($v->fails()) {
                flash('error', $v->firstError());
                redirect('expeditions');
            }

            $data = $v->validated();
            $this->expeditionService->update($id, $data);

            // Upload new photo if provided (replaces old)
            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $result = $this->fileService->replaceFile($_FILES['photo'], 'expeditions', (int)$id, auth('user_id'));
                if (!$result['success']) {
                    flash('warning', 'Ekspedisi diupdate, tapi foto gagal: ' . $result['message']);
                    redirect('expeditions');
                }
            }

            flash('success', 'Ekspedisi berhasil diupdate.');
        }
        redirect('expeditions');
    }

    public function delete($id) {
        checkPermission('expeditions', 'can_delete');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete associated files
            $this->fileService->deleteModuleFiles('expeditions', (int)$id);
            $this->expeditionService->delete($id);
            flash('success', 'Ekspedisi berhasil dihapus.');
        }
        redirect('expeditions');
    }
}
