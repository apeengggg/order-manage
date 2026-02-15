<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
<?php
    $_sidebarLogo = appSetting('logo_file_id');
    $_sidebarLogoUrl = null;
    if ($_sidebarLogo) {
        $fs = new \App\Services\FileService();
        $f = $fs->getFile((int)$_sidebarLogo);
        if ($f) $_sidebarLogoUrl = $fs->getThumbnailUrl($f) ?: $fs->getFileUrl($f);
    }
    $_sidebarAppName = appSetting('app_name', 'Order Manager');
    $_isImpersonating = \App\TenantContext::isImpersonating();
    $_tenantData = \App\TenantContext::tenant();
    ?>
    <a href="<?= BASE_URL ?>dashboard" class="brand-link">
        <?php if ($_sidebarLogoUrl): ?>
            <img src="<?= e($_sidebarLogoUrl) ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity:.8">
        <?php else: ?>
            <i class="fas fa-shipping-fast brand-image ml-3" style="font-size:1.5rem; line-height:1.8;"></i>
        <?php endif; ?>
        <span class="brand-text font-weight-light"><?= e($_sidebarAppName) ?></span>
    </a>

    <div class="sidebar">
        <!-- Tenant info / Impersonation banner -->
        <?php if ($_isImpersonating && $_tenantData): ?>
        <div class="mt-2 mx-2 p-2 rounded" style="background:#856404;color:#fff;font-size:0.85rem;">
            <i class="fas fa-eye mr-1"></i> Melihat sebagai:
            <strong><?= e($_tenantData['name']) ?></strong>
            <a href="<?= BASE_URL ?>tenants/stopImpersonate" class="btn btn-sm btn-light ml-1 py-0 px-1" title="Kembali ke Super Admin">
                <i class="fas fa-times"></i>
            </a>
        </div>
        <?php elseif (isSuperAdmin()): ?>
        <div class="mt-2 mx-2 p-2 rounded" style="background:#6f42c1;color:#fff;font-size:0.85rem;">
            <i class="fas fa-crown mr-1"></i> Super Admin Mode
        </div>
        <?php elseif ($_tenantData): ?>
        <div class="mt-2 mx-2 p-2 rounded" style="background:rgba(255,255,255,0.1);font-size:0.85rem;">
            <i class="fas fa-building mr-1"></i> <?= e($_tenantData['name']) ?>
        </div>
        <?php endif; ?>

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle text-light" style="font-size:2rem;"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= e(auth('name')) ?></a>
                <span class="badge badge-<?= isSuperAdmin() ? 'warning' : (isAdmin() ? 'danger' : 'info') ?>"><?= e(auth('role_name')) ?></span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <?php
                $menus = $_SESSION['menus'] ?? [];
                $currentUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';

                foreach ($menus as $slug => $menu):
                    $isActive = ($currentUrl === $menu['url'] || strpos($currentUrl, $menu['url']) === 0);
                ?>
                <li class="nav-item">
                    <a href="<?= BASE_URL . $menu['url'] ?>" class="nav-link <?= $isActive ? 'active' : '' ?>">
                        <i class="nav-icon <?= e($menu['icon']) ?>"></i>
                        <p><?= e($menu['name']) ?></p>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
