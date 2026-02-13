<?php
require_once __DIR__ . '/config/app.php';

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
        require_once __DIR__ . '/controllers/AuthController.php';
        $ctrl = new AuthController();
        break;

    case 'dashboard':
        if (!isLoggedIn()) redirect('auth/login');
        require_once __DIR__ . '/controllers/DashboardController.php';
        $ctrl = new DashboardController();
        break;

    case 'orders':
        if (!isLoggedIn()) redirect('auth/login');
        require_once __DIR__ . '/controllers/OrderController.php';
        $ctrl = new OrderController();
        break;

    case 'admin':
        if (!isLoggedIn()) redirect('auth/login');
        if (!isAdmin()) redirect('dashboard');
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        break;

    case 'expeditions':
        if (!isLoggedIn()) redirect('auth/login');
        if (!isAdmin()) redirect('dashboard');
        require_once __DIR__ . '/controllers/ExpeditionController.php';
        $ctrl = new ExpeditionController();
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
