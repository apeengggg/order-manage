<?php
/**
 * Front Controller
 * Document root entry point - all requests go through here.
 */

// Base path = parent of public/
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/app.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\OrderController;
use App\Controllers\AdminController;
use App\Controllers\ExpeditionController;
use App\Controllers\PermissionController;
use App\Controllers\ModuleController;
use App\Controllers\FileController;

// Parse URL from query string OR REQUEST_URI
$url = '';
if (isset($_GET['url'])) {
    $url = rtrim($_GET['url'], '/');
} else {
    // PHP built-in server: parse from REQUEST_URI
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $url = rtrim($uri, '/');
    $url = ltrim($url, '/');
}

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

    case 'files':
        if (!isLoggedIn()) redirect('auth/login');
        $ctrl = new FileController();
        break;

    default:
        http_response_code(404);
        require ROOT_PATH . '/views/errors/404.php';
        exit;
}

if (method_exists($ctrl, $action)) {
    $ctrl->$action($id);
} elseif ($action === 'index') {
    $ctrl->index();
} else {
    http_response_code(404);
    require ROOT_PATH . '/views/errors/404.php';
    exit;
}
