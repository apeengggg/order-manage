<?php
namespace App;

class TenantContext {
    private static ?int $tenantId = null;
    private static ?array $tenant = null;
    private static ?int $filterTenantId = null;

    public static function set(?int $tenantId): void {
        self::$tenantId = $tenantId;
        self::$tenant = null; // reset cached tenant
    }

    public static function id(): ?int {
        return self::$tenantId;
    }

    public static function setTenant(?array $tenant): void {
        self::$tenant = $tenant;
        self::$tenantId = $tenant ? (int)$tenant['id'] : null;
    }

    public static function tenant(): ?array {
        if (self::$tenant === null && self::$tenantId !== null) {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM tenants WHERE id = ?");
            $stmt->execute([self::$tenantId]);
            self::$tenant = $stmt->fetch() ?: null;
        }
        return self::$tenant;
    }

    public static function isSuperAdmin(): bool {
        return self::$tenantId === null && isset($_SESSION['user_id']);
    }

    public static function isImpersonating(): bool {
        return isset($_SESSION['original_tenant_id']) || isset($_SESSION['impersonate_tenant_id']);
    }

    public static function impersonate(int $tenantId): void {
        if (!isset($_SESSION['original_tenant_id'])) {
            $_SESSION['original_tenant_id'] = self::$tenantId;
        }
        $_SESSION['impersonate_tenant_id'] = $tenantId;
        self::set($tenantId);
    }

    public static function stopImpersonating(): void {
        $original = $_SESSION['original_tenant_id'] ?? null;
        unset($_SESSION['impersonate_tenant_id'], $_SESSION['original_tenant_id']);
        self::set($original);
    }

    // --- Tenant Filter (superadmin stays superadmin, just filters data) ---

    public static function setFilter(?int $tenantId): void {
        self::$filterTenantId = $tenantId;
        if ($tenantId !== null) {
            $_SESSION['filter_tenant_id'] = $tenantId;
        } else {
            unset($_SESSION['filter_tenant_id']);
        }
    }

    public static function filterTenantId(): ?int {
        return self::$filterTenantId;
    }

    public static function isFiltering(): bool {
        return self::$filterTenantId !== null;
    }

    /**
     * Returns the tenant ID to use for data queries.
     * Priority: filter > impersonate > user's own tenant
     */
    public static function effectiveTenantId(): ?int {
        if (self::$filterTenantId !== null) {
            return self::$filterTenantId;
        }
        return self::$tenantId;
    }

    public static function resolve(): void {
        if (isset($_SESSION['impersonate_tenant_id'])) {
            self::set((int)$_SESSION['impersonate_tenant_id']);
        } elseif (isset($_SESSION['tenant_id'])) {
            self::set((int)$_SESSION['tenant_id']);
        } else {
            self::set(null);
        }

        // Restore filter from session (only for superadmin)
        if (self::isSuperAdmin() && isset($_SESSION['filter_tenant_id'])) {
            self::$filterTenantId = (int)$_SESSION['filter_tenant_id'];
        }
    }
}
