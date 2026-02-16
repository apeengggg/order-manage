<?php
namespace App\Services;

use App\Repositories\TemplateRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateService {
    private $templateRepo;
    private $fileService;
    private AuditService $audit;

    public function __construct() {
        $this->templateRepo = new TemplateRepository();
        $this->fileService = new FileService();
        $this->audit = new AuditService();
    }

    /**
     * Parse an uploaded XLSX file and store template metadata.
     */
    public function parseAndStore(array $uploadedFile, int $expeditionId, int $userId): array {
        // Validate file type
        $ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            return ['success' => false, 'message' => 'File harus berformat XLSX atau XLS.'];
        }

        try {
            $spreadsheet = IOFactory::load($uploadedFile['tmp_name']);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()];
        }

        // Find first visible sheet
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

        $sheetName = $dataSheet->getTitle();

        // Read header row (row 1)
        $columns = [];
        $highestCol = $dataSheet->getHighestColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        for ($colIdx = 1; $colIdx <= $highestColIndex; $colIdx++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $cellValue = $dataSheet->getCell($colLetter . '1')->getValue();
            if ($cellValue === null || trim((string)$cellValue) === '') {
                continue;
            }

            $name = trim((string)$cellValue);
            $isRequired = false;

            // Detect * prefix for required fields
            if (str_starts_with($name, '*')) {
                $isRequired = true;
                $cleanName = trim(ltrim($name, '* '));
            } else {
                $cleanName = $name;
            }

            // Strip trailing // bilingual text (e.g. "*Nama Penerima // *Recipient Name")
            if (strpos($cleanName, '//') !== false) {
                $cleanName = trim(explode('//', $cleanName)[0]);
            }

            // Check for data validation (dropdown options)
            $inputType = 'text';
            $options = null;
            $dvOptions = $this->extractDataValidation($dataSheet, $colIdx, $spreadsheet);
            if ($dvOptions !== null) {
                $inputType = 'select';
                $options = $dvOptions;
            }

            $columns[] = [
                'name' => $name,
                'clean_name' => $cleanName,
                'position' => $colIdx - 1,
                'is_required' => $isRequired,
                'input_type' => $inputType,
                'options' => $options,
            ];
        }

        if (empty($columns)) {
            return ['success' => false, 'message' => 'Tidak ada kolom header ditemukan di sheet pertama.'];
        }

        // Upload XLSX file via FileService
        $fileResult = $this->fileService->upload($uploadedFile, 'expedition-templates', $expeditionId, $userId);
        $fileId = $fileResult['success'] ? ($fileResult['file_id'] ?? null) : null;

        // Check if template already exists (for audit: create vs update)
        $oldTemplate = $this->templateRepo->findByExpeditionId($expeditionId);

        // Upsert template
        $this->templateRepo->upsert([
            'expedition_id' => $expeditionId,
            'file_id' => $fileId,
            'sheet_name' => $sheetName,
            'columns' => json_encode($columns, JSON_UNESCAPED_UNICODE),
            'uploaded_by' => $userId,
        ]);

        // Get expedition name for audit label
        $db = getDB();
        $expStmt = $db->prepare("SELECT name FROM expeditions WHERE id = ?");
        $expStmt->execute([$expeditionId]);
        $expName = $expStmt->fetchColumn() ?: "Expedition #$expeditionId";

        $newData = ['sheet_name' => $sheetName, 'columns_count' => count($columns), 'file_id' => $fileId];
        if ($oldTemplate) {
            $oldData = ['sheet_name' => $oldTemplate['sheet_name'], 'columns_count' => count(json_decode($oldTemplate['columns'], true) ?: []), 'file_id' => $oldTemplate['file_id']];
            $this->audit->log('update', 'template', (string)$expeditionId, $expName, $oldData, $newData);
        } else {
            $this->audit->log('create', 'template', (string)$expeditionId, $expName, null, $newData);
        }

        return [
            'success' => true,
            'message' => 'Template berhasil diupload. ' . count($columns) . ' kolom ditemukan.',
            'columns' => $columns,
            'sheet_name' => $sheetName,
        ];
    }

    /**
     * Extract data validation options for a column.
     */
    private function extractDataValidation(Worksheet $sheet, int $colIdx, $spreadsheet): ?array {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $dataValidations = $sheet->getDataValidationCollection();

        foreach ($dataValidations as $range => $dv) {
            if ($dv->getType() !== \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST) {
                continue;
            }

            // Check if this validation applies to our column
            if (!$this->validationAppliesToColumn($range, $colLetter)) {
                continue;
            }

            $formula = $dv->getFormula1();
            if (empty($formula)) continue;

            // Case 1: Inline options like "Opt1,Opt2,Opt3"
            if (str_starts_with($formula, '"') && str_ends_with($formula, '"')) {
                $inner = substr($formula, 1, -1);
                return array_map('trim', explode(',', $inner));
            }

            // Case 2: Sheet reference like Sheet1!$A$1:$A$2
            if (preg_match('/^([^!]+)!\$?([A-Z]+)\$?(\d+):\$?([A-Z]+)\$?(\d+)$/', $formula, $m)) {
                $refSheet = $spreadsheet->getSheetByName($m[1]);
                if ($refSheet) {
                    return $this->readRangeValues($refSheet, $m[2], (int)$m[3], $m[4], (int)$m[5]);
                }
            }

            // Case 3: OFFSET formula referencing another sheet - extract sheet name and column
            if (preg_match('/OFFSET\(([^!]+)!\$?([A-Z]+)\$?\d+/', $formula, $m)) {
                $refSheet = $spreadsheet->getSheetByName($m[1]);
                if ($refSheet) {
                    return $this->readColumnValues($refSheet, $m[2]);
                }
            }

            // Case 4: Simple range like $A$1:$A$10
            if (preg_match('/^\$?([A-Z]+)\$?(\d+):\$?([A-Z]+)\$?(\d+)$/', $formula, $m)) {
                return $this->readRangeValues($sheet, $m[1], (int)$m[2], $m[3], (int)$m[4]);
            }
        }

        return null;
    }

    /**
     * Check if a validation range applies to a specific column.
     */
    private function validationAppliesToColumn(string $range, string $colLetter): bool {
        // Range can be like "J2", "J3:J1002", "D2:D1002", "K24:K25 M24:M25"
        $parts = preg_split('/\s+/', $range);
        foreach ($parts as $part) {
            if (preg_match('/^([A-Z]+)\d+/', $part, $m)) {
                if ($m[1] === $colLetter) return true;
            }
            if (preg_match('/^([A-Z]+)\d+:([A-Z]+)\d+/', $part, $m)) {
                if ($m[1] === $colLetter || $m[2] === $colLetter) return true;
            }
        }
        return false;
    }

    /**
     * Read values from a range in a sheet.
     */
    private function readRangeValues(Worksheet $sheet, string $colStart, int $rowStart, string $colEnd, int $rowEnd): array {
        $values = [];
        for ($row = $rowStart; $row <= $rowEnd; $row++) {
            $val = $sheet->getCell($colStart . $row)->getValue();
            if ($val !== null && trim((string)$val) !== '') {
                $values[] = trim((string)$val);
            }
        }
        return $values;
    }

    /**
     * Read all non-empty values from a column (skip header row).
     */
    private function readColumnValues(Worksheet $sheet, string $col): array {
        $values = [];
        $maxRow = $sheet->getHighestRow();
        for ($row = 1; $row <= $maxRow; $row++) {
            $val = $sheet->getCell($col . $row)->getValue();
            if ($val === null || trim((string)$val) === '') continue;
            $values[] = trim((string)$val);
        }
        return array_values(array_unique($values));
    }

    /**
     * Get template metadata for an expedition.
     */
    public function getTemplate(int $expeditionId): ?array {
        return $this->templateRepo->findByExpeditionId($expeditionId);
    }

    /**
     * Get parsed template columns for an expedition.
     */
    public function getTemplateColumns(int $expeditionId): array {
        $template = $this->templateRepo->findByExpeditionId($expeditionId);
        if (!$template) return [];
        return json_decode($template['columns'], true) ?: [];
    }

    /**
     * Check which expedition IDs have templates.
     */
    public function getTemplateMap(array $expeditionIds): array {
        return $this->templateRepo->findByExpeditionIds($expeditionIds);
    }

    /**
     * Update template columns (editable fields only: clean_name, is_required, input_type, options).
     */
    public function updateColumns(int $expeditionId, array $incomingColumns): array {
        $existing = $this->templateRepo->findByExpeditionId($expeditionId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Template tidak ditemukan.'];
        }

        $existingColumns = json_decode($existing['columns'], true) ?: [];
        $existingByPos = [];
        foreach ($existingColumns as $col) {
            $existingByPos[$col['position']] = $col;
        }

        $updated = [];
        foreach ($incomingColumns as $col) {
            $pos = $col['position'] ?? null;
            if ($pos === null || !isset($existingByPos[$pos])) {
                continue;
            }

            $orig = $existingByPos[$pos];
            $orig['clean_name'] = trim($col['clean_name'] ?? $orig['clean_name']);
            $orig['is_required'] = (bool)($col['is_required'] ?? $orig['is_required']);
            $orig['input_type'] = in_array($col['input_type'] ?? '', ['text', 'select'])
                ? $col['input_type']
                : $orig['input_type'];

            if ($orig['input_type'] === 'select' && isset($col['options'])) {
                if (is_string($col['options'])) {
                    $orig['options'] = array_values(array_filter(
                        array_map('trim', explode(',', $col['options'])),
                        fn($v) => $v !== ''
                    ));
                } else {
                    $orig['options'] = (array)$col['options'];
                }
            } elseif ($orig['input_type'] === 'text') {
                $orig['options'] = null;
            }

            $updated[] = $orig;
        }

        usort($updated, fn($a, $b) => $a['position'] <=> $b['position']);

        $json = json_encode($updated, JSON_UNESCAPED_UNICODE);
        $this->templateRepo->updateColumns($expeditionId, $json);

        // Audit log
        $db = getDB();
        $expStmt = $db->prepare("SELECT name FROM expeditions WHERE id = ?");
        $expStmt->execute([$expeditionId]);
        $expName = $expStmt->fetchColumn() ?: "Expedition #$expeditionId";
        $this->audit->log('update', 'template', (string)$expeditionId, $expName,
            ['columns' => $existingColumns], ['columns' => $updated]);

        return ['success' => true, 'message' => 'Kolom template berhasil diupdate.', 'columns' => $updated];
    }

    /**
     * Get the filesystem path of the original template XLSX file.
     */
    public function getTemplateFilePath(int $expeditionId): ?string {
        $template = $this->templateRepo->findByExpeditionId($expeditionId);
        if (!$template || empty($template['file_id'])) {
            return null;
        }

        $file = $this->fileService->getFile((int)$template['file_id']);
        if (!$file) {
            return null;
        }

        return $this->fileService->getDownloadPath($file);
    }

    /**
     * Get full options array for a specific column by position.
     */
    public function getColumnOptions(int $expeditionId, int $position): array {
        $columns = $this->getTemplateColumns($expeditionId);
        foreach ($columns as $col) {
            if ((int)$col['position'] === $position && $col['input_type'] === 'select') {
                return $col['options'] ?? [];
            }
        }
        return [];
    }

    /**
     * Delete template for an expedition.
     */
    public function deleteTemplate(int $expeditionId): bool {
        $old = $this->templateRepo->findByExpeditionId($expeditionId);
        $result = $this->templateRepo->delete($expeditionId);

        if ($result && $old) {
            $db = getDB();
            $expStmt = $db->prepare("SELECT name FROM expeditions WHERE id = ?");
            $expStmt->execute([$expeditionId]);
            $expName = $expStmt->fetchColumn() ?: "Expedition #$expeditionId";
            $this->audit->log('delete', 'template', (string)$expeditionId, $expName,
                ['sheet_name' => $old['sheet_name'], 'file_id' => $old['file_id']], null);
        }

        return $result;
    }
}
