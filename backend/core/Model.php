<?php
/**
 * Base Model
 * All models extend this. Provides common CRUD and convenience methods.
 */
abstract class Model
{
    protected Database $db;
    protected string $table  = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1",
            [$id]
        );
    }

    public function findAll(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$dir}"
        );
    }

    public function findWhere(array $conditions, string $orderBy = 'id'): array
    {
        $clauses = implode(' AND ', array_map(fn($k) => "{$k} = ?", array_keys($conditions)));
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$clauses} ORDER BY {$orderBy}",
            array_values($conditions)
        );
    }

    public function findOneWhere(array $conditions): array|false
    {
        $clauses = implode(' AND ', array_map(fn($k) => "{$k} = ?", array_keys($conditions)));
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE {$clauses} LIMIT 1",
            array_values($conditions)
        );
    }

    public function create(array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        return $this->db->insert(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public function update(int $id, array $data): int
    {
        $clauses = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        return $this->db->execute(
            "UPDATE {$this->table} SET {$clauses} WHERE {$this->primaryKey} = ?",
            [...array_values($data), $id]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            return (int) $this->db->fetchScalar("SELECT COUNT(*) FROM {$this->table}");
        }
        $clauses = implode(' AND ', array_map(fn($k) => "{$k} = ?", array_keys($conditions)));
        return (int) $this->db->fetchScalar(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$clauses}",
            array_values($conditions)
        );
    }
}