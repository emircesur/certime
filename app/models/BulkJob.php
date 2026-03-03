<?php

namespace App\Models;

use Database;
use PDO;

class BulkJob
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM bulk_jobs WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM bulk_jobs WHERE user_id = :uid ORDER BY created_at DESC"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(int $createdBy, string $filename, int $totalRows, string $columnMapping): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO bulk_jobs (user_id, filename, total_rows, mapping_json, status)
             VALUES (:uid, :fn, :total, :mapping, 'pending')"
        );
        $stmt->bindParam(':uid', $createdBy);
        $stmt->bindParam(':fn', $filename);
        $stmt->bindParam(':total', $totalRows);
        $stmt->bindParam(':mapping', $columnMapping);

        if ($stmt->execute()) {
            Database::audit('bulk_job.create', "Bulk job created by user {$createdBy}: {$filename}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function updateProgress(int $id, int $processedRows, int $successCount, int $errorCount): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE bulk_jobs SET processed_rows = :pr, success_count = :sc, error_count = :ec,
             status = CASE WHEN :pr2 >= total_rows THEN 'completed' ELSE 'processing' END,
             completed_at = CASE WHEN :pr3 >= total_rows THEN CURRENT_TIMESTAMP ELSE completed_at END
             WHERE id = :id"
        );
        $stmt->bindParam(':pr', $processedRows);
        $stmt->bindParam(':sc', $successCount);
        $stmt->bindParam(':ec', $errorCount);
        $stmt->bindParam(':pr2', $processedRows);
        $stmt->bindParam(':pr3', $processedRows);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function setError(int $id, string $errorLog): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE bulk_jobs SET status = 'failed', errors_log = :err, completed_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindParam(':err', $errorLog);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getAll(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT bj.*, u.username as creator_name
             FROM bulk_jobs bj
             LEFT JOIN users u ON bj.user_id = u.id
             ORDER BY bj.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
