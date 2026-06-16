<?php
class ClientModel extends Model
{
    protected string $table = 'clients';

    public function findByUserId(int $userId): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM clients WHERE user_id = ? LIMIT 1',
            [$userId]
        );
    }

    public function getWithUser(int $clientId): array|false
    {
        return $this->db->fetchOne(
            'SELECT c.*, u.full_name, u.email, u.phone, u.is_active, u.created_at AS user_created_at
             FROM clients c
             JOIN users u ON c.user_id = u.id
             WHERE c.id = ? LIMIT 1',
            [$clientId]
        );
    }

    public function createProfile(int $userId, array $data): string
    {
        $fields = ['user_id' => $userId];
        foreach (['date_of_birth', 'gender', 'address', 'allergies', 'hair_type', 'skin_type', 'notes'] as $key) {
            if (array_key_exists($key, $data)) {
                $fields[$key] = $data[$key];
            }
        }

        $columns      = implode(', ', array_keys($fields));
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        return $this->db->insert(
            "INSERT INTO clients ({$columns}) VALUES ({$placeholders})",
            array_values($fields)
        );
    }

    public function updateProfile(int $userId, array $data): int
    {
        $allowed = ['date_of_birth', 'gender', 'address', 'allergies', 'hair_type', 'skin_type', 'notes'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        if (empty($fields)) {
            return 0;
        }

        $clauses = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($fields)));

        return $this->db->execute(
            "UPDATE clients SET {$clauses} WHERE user_id = ?",
            [...array_values($fields), $userId]
        );
    }

    public function getAllWithUsers(): array
    {
        return $this->db->fetchAll(
            'SELECT c.*, u.full_name, u.email, u.phone, u.is_active, r.name AS role
             FROM clients c
             JOIN users u ON c.user_id = u.id
             JOIN roles r ON u.role_id = r.id
             ORDER BY u.full_name ASC'
        );
    }
}
