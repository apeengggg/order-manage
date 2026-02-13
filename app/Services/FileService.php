<?php
namespace App\Services;

use App\Repositories\FileRepository;
use App\Storage\StorageFactory;
use App\Storage\StorageInterface;

class FileService {
    private $fileRepo;
    private StorageInterface $storage;

    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv',
        'txt', 'zip', 'rar'
    ];

    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const THUMB_WIDTH = 200;
    private const THUMB_HEIGHT = 200;

    public function __construct() {
        $this->fileRepo = new FileRepository();
        $this->storage = StorageFactory::make();
    }

    /**
     * Upload a file for any module
     * @param array $file $_FILES['field_name']
     * @param string $module Module name e.g. 'expeditions', 'orders'
     * @param int $moduleId ID of the related record
     * @param int|null $uploadedBy User ID
     * @return array ['success' => bool, 'message' => string, 'file_id' => int|null]
     */
    public function upload(array $file, string $module, int $moduleId, ?int $uploadedBy = null): array {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => $this->getUploadErrorMessage($file['error']), 'file_id' => null];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'Ukuran file maksimal 5MB.', 'file_id' => null];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Allowed: ' . implode(', ', self::ALLOWED_EXTENSIONS), 'file_id' => null];
        }

        // Generate unique filename
        $storedName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $relativePath = $module . '/' . $moduleId . '/' . $storedName;

        // Store file via storage driver
        if (!$this->storage->put($file['tmp_name'], $relativePath)) {
            return ['success' => false, 'message' => 'Gagal menyimpan file.', 'file_id' => null];
        }

        // Generate thumbnail for images (in separate thumbnails/ folder)
        if (self::isImage($file['type'])) {
            $thumbRelPath = 'thumbnails/' . $module . '/' . $moduleId . '/' . $storedName;
            $this->createAndStoreThumbnail($file['tmp_name'], $thumbRelPath);
        }

        // Save to database
        $fileId = $this->fileRepo->create([
            'module' => $module,
            'module_id' => $moduleId,
            'file_name' => $file['name'],
            'file_path' => $relativePath,
            'file_type' => $file['type'],
            'file_size' => $file['size'],
            'uploaded_by' => $uploadedBy,
        ]);

        return ['success' => true, 'message' => 'File berhasil diupload.', 'file_id' => $fileId];
    }

    /**
     * Upload multiple files at once
     */
    public function uploadMultiple(array $files, string $module, int $moduleId, ?int $uploadedBy = null): array {
        $results = [];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
            $results[] = $this->upload($file, $module, $moduleId, $uploadedBy);
        }

        return $results;
    }

    /**
     * Get all files for a module record
     */
    public function getFiles(string $module, int $moduleId): array {
        return $this->fileRepo->findByModule($module, $moduleId);
    }

    /**
     * Get single file by ID
     */
    public function getFile(int $id): ?array {
        return $this->fileRepo->findById($id);
    }

    /**
     * Get a map of latest file per module_id (for listing pages)
     * @return array keyed by module_id
     */
    public function getFilesMap(string $module, array $moduleIds): array {
        $map = $this->fileRepo->findLatestByModuleIds($module, $moduleIds);
        foreach ($map as $moduleId => &$file) {
            $file['thumb_url'] = $this->getThumbnailUrl($file);
            $file['url'] = $this->getFileUrl($file);
        }
        return $map;
    }

    /**
     * Replace all files for a module record (delete old, upload new)
     */
    public function replaceFile(array $fileData, string $module, int $moduleId, ?int $uploadedBy = null): array {
        $this->deleteModuleFiles($module, $moduleId);
        return $this->upload($fileData, $module, $moduleId, $uploadedBy);
    }

    /**
     * Delete a file (DB + storage + thumbnail)
     */
    public function deleteFile(int $id): bool {
        $file = $this->fileRepo->findById($id);
        if (!$file) return false;

        $this->removeFromStorage($file);
        return $this->fileRepo->delete($id);
    }

    /**
     * Delete all files for a module record
     */
    public function deleteModuleFiles(string $module, int $moduleId): void {
        $files = $this->fileRepo->deleteByModule($module, $moduleId);
        foreach ($files as $file) {
            $this->removeFromStorage($file);
        }
    }

    /**
     * Get the URL for a file (original)
     */
    public function getFileUrl(array $file): string {
        return $this->storage->url($file['file_path']);
    }

    /**
     * Get thumbnail URL for a file
     */
    public function getThumbnailUrl(array $file): ?string {
        if (!self::isImage($file['file_type'])) return null;

        // Thumbnails stored in thumbnails/{module}/{id}/{filename}
        $thumbPath = 'thumbnails/' . $file['file_path'];

        if ($this->storage->exists($thumbPath)) {
            return $this->storage->url($thumbPath);
        }

        return $this->storage->url($file['file_path']); // fallback to original
    }

    /**
     * Get full path for download (local) or stream URL (minio)
     */
    public function getDownloadPath(array $file): string {
        return $this->storage->getFullPath($file['file_path']);
    }

    /**
     * Format file size for display
     */
    public static function formatSize(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Check if file is an image
     */
    public static function isImage(string $mimeType): bool {
        return strpos($mimeType, 'image/') === 0;
    }

    private function removeFromStorage(array $file): void {
        $this->storage->delete($file['file_path']);
        // Remove thumbnail from separate folder
        $thumbPath = 'thumbnails/' . $file['file_path'];
        $this->storage->delete($thumbPath);
    }

    /**
     * Create thumbnail and store via storage driver
     */
    private function createAndStoreThumbnail(string $sourcePath, string $thumbRelPath): bool {
        $info = getimagesize($sourcePath);
        if (!$info) return false;

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg': $source = imagecreatefromjpeg($sourcePath); break;
            case 'image/png':  $source = imagecreatefrompng($sourcePath); break;
            case 'image/gif':  $source = imagecreatefromgif($sourcePath); break;
            case 'image/webp': $source = imagecreatefromwebp($sourcePath); break;
            default: return false;
        }

        if (!$source) return false;

        $origW = imagesx($source);
        $origH = imagesy($source);

        $ratio = min(self::THUMB_WIDTH / $origW, self::THUMB_HEIGHT / $origH);
        $newW = (int)($origW * $ratio);
        $newH = (int)($origH * $ratio);

        $thumb = imagecreatetruecolor($newW, $newH);

        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $newW, $newH, $transparent);
        }

        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Save to temp file then store via driver
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('thumb_');
        switch ($mime) {
            case 'image/jpeg': imagejpeg($thumb, $tmpFile, 80); break;
            case 'image/png':  imagepng($thumb, $tmpFile, 8); break;
            case 'image/gif':  imagegif($thumb, $tmpFile); break;
            case 'image/webp': imagewebp($thumb, $tmpFile, 80); break;
        }

        imagedestroy($source);
        imagedestroy($thumb);

        $result = $this->storage->put($tmpFile, $thumbRelPath);
        @unlink($tmpFile);

        return $result;
    }

    private function getUploadErrorMessage(int $error): string {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi batas server).',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar.',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi.',
        ];
        return $messages[$error] ?? 'Upload error.';
    }
}
