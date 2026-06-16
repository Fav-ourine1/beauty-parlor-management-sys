<?php
class ServiceModel extends Model
{
    protected string $table = 'services';

    public function getAllWithCategories(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE s.is_active = 1' : '';

        return $this->db->fetchAll(
            "SELECT s.*, sc.name AS category_name, sc.description AS category_description
             FROM services s
             JOIN service_categories sc ON s.category_id = sc.id
             {$where}
             ORDER BY sc.name, s.name"
        );
    }

    public function getByCategory(int $categoryId, bool $activeOnly = true): array
    {
        if ($activeOnly) {
            return $this->db->fetchAll(
                'SELECT * FROM services WHERE category_id = ? AND is_active = 1 ORDER BY name',
                [$categoryId]
            );
        }

        return $this->db->fetchAll(
            'SELECT * FROM services WHERE category_id = ? ORDER BY name',
            [$categoryId]
        );
    }

    public function getCategories(bool $activeOnly = true): array
    {
        if ($activeOnly) {
            return $this->db->fetchAll(
                'SELECT * FROM service_categories WHERE is_active = 1 ORDER BY name'
            );
        }

        return $this->db->fetchAll(
            'SELECT * FROM service_categories ORDER BY name'
        );
    }

    public function getCategoryById(int $id): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM service_categories WHERE id = ? LIMIT 1',
            [$id]
        );
    }

    public function createCategory(array $data): string
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return $this->db->insert(
            "INSERT INTO service_categories ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public function updateCategory(int $id, array $data): int
    {
        $clauses = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));

        return $this->db->execute(
            "UPDATE service_categories SET {$clauses} WHERE id = ?",
            [...array_values($data), $id]
        );
    }

    public function getServicesById(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchAll(
            "SELECT * FROM services WHERE id IN ({$placeholders})",
            $ids
        );

        $keyed = [];
        foreach ($rows as $row) {
            $keyed[$row['id']] = $row;
        }

        return $keyed;
    }
}
