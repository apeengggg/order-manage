<?php
namespace App\Storage;

class LocalStorage implements StorageInterface {
    private string $basePath;

    public function __construct() {
        $this->basePath = ROOT_PATH . '/storage/uploads';

        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    public function put(string $sourcePath, string $destination): bool {
        $fullPath = $this->basePath . '/' . $destination;
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return copy($sourcePath, $fullPath);
    }

    public function delete(string $path): bool {
        $fullPath = $this->basePath . '/' . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    public function exists(string $path): bool {
        return file_exists($this->basePath . '/' . $path);
    }

    public function url(string $path): string {
        // Files are served via FileController by encoded path (no file extension in URL)
        return BASE_URL . 'files/serve?path=' . urlencode(str_replace('\\', '/', $path));
    }

    public function getFullPath(string $path): string {
        return $this->basePath . '/' . $path;
    }
}
