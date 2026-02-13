<?php
namespace App\Services;

use App\Repositories\UserRepository;

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

        return $user;
    }

    public function login(array $user): void {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        loadPermissions($user['role']);
    }

    public function logout(): void {
        session_destroy();
    }
}
