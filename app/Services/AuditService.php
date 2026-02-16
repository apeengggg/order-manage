<?php
namespace App\Services;

use App\Repositories\AuditRepository;
use App\TenantContext;

class AuditService {
    private AuditRepository $repo;

    public function __construct() {
        $this->repo = new AuditRepository();
    }

    public function log(string $action, string $entityType, string $entityId, ?string $label = null, ?array $old = null, ?array $new = null): void {
        // Skip if no user logged in
        if (!isset($_SESSION['user_id'])) return;

        // For updates, only store changed fields
        if ($action === 'update' && $old !== null && $new !== null) {
            $diff = $this->diffValues($old, $new);
            if (empty($diff['old']) && empty($diff['new'])) return; // nothing changed
            $old = $diff['old'];
            $new = $diff['new'];
        }

        // Remove sensitive fields
        $old = $this->sanitize($old);
        $new = $this->sanitize($new);

        try {
            $this->repo->create([
                'tenant_id' => TenantContext::id(),
                'user_id' => $_SESSION['user_id'],
                'user_name' => $_SESSION['name'] ?? $_SESSION['username'] ?? 'Unknown',
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => (string)$entityId,
                'entity_label' => $label ? mb_substr($label, 0, 200) : null,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Exception $e) {
            // Audit logging should never break the main operation
            error_log("Audit log failed: " . $e->getMessage());
        }
    }

    public function getAll(array $filters = [], int $limit = 50, int $offset = 0): array {
        return $this->repo->findAll($filters, $limit, $offset);
    }

    public function countAll(array $filters = []): int {
        return $this->repo->countAll($filters);
    }

    public function getByEntity(string $type, string $id): array {
        return $this->repo->findByEntity($type, $id);
    }

    /**
     * Compare old and new values, return only changed fields
     */
    public function diffValues(array $old, array $new): array {
        $oldDiff = [];
        $newDiff = [];

        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            // Skip internal/auto fields
            if (in_array($key, ['created_at', 'updated_at', 'id', 'tenant_id'])) continue;

            if ((string)$oldVal !== (string)$newVal) {
                $oldDiff[$key] = $oldVal;
                $newDiff[$key] = $newVal;
            }
        }

        return ['old' => $oldDiff, 'new' => $newDiff];
    }

    /**
     * Remove sensitive fields from audit data
     */
    private function sanitize(?array $data): ?array {
        if ($data === null) return null;
        $sensitive = ['password'];
        foreach ($sensitive as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***';
            }
        }
        return $data;
    }
}
