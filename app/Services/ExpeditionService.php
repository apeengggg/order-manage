<?php
namespace App\Services;

use App\Repositories\ExpeditionRepository;

class ExpeditionService {
    private $expeditionRepo;

    public function __construct() {
        $this->expeditionRepo = new ExpeditionRepository();
    }

    public function getAll(): array {
        return $this->expeditionRepo->findAll();
    }

    public function find(int $id): ?array {
        return $this->expeditionRepo->findById($id);
    }

    public function create(array $data): bool {
        return $this->expeditionRepo->create($data);
    }

    public function update(int $id, array $data): bool {
        return $this->expeditionRepo->update($id, $data);
    }

    public function delete(int $id): bool {
        return $this->expeditionRepo->delete($id);
    }
}
