<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function index() {
        $this->login();
    }

    public function login() {
        if (isLoggedIn()) redirect('dashboard');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                flash('error', 'Username dan password harus diisi.');
                require __DIR__ . '/../views/auth/login.php';
                return;
            }

            $user = $this->user->findByUsername($username);
            if ($user && $this->user->verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                redirect('dashboard');
            } else {
                flash('error', 'Username atau password salah.');
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        redirect('auth/login');
    }
}
