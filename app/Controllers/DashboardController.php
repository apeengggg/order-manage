<?php
namespace App\Controllers;

use App\Models\Order;

class DashboardController {
    private $order;

    public function __construct() {
        $this->order = new Order();
    }

    public function index() {
        $totalOrders = $this->order->countAll();
        $exported = $this->order->countExported();
        $pending = $this->order->countPending();
        $revenue = $this->order->totalRevenue();

        $pageTitle = 'Dashboard';
        require __DIR__ . '/../../views/dashboard/index.php';
    }
}
