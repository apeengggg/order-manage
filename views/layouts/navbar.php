<!-- Navbar -->
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
