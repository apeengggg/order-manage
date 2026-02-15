<?php
namespace App\Services;

use App\Repositories\RoleRepository;
use App\Repositories\PermissionRepository;

class RoleService {
    private $roleRepo;
    private $permRepo;

    public function __construct() {
        $this->roleRepo = new RoleRepository();
        $this->permRepo = new PermissionRepository();
    }

    public function getAll(): array {
        return $this->roleRepo->findAllWithUserCount();
    }

    public function find(int $id): ?array {
        return $this->roleRepo->findById($id);
    }

    public function create(array $data): int {
        $roleId = $this->roleRepo->create($data);

        // Auto-create empty permission rows for all active modules
        $modules = $this->permRepo->findAllModules();
        foreach ($modules as $module) {
            $perms = array_fill_keys(
                ['can_view','can_add','can_edit','can_delete','can_view_detail','can_upload','can_download'],
                0
            );
            $this->permRepo->upsertPermission($roleId, $module['id'], $perms);
        }

        return $roleId;
    }

    public function update(int $id, array $data): bool {
        return $this->roleRepo->update($id, $data);
    }

    public function delete(int $id): array {
        $userCount = $this->roleRepo->countUsers($id);
        if ($userCount > 0) {
            return ['success' => false, 'message' => "Role masih digunakan oleh $userCount user. Pindahkan user ke role lain terlebih dahulu."];
        }

        $this->roleRepo->delete($id);
        return ['success' => true, 'message' => 'Role berhasil dihapus.'];
    }
}
