<?php

namespace App\Models;

use Database;
use PDO;

class TeamMember
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getTeamByOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare(
            "SELECT tm.*, u.username, u.email, u.full_name
             FROM team_members tm
             JOIN users u ON tm.user_id = u.id
             JOIN subscriptions s ON tm.subscription_id = s.id
             WHERE s.user_id = :oid
             ORDER BY tm.created_at DESC"
        );
        $stmt->bindParam(':oid', $ownerId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the active subscription ID for a team owner, or false if none.
     */
    private function ownerSubId(int $ownerId): int|false
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM subscriptions WHERE user_id = :uid AND status IN ('active','trialing') ORDER BY id DESC LIMIT 1"
        );
        $stmt->bindParam(':uid', $ownerId);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : false;
    }

    public function addMember(int $teamOwnerId, int $userId, string $role = 'member'): int|false
    {
        $subId = $this->ownerSubId($teamOwnerId);
        if (!$subId) return false;

        // Check not already member
        $stmt = $this->db->prepare(
            "SELECT id FROM team_members WHERE subscription_id = :sid AND user_id = :uid"
        );
        $stmt->bindParam(':sid', $subId);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        if ($stmt->fetch()) return false;

        $stmt = $this->db->prepare(
            "INSERT INTO team_members (subscription_id, user_id, role) VALUES (:sid, :uid, :role)"
        );
        $stmt->bindParam(':sid', $subId);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            Database::audit('team.add_member', "User {$userId} added to team owned by {$teamOwnerId}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function removeMember(int $teamOwnerId, int $userId): bool
    {
        $subId = $this->ownerSubId($teamOwnerId);
        if (!$subId) return false;

        $stmt = $this->db->prepare(
            "DELETE FROM team_members WHERE subscription_id = :sid AND user_id = :uid"
        );
        $stmt->bindParam(':sid', $subId);
        $stmt->bindParam(':uid', $userId);
        $result = $stmt->execute();
        if ($result) {
            Database::audit('team.remove_member', "User {$userId} removed from team owned by {$teamOwnerId}");
        }
        return $result;
    }

    public function updateRole(int $memberId, string $role): bool
    {
        $stmt = $this->db->prepare("UPDATE team_members SET role = :role WHERE id = :id");
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $memberId);
        return $stmt->execute();
    }

    public function countMembers(int $teamOwnerId): int
    {
        $subId = $this->ownerSubId($teamOwnerId);
        if (!$subId) return 0;

        $stmt = $this->db->prepare("SELECT COUNT(*) as c FROM team_members WHERE subscription_id = :sid");
        $stmt->bindParam(':sid', $subId);
        $stmt->execute();
        return (int)$stmt->fetch()['c'];
    }

    public function getTeamsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT tm.*, u.username as owner_name, u.institution_name
             FROM team_members tm
             JOIN subscriptions s ON tm.subscription_id = s.id
             JOIN users u ON s.user_id = u.id
             WHERE tm.user_id = :uid"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
