<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Expedition.php';

class AdminController {
    private $order;
    private $expedition;

    public function __construct() {
        $this->order = new Order();
        $this->expedition = new Expedition();
    }

    public function index() {
        $expeditions = $this->expedition->getAll();
        $selectedExpedition = $_GET['expedition_id'] ?? '';

        $orders = [];
        if ($selectedExpedition) {
            $orders = $this->order->getByExpedition($selectedExpedition);
        } else {
            $orders = $this->order->getAll(['is_exported' => '0']);
        }

        $pageTitle = 'Admin - Export Order';
        require __DIR__ . '/../views/admin/index.php';
    }

    public function export() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('admin');

        $ids = $_POST['order_ids'] ?? [];
        $expeditionId = $_POST['expedition_id'] ?? '';

        if (empty($ids)) {
            flash('error', 'Pilih minimal satu order untuk diexport.');
            redirect('admin');
        }

        $count = $this->order->markExported($ids, auth('user_id'));
        flash('success', "$count order berhasil diexport.");

        // Generate CSV export
        $orders = [];
        foreach ($ids as $id) {
            $o = $this->order->find($id);
            if ($o) $orders[] = $o;
        }

        if (!empty($orders)) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="export_orders_' . date('Ymd_His') . '.csv"');
            $output = fopen('php://output', 'w');
            // BOM for Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['No', 'Nama Customer', 'Telepon', 'Alamat', 'Produk', 'Qty', 'Harga', 'Total', 'Ekspedisi', 'Resi', 'Catatan']);
            $no = 1;
            foreach ($orders as $o) {
                fputcsv($output, [
                    $no++,
                    $o['customer_name'],
                    $o['customer_phone'],
                    $o['customer_address'],
                    $o['product_name'],
                    $o['qty'],
                    $o['price'],
                    $o['total'],
                    $o['expedition_name'] ?? '-',
                    $o['resi'] ?? '-',
                    $o['notes'] ?? '-'
                ]);
            }
            fclose($output);
            exit;
        }

        redirect('admin');
    }
}
