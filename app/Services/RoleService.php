<?php
namespace App\Services;

use App\Repositories\RoleRepository;
use App\Repositories\PermissionRepository;

class RoleService {
    private $roleRepo;
    private $permRepo;
    private AuditService $audit;

    public function __construct() {
        $this->roleRepo = new RoleRepository();
        $this->permRepo = new PermissionRepository();
        $this->audit = new AuditService();
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

        $this->audit->log('create', 'role', $roleId, $data['name'] ?? '', null, $data);

        return $roleId;
    }

    public function update(int $id, array $data): bool {
        $old = $this->roleRepo->findById($id);
        $result = $this->roleRepo->update($id, $data);

        if ($result && $old) {
            $this->audit->log('update', 'role', $id, $old['name'] ?? '', $old, $data);
        }

        return $result;
    }

    public function delete(int $id): array {
        $userCount = $this->roleRepo->countUsers($id);
        if ($userCount > 0) {
            return ['success' => false, 'message' => "Role masih digunakan oleh $userCount user. Pindahkan user ke role lain terlebih dahulu."];
        }

        $old = $this->roleRepo->findById($id);
        $this->roleRepo->delete($id);

        if ($old) {
            $this->audit->log('delete', 'role', $id, $old['name'] ?? '', $old, null);
        }

        return ['success' => true, 'message' => 'Role berhasil dihapus.'];
    }
}
