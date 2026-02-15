<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;

class UserService {
    private $userRepo;
    private $roleRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
        $this->roleRepo = new RoleRepository();
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
        return $this->userRepo->create($data);
    }

    public function update(int $id, array $data): bool {
        return $this->userRepo->update($id, $data);
    }

    public function changePassword(int $id, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->userRepo->updatePassword($id, $hash);
    }

    public function delete(int $id, int $currentUserId): array {
        if ($id === $currentUserId) {
            return ['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri.'];
        }

        $this->userRepo->delete($id);
        return ['success' => true, 'message' => 'User berhasil dihapus.'];
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool {
        return $this->userRepo->usernameExists($username, $excludeId);
    }
}
