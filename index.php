<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/app.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\ExpeditionController;
use App\Controllers\PermissionController;
use App\Controllers\ModuleController;

// Simple router
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$segments = $url ? explode('/', $url) : [];

$page = $segments[0] ?? 'dashboard';
$action = $segments[1] ?? 'index';
$id = $segments[2] ?? null;

// Auth check - redirect to login if not logged in
if (!isLoggedIn() && $page !== 'auth') {
    redirect('auth/login');
}

// Route to controllers
switch ($page) {
    case 'auth':
        $ctrl = new AuthController();
        break;

    case 'dashboard':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('dashboard', 'can_view');
        $ctrl = new DashboardController();
        break;

    case 'orders':
        if (!isLoggedIn()) redirect('auth/login');
        $ctrl = new OrderController();
        break;

    case 'admin':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('admin-export', 'can_view');
        $ctrl = new AdminController();
        break;

    case 'expeditions':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('expeditions', 'can_view');
        $ctrl = new ExpeditionController();
        break;

    case 'permissions':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('permissions', 'can_view');
        $ctrl = new PermissionController();
        break;

    case 'modules':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('permissions', 'can_view');
        $ctrl = new ModuleController();
        break;

    default:
        redirect('dashboard');
        break;
}

if (method_exists($ctrl, $action)) {
    $ctrl->$action($id);
} else {
    $ctrl->index();
}
