<?php
namespace App\Controllers;

use App\Services\OrderService;
use App\Services\ExpeditionService;

class OrderController {
    private $orderService;
    private $expeditionService;

    public function __construct() {
        $this->orderService = new OrderService();
        $this->expeditionService = new ExpeditionService();
    }

    public function index() {
        checkPermission('orders', 'can_view');

        $filters = [
            'search' => $_GET['search'] ?? '',
            'expedition_id' => $_GET['expedition_id'] ?? '',
            'is_exported' => $_GET['is_exported'] ?? ''
        ];
        $orders = $this->orderService->getAll($filters);
        $expeditions = $this->expeditionService->getAll();
        $pageTitle = 'List Order';
        require ROOT_PATH . '/views/orders/index.php';
    }

    public function create() {
        checkPermission('orders-create', 'can_view');
        checkPermission('orders', 'can_add');

        $expeditions = $this->expeditionService->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getOrderData();

            if (empty($data['customer_name']) || empty($data['customer_phone']) || empty($data['customer_address']) || empty($data['product_name'])) {
                flash('error', 'Semua field wajib harus diisi.');
                $pageTitle = 'Input Data Customer';
                require ROOT_PATH . '/views/orders/create.php';
                return;
            }

            $this->orderService->create($data);
            flash('success', 'Order berhasil ditambahkan.');
            redirect('orders');
        }

        $pageTitle = 'Input Data Customer';
        require ROOT_PATH . '/views/orders/create.php';
    }

    public function edit($id) {
        checkPermission('orders', 'can_edit');

        $order = $this->orderService->find($id);
        if (!$order) {
            flash('error', 'Order tidak ditemukan.');
            redirect('orders');
        }

        if ($order['is_exported']) {
            flash('error', 'Order sudah diexport oleh Admin. Edit diblokir.');
            redirect('orders');
        }

        $expeditions = $this->expeditionService->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getOrderData(false);
            $result = $this->orderService->update($id, $data);

            if ($result === null) {
                flash('error', 'Order sudah diexport oleh Admin. Edit diblokir.');
            } else {
                flash('success', 'Order berhasil diupdate.');
            }
            redirect('orders');
        }

        $pageTitle = 'Edit Order';
        require ROOT_PATH . '/views/orders/edit.php';
    }

    public function delete($id) {
        checkPermission('orders', 'can_delete');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->orderService->delete($id);
            if ($result === null) {
                flash('error', 'Order sudah diexport oleh Admin. Delete diblokir.');
            } else {
                flash('success', 'Order berhasil dihapus.');
            }
        }
        redirect('orders');
    }

    public function detail($id) {
        checkPermission('orders', 'can_view_detail');

        $order = $this->orderService->find($id);
        header('Content-Type: application/json');
        echo json_encode($order ?: ['error' => 'Not found']);
        exit;
    }

    private function getOrderData(bool $includeCreatedBy = true): array {
        $data = [
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'customer_phone' => trim($_POST['customer_phone'] ?? ''),
            'customer_address' => trim($_POST['customer_address'] ?? ''),
            'product_name' => trim($_POST['product_name'] ?? ''),
            'qty' => (int)($_POST['qty'] ?? 1),
            'price' => (float)($_POST['price'] ?? 0),
            'expedition_id' => $_POST['expedition_id'] ?: null,
            'resi' => trim($_POST['resi'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];
        if ($includeCreatedBy) {
            $data['created_by'] = auth('user_id');
        }
        return $data;
    }
}
