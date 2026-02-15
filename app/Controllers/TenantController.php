<?php
namespace App\Controllers;

use App\Services\TenantService;
use App\TenantContext;

class TenantController {
    private TenantService $tenantService;

    public function __construct() {
        $this->tenantService = new TenantService();
    }

    public function index(): void {
        checkPermission('tenants', 'can_view');
        $tenants = $this->tenantService->getAll();
        $pageTitle = 'Kelola Tenant';
        require ROOT_PATH . '/views/tenants/index.php';
    }

    public function create(): void {
        checkPermission('tenants', 'can_add');

        $v = validate($_POST, [
            'name' => 'required|max:100',
            'slug' => 'required|max:50',
        ]);

        if ($v->fails()) {
            flash('error', $v->firstError());
            redirect('tenants');
        }

        $slug = strtolower(preg_replace('/[^a-z0-9_-]/', '', $_POST['slug']));

        if ($this->tenantService->slugExists($slug)) {
            flash('error', 'Slug sudah digunakan.');
            redirect('tenants');
        }

        $this->tenantService->create([
            'name' => $_POST['name'],
            'slug' => $slug,
            'domain' => $_POST['domain'] ?? null,
            'max_users' => (int)($_POST['max_users'] ?? 10),
        ]);

        flash('success', 'Tenant berhasil ditambahkan dengan role & user default.');
        redirect('tenants');
    }

    public function update($id): void {
        checkPermission('tenants', 'can_edit');

        $v = validate($_POST, [
            'name' => 'required|max:100',
            'slug' => 'required|max:50',
        ]);

        if ($v->fails()) {
            flash('error', $v->firstError());
            redirect('tenants');
        }

        $slug = strtolower(preg_replace('/[^a-z0-9_-]/', '', $_POST['slug']));

        if ($this->tenantService->slugExists($slug, (int)$id)) {
            flash('error', 'Slug sudah digunakan.');
            redirect('tenants');
        }

        $this->tenantService->update((int)$id, [
            'name' => $_POST['name'],
            'slug' => $slug,
            'domain' => $_POST['domain'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'max_users' => (int)($_POST['max_users'] ?? 10),
        ]);

        flash('success', 'Tenant berhasil diupdate.');
        redirect('tenants');
    }

    public function delete($id): void {
        checkPermission('tenants', 'can_delete');
        $this->tenantService->delete((int)$id);
        flash('success', 'Tenant berhasil dihapus beserta semua datanya.');
        redirect('tenants');
    }

    public function impersonate($id): void {
        if (!isSuperAdmin()) {
            flash('error', 'Hanya super admin yang bisa impersonate tenant.');
            redirect('dashboard');
            return;
        }

        $tenant = $this->tenantService->getById((int)$id);
        if (!$tenant) {
            flash('error', 'Tenant tidak ditemukan.');
            redirect('tenants');
            return;
        }

        TenantContext::impersonate((int)$id);
        flash('success', 'Anda sekarang melihat sebagai tenant: ' . $tenant['name']);
        redirect('dashboard');
    }

    public function stopImpersonate(): void {
        TenantContext::stopImpersonating();
        flash('success', 'Kembali ke mode Super Admin.');
        redirect('tenants');
    }
}
