<?php
namespace App\Services;

use App\Repositories\TenantRepository;
use App\Repositories\RoleRepository;
use App\Repositories\PermissionRepository;

class TenantService {
    private TenantRepository $tenantRepo;
    private AuditService $audit;

    public function __construct() {
        $this->tenantRepo = new TenantRepository();
        $this->audit = new AuditService();
    }

    public function getAll(): array {
        return $this->tenantRepo->findAll();
    }

    public function getById(int $id): ?array {
        return $this->tenantRepo->findById($id);
    }

    public function create(array $data): int {
        $tenantId = $this->tenantRepo->create($data);

        // Create default roles for the new tenant
        $this->createDefaultRoles($tenantId);

        // Create default settings for the new tenant
        $this->createDefaultSettings($tenantId);

        $this->audit->log('create', 'tenant', $tenantId, $data['name'] ?? '', null, $data);

        return $tenantId;
    }

    public function update(int $id, array $data): bool {
        $old = $this->tenantRepo->findById($id);
        $result = $this->tenantRepo->update($id, $data);

        if ($result && $old) {
            $this->audit->log('update', 'tenant', $id, $old['name'] ?? '', $old, $data);
        }

        return $result;
    }

    public function delete(int $id): bool {
        $old = $this->tenantRepo->findById($id);
        $result = $this->tenantRepo->delete($id);

        if ($result && $old) {
            $this->audit->log('delete', 'tenant', $id, $old['name'] ?? '', $old, null);
        }

        return $result;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool {
        return $this->tenantRepo->slugExists($slug, $excludeId);
    }

    private function createDefaultRoles(int $tenantId): void {
        $db = getDB();

        // Create Admin role
        $stmt = $db->prepare("INSERT INTO roles (tenant_id, name, slug, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tenantId, 'Admin', 'admin', 'Tenant admin - full access']);
        $adminRoleId = (int)$db->lastInsertId();

        // Create CS role
        $stmt->execute([$tenantId, 'Customer Service', 'cs', 'Limited access']);
        $csRoleId = (int)$db->lastInsertId();

        // Get all active non-superadmin modules
        $modules = $db->query("SELECT id, slug FROM modules WHERE is_active = 1 AND is_superadmin_only = 0")->fetchAll();

        $permStmt = $db->prepare(
            "INSERT INTO role_permissions (role_id, module_id, can_view, can_add, can_edit, can_delete, can_view_detail, can_upload, can_download)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($modules as $mod) {
            // Admin: full access
            $permStmt->execute([$adminRoleId, $mod['id'], 1, 1, 1, 1, 1, 1, 1]);

            // CS: limited
            $isOrderCreate = $mod['slug'] === 'orders-create';
            $isOrders = $mod['slug'] === 'orders';
            $isDashboard = $mod['slug'] === 'dashboard';

            if ($isDashboard) {
                $permStmt->execute([$csRoleId, $mod['id'], 1, 0, 0, 0, 0, 0, 0]);
            } elseif ($isOrderCreate) {
                $permStmt->execute([$csRoleId, $mod['id'], 1, 1, 0, 0, 0, 0, 0]);
            } elseif ($isOrders) {
                $permStmt->execute([$csRoleId, $mod['id'], 1, 1, 1, 1, 1, 0, 0]);
            } else {
                $permStmt->execute([$csRoleId, $mod['id'], 0, 0, 0, 0, 0, 0, 0]);
            }
        }

        // Create default admin user for the tenant (password: admin123)
        $stmt = $db->prepare("INSERT INTO users (tenant_id, username, password, name, role_id) VALUES (?, ?, ?, ?, ?)");
        $slugStmt = $db->prepare("SELECT slug FROM tenants WHERE id = ?");
        $slugStmt->execute([$tenantId]);
        $slug = $slugStmt->fetchColumn();
        $stmt->execute([
            $tenantId,
            'admin_' . $slug,
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin ' . ucfirst($slug),
            $adminRoleId
        ]);
    }

    private function createDefaultSettings(int $tenantId): void {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO app_settings (tenant_id, setting_key, setting_value) VALUES (?, ?, ?)"
        );
        $defaults = [
            'app_name' => 'Order Management System',
            'primary_color' => '#007bff',
            'login_bg_color' => '#667eea',
            'logo_file_id' => null,
            'login_bg_file_id' => null,
        ];
        foreach ($defaults as $key => $value) {
            $stmt->execute([$tenantId, $key, $value]);
        }
    }
}
