<?php
namespace App\Controllers;

use App\Services\AuthService;

class AuthController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function index() {
        $this->login();
    }

    public function login() {
        if (isLoggedIn()) redirect('dashboard');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->authService->attempt($username, $password);

            if ($user) {
                $this->authService->login($user);
                redirect('dashboard');
            } else {
                flash('error', empty($username) || empty($password)
                    ? 'Username dan password harus diisi.'
                    : 'Username atau password salah.');
            }
        }

        require ROOT_PATH . '/views/auth/login.php';
    }

    public function logout() {
        $this->authService->logout();
        redirect('auth/login');
    }
}
