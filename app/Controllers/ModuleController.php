<?php
namespace App\Controllers;

use App\Services\PermissionService;

class ModuleController {
    private $permService;

    public function __construct() {
        $this->permService = new PermissionService();
    }

    public function index() {
        checkPermission('permissions', 'can_view');
        $modules = $this->permService->getAllModules();
        $pageTitle = 'Kelola Menu / Modul';
        require ROOT_PATH . '/views/modules/index.php';
    }

    public function create() {
        checkPermission('permissions', 'can_add');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $data = $this->getModuleData();

        if (empty($data['name']) || empty($data['slug']) || empty($data['url'])) {
            flash('error', 'Nama, slug, dan URL harus diisi.');
            redirect('modules');
        }

        $this->permService->createModule($data);
        loadPermissions(auth('role'));

        flash('success', 'Modul berhasil ditambahkan.');
        redirect('modules');
    }

    public function update($id) {
        checkPermission('permissions', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $data = $this->getModuleData();
        $this->permService->updateModule($id, $data);
        loadPermissions(auth('role'));

        flash('success', 'Modul berhasil diupdate.');
        redirect('modules');
    }

    public function delete($id) {
        checkPermission('permissions', 'can_delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $this->permService->deleteModule($id);
        loadPermissions(auth('role'));

        flash('success', 'Modul berhasil dihapus.');
        redirect('modules');
    }

    private function getModuleData(): array {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'icon' => trim($_POST['icon'] ?? 'fas fa-circle'),
            'url' => trim($_POST['url'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
    }
}
