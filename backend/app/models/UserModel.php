<?php
class UserModel extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    public function findByPhone(string $phone): array|false
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE phone = ? LIMIT 1',
            [$phone]
        );
    }

    public function findWithRole(int $id): array|false
    {
        return $this->db->fetchOne(
            'SELECT u.*, r.name AS role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? LIMIT 1',
            [$id]
        );
    }

    public function createUser(array $data, string $roleName): string
    {
        $role = $this->db->fetchOne(
            'SELECT id FROM roles WHERE name = ? LIMIT 1',
            [$roleName]
        );

        if (!$role) {
            throw new \InvalidArgumentException("Role '{$roleName}' not found.");
        }

        return $this->db->insert(
            'INSERT INTO users (role_id, full_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)',
            [
                $role['id'],
                $data['full_name'],
                $data['email'],
                $data['phone'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            ]
        );
    }

    public function updateLastLogin(int $userId): void
    {
        $this->db->execute(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?',
            [$userId]
        );
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function setActive(int $userId, bool $active): void
    {
        $this->db->execute(
            'UPDATE users SET is_active = ? WHERE id = ?',
            [(int) $active, $userId]
        );
    }

    public function getAllWithRoles(): array
    {
        return $this->db->fetchAll(
            'SELECT u.*, r.name AS role FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC'
        );
    }
}
