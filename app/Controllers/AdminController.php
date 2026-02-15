<?php
namespace App\Controllers;

use App\Services\OrderService;
use App\Services\ExpeditionService;
use App\Services\TemplateService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminController {
    private $orderService;
    private $expeditionService;
    private $templateService;

    public function __construct() {
        $this->orderService = new OrderService();
        $this->expeditionService = new ExpeditionService();
        $this->templateService = new TemplateService();
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

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $ids = $_POST['order_ids'] ?? [];
        if (empty($ids)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Pilih minimal satu order untuk diexport.']);
                exit;
            }
            flash('error', 'Pilih minimal satu order untuk diexport.');
            redirect('admin');
        }

        $count = $this->orderService->exportOrders($ids, auth('user_id'));
        flash('success', "$count order berhasil diexport.");

        $orders = $this->orderService->generateCsv($ids);
        if (!empty($orders)) {
            $this->downloadExport($orders);
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak ada data untuk diexport.']);
            exit;
        }

        redirect('admin');
    }

    /**
     * Group orders by expedition and export accordingly.
     */
    private function downloadExport(array $orders): void {
        // Group orders by expedition_id
        $grouped = [];
        foreach ($orders as $o) {
            $expId = $o['expedition_id'] ?? 0;
            $grouped[$expId][] = $o;
        }

        // Single expedition → single file download
        if (count($grouped) === 1) {
            $expId = (int)array_key_first($grouped);
            $expOrders = reset($grouped);

            if ($expId > 0) {
                $templateColumns = $this->templateService->getTemplateColumns($expId);
                if (!empty($templateColumns)) {
                    $this->downloadTemplateXlsx($expOrders, $templateColumns, $expId);
                    return;
                }
            }

            $this->downloadGenericCsv($expOrders);
            return;
        }

        // Multiple expeditions → ZIP with one XLSX per expedition
        $this->downloadMultiExpeditionZip($grouped);
    }

    /**
     * Create a ZIP file with one XLSX per expedition.
     */
    private function downloadMultiExpeditionZip(array $grouped): void {
        $tmpDir = sys_get_temp_dir();
        $zipPath = $tmpDir . '/export_orders_' . date('Ymd_His') . '_' . uniqid() . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            $this->downloadGenericCsv(array_merge(...array_values($grouped)));
            return;
        }

        $tempFiles = [];

        foreach ($grouped as $expId => $expOrders) {
            $expId = (int)$expId;
            $expName = $expOrders[0]['expedition_name'] ?? $expOrders[0]['expedition_code'] ?? 'unknown';
            $safeExpName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $expName);

            if ($expId > 0) {
                $templateColumns = $this->templateService->getTemplateColumns($expId);
                if (!empty($templateColumns)) {
                    $tmpFile = $this->buildTemplateXlsx($expOrders, $templateColumns, $expId);
                    if ($tmpFile) {
                        $zip->addFile($tmpFile, $safeExpName . '_' . date('Ymd_His') . '.xlsx');
                        $tempFiles[] = $tmpFile;
                        continue;
                    }
                }
            }

            // Fallback: CSV for this expedition
            $csvFile = $tmpDir . '/' . uniqid('csv_') . '.csv';
            $output = fopen($csvFile, 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, ['No', 'Nama Customer', 'Telepon', 'Alamat', 'Produk', 'Qty', 'Harga', 'Total', 'Ekspedisi', 'Resi', 'Catatan']);
            $no = 1;
            foreach ($expOrders as $o) {
                fputcsv($output, [
                    $no++, $o['customer_name'], $o['customer_phone'], $o['customer_address'],
                    $o['product_name'], $o['qty'], $o['price'], $o['total'],
                    $o['expedition_name'] ?? '-', $o['resi'] ?? '-', $o['notes'] ?? '-'
                ]);
            }
            fclose($output);
            $zip->addFile($csvFile, $safeExpName . '_' . date('Ymd_His') . '.csv');
            $tempFiles[] = $csvFile;
        }

        $zip->close();

        // Stream ZIP to browser
        $filename = 'export_orders_' . date('Ymd_His') . '.zip';
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: max-age=0');
        readfile($zipPath);

        // Cleanup temp files
        @unlink($zipPath);
        foreach ($tempFiles as $f) {
            @unlink($f);
        }
        exit;
    }

    /**
     * Build a template XLSX to a temp file and return the path.
     */
    private function buildTemplateXlsx(array $orders, array $templateColumns, int $expeditionId): ?string {
        $templatePath = $this->templateService->getTemplateFilePath($expeditionId);
        if (!$templatePath || !file_exists($templatePath)) {
            return null;
        }

        try {
            $spreadsheet = IOFactory::load($templatePath);
        } catch (\Exception $e) {
            return null;
        }

        $dataSheet = null;
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if ($sheet->getSheetState() === Worksheet::SHEETSTATE_VISIBLE) {
                $dataSheet = $sheet;
                break;
            }
        }
        if (!$dataSheet) {
            $dataSheet = $spreadsheet->getActiveSheet();
        }

        $row = 2;
        foreach ($orders as $order) {
            $extra = json_decode($order['extra_fields'] ?? '{}', true) ?: [];
            foreach ($templateColumns as $col) {
                $colIndex = $col['position'] + 1;
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $value = $extra[$col['name']] ?? '';
                $dataSheet->setCellValue($colLetter . $row, $value);
            }
            $row++;
        }

        $tmpFile = sys_get_temp_dir() . '/' . uniqid('xlsx_') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tmpFile);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $tmpFile;
    }

    /**
     * Export single XLSX using the original expedition template file as base.
     */
    private function downloadTemplateXlsx(array $orders, array $templateColumns, int $expeditionId): void {
        $templatePath = $this->templateService->getTemplateFilePath($expeditionId);

        if (!$templatePath || !file_exists($templatePath)) {
            $this->downloadTemplateCsvFallback($orders, $templateColumns);
            return;
        }

        try {
            $spreadsheet = IOFactory::load($templatePath);
        } catch (\Exception $e) {
            $this->downloadTemplateCsvFallback($orders, $templateColumns);
            return;
        }

        $dataSheet = null;
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if ($sheet->getSheetState() === Worksheet::SHEETSTATE_VISIBLE) {
                $dataSheet = $sheet;
                break;
            }
        }
        if (!$dataSheet) {
            $dataSheet = $spreadsheet->getActiveSheet();
        }

        $row = 2;
        foreach ($orders as $order) {
            $extra = json_decode($order['extra_fields'] ?? '{}', true) ?: [];
            foreach ($templateColumns as $col) {
                $colIndex = $col['position'] + 1;
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $value = $extra[$col['name']] ?? '';
                $dataSheet->setCellValue($colLetter . $row, $value);
            }
            $row++;
        }

        $filename = 'export_orders_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    }

    /**
     * Generic CSV export (no template).
     */
    private function downloadGenericCsv(array $orders): void {
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

    /**
     * CSV fallback when template file cannot be loaded.
     */
    private function downloadTemplateCsvFallback(array $orders, array $templateColumns): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="export_orders_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = array_map(fn($c) => $c['clean_name'], $templateColumns);
        fputcsv($output, $headers);

        foreach ($orders as $o) {
            $extra = json_decode($o['extra_fields'] ?? '{}', true) ?: [];
            $row = [];
            foreach ($templateColumns as $col) {
                $row[] = $extra[$col['name']] ?? '';
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
