<?php

namespace App\Models;

use Database;
use PDO;

class Evidence
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByCredential(int $credentialId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM evidence WHERE credential_id = :cid ORDER BY created_at DESC"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM evidence WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(int $credentialId, string $type, string $title, string $url = '', string $description = '', string $filePath = ''): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO evidence (credential_id, type, title, url, description, file_path)
             VALUES (:cid, :type, :title, :url, :desc, :fp)"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':fp', $filePath);

        if ($stmt->execute()) {
            Database::audit('evidence.create', "Evidence added to credential {$credentialId}: {$title}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM evidence WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function countByCredential(int $credentialId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM evidence WHERE credential_id = :cid");
        $stmt->bindParam(':cid', $credentialId);
        $stmt->execute();
        return (int)$stmt->fetch()['c'];
    }
}
