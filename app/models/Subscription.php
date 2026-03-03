<?php

namespace App\Models;

use Database;
use PDO;

class Subscription
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByUser(int $userId)
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, p.name as plan_name, p.slug as plan_slug, p.features, p.max_credentials, p.max_users
             FROM subscriptions s
             JOIN plans p ON s.plan_id = p.id
             WHERE s.user_id = :uid AND s.status = 'active'
             ORDER BY s.created_at DESC LIMIT 1"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(int $userId, int $planId, string $billingCycle = 'monthly'): int|false
    {
        $plan = (new Plan())->findById($planId);
        if (!$plan) return false;

        // Deactivate existing subscriptions
        $stmt = $this->db->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = :uid AND status = 'active'");
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();

        $startDate = date('Y-m-d H:i:s');
        $endDate = $billingCycle === 'yearly'
            ? date('Y-m-d H:i:s', strtotime('+1 year'))
            : date('Y-m-d H:i:s', strtotime('+1 month'));

        $stmt = $this->db->prepare(
            "INSERT INTO subscriptions (user_id, plan_id, billing_cycle, start_date, end_date, status)
             VALUES (:uid, :pid, :bc, :start, :end, 'active')"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':pid', $planId);
        $stmt->bindParam(':bc', $billingCycle);
        $stmt->bindParam(':start', $startDate);
        $stmt->bindParam(':end', $endDate);

        if ($stmt->execute()) {
            // Update user plan_id
            $upd = $this->db->prepare("UPDATE users SET plan_id = :pid WHERE id = :uid");
            $upd->bindParam(':pid', $planId);
            $upd->bindParam(':uid', $userId);
            $upd->execute();

            Database::audit('subscription.create', "User {$userId} subscribed to plan {$planId}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function cancel(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE subscriptions SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
             WHERE user_id = :uid AND status = 'active'"
        );
        $stmt->bindParam(':uid', $userId);
        $result = $stmt->execute();

        if ($result) {
            // Set user plan back to free plan (id=1)
            $upd = $this->db->prepare("UPDATE users SET plan_id = 1 WHERE id = :uid");
            $upd->bindParam(':uid', $userId);
            $upd->execute();
            Database::audit('subscription.cancel', "User {$userId} cancelled subscription");
        }
        return $result;
    }

    public function getUserPlanFeatures(int $userId): array
    {
        $sub = $this->findByUser($userId);
        if ($sub && !empty($sub['features'])) {
            return json_decode($sub['features'], true) ?: [];
        }
        // Default free plan features
        return ['pdf_download' => true, 'basic_badge' => true];
    }

    public function getUserCredentialLimit(int $userId): int
    {
        $sub = $this->findByUser($userId);
        return $sub ? (int)$sub['max_credentials'] : 5;
    }
}
