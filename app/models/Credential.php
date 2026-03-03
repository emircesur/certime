<?php

namespace App\Models;

use Database;
use PDO;

class Credential
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find credentials by user ID
     */
    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM credentials WHERE user_id = :user_id AND status = 'active' ORDER BY issuance_date DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a credential by its unique ID
     */
    public function findByUid(string $uid)
    {
        $stmt = $this->db->prepare("SELECT * FROM credentials WHERE credential_uid = :uid");
        $stmt->bindParam(':uid', $uid);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get all credentials with optional pagination
     */
    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.full_name, u.username as recipient_name, u.email as recipient_email 
             FROM credentials c 
             LEFT JOIN users u ON c.user_id = u.id 
             ORDER BY c.issuance_date DESC 
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count total credentials
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM credentials");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Count active credentials
     */
    public function countActive(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM credentials WHERE status = 'active'");
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Create a new credential
     */
    public function create(int $userId, string $credentialUid, string $courseName, string $description, string $badgeJsonld, string $category = 'general', string $skills = '', string $issuerName = '', string $credentialType = 'certificate', float $creditHours = 0): int|false
    {
        $issuerName = $issuerName ?: APP_NAME;
        $issuanceDate = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            "INSERT INTO credentials (user_id, credential_uid, course_name, description, issuer_name, category, skills, credential_type, credit_hours, issuance_date, badge_jsonld) 
             VALUES (:user_id, :credential_uid, :course_name, :description, :issuer_name, :category, :skills, :credential_type, :credit_hours, :issuance_date, :badge_jsonld)"
        );

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':credential_uid', $credentialUid);
        $stmt->bindParam(':course_name', $courseName);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':issuer_name', $issuerName);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':skills', $skills);
        $stmt->bindParam(':credential_type', $credentialType);
        $stmt->bindParam(':credit_hours', $creditHours);
        $stmt->bindParam(':issuance_date', $issuanceDate);
        $stmt->bindParam(':badge_jsonld', $badgeJsonld);

        if ($stmt->execute()) {
            $id = (int)$this->db->lastInsertId();
            Database::audit('credential.create', "Credential issued: {$credentialUid} to user {$userId}");
            return $id;
        }
        return false;
    }

    /**
     * Revoke a credential
     */
    public function revoke(string $uid): bool
    {
        $stmt = $this->db->prepare("UPDATE credentials SET status = 'revoked' WHERE credential_uid = :uid");
        $stmt->bindParam(':uid', $uid);
        $result = $stmt->execute();
        if ($result) {
            Database::audit('credential.revoke', "Credential revoked: {$uid}");
        }
        return $result;
    }

    /**
     * Search credentials
     */
    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.full_name, u.username as recipient_name 
             FROM credentials c 
             LEFT JOIN users u ON c.user_id = u.id 
             WHERE c.course_name LIKE :q OR c.credential_uid LIKE :q2 OR c.description LIKE :q3
             ORDER BY c.issuance_date DESC LIMIT 50"
        );
        $like = '%' . $query . '%';
        $stmt->bindValue(':q', $like);
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':q3', $like);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get credential categories
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT category FROM credentials WHERE category != '' ORDER BY category");
        return array_column($stmt->fetchAll(), 'category');
    }

    /**
     * Count credentials by user
     */
    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM credentials WHERE user_id = :uid AND status = 'active'");
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Update a credential
     */
    public function update(string $uid, array $data): bool
    {
        $allowed = ['course_name', 'description', 'category', 'skills', 'credential_type', 'credit_hours', 'expiration_date', 'renewal_status', 'pdf_template'];
        $sets = [];
        $params = [':uid' => $uid];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($sets)) return false;

        $sql = "UPDATE credentials SET " . implode(', ', $sets) . " WHERE credential_uid = :uid";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);
        if ($result) {
            Database::audit('credential.update', "Credential updated: {$uid}");
        }
        return $result;
    }

    /**
     * Get expiring credentials (within days)
     */
    public function getExpiring(int $days = 30): array
    {
        $futureDate = date('Y-m-d', strtotime("+{$days} days"));
        $stmt = $this->db->prepare(
            "SELECT c.*, u.full_name, u.email, u.username as recipient_name
             FROM credentials c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.expiration_date IS NOT NULL 
               AND c.expiration_date != ''
               AND c.expiration_date <= :future
               AND c.status = 'active'
             ORDER BY c.expiration_date ASC"
        );
        $stmt->bindParam(':future', $futureDate);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get expired credentials
     */
    public function getExpired(): array
    {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT c.*, u.full_name, u.email, u.username as recipient_name
             FROM credentials c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.expiration_date IS NOT NULL 
               AND c.expiration_date != ''
               AND c.expiration_date < :today
               AND c.status = 'active'
             ORDER BY c.expiration_date ASC"
        );
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Renew a credential
     */
    public function renew(string $uid, string $newExpirationDate): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE credentials SET expiration_date = :exp, renewal_status = 'renewed' WHERE credential_uid = :uid"
        );
        $stmt->bindParam(':exp', $newExpirationDate);
        $stmt->bindParam(':uid', $uid);
        $result = $stmt->execute();
        if ($result) {
            Database::audit('credential.renew', "Credential renewed: {$uid} until {$newExpirationDate}");
        }
        return $result;
    }
}
