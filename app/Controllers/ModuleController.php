<?php
namespace App\Controllers;

use App\Services\PermissionService;

class ModuleController {
    private $permService;

    public function __construct() {
        $this->permService = new PermissionService();
    }

    public function index() {
        checkPermission('modules', 'can_view');
        $modules = $this->permService->getAllModules();
        $pageTitle = 'Kelola Menu / Modul';
        require ROOT_PATH . '/views/modules/index.php';
    }

    public function create() {
        checkPermission('modules', 'can_add');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $v = validate($_POST, [
            'name' => 'required|string|min:2|max:100',
            'slug' => 'required|string|min:2|max:50|alpha_dash',
            'url' => 'required|string|max:200',
            'icon' => 'string|max:50',
            'sort_order' => 'integer|min:0',
        ], [], [
            'name' => 'nama modul',
            'slug' => 'slug',
            'url' => 'URL',
        ]);

        if ($v->fails()) {
            flash('error', $v->firstError());
            redirect('modules');
        }

        $data = $this->getModuleData();
        $this->permService->createModule($data);
        loadPermissions((int)auth('role_id'));

        flash('success', 'Modul berhasil ditambahkan.');
        redirect('modules');
    }

    public function update($id) {
        checkPermission('modules', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $v = validate($_POST, [
            'name' => 'required|string|min:2|max:100',
            'slug' => 'required|string|min:2|max:50|alpha_dash',
            'url' => 'required|string|max:200',
            'icon' => 'string|max:50',
            'sort_order' => 'integer|min:0',
        ], [], [
            'name' => 'nama modul',
            'slug' => 'slug',
            'url' => 'URL',
        ]);

        if ($v->fails()) {
            flash('error', $v->firstError());
            redirect('modules');
        }

        $data = $this->getModuleData();
        $this->permService->updateModule($id, $data);
        loadPermissions((int)auth('role_id'));

        flash('success', 'Modul berhasil diupdate.');
        redirect('modules');
    }

    public function delete($id) {
        checkPermission('modules', 'can_delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $this->permService->deleteModule($id);
        loadPermissions((int)auth('role_id'));

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
