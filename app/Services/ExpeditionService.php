<?php
namespace App\Services;

use App\Repositories\ExpeditionRepository;

class ExpeditionService {
    private $expeditionRepo;
    private AuditService $audit;

    public function __construct() {
        $this->expeditionRepo = new ExpeditionRepository();
        $this->audit = new AuditService();
    }

    public function getAll(): array {
        return $this->expeditionRepo->findAll();
    }

    public function find(int $id): ?array {
        return $this->expeditionRepo->findById($id);
    }

    public function create(array $data): int {
        $id = $this->expeditionRepo->create($data);
        $this->audit->log('create', 'expedition', $id, $data['name'] ?? '', null, $data);
        return $id;
    }

    public function update(int $id, array $data): bool {
        $old = $this->expeditionRepo->findById($id);
        $result = $this->expeditionRepo->update($id, $data);
        if ($result) {
            $this->audit->log('update', 'expedition', $id, $old['name'] ?? '', $old, $data);
        }
        return $result;
    }

    public function delete(int $id): bool {
        $old = $this->expeditionRepo->findById($id);
        $result = $this->expeditionRepo->delete($id);
        if ($result && $old) {
            $this->audit->log('delete', 'expedition', $id, $old['name'] ?? '', $old, null);
        }
        return $result;
    }
}
