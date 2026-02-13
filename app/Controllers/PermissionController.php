<?php
namespace App\Controllers;

use App\Models\Permission;

class PermissionController {
    private $permission;

    public function __construct() {
        $this->permission = new Permission();
    }

    public function index() {
        checkPermission('permissions', 'can_view');

        $modules = $this->permission->getAllModules();
        $allPermissions = $this->permission->getAllPermissions();
        $roles = $this->permission->getRoles();

        $pageTitle = 'Kelola Permission';
        require __DIR__ . '/../../views/permissions/index.php';
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

        $modules = $this->permission->getAllModules();

        foreach ($modules as $module) {
            $mid = $module['id'];
            $perms = [
                'can_view'        => isset($permissions[$mid]['can_view']) ? 1 : 0,
                'can_add'         => isset($permissions[$mid]['can_add']) ? 1 : 0,
                'can_edit'        => isset($permissions[$mid]['can_edit']) ? 1 : 0,
                'can_delete'      => isset($permissions[$mid]['can_delete']) ? 1 : 0,
                'can_view_detail' => isset($permissions[$mid]['can_view_detail']) ? 1 : 0,
                'can_upload'      => isset($permissions[$mid]['can_upload']) ? 1 : 0,
                'can_download'    => isset($permissions[$mid]['can_download']) ? 1 : 0,
            ];
            $this->permission->updatePermission($role, $mid, $perms);
        }

        if ($role === auth('role')) {
            loadPermissions($role);
        }

        flash('success', 'Permission untuk role "' . strtoupper($role) . '" berhasil diupdate.');
        redirect('permissions');
    }
}
