<?php
namespace App\Controllers;

use App\Services\OrderService;

class DashboardController {
    private $orderService;

    public function __construct() {
        $this->orderService = new OrderService();
    }

    public function index() {
        $stats = $this->orderService->getDashboardStats();
        extract($stats); // $totalOrders, $exported, $pending, $revenue

        $pageTitle = 'Dashboard';
        require ROOT_PATH . '/views/dashboard/index.php';
    }
}
