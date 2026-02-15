<?php
namespace App\Services;

use App\Repositories\PermissionRepository;

class PermissionService {
    private $permRepo;

    public function __construct() {
        $this->permRepo = new PermissionRepository();
    }

    public function getAllModules(): array {
        return $this->permRepo->findAllModules();
    }

    public function getAllPermissions(): array {
        return $this->permRepo->findAllPermissions();
    }

    public function getRoles(): array {
        return $this->permRepo->getRoles();
    }

    public function loadForRole(int $roleId): array {
        return $this->permRepo->loadPermissionsForRole($roleId);
    }

    public function updateRolePermissions(int $roleId, array $modules, array $formPermissions): void {
        foreach ($modules as $module) {
            $mid = $module['id'];
            $perms = [
                'can_view'        => isset($formPermissions[$mid]['can_view']) ? 1 : 0,
                'can_add'         => isset($formPermissions[$mid]['can_add']) ? 1 : 0,
                'can_edit'        => isset($formPermissions[$mid]['can_edit']) ? 1 : 0,
                'can_delete'      => isset($formPermissions[$mid]['can_delete']) ? 1 : 0,
                'can_view_detail' => isset($formPermissions[$mid]['can_view_detail']) ? 1 : 0,
                'can_upload'      => isset($formPermissions[$mid]['can_upload']) ? 1 : 0,
                'can_download'    => isset($formPermissions[$mid]['can_download']) ? 1 : 0,
            ];
            $this->permRepo->upsertPermission($roleId, $mid, $perms);
        }
    }

    public function createModule(array $data): int {
        $moduleId = $this->permRepo->createModule($data);

        // Auto-create permission rows for all roles
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            $perms = array_fill_keys(
                ['can_view','can_add','can_edit','can_delete','can_view_detail','can_upload','can_download'],
                0
            );
            $this->permRepo->upsertPermission($role['id'], $moduleId, $perms);
        }

        return $moduleId;
    }

    public function updateModule(int $id, array $data): bool {
        return $this->permRepo->updateModule($id, $data);
    }

    public function deleteModule(int $id): bool {
        return $this->permRepo->deleteModule($id);
    }
}
