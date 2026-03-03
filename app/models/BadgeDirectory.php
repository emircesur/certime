<?php

namespace App\Models;

use Database;
use PDO;

class BadgeDirectory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare(
            "INSERT INTO badge_directory (institution_id, title, description, category, skills, badge_image_url, course_url, provider_name, is_featured)
             VALUES (:iid, :title, :desc, :cat, :skills, :img, :url, :provider, :featured)"
        );
        $stmt->bindParam(':iid', $data['institution_id'] ?? null);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':desc', $data['description'] ?? '');
        $stmt->bindParam(':cat', $data['category'] ?? 'general');
        $stmt->bindParam(':skills', $data['skills'] ?? '');
        $stmt->bindParam(':img', $data['badge_image_url'] ?? '');
        $stmt->bindParam(':url', $data['course_url'] ?? '');
        $stmt->bindParam(':provider', $data['provider_name'] ?? '');
        $stmt->bindValue(':featured', $data['is_featured'] ?? 0, PDO::PARAM_INT);

        if ($stmt->execute()) return (int)$this->db->lastInsertId();
        return false;
    }

    public function search(string $query = '', string $category = '', int $limit = 50): array
    {
        $sql = "SELECT bd.*, i.name as institution_name
                FROM badge_directory bd
                LEFT JOIN institutions i ON bd.institution_id = i.id
                WHERE bd.is_active = 1";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (bd.title LIKE :q OR bd.description LIKE :q2 OR bd.skills LIKE :q3 OR bd.provider_name LIKE :q4)";
            $like = '%' . $query . '%';
            $params[':q'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        if (!empty($category)) {
            $sql .= " AND bd.category = :cat";
            $params[':cat'] = $category;
        }

        $sql .= " ORDER BY bd.is_featured DESC, bd.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM badge_directory WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getCategories(): array
    {
        return array_column(
            $this->db->query("SELECT DISTINCT category FROM badge_directory WHERE is_active = 1 AND category != '' ORDER BY category")->fetchAll(),
            'category'
        );
    }

    public function getFeatured(int $limit = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT bd.*, i.name as institution_name FROM badge_directory bd
             LEFT JOIN institutions i ON bd.institution_id = i.id
             WHERE bd.is_active = 1 AND bd.is_featured = 1
             ORDER BY bd.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['title', 'description', 'category', 'skills', 'badge_image_url', 'course_url', 'provider_name', 'is_featured', 'is_active'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($sets)) return false;

        $sql = "UPDATE badge_directory SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM badge_directory WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function count(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) as c FROM badge_directory WHERE is_active = 1")->fetch()['c'];
    }
}
