<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\TenantContext;

class AuthService {
    private $userRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
    }

    public function attempt(string $username, string $password): ?array {
        if (empty($username) || empty($password)) {
            return null;
        }

        $user = $this->userRepo->findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        // Check if user's tenant is active (skip for super admin with tenant_id = NULL)
        if ($user['tenant_id'] !== null) {
            $db = getDB();
            $stmt = $db->prepare("SELECT is_active FROM tenants WHERE id = ?");
            $stmt->execute([$user['tenant_id']]);
            $tenant = $stmt->fetch();
            if (!$tenant || !$tenant['is_active']) {
                return null; // tenant deactivated
            }
        }

        return $user;
    }

    public function login(array $user): void {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role_id'] = (int)$user['role_id'];
        $_SESSION['role_name'] = $user['role_name'] ?? 'User';
        $_SESSION['role_slug'] = $user['role_slug'] ?? '';
        $_SESSION['tenant_id'] = $user['tenant_id'];

        // Set tenant context
        TenantContext::set($user['tenant_id'] !== null ? (int)$user['tenant_id'] : null);

        loadPermissions((int)$user['role_id']);
    }

    public function logout(): void {
        session_destroy();
    }
}
