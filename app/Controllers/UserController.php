<?php
namespace App\Controllers;

use App\Services\UserService;

class UserController {
    private $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function index() {
        checkPermission('users', 'can_view');

        $users = $this->userService->getAll();
        $roles = $this->userService->getAllRoles();
        $pageTitle = 'Kelola User';
        require ROOT_PATH . '/views/users/index.php';
    }

    public function create() {
        checkPermission('users', 'can_add');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');

        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (empty($username) || empty($name) || empty($password) || $roleId <= 0) {
            flash('error', 'Semua field wajib diisi.');
            redirect('users');
        }

        if (strlen($password) < 6) {
            flash('error', 'Password minimal 6 karakter.');
            redirect('users');
        }

        if ($this->userService->usernameExists($username)) {
            flash('error', 'Username "' . $username . '" sudah digunakan.');
            redirect('users');
        }

        $this->userService->create([
            'username' => $username,
            'name' => $name,
            'password' => $password,
            'role_id' => $roleId,
        ]);

        flash('success', 'User "' . $name . '" berhasil ditambahkan.');
        redirect('users');
    }

    public function update($id) {
        checkPermission('users', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');

        $id = (int)$id;
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (empty($username) || empty($name) || $roleId <= 0) {
            flash('error', 'Semua field wajib diisi.');
            redirect('users');
        }

        if ($this->userService->usernameExists($username, $id)) {
            flash('error', 'Username "' . $username . '" sudah digunakan.');
            redirect('users');
        }

        $this->userService->update($id, [
            'username' => $username,
            'name' => $name,
            'role_id' => $roleId,
        ]);

        // If editing self, update session
        if ($id === (int)auth('user_id')) {
            $_SESSION['username'] = $username;
            $_SESSION['name'] = $name;
            $_SESSION['role_id'] = $roleId;
            // Reload role info
            $user = $this->userService->find($id);
            if ($user) {
                $_SESSION['role_name'] = $user['role_name'];
                $_SESSION['role_slug'] = $user['role_slug'];
            }
            loadPermissions($roleId);
        }

        flash('success', 'User berhasil diupdate.');
        redirect('users');
    }

    public function changePassword($id) {
        checkPermission('users', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');

        $id = (int)$id;
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';

        if (empty($password)) {
            flash('error', 'Password wajib diisi.');
            redirect('users');
        }

        if (strlen($password) < 6) {
            flash('error', 'Password minimal 6 karakter.');
            redirect('users');
        }

        if ($password !== $confirm) {
            flash('error', 'Konfirmasi password tidak cocok.');
            redirect('users');
        }

        $this->userService->changePassword($id, $password);
        flash('success', 'Password berhasil diubah.');
        redirect('users');
    }

    public function delete($id) {
        checkPermission('users', 'can_delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');

        $id = (int)$id;
        $result = $this->userService->delete($id, (int)auth('user_id'));

        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect('users');
    }
}
