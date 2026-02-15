<?php
namespace App\Controllers;

use App\Services\FileService;

class FileController {
    private $fileService;

    public function __construct() {
        $this->fileService = new FileService();
    }

    /**
     * AJAX upload endpoint: POST /files/upload
     * Expects: file (multipart), module, module_id
     */
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $module = $_POST['module'] ?? '';
        $moduleId = (int)($_POST['module_id'] ?? 0);

        if (empty($module) || $moduleId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Module dan module_id wajib diisi.']);
            return;
        }

        // Check upload permission for the module
        if (!hasPermission($module, 'can_upload')) {
            $this->jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki izin upload.'], 403);
            return;
        }

        if (empty($_FILES['file'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Tidak ada file yang dipilih.']);
            return;
        }

        // Support multiple files
        if (is_array($_FILES['file']['name'])) {
            $results = $this->fileService->uploadMultiple($_FILES['file'], $module, $moduleId, auth('user_id'));
            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $failCount = count($results) - $successCount;
            $message = "$successCount file berhasil diupload.";
            if ($failCount > 0) $message .= " $failCount gagal.";
            $this->jsonResponse(['success' => $successCount > 0, 'message' => $message, 'results' => $results]);
        } else {
            $result = $this->fileService->upload($_FILES['file'], $module, $moduleId, auth('user_id'));
            $this->jsonResponse($result);
        }
    }

    /**
     * AJAX delete endpoint: POST /files/delete/{id}
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        $file = $this->fileService->getFile((int)$id);
        if (!$file) {
            $this->jsonResponse(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
            return;
        }

        // Check permission on the module
        if (!hasPermission($file['module'], 'can_upload')) {
            $this->jsonResponse(['success' => false, 'message' => 'Anda tidak memiliki izin menghapus file.'], 403);
            return;
        }

        $this->fileService->deleteFile((int)$id);
        $this->jsonResponse(['success' => true, 'message' => 'File berhasil dihapus.']);
    }

    /**
     * Get files for a module: GET /files/list?module=xxx&module_id=xxx
     */
    public function list() {
        $module = $_GET['module'] ?? '';
        $moduleId = (int)($_GET['module_id'] ?? 0);

        if (empty($module) || $moduleId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Parameter tidak valid.']);
            return;
        }

        $files = $this->fileService->getFiles($module, $moduleId);

        // Add display helpers
        foreach ($files as &$f) {
            $f['size_formatted'] = FileService::formatSize($f['file_size']);
            $f['is_image'] = FileService::isImage($f['file_type']);
            $f['url'] = $this->fileService->getFileUrl($f);
            $f['thumb_url'] = $this->fileService->getThumbnailUrl($f);
            $f['download_url'] = BASE_URL . 'files/download/' . $f['id'];
        }

        $this->jsonResponse(['success' => true, 'files' => $files]);
    }

    /**
     * Serve file inline (for thumbnails/preview): GET /files/serve/{path}
     */
    public function serve($id = null) {
        // Path comes from query string: /files/serve?path=expeditions/1/file.jpg
        $relativePath = $_GET['path'] ?? '';
        if (empty($relativePath)) {
            http_response_code(404);
            exit;
        }

        // Sanitize: remove directory traversal attempts
        $relativePath = str_replace(['../', '..\\', '..'], '', $relativePath);

        // Guard: only settings files (logo/bg) allowed without login
        // Paths may be tenant-prefixed: {tenant_id}/settings/... or settings/...
        if (!isLoggedIn()) {
            $isSettingsFile = (bool)preg_match('#^(\d+/)?settings/#', $relativePath)
                || (bool)preg_match('#^(\d+/)?thumbnails/(\d+/)?settings/#', $relativePath);
            if (!$isSettingsFile) {
                http_response_code(403);
                exit;
            }
        }

        $fullPath = ROOT_PATH . '/storage/uploads/' . $relativePath;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            http_response_code(404);
            exit;
        }

        // Security: ensure resolved path is within uploads directory
        $realBase = realpath(ROOT_PATH . '/storage/uploads');
        $realPath = realpath($fullPath);
        if ($realBase === false || $realPath === false || strpos($realPath, $realBase) !== 0) {
            http_response_code(404);
            exit;
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400');
        readfile($fullPath);
        exit;
    }

    /**
     * Download file (original size): GET /files/download/{id}
     */
    public function download($id) {
        $file = $this->fileService->getFile((int)$id);
        if (!$file) {
            flash('error', 'File tidak ditemukan.');
            redirect('dashboard');
            return;
        }

        $downloadPath = $this->fileService->getDownloadPath($file);

        // For MinIO/remote storage, redirect to the URL
        if (filter_var($downloadPath, FILTER_VALIDATE_URL)) {
            header('Location: ' . $downloadPath);
            exit;
        }

        // For local storage, serve the file
        if (!file_exists($downloadPath)) {
            flash('error', 'File tidak ditemukan di server.');
            redirect('dashboard');
            return;
        }

        header('Content-Type: ' . $file['file_type']);
        header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
        header('Content-Length: ' . $file['file_size']);
        readfile($downloadPath);
        exit;
    }

    private function jsonResponse(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
