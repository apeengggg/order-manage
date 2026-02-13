<?php
namespace App\Controllers;

use App\Models\Permission;

class ModuleController {
    private $permission;

    public function __construct() {
        $this->permission = new Permission();
    }

    public function index() {
        checkPermission('permissions', 'can_view');
        $modules = $this->permission->getAllModules();
        $pageTitle = 'Kelola Menu / Modul';
        require __DIR__ . '/../../views/modules/index.php';
    }

    public function create() {
        checkPermission('permissions', 'can_add');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'icon' => trim($_POST['icon'] ?? 'fas fa-circle'),
            'url' => trim($_POST['url'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        if (empty($data['name']) || empty($data['slug']) || empty($data['url'])) {
            flash('error', 'Nama, slug, dan URL harus diisi.');
            redirect('modules');
        }

        $db = getDB();
        $stmt = $db->prepare("INSERT INTO modules (name, slug, icon, url, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['slug'], $data['icon'], $data['url'], $data['sort_order']]);

        // Auto-create permission rows for all roles
        $moduleId = $db->lastInsertId();
        $roles = $this->permission->getRoles();
        foreach ($roles as $role) {
            $this->permission->updatePermission($role, $moduleId, [
                'can_view' => $role === 'admin' ? 1 : 0,
                'can_add' => $role === 'admin' ? 1 : 0,
                'can_edit' => $role === 'admin' ? 1 : 0,
                'can_delete' => $role === 'admin' ? 1 : 0,
                'can_view_detail' => $role === 'admin' ? 1 : 0,
                'can_upload' => $role === 'admin' ? 1 : 0,
                'can_download' => $role === 'admin' ? 1 : 0,
            ]);
        }

        // Reload permissions
        loadPermissions(auth('role'));

        flash('success', 'Modul berhasil ditambahkan.');
        redirect('modules');
    }

    public function update($id) {
        checkPermission('permissions', 'can_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'icon' => trim($_POST['icon'] ?? 'fas fa-circle'),
            'url' => trim($_POST['url'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        $db = getDB();
        $stmt = $db->prepare("UPDATE modules SET name=?, slug=?, icon=?, url=?, sort_order=? WHERE id=?");
        $stmt->execute([$data['name'], $data['slug'], $data['icon'], $data['url'], $data['sort_order'], $id]);

        loadPermissions(auth('role'));

        flash('success', 'Modul berhasil diupdate.');
        redirect('modules');
    }

    public function delete($id) {
        checkPermission('permissions', 'can_delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('modules');

        $db = getDB();
        $stmt = $db->prepare("UPDATE modules SET is_active=0 WHERE id=?");
        $stmt->execute([$id]);

        loadPermissions(auth('role'));

        flash('success', 'Modul berhasil dihapus.');
        redirect('modules');
    }
}
