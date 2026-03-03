<?php

namespace App\Models;

use Database;
use PDO;

class Dispute
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO disputes (reporter_user_id, reporter_email, target_type, target_id, reason, description, evidence_urls)
             VALUES (:uid, :email, :type, :tid, :reason, :desc, :evidence)"
        );
        $stmt->bindParam(':uid', $data['reporter_user_id'] ?? null);
        $stmt->bindParam(':email', $data['reporter_email'] ?? '');
        $stmt->bindParam(':type', $data['target_type']);
        $stmt->bindParam(':tid', $data['target_id']);
        $stmt->bindParam(':reason', $data['reason']);
        $stmt->bindParam(':desc', $data['description'] ?? '');
        $stmt->bindParam(':evidence', $data['evidence_urls'] ?? '');

        if ($stmt->execute()) {
            Database::audit('dispute.create', "Dispute filed: {$data['target_type']} #{$data['target_id']}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.username as reporter_name, a.username as assigned_admin_name
             FROM disputes d
             LEFT JOIN users u ON d.reporter_user_id = u.id
             LEFT JOIN users a ON d.assigned_admin_id = a.id
             WHERE d.id = :id"
        );
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll(string $status = '', int $limit = 100): array
    {
        $sql = "SELECT d.*, u.username as reporter_name, a.username as assigned_admin_name
                FROM disputes d
                LEFT JOIN users u ON d.reporter_user_id = u.id
                LEFT JOIN users a ON d.assigned_admin_id = a.id";
        $params = [];

        if (!empty($status)) {
            $sql .= " WHERE d.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY d.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status, string $notes = '', ?int $adminId = null): bool
    {
        $sql = "UPDATE disputes SET status = :status, resolution_notes = :notes";
        if ($adminId) $sql .= ", assigned_admin_id = :admin";
        if (in_array($status, ['resolved', 'dismissed'])) $sql .= ", resolved_at = CURRENT_TIMESTAMP";
        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':id', $id);
        if ($adminId) $stmt->bindParam(':admin', $adminId);

        $result = $stmt->execute();
        if ($result) Database::audit('dispute.update', "Dispute #{$id} status: {$status}");
        return $result;
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM disputes WHERE status = :s");
        $stmt->bindParam(':s', $status);
        $stmt->execute();
        return (int)$stmt->fetch()['c'];
    }

    public function countOpen(): int
    {
        return $this->countByStatus('open') + $this->countByStatus('under_review');
    }
}
