<?php

namespace App\Models;

use Database;
use PDO;

class UploadedCredential
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private static string $ALIASES =
        "*, issuer_name AS issuer, issue_date AS issued_date";

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT " . self::$ALIASES . " FROM uploaded_credentials WHERE user_id = :uid ORDER BY created_at DESC"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT " . self::$ALIASES . " FROM uploaded_credentials WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(int $userId, string $title, string $issuer, string $description = '', string $externalUrl = '', string $filePath = '', string $credentialType = 'certificate', string $issuedDate = '', string $expirationDate = ''): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO uploaded_credentials (user_id, title, issuer_name, description, external_url, file_path, credential_type, issue_date, expiration_date)
             VALUES (:uid, :title, :issuer, :desc, :url, :fp, :type, :issued, :exp)"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':issuer', $issuer);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':url', $externalUrl);
        $stmt->bindParam(':fp', $filePath);
        $stmt->bindParam(':type', $credentialType);
        $stmt->bindParam(':issued', $issuedDate ?: date('Y-m-d'));
        $stmt->bindParam(':exp', $expirationDate);

        if ($stmt->execute()) {
            Database::audit('uploaded_credential.create', "User {$userId} uploaded external credential: {$title}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function update(int $id, array $data): bool
    {
        // Map view field names to actual DB column names
        $colMap = ['issuer' => 'issuer_name', 'issued_date' => 'issue_date'];
        $mapped = [];
        foreach ($data as $key => $value) {
            $mapped[$colMap[$key] ?? $key] = $value;
        }

        $allowed = ['title', 'issuer_name', 'description', 'external_url', 'credential_type', 'issue_date', 'expiration_date', 'is_verified', 'status'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($mapped as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($sets)) return false;

        $sql = "UPDATE uploaded_credentials SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM uploaded_credentials WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM uploaded_credentials WHERE user_id = :uid");
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return (int)$stmt->fetch()['c'];
    }
}
