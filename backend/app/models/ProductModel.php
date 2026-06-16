<?php
class ProductModel extends Model
{
    protected string $table = 'products';

    public function getAllWithCategories(bool $activeOnly = false): array
    {
        $where = $activeOnly ? 'WHERE p.is_active = 1' : '';

        return $this->db->fetchAll(
            "SELECT p.*, pc.name AS category_name
             FROM products p
             JOIN product_categories pc ON p.category_id = pc.id
             {$where}
             ORDER BY pc.name, p.name"
        );
    }

    public function getLowStock(): array
    {
        return $this->db->fetchAll('SELECT * FROM v_low_stock_products');
    }

    public function getCategories(): array
    {
        return $this->db->fetchAll('SELECT * FROM product_categories ORDER BY name');
    }

    public function adjustStock(int $productId, int $change, array $movementData): void
    {
        $this->db->beginTransaction();

        try {
            $this->db->execute(
                'UPDATE products SET current_stock = current_stock + ? WHERE id = ?',
                [$change, $productId]
            );

            $stockAfter = (int) $this->db->fetchScalar(
                'SELECT current_stock FROM products WHERE id = ?',
                [$productId]
            );

            $record = [
                'product_id'     => $productId,
                'movement_type'  => $movementData['movement_type'],
                'quantity_change' => $change,
                'stock_after'    => $stockAfter,
                'notes'          => $movementData['notes'] ?? null,
                'recorded_by'    => $movementData['recorded_by'],
            ];

            if (!empty($movementData['reference_id'])) {
                $record['reference_id'] = $movementData['reference_id'];
            }

            $columns      = implode(', ', array_keys($record));
            $placeholders = implode(', ', array_fill(0, count($record), '?'));

            $this->db->insert(
                "INSERT INTO stock_movements ({$columns}) VALUES ({$placeholders})",
                array_values($record)
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getMovements(int $productId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            'SELECT sm.*, u.full_name AS recorded_by_name
             FROM stock_movements sm
             JOIN users u ON sm.recorded_by = u.id
             WHERE sm.product_id = ?
             ORDER BY sm.created_at DESC
             LIMIT ?',
            [$productId, $limit]
        );
    }

    public function findBySku(string $sku): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM products WHERE sku = ? LIMIT 1',
            [$sku]
        );
    }
}
