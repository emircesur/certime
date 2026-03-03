<?php

namespace App\Models;

use Database;
use PDO;

class ApiKey
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, string $name = 'API Key', array $scopes = ['*'], ?int $institutionId = null): array
    {
        $rawKey = 'cm_' . bin2hex(random_bytes(24));
        $keyHash = hash('sha256', $rawKey);
        $keyPrefix = substr($rawKey, 0, 10);

        $stmt = $this->db->prepare(
            "INSERT INTO api_keys (user_id, institution_id, key_hash, key_prefix, name, scopes)
             VALUES (:uid, :iid, :hash, :prefix, :name, :scopes)"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':hash', $keyHash);
        $stmt->bindParam(':prefix', $keyPrefix);
        $stmt->bindParam(':name', $name);
        $scopesJson = json_encode($scopes);
        $stmt->bindParam(':scopes', $scopesJson);

        if ($stmt->execute()) {
            Database::audit('api_key.create', "API key created: {$name}");
            return [
                'id' => (int)$this->db->lastInsertId(),
                'key' => $rawKey,
                'prefix' => $keyPrefix,
            ];
        }
        return [];
    }

    public function findByKey(string $rawKey)
    {
        $keyHash = hash('sha256', $rawKey);
        $stmt = $this->db->prepare(
            "SELECT ak.*, u.username, u.email, u.role as user_role
             FROM api_keys ak
             JOIN users u ON ak.user_id = u.id
             WHERE ak.key_hash = :hash AND ak.is_active = 1"
        );
        $stmt->bindParam(':hash', $keyHash);
        $stmt->execute();
        $key = $stmt->fetch();

        if ($key) {
            // Check expiry
            if (!empty($key['expires_at']) && strtotime($key['expires_at']) < time()) {
                return null;
            }
            // Update last used
            $this->db->prepare("UPDATE api_keys SET last_used = CURRENT_TIMESTAMP WHERE id = ?")->execute([$key['id']]);
        }
        return $key;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, key_prefix, name, scopes, last_used, is_active, expires_at, created_at
             FROM api_keys WHERE user_id = :uid ORDER BY created_at DESC"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function revoke(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE api_keys SET is_active = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();
        if ($result) Database::audit('api_key.revoke', "API key #{$id} revoked");
        return $result;
    }

    public function hasScope(array $keyScopes, string $requiredScope): bool
    {
        if (in_array('*', $keyScopes)) return true;
        return in_array($requiredScope, $keyScopes);
    }

    public function getAll(): array
    {
        return $this->db->query(
            "SELECT ak.*, u.username FROM api_keys ak JOIN users u ON ak.user_id = u.id ORDER BY ak.created_at DESC"
        )->fetchAll();
    }
}
