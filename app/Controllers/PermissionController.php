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

        $role = $_POST['role'] ?? '';
        $permissions = $_POST['permissions'] ?? [];

        if (empty($role)) {
            flash('error', 'Role tidak valid.');
            redirect('permissions');
        }

        $modules = $this->permService->getAllModules();
        $this->permService->updateRolePermissions($role, $modules, $permissions);

        if ($role === auth('role')) {
            loadPermissions($role);
        }

        flash('success', 'Permission untuk role "' . strtoupper($role) . '" berhasil diupdate.');
        redirect('permissions');
    }
}
