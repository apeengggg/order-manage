<?php
namespace App\Services;

use App\Repositories\OrderRepository;

class OrderService {
    private $orderRepo;

    /**
     * Map template column clean_name (lowercase) to orders table column.
     */
    private const FIELD_MAP = [
        'customer_name' => ['nama penerima', 'penerima', 'nama_penerima'],
        'customer_phone' => [
            'nomor telepon penerima', 'no handphone penerima', 'no.hp penerima',
            'telpon1_penerima', 'kontak penerima', 'no handphone', 'kontak'
        ],
        'customer_address' => ['alamat lengkap', 'alamat penerima', 'alamat_penerima_1', 'detail address'],
        'product_name' => ['nama barang', 'deskripsi barang', 'deskripsi_barang', 'item name'],
        'qty' => ['jumlah barang', 'jumlah', 'koli', 'item quantity'],
        'price' => ['harga barang', 'harga paket', 'nilai_barang', 'parcel value'],
    ];

    public function __construct() {
        $this->orderRepo = new OrderRepository();
    }

    public function getAll(array $filters = []): array {
        return $this->orderRepo->findAll($filters);
    }

    public function find(int $id): ?array {
        $order = $this->orderRepo->findById($id);
        if ($order && !empty($order['extra_fields'])) {
            $order['extra_fields_decoded'] = json_decode($order['extra_fields'], true) ?: [];
        } else if ($order) {
            $order['extra_fields_decoded'] = [];
        }
        return $order;
    }

    public function create(array $data): int {
        $data['total'] = $data['qty'] * $data['price'];
        $data['expedition_id'] = $data['expedition_id'] ?: null;
        $data['resi'] = $data['resi'] ?? null;
        $data['notes'] = $data['notes'] ?? null;
        return $this->orderRepo->create($data);
    }

    /**
     * @return bool|null  true=updated, false=failed, null=blocked by export
     */
    public function update(int $id, array $data): ?bool {
        $order = $this->orderRepo->findById($id);
        if (!$order) return false;
        if ($order['is_exported']) return null;

        $data['total'] = $data['qty'] * $data['price'];
        $data['expedition_id'] = $data['expedition_id'] ?: null;
        $data['resi'] = $data['resi'] ?? null;
        $data['notes'] = $data['notes'] ?? null;
        return $this->orderRepo->update($id, $data);
    }

    /**
     * @return bool|null  true=deleted, false=not found, null=blocked by export
     */
    public function delete(int $id): ?bool {
        $order = $this->orderRepo->findById($id);
        if (!$order) return false;
        if ($order['is_exported']) return null;
        return $this->orderRepo->delete($id);
    }

    public function isExported(int $id): bool {
        $order = $this->orderRepo->findById($id);
        return $order && (bool)$order['is_exported'];
    }

    public function getByExpedition(int $expeditionId): array {
        return $this->orderRepo->findByExpedition($expeditionId);
    }

    public function exportOrders(array $ids, int $userId): int {
        return $this->orderRepo->markExported($ids, $userId);
    }

    public function getDashboardStats(): array {
        return [
            'totalOrders' => $this->orderRepo->countAll(),
            'exported' => $this->orderRepo->countExported(),
            'pending' => $this->orderRepo->countPending(),
            'revenue' => $this->orderRepo->totalRevenue(),
        ];
    }

    public function generateCsv(array $ids): array {
        $orders = [];
        foreach ($ids as $id) {
            $o = $this->orderRepo->findById($id);
            if ($o) $orders[] = $o;
        }
        return $orders;
    }

    /**
     * Map extra_fields values to standard order columns using FIELD_MAP.
     */
    public function mapTemplateToCommon(array $extraFields): array {
        $mapped = [];
        foreach (self::FIELD_MAP as $dbCol => $aliases) {
            foreach ($extraFields as $colName => $value) {
                $clean = strtolower(trim(ltrim($colName, '* ')));
                if (in_array($clean, $aliases)) {
                    $mapped[$dbCol] = $value;
                    break;
                }
            }
        }
        return $mapped;
    }
}
