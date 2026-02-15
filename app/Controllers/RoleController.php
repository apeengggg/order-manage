<?php
namespace App\Controllers;

use App\Services\RoleService;

class RoleController {
    private $roleService;

    public function __construct() {
        $this->roleService = new RoleService();
    }

    public function index() {
        checkPermission('roles', 'can_view');

        $roles = $this->roleService->getAll();
        $pageTitle = 'Kelola Role';
        require ROOT_PATH . '/views/roles/index.php';
    }

    public function create() {
        checkPermission('roles', 'can_add');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('roles');

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name) || empty($slug)) {
            flash('error', 'Nama dan slug role wajib diisi.');
            redirect('roles');
        }

        // Sanitize slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '-', $slug));

        $this->roleService->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);

        flash('success', 'Role "' . $name . '" berhasil ditambahkan.');
        redirect('roles');
    }

    public function update($id) {
        checkPermission('roles', 'can_edit');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('roles');

        $id = (int)$id;
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name) || empty($slug)) {
            flash('error', 'Nama dan slug role wajib diisi.');
            redirect('roles');
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '-', $slug));

        $this->roleService->update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);

        flash('success', 'Role berhasil diupdate.');
        redirect('roles');
    }

    public function delete($id) {
        checkPermission('roles', 'can_delete');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('roles');

        $id = (int)$id;
        $result = $this->roleService->delete($id);

        flash($result['success'] ? 'success' : 'error', $result['message']);
        redirect('roles');
    }
}
