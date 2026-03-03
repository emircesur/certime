<?php

namespace App\Models;

use Database;
use PDO;

class SkillTaxonomy
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM skill_taxonomy ORDER BY framework, category, name")->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM skill_taxonomy WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByCode(string $code)
    {
        $stmt = $this->db->prepare("SELECT * FROM skill_taxonomy WHERE code = :code");
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function search(string $query): array
    {
        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            "SELECT * FROM skill_taxonomy WHERE name LIKE :q OR code LIKE :q2 OR category LIKE :q3 ORDER BY name LIMIT 50"
        );
        $stmt->bindValue(':q', $like);
        $stmt->bindValue(':q2', $like);
        $stmt->bindValue(':q3', $like);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategories(): array
    {
        return array_column(
            $this->db->query("SELECT DISTINCT category FROM skill_taxonomy WHERE category != '' ORDER BY category")->fetchAll(),
            'category'
        );
    }

    public function getFrameworks(): array
    {
        return array_column(
            $this->db->query("SELECT DISTINCT framework FROM skill_taxonomy ORDER BY framework")->fetchAll(),
            'framework'
        );
    }

    public function create(string $code, string $name, string $framework = 'custom', string $category = '', string $description = ''): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO skill_taxonomy (code, name, framework, category, description) VALUES (:code, :name, :fw, :cat, :desc)"
        );
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':fw', $framework);
        $stmt->bindParam(':cat', $category);
        $stmt->bindParam(':desc', $description);

        if ($stmt->execute()) return (int)$this->db->lastInsertId();
        return false;
    }

    public function linkToCredential(int $credentialId, int $skillId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT OR IGNORE INTO credential_skills (credential_id, skill_id) VALUES (:cid, :sid)"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->bindParam(':sid', $skillId);
        return $stmt->execute();
    }

    public function unlinkFromCredential(int $credentialId, int $skillId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM credential_skills WHERE credential_id = :cid AND skill_id = :sid"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->bindParam(':sid', $skillId);
        return $stmt->execute();
    }

    public function getCredentialSkills(int $credentialId): array
    {
        $stmt = $this->db->prepare(
            "SELECT st.* FROM skill_taxonomy st
             JOIN credential_skills cs ON st.id = cs.skill_id
             WHERE cs.credential_id = :cid
             ORDER BY st.name"
        );
        $stmt->bindParam(':cid', $credentialId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
