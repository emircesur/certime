<?php

namespace App\Models;

use Database;
use PDO;

class Plan
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM plans ORDER BY price_monthly ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findBySlug(string $slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE slug = :slug");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getByType(string $type): array
    {
        $stmt = $this->db->prepare("SELECT * FROM plans WHERE type = :type ORDER BY price_monthly ASC");
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFeatures(int $planId): array
    {
        $plan = $this->findById($planId);
        if (!$plan || empty($plan['features'])) return [];
        return json_decode($plan['features'], true) ?: [];
    }
}
