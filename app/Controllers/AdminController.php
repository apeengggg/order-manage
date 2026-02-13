<?php
namespace App\Controllers;

use App\Services\OrderService;
use App\Services\ExpeditionService;

class AdminController {
    private $orderService;
    private $expeditionService;

    public function __construct() {
        $this->orderService = new OrderService();
        $this->expeditionService = new ExpeditionService();
    }

    public function index() {
        checkPermission('admin-export', 'can_view');

        $expeditions = $this->expeditionService->getAll();
        $selectedExpedition = $_GET['expedition_id'] ?? '';

        if ($selectedExpedition) {
            $orders = $this->orderService->getByExpedition($selectedExpedition);
        } else {
            $orders = $this->orderService->getAll(['is_exported' => '0']);
        }

        $pageTitle = 'Admin - Export Order';
        require ROOT_PATH . '/views/admin/index.php';
    }

    public function export() {
        checkPermission('admin-export', 'can_download');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin');

        $ids = $_POST['order_ids'] ?? [];
        if (empty($ids)) {
            flash('error', 'Pilih minimal satu order untuk diexport.');
            redirect('admin');
        }

        $count = $this->orderService->exportOrders($ids, auth('user_id'));
        flash('success', "$count order berhasil diexport.");

        $orders = $this->orderService->generateCsv($ids);
        if (!empty($orders)) {
            $this->downloadCsv($orders);
        }

        redirect('admin');
    }

    private function downloadCsv(array $orders): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export_orders_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['No', 'Nama Customer', 'Telepon', 'Alamat', 'Produk', 'Qty', 'Harga', 'Total', 'Ekspedisi', 'Resi', 'Catatan']);
        $no = 1;
        foreach ($orders as $o) {
            fputcsv($output, [
                $no++, $o['customer_name'], $o['customer_phone'], $o['customer_address'],
                $o['product_name'], $o['qty'], $o['price'], $o['total'],
                $o['expedition_name'] ?? '-', $o['resi'] ?? '-', $o['notes'] ?? '-'
            ]);
        }
        fclose($output);
        exit;
    }
}
