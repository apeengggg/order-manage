<?php
namespace App\Controllers;

use App\Services\OrderService;
use App\Services\TenantService;
use App\TenantContext;

class DashboardController {
    private $orderService;

    public function __construct() {
        $this->orderService = new OrderService();
    }

    public function index() {
        // Super admin without impersonation → redirect to tenant management
        if (TenantContext::isSuperAdmin() && !TenantContext::isImpersonating()) {
            redirect('tenants');
        }

        $stats = $this->orderService->getDashboardStats();
        extract($stats); // $totalOrders, $exported, $pending, $revenue

        $pageTitle = 'Dashboard';
        require ROOT_PATH . '/views/dashboard/index.php';
    }
}
