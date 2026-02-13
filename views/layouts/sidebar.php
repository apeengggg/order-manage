<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= BASE_URL ?>dashboard" class="brand-link">
        <i class="fas fa-shipping-fast brand-image ml-3" style="font-size:1.5rem; line-height:1.8;"></i>
        <span class="brand-text font-weight-light"><b>Order</b> Manager</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle text-light" style="font-size:2rem;"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= e(auth('name')) ?></a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>dashboard" class="nav-link <?= ($pageTitle ?? '') === 'Dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php if (isCS() || isAdmin()): ?>
                <li class="nav-header">CUSTOMER SERVICE</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>orders/create" class="nav-link <?= ($pageTitle ?? '') === 'Input Data Customer' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-plus-circle"></i>
                        <p>Input Data Customer</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>orders" class="nav-link <?= ($pageTitle ?? '') === 'List Order' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-list-alt"></i>
                        <p>List Order</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (isAdmin()): ?>
                <li class="nav-header">ADMIN</li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>admin" class="nav-link <?= strpos($pageTitle ?? '', 'Admin') === 0 ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-export"></i>
                        <p>Export Order</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>expeditions" class="nav-link <?= ($pageTitle ?? '') === 'Kelola Ekspedisi' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Kelola Ekspedisi</p>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</aside>
