<?php

namespace App\Models;

use Database;
use PDO;

class Endorsement
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new endorsement request
     */
    public function create(int $credentialId, string $name, string $email, string $org, string $title, string $comment): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO endorsements (credential_id, endorser_name, endorser_email, endorser_org, endorser_title, comment) 
             VALUES (:cid, :name, :email, :org, :title, :comment)"
        );
        $stmt->execute([
            ':cid' => $credentialId,
            ':name' => $name,
            ':email' => $email,
            ':org' => $org,
            ':title' => $title,
            ':comment' => $comment,
        ]);

        if ($stmt->rowCount() > 0) {
            $id = (int)$this->db->lastInsertId();
            Database::audit('endorsement.create', "Endorsement #{$id} for credential #{$credentialId}");
            return $id;
        }
        return false;
    }

    /**
     * Find endorsements by credential ID
     */
    public function findByCredential(int $credentialId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM endorsements WHERE credential_id = :cid ORDER BY created_at DESC"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find approved endorsements by credential ID
     */
    public function findApprovedByCredential(int $credentialId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM endorsements WHERE credential_id = :cid AND status = 'approved' ORDER BY created_at DESC"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find by ID
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM endorsements WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Update endorsement status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $validStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $validStatuses)) return false;

        $stmt = $this->db->prepare("UPDATE endorsements SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();
        
        if ($result) {
            Database::audit('endorsement.' . $status, "Endorsement #{$id} {$status}");
        }
        return $result;
    }

    /**
     * Sign an endorsement (admin/moderator action)
     */
    public function sign(int $id, string $signature): bool
    {
        $stmt = $this->db->prepare("UPDATE endorsements SET signature = :sig, status = 'approved' WHERE id = :id");
        $stmt->bindParam(':sig', $signature);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Get pending endorsements
     */
    public function getPending(): array
    {
        $stmt = $this->db->query(
            "SELECT e.*, c.course_name, c.credential_uid 
             FROM endorsements e 
             JOIN credentials c ON e.credential_id = c.id 
             WHERE e.status = 'pending' 
             ORDER BY e.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Count pending endorsements
     */
    public function countPending(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM endorsements WHERE status = 'pending'");
        return (int)$stmt->fetch()['total'];
    }
}
