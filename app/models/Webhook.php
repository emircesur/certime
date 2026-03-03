<?php

namespace App\Models;

use Database;
use PDO;

class Webhook
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, string $url, array $events, string $secret = '', ?int $institutionId = null): int|false
    {
        if (empty($secret)) {
            $secret = bin2hex(random_bytes(16));
        }

        $stmt = $this->db->prepare(
            "INSERT INTO webhooks (user_id, institution_id, url, secret, events)
             VALUES (:uid, :iid, :url, :secret, :events)"
        );
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':iid', $institutionId);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':secret', $secret);
        $eventsJson = json_encode($events);
        $stmt->bindParam(':events', $eventsJson);

        if ($stmt->execute()) {
            Database::audit('webhook.create', "Webhook created: {$url}");
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM webhooks WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM webhooks WHERE user_id = :uid ORDER BY created_at DESC");
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT w.*, u.username FROM webhooks w LEFT JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM webhooks WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function toggle(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE webhooks SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = :id"
        );
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Broadcast an event to all matching webhooks
     */
    public function broadcast(string $eventType, array $payload): int
    {
        $stmt = $this->db->query("SELECT * FROM webhooks WHERE is_active = 1");
        $webhooks = $stmt->fetchAll();
        $sent = 0;

        foreach ($webhooks as $webhook) {
            $events = json_decode($webhook['events'], true) ?: [];
            if (!in_array($eventType, $events) && !in_array('*', $events)) continue;

            $body = json_encode([
                'event' => $eventType,
                'timestamp' => date('c'),
                'data' => $payload,
            ]);

            $signature = hash_hmac('sha256', $body, $webhook['secret']);

            // Log the event
            $logStmt = $this->db->prepare(
                "INSERT INTO webhook_events (webhook_id, event_type, payload, status) VALUES (:wid, :type, :payload, 'pending')"
            );
            $logStmt->bindParam(':wid', $webhook['id']);
            $logStmt->bindParam(':type', $eventType);
            $logStmt->bindParam(':payload', $body);
            $logStmt->execute();
            $eventId = (int)$this->db->lastInsertId();

            // Send HTTP request
            $ch = curl_init($webhook['url']);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Webhook-Signature: sha256=' . $signature,
                    'X-Event-Type: ' . $eventType,
                    'User-Agent: CertiMe-Webhook/1.0',
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $status = ($httpCode >= 200 && $httpCode < 300) ? 'sent' : 'failed';

            $updStmt = $this->db->prepare(
                "UPDATE webhook_events SET response_code = :code, response_body = :body, status = :status WHERE id = :id"
            );
            $updStmt->bindParam(':code', $httpCode);
            $updStmt->bindParam(':body', $response ?: '');
            $updStmt->bindParam(':status', $status);
            $updStmt->bindParam(':id', $eventId);
            $updStmt->execute();

            // Update webhook last_triggered
            $this->db->prepare("UPDATE webhooks SET last_triggered = CURRENT_TIMESTAMP WHERE id = ?")->execute([$webhook['id']]);

            if ($status === 'failed') {
                $this->db->prepare("UPDATE webhooks SET failure_count = failure_count + 1 WHERE id = ?")->execute([$webhook['id']]);
            }

            $sent++;
        }

        return $sent;
    }

    public function getEvents(int $webhookId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM webhook_events WHERE webhook_id = :wid ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindParam(':wid', $webhookId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
