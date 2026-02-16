<!-- Navbar -->
<?php
$_navIsSuper = isSuperAdmin() && !\App\TenantContext::isImpersonating();
$_navIsFiltering = \App\TenantContext::isFiltering();
$_navFilterId = \App\TenantContext::filterTenantId();
$_navTenants = [];
if ($_navIsSuper) {
    $_navTenantRepo = new \App\Repositories\TenantRepository();
    $_navTenants = $_navTenantRepo->findAll();
}
// Find current filter tenant name
$_navFilterName = null;
if ($_navIsFiltering) {
    foreach ($_navTenants as $_nt) {
        if ((int)$_nt['id'] === $_navFilterId) {
            $_navFilterName = $_nt['name'];
            break;
        }
    }
}
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light" id="mainNavbar">
    <!-- Left -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <?php if (\App\TenantContext::isImpersonating()): ?>
        <li class="nav-item d-none d-md-block">
            <span class="nav-link text-warning">
                <i class="fas fa-eye mr-1"></i> Impersonating: <strong><?= e(\App\TenantContext::tenant()['name'] ?? '') ?></strong>
                <a href="<?= BASE_URL ?>tenants/stopImpersonate" class="badge badge-warning ml-1">Kembali</a>
            </span>
        </li>
        <?php elseif ($_navIsSuper): ?>
        <li class="nav-item dropdown d-none d-md-block">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button">
                <i class="fas fa-building mr-1"></i>
                <?php if ($_navIsFiltering && $_navFilterName): ?>
                    <span class="badge badge-info"><?= e($_navFilterName) ?></span>
                <?php else: ?>
                    <span class="badge badge-secondary">Semua Tenant</span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu">
                <h6 class="dropdown-header">Filter Tenant</h6>
                <a class="dropdown-item <?= !$_navIsFiltering ? 'active' : '' ?>" href="<?= BASE_URL ?>tenants/filter/clear">
                    <i class="fas fa-globe mr-2"></i> Semua Tenant
                </a>
                <div class="dropdown-divider"></div>
                <?php foreach ($_navTenants as $_nt): ?>
                <a class="dropdown-item <?= $_navFilterId === (int)$_nt['id'] ? 'active' : '' ?>" href="<?= BASE_URL ?>tenants/filter/<?= $_nt['id'] ?>">
                    <i class="fas fa-building mr-2"></i> <?= e($_nt['name']) ?>
                    <?php if (!$_nt['is_active']): ?>
                        <span class="badge badge-danger ml-1">Nonaktif</span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </li>
        <?php endif; ?>
    </ul>
    <!-- Right -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" href="#" id="btnToggleDarkMode" role="button" title="Toggle Dark Mode">
                <i class="fas fa-moon"></i>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="fas fa-user-circle mr-1"></i> <?= e(auth('name')) ?>
                <span class="badge badge-<?= isSuperAdmin() ? 'warning' : (isAdmin() ? 'danger' : 'info') ?> ml-1"><?= e(auth('role_name')) ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item-text text-muted">Login sebagai: <?= e(auth('username')) ?></span>
                <?php if (\App\TenantContext::tenant()): ?>
                <span class="dropdown-item-text text-muted">
                    <i class="fas fa-building mr-1"></i> <?= e(\App\TenantContext::tenant()['name']) ?>
                </span>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
