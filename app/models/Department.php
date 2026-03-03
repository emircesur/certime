<?php

namespace App\Models;

use Database;
use PDO;

class Department
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $institutionId, string $name, string $description = '', ?int $headUserId = null): int|false
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));

        $stmt = $this->db->prepare(
            "INSERT INTO departments (institution_id, name, slug, description, head_user_id)
             VALUES (:iid, :name, :slug, :desc, :head)"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':head', $headUserId);

        if ($stmt->execute()) {
            Database::audit('department.create', "Department created: {$name} in institution {$institutionId}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByInstitution(int $institutionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.full_name as head_name,
                    (SELECT COUNT(*) FROM institution_members im WHERE im.department_id = d.id AND im.status = 'active') as member_count,
                    (SELECT COUNT(*) FROM credentials c WHERE c.department_id = d.id) as credential_count
             FROM departments d
             LEFT JOIN users u ON d.head_user_id = u.id
             WHERE d.institution_id = :iid
             ORDER BY d.name ASC"
        );
        $stmt->bindParam(':iid', $institutionId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'description', 'head_user_id', 'is_active'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($sets)) return false;

        $sql = "UPDATE departments SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
