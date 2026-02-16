<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;

class UserService {
    private $userRepo;
    private $roleRepo;
    private AuditService $audit;

    public function __construct() {
        $this->userRepo = new UserRepository();
        $this->roleRepo = new RoleRepository();
        $this->audit = new AuditService();
    }

    public function getAll(): array {
        return $this->userRepo->findAll();
    }

    public function find(int $id): ?array {
        return $this->userRepo->findById($id);
    }

    public function getAllRoles(): array {
        return $this->roleRepo->findAll();
    }

    public function create(array $data): int {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $id = $this->userRepo->create($data);

        $logData = $data;
        unset($logData['password']);
        $this->audit->log('create', 'user', $id, $data['username'] ?? '', null, $logData);

        return $id;
    }

    public function update(int $id, array $data): bool {
        $old = $this->userRepo->findById($id);
        $result = $this->userRepo->update($id, $data);

        if ($result && $old) {
            $this->audit->log('update', 'user', $id, $old['username'] ?? '', $old, $data);
        }

        return $result;
    }

    public function changePassword(int $id, string $password): bool {
        $user = $this->userRepo->findById($id);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $result = $this->userRepo->updatePassword($id, $hash);

        if ($result && $user) {
            $this->audit->log('update', 'user', $id, $user['username'] ?? '', null, ['password' => '***changed***']);
        }

        return $result;
    }

    public function delete(int $id, int $currentUserId): array {
        if ($id === $currentUserId) {
            return ['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'];
        }

        $old = $this->userRepo->findById($id);
        $this->userRepo->delete($id);

        if ($old) {
            $this->audit->log('delete', 'user', $id, $old['username'] ?? '', $old, null);
        }

        return ['success' => true, 'message' => 'User berhasil dihapus.'];
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool {
        return $this->userRepo->usernameExists($username, $excludeId);
    }
}
