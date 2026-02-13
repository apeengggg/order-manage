<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Expedition.php';

class OrderController {
    private $order;
    private $expedition;

    public function __construct() {
        $this->order = new Order();
        $this->expedition = new Expedition();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'expedition_id' => $_GET['expedition_id'] ?? '',
            'is_exported' => $_GET['is_exported'] ?? ''
        ];
        $orders = $this->order->getAll($filters);
        $expeditions = $this->expedition->getAll();
        $pageTitle = 'List Order';
        require __DIR__ . '/../views/orders/index.php';
    }

    public function create() {
        $expeditions = $this->expedition->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                'created_by' => auth('user_id')
            ];

            // Validation
            if (empty($data['customer_name']) || empty($data['customer_phone']) || empty($data['customer_address']) || empty($data['product_name'])) {
                flash('error', 'Semua field wajib harus diisi.');
                $pageTitle = 'Input Data Customer';
                require __DIR__ . '/../views/orders/create.php';
                return;
            }

            $this->order->create($data);
            flash('success', 'Order berhasil ditambahkan.');
            redirect('orders');
        }

        $pageTitle = 'Input Data Customer';
        require __DIR__ . '/../views/orders/create.php';
    }

    public function edit($id) {
        $order = $this->order->find($id);
        if (!$order) {
            flash('error', 'Order tidak ditemukan.');
            redirect('orders');
        }

        // Block edit if exported
        if ($order['is_exported']) {
            flash('error', 'Order sudah diexport oleh Admin. Edit diblokir.');
            redirect('orders');
        }

        $expeditions = $this->expedition->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'customer_name' => trim($_POST['customer_name'] ?? ''),
                'customer_phone' => trim($_POST['customer_phone'] ?? ''),
                'customer_address' => trim($_POST['customer_address'] ?? ''),
                'product_name' => trim($_POST['product_name'] ?? ''),
                'qty' => (int)($_POST['qty'] ?? 1),
                'price' => (float)($_POST['price'] ?? 0),
                'expedition_id' => $_POST['expedition_id'] ?: null,
                'resi' => trim($_POST['resi'] ?? ''),
                'notes' => trim($_POST['notes'] ?? '')
            ];

            $result = $this->order->update($id, $data);
            if ($result === false) {
                flash('error', 'Order sudah diexport oleh Admin. Edit diblokir.');
            } else {
                flash('success', 'Order berhasil diupdate.');
            }
            redirect('orders');
        }

        $pageTitle = 'Edit Order';
        require __DIR__ . '/../views/orders/edit.php';
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->order->delete($id);
            if ($result === false) {
                flash('error', 'Order sudah diexport oleh Admin. Delete diblokir.');
            } else {
                flash('success', 'Order berhasil dihapus.');
            }
        }
        redirect('orders');
    }

    // AJAX: get order detail
    public function detail($id) {
        $order = $this->order->find($id);
        header('Content-Type: application/json');
        echo json_encode($order ?: ['error' => 'Not found']);
        exit;
    }
}
