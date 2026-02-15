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
                <span class="badge badge-<?= isAdmin() ? 'danger' : 'info' ?>"><?= e(auth('role_name')) ?></span>
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
