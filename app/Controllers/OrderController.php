<?php
namespace App\Controllers;

use App\Services\OrderService;
use App\Services\ExpeditionService;
use App\Services\TemplateService;

class OrderController {
    private $orderService;
    private $expeditionService;
    private $templateService;

    public function __construct() {
        $this->orderService = new OrderService();
        $this->expeditionService = new ExpeditionService();
        $this->templateService = new TemplateService();
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

        // Load template map for expedition badges
        $expIds = array_column($expeditions, 'id');
        $templateMap = $this->templateService->getTemplateMap($expIds);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $expeditionId = (int)($_POST['expedition_id'] ?? 0);

            if ($expeditionId <= 0) {
                flash('error', 'Ekspedisi harus dipilih.');
                $pageTitle = 'Input Data Customer';
                require ROOT_PATH . '/views/orders/create.php';
                return;
            }

            // Get template columns
            $templateColumns = $this->templateService->getTemplateColumns($expeditionId);

            if (empty($templateColumns)) {
                flash('error', 'Ekspedisi ini belum memiliki template. Hubungi admin.');
                $pageTitle = 'Input Data Customer';
                require ROOT_PATH . '/views/orders/create.php';
                return;
            }

            // Build extra_fields from template columns
            $extraFields = [];
            $errors = [];
            foreach ($templateColumns as $col) {
                $fieldKey = 'tpl_' . $col['position'];
                $value = trim($_POST[$fieldKey] ?? '');
                $extraFields[$col['name']] = $value;

                if ($col['is_required'] && $value === '') {
                    $errors[] = $col['clean_name'] . ' wajib diisi.';
                }
            }

            if (!empty($errors)) {
                flash('error', $errors[0]);
                $pageTitle = 'Input Data Customer';
                require ROOT_PATH . '/views/orders/create.php';
                return;
            }

            // Map template fields to common columns
            $mapped = $this->orderService->mapTemplateToCommon($extraFields);

            $data = [
                'customer_name' => $mapped['customer_name'] ?? '',
                'customer_phone' => $mapped['customer_phone'] ?? '',
                'customer_address' => $mapped['customer_address'] ?? '',
                'product_name' => $mapped['product_name'] ?? '',
                'qty' => (int)($mapped['qty'] ?? 1),
                'price' => (float)($mapped['price'] ?? 0),
                'expedition_id' => $expeditionId,
                'resi' => trim($_POST['resi'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'extra_fields' => json_encode($extraFields, JSON_UNESCAPED_UNICODE),
                'created_by' => auth('user_id'),
            ];

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
        $expIds = array_column($expeditions, 'id');
        $templateMap = $this->templateService->getTemplateMap($expIds);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $expeditionId = (int)($_POST['expedition_id'] ?? 0);
            $templateColumns = $this->templateService->getTemplateColumns($expeditionId);

            // Build extra_fields
            $extraFields = [];
            foreach ($templateColumns as $col) {
                $fieldKey = 'tpl_' . $col['position'];
                $extraFields[$col['name']] = trim($_POST[$fieldKey] ?? '');
            }

            $mapped = $this->orderService->mapTemplateToCommon($extraFields);

            $data = [
                'customer_name' => $mapped['customer_name'] ?? '',
                'customer_phone' => $mapped['customer_phone'] ?? '',
                'customer_address' => $mapped['customer_address'] ?? '',
                'product_name' => $mapped['product_name'] ?? '',
                'qty' => (int)($mapped['qty'] ?? 1),
                'price' => (float)($mapped['price'] ?? 0),
                'expedition_id' => $expeditionId,
                'resi' => trim($_POST['resi'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'extra_fields' => json_encode($extraFields, JSON_UNESCAPED_UNICODE),
            ];

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

    /**
     * AJAX: Get template fields for an expedition
     * GET /orders/getTemplateFields/{expeditionId}
     * For columns with >100 options, returns options_count instead of full options array
     */
    public function getTemplateFields($expeditionId) {
        $columns = $this->templateService->getTemplateColumns((int)$expeditionId);

        // Trim large option lists to reduce payload
        foreach ($columns as &$col) {
            if ($col['input_type'] === 'select' && !empty($col['options']) && count($col['options']) > 100) {
                $col['options_count'] = count($col['options']);
                $col['options'] = [];
            }
        }
        unset($col);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => !empty($columns),
            'columns' => $columns,
        ]);
        exit;
    }

    /**
     * AJAX: Search options for a template column (Select2 AJAX)
     * GET /orders/searchOptions/{expeditionId}?position=X&search=query&page=1
     */
    public function searchOptions($expeditionId) {
        $expeditionId = (int)$expeditionId;
        $position = (int)($_GET['position'] ?? 0);
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;

        $options = $this->templateService->getColumnOptions($expeditionId, $position);

        // Filter by search term
        if ($search !== '') {
            $options = array_values(array_filter($options, function($opt) use ($search) {
                return stripos($opt, $search) !== false;
            }));
        }

        // Paginate
        $total = count($options);
        $offset = ($page - 1) * $limit;
        $paged = array_slice($options, $offset, $limit);

        $results = [];
        foreach ($paged as $opt) {
            $results[] = ['id' => $opt, 'text' => $opt];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'results' => $results,
            'pagination' => ['more' => ($offset + $limit) < $total],
        ]);
        exit;
    }
}
