<?php
namespace App\Services;

use App\Repositories\SettingRepository;

class SettingService {
    private $settingRepo;
    private $fileService;
    private AuditService $audit;

    public function __construct() {
        $this->settingRepo = new SettingRepository();
        $this->fileService = new FileService();
        $this->audit = new AuditService();
    }

    public function getAll(): array {
        return $this->settingRepo->findAll();
    }

    public function get(string $key, $default = null): ?string {
        $val = $this->settingRepo->get($key);
        return $val !== null ? $val : $default;
    }

    public function set(string $key, ?string $value): void {
        $this->settingRepo->set($key, $value);
    }

    public function updateMultiple(array $data): void {
        $this->settingRepo->setMultiple($data);
    }

    public function uploadLogo(array $file): array {
        // Remove old logo
        $oldFileId = $this->get('logo_file_id');
        if ($oldFileId) {
            $this->fileService->deleteFile((int)$oldFileId);
        }

        $result = $this->fileService->upload($file, 'settings', 0, auth('user_id'));
        if ($result['success']) {
            $this->set('logo_file_id', (string)$result['file_id']);
        }
        return $result;
    }

    public function uploadLoginBg(array $file): array {
        $oldFileId = $this->get('login_bg_file_id');
        if ($oldFileId) {
            $this->fileService->deleteFile((int)$oldFileId);
        }

        $result = $this->fileService->upload($file, 'settings', 1, auth('user_id'));
        if ($result['success']) {
            $this->set('login_bg_file_id', (string)$result['file_id']);
        }
        return $result;
    }

    public function removeLogo(): void {
        $fileId = $this->get('logo_file_id');
        if ($fileId) {
            $this->fileService->deleteFile((int)$fileId);
            $this->set('logo_file_id', null);
        }
    }

    public function removeLoginBg(): void {
        $fileId = $this->get('login_bg_file_id');
        if ($fileId) {
            $this->fileService->deleteFile((int)$fileId);
            $this->set('login_bg_file_id', null);
        }
    }

    public function getLogoUrl(): ?string {
        $fileId = $this->get('logo_file_id');
        if (!$fileId) return null;

        $file = $this->fileService->getFile((int)$fileId);
        if (!$file) return null;

        $thumbUrl = $this->fileService->getThumbnailUrl($file);
        return $thumbUrl ?: $this->fileService->getFileUrl($file);
    }

    public function getLoginBgUrl(): ?string {
        $fileId = $this->get('login_bg_file_id');
        if (!$fileId) return null;

        $file = $this->fileService->getFile((int)$fileId);
        if (!$file) return null;

        return $this->fileService->getFileUrl($file);
    }
}
