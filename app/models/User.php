<?php

namespace App\Models;

use Database;
use PDO;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new user
     */
    public function create(string $username, string $email, string $password, string $role = 'student'): int|false
    {
        $password_hash = password_hash($password, PASSWORD_ARGON2ID);

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash, role) 
             VALUES (:username, :email, :password_hash, :role)"
        );
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            Database::audit('user.create', "User registered: {$username}", (int)$this->db->lastInsertId());
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Find a user by username
     */
    public function findByUsername(string $username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Find a user by ID
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Get all users with optional pagination
     */
    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.username, u.email, u.role, u.full_name, u.is_active, u.created_at, u.last_login,
                    (SELECT COUNT(*) FROM credentials c WHERE c.user_id = u.id) as credential_count
             FROM users u ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total users
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Verify user password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Update user role
     */
    public function updateRole(int $id, string $role): bool
    {
        $validRoles = ['student', 'issuer', 'designer', 'viewer', 'moderator', 'admin'];
        if (!in_array($role, $validRoles)) return false;

        $stmt = $this->db->prepare("UPDATE users SET role = :role, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();
        
        if ($result) {
            Database::audit('user.role_change', "User {$id} role changed to {$role}");
        }
        return $result;
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $id, array $data): bool
    {
        $allowed = ['full_name', 'bio', 'avatar_url'];
        $sets = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        
        if (empty($sets)) return false;
        
        $sets[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Search users
     */
    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, role, full_name, is_active, created_at 
             FROM users WHERE username LIKE :q OR email LIKE :q2 OR full_name LIKE :q3 
             ORDER BY username LIMIT 50"
        );
        $like = '%' . $query . '%';
        $stmt->bindValue(':q', $like);
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':q3', $like);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
