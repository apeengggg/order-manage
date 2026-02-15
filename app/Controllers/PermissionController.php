<?php
namespace App\Controllers;

use App\Services\PermissionService;

class PermissionController {
    private $permService;

    public function __construct() {
        $this->permService = new PermissionService();
    }

    public function index() {
        checkPermission('permissions', 'can_view');

        $modules = $this->permService->getAllModules();
        $allPermissions = $this->permService->getAllPermissions();
        $roles = $this->permService->getRoles();

        $pageTitle = 'Kelola Permission';
        require ROOT_PATH . '/views/permissions/index.php';
    }

    public function update() {
        checkPermission('permissions', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('permissions');

        $roleId = (int)($_POST['role_id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];

        if (empty($roleId)) {
            flash('error', 'Role tidak valid.');
            redirect('permissions');
        }

        $modules = $this->permService->getAllModules();
        $this->permService->updateRolePermissions($roleId, $modules, $permissions);

        // Reload permissions if editing current user's role
        if ($roleId === (int)auth('role_id')) {
            loadPermissions($roleId);
        }

        flash('success', 'Permission berhasil diupdate.');
        redirect('permissions');
    }
}
