<?php
namespace App\Controllers;

use App\Services\ExpeditionService;
use App\Services\FileService;
use App\Services\TemplateService;

class ExpeditionController {
    private $expeditionService;
    private $fileService;
    private $templateService;

    public function __construct() {
        $this->expeditionService = new ExpeditionService();
        $this->fileService = new FileService();
        $this->templateService = new TemplateService();
    }

    public function index() {
        checkPermission('expeditions', 'can_view');
        $expeditions = $this->expeditionService->getAll();

        // Load photos for all expeditions
        $expIds = array_column($expeditions, 'id');
        $photosMap = $this->fileService->getFilesMap('expeditions', $expIds);

        // Load template status for all expeditions
        $templateMap = $this->templateService->getTemplateMap($expIds);

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

            // Upload template if provided
            if (!empty($_FILES['template']) && $_FILES['template']['error'] === UPLOAD_ERR_OK) {
                $result = $this->templateService->parseAndStore($_FILES['template'], $newId, auth('user_id'));
                if (!$result['success']) {
                    flash('warning', 'Ekspedisi ditambahkan, tapi template gagal: ' . $result['message']);
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
            // Delete associated files and template
            $this->fileService->deleteModuleFiles('expeditions', (int)$id);
            $this->templateService->deleteTemplate((int)$id);
            $this->expeditionService->delete($id);
            flash('success', 'Ekspedisi berhasil dihapus.');
        }
        redirect('expeditions');
    }

    /**
     * AJAX: Upload template XLSX for an expedition
     * POST /expeditions/uploadTemplate/{id}
     */
    public function uploadTemplate($id) {
        checkPermission('expeditions', 'can_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        if (empty($_FILES['template']) || $_FILES['template']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => 'File template tidak ditemukan.']);
            return;
        }

        $result = $this->templateService->parseAndStore($_FILES['template'], (int)$id, auth('user_id'));
        $this->jsonResponse($result);
    }

    /**
     * AJAX: Get template columns for an expedition
     * GET /expeditions/getTemplate/{id}
     */
    public function getTemplate($id) {
        $columns = $this->templateService->getTemplateColumns((int)$id);
        $template = $this->templateService->getTemplate((int)$id);

        $this->jsonResponse([
            'success' => !empty($columns),
            'columns' => $columns,
            'sheet_name' => $template['sheet_name'] ?? null,
        ]);
    }

    /**
     * Download the original template XLSX file
     * GET /expeditions/downloadTemplate/{id}
     */
    public function downloadTemplate($id) {
        $template = $this->templateService->getTemplate((int)$id);
        if (!$template || !$template['file_id']) {
            flash('error', 'Template tidak ditemukan.');
            redirect('expeditions');
            return;
        }

        // Redirect to file download
        redirect('files/download/' . $template['file_id']);
    }

    /**
     * AJAX: Update template column properties
     * POST /expeditions/updateTemplateColumns/{id}
     */
    public function updateTemplateColumns($id) {
        checkPermission('expeditions', 'can_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input) || !isset($input['columns']) || !is_array($input['columns'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Data kolom tidak valid.']);
            return;
        }

        $result = $this->templateService->updateColumns((int)$id, $input['columns']);
        $this->jsonResponse($result);
    }

    private function jsonResponse(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
