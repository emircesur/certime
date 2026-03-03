<?php

namespace App\Models;

use Database;
use PDO;

class Institution
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $name, int $ownerId, string $billingEmail = '', int $planId = null): int|false
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
        $slug = trim($slug, '-');

        // Ensure unique slug
        $existing = $this->findBySlug($slug);
        if ($existing) {
            $slug .= '-' . rand(100, 999);
        }

        $stmt = $this->db->prepare(
            "INSERT INTO institutions (name, slug, owner_user_id, billing_email, plan_id)
             VALUES (:name, :slug, :owner, :email, :plan)"
        );
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':owner', $ownerId);
        $stmt->bindParam(':email', $billingEmail);
        $stmt->bindParam(':plan', $planId);

        if ($stmt->execute()) {
            $id = (int)$this->db->lastInsertId();
            // Add owner as institution member
            $this->addMember($id, $ownerId, 'owner');
            Database::audit('institution.create', "Institution created: {$name}", $ownerId);
            return $id;
        }
        return false;
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM institutions WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findBySlug(string $slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM institutions WHERE slug = :slug");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByOwner(int $ownerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM institutions WHERE owner_user_id = :uid ORDER BY created_at DESC");
        $stmt->bindParam(':uid', $ownerId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAll(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.*, u.username as owner_name, u.email as owner_email, p.name as plan_name,
                    (SELECT COUNT(*) FROM institution_members im WHERE im.institution_id = i.id AND im.status = 'active') as member_count,
                    (SELECT COUNT(*) FROM departments d WHERE d.institution_id = i.id) as dept_count
             FROM institutions i
             LEFT JOIN users u ON i.owner_user_id = u.id
             LEFT JOIN plans p ON i.plan_id = p.id
             ORDER BY i.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'logo_url', 'domain', 'status', 'billing_email', 'settings', 'plan_id'];
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
        $sql = "UPDATE institutions SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function suspend(int $id): bool
    {
        return $this->update($id, ['status' => 'suspended']);
    }

    public function terminate(int $id): bool
    {
        return $this->update($id, ['status' => 'terminated']);
    }

    public function activate(int $id): bool
    {
        return $this->update($id, ['status' => 'active']);
    }

    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as c FROM institutions");
        return (int)$stmt->fetch()['c'];
    }

    // --- Members ---

    public function addMember(int $institutionId, int $userId, string $role = 'viewer', ?int $departmentId = null): int|false
    {
        // Check if already a member
        $stmt = $this->db->prepare(
            "SELECT id FROM institution_members WHERE institution_id = :iid AND user_id = :uid AND status = 'active'"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        if ($stmt->fetch()) return false;

        $stmt = $this->db->prepare(
            "INSERT INTO institution_members (institution_id, department_id, user_id, role)
             VALUES (:iid, :did, :uid, :role)"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':did', $departmentId);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            // Update user's institution_id
            $upd = $this->db->prepare("UPDATE users SET institution_id = :iid WHERE id = :uid");
            $upd->bindParam(':iid', $institutionId);
            $upd->bindParam(':uid', $userId);
            $upd->execute();

            Database::audit('institution.add_member', "User {$userId} added to institution {$institutionId} as {$role}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function inviteMember(int $institutionId, string $email, string $role = 'viewer', ?int $departmentId = null): string|false
    {
        $token = bin2hex(random_bytes(16));
        $stmt = $this->db->prepare(
            "INSERT INTO institution_members (institution_id, department_id, user_id, role, status, invited_email, invite_token)
             VALUES (:iid, :did, 0, :role, 'invited', :email, :token)"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':did', $departmentId);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);

        if ($stmt->execute()) {
            Database::audit('institution.invite', "Invited {$email} to institution {$institutionId} as {$role}");
            return $token;
        }
        return false;
    }

    public function getMembers(int $institutionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT im.*, u.username, u.email, u.full_name, d.name as department_name
             FROM institution_members im
             LEFT JOIN users u ON im.user_id = u.id
             LEFT JOIN departments d ON im.department_id = d.id
             WHERE im.institution_id = :iid AND im.status != 'removed'
             ORDER BY im.created_at DESC"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function removeMember(int $institutionId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE institution_members SET status = 'removed' WHERE institution_id = :iid AND user_id = :uid"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':uid', $userId);
        return $stmt->execute();
    }

    public function updateMemberRole(int $memberId, string $role): bool
    {
        $validRoles = ['owner', 'issuer', 'designer', 'viewer'];
        if (!in_array($role, $validRoles)) return false;

        $stmt = $this->db->prepare("UPDATE institution_members SET role = :role WHERE id = :id");
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':id', $memberId);
        return $stmt->execute();
    }

    public function getUserRole(int $institutionId, int $userId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT role FROM institution_members WHERE institution_id = :iid AND user_id = :uid AND status = 'active'"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $row['role'] : null;
    }
}
