<?php
/**
 * Front Controller
 * Document root entry point - all requests go through here.
 */

ini_set('display_errors', 0);
error_reporting(0);

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
use App\Controllers\RoleController;
use App\Controllers\UserController;
use App\Controllers\FileController;
use App\Controllers\SettingController;
use App\Controllers\TenantController;

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
// Allow files/serve without auth (for login page logo/background)
if (!isLoggedIn() && $page !== 'auth' && !($page === 'files' && $action === 'serve')) {
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
        checkPermission('modules', 'can_view');
        $ctrl = new ModuleController();
        break;

    case 'roles':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('roles', 'can_view');
        $ctrl = new RoleController();
        break;

    case 'users':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('users', 'can_view');
        $ctrl = new UserController();
        break;

    case 'files':
        // Allow serve action without login (for login page logo/bg)
        if ($action !== 'serve' && !isLoggedIn()) redirect('auth/login');
        $ctrl = new FileController();
        break;

    case 'settings':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('settings', 'can_view');
        $ctrl = new SettingController();
        break;

    case 'tenants':
        if (!isLoggedIn()) redirect('auth/login');
        checkPermission('tenants', 'can_view');
        $ctrl = new TenantController();
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
