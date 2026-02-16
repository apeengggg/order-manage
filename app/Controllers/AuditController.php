<?php
namespace App\Controllers;

use App\Services\AuditService;

class AuditController {
    private AuditService $auditService;

    public function __construct() {
        $this->auditService = new AuditService();
    }

    public function index(): void {
        checkPermission('audit', 'can_view');

        $filters = [
            'entity_type' => $_GET['entity_type'] ?? '',
            'action' => $_GET['action'] ?? '',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $logs = $this->auditService->getAll($filters, $perPage, $offset);
        $totalCount = $this->auditService->countAll($filters);
        $totalPages = max(1, ceil($totalCount / $perPage));

        $pageTitle = 'Audit Log';
        require ROOT_PATH . '/views/audit/index.php';
    }
}
