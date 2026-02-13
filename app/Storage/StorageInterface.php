<?php
namespace App\Storage;

interface StorageInterface {
    /**
     * Store a file
     * @param string $sourcePath Temporary file path
     * @param string $destination Relative path e.g. "expeditions/1/abc.jpg"
     * @return bool
     */
    public function put(string $sourcePath, string $destination): bool;

    /**
     * Delete a file
     * @param string $path Relative path
     * @return bool
     */
    public function delete(string $path): bool;

    /**
     * Check if file exists
     */
    public function exists(string $path): bool;

    /**
     * Get public URL for a file
     */
    public function url(string $path): string;

    /**
     * Get full filesystem path (for local) or temp download (for remote)
     */
    public function getFullPath(string $path): string;
}
