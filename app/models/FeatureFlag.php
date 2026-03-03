<?php

namespace App\Models;

use Database;
use PDO;

class FeatureFlag
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getGlobal(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM feature_flags WHERE institution_id IS NULL ORDER BY flag_name ASC"
        );
        return $stmt->fetchAll();
    }

    public function getForInstitution(int $institutionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM feature_flags WHERE institution_id = :iid ORDER BY flag_name ASC"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function isEnabled(string $flagName, ?int $institutionId = null): bool
    {
        // Check institution-specific override first
        if ($institutionId) {
            $stmt = $this->db->prepare(
                "SELECT is_enabled FROM feature_flags WHERE flag_name = :name AND institution_id = :iid"
            );
            $stmt->bindParam(':name', $flagName);
            $stmt->bindParam(':iid', $institutionId);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row) return (bool)$row['is_enabled'];
        }

        // Fall back to global default
        $stmt = $this->db->prepare(
            "SELECT is_enabled FROM feature_flags WHERE flag_name = :name AND institution_id IS NULL"
        );
        $stmt->bindParam(':name', $flagName);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (bool)$row['is_enabled'] : false;
    }

    public function toggle(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE feature_flags SET is_enabled = CASE WHEN is_enabled = 1 THEN 0 ELSE 1 END, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();
        if ($result) {
            Database::audit('feature_flag.toggle', "Feature flag #{$id} toggled");
        }
        return $result;
    }

    public function setForInstitution(int $institutionId, string $flagName, bool $enabled): bool
    {
        // Check if exists
        $stmt = $this->db->prepare(
            "SELECT id FROM feature_flags WHERE institution_id = :iid AND flag_name = :name"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':name', $flagName);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row) {
            $upd = $this->db->prepare(
                "UPDATE feature_flags SET is_enabled = :enabled, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
            );
            $upd->bindValue(':enabled', $enabled ? 1 : 0, PDO::PARAM_INT);
            $upd->bindParam(':id', $row['id']);
            return $upd->execute();
        }

        $ins = $this->db->prepare(
            "INSERT INTO feature_flags (institution_id, flag_name, is_enabled, description)
             VALUES (:iid, :name, :enabled, '')"
        );
        $ins->bindParam(':iid', $institutionId);
        $ins->bindParam(':name', $flagName);
        $ins->bindValue(':enabled', $enabled ? 1 : 0, PDO::PARAM_INT);
        return $ins->execute();
    }
}
