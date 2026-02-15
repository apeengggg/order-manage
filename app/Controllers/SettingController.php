<?php
namespace App\Controllers;

use App\Services\SettingService;

class SettingController {
    private $settingService;

    public function __construct() {
        $this->settingService = new SettingService();
    }

    public function index() {
        checkPermission('settings', 'can_view');

        $settings = $this->settingService->getAll();
        $logoUrl = $this->settingService->getLogoUrl();
        $loginBgUrl = $this->settingService->getLoginBgUrl();
        $pageTitle = 'Pengaturan';
        require ROOT_PATH . '/views/settings/index.php';
    }

    public function update() {
        checkPermission('settings', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('settings');

        $data = [
            'app_name' => trim($_POST['app_name'] ?? ''),
            'primary_color' => trim($_POST['primary_color'] ?? '#007bff'),
            'login_bg_color' => trim($_POST['login_bg_color'] ?? '#667eea'),
        ];

        if (empty($data['app_name'])) {
            $data['app_name'] = 'Order Management System';
        }

        $this->settingService->updateMultiple($data);

        flash('success', 'Pengaturan berhasil disimpan.');
        redirect('settings');
    }

    public function uploadLogo() {
        checkPermission('settings', 'can_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'File tidak ditemukan.']);
            return;
        }

        $result = $this->settingService->uploadLogo($_FILES['logo']);

        if ($result['success']) {
            $result['url'] = $this->settingService->getLogoUrl();
        }

        $this->json($result);
    }

    public function uploadLoginBg() {
        checkPermission('settings', 'can_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['login_bg']) || $_FILES['login_bg']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'File tidak ditemukan.']);
            return;
        }

        $result = $this->settingService->uploadLoginBg($_FILES['login_bg']);

        if ($result['success']) {
            $result['url'] = $this->settingService->getLoginBgUrl();
        }

        $this->json($result);
    }

    public function removeLogo() {
        checkPermission('settings', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $this->settingService->removeLogo();
        $this->json(['success' => true, 'message' => 'Logo berhasil dihapus.']);
    }

    public function removeLoginBg() {
        checkPermission('settings', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $this->settingService->removeLoginBg();
        $this->json(['success' => true, 'message' => 'Background login berhasil dihapus.']);
    }

    private function json(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
